<div class="card border-0 shadow-sm rounded-3 mb-4 p-3 p-lg-4" style="background:#fff;" id="reviews-section">
    <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3">
        ĐÁNH GIÁ SẢN PHẨM (5 SAO)
    </h2>

    {{-- Rating Summary Bar --}}
    <div class="row align-items-center p-3 mb-4 rounded-3" style="background: #fffbf8; border: 1px solid #fbe3d5;">
        <div class="col-md-3 text-center border-end-md mb-3 mb-md-0">
            <div class="display-5 fw-bold text-danger">{{ $avgRating }} <span class="fs-5 text-muted">trên
                    5</span></div>
            <div class="text-warning my-1">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="fa-solid fa-star"></i>
                @endfor
            </div>
            <span class="text-muted small">Dựa trên {{ number_format($totalReviews) }} nhận xét</span>
        </div>
        <div class="col-md-9">
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-sm btn-outline-danger active">Tất Cả
                    ({{ $totalReviews }})</button>
                <button class="btn btn-sm btn-outline-secondary">5 Sao
                    ({{ round($totalReviews * 0.8) }})</button>
                <button class="btn btn-sm btn-outline-secondary">4 Sao
                    ({{ round($totalReviews * 0.15) }})</button>
                <button class="btn btn-sm btn-outline-secondary">3 Sao
                    ({{ round($totalReviews * 0.05) }})</button>
                <button class="btn btn-sm btn-outline-secondary">Có Hình Ảnh/Video</button>
            </div>
        </div>
    </div>

    {{-- Danh sách Đánh Giá từ Khách Hàng --}}
    <div class="review-list">
        @forelse ($reviews as $rev)
            <div class="review-item border-bottom pb-3 mb-3">
                <div class="d-flex gap-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($rev->user->name ?? 'Khách hàng') }}&background=ee4d2d&color=fff"
                        alt="{{ $rev->user->name ?? 'User' }}" class="rounded-circle"
                        style="width: 42px; height: 42px;">
                    <div class="flex-fill">
                        <div class="fw-bold text-dark mb-1">
                            {{ $rev->user->name ?? 'Khách hàng ẩn danh' }}
                        </div>
                        <div class="text-warning small mb-1">
                            @for ($s = 1; $s <= 5; $s++)
                                @if ($s <= $rev->rating)
                                    <i class="fa-solid fa-star"></i>
                                @else
                                    <i class="fa-regular fa-star text-muted"></i>
                                @endif
                            @endfor
                        </div>
                        <div class="text-muted extra-small mb-2">
                            {{ $rev->created_at ? $rev->created_at->format('d/m/Y H:i') : 'Mới đây' }}
                        </div>
                        <p class="text-dark mb-0 fs-6">{{ $rev->comment }}</p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted text-center py-4">Chưa có đánh giá nào cho sản phẩm này.</p>
        @endforelse
    </div>

</div>
