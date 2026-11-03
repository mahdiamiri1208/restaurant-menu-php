@extends('layouts.app')

@section('title', 'منوی '.$restaurantName)

@section('content')
<main class="menu-shell" data-menu>
    <div class="ambient ambient-orange"></div><div class="ambient ambient-teal"></div><div class="ambient ambient-red"></div>
    <div class="menu-content">
        <header class="menu-header">
            <div class="restaurant-mark">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="" width="48" height="48">
                @else
                    @include('partials.menu-icon', ['name' => 'plate', 'size' => 24])
                @endif
            </div>
            <div class="restaurant-copy"><p class="eyebrow">طعم خوب، حال خوب</p><h1>{{ $restaurantName }}</h1></div>
            <button class="icon-button search-toggle" type="button" aria-label="جستجو در منو" aria-expanded="false" aria-controls="menu-search" data-search-toggle>
                @include('partials.menu-icon', ['name' => 'search', 'size' => 20])
            </button>
        </header>
        <div class="search-field" id="menu-search" aria-hidden="true" data-search-container>
            @include('partials.menu-icon', ['name' => 'search', 'size' => 18])
            <input type="search" placeholder="جستجو در منو..." aria-label="جستجو در منو" tabindex="-1" data-search-input>
        </div>
        <nav class="category-rail" aria-label="دسته‌بندی منو">
            <button type="button" class="category-chip active" aria-pressed="true" data-category-chip="all"><span class="category-symbol" aria-hidden="true">✦</span>همه</button>
            @foreach($categories as $category)
                <button type="button" class="category-chip" aria-pressed="false" data-category-chip="{{ $category['id'] }}"><span class="category-symbol" aria-hidden="true">{{ $category['symbol'] }}</span>{{ $category['name'] }}</button>
            @endforeach
        </nav>
        <section class="menu-intro" data-category="all"><p>منوی امروز</p><span>با عشق آماده شده برای شما</span></section>
        <div class="menu-sections">
            @foreach($categories as $category)
                @php($items = $products->where('category_id', $category['id'])->values())
                <section class="menu-section" id="section-{{ $category['id'] }}" data-category="{{ $category['id'] }}">
                    <div class="section-heading"><span class="section-icon" aria-hidden="true">{{ $category['symbol'] }}</span><h2>{{ $category['name'] }}</h2><span class="section-count" data-section-count>{{ \App\Support\MenuFormatter::toman($items->count()) }} مورد</span></div>
                    <div class="product-list">
                        @foreach($items as $product)
                            <article class="product-card{{ $product['available'] ? '' : ' product-unavailable' }}" data-product data-name="{{ mb_strtolower($product['name'], 'UTF-8') }}">
                                <div class="product-art art-{{ $product['color'] }}" aria-hidden="true">
                                    @if($product['image_url'])
                                        <img src="{{ $product['image_url'] }}" alt="" width="112" height="112" loading="lazy">
                                    @else
                                        <span class="product-placeholder-icon">{{ $product['category_symbol'] }}</span>
                                    @endif
                                    <span class="art-glint"></span>
                                </div>
                                <div class="product-info">
                                    <div class="product-title-line"><h3>{{ $product['name'] }}</h3>@if($product['discount_percent'])<span class="discount-badge">٪{{ \App\Support\MenuFormatter::toman($product['discount_percent']) }}</span>@endif</div>
                                    <p class="product-description">{{ $product['description'] }}</p>
                                    <div class="product-bottom">
                                        <div class="product-tags">
                                            @if($product['spicy'])<span class="food-tag spicy">@include('partials.menu-icon', ['name' => 'flame', 'size' => 14]) تند</span>@endif
                                            @if($product['vegetarian'])<span class="food-tag vegetarian">@include('partials.menu-icon', ['name' => 'leaf', 'size' => 14]) گیاهی</span>@endif
                                            @unless($product['available'])<span class="unavailable-label">ناموجود</span>@endunless
                                        </div>
                                        <div class="product-price">
                                            @if($product['discount_percent'])<span class="old-price">{{ \App\Support\MenuFormatter::toman($product['price']) }}</span>@endif
                                            <strong>{{ \App\Support\MenuFormatter::toman($product['final_price']) }} <small>تومان</small></strong>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
            <p class="empty-results" hidden data-empty-results>موردی با این نام پیدا نشد.</p>
        </div>
        <footer class="menu-footer">مواد اولیه تازه، هر روز در نارنج</footer>
    </div>
    <aside class="waiter-bar"><span class="bell-disc">@include('partials.menu-icon', ['name' => 'bell', 'size' => 19])</span><span>سفارش خود را به گارسون اعلام کنید</span><span class="waiter-dot"></span></aside>
</main>
@endsection
