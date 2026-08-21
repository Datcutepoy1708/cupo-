/**
 * =========================================================
 * CUPO - REAL-TIME NOTIFICATION CLIENT JAVASCRIPT
 * =========================================================
 */

(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const badgeEl = document.getElementById('notifBadgeCount');
    const containerEl = document.getElementById('notifListContainer');
    const bellBtn = document.getElementById('notifBellBtn');
    const markAllBtn = document.getElementById('btnMarkAllRead');

    if (!bellBtn) return;

    // Helper: Format Time Ago (Việt hóa)
    function timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        if (seconds < 60) return 'Vừa xong';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return `${minutes} phút trước`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours} giờ trước`;
        const days = Math.floor(hours / 24);
        return `${days} ngày trước`;
    }

    // 1. Fetch Unread Count
    function fetchUnreadCount() {
        fetch('/notifications/unread-count', {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(res => {
                const count = res.unread_count || 0;
                if (badgeEl) {
                    if (count > 0) {
                        badgeEl.textContent = count > 99 ? '99+' : count;
                        badgeEl.classList.remove('d-none');
                    } else {
                        badgeEl.classList.add('d-none');
                    }
                }
            })
            .catch(() => {});
    }

    // 2. Fetch Notifications List
    function fetchNotificationList() {
        if (!containerEl) return;

        fetch('/notifications?limit=10', {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(res => {
                const notifs = res.data || [];
                if (notifs.length === 0) {
                    containerEl.innerHTML = `
                        <div class="p-4 text-center text-muted notif-empty-state">
                            <i class="fa-regular fa-bell-slash fs-3 mb-2 text-secondary"></i>
                            <div class="small">Chưa có thông báo nào mới</div>
                        </div>
                    `;
                    return;
                }

                let html = '';
                notifs.forEach(n => {
                    const data = n.data || {};
                    const isUnread = !n.read_at;
                    const type = data.type || 'info';
                    const icon = data.icon || 'fa-solid fa-bell';
                    const title = data.title || 'Thông báo hệ thống';
                    const desc = data.message || '';
                    const url = data.url || '#';
                    const time = timeAgo(n.created_at);

                    html += `
                        <div class="notif-item ${isUnread ? 'unread' : ''}" data-id="${n.id}" data-url="${url}">
                            <div class="notif-icon-circle notif-icon-${type}">
                                <i class="${icon}"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="notif-title text-truncate">${title}</div>
                                <div class="notif-desc">${desc}</div>
                                <div class="notif-time"><i class="fa-regular fa-clock me-1"></i>${time}</div>
                            </div>
                            ${isUnread ? '<div class="notif-unread-dot"></div>' : ''}
                        </div>
                    `;
                });

                containerEl.innerHTML = html;

                // Bind click events on notification items
                containerEl.querySelectorAll('.notif-item').forEach(item => {
                    item.addEventListener('click', function () {
                        const notifId = this.dataset.id;
                        const targetUrl = this.dataset.url;

                        // Mark single as read
                        fetch(`/notifications/${notifId}/read`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).finally(() => {
                            fetchUnreadCount();
                            if (targetUrl && targetUrl !== '#') {
                                window.location.href = targetUrl;
                            }
                        });
                    });
                });
            })
            .catch(() => {});
    }

    // 3. Mark All As Read
    markAllBtn?.addEventListener('click', function (e) {
        e.stopPropagation();
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(r => r.json())
            .then(() => {
                fetchUnreadCount();
                fetchNotificationList();
            })
            .catch(() => {});
    });

    // When dropdown opens, reload the list
    bellBtn.addEventListener('click', function () {
        fetchNotificationList();
    });

    // Initial check & periodic polling every 30s
    fetchUnreadCount();
    setInterval(fetchUnreadCount, 30000);

})();
