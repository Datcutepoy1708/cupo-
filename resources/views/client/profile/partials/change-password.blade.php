{{-- ===== TAB: ĐỔI MẬT KHẨU ===== --}}
<div class="tab-pane fade {{ $activeTab === 'changePassword' ? 'show active' : '' }}" id="changePassword" role="tabpanel">
    <div class="content-card">
        <h2 class="content-title">Đổi mật khẩu</h2>

        @if (session('status') === 'password-updated')
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check me-2"></i>Đổi mật khẩu thành công!
            </div>
        @endif

        <form class="change-pass" method="post" action="{{ route('profile.password.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" value="changePassword">

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="password"
                            class="form-control password-field @error('current_password') is-invalid @enderror"
                            name="current_password" placeholder="Nhập mật khẩu cũ">
                        <i class="fa-solid fa-eye toggle-password"></i>
                    </div>
                    @error('current_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="password"
                            class="form-control password-field @error('new_password') is-invalid @enderror"
                            name="new_password" placeholder="Nhập mật khẩu mới">
                        <i class="fa-solid fa-eye toggle-password"></i>
                    </div>
                    @error('new_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="password" class="form-control password-field" name="new_password_confirmation"
                            placeholder="Nhập lại mật khẩu mới">
                        <i class="fa-solid fa-eye toggle-password"></i>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-save"><i class="fa-solid fa-key me-2"></i>Lưu thay đổi</button>
                <button type="reset" class="btn btn-cancel"><i class="fa-solid fa-xmark me-2"></i>Hủy</button>
            </div>
        </form>
    </div>
</div>
