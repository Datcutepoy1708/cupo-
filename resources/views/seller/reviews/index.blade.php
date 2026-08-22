@extends('layouts.admin.app')

@section('page-title', 'Đánh giá & Phản hồi Khách hàng')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Seller</a></li>
    <li class="breadcrumb-item active">Đánh giá sản phẩm</li>
@endsection

@push('styles')
<link href="{{ asset('seller/css/reviews.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Quản lý Đánh giá & Phản hồi Khách hàng</h4>
            <p class="text-muted small mb-0">Theo dõi mức độ hài lòng của người mua, phản hồi đánh giá và báo cáo các đánh giá vi phạm.</p>
        </div>
    </div>

    <!-- Rating Overview & Stats Card -->
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="seller-rating-card shadow-sm h-100 d-flex flex-column justify-content-center text-center">
                <div class="text-muted small mb-2 text-uppercase fw-bold">Điểm đánh giá trung bình Shop</div>
                <div class="seller-star-score mb-2">{{ number_format($stats['average_rating'], 1) }}<span class="fs-4 text-muted">/5</span></div>
                <div class="text-warning fs-5 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star {{ $i <= round($stats['average_rating']) ? 'text-warning' : 'text-muted text-opacity-25' }}"></i>
                    @endfor
                </div>
                <div class="text-muted small">Dựa trên <strong>{{ number_format($stats['total_reviews']) }}</strong> lượt đánh giá hợp lệ</div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="seller-rating-card shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold small text-uppercase text-muted">Chi tiết phân bổ sao</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                        Tỷ lệ phản hồi: <strong>{{ $stats['response_rate'] }}%</strong>
                    </span>
                </div>

                @php $total = max(1, $stats['total_reviews']); @endphp
                @foreach([5, 4, 3, 2, 1] as $star)
                    @php
                        $count = $stats['star_counts'][$star] ?? 0;
                        $percent = round(($count / $total) * 100);
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-2 small">
                        <span style="width: 50px;" class="fw-semibold text-muted">{{ $star }} <i class="fa-solid fa-star text-warning" style="font-size: 10px;"></i></span>
                        <div class="star-progress-bar flex-grow-1">
                            <div class="star-progress-fill" style="width: {{ $percent }}%;"></div>
                        </div>
                        <span style="width: 60px;" class="text-end text-muted">{{ $count }} ({{ $percent }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('seller.reviews.index') }}" class="row g-2 align-items-center">
                <div class="col-lg-4 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tên SP, người mua, nội dung..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <select name="rating" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả số sao (1-5★) --</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Sao (Tuyệt vời)</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Sao (Tốt)</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Sao (Bình thường)</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Sao (Kém)</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Sao (Rất tệ)</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <select name="state" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="unreplied" {{ request('state') == 'unreplied' ? 'selected' : '' }}>Chưa phản hồi ({{ $stats['unreplied_count'] }})</option>
                        <option value="replied" {{ request('state') == 'replied' ? 'selected' : '' }}>Đã phản hồi</option>
                        <option value="reported" {{ request('state') == 'reported' ? 'selected' : '' }}>Đang khiếu nại</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Lọc</button>
                    <a href="{{ route('seller.reviews.index') }}" class="btn btn-outline-secondary" title="Đặt lại"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews List -->
    @forelse($reviews as $rev)
        <div class="review-item-card shadow-sm">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <!-- Customer Info -->
                <div class="d-flex align-items-center gap-3">
                    <div class="customer-avatar">{{ strtoupper(substr($rev->user->name ?? 'K', 0, 1)) }}</div>
                    <div>
                        <div class="fw-bold text-dark">{{ $rev->user->name ?? 'Khách hàng ẩn danh' }}</div>
                        <div class="small text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i <= $rev->rating ? 'text-warning' : 'text-muted text-opacity-25' }}" style="font-size: 11px;"></i>
                            @endfor
                            <span class="text-muted ms-2" style="font-size: 11px;">{{ $rev->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Product Badge & Report Status -->
                <div class="text-end">
                    @if($rev->is_reported)
                        <span class="badge {{ match($rev->report_status) {
                            'pending'   => 'bg-warning text-dark',
                            'resolved'  => 'bg-danger text-white',
                            'dismissed' => 'bg-secondary text-white',
                            default     => 'bg-warning text-dark',
                        } }} mb-1">
                            <i class="fa-solid fa-flag me-1"></i>Khiếu nại: {{ match($rev->report_status) {
                                'pending'   => 'Chờ Admin duyệt',
                                'resolved'  => 'Đã chấp thuận',
                                'dismissed' => 'Bị từ chối',
                                default     => $rev->report_status,
                            } }}
                        </span>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-report-review"
                            data-review-id="{{ $rev->id }}"
                            data-customer="{{ $rev->user->name ?? 'Khách hàng' }}"
                            data-comment="{{ $rev->comment }}">
                            <i class="fa-regular fa-flag me-1"></i>Báo cáo vi phạm
                        </button>
                    @endif
                </div>
            </div>

            <!-- Product Purchased -->
            <div class="p-2 bg-light rounded-2 small text-muted mb-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-box-open text-primary"></i>
                <span>Sản phẩm: <strong>{{ $rev->product->name ?? 'N/A' }}</strong></span>
            </div>

            <!-- Review Comment -->
            <div class="text-dark mb-3">
                {{ $rev->comment ?: 'Khách hàng không để lại nhận xét bằng chữ.' }}
            </div>

            <!-- Seller Reply Section -->
            @if($rev->reply)
                <div class="seller-reply-box">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-primary small"><i class="fa-solid fa-reply me-1"></i>Phản hồi của Shop:</strong>
                        <span class="small text-muted" style="font-size: 11px;">{{ $rev->reply->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="small text-dark">{{ $rev->reply->reply }}</div>
                </div>
            @else
                <!-- Reply Form -->
                <form class="reply-review-form mt-2" data-review-id="{{ $rev->id }}">
                    <div class="input-group">
                        <textarea name="reply" class="form-control form-control-sm" rows="1" placeholder="Nhập câu trả lời/cảm ơn khách hàng..." required></textarea>
                        <button type="submit" class="btn btn-sm btn-primary">Gửi phản hồi</button>
                    </div>
                </form>
            @endif
        </div>
    @empty
        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
            <i class="fa-regular fa-comment-dots fs-1 text-muted mb-2"></i>
            <h5 class="text-muted">Chưa có đánh giá nào phù hợp</h5>
            <p class="text-muted small mb-0">Khi khách hàng mua và đánh giá sản phẩm của shop, danh sách sẽ hiển thị tại đây.</p>
        </div>
    @endforelse

    <!-- Pagination -->
    <div class="mt-4">
        {{ $reviews->links() }}
    </div>

</div>

<!-- Include Report Modal -->
@include('seller.reviews.partials._report-modal')

@endsection

@push('scripts')
<script src="{{ asset('seller/js/reviews.js') }}"></script>
@endpush
