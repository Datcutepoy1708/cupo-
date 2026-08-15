<div class="modal fade" id="flashSaleRegistrationsModal" tabindex="-1" aria-labelledby="flashSaleRegistrationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <div>
                    <h5 class="modal-header-title mb-1" id="flashSaleRegistrationsModalLabel">Danh sach Dang ky Flash Sale</h5>
                    <span class="text-muted small" id="registrationsSessionTitle"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Stat badges --}}
                <div class="d-flex gap-3 mb-3" id="registrationStatBadges">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">
                        Cho duyet: <strong id="regCountPending">0</strong>
                    </span>
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2">
                        Da duyet: <strong id="regCountApproved">0</strong>
                    </span>
                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-3 py-2">
                        Da tu choi: <strong id="regCountRejected">0</strong>
                    </span>
                </div>

                {{-- Tab filter --}}
                <ul class="nav nav-tabs mb-3" id="regTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-status="">Tat ca</button></li>
                    <li class="nav-item"><button class="nav-link" data-status="pending">Cho duyet</button></li>
                    <li class="nav-item"><button class="nav-link" data-status="approved">Da duyet</button></li>
                    <li class="nav-item"><button class="nav-link" data-status="rejected">Da tu choi</button></li>
                </ul>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="registrationsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Seller</th>
                                <th>San pham</th>
                                <th>Gia de xuat</th>
                                <th>So luong</th>
                                <th>Trang thai</th>
                                <th>Hanh dong</th>
                            </tr>
                        </thead>
                        <tbody id="registrationsTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Dang tai du lieu...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Reject reason form (hidden by default, shown inline) --}}
                <div id="rejectReasonBox" class="mt-3 p-3 bg-light rounded-3 border d-none">
                    <label class="form-label fw-semibold">Ly do tu choi <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejectReasonInput" rows="2" placeholder="Nhap ly do tu choi (toi thieu 5 ky tu)..."></textarea>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-danger" id="btnConfirmReject">Xac nhan Tu choi</button>
                        <button type="button" class="btn btn-sm btn-light" id="btnCancelReject">Huy</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Dong</button>
            </div>
        </div>
    </div>
</div>
