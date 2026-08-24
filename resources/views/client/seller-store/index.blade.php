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
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

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
                    <form id="addProductForm" method="post" action="{{ route('seller.products.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-7">
                                <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="add_product_name" required
                                    placeholder="Nhập tên sản phẩm">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Loại / Danh mục sản phẩm <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="add_category_id" required>
                                    <option value="">-- Chọn loại sản phẩm --</option>
                                    @forelse ($allCategories ?? [] as $category)
                                        @if ($category->children && $category->children->count() > 0)
                                            <optgroup label="{{ $category->name }}">
                                                @foreach ($category->children as $child)
                                                    <option value="{{ $child->id }}">{{ $child->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @else
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endif
                                    @empty
                                        <option value="" disabled>Gian hàng chưa có ngành hàng được duyệt</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Giá niêm yết (Giá gốc) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="price" id="add_price" min="0" step="1000" required placeholder="0">
                                    <span class="input-group-text">₫</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giá khuyến mãi (Giảm giá)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="sale_price" id="add_sale_price" min="0" step="1000" placeholder="Để trống nếu không giảm">
                                    <span class="input-group-text">₫</span>
                                </div>
                                <div class="form-text text-danger d-none" id="add_discount_calc">
                                    <i class="fa-solid fa-bolt me-1"></i>Giảm: <strong id="add_discount_percent">0%</strong> (Tiết kiệm: <span id="add_discount_amount">0₫</span>)
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Số lượng kho <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stock" id="add_stock" min="0" value="1" required placeholder="Số lượng">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Mã SKU <span class="text-muted small">(Tùy chọn)</span></label>
                                <input type="text" class="form-control" name="sku" id="add_sku" placeholder="Tự sinh nếu để trống">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ảnh đại diện sản phẩm</label>
                                <input type="file" class="form-control" name="thumbnail" id="add_thumbnail" accept="image/*">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Mô tả sản phẩm</label>
                                <textarea class="form-control" name="description" id="add_description" rows="3" placeholder="Nhập mô tả sản phẩm..."></textarea>
                            </div>
                        </div>
                    </form>
                    <x-slot name="footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" form="addProductForm" class="btn btn-danger" id="btnAddProductSubmit">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Lưu sản phẩm
                        </button>
                    </x-slot>
                </x-modal>

                {{-- ===== MODAL: SỬA SẢN PHẨM ===== --}}
                <x-modal name="editProductModal" title="Chỉnh sửa sản phẩm" max-width="lg">
                    <form id="editProductForm" method="post" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" id="edit_product_id" name="id">

                        <div class="row mb-3">
                            <div class="col-md-7">
                                <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="edit_product_name" required
                                    placeholder="Nhập tên sản phẩm">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Loại / Danh mục sản phẩm <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="edit_category_id" required>
                                    <option value="">-- Chọn loại sản phẩm --</option>
                                    @forelse ($allCategories ?? [] as $category)
                                        @if ($category->children && $category->children->count() > 0)
                                            <optgroup label="{{ $category->name }}">
                                                @foreach ($category->children as $child)
                                                    <option value="{{ $child->id }}">{{ $child->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @else
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endif
                                    @empty
                                        <option value="" disabled>Gian hàng chưa có ngành hàng được duyệt</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Giá niêm yết (Giá gốc) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="price" id="edit_price" min="0" step="1000" required placeholder="0">
                                    <span class="input-group-text">₫</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giá khuyến mãi (Giảm giá)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="sale_price" id="edit_sale_price" min="0" step="1000" placeholder="Để trống nếu không giảm">
                                    <span class="input-group-text">₫</span>
                                </div>
                                <div class="form-text text-danger d-none" id="edit_discount_calc">
                                    <i class="fa-solid fa-bolt me-1"></i>Giảm: <strong id="edit_discount_percent">0%</strong> (Tiết kiệm: <span id="edit_discount_amount">0₫</span>)
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Số lượng kho <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stock" id="edit_stock" min="0" required placeholder="Số lượng">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Mã SKU</label>
                                <input type="text" class="form-control" name="sku" id="edit_sku" placeholder="Mã SKU">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ảnh đại diện sản phẩm</label>
                                <input type="file" class="form-control mb-1" name="thumbnail" id="edit_thumbnail" accept="image/*">
                                <div id="edit_thumbnail_preview_wrap" class="d-none mt-2">
                                    <span class="small text-muted d-block mb-1">Ảnh hiện tại:</span>
                                    <img id="edit_thumbnail_preview" src="" alt="Thumbnail" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Mô tả sản phẩm</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="3" placeholder="Nhập mô tả sản phẩm..."></textarea>
                            </div>
                        </div>
                    </form>
                    <x-slot name="footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" form="editProductForm" class="btn btn-danger" id="btnEditProductSubmit">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Cập nhật sản phẩm
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
