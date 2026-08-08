@extends('layouts.client.app')

@section('content')
    @php
        $shop = [
            'name' => 'Cupo Store - Điện tử chính hãng',
            'avatar' => 'https://picsum.photos/200/200',
            'banner' => 'https://picsum.photos/1600/400',
            'product_count' => 128,
            'rating' => 4.8,
            'review_count' => 356,
            'followers' => 2450,
            'response_rate' => 96,
            'joined' => '2 năm trước',
        ];
    @endphp

    <div class="shop-page">

        {{-- ===== BANNER + AVATAR + THÔNG TIN SHOP ===== --}}
        <div class="shop-banner" style="background-image: url('{{ $shop['banner'] }}');"></div>

        <div class="container">
            <div class="shop-header">
                <div class="shop-avatar-wrap">
                    <img src="{{ $shop['avatar'] }}" alt="{{ $shop['name'] }}" class="shop-avatar">
                </div>

                <div class="shop-info">
                    <div class="shop-info-top">
                        <div>
                            <h1 class="shop-name">{{ $shop['name'] }}</h1>
                            <div class="shop-stats">
                                <span><i class="fa-solid fa-box"></i> {{ $shop['product_count'] }} sản phẩm</span>
                                <span class="divider">|</span>
                                <span class="stars">
                                    <i class="fa-solid fa-star"></i> {{ number_format($shop['rating'], 1) }}
                                    <span class="text-muted">({{ $shop['review_count'] }} đánh giá)</span>
                                </span>
                                <span class="divider">|</span>
                                <span><i class="fa-solid fa-users"></i> {{ number_format($shop['followers']) }} người theo
                                    dõi</span>
                            </div>
                        </div>

                        <div class="shop-actions">
                            <button type="button" class="btn btn-outline-danger">
                                <i class="fa-regular fa-heart me-1"></i> Theo dõi
                            </button>
                            <button type="button" class="btn btn-save">
                                <i class="fa-solid fa-comment-dots me-1"></i> Chat ngay
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== THANH NAV TAB ===== --}}
            <ul class="nav shop-nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="pill" href="#shopAllProducts" role="tab">Tất cả sản
                        phẩm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#shopBestSellers" role="tab">Bán chạy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#shopReviews" role="tab">Đánh giá
                        ({{ $shop['review_count'] }})</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#shopInfo" role="tab">Thông tin shop</a>
                </li>
            </ul>

            {{-- ===== DASHBOARD NỘI DUNG ===== --}}
            <div class="tab-content shop-dashboard">

                {{-- TAB: TẤT CẢ SẢN PHẨM --}}
                <div class="tab-pane fade show active" id="shopAllProducts" role="tabpanel">
                    <div class="row g-3">
                        @for ($i = 0; $i < 8; $i++)
                            <div class="col-6 col-md-3">
                                <div class="card h-100 border-0 shadow-sm shop-product-card">
                                    <img src="https://picsum.photos/seed/{{ $i }}/300/300" class="card-img-top"
                                        alt="Sản phẩm">
                                    <div class="card-body">
                                        <p class="small mb-1 text-truncate">Sản phẩm {{ $i + 1 }} của shop</p>
                                        <p class="fw-bold mb-0" style="color: var(--primary-red, #c62828);">
                                            {{ number_format(199000 + $i * 50000) }}₫
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item"><button class="page-link">&laquo;</button></li>
                            <li class="page-item"><span class="page-link">Trang 1 / 16</span></li>
                            <li class="page-item"><button class="page-link">&raquo;</button></li>
                        </ul>
                    </nav>
                </div>

                {{-- TAB: BÁN CHẠY --}}
                <div class="tab-pane fade" id="shopBestSellers" role="tabpanel">
                    <div class="row g-3">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="col-6 col-md-3">
                                <div class="card h-100 border-0 shadow-sm shop-product-card">
                                    <span class="badge bestseller-badge">Bán chạy</span>
                                    <img src="https://picsum.photos/seed/best{{ $i }}/300/300"
                                        class="card-img-top" alt="Sản phẩm">
                                    <div class="card-body">
                                        <p class="small mb-1 text-truncate">Sản phẩm bán chạy {{ $i + 1 }}</p>
                                        <p class="fw-bold mb-0" style="color: var(--primary-red, #c62828);">
                                            {{ number_format(250000 + $i * 70000) }}₫
                                        </p>
                                        <p class="text-muted small mb-0">Đã bán {{ 500 - $i * 80 }}</p>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- TAB: ĐÁNH GIÁ --}}
                <div class="tab-pane fade" id="shopReviews" role="tabpanel">
                    <div class="shop-rating-summary">
                        <div class="rating-score">
                            <div class="score-number">{{ number_format($shop['rating'], 1) }}</div>
                            <div class="score-stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fa-solid fa-star {{ $i <= round($shop['rating']) ? '' : 'text-muted opacity-25' }}"></i>
                                @endfor
                            </div>
                            <p class="text-muted small mb-0">{{ $shop['review_count'] }} đánh giá</p>
                        </div>
                    </div>

                    @php
                        $demoReviews = [
                            [
                                'name' => 'Trần Thị B',
                                'rating' => 5,
                                'date' => '2 ngày trước',
                                'content' => 'Sản phẩm đúng như mô tả, giao hàng nhanh, đóng gói cẩn thận.',
                            ],
                            [
                                'name' => 'Lê Văn C',
                                'rating' => 4,
                                'date' => '1 tuần trước',
                                'content' => 'Chất lượng tốt, shop tư vấn nhiệt tình. Sẽ ủng hộ tiếp.',
                            ],
                            [
                                'name' => 'Phạm Thị D',
                                'rating' => 5,
                                'date' => '2 tuần trước',
                                'content' => 'Rất hài lòng, đúng hẹn giao hàng.',
                            ],
                        ];
                    @endphp

                    @foreach ($demoReviews as $review)
                        <div class="review-row">
                            <div class="review-row-top">
                                <span class="fw-bold">{{ $review['name'] }}</span>
                                <span class="text-muted small">{{ $review['date'] }}</span>
                            </div>
                            <div class="review-stars mb-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fa-solid fa-star {{ $i <= $review['rating'] ? '' : 'text-muted opacity-25' }}"></i>
                                @endfor
                            </div>
                            <p class="mb-0 text-muted">{{ $review['content'] }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- TAB: THÔNG TIN SHOP --}}
                <div class="tab-pane fade" id="shopInfo" role="tabpanel">
                    <table class="table table-borderless shop-info-table">
                        <tr>
                            <td class="text-muted" style="width: 200px;">Tên shop:</td>
                            <td class="fw-bold">{{ $shop['name'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tham gia:</td>
                            <td>{{ $shop['joined'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tỉ lệ phản hồi:</td>
                            <td>{{ $shop['response_rate'] }}%</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Người theo dõi:</td>
                            <td>{{ number_format($shop['followers']) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Đánh giá:</td>
                            <td>{{ number_format($shop['rating'], 1) }}/5 ({{ $shop['review_count'] }} đánh giá)</td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection
