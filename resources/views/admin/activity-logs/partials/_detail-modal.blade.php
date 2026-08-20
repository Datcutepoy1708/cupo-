<div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="logDetailModalLabel">
                        <i class="fa-solid fa-shield-halved text-warning me-2"></i>Chi tiết Nhật ký Kiểm toán #<span id="modalLogId"></span>
                    </h5>
                    <div class="small text-white-50">
                        Thời gian: <span id="modalLogTime" class="text-white fw-semibold"></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- User & Action Summary -->
                <div class="p-3 bg-light rounded-3 mb-4 border">
                    <div class="row g-3 small">
                        <div class="col-md-6">
                            <span class="text-muted d-block">Nhân viên thực hiện:</span>
                            <strong id="modalLogUser" class="text-dark fs-6"></strong>
                            <div id="modalLogUserRole" class="mt-1"></div>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Phân hệ / Hành động:</span>
                            <span id="modalLogModule" class="badge me-1"></span>
                            <span id="modalLogAction" class="badge bg-secondary font-monospace"></span>
                        </div>
                        <div class="col-md-12 border-top pt-2">
                            <span class="text-muted d-block">Mô tả hành động:</span>
                            <div id="modalLogDesc" class="fw-semibold text-dark"></div>
                        </div>
                        <div class="col-md-6 border-top pt-2">
                            <span class="text-muted d-block">Địa chỉ IP:</span>
                            <code id="modalLogIp" class="text-primary fw-bold"></code>
                        </div>
                        <div class="col-md-6 border-top pt-2">
                            <span class="text-muted d-block">Thiết bị / Trình duyệt:</span>
                            <span id="modalLogUserAgent" class="text-muted small text-truncate d-block" style="max-width: 320px;"></span>
                        </div>
                    </div>
                </div>

                <!-- Properties JSON Viewer -->
                <h6 class="fw-bold text-dark mb-2">
                    <i class="fa-solid fa-code me-1 text-primary"></i>Dữ liệu thay đổi / Request Payload (JSON):
                </h6>
                <pre id="modalLogProperties" class="json-viewer-box"></pre>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
