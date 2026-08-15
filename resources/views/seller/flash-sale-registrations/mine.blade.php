@extends('layouts.admin.app')

@section('page-title', 'Dang ky Flash Sale cua toi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Seller</a></li>
    <li class="breadcrumb-item"><a href="{{ route('seller.flash-sale-registrations.index') }}">Dang ky Flash Sale</a></li>
    <li class="breadcrumb-item active">Dang ky cua toi</li>
@endsection

@section('content')

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-check me-2 text-primary"></i>Danh sach Dang ky Flash Sale cua toi</h5>
            <a href="{{ route('seller.flash-sale-registrations.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> Dang ky them
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Phien Flash Sale</th>
                            <th>San pham</th>
                            <th>Gia de xuat</th>
                            <th>So luong</th>
                            <th>Trang thai</th>
                            <th>Ly do tu choi</th>
                            <th class="text-end pe-4">Hanh dong</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $reg)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $reg->flashSale?->name ?? 'Phien da bi xoa' }}</div>
                                    <div class="text-muted small">
                                        Bat dau: {{ $reg->flashSale?->starts_at?->format('d/m/Y H:i') ?? '--' }}
                                    </div>
                                    @if($reg->flashSale?->registration_deadline)
                                        <div class="text-muted small">
                                            Han chot: {{ $reg->flashSale->registration_deadline->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $reg->product?->name ?? 'San pham khong con' }}</td>
                                <td class="fw-bold text-primary">{{ number_format($reg->proposed_price, 0, ',', '.') }}d</td>
                                <td>{{ number_format($reg->proposed_quantity) }}</td>
                                <td>
                                    @if($reg->status === 'pending')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            <i class="fa-solid fa-clock me-1"></i>Cho duyet
                                        </span>
                                    @elseif($reg->status === 'approved')
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                            <i class="fa-solid fa-check me-1"></i>Da duyet
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                            <i class="fa-solid fa-xmark me-1"></i>Da tu choi
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($reg->rejection_reason)
                                        <span class="text-danger small">{{ $reg->rejection_reason }}</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($reg->canBeCancelledBy(auth()->user()))
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-cancel-registration"
                                                data-id="{{ $reg->id }}"
                                                data-destroy-url="{{ route('seller.flash-sale-registrations.destroy', $reg) }}">
                                            <i class="fa-solid fa-xmark me-1"></i>Huy
                                        </button>
                                    @else
                                        <span class="text-muted small">--</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-clipboard fa-2x mb-2 d-block opacity-25"></i>
                                    Ban chua co dang ky Flash Sale nao.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($registrations->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('seller/js/flash-sale-registrations.js') }}"></script>
@endpush
