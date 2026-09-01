document.addEventListener("DOMContentLoaded", function () {
    const addressSelect = document.getElementById("checkoutAddressSelect");
    const recipientName = document.getElementById("recipient_name");
    const recipientPhone = document.getElementById("recipient_phone");
    const recipientAddress = document.getElementById("recipient_address");

    if (addressSelect) {
        addressSelect.addEventListener("change", function () {
            const selected = this.options[this.selectedIndex];
            if (!selected) return;

            recipientName.value = selected.dataset.name || "";
            recipientPhone.value = selected.dataset.phone || "";
            recipientAddress.value = selected.dataset.address || "";
        });
    }

    document.querySelectorAll(".payment-card").forEach(function (card) {
        card.addEventListener("click", function () {
            document.querySelectorAll(".payment-card").forEach(function (item) {
                item.classList.remove("active");
            });
            this.classList.add("active");
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    const form = document.getElementById("checkoutPageForm");
    const submitBtn = form?.querySelector('button[type="submit"]');
    if (form && submitBtn) {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';

            const formData = new FormData(form);
            const payload = {
                recipient_name: formData.get("recipient_name"),
                phone: formData.get("phone"),
                shipping_address: formData.get("shipping_address"),
                payment_method: formData.get("payment_method"),
                note: formData.get("note") || null,
                checkout_mode: formData.get("checkout_mode"),
                cart_item_ids: formData.get("cart_item_ids") || null,
                product_id: formData.get("product_id") || null,
                product_variant_id: formData.get("product_variant_id") || null,
                qty: formData.get("qty") || null,
            };

            try {
                const response = await fetch(form.action, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || "Đặt hàng thất bại.");
                }

                window.location.href =
                    form.dataset.redirectUrl || "/customer/orders";
            } catch (error) {
                submitBtn.disabled = false;
                submitBtn.innerHTML =
                    '<i class="fa-solid fa-lock me-2"></i> Xác nhận đơn hàng';
                const errorBox = document.getElementById("checkoutErrorBox");
                if (errorBox) {
                    errorBox.textContent = error.message;
                    errorBox.classList.remove("d-none");
                }
            }
        });
    }
});
