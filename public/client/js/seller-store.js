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

    // =========================================================================
    // 5. SHOPEE & TIKTOK SHOP STYLE PRODUCT VARIANT MANAGER
    // =========================================================================
    class ProductVariantManager {
        constructor(prefix) {
            this.prefix = prefix;
            this.toggleSwitch = document.getElementById(`${prefix}_has_variants_toggle`);
            this.configWrap = document.getElementById(`${prefix}_variant_config_wrap`);
            this.simplePriceRow = document.getElementById(`${prefix}_simple_price_row`);
            this.priceInput = document.getElementById(`${prefix}_price`);
            this.stockInput = document.getElementById(`${prefix}_stock`);

            // Group 1
            this.group1NameInput = document.getElementById(`${prefix}_group1_name`);
            this.group1Container = document.getElementById(`${prefix}_group1_items_container`);
            this.btnAddGroup1Val = document.getElementById(`${prefix}_btn_add_group1_val`);

            // Group 2
            this.group2Card = document.getElementById(`${prefix}_group2_card`);
            this.group2NameInput = document.getElementById(`${prefix}_group2_name`);
            this.group2Container = document.getElementById(`${prefix}_group2_items_container`);
            this.btnAddGroup2Val = document.getElementById(`${prefix}_btn_add_group2_val`);
            this.btnShowGroup2 = document.getElementById(`${prefix}_btn_show_group2`);
            this.btnRemoveGroup2 = document.getElementById(`${prefix}_btn_remove_group2`);
            this.addGroup2Wrap = document.getElementById(`${prefix}_add_group2_btn_wrap`);

            // Matrix Table
            this.thGroup1 = document.getElementById(`${prefix}_th_group1`);
            this.thGroup2 = document.getElementById(`${prefix}_th_group2`);
            this.tbody = document.getElementById(`${prefix}_matrix_tbody`);
            this.variantsCountEl = document.getElementById(`${prefix}_variants_count`);
            this.totalStockEl = document.getElementById(`${prefix}_total_stock`);
            this.priceRangeEl = document.getElementById(`${prefix}_price_range`);

            // Batch apply
            this.btnBatchApply = document.getElementById(`${prefix}_btn_batch_apply`);
            this.batchPrice = document.getElementById(`${prefix}_batch_price`);
            this.batchSalePrice = document.getElementById(`${prefix}_batch_sale_price`);
            this.batchStock = document.getElementById(`${prefix}_batch_stock`);
            this.batchSku = document.getElementById(`${prefix}_batch_sku`);

            this.hasGroup2 = false;
            this.existingVariantsData = {}; // key -> { price, sale_price, stock, sku, image_path, image_url, file }

            this.init();
        }

        init() {
            if (!this.toggleSwitch) return;

            // Toggle switch event
            this.toggleSwitch.addEventListener('change', () => {
                this.updateVisibility();
                if (this.toggleSwitch.checked && this.group1Container.children.length === 0) {
                    this.addGroup1Item('Đen');
                    this.addGroup1Item('Trắng');
                }
                this.renderMatrix();
            });

            // Group 1 change
            if (this.group1NameInput) {
                this.group1NameInput.addEventListener('input', () => {
                    if (this.thGroup1) this.thGroup1.textContent = this.group1NameInput.value.trim() || 'Nhóm 1';
                });
            }

            if (this.btnAddGroup1Val) {
                this.btnAddGroup1Val.addEventListener('click', () => {
                    this.addGroup1Item('');
                });
            }

            // Group 2 change
            if (this.btnShowGroup2) {
                this.btnShowGroup2.addEventListener('click', () => {
                    this.hasGroup2 = true;
                    this.group2Card.classList.remove('d-none');
                    this.addGroup2Wrap.classList.add('d-none');
                    if (this.group2Container.children.length === 0) {
                        this.addGroup2Item('S');
                        this.addGroup2Item('M');
                    }
                    this.renderMatrix();
                });
            }

            if (this.btnRemoveGroup2) {
                this.btnRemoveGroup2.addEventListener('click', () => {
                    this.hasGroup2 = false;
                    this.group2Card.classList.add('d-none');
                    this.addGroup2Wrap.classList.remove('d-none');
                    this.group2Container.innerHTML = '';
                    this.renderMatrix();
                });
            }

            if (this.group2NameInput) {
                this.group2NameInput.addEventListener('input', () => {
                    if (this.thGroup2) this.thGroup2.textContent = this.group2NameInput.value.trim() || 'Nhóm 2';
                });
            }

            if (this.btnAddGroup2Val) {
                this.btnAddGroup2Val.addEventListener('click', () => {
                    this.addGroup2Item('');
                });
            }

            // Batch apply event
            if (this.btnBatchApply) {
                this.btnBatchApply.addEventListener('click', () => {
                    this.applyBatchValues();
                });
            }
        }

        updateVisibility() {
            const isChecked = this.toggleSwitch.checked;
            if (isChecked) {
                this.configWrap.classList.remove('d-none');
                if (this.simplePriceRow) this.simplePriceRow.classList.add('d-none');
                if (this.priceInput) this.priceInput.removeAttribute('required');
                if (this.stockInput) this.stockInput.removeAttribute('required');
            } else {
                this.configWrap.classList.add('d-none');
                if (this.simplePriceRow) this.simplePriceRow.classList.remove('d-none');
                if (this.priceInput) this.priceInput.setAttribute('required', 'required');
                if (this.stockInput) this.stockInput.setAttribute('required', 'required');
            }
        }

        addGroup1Item(val = '', imagePath = '', imageUrl = '') {
            const itemId = `g1_${this.prefix}_${Date.now()}_${Math.random().toString(36).substr(2, 4)}`;
            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 group1-item-row';
            row.dataset.id = itemId;

            let imgPreviewHtml = '';
            if (imageUrl || imagePath) {
                const src = imageUrl || (imagePath.startsWith('http') ? imagePath : `/storage/${imagePath.replace(/^\//, '')}`);
                imgPreviewHtml = `<img src="${src}" class="g1-thumb-preview" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">`;
            } else {
                imgPreviewHtml = `<span class="g1-thumb-placeholder text-muted d-flex align-items-center justify-content-center border rounded" style="width:32px;height:32px;font-size:12px;background:#f8f9fa;"><i class="fa-solid fa-image"></i></span>`;
            }

            row.innerHTML = `
                <div class="position-relative d-inline-block g1-img-wrap" style="flex-shrink:0;">
                    <label class="m-0 cursor-pointer" title="Chọn ảnh cho phân loại này" style="cursor:pointer;">
                        ${imgPreviewHtml}
                        <input type="file" class="d-none g1-img-input" accept="image/*">
                    </label>
                </div>
                <div class="input-group input-group-sm flex-fill" style="max-width:320px;">
                    <input type="text" class="form-control g1-val-input" placeholder="Tên phân loại (vd: Đen, Trắng...)" value="${val}">
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm text-danger border-0 g1-btn-del" title="Xóa">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            // Lưu imagePath nếu có
            if (imagePath) {
                row.dataset.imagePath = imagePath;
            }

            const valInput = row.querySelector('.g1-val-input');
            const fileInput = row.querySelector('.g1-img-input');
            const btnDel = row.querySelector('.g1-btn-del');

            valInput.addEventListener('input', () => this.renderMatrix());

            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    row.imageFile = file;
                    const url = URL.createObjectURL(file);
                    const imgWrap = row.querySelector('.g1-img-wrap label');
                    imgWrap.innerHTML = `<img src="${url}" class="g1-thumb-preview" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;"><input type="file" class="d-none g1-img-input" accept="image/*">`;
                    // Re-bind
                    imgWrap.querySelector('.g1-img-input').addEventListener('change', (ev) => fileInput.dispatchEvent(new Event('change')));
                    this.renderMatrix();
                }
            });

            btnDel.addEventListener('click', () => {
                row.remove();
                this.renderMatrix();
            });

            this.group1Container.appendChild(row);
            if (val) this.renderMatrix();
        }

        addGroup2Item(val = '') {
            const itemId = `g2_${this.prefix}_${Date.now()}_${Math.random().toString(36).substr(2, 4)}`;
            const chip = document.createElement('div');
            chip.className = 'input-group input-group-sm group2-item-chip';
            chip.style.width = '140px';
            chip.dataset.id = itemId;

            chip.innerHTML = `
                <input type="text" class="form-control g2-val-input" placeholder="Size (vd: S, M)" value="${val}">
                <button class="btn btn-outline-secondary text-danger g2-btn-del" type="button"><i class="fa-solid fa-xmark"></i></button>
            `;

            const valInput = chip.querySelector('.g2-val-input');
            const btnDel = chip.querySelector('.g2-btn-del');

            valInput.addEventListener('input', () => this.renderMatrix());
            btnDel.addEventListener('click', () => {
                chip.remove();
                this.renderMatrix();
            });

            this.group2Container.appendChild(chip);
            if (val) this.renderMatrix();
        }

        getGroup1Values() {
            const items = [];
            this.group1Container.querySelectorAll('.group1-item-row').forEach(row => {
                const val = row.querySelector('.g1-val-input')?.value.trim();
                if (val) {
                    const imgEl = row.querySelector('.g1-thumb-preview');
                    const imgUrl = imgEl ? imgEl.src : '';
                    items.push({
                        id: row.dataset.id,
                        name: val,
                        imagePath: row.dataset.imagePath || '',
                        imageUrl: imgUrl,
                        imageFile: row.imageFile || null
                    });
                }
            });
            return items;
        }

        getGroup2Values() {
            if (!this.hasGroup2) return [];
            const items = [];
            this.group2Container.querySelectorAll('.group2-item-chip').forEach(chip => {
                const val = chip.querySelector('.g2-val-input')?.value.trim();
                if (val) {
                    items.push(val);
                }
            });
            return items;
        }

        // Lưu giá trị hiện tại của matrix trước khi re-render
        saveCurrentMatrixState() {
            this.tbody.querySelectorAll('.matrix-row').forEach(tr => {
                const key = tr.dataset.variantName;
                if (key) {
                    this.existingVariantsData[key] = {
                        price: tr.querySelector('.matrix-price')?.value || '',
                        sale_price: tr.querySelector('.matrix-sale-price')?.value || '',
                        stock: tr.querySelector('.matrix-stock')?.value || '',
                        sku: tr.querySelector('.matrix-sku')?.value || '',
                        imagePath: tr.dataset.imagePath || '',
                        imageUrl: tr.dataset.imageUrl || '',
                    };
                }
            });
        }

        renderMatrix() {
            this.saveCurrentMatrixState();
            const g1List = this.getGroup1Values();
            const g2List = this.getGroup2Values();

            if (this.thGroup1) {
                this.thGroup1.textContent = this.group1NameInput.value.trim() || 'Nhóm 1';
            }

            if (this.hasGroup2 && g2List.length > 0) {
                if (this.thGroup2) {
                    this.thGroup2.textContent = this.group2NameInput.value.trim() || 'Nhóm 2';
                    this.thGroup2.classList.remove('d-none');
                }
            } else {
                if (this.thGroup2) this.thGroup2.classList.add('d-none');
            }

            this.tbody.innerHTML = '';

            let rows = [];
            if (g1List.length === 0) {
                this.tbody.innerHTML = `<tr><td colspan="7" class="text-muted py-3">Vui lòng nhập ít nhất một giá trị phân loại ở trên.</td></tr>`;
                this.updateStats([]);
                return;
            }

            if (this.hasGroup2 && g2List.length > 0) {
                // Tích Descartes: Nhóm 1 x Nhóm 2
                g1List.forEach(g1 => {
                    g2List.forEach(g2 => {
                        const variantName = `${g1.name}, ${g2}`;
                        rows.push({
                            variantName: variantName,
                            g1Val: g1.name,
                            g2Val: g2,
                            imagePath: g1.imagePath,
                            imageUrl: g1.imageUrl,
                            imageFile: g1.imageFile
                        });
                    });
                });
            } else {
                // Chỉ có Nhóm 1
                g1List.forEach(g1 => {
                    rows.push({
                        variantName: g1.name,
                        g1Val: g1.name,
                        g2Val: '',
                        imagePath: g1.imagePath,
                        imageUrl: g1.imageUrl,
                        imageFile: g1.imageFile
                    });
                });
            }

            rows.forEach((r, idx) => {
                const saved = this.existingVariantsData[r.variantName] || {};
                const price = saved.price || '';
                const salePrice = saved.sale_price || '';
                const stock = saved.stock !== undefined ? saved.stock : '';
                const sku = saved.sku || '';

                const tr = document.createElement('tr');
                tr.className = 'matrix-row';
                tr.dataset.variantName = r.variantName;
                tr.dataset.index = idx;
                if (r.imagePath) tr.dataset.imagePath = r.imagePath;
                if (r.imageUrl) tr.dataset.imageUrl = r.imageUrl;
                tr.imageFile = r.imageFile || null;

                let imgHtml = '<span class="text-muted" style="font-size:12px;">--</span>';
                if (r.imageUrl) {
                    imgHtml = `<img src="${r.imageUrl}" style="width:30px;height:30px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">`;
                }

                tr.innerHTML = `
                    <td>${imgHtml}</td>
                    <td class="fw-semibold text-start">${r.g1Val}</td>
                    ${this.hasGroup2 && g2List.length > 0 ? `<td class="text-start">${r.g2Val}</td>` : ''}
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control matrix-price text-end" value="${price}" placeholder="0" min="0" step="1000" required>
                            <span class="input-group-text">₫</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control matrix-sale-price text-end" value="${salePrice}" placeholder="Giá sale" min="0" step="1000">
                            <span class="input-group-text">₫</span>
                        </div>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm matrix-stock text-center" value="${stock}" placeholder="0" min="0" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm matrix-sku" value="${sku}" placeholder="Mã SKU">
                    </td>
                `;

                // Event listener on inputs to update stats in real time
                tr.querySelectorAll('input').forEach(inp => {
                    inp.addEventListener('input', () => this.updateStats(rows));
                });

                this.tbody.appendChild(tr);
            });

            this.updateStats(rows);
        }

        applyBatchValues() {
            const bPrice = this.batchPrice ? this.batchPrice.value : '';
            const bSalePrice = this.batchSalePrice ? this.batchSalePrice.value : '';
            const bStock = this.batchStock ? this.batchStock.value : '';
            const bSku = this.batchSku ? this.batchSku.value.trim() : '';

            this.tbody.querySelectorAll('.matrix-row').forEach((tr, i) => {
                if (bPrice !== '') tr.querySelector('.matrix-price').value = bPrice;
                if (bSalePrice !== '') tr.querySelector('.matrix-sale-price').value = bSalePrice;
                if (bStock !== '') tr.querySelector('.matrix-stock').value = bStock;
                if (bSku !== '') tr.querySelector('.matrix-sku').value = `${bSku}-${i + 1}`;
            });

            this.renderMatrix();
        }

        updateStats(rows) {
            let totalStock = 0;
            let prices = [];

            this.tbody.querySelectorAll('.matrix-row').forEach(tr => {
                const stock = parseInt(tr.querySelector('.matrix-stock')?.value) || 0;
                const price = parseFloat(tr.querySelector('.matrix-price')?.value) || 0;
                const salePrice = parseFloat(tr.querySelector('.matrix-sale-price')?.value) || 0;

                totalStock += stock;
                if (salePrice > 0 && salePrice < price) {
                    prices.push(salePrice);
                } else if (price > 0) {
                    prices.push(price);
                }
            });

            if (this.variantsCountEl) this.variantsCountEl.textContent = rows ? rows.length : 0;
            if (this.totalStockEl) this.totalStockEl.textContent = new Intl.NumberFormat('vi-VN').format(totalStock);

            if (this.priceRangeEl) {
                if (prices.length > 0) {
                    const min = Math.min(...prices);
                    const max = Math.max(...prices);
                    if (min === max) {
                        this.priceRangeEl.textContent = `${new Intl.NumberFormat('vi-VN').format(min)}₫`;
                    } else {
                        this.priceRangeEl.textContent = `${new Intl.NumberFormat('vi-VN').format(min)}₫ - ${new Intl.NumberFormat('vi-VN').format(max)}₫`;
                    }
                } else {
                    this.priceRangeEl.textContent = '0₫';
                }
            }
        }

        getPayload() {
            const isChecked = this.toggleSwitch && this.toggleSwitch.checked;
            if (!isChecked) {
                return { has_variants: false, attributes: null, variants: [] };
            }

            const g1Name = this.group1NameInput.value.trim() || 'Màu sắc';
            const g1Items = this.getGroup1Values();
            const g2Name = this.group2NameInput.value.trim() || 'Kích cỡ';
            const g2Items = this.getGroup2Values();

            const attributes = [
                { name: g1Name, options: g1Items.map(x => x.name) }
            ];
            if (this.hasGroup2 && g2Items.length > 0) {
                attributes.push({ name: g2Name, options: g2Items });
            }

            const variants = [];
            const variantFiles = {};

            this.tbody.querySelectorAll('.matrix-row').forEach((tr, idx) => {
                const name = tr.dataset.variantName;
                const price = parseFloat(tr.querySelector('.matrix-price')?.value) || 0;
                const salePriceVal = tr.querySelector('.matrix-sale-price')?.value;
                const salePrice = salePriceVal !== '' && !isNaN(salePriceVal) ? parseFloat(salePriceVal) : null;
                const stock = parseInt(tr.querySelector('.matrix-stock')?.value) || 0;
                const sku = tr.querySelector('.matrix-sku')?.value.trim() || '';
                const imagePath = tr.dataset.imagePath || '';

                variants.push({
                    name: name,
                    sku: sku,
                    price: price,
                    sale_price: salePrice,
                    stock: stock,
                    image_path: imagePath
                });

                if (tr.imageFile) {
                    variantFiles[`variant_image_${idx}`] = tr.imageFile;
                }
            });

            return {
                has_variants: true,
                attributes: attributes,
                variants: variants,
                variantFiles: variantFiles
            };
        }

        loadData(hasVariants, attributes, variants) {
            this.existingVariantsData = {};
            this.group1Container.innerHTML = '';
            this.group2Container.innerHTML = '';

            if (!hasVariants || !variants || variants.length === 0) {
                this.toggleSwitch.checked = false;
                this.updateVisibility();
                return;
            }

            this.toggleSwitch.checked = true;
            this.updateVisibility();

            // Populate attributes
            if (attributes && Array.isArray(attributes) && attributes.length > 0) {
                const g1 = attributes[0];
                if (g1) {
                    this.group1NameInput.value = g1.name || 'Màu sắc';
                    const g1Opts = Array.isArray(g1.options) ? g1.options : [];
                    g1Opts.forEach(optName => {
                        // Tìm variant tương ứng để lấy ảnh nếu có
                        const matchedVar = variants.find(v => v.name.startsWith(optName));
                        const imgPath = matchedVar?.image_path || '';
                        this.addGroup1Item(optName, imgPath);
                    });
                }

                if (attributes.length > 1) {
                    const g2 = attributes[1];
                    this.hasGroup2 = true;
                    this.group2Card.classList.remove('d-none');
                    this.addGroup2Wrap.classList.add('d-none');
                    this.group2NameInput.value = g2.name || 'Kích cỡ';
                    const g2Opts = Array.isArray(g2.options) ? g2.options : [];
                    g2Opts.forEach(optName => {
                        this.addGroup2Item(optName);
                    });
                } else {
                    this.hasGroup2 = false;
                    this.group2Card.classList.add('d-none');
                    this.addGroup2Wrap.classList.remove('d-none');
                }
            } else {
                // Fallback nếu không có attributes rõ ràng: load từ variants
                variants.forEach(v => {
                    this.addGroup1Item(v.name, v.image_path);
                });
            }

            // Populate variant rows data
            variants.forEach(v => {
                this.existingVariantsData[v.name] = {
                    price: v.price || '',
                    sale_price: v.sale_price || '',
                    stock: v.stock !== undefined ? v.stock : '',
                    sku: v.sku || '',
                    imagePath: v.image_path || ''
                };
            });

            this.renderMatrix();
        }
    }

    // Khởi tạo Variant Managers cho cả Add Modal & Edit Modal
    const addVariantMgr = new ProductVariantManager('add');
    const editVariantMgr = new ProductVariantManager('edit');

    // 6. Xử lý mở Modal Chỉnh sửa sản phẩm và điền thông tin
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
            const hasVariants = this.dataset.has_variants === '1';

            let attributes = [];
            let variants = [];
            try {
                attributes = JSON.parse(this.dataset.attributes || '[]');
            } catch (e) { attributes = []; }
            try {
                variants = JSON.parse(this.dataset.variants || '[]');
            } catch (e) { variants = []; }

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

                // Load dữ liệu biến thể vào Edit Variant Manager
                editVariantMgr.loadData(hasVariants, attributes, variants);
            }
        });
    });

    // 7. Xử lý submit AJAX cho Form Thêm sản phẩm
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
            const variantPayload = addVariantMgr.getPayload();

            formData.set('has_variants', variantPayload.has_variants ? '1' : '0');
            if (variantPayload.has_variants) {
                formData.set('attributes', JSON.stringify(variantPayload.attributes));
                formData.set('variants', JSON.stringify(variantPayload.variants));
                if (variantPayload.variantFiles) {
                    for (const [key, file] of Object.entries(variantPayload.variantFiles)) {
                        formData.append(key, file);
                    }
                }
            }

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

    // 8. Xử lý submit AJAX cho Form Sửa sản phẩm
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
            const variantPayload = editVariantMgr.getPayload();

            formData.set('has_variants', variantPayload.has_variants ? '1' : '0');
            if (variantPayload.has_variants) {
                formData.set('attributes', JSON.stringify(variantPayload.attributes));
                formData.set('variants', JSON.stringify(variantPayload.variants));
                if (variantPayload.variantFiles) {
                    for (const [key, file] of Object.entries(variantPayload.variantFiles)) {
                        formData.append(key, file);
                    }
                }
            } else {
                formData.set('attributes', '');
                formData.set('variants', '');
            }

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

    // 9. Xử lý xóa sản phẩm
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

    // =========================================================================
    // 10. FLASH SALE REGISTRATION LOGIC FOR SELLER
    // =========================================================================
    const fsForms = document.querySelectorAll('.seller-flash-sale-form');

    fsForms.forEach(form => {
        const productSelect = form.querySelector('.fs-select-product');
        const discountPctInput = form.querySelector('.fs-discount-percent');
        const hiddenPriceInput = form.querySelector('.fs-proposed-price');
        const qtyInput = form.querySelector('.fs-proposed-quantity');
        const priceHint = form.querySelector('.fs-price-hint');
        const stockHint = form.querySelector('.fs-stock-hint');
        const submitBtn = form.querySelector('.btn-submit-fs');
        const quickPctBtns = form.querySelectorAll('.btn-quick-pct');

        function updateHints() {
            const selectedOpt = productSelect.options[productSelect.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) {
                if (priceHint) {
                    priceHint.textContent = 'Tối thiểu giảm 10%';
                    priceHint.className = 'fs-price-hint text-muted small mt-1';
                }
                if (stockHint) stockHint.textContent = '';
                if (hiddenPriceInput) hiddenPriceInput.value = '';
                return;
            }

            const originPrice = parseFloat(selectedOpt.dataset.price) || 0;
            const stock = parseInt(selectedOpt.dataset.stock) || 0;

            const hasVars = selectedOpt.dataset.hasVariants === '1';
            const varCount = parseInt(selectedOpt.dataset.varCount) || 0;
            const minPrice = parseFloat(selectedOpt.dataset.minPrice) || originPrice;
            const maxPrice = parseFloat(selectedOpt.dataset.maxPrice) || originPrice;

            if (stockHint) {
                stockHint.textContent = `Tổng tồn kho: ${stock}`;
            }
            if (stock > 0 && qtyInput) {
                qtyInput.max = stock;
            }

            const pctVal = parseFloat(discountPctInput.value);

            if (isNaN(pctVal) || pctVal <= 0) {
                if (priceHint) {
                    if (hasVars && minPrice !== maxPrice) {
                        priceHint.textContent = `Tối thiểu giảm 10% (Giá gốc: ${new Intl.NumberFormat('vi-VN').format(minPrice)}₫ - ${new Intl.NumberFormat('vi-VN').format(maxPrice)}₫)`;
                    } else {
                        priceHint.textContent = `Tối thiểu giảm 10% (Giá gốc: ${new Intl.NumberFormat('vi-VN').format(originPrice)}₫)`;
                    }
                    priceHint.className = 'fs-price-hint text-muted small mt-1';
                }
                if (hiddenPriceInput) hiddenPriceInput.value = '';
                return;
            }

            if (pctVal < 10) {
                if (priceHint) {
                    priceHint.textContent = `Mức giảm phải từ 10% trở lên theo quy định!`;
                    priceHint.className = 'fs-price-hint text-danger small mt-1 fw-semibold';
                }
                if (hiddenPriceInput) hiddenPriceInput.value = '';
            } else if (pctVal > 90) {
                if (priceHint) {
                    priceHint.textContent = `Mức giảm tối đa là 90%!`;
                    priceHint.className = 'fs-price-hint text-danger small mt-1 fw-semibold';
                }
                if (hiddenPriceInput) hiddenPriceInput.value = '';
            } else {
                // Tính giá Flash Sale cho biến thể rẻ nhất và đắt nhất
                const minCalculatedPrice = Math.round((minPrice * (100 - pctVal) / 100) / 1000) * 1000;
                const maxCalculatedPrice = Math.round((maxPrice * (100 - pctVal) / 100) / 1000) * 1000;
                const savedAmount = minPrice - minCalculatedPrice;

                // Lưu giá của biến thể rẻ nhất vào hidden input để gửi backend
                if (hiddenPriceInput) {
                    hiddenPriceInput.value = minCalculatedPrice;
                }

                if (priceHint) {
                    if (hasVars && minPrice !== maxPrice) {
                        priceHint.innerHTML = `<span class="text-success fw-bold"><i class="fa-solid fa-layer-group me-1"></i>Áp dụng -${pctVal}% cho toàn bộ ${varCount} biến thể:</span><br><span class="text-dark">Giá biến thể rẻ nhất: <strong class="text-danger">${new Intl.NumberFormat('vi-VN').format(minCalculatedPrice)}₫</strong> (Khoảng giá: ${new Intl.NumberFormat('vi-VN').format(minCalculatedPrice)}₫ - ${new Intl.NumberFormat('vi-VN').format(maxCalculatedPrice)}₫)</span>`;
                    } else {
                        priceHint.innerHTML = `<span class="text-success fw-bold"><i class="fa-solid fa-check-circle me-1"></i>Giá Flash Sale: ${new Intl.NumberFormat('vi-VN').format(minCalculatedPrice)}₫</span> <span class="text-muted">(Tiết kiệm ${new Intl.NumberFormat('vi-VN').format(savedAmount)}₫)</span>`;
                    }
                    priceHint.className = 'fs-price-hint small mt-1';
                }
            }
        }

        if (productSelect) {
            productSelect.addEventListener('change', updateHints);
        }
        if (discountPctInput) {
            discountPctInput.addEventListener('input', updateHints);
        }

        // Nút chọn nhanh % (10%, 20%, 30%, 50%)
        quickPctBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const pct = this.dataset.pct;
                if (discountPctInput) {
                    discountPctInput.value = pct;
                    updateHints();
                }
            });
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const selectedOpt = productSelect.options[productSelect.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) {
                alert('Vui lòng chọn sản phẩm muốn đăng ký!');
                return;
            }

            const originPrice = parseFloat(selectedOpt.dataset.price) || 0;
            const pctVal = parseFloat(discountPctInput.value);
            const proposedPrice = parseFloat(hiddenPriceInput.value) || 0;
            const proposedQty = parseInt(qtyInput.value) || 0;
            const stock = parseInt(selectedOpt.dataset.stock) || 0;

            if (isNaN(pctVal) || pctVal < 10 || pctVal > 90) {
                alert('Mức giảm giá phải từ 10% đến 90% theo quy định Flash Sale!');
                discountPctInput.focus();
                return;
            }
            if (proposedPrice <= 0) {
                alert('Giá đề xuất không hợp lệ! Vui lòng kiểm tra lại phần trăm giảm.');
                return;
            }
            if (proposedQty <= 0) {
                alert('Số lượng đăng ký phải lớn hơn 0!');
                qtyInput.focus();
                return;
            }
            if (proposedQty > stock) {
                alert(`Số lượng đăng ký (${proposedQty}) không được vượt tồn kho khả dụng (${stock})!`);
                qtyInput.focus();
                return;
            }

            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang gửi...';

            const formData = new FormData(form);
            // Đảm bảo proposed_price luôn có trong formData
            formData.set('proposed_price', proposedPrice);

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch('/seller/flash-sale-registrations', {
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
                        let errMsg = data.message || 'Có lỗi xảy ra khi gửi đăng ký.';
                        if (data.errors) {
                            errMsg = Object.values(data.errors).flat().join('\n');
                        }
                        throw new Error(errMsg);
                    }
                    return data;
                })
                .then((data) => {
                    alert(data.message || 'Đăng ký sản phẩm vào Flash Sale thành công! Đang chờ Admin duyệt.');
                    window.location.hash = '#dashFlashSale';
                    window.location.reload();
                })
                .catch((err) => {
                    alert(err.message);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                });
        });
    });

    // Xử lý Hủy đăng ký Flash Sale
    const cancelFsButtons = document.querySelectorAll('.btn-cancel-fs-reg');
    cancelFsButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.dataset.url;
            const regId = this.dataset.id;

            if (!confirm('Bạn có chắc chắn muốn hủy đăng ký sản phẩm này khỏi phiên Flash Sale không?')) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            this.disabled = true;

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Không thể hủy đăng ký.');
                    }
                    return data;
                })
                .then((data) => {
                    alert(data.message || 'Đã hủy đăng ký thành công!');
                    const row = document.getElementById(`fs-reg-row-${regId}`);
                    if (row) {
                        row.remove();
                    } else {
                        window.location.hash = '#dashFlashSale';
                        window.location.reload();
                    }
                })
                .catch((err) => {
                    alert(err.message);
                    this.disabled = false;
                });
        });
    });
});