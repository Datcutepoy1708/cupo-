{{-- Tab 5: Cấu hình Email SMTP --}}
<div class="settings-tab-pane" id="tab-mail">
    <div class="settings-section-header">
        <h5 class="settings-section-title">
            <i class="fa-solid fa-envelope-open-text text-danger me-2"></i>Cấu Hình Email (SMTP)
        </h5>
        <p class="settings-section-desc">Cấu hình máy chủ gửi email tự động (xác thực tài khoản, thông báo đơn hàng, reset mật khẩu).</p>
    </div>

    <div class="row g-4">
        {{-- Mailer & Host & Port --}}
        <div class="col-md-4">
            <label class="form-label fw-bold" for="mail_mailer">Giao thức gửi (Driver)</label>
            <select class="form-select" id="mail_mailer" name="mail_mailer">
                <option value="smtp" {{ (old('mail_mailer', $settings['mail_mailer'] ?? 'smtp') === 'smtp') ? 'selected' : '' }}>SMTP</option>
                <option value="sendmail" {{ (old('mail_mailer', $settings['mail_mailer'] ?? '') === 'sendmail') ? 'selected' : '' }}>Sendmail</option>
                <option value="log" {{ (old('mail_mailer', $settings['mail_mailer'] ?? '') === 'log') ? 'selected' : '' }}>Log (Thử nghiệm)</option>
            </select>
        </div>

        <div class="col-md-5">
            <label class="form-label fw-bold" for="mail_host">Máy chủ SMTP (Host)</label>
            <input type="text" class="form-control" id="mail_host" name="mail_host"
                   value="{{ old('mail_host', $settings['mail_host'] ?? 'smtp.gmail.com') }}" placeholder="VD: smtp.gmail.com">
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold" for="mail_port">Cổng (Port)</label>
            <input type="number" class="form-control" id="mail_port" name="mail_port"
                   value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" placeholder="VD: 587 hoặc 465">
        </div>

        {{-- Username, Password, Encryption --}}
        <div class="col-md-4">
            <label class="form-label fw-bold" for="mail_username">Tài khoản SMTP (Username)</label>
            <input type="text" class="form-control" id="mail_username" name="mail_username"
                   value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" placeholder="Email hoặc Username">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold" for="mail_password">Mật khẩu SMTP (Password)</label>
            <div class="input-group">
                <input type="password" class="form-control password-toggle-input" id="mail_password" name="mail_password"
                       value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" placeholder="Mật khẩu ứng dụng">
                <button class="btn btn-outline-secondary btn-toggle-password" type="button" title="Hiện/Ẩn">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold" for="mail_encryption">Mã hóa (Encryption)</label>
            <select class="form-select" id="mail_encryption" name="mail_encryption">
                <option value="tls" {{ (old('mail_encryption', $settings['mail_encryption'] ?? 'tls') === 'tls') ? 'selected' : '' }}>TLS</option>
                <option value="ssl" {{ (old('mail_encryption', $settings['mail_encryption'] ?? '') === 'ssl') ? 'selected' : '' }}>SSL</option>
                <option value="" {{ (old('mail_encryption', $settings['mail_encryption'] ?? '') === '') ? 'selected' : '' }}>Không mã hóa (None)</option>
            </select>
        </div>

        <hr class="my-2">

        {{-- From address & From name --}}
        <div class="col-md-6">
            <label class="form-label fw-bold" for="mail_from_address">Email người gửi (From Address)</label>
            <input type="email" class="form-control" id="mail_from_address" name="mail_from_address"
                   value="{{ old('mail_from_address', $settings['mail_from_address'] ?? 'noreply@cupo.vn') }}" placeholder="noreply@cupo.vn">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="mail_from_name">Tên người gửi (From Name)</label>
            <input type="text" class="form-control" id="mail_from_name" name="mail_from_name"
                   value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'Cupo Marketplace') }}" placeholder="Cupo Marketplace">
        </div>

        {{-- Khung Test gửi mail --}}
        <div class="col-12 mt-4">
            <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold mb-2 text-dark"><i class="fa-solid fa-paper-plane me-1 text-primary"></i>Kiểm tra kết nối gửi email</h6>
                <p class="text-muted small mb-3">Nhập địa chỉ email để hệ thống gửi 1 bức thư thử nghiệm kiểm tra xem thông tin SMTP đã hoạt động chưa.</p>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="email" class="form-control" id="testEmailInput" style="max-width: 320px;" placeholder="Nhập email nhận thử nghiệm...">
                    <button type="button" class="btn btn-outline-primary" id="btnSendTestMail" data-url="{{ route('admin.settings.test-mail') }}">
                        <i class="fa-solid fa-paper-plane me-1"></i> Gửi thử nghiệm
                    </button>
                </div>
                <div id="testMailResult" class="mt-2 small"></div>
            </div>
        </div>
    </div>
</div>
