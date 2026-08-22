/**
 * =========================================================
 * CUPO ADMIN - ACTIVITY LOGS / AUDIT TRAIL JAVASCRIPT
 * =========================================================
 */

(function () {
    'use strict';

    const tableBody = document.getElementById('activityLogsTableBody');
    if (!tableBody) return;

    let currentPage = 1;
    let currentUser = '';
    let currentModule = '';
    let currentSearch = '';
    let currentDateFrom = '';
    let currentDateTo = '';

    const detailModalEl = document.getElementById('logDetailModal');
    const bsDetailModal = detailModalEl ? new bootstrap.Modal(detailModalEl) : null;

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())} ${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
    }

    function loadLogs(page = 1) {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>`;

        const params = new URLSearchParams({
            page: page,
            user_id: currentUser,
            module: currentModule,
            search: currentSearch,
            date_from: currentDateFrom,
            date_to: currentDateTo,
        });

        fetch(`/admin/activity-logs?${params.toString()}`, {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(json => {
                renderLogsTable(json.data);
                renderPagination(json);
                updateStats(json.meta);
            })
            .catch(() => {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Không thể tải dữ liệu nhật ký.</td></tr>`;
            });
    }

    function renderLogsTable(items) {
        if (!items || items.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-5"><i class="fa-solid fa-clipboard-list fs-2 d-block mb-2 text-secondary"></i>Không tìm thấy nhật ký hoạt động nào</td></tr>`;
            return;
        }

        const moduleBadges = {
            'withdrawals': '<span class="badge bg-success text-white">Tài chính</span>',
            'sellers': '<span class="badge bg-primary text-white">Gian hàng</span>',
            'products': '<span class="badge bg-info text-dark">Sản phẩm</span>',
            'disputes': '<span class="badge bg-danger text-white">Tranh chấp</span>',
            'shipping': '<span class="badge bg-indigo text-white">Vận chuyển</span>',
            'coupons': '<span class="badge bg-warning text-dark">Mã giảm giá</span>',
            'settings': '<span class="badge bg-dark text-white">Cài đặt</span>',
            'roles': '<span class="badge bg-purple text-white">Phân quyền</span>',
            'auth': '<span class="badge bg-secondary text-white">Bảo mật</span>',
        };

        tableBody.innerHTML = items.map((item) => {
            const userName = item.user?.name || 'System / Hệ thống';
            const userRole = item.user?.role || 'System';
            const userEmail = item.user?.email || '';
            const initial = userName.charAt(0).toUpperCase();

            const roleBadges = {
                'super-admin': '<span class="badge bg-danger text-white" style="font-size: 10px;">Super Admin</span>',
                'admin': '<span class="badge bg-primary text-white" style="font-size: 10px;">Admin</span>',
                'moderator': '<span class="badge bg-info text-dark" style="font-size: 10px;">Moderator</span>',
                'accountant': '<span class="badge bg-success text-white" style="font-size: 10px;">Kế toán</span>',
            };

            const isSensitive = [
                'approve_withdrawal', 'reject_withdrawal', 'block_seller',
                'reject_seller', 'refund_dispute', 'update_settings', 'roles.manage'
            ].includes(item.action);

            return `
              <tr>
                <td class="small text-muted" style="width: 145px;">
                  <i class="fa-regular fa-clock me-1"></i>${formatDate(item.created_at)}
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="audit-avatar">${initial}</div>
                    <div>
                      <div class="fw-bold text-dark fs-7">${escHtml(userName)}</div>
                      <div class="d-flex align-items-center gap-1">
                        ${roleBadges[userRole] || `<span class="badge bg-secondary" style="font-size: 10px;">${userRole}</span>`}
                        <small class="text-muted" style="font-size: 11px;">${escHtml(userEmail)}</small>
                      </div>
                    </div>
                  </div>
                </td>
                <td>${moduleBadges[item.module] || `<span class="badge bg-light text-dark border">${item.module}</span>`}</td>
                <td>
                  <div class="fw-semibold text-dark ${isSensitive ? 'text-danger' : ''}">${escHtml(item.description)}</div>
                  <div class="small font-monospace text-muted" style="font-size: 11px;">Mã action: ${escHtml(item.action)}</div>
                </td>
                <td>
                  <span class="audit-ip-badge"><i class="fa-solid fa-network-wired me-1"></i>${escHtml(item.ip_address || '127.0.0.1')}</span>
                </td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-secondary btn-view-log-detail" data-id="${item.id}" title="Xem chi tiết payload">
                    <i class="fa-solid fa-eye me-1"></i>Chi tiết
                  </button>
                </td>
              </tr>
            `;
        }).join('');

        tableBody.querySelectorAll('.btn-view-log-detail').forEach(btn => {
            btn.addEventListener('click', () => openLogDetail(btn.dataset.id));
        });
    }

    function renderPagination(json) {
        const wrap = document.getElementById('logsPaginationWrap');
        if (!wrap) return;
        const { current_page, last_page, from, to, total } = json;
        if (total === 0) {
            wrap.style.display = 'none';
            return;
        }
        wrap.style.display = 'flex';
        document.getElementById('logsPaginationInfo').textContent = `Hiển thị ${from ?? 0}–${to ?? 0} / ${total} nhật ký`;

        let pagesHtml = '';
        for (let p = 1; p <= last_page; p++) {
            pagesHtml += `<button type="button" class="btn btn-sm ${p === current_page ? 'btn-primary' : 'btn-outline-secondary'} page-btn" data-page="${p}">${p}</button>`;
        }
        const linksWrap = document.getElementById('logsPaginationLinks');
        linksWrap.innerHTML = pagesHtml;

        linksWrap.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentPage = parseInt(btn.dataset.page);
                loadLogs(currentPage);
            });
        });
    }

    function updateStats(meta) {
        if (!meta) return;
        if (document.getElementById('statTotalLogs')) document.getElementById('statTotalLogs').textContent = meta.total_logs;
        if (document.getElementById('statTodayLogs')) document.getElementById('statTodayLogs').textContent = meta.today_logs;
        if (document.getElementById('statSensitiveLogs')) document.getElementById('statSensitiveLogs').textContent = meta.sensitive_logs;
        if (document.getElementById('statAuthLogs')) document.getElementById('statAuthLogs').textContent = meta.auth_logs;
    }

    function openLogDetail(logId) {
        if (!bsDetailModal) return;
        document.getElementById('modalLogProperties').textContent = 'Đang tải dữ liệu...';
        bsDetailModal.show();

        fetch(`/admin/activity-logs/${logId}`, {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(res => {
                const d = res.data;
                document.getElementById('modalLogId').textContent = d.id;
                document.getElementById('modalLogTime').textContent = d.created_at;
                document.getElementById('modalLogUser').textContent = d.user?.name || 'System / Hệ thống';
                document.getElementById('modalLogUserRole').innerHTML = `<span class="badge bg-secondary">${d.user?.role || 'N/A'}</span> (${d.user?.email || 'N/A'})`;
                document.getElementById('modalLogModule').textContent = d.module_label;
                document.getElementById('modalLogModule').className = 'badge bg-primary me-1';
                document.getElementById('modalLogAction').textContent = d.action;
                document.getElementById('modalLogDesc').textContent = d.description;
                document.getElementById('modalLogIp').textContent = d.ip_address || '127.0.0.1';
                document.getElementById('modalLogUserAgent').textContent = d.user_agent || 'N/A';

                if (d.properties && Object.keys(d.properties).length > 0) {
                    document.getElementById('modalLogProperties').textContent = JSON.stringify(d.properties, null, 2);
                } else {
                    document.getElementById('modalLogProperties').textContent = 'Không có dữ liệu payload kèm theo.';
                }
            })
            .catch(() => {
                document.getElementById('modalLogProperties').textContent = 'Không thể tải chi tiết nhật ký.';
            });
    }

    // Filter event listeners
    document.getElementById('userFilterSelect')?.addEventListener('change', function () {
        currentUser = this.value;
        currentPage = 1;
        loadLogs(1);
    });

    document.getElementById('moduleFilterSelect')?.addEventListener('change', function () {
        currentModule = this.value;
        currentPage = 1;
        loadLogs(1);
    });

    document.getElementById('searchLogInput')?.addEventListener('input', function () {
        currentSearch = this.value.trim();
        currentPage = 1;
        loadLogs(1);
    });

    document.getElementById('dateFromInput')?.addEventListener('change', function () {
        currentDateFrom = this.value;
        currentPage = 1;
        loadLogs(1);
    });

    document.getElementById('dateToInput')?.addEventListener('change', function () {
        currentDateTo = this.value;
        currentPage = 1;
        loadLogs(1);
    });

    document.getElementById('btnResetFilters')?.addEventListener('click', function () {
        document.getElementById('userFilterSelect').value = '';
        document.getElementById('moduleFilterSelect').value = '';
        document.getElementById('searchLogInput').value = '';
        document.getElementById('dateFromInput').value = '';
        document.getElementById('dateToInput').value = '';
        currentUser = '';
        currentModule = '';
        currentSearch = '';
        currentDateFrom = '';
        currentDateTo = '';
        loadLogs(1);
    });

    // Export CSV
    document.getElementById('btnExportLogs')?.addEventListener('click', function () {
        const params = new URLSearchParams({
            user_id: currentUser,
            module: currentModule,
            search: currentSearch,
            date_from: currentDateFrom,
            date_to: currentDateTo,
        });
        window.location.href = `/admin/activity-logs/export?${params.toString()}`;
    });

    // Initial Load
    loadLogs(1);

})();
