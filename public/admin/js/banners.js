/**
 * CUPO ADMIN — Trang Quản lý Banner trang chủ
 * Xử lý: tải danh sách, tab filter, lọc vị trí, tìm kiếm, checkbox bulk,
 *         modal tạo/sửa banner, modal chi tiết, xóa banner, toggle active
 *
 * Data context từ data-* attributes trên #bannersAppConfig (Rule 20)
 */

(function () {
    'use strict';

    /* ---- 0. Config: lấy từ data-* ---- */
    const appEl = document.getElementById('bannersAppConfig');
    if (!appEl) return;

    const cfg = appEl.dataset;
    const ROUTES = {
        index:      cfg.indexUrl,
        store:      cfg.storeUrl,
        update:     cfg.updateUrl,      // chứa __ID__
        destroy:    cfg.destroyUrl,     // chứa __ID__
        bulkStatus: cfg.bulkStatusUrl,
        bulkDelete: cfg.bulkDeleteUrl,
        upload:     cfg.uploadUrl,
        homeUrl:    cfg.homeUrl,
        csrf:       cfg.csrf,
    };

    /* ---- Constants ---- */
    const POSITION_MAP = {
        homepage_hero: { label: 'Slide chính (Hero)', cls: 'pos-hero' },
        homepage_mid:  { label: 'Giữa trang chủ', cls: 'pos-mid' },
        category_top:  { label: 'Đầu trang danh mục', cls: 'pos-category' },
        sidebar:       { label: 'Thanh bên', cls: 'pos-sidebar' },
    };

    /* ---- State ---- */
    let currentStatus   = '';          // '', 'active', 'inactive', 'expired'
    let currentPosition = '';          // '', 'homepage_hero', etc.
    let currentPage     = 1;
    let searchTimer     = null;
    let currentBanner   = null;
    let editingId       = null;
    let selectedIds     = new Set();

    /* ---- DOM refs ---- */
    const tbody          = document.getElementById('bannersTableBody');
    const paginationWrap   = document.getElementById('paginationWrap');
    const paginationInfo   = document.getElementById('paginationInfo');
    const paginationLinks  = document.getElementById('paginationLinks');
    const searchInput    = document.getElementById('bannerSearchInput');
    const positionFilter = document.getElementById('bannerPositionFilter');
    const tabButtons     = document.querySelectorAll('.seller-tab');
    const checkAll       = document.getElementById('checkAllBanners');
    const bulkToolbar    = document.getElementById('bulkToolbar');
    const bulkCount      = document.getElementById('bulkCount');
    const btnBulkShow    = document.getElementById('btnBulkShow');
    const btnBulkHide    = document.getElementById('btnBulkHide');
    const btnBulkDelete  = document.getElementById('btnBulkDelete');
    const btnBulkClear   = document.getElementById('btnBulkClear');
    const btnAddBanner   = document.getElementById('btnAddBanner');
    const btnPreview     = document.getElementById('btnPreviewStorefront');

    // Modals
    const formModalEl    = document.getElementById('bannerFormModal');
    const formModal      = new bootstrap.Modal(formModalEl);
    const formModalTitle = document.getElementById('bannerFormModalLabel');
    const bannerForm     = document.getElementById('bannerForm');
    const inputTitle     = document.getElementById('bannerTitle');
    const inputPosition  = document.getElementById('bannerPosition');
    const inputSortOrder = document.getElementById('bannerSortOrder');
    const inputImagePath = document.getElementById('bannerImagePath');
    const inputLinkUrl   = document.getElementById('bannerLinkUrl');
    const inputStartsAt  = document.getElementById('bannerStartsAt');
    const inputEndsAt    = document.getElementById('bannerEndsAt');
    const inputIsActive  = document.getElementById('bannerIsActive');
    const btnSaveBanner  = document.getElementById('btnSaveBanner');
    const btnUploadFile  = document.getElementById('btnUploadBannerFile');
    const filePicker     = document.getElementById('bannerFilePicker');

    const detailModalEl  = document.getElementById('bannerDetailModal');
    const detailModal    = new bootstrap.Modal(detailModalEl);

    const previewModalEl = document.getElementById('bannerClientPreviewModal');
    const previewModal   = previewModalEl ? new bootstrap.Modal(previewModalEl) : null;
    const previewFrame   = document.getElementById('clientPreviewFrame');

    const toastEl        = document.getElementById('actionToast');
    const actionToast    = new bootstrap.Toast(toastEl, { delay: 3000 });

    /* ================================================================
       LOAD DATA
       ================================================================ */
    function loadBanners(page = 1) {
        currentPage = page;
        showLoading();
        clearCheckboxState();

        const keyword = searchInput.value.trim();
        let url = ROUTES.index + '?page=' + page;
        if (currentStatus)   url += '&status=' + currentStatus;
        if (currentPosition) url += '&position=' + currentPosition;
        if (keyword)         url += '&search=' + encodeURIComponent(keyword);

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
    function renderTable(banners) {
        if (!banners || banners.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8">
                        <div class="banners-empty">
                            <i class="fa-solid fa-image"></i>
                            <p>Không tìm thấy banner nào phù hợp</p>
                        </div>
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = banners.map((b, i) => {
            const pos = POSITION_MAP[b.position] || { label: b.position, cls: '' };
            const fullImgUrl = formatImageUrl(b.image_path);
            const img = b.image_path
                ? `<img src="${escHtml(fullImgUrl)}" class="banner-img-sm" alt="${escHtml(b.title)}" onerror="this.src='https://via.placeholder.com/200x100?text=L%E1%BB%97i+%E1%BA%A3nh'">`
                : `<div class="banner-img-letter">${b.title.charAt(0).toUpperCase()}</div>`;

            const linkText  = b.link_url ? escHtml(b.link_url) : '—';
            const isChecked = selectedIds.has(b.id) ? 'checked' : '';
            const statusToggleChecked = b.is_active ? 'checked' : '';

            return `
                <tr data-id="${b.id}">
                    <td style="text-align:center;">
                        <input type="checkbox" class="seller-checkbox row-banner-check"
                            data-id="${b.id}" ${isChecked}>
                    </td>
                    <td class="text-muted" style="font-size: 12px;">${(currentPage - 1) * 10 + i + 1}</td>
                    <td>
                        <div class="banner-cell">
                            ${img}
                            <div>
                                <div class="banner-title-text" title="${escHtml(b.title)}">${escHtml(b.title)}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="pos-badge ${pos.cls}">${pos.label}</span></td>
                    <td><div class="banner-url-text" title="${linkText}">${linkText}</div></td>
                    <td style="text-align: center; font-weight: 600;">${b.sort_order ?? 0}</td>
                    <td style="text-align: center;">
                        <label class="cat-toggle-switch" title="${b.is_active ? 'Đang hiển thị' : 'Đã ẩn'}">
                            <input type="checkbox" ${statusToggleChecked}
                                onchange="toggleBannerStatus(${b.id}, this.checked)">
                            <span class="cat-toggle-track"></span>
                        </label>
                    </td>
                    <td style="text-align: center;">
                        <div class="dropdown">
                            <button class="btn-row-detail" data-bs-toggle="dropdown" title="Hành động">
                                •••
                            </button>
                            <ul class="dropdown-menu cat-dropdown-menu dropdown-menu-end">
                                <li>
                                    <button class="dropdown-item" onclick="openBannerDetail(${JSON.stringify(b).replace(/"/g, '&quot;')})">
                                        <i class="fa-solid fa-eye" style="color:#1565c0;width:14px;"></i>
                                        Xem chi tiết
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item" onclick="openEditBannerModal(${JSON.stringify(b).replace(/"/g, '&quot;')})">
                                        <i class="fa-solid fa-pen" style="color:#e65100;width:14px;"></i>
                                        Sửa banner
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <button class="dropdown-item text-danger" onclick="deleteBanner(${b.id}, '${escAttr(b.title)}')">
                                        <i class="fa-solid fa-trash" style="width:14px;"></i>
                                        Xóa banner
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>`;
        }).join('');

        // Re-attach checkbox listeners
        tbody.querySelectorAll('.row-banner-check').forEach(cb => {
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
        paginationInfo.textContent = `Hiển thị ${from}–${to} / ${total} banner`;

        let btns = '';
        btns += `<button class="page-btn" onclick="goBannerPage(${page - 1})" ${page === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                 </button>`;

        for (let p = 1; p <= lastPage; p++) {
            if (lastPage > 7 && Math.abs(p - page) > 2 && p !== 1 && p !== lastPage) {
                if (p === 2 || p === lastPage - 1)
                    btns += `<span class="page-btn" style="cursor:default; border:none;">...</span>`;
                continue;
            }
            btns += `<button class="page-btn ${p === page ? 'active' : ''}" onclick="goBannerPage(${p})">${p}</button>`;
        }

        btns += `<button class="page-btn" onclick="goBannerPage(${page + 1})" ${page === lastPage ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
                 </button>`;

        paginationLinks.innerHTML = btns;
    }

    window.goBannerPage = function (page) { loadBanners(page); };

    /* ================================================================
       STAT COUNTS
       ================================================================ */
    function updateStatCounts(meta) {
        if (!meta) return;
        safeSet('count-all',      meta.total_all      ?? meta.total ?? '--');
        safeSet('count-active',   meta.total_active   ?? '--');
        safeSet('count-inactive', meta.total_inactive ?? '--');
        safeSet('count-expired',  meta.total_expired  ?? '--');

        const badgeActive = document.getElementById('tab-badge-active');
        if (badgeActive && meta.total_active != null) {
            badgeActive.textContent   = meta.total_active;
            badgeActive.style.display = meta.total_active > 0 ? '' : 'none';
        }
    }

    /* ================================================================
       TABS & FILTERS
       ================================================================ */
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentStatus = this.dataset.status;
            loadBanners(1);
        });
    });

    positionFilter.addEventListener('change', function () {
        currentPosition = this.value;
        loadBanners(1);
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadBanners(1), 300);
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
        tbody.querySelectorAll('.row-banner-check').forEach(cb => {
            cb.checked = checked;
            const id   = Number(cb.dataset.id);
            if (checked) selectedIds.add(id);
            else         selectedIds.delete(id);
            cb.closest('tr').classList.toggle('seller-row-selected', checked);
        });
        updateBulkToolbar();
    });

    function syncCheckAll() {
        const all    = tbody.querySelectorAll('.row-banner-check');
        const ticked = tbody.querySelectorAll('.row-banner-check:checked');
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
       BULK SHOW / HIDE / DELETE
       ================================================================ */
    btnBulkShow.addEventListener('click', function () {
        if (selectedIds.size === 0) return;
        sendBulkStatus(true);
    });

    btnBulkHide.addEventListener('click', function () {
        if (selectedIds.size === 0) return;
        sendBulkStatus(false);
    });

    function sendBulkStatus(isActive) {
        fetch(ROUTES.bulkStatus, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': ROUTES.csrf,
            },
            body: JSON.stringify({ ids: [...selectedIds], is_active: isActive }),
        })
            .then(r => r.json())
            .then(json => {
                showToast(json.message ?? 'Cập nhật thành công!', 'success');
                loadBanners(currentPage);
            })
            .catch(() => showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error'));
    }

    btnBulkDelete.addEventListener('click', function () {
        const n = selectedIds.size;
        if (n === 0) return;
        if (!confirm(`Bạn có chắc chắn muốn xóa ${n} banner đã chọn? Hành động này không thể hoàn tác!`)) return;

        fetch(ROUTES.bulkDelete, {
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
                showToast(json.message ?? 'Đã xóa thành công!', 'success');
                loadBanners(currentPage);
            })
            .catch(() => showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error'));
    });

    btnBulkClear.addEventListener('click', function () {
        clearCheckboxState();
        tbody.querySelectorAll('.row-banner-check').forEach(cb => {
            cb.checked = false;
            cb.closest('tr').classList.remove('seller-row-selected');
        });
        checkAll.checked = false;
    });

    /* ================================================================
       TOGGLE STATUS INLINE
       ================================================================ */
    window.toggleBannerStatus = function (id, checked) {
        const url = ROUTES.update.replace('__ID__', id);
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': ROUTES.csrf,
            },
            body: JSON.stringify({ is_active: checked }),
        })
            .then(r => r.json())
            .then(json => {
                showToast(json.message ?? 'Đã cập nhật trạng thái.', 'success');
                loadBanners(currentPage);
            })
            .catch(() => showToast('Có lỗi xảy ra.', 'error'));
    };

    /* ================================================================
       MODAL CHI TIẾT BANNER
       ================================================================ */
    window.openBannerDetail = function (b) {
        currentBanner = b;
        const pos = POSITION_MAP[b.position] || { label: b.position, cls: '' };

        const imgEl = document.getElementById('detailBannerImg');
        imgEl.src = formatImageUrl(b.image_path);
        imgEl.onerror = function () {
            this.src = 'https://via.placeholder.com/800x300?text=Kh%C3%B4ng+th%E1%BB%83+t%E1%BA%A3i+hình+%E1%BA%A3nh';
        };

        setText('detailBannerTitle', b.title);
        setText('dTitle',           b.title);
        setText('dLinkUrl',         b.link_url || 'Không có liên kết');
        setText('dSortOrder',       b.sort_order ?? 0);
        setText('dStartsAt',        formatDateTime(b.starts_at));
        setText('dEndsAt',          formatDateTime(b.ends_at));

        document.getElementById('dPositionBadge').innerHTML = `<span class="pos-badge ${pos.cls}">${pos.label}</span>`;

        const stBadge = b.is_active
            ? `<span class="badge-status badge-approved">Đang hiển thị</span>`
            : `<span class="badge-status badge-rejected" style="background:#f5f5f5;color:#616161;">Đã ẩn</span>`;
        document.getElementById('detailStatusBadge').innerHTML = stBadge;

        detailModal.show();
    };

    /* ================================================================
       MODAL TẠO / SỬA BANNER
       ================================================================ */
    btnAddBanner.addEventListener('click', function () {
        editingId = null;
        formModalTitle.textContent = 'Thêm Banner Mới';
        bannerForm.reset();
        inputIsActive.checked = true;
        clearFormErrors();
        document.getElementById('formImagePreviewWrap').classList.add('d-none');
        formModal.show();
    });

    window.openEditBannerModal = function (b) {
        editingId = b.id;
        formModalTitle.textContent = 'Sửa Banner #' + b.id;
        clearFormErrors();

        inputTitle.value     = b.title || '';
        inputPosition.value  = b.position || 'homepage_hero';
        inputSortOrder.value = b.sort_order ?? 0;
        inputImagePath.value = b.image_path || '';
        inputLinkUrl.value   = b.link_url || '';

        inputStartsAt.value  = toLocalDatetimeValue(b.starts_at);
        inputEndsAt.value    = toLocalDatetimeValue(b.ends_at);

        inputIsActive.checked = !!b.is_active;

        triggerImagePreview(b.image_path);
        formModal.show();
    };

    // Live preview ảnh khi dán URL vào input
    inputImagePath.addEventListener('input', function () {
        triggerImagePreview(this.value.trim());
    });

    // Modal xem trước trang web Client (Storefront Preview)
    if (btnPreview && previewModal && previewFrame) {
        btnPreview.addEventListener('click', function () {
            previewFrame.src = ROUTES.homeUrl || '/';
            previewModal.show();
        });
    }

    // Upload file từ máy tính
    if (btnUploadFile && filePicker) {
        btnUploadFile.addEventListener('click', function () {
            filePicker.click();
        });

        filePicker.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);
            formData.append('folder', 'banners');

            btnUploadFile.disabled = true;
            btnUploadFile.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang tải...';

            fetch(ROUTES.upload, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': ROUTES.csrf,
                },
                body: formData,
            })
                .then(r => r.json())
                .then(json => {
                    if (json.status === 'success' && json.url) {
                        inputImagePath.value = json.url;
                        triggerImagePreview(json.url);
                        showToast('Tải ảnh lên thành công!', 'success');
                    } else {
                        showToast(json.message ?? 'Lỗi tải ảnh lên.', 'error');
                    }
                })
                .catch(() => showToast('Có lỗi xảy ra khi tải tệp ảnh.', 'error'))
                .finally(() => {
                    btnUploadFile.disabled = false;
                    btnUploadFile.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải ảnh lên';
                    filePicker.value = '';
                });
        });
    }

    function formatImageUrl(path) {
        if (!path) return '';
        const p = String(path).trim();

        // Nếu chuỗi chứa /storage/ (ví dụ http://localhost/storage/banners/xyz.jpg)
        if (p.includes('/storage/')) {
            return '/storage/' + p.split('/storage/')[1];
        }

        if (p.startsWith('http://') || p.startsWith('https://') || p.startsWith('/')) {
            return p;
        }
        return '/storage/' + p;
    }

    function triggerImagePreview(url) {
        const wrap = document.getElementById('formImagePreviewWrap');
        const img  = document.getElementById('formImagePreview');
        if (!wrap || !img) return;

        if (url && url.trim() !== '') {
            const formatted = formatImageUrl(url.trim());
            img.src = formatted;
            wrap.classList.remove('d-none');

            img.onerror = function () {
                wrap.classList.add('d-none');
            };
            img.onload = function () {
                wrap.classList.remove('d-none');
            };
        } else {
            wrap.classList.add('d-none');
        }
    }

    // Submit Form (Create / Update)
    btnSaveBanner.addEventListener('click', function () {
        clearFormErrors();

        const data = {
            title:      inputTitle.value.trim(),
            position:   inputPosition.value,
            sort_order: Number(inputSortOrder.value) || 0,
            image_path: inputImagePath.value.trim(),
            link_url:   inputLinkUrl.value.trim() || null,
            starts_at:  inputStartsAt.value || null,
            ends_at:    inputEndsAt.value || null,
            is_active:  inputIsActive.checked,
        };

        const isEdit = !!editingId;
        const url    = isEdit ? ROUTES.update.replace('__ID__', editingId) : ROUTES.store;
        const method = isEdit ? 'PATCH' : 'POST';

        btnSaveBanner.disabled    = true;
        btnSaveBanner.textContent = 'Đang lưu...';

        fetch(url, {
            method:  method,
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': ROUTES.csrf,
            },
            body: JSON.stringify(data),
        })
            .then(r => r.json().then(json => ({ status: r.status, body: json })))
            .then(({ status, body }) => {
                if (status === 422) {
                    showFormErrors(body.errors || {});
                    return;
                }
                formModal.hide();
                showToast(body.message ?? 'Đã lưu banner thành công!', 'success');
                loadBanners(currentPage);
            })
            .catch(() => showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error'))
            .finally(() => {
                btnSaveBanner.disabled  = false;
                btnSaveBanner.innerHTML = '<i class="fa-solid fa-save me-1"></i> Lưu thông tin';
            });
    });

    /* ================================================================
       XÓA BANNER SINGLE
       ================================================================ */
    window.deleteBanner = function (id, title) {
        if (!confirm(`Bạn có chắc chắn muốn xóa banner "${title}"?`)) return;

        const url = ROUTES.destroy.replace('__ID__', id);
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': ROUTES.csrf,
            },
        })
            .then(r => r.json())
            .then(json => {
                showToast(json.message ?? 'Đã xóa banner!', 'success');
                loadBanners(currentPage);
            })
            .catch(() => showToast('Có lỗi xảy ra.', 'error'));
    };

    /* ================================================================
       HELPERS & UTILS
       ================================================================ */
    function clearFormErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        document.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
    }

    function showFormErrors(errors) {
        if (errors.title) {
            inputTitle.classList.add('is-invalid');
            document.getElementById('bannerTitleError').textContent = errors.title[0];
        }
        if (errors.position) {
            inputPosition.classList.add('is-invalid');
            document.getElementById('bannerPositionError').textContent = errors.position[0];
        }
        if (errors.image_path) {
            inputImagePath.classList.add('is-invalid');
            document.getElementById('bannerImagePathError').textContent = errors.image_path[0];
        }
        if (errors.link_url) {
            inputLinkUrl.classList.add('is-invalid');
            document.getElementById('bannerLinkUrlError').textContent = errors.link_url[0];
        }
        if (errors.ends_at) {
            inputEndsAt.classList.add('is-invalid');
            document.getElementById('bannerEndsAtError').textContent = errors.ends_at[0];
        }
    }

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

    function escAttr(str) {
        return String(str ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    function formatDateTime(str) {
        if (!str) return 'Không giới hạn';
        return new Date(str).toLocaleString('vi-VN', {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function toLocalDatetimeValue(str) {
        if (!str) return '';
        const d = new Date(str);
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    /* ================================================================
       KHỞI ĐỘNG — Tải danh sách banner ban đầu
       ================================================================ */
    loadBanners(1);

})();
