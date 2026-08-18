/**
 * support-tickets.js — Admin Support & Appeals Management JavaScript
 * Dùng cho: /admin/support-tickets (index & show pages)
 */

(function () {
    'use strict';

    /* =====================================================
       SHARED HELPERS
    ===================================================== */

    function showToast(msg, type) {
        const el = document.getElementById('ticketToast');
        const body = document.getElementById('ticketToastMsg');
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
       RESPONSE MODAL HANDLER
    ===================================================== */

    let modalInstance = null;

    function openResponseModal(ticketId, ticketSubject, onConfirmCallback, category, hasProduct) {
        const modalEl = document.getElementById('ticketResponseModal');
        const subjectEl = document.getElementById('modalTicketSubject');
        const textEl = document.getElementById('adminResponseText');
        const errEl = document.getElementById('responseNoteError');
        const confirmBtn = document.getElementById('confirmTicketResponseBtn');

        const actionWrap  = document.getElementById('modalActionOptions');
        const unlockWrap  = document.getElementById('optUnlockSellerWrap');
        const approveWrap = document.getElementById('optApproveProductWrap');
        const unlockChk   = document.getElementById('chkUnlockSeller');
        const approveChk  = document.getElementById('chkApproveProduct');

        if (!modalEl) return;

        if (subjectEl) subjectEl.textContent = ticketSubject;
        if (textEl) textEl.value = '';
        if (errEl) errEl.classList.add('d-none');

        if (unlockChk) unlockChk.checked = false;
        if (approveChk) approveChk.checked = false;

        let hasAnyAction = false;
        if (category === 'account_blocked' && unlockWrap) {
            unlockWrap.classList.remove('d-none');
            hasAnyAction = true;
        } else if (unlockWrap) {
            unlockWrap.classList.add('d-none');
        }

        if (category === 'product_rejected' && hasProduct && approveWrap) {
            approveWrap.classList.remove('d-none');
            hasAnyAction = true;
        } else if (approveWrap) {
            approveWrap.classList.add('d-none');
        }

        if (actionWrap) {
            if (hasAnyAction) actionWrap.classList.remove('d-none');
            else actionWrap.classList.add('d-none');
        }

        confirmBtn.onclick = function () {
            const responseText = textEl.value.trim();
            const actionStatus = document.querySelector('input[name="action_status"]:checked')?.value || 'resolved';
            const unlockSeller = unlockChk ? unlockChk.checked : false;
            const approveProduct = approveChk ? approveChk.checked : false;

            if (!responseText) {
                if (errEl) {
                    errEl.textContent = 'Vui lòng nhập nội dung phản hồi cho Seller.';
                    errEl.classList.remove('d-none');
                }
                return;
            }
            if (errEl) errEl.classList.add('d-none');

            onConfirmCallback(ticketId, responseText, actionStatus, modalInstance, { unlockSeller, approveProduct });
        };

        modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
    }

    /* =====================================================
       INDEX PAGE — Danh sách Ticket
    ===================================================== */

    const indexConfig = document.getElementById('ticketsAppConfig');
    if (indexConfig) {
        const INDEX_URL     = indexConfig.dataset.indexUrl;
        const EXPORT_URL    = indexConfig.dataset.exportUrl;
        const SHOW_URL      = indexConfig.dataset.showUrl; // contains __ID__
        const IN_REVIEW_URL = indexConfig.dataset.inReviewUrl;
        const RESPOND_URL   = indexConfig.dataset.respondUrl;
        const CSRF          = indexConfig.dataset.csrf;

        let currentStatus   = '';
        let currentCategory = '';
        let currentSearch   = '';
        let currentPage     = 1;
        let debounceTimer   = null;

        function loadTickets(page, status, category, search) {
            currentPage     = page     ?? currentPage;
            currentStatus   = status   ?? currentStatus;
            currentCategory = category ?? currentCategory;
            currentSearch   = search   ?? currentSearch;

            const tbody = document.getElementById('ticketsTableBody');
            if (tbody) {
                tbody.innerHTML = `
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                      Đang tải dữ liệu...
                    </td>
                  </tr>`;
            }

            const params = new URLSearchParams({
                page: currentPage,
                ...(currentStatus && { status: currentStatus }),
                ...(currentCategory && { category: currentCategory }),
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
                    updateTabBadge(json.meta.total_open);
                })
                .catch(() => showToast('Không thể tải dữ liệu yêu cầu hỗ trợ.', 'danger'));
        }

        function renderTable(tickets) {
            const tbody = document.getElementById('ticketsTableBody');
            if (!tbody) return;

            if (!tickets || tickets.length === 0) {
                tbody.innerHTML = `
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <i class="fa-solid fa-headset fa-2x mb-2 d-block opacity-25"></i>
                      Không có yêu cầu hỗ trợ nào.
                    </td>
                  </tr>`;
                return;
            }

            tbody.innerHTML = tickets.map((t, i) => {
                const statusBadge = getStatusBadgeHtml(t.status);
                const showUrl = SHOW_URL.replace('__ID__', t.id);
                const shopName = t.seller?.seller_profile?.shop_name ?? t.seller?.name ?? '—';

                let actionButtons = `
                  <a href="${showUrl}" class="btn-ticket-view" title="Xem chi tiết">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                `;

                if (t.status !== 'closed') {
                    actionButtons += `
                      <button type="button" class="btn-ticket-action btn-ticket-respond ms-1"
                              data-id="${t.id}" data-subject="${escHtml(t.subject)}"
                              data-category="${t.category}" data-has-product="${t.product_id ? '1' : '0'}"
                              title="Phản hồi">
                        <i class="fa-solid fa-reply"></i>
                      </button>
                    `;
                }

                return `
                  <tr>
                    <td>${i + 1}</td>
                    <td>
                      <div class="fw-semibold text-dark">${escHtml(shopName)}</div>
                      <div class="text-muted small">${escHtml(t.seller?.email ?? '')}</div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border small">${escHtml(getCategoryLabel(t.category))}</span>
                    </td>
                    <td>
                      <a href="${showUrl}" class="fw-semibold text-decoration-none text-dark d-block text-truncate" style="max-width: 280px;" title="${escHtml(t.subject)}">
                        ${escHtml(t.subject)}
                      </a>
                    </td>
                    <td class="text-muted small">${formatDate(t.created_at)}</td>
                    <td>${statusBadge}</td>
                    <td>
                      <div class="ticket-table-actions">
                        ${actionButtons}
                      </div>
                    </td>
                  </tr>`;
            }).join('');

            tbody.querySelectorAll('.btn-ticket-respond').forEach(btn => {
                btn.addEventListener('click', () => {
                    openResponseModal(
                        btn.dataset.id,
                        btn.dataset.subject,
                        handleResponseSubmit,
                        btn.dataset.category,
                        btn.dataset.hasProduct === '1'
                    );
                });
            });
        }

        function getCategoryLabel(category) {
            switch (category) {
                case 'account_blocked': return 'Khóa tài khoản';
                case 'withdrawal_issue': return 'Sự cố rút tiền';
                case 'product_rejected': return 'Duyệt sản phẩm';
                case 'commission_fee': return 'Hoa hồng & Phí';
                default: return 'Khác / Chung';
            }
        }

        function getStatusBadgeHtml(status) {
            switch (status) {
                case 'open':
                    return '<span class="ticket-badge ticket-badge-open"><i class="fa-solid fa-envelope-open"></i> Mới mở</span>';
                case 'in_review':
                    return '<span class="ticket-badge ticket-badge-review"><i class="fa-solid fa-arrows-rotate"></i> Đang xử lý</span>';
                case 'resolved':
                    return '<span class="ticket-badge ticket-badge-resolved"><i class="fa-solid fa-circle-check"></i> Đã giải quyết</span>';
                case 'closed':
                    return '<span class="ticket-badge ticket-badge-closed"><i class="fa-solid fa-lock"></i> Đã đóng</span>';
                default:
                    return `<span class="ticket-badge">${escHtml(status)}</span>`;
            }
        }

        function handleResponseSubmit(ticketId, responseText, actionStatus, modalInst, extra) {
            const url = RESPOND_URL.replace('__ID__', ticketId);

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    admin_response: responseText,
                    action_status: actionStatus,
                    unlock_seller: extra?.unlockSeller || false,
                    approve_product: extra?.approveProduct || false,
                }),
            })
                .then(r => r.json())
                .then(json => {
                    if (modalInst) modalInst.hide();
                    showToast(json.message || 'Đã gửi phản hồi thành công!', 'success');
                    loadTickets(currentPage, currentStatus, currentCategory, currentSearch);
                })
                .catch(() => showToast('Có lỗi xảy ra khi gửi phản hồi.', 'danger'));
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
            prevBtn.addEventListener('click', () => loadTickets(current_page - 1));
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
                btn.addEventListener('click', () => loadTickets(p));
                links.appendChild(btn);
            }

            const nextBtn = document.createElement('button');
            nextBtn.className = 'page-btn';
            nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
            nextBtn.disabled = current_page >= last_page;
            nextBtn.addEventListener('click', () => loadTickets(current_page + 1));
            links.appendChild(nextBtn);
        }

        function updateStatCards(meta) {
            if (!meta) return;
            setText('count-all',       meta.total_all);
            setText('count-open',      meta.total_open);
            setText('count-in-review', meta.total_in_review);
            setText('count-resolved',  meta.total_resolved);
            setText('count-closed',    meta.total_closed);
        }

        function updateTabBadge(count) {
            const el = document.getElementById('tab-badge-open');
            if (el) el.textContent = count;
        }

        // Tab filter
        document.getElementById('ticketStatusTabs')?.addEventListener('click', e => {
            const tab = e.target.closest('.ticket-tab');
            if (!tab) return;
            document.querySelectorAll('.ticket-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            loadTickets(1, tab.dataset.status, currentCategory, currentSearch);
        });

        // Category dropdown filter
        document.getElementById('categoryFilter')?.addEventListener('change', e => {
            loadTickets(1, currentStatus, e.target.value, currentSearch);
        });

        // Search debounce
        document.getElementById('ticketSearchInput')?.addEventListener('input', e => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadTickets(1, currentStatus, currentCategory, e.target.value.trim()), 400);
        });

        // Export CSV
        document.getElementById('btnExportCsv')?.addEventListener('click', e => {
            e.preventDefault();
            const params = new URLSearchParams({
                ...(currentStatus && { status: currentStatus }),
                ...(currentCategory && { category: currentCategory }),
                ...(currentSearch && { search: currentSearch }),
            });
            window.location.href = `${EXPORT_URL}?${params}`;
        });

        // Init
        loadTickets(1, '', '', '');
    }

    /* =====================================================
       SHOW PAGE — Chi tiết Ticket
    ===================================================== */

    const showConfig = document.getElementById('ticketShowConfig');
    if (showConfig) {
        const ticketId      = showConfig.dataset.id;
        const ticketSubject = showConfig.dataset.subject;
        const IN_REVIEW_URL = showConfig.dataset.inReviewUrl;
        const RESPOND_URL   = showConfig.dataset.respondUrl;
        const CSRF          = showConfig.dataset.csrf;

        document.getElementById('btnInReviewTicket')?.addEventListener('click', () => {
            if (!confirm('Tiếp nhận xử lý yêu cầu này?')) return;
            fetch(IN_REVIEW_URL, {
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

        document.getElementById('btnRespondTicket')?.addEventListener('click', () => {
            openResponseModal(ticketId, ticketSubject, (id, note, status, modalInst, extra) => {
                fetch(RESPOND_URL, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        admin_response: note,
                        action_status: status,
                        unlock_seller: extra?.unlockSeller || false,
                        approve_product: extra?.approveProduct || false,
                    }),
                })
                    .then(r => r.json())
                    .then(json => {
                        if (modalInst) modalInst.hide();
                        showToast(json.message || 'Đã gửi phản hồi thành công!', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(() => showToast('Có lỗi xảy ra.', 'danger'));
            }, showConfig.dataset.category, showConfig.dataset.hasProduct === '1');
        });
    }

})();
