document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. Form Modal Handler (Create / Edit)
    const flashSaleForm = document.getElementById('flashSaleForm');
    const flashSaleFormModalEl = document.getElementById('flashSaleFormModal');
    const flashSaleFormModal = flashSaleFormModalEl ? new bootstrap.Modal(flashSaleFormModalEl) : null;
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const formMethod = document.getElementById('formMethod');
    const modalTitle = document.getElementById('flashSaleFormModalLabel');

    if (btnOpenCreateModal) {
        btnOpenCreateModal.addEventListener('click', function () {
            flashSaleForm.reset();
            flashSaleForm.action = flashSaleForm.getAttribute('data-store-url') || '/admin/flash-sales';
            formMethod.value = 'POST';
            if (modalTitle) modalTitle.textContent = 'Tạo phiên Flash Sale mới';
            clearFormErrors();
        });
    }

    document.querySelectorAll('.btn-edit-sale').forEach(button => {
        button.addEventListener('click', function () {
            const updateUrl = this.getAttribute('data-update-url');
            const name = this.getAttribute('data-name');
            const startsAt = this.getAttribute('data-starts-at');
            const endsAt = this.getAttribute('data-ends-at');
            const registrationDeadline = this.getAttribute('data-registration-deadline');
            const status = this.getAttribute('data-status') === '1';

            flashSaleForm.action = updateUrl;
            formMethod.value = 'PUT';
            if (modalTitle) modalTitle.textContent = 'Chỉnh sửa phiên Flash Sale';

            document.getElementById('flash_sale_name').value = name || '';
            document.getElementById('flash_sale_starts_at').value = startsAt || '';
            document.getElementById('flash_sale_ends_at').value = endsAt || '';
            document.getElementById('flash_sale_registration_deadline').value = registrationDeadline || '';
            document.getElementById('flash_sale_status').checked = status;

            clearFormErrors();
            if (flashSaleFormModal) flashSaleFormModal.show();
        });
    });

    if (flashSaleForm) {
        flashSaleForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearFormErrors();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                if (status === 200 || status === 201) {
                    if (flashSaleFormModal) flashSaleFormModal.hide();
                    window.location.reload();
                } else if (status === 422 && body.errors) {
                    displayFormErrors(body.errors);
                } else {
                    alert(body.message || 'Đã có lỗi xảy ra!');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Không thể gửi dữ liệu lên máy chủ!');
            });
        });
    }

    // 2. Toggle Status Handler
    document.querySelectorAll('.toggle-status-btn').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const toggleUrl = this.getAttribute('data-url');
            const currentChecked = this.checked;

            fetch(toggleUrl, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    this.checked = !currentChecked;
                    alert(data.message || 'Không thể đổi trạng thái!');
                }
            })
            .catch(err => {
                console.error(err);
                this.checked = !currentChecked;
                alert('Lỗi kết nối máy chủ!');
            });
        });
    });

    // 3. Delete Sale Handler
    document.querySelectorAll('.btn-delete-sale').forEach(btn => {
        btn.addEventListener('click', function () {
            const deleteUrl = this.getAttribute('data-delete-url');

            if (!confirm('Bạn có chắc chắn muốn xóa phiên Flash Sale này?')) {
                return;
            }

            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Không thể xóa phiên!');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối máy chủ!');
            });
        });
    });

    // 4. Products Management Modal Handler
    const flashSaleProductsModalEl = document.getElementById('flashSaleProductsModal');
    const flashSaleProductsModal = flashSaleProductsModalEl ? new bootstrap.Modal(flashSaleProductsModalEl) : null;
    const selectProductToAdd = document.getElementById('selectProductToAdd');
    const btnAddProductToTable = document.getElementById('btnAddProductToTable');
    const tableBody = document.getElementById('flashSaleProductsTableBody');
    const emptyRow = document.getElementById('emptyProductsRow');
    const currentSessionTitle = document.getElementById('currentSessionTitle');
    const btnSaveSyncProducts = document.getElementById('btnSaveSyncProducts');
    const productsFormAction = document.getElementById('productsFormAction');

    document.querySelectorAll('.btn-manage-products').forEach(btn => {
        btn.addEventListener('click', function () {
            const saleName = this.getAttribute('data-name');
            const syncUrl = this.getAttribute('data-sync-url');
            const productsJson = this.getAttribute('data-products');
            let products = [];

            try {
                products = JSON.parse(productsJson || '[]');
            } catch (e) {
                products = [];
            }

            if (currentSessionTitle) currentSessionTitle.textContent = saleName;
            if (productsFormAction) productsFormAction.value = syncUrl;

            // Clear table rows except emptyRow
            tableBody.querySelectorAll('tr:not(#emptyProductsRow)').forEach(tr => tr.remove());

            if (products.length > 0) {
                if (emptyRow) emptyRow.classList.add('d-none');
                products.forEach(item => {
                    addProductRow({
                        id: item.product_id,
                        name: item.product ? item.product.name : 'Sản phẩm #' + item.product_id,
                        price: item.product ? item.product.price : item.flash_sale_price,
                        stock: item.product ? item.product.stock : item.quantity_limit,
                        flash_sale_price: item.flash_sale_price,
                        quantity_limit: item.quantity_limit,
                        quantity_sold: item.quantity_sold || 0
                    });
                });
            } else {
                if (emptyRow) emptyRow.classList.remove('d-none');
            }

            if (flashSaleProductsModal) flashSaleProductsModal.show();
        });
    });

    if (btnAddProductToTable) {
        btnAddProductToTable.addEventListener('click', function () {
            const selectedOpt = selectProductToAdd.options[selectProductToAdd.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) {
                alert('Vui lòng chọn 1 sản phẩm!');
                return;
            }

            const productId = selectedOpt.value;
            const name = selectedOpt.getAttribute('data-name');
            const price = parseFloat(selectedOpt.getAttribute('data-price') || 0);
            const stock = parseInt(selectedOpt.getAttribute('data-stock') || 0);

            // Check if already in table
            if (tableBody.querySelector(`tr[data-product-id="${productId}"]`)) {
                alert('Sản phẩm này đã có trong danh sách!');
                return;
            }

            if (emptyRow) emptyRow.classList.add('d-none');

            // Default flash_sale_price = 80% of price
            const defaultFsPrice = Math.floor(price * 0.8);
            const defaultLimit = Math.min(stock, 10);

            addProductRow({
                id: productId,
                name: name,
                price: price,
                stock: stock,
                flash_sale_price: defaultFsPrice,
                quantity_limit: defaultLimit,
                quantity_sold: 0
            });

            selectProductToAdd.value = '';
        });
    }

    function addProductRow(data) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-product-id', data.id);

        const formattedPrice = new Intl.NumberFormat('vi-VN').format(data.price);

        tr.innerHTML = `
            <td>
                <span class="fw-semibold text-dark d-block">${escapeHtml(data.name)}</span>
                <input type="hidden" name="products[${data.id}][product_id]" value="${data.id}">
            </td>
            <td>
                <span class="d-block text-primary fw-bold">${formattedPrice}đ</span>
                <span class="badge bg-light text-muted border">Tồn kho: ${data.stock}</span>
            </td>
            <td>
                <input type="number" 
                       class="form-control form-control-sm input-fs-price" 
                       name="products[${data.id}][flash_sale_price]" 
                       value="${data.flash_sale_price}" 
                       max="${Math.floor(data.price * 0.9)}" 
                       min="1" 
                       required>
                <div class="form-text text-muted small" style="font-size:0.75rem">Max: ${new Intl.NumberFormat('vi-VN').format(Math.floor(data.price * 0.9))}đ</div>
            </td>
            <td>
                <input type="number" 
                       class="form-control form-control-sm input-fs-limit" 
                       name="products[${data.id}][quantity_limit]" 
                       value="${data.quantity_limit}" 
                       max="${data.stock}" 
                       min="1" 
                       required>
                <div class="form-text text-muted small" style="font-size:0.75rem">Đã bán: ${data.quantity_sold || 0}</div>
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                    <i class="fa-solid fa-times"></i>
                </button>
            </td>
        `;

        tr.querySelector('.btn-remove-row').addEventListener('click', function () {
            tr.remove();
            if (tableBody.querySelectorAll('tr:not(#emptyProductsRow)').length === 0) {
                if (emptyRow) emptyRow.classList.remove('d-none');
            }
        });

        tableBody.appendChild(tr);
    }

    if (btnSaveSyncProducts) {
        btnSaveSyncProducts.addEventListener('click', function () {
            const syncUrl = productsFormAction.value;
            if (!syncUrl) return;

            const form = document.getElementById('flashSaleProductsForm');
            const formData = new FormData(form);

            fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: new URLSearchParams(formData)
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                if (status === 200) {
                    alert(body.message || 'Đồng bộ thành công!');
                    if (flashSaleProductsModal) flashSaleProductsModal.hide();
                    window.location.reload();
                } else if (status === 422 && body.errors) {
                    let errMsgs = [];
                    Object.values(body.errors).forEach(errList => {
                        errMsgs = errMsgs.concat(errList);
                    });
                    alert('Lỗi xác thực:\n- ' + errMsgs.join('\n- '));
                } else {
                    alert(body.message || 'Đồng bộ thất bại!');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối máy chủ!');
            });
        });
    }

    // Helper functions
    function clearFormErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
    }

    function displayFormErrors(errors) {
        Object.keys(errors).forEach(field => {
            const errorEl = document.getElementById('error-' + field);
            const inputEl = document.getElementById('flash_sale_' + field);

            if (inputEl) inputEl.classList.add('is-invalid');
            if (errorEl) errorEl.textContent = errors[field][0];
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
});
