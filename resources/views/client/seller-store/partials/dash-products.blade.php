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
                    <th>Loại sản phẩm</th>
                    <th>Giá</th>
                    <th>Kho</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shop->products ?? [] as $product)
                    @php
                        $thumbUrl = $product->thumbnail 
                            ? (Str::startsWith($product->thumbnail, 'http') ? $product->thumbnail : asset('storage/' . $product->thumbnail))
                            : 'https://placehold.co/100x100?text=No+Image';
                    @endphp
                    <tr id="product-row-{{ $product->id }}">
                        <td>
                            <img src="{{ $thumbUrl }}" class="dash-product-thumb" alt="{{ $product->name }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px;">
                        </td>
                        <td>
                            <div class="fw-semibold text-dark d-flex align-items-center flex-wrap gap-1">
                                <span>{{ $product->name }}</span>
                                @if ($product->has_variants && $product->variants->isNotEmpty())
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-layer-group me-1"></i>{{ $product->variants->count() }} biến thể
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">SKU: {{ $product->sku }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-tag me-1 text-primary"></i>
                                {{ $product->category->name ?? 'Chưa phân loại' }}
                            </span>
                        </td>
                        <td>
                            @if ($product->has_variants && $product->variants->isNotEmpty())
                                <div class="fw-bold text-danger">{{ $product->price_range_display }}</div>
                                <small class="text-muted" style="font-size: 0.75rem;">(Theo phân loại)</small>
                            @elseif ($product->is_on_sale)
                                <div class="fw-bold text-danger">{{ number_format($product->sale_price) }}₫</div>
                                <div class="d-flex align-items-center gap-1">
                                    <del class="text-muted small">{{ number_format($product->price) }}₫</del>
                                    <span class="badge bg-danger-subtle text-danger small" style="font-size: 0.7rem;">-{{ $product->discount_percentage }}%</span>
                                </div>
                            @else
                                <div class="fw-bold text-dark">{{ number_format($product->price) }}₫</div>
                            @endif
                        </td>
                        <td>
                            {{ $product->stock }}
                            @if ($product->has_variants && $product->variants->isNotEmpty())
                                <span class="d-block text-muted" style="font-size: 0.7rem;">Tổng kho biến thể</span>
                            @endif
                        </td>
                        <td>
                            @if ($product->status === 'approved')
                                @if ($product->stock <= 0)
                                    <span class="badge bg-secondary">Hết hàng</span>
                                @else
                                    <span class="badge bg-success">Đang bán</span>
                                @endif
                            @elseif ($product->status === 'pending')
                                <span class="badge bg-warning text-dark">Chờ duyệt</span>
                            @elseif ($product->status === 'rejected')
                                <span class="badge bg-danger">Bị từ chối</span>
                            @elseif ($product->status === 'blocked')
                                <span class="badge bg-dark">Bị khóa</span>
                            @else
                                <span class="badge bg-secondary">Bản nháp</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-product me-1" 
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-category_id="{{ $product->category_id }}"
                                data-sku="{{ $product->sku }}"
                                data-price="{{ (float)$product->price }}"
                                data-sale_price="{{ $product->sale_price ? (float)$product->sale_price : '' }}"
                                data-stock="{{ $product->stock }}"
                                data-has_variants="{{ $product->has_variants ? '1' : '0' }}"
                                data-attributes="{{ json_encode($product->attributes ?? []) }}"
                                data-variants="{{ json_encode($product->variants ?? []) }}"
                                data-description="{{ $product->description }}"
                                data-thumbnail="{{ $thumbUrl }}"
                                data-bs-toggle="modal" 
                                data-bs-target="#editProductModal"
                                title="Chỉnh sửa sản phẩm">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-product" 
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                title="Xóa sản phẩm">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-box-open fa-2x mb-2 d-block text-secondary opacity-50"></i>
                            Chưa có sản phẩm nào. Hãy bấm <strong>Thêm sản phẩm</strong> để đăng bán!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
