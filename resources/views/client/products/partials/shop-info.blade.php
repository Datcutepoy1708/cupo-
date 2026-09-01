<div class="card border-0 shadow-sm rounded-3 mb-4 p-3 p-lg-4" style="background:#fff;">
    <div class="row align-items-center g-3">
        {{-- Cột trái Shop info --}}
        <div class="col-lg-4 col-md-5 border-end-md">
            <div class="d-flex align-items-center gap-3">
                <div class="shop-avatar-wrap position-relative">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $shopName }}" class="rounded-circle border"
                            style="width: 72px; height: 72px; object-fit: cover;">
                    @else
                        <div class="product-shop-avatar-fallback" aria-label="{{ $shopName }}">
                            {{ $shopInitials }}</div>
                    @endif
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ $shopName }}</h5>
                    <p class="text-muted small mb-2"><i class="fa-solid fa-circle text-success me-1"
                            style="font-size: 8px;"></i>Online 8 phút trước</p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-danger px-3 py-1"><i
                                class="fa-solid fa-comments me-1"></i>Chat Ngay</a>
                        <a href="{{ route('shops.show', $profile->id ?? ($sellerObj->id ?? 1)) }}"
                            class="btn btn-sm btn-light border px-3 py-1 text-dark"><i
                                class="fa-solid fa-store me-1"></i>Xem Shop</a>
                    </div>

                </div>
            </div>
        </div>

        {{-- Cột phải Shop Stats Grid --}}
        <div class="col-lg-8 col-md-7">
            <div class="row text-center g-3 shop-stats-grid">
                <div class="col-4 col-md-2">
                    <span class="d-block text-muted small">Đánh Giá</span>
                    <strong class="text-danger">{{ number_format($totalReviews) }}</strong>
                </div>
                <div class="col-4 col-md-2">
                    <span class="d-block text-muted small">Sản Phẩm</span>
                    <strong class="text-danger">{{ number_format($shopProductCount) }}</strong>
                </div>
                <div class="col-4 col-md-2">
                    <span class="d-block text-muted small">Tỉ Lệ Phản Hồi</span>
                    <strong class="text-danger">90%</strong>
                </div>
                <div class="col-4 col-md-3">
                    <span class="d-block text-muted small">Thời Gian Phản Hồi</span>
                    <strong class="text-danger">trong vài giờ</strong>
                </div>
                <div class="col-4 col-md-3">
                    <span class="d-block text-muted small">Người Theo Dõi</span>
                    <strong class="text-danger">{{ number_format($shopFollowersCount) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
