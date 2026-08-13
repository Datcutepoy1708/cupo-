/**
 * CUPO ADMIN — Trang Quản lý Mã giảm giá (Coupons / Vouchers)
 * Xử lý: tải danh sách AJAX, tab lọc trạng thái, lọc loại & phạm vi, tìm kiếm,
 *         modal tạo/sửa kèm live preview thẻ voucher, modal chi tiết & lịch sử dùng,
 *         bật/tắt trạng thái, xóa đơn & xóa hàng loạt, copy mã code.
 */

(function () {
    'use strict';

    /* ---- 0. Config: lay tu data-* tren #couponsAppConfig ---- */
    const appEl = document.getElementById('couponsAppConfig');
    if (!appEl) return;

    const cfg = appEl.dataset;
    const ROUTES = {
        index:        cfg.indexUrl,
        store:        cfg.storeUrl,
        update:       cfg.updateUrl,       // chua __ID__
        show:         cfg.showUrl,         // chua __ID__
        destroy:      cfg.destroyUrl,      // chua __ID__
        toggleStatus: cfg.toggleStatusUrl, // chua __ID__
        bulkStatus:   cfg.bulkStatusUrl,
        bulkDelete:   cfg.bulkDeleteUrl,
        csrf:         cfg.csrf,
    };

    /* ---- State ---- */
    let currentStatus   = '';          // '', 'active', 'upcoming', 'expired', 'inactive'
    let currentScope    = '';          // '', 'platform', 'shop'
    let currentType     = '';          // '', 'fixed_amount', 'percentage'
    let currentPage     = 1;
    let searchTimer     = null;
    let editingId       = null;
    let deletingId      = null;
    let selectedIds     = new Set();

    /* ---- DOM Elements ---- */
    const tbody           = document.getElementById('couponsTableBody');
    const paginationWrap  = document.getElementById('paginationWrap');
    const paginationInfo  = document.getElementById('paginationInfo');
    const paginationLinks = document.getElementById('paginationLinks');
    const searchInput     = document.getElementById('couponSearchInput');
    const scopeFilter     = document.getElementById('couponScopeFilter');
    const typeFilter      = document.getElementById('couponTypeFilter');
    const tabButtons      = document.querySelectorAll('.seller-tab');
    const checkAll        = document.getElementById('checkAllCoupons');
    const bulkToolbar     = document.getElementById('bulkToolbar');
    const bulkCount       = document.getElementById('bulkCount');
    const btnBulkActivate = document.getElementById('btnBulkActivate');
    const btnBulkDeactivate = document.getElementById('btnBulkDeactivate');
    const btnBulkDelete   = document.getElementById('btnBulkDelete');
    const btnBulkClear    = document.getElementById('btnBulkClear');
    const btnAddCoupon    = document.getElementById('btnAddCoupon');
    const btnRefreshList  = document.getElementById('btnRefreshList');

    // Stat card counters
    const countAll        = document.getElementById('count-all');
    const countActive     = document.getElementById('count-active');
    const countUpcoming   = document.getElementById('count-upcoming');
    const countExpired    = document.getElementById('count-expired');
    const countUsed       = document.getElementById('count-used');
    const tabBadgeActive  = document.getElementById('tab-badge-active');
    const tabBadgeUpcoming = document.getElementById('tab-badge-upcoming');
    const tabBadgeExpired = document.getElementById('tab-badge-expired');

    // Form Modal
    const formModalEl     = document.getElementById('couponFormModal');
    const formModal       = formModalEl ? new bootstrap.Modal(formModalEl) : null;
    const formModalTitle  = document.getElementById('couponFormModalLabel');
    const couponForm      = document.getElementById('couponForm');
    const inputCode       = document.getElementById('couponCode');
    const inputType       = document.getElementById('couponType');
    const inputValue      = document.getElementById('couponValue');
    const inputValueUnit  = document.getElementById('couponValueUnit');
    const inputMinOrder   = document.getElementById('couponMinOrder');
    const inputMaxDiscount= document.getElementById('couponMaxDiscount');
    const maxDiscountWrap = document.getElementById('maxDiscountWrap');
    const inputUsageLimit = document.getElementById('couponUsageLimit');
    const inputStartsAt   = document.getElementById('couponStartsAt');
    const inputExpiresAt  = document.getElementById('couponExpiresAt');
    const inputStatus     = document.getElementById('couponStatus');
    const sellerSelectWrap= document.getElementById('sellerSelectWrap');
    const inputSellerId   = document.getElementById('couponSellerId');
    const scopePlatformCard = document.getElementById('scopePlatformCard');
    const scopeShopCard   = document.getElementById('scopeShopCard');
    const btnGenRandomCode= document.getElementById('btnGenRandomCode');
    const btnSaveCoupon   = document.getElementById('btnSaveCoupon');

    // Live Preview Elements
    const voucherCardSim  = document.getElementById('voucherCardSim');
    const previewBadgeType= document.getElementById('previewBadgeType');
    const voucherSimBrand = document.getElementById('voucherSimBrand');
    const voucherSimDiscount = document.getElementById('voucherSimDiscount');
    const voucherSimMin   = document.getElementById('voucherSimMin');
    const voucherSimMax   = document.getElementById('voucherSimMax');
    const voucherSimCode  = document.getElementById('voucherSimCode');
    const voucherSimExpiry= document.getElementById('voucherSimExpiry');
    const sumCode         = document.getElementById('sumCode');
    const sumDiscount     = document.getElementById('sumDiscount');
    const sumScope        = document.getElementById('sumScope');
    const sumLimit        = document.getElementById('sumLimit');
    const sumDuration     = document.getElementById('sumDuration');

    // Detail Modal
    const detailModalEl   = document.getElementById('couponDetailModal');
    const detailModal     = detailModalEl ? new bootstrap.Modal(detailModalEl) : null;
    const detailCodeBadge = document.getElementById('detailCodeBadge');
    const detailDiscountText = document.getElementById('detailDiscountText');
    const detailScopeBadge= document.getElementById('detailScopeBadge');
    const detailStatusBadge = document.getElementById('detailStatusBadge');
    const detailType      = document.getElementById('detailType');
    const detailValue     = document.getElementById('detailValue');
    const detailMinOrder  = document.getElementById('detailMinOrder');
    const detailMaxDiscount = document.getElementById('detailMaxDiscount');
    const detailShopName  = document.getElementById('detailShopName');
    const detailUsageText = document.getElementById('detailUsageText');
    const detailProgressBar = document.getElementById('detailProgressBar');
    const detailStartsAt  = document.getElementById('detailStartsAt');
    const detailExpiresAt = document.getElementById('detailExpiresAt');
    const detailCreatedAt = document.getElementById('detailCreatedAt');
    const detailUsagesTableBody = document.getElementById('detailUsagesTableBody');
    const btnCopyDetailCode = document.getElementById('btnCopyDetailCode');
    const btnEditFromDetail = document.getElementById('btnEditFromDetail');

    // Delete Modal
    const deleteModalEl   = document.getElementById('deleteCouponModal');
    const deleteModal     = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
    const deleteCouponCodeText = document.getElementById('deleteCouponCodeText');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');

    // Toast
    const toastEl         = document.getElementById('actionToast');
    const toastMessage    = document.getElementById('toastMessage');
    const actionToast     = toastEl ? new bootstrap.Toast(toastEl, { delay: 3000 }) : null;

    let loadedCouponsMap = {};

    /* ================================================================
       1. LOAD & RENDER DATA
       ================================================================ */
    function loadCoupons(page = 1) {
        currentPage = page;
        showTableLoading();
        clearCheckboxState();

        const keyword = searchInput.value.trim();
        let url = `${ROUTES.index}?page=${page}`;
        if (currentStatus) url += `&status=${encodeURIComponent(currentStatus)}`;
        if (currentScope)  url += `&scope=${encodeURIComponent(currentScope)}`;
        if (currentType)   url += `&type=${encodeURIComponent(currentType)}`;
        if (keyword)       url += `&search=${encodeURIComponent(keyword)}`;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Không thể tải dữ liệu');
            return res.json();
        })
        .then(data => {
            renderTable(data.data || []);
            renderPagination(data);
            updateStats(data.meta || {});
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger py-4">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Có lỗi xảy ra khi tải danh sách mã giảm giá.
                    </td>
                </tr>
            `;
        });
    }

    function showTableLoading() {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Đang tải danh sách mã giảm giá...
                </td>
            </tr>
        `;
    }

    function renderTable(coupons) {
        loadedCouponsMap = {};
        if (!coupons.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <div class="mb-2" style="font-size: 32px; color: #adb5bd;">
                            <i class="fa-solid fa-ticket-simple"></i>
                        </div>
                        <div class="fw-semibold">Không tìm thấy mã giảm giá nào phù hợp</div>
                        <small class="text-secondary">Hãy thử thay đổi từ khóa hoặc bộ lọc trạng thái</small>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        coupons.forEach(coupon => {
            loadedCouponsMap[coupon.id] = coupon;
            html += renderTableRow(coupon);
        });
        tbody.innerHTML = html;
        bindRowEvents();
    }

    function renderTableRow(c) {
        const isChecked = selectedIds.has(Number(c.id));
        const isPlatform = !c.seller_id;
        const shopName = c.seller?.seller_profile?.shop_name || c.seller?.name || 'Gian hàng';

        // Tinh toan tien do su dung
        const usageLimit = Number(c.usage_limit) || 1;
        const usedCount  = Number(c.used_count) || 0;
        const usagePct   = Math.min(100, Math.round((usedCount / usageLimit) * 100));
        let progressCls  = '';
        if (usagePct >= 90) progressCls = 'danger';
        else if (usagePct >= 60) progressCls = 'warning';

        // Tinh toan trang thai
        const now = new Date();
        const startsAt = c.starts_at ? new Date(c.starts_at) : null;
        const expiresAt = c.expires_at ? new Date(c.expires_at) : null;

        let statusChip = '';
        if (!c.status) {
            statusChip = `<span class="coupon-status-chip inactive"><span class="chip-dot grey"></span> Tạm ẩn</span>`;
        } else if (expiresAt && expiresAt < now) {
            statusChip = `<span class="coupon-status-chip expired"><span class="chip-dot red"></span> Hết hạn</span>`;
        } else if (usedCount >= usageLimit) {
            statusChip = `<span class="coupon-status-chip expired"><span class="chip-dot red"></span> Hết lượt</span>`;
        } else if (startsAt && startsAt > now) {
            statusChip = `<span class="coupon-status-chip upcoming"><span class="chip-dot blue"></span> Sắp tới</span>`;
        } else {
            statusChip = `<span class="coupon-status-chip active"><span class="chip-dot green"></span> Đang chạy</span>`;
        }

        // Mức giảm & Điều kiện
        let discountHtml = '';
        if (c.type === 'percentage') {
            const maxText = c.max_discount ? ` (Tối đa ${formatMoney(c.max_discount)}đ)` : '';
            discountHtml = `
                <div class="discount-val-main">Giảm ${c.value}%${maxText}</div>
                <div class="discount-cond-sub">Đơn từ ${formatMoney(c.min_order_amount)}đ</div>
            `;
        } else {
            discountHtml = `
                <div class="discount-val-main">Giảm ${formatMoney(c.value)}đ</div>
                <div class="discount-cond-sub">Đơn từ ${formatMoney(c.min_order_amount)}đ</div>
            `;
        }

        // Thời gian
        let timeHtml = '';
        if (startsAt || expiresAt) {
            const startStr = startsAt ? formatDate(startsAt) : 'Ngay';
            const endStr   = expiresAt ? formatDate(expiresAt) : 'Vô hạn';
            timeHtml = `
                <div class="fs-7 fw-semibold text-dark">${startStr} &rarr; ${endStr}</div>
                <small class="text-muted">${getRelativeTimeText(startsAt, expiresAt)}</small>
            `;
        } else {
            timeHtml = `<span class="badge bg-light text-dark border">Vô thời hạn</span>`;
        }

        return `
            <tr data-id="${c.id}">
                <td>
                    <input type="checkbox" class="form-check-input coupon-row-check" value="${c.id}" ${isChecked ? 'checked' : ''}>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="coupon-code-pill">
                            ${escapeHtml(c.code)}
                            <i class="fa-regular fa-copy btn-copy-code" title="Sao chép mã" data-code="${escapeHtml(c.code)}"></i>
                        </span>
                    </div>
                </td>
                <td>
                    ${isPlatform
                        ? `<span class="coupon-scope-badge platform"><i class="fa-solid fa-globe"></i> Toàn sàn</span>`
                        : `<span class="coupon-scope-badge shop" title="${escapeHtml(shopName)}"><i class="fa-solid fa-store"></i> ${escapeHtml(truncate(shopName, 18))}</span>`
                    }
                </td>
                <td>${discountHtml}</td>
                <td>
                    <div class="usage-cell-wrap">
                        <div class="usage-cell-text">
                            <span>Đã dùng: <strong>${usedCount}</strong>/${usageLimit}</span>
                            <span>${usagePct}%</span>
                        </div>
                        <div class="usage-progress-bar">
                            <div class="usage-progress-fill ${progressCls}" style="width: ${usagePct}%;"></div>
                        </div>
                    </div>
                </td>
                <td>${timeHtml}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input btn-toggle-status"
                                   type="checkbox"
                                   role="switch"
                                   data-id="${c.id}"
                                   ${c.status ? 'checked' : ''}
                                   title="${c.status ? 'Bấm để tạm ẩn' : 'Bấm để kích hoạt'}">
                        </div>
                        ${statusChip}
                    </div>
                </td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn-action-icon view btn-view-coupon" data-id="${c.id}" title="Xem chi tiết">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                        <button type="button" class="btn-action-icon edit btn-edit-coupon" data-id="${c.id}" title="Chỉnh sửa">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        <button type="button" class="btn-action-icon delete btn-delete-coupon" data-id="${c.id}" data-code="${escapeHtml(c.code)}" title="Xóa">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function bindRowEvents() {
        // Checkboxes
        document.querySelectorAll('.coupon-row-check').forEach(chk => {
            chk.addEventListener('change', function () {
                const id = Number(this.value);
                if (this.checked) selectedIds.add(id);
                else selectedIds.delete(id);
                updateBulkToolbar();
            });
        });

        // Copy button in pill
        document.querySelectorAll('.btn-copy-code').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                copyToClipboard(this.dataset.code);
            });
        });

        // Switch toggle status
        document.querySelectorAll('.btn-toggle-status').forEach(sw => {
            sw.addEventListener('change', function () {
                const id = this.dataset.id;
                toggleStatus(id, this);
            });
        });

        // View Detail
        document.querySelectorAll('.btn-view-coupon').forEach(btn => {
            btn.addEventListener('click', function () {
                openDetailModal(this.dataset.id);
            });
        });

        // Edit
        document.querySelectorAll('.btn-edit-coupon').forEach(btn => {
            btn.addEventListener('click', function () {
                openEditModal(this.dataset.id);
            });
        });

        // Delete
        document.querySelectorAll('.btn-delete-coupon').forEach(btn => {
            btn.addEventListener('click', function () {
                openDeleteModal(this.dataset.id, this.dataset.code);
            });
        });
    }

    /* ================================================================
       2. STATS & PAGINATION
       ================================================================ */
    function updateStats(meta) {
        if (countAll)        countAll.textContent        = formatNumber(meta.total_all || 0);
        if (countActive)     countActive.textContent     = formatNumber(meta.total_active || 0);
        if (countUpcoming)   countUpcoming.textContent   = formatNumber(meta.total_upcoming || 0);
        if (countExpired)    countExpired.textContent    = formatNumber(meta.total_expired || 0);
        if (countUsed)       countUsed.textContent       = formatNumber(meta.total_used || 0);

        if (tabBadgeActive)  tabBadgeActive.textContent  = meta.total_active || 0;
        if (tabBadgeUpcoming)tabBadgeUpcoming.textContent= meta.total_upcoming || 0;
        if (tabBadgeExpired) tabBadgeExpired.textContent = meta.total_expired || 0;
    }

    function renderPagination(data) {
        if (!paginationWrap) return;
        const total = data.total || 0;
        const from  = data.from || 0;
        const to    = data.to || 0;

        if (total === 0) {
            paginationWrap.style.display = 'none';
            return;
        }

        paginationWrap.style.display = 'flex';
        paginationInfo.textContent = `Hiển thị ${from} - ${to} trong ${total} mã giảm giá`;

        const current = data.current_page || 1;
        const last    = data.last_page || 1;

        let linksHtml = '';
        linksHtml += `
            <button class="pagination-btn ${current === 1 ? 'disabled' : ''}" data-page="${current - 1}" ${current === 1 ? 'disabled' : ''}>
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        `;

        for (let i = 1; i <= last; i++) {
            if (i === 1 || i === last || (i >= current - 2 && i <= current + 2)) {
                linksHtml += `
                    <button class="pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">
                        ${i}
                    </button>
                `;
            } else if (i === current - 3 || i === current + 3) {
                linksHtml += `<span class="pagination-dots">...</span>`;
            }
        }

        linksHtml += `
            <button class="pagination-btn ${current === last ? 'disabled' : ''}" data-page="${current + 1}" ${current === last ? 'disabled' : ''}>
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        `;

        paginationLinks.innerHTML = linksHtml;
        paginationLinks.querySelectorAll('.pagination-btn:not(.disabled)').forEach(btn => {
            btn.addEventListener('click', function () {
                const page = Number(this.dataset.page);
                if (page && page !== currentPage) loadCoupons(page);
            });
        });
    }

    /* ================================================================
       3. MODAL CREATE / EDIT & LIVE PREVIEW
       ================================================================ */
    function openCreateModal() {
        editingId = null;
        couponForm.reset();
        clearFormErrors();
        formModalTitle.textContent = 'Thêm mã giảm giá mới';

        // Default values
        document.getElementById('couponId').value = '';
        inputUsageLimit.value = '100';
        inputMinOrder.value = '0';
        inputStatus.checked = true;
        setScopeMode('platform');
        handleTypeChange('fixed_amount');
        generateRandomCode();

        updateLivePreview();
        formModal.show();
    }

    function openEditModal(id) {
        const coupon = loadedCouponsMap[id];
        if (!coupon) {
            fetchCouponAndOpenEdit(id);
            return;
        }
        populateAndShowEditModal(coupon);
    }

    function fetchCouponAndOpenEdit(id) {
        const url = ROUTES.show.replace('__ID__', id);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(res => {
                if (res.data) populateAndShowEditModal(res.data);
            });
    }

    function populateAndShowEditModal(c) {
        editingId = c.id;
        couponForm.reset();
        clearFormErrors();
        formModalTitle.textContent = `Chỉnh sửa mã giảm giá [${c.code}]`;

        document.getElementById('couponId').value = c.id;
        inputCode.value       = c.code || '';
        inputType.value      = c.type || 'fixed_amount';
        inputValue.value     = c.value || '';
        inputMinOrder.value  = c.min_order_amount || 0;
        inputMaxDiscount.value = c.max_discount || '';
        inputUsageLimit.value = c.usage_limit || 1;
        inputStartsAt.value  = formatForDatetimeInput(c.starts_at);
        inputExpiresAt.value = formatForDatetimeInput(c.expires_at);
        inputStatus.checked  = Boolean(c.status);

        if (c.seller_id) {
            setScopeMode('shop');
            inputSellerId.value = c.seller_id;
        } else {
            setScopeMode('platform');
            inputSellerId.value = '';
        }

        handleTypeChange(c.type || 'fixed_amount');
        updateLivePreview();
        formModal.show();
    }

    function setScopeMode(mode) {
        const platformRadio = scopePlatformCard.querySelector('input[type="radio"]');
        const shopRadio = scopeShopCard.querySelector('input[type="radio"]');

        if (mode === 'shop') {
            shopRadio.checked = true;
            scopeShopCard.classList.add('active');
            scopePlatformCard.classList.remove('active');
            sellerSelectWrap.style.display = 'block';
            voucherCardSim.classList.add('is-shop');
            previewBadgeType.textContent = 'Voucher Gian Hàng';
            previewBadgeType.className = 'badge bg-info text-dark';
            voucherSimBrand.textContent = getSelectedShopName() || 'SHOP VOUCHER';
            sumScope.textContent = getSelectedShopName() || 'Gian hàng';
            sumScope.className = 'badge bg-info text-dark';
        } else {
            platformRadio.checked = true;
            scopePlatformCard.classList.add('active');
            scopeShopCard.classList.remove('active');
            sellerSelectWrap.style.display = 'none';
            inputSellerId.value = '';
            voucherCardSim.classList.remove('is-shop');
            previewBadgeType.textContent = 'Voucher Toàn Sàn';
            previewBadgeType.className = 'badge bg-primary';
            voucherSimBrand.textContent = 'CUPO MALL';
            sumScope.textContent = 'Toàn sàn';
            sumScope.className = 'badge bg-secondary';
        }
    }

    function handleTypeChange(type) {
        if (type === 'percentage') {
            inputValueUnit.textContent = '%';
            maxDiscountWrap.style.display = 'block';
        } else {
            inputValueUnit.textContent = 'đ';
            maxDiscountWrap.style.display = 'none';
            inputMaxDiscount.value = '';
        }
    }

    function generateRandomCode() {
        const prefixes = ['CUPO', 'SALE', 'VIP', 'HOT', 'KM'];
        const pfx = prefixes[Math.floor(Math.random() * prefixes.length)];
        const rand = Math.random().toString(36).substring(2, 6).toUpperCase();
        const code = `${pfx}${rand}`;
        inputCode.value = code;
        updateLivePreview();
    }

    function updateLivePreview() {
        const code = (inputCode.value.trim() || 'CUPO50K').toUpperCase();
        const type = inputType.value;
        const val  = parseFloat(inputValue.value) || 0;
        const minOrder = parseFloat(inputMinOrder.value) || 0;
        const maxDisc  = parseFloat(inputMaxDiscount.value) || 0;
        const limit    = parseInt(inputUsageLimit.value) || 100;
        const expires  = inputExpiresAt.value;

        // Code
        voucherSimCode.textContent = code;
        sumCode.textContent = code;

        // Discount Text
        let discText = 'Chưa nhập giá trị';
        if (val > 0) {
            if (type === 'percentage') {
                discText = `Giảm ${val}%`;
            } else {
                discText = `Giảm ${formatMoney(val)}đ`;
            }
        }
        voucherSimDiscount.textContent = discText;
        sumDiscount.textContent = discText;

        // Min Order
        voucherSimMin.textContent = `Đơn tối thiểu ${formatMoney(minOrder)}đ`;

        // Max Discount
        if (type === 'percentage' && maxDisc > 0) {
            voucherSimMax.style.display = 'block';
            voucherSimMax.textContent = `Tối đa ${formatMoney(maxDisc)}đ`;
        } else {
            voucherSimMax.style.display = 'none';
        }

        // Expiry
        if (expires) {
            const expDate = new Date(expires);
            voucherSimExpiry.textContent = `HSD: ${formatDate(expDate)}`;
            sumDuration.textContent = `Đến ${formatDate(expDate)}`;
        } else {
            voucherSimExpiry.textContent = 'HSD: Vô thời hạn';
            sumDuration.textContent = 'Không thời hạn';
        }

        sumLimit.textContent = `${limit} lượt`;
    }

    function getSelectedShopName() {
        if (!inputSellerId.value) return '';
        const opt = inputSellerId.options[inputSellerId.selectedIndex];
        return opt ? opt.text.split('(')[0].trim() : '';
    }

    function saveCoupon() {
        clearFormErrors();
        btnSaveCoupon.disabled = true;
        btnSaveCoupon.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang lưu...';

        const isEdit = Boolean(editingId);
        const url = isEdit ? ROUTES.update.replace('__ID__', editingId) : ROUTES.store;
        const method = isEdit ? 'PUT' : 'POST';

        const payload = {
            code: inputCode.value.trim().toUpperCase(),
            type: inputType.value,
            value: inputValue.value,
            min_order_amount: inputMinOrder.value || 0,
            max_discount: inputMaxDiscount.value || null,
            usage_limit: inputUsageLimit.value || 1,
            starts_at: inputStartsAt.value || null,
            expires_at: inputExpiresAt.value || null,
            status: inputStatus.checked ? 1 : 0,
            seller_id: inputSellerId.value || null,
        };

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': ROUTES.csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    showValidationErrors(data.errors);
                } else {
                    showToast(data.message || 'Đã xảy ra lỗi khi lưu mã giảm giá.', 'danger');
                }
                throw new Error('Validation failed');
            }
            return data;
        })
        .then(res => {
            formModal.hide();
            showToast(res.message || 'Lưu mã giảm giá thành công!', 'success');
            loadCoupons(isEdit ? currentPage : 1);
        })
        .catch(err => {
            console.error(err);
        })
        .finally(() => {
            btnSaveCoupon.disabled = false;
            btnSaveCoupon.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu mã giảm giá';
        });
    }

    function showValidationErrors(errors) {
        for (const [field, msgs] of Object.entries(errors)) {
            const input = document.querySelector(`[name="${field}"]`);
            const errDiv = document.getElementById(`err-${field}`);
            if (input) input.classList.add('is-invalid');
            if (errDiv) {
                errDiv.textContent = msgs[0];
                errDiv.style.display = 'block';
            }
        }
    }

    function clearFormErrors() {
        couponForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        couponForm.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }

    /* ================================================================
       4. DETAIL MODAL & USAGE HISTORY
       ================================================================ */
    function openDetailModal(id) {
        const url = ROUTES.show.replace('__ID__', id);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(res => {
                if (res.data) renderDetailModal(res.data);
            })
            .catch(err => {
                console.error(err);
                showToast('Không thể tải chi tiết mã giảm giá.', 'danger');
            });
    }

    function renderDetailModal(c) {
        detailCodeBadge.textContent = c.code;
        btnCopyDetailCode.onclick = () => copyToClipboard(c.code);
        btnEditFromDetail.onclick = () => {
            detailModal.hide();
            openEditModal(c.id);
        };

        const isPlatform = !c.seller_id;
        const shopName = c.seller?.seller_profile?.shop_name || c.seller?.name || 'Gian hàng';

        detailScopeBadge.textContent = isPlatform ? 'Toàn sàn' : `Shop: ${shopName}`;
        detailScopeBadge.className = isPlatform ? 'badge bg-primary' : 'badge bg-info text-dark';

        if (c.type === 'percentage') {
            const maxText = c.max_discount ? ` (Tối đa ${formatMoney(c.max_discount)}đ)` : '';
            detailDiscountText.textContent = `Giảm ${c.value}%${maxText}`;
            detailType.textContent = 'Theo phần trăm (%)';
            detailValue.textContent = `${c.value}%`;
            detailMaxDiscount.textContent = c.max_discount ? `${formatMoney(c.max_discount)}đ` : 'Không giới hạn';
        } else {
            detailDiscountText.textContent = `Giảm ${formatMoney(c.value)}đ`;
            detailType.textContent = 'Số tiền cố định (VNĐ)';
            detailValue.textContent = `${formatMoney(c.value)}đ`;
            detailMaxDiscount.textContent = 'Không áp dụng';
        }

        detailMinOrder.textContent = `${formatMoney(c.min_order_amount)}đ`;
        detailShopName.textContent = isPlatform ? 'Tất cả gian hàng trên sàn' : shopName;

        // Progress
        const limit = Number(c.usage_limit) || 1;
        const used  = Number(c.used_count) || 0;
        const pct   = Math.min(100, Math.round((used / limit) * 100));
        detailUsageText.textContent = `${used} / ${limit} lượt (${pct}%)`;
        detailProgressBar.style.width = `${pct}%`;

        // Dates
        detailStartsAt.textContent = c.starts_at ? formatDate(new Date(c.starts_at)) : 'Kích hoạt ngay khi tạo';
        detailExpiresAt.textContent = c.expires_at ? formatDate(new Date(c.expires_at)) : 'Vô thời hạn';
        detailCreatedAt.textContent = c.created_at ? formatDate(new Date(c.created_at)) : '--';

        // Usages table
        const usages = c.usages || [];
        if (!usages.length) {
            detailUsagesTableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">Chưa có khách hàng nào sử dụng mã này</td>
                </tr>
            `;
        } else {
            let uHtml = '';
            usages.forEach((u, idx) => {
                const uName = u.user?.name || `Khách hàng #${u.user_id || '?'}`;
                const oCode = u.order?.order_code || `#${u.order_id || '?'}`;
                const uDate = u.created_at ? formatDate(new Date(u.created_at)) : '--';
                uHtml += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td class="fw-semibold">${escapeHtml(uName)}</td>
                        <td><span class="badge bg-light text-dark border font-monospace">${escapeHtml(oCode)}</span></td>
                        <td class="text-muted">${uDate}</td>
                    </tr>
                `;
            });
            detailUsagesTableBody.innerHTML = uHtml;
        }

        detailModal.show();
    }

    /* ================================================================
       5. TOGGLE STATUS & DELETE ACTIONS
       ================================================================ */
    function toggleStatus(id, switchEl) {
        const url = ROUTES.toggleStatus.replace('__ID__', id);
        fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': ROUTES.csrf,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(res => {
            showToast(res.message || 'Cập nhật trạng thái thành công!', 'success');
            loadCoupons(currentPage);
        })
        .catch(err => {
            console.error(err);
            switchEl.checked = !switchEl.checked;
            showToast('Không thể thay đổi trạng thái.', 'danger');
        });
    }

    function openDeleteModal(id, code) {
        deletingId = id;
        deleteCouponCodeText.textContent = code;
        deleteModal.show();
    }

    function confirmDelete() {
        if (!deletingId) return;
        const url = ROUTES.destroy.replace('__ID__', deletingId);
        btnConfirmDelete.disabled = true;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': ROUTES.csrf,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(res => {
            deleteModal.hide();
            showToast(res.message || 'Đã xóa mã giảm giá thành công.', 'success');
            loadCoupons(currentPage);
        })
        .catch(err => {
            console.error(err);
            showToast('Lỗi khi xóa mã giảm giá.', 'danger');
        })
        .finally(() => {
            btnConfirmDelete.disabled = false;
            deletingId = null;
        });
    }

    /* ================================================================
       6. BULK ACTIONS
       ================================================================ */
    function clearCheckboxState() {
        selectedIds.clear();
        if (checkAll) checkAll.checked = false;
        updateBulkToolbar();
    }

    function updateBulkToolbar() {
        const count = selectedIds.size;
        if (count > 0) {
            bulkToolbar.style.display = 'flex';
            bulkCount.textContent = count;
        } else {
            bulkToolbar.style.display = 'none';
        }
    }

    function bulkSetStatus(status) {
        if (!selectedIds.size) return;
        const ids = Array.from(selectedIds);
        fetch(ROUTES.bulkStatus, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': ROUTES.csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ids: ids, status: status })
        })
        .then(res => res.json())
        .then(res => {
            showToast(res.message || 'Cập nhật trạng thái hàng loạt thành công!', 'success');
            loadCoupons(currentPage);
        })
        .catch(err => {
            console.error(err);
            showToast('Lỗi khi cập nhật hàng loạt.', 'danger');
        });
    }

    function bulkDelete() {
        if (!selectedIds.size) return;
        if (!confirm(`Bạn có chắc chắn muốn xóa vĩnh viễn ${selectedIds.size} mã giảm giá đã chọn?`)) return;

        const ids = Array.from(selectedIds);
        fetch(ROUTES.bulkDelete, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': ROUTES.csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(res => res.json())
        .then(res => {
            showToast(res.message || 'Xóa hàng loạt thành công!', 'success');
            loadCoupons(currentPage);
        })
        .catch(err => {
            console.error(err);
            showToast('Lỗi khi xóa hàng loạt.', 'danger');
        });
    }

    /* ================================================================
       7. UTILS & EVENT LISTENERS
       ================================================================ */
    function showToast(msg, type = 'success') {
        if (!actionToast || !toastEl) return;
        toastMessage.innerHTML = type === 'success'
            ? `<i class="fa-solid fa-circle-check text-white"></i> ${escapeHtml(msg)}`
            : `<i class="fa-solid fa-circle-exclamation text-white"></i> ${escapeHtml(msg)}`;

        toastEl.className = `toast align-items-center text-white border-0 bg-${type === 'success' ? 'success' : 'danger'}`;
        actionToast.show();
    }

    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showToast(`Đã sao chép mã "${text}" vào bộ nhớ tạm!`, 'success');
            });
        } else {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            showToast(`Đã sao chép mã "${text}"!`, 'success');
        }
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount || 0);
    }

    function formatDate(date) {
        if (!date) return '';
        const d = new Date(date);
        const day   = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year  = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const mins  = String(d.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${mins}`;
    }

    function formatForDatetimeInput(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        const year  = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day   = String(d.getDate()).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const mins  = String(d.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${mins}`;
    }

    function getRelativeTimeText(startsAt, expiresAt) {
        const now = new Date();
        if (expiresAt && expiresAt < now) return 'Đã kết thúc';
        if (startsAt && startsAt > now) {
            const diffDays = Math.ceil((startsAt - now) / (1000 * 60 * 60 * 24));
            return `Bắt đầu sau ${diffDays} ngày nữa`;
        }
        if (expiresAt) {
            const diffDays = Math.ceil((expiresAt - now) / (1000 * 60 * 60 * 24));
            return `Còn ${diffDays} ngày nữa hết hạn`;
        }
        return 'Không giới hạn ngày';
    }

    function truncate(str, len = 20) {
        if (!str) return '';
        return str.length > len ? str.substring(0, len) + '...' : str;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /* ---- Event Bindings ---- */
    document.addEventListener('DOMContentLoaded', function () {
        loadCoupons(1);

        // Tab filters
        tabButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                tabButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentStatus = this.dataset.status;
                loadCoupons(1);
            });
        });

        // Scope filter
        if (scopeFilter) {
            scopeFilter.addEventListener('change', function () {
                currentScope = this.value;
                loadCoupons(1);
            });
        }

        // Type filter
        if (typeFilter) {
            typeFilter.addEventListener('change', function () {
                currentType = this.value;
                loadCoupons(1);
            });
        }

        // Search input
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => loadCoupons(1), 350);
            });
        }

        // Refresh list
        if (btnRefreshList) {
            btnRefreshList.addEventListener('click', () => loadCoupons(currentPage));
        }

        // Check All
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.coupon-row-check');
                checkboxes.forEach(chk => {
                    chk.checked = checkAll.checked;
                    const id = Number(chk.value);
                    if (checkAll.checked) selectedIds.add(id);
                    else selectedIds.delete(id);
                });
                updateBulkToolbar();
            });
        }

        // Bulk toolbar actions
        if (btnBulkClear)      btnBulkClear.addEventListener('click', clearCheckboxState);
        if (btnBulkActivate)   btnBulkActivate.addEventListener('click', () => bulkSetStatus(1));
        if (btnBulkDeactivate) btnBulkDeactivate.addEventListener('click', () => bulkSetStatus(0));
        if (btnBulkDelete)     btnBulkDelete.addEventListener('click', bulkDelete);

        // Add Coupon Button
        if (btnAddCoupon) btnAddCoupon.addEventListener('click', openCreateModal);

        // Delete Confirm
        if (btnConfirmDelete) btnConfirmDelete.addEventListener('click', confirmDelete);

        // Form actions & Live preview bindings
        if (scopePlatformCard) {
            scopePlatformCard.addEventListener('click', () => setScopeMode('platform'));
        }
        if (scopeShopCard) {
            scopeShopCard.addEventListener('click', () => setScopeMode('shop'));
        }
        if (inputSellerId) {
            inputSellerId.addEventListener('change', () => {
                voucherSimBrand.textContent = getSelectedShopName() || 'SHOP VOUCHER';
                sumScope.textContent = getSelectedShopName() || 'Gian hàng';
            });
        }
        if (inputType) {
            inputType.addEventListener('change', function () {
                handleTypeChange(this.value);
                updateLivePreview();
            });
        }
        if (btnGenRandomCode) {
            btnGenRandomCode.addEventListener('click', generateRandomCode);
        }

        // Live typing in form inputs
        [inputCode, inputValue, inputMinOrder, inputMaxDiscount, inputUsageLimit, inputStartsAt, inputExpiresAt].forEach(el => {
            if (el) {
                el.addEventListener('input', updateLivePreview);
                el.addEventListener('change', updateLivePreview);
            }
        });

        // Save Coupon
        if (btnSaveCoupon) btnSaveCoupon.addEventListener('click', saveCoupon);
    });

})();
