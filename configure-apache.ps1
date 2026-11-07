param(
    [string] $XamppRoot = 'C:\xampp'
)

$ErrorActionPreference = 'Stop'

$apache = Join-Path $XamppRoot 'apache\bin\httpd.exe'
$httpdConf = Join-Path $XamppRoot 'apache\conf\httpd.conf'
$vhostsConf = Join-Path $XamppRoot 'apache\conf\extra\httpd-vhosts.conf'
$phpIni = Join-Path $XamppRoot 'php\php.ini'
$apacheDirectory = Join-Path $XamppRoot 'apache'
$projectPublic = (Resolve-Path (Join-Path $PSScriptRoot 'public')).Path.Replace('\', '/')

function Test-ListeningPort([int] $Port) {
    return [bool](Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction SilentlyContinue |
        Select-Object -First 1)
}

function Stop-LocalApache {
    $listeners = @(Get-NetTCPConnection -State Listen -ErrorAction SilentlyContinue |
        Where-Object { $_.LocalPort -in 80, 443 })
    if ($listeners.Count -eq 0) {
        return
    }

    $apacheProcesses = @(Get-CimInstance Win32_Process |
        Where-Object { $_.ExecutablePath -and $_.ExecutablePath -ieq $apache })
    $listenerIds = @($listeners | Select-Object -ExpandProperty OwningProcess -Unique)
    foreach ($listenerId in $listenerIds) {
        if (-not ($apacheProcesses.ProcessId -contains $listenerId)) {
            throw "Port 80/443 is held by a process other than this XAMPP Apache (PID $listenerId)."
        }
    }

    $targetIds = [System.Collections.Generic.List[int]]::new()
    foreach ($listenerId in $listenerIds) {
        $targetIds.Add([int] $listenerId)
    }

    $added = $true
    while ($added) {
        $added = $false
        foreach ($process in $apacheProcesses) {
            if ($targetIds.Contains([int] $process.ParentProcessId) -and
                -not $targetIds.Contains([int] $process.ProcessId)) {
                $targetIds.Add([int] $process.ProcessId)
                $added = $true
            }
        }
    }

    foreach ($processId in $targetIds) {
        if (Get-Process -Id $processId -ErrorAction SilentlyContinue) {
            Stop-Process -Id $processId -ErrorAction Stop
        }
    }

    for ($attempt = 0; $attempt -lt 40; $attempt++) {
        if (-not (Test-ListeningPort 80)) {
            return
        }
        Start-Sleep -Milliseconds 250
    }

    throw 'The existing Apache process did not stop; configuration changes were not applied.'
}

function Start-LocalApache {
    Start-Process -FilePath $apache `
        -ArgumentList @('-d', $apacheDirectory, '-f', $httpdConf) `
        -WorkingDirectory (Split-Path $apache -Parent) `
        -WindowStyle Hidden

    for ($attempt = 0; $attempt -lt 40; $attempt++) {
        if (Test-ListeningPort 80) {
            return
        }
        Start-Sleep -Milliseconds 250
    }

    throw 'Apache did not start listening on port 80.'
}

foreach ($path in @($apache, $httpdConf, $vhostsConf, $phpIni)) {
    if (-not (Test-Path $path -PathType Leaf)) {
        throw "Required XAMPP file not found: $path"
    }
}

$originalVhosts = [System.IO.File]::ReadAllText($vhostsConf, [System.Text.Encoding]::Default)
$markerPattern = '(?ms)^# BEGIN restaurant-menu-php local development\r?\n.*?^# END restaurant-menu-php local development\r?\n?'
$vhostsWithoutBlock = [System.Text.RegularExpressions.Regex]::Replace($originalVhosts, $markerPattern, '')
$vhostBlock = @"
# BEGIN restaurant-menu-php local development
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "$projectPublic"
    <Directory "$projectPublic">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
# END restaurant-menu-php local development
"@
$newVhosts = $vhostsWithoutBlock.TrimEnd() + [Environment]::NewLine + $vhostBlock + [Environment]::NewLine

$originalIni = [System.IO.File]::ReadAllText($phpIni, [System.Text.Encoding]::Default)
$newIni = $originalIni
$opcacheSettings = [ordered]@{
    'opcache.enable' = '1'
    'opcache.validate_timestamps' = '1'
    'opcache.revalidate_freq' = '0'
}
foreach ($name in $opcacheSettings.Keys) {
    $value = $opcacheSettings[$name]
    $pattern = "(?m)^[ \t]*;?[ \t]*$([System.Text.RegularExpressions.Regex]::Escape($name))[ \t]*=.*$"
    $settingMatches = [System.Text.RegularExpressions.Regex]::Matches($newIni, $pattern)

    if ($settingMatches.Count -gt 1) {
        throw "Multiple PHP INI entries found for $name; refusing to guess which setting to change."
    }

    if ($settingMatches.Count -eq 1) {
        $newIni = ([System.Text.RegularExpressions.Regex]::new($pattern)).Replace(
            $newIni,
            "$name=$value",
            1
        )
    }
    else {
        throw "PHP INI setting not found: $name"
    }
}

$apacheWasRunning = Test-ListeningPort 80
$apacheStopped = $false
try {
    [System.IO.File]::WriteAllText($vhostsConf, $newVhosts, [System.Text.Encoding]::Default)
    [System.IO.File]::WriteAllText($phpIni, $newIni, [System.Text.Encoding]::Default)

    $previousErrorActionPreference = $ErrorActionPreference
    try {
        $ErrorActionPreference = 'Continue'
        & $apache -t -f $httpdConf 2>&1 | Out-Null
        $apacheConfigValid = $LASTEXITCODE -eq 0
    }
    finally {
        $ErrorActionPreference = $previousErrorActionPreference
    }

    if (-not $apacheConfigValid) {
        throw 'Apache configuration validation failed.'
    }

    if ($apacheWasRunning) {
        Stop-LocalApache
        $apacheStopped = $true
        Start-LocalApache
    }
}
catch {
    [System.IO.File]::WriteAllText($vhostsConf, $originalVhosts, [System.Text.Encoding]::Default)
    [System.IO.File]::WriteAllText($phpIni, $originalIni, [System.Text.Encoding]::Default)

    if ($apacheStopped) {
        try {
            Stop-LocalApache
            Start-LocalApache
        }
        catch {
            throw "Apache configuration was rolled back, but Apache could not be restarted: $($_.Exception.Message)"
        }
    }

    throw
}

Write-Host "Apache now serves $projectPublic at http://localhost"
Write-Host 'PHP OPcache timestamp validation is enabled with a zero-second recheck interval.'
if (-not $apacheWasRunning) {
    Write-Host 'Apache is not running; start it with .\dev.ps1.'
}
