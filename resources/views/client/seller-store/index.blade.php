@extends('layouts.client.app')

@section('content')
    <div class="shop-page">
        <div class="container">

            @if ($shop->status === 'pending')
                {{-- ===== TRẠNG THÁI: ĐANG CHỜ DUYỆT ===== --}}
                <div class="shop-status-card status-pending">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <h2>Gian hàng đang chờ duyệt</h2>
                    <p>
                        Cupo đang xem xét thông tin đăng ký gian hàng <strong>"{{ $shop->name }}"</strong> của bạn.
                        Thời gian duyệt thường trong vòng 24-48 giờ làm việc.
                    </p>
                    <a href="{{ route('home') }}" class="btn btn-save">Quay về trang chủ</a>
                </div>
            @elseif ($shop->status === 'rejected')
                {{-- ===== TRẠNG THÁI: BỊ TỪ CHỐI ===== --}}
                <div class="shop-status-card status-rejected">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <h2>Đăng ký gian hàng chưa được duyệt</h2>
                    <p>Gian hàng <strong>"{{ $shop->name }}"</strong> chưa đáp ứng đủ điều kiện. Lý do:</p>
                    <div class="reject-reason">
                        {{ $shop->reject_reason ?? 'Không có lý do cụ thể, vui lòng liên hệ CSKH để biết thêm chi tiết.' }}
                    </div>
                    <a href="{{ route('seller.register') }}" class="btn btn-save">
                        <i class="fa-solid fa-rotate-right me-1"></i> Đăng ký lại
                    </a>
                </div>
            @elseif ($shop->status === 'approved')
                {{-- ===== TRẠNG THÁI: ĐÃ DUYỆT — DASHBOARD ĐẦY ĐỦ ===== --}}
                @include('client.seller-store.partials.info-header')
                @include('client.seller-store.partials.navigation')

                <div class="tab-content shop-dashboard">
                    @include('client.seller-store.partials.dash-overview')
                    @include('client.seller-store.partials.dash-products')
                    @include('client.seller-store.partials.dash-orders')
                    @include('client.seller-store.partials.dash-reviews')
                    @include('client.seller-store.partials.dash-info')
                </div>

                {{-- ===== MODAL: THÊM SẢN PHẨM ===== --}}
                <x-modal name="addProductModal" title="Thêm sản phẩm mới" max-width="lg">
                    <form id="addProductForm" method="post" action="#">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="product_name"
                                    placeholder="Nhập tên sản phẩm">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Giá bán <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="price" placeholder="VNĐ">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số lượng kho <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stock" placeholder="Số lượng">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Mô tả sản phẩm</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Ảnh sản phẩm</label>
                                <input type="file" class="form-control" name="images[]" multiple>
                            </div>
                        </div>
                    </form>
                    <x-slot name="footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" form="addProductForm" class="btn btn-danger">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Lưu sản phẩm
                        </button>
                    </x-slot>
                </x-modal>

                {{-- ===== MODAL: ĐỔI AVATAR / BANNER ===== --}}
                <x-modal name="editAvatarModal" title="Đổi ảnh đại diện cửa hàng" max-width="sm">
                    <form id="editAvatarForm" method="post" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" class="form-control" name="avatar" accept="image/*">
                    </form>
                    <x-slot name="footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" form="editAvatarForm" class="btn btn-danger">Cập nhật</button>
                    </x-slot>
                </x-modal>

                <x-modal name="editBannerModal" title="Đổi ảnh bìa cửa hàng">
                    <form id="editBannerForm" method="post" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" class="form-control" name="banner" accept="image/*">
                    </form>
                    <x-slot name="footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" form="editBannerForm" class="btn btn-danger">Cập nhật</button>
                    </x-slot>
                </x-modal>
            @endif

        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}">
@endpush
