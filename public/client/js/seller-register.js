/**
 * CUPO — Seller Registration: Category Tag Multi-Select
 * File: public/client/js/seller-register.js
 *
 * Phụ thuộc: DOM elements có id:
 *   parentCategorySelect, childCategorySelect,
 *   addCategoryTagBtn, categoryTagsWrap,
 *   categoryHiddenInputs, categoryTagsPlaceholder
 */

document.addEventListener('DOMContentLoaded', function () {
    const parentSelect = document.getElementById('parentCategorySelect');
    const childSelect  = document.getElementById('childCategorySelect');
    const addBtn       = document.getElementById('addCategoryTagBtn');
    const tagsWrap     = document.getElementById('categoryTagsWrap');
    const hiddenInputs = document.getElementById('categoryHiddenInputs');
    const placeholder  = document.getElementById('categoryTagsPlaceholder');

    // Nếu không tìm thấy các element cần thiết (trang khác) → dừng
    if (!parentSelect || !childSelect || !addBtn) return;

    // Map lưu các danh mục đã chọn: { id (string) -> label (string) }
    const selectedMap = {};

    /* ---- Helper ---- */
    function refreshPlaceholder() {
        if (!placeholder) return;
        placeholder.style.display = Object.keys(selectedMap).length === 0 ? '' : 'none';
    }

    function addTag(id, label) {
        id = String(id);
        if (selectedMap[id]) return; // Chống thêm trùng
        selectedMap[id] = label;

        // Tạo tag DOM
        const tag = document.createElement('span');
        tag.className = 'cat-tag';
        tag.dataset.id = id;
        tag.innerHTML = label + '<button type="button" class="cat-tag-remove" title="Xoá">&#x2715;</button>';

        // Nút X: xóa tag + hidden input
        tag.querySelector('.cat-tag-remove').addEventListener('click', function () {
            delete selectedMap[id];
            tag.remove();
            const hi = hiddenInputs.querySelector('input[value="' + CSS.escape(id) + '"]');
            if (hi) hi.remove();
            refreshPlaceholder();
        });

        tagsWrap.appendChild(tag);

        // Thêm hidden input để form POST gửi category_ids[]
        const inp  = document.createElement('input');
        inp.type   = 'hidden';
        inp.name   = 'category_ids[]';
        inp.value  = id;
        hiddenInputs.appendChild(inp);

        refreshPlaceholder();
    }

    /* ---- Chọn ngành hàng cha → nạp danh mục con ---- */
    parentSelect.addEventListener('change', function () {
        const selectedOpt  = parentSelect.options[parentSelect.selectedIndex];
        const childrenData = selectedOpt.dataset.children
            ? JSON.parse(selectedOpt.dataset.children)
            : [];

        // Reset dropdown con
        childSelect.innerHTML = '<option value="" disabled selected>-- Chọn mặt hàng cụ thể --</option>';
        addBtn.disabled = true;

        if (childrenData && childrenData.length > 0) {
            // Có con → người dùng chọn con rồi mới Thêm
            childSelect.disabled = false;
            childrenData.forEach(function (child) {
                const opt = document.createElement('option');
                opt.value = child.id;
                opt.textContent = child.name;
                childSelect.appendChild(opt);
            });
        } else {
            // Không có con → cho phép thêm thẳng danh mục cha
            childSelect.disabled = true;
            addBtn.disabled = false;
            addBtn.dataset.catId    = parentSelect.value;
            addBtn.dataset.catLabel = selectedOpt.textContent.trim();
        }
    });

    /* ---- Chọn danh mục con → kích hoạt nút Thêm ---- */
    childSelect.addEventListener('change', function () {
        if (childSelect.value) {
            addBtn.disabled = false;
            addBtn.dataset.catId    = childSelect.value;
            addBtn.dataset.catLabel = childSelect.options[childSelect.selectedIndex].textContent.trim();
        } else {
            addBtn.disabled = true;
        }
    });

    /* ---- Nhấn nút Thêm → tạo tag ---- */
    addBtn.addEventListener('click', function () {
        if (addBtn.dataset.catId) {
            addTag(addBtn.dataset.catId, addBtn.dataset.catLabel);
        }
    });

    /* ---- Khởi tạo ---- */
    refreshPlaceholder();
});
