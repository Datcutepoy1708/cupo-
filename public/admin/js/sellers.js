/**
 * CUPO ADMIN — Trang Quan ly Seller
 * Xu ly: tai danh sach, tab filter, tim kiem, modal chi tiet, cac hanh dong approve/reject/block
 */

(function () {
    'use strict';

    /* ---- Constants ---- */
    const ROUTES = window.ADMIN_SELLERS_ROUTES;
    const STATUS_MAP = {
        pending: { label: 'Chờ duyệt', cls: 'badge-pending' },
        approved: { label: 'Đã duyệt', cls: 'badge-approved' },
        rejected: { label: 'Từ chối', cls: 'badge-rejected' },
        blocked: { label: 'Đã khóa', cls: 'badge-blocked' },
    };

    /* ---- State ---- */
    let currentStatus = '';
    let currentPage = 1;
    let searchTimer = null;
    let currentSeller = null;      // seller dang mo trong modal chi tiet
    let pendingAction = null;      // { action: 'reject'|'block', sellerId }

    /* ---- DOM refs ---- */
    const tbody = document.getElementById('sellersTableBody');
    const paginationWrap = document.getElementById('paginationWrap');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationLinks = document.getElementById('paginationLinks');
    const searchInput = document.getElementById('sellerSearchInput');
    const tabButtons = document.querySelectorAll('.seller-tab');

    const detailModal = new bootstrap.Modal(document.getElementById('sellerDetailModal'));
    const actionModal = new bootstrap.Modal(document.getElementById('sellerActionModal'));
    const actionToast = new bootstrap.Toast(document.getElementById('actionToast'), { delay: 3000 });

    /* ================================================================
       LOAD DATA
       ================================================================ */
    function loadSellers(page = 1) {
        currentPage = page;
        showLoading();

        const keyword = searchInput.value.trim();
        let url = ROUTES.index + '?page=' + page;
        if (currentStatus) url += '&status=' + currentStatus;
        if (keyword) url += '&search=' + encodeURIComponent(keyword);

        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': ROUTES.csrf },
        })
            .then(r => r.json())
            .then(json => {
                renderTable(json.data || json);
                renderPagination(json);
                updateStatCounts(json.meta || json);
            })
            .catch(() => showError());
    }

    function showLoading() {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                    Đang tải dữ liệu .....
                </td>
            </tr>`;
        paginationWrap.style.display = 'none';
    }

    function showError() {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Không thể tải dữ liệu. Vui lòng thử lại
                </td>
            </tr>`;
    }

    /* ================================================================
       RENDER TABLE
       ================================================================ */
    function renderTable(sellers) {
        if (!sellers || sellers.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8">
                        <div class="sellers-empty">
                            <i class="fa-solid fa-store-slash"></i>
                            <p>Không có gian hàng nào phù hợp</p>
                        </div>
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = sellers.map((s, i) => {
            const st = STATUS_MAP[s.status] || { label: s.status, cls: '' };
            const logo = s.logo
                ? `<img src="${s.logo}" class="shop-logo-sm" alt="${s.shop_name}">`
                : `<div class="shop-logo-letter">${s.shop_name.charAt(0).toUpperCase()}</div>`;

            const bizLabel = s.business_type === 'company' ? 'Doanh nghiệp' : 'Cá nhân';
            const commRate = s.commission_rate ? s.commission_rate + '%' : '--';
            const regDate = s.created_at ? s.created_at.substring(0, 10) : '--';

            return `
                <tr>
                    <td class="text-muted" style="font-size: 12px;">${(currentPage - 1) * 10 + i + 1}</td>
                    <td>
                        <div class="shop-cell">
                            ${logo}
                            <div>
                                <div class="shop-name-text">${escHtml(s.shop_name)}</div>
                                <div class="shop-slug-text">@${escHtml(s.slug)}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="owner-name">${escHtml(s.user?.name ?? '--')}</div>
                        <div class="owner-email">${escHtml(s.user?.email ?? '')}</div>
                    </td>
                    <td style="font-size: 13px;">${bizLabel}</td>
                    <td style="font-size: 13px;">${commRate}</td>
                    <td style="font-size: 13px; color: #6c757d;">${regDate}</td>
                    <td><span class="badge-status ${st.cls}">${st.label}</span></td>
                    <td style="text-align: center;">
                        <button class="btn-row-detail" title="Xem chi tiết" onclick="openSellerDetail(${JSON.stringify(s).replace(/"/g, '&quot;')})">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');
    }

    /* ================================================================
       PAGINATION
       ================================================================ */
    function renderPagination(json) {
        const total = json.total ?? 0;
        const perPage = json.per_page ?? 10;
        const page = json.current_page ?? 1;
        const lastPage = json.last_page ?? 1;

        if (total === 0) {
            paginationWrap.style.display = 'none';
            return;
        }

        paginationWrap.style.display = '';
        const from = (page - 1) * perPage + 1;
        const to = Math.min(page * perPage, total);
        paginationInfo.textContent = `Hiển thị ${from}–${to} / ${total} gian hàng`;

        let btns = '';
        btns += `<button class="page-btn" onclick="goPage(${page - 1})" ${page === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                 </button>`;

        for (let p = 1; p <= lastPage; p++) {
            if (lastPage > 7 && Math.abs(p - page) > 2 && p !== 1 && p !== lastPage) {
                if (p === 2 || p === lastPage - 1) btns += `<span class="page-btn" style="cursor:default; border:none;">...</span>`;
                continue;
            }
            btns += `<button class="page-btn ${p === page ? 'active' : ''}" onclick="goPage(${p})">${p}</button>`;
        }

        btns += `<button class="page-btn" onclick="goPage(${page + 1})" ${page === lastPage ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
                 </button>`;

        paginationLinks.innerHTML = btns;
    }

    window.goPage = function (page) { loadSellers(page); };

    /* ================================================================
       STAT COUNTS (lay tu response meta hoac tu API rieng neu co)
       ================================================================ */
    function updateStatCounts(meta) {
        if (!meta) return;
        safeSet('count-all', meta.total_all ?? meta.total ?? '--');
        safeSet('count-pending', meta.total_pending ?? '--');
        safeSet('count-approved', meta.total_approved ?? '--');
        safeSet('count-blocked', meta.total_blocked ?? '--');

        // Cap nhat badge tren tab "Cho duyet"
        const badgePending = document.getElementById('tab-badge-pending');
        if (badgePending && meta.total_pending != null) {
            badgePending.textContent = meta.total_pending;
            badgePending.style.display = meta.total_pending > 0 ? '' : 'none';
        }
    }

    /* ================================================================
       TABS
       ================================================================ */
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentStatus = this.dataset.status;
            loadSellers(1);
        });
    });

    /* ================================================================
       SEARCH (debounce 350ms)
       ================================================================ */
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadSellers(1), 350);
    });

    /* ================================================================
       MODAL CHI TIET SELLER
       ================================================================ */
    window.openSellerDetail = function (seller) {
        currentSeller = seller;
        const st = STATUS_MAP[seller.status] || { label: seller.status, cls: '' };

        // Logo header
        const logoEl = document.getElementById('modalShopLogo');
        if (seller.logo) {
            logoEl.innerHTML = `<img src="${seller.logo}" style="width:100%;height:100%;object-fit:cover;border-radius:8px;" alt="">`;
        } else {
            logoEl.textContent = seller.shop_name.charAt(0).toUpperCase();
        }

        setText('modalShopName', seller.shop_name);
        setText('modalShopSlug', '@' + seller.slug);

        // Admin note
        const noteWrap = document.getElementById('adminNoteWrap');
        if (seller.admin_note) {
            noteWrap.classList.remove('d-none');
            setText('adminNoteText', 'Ghi chú Admin: ' + seller.admin_note);
        } else {
            noteWrap.classList.add('d-none');
        }

        // Info
        setText('dOwnerName', seller.user?.name ?? '--');
        setText('dOwnerEmail', seller.user?.email ?? '--');
        setText('dBusinessType', seller.business_type === 'company' ? 'Doanh nghiệp' : 'Cá nhân');

        const catNames = (seller.categories && seller.categories.length > 0)
            ? seller.categories.map(c => c.name).join(', ')
            : 'Chưa chọn';
        setText('dCategories', catNames);
        setText('dFollowersCount', (seller.followers_count ?? 0) + ' lượt');

        setText('dAddress', seller.address ?? '--');
        setText('dNationalId', seller.national_id ?? '--');
        setText('dCommission', seller.commission_rate ? seller.commission_rate + '%' : '--');
        setText('dBalance', seller.balance != null ? formatVnd(seller.balance) : '--');
        setText('dBankName', seller.bank_name ?? '--');
        setText('dBankAccount', seller.bank_account ?? '--');
        setText('dBankOwner', seller.bank_owner ?? '--');

        // Status badge
        document.getElementById('modalStatusBadge').innerHTML =
            `<span class="badge-status ${st.cls}">${st.label}</span>`;

        // Action buttons tuy theo status
        const btnsEl = document.getElementById('modalActionBtns');
        btnsEl.innerHTML = '';
        if (seller.status === 'pending') {
            btnsEl.innerHTML += `
                <button class="btn-action-reject" onclick="openActionModal('reject')">
                    <i class="fa-solid fa-xmark"></i> Từ chối
                </button>
                <button class="btn-action-approve" onclick="doApprove()">
                    <i class="fa-solid fa-check"></i> Duyệt gian hàng
                </button>`;
        } else if (seller.status === 'approved') {
            btnsEl.innerHTML += `
                <button class="btn-action-block" onclick="openActionModal('block')">
                    <i class="fa-solid fa-ban"></i> Khóa gian hàng
                </button>`;
        } else if (seller.status === 'blocked' || seller.status === 'rejected') {
            btnsEl.innerHTML += `
                <button class="btn-action-approve" onclick="doApprove()">
                    <i class="fa-solid fa-rotate-left"></i> Mở khóa / Duyệt lại
                </button>`;
        }

        detailModal.show();
    };

    /* ================================================================
       HANH DONG: APPROVE (khong can ly do)
       ================================================================ */
    window.doApprove = function () {
        if (!currentSeller) return;
        sendAction('approve', currentSeller.id, null);
    };

    /* ================================================================
       HANH DONG: REJECT / BLOCK (can nhap ly do)
       ================================================================ */
    window.openActionModal = function (action) {
        pendingAction = { action, sellerId: currentSeller.id };

        const cfg = {
            reject: {
                title: 'Từ chối gian hàng',
                desc: 'Gian hàng sẽ bị từ chối. Nguời bán sẽ nhận được thông báo kèm lí do bên dưới',
                btnCls: 'btn-danger',
                btnText: 'Xác nhận từ chối',
            },
            block: {
                title: 'Khoa gian hang',
                desc: 'Gian hang sẽ bị khóa ngay lập tức. Người bán không thể bán hàng cho đến khi mở khóa',
                btnCls: 'btn-danger',
                btnText: 'Xác nhận khóa',
            },
        }[action];

        document.getElementById('actionModalTitle').textContent = cfg.title;
        document.getElementById('actionModalDesc').textContent = cfg.desc;
        document.getElementById('actionNote').value = '';
        document.getElementById('actionNoteError').classList.add('d-none');

        const confirmBtn = document.getElementById('confirmActionBtn');
        confirmBtn.className = 'btn btn-sm ' + cfg.btnCls;
        confirmBtn.textContent = cfg.btnText;

        // Dong detail modal, mo action modal
        detailModal.hide();
        setTimeout(() => actionModal.show(), 300);
    };

    document.getElementById('confirmActionBtn').addEventListener('click', function () {
        const note = document.getElementById('actionNote').value.trim();
        if (note.length < 10) {
            document.getElementById('actionNoteError').classList.remove('d-none');
            return;
        }
        document.getElementById('actionNoteError').classList.add('d-none');
        if (pendingAction) {
            sendAction(pendingAction.action, pendingAction.sellerId, note);
        }
    });

    /* ================================================================
       GUI REQUEST HANH DONG LEN BACKEND
       ================================================================ */
    function sendAction(action, sellerId, note) {
        const url = ROUTES[action].replace('__ID__', sellerId);
        const body = {};
        if (note) body.admin_note = note;

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': ROUTES.csrf,
            },
            body: JSON.stringify(body),
        })
            .then(r => r.json())
            .then(json => {
                actionModal.hide();
                detailModal.hide();
                showToast(json.message ?? 'Thành công!', 'success');
                loadSellers(currentPage);
            })
            .catch(() => {
                showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
            });
    }

    /* ================================================================
       TOAST
       ================================================================ */
    function showToast(msg, type) {
        const toastEl = document.getElementById('actionToast');
        toastEl.className = 'toast align-items-center border-0 toast-' + type;
        document.getElementById('toastMsg').textContent = msg;
        actionToast.show();
    }

    /* ================================================================
       UTILS
       ================================================================ */
    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? '--';
    }

    function safeSet(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatVnd(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    /* ================================================================
       KHOI DONG
       ================================================================ */
    loadSellers(1);

})();
