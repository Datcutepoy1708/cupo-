/**
 * disputes.js — Admin Dispute Management JavaScript
 * Dùng cho: /admin/disputes (index & show pages)
 *
 * Không có inline script / inline style (Rule 24).
 * Tất cả URL đọc từ #disputesAppConfig hoặc #disputeShowConfig data attributes.
 */

(function () {
    'use strict';

    /* =====================================================
       SHARED HELPERS
    ===================================================== */

    function showToast(msg, type) {
        const el = document.getElementById('disputeToast');
        const body = document.getElementById('disputeToastMsg');
        if (!el || !body) return;
        el.classList.remove('success', 'danger');
        el.classList.add(type || 'success');
        body.textContent = msg;
        const toast = bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 });
        toast.show();
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatDate(str) {
        if (!str) return '—';
        const d = new Date(str);
        return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = (val ?? 0).toLocaleString('vi-VN');
    }

    /* =====================================================
       DECISION MODAL HANDLER (Refund / Reject)
    ===================================================== */

    let currentActionDisputeId = null;
    let currentActionType = null; // 'refund' | 'reject'
    let decisionModalInstance = null;

    function openDecisionModal(disputeId, actionType, onConfirmCallback) {
        currentActionDisputeId = disputeId;
        currentActionType = actionType;

        const modalEl = document.getElementById('decisionModal');
        const titleEl = document.getElementById('decisionModalTitle');
        const descEl = document.getElementById('decisionModalDesc');
        const noteEl = document.getElementById('adminDecisionNote');
        const errEl = document.getElementById('decisionNoteError');
        const confirmBtn = document.getElementById('confirmDecisionBtn');

        if (!modalEl) return;

        if (noteEl) noteEl.value = '';
        if (errEl) errEl.classList.add('d-none');

        if (actionType === 'refund') {
            titleEl.innerHTML = '<i class="fa-solid fa-money-bill-transfer me-2 text-success"></i> Chấp thuận hoàn tiền';
            descEl.textContent = 'Xác nhận hoàn tiền cho khách hàng và trừ tiền tương ứng từ số dư của Seller. Vui lòng nhập giải trình:';
            confirmBtn.className = 'btn btn-success btn-sm';
            confirmBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Xác nhận hoàn tiền';
        } else {
            titleEl.innerHTML = '<i class="fa-solid fa-ban me-2 text-danger"></i> Từ chối khiếu nại';
            descEl.textContent = 'Từ chối yêu cầu khiếu nại của khách hàng. Vui lòng nhập lý do từ chối:';
            confirmBtn.className = 'btn btn-danger btn-sm';
            confirmBtn.innerHTML = '<i class="fa-solid fa-ban me-1"></i> Xác nhận từ chối';
        }

        // Setup confirm handler
        confirmBtn.onclick = function () {
            const note = noteEl.value.trim();
            if (!note) {
                if (errEl) {
                    errEl.textContent = 'Vui lòng nhập lý do / phán quyết.';
                    errEl.classList.remove('d-none');
                }
                return;
            }
            if (errEl) errEl.classList.add('d-none');

            onConfirmCallback(disputeId, actionType, note, decisionModalInstance);
        };

        decisionModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        decisionModalInstance.show();
    }

    /* =====================================================
       INDEX PAGE — Danh sách tranh chấp
    ===================================================== */

    const indexConfig = document.getElementById('disputesAppConfig');
    if (indexConfig) {
        const INDEX_URL   = indexConfig.dataset.indexUrl;
        const EXPORT_URL  = indexConfig.dataset.exportUrl;
        const SHOW_URL    = indexConfig.dataset.showUrl; // contains __ID__
        const PROCESS_URL = indexConfig.dataset.processUrl;
        const REFUND_URL  = indexConfig.dataset.refundUrl;
        const REJECT_URL  = indexConfig.dataset.rejectUrl;
        const CSRF        = indexConfig.dataset.csrf;

        let currentStatus = '';
        let currentSearch = '';
        let currentPage   = 1;
        let debounceTimer = null;

        function loadDisputes(page, status, search) {
            currentPage   = page   ?? currentPage;
            currentStatus = status ?? currentStatus;
            currentSearch = search ?? currentSearch;

            const tbody = document.getElementById('disputesTableBody');
            if (tbody) {
                tbody.innerHTML = `
                  <tr>
                    <td colspan="8" class="text-center py-4">
                      <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                      Đang tải dữ liệu...
                    </td>
                  </tr>`;
            }

            const params = new URLSearchParams({
                page: currentPage,
                ...(currentStatus && { status: currentStatus }),
                ...(currentSearch && { search: currentSearch }),
            });

            fetch(`${INDEX_URL}?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(r => r.json())
                .then(json => {
                    renderTable(json.data);
                    renderPagination(json);
                    updateStatCards(json.meta);
                    updateTabBadge(json.meta.total_pending);
                })
                .catch(() => showToast('Không thể tải dữ liệu tranh chấp.', 'danger'));
        }

        function renderTable(disputes) {
            const tbody = document.getElementById('disputesTableBody');
            if (!tbody) return;

            if (!disputes || disputes.length === 0) {
                tbody.innerHTML = `
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <i class="fa-solid fa-scale-balanced fa-2x mb-2 d-block opacity-25"></i>
                      Không tìm thấy tranh chấp nào.
                    </td>
                  </tr>`;
                return;
            }

            tbody.innerHTML = disputes.map((d, i) => {
                const statusBadge = getStatusBadgeHtml(d.status);
                const showUrl = SHOW_URL.replace('__ID__', d.id);
                const orderNumber = d.seller_order?.order?.order_number ?? `#${d.seller_order_id}`;
                const shopName = d.seller_order?.seller?.seller_profile?.shop_name ?? '—';

                let actionButtons = `
                  <a href="${showUrl}" class="btn-dispute-view" title="Xem chi tiết">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                `;

                if (d.status === 'pending') {
                    actionButtons += `
                      <button type="button" class="btn-dispute-action btn-dispute-process ms-1" data-id="${d.id}" title="Tiếp nhận xử lý">
                        <i class="fa-solid fa-spinner"></i>
                      </button>
                    `;
                }

                if (d.status === 'pending' || d.status === 'in_progress') {
                    actionButtons += `
                      <button type="button" class="btn-dispute-action btn-dispute-refund ms-1" data-id="${d.id}" title="Hoàn tiền">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                      </button>
                      <button type="button" class="btn-dispute-action btn-dispute-reject ms-1" data-id="${d.id}" title="Từ chối">
                        <i class="fa-solid fa-ban"></i>
                      </button>
                    `;
                }

                return `
                  <tr>
                    <td>${i + 1}</td>
                    <td>
                      <div class="fw-semibold text-dark">${escHtml(d.buyer?.name ?? '—')}</div>
                      <div class="text-muted small">${escHtml(d.buyer?.email ?? '')}</div>
                    </td>
                    <td>
                      <span class="fw-semibold text-primary">#${escHtml(orderNumber)}</span>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark">${escHtml(shopName)}</span>
                    </td>
                    <td>
                      <div class="text-truncate" style="max-width: 250px;" title="${escHtml(d.reason)}">
                        ${escHtml(d.reason)}
                      </div>
                    </td>
                    <td class="text-muted small">${formatDate(d.created_at)}</td>
                    <td>${statusBadge}</td>
                    <td>
                      <div class="dispute-table-actions">
                        ${actionButtons}
                      </div>
                    </td>
                  </tr>`;
            }).join('');

            // Attach event handlers
            tbody.querySelectorAll('.btn-dispute-process').forEach(btn => {
                btn.addEventListener('click', () => processDispute(btn.dataset.id));
            });

            tbody.querySelectorAll('.btn-dispute-refund').forEach(btn => {
                btn.addEventListener('click', () => {
                    openDecisionModal(btn.dataset.id, 'refund', handleDecisionSubmit);
                });
            });

            tbody.querySelectorAll('.btn-dispute-reject').forEach(btn => {
                btn.addEventListener('click', () => {
                    openDecisionModal(btn.dataset.id, 'reject', handleDecisionSubmit);
                });
            });
        }

        function getStatusBadgeHtml(status) {
            switch (status) {
                case 'pending':
                    return '<span class="dispute-badge dispute-badge-pending"><i class="fa-solid fa-clock"></i> Chờ xử lý</span>';
                case 'in_progress':
                    return '<span class="dispute-badge dispute-badge-progress"><i class="fa-solid fa-spinner"></i> Đang xử lý</span>';
                case 'refunded':
                    return '<span class="dispute-badge dispute-badge-refunded"><i class="fa-solid fa-circle-check"></i> Đã hoàn tiền</span>';
                case 'rejected':
                    return '<span class="dispute-badge dispute-badge-rejected"><i class="fa-solid fa-circle-xmark"></i> Đã từ chối</span>';
                default:
                    return `<span class="dispute-badge">${escHtml(status)}</span>`;
            }
        }

        function processDispute(disputeId) {
            if (!confirm(`Tiếp nhận và chuyển tranh chấp #${disputeId} sang trạng thái "Đang xử lý"?`)) return;

            const url = PROCESS_URL.replace('__ID__', disputeId);
            fetch(url, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(json => {
                    showToast(json.message || 'Đã tiếp nhận tranh chấp!', 'success');
                    loadDisputes(currentPage, currentStatus, currentSearch);
                })
                .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
        }

        function handleDecisionSubmit(disputeId, actionType, note, modalInst) {
            const baseUrl = actionType === 'refund' ? REFUND_URL : REJECT_URL;
            const url = baseUrl.replace('__ID__', disputeId);

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ admin_decision: note }),
            })
                .then(r => r.json())
                .then(json => {
                    if (modalInst) modalInst.hide();
                    showToast(json.message || 'Xử lý thành công!', 'success');
                    loadDisputes(currentPage, currentStatus, currentSearch);
                })
                .catch(() => showToast('Có lỗi xảy ra khi cập nhật quyết định.', 'danger'));
        }

        function renderPagination(json) {
            const wrap  = document.getElementById('paginationWrap');
            const info  = document.getElementById('paginationInfo');
            const links = document.getElementById('paginationLinks');
            if (!wrap || !info || !links) return;

            const { from, to, total, last_page, current_page } = json;

            if (total === 0) {
                wrap.style.display = 'none';
                return;
            }

            wrap.style.display = 'flex';
            info.textContent = `Hiển thị ${from ?? 0}–${to ?? 0} / ${total} khiếu nại`;
            links.innerHTML = '';

            const prevBtn = document.createElement('button');
            prevBtn.className = 'page-btn';
            prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
            prevBtn.disabled = current_page <= 1;
            prevBtn.addEventListener('click', () => loadDisputes(current_page - 1));
            links.appendChild(prevBtn);

            for (let p = 1; p <= last_page; p++) {
                if (last_page > 7 && p > 2 && p < last_page - 1 && Math.abs(p - current_page) > 1) {
                    if (p === 3 || p === last_page - 2) {
                        const ellipsis = document.createElement('span');
                        ellipsis.className = 'page-btn';
                        ellipsis.textContent = '...';
                        ellipsis.style.cursor = 'default';
                        links.appendChild(ellipsis);
                    }
                    continue;
                }
                const btn = document.createElement('button');
                btn.className = 'page-btn' + (p === current_page ? ' active' : '');
                btn.textContent = p;
                btn.addEventListener('click', () => loadDisputes(p));
                links.appendChild(btn);
            }

            const nextBtn = document.createElement('button');
            nextBtn.className = 'page-btn';
            nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
            nextBtn.disabled = current_page >= last_page;
            nextBtn.addEventListener('click', () => loadDisputes(current_page + 1));
            links.appendChild(nextBtn);
        }

        function updateStatCards(meta) {
            if (!meta) return;
            setText('count-all',         meta.total_all);
            setText('count-pending',     meta.total_pending);
            setText('count-in-progress', meta.total_in_progress);
            setText('count-refunded',    meta.total_refunded);
            setText('count-rejected',    meta.total_rejected);
        }

        function updateTabBadge(count) {
            const el = document.getElementById('tab-badge-pending');
            if (el) el.textContent = count;
        }

        // Filter tabs
        document.getElementById('disputeStatusTabs')?.addEventListener('click', e => {
            const tab = e.target.closest('.dispute-tab');
            if (!tab) return;
            document.querySelectorAll('.dispute-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            loadDisputes(1, tab.dataset.status, currentSearch);
        });

        // Search debounce
        document.getElementById('disputeSearchInput')?.addEventListener('input', e => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadDisputes(1, currentStatus, e.target.value.trim()), 400);
        });

        // Export CSV
        document.getElementById('btnExportCsv')?.addEventListener('click', e => {
            e.preventDefault();
            const params = new URLSearchParams({
                ...(currentStatus && { status: currentStatus }),
                ...(currentSearch && { search: currentSearch }),
            });
            window.location.href = `${EXPORT_URL}?${params}`;
        });

        // Init
        loadDisputes(1, '', '');
    }

    /* =====================================================
       SHOW PAGE — Chi tiết tranh chấp
    ===================================================== */

    const showConfig = document.getElementById('disputeShowConfig');
    if (showConfig) {
        const disputeId   = showConfig.dataset.id;
        const PROCESS_URL = showConfig.dataset.processUrl;
        const REFUND_URL  = showConfig.dataset.refundUrl;
        const REJECT_URL  = showConfig.dataset.rejectUrl;
        const CSRF        = showConfig.dataset.csrf;

        document.getElementById('btnProcessDispute')?.addEventListener('click', () => {
            if (!confirm('Tiếp nhận xử lý khiếu nại này?')) return;
            fetch(PROCESS_URL, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(json => {
                    showToast(json.message || 'Đã tiếp nhận!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
        });

        document.getElementById('btnRefundDispute')?.addEventListener('click', () => {
            openDecisionModal(disputeId, 'refund', (id, type, note, modalInst) => {
                fetch(REFUND_URL, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ admin_decision: note }),
                })
                    .then(r => r.json())
                    .then(json => {
                        if (modalInst) modalInst.hide();
                        showToast(json.message || 'Đã hoàn tiền!', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
            });
        });

        document.getElementById('btnRejectDispute')?.addEventListener('click', () => {
            openDecisionModal(disputeId, 'reject', (id, type, note, modalInst) => {
                fetch(REJECT_URL, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ admin_decision: note }),
                })
                    .then(r => r.json())
                    .then(json => {
                        if (modalInst) modalInst.hide();
                        showToast(json.message || 'Đã từ chối!', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
            });
        });
    }

})();
