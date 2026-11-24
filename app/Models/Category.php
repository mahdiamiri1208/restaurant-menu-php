<?php

namespace App\Models;

use App\Support\MenuImageProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'icon', 'sort_order', 'is_active'];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(static function (self $category): void {
            if (! $category->getKey()) {
                $category->setAttribute($category->getKeyName(), (string) Str::ulid());
            }
        });
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function deleteWithProductImages(): bool
    {
        return DB::transaction(function (): bool {
            static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $imagePaths = $this->products()
                ->whereNotNull('image_url')
                ->lockForUpdate()
                ->pluck('image_url')
                ->all();

            if (! $this->delete()) {
                return false;
            }

            DB::afterCommit(static function () use ($imagePaths): void {
                $processor = app(MenuImageProcessor::class);

                foreach ($imagePaths as $imagePath) {
                    $processor->delete($imagePath);
                }
            });

            return true;
        });
    }
}
