/**
 * CUPO ADMIN — Global JS
 * File: public/admin/js/admin.js
 *
 * 1. Sidebar mobile toggle
 * 2. Light / Dark theme toggle (persisted in localStorage)
 */

(function () {

    /* ─── 1. Sidebar mobile toggle ─────────────────────────── */
    const sidebar   = document.getElementById('adminSidebar');
    const overlay   = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (sidebar && overlay && toggleBtn) {
        function openSidebar() {
            sidebar.classList.add('sidebar-open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('sidebar-open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        toggleBtn.addEventListener('click', function () {
            sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
        });

        overlay.addEventListener('click', closeSidebar);
    }

    /* ─── 2. Light / Dark theme ────────────────────────────── */
    const htmlEl        = document.documentElement;
    const themeBtn      = document.getElementById('themeToggleBtn');
    const STORAGE_KEY   = 'cupo_admin_theme';

    /**
     * Áp dụng theme (gán data-theme lên <html>)
     * @param {'light'|'dark'} theme
     */
    function applyTheme(theme) {
        htmlEl.setAttribute('data-theme', theme);
        if (themeBtn) {
            themeBtn.title = theme === 'dark' ? 'Chuyển sang Light mode' : 'Chuyển sang Dark mode';
        }
    }

    // Khởi tạo từ localStorage (ưu tiên), mặc định light
    const savedTheme = localStorage.getItem(STORAGE_KEY) || 'light';
    applyTheme(savedTheme);

    // Khi bấm nút toggle
    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            const current = htmlEl.getAttribute('data-theme') || 'light';
            const next    = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem(STORAGE_KEY, next);
        });
    }

})();
