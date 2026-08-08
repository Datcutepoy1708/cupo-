<div class="tab-pane fade" id="dashProducts" role="tabpanel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="dash-section-title mb-0">Sản phẩm của tôi</div>
        <button type="button" class="btn btn-save" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fa-solid fa-plus me-1"></i> Thêm sản phẩm
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th></th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Kho</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shop->products ?? [] as $product)
                    <tr>
                        <td><img src="{{ $product->thumbnail }}" class="dash-product-thumb"></td>
                        <td>{{ $product->name }}</td>
                        <td>{{ number_format($product->price) }}₫</td>
                        <td>{{ $product->stock }}</td>
                        <td>
                            @if ($product->stock <= 0)
                                <span class="badge bg-secondary">Hết hàng</span>
                            @else
                                <span class="badge bg-success">Đang bán</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" data-id="{{ $product->id }}"><i
                                    class="fa-solid fa-pen"></i></button>
                            <button class="btn btn-sm btn-outline-danger" data-id="{{ $product->id }}"><i
                                    class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Chưa có sản phẩm nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
