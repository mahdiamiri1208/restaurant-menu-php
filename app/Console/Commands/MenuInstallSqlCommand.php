<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use RuntimeException;

class MenuInstallSqlCommand extends Command
{
    private const TABLES = [
        'migrations',
        'users',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'categories',
        'products',
        'settings',
    ];

    protected $signature = 'menu:install-sql {--force : Overwrite the generated SQL file}';

    protected $description = 'Export the current MySQL schema and menu data for phpMyAdmin';

    public function handle(): int
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->error('The install SQL can only be generated from a MySQL or MariaDB database.');

            return self::FAILURE;
        }

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("The {$table} table is missing. Run migrations and seed the database first.");

                return self::FAILURE;
            }
        }

        $settings = DB::table('settings')->where('id', 1)->first();
        $admin = DB::table('users')->where('username', config('menu.admin_username'))->first();

        if (! $settings || ! $settings->samples_seeded || ! $admin) {
            $this->error('A seeded settings row and admin user are required. Run php artisan db:seed first.');

            return self::FAILURE;
        }

        $outputPath = database_path('install.sql');

        if (is_file($outputPath) && ! $this->option('force')) {
            $this->error('database/install.sql already exists. Use --force to replace it.');

            return self::FAILURE;
        }

        $pdo = DB::connection()->getPdo();
        $lines = [
            '-- Generated from the current Laravel MySQL/MariaDB schema and seeded data.',
            '-- This file contains the admin password hash. Do not commit or publish it.',
            'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'SET FOREIGN_KEY_CHECKS = 0;',
            '',
        ];

        foreach (array_reverse(self::TABLES) as $table) {
            $lines[] = "DROP TABLE IF EXISTS `{$table}`;";
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        $lines[] = '';

        foreach (self::TABLES as $table) {
            $createTable = $this->createTableStatement($pdo, $table);
            $lines[] = $createTable.';';
            $lines[] = '';
        }

        foreach (['migrations', 'users', 'categories', 'products', 'settings'] as $table) {
            foreach ($this->selectRows($pdo, $table) as $row) {
                $columns = array_map(
                    static fn (string $column): string => '`'.str_replace('`', '``', $column).'`',
                    array_keys($row),
                );
                $values = array_map(fn (mixed $value): string => $this->formatValue($value), array_values($row));

                $lines[] = "INSERT INTO `{$table}` (".implode(', ', $columns).') VALUES ('.implode(', ', $values).');';
            }

            $lines[] = '';
        }

        $sql = implode(PHP_EOL, $lines).PHP_EOL;

        if (file_put_contents($outputPath, $sql) === false) {
            throw new RuntimeException('The install SQL file could not be written.');
        }

        $this->info('Generated database/install.sql. It contains an admin password hash and is excluded from Git.');

        return self::SUCCESS;
    }

    private function createTableStatement(PDO $pdo, string $table): string
    {
        $statement = $pdo->query("SHOW CREATE TABLE `{$table}`");
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $createTable = is_array($row) ? (array_values($row)[1] ?? null) : null;

        if (! is_string($createTable) || $createTable === '') {
            throw new RuntimeException("The CREATE TABLE statement for {$table} could not be read.");
        }

        return $createTable;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function selectRows(PDO $pdo, string $table): array
    {
        $statement = $pdo->query("SELECT * FROM `{$table}`");

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (! is_string($value)) {
            throw new RuntimeException('The install SQL exporter encountered an unsupported database value.');
        }

        return "CONVERT(X'".bin2hex($value)."' USING utf8mb4)";
    }
}
