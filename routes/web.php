<?php

use App\Http\Controllers\MenuController;
use App\Http\Middleware\CachePublicMenu;
use Illuminate\Support\Facades\Route;

Route::get('/', MenuController::class)
    ->middleware(CachePublicMenu::class)
    ->name('menu');
