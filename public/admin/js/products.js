/**
 * CUPO ADMIN — Trang Quản lý Sản phẩm
 * Xử lý: tải danh sách, tab filter, tìm kiếm, checkbox bulk, export CSV,
 *         modal chi tiết, các hành động approve/reject
 *
 * Data context từ data-* attributes trên #productsAppConfig (Rule 20)
 */

(function () {
    'use strict';

    /* ---- Config: lấy từ data-* (không dùng window.* global) ---- */
    const appEl = document.getElementById('productsAppConfig');
    if (!appEl) return;

    const cfg = appEl.dataset;
    const ROUTES = {
        index:       cfg.indexUrl,
        export:      cfg.exportUrl,
        bulkApprove: cfg.bulkApproveUrl,
        bulkReject:  cfg.bulkRejectUrl,
        approve:     cfg.approveUrl,   // chứa __ID__
        reject:      cfg.rejectUrl,    // chứa __ID__
        csrf:        cfg.csrf,
    };

    /* ---- Constants ---- */
    const STATUS_MAP = {
        pending:  { label: 'Chờ duyệt', cls: 'badge-pending' },
        approved: { label: 'Đã duyệt',  cls: 'badge-approved' },
        rejected: { label: 'Từ chối',   cls: 'badge-rejected' },
    };

    /* ---- Helper helper to resolve image URL ---- */
    function resolveImgUrl(p) {
        if (!p) return '';
        if (p.thumbnail_url) return p.thumbnail_url;
        if (!p.thumbnail) return '';
        if (p.thumbnail.startsWith('http://') || p.thumbnail.startsWith('https://')) return p.thumbnail;
        return '/storage/' + p.thumbnail.replace(/^\//, '');
    }

    /* ---- State ---- */
    let currentStatus  = 'pending';   // Mặc định tab "Chờ duyệt"
    let currentPage    = 1;
    let searchTimer    = null;
    let currentProduct = null;        // product đang mở trong modal chi tiết
    let pendingAction  = null;        // { action, productId, requireNote, isBulk }
    let selectedIds    = new Set();   // ID đã chọn qua checkbox

    /* ---- DOM refs ---- */
    const tbody          = document.getElementById('productsTableBody');
    const paginationWrap   = document.getElementById('paginationWrap');
    const paginationInfo   = document.getElementById('paginationInfo');
    const paginationLinks  = document.getElementById('paginationLinks');
    const searchInput    = document.getElementById('productSearchInput');
    const tabButtons     = document.querySelectorAll('.seller-tab');
    const checkAll       = document.getElementById('checkAllProducts');
    const bulkToolbar    = document.getElementById('bulkToolbar');
    const bulkCount      = document.getElementById('bulkCount');
    const btnBulkApprove = document.getElementById('btnBulkApprove');
    const btnBulkReject  = document.getElementById('btnBulkReject');
    const btnBulkClear   = document.getElementById('btnBulkClear');
    const btnExport      = document.getElementById('btnExportProductCsv');

    const detailModal = new bootstrap.Modal(document.getElementById('productDetailModal'));
    const actionModal = new bootstrap.Modal(document.getElementById('productActionModal'));
    const actionToast = new bootstrap.Toast(document.getElementById('actionToast'), { delay: 3000 });

    /* ================================================================
       LOAD DATA
       ================================================================ */
    function loadProducts(page = 1) {
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
                <td colspan="10" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                    Đang tải dữ liệu .....
                </td>
            </tr>`;
        paginationWrap.style.display = 'none';
    }

    function showError() {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-4 text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Không thể tải dữ liệu. Vui lòng thử lại
                </td>
            </tr>`;
    }

    /* ================================================================
       RENDER TABLE
       ================================================================ */
    function renderTable(products) {
        if (!products || products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10">
                        <div class="products-empty">
                            <i class="fa-solid fa-box-open"></i>
                            <p>Không có sản phẩm nào phù hợp</p>
                        </div>
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = products.map((p, i) => {
            const st = STATUS_MAP[p.status] || { label: p.status, cls: '' };
            const thumbUrl = resolveImgUrl(p);
            const thumb = thumbUrl
                ? `<img src="${thumbUrl}" class="product-thumb-sm" alt="${escHtml(p.name)}">`
                : `<div class="product-thumb-letter">${p.name.charAt(0).toUpperCase()}</div>`;

            const shopName  = escHtml(p.seller?.seller_profile?.shop_name ?? p.seller?.name ?? 'N/A');
            const sellerEmail = escHtml(p.seller?.email ?? '');
            const categoryName = escHtml(p.category?.name ?? 'Chưa chọn');
            const price = formatVnd(p.price);
            const stock = p.stock ?? 0;
            const regDate = p.created_at ? p.created_at.substring(0, 10) : '--';
            const waitBadge = p.status === 'pending' ? daysBadge(p.created_at) : '';
            const isChecked = selectedIds.has(p.id) ? 'checked' : '';

            return `
                <tr data-id="${p.id}">
                    <td style="text-align:center;">
                        <input type="checkbox" class="seller-checkbox row-product-check"
                            data-id="${p.id}" ${isChecked}>
                    </td>
                    <td class="text-muted" style="font-size: 12px;">${(currentPage - 1) * 10 + i + 1}</td>
                    <td>
                        <div class="product-cell">
                            ${thumb}
                            <div>
                                <div class="product-name-text" title="${escHtml(p.name)}">${escHtml(p.name)}</div>
                                <div class="product-sku-text">SKU: ${escHtml(p.sku ?? '--')}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="shop-name-sub">${shopName}</div>
                        <div class="seller-name-sub">${sellerEmail}</div>
                    </td>
                    <td style="font-size: 13px;">${categoryName}</td>
                    <td style="font-size: 13px; font-weight: 600;" class="text-danger">${price}</td>
                    <td style="font-size: 13px;">${stock}</td>
                    <td style="font-size: 13px; color: #6c757d;">
                        ${regDate}
                        ${waitBadge}
                    </td>
                    <td><span class="badge-status ${st.cls}">${st.label}</span></td>
                    <td style="text-align: center;">
                        <button class="btn-row-detail" title="Xem chi tiết" onclick="openProductDetail(${JSON.stringify(p).replace(/"/g, '&quot;')})">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');

        // Re-attach checkbox listeners
        tbody.querySelectorAll('.row-product-check').forEach(cb => {
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
        paginationInfo.textContent = `Hiển thị ${from}–${to} / ${total} sản phẩm`;

        let btns = '';
        btns += `<button class="page-btn" onclick="goProductPage(${page - 1})" ${page === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                 </button>`;

        for (let p = 1; p <= lastPage; p++) {
            if (lastPage > 7 && Math.abs(p - page) > 2 && p !== 1 && p !== lastPage) {
                if (p === 2 || p === lastPage - 1)
                    btns += `<span class="page-btn" style="cursor:default; border:none;">...</span>`;
                continue;
            }
            btns += `<button class="page-btn ${p === page ? 'active' : ''}" onclick="goProductPage(${p})">${p}</button>`;
        }

        btns += `<button class="page-btn" onclick="goProductPage(${page + 1})" ${page === lastPage ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
                 </button>`;

        paginationLinks.innerHTML = btns;
    }

    window.goProductPage = function (page) { loadProducts(page); };

    /* ================================================================
       STAT COUNTS
       ================================================================ */
    function updateStatCounts(meta) {
        if (!meta) return;
        safeSet('count-all',      meta.total_all      ?? meta.total ?? '--');
        safeSet('count-pending',  meta.total_pending  ?? '--');
        safeSet('count-approved', meta.total_approved ?? '--');
        safeSet('count-rejected', meta.total_rejected ?? '--');

        const badgePending = document.getElementById('tab-badge-pending');
        if (badgePending && meta.total_pending != null) {
            badgePending.textContent   = meta.total_pending;
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
            loadProducts(1);
        });
    });

    /* ================================================================
       SEARCH (debounce 350ms)
       ================================================================ */
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadProducts(1), 350);
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
        tbody.querySelectorAll('.row-product-check').forEach(cb => {
            cb.checked = checked;
            const id   = Number(cb.dataset.id);
            if (checked) selectedIds.add(id);
            else         selectedIds.delete(id);
            cb.closest('tr').classList.toggle('seller-row-selected', checked);
        });
        updateBulkToolbar();
    });

    function syncCheckAll() {
        const all    = tbody.querySelectorAll('.row-product-check');
        const ticked = tbody.querySelectorAll('.row-product-check:checked');
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
       BULK APPROVE / BULK REJECT
       ================================================================ */
    btnBulkApprove.addEventListener('click', function () {
        if (selectedIds.size === 0) return;
        if (!confirm(`Duyệt ${selectedIds.size} sản phẩm đã chọn?\n\nSản phẩm sẽ được hiển thị công khai trên sàn.`)) return;

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
                loadProducts(currentPage);
            })
            .catch(() => showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error'))
            .finally(() => {
                this.disabled  = false;
                this.innerHTML = '<i class="fa-solid fa-check-double"></i> Duyệt tất cả đã chọn';
            });
    });

    btnBulkReject.addEventListener('click', function () {
        if (selectedIds.size === 0) return;

        pendingAction = { action: 'reject', isBulk: true };

        document.getElementById('productActionModalTitle').textContent = 'Từ chối hàng loạt sản phẩm';
        document.getElementById('productActionModalDesc').textContent  = `Từ chối ${selectedIds.size} sản phẩm đã chọn. Người bán sẽ nhận được lý do bên dưới.`;
        document.getElementById('productActionNote').value             = '';
        document.getElementById('productActionNoteError').classList.add('d-none');

        const noteWrap = document.getElementById('productActionNoteWrap');
        if (noteWrap) noteWrap.style.display = '';

        const confirmBtn = document.getElementById('confirmProductActionBtn');
        confirmBtn.className   = 'btn btn-sm btn-danger';
        confirmBtn.textContent = 'Xác nhận từ chối tất cả';

        actionModal.show();
    });

    btnBulkClear.addEventListener('click', function () {
        clearCheckboxState();
        tbody.querySelectorAll('.row-product-check').forEach(cb => {
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

        const a = document.createElement('a');
        a.href  = url;
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        showToast('Đang tải file CSV...', 'success');
    });

    /* ================================================================
       MODAL CHI TIẾT SẢN PHẨM
       ================================================================ */
    window.openProductDetail = function (product) {
        currentProduct = product;
        const st = STATUS_MAP[product.status] || { label: product.status, cls: '' };

        const thumbEl = document.getElementById('modalProductThumb');
        const modalThumbUrl = resolveImgUrl(product);
        if (modalThumbUrl) {
            thumbEl.innerHTML = `<img src="${modalThumbUrl}" style="width:100%;height:100%;object-fit:cover;" alt="">`;
        } else {
            thumbEl.textContent = product.name.charAt(0).toUpperCase();
        }

        setText('modalProductName', product.name);
        setText('modalProductSku',  'SKU: ' + (product.sku ?? '--'));

        const noteWrapEl = document.getElementById('adminNoteWrap');
        if (product.admin_note) {
            noteWrapEl.classList.remove('d-none');
            setText('adminNoteText', 'Ghi chú Admin: ' + product.admin_note);
        } else {
            noteWrapEl.classList.add('d-none');
        }

        setText('dProductName', product.name);
        setText('dProductSku',  product.sku ?? '--');
        setText('dCategory',    product.category?.name ?? 'Chưa chọn');
        setText('dPrice',       formatVnd(product.price));
        setText('dStock',       product.stock ?? 0);
        setText('dHasVariants', product.has_variants ? 'Có biến thể' : 'Sản phẩm thường');
        setText('dViewsCount',  (product.views_count ?? 0) + ' lượt');
        setText('dShortDesc',   product.short_description ?? '--');
        document.getElementById('dFullDesc').innerHTML = product.description ?? 'Không có mô tả chi tiết';

        const shopName  = product.seller?.seller_profile?.shop_name ?? product.seller?.name ?? '--';
        const ownerName = product.seller?.name ?? '--';
        const ownerMail = product.seller?.email ?? '--';
        setText('dShopName',    shopName);
        setText('dSellerName',  ownerName);
        setText('dSellerEmail', ownerMail);

        const regDate = product.created_at ? product.created_at.substring(0, 10) : '--';
        const waitTxt = product.status === 'pending' ? ' ' + daysText(product.created_at) : '';
        setText('dCreatedAt', regDate + waitTxt);

        document.getElementById('modalStatusBadge').innerHTML =
            `<span class="badge-status ${st.cls}">${st.label}</span>`;

        const btnsEl = document.getElementById('modalActionBtns');
        btnsEl.innerHTML = '';
        if (product.status === 'pending') {
            btnsEl.innerHTML += `
                <button class="btn-action-reject" onclick="openProductActionModal('reject')">
                    <i class="fa-solid fa-xmark"></i> Từ chối
                </button>
                <button class="btn-action-approve" onclick="openProductActionModal('approve')">
                    <i class="fa-solid fa-check"></i> Duyệt sản phẩm
                </button>`;
        } else if (product.status === 'approved') {
            btnsEl.innerHTML += `
                <button class="btn-action-reject" onclick="openProductActionModal('reject')">
                    <i class="fa-solid fa-ban"></i> Gỡ sản phẩm vi phạm
                </button>`;
        } else if (product.status === 'rejected') {
            btnsEl.innerHTML += `
                <button class="btn-action-approve" onclick="openProductActionModal('approve')">
                    <i class="fa-solid fa-rotate-left"></i> Duyệt lại
                </button>`;
        }

        detailModal.show();
    };

    /* ================================================================
       HÀNH ĐỘNG: APPROVE / REJECT
       ================================================================ */
    window.openProductActionModal = function (action) {
        pendingAction = { action, productId: currentProduct.id, isBulk: false };

        const prodName = escHtml(currentProduct.name);

        const cfgMap = {
            approve: {
                title:       'Xác nhận duyệt sản phẩm',
                desc:        `Bạn chắc chắn muốn duyệt sản phẩm "${prodName}"? Sau khi duyệt, sản phẩm sẽ được hiển thị công khai trên sàn.`,
                btnCls:      'btn-success',
                btnText:     'Xác nhận duyệt',
                requireNote: false,
            },
            reject: {
                title:       'Từ chối / Gỡ sản phẩm',
                desc:        `Sản phẩm "${prodName}" sẽ bị chuyển thành trạng thái từ chối. Người bán sẽ nhận được lý do bên dưới.`,
                btnCls:      'btn-danger',
                btnText:     'Xác nhận từ chối',
                requireNote: true,
            },
        };

        const c = cfgMap[action];
        pendingAction.requireNote = c.requireNote;

        document.getElementById('productActionModalTitle').textContent = c.title;
        document.getElementById('productActionModalDesc').textContent  = c.desc;
        document.getElementById('productActionNote').value             = '';
        document.getElementById('productActionNoteError').classList.add('d-none');

        const noteWrap = document.getElementById('productActionNoteWrap');
        if (noteWrap) noteWrap.style.display = c.requireNote ? '' : 'none';

        const confirmBtn = document.getElementById('confirmProductActionBtn');
        confirmBtn.className   = 'btn btn-sm ' + c.btnCls;
        confirmBtn.textContent = c.btnText;

        detailModal.hide();
        setTimeout(() => actionModal.show(), 300);
    };

    document.getElementById('confirmProductActionBtn').addEventListener('click', function () {
        if (!pendingAction) return;

        if (pendingAction.isBulk) {
            // Bulk reject
            const note = document.getElementById('productActionNote').value.trim();
            if (note.length < 10) {
                document.getElementById('productActionNoteError').classList.remove('d-none');
                return;
            }
            document.getElementById('productActionNoteError').classList.add('d-none');

            fetch(ROUTES.bulkReject, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': ROUTES.csrf,
                },
                body: JSON.stringify({ ids: [...selectedIds], admin_note: note }),
            })
                .then(r => r.json())
                .then(json => {
                    actionModal.hide();
                    showToast(json.message ?? 'Đã từ chối hàng loạt sản phẩm!', 'success');
                    loadProducts(currentPage);
                })
                .catch(() => showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error'));
            return;
        }

        if (pendingAction.requireNote) {
            const note = document.getElementById('productActionNote').value.trim();
            if (note.length < 10) {
                document.getElementById('productActionNoteError').classList.remove('d-none');
                return;
            }
            document.getElementById('productActionNoteError').classList.add('d-none');
            sendProductAction(pendingAction.action, pendingAction.productId, note);
        } else {
            sendProductAction(pendingAction.action, pendingAction.productId, null);
        }
    });

    /* ================================================================
       GỬI REQUEST LÊN BACKEND
       ================================================================ */
    function sendProductAction(action, productId, note) {
        const url  = ROUTES[action].replace('__ID__', productId);
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
                loadProducts(currentPage);
            })
            .catch(() => {
                showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
            });
    }

    /* ================================================================
       TOAST & UTILS
       ================================================================ */
    function showToast(msg, type) {
        const toastEl = document.getElementById('actionToast');
        toastEl.className = 'toast align-items-center border-0 toast-' + type;
        document.getElementById('toastMsg').textContent = msg;
        actionToast.show();
    }

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
        if (amount == null) return '--';
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
       KHỞI ĐỘNG — Mặc định tab "Chờ duyệt"
       ================================================================ */
    (function initDefaultTab() {
        tabButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.status === 'pending');
        });
    })();

    loadProducts(1);

})();
