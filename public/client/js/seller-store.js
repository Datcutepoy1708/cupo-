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
});