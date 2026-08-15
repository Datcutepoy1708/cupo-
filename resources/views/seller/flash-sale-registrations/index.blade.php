@extends('layouts.admin.app')

@section('page-title', 'Dang ky Flash Sale')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Seller</a></li>
    <li class="breadcrumb-item active">Dang ky Flash Sale</li>
@endsection

@section('content')

    <div id="sellerFlashSaleApp"
         data-store-url="{{ route('seller.flash-sale-registrations.store') }}">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">Phien Flash Sale Dang Mo Dang Ky</h4>
                <p class="text-muted mb-0 small">Chon phien va gui de xuat san pham cua ban de Admin xet duyet.</p>
            </div>
            <a href="{{ route('seller.flash-sale-registrations.mine') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-list-check me-1"></i> Xem dang ky cua toi
            </a>
        </div>

        @forelse($openSales as $sale)
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-start justify-content-between">
                    <div>
                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle me-2">
                            <i class="fa-solid fa-fire me-1"></i>Flash Sale
                        </span>
                        <h5 class="fw-bold mb-1 mt-2">{{ $sale->name }}</h5>
                        <div class="text-muted small">
                            <i class="fa-regular fa-clock me-1"></i>
                            Phien dien ra: <strong>{{ $sale->starts_at?->format('d/m/Y H:i') }}</strong>
                            &nbsp;|&nbsp;
                            Han chot dang ky: <strong class="text-danger">{{ $sale->registration_deadline?->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                    @if($sale->my_registration_count > 0)
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2">
                            <i class="fa-solid fa-check me-1"></i>Da dang ky {{ $sale->my_registration_count }} SP
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <form class="registration-form"
                          data-flash-sale-id="{{ $sale->id }}">
                        @csrf
                        <input type="hidden" name="flash_sale_id" value="{{ $sale->id }}">

                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">San pham cua ban <span class="text-danger">*</span></label>
                                <select class="form-select product-select" name="product_id" required>
                                    <option value="">-- Chon san pham --</option>
                                    @foreach($sellerProducts as $product)
                                        <option value="{{ $product->id }}"
                                                data-price="{{ $product->price }}"
                                                data-stock="{{ $product->stock }}">
                                            {{ $product->name }} (Gia: {{ number_format($product->price, 0, ',', '.') }}d | Ton: {{ $product->stock }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback product-id-error"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Gia de xuat (VND) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control proposed-price" name="proposed_price" min="1" placeholder="0" required>
                                <div class="form-text proposed-price-hint text-muted small"></div>
                                <div class="invalid-feedback proposed-price-error"></div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">So luong <span class="text-danger">*</span></label>
                                <input type="number" class="form-control proposed-quantity" name="proposed_quantity" min="1" placeholder="0" required>
                                <div class="invalid-feedback proposed-quantity-error"></div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Gui Dang Ky
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body text-center py-5 text-muted">
                    <i class="fa-solid fa-bolt fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">Hien tai khong co phien Flash Sale nao dang mo nhan dang ky.</p>
                </div>
            </div>
        @endforelse
    </div>

@endsection

@push('styles')
    <link href="{{ asset('seller/css/flash-sale-registrations.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('seller/js/flash-sale-registrations.js') }}"></script>
@endpush
