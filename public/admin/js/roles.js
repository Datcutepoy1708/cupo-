/* ==============================================
   CUPO ADMIN — Staff Management & Granular CRUD RBAC JS
   File: public/admin/js/roles.js
   ============================================== */

document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // State Variables
    let rolesData = [];
    let groupedPermissions = {};
    let usersData = [];

    // DOM Elements & Dynamic URLs
    const containerEl = document.getElementById('rolesContainer');
    const apiUrl = containerEl ? containerEl.getAttribute('data-api-url') : '/admin/roles/data';
    const storeUrl = containerEl ? containerEl.getAttribute('data-store-url') : '/admin/roles';
    const assignUrl = containerEl ? containerEl.getAttribute('data-assign-url') : '/admin/roles/assign-user';
    const staffUrl = '/admin/staff';

    const staffTableBody = document.getElementById('staffTableBody');
    const rolesTableBody = document.getElementById('rolesTableBody');
    const totalRolesCount = document.getElementById('totalRolesCount');
    const totalPermsCount = document.getElementById('totalPermsCount');
    const totalStaffCount = document.getElementById('totalStaffCount');

    // Load Initial Data
    loadRolesData();

    function loadRolesData() {
        fetch(apiUrl, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(resData => {
            if (resData.status === 'success') {
                const data = resData.data;
                rolesData = data.roles || [];
                groupedPermissions = data.grouped_permissions || {};
                usersData = data.users || [];

                renderStats();
                renderStaffTable();
                renderRolesTable();
                renderPermissionMatrix('createRolePermissionsContainer');
                populateRoleOptions();
            }
        })
        .catch(err => console.error('Failed to load roles data:', err));
    }

    // Render Stats
    function renderStats() {
        if (totalRolesCount) totalRolesCount.innerText = rolesData.length;
        if (totalPermsCount) {
            let totalP = 0;
            Object.values(groupedPermissions).forEach(group => {
                totalP += (group.length || 0);
            });
            totalPermsCount.innerText = totalP;
        }
        if (totalStaffCount) totalStaffCount.innerText = usersData.length;
    }

    // 1. Render Staff Table (Tab 1)
    function renderStaffTable() {
        if (!staffTableBody) return;
        if (usersData.length === 0) {
            staffTableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">Chưa có nhân viên nào.</td></tr>`;
            return;
        }

        staffTableBody.innerHTML = usersData.map((u, idx) => {
            const roleName = u.role || 'N/A';
            const statusBadge = u.status === 'active'
                ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Hoạt động</span>`
                : `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i>Bị khóa</span>`;

            const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=ee4d2d&color=fff`;

            return `
                <tr>
                    <td class="fw-semibold text-secondary">${idx + 1}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="${avatarUrl}" alt="${u.name}" class="rounded-circle" style="width: 36px; height: 36px;">
                            <div>
                                <strong class="d-block text-dark small">${u.name}</strong>
                                <span class="text-muted extra-small">${u.email}</span>
                            </div>
                        </div>
                    </td>
                    <td class="small text-secondary">${u.phone || 'Chưa cập nhật'}</td>
                    <td>
                        <span class="badge-role ${roleName}">${roleName}</span>
                    </td>
                    <td>${statusBadge}</td>
                    <td class="small text-muted">${u.created_at ? new Date(u.created_at).toLocaleDateString('vi-VN') : 'N/A'}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="openEditStaffModal(${u.id})" title="Chỉnh sửa thông tin">
                            <i class="fa-solid fa-user-pen"></i> Sửa
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning me-1" onclick="openResetPasswordModal(${u.id}, '${u.name}')" title="Đổi mật khẩu">
                            <i class="fa-solid fa-key"></i> Đổi MK
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStaff(${u.id}, '${u.name}')" title="Xóa tài khoản">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // 2. Render Roles Table (Tab 2)
    function renderRolesTable() {
        if (!rolesTableBody) return;
        if (rolesData.length === 0) {
            rolesTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">Chưa có chức vụ nào.</td></tr>`;
            return;
        }

        rolesTableBody.innerHTML = rolesData.map((role, idx) => {
            const roleClass = role.name;
            const permsCount = role.permissions ? role.permissions.length : 0;
            const usersCount = role.users_count || 0;
            const isProtected = ['super-admin', 'admin', 'seller', 'customer'].includes(role.name);

            return `
                <tr>
                    <td class="fw-semibold text-secondary">${idx + 1}</td>
                    <td>
                        <span class="badge-role ${roleClass}">${role.name}</span>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">
                            <i class="fa-solid fa-key me-1"></i>${permsCount} quyền CRUD
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
                            <i class="fa-solid fa-users me-1"></i>${usersCount} nhân viên
                        </span>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="openEditRoleModal(${role.id})" title="Chỉnh sửa mảng quyền">
                            <i class="fa-solid fa-sliders me-1"></i>Chỉnh Phân Quyền
                        </button>
                        ${!isProtected ? `
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRole(${role.id}, '${role.name}')" title="Xóa chức vụ">
                                <i class="fa-solid fa-trash-can"></i> Xóa
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `;
        }).join('');
    }

    // 3. Render Permission Matrix in Modal
    function renderPermissionMatrix(targetModalId = 'createRolePermissionsContainer') {
        const container = document.getElementById(targetModalId);
        if (!container) return;

        let html = '';
        Object.keys(groupedPermissions).forEach((groupName, gIdx) => {
            const perms = groupedPermissions[groupName];
            if (!perms || perms.length === 0) return;

            html += `
                <div class="perm-group-box">
                    <div class="perm-group-header">
                        <span class="perm-group-title"><i class="fa-solid fa-folder-open me-2 text-danger"></i>${groupName}</span>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input group-select-all" type="checkbox" id="group_check_${targetModalId}_${gIdx}" onchange="toggleGroupCheck(this, '${targetModalId}', ${gIdx})">
                            <label class="form-check-label small fw-semibold text-secondary" for="group_check_${targetModalId}_${gIdx}">Chọn tất cả</label>
                        </div>
                    </div>
                    <div class="row g-2 group-perm-rows" data-group-idx="${gIdx}">
                        ${perms.map(p => `
                            <div class="col-md-6 col-lg-4">
                                <div class="perm-checkbox-item d-flex align-items-center">
                                    <input class="form-check-input me-2 perm-checkbox" type="checkbox" name="permissions[]" value="${p.name}" id="perm_${targetModalId}_${p.id}">
                                    <label class="form-check-label flex-fill text-truncate" for="perm_${targetModalId}_${p.id}" title="${p.name}">
                                        ${p.name}
                                    </label>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // Toggle Group Checkbox
    window.toggleGroupCheck = function (checkEl, containerId, groupIdx) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const groupRow = container.querySelector(`.group-perm-rows[data-group-idx="${groupIdx}"]`);
        if (!groupRow) return;

        const checkboxes = groupRow.querySelectorAll('.perm-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkEl.checked;
        });
    };

    // Populate Roles Select
    function populateRoleOptions() {
        const createSelect = document.getElementById('createStaffRoleSelect');
        const editSelect = document.getElementById('editStaffRoleSelect');

        const optionsHtml = '<option value="">-- Chọn chức vụ --</option>' +
            rolesData.map(r => `<option value="${r.name}">${r.name}</option>`).join('');

        if (createSelect) createSelect.innerHTML = optionsHtml;
        if (editSelect) editSelect.innerHTML = optionsHtml;
    }

    // 4. Handle Create Staff Form Submit
    const createStaffForm = document.getElementById('createStaffForm');
    if (createStaffForm) {
        createStaffForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const payload = {
                name: document.getElementById('createStaffName').value.trim(),
                email: document.getElementById('createStaffEmail').value.trim(),
                phone: document.getElementById('createStaffPhone').value.trim(),
                password: document.getElementById('createStaffPassword').value,
                role_name: document.getElementById('createStaffRoleSelect').value,
                status: document.getElementById('createStaffStatusSelect').value,
            };

            fetch(staffUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✓ ' + data.message);
                    const modalEl = document.getElementById('createStaffModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    createStaffForm.reset();
                    loadRolesData();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi tạo tài khoản.');
                }
            })
            .catch(err => console.error(err));
        });
    }

    // 5. Open Edit Staff Modal
    window.openEditStaffModal = function (staffId) {
        const user = usersData.find(u => u.id === staffId);
        if (!user) return;

        document.getElementById('editStaffId').value = user.id;
        document.getElementById('editStaffName').value = user.name;
        document.getElementById('editStaffEmail').value = user.email;
        document.getElementById('editStaffPhone').value = user.phone || '';
        document.getElementById('editStaffRoleSelect').value = user.role || '';
        document.getElementById('editStaffStatusSelect').value = user.status || 'active';

        const modalEl = document.getElementById('editStaffModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    // Handle Edit Staff Form Submit
    const editStaffForm = document.getElementById('editStaffForm');
    if (editStaffForm) {
        editStaffForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const staffId = document.getElementById('editStaffId').value;
            const payload = {
                name: document.getElementById('editStaffName').value.trim(),
                email: document.getElementById('editStaffEmail').value.trim(),
                phone: document.getElementById('editStaffPhone').value.trim(),
                role_name: document.getElementById('editStaffRoleSelect').value,
                status: document.getElementById('editStaffStatusSelect').value,
            };

            fetch(`${staffUrl}/${staffId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✓ ' + data.message);
                    const modalEl = document.getElementById('editStaffModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    loadRolesData();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi cập nhật.');
                }
            })
            .catch(err => console.error(err));
        });
    }

    // 6. Open Reset Password Modal
    window.openResetPasswordModal = function (staffId, staffName) {
        document.getElementById('resetStaffId').value = staffId;
        document.getElementById('resetStaffNameDisplay').innerText = staffName;
        document.getElementById('resetStaffPassword').value = '';

        const modalEl = document.getElementById('resetPasswordModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    // Handle Reset Password Form Submit
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const staffId = document.getElementById('resetStaffId').value;
            const password = document.getElementById('resetStaffPassword').value;

            fetch(`${staffUrl}/${staffId}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ password: password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✓ ' + data.message);
                    const modalEl = document.getElementById('resetPasswordModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    resetPasswordForm.reset();
                } else {
                    alert(data.message || 'Có lỗi xảy ra.');
                }
            })
            .catch(err => console.error(err));
        });
    }

    // 7. Delete Staff Account
    window.deleteStaff = function (staffId, staffName) {
        if (!confirm(`Bạn có chắc chắn muốn xóa nhân viên "${staffName}" không?`)) return;

        fetch(`${staffUrl}/${staffId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            loadRolesData();
        })
        .catch(err => console.error(err));
    };

    // 8. Create Role Form Submit
    const createRoleForm = document.getElementById('createRoleForm');
    if (createRoleForm) {
        createRoleForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const roleNameInput = document.getElementById('createRoleNameInput');
            const roleName = roleNameInput ? roleNameInput.value.trim() : '';

            const selectedPerms = [];
            const checkedBoxes = document.querySelectorAll('#createRolePermissionsContainer .perm-checkbox:checked');
            checkedBoxes.forEach(cb => selectedPerms.push(cb.value));

            if (!roleName) {
                alert('Vui lòng nhập tên chức vụ!');
                return;
            }

            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    name: roleName,
                    permissions: selectedPerms
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.data) {
                    alert('✓ Tạo chức vụ mới thành công!');
                    const modalEl = document.getElementById('createRoleModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    createRoleForm.reset();
                    loadRolesData();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi tạo chức vụ.');
                }
            })
            .catch(err => console.error(err));
        });
    }

    // 9. Open Edit Role Modal
    window.openEditRoleModal = function (roleId) {
        const role = rolesData.find(r => r.id === roleId);
        if (!role) return;

        document.getElementById('editRoleIdInput').value = role.id;
        document.getElementById('editRoleNameInput').value = role.name;

        const isProtected = ['super-admin', 'admin', 'seller', 'customer'].includes(role.name);
        document.getElementById('editRoleNameInput').disabled = isProtected;

        renderPermissionMatrix('editRolePermissionsContainer');

        const activePermNames = role.permissions ? role.permissions.map(p => p.name) : [];
        const editContainer = document.getElementById('editRolePermissionsContainer');
        const checkboxes = editContainer.querySelectorAll('.perm-checkbox');

        checkboxes.forEach(cb => {
            cb.checked = activePermNames.includes(cb.value);
        });

        const modalEl = document.getElementById('editRoleModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    // Handle Edit Role Form Submit
    const editRoleForm = document.getElementById('editRoleForm');
    if (editRoleForm) {
        editRoleForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const roleId = document.getElementById('editRoleIdInput').value;
            const roleName = document.getElementById('editRoleNameInput').value.trim();

            const selectedPerms = [];
            const checkedBoxes = document.querySelectorAll('#editRolePermissionsContainer .perm-checkbox:checked');
            checkedBoxes.forEach(cb => selectedPerms.push(cb.value));

            fetch(`${storeUrl}/${roleId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    name: roleName,
                    permissions: selectedPerms
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.data) {
                    alert('✓ Cập nhật phân quyền chức vụ thành công!');
                    const modalEl = document.getElementById('editRoleModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    loadRolesData();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi cập nhật.');
                }
            })
            .catch(err => console.error(err));
        });
    }

    // 10. Delete Role
    window.deleteRole = function (roleId, roleName) {
        if (!confirm(`Bạn có chắc chắn muốn xóa chức vụ "${roleName}" không?`)) return;

        fetch(`${storeUrl}/${roleId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            loadRolesData();
        })
        .catch(err => console.error(err));
    };
});
