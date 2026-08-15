/**
 * CUPO ADMIN — Trang Quan ly Don Hang (orders.js)
 *
 * Trang INDEX: AJAX load danh sach, tab filter payment_status,
 *              loc seller_order_status, date range, tim kiem, export CSV, phan trang.
 *
 * Trang SHOW:  Cap nhat trang thai tung seller_order (PATCH) via AJAX.
 *
 * Data context tu data-* tren #ordersAppConfig (Rule 24 AGENT.md - khong inline script)
 */

(function () {
    'use strict';

    const cfgEl = document.getElementById('ordersAppConfig');
    if (!cfgEl) return;
    const cfg = cfgEl.dataset;

    const ROUTES = {
        index:     cfg.indexUrl,
        export:    cfg.exportUrl,
        showBase:  cfg.showUrl,            // chua '__ID__'
        updateStatusBase: cfg.updateStatusUrl, // chua '__ID__'
        csrf:      cfg.csrf,
    };

    const PAGE = cfgEl.dataset.indexUrl ? 'index' : 'show';

    // =====================================================================
    // TRANG INDEX
    // =====================================================================
    if (ROUTES.index && document.getElementById('ordersTable')) {
        initIndexPage();
    }

    // =====================================================================
    // TRANG SHOW
    // =====================================================================
    if (document.getElementById('sellerOrdersAccordion')) {
        initShowPage();
    }

    /* ==================================================================
       INDEX PAGE LOGIC
       ================================================================== */
    function initIndexPage() {
        let currentPaymentStatus = '';
        let currentPage = 1;
        let searchTimer = null;

        const tbody = document.getElementById('ordersTableBody');
        const paginationWrap  = document.getElementById('ordersPaginationWrap');
        const paginationInfo  = document.getElementById('ordersPaginationInfo');
        const paginationLinks = document.getElementById('ordersPaginationLinks');
        const searchInput     = document.getElementById('orderSearchInput');
        const sellerStatusFilter = document.getElementById('sellerOrderStatusFilter');
        const dateFromInput   = document.getElementById('dateFromFilter');
        const dateToInput     = document.getElementById('dateToFilter');
        const btnExport       = document.getElementById('btnExportOrders');
        const btnRefresh      = document.getElementById('btnRefreshOrders');
        const toast           = document.getElementById('ordersToast')
            ? new bootstrap.Toast(document.getElementById('ordersToast'), { delay: 3000 })
            : null;

        // --- Tab payment_status ---
        document.querySelectorAll('#paymentStatusTabs .seller-tab').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('#paymentStatusTabs .seller-tab').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentPaymentStatus = this.dataset.paymentStatus || '';
                loadOrders(1);
            });
        });

        // --- Bo loc seller_order_status ---
        sellerStatusFilter?.addEventListener('change', () => loadOrders(1));

        // --- Date range ---
        dateFromInput?.addEventListener('change', () => loadOrders(1));
        dateToInput?.addEventListener('change', () => loadOrders(1));

        // --- Tim kiem (debounce 350ms) ---
        searchInput?.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadOrders(1), 350);
        });

        // --- Lam moi ---
        btnRefresh?.addEventListener('click', () => loadOrders(currentPage));

        // --- Export CSV ---
        btnExport?.addEventListener('click', function (e) {
            e.preventDefault();
            const qs = buildQueryString(1);
            window.location.href = ROUTES.export + qs;
        });

        // --- Phan trang ---
        document.addEventListener('click', function (e) {
            const pageBtn = e.target.closest('[data-page]');
            if (!pageBtn) return;
            const page = parseInt(pageBtn.dataset.page, 10);
            if (!isNaN(page)) loadOrders(page);
        });

        // Load lan dau
        loadOrders(1);

        function buildQueryString(page) {
            let qs = '?page=' + page;
            if (currentPaymentStatus) qs += '&payment_status=' + currentPaymentStatus;
            const soStatus = sellerStatusFilter?.value;
            if (soStatus) qs += '&seller_order_status=' + soStatus;
            const from = dateFromInput?.value;
            const to   = dateToInput?.value;
            if (from) qs += '&date_from=' + from;
            if (to)   qs += '&date_to=' + to;
            const kw = searchInput?.value.trim();
            if (kw) qs += '&q=' + encodeURIComponent(kw);
            return qs;
        }

        function loadOrders(page) {
            currentPage = page;
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>Đang tải...
            </td></tr>`;

            const url = ROUTES.index + buildQueryString(page);

            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': ROUTES.csrf },
            })
                .then(r => r.json())
                .then(json => {
                    renderTable(json.data || []);
                    renderPagination(json);
                    updateStatCards(json.meta || {});
                    updateTabBadges(json.meta || {});
                })
                .catch(() => {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Lỗi tải dữ liệu. Vui lòng thử lại.
                    </td></tr>`;
                });
        }

        function renderTable(orders) {
            if (!orders.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-box-open fa-2x mb-3 opacity-25"></i>
                    <p class="mb-0">Không tìm thấy đơn hàng nào.</p>
                </td></tr>`;
                return;
            }

            tbody.innerHTML = orders.map(o => {
                const paymentCls = {
                    pending: 'payment-badge-pending',
                    paid: 'payment-badge-paid',
                    failed: 'payment-badge-failed',
                    refunded: 'payment-badge-refunded',
                }[o.payment_status] || 'bg-secondary text-white';

                const paymentLabel = {
                    pending: 'Chờ TT',
                    paid: 'Đã TT',
                    failed: 'TT lỗi',
                    refunded: 'Hoàn tiền',
                }[o.payment_status] || o.payment_status;

                const showUrl = ROUTES.showBase
                    ? ROUTES.showBase.replace('__ID__', o.id)
                    : '#';

                return `<tr>
                    <td class="ps-4">
                        <a href="${showUrl}" class="order-number-link">#${escapeHtml(o.order_number)}</a>
                    </td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(o.shipping_name)}</div>
                        <div class="text-muted small">${escapeHtml(o.shipping_phone)}</div>
                    </td>
                    <td class="fw-bold">${formatCurrency(o.grand_total)}</td>
                    <td>
                        <span class="badge ${paymentCls} px-2 py-1">${paymentLabel}</span>
                    </td>
                    <td>
                        <span class="badge bg-secondary rounded-pill">${o.seller_orders_count ?? (o.seller_orders?.length ?? 0)} Seller</span>
                    </td>
                    <td class="text-muted small">${formatDate(o.created_at)}</td>
                    <td class="text-end pe-4">
                        <a href="${showUrl}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>`;
            }).join('');
        }

        function renderPagination(json) {
            if (!json.last_page || json.last_page <= 1) {
                paginationWrap.style.display = 'none';
                return;
            }
            paginationWrap.style.display = '';
            const from = json.from ?? 0;
            const to   = json.to ?? 0;
            const total = json.total ?? 0;
            paginationInfo.textContent = `Hiển thị ${from} - ${to} trong ${total} kết quả`;

            const btns = [];
            if (json.current_page > 1) {
                btns.push(`<button class="btn btn-sm btn-outline-secondary" data-page="${json.current_page - 1}">‹</button>`);
            }
            for (let p = Math.max(1, json.current_page - 2); p <= Math.min(json.last_page, json.current_page + 2); p++) {
                const active = p === json.current_page ? 'btn-primary' : 'btn-outline-secondary';
                btns.push(`<button class="btn btn-sm ${active}" data-page="${p}">${p}</button>`);
            }
            if (json.current_page < json.last_page) {
                btns.push(`<button class="btn btn-sm btn-outline-secondary" data-page="${json.current_page + 1}">›</button>`);
            }
            paginationLinks.innerHTML = `<div class="d-flex gap-1">${btns.join('')}</div>`;
        }

        function updateStatCards(meta) {
            setEl('order-count-all', meta.total_all);
            setEl('order-count-pending-payment', meta.payment_pending);
            setEl('order-count-shipping', meta.seller_order_shipping);
            setEl('order-count-completed', meta.seller_order_completed);
        }

        function updateTabBadges(meta) {
            setEl('tab-badge-payment-pending', meta.payment_pending ?? 0);
            setEl('tab-badge-payment-paid', meta.payment_paid ?? 0);
            setEl('tab-badge-payment-failed', meta.payment_failed ?? 0);
            setEl('tab-badge-payment-refunded', meta.payment_refunded ?? 0);
        }
    }

    /* ==================================================================
       SHOW PAGE LOGIC
       ================================================================== */
    function initShowPage() {
        const toastEl = document.getElementById('ordersToast');
        const toast   = toastEl ? new bootstrap.Toast(toastEl, { delay: 3500 }) : null;
        const csrf    = ROUTES.csrf;

        // Hien/an tracking & cancel reason theo trang thai chon
        document.querySelectorAll('.seller-order-status-select').forEach(select => {
            select.addEventListener('change', function () {
                const wrap = this.closest('.d-flex');
                const trackWrap  = wrap.querySelector('.tracking-input-wrap');
                const cancelWrap = wrap.querySelector('.cancel-reason-wrap');
                if (trackWrap)  trackWrap.style.display  = this.value === 'shipping'  ? '' : 'none';
                if (cancelWrap) cancelWrap.style.display = this.value === 'cancelled' ? '' : 'none';
            });

            // Trigger khi load de khoi tao trang thai dung neu dang o shipping
            select.dispatchEvent(new Event('change'));
        });

        // Xu ly nut Luu
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-update-seller-order-status');
            if (!btn) return;

            const sellerOrderId = btn.dataset.sellerOrderId;
            const wrap   = btn.closest('.d-flex');
            const select = wrap.querySelector('.seller-order-status-select');
            const trackInput  = wrap.querySelector('.tracking-number-input');
            const cancelInput = wrap.querySelector('.cancel-reason-input');
            const errEl = document.getElementById('statusError' + sellerOrderId);

            if (errEl) errEl.textContent = '';

            const status = select?.value;
            const tracking = trackInput?.value?.trim() || null;
            const cancelReason = cancelInput?.value?.trim() || null;

            const url = cfg.updateStatusUrl?.replace('__ID__', sellerOrderId);
            if (!url) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang lưu...';

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    status,
                    tracking_number: tracking,
                    cancel_reason: cancelReason,
                }),
            })
                .then(async r => {
                    const data = await r.json();
                    if (r.status === 422) {
                        const msgs = Object.values(data.errors || {}).flat().join(' ');
                        if (errEl) errEl.textContent = msgs;
                        return null;
                    }
                    if (!r.ok || !data.success) throw data.message || 'Lỗi cập nhật.';
                    return data;
                })
                .then(data => {
                    if (!data) return;
                    showToast(toast, 'Cập nhật trạng thái thành công!', 'success');
                    // Cap nhat badge tren accordion header
                    const card = document.getElementById('sellerOrderCard' + sellerOrderId);
                    if (card) {
                        const badge = card.querySelector('.badge');
                        if (badge) {
                            const statusLabels = {
                                pending: 'Chờ xác nhận', confirmed: 'Đã xác nhận',
                                shipping: 'Đang giao', completed: 'Hoàn thành', cancelled: 'Đã hủy',
                            };
                            const statusClass = {
                                pending: 'bg-warning text-dark', confirmed: 'bg-info text-dark',
                                shipping: 'bg-primary', completed: 'bg-success', cancelled: 'bg-danger',
                            };
                            badge.textContent = statusLabels[status] || status;
                            badge.className = 'badge ms-2 ' + (statusClass[status] || 'bg-secondary');
                        }
                    }
                })
                .catch(msg => showToast(toast, String(msg), 'danger'))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu';
                });
        });
    }

    /* ==================================================================
       HELPERS
       ================================================================== */
    function setEl(id, val) {
        const el = document.getElementById(id);
        if (el && val !== undefined) el.textContent = val;
    }

    function formatCurrency(num) {
        return parseInt(num ?? 0).toLocaleString('vi-VN') + 'đ';
    }

    function formatDate(dateStr) {
        if (!dateStr) return '--';
        const d = new Date(dateStr);
        const pad = n => String(n).padStart(2, '0');
        return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function showToast(toast, message, type = 'success') {
        const toastEl = document.getElementById('ordersToast');
        if (!toastEl) return;
        toastEl.className = `toast align-items-center text-white border-0 bg-${type === 'success' ? 'success' : 'danger'}`;
        const body = document.getElementById('ordersToastMessage');
        if (body) body.innerHTML = `<i class="fa-solid fa-${type === 'success' ? 'circle-check' : 'triangle-exclamation'} me-2"></i>${escapeHtml(message)}`;
        if (toast) toast.show();
    }

})();
