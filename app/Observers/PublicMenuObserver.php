<?php

namespace App\Observers;

use App\Caching\PublicMenuCache;
use App\Models\Product;
use App\Models\Settings;
use App\Support\MenuImageProcessor;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class PublicMenuObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        app(PublicMenuCache::class)->invalidate();

        $attribute = match (true) {
            $model instanceof Product => 'image_url',
            $model instanceof Settings => 'logo_url',
            default => null,
        };

        if ($attribute === null || ! $model->wasChanged($attribute)) {
            return;
        }

        $previousPath = $model->getPrevious()[$attribute] ?? null;

        if (
            ! is_string($previousPath)
            || blank($previousPath)
            || $previousPath === $model->getAttribute($attribute)
        ) {
            return;
        }

        app(MenuImageProcessor::class)->delete($previousPath);
    }

    public function deleted(Model $model): void
    {
        app(PublicMenuCache::class)->invalidate();

        if ($model instanceof Product && filled($model->image_url)) {
            app(MenuImageProcessor::class)->delete($model->image_url);
        }
    }
}
