{{-- ===== TAB: THÔNG TIN CÁ NHÂN ===== --}}
<div class="tab-pane fade {{ $activeTab === 'personal' ? 'show active' : '' }}" id="personal" role="tabpanel">
    <div class="content-card">
        <h2 class="content-title">Thông tin cá nhân</h2>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check me-2"></i>Cập nhật thông tin thành công!
            </div>
        @endif

        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profile-form">
            @csrf
            @method('PATCH')
            <input type="hidden" name="active_tab" value="personal">

            {{-- Upload avatar --}}
            <div class="avatar-upload-section">
                <div class="avatar-preview-container" onclick="document.getElementById('avatar-input').click()">
                    <img src="{{ asset('https://picsum.photos/1600/700') }}" alt="Avatar" class="avatar-preview"
                        id="avatar-preview">
                    <div class="camera-overlay">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/jpg"
                        style="display:none;">
                </div>
                <div class="avatar-upload-controls">
                    <h5><i class="fa-solid fa-circle-user me-2"></i>Ảnh đại diện</h5>
                    <p class="text-muted mb-2">Nhấn vào ảnh để thay đổi</p>
                    <p class="text-muted small mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>Định dạng: JPG, PNG | Tối đa: 5MB
                    </p>
                    <span class="file-name-display" id="file-name">
                        <i class="fa-solid fa-file-image me-1"></i>Chưa chọn file
                    </span>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        value="{{ old('name', auth()->user()->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                        name="date_of_birth"
                        value="{{ old('date_of_birth', auth()->user()->date_of_birth?->format('d/m/Y')) }}">
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                        value="{{ old('email', auth()->user()->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số điện thoại</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone"
                        value="{{ old('phone', auth()->user()->phone ?? '') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-save"><i class="fa-solid fa-floppy-disk me-2"></i>Cập
                    nhật</button>
                <button type="reset" class="btn btn-cancel"><i class="fa-solid fa-xmark me-2"></i>Hủy</button>
            </div>
        </form>
    </div>
</div>
