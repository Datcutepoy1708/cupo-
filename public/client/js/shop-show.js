/* ==============================================
   CUPO STOREFRONT — Shop Detail Page JS
   File: public/client/js/shop-show.js
   ============================================== */

document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // 1. AJAX Toggle Follow/Unfollow Shop
    const followBtn = document.getElementById('btnFollowShop');
    const followersCountEl = document.getElementById('shopFollowersCount');

    if (followBtn) {
        followBtn.addEventListener('click', function () {
            const shopId = followBtn.getAttribute('data-shop-id');
            if (!shopId) return;

            followBtn.disabled = true;

            fetch(`/shops/${shopId}/follow`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => {
                if (res.status === 401) {
                    alert('Vui lòng đăng nhập để thực hiện theo dõi gian hàng này!');
                    window.location.href = '/login';
                    return null;
                }
                return res.json();
            })
            .then(data => {
                if (!data) return;

                if (data.status === 'success') {
                    const isFollowed = data.is_followed;
                    const newCount = data.followers_count;

                    if (isFollowed) {
                        followBtn.classList.add('following');
                        followBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Đang Theo Dõi';
                    } else {
                        followBtn.classList.remove('following');
                        followBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i>Theo Dõi';
                    }

                    if (followersCountEl && typeof newCount !== 'undefined') {
                        followersCountEl.innerText = newCount;
                    }
                } else {
                    alert(data.message || 'Có lỗi xảy ra.');
                }
            })
            .catch(err => console.error('Error toggling shop follow:', err))
            .finally(() => {
                followBtn.disabled = false;
            });
        });
    }

    // 2. Chat Button Click
    const chatBtn = document.getElementById('btnChatShop');
    if (chatBtn) {
        chatBtn.addEventListener('click', function () {
            alert('Tính năng Nhắn tin trực tiếp với Shop đang được kết nối!');
        });
    }

    // 3. Auto-submit search form on Enter or Button Click
    const searchInput = document.getElementById('shopSearchInput');
    const searchBtn = document.getElementById('shopSearchBtn');
    const searchForm = document.getElementById('shopSearchForm');

    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            // Keep default submit behavior to reload with ?q=...
        });
    }
});
