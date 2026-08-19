/**
 * =========================================================
 * CUPO ADMIN - SHIPPING MANAGEMENT JAVASCRIPT
 * =========================================================
 */

(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function showToast(msg, type = 'success') {
        const wrap = document.getElementById('adminToastContainer');
        if (!wrap) {
            alert(msg);
            return;
        }
        const id = 'toast_' + Date.now();
        const icon = type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation';
        const html = `
          <div id="${id}" class="toast align-items-center text-bg-${type} border-0 show" role="alert">
            <div class="d-flex">
              <div class="toast-body"><i class="fa-solid ${icon} me-2"></i>${msg}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
          </div>`;
        wrap.insertAdjacentHTML('beforeend', html);
        setTimeout(() => document.getElementById(id)?.remove(), 4000);
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);
    }

    // ==========================================
    // 1. CARRIERS MANAGEMENT (Index Page)
    // ==========================================
    const carriersWrap = document.getElementById('carriersGridWrap');
    if (carriersWrap) {
        // Toggle Carrier Active
        document.querySelectorAll('.btn-toggle-carrier').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const url = `/admin/shipping/carriers/${id}/toggle`;

                fetch(url, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(res => {
                        showToast(res.message, 'success');
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
            });
        });

        // Set Default Carrier
        document.querySelectorAll('.btn-set-default-carrier').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const url = `/admin/shipping/carriers/${id}/default`;

                fetch(url, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(res => {
                        showToast(res.message, 'success');
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
            });
        });

        // Open Edit Modal
        const editModal = document.getElementById('editCarrierModal');
        const bsEditModal = editModal ? new bootstrap.Modal(editModal) : null;
        const editForm = document.getElementById('editCarrierForm');

        document.querySelectorAll('.btn-edit-carrier').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const fee = this.dataset.fee;
                const days = this.dataset.days;
                const hotline = this.dataset.hotline;
                const desc = this.dataset.desc;

                document.getElementById('carrierModalId').value = id;
                document.getElementById('carrierModalName').textContent = name;
                document.getElementById('carrierModalFee').value = fee;
                document.getElementById('carrierModalDays').value = days;
                document.getElementById('carrierModalHotline').value = hotline || '';
                document.getElementById('carrierModalDesc').value = desc || '';

                if (bsEditModal) bsEditModal.show();
            });
        });

        // Submit Edit Carrier Form
        editForm?.addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('carrierModalId').value;
            const url = `/admin/shipping/carriers/${id}`;

            const payload = {
                base_fee: document.getElementById('carrierModalFee').value,
                estimated_days: document.getElementById('carrierModalDays').value,
                hotline: document.getElementById('carrierModalHotline').value,
                description: document.getElementById('carrierModalDesc').value,
            };

            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            })
                .then(r => r.json())
                .then(res => {
                    if (res.message) {
                        showToast(res.message, 'success');
                        if (bsEditModal) bsEditModal.hide();
                        setTimeout(() => window.location.reload(), 800);
                    } else if (res.errors) {
                        const firstErr = Object.values(res.errors)[0][0];
                        showToast(firstErr, 'danger');
                    }
                })
                .catch(() => showToast('Có lỗi xảy ra khi lưu thông tin.', 'danger'));
        });
    }

    // ==========================================
    // 2. SHIPMENTS / ORDERS LIST & SIMULATION
    // ==========================================
    const ordersTableBody = document.getElementById('shipmentsTableBody');
    if (ordersTableBody) {
        let currentPage = 1;
        let currentStatus = '';
        let currentCarrier = '';
        let currentSearch = '';

        function loadShipments(page = 1, status = '', carrierId = '', search = '') {
            ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>`;

            const params = new URLSearchParams({
                page: page,
                status: status,
                carrier_id: carrierId,
                search: search,
            });

            fetch(`/admin/shipping/orders?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(json => {
                    renderShipmentsTable(json.data);
                    renderPagination(json);
                    updateCounters(json.meta);
                })
                .catch(() => {
                    ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Không thể tải dữ liệu vận đơn.</td></tr>`;
                });
        }

        function renderShipmentsTable(items) {
            if (!items || items.length === 0) {
                ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-5"><i class="fa-solid fa-box-open fs-2 d-block mb-2 text-secondary"></i>Không có kiện hàng nào phù hợp</td></tr>`;
                return;
            }

            ordersTableBody.innerHTML = items.map((item, idx) => {
                const statusBadges = {
                    'pending': '<span class="badge bg-secondary">Chờ xác nhận</span>',
                    'confirmed': '<span class="badge bg-info text-dark">Chuẩn bị hàng</span>',
                    'shipping': '<span class="badge bg-primary">Đang vận chuyển</span>',
                    'completed': '<span class="badge bg-success">Đã giao hàng</span>',
                    'cancelled': '<span class="badge bg-danger">Đã hủy</span>',
                };

                const carrierName = item.carrier?.name || 'SPX Express';
                const trackingCode = item.tracking_number ? `<span class="fw-semibold text-primary">${escHtml(item.tracking_number)}</span>` : '<span class="text-muted small">Chưa phát hành</span>';

                return `
                  <tr>
                    <td>${idx + 1}</td>
                    <td>
                      <div class="fw-bold text-dark">${escHtml(item.order?.order_number || 'N/A')}</div>
                      <div class="small text-muted">${trackingCode}</div>
                    </td>
                    <td>
                      <div class="fw-semibold text-dark">${escHtml(item.seller?.seller_profile?.shop_name || item.seller?.name || 'N/A')}</div>
                      <div class="small text-muted text-truncate" style="max-width: 200px;">${escHtml(item.seller?.seller_profile?.address || '')}</div>
                    </td>
                    <td>
                      <div class="fw-semibold text-dark">${escHtml(item.order?.shipping_name || 'Khách hàng')}</div>
                      <div class="small text-muted">${escHtml(item.order?.shipping_phone || '')}</div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border">${escHtml(carrierName)}</span>
                      <div class="small text-muted mt-1">${formatMoney(item.shipping_fee)}</div>
                    </td>
                    <td>${statusBadges[item.status] || item.status}</td>
                    <td>
                      <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-view-tracking" data-id="${item.id}" title="Xem hành trình bưu cục">
                          <i class="fa-solid fa-route me-1"></i>Hành trình
                        </button>
                        ${item.status !== 'completed' && item.status !== 'cancelled' ? `
                          <button type="button" class="btn btn-sm btn-outline-success btn-simulate-step" data-id="${item.id}" title="Mô phỏng bước tiếp theo">
                            <i class="fa-solid fa-forward-step"></i>
                          </button>
                        ` : ''}
                      </div>
                    </td>
                  </tr>
                `;
            }).join('');

            // Bind click events
            ordersTableBody.querySelectorAll('.btn-view-tracking').forEach(btn => {
                btn.addEventListener('click', () => openTrackingModal(btn.dataset.id));
            });

            ordersTableBody.querySelectorAll('.btn-simulate-step').forEach(btn => {
                btn.addEventListener('click', () => simulateStep(btn.dataset.id));
            });
        }

        function renderPagination(json) {
            const wrap = document.getElementById('shipmentsPaginationWrap');
            if (!wrap) return;
            const { current_page, last_page, from, to, total } = json;
            if (total === 0) {
                wrap.style.display = 'none';
                return;
            }
            wrap.style.display = 'flex';
            document.getElementById('shipmentsPaginationInfo').textContent = `Hiển thị ${from ?? 0}–${to ?? 0} / ${total} kiện hàng`;

            let pagesHtml = '';
            for (let p = 1; p <= last_page; p++) {
                pagesHtml += `<button type="button" class="btn btn-sm ${p === current_page ? 'btn-primary' : 'btn-outline-secondary'} page-btn" data-page="${p}">${p}</button>`;
            }
            const linksWrap = document.getElementById('shipmentsPaginationLinks');
            linksWrap.innerHTML = pagesHtml;

            linksWrap.querySelectorAll('.page-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentPage = parseInt(btn.dataset.page);
                    loadShipments(currentPage, currentStatus, currentCarrier, currentSearch);
                });
            });
        }

        function updateCounters(meta) {
            if (!meta) return;
            if (document.getElementById('countAll')) document.getElementById('countAll').textContent = meta.total_all;
            if (document.getElementById('countPending')) document.getElementById('countPending').textContent = meta.total_pending;
            if (document.getElementById('countConfirmed')) document.getElementById('countConfirmed').textContent = meta.total_confirmed;
            if (document.getElementById('countShipping')) document.getElementById('countShipping').textContent = meta.total_shipping;
            if (document.getElementById('countCompleted')) document.getElementById('countCompleted').textContent = meta.total_completed;
        }

        // Filter tabs
        document.querySelectorAll('.shipment-filter-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.shipment-filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentStatus = this.dataset.status || '';
                currentPage = 1;
                loadShipments(currentPage, currentStatus, currentCarrier, currentSearch);
            });
        });

        // Search and Carrier Select
        document.getElementById('carrierFilterSelect')?.addEventListener('change', function () {
            currentCarrier = this.value;
            currentPage = 1;
            loadShipments(currentPage, currentStatus, currentCarrier, currentSearch);
        });

        document.getElementById('searchShipmentInput')?.addEventListener('input', function () {
            currentSearch = this.value.trim();
            currentPage = 1;
            loadShipments(currentPage, currentStatus, currentCarrier, currentSearch);
        });

        // Initial Load
        loadShipments(1, '', '', '');
    }

    // ==========================================
    // 3. TRACKING MODAL & 1-CLICK SIMULATE
    // ==========================================
    const trackingModalEl = document.getElementById('trackingModal');
    const bsTrackingModal = trackingModalEl ? new bootstrap.Modal(trackingModalEl) : null;

    function openTrackingModal(sellerOrderId) {
        if (!bsTrackingModal) return;
        const timelineWrap = document.getElementById('modalTimelineContent');
        timelineWrap.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>`;
        bsTrackingModal.show();

        fetch(`/admin/shipping/tracking/${sellerOrderId}`, {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                document.getElementById('modalOrderNum').textContent = data.order_number;
                document.getElementById('modalTrackingCode').textContent = data.tracking_number;
                document.getElementById('modalCarrierName').textContent = data.carrier_name;
                document.getElementById('modalRecipientName').textContent = data.recipient.name + ' - ' + data.recipient.phone;
                document.getElementById('modalRecipientAddr').textContent = data.recipient.address;

                renderTrackingTimeline(data.timeline, data.status);
            })
            .catch(() => {
                timelineWrap.innerHTML = `<div class="text-danger text-center py-4">Không thể tải thông tin hành trình.</div>`;
            });
    }

    function renderTrackingTimeline(timeline, currentStatus) {
        const wrap = document.getElementById('modalTimelineContent');
        if (!timeline || timeline.length === 0) {
            wrap.innerHTML = `<div class="text-muted text-center py-3">Chưa có bản ghi lộ trình nào.</div>`;
            return;
        }

        wrap.innerHTML = timeline.map((item, idx) => {
            const isFirst = idx === 0; // Mốc mới nhất
            return `
              <div class="timeline-item ${isFirst ? 'active' : ''}">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="timeline-title">${escHtml(item.title)}</span>
                    <span class="timeline-time">${escHtml(item.time)}</span>
                  </div>
                  <div class="timeline-desc">${escHtml(item.description || '')}</div>
                  ${item.location ? `<div class="small text-muted"><i class="fa-solid fa-location-dot me-1 text-danger"></i>${escHtml(item.location)}</div>` : ''}
                </div>
              </div>
            `;
        }).join('');
    }

    function simulateStep(sellerOrderId) {
        fetch(`/admin/shipping/simulate/${sellerOrderId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(res => {
                showToast(res.message, 'success');
                // Reload list
                const search = document.getElementById('searchShipmentInput')?.value.trim() || '';
                const carrier = document.getElementById('carrierFilterSelect')?.value || '';
                const status = document.querySelector('.shipment-filter-tab.active')?.dataset.status || '';
                if (typeof loadShipments === 'function') {
                    loadShipments(1, status, carrier, search);
                }
            })
            .catch(() => showToast('Có lỗi khi mô phỏng hành trình.', 'danger'));
    }

})();
