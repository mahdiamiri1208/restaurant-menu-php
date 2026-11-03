<?php

namespace App\Observers;

use App\Caching\PublicMenuCache;
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
    }
}
