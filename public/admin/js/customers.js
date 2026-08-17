/**
 * customers.js — Admin Customer Management JavaScript
 * Dùng cho: /admin/customers (index & show pages)
 *
 * Không có inline script / inline style (Rule 24).
 * Tất cả URL đọc từ #customersAppConfig hoặc #customerShowConfig data attributes.
 */

(function () {
    'use strict';

    /* =====================================================
       SHARED HELPERS
    ===================================================== */

    /**
     * Hiển thị toast thông báo.
     * @param {string} msg   - Nội dung
     * @param {'success'|'danger'} type - Loại toast
     */
    function showToast(msg, type) {
        const el = document.getElementById('customerToast');
        const body = document.getElementById('customerToastMsg');
        if (!el || !body) return;
        el.classList.remove('success', 'danger');
        el.classList.add(type || 'success');
        body.textContent = msg;
        const toast = bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 });
        toast.show();
    }

    /* =====================================================
       INDEX PAGE — Danh sách khách hàng
    ===================================================== */

    const indexConfig = document.getElementById('customersAppConfig');
    if (indexConfig) {
        const INDEX_URL    = indexConfig.dataset.indexUrl;
        const EXPORT_URL   = indexConfig.dataset.exportUrl;
        const SHOW_URL_TPL = indexConfig.dataset.showUrl;   // contains __ID__
        const BLOCK_URL    = indexConfig.dataset.blockUrl;  // contains __ID__
        const UNBLOCK_URL  = indexConfig.dataset.unblockUrl;
        const CSRF         = indexConfig.dataset.csrf;

        let currentStatus   = '';
        let currentSearch   = '';
        let currentPage     = 1;
        let pendingBlockId  = null;
        let blockModalInst  = null;
        let debounceTimer   = null;

        /* ---------- Load customers ---------- */
        function loadCustomers(page, status, search) {
            currentPage   = page   ?? currentPage;
            currentStatus = status ?? currentStatus;
            currentSearch = search ?? currentSearch;

            const tbody = document.getElementById('customersTableBody');
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
                    updateTabBadge(json.meta.total_blocked);
                })
                .catch(() => showToast('Không thể tải dữ liệu. Vui lòng thử lại.', 'danger'));
        }

        /* ---------- Render table rows ---------- */
        function renderTable(customers) {
            const tbody = document.getElementById('customersTableBody');
            if (!tbody) return;

            if (!customers || customers.length === 0) {
                tbody.innerHTML = `
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <i class="fa-solid fa-users fa-2x mb-2 d-block opacity-25"></i>
                      Không có khách hàng nào.
                    </td>
                  </tr>`;
                return;
            }

            tbody.innerHTML = customers.map((c, i) => {
                const statusBadge = c.status === 'active'
                    ? `<span class="customer-badge active"><i class="fa-solid fa-circle-check"></i> Hoạt động</span>`
                    : `<span class="customer-badge blocked"><i class="fa-solid fa-ban"></i> Đã khóa</span>`;

                const actionBtn = c.status === 'active'
                    ? `<button type="button" class="btn-table-block ms-1" title="Khóa"
                               data-id="${c.id}" data-name="${escHtml(c.name)}">
                           <i class="fa-solid fa-ban"></i>
                       </button>`
                    : `<button type="button" class="btn-table-unblock ms-1" title="Mở khóa"
                               data-id="${c.id}" data-name="${escHtml(c.name)}">
                           <i class="fa-solid fa-circle-check"></i>
                       </button>`;

                const showUrl = SHOW_URL_TPL.replace('__ID__', c.id);

                return `
                  <tr>
                    <td>${i + 1}</td>
                    <td>
                      <div class="customer-table-info">
                        <img src="${escHtml(c.avatar_url ?? `https://ui-avatars.com/api/?name=${encodeURIComponent(c.name)}&background=c62828&color=fff&size=64&bold=true`)}"
                             alt="${escHtml(c.name)}"
                             class="customer-table-avatar">
                        <span class="customer-table-name">${escHtml(c.name)}</span>
                      </div>
                    </td>
                    <td class="text-muted small">${escHtml(c.email)}</td>
                    <td class="text-muted small">${escHtml(c.phone ?? '—')}</td>
                    <td style="text-align:center;" class="fw-semibold">${c.orders_count ?? 0}</td>
                    <td class="text-muted small">${formatDate(c.created_at)}</td>
                    <td>${statusBadge}</td>
                    <td>
                      <div class="customer-table-actions">
                        <a href="${showUrl}" class="btn-customer-view" title="Xem hồ sơ">
                          <i class="fa-solid fa-eye"></i>
                        </a>
                        ${actionBtn}
                      </div>
                    </td>
                  </tr>`;
            }).join('');

            /* Event delegation cho block / unblock buttons */
            tbody.querySelectorAll('.btn-table-block').forEach(btn => {
                btn.addEventListener('click', () => openBlockModal(btn.dataset.id, btn.dataset.name));
            });
            tbody.querySelectorAll('.btn-table-unblock').forEach(btn => {
                btn.addEventListener('click', () => unblockCustomer(btn.dataset.id, btn.dataset.name));
            });
        }

        /* ---------- Render pagination ---------- */
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
            info.textContent = `Hiển thị ${from ?? 0}–${to ?? 0} / ${total} khách hàng`;

            links.innerHTML = '';

            // Nút Trước
            const prevBtn = document.createElement('button');
            prevBtn.className = 'page-btn';
            prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
            prevBtn.disabled = current_page <= 1;
            prevBtn.addEventListener('click', () => loadCustomers(current_page - 1));
            links.appendChild(prevBtn);

            // Số trang
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
                btn.addEventListener('click', () => loadCustomers(p));
                links.appendChild(btn);
            }

            // Nút Sau
            const nextBtn = document.createElement('button');
            nextBtn.className = 'page-btn';
            nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
            nextBtn.disabled = current_page >= last_page;
            nextBtn.addEventListener('click', () => loadCustomers(current_page + 1));
            links.appendChild(nextBtn);
        }

        /* ---------- Update stat cards ---------- */
        function updateStatCards(meta) {
            if (!meta) return;
            setText('count-all',      meta.total_all);
            setText('count-active',   meta.total_active);
            setText('count-blocked',  meta.total_blocked);
            setText('count-new-30d',  meta.total_new_30d);
        }

        function updateTabBadge(count) {
            const el = document.getElementById('tab-badge-blocked');
            if (el) el.textContent = count;
        }

        /* ---------- Block customer ---------- */
        function openBlockModal(userId, userName) {
            pendingBlockId = userId;
            const nameEl = document.getElementById('blockCustomerName');
            if (nameEl) nameEl.textContent = userName;
            const noteEl = document.getElementById('blockAdminNote');
            if (noteEl) noteEl.value = '';
            const errEl = document.getElementById('blockNoteError');
            if (errEl) errEl.classList.add('d-none');

            const modalEl = document.getElementById('blockCustomerModal');
            if (!modalEl) return;
            blockModalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
            blockModalInst.show();
        }

        const confirmBlockBtn = document.getElementById('confirmBlockBtn');
        if (confirmBlockBtn) {
            confirmBlockBtn.addEventListener('click', () => {
                const note = document.getElementById('blockAdminNote')?.value?.trim();
                const errEl = document.getElementById('blockNoteError');
                if (!note) {
                    if (errEl) { errEl.textContent = 'Vui lòng nhập lý do khóa.'; errEl.classList.remove('d-none'); }
                    return;
                }
                if (errEl) errEl.classList.add('d-none');

                const url = BLOCK_URL.replace('__ID__', pendingBlockId);
                fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ admin_note: note }),
                })
                    .then(r => r.json())
                    .then(json => {
                        if (blockModalInst) blockModalInst.hide();
                        showToast(json.message || 'Đã khóa tài khoản!', 'success');
                        loadCustomers(currentPage, currentStatus, currentSearch);
                    })
                    .catch(() => showToast('Có lỗi xảy ra khi khóa tài khoản.', 'danger'));
            });
        }

        /* ---------- Unblock customer ---------- */
        function unblockCustomer(userId, userName) {
            if (!confirm(`Mở khóa tài khoản ${userName}?`)) return;
            const url = UNBLOCK_URL.replace('__ID__', userId);
            fetch(url, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(json => {
                    showToast(json.message || 'Đã mở khóa!', 'success');
                    loadCustomers(currentPage, currentStatus, currentSearch);
                })
                .catch(() => showToast('Có lỗi xảy ra khi mở khóa.', 'danger'));
        }

        /* ---------- Tab filter ---------- */
        document.getElementById('customerStatusTabs')?.addEventListener('click', e => {
            const tab = e.target.closest('.customer-tab');
            if (!tab) return;
            document.querySelectorAll('.customer-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            loadCustomers(1, tab.dataset.status, currentSearch);
        });

        /* ---------- Search (debounce 400ms) ---------- */
        document.getElementById('customerSearchInput')?.addEventListener('input', e => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadCustomers(1, currentStatus, e.target.value.trim()), 400);
        });

        /* ---------- Export CSV ---------- */
        document.getElementById('btnExportCsv')?.addEventListener('click', e => {
            e.preventDefault();
            const params = new URLSearchParams({
                ...(currentStatus && { status: currentStatus }),
                ...(currentSearch && { search: currentSearch }),
            });
            window.location.href = `${EXPORT_URL}?${params}`;
        });

        /* ---------- Init ---------- */
        loadCustomers(1, '', '');
    }

    /* =====================================================
       SHOW PAGE — Hồ sơ chi tiết khách hàng
    ===================================================== */

    const showConfig = document.getElementById('customerShowConfig');
    if (showConfig) {
        const BLOCK_URL   = showConfig.dataset.blockUrl;
        const UNBLOCK_URL = showConfig.dataset.unblockUrl;
        const BACK_URL    = showConfig.dataset.backUrl;
        const CSRF        = showConfig.dataset.csrf;
        const userStatus  = showConfig.dataset.status;

        /* Block button on show page */
        document.getElementById('btnBlockCustomer')?.addEventListener('click', () => {
            const modalEl = document.getElementById('blockCustomerModal');
            if (!modalEl) return;
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        document.getElementById('confirmBlockBtn')?.addEventListener('click', () => {
            const note = document.getElementById('blockAdminNote')?.value?.trim();
            const errEl = document.getElementById('blockNoteError');
            if (!note) {
                if (errEl) { errEl.textContent = 'Vui lòng nhập lý do khóa.'; errEl.classList.remove('d-none'); }
                return;
            }
            if (errEl) errEl.classList.add('d-none');

            fetch(BLOCK_URL, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ admin_note: note }),
            })
                .then(r => r.json())
                .then(json => {
                    showToast(json.message || 'Đã khóa tài khoản!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
        });

        /* Unblock button on show page */
        document.getElementById('btnUnblockCustomer')?.addEventListener('click', () => {
            fetch(UNBLOCK_URL, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(json => {
                    showToast(json.message || 'Đã mở khóa tài khoản!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
        });
    }

    /* =====================================================
       UTILITIES
    ===================================================== */

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = (val ?? 0).toLocaleString('vi-VN');
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatDate(str) {
        if (!str) return '—';
        const d = new Date(str);
        return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

})();
