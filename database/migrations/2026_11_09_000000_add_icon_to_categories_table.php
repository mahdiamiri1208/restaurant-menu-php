<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('categories', 'icon')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('icon', 50)->default('utensils');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('categories', 'icon')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('icon');
        });
    }
};
