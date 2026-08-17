/**
 * flash-sale-registrations.js (Seller side)
 *
 * Xu ly:
 * 1. Khi Seller chon san pham -> tu tinh gia toi da (90%) va hien thi goi y
 * 2. Submit form dang ky qua Ajax
 * 3. Huy dang ky (DELETE)
 *
 * Data tu backend truyen vao qua data-* attributes (Rule 24 AGENT.md)
 */

(function () {
    'use strict';

    const app = document.getElementById('sellerFlashSaleApp');
    if (!app) return;

    const storeUrl = app.dataset.storeUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ----------- Cap nhat goi y gia khi chon san pham -----------

    document.addEventListener('change', function (e) {
        const select = e.target.closest('.product-select');
        if (!select) return;

        const option = select.options[select.selectedIndex];
        const price = parseFloat(option.dataset.price || 0);
        const stock = parseInt(option.dataset.stock || 0);

        const form = select.closest('.registration-form');
        const priceInput = form.querySelector('.proposed-price');
        const priceHint = form.querySelector('.proposed-price-hint');
        const qtyInput = form.querySelector('.proposed-quantity');

        if (price > 0) {
            const maxPrice = Math.floor(price * 0.9);
            priceHint.textContent = 'Gia toi da duoc phep: ' + maxPrice.toLocaleString('vi-VN') + 'd (90% gia goc ' + price.toLocaleString('vi-VN') + 'd)';
            priceInput.max = maxPrice;
        } else {
            priceHint.textContent = '';
            priceInput.removeAttribute('max');
        }

        if (stock > 0) {
            qtyInput.max = stock;
        } else {
            qtyInput.removeAttribute('max');
        }
    });

    // ----------- Submit form dang ky -----------

    document.addEventListener('submit', function (e) {
        const form = e.target.closest('.registration-form');
        if (!form) return;
        e.preventDefault();

        clearFormErrors(form);

        const data = {
            flash_sale_id: form.querySelector('[name="flash_sale_id"]').value,
            product_id: form.querySelector('[name="product_id"]').value,
            proposed_price: form.querySelector('[name="proposed_price"]').value,
            proposed_quantity: form.querySelector('[name="proposed_quantity"]').value,
        };

        const submitBtn = form.querySelector('[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Dang gui...';

        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(data),
        })
            .then(async r => {
                const json = await r.json();
                if (r.status === 422) {
                    showFormErrors(form, json.errors || {});
                    return null;
                }
                if (!r.ok || !json.success) throw json.message || 'Co loi xay ra.';
                return json;
            })
            .then(json => {
                if (!json) return;
                showToast(json.message, 'success');
                form.reset();
                form.closest('.card').querySelector('.proposed-price-hint').textContent = '';
            })
            .catch(msg => showToast(String(msg), 'danger'))
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Gui Dang Ky';
            });
    });

    // ----------- Huy dang ky -----------

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-cancel-registration');
        if (!btn) return;

        if (!confirm('Ban co chac chan muon huy dang ky nay khong?')) return;

        const destroyUrl = btn.dataset.destroyUrl;
        const row = btn.closest('tr');

        fetch(destroyUrl, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
        })
            .then(async r => {
                const json = await r.json();
                if (!r.ok || !json.success) throw json.message || 'Co loi xay ra.';
                return json;
            })
            .then(json => {
                showToast(json.message, 'success');
                if (row) row.remove();
            })
            .catch(msg => showToast(String(msg), 'danger'));
    });

    // ----------- Helpers -----------

    function clearFormErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => (el.textContent = ''));
    }

    function showFormErrors(form, errors) {
        const fieldMap = {
            product_id: { input: '.product-select', feedback: '.product-id-error' },
            proposed_price: { input: '.proposed-price', feedback: '.proposed-price-error' },
            proposed_quantity: { input: '.proposed-quantity', feedback: '.proposed-quantity-error' },
        };

        Object.entries(errors).forEach(([field, messages]) => {
            const map = fieldMap[field];
            if (!map) return;
            const input = form.querySelector(map.input);
            const feedback = form.querySelector(map.feedback);
            if (input) input.classList.add('is-invalid');
            if (feedback) feedback.textContent = Array.isArray(messages) ? messages[0] : messages;
        });
    }

    function showToast(message, type = 'success') {
        if (window.showSellerToast) {
            window.showSellerToast(message, type);
        } else {
            alert(message);
        }
    }

})();
