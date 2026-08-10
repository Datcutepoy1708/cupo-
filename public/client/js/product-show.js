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
        if (mainImg) {
            mainImg.src = src;
        }
        document.querySelectorAll('.prod-thumb-item').forEach(function (item) {
            item.classList.remove('active');
        });
        if (el) {
            el.classList.add('active');
        }
    };

    // 2. Select Product Variant Option
    window.selectVariant = function (el) {
        document.querySelectorAll('.btn-variant').forEach(function (btn) {
            btn.classList.remove('active');
        });
        el.classList.add('active');

        const price = el.getAttribute('data-price');
        const stock = el.getAttribute('data-stock');

        if (price) {
            const priceEl = document.querySelector('.prod-current-price');
            if (priceEl) {
                priceEl.innerText = new Intl.NumberFormat('vi-VN').format(price) + ' ₫';
            }
        }
        if (stock) {
            const stockDisplay = document.getElementById('stockDisplay');
            const buyQty = document.getElementById('buyQuantity');
            if (stockDisplay) stockDisplay.innerText = stock;
            if (buyQty) buyQty.max = stock;
        }
    };

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

        const qtyInput = document.getElementById('buyQuantity');
        const quantity = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;
        const activeVariant = document.querySelector('.btn-variant.active');
        const variantId = activeVariant ? activeVariant.getAttribute('data-variant-id') : null;

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
