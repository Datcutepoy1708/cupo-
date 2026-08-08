<ul class="nav shop-nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="pill" href="#dashOverview" role="tab">Tổng quan</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="pill" href="#dashProducts" role="tab">Sản phẩm</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="pill" href="#dashOrders" role="tab">
            Đơn hàng
            @if ($shop->pending_orders > 0)
                <span class="tab-badge">{{ $shop->pending_orders }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="pill" href="#dashReviews" role="tab">Đánh giá</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="pill" href="#dashInfo" role="tab">Thông tin</a>
    </li>
</ul>
