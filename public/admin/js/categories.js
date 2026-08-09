/**
 * CUPO ADMIN — Quản lý Danh mục (table layout)
 * File: public/admin/js/categories.js
 *
 * Data context từ data-* attributes trên #categoriesApp
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ─── 0. Config ─────────────────────────────────────────── */
    const app    = document.getElementById('categoriesApp');
    const ROUTES = {
<<<<<<< feature/be-admin
        data:    app.dataset.dataUrl,
        store:   app.dataset.storeUrl,
        update:  app.dataset.updateUrl,   // chứa __ID__
        destroy: app.dataset.destroyUrl,  // chứa __ID__
        csrf:    app.dataset.csrf,
=======
        data:       app.dataset.dataUrl,
        store:      app.dataset.storeUrl,
        update:     app.dataset.updateUrl,      // chứa __ID__
        destroy:    app.dataset.destroyUrl,     // chứa __ID__
        export:     app.dataset.exportUrl,
        bulkStatus: app.dataset.bulkStatusUrl,
        bulkDelete: app.dataset.bulkDeleteUrl,
        upload:     app.dataset.uploadUrl,
        csrf:       app.dataset.csrf,
>>>>>>> local
    };

    /* ─── 1. DOM refs ───────────────────────────────────────── */
    const tableBody    = document.getElementById('catTableBody');
    const searchInput  = document.getElementById('catSearchInput');
    const checkAll     = document.getElementById('checkAll');
    const selectInfo   = document.getElementById('catSelectInfo');
    const paginationEl = document.getElementById('catPagination');

    const modalEl      = document.getElementById('catModal');
    const modal        = new bootstrap.Modal(modalEl);
    const modalTitle   = document.getElementById('catModalLabel');
    const inputName    = document.getElementById('catName');
    const nameError    = document.getElementById('catNameError');
    const selParent    = document.getElementById('catParentId');
    const inputImage   = document.getElementById('catImage');
    const filePicker   = document.getElementById('catFilePicker');
    const btnUploadImg = document.getElementById('btnUploadCatImage');
    const imgPreviewWrap = document.getElementById('catImagePreviewWrap');
    const imgPreview   = document.getElementById('catImagePreview');
    const btnClearImg  = document.getElementById('btnClearCatImage');
    const statusToggle = document.getElementById('catStatusToggle');
    const statusLabel  = document.getElementById('catStatusLabel');
    const btnSave      = document.getElementById('btnCatSave');

    const toastEl      = document.getElementById('catToast');
    const toastMsg     = document.getElementById('catToastMsg');
    const bsToast      = new bootstrap.Toast(toastEl, { delay: 3000 });

    /* ─── 2. State ──────────────────────────────────────────── */
    let allCategories  = [];
    let filteredRows   = [];  // flat list sau khi filter/search
    let openNodes      = new Set();
    let selectedIds    = new Set();
    let editingId      = null;
    let activeFilter   = 'all';

    // Pagination
    const PER_PAGE     = 10;
    let currentPage    = 1;

    /* ─── 3. Helpers ────────────────────────────────────────── */
    const apiUrl = (tpl, id) => tpl.replace('__ID__', id);

    const headers = () => ({
        'Content-Type': 'application/json',
        'Accept':       'application/json',
        'X-CSRF-TOKEN': ROUTES.csrf,
    });

    function showToast(msg, type = 'success') {
        toastEl.className = `toast align-items-center border-0 toast-${type}`;
        toastMsg.textContent = msg;
        bsToast.show();
    }

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function escAttr(str) {
        return String(str ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    function formatImgUrl(path) {
        if (!path) return '';
        const p = String(path).trim();
        if (p.includes('/storage/')) return '/storage/' + p.split('/storage/')[1];
        if (p.startsWith('http://') || p.startsWith('https://') || p.startsWith('/')) return p;
        return '/storage/' + p;
    }

    function showImgPreview(url) {
        const formatted = formatImgUrl(url);
        if (formatted) {
            imgPreview.src = formatted;
            imgPreviewWrap.classList.remove('d-none');
            imgPreview.onerror = () => imgPreviewWrap.classList.add('d-none');
        } else {
            imgPreviewWrap.classList.add('d-none');
        }
    }

    function clearImgPreview() {
        inputImage.value = '';
        imgPreviewWrap.classList.add('d-none');
        imgPreview.src = '';
    }

    /* ─── 4. Load data ──────────────────────────────────────── */
    function loadCategories() {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="cat-loading-cell">
                    <span class="cat-spinner"></span>Đang tải dữ liệu...
                </td>
            </tr>`;

        fetch(ROUTES.data, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(res => {
                allCategories = res.data || [];
                buildParentDropdown();
                applyFilterAndRender();
            })
            .catch(() => {
                tableBody.innerHTML = `
                    <tr><td colspan="6" class="cat-empty-cell">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <p>Không thể tải dữ liệu.</p>
                    </td></tr>`;
            });
    }

    /* ─── 5. Filter + search → filteredRows ────────────────── */
    function applyFilterAndRender() {
        const kw = searchInput.value.trim().toLowerCase();

        // Danh sách root sau khi filter
        const roots = allCategories.filter(cat => {
            if (activeFilter === 'active'  && !cat.status) return false;
            if (activeFilter === 'hidden'  && cat.status)  return false;
            if (activeFilter === 'parent')                 return true; // chỉ root

            // search
            if (kw) {
                const matchSelf     = cat.name.toLowerCase().includes(kw);
                const matchChildren = (cat.children || []).some(c => c.name.toLowerCase().includes(kw));
                return matchSelf || matchChildren;
            }
            return true;
        });

        filteredRows = roots;
        currentPage  = 1;
        selectedIds.clear();
        updateSelectInfo();
        renderTable();
    }

    /* ─── 6. Render table (paginated) ──────────────────────── */
    function renderTable() {
        if (filteredRows.length === 0) {
            tableBody.innerHTML = `
                <tr><td colspan="6" class="cat-empty-cell">
                    <i class="fa-solid fa-folder-open"></i>
                    <p>Không tìm thấy danh mục nào.</p>
                </td></tr>`;
            renderPagination(0);
            return;
        }

        const total      = filteredRows.length;
        const start      = (currentPage - 1) * PER_PAGE;
        const pageItems  = filteredRows.slice(start, start + PER_PAGE);
        const kw         = searchInput.value.trim().toLowerCase();

        let html = '';

        pageItems.forEach(cat => {
            const hasChildren = cat.children && cat.children.length > 0;
            const isOpen      = openNodes.has(cat.id) || (kw && hasChildren && cat.children.some(c => c.name.toLowerCase().includes(kw)));
            const isSelected  = selectedIds.has(cat.id);

            html += renderParentRow(cat, hasChildren, isOpen, isSelected);

            if (hasChildren && isOpen) {
                cat.children.forEach(child => {
                    if (kw && !child.name.toLowerCase().includes(kw) && !cat.name.toLowerCase().includes(kw)) return;
                    html += renderChildRow(child, selectedIds.has(child.id));
                });
            }
        });

        tableBody.innerHTML = html;
        renderPagination(total);
        syncCheckAll();
    }

    /* ─── 7. Row builders ───────────────────────────────────── */
    function renderParentRow(cat, hasChildren, isOpen, isSelected) {
        const statusChecked = cat.status ? 'checked' : '';
        const imgUrl = cat.image ? formatImgUrl(cat.image) : '';
        const initial = escHtml(cat.name.charAt(0).toUpperCase());
        const avatarHtml = imgUrl
            ? `<img src="${escHtml(imgUrl)}" class="cat-row-img" alt="${escHtml(cat.name)}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
               <div class="cat-row-icon parent" style="display:none;">${initial}</div>`
            : `<div class="cat-row-icon parent">${initial}</div>`;

        return `
        <tr data-id="${cat.id}" class="${isSelected ? 'selected' : ''}">
            <td class="col-check">
                <input type="checkbox" class="cat-checkbox row-check"
                    data-id="${cat.id}" ${isSelected ? 'checked' : ''}>
            </td>
            <td>
                <div class="cat-name-cell">
                    <button class="cat-expand-btn ${hasChildren ? (isOpen ? 'open' : '') : 'invisible'}"
                        onclick="catToggle(${cat.id})" title="${isOpen ? 'Thu gọn' : 'Mở rộng'}">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <div class="cat-row-avatar">${avatarHtml}</div>
                    <div>
                        <div class="cat-row-name">${escHtml(cat.name)}</div>
                        <div class="cat-row-meta">${cat.children_count ?? 0} danh mục con</div>
                    </div>
                </div>
            </td>
            <td><span class="cat-slug-text">/${escHtml(cat.slug ?? '')}</span></td>
            <td class="text-center">
                <span class="cat-children-badge">${cat.children_count ?? 0}</span>
            </td>
            <td class="text-center">
                <label class="cat-toggle-switch" title="${cat.status ? 'Đang hiển thị' : 'Đang ẩn'}">
                    <input type="checkbox" ${statusChecked}
                        onchange="catToggleStatus(${cat.id}, this.checked)">
                    <span class="cat-toggle-track"></span>
                </label>
            </td>
            <td class="text-center">
                <div class="dropdown">
                    <button class="cat-action-btn" data-bs-toggle="dropdown" title="Hành động">
                        •••
                    </button>
                    <ul class="dropdown-menu cat-dropdown-menu dropdown-menu-end">
                        <li>
                            <button class="dropdown-item" onclick="catOpenCreateChild(${cat.id}, '${escAttr(cat.name)}')">
                                <i class="fa-solid fa-plus" style="color:#2e7d32;width:14px;"></i>
                                Thêm danh mục con
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" onclick="catOpenEdit(${cat.id})">
                                <i class="fa-solid fa-pen" style="color:#e65100;width:14px;"></i>
                                Sửa
                            </button>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <button class="dropdown-item text-danger" onclick="catDelete(${cat.id}, '${escAttr(cat.name)}')">
                                <i class="fa-solid fa-trash" style="width:14px;"></i>
                                Xóa
                            </button>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>`;
    }

    function renderChildRow(child, isSelected) {
        const statusChecked = child.status ? 'checked' : '';
        const imgUrl = child.image ? formatImgUrl(child.image) : '';
        const initial = escHtml(child.name.charAt(0).toUpperCase());
        const avatarHtml = imgUrl
            ? `<img src="${escHtml(imgUrl)}" class="cat-row-img child-img" alt="${escHtml(child.name)}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
               <div class="cat-row-icon child" style="display:none;">${initial}</div>`
            : `<div class="cat-row-icon child">${initial}</div>`;

        return `
        <tr data-id="${child.id}" class="cat-child-row ${isSelected ? 'selected' : ''}">
            <td class="col-check">
                <input type="checkbox" class="cat-checkbox row-check"
                    data-id="${child.id}" ${isSelected ? 'checked' : ''}>
            </td>
            <td>
                <div class="cat-name-cell">
                    <span style="display:inline-block;width:28px;flex-shrink:0;"></span>
                    <span class="cat-expand-btn invisible"></span>
                    <div class="cat-row-avatar">${avatarHtml}</div>
                    <div>
                        <div class="cat-row-name" style="font-weight:500;">${escHtml(child.name)}</div>
                    </div>
                </div>
            </td>
            <td><span class="cat-slug-text">/${escHtml(child.slug ?? '')}</span></td>
            <td class="text-center"><span class="cat-children-badge">—</span></td>
            <td class="text-center">
                <label class="cat-toggle-switch" title="${child.status ? 'Đang hiển thị' : 'Đang ẩn'}">
                    <input type="checkbox" ${statusChecked}
                        onchange="catToggleStatus(${child.id}, this.checked)">
                    <span class="cat-toggle-track"></span>
                </label>
            </td>
            <td class="text-center">
                <div class="dropdown">
                    <button class="cat-action-btn" data-bs-toggle="dropdown" title="Hành động">
                        •••
                    </button>
                    <ul class="dropdown-menu cat-dropdown-menu dropdown-menu-end">
                        <li>
                            <button class="dropdown-item" onclick="catOpenEdit(${child.id})">
                                <i class="fa-solid fa-pen" style="color:#e65100;width:14px;"></i>
                                Sửa
                            </button>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <button class="dropdown-item text-danger" onclick="catDelete(${child.id}, '${escAttr(child.name)}')">
                                <i class="fa-solid fa-trash" style="width:14px;"></i>
                                Xóa
                            </button>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>`;
    }

    /* ─── 8. Pagination ─────────────────────────────────────── */
    function renderPagination(total) {
        const totalPages = Math.ceil(total / PER_PAGE);
        if (totalPages <= 1) { paginationEl.innerHTML = ''; return; }

        const start = (currentPage - 1) * PER_PAGE + 1;
        const end   = Math.min(currentPage * PER_PAGE, total);

        let html = `<span class="cat-page-info">${start}–${end} / ${total}</span>`;
        html += `<button class="cat-page-btn" onclick="catGoPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left" style="font-size:10px;"></i>
                 </button>`;

        for (let p = 1; p <= totalPages; p++) {
            if (totalPages > 7 && Math.abs(p - currentPage) > 2 && p !== 1 && p !== totalPages) {
                if (p === currentPage - 3 || p === currentPage + 3) html += `<span class="cat-page-info">…</span>`;
                continue;
            }
            html += `<button class="cat-page-btn ${p === currentPage ? 'active' : ''}" onclick="catGoPage(${p})">${p}</button>`;
        }

        html += `<button class="cat-page-btn" onclick="catGoPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
                 </button>`;

        paginationEl.innerHTML = html;
    }

    window.catGoPage = function (page) {
        const totalPages = Math.ceil(filteredRows.length / PER_PAGE);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderTable();
    };

    /* ─── 9. Expand / collapse ──────────────────────────────── */
    window.catToggle = function (id) {
        if (openNodes.has(id)) openNodes.delete(id);
        else openNodes.add(id);
        renderTable();
    };

    /* ─── 10. Toggle status inline ──────────────────────────── */
    window.catToggleStatus = function (id, checked) {
        fetch(apiUrl(ROUTES.update, id), {
            method:  'PATCH',
            headers: headers(),
            body:    JSON.stringify({ status: checked }),
        })
            .then(r => r.json())
            .then(res => {
                showToast(res.message ?? 'Đã cập nhật trạng thái.', 'success');
                // Cập nhật local data
                allCategories.forEach(cat => {
                    if (cat.id === id) cat.status = checked;
                    (cat.children || []).forEach(ch => { if (ch.id === id) ch.status = checked; });
                });
            })
            .catch(() => showToast('Lỗi kết nối.', 'error'));
    };

    /* ─── 11. Modal: create root ────────────────────────────── */
    document.getElementById('btnAddCategory').addEventListener('click', () => {
        editingId = null;
        modalTitle.textContent = 'Thêm danh mục mới';
        inputName.value        = '';
        selParent.value        = '';
        clearImgPreview();
        statusToggle.checked   = true;
        updateStatusLabel();
        clearModalError();
        modal.show();
    });

    /* ─── 12. Modal: create child ───────────────────────────── */
    window.catOpenCreateChild = function (parentId, parentName) {
        editingId = null;
        modalTitle.textContent = `Thêm con vào "${parentName}"`;
        inputName.value        = '';
        selParent.value        = parentId;
        clearImgPreview();
        statusToggle.checked   = true;
        updateStatusLabel();
        clearModalError();
        modal.show();
    };

    /* ─── 13. Modal: edit ───────────────────────────────────── */
    window.catOpenEdit = function (id) {
        let found = null;
        allCategories.forEach(cat => {
            if (cat.id === id) found = cat;
            (cat.children || []).forEach(ch => { if (ch.id === id) found = ch; });
        });
        if (!found) return;

        editingId              = id;
        modalTitle.textContent = 'Sửa danh mục';
        inputName.value        = found.name;
        selParent.value        = found.parent_id ?? '';
        inputImage.value       = found.image ? formatImgUrl(found.image) : '';
        showImgPreview(found.image);
        statusToggle.checked   = !!found.status;
        updateStatusLabel();
        clearModalError();
        modal.show();
    };

    /* ─── 14. Save ──────────────────────────────────────────── */
    btnSave.addEventListener('click', () => {
        const name     = inputName.value.trim();
        const parentId = selParent.value || null;
        const status   = statusToggle.checked;
        const image    = inputImage.value.trim() || null;

        if (!name) {
            inputName.classList.add('is-invalid');
            nameError.textContent = 'Vui lòng nhập tên danh mục.';
            inputName.focus();
            return;
        }
        clearModalError();

        const url    = editingId ? apiUrl(ROUTES.update, editingId) : ROUTES.store;
        const method = editingId ? 'PATCH' : 'POST';

        btnSave.disabled    = true;
        btnSave.textContent = 'Đang lưu...';

        fetch(url, {
            method,
            headers: headers(),
            body: JSON.stringify({ name, parent_id: parentId, status, image }),
        })
            .then(r => r.json())
            .then(res => {
                if (res.errors) {
                    const msgs = Object.values(res.errors).flat();
                    nameError.textContent = msgs[0] ?? 'Có lỗi.';
                    inputName.classList.add('is-invalid');
                    return;
                }
                modal.hide();
                showToast(res.message ?? 'Đã lưu thành công.', 'success');
                loadCategories();
            })
            .catch(() => showToast('Lỗi kết nối.', 'error'))
            .finally(() => {
                btnSave.disabled    = false;
                btnSave.textContent = 'Lưu';
            });
    });

    /* ─── 14b. Image upload & preview ───────────────────────── */
    if (inputImage) {
        inputImage.addEventListener('input', function () {
            showImgPreview(this.value.trim());
        });
    }

    if (btnUploadImg && filePicker) {
        btnUploadImg.addEventListener('click', () => filePicker.click());

        filePicker.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);
            formData.append('folder', 'categories');

            btnUploadImg.disabled = true;
            btnUploadImg.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang tải...';

            fetch(ROUTES.upload, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': ROUTES.csrf },
                body: formData,
            })
                .then(r => r.json())
                .then(json => {
                    if (json.status === 'success' && json.url) {
                        inputImage.value = json.url;
                        showImgPreview(json.url);
                        showToast('Tải ảnh lên thành công!', 'success');
                    } else {
                        showToast(json.message ?? 'Lỗi tải ảnh.', 'error');
                    }
                })
                .catch(() => showToast('Có lỗi xảy ra khi tải tệp ảnh.', 'error'))
                .finally(() => {
                    btnUploadImg.disabled = false;
                    btnUploadImg.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải lên';
                    filePicker.value = '';
                });
        });
    }

    if (btnClearImg) {
        btnClearImg.addEventListener('click', clearImgPreview);
    }

    /* ─── 15. Delete ────────────────────────────────────────── */
    window.catDelete = function (id, name) {
        if (!confirm(`Xóa danh mục "${name}"?\n\nCác danh mục con và liên kết gian hàng có thể bị ảnh hưởng.`)) return;

        fetch(apiUrl(ROUTES.destroy, id), {
            method:  'DELETE',
            headers: headers(),
        })
            .then(r => r.json())
            .then(res => {
                showToast(res.message ?? 'Đã xóa.', 'success');
                openNodes.delete(id);
                selectedIds.delete(id);
                loadCategories();
            })
            .catch(() => showToast('Lỗi kết nối.', 'error'));
    };

    /* ─── 16. Checkbox select ───────────────────────────────── */
    tableBody.addEventListener('change', e => {
        const cb = e.target.closest('.row-check');
        if (!cb) return;
        const id = Number(cb.dataset.id);
        if (cb.checked) selectedIds.add(id);
        else selectedIds.delete(id);
        updateSelectInfo();
        syncCheckAll();
        cb.closest('tr').classList.toggle('selected', cb.checked);
    });

    checkAll.addEventListener('change', () => {
        const checked = checkAll.checked;
        tableBody.querySelectorAll('.row-check').forEach(cb => {
            cb.checked = checked;
            const id   = Number(cb.dataset.id);
            if (checked) selectedIds.add(id);
            else selectedIds.delete(id);
            cb.closest('tr').classList.toggle('selected', checked);
        });
        updateSelectInfo();
    });

    function syncCheckAll() {
        const all    = tableBody.querySelectorAll('.row-check');
        const ticked = tableBody.querySelectorAll('.row-check:checked');
        checkAll.indeterminate = ticked.length > 0 && ticked.length < all.length;
        checkAll.checked       = all.length > 0 && ticked.length === all.length;
    }

    function updateSelectInfo() {
        selectInfo.textContent = `${selectedIds.size} dòng đã chọn`;
    }

    /* ─── 17. Filter chips ──────────────────────────────────── */
    document.querySelectorAll('.cat-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            activeFilter = chip.dataset.filter;
            applyFilterAndRender();
        });
    });

    /* ─── 18. Search debounce ───────────────────────────────── */
    let searchTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilterAndRender, 220);
    });

    /* ─── 19. Modal toggle label ────────────────────────────── */
    statusToggle.addEventListener('change', updateStatusLabel);

    function updateStatusLabel() {
        statusLabel.textContent = statusToggle.checked ? 'Hiển thị' : 'Đã ẩn';
    }

    function clearModalError() {
        inputName.classList.remove('is-invalid');
        nameError.textContent = '';
    }

    /* ─── 20. Parent dropdown ───────────────────────────────── */
    function buildParentDropdown() {
        selParent.innerHTML = '<option value="">— Là danh mục gốc —</option>';
        allCategories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            selParent.appendChild(opt);
        });
    }

    /* ─── Boot ──────────────────────────────────────────────── */
    loadCategories();
});
