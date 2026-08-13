{{--
    Partial: Modal chi tiết Sản phẩm (Admin)
    Điền tự động bởi products.js: openProductDetail(productObject)
--}}
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            {{-- Header: thumbnail + ten san pham + SKU --}}
            <div class="modal-header" style="background: linear-gradient(135deg, #c62828, #b71c1c);">
                <div class="d-flex align-items-center gap-3">
                    <div id="modalProductThumb" class="product-thumb-circle"></div>
                    <div>
                        <h5 class="modal-title text-white mb-0" id="modalProductName">--</h5>
                        <small class="text-white-50" id="modalProductSku">SKU: --</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-0">

                {{-- Admin note banner (neu co) --}}
                <div id="adminNoteWrap" class="admin-note-banner d-none">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    <span id="adminNoteText"></span>
                </div>

                {{-- Lưới 2 cột thông tin --}}
                <div class="product-detail-grid">

                    {{-- Cột trái: Thông tin sản phẩm --}}
                    <div class="detail-section">
                        <div class="detail-section-title">Thông tin sản phẩm</div>
                        <div class="detail-row">
                            <span class="detail-label">Tên sản phẩm</span>
                            <span class="detail-value" id="dProductName">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Mã SKU</span>
                            <span class="detail-value" id="dProductSku">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Danh mục</span>
                            <span class="detail-value" id="dCategory">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Giá bán</span>
                            <span class="detail-value fw-bold text-danger" id="dPrice">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tồn kho</span>
                            <span class="detail-value" id="dStock">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phân loại</span>
                            <span class="detail-value" id="dHasVariants">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Lượt xem</span>
                            <span class="detail-value" id="dViewsCount">0 lượt</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Ngày tạo</span>
                            <span class="detail-value" id="dCreatedAt">--</span>
                        </div>
                    </div>

                    {{-- Cột phải: Gian hàng & Người bán --}}
                    <div class="detail-section">
                        <div class="detail-section-title">Gian hàng & Người bán</div>
                        <div class="detail-row">
                            <span class="detail-label">Tên gian hàng</span>
                            <span class="detail-value fw-semibold" id="dShopName">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Chủ shop</span>
                            <span class="detail-value" id="dSellerName">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value" id="dSellerEmail">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Mô tả ngắn</span>
                            <span class="detail-value" id="dShortDesc">--</span>
                        </div>
                    </div>

                </div>

                {{-- Mô tả chi tiết --}}
                <div class="px-4 py-3 border-top">
                    <div class="detail-section-title mb-2">Mô tả chi tiết</div>
                    <div class="p-3 bg-light rounded text-secondary" style="font-size: 13px; max-height: 320px; overflow-y: auto;" id="dFullDesc">
                        --
                    </div>
                </div>

            </div>

            {{-- Footer: trạng thái hiện tại + action buttons --}}
            <div class="modal-footer justify-content-between">
                <div>
                    <span class="me-2" style="font-size: 13px; color: #6c757d;">Trạng thái hiện tại:</span>
                    <span id="modalStatusBadge"></span>
                </div>
                <div class="d-flex gap-2" id="modalActionBtns">
                    {{-- Render bởi JS tùy theo status --}}
                </div>
            </div>

        </div>
    </div>
</div>
