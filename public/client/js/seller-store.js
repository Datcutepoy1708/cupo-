document.addEventListener('DOMContentLoaded', function () {
    const tabLinks = document.querySelectorAll('.shop-nav-tabs [data-bs-toggle="pill"]');

    // 1. Khi người dùng chuyển tab -> cập nhật hash trên URL (không reload trang)
    tabLinks.forEach(function (link) {
        link.addEventListener('shown.bs.tab', function (e) {
            const targetId = e.target.getAttribute('href'); // vd: "#dashOrders"
            history.replaceState(null, '', targetId);
        });
    });

    // 2. Khi trang load (F5, mở link có #hash, hoặc quay lại từ redirect) -> active đúng tab theo hash
    activateTabFromHash();

    function activateTabFromHash() {
        const hash = window.location.hash; // vd: "#dashOrders"
        if (!hash) return;

        const trigger = document.querySelector(`.shop-nav-tabs [href="${hash}"]`);
        if (trigger) {
            const tab = new bootstrap.Tab(trigger);
            tab.show();
        }
    }

    // 3. Trước khi submit các form nằm trong tab-pane (vd: xác nhận/từ chối đơn hàng)
    //    -> gắn hash hiện tại vào action URL để sau khi redirect vẫn còn #dashOrders
    document.querySelectorAll('.tab-pane form').forEach(function (form) {
        form.addEventListener('submit', function () {
            const paneId = form.closest('.tab-pane')?.id;
            if (paneId) {
                const url = new URL(form.action, window.location.origin);
                url.hash = paneId;
                form.action = url.toString();
            }
        });
    });

    // 4. Tính toán và hiển thị % giảm giá khi nhập Giá gốc & Giá sale
    function setupDiscountCalculation(priceId, salePriceId, calcBoxId, percentId, amountId) {
        const priceInput = document.getElementById(priceId);
        const salePriceInput = document.getElementById(salePriceId);
        const calcBox = document.getElementById(calcBoxId);
        const percentSpan = document.getElementById(percentId);
        const amountSpan = document.getElementById(amountId);

        if (!priceInput || !salePriceInput || !calcBox) return;

        function updateDiscount() {
            const price = parseFloat(priceInput.value) || 0;
            const salePrice = parseFloat(salePriceInput.value) || 0;

            if (price > 0 && salePrice > 0 && salePrice < price) {
                const discount = Math.round(((price - salePrice) / price) * 100);
                const saved = price - salePrice;
                percentSpan.textContent = `-${discount}%`;
                amountSpan.textContent = `${new Intl.NumberFormat('vi-VN').format(saved)}₫`;
                calcBox.classList.remove('d-none');
            } else {
                calcBox.classList.add('d-none');
            }
        }

        priceInput.addEventListener('input', updateDiscount);
        salePriceInput.addEventListener('input', updateDiscount);
        updateDiscount();
    }

    setupDiscountCalculation('add_price', 'add_sale_price', 'add_discount_calc', 'add_discount_percent', 'add_discount_amount');
    setupDiscountCalculation('edit_price', 'edit_sale_price', 'edit_discount_calc', 'edit_discount_percent', 'edit_discount_amount');

    // 5. Xử lý mở Modal Chỉnh sửa sản phẩm và điền thông tin
    const editProductButtons = document.querySelectorAll('.btn-edit-product');
    const editProductForm = document.getElementById('editProductForm');

    editProductButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name || '';
            const categoryId = this.dataset.category_id || '';
            const sku = this.dataset.sku || '';
            const price = this.dataset.price || '';
            const salePrice = this.dataset.sale_price || '';
            const stock = this.dataset.stock || '';
            const description = this.dataset.description || '';
            const thumbnail = this.dataset.thumbnail || '';

            if (editProductForm) {
                editProductForm.action = `/seller/products/${id}`;
                document.getElementById('edit_product_id').value = id;
                document.getElementById('edit_product_name').value = name;
                document.getElementById('edit_category_id').value = categoryId;
                document.getElementById('edit_sku').value = sku;
                document.getElementById('edit_price').value = price;
                document.getElementById('edit_sale_price').value = salePrice;
                document.getElementById('edit_stock').value = stock;
                document.getElementById('edit_description').value = description;

                // Preview ảnh hiện tại
                const previewWrap = document.getElementById('edit_thumbnail_preview_wrap');
                const previewImg = document.getElementById('edit_thumbnail_preview');
                if (previewWrap && previewImg && thumbnail) {
                    previewImg.src = thumbnail;
                    previewWrap.classList.remove('d-none');
                } else if (previewWrap) {
                    previewWrap.classList.add('d-none');
                }

                // Cập nhật lại % giảm giá
                const priceInput = document.getElementById('edit_price');
                const salePriceInput = document.getElementById('edit_sale_price');
                if (priceInput && salePriceInput) {
                    priceInput.dispatchEvent(new Event('input'));
                }
            }
        });
    });

    // 6. Xử lý submit AJAX cho Form Thêm sản phẩm
    const addProductForm = document.getElementById('addProductForm');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btnAddProductSubmit');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang lưu...';
            }

            const formData = new FormData(addProductForm);
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(addProductForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    let errMsg = data.message || 'Đã có lỗi xảy ra khi lưu sản phẩm.';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join('\n');
                    }
                    throw new Error(errMsg);
                }
                return data;
            })
            .then((data) => {
                alert(data.message || 'Thêm sản phẩm thành công!');
                window.location.hash = '#dashProducts';
                window.location.reload();
            })
            .catch((err) => {
                alert(err.message);
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });
        });
    }

    // 7. Xử lý submit AJAX cho Form Sửa sản phẩm
    if (editProductForm) {
        editProductForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btnEditProductSubmit');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang cập nhật...';
            }

            const formData = new FormData(editProductForm);
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(editProductForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    let errMsg = data.message || 'Đã có lỗi xảy ra khi cập nhật.';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join('\n');
                    }
                    throw new Error(errMsg);
                }
                return data;
            })
            .then((data) => {
                alert(data.message || 'Cập nhật sản phẩm thành công!');
                window.location.hash = '#dashProducts';
                window.location.reload();
            })
            .catch((err) => {
                alert(err.message);
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });
        });
    }

    // 8. Xử lý xóa sản phẩm
    const deleteButtons = document.querySelectorAll('.btn-delete-product');
    deleteButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name || 'sản phẩm này';

            if (!confirm(`Bạn có chắc chắn muốn xóa "${name}" không?`)) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`/seller/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Không thể xóa sản phẩm.');
                }
                return data;
            })
            .then((data) => {
                alert(data.message || 'Xóa sản phẩm thành công!');
                const row = document.getElementById(`product-row-${id}`);
                if (row) {
                    row.remove();
                } else {
                    window.location.hash = '#dashProducts';
                    window.location.reload();
                }
            })
            .catch((err) => {
                alert(err.message);
            });
        });
    });
});