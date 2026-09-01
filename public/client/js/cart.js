/**
 * CUPO CART - Dynamic AJAX & Shopee/TikTok Shop Style Interactivity
 */

// Helper: Format Currency (VND)
function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN").format(Math.round(amount)) + "₫";
}

// Global Badge Updater
window.updateCartBadge = function (count) {
    const badge = document.getElementById("header-cart-badge");
    if (badge) {
        badge.textContent = count;
        if (count > 0) {
            badge.classList.remove("d-none");
        } else {
            badge.classList.add("d-none");
        }
    }
};

// Global Toast Messenger
window.showCartToast = function (message, type = "success") {
    let toastContainer = document.getElementById("cartToastContainer");
    if (!toastContainer) {
        toastContainer = document.createElement("div");
        toastContainer.id = "cartToastContainer";
        toastContainer.className =
            "toast-container position-fixed top-0 end-0 p-3";
        toastContainer.style.zIndex = "1090";
        document.body.appendChild(toastContainer);
    }

    const toastId = "toast-" + Date.now();
    const bgClass =
        type === "success"
            ? "bg-success text-white"
            : type === "danger"
              ? "bg-danger text-white"
              : "bg-dark text-white";
    const icon =
        type === "success"
            ? "fa-circle-check"
            : type === "danger"
              ? "fa-circle-exclamation"
              : "fa-circle-info";

    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2 font-medium" style="font-size: 0.9rem;">
                    <i class="fa-solid ${icon}"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML("beforeend", toastHtml);
    const toastEl = document.getElementById(toastId);
    if (window.bootstrap && bootstrap.Toast) {
        const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
        bsToast.show();
        toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
    } else {
        setTimeout(() => toastEl.remove(), 3500);
    }
};

document.addEventListener("DOMContentLoaded", function () {
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";
    const cartWrapper = document.getElementById("cartMainWrapper");
    if (!cartWrapper) return;

    // References to DOM elements
    const selectAllCheckboxes = document.querySelectorAll(".cart-select-all");
    const shopCheckboxes = document.querySelectorAll(".shop-select-checkbox");
    const itemCheckboxes = document.querySelectorAll(".item-select-checkbox");
    const selectedCountEls = document.querySelectorAll(".selected-items-count");
    const totalPaymentAmountEl = document.getElementById("totalPaymentAmount");
    const totalSavedAmountEl = document.getElementById("totalSavedAmount");
    const totalSavedBoxEl = document.getElementById("totalSavedBox");
    const btnCheckoutNow = document.getElementById("btnCheckoutNow");
    const btnBulkDelete = document.getElementById("btnBulkDelete");

    // Debounce timer for quantity updates
    const debounceTimers = {};

    /**
     * Recalculate totals based on selected checkboxes
     */
    function recalculateCart() {
        let totalQty = 0;
        let totalPrice = 0;
        let totalOriginalPrice = 0;
        let totalItemRows = 0;
        let checkedItemRows = 0;

        document.querySelectorAll(".cart-item-row").forEach((row) => {
            totalItemRows++;
            const chk = row.querySelector(".item-select-checkbox");
            const qtyInput = row.querySelector(".stepper-input");
            const currentPrice = parseFloat(row.dataset.currentPrice || 0);
            const originalPrice = parseFloat(
                row.dataset.originalPrice || currentPrice,
            );
            const qty = parseInt(qtyInput.value) || 1;

            if (chk && chk.checked) {
                checkedItemRows++;
                totalQty += qty;
                totalPrice += currentPrice * qty;
                totalOriginalPrice += originalPrice * qty;
            }
        });

        // Update counts and totals
        selectedCountEls.forEach((el) => (el.textContent = checkedItemRows));

        if (totalPaymentAmountEl) {
            totalPaymentAmountEl.textContent = formatCurrency(totalPrice);
        }

        const savedAmount = totalOriginalPrice - totalPrice;
        if (totalSavedAmountEl) {
            if (savedAmount > 0) {
                totalSavedAmountEl.textContent = formatCurrency(savedAmount);
                if (totalSavedBoxEl) totalSavedBoxEl.classList.remove("d-none");
            } else {
                if (totalSavedBoxEl) totalSavedBoxEl.classList.add("d-none");
            }
        }

        // Enable / Disable buttons
        if (btnCheckoutNow) {
            btnCheckoutNow.disabled = checkedItemRows === 0;
        }
        if (btnBulkDelete) {
            btnBulkDelete.disabled = checkedItemRows === 0;
        }

        // Synchronize Shop Checkboxes
        document.querySelectorAll(".cart-shop-group").forEach((shopGroup) => {
            const shopChk = shopGroup.querySelector(".shop-select-checkbox");
            const shopItemChks = shopGroup.querySelectorAll(
                ".item-select-checkbox",
            );
            if (shopChk && shopItemChks.length > 0) {
                const allCheckedInShop = Array.from(shopItemChks).every(
                    (c) => c.checked,
                );
                shopChk.checked = allCheckedInShop;
            }
        });

        // Synchronize Select All Checkboxes
        const allItemsChecked =
            totalItemRows > 0 && checkedItemRows === totalItemRows;
        selectAllCheckboxes.forEach((chk) => (chk.checked = allItemsChecked));
    }

    /**
     * Check if Cart is empty and show empty state
     */
    function checkEmptyCart() {
        const remainingRows = document.querySelectorAll(".cart-item-row");
        if (remainingRows.length === 0) {
            const emptyContainer =
                document.getElementById("cartEmptyContainer");
            const contentContainer = document.getElementById(
                "cartContentContainer",
            );
            const stickyBar = document.getElementById("cartStickyBar");
            if (contentContainer) contentContainer.classList.add("d-none");
            if (stickyBar) stickyBar.classList.add("d-none");
            if (emptyContainer) emptyContainer.classList.remove("d-none");
            window.updateCartBadge(0);
        }
    }

    /**
     * Checkbox event listeners
     */
    selectAllCheckboxes.forEach((chk) => {
        chk.addEventListener("change", function () {
            const isChecked = this.checked;
            selectAllCheckboxes.forEach((c) => (c.checked = isChecked));
            document
                .querySelectorAll(".shop-select-checkbox")
                .forEach((c) => (c.checked = isChecked));
            document
                .querySelectorAll(".item-select-checkbox")
                .forEach((c) => (c.checked = isChecked));
            recalculateCart();
        });
    });

    document.querySelectorAll(".shop-select-checkbox").forEach((shopChk) => {
        shopChk.addEventListener("change", function () {
            const shopGroup = this.closest(".cart-shop-group");
            if (shopGroup) {
                shopGroup
                    .querySelectorAll(".item-select-checkbox")
                    .forEach((c) => (c.checked = shopChk.checked));
            }
            recalculateCart();
        });
    });

    document.querySelectorAll(".item-select-checkbox").forEach((itemChk) => {
        itemChk.addEventListener("change", function () {
            recalculateCart();
        });
    });

    /**
     * AJAX Quantity Update
     */
    function sendQuantityUpdate(cartItemId, newQty, row) {
        const updateUrl = row.dataset.updateUrl;
        const subtotalEl = row.querySelector(".subtotal-price-val");
        const minusBtn = row.querySelector(".btn-minus");
        const plusBtn = row.querySelector(".btn-plus");
        const maxStock = parseInt(row.dataset.maxStock) || 999999;

        row.classList.add("is-updating");

        fetch(updateUrl, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({ quantity: newQty }),
        })
            .then(async (res) => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(
                        data.message || "Không thể cập nhật số lượng",
                    );
                }
                return data;
            })
            .then((data) => {
                row.classList.remove("is-updating");
                // Update Subtotal
                if (subtotalEl && data.item_subtotal) {
                    subtotalEl.textContent = formatCurrency(data.item_subtotal);
                }
                // Update Header Cart Badge
                if (data.total_items !== undefined) {
                    window.updateCartBadge(data.total_items);
                }
                // Toggle button states
                if (minusBtn) minusBtn.disabled = newQty <= 1;
                if (plusBtn) plusBtn.disabled = newQty >= maxStock;

                recalculateCart();
            })
            .catch((err) => {
                row.classList.remove("is-updating");
                window.showCartToast(err.message, "danger");
                // Revert value
                const currentQty = parseInt(row.dataset.currentQty) || 1;
                const input = row.querySelector(".stepper-input");
                if (input) input.value = currentQty;
                recalculateCart();
            });
    }

    /**
     * Quantity Stepper Buttons (+ / -)
     */
    document.querySelectorAll(".cart-quantity-stepper").forEach((stepper) => {
        const row = stepper.closest(".cart-item-row");
        const cartItemId = row.dataset.itemId;
        const input = stepper.querySelector(".stepper-input");
        const btnMinus = stepper.querySelector(".btn-minus");
        const btnPlus = stepper.querySelector(".btn-plus");
        const maxStock = parseInt(row.dataset.maxStock) || 999999;

        if (btnMinus) {
            btnMinus.addEventListener("click", function () {
                let currentVal = parseInt(input.value) || 1;
                if (currentVal > 1) {
                    currentVal--;
                    input.value = currentVal;
                    row.dataset.currentQty = currentVal;
                    row.querySelector(".subtotal-price-val").textContent =
                        formatCurrency(
                            parseFloat(row.dataset.currentPrice) * currentVal,
                        );
                    recalculateCart();

                    clearTimeout(debounceTimers[cartItemId]);
                    debounceTimers[cartItemId] = setTimeout(() => {
                        sendQuantityUpdate(cartItemId, currentVal, row);
                    }, 350);
                }
            });
        }

        if (btnPlus) {
            btnPlus.addEventListener("click", function () {
                let currentVal = parseInt(input.value) || 1;
                if (currentVal < maxStock) {
                    currentVal++;
                    input.value = currentVal;
                    row.dataset.currentQty = currentVal;
                    row.querySelector(".subtotal-price-val").textContent =
                        formatCurrency(
                            parseFloat(row.dataset.currentPrice) * currentVal,
                        );
                    recalculateCart();

                    clearTimeout(debounceTimers[cartItemId]);
                    debounceTimers[cartItemId] = setTimeout(() => {
                        sendQuantityUpdate(cartItemId, currentVal, row);
                    }, 350);
                } else {
                    window.showCartToast(
                        `Số lượng đã đạt giới hạn tồn kho (${maxStock})`,
                        "warning",
                    );
                }
            });
        }

        if (input) {
            input.addEventListener("change", function () {
                let val = parseInt(this.value);
                if (isNaN(val) || val < 1) val = 1;
                if (val > maxStock) {
                    val = maxStock;
                    window.showCartToast(
                        `Số lượng đã được chỉnh về mức tồn kho tối đa (${maxStock})`,
                        "warning",
                    );
                }
                this.value = val;
                row.dataset.currentQty = val;
                row.querySelector(".subtotal-price-val").textContent =
                    formatCurrency(parseFloat(row.dataset.currentPrice) * val);
                recalculateCart();

                clearTimeout(debounceTimers[cartItemId]);
                debounceTimers[cartItemId] = setTimeout(() => {
                    sendQuantityUpdate(cartItemId, val, row);
                }, 300);
            });
        }
    });

    /**
     * Single Item Delete
     */
    document.querySelectorAll(".btn-remove-item").forEach((btn) => {
        btn.addEventListener("click", function () {
            const row = this.closest(".cart-item-row");
            const cartItemId = row.dataset.itemId;
            const deleteUrl = row.dataset.deleteUrl;
            const productName = row.dataset.productName || "sản phẩm này";

            if (
                !confirm(
                    `Bạn có chắc chắn muốn xóa "${productName}" khỏi giỏ hàng?`,
                )
            ) {
                return;
            }

            row.classList.add("is-removing");

            fetch(deleteUrl, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
            })
                .then(async (res) => {
                    const data = await res.json();
                    if (!res.ok)
                        throw new Error(
                            data.message || "Xóa sản phẩm thất bại",
                        );
                    return data;
                })
                .then((data) => {
                    const shopGroup = row.closest(".cart-shop-group");
                    row.remove();

                    // Check if shop group is now empty
                    if (
                        shopGroup &&
                        shopGroup.querySelectorAll(".cart-item-row").length ===
                            0
                    ) {
                        shopGroup.remove();
                    }

                    window.updateCartBadge(data.total_items || 0);
                    window.showCartToast(
                        data.message || "Đã xóa sản phẩm khỏi giỏ hàng!",
                    );
                    recalculateCart();
                    checkEmptyCart();
                })
                .catch((err) => {
                    row.classList.remove("is-removing");
                    window.showCartToast(err.message, "danger");
                });
        });
    });

    /**
     * Bulk Delete Selected Items
     */
    if (btnBulkDelete) {
        btnBulkDelete.addEventListener("click", async function () {
            const selectedRows = Array.from(
                document.querySelectorAll(".cart-item-row"),
            ).filter((r) => {
                const chk = r.querySelector(".item-select-checkbox");
                return chk && chk.checked;
            });

            if (selectedRows.length === 0) {
                window.showCartToast(
                    "Vui lòng chọn ít nhất một sản phẩm để xóa.",
                    "warning",
                );
                return;
            }

            if (
                !confirm(
                    `Bạn có chắc muốn xóa ${selectedRows.length} sản phẩm đã chọn khỏi giỏ hàng?`,
                )
            ) {
                return;
            }

            btnBulkDelete.disabled = true;
            let successCount = 0;

            for (const row of selectedRows) {
                row.classList.add("is-removing");
                const deleteUrl = row.dataset.deleteUrl;
                try {
                    const res = await fetch(deleteUrl, {
                        method: "DELETE",
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const shopGroup = row.closest(".cart-shop-group");
                        row.remove();
                        if (
                            shopGroup &&
                            shopGroup.querySelectorAll(".cart-item-row")
                                .length === 0
                        ) {
                            shopGroup.remove();
                        }
                        successCount++;
                        if (data.total_items !== undefined) {
                            window.updateCartBadge(data.total_items);
                        }
                    }
                } catch (e) {
                    row.classList.remove("is-removing");
                }
            }

            btnBulkDelete.disabled = false;
            window.showCartToast(`Đã xóa thành công ${successCount} sản phẩm.`);
            recalculateCart();
            checkEmptyCart();
        });
    }

    /**
     * Redirect selected cart items to the dedicated checkout page
     */
    if (btnCheckoutNow) {
        btnCheckoutNow.addEventListener("click", function () {
            const checkedRows = Array.from(
                document.querySelectorAll(".cart-item-row"),
            ).filter((r) => {
                const chk = r.querySelector(".item-select-checkbox");
                return chk && chk.checked;
            });

            if (checkedRows.length === 0) {
                window.showCartToast(
                    "Vui lòng chọn ít nhất 1 sản phẩm để tiến hành Mua Hàng!",
                    "warning",
                );
                return;
            }

            const checkoutUrl =
                btnCheckoutNow.dataset.checkoutUrl || "/checkout";
            const cartItemIds = checkedRows
                .map((row) => row.dataset.itemId)
                .filter(Boolean);
            const query = new URLSearchParams({
                cart_item_ids: cartItemIds.join(","),
            });

            window.location.href = checkoutUrl + "?" + query.toString();
        });
    }

    // Initial calculation on page load
    recalculateCart();
});
