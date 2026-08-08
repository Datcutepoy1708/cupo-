/**
 * CUPO ADMIN — Quản lý Danh mục
 * File: public/admin/js/categories.js
 *
 * Data context được đọc từ data-* attributes trên #categoriesApp
 * (xem admin/categories/index.blade.php)
 */

document.addEventListener('DOMContentLoaded', function () {
    /* -------------------------------------------------------
       0. Config từ data-* attributes (Rule 20: không inline JS)
    ------------------------------------------------------- */
    const app      = document.getElementById('categoriesApp');
    const ROUTES   = {
        data:    app.dataset.dataUrl,
        store:   app.dataset.storeUrl,
        update:  app.dataset.updateUrl,   // __ID__ placeholder
        destroy: app.dataset.destroyUrl,  // __ID__ placeholder
        csrf:    app.dataset.csrf,
    };

    /* -------------------------------------------------------
       1. DOM references
    ------------------------------------------------------- */
    const treeWrap    = document.getElementById('categoryTree');
    const searchInput = document.getElementById('catSearchInput');
    const modalEl     = document.getElementById('catModal');
    const modal       = new bootstrap.Modal(modalEl);
    const modalTitle  = document.getElementById('catModalTitle');
    const modalForm   = document.getElementById('catForm');
    const inputName   = document.getElementById('catName');
    const selParent   = document.getElementById('catParentId');
    const selStatus   = document.getElementById('catStatus');
    const btnSave     = document.getElementById('btnCatSave');
    const toastEl     = document.getElementById('catToast');
    const toastMsg    = document.getElementById('catToastMsg');
    const bsToast     = new bootstrap.Toast(toastEl, { delay: 3000 });

    // Stat elements
    const statTotal    = document.getElementById('statTotal');
    const statParent   = document.getElementById('statParent');
    const statChildren = document.getElementById('statChildren');

    /* -------------------------------------------------------
       2. State
    ------------------------------------------------------- */
    let allCategories = [];   // raw data từ API
    let editingId     = null; // null = create mode, số = edit mode
    let openNodes     = new Set(); // lưu id của node đang mở

    /* -------------------------------------------------------
       3. Utilities
    ------------------------------------------------------- */
    function showToast(message, type = 'success') {
        toastEl.className = 'toast align-items-center border-0 toast-' + type;
        toastMsg.textContent = message;
        bsToast.show();
    }

    function apiUrl(template, id) {
        return template.replace('__ID__', id);
    }

    function headers() {
        return {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': ROUTES.csrf,
        };
    }

    /* -------------------------------------------------------
       4. Load + render tree
    ------------------------------------------------------- */
    function loadCategories() {
        renderSkeleton();
        fetch(ROUTES.data, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                allCategories = res.data || [];
                renderStats();
                renderTree(allCategories);
                buildParentDropdown(allCategories);
            })
            .catch(() => {
                treeWrap.innerHTML = '<div class="cat-empty"><i class="fa-solid fa-circle-exclamation"></i><p>Không thể tải dữ liệu.</p></div>';
            });
    }

    function renderSkeleton() {
        let html = '';
        for (let i = 0; i < 5; i++) {
            html += `
            <div class="cat-skeleton-row">
                <div class="skeleton-box" style="width:36px;height:36px;border-radius:8px;"></div>
                <div style="flex:1;">
                    <div class="skeleton-box" style="height:13px;width:160px;margin-bottom:6px;"></div>
                    <div class="skeleton-box" style="height:10px;width:90px;"></div>
                </div>
                <div class="skeleton-box" style="height:22px;width:60px;border-radius:20px;"></div>
            </div>`;
        }
        treeWrap.innerHTML = html;
    }

    function renderStats() {
        const parentCount   = allCategories.length;
        const childrenCount = allCategories.reduce((sum, c) => sum + (c.children_count || 0), 0);
        statTotal.textContent    = parentCount + childrenCount;
        statParent.textContent   = parentCount;
        statChildren.textContent = childrenCount;
    }

    function renderTree(categories, keyword = '') {
        const kw = keyword.trim().toLowerCase();

        if (categories.length === 0) {
            treeWrap.innerHTML = '<div class="cat-empty"><i class="fa-solid fa-folder-open"></i><p>Chưa có danh mục nào.</p></div>';
            return;
        }

        let html = '';
        let visibleCount = 0;

        categories.forEach(cat => {
            const matchParent = cat.name.toLowerCase().includes(kw);
            const matchChildren = (cat.children || []).some(ch => ch.name.toLowerCase().includes(kw));

            if (kw && !matchParent && !matchChildren) return;
            visibleCount++;

            const hasChildren = cat.children && cat.children.length > 0;
            const isOpen      = openNodes.has(cat.id) || (kw && matchChildren);
            const statusClass = cat.status ? 'active' : 'inactive';
            const statusLabel = cat.status ? 'Hoạt động' : 'Ẩn';

            html += `
            <div class="cat-node" data-id="${cat.id}">
                <div class="cat-root-row" onclick="catToggle(${cat.id})">
                    <span class="cat-toggle ${hasChildren ? '' : 'no-children'} ${isOpen ? 'open' : ''}">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                    <div class="cat-icon">${cat.name.charAt(0).toUpperCase()}</div>
                    <div style="flex:1; min-width:0;">
                        <div class="cat-name">${escHtml(cat.name)}</div>
                        <div class="cat-meta">/${escHtml(cat.slug || '')} &bull; ${cat.children_count || 0} danh mục con &bull; ${cat.seller_profiles_count || 0} gian hàng</div>
                    </div>
                    <div class="cat-badge-wrap">
                        <span class="cat-status-badge ${statusClass}">${statusLabel}</span>
                        <div class="cat-row-actions" onclick="event.stopPropagation()">
                            <button class="btn-row-icon add" title="Thêm danh mục con" onclick="catOpenCreateChild(${cat.id}, '${escAttr(cat.name)}')">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                            <button class="btn-row-icon edit" title="Sửa" onclick="catOpenEdit(${cat.id})">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn-row-icon del" title="Xóa" onclick="catDelete(${cat.id}, '${escAttr(cat.name)}')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;

            if (hasChildren) {
                html += `<div class="cat-children-wrap ${isOpen ? 'open' : ''}" id="children-${cat.id}">`;
                (cat.children || []).forEach(child => {
                    if (kw && !child.name.toLowerCase().includes(kw) && !matchParent) return;
                    const cStatusClass = child.status ? 'active' : 'inactive';
                    const cStatusLabel = child.status ? 'Hoạt động' : 'Ẩn';
                    html += `
                    <div class="cat-child-row">
                        <div class="cat-icon child">${child.name.charAt(0).toUpperCase()}</div>
                        <div style="flex:1; min-width:0;">
                            <div class="cat-child-name">${escHtml(child.name)}</div>
                            <div class="cat-meta">/${escHtml(child.slug || '')}</div>
                        </div>
                        <div class="cat-badge-wrap">
                            <span class="cat-status-badge ${cStatusClass}">${cStatusLabel}</span>
                            <div class="cat-row-actions">
                                <button class="btn-row-icon edit" title="Sửa" onclick="catOpenEdit(${child.id})">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn-row-icon del" title="Xóa" onclick="catDelete(${child.id}, '${escAttr(child.name)}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>`;
                });
                html += `</div>`;
            }

            html += `</div>`;
        });

        if (visibleCount === 0) {
            treeWrap.innerHTML = '<div class="cat-empty"><i class="fa-solid fa-magnifying-glass"></i><p>Không tìm thấy danh mục phù hợp.</p></div>';
        } else {
            treeWrap.innerHTML = html;
        }
    }

    function buildParentDropdown(categories) {
        selParent.innerHTML = '<option value="">-- Là danh mục gốc --</option>';
        categories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            selParent.appendChild(opt);
        });
    }

    /* -------------------------------------------------------
       5. Toggle expand node (exposed globally for onclick)
    ------------------------------------------------------- */
    window.catToggle = function (id) {
        const childrenDiv = document.getElementById('children-' + id);
        const toggleBtn   = document.querySelector(`.cat-node[data-id="${id}"] .cat-toggle`);
        if (!childrenDiv) return;

        if (openNodes.has(id)) {
            openNodes.delete(id);
            childrenDiv.classList.remove('open');
            toggleBtn && toggleBtn.classList.remove('open');
        } else {
            openNodes.add(id);
            childrenDiv.classList.add('open');
            toggleBtn && toggleBtn.classList.add('open');
        }
    };

    /* -------------------------------------------------------
       6. Modal: Create root
    ------------------------------------------------------- */
    document.getElementById('btnAddCategory').addEventListener('click', () => {
        editingId = null;
        modalTitle.textContent = 'Thêm danh mục mới';
        modalForm.reset();
        selParent.value = '';
        selStatus.value = '1';
        modal.show();
    });

    /* -------------------------------------------------------
       7. Modal: Create child (exposed globally for onclick)
    ------------------------------------------------------- */
    window.catOpenCreateChild = function (parentId, parentName) {
        editingId = null;
        modalTitle.textContent = `Thêm danh mục con vào "${parentName}"`;
        modalForm.reset();
        selParent.value = parentId;
        selStatus.value = '1';
        modal.show();
    };

    /* -------------------------------------------------------
       8. Modal: Edit (exposed globally for onclick)
    ------------------------------------------------------- */
    window.catOpenEdit = function (id) {
        // Tìm category trong data đã tải
        let found = null;
        allCategories.forEach(cat => {
            if (cat.id === id) found = cat;
            (cat.children || []).forEach(ch => { if (ch.id === id) found = ch; });
        });
        if (!found) return;

        editingId = id;
        modalTitle.textContent = 'Sửa danh mục';
        inputName.value        = found.name;
        selParent.value        = found.parent_id ?? '';
        selStatus.value        = found.status ? '1' : '0';
        modal.show();
    };

    /* -------------------------------------------------------
       9. Save (Create / Update)
    ------------------------------------------------------- */
    btnSave.addEventListener('click', () => {
        const name     = inputName.value.trim();
        const parentId = selParent.value || null;
        const status   = selStatus.value === '1';

        if (!name) {
            inputName.focus();
            inputName.classList.add('is-invalid');
            return;
        }
        inputName.classList.remove('is-invalid');

        const url    = editingId ? apiUrl(ROUTES.update, editingId) : ROUTES.store;
        const method = editingId ? 'PATCH' : 'POST';

        btnSave.disabled = true;
        btnSave.textContent = 'Đang lưu...';

        fetch(url, {
            method,
            headers: headers(),
            body: JSON.stringify({ name, parent_id: parentId, status }),
        })
            .then(r => r.json())
            .then(res => {
                if (res.message) {
                    modal.hide();
                    showToast(res.message, 'success');
                    loadCategories();
                } else {
                    showToast('Có lỗi xảy ra, vui lòng thử lại.', 'error');
                }
            })
            .catch(() => showToast('Lỗi kết nối.', 'error'))
            .finally(() => {
                btnSave.disabled = false;
                btnSave.textContent = 'Lưu';
            });
    });

    /* -------------------------------------------------------
       10. Delete (exposed globally for onclick)
    ------------------------------------------------------- */
    window.catDelete = function (id, name) {
        if (!confirm(`Xóa danh mục "${name}"?\n\nDanh mục con và liên kết với gian hàng sẽ bị ảnh hưởng.`)) return;

        fetch(apiUrl(ROUTES.destroy, id), {
            method: 'DELETE',
            headers: headers(),
        })
            .then(r => r.json())
            .then(res => {
                showToast(res.message || 'Đã xóa.', 'success');
                openNodes.delete(id);
                loadCategories();
            })
            .catch(() => showToast('Lỗi kết nối.', 'error'));
    };

    /* -------------------------------------------------------
       11. Search debounce
    ------------------------------------------------------- */
    let searchTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            renderTree(allCategories, searchInput.value);
        }, 220);
    });

    /* -------------------------------------------------------
       12. Escape helpers
    ------------------------------------------------------- */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escAttr(str) {
        return String(str).replace(/'/g, "\\'");
    }

    /* -------------------------------------------------------
       13. Bootstrap
    ------------------------------------------------------- */
    loadCategories();
});
