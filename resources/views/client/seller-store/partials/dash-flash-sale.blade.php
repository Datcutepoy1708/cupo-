<div class="tab-pane fade" id="dashFlashSale" role="tabpanel">
    {{-- Header Banner Flash Sale --}}
    <div class="flash-sale-hero-banner mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white text-danger fw-bold small mb-2 shadow-sm">
                    <i class="fa-solid fa-bolt text-danger"></i> CUPO FLASH SALE PROGRAM
                </div>
                <h3 class="fw-bold text-white mb-1">Chương Trình Flash Sale Dành Cho Người Bán</h3>
                <p class="text-white-50 mb-0 small">
                    Đăng ký sản phẩm vào các phiên Flash Sale để bùng nổ doanh số và tiếp cận hàng triệu khách hàng tiềm năng.
                </p>
            </div>
            <div class="text-md-end text-white">
                <div class="fs-4 fw-bold">{{ $openFlashSales->count() }}</div>
                <div class="small text-white-50">Phiên đang mở đăng ký</div>
            </div>
        </div>
    </div>

    {{-- Điều kiện & Lưu ý đăng ký nhanh --}}
    <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-3">
        <div class="fs-3 text-warning"><i class="fa-solid fa-circle-info"></i></div>
        <div class="small">
            <div class="fw-bold text-dark mb-1">Lưu ý khi tham gia Flash Sale:</div>
            <ul class="mb-0 ps-3 text-secondary">
                <li>Giá đề xuất phải <strong>giảm tối thiểu 10%</strong> so với giá niêm yết (tức ≤ 90% giá gốc).</li>
                <li>Số lượng sản phẩm đề xuất không được vượt quá số lượng tồn kho khả dụng thực tế.</li>
                <li>Sau khi đăng ký, ban quản trị Cupo sẽ thẩm định và phê duyệt sản phẩm đủ điều kiện.</li>
                <li>Bạn có thể hủy yêu cầu đăng ký khi phiên còn trong trạng thái <strong>Chờ duyệt</strong> và <strong>chưa hết hạn đăng ký</strong>.</li>
            </ul>
        </div>
    </div>

    {{-- Navigation con: Phiên đang mở & Lịch sử đăng ký --}}
    <ul class="nav nav-tabs flash-sale-subtabs mb-4 border-bottom" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#subtabOpenSales" type="button">
                <i class="fa-solid fa-fire text-danger me-1"></i> Phiên đang mở đăng ký 
                <span class="badge bg-danger rounded-pill ms-1">{{ $openFlashSales->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#subtabMyRegistrations" type="button">
                <i class="fa-solid fa-clipboard-check text-primary me-1"></i> Đăng ký của shop
                <span class="badge bg-secondary rounded-pill ms-1">{{ $myFlashSaleRegistrations->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- 1. SUBTAB: PHIÊN ĐANG MỞ ĐĂNG KÝ --}}
        <div class="tab-pane fade show active" id="subtabOpenSales" role="tabpanel">
            @forelse ($openFlashSales as $sale)
                <div class="card flash-sale-card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
                    <div class="card-header bg-light border-0 py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger px-2.5 py-1.5 fs-7"><i class="fa-solid fa-bolt me-1"></i>Flash Sale</span>
                            <h5 class="fw-bold mb-0 text-dark">{{ $sale->name }}</h5>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            @if ($sale->my_registration_count > 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    <i class="fa-solid fa-circle-check me-1"></i> Shop đã đăng ký {{ $sale->my_registration_count }} sản phẩm
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3 mb-3 text-secondary small">
                            <div class="col-sm-6 col-md-4">
                                <i class="fa-regular fa-calendar text-muted me-1"></i> Thời gian diễn ra:
                                <div class="fw-semibold text-dark mt-1">
                                    {{ $sale->starts_at?->format('H:i d/m/Y') }} — {{ $sale->ends_at?->format('H:i d/m/Y') }}
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <i class="fa-regular fa-clock text-danger me-1"></i> Hạn chót đăng ký:
                                <div class="fw-bold text-danger mt-1">
                                    {{ $sale->registration_deadline?->format('H:i d/m/Y') }}
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 text-md-end">
                                <span class="text-muted">Trạng thái: </span>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                    <i class="fa-solid fa-door-open me-1"></i>Đang nhận đăng ký
                                </span>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded-3 border">
                            <div class="fw-semibold text-dark mb-2">
                                <i class="fa-solid fa-paper-plane text-danger me-1"></i> Gửi sản phẩm đăng ký tham gia phiên này:
                            </div>
                            <form class="seller-flash-sale-form" data-flash-sale-id="{{ $sale->id }}">
                                @csrf
                                <input type="hidden" name="flash_sale_id" value="{{ $sale->id }}">

                                <div class="row g-2 align-items-end">
                                    {{-- Chọn sản phẩm --}}
                                    <div class="col-lg-5 col-md-12">
                                        <label class="form-label small text-muted mb-1">Chọn sản phẩm <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm fs-select-product" name="product_id" required>
                                            <option value="">-- Chọn sản phẩm của shop --</option>
                                            @forelse ($shop->products as $prod)
                                                @if ($prod->status === 'approved')
                                                    @php
                                                        $pThumb = $prod->thumbnail;
                                                        $pThumbUrl = $pThumb ? (\Illuminate\Support\Str::startsWith($pThumb, ['http://', 'https://']) ? $pThumb : asset('storage/' . ltrim($pThumb, '/'))) : 'https://picsum.photos/400/300';
                                                        $hasVars = $prod->has_variants && $prod->relationLoaded('variants') && $prod->variants->isNotEmpty();
                                                        if ($hasVars) {
                                                            $minVarPrice = (float) $prod->variants->min('price');
                                                            $maxVarPrice = (float) $prod->variants->max('price');
                                                            $totalStock = (int) $prod->variants->sum('stock');
                                                            $displayLabel = $prod->name . ' (' . $prod->variants->count() . ' biến thể | ' . number_format($minVarPrice, 0, ',', '.') . '₫ - ' . number_format($maxVarPrice, 0, ',', '.') . '₫ | Tồn: ' . $totalStock . ')';
                                                        } else {
                                                            $minVarPrice = (float) $prod->price;
                                                            $maxVarPrice = (float) $prod->price;
                                                            $totalStock = (int) $prod->stock;
                                                            $displayLabel = $prod->name . ' (Giá gốc: ' . number_format($minVarPrice, 0, ',', '.') . '₫ | Tồn: ' . $totalStock . ')';
                                                        }
                                                    @endphp
                                                    <option value="{{ $prod->id }}"
                                                        data-has-variants="{{ $hasVars ? '1' : '0' }}"
                                                        data-var-count="{{ $hasVars ? $prod->variants->count() : 0 }}"
                                                        data-min-price="{{ $minVarPrice }}"
                                                        data-max-price="{{ $maxVarPrice }}"
                                                        data-price="{{ $minVarPrice }}"
                                                        data-stock="{{ $totalStock }}"
                                                        data-name="{{ $prod->name }}"
                                                        data-thumb="{{ $pThumbUrl }}">
                                                        {{ $displayLabel }}
                                                    </option>
                                                @endif
                                            @empty
                                                <option value="" disabled>Shop chưa có sản phẩm nào được duyệt</option>
                                            @endforelse
                                        </select>
                                    </div>

                                    {{-- Mức giảm giá --}}
                                    <div class="col-lg-3 col-sm-6">
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <label class="form-label small text-muted mb-0">Mức giảm giá (%) <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-1 ms-auto">
                                                <button type="button" class="btn btn-outline-secondary py-0 px-1 btn-quick-pct" data-pct="10" style="font-size: 10px; line-height: 1.6;">10%</button>
                                                <button type="button" class="btn btn-outline-secondary py-0 px-1 btn-quick-pct" data-pct="20" style="font-size: 10px; line-height: 1.6;">20%</button>
                                                <button type="button" class="btn btn-outline-secondary py-0 px-1 btn-quick-pct" data-pct="30" style="font-size: 10px; line-height: 1.6;">30%</button>
                                                <button type="button" class="btn btn-outline-secondary py-0 px-1 btn-quick-pct" data-pct="50" style="font-size: 10px; line-height: 1.6;">50%</button>
                                            </div>
                                        </div>
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control fs-discount-percent text-end" name="discount_percent" min="10" max="90" step="1" placeholder="VD: 20" required>
                                            <span class="input-group-text bg-light text-danger fw-bold">%</span>
                                        </div>
                                        <input type="hidden" class="fs-proposed-price" name="proposed_price" value="">
                                    </div>

                                    {{-- Số lượng --}}
                                    <div class="col-lg-2 col-sm-6">
                                        <label class="form-label small text-muted mb-1">Số lượng <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm fs-proposed-quantity" name="proposed_quantity" min="1" placeholder="SL" required>
                                    </div>

                                    {{-- Nút đăng ký --}}
                                    <div class="col-lg-2 col-md-12">
                                        <button type="submit" class="btn btn-danger btn-sm w-100 btn-submit-fs">
                                            <i class="fa-solid fa-paper-plane me-1"></i> Đăng ký
                                        </button>
                                    </div>
                                </div>

                                {{-- Hint texts (tách riêng để không ảnh hưởng căn hàng input) --}}
                                <div class="row g-0 mt-1">
                                    <div class="col-lg-5 col-md-12"></div>
                                    <div class="col-lg-3 col-sm-6">
                                        <div class="fs-price-hint text-muted" style="font-size: 11px;">Tối thiểu giảm 10%</div>
                                    </div>
                                    <div class="col-lg-2 col-sm-6">
                                        <div class="fs-stock-hint text-muted" style="font-size: 11px;"></div>
                                    </div>
                                    <div class="col-lg-2 col-md-12"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm rounded-3 py-5 text-center text-muted">
                    <div class="mb-3">
                        <i class="fa-solid fa-bolt fa-3x text-secondary opacity-25"></i>
                    </div>
                    <h5 class="fw-bold">Hiện không có phiên Flash Sale nào mở nhận đăng ký</h5>
                    <p class="small text-muted mb-0">Ban quản trị sẽ cập nhật các phiên Flash Sale mới sớm nhất. Vui lòng quay lại sau!</p>
                </div>
            @endforelse
        </div>

        {{-- 2. SUBTAB: ĐĂNG KÝ CỦA SHOP --}}
        <div class="tab-pane fade" id="subtabMyRegistrations" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list-check text-primary me-2"></i>Danh sách sản phẩm đã gửi đăng ký</h6>
                    <span class="text-muted small">Tổng cộng: {{ $myFlashSaleRegistrations->count() }} lượt đăng ký</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableMyFsRegistrations">
                        <thead class="table-light text-secondary small">
                            <tr>
                                <th class="ps-4">Phiên Flash Sale</th>
                                <th>Sản phẩm</th>
                                <th>Giá đề xuất</th>
                                <th>Số lượng</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú / Lý do</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($myFlashSaleRegistrations as $reg)
                                <tr id="fs-reg-row-{{ $reg->id }}">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $reg->flashSale?->name ?? 'Phiên đã xóa' }}</div>
                                        <div class="text-muted small" style="font-size: 11px;">
                                            <i class="fa-regular fa-clock me-1"></i>Diễn ra: {{ $reg->flashSale?->starts_at?->format('H:i d/m/Y') }}
                                        </div>
                                        @if ($reg->flashSale?->registration_deadline)
                                            <div class="text-danger small" style="font-size: 11px;">
                                                Hạn chót: {{ $reg->flashSale->registration_deadline->format('H:i d/m/Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $pThumb = $reg->product?->thumbnail;
                                                $thumbSrc = $pThumb ? (\Illuminate\Support\Str::startsWith($pThumb, ['http://', 'https://']) ? $pThumb : asset('storage/' . ltrim($pThumb, '/'))) : 'https://placehold.co/50x50?text=No+Img';
                                                $pHasVars = $reg->product && $reg->product->has_variants && $reg->product->relationLoaded('variants') && $reg->product->variants->isNotEmpty();
                                                if ($pHasVars) {
                                                    $cheapestVar = $reg->product->variants->sortBy('price')->first();
                                                    $pMinPrice = (float) ($cheapestVar->price ?? $reg->product->price);
                                                    $pMaxPrice = (float) $reg->product->variants->max('price');
                                                } else {
                                                    $pMinPrice = (float) ($reg->product?->price ?? 0);
                                                    $pMaxPrice = (float) ($reg->product?->price ?? 0);
                                                }
                                            @endphp
                                            <img src="{{ $thumbSrc }}" alt="" class="rounded" style="width: 42px; height: 42px; object-fit: cover; border: 1px solid #eee;">
                                            <div>
                                                <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;">
                                                    {{ $reg->product?->name ?? 'Sản phẩm không còn' }}
                                                </div>
                                                @if ($pHasVars)
                                                    <div class="text-primary small" style="font-size: 11px;">
                                                        <i class="fa-solid fa-layer-group me-1"></i>{{ $reg->product->variants->count() }} biến thể
                                                    </div>
                                                    <div class="text-muted small" style="font-size: 11px;">
                                                        Gốc rẻ nhất: {{ number_format($pMinPrice, 0, ',', '.') }}₫ (Đến {{ number_format($pMaxPrice, 0, ',', '.') }}₫)
                                                    </div>
                                                @else
                                                    <div class="text-muted small" style="font-size: 11px;">
                                                        Giá gốc: {{ number_format($pMinPrice, 0, ',', '.') }}₫
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-danger">{{ number_format($reg->proposed_price, 0, ',', '.') }}₫</div>
                                        @if ($pMinPrice > 0)
                                            @php
                                                $pct = round((($pMinPrice - $reg->proposed_price) / $pMinPrice) * 100);
                                            @endphp
                                            <span class="badge bg-danger-subtle text-danger" style="font-size: 10px;">
                                                -{{ $pct }}% @if($pHasVars)(toàn bộ biến thể)@endif
                                            </span>
                                        @endif
                                        @if ($pHasVars)
                                            <div class="text-muted" style="font-size: 10px;">(Giá biến thể rẻ nhất)</div>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">
                                        {{ number_format($reg->proposed_quantity) }}
                                    </td>
                                    <td>
                                        @if ($reg->status === 'pending')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                                <i class="fa-solid fa-clock me-1"></i>Chờ duyệt
                                            </span>
                                        @elseif ($reg->status === 'approved')
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                                <i class="fa-solid fa-circle-check me-1"></i>Đã duyệt
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                                <i class="fa-solid fa-circle-xmark me-1"></i>Từ chối
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($reg->rejection_reason)
                                            <span class="text-danger small" title="{{ $reg->rejection_reason }}">
                                                <i class="fa-solid fa-circle-exclamation me-1"></i>{{ Str::limit($reg->rejection_reason, 35) }}
                                            </span>
                                        @else
                                            <span class="text-muted small">--</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if ($reg->canBeCancelledBy(auth()->user()))
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-cancel-fs-reg"
                                                data-id="{{ $reg->id }}"
                                                data-url="{{ route('seller.flash-sale-registrations.destroy', $reg) }}">
                                                <i class="fa-solid fa-trash-can me-1"></i>Hủy
                                            </button>
                                        @else
                                            <span class="text-muted small">--</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="row-no-fs-reg">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                        Gian hàng chưa đăng ký sản phẩm Flash Sale nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
