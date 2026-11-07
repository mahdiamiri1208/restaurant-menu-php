param(
    [string] $XamppRoot = 'C:\xampp'
)

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path $PSScriptRoot).Path
$apache = Join-Path $XamppRoot 'apache\bin\httpd.exe'
$httpdConf = Join-Path $XamppRoot 'apache\conf\httpd.conf'
$vhostsConf = Join-Path $XamppRoot 'apache\conf\extra\httpd-vhosts.conf'
$mysql = Join-Path $XamppRoot 'mysql\bin\mysqld.exe'
$mysqlIni = Join-Path $XamppRoot 'mysql\bin\my.ini'
$environmentPath = Join-Path $projectRoot '.env'
$projectPublic = (Resolve-Path (Join-Path $projectRoot 'public')).Path.Replace('\', '/')

function Test-ApacheConfiguration {
    $previousErrorActionPreference = $ErrorActionPreference
    try {
        $ErrorActionPreference = 'Continue'
        & $apache -t -f $httpdConf 2>&1 | Out-Null
        return ($LASTEXITCODE -eq 0)
    }
    finally {
        $ErrorActionPreference = $previousErrorActionPreference
    }
}

foreach ($path in @($apache, $httpdConf, $vhostsConf, $mysql, $mysqlIni)) {
    if (-not (Test-Path $path -PathType Leaf)) {
        throw "Required XAMPP file not found: $path"
    }
}

$vhosts = [System.IO.File]::ReadAllText($vhostsConf)
if ($vhosts -notlike "*$projectPublic*") {
    throw 'Apache is not configured for this checkout. Run .\configure-apache.ps1 once from an elevated PowerShell window.'
}

function Test-ListeningPort([int] $Port) {
    return [bool](Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction SilentlyContinue |
        Select-Object -First 1)
}

function Assert-ExpectedService([int] $Port, [string] $ExecutablePath, [string] $ServiceName) {
    $listeners = Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction SilentlyContinue |
        Select-Object -ExpandProperty OwningProcess -Unique
    foreach ($processId in $listeners) {
        $process = Get-CimInstance Win32_Process -Filter "ProcessId=$processId"
        if (-not $process -or $process.ExecutablePath -ine $ExecutablePath) {
            throw "Port $Port is occupied by a process other than the configured XAMPP $ServiceName."
        }
    }
}

function Wait-ForPort([int] $Port, [string] $ServiceName) {
    for ($attempt = 0; $attempt -lt 40; $attempt++) {
        if (Test-ListeningPort $Port) {
            return
        }
        Start-Sleep -Milliseconds 500
    }

    throw "$ServiceName did not start listening on port $Port."
}

$environment = @{}
if (-not (Test-Path $environmentPath -PathType Leaf)) {
    throw 'The local .env file is missing.'
}
Get-Content $environmentPath | ForEach-Object {
    if ($_ -match '^\s*([^#=]+)=(.*)$') {
        $environment[$matches[1].Trim()] = $matches[2].Trim().Trim('"').Trim("'")
    }
}
if ($environment['APP_ENV'] -ne 'local' -or $environment['APP_URL'] -ne 'http://localhost' -or
    $environment['CACHE_STORE'] -ne 'file') {
    throw 'Refusing to start local development: use APP_ENV=local, APP_URL=http://localhost, and CACHE_STORE=file in .env.'
}

if (-not (Test-ListeningPort 3306)) {
    Write-Host 'Starting XAMPP MySQL...'
    Start-Process -FilePath $mysql `
        -ArgumentList @("--defaults-file=$mysqlIni", '--standalone') `
        -WorkingDirectory (Split-Path $mysql -Parent) `
        -WindowStyle Hidden
    Wait-ForPort 3306 'MySQL'
}
Assert-ExpectedService 3306 $mysql 'MySQL'

if (-not (Test-ListeningPort 80)) {
    if (-not (Test-ApacheConfiguration)) {
        throw 'Apache configuration validation failed; Apache was not started.'
    }

    Write-Host 'Starting XAMPP Apache...'
    Start-Process -FilePath $apache `
        -ArgumentList @('-f', $httpdConf) `
        -WorkingDirectory (Split-Path $apache -Parent) `
        -WindowStyle Hidden
    Wait-ForPort 80 'Apache'
}
Assert-ExpectedService 80 $apache 'Apache'
if (-not (Test-ApacheConfiguration)) {
    throw 'Apache configuration validation failed.'
}
if (Test-ListeningPort 5173) {
    throw 'Port 5173 is already in use; stop its existing Vite server before running .\dev.ps1.'
}
try {
    $health = Invoke-WebRequest -Uri 'http://localhost/up' -UseBasicParsing -TimeoutSec 10
    if ($health.StatusCode -ne 200) {
        throw 'Laravel health check returned an unexpected status.'
    }
}
catch {
    throw "Apache is running, but Laravel at http://localhost/up is not healthy: $($_.Exception.Message)"
}

Push-Location $projectRoot
try {
    foreach ($command in @('config:clear', 'route:clear', 'view:clear')) {
        & php artisan $command --no-interaction
        if ($LASTEXITCODE -ne 0) {
            throw "Laravel cache cleanup failed: php artisan $command"
        }
    }

    & php artisan cache:clear --no-interaction
    if ($LASTEXITCODE -ne 0) {
        throw 'Laravel file cache cleanup failed: php artisan cache:clear'
    }

    & php artisan tinker --execute="DB::connection()->getPdo(); echo 'MySQL connection OK';"
    if ($LASTEXITCODE -ne 0) {
        throw 'Laravel could not connect to the configured MySQL database.'
    }

    Write-Host 'Apache: http://localhost'
    Write-Host 'Vite HMR: http://127.0.0.1:5173'
    Write-Host 'Press Ctrl+C to stop Vite.'
    try {
        npm run dev
        if ($LASTEXITCODE -ne 0) {
            throw 'Vite development server exited with an error.'
        }
    }
    finally {
        $hotFile = Join-Path $projectRoot 'public\hot'
        if ((Test-Path $hotFile -PathType Leaf) -and
            ([System.IO.File]::ReadAllText($hotFile).Trim() -eq 'http://127.0.0.1:5173')) {
            Remove-Item -LiteralPath $hotFile
        }
    }
}
finally {
    Pop-Location
}
