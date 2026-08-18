/**
 * withdrawals.js — Admin Withdrawal Management JavaScript
 * Dùng cho: /admin/withdrawals (index & show pages)
 */

(function () {
    'use strict';

    /* =====================================================
       SHARED HELPERS
    ===================================================== */

    function showToast(msg, type) {
        const el = document.getElementById('withdrawalToast');
        const body = document.getElementById('withdrawalToastMsg');
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

    function formatVND(amount) {
        return Number(amount || 0).toLocaleString('vi-VN') + 'đ';
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = (val ?? 0).toLocaleString('vi-VN');
    }

    /* =====================================================
       REJECT MODAL HANDLER
    ===================================================== */

    let currentRejectWithdrawalId = null;
    let rejectModalInstance = null;

    function openRejectModal(withdrawalId, shopName, onConfirmCallback) {
        currentRejectWithdrawalId = withdrawalId;

        const modalEl = document.getElementById('rejectWithdrawalModal');
        const shopNameEl = document.getElementById('modalRejectShopName');
        const noteEl = document.getElementById('rejectAdminNote');
        const errEl = document.getElementById('rejectNoteError');
        const confirmBtn = document.getElementById('confirmRejectBtn');

        if (!modalEl) return;

        if (shopNameEl) shopNameEl.textContent = shopName || '—';
        if (noteEl) noteEl.value = '';
        if (errEl) errEl.classList.add('d-none');

        confirmBtn.onclick = function () {
            const note = noteEl.value.trim();
            if (!note) {
                if (errEl) {
                    errEl.textContent = 'Vui lòng nhập lý do từ chối yêu cầu rút tiền.';
                    errEl.classList.remove('d-none');
                }
                return;
            }
            if (errEl) errEl.classList.add('d-none');

            onConfirmCallback(withdrawalId, note, rejectModalInstance);
        };

        rejectModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        rejectModalInstance.show();
    }

    /* =====================================================
       INDEX PAGE — Danh sách Yêu cầu Rút tiền
    ===================================================== */

    const indexConfig = document.getElementById('withdrawalsAppConfig');
    if (indexConfig) {
        const INDEX_URL   = indexConfig.dataset.indexUrl;
        const EXPORT_URL  = indexConfig.dataset.exportUrl;
        const SHOW_URL    = indexConfig.dataset.showUrl; // contains __ID__
        const APPROVE_URL = indexConfig.dataset.approveUrl;
        const REJECT_URL  = indexConfig.dataset.rejectUrl;
        const CSRF        = indexConfig.dataset.csrf;

        let currentStatus = '';
        let currentSearch = '';
        let currentPage   = 1;
        let debounceTimer = null;

        function loadWithdrawals(page, status, search) {
            currentPage   = page   ?? currentPage;
            currentStatus = status ?? currentStatus;
            currentSearch = search ?? currentSearch;

            const tbody = document.getElementById('withdrawalsTableBody');
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
                .catch(() => showToast('Không thể tải dữ liệu rút tiền.', 'danger'));
        }

        function renderTable(withdrawals) {
            const tbody = document.getElementById('withdrawalsTableBody');
            if (!tbody) return;

            if (!withdrawals || withdrawals.length === 0) {
                tbody.innerHTML = `
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <i class="fa-solid fa-money-bill-transfer fa-2x mb-2 d-block opacity-25"></i>
                      Không tìm thấy yêu cầu rút tiền nào.
                    </td>
                  </tr>`;
                return;
            }

            tbody.innerHTML = withdrawals.map((w, i) => {
                const statusBadge = getStatusBadgeHtml(w.status);
                const showUrl = SHOW_URL.replace('__ID__', w.id);
                const shopName = w.seller?.seller_profile?.shop_name ?? w.seller?.name ?? '—';
                const sellerBalance = w.seller?.seller_profile?.balance ?? 0;

                let actionButtons = `
                  <a href="${showUrl}" class="btn-withdrawal-view" title="Xem chi tiết">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                `;

                if (w.status === 'pending') {
                    actionButtons += `
                      <button type="button" class="btn-withdrawal-action btn-withdrawal-approve ms-1"
                              data-id="${w.id}" data-amount="${formatVND(w.amount)}" data-shop="${escHtml(shopName)}" title="Duyệt rút tiền">
                        <i class="fa-solid fa-check"></i>
                      </button>
                      <button type="button" class="btn-withdrawal-action btn-withdrawal-reject ms-1"
                              data-id="${w.id}" data-shop="${escHtml(shopName)}" title="Từ chối">
                        <i class="fa-solid fa-ban"></i>
                      </button>
                    `;
                }

                return `
                  <tr>
                    <td>${i + 1}</td>
                    <td>
                      <div class="fw-semibold text-dark">${escHtml(shopName)}</div>
                      <div class="text-muted small">${escHtml(w.seller?.email ?? '')}</div>
                    </td>
                    <td>
                      <div class="fw-semibold text-dark">${escHtml(w.bank_name)}</div>
                      <div class="text-muted small font-monospace">${escHtml(w.bank_account)} - ${escHtml(w.bank_owner)}</div>
                    </td>
                    <td style="text-align: right;">
                      <span class="fw-bold text-danger fs-6">${formatVND(w.amount)}</span>
                    </td>
                    <td style="text-align: right;">
                      <span class="fw-semibold text-success">${formatVND(sellerBalance)}</span>
                    </td>
                    <td class="text-muted small">${formatDate(w.created_at)}</td>
                    <td>${statusBadge}</td>
                    <td>
                      <div class="withdrawal-table-actions">
                        ${actionButtons}
                      </div>
                    </td>
                  </tr>`;
            }).join('');

            // Attach event handlers
            tbody.querySelectorAll('.btn-withdrawal-approve').forEach(btn => {
                btn.addEventListener('click', () => {
                    approveWithdrawal(btn.dataset.id, btn.dataset.shop, btn.dataset.amount);
                });
            });

            tbody.querySelectorAll('.btn-withdrawal-reject').forEach(btn => {
                btn.addEventListener('click', () => {
                    openRejectModal(btn.dataset.id, btn.dataset.shop, handleRejectSubmit);
                });
            });
        }

        function getStatusBadgeHtml(status) {
            switch (status) {
                case 'pending':
                    return '<span class="withdrawal-badge withdrawal-badge-pending"><i class="fa-solid fa-clock"></i> Chờ duyệt</span>';
                case 'approved':
                    return '<span class="withdrawal-badge withdrawal-badge-approved"><i class="fa-solid fa-circle-check"></i> Đã duyệt</span>';
                case 'rejected':
                    return '<span class="withdrawal-badge withdrawal-badge-rejected"><i class="fa-solid fa-circle-xmark"></i> Đã từ chối</span>';
                default:
                    return `<span class="withdrawal-badge">${escHtml(status)}</span>`;
            }
        }

        function approveWithdrawal(withdrawalId, shopName, amountStr) {
            if (!confirm(`Xác nhận duyệt và chuyển ${amountStr} cho gian hàng "${shopName}"?\nSố dư ví của Seller sẽ bị trừ tự động.`)) return;

            const url = APPROVE_URL.replace('__ID__', withdrawalId);
            fetch(url, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
                .then(r => r.json().then(data => ({ status: r.status, body: data })))
                .then(({ status, body }) => {
                    if (status >= 400) {
                        showToast(body.message || 'Không thể duyệt yêu cầu rút tiền.', 'danger');
                        return;
                    }
                    showToast(body.message || 'Duyệt yêu cầu rút tiền thành công!', 'success');
                    loadWithdrawals(currentPage, currentStatus, currentSearch);
                })
                .catch(() => showToast('Có lỗi xảy ra khi duyệt rút tiền.', 'danger'));
        }

        function handleRejectSubmit(withdrawalId, note, modalInst) {
            const url = REJECT_URL.replace('__ID__', withdrawalId);

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ admin_note: note }),
            })
                .then(r => r.json().then(data => ({ status: r.status, body: data })))
                .then(({ status, body }) => {
                    if (status >= 400) {
                        showToast(body.message || 'Có lỗi xảy ra.', 'danger');
                        return;
                    }
                    if (modalInst) modalInst.hide();
                    showToast(body.message || 'Đã từ chối yêu cầu rút tiền!', 'success');
                    loadWithdrawals(currentPage, currentStatus, currentSearch);
                })
                .catch(() => showToast('Có lỗi xảy ra khi từ chối.', 'danger'));
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
            info.textContent = `Hiển thị ${from ?? 0}–${to ?? 0} / ${total} yêu cầu`;
            links.innerHTML = '';

            const prevBtn = document.createElement('button');
            prevBtn.className = 'page-btn';
            prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
            prevBtn.disabled = current_page <= 1;
            prevBtn.addEventListener('click', () => loadWithdrawals(current_page - 1));
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
                btn.addEventListener('click', () => loadWithdrawals(p));
                links.appendChild(btn);
            }

            const nextBtn = document.createElement('button');
            nextBtn.className = 'page-btn';
            nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
            nextBtn.disabled = current_page >= last_page;
            nextBtn.addEventListener('click', () => loadWithdrawals(current_page + 1));
            links.appendChild(nextBtn);
        }

        function updateStatCards(meta) {
            if (!meta) return;
            setText('count-all',       meta.total_all);
            setText('count-pending',   meta.total_pending);
            setText('count-approved',  meta.total_approved);
            setText('count-rejected',  meta.total_rejected);

            const paidEl = document.getElementById('count-total-paid');
            if (paidEl) {
                paidEl.textContent = formatVND(meta.total_paid);
            }
        }

        function updateTabBadge(count) {
            const el = document.getElementById('tab-badge-pending');
            if (el) el.textContent = count;
        }

        // Tab filter
        document.getElementById('withdrawalStatusTabs')?.addEventListener('click', e => {
            const tab = e.target.closest('.withdrawal-tab');
            if (!tab) return;
            document.querySelectorAll('.withdrawal-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            loadWithdrawals(1, tab.dataset.status, currentSearch);
        });

        // Search debounce
        document.getElementById('withdrawalSearchInput')?.addEventListener('input', e => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadWithdrawals(1, currentStatus, e.target.value.trim()), 400);
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
        loadWithdrawals(1, '', '');
    }

    /* =====================================================
       SHOW PAGE — Chi tiết Yêu cầu Rút tiền
    ===================================================== */

    const showConfig = document.getElementById('withdrawalShowConfig');
    if (showConfig) {
        const withdrawalId = showConfig.dataset.id;
        const shopName     = showConfig.dataset.shopName;
        const APPROVE_URL  = showConfig.dataset.approveUrl;
        const REJECT_URL   = showConfig.dataset.rejectUrl;
        const CSRF         = showConfig.dataset.csrf;

        document.getElementById('btnApproveWithdrawal')?.addEventListener('click', () => {
            if (!confirm('Xác nhận duyệt và hoàn tất lệnh rút tiền này?\nSố dư ví của Seller sẽ bị trừ tự động.')) return;
            fetch(APPROVE_URL, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
                .then(r => r.json().then(data => ({ status: r.status, body: data })))
                .then(({ status, body }) => {
                    if (status >= 400) {
                        showToast(body.message || 'Lỗi khi duyệt rút tiền.', 'danger');
                        return;
                    }
                    showToast(body.message || 'Duyệt thành công!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
        });

        document.getElementById('btnRejectWithdrawal')?.addEventListener('click', () => {
            openRejectModal(withdrawalId, shopName, (id, note, modalInst) => {
                fetch(REJECT_URL, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ admin_note: note }),
                })
                    .then(r => r.json().then(data => ({ status: r.status, body: data })))
                    .then(({ status, body }) => {
                        if (status >= 400) {
                            showToast(body.message || 'Có lỗi xảy ra.', 'danger');
                            return;
                        }
                        if (modalInst) modalInst.hide();
                        showToast(body.message || 'Đã từ chối!', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
            });
        });
    }

})();
