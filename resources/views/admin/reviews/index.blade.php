@extends('layouts.admin.app')

@section('page-title', 'Kiểm duyệt Đánh giá')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Quản lý Đánh giá</li>
@endsection

@push('styles')
<link href="{{ asset('admin/css/reviews.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Kiểm duyệt Đánh giá & Xử lý Khiếu nại</h4>
            <p class="text-muted small mb-0">Giám sát tính minh bạch của toàn bộ đánh giá sản phẩm trên sàn Cupo, xử lý các báo cáo vi phạm từ Seller.</p>
        </div>
    </div>

    <!-- 4 Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="review-stat-card shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="fa-solid fa-comments fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tổng số đánh giá</div>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['total_reviews']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="review-stat-card shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                        <i class="fa-solid fa-star fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Điểm trung bình sàn</div>
                        <h4 class="fw-bold mb-0 text-warning">{{ number_format($stats['average_rating'], 1) }} / 5★</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="review-stat-card shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                        <i class="fa-solid fa-flag fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Khiếu nại chờ duyệt</div>
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($stats['pending_reports_count']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="review-stat-card shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-secondary bg-opacity-10 text-secondary rounded-3 p-3 me-3">
                        <i class="fa-solid fa-eye-slash fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Đánh giá đã ẩn</div>
                        <h4 class="fw-bold mb-0 text-secondary">{{ number_format($stats['hidden_reviews_count']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-2 align-items-center">
                <div class="col-lg-4 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tên SP, người mua, nội dung..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <select name="rating" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả số sao --</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Sao</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Sao</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Sao</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Sao</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Sao</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <select name="report_status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả khiếu nại --</option>
                        <option value="pending" {{ request('report_status') == 'pending' ? 'selected' : '' }}>Chờ duyệt ({{ $stats['pending_reports_count'] }})</option>
                        <option value="resolved" {{ request('report_status') == 'resolved' ? 'selected' : '' }}>Đã chấp thuận</option>
                        <option value="dismissed" {{ request('report_status') == 'dismissed' ? 'selected' : '' }}>Đã bác bỏ</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Trạng thái hiển thị --</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đang hiển thị</option>
                        <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Đã ẩn vi phạm</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Lọc</button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary" title="Đặt lại"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 130px;">Thời gian</th>
                        <th>Sản phẩm & Shop</th>
                        <th>Khách hàng & Rating</th>
                        <th>Nội dung đánh giá</th>
                        <th>Trạng thái & Khiếu nại</th>
                        <th class="text-end" style="width: 140px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $rev)
                        <tr>
                            <td class="small text-muted">
                                <i class="fa-regular fa-clock me-1"></i>{{ $rev->created_at->format('d/m/Y') }}
                                <div style="font-size: 11px;">{{ $rev->created_at->format('H:i:s') }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-7">{{ $rev->product->name ?? 'N/A' }}</div>
                                <div class="small text-muted">
                                    Shop: <strong class="text-primary">{{ $rev->product->seller->sellerProfile->shop_name ?? $rev->product->seller->name ?? 'N/A' }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $rev->user->name ?? 'Khách ẩn danh' }}</div>
                                <div class="text-warning small">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $rev->rating ? 'text-warning' : 'text-muted text-opacity-25' }}" style="font-size: 10px;"></i>
                                    @endfor
                                    <span class="text-muted ms-1">({{ $rev->rating }}★)</span>
                                </div>
                            </td>
                            <td class="review-comment-cell">
                                <div class="text-dark">{{ $rev->comment ?: 'Không có lời nhận xét.' }}</div>
                                @if($rev->reply)
                                    <div class="seller-reply-preview">
                                        <strong>Shop trả lời:</strong> {{ $rev->reply->reply }}
                                    </div>
                                @endif
                                @if($rev->is_reported)
                                    <div class="report-reason-box">
                                        <i class="fa-solid fa-flag me-1"></i><strong>Shop khiếu nại:</strong> {{ $rev->report_reason }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="mb-1">
                                    <span class="badge {{ $rev->status === 'approved' ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                        {{ $rev->status === 'approved' ? 'Đang hiển thị' : 'Đã ẩn vi phạm' }}
                                    </span>
                                </div>
                                @if($rev->is_reported)
                                    <div>
                                        <span class="badge {{ match($rev->report_status) {
                                            'pending'   => 'bg-warning text-dark',
                                            'resolved'  => 'bg-danger text-white',
                                            'dismissed' => 'bg-secondary text-white',
                                            default     => 'bg-warning text-dark',
                                        } }}" style="font-size: 10px;">
                                            {{ match($rev->report_status) {
                                                'pending'   => 'Báo cáo: Chờ xử lý',
                                                'resolved'  => 'Báo cáo: Đã chấp thuận',
                                                'dismissed' => 'Báo cáo: Đã bác bỏ',
                                                default     => $rev->report_status,
                                            } }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($rev->is_reported && $rev->report_status === 'pending')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-resolve-report"
                                            data-review-id="{{ $rev->id }}"
                                            data-shop="{{ $rev->product->seller->sellerProfile->shop_name ?? 'Shop' }}"
                                            data-reason="{{ $rev->report_reason }}"
                                            data-comment="{{ $rev->comment }}"
                                            title="Xử lý khiếu nại báo cáo">
                                            <i class="fa-solid fa-scale-balanced"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm {{ $rev->status === 'approved' ? 'btn-outline-secondary' : 'btn-outline-success' }} btn-toggle-review"
                                        data-review-id="{{ $rev->id }}"
                                        data-status="{{ $rev->status }}"
                                        title="{{ $rev->status === 'approved' ? 'Ẩn đánh giá' : 'Khôi phục hiển thị' }}">
                                        <i class="fa-solid {{ $rev->status === 'approved' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-comments fs-2 d-block mb-2 text-secondary"></i>
                                Không tìm thấy đánh giá nào phù hợp
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-3 border-top">
            {{ $reviews->links() }}
        </div>
    </div>

</div>

<!-- Include Resolve Report Modal -->
@include('admin.reviews.partials._resolve-report-modal')

@endsection

@push('scripts')
<script src="{{ asset('admin/js/reviews.js') }}"></script>
@endpush
