<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $username = trim((string) env('ADMIN_USERNAME'));
        $password = (string) env('ADMIN_PASSWORD');

        if ($username === '' || strlen($username) > 64 || strlen($password) < 12 || strlen($password) > 128) {
            throw new RuntimeException('Set ADMIN_USERNAME and an ADMIN_PASSWORD between 12 and 128 characters before seeding.');
        }

        $admin = User::query()->firstOrNew();
        $admin->forceFill([
            'name' => $username,
            'username' => $username,
            'email' => 'admin@restaurant-menu.invalid',
            'password' => $password,
        ])->save();

        $settings = Settings::query()->firstOrCreate(
            ['id' => 1],
            [
                'restaurant_name' => 'پاریس لانژ',
                'phone' => '021-00000000',
                'address' => 'تهران',
            ],
        );

        $samples = [
            [
                'name' => 'پیش‌غذا',
                'products' => [
                    ['name' => 'سالاد سزار', 'description' => 'کاهوی تازه، مرغ گریل و سس مخصوص', 'price' => 285000, 'is_vegetarian' => false],
                    ['name' => 'سوپ روز', 'description' => 'سوپ گرم با سبزیجات فصل', 'price' => 165000, 'is_vegetarian' => true],
                ],
            ],
            [
                'name' => 'غذای اصلی',
                'products' => [
                    ['name' => 'چلو کباب لقمه', 'description' => 'کباب دست‌ساز، برنج ایرانی و گوجه کبابی', 'price' => 495000, 'is_spicy' => true],
                    ['name' => 'پاستا آلفردو', 'description' => 'مرغ گریل، قارچ و سس خامه‌ای', 'price' => 385000, 'discount_percent' => 15],
                ],
            ],
            [
                'name' => 'نوشیدنی',
                'products' => [
                    ['name' => 'لیموناد نعنا', 'description' => 'آب‌لیموی تازه، نعنا و یخ', 'price' => 115000, 'is_vegetarian' => true],
                ],
            ],
        ];

        if (! $settings->samples_seeded) {
            foreach ($samples as $categoryIndex => $sample) {
                $categoryId = 'sample-category-'.($categoryIndex + 1);
                $category = Category::query()->firstOrCreate(
                    ['id' => $categoryId],
                    ['name' => $sample['name'], 'sort_order' => $categoryIndex],
                );

                foreach ($sample['products'] as $productIndex => $attributes) {
                    Product::query()->firstOrCreate(
                        ['id' => 'sample-product-'.($categoryIndex + 1).'-'.($productIndex + 1)],
                        [
                            ...$attributes,
                            'category_id' => $category->id,
                            'sort_order' => $productIndex,
                        ],
                    );
                }
            }

            $settings->forceFill(['samples_seeded' => true])->save();
        }
    }
}
