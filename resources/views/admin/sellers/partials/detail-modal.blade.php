{{--
    Partial: Modal chi tiet Seller
    Mo bang JS: openSellerDetail(sellerObject)
    Tat ca [id="d*"] duoc dien boi sellers.js
--}}
<div class="modal fade" id="sellerDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            {{-- Header: logo + ten shop + slug --}}
            <div class="modal-header" style="background: linear-gradient(135deg, #c62828, #b71c1c);">
                <div class="d-flex align-items-center gap-3">
                    <div id="modalShopLogo" class="seller-logo-circle"></div>
                    <div>
                        <h5 class="modal-title text-white mb-0" id="modalShopName">--</h5>
                        <small class="text-white-50" id="modalShopSlug">--</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-0">

                {{-- Admin note (an neu khong co) --}}
                <div id="adminNoteWrap" class="admin-note-banner d-none">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    <span id="adminNoteText"></span>
                </div>

                {{-- Luoi 2 cot thong tin --}}
                <div class="seller-detail-grid">

                    {{-- Cot trai: Thong tin chu shop --}}
                    <div class="detail-section">
                        <div class="detail-section-title">Thông tin chủ shop</div>
                        <div class="detail-row">
                            <span class="detail-label">Họ tên</span>
                            <span class="detail-value" id="dOwnerName">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value" id="dOwnerEmail">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Loại hình</span>
                            <span class="detail-value" id="dBusinessType">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Lĩnh vực</span>
                            <span class="detail-value" id="dCategories">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Theo dõi</span>
                            <span class="detail-value" id="dFollowersCount">0 lượt</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Địa chỉ</span>
                            <span class="detail-value" id="dAddress">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">CCCD / MST</span>
                            <span class="detail-value" id="dNationalId">--</span>
                        </div>
                    </div>

                    {{-- Cot phai: Tai chinh & Ngan hang --}}
                    <div class="detail-section">
                        <div class="detail-section-title">Tài chính & Ngân hàng</div>
                        <div class="detail-row">
                            <span class="detail-label">Hoa hồng sàn</span>
                            <span class="detail-value" id="dCommission">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Số dư ví</span>
                            <span class="detail-value" id="dBalance">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Ngân hàng</span>
                            <span class="detail-value" id="dBankName">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Số tài khoản</span>
                            <span class="detail-value" id="dBankAccount">--</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Chủ tài khoản</span>
                            <span class="detail-value" id="dBankOwner">--</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer: trang thai hien tai + action buttons --}}
            <div class="modal-footer justify-content-between">
                <div>
                    <span class="me-2" style="font-size: 13px; color: #6c757d;">Trạng thái hiện tại:</span>
                    <span id="modalStatusBadge"></span>
                </div>
                <div class="d-flex gap-2" id="modalActionBtns">
                    {{-- Render boi JS tuy theo status --}}
                </div>
            </div>

        </div>
    </div>
</div>
