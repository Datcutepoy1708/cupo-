@extends('layouts.client.app')

@section('page-title', $product->name . ' — Mua ngay giá tốt | Cupo')

@push('styles')
    <link href="{{ asset('client/css/product-show.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $sellerObj = $product->seller;
        $profile = $sellerObj->sellerProfile ?? null;
        $shopName = $profile->shop_name ?? ($sellerObj->name ?? 'Gian Hàng Chính Hãng');
        $avatarUrl =
            $profile?->logo ?: ($sellerObj->avatar ? asset('storage/' . ltrim($sellerObj->avatar, '/')) : null);
        $shopNameWords = preg_split('/\s+/', trim($shopName));
        $shopInitials = mb_strtoupper(
            mb_substr($shopNameWords[0] ?? 'G', 0, 1) .
                (count($shopNameWords) > 1 ? mb_substr(end($shopNameWords), 0, 1) : ''),
        );
        $shopProductCount = $profile?->products()->where('status', 'approved')->count() ?? 0;
        $shopFollowersCount = $profile?->followers()->count() ?? 0;
        $reviews = $product->reviews;
        $isLiked = (bool) session('liked_product_' . $product->id);

        $mainImg = $product->thumbnail_url ?? asset('images/product-placeholder.png');
        $galleryList = [];
        if ($mainImg) {
            $galleryList[] = [
                'url' => $mainImg,
                'title' => 'Ảnh đại diện',
                'type' => 'main',
            ];
        }
        foreach ($product->images as $img) {
            $subPath = Str::startsWith($img->image_path, ['http://', 'https://'])
                ? $img->image_path
                : asset('storage/' . ltrim($img->image_path, '/'));
            if (!in_array($subPath, array_column($galleryList, 'url'))) {
                $galleryList[] = [
                    'url' => $subPath,
                    'title' => 'Ảnh chi tiết',
                    'type' => 'gallery',
                ];
            }
        }
        if ($product->has_variants && $product->variants->isNotEmpty()) {
            foreach ($product->variants as $varItem) {
                if ($varItem->image_url && !in_array($varItem->image_url, array_column($galleryList, 'url'))) {
                    $galleryList[] = [
                        'url' => $varItem->image_url,
                        'title' => 'Phân loại: ' . $varItem->name,
                        'type' => 'variant',
                        'variant_name' => $varItem->name,
                    ];
                }
            }
        }

        $attrGroups = [];
        if ($product->has_variants && $product->variants->isNotEmpty()) {
            if (
                is_array($product->attributes) &&
                !empty($product->attributes) &&
                isset($product->attributes[0]['name'])
            ) {
                $attrGroups = $product->attributes;
            } else {
                $firstVarName = $product->variants->first()->name;
                if (str_contains($firstVarName, ',')) {
                    $g1Opts = [];
                    $g2Opts = [];
                    foreach ($product->variants as $v) {
                        $parts = array_map('trim', explode(',', $v->name));
                        if (isset($parts[0]) && !in_array($parts[0], $g1Opts)) {
                            $g1Opts[] = $parts[0];
                        }
                        if (isset($parts[1]) && !in_array($parts[1], $g2Opts)) {
                            $g2Opts[] = $parts[1];
                        }
                    }
                    $attrGroups = [
                        ['name' => 'Màu sắc', 'options' => $g1Opts],
                        ['name' => 'Kích cỡ', 'options' => $g2Opts],
                    ];
                } else {
                    $attrGroups = [
                        [
                            'name' => 'Phân loại',
                            'options' => $product->variants->pluck('name')->unique()->values()->all(),
                        ],
                    ];
                }
            }
        }
    @endphp

    <div class="prod-detail-bg py-4" id="productDetailContainer" data-is-guest="{{ auth()->guest() ? 'true' : 'false' }}"
        data-cart-url="{{ route('cart.store') }}" data-cart-index-url="{{ route('cart.index') }}"
        data-checkout-url="{{ route('checkout.store') }}" data-orders-url="{{ route('customer.orders.index') }}">
        <div class="container">
            @include('client.products.partials.breadcrumb', [
                'product' => $product,
            ])
            <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden p-3 p-lg-4" style="background:#fff;">
                <div class="row g-4">

                    @include('client.products.partials.card-info', [
                        'product' => $product,
                        'mainImg' => $mainImg,
                        'galleryList' => $galleryList,
                        'attrGroups' => $attrGroups,
                        'likesCount' => $likesCount,
                        'avgRating' => $avgRating,
                        'totalReviews' => $totalReviews,
                        'soldCount' => $soldCount,
                        'isLiked' => $isLiked,
                    ])
                    @include('client.products.partials.shop-info', [
                        'sellerObj' => $sellerObj,
                        'profile' => $profile,
                        'shopName' => $shopName,
                        'avatarUrl' => $avatarUrl,
                        'shopInitials' => $shopInitials,
                        'totalReviews' => $totalReviews,
                        'shopProductCount' => $shopProductCount,
                        'shopFollowersCount' => $shopFollowersCount,
                    ])
                    @include('client.products.partials.product-detail', [
                        'product' => $product,
                        'profile' => $profile,
                        'shopName' => $shopName,
                    ])
                    @include('client.products.partials.product-reviews', [
                        'reviews' => $reviews,
                        'totalReviews' => $totalReviews,
                        'avgRating' => $avgRating,
                    ])
                    @include('client.products.partials.product-suggestion', [
                        'relatedProducts' => $relatedProducts,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
