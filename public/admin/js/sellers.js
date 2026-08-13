/**
 * CUPO ADMIN — Trang Quản lý Seller
 * Xu ly: tai danh sach, tab filter, tim kiem, checkbox bulk, export CSV,
 *         modal chi tiet, cac hanh dong approve/reject/block
 *
 * Data context tu data-* attributes tren #sellersAppConfig (Rule 20)
 */

(function () {
    'use strict';

    /* ---- Config: lay tu data-* (khong dung window.* global) ---- */
    const cfg = document.getElementById('sellersAppConfig').dataset;
    const ROUTES = {
        index:       cfg.indexUrl,
        export:      cfg.exportUrl,
        bulkApprove: cfg.bulkApproveUrl,
        approve:     cfg.approveUrl,   // chua __ID__
        reject:      cfg.rejectUrl,    // chua __ID__
        block:       cfg.blockUrl,     // chua __ID__
        csrf:        cfg.csrf,
    };

    /* ---- Constants ---- */
    const STATUS_MAP = {
        pending:  { label: 'Chờ duyệt', cls: 'badge-pending' },
        approved: { label: 'Đã duyệt',  cls: 'badge-approved' },
        rejected: { label: 'Từ chối',   cls: 'badge-rejected' },
        blocked:  { label: 'Đã khóa',   cls: 'badge-blocked' },
    };

    /* ---- State ---- */
    let currentStatus = 'pending';   // Mac dinh tab "Cho duyet"
    let currentPage   = 1;
    let searchTimer   = null;
    let currentSeller = null;        // seller dang mo trong modal chi tiet
    let pendingAction = null;        // { action, sellerId, requireNote }
    let selectedIds   = new Set();   // ID da chon qua checkbox

    /* ---- DOM refs ---- */
    const tbody         = document.getElementById('sellersTableBody');
    const paginationWrap  = document.getElementById('paginationWrap');
    const paginationInfo  = document.getElementById('paginationInfo');
    const paginationLinks = document.getElementById('paginationLinks');
    const searchInput   = document.getElementById('sellerSearchInput');
    const tabButtons    = document.querySelectorAll('.seller-tab');
    const checkAll      = document.getElementById('checkAllSellers');
    const bulkToolbar   = document.getElementById('bulkToolbar');
    const bulkCount     = document.getElementById('bulkCount');
    const btnBulkApprove = document.getElementById('btnBulkApprove');
    const btnBulkClear   = document.getElementById('btnBulkClear');
    const btnExport      = document.getElementById('btnExportCsv');

    const detailModal = new bootstrap.Modal(document.getElementById('sellerDetailModal'));
    const actionModal = new bootstrap.Modal(document.getElementById('sellerActionModal'));
    const actionToast = new bootstrap.Toast(document.getElementById('actionToast'), { delay: 3000 });

    /* ================================================================
       LOAD DATA
       ================================================================ */
    function loadSellers(page = 1) {
        currentPage = page;
        showLoading();
        clearCheckboxState();

        const keyword = searchInput.value.trim();
        let url = ROUTES.index + '?page=' + page;
        if (currentStatus) url += '&status=' + currentStatus;
        if (keyword)       url += '&search=' + encodeURIComponent(keyword);

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
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                    Đang tải dữ liệu .....
                </td>
            </tr>`;
        paginationWrap.style.display = 'none';
    }

    function showError() {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4 text-danger">
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
                    <td colspan="9">
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

            const bizLabel  = s.business_type === 'company' ? 'Doanh nghiệp' : 'Cá nhân';
            const commRate  = s.commission_rate ? s.commission_rate + '%' : '--';
            const regDate   = s.created_at ? s.created_at.substring(0, 10) : '--';
            const waitBadge = s.status === 'pending' ? daysBadge(s.created_at) : '';
            const isChecked = selectedIds.has(s.id) ? 'checked' : '';

            return `
                <tr data-id="${s.id}">
                    <td style="text-align:center;">
                        <input type="checkbox" class="seller-checkbox row-seller-check"
                            data-id="${s.id}" ${isChecked}>
                    </td>
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
                    <td style="font-size: 13px; color: #6c757d;">
                        ${regDate}
                        ${waitBadge}
                    </td>
                    <td><span class="badge-status ${st.cls}">${st.label}</span></td>
                    <td style="text-align: center;">
                        <button class="btn-row-detail" title="Xem chi tiết" onclick="openSellerDetail(${JSON.stringify(s).replace(/"/g, '&quot;')})">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');

        // Re-attach checkbox listeners
        tbody.querySelectorAll('.row-seller-check').forEach(cb => {
            cb.addEventListener('change', onRowCheckChange);
        });
        syncCheckAll();
    }

    /* ================================================================
       PAGINATION
       ================================================================ */
    function renderPagination(json) {
        const total    = json.total    ?? 0;
        const perPage  = json.per_page ?? 10;
        const page     = json.current_page ?? 1;
        const lastPage = json.last_page    ?? 1;

        if (total === 0) { paginationWrap.style.display = 'none'; return; }

        paginationWrap.style.display = '';
        const from = (page - 1) * perPage + 1;
        const to   = Math.min(page * perPage, total);
        paginationInfo.textContent = `Hiển thị ${from}–${to} / ${total} gian hàng`;

        let btns = '';
        btns += `<button class="page-btn" onclick="goPage(${page - 1})" ${page === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                 </button>`;

        for (let p = 1; p <= lastPage; p++) {
            if (lastPage > 7 && Math.abs(p - page) > 2 && p !== 1 && p !== lastPage) {
                if (p === 2 || p === lastPage - 1)
                    btns += `<span class="page-btn" style="cursor:default; border:none;">...</span>`;
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
       STAT COUNTS
       ================================================================ */
    function updateStatCounts(meta) {
        if (!meta) return;
        safeSet('count-all',      meta.total_all     ?? meta.total ?? '--');
        safeSet('count-pending',  meta.total_pending  ?? '--');
        safeSet('count-approved', meta.total_approved ?? '--');
        safeSet('count-blocked',  meta.total_blocked  ?? '--');

        const badgePending = document.getElementById('tab-badge-pending');
        if (badgePending && meta.total_pending != null) {
            badgePending.textContent    = meta.total_pending;
            badgePending.style.display  = meta.total_pending > 0 ? '' : 'none';
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
       CHECKBOX — BULK SELECT
       ================================================================ */
    function onRowCheckChange(e) {
        const id = Number(e.target.dataset.id);
        if (e.target.checked) selectedIds.add(id);
        else                  selectedIds.delete(id);
        e.target.closest('tr').classList.toggle('seller-row-selected', e.target.checked);
        syncCheckAll();
        updateBulkToolbar();
    }

    checkAll.addEventListener('change', function () {
        const checked = this.checked;
        tbody.querySelectorAll('.row-seller-check').forEach(cb => {
            cb.checked = checked;
            const id   = Number(cb.dataset.id);
            if (checked) selectedIds.add(id);
            else         selectedIds.delete(id);
            cb.closest('tr').classList.toggle('seller-row-selected', checked);
        });
        updateBulkToolbar();
    });

    function syncCheckAll() {
        const all    = tbody.querySelectorAll('.row-seller-check');
        const ticked = tbody.querySelectorAll('.row-seller-check:checked');
        checkAll.indeterminate = ticked.length > 0 && ticked.length < all.length;
        checkAll.checked       = all.length > 0 && ticked.length === all.length;
    }

    function updateBulkToolbar() {
        const n = selectedIds.size;
        bulkToolbar.style.display = n > 0 ? '' : 'none';
        bulkCount.textContent     = n;
    }

    function clearCheckboxState() {
        selectedIds.clear();
        checkAll.checked       = false;
        checkAll.indeterminate = false;
        bulkToolbar.style.display = 'none';
    }

    /* ================================================================
       BULK APPROVE
       ================================================================ */
    btnBulkApprove.addEventListener('click', function () {
        if (selectedIds.size === 0) return;
        if (!confirm(`Duyệt ${selectedIds.size} gian hàng đã chọn?\n\nChỉ những gian hàng ở trạng thái chờ duyệt / từ chối / bị khóa mới được cập nhật.`)) return;

        this.disabled    = true;
        this.textContent = 'Đang xử lý...';

        fetch(ROUTES.bulkApprove, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': ROUTES.csrf,
            },
            body: JSON.stringify({ ids: [...selectedIds] }),
        })
            .then(r => r.json())
            .then(json => {
                showToast(json.message ?? 'Đã duyệt thành công!', 'success');
                loadSellers(currentPage);
            })
            .catch(() => showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error'))
            .finally(() => {
                this.disabled    = false;
                this.innerHTML   = '<i class="fa-solid fa-check-double"></i> Duyệt tất cả đã chọn';
            });
    });

    btnBulkClear.addEventListener('click', function () {
        clearCheckboxState();
        tbody.querySelectorAll('.row-seller-check').forEach(cb => {
            cb.checked = false;
            cb.closest('tr').classList.remove('seller-row-selected');
        });
        checkAll.checked = false;
    });

    /* ================================================================
       EXPORT CSV
       ================================================================ */
    btnExport.addEventListener('click', function (e) {
        e.preventDefault();
        const keyword = searchInput.value.trim();
        let url       = ROUTES.export;
        const params  = [];
        if (currentStatus) params.push('status=' + currentStatus);
        if (keyword)       params.push('search=' + encodeURIComponent(keyword));
        if (params.length) url += '?' + params.join('&');

        // Trigger download bang iframe an
        const a = document.createElement('a');
        a.href  = url;
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        showToast('Đang tải file CSV...', 'success');
    });

    /* ================================================================
       MODAL CHI TIET SELLER
       ================================================================ */
    window.openSellerDetail = function (seller) {
        currentSeller = seller;
        const st = STATUS_MAP[seller.status] || { label: seller.status, cls: '' };

        const logoEl = document.getElementById('modalShopLogo');
        if (seller.logo) {
            logoEl.innerHTML = `<img src="${seller.logo}" style="width:100%;height:100%;object-fit:cover;border-radius:8px;" alt="">`;
        } else {
            logoEl.textContent = seller.shop_name.charAt(0).toUpperCase();
        }

        setText('modalShopName', seller.shop_name);
        setText('modalShopSlug', '@' + seller.slug);

        const noteWrapEl = document.getElementById('adminNoteWrap');
        if (seller.admin_note) {
            noteWrapEl.classList.remove('d-none');
            setText('adminNoteText', 'Ghi chú Admin: ' + seller.admin_note);
        } else {
            noteWrapEl.classList.add('d-none');
        }

        setText('dOwnerName',     seller.user?.name    ?? '--');
        setText('dOwnerEmail',    seller.user?.email   ?? '--');
        setText('dBusinessType',  seller.business_type === 'company' ? 'Doanh nghiệp' : 'Cá nhân');

        const catNames = (seller.categories && seller.categories.length > 0)
            ? seller.categories.map(c => c.name).join(', ')
            : 'Chưa chọn';
        setText('dCategories',    catNames);
        setText('dFollowersCount', (seller.followers_count ?? 0) + ' lượt');
        setText('dAddress',       seller.address     ?? '--');
        setText('dNationalId',    seller.national_id ?? '--');
        setText('dCommission',    seller.commission_rate ? seller.commission_rate + '%' : '--');
        setText('dBalance',       seller.balance != null ? formatVnd(seller.balance) : '--');
        setText('dBankName',      seller.bank_name    ?? '--');
        setText('dBankAccount',   seller.bank_account ?? '--');
        setText('dBankOwner',     seller.bank_owner   ?? '--');

        // Ngay dang ky + so ngay cho
        const regDate = seller.created_at ? seller.created_at.substring(0, 10) : '--';
        const waitTxt = seller.status === 'pending' ? ' ' + daysText(seller.created_at) : '';
        setText('dRegDate', regDate + waitTxt);

        // Status badge
        document.getElementById('modalStatusBadge').innerHTML =
            `<span class="badge-status ${st.cls}">${st.label}</span>`;

        // Action buttons
        const btnsEl = document.getElementById('modalActionBtns');
        btnsEl.innerHTML = '';
        if (seller.status === 'pending') {
            btnsEl.innerHTML += `
                <button class="btn-action-reject" onclick="openActionModal('reject')">
                    <i class="fa-solid fa-xmark"></i> Từ chối
                </button>
                <button class="btn-action-approve" onclick="openActionModal('approve')">
                    <i class="fa-solid fa-check"></i> Duyệt gian hàng
                </button>`;
        } else if (seller.status === 'approved') {
            btnsEl.innerHTML += `
                <button class="btn-action-block" onclick="openActionModal('block')">
                    <i class="fa-solid fa-ban"></i> Khóa gian hàng
                </button>`;
        } else if (seller.status === 'blocked' || seller.status === 'rejected') {
            btnsEl.innerHTML += `
                <button class="btn-action-approve" onclick="openActionModal('approve')">
                    <i class="fa-solid fa-rotate-left"></i> Mở khóa / Duyệt lại
                </button>`;
        }

        detailModal.show();
    };

    /* ================================================================
       HANH DONG: APPROVE / REJECT / BLOCK
       ================================================================ */
    window.openActionModal = function (action) {
        pendingAction = { action, sellerId: currentSeller.id };

        const shopName = escHtml(currentSeller.shop_name);

        const cfgMap = {
            approve: {
                title:       'Xác nhận duyệt gian hàng',
                desc:        `Bạn chắc chắn muốn duyệt gian hàng "${shopName}"? Sau khi duyệt, người bán có thể bắt đầu kinh doanh trên Cupo.`,
                btnCls:      'btn-success',
                btnText:     'Xác nhận duyệt',
                requireNote: false,
            },
            reject: {
                title:       'Từ chối gian hàng',
                desc:        'Gian hàng sẽ bị từ chối. Người bán sẽ nhận được thông báo kèm lí do bên dưới.',
                btnCls:      'btn-danger',
                btnText:     'Xác nhận từ chối',
                requireNote: true,
            },
            block: {
                title:       'Khóa gian hàng',
                desc:        'Gian hàng sẽ bị khóa ngay lập tức. Người bán không thể bán hàng cho đến khi mở khóa.',
                btnCls:      'btn-danger',
                btnText:     'Xác nhận khóa',
                requireNote: true,
            },
        };

        const c = cfgMap[action];
        pendingAction.requireNote = c.requireNote;

        document.getElementById('actionModalTitle').textContent = c.title;
        document.getElementById('actionModalDesc').textContent  = c.desc;
        document.getElementById('actionNote').value             = '';
        document.getElementById('actionNoteError').classList.add('d-none');

        const noteWrap = document.getElementById('actionNoteWrap');
        if (noteWrap) noteWrap.style.display = c.requireNote ? '' : 'none';

        const confirmBtn = document.getElementById('confirmActionBtn');
        confirmBtn.className   = 'btn btn-sm ' + c.btnCls;
        confirmBtn.textContent = c.btnText;

        detailModal.hide();
        setTimeout(() => actionModal.show(), 300);
    };

    document.getElementById('confirmActionBtn').addEventListener('click', function () {
        if (!pendingAction) return;

        if (pendingAction.requireNote) {
            const note = document.getElementById('actionNote').value.trim();
            if (note.length < 10) {
                document.getElementById('actionNoteError').classList.remove('d-none');
                return;
            }
            document.getElementById('actionNoteError').classList.add('d-none');
            sendAction(pendingAction.action, pendingAction.sellerId, note);
        } else {
            sendAction(pendingAction.action, pendingAction.sellerId, null);
        }
    });

    /* ================================================================
       GUI REQUEST LEN BACKEND
       ================================================================ */
    function sendAction(action, sellerId, note) {
        const url  = ROUTES[action].replace('__ID__', sellerId);
        const body = {};
        if (note) body.admin_note = note;

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
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
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatVnd(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    function daysWaiting(createdAt) {
        if (!createdAt) return 0;
        return Math.floor((Date.now() - new Date(createdAt).getTime()) / 86400000);
    }

    function daysBadge(createdAt) {
        const d = daysWaiting(createdAt);
        if (d === 0) return `<span class="waiting-badge new">Hôm nay</span>`;
        return `<span class="waiting-badge${d >= 3 ? ' urgent' : ''}">chờ ${d} ngày</span>`;
    }

    function daysText(createdAt) {
        const d = daysWaiting(createdAt);
        if (d === 0) return '(hôm nay)';
        return `(chờ ${d} ngày${d >= 3 ? ' ⚠️' : ''})`;
    }

    /* ================================================================
       KHOI DONG — Mac dinh tab "Cho duyet"
       ================================================================ */
    (function initDefaultTab() {
        tabButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.status === 'pending');
        });
    })();

    loadSellers(1);

})();
