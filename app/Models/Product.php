<?php

namespace App\Models;

use App\Support\MenuPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'discount_percent',
        'image_url',
        'is_available',
        'is_spicy',
        'is_vegetarian',
        'sort_order',
        'category_id',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(static function (self $product): void {
            if (! $product->getKey()) {
                $product->setAttribute($product->getKeyName(), (string) Str::ulid());
            }
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'discount_percent' => 'integer',
            'is_available' => 'boolean',
            'is_spicy' => 'boolean',
            'is_vegetarian' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function finalPrice(): int
    {
        return MenuPrice::final($this->price, $this->discount_percent);
    }
}
