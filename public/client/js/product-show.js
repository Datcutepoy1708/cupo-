/* ==============================================
   CUPO CLIENT — Product Detail Page JS
   File: public/client/js/product-show.js
   ============================================== */

document.addEventListener('DOMContentLoaded', function () {
    // Global CSRF Token
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    // 1. Change Main Image on Thumbnail Click
    window.changeMainImg = function (src, el) {
        const mainImg = document.getElementById('mainProductImg');
        if (mainImg && src) {
            mainImg.src = src;
        }

        let targetEl = el;
        if (!targetEl && src) {
            targetEl = document.querySelector(`.prod-thumb-item[data-img-src="${src}"]`);
        }

        document.querySelectorAll('.prod-thumb-item').forEach(function (item) {
            item.classList.remove('active');
        });
        if (targetEl) {
            targetEl.classList.add('active');
            targetEl.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });

            // Nếu thumbnail này thuộc về 1 biến thể cụ thể và người dùng trực tiếp click vào thumbnail
            if (el && targetEl.dataset.variantName) {
                const varName = targetEl.dataset.variantName;
                const firstPart = varName.split(',')[0].trim();
                const matchedOptionBtn = document.querySelector(`.btn-variant-option[data-group-index="0"][data-value="${firstPart}"]`);
                if (matchedOptionBtn && !matchedOptionBtn.classList.contains('active')) {
                    matchedOptionBtn.click();
                }
            }
        }
    };

    // =========================================================================
    // 2. SHOPEE & TIKTOK SHOP STYLE VARIANT SELECTOR
    // =========================================================================
    const variantsSection = document.getElementById('productVariantsSection');
    const hasVariants = variantsSection && variantsSection.dataset.hasVariants === 'true';
    const totalGroupsCount = variantsSection ? parseInt(variantsSection.dataset.groupsCount || '1') : 0;
    
    let variantsData = [];
    if (hasVariants) {
        try {
            variantsData = JSON.parse(variantsSection.dataset.variants || '[]');
        } catch (e) {
            variantsData = [];
        }
    }

    const selectedOptionsByGroup = {}; // groupIndex -> selectedValue
    let activeMatchedVariant = null;

    // Elements
    const prodCurrentPrice = document.getElementById('prodCurrentPrice');
    const prodOriginalPrice = document.getElementById('prodOriginalPrice');
    const prodDiscountBadge = document.getElementById('prodDiscountBadge');
    const stockDisplay = document.getElementById('stockDisplay');
    const buyQuantityInput = document.getElementById('buyQuantity');
    const summaryWrap = document.getElementById('selectedVariantSummaryWrap');
    const summaryTitle = document.getElementById('selectedVariantFullTitle');
    const btnAddToCart = document.getElementById('btnAddToCart');
    const btnBuyNow = document.getElementById('btnBuyNow');

    // Lưu lại giá và kho mặc định ban đầu
    const defaultCurrentPriceText = prodCurrentPrice ? prodCurrentPrice.textContent : '';
    const defaultStockText = stockDisplay ? stockDisplay.textContent : '0';

    window.onSelectVariantOption = function (btn, groupIndex, val) {
        const row = btn.closest('.variant-group-row');
        const hint = document.getElementById(`variant_hint_${groupIndex}`);
        
        // Nếu click lại nút đang active -> Bỏ chọn (Deselect)
        if (btn.classList.contains('active')) {
            btn.classList.remove('active');
            delete selectedOptionsByGroup[groupIndex];
            if (hint) hint.textContent = '';
        } else {
            // Active nút mới
            if (row) {
                row.querySelectorAll('.btn-variant-option').forEach(b => b.classList.remove('active'));
            }
            btn.classList.add('active');
            selectedOptionsByGroup[groupIndex] = val;
            if (hint) hint.textContent = `(${val})`;
        }

        // Xóa hiệu ứng rung nếu có
        if (variantsSection) {
            variantsSection.classList.remove('variant-shake-warning');
        }

        updateVariantState();
    };

    function updateVariantState() {
        const selectedGroupCount = Object.keys(selectedOptionsByGroup).length;

        // Nếu đã chọn đủ tất cả các nhóm phân loại
        if (selectedGroupCount === totalGroupsCount && totalGroupsCount > 0) {
            // Tìm variant tương ứng
            // Tên variant có dạng: "Màu, Size" hoặc "Màu"
            const selectedVals = [];
            for (let i = 0; i < totalGroupsCount; i++) {
                if (selectedOptionsByGroup[i]) selectedVals.push(selectedOptionsByGroup[i]);
            }

            const targetName1 = selectedVals.join(', ');
            const targetName2 = selectedVals.join(' - ');

            activeMatchedVariant = variantsData.find(v => {
                const vName = (v.name || '').trim();
                return vName === targetName1 || vName === targetName2 || 
                    selectedVals.every(val => vName.includes(val));
            });

            if (activeMatchedVariant) {
                // Cập nhật giá bán
                const price = parseFloat(activeMatchedVariant.price) || 0;
                const salePrice = parseFloat(activeMatchedVariant.sale_price) || 0;
                const hasSale = salePrice > 0 && salePrice < price;

                if (hasSale) {
                    const discount = Math.round(((price - salePrice) / price) * 100);
                    if (prodOriginalPrice) {
                        prodOriginalPrice.textContent = `${new Intl.NumberFormat('vi-VN').format(price)} ₫`;
                        prodOriginalPrice.classList.remove('d-none');
                    }
                    if (prodCurrentPrice) {
                        prodCurrentPrice.textContent = `${new Intl.NumberFormat('vi-VN').format(salePrice)} ₫`;
                    }
                    if (prodDiscountBadge) {
                        prodDiscountBadge.textContent = `-${discount}%`;
                        prodDiscountBadge.classList.remove('d-none');
                    }
                } else {
                    if (prodOriginalPrice) prodOriginalPrice.classList.add('d-none');
                    if (prodDiscountBadge) prodDiscountBadge.classList.add('d-none');
                    if (prodCurrentPrice) {
                        prodCurrentPrice.textContent = `${new Intl.NumberFormat('vi-VN').format(price)} ₫`;
                    }
                }

                // Cập nhật số lượng kho
                const stock = parseInt(activeMatchedVariant.stock) || 0;
                if (stockDisplay) {
                    stockDisplay.textContent = new Intl.NumberFormat('vi-VN').format(stock);
                }
                if (buyQuantityInput) {
                    buyQuantityInput.max = stock;
                    if (parseInt(buyQuantityInput.value) > stock) {
                        buyQuantityInput.value = Math.max(1, stock);
                    }
                }

                // Chuyển ảnh chính lớn nếu biến thể có ảnh
                if (activeMatchedVariant.image_url) {
                    window.changeMainImg(activeMatchedVariant.image_url, null);
                }

                // Cập nhật thanh tóm tắt
                if (summaryWrap && summaryTitle) {
                    summaryTitle.textContent = activeMatchedVariant.name;
                    summaryWrap.classList.remove('d-none');
                }

                // Xử lý hết hàng
                if (stock <= 0) {
                    if (btnAddToCart) {
                        btnAddToCart.disabled = true;
                        btnAddToCart.innerHTML = '<i class="fa-solid fa-ban me-2"></i>Hết Hàng';
                    }
                    if (btnBuyNow) {
                        btnBuyNow.disabled = true;
                    }
                } else {
                    if (btnAddToCart) {
                        btnAddToCart.disabled = false;
                        btnAddToCart.innerHTML = '<i class="fa-solid fa-cart-plus me-2"></i>Thêm Vào Giỏ Hàng';
                    }
                    if (btnBuyNow) {
                        btnBuyNow.disabled = false;
                    }
                }
            }
        } else {
            // Chưa chọn đủ các nhóm phân loại -> Trả về trạng thái mặc định
            activeMatchedVariant = null;
            if (prodCurrentPrice) prodCurrentPrice.textContent = defaultCurrentPriceText;
            if (prodOriginalPrice) prodOriginalPrice.classList.add('d-none');
            if (prodDiscountBadge) prodDiscountBadge.classList.add('d-none');
            if (stockDisplay) stockDisplay.textContent = defaultStockText;
            if (summaryWrap) summaryWrap.classList.add('d-none');
            
            if (btnAddToCart) {
                btnAddToCart.disabled = false;
                btnAddToCart.innerHTML = '<i class="fa-solid fa-cart-plus me-2"></i>Thêm Vào Giỏ Hàng';
            }
            if (btnBuyNow) {
                btnBuyNow.disabled = false;
            }

            // Nếu chỉ chọn nhóm 1 và có ảnh -> chuyển ảnh xem trước
            if (selectedOptionsByGroup[0]) {
                const optVal = selectedOptionsByGroup[0];
                const matchedVarWithImg = variantsData.find(v => v.name.includes(optVal) && v.image_url);
                if (matchedVarWithImg && matchedVarWithImg.image_url) {
                    window.changeMainImg(matchedVarWithImg.image_url, null);
                }
            }
        }
    }

    // 3. Adjust Quantity (- / +)
    window.adjustQty = function (delta) {
        const input = document.getElementById('buyQuantity');
        if (!input) return;

        let current = parseInt(input.value) || 1;
        const max = parseInt(input.max) || 999;
        current += delta;

        if (current < 1) current = 1;
        if (current > max) current = max;
        input.value = current;
    };

    // 4. AJAX Toggle Product Like (Đã Thích)
    window.toggleProductLike = function (productId) {
        const btn = document.getElementById('btnLikeProduct');
        const countEl = document.getElementById('likesCountNum');

        fetch(`/products/${productId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.status === 'success') {
                if (countEl) {
                    countEl.innerText = new Intl.NumberFormat('vi-VN').format(data.likes_count);
                }
                if (btn) {
                    if (data.liked) {
                        btn.classList.add('liked');
                    } else {
                        btn.classList.remove('liked');
                    }
                }
            }
        })
        .catch(function (err) {
            console.error('Error toggling like:', err);
        });
    };

    // 5. AJAX Add to Cart & Buy Now
    window.addToCart = function (productId, isBuyNow) {
        const detailContainer = document.getElementById('productDetailContainer');
        const isGuest = detailContainer ? detailContainer.getAttribute('data-is-guest') === 'true' : false;

        if (isGuest) {
            window.location.href = '/login';
            return;
        }

        // BẮT LỖI NẾU SẢN PHẨM CÓ BIẾN THỂ NHƯNG CHƯA CHỌN ĐỦ PHÂN LOẠI
        if (hasVariants && totalGroupsCount > 0) {
            const selectedCount = Object.keys(selectedOptionsByGroup).length;
            if (selectedCount < totalGroupsCount || !activeMatchedVariant) {
                if (variantsSection) {
                    variantsSection.classList.remove('variant-shake-warning');
                    void variantsSection.offsetWidth; // Trigger reflow
                    variantsSection.classList.add('variant-shake-warning');
                    variantsSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                alert('⚠️ Vui lòng chọn đầy đủ Phân loại hàng (Màu sắc, kích cỡ...) trước khi mua!');
                return;
            }
        }

        const qtyInput = document.getElementById('buyQuantity');
        const quantity = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;
        const variantId = activeMatchedVariant ? activeMatchedVariant.id : null;

        const payload = {
            product_id: productId,
            quantity: quantity,
            product_variant_id: variantId
        };

        const cartStoreUrl = detailContainer ? detailContainer.getAttribute('data-cart-url') : '/cart/add';
        const cartIndexUrl = detailContainer ? detailContainer.getAttribute('data-cart-index-url') : '/cart';

        fetch(cartStoreUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.total_items !== undefined) {
                // Update Header Cart Badge Counter
                const badge = document.getElementById('header-cart-badge');
                if (badge) {
                    badge.innerText = data.total_items;
                    badge.classList.remove('d-none');
                }

                if (isBuyNow) {
                    window.location.href = cartIndexUrl;
                } else {
                    alert('✓ Đã thêm sản phẩm vào giỏ hàng thành công!');
                }
            } else {
                alert(data.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng.');
            }
        })
        .catch(function (err) {
            console.error('Error adding to cart:', err);
            alert('Không thể kết nối đến máy chủ.');
        });
    };
});

