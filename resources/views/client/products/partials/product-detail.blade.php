<div class="card border-0 shadow-sm rounded-3 mb-4 p-3 p-lg-4" style="background:#fff;">

    {{-- Chi tiết thông số --}}
    <div class="mb-4">
        <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3 bg-light p-2 rounded">
            CHI TIẾT SẢN PHẨM
        </h2>
        <div class="table-responsive">
            <table class="table table-borderless table-sm spec-table align-middle">
                <tbody>
                    <tr>
                        <td class="text-muted fw-semibold" style="width: 200px;">Danh Mục</td>
                        <td>
                            <a href="{{ route('categories.index') }}" class="text-danger text-decoration-none">Cupo</a>
                            &gt;
                            @if ($product->category)
                                <a href="{{ url('/categories/' . $product->category->slug) }}"
                                    class="text-danger text-decoration-none">{{ $product->category->name }}</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Thương hiệu</td>
                        <td><strong class="text-primary">{{ $shopName }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Mã SKU</td>
                        <td><code>{{ $product->sku ?: 'Chưa cập nhật' }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Tình trạng</td>
                        <td><span
                                class="badge {{ $product->stock > 0 ? 'bg-success' : 'bg-secondary' }}">{{ $product->stock > 0 ? 'Còn hàng' : 'Hết hàng' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Gửi từ</td>
                        <td>{{ $profile->address ?? 'Thành phố Hà Nội' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mô tả chi tiết HTML --}}
    <div>
        <h2 class="h5 fw-bold text-dark border-bottom pb-2 mb-3 bg-light p-2 rounded">
            MÔ TẢ SẢN PHẨM
        </h2>
        <div class="prod-desc-content p-2">
            {!! $product->description ?: '<p class="text-muted mb-0">Sản phẩm chưa có mô tả.</p>' !!}
        </div>
    </div>

</div>
