@extends('layouts.client.app')

@section('content')
    <div class="container py-4">
        <h2 class="h4 fw-bold mb-4">
            <i class="fa-solid fa-cart-shopping text-danger me-2"></i>Giỏ hàng của tôi ({{ $totalItems }} sản phẩm)
        </h2>

        @if (session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @elseif (session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif

        @if ($groupedShops->isEmpty())
            <div class="content-card text-center py-5">
                <h5>Giỏ hàng của bạn đang trống!</h5>
                <p class="text-muted">Hãy khám phá thêm hàng ngàn sản phẩm giá tốt trên Cupo.</p>
                <a href="{{ route('home') }}" class="btn btn-save px-4 mt-2">
                    <i class="fa-solid fa-bag-shopping me-2"></i>Mua sắm ngay
                </a>
            </div>
        @else
            <div class="row g-4">
                {{-- Cột trái: Danh sách các Shop & Sản phẩm --}}
                <div class="col-lg-8">
                    @foreach ($groupedShops as $shopData)
                        <div class="content-card mb-3">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                <i class="fa-solid fa-store text-danger me-2"></i>
                                <span class="fw-bold">{{ $shopData['shop_name'] }}</span>
                            </div>

                            <div class="cart-items">
                                @foreach ($shopData['items'] as $item)
                                    @php
                                        $unitPrice = $item->variant ? $item->variant->price : $item->product->price;
                                        $itemSubtotal = $unitPrice * $item->quantity;
                                    @endphp
                                    <div class="order-item-row align-items-center py-3 border-bottom">
                                        <img src="{{ asset($item->product->thumbnail ?? 'https://via.placeholder.com/80') }}"
                                            alt="{{ $item->product->name }}" class="order-thumb me-3">

                                        <div class="order-info flex-grow-1">
                                            <h6 class="mb-1 text-truncate" style="max-width: 300px;">
                                                {{ $item->product->name }}
                                            </h6>
                                            @if ($item->variant)
                                                <span class="badge bg-light text-dark border">
                                                    Phân loại: {{ $item->variant->name }}
                                                </span>
                                            @endif
                                            <div class="text-danger fw-bold mt-1">
                                                {{ number_format($unitPrice) }}₫
                                            </div>
                                        </div>

                                        {{-- Cập nhật số lượng --}}
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST"
                                            class="d-flex align-items-center me-3">
                                            @csrf
                                            @method('PUT')
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                                class="form-control form-control-sm text-center me-2" style="width: 70px;">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Cập nhật số lượng">
                                                <i class="fa-solid fa-rotate"></i>
                                            </button>
                                        </form>

                                        <div class="fw-bold text-end me-3" style="min-width: 100px;">
                                            {{ number_format($itemSubtotal) }}₫
                                        </div>

                                        {{-- Xóa mục khỏi giỏ --}}
                                        <form action="{{ route('cart.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- Nút xóa sạch giỏ hàng --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left me-2"></i>Tiếp tục mua sắm
                        </a>
                        <form action="{{ route('cart.clear') }}" method="POST"
                            onsubmit="return confirm('Bạn có chắc muốn xóa sạch toàn bộ giỏ hàng?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fa-solid fa-broom me-2"></i>Xóa toàn bộ giỏ hàng
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Cột phải: Tóm tắt đơn hàng & Checkout --}}
                <div class="col-lg-4">
                    <div class="content-card position-sticky" style="top: 80px;">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Tóm tắt đơn hàng</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tổng số lượng sản phẩm:</span>
                            <span class="fw-bold">{{ $totalItems }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 fs-5">
                            <span class="fw-bold">Tổng tiền tạm tính:</span>
                            <span class="fw-bold text-danger">{{ number_format($totalPrice) }}₫</span>
                        </div>
                        <hr>
                        <a href="{{ route('customer.orders.index') }}" class="btn btn-save w-100 py-2 fs-6">
                            <i class="fa-solid fa-credit-card me-2"></i>Tiến hành Đặt hàng
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
