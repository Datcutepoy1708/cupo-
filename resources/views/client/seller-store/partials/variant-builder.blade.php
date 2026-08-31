{{--
    Variant Builder Component (Shopee / TikTok Shop Style)
    Usage: @include('client.seller-store.partials.variant-builder', ['prefix' => 'add' | 'edit'])
--}}
<div class="variant-builder-section border rounded-3 p-3 mb-3 bg-light-subtle" id="{{ $prefix }}_variant_section">
    {{-- 1. Switch Bật / Tắt biến thể --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-layer-group text-danger fs-5"></i>
            <div>
                <span class="fw-bold text-dark d-block">Phân loại hàng (Biến thể)</span>
                <small class="text-muted">Bật nếu sản phẩm có nhiều màu sắc, kích cỡ, dung lượng...</small>
            </div>
        </div>
        <div class="form-check form-switch fs-5 m-0">
            <input class="form-check-input variant-toggle-switch" type="checkbox" role="switch" 
                id="{{ $prefix }}_has_variants_toggle" name="has_variants" value="1">
        </div>
    </div>

    {{-- 2. Khối nội dung cấu hình biến thể (Chỉ hiện khi Switch = ON) --}}
    <div class="variant-config-wrap d-none mt-3" id="{{ $prefix }}_variant_config_wrap">
        
        {{-- NHÓM PHÂN LOẠI 1 --}}
        <div class="card border mb-3 shadow-none bg-white">
            <div class="card-body p-3">
                <div class="row align-items-center mb-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-secondary mb-1">Tên nhóm phân loại 1:</label>
                        <input type="text" class="form-control form-control-sm variant-group-name" 
                            id="{{ $prefix }}_group1_name" value="Màu sắc" placeholder="Ví dụ: Màu sắc, Mẫu...">
                    </div>
                    <div class="col-md-8 text-end">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                            <i class="fa-solid fa-image me-1"></i>Hỗ trợ tải ảnh cho từng phân loại màu sắc
                        </span>
                    </div>
                </div>

                <div class="variant-group-values-wrap mt-2">
                    <label class="form-label small text-muted mb-1">Các giá trị phân loại:</label>
                    <div class="d-flex flex-column gap-2" id="{{ $prefix }}_group1_items_container">
                        {{-- Items sinh bởi JS --}}
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-add-group1-val" id="{{ $prefix }}_btn_add_group1_val">
                        <i class="fa-solid fa-plus me-1"></i> Thêm giá trị phân loại
                    </button>
                </div>
            </div>
        </div>

        {{-- NHÓM PHÂN LOẠI 2 (TÙY CHỌN) --}}
        <div class="card border mb-3 shadow-none bg-white d-none" id="{{ $prefix }}_group2_card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div style="max-width: 250px;">
                        <label class="form-label fw-bold small text-secondary mb-1">Tên nhóm phân loại 2:</label>
                        <input type="text" class="form-control form-control-sm variant-group-name" 
                            id="{{ $prefix }}_group2_name" value="Kích cỡ" placeholder="Ví dụ: Kích cỡ, Size...">
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm text-danger border-0" id="{{ $prefix }}_btn_remove_group2" title="Xóa nhóm 2">
                        <i class="fa-solid fa-trash me-1"></i> Xóa nhóm 2
                    </button>
                </div>

                <div class="variant-group-values-wrap mt-2">
                    <label class="form-label small text-muted mb-1">Các giá trị phân loại:</label>
                    <div class="d-flex flex-wrap gap-2 align-items-center" id="{{ $prefix }}_group2_items_container">
                        {{-- Items sinh bởi JS --}}
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-add-group2-val" id="{{ $prefix }}_btn_add_group2_val">
                        <i class="fa-solid fa-plus me-1"></i> Thêm giá trị phân loại
                    </button>
                </div>
            </div>
        </div>

        {{-- Nút Thêm Nhóm Phân Loại 2 --}}
        <div class="mb-3" id="{{ $prefix }}_add_group2_btn_wrap">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="{{ $prefix }}_btn_show_group2">
                <i class="fa-solid fa-plus me-1"></i> Thêm nhóm phân loại 2 (Size, Kích cỡ, Dung tích...)
            </button>
        </div>

        {{-- 3. BẢNG MA TRẬN BIẾN THỂ & THANH CÔNG CỤ ÁP DỤNG HÀNG LOẠT --}}
        <div class="variant-matrix-box border rounded-3 p-3 bg-white" id="{{ $prefix }}_matrix_box">
            
            {{-- Fast Fill / Batch Apply Toolbar --}}
            <div class="batch-apply-bar p-2 rounded-2 mb-3 bg-light border">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="fw-bold small text-danger text-nowrap">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Áp dụng hàng loạt:
                    </div>
                    <div class="input-group input-group-sm" style="max-width: 140px;">
                        <input type="number" class="form-control" id="{{ $prefix }}_batch_price" placeholder="Giá gốc" min="0" step="1000">
                        <span class="input-group-text">₫</span>
                    </div>
                    <div class="input-group input-group-sm" style="max-width: 140px;">
                        <input type="number" class="form-control" id="{{ $prefix }}_batch_sale_price" placeholder="Giá sale" min="0" step="1000">
                        <span class="input-group-text">₫</span>
                    </div>
                    <div class="input-group input-group-sm" style="max-width: 110px;">
                        <input type="number" class="form-control" id="{{ $prefix }}_batch_stock" placeholder="Kho" min="0">
                    </div>
                    <div class="input-group input-group-sm" style="max-width: 130px;">
                        <input type="text" class="form-control" id="{{ $prefix }}_batch_sku" placeholder="SKU tiền tố">
                    </div>
                    <button type="button" class="btn btn-danger btn-sm px-3" id="{{ $prefix }}_btn_batch_apply">
                        Áp dụng cho tất cả
                    </button>
                </div>
            </div>

            {{-- Table Matrix --}}
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle text-center variant-matrix-table mb-2">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 55px;">Ảnh</th>
                            <th id="{{ $prefix }}_th_group1">Màu sắc</th>
                            <th id="{{ $prefix }}_th_group2" class="d-none">Kích cỡ</th>
                            <th style="width: 150px;">Giá niêm yết (₫) <span class="text-danger">*</span></th>
                            <th style="width: 150px;">Giá khuyến mãi (₫)</th>
                            <th style="width: 110px;">Kho hàng <span class="text-danger">*</span></th>
                            <th style="width: 140px;">Mã SKU</th>
                        </tr>
                    </thead>
                    <tbody id="{{ $prefix }}_matrix_tbody">
                        {{-- Rows generated dynamically by JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Summary stats --}}
            <div class="d-flex justify-content-between align-items-center small text-muted px-1 mt-2">
                <div>
                    <span class="me-3">Số lượng biến thể: <strong class="text-dark" id="{{ $prefix }}_variants_count">0</strong></span>
                    <span>Tổng kho: <strong class="text-danger" id="{{ $prefix }}_total_stock">0</strong></span>
                </div>
                <div>
                    <span>Khoảng giá: <strong class="text-danger" id="{{ $prefix }}_price_range">0₫</strong></span>
                </div>
            </div>

        </div>

    </div>
</div>
