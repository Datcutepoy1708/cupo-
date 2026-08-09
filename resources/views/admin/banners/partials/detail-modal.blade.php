{{--
    Partial: Modal Chi tiết Banner (Admin)
--}}
<div class="modal fade" id="bannerDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background: linear-gradient(135deg, #c62828, #b71c1c);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-image text-white fs-5 me-2"></i>
                    <h5 class="modal-title text-white mb-0" id="detailBannerTitle">Chi tiết Banner</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                {{-- Banner preview image container --}}
                <div class="banner-preview-box">
                    <img id="detailBannerImg" src="" alt="Banner Preview" class="img-fluid rounded">
                </div>

                {{-- Details list --}}
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold uppercase d-block mb-1">TIÊU ĐỀ BANNER</label>
                            <div class="fw-semibold fs-6" id="dTitle">--</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold uppercase d-block mb-1">VỊ TRÍ HIỂN THỊ</label>
                            <div id="dPositionBadge">--</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold uppercase d-block mb-1">ĐƯỜNG DẪN ĐÍCH (URL)</label>
                            <div class="text-break" id="dLinkUrl">--</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold uppercase d-block mb-1">THỨ TỰ ƯU TIÊN</label>
                            <div class="fw-semibold" id="dSortOrder">0</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold uppercase d-block mb-1">THỜI GIAN BẮT ĐẦU</label>
                            <div class="text-secondary" id="dStartsAt">--</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold uppercase d-block mb-1">THỜI GIAN KẾT THÚC</label>
                            <div class="text-secondary" id="dEndsAt">--</div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer justify-content-between">
                <div>
                    <span class="me-2 text-muted small">Trạng thái:</span>
                    <span id="detailStatusBadge"></span>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>

        </div>
    </div>
</div>
