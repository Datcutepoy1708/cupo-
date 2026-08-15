/**
 * flash-sale-registrations.js (Admin side)
 *
 * Xu ly:
 * 1. Nut "Dang ky" -> open modal, load danh sach dang ky qua Ajax
 * 2. Tab loc (pending/approved/rejected)
 * 3. Duyet dang ky (PATCH /admin/flash-sales/registrations/{id}/approve)
 * 4. Tu choi dang ky (PATCH /admin/flash-sales/registrations/{id}/reject)
 *
 * Moi truong: data-* attributes tren phan tu HTML (khong co inline script)
 */

(function () {
    'use strict';

    const modal = document.getElementById('flashSaleRegistrationsModal');
    const bsModal = modal ? new bootstrap.Modal(modal) : null;

    const modalTitle = document.getElementById('registrationsSessionTitle');
    const tableBody = document.getElementById('registrationsTableBody');
    const countPending = document.getElementById('regCountPending');
    const countApproved = document.getElementById('regCountApproved');
    const countRejected = document.getElementById('regCountRejected');
    const rejectBox = document.getElementById('rejectReasonBox');
    const rejectInput = document.getElementById('rejectReasonInput');
    const btnConfirmReject = document.getElementById('btnConfirmReject');
    const btnCancelReject = document.getElementById('btnCancelReject');

    let allRegistrations = [];
    let activeFilter = '';
    let pendingRejectUrl = null;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ----------- Open modal & load data -----------

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-view-registrations');
        if (!btn) return;

        const name = btn.dataset.name;
        const registrationsUrl = btn.dataset.registrationsUrl;

        modalTitle.textContent = name;
        activeFilter = '';
        setActiveTab('');
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Dang tai du lieu...</td></tr>';

        if (bsModal) bsModal.show();

        fetch(registrationsUrl, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        })
            .then(r => r.json())
            .then(data => {
                allRegistrations = data.registrations || [];
                updateCounts(data.counts || {});
                renderTable(allRegistrations, activeFilter, btn);
            })
            .catch(() => {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Loi tai du lieu. Vui long thu lai.</td></tr>';
            });
    });

    // ----------- Tab filter -----------

    document.getElementById('regTabs')?.addEventListener('click', function (e) {
        const tab = e.target.closest('[data-status]');
        if (!tab) return;

        activeFilter = tab.dataset.status;
        setActiveTab(activeFilter);
        renderTable(allRegistrations, activeFilter, null);
    });

    function setActiveTab(status) {
        document.querySelectorAll('#regTabs .nav-link').forEach(t => {
            t.classList.toggle('active', t.dataset.status === status);
        });
    }

    // ----------- Render table -----------

    function renderTable(registrations, filter, btn) {
        const filtered = filter
            ? registrations.filter(r => r.status === filter)
            : registrations;

        if (filtered.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Khong co dang ky nao.</td></tr>';
            return;
        }

        tableBody.innerHTML = filtered.map(r => {
            const approveUrl = btn ? `${btn.dataset.approveUrl}/${r.id}/approve` : `${window._flashSaleApproveBase}/${r.id}/approve`;
            const rejectUrl = btn ? `${btn.dataset.rejectUrl}/${r.id}/reject` : `${window._flashSaleRejectBase}/${r.id}/reject`;
            const statusBadge = statusLabel(r.status);
            const actions = r.status === 'pending'
                ? `<button class="btn btn-xs btn-success btn-approve-reg" data-url="${approveUrl}" data-id="${r.id}">Duyet</button>
                   <button class="btn btn-xs btn-danger btn-reject-reg ms-1" data-url="${rejectUrl}" data-id="${r.id}">Tu choi</button>`
                : '--';

            return `<tr data-id="${r.id}">
                <td>
                    <div class="fw-semibold">${r.seller?.name ?? '--'}</div>
                    <div class="text-muted small">${r.seller?.email ?? ''}</div>
                </td>
                <td>${r.product?.name ?? 'SP khong con'}</td>
                <td class="fw-bold text-primary">${formatCurrency(r.proposed_price)}</td>
                <td>${r.proposed_quantity}</td>
                <td>${statusBadge}</td>
                <td>${actions}</td>
            </tr>`;
        }).join('');

        // Store approve/reject urls for later use from event delegation
        if (btn) {
            window._flashSaleApproveBase = btn.dataset.approveUrl;
            window._flashSaleRejectBase = btn.dataset.rejectUrl;
        }
    }

    // ----------- Approve -----------

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-approve-reg');
        if (!btn) return;

        if (!confirm('Duyet dang ky nay va them san pham vao phien Flash Sale?')) return;

        patchUrl(btn.dataset.url)
            .then(data => {
                showToast(data.message, 'success');
                const reg = allRegistrations.find(r => String(r.id) === String(btn.dataset.id));
                if (reg) reg.status = 'approved';
                updateCountsFromAll();
                renderTable(allRegistrations, activeFilter, null);
                hideRejectBox();
            })
            .catch(msg => showToast(msg, 'danger'));
    });

    // ----------- Reject -----------

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-reject-reg');
        if (!btn) return;

        pendingRejectUrl = btn.dataset.url;
        rejectInput.value = '';
        rejectBox.classList.remove('d-none');
        rejectInput.focus();
    });

    btnConfirmReject?.addEventListener('click', function () {
        const reason = rejectInput.value.trim();
        if (reason.length < 5) {
            rejectInput.classList.add('is-invalid');
            return;
        }
        rejectInput.classList.remove('is-invalid');

        patchUrl(pendingRejectUrl, { rejection_reason: reason })
            .then(data => {
                showToast(data.message, 'success');
                // Find the registration being rejected by URL ID
                const id = pendingRejectUrl.match(/registrations\/(\d+)\/reject/)?.[1];
                if (id) {
                    const reg = allRegistrations.find(r => String(r.id) === id);
                    if (reg) reg.status = 'rejected';
                }
                updateCountsFromAll();
                renderTable(allRegistrations, activeFilter, null);
                hideRejectBox();
            })
            .catch(msg => showToast(msg, 'danger'));
    });

    btnCancelReject?.addEventListener('click', hideRejectBox);

    function hideRejectBox() {
        rejectBox?.classList.add('d-none');
        pendingRejectUrl = null;
    }

    // ----------- Helpers -----------

    function patchUrl(url, body = {}) {
        return fetch(url, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body),
        }).then(async r => {
            const data = await r.json();
            if (!r.ok || !data.success) throw data.message || 'Co loi xay ra.';
            return data;
        });
    }

    function updateCounts(counts) {
        if (countPending) countPending.textContent = counts.pending ?? 0;
        if (countApproved) countApproved.textContent = counts.approved ?? 0;
        if (countRejected) countRejected.textContent = counts.rejected ?? 0;
    }

    function updateCountsFromAll() {
        const counts = {
            pending: allRegistrations.filter(r => r.status === 'pending').length,
            approved: allRegistrations.filter(r => r.status === 'approved').length,
            rejected: allRegistrations.filter(r => r.status === 'rejected').length,
        };
        updateCounts(counts);
    }

    function statusLabel(status) {
        const map = {
            pending: '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Cho duyet</span>',
            approved: '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Da duyet</span>',
            rejected: '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Da tu choi</span>',
        };
        return map[status] ?? status;
    }

    function formatCurrency(num) {
        return parseInt(num).toLocaleString('vi-VN') + 'd';
    }

    function showToast(message, type = 'success') {
        // Reuse toast utility if available in the project, fallback to alert
        if (window.showAdminToast) {
            window.showAdminToast(message, type);
        } else {
            alert(message);
        }
    }

})();
