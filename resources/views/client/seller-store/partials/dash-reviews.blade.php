<div class="tab-pane fade" id="dashReviews" role="tabpanel">
    <div class="dash-section-title">Đánh giá từ khách hàng</div>

    @forelse ($shop->reviews ?? [] as $review)
        <div class="review-row">
            <div class="review-row-top">
                <span class="fw-bold">{{ $review->customer_name }}</span>
                <span class="text-muted small">{{ $review->created_at->diffForHumans() }}</span>
            </div>
            <div class="review-stars mb-2">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="fa-solid fa-star {{ $i <= $review->rating ? '' : 'text-muted opacity-25' }}"></i>
                @endfor
            </div>
            <p class="mb-2 text-muted">{{ $review->content }}</p>

            @if ($review->reply)
                <span class="badge bg-secondary">Đã phản hồi</span>
            @else
                <button class="btn btn-sm btn-outline-secondary" data-id="{{ $review->id }}">
                    <i class="fa-solid fa-reply me-1"></i>Phản hồi
                </button>
            @endif
        </div>
    @empty
        <p class="text-muted">Chưa có đánh giá nào</p>
    @endforelse
</div>
