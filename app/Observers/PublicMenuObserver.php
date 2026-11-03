<?php

namespace App\Observers;

use App\Caching\PublicMenuCache;
use App\Models\Product;
use App\Support\MenuImageProcessor;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class PublicMenuObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        app(PublicMenuCache::class)->invalidate();
    }

    public function deleted(Model $model): void
    {
        app(PublicMenuCache::class)->invalidate();

        if ($model instanceof Product && filled($model->image_url)) {
            app(MenuImageProcessor::class)->delete($model->image_url);
        }
    }
}
