<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MenuRequirementsCommand extends Command
{
    protected $signature = 'menu:requirements';

    protected $description = 'Check PHP extensions required by the restaurant menu';

    public function handle(): int
    {
        $requirements = [
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mbstring' => extension_loaded('mbstring'),
            'gd (WebP support)' => extension_loaded('gd')
                && function_exists('gd_info')
                && (gd_info()['WebP Support'] ?? false),
            'fileinfo' => extension_loaded('fileinfo'),
            'intl' => extension_loaded('intl'),
            'openssl' => extension_loaded('openssl'),
            'ctype' => extension_loaded('ctype'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml' => extension_loaded('xml'),
        ];

        $missing = [];

        foreach ($requirements as $requirement => $available) {
            if ($available) {
                $this->line("OK: {$requirement}");

                continue;
            }

            $this->error("MISSING: {$requirement}");
            $missing[] = $requirement;
        }

        return $missing === [] ? self::SUCCESS : self::FAILURE;
    }
}
