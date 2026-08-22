<div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white p-4">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="trackingModalLabel">
                        <i class="fa-solid fa-route me-2"></i>Hành trình vận đơn: <span id="modalTrackingCode" class="text-warning"></span>
                    </h5>
                    <div class="small text-white-50">
                        Đơn hàng: <span id="modalOrderNum" class="fw-semibold text-white"></span> | Đơn vị: <span id="modalCarrierName" class="badge bg-light text-dark ms-1"></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Info Summary Box -->
                <div class="p-3 bg-light rounded-3 mb-4">
                    <div class="row g-2 small">
                        <div class="col-md-6">
                            <span class="text-muted">Người nhận:</span> <strong id="modalRecipientName" class="text-dark"></strong>
                        </div>
                        <div class="col-md-12">
                            <span class="text-muted">Địa chỉ nhận:</span> <span id="modalRecipientAddr" class="text-dark"></span>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i>Lịch sử luân chuyển bưu kiện:
                </h6>

                <!-- Vertical Timeline -->
                <div id="modalTimelineContent" class="tracking-timeline">
                    <!-- Loaded dynamically via JS -->
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
