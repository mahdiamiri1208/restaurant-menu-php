<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Settings;

return new class extends Migration
{
    public function up(): void
    {
        Settings::query()
            ->where('restaurant_name', 'رستوران نارنج')
            ->get()
            ->each(static function (Settings $settings): void {
                $settings->restaurant_name = 'پاریس لانژ';
                $settings->save();
            });
    }

    public function down(): void
    {
    }
};
