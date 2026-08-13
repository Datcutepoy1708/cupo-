window.updateCartBadge = function (count) {
    const badge = document.getElementById('header-cart-badge');
    if (badge) {
        badge.textContent = count;
        if (count > 0) {
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }
};
