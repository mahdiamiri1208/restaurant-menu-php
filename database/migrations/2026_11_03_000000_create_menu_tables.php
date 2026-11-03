<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->string('id', 191)->primary();
            $table->string('name', 255);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->string('id', 191)->primary();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_spicy')->default(false);
            $table->boolean('is_vegetarian')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('category_id', 191);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->index(['category_id', 'sort_order']);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->integer('id')->primary()->default(1);
            $table->string('restaurant_name', 255)->default('رستوران نارنج');
            $table->string('logo_url', 255)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('address', 300)->nullable();
            $table->boolean('samples_seeded')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('categories');
    }
};
