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

    // ----------- Cap nhat goi y gia khi chon san pham hoac nhap % -----------

    function updatePriceFromPercent(form) {
        const select = form.querySelector('.product-select');
        const option = select ? select.options[select.selectedIndex] : null;
        const price = parseFloat(option?.dataset.price || 0);
        const stock = parseInt(option?.dataset.stock || 0);

        const pctInput = form.querySelector('.proposed-percent');
        const hiddenPrice = form.querySelector('.proposed-price');
        const priceHint = form.querySelector('.proposed-price-hint');
        const qtyInput = form.querySelector('.proposed-quantity');

        if (stock > 0 && qtyInput) {
            qtyInput.max = stock;
        }

        if (price <= 0 || !pctInput) return;

        const pct = parseFloat(pctInput.value);
        if (isNaN(pct) || pct <= 0) {
            priceHint.textContent = 'Toi thieu giam 10% (Gia goc: ' + price.toLocaleString('vi-VN') + 'd)';
            if (hiddenPrice) hiddenPrice.value = '';
            return;
        }

        if (pct < 10) {
            priceHint.textContent = 'Muc giam phai tu 10% tro len theo quy dinh!';
            if (hiddenPrice) hiddenPrice.value = '';
        } else if (pct > 90) {
            priceHint.textContent = 'Muc giam toi da la 90%!';
            if (hiddenPrice) hiddenPrice.value = '';
        } else {
            const calculatedPrice = Math.round((price * (100 - pct) / 100) / 1000) * 1000;
            const savedAmount = price - calculatedPrice;
            if (hiddenPrice) hiddenPrice.value = calculatedPrice;
            priceHint.textContent = 'Gia Flash Sale: ' + calculatedPrice.toLocaleString('vi-VN') + 'd (Tiet kiem ' + savedAmount.toLocaleString('vi-VN') + 'd)';
        }
    }

    document.addEventListener('change', function (e) {
        const select = e.target.closest('.product-select');
        if (select) {
            updatePriceFromPercent(select.closest('.registration-form'));
        }
    });

    document.addEventListener('input', function (e) {
        const pctInput = e.target.closest('.proposed-percent');
        if (pctInput) {
            updatePriceFromPercent(pctInput.closest('.registration-form'));
        }
    });

    // Nút chọn nhanh % (10%, 20%, 30%, 50%)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-quick-pct');
        if (!btn) return;
        const form = btn.closest('.registration-form');
        const pctInput = form ? form.querySelector('.proposed-percent') : null;
        if (pctInput) {
            pctInput.value = btn.dataset.pct;
            updatePriceFromPercent(form);
        }
    });

    // ----------- Submit form dang ky -----------

    document.addEventListener('submit', function (e) {
        const form = e.target.closest('.registration-form');
        if (!form) return;
        e.preventDefault();

        clearFormErrors(form);

        const proposedPriceVal = form.querySelector('.proposed-price')?.value;
        if (!proposedPriceVal || parseFloat(proposedPriceVal) <= 0) {
            alert('Vui long nhap muc giam gia hop le (tu 10% den 90%)!');
            return;
        }

        const data = {
            flash_sale_id: form.querySelector('[name="flash_sale_id"]').value,
            product_id: form.querySelector('[name="product_id"]').value,
            proposed_price: proposedPriceVal,
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
