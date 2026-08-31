@extends('layouts.client.app')

@section('page-title', $shop->shop_name . ' - Cửa hàng chính hãng | Cupo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('client/css/shop-show.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/vouchers.css') }}">
@endpush

@section('content')
    @php
        $shopLogo = $shop->logo
            ? (\Illuminate\Support\Str::startsWith($shop->logo, ['http://', 'https://'])
                ? $shop->logo
                : asset('storage/' . ltrim($shop->logo, '/')))
            : 'https://ui-avatars.com/api/?name=' .
                urlencode($shop->shop_name) .
                '&background=c62828&color=fff&size=220&bold=true';
        $shopBanner = $shop->banner ?? null;
        $shopBanner = $shopBanner
            ? (\Illuminate\Support\Str::startsWith($shopBanner, ['http://', 'https://'])
                ? $shopBanner
                : asset('storage/' . ltrim($shopBanner, '/')))
            : 'https://picsum.photos/1600/400?grayscale&blur=1';
    @endphp

    <div class="shop-page">
        <div class="container">
            <div class="shop-banner" style="background-image: url('{{ $shopBanner }}');"></div>
            <div class="shop-header">
                <div class="shop-avatar-wrap">
                    @if ($shop->logo)
                        <img src="{{ $shopLogo }}" alt="{{ $shop->shop_name }}" class="shop-avatar">
                    @else
                        @php
                            $words = preg_split('/\s+/', trim($shop->shop_name));
                            $initials = mb_strtoupper(
                                mb_substr($words[0], 0, 1) . (count($words) > 1 ? mb_substr(end($words), 0, 1) : ''),
                            );
                        @endphp
                        <div class="shop-avatar shop-avatar-fallback">{{ $initials }}</div>
                    @endif
                </div>
                <div class="shop-info">
                    <div class="shop-info-top">
                        <div>
                            <h1 class="shop-name">{{ $shop->shop_name }}</h1>
                            <div class="shop-stats">
                                <span><i class="fa-solid fa-box"></i> {{ number_format($totalProducts) }} sản
                                    phẩm</span><span class="divider">|</span>
                                <span class="stars"><i class="fa-solid fa-star"></i> 4.9 <span class="text-muted">(99% tích
                                        cực)</span></span><span class="divider">|</span>
                                <span id="shopFollowersCount"><i class="fa-solid fa-users"></i>
                                    {{ number_format($followersCount) }} người theo dõi</span>
                            </div>
                        </div>
                        <div class="shop-actions">
                            <button type="button" class="btn btn-outline-danger" id="btnFollowShop"
                                data-shop-id="{{ $shop->id }}"
                                data-url="{{ route('shops.follow.toggle', $shop->id) }}">
                                <i
                                    class="fa-regular {{ $isFollowed ? 'fa-circle-check' : 'fa-heart' }} me-1"></i>{{ $isFollowed ? 'Đang theo dõi' : 'Theo dõi' }}
                            </button>
                            @if (Route::has('chat.room'))
                                <a href="{{ route('chat.room', ['seller_id' => $shop->user_id]) }}" class="btn btn-save"><i
                                        class="fa-solid fa-comment-dots me-1"></i>Chat ngay</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav shop-nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#shopAllProducts"
                        role="tab">Tất cả sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#shopBestSellers" role="tab">Bán
                        chạy</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#shopReviews" role="tab">Đánh
                        giá</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#shopInfo" role="tab">Thông tin
                        shop</a></li>
            </ul>

            <div class="tab-content shop-dashboard">
                <div class="tab-pane fade show active" id="shopAllProducts" role="tabpanel">
                    @if (isset($shopCoupons) && $shopCoupons->count() > 0)
                        <div class="shop-vouchers-section mb-4">
                            <h2 class="h6 fw-bold mb-3"><i class="fa-solid fa-ticket text-danger me-2"></i>Mã giảm giá của
                                shop</h2>
                            <div class="row g-3">
                                @foreach ($shopCoupons as $voucher)
                                    @php $isClaimed = in_array($voucher->id, $savedCouponIds ?? []); @endphp
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <div
                                            class="voucher-ticket {{ $voucher->isExpired() || !$voucher->isAvailable() ? 'opacity-75' : '' }}">
                                            <div class="voucher-left"><i
                                                    class="fa-solid fa-store voucher-left-icon"></i><span
                                                    class="voucher-left-tag">SHOP</span></div>
                                            <div class="voucher-body">
                                                <div class="voucher-title">
                                                    {{ in_array($voucher->type, ['percent', 'percentage']) ? 'Giảm ' . (float) $voucher->value . '%' : 'Giảm ' . number_format($voucher->value, 0, ',', '.') . 'đ' }}
                                                </div>
                                                <div class="voucher-desc">Đơn tối thiểu
                                                    {{ number_format($voucher->min_order_amount ?? 0, 0, ',', '.') }}đ
                                                </div>
                                                <div class="voucher-footer"><span class="voucher-exp">HSD:
                                                        {{ $voucher->end_date ? \Carbon\Carbon::parse($voucher->end_date)->format('d/m/Y') : 'Không giới hạn' }}</span>
                                                    <button type="button"
                                                        class="btn btn-sm btn-claim-voucher {{ $isClaimed ? 'btn-claimed' : 'btn-danger' }}"
                                                        data-voucher-id="{{ $voucher->id }}"
                                                        data-url="{{ route('customer.vouchers.save', $voucher->id) }}"
                                                        {{ $isClaimed || $voucher->isExpired() || !$voucher->isAvailable() ? 'disabled' : '' }}>{{ $isClaimed ? 'Đã lưu' : 'Lưu' }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <form action="{{ route('shops.show', $shop->id) }}" method="GET" class="shop-search-form mb-4">
                        <div class="input-group"><input type="text" name="q" class="form-control"
                                placeholder="Tìm sản phẩm trong shop này..." value="{{ $searchQuery }}"><input
                                type="hidden" name="sort" value="{{ $sort }}"><button class="btn btn-danger"
                                type="submit"><i class="fa-solid fa-magnifying-glass"></i></button></div>
                    </form>
                    <div class="row g-3">
                        @forelse($products as $prod)
                        @include('client.shops.partials.product-card', ['prod' => $prod]) @empty
                            <div class="col-12 text-center py-5 text-muted"><i class="fa-solid fa-box-open fs-1 mb-3"></i>
                                <p>Không tìm thấy sản phẩm nào.</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $products->links() }}</div>
                </div>
                <div class="tab-pane fade" id="shopBestSellers" role="tabpanel">
                    <div class="row g-3">
                        @forelse($topProducts as $prod)
                        @include('client.shops.partials.product-card', [
                            'prod' => $prod,
                            'bestSeller' => true,
                        ]) @empty
                            <p class="text-center text-muted py-5">Shop chưa có sản phẩm bán chạy.</p>
                        @endforelse
                    </div>
                </div>
                <div class="tab-pane fade" id="shopReviews" role="tabpanel">
                    <div class="shop-rating-summary">
                        <div class="score-number">4.9</div>
                        <div class="score-stars">★★★★★</div>
                        <p class="mb-0 text-muted">Đánh giá tích cực từ khách hàng</p>
                    </div>
                </div>
                <div class="tab-pane fade" id="shopInfo" role="tabpanel">
                    <h2 class="h5 mb-3">Thông tin shop</h2>
                    <table class="table shop-info-table mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Tên shop</td>
                                <td>{{ $shop->shop_name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tham gia</td>
                                <td>{{ $shop->created_at ? $shop->created_at->format('d/m/Y') : 'Đang cập nhật' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Sản phẩm</td>
                                <td>{{ number_format($totalProducts) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('client/js/shop-show.js') }}"></script>
    <script src="{{ asset('client/js/vouchers.js') }}"></script>
@endpush
