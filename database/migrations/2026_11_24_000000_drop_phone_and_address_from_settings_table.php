<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('settings', 'phone')) {
            Schema::table('settings', static function (Blueprint $table): void {
                $table->dropColumn('phone');
            });
        }

        if (Schema::hasColumn('settings', 'address')) {
            Schema::table('settings', static function (Blueprint $table): void {
                $table->dropColumn('address');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('settings', 'phone')) {
            Schema::table('settings', static function (Blueprint $table): void {
                $table->string('phone', 32)->nullable();
            });
        }

        if (! Schema::hasColumn('settings', 'address')) {
            Schema::table('settings', static function (Blueprint $table): void {
                $table->string('address', 300)->nullable();
            });
        }
    }
};
