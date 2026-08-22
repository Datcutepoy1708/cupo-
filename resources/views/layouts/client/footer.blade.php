<footer class="site-footer">
    <!-- Top Footer: Multi-column Information -->
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                
                <!-- Cột 1: Thông tin thương hiệu & Công ty chủ quản -->
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('home') }}" class="footer-brand-title">
                        {{ setting('site_name', 'Cupo') }}
                    </a>
                    <p class="footer-slogan">
                        {{ setting('footer_slogan', 'Cupo — Nền tảng sàn thương mại điện tử mua sắm trực tuyến hàng đầu, kết nối hàng triệu người mua và người bán uy tín trên toàn quốc.') }}
                    </p>

                    <div class="contact-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div>
                            <strong>Trụ sở:</strong> 
                            <span>{{ setting('contact_address', 'Tầng 12, Tòa nhà Cupo Tower, Cầu Giấy, Hà Nội') }}</span>
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fa-solid fa-phone"></i>
                        <div>
                            <strong>Hotline:</strong> 
                            <a href="tel:{{ setting('contact_phone', '1900 8888') }}" class="text-decoration-none text-danger fw-bold">
                                {{ setting('contact_phone', '1900 8888') }}
                            </a>
                            <span class="text-muted small ms-1">({{ setting('working_hours', '08:00 - 22:00 hàng ngày') }})</span>
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fa-solid fa-envelope"></i>
                        <div>
                            <strong>Email:</strong> 
                            <a href="mailto:{{ setting('contact_email', 'support@cupo.vn') }}" class="text-decoration-none text-dark">
                                {{ setting('contact_email', 'support@cupo.vn') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cột 2: Chăm sóc khách hàng -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Chăm sóc khách hàng</h6>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Trung tâm trợ giúp</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Hướng dẫn mua hàng</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Hướng dẫn bán hàng</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Chính sách bảo hành</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Trả hàng & Hoàn tiền</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Chăm sóc khách hàng</a></li>
                    </ul>
                </div>

                <!-- Cột 3: Về Cupo -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Về Cupo Việt Nam</h6>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Giới thiệu về Cupo</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Tuyển dụng nhân tài</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Điều khoản sử dụng</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Chính sách bảo mật</a></li>
                        <li><a href="{{ route('seller.register') }}"><i class="fa-solid fa-angle-right me-1 small"></i>Kênh Người Bán</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right me-1 small"></i>Liên hệ truyền thông</a></li>
                    </ul>
                </div>

                <!-- Cột 4: Thanh toán, Vận chuyển & Chứng nhận -->
                <div class="col-lg-4 col-md-6">
                    <!-- Thanh toán -->
                    <h6 class="footer-heading">Phương thức thanh toán</h6>
                    <div class="footer-badge-grid">
                        <span class="footer-badge-item"><i class="fa-solid fa-money-bill-wave text-success"></i> Tiền mặt (COD)</span>
                        <span class="footer-badge-item"><i class="fa-solid fa-qrcode text-primary"></i> VNPAY-QR</span>
                        <span class="footer-badge-item"><i class="fa-solid fa-wallet text-danger"></i> Ví MoMo</span>
                        <span class="footer-badge-item"><i class="fa-brands fa-cc-visa text-primary"></i> Visa / Master</span>
                    </div>

                    <!-- Vận chuyển -->
                    <h6 class="footer-heading">Đối tác vận chuyển</h6>
                    <div class="footer-badge-grid">
                        <span class="footer-badge-item"><i class="fa-solid fa-truck-fast text-danger"></i> GHN Express</span>
                        <span class="footer-badge-item"><i class="fa-solid fa-bolt text-danger"></i> Viettel Post</span>
                        <span class="footer-badge-item"><i class="fa-solid fa-cube text-dark"></i> Ninja Van</span>
                        <span class="footer-badge-item"><i class="fa-solid fa-paper-plane text-danger"></i> J&T Express</span>
                    </div>

                    <!-- Kết nối & Chứng nhận -->
                    <h6 class="footer-heading">Kết nối với chúng tôi</h6>
                    <div class="footer-social-links">
                        @if(setting('social_facebook'))
                            <a href="{{ setting('social_facebook') }}" target="_blank" class="footer-social-btn" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        @else
                            <a href="https://facebook.com" target="_blank" class="footer-social-btn" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        @endif

                        @if(setting('social_tiktok'))
                            <a href="{{ setting('social_tiktok') }}" target="_blank" class="footer-social-btn" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                        @else
                            <a href="https://tiktok.com" target="_blank" class="footer-social-btn" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                        @endif

                        @if(setting('social_youtube'))
                            <a href="{{ setting('social_youtube') }}" target="_blank" class="footer-social-btn" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        @else
                            <a href="https://youtube.com" target="_blank" class="footer-social-btn" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        @endif

                        @if(setting('social_zalo'))
                            <a href="{{ setting('social_zalo') }}" target="_blank" class="footer-social-btn" title="Zalo"><i class="fa-solid fa-comment-dots"></i></a>
                        @endif
                    </div>

                    <!-- Chứng nhận BCT & DMCA -->
                    <div class="d-flex flex-wrap align-items-center">
                        @if(setting('bct_registered', '1') == '1')
                            <span class="footer-cert-badge cert-bct">
                                <i class="fa-solid fa-shield-halved"></i> Đã đăng ký Bộ Công Thương
                            </span>
                        @endif

                        @if(setting('dmca_protected', '1') == '1')
                            <span class="footer-cert-badge cert-dmca">
                                <i class="fa-solid fa-lock"></i> DMCA Protected
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bottom Footer: Company Legal & Copyright -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <div class="footer-company-meta">
                        <div><strong>{{ setting('company_name', 'Công ty Cổ phần Thương Mại Điện Tử Cupo Việt Nam') }}</strong></div>
                        <div>{{ setting('business_license', 'Mã số doanh nghiệp: 0109876543 do Sở Kế hoạch & Đầu tư TP. Hà Nội cấp lần đầu ngày 15/08/2024') }}</div>
                        <div class="text-muted" style="font-size: 11px;">Địa chỉ nhận hàng đổi trả: {{ setting('contact_address', 'Tầng 12, Tòa nhà Cupo Tower, Cầu Giấy, Hà Nội') }}</div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end text-muted">
                    <div>{{ setting('copyright_text', '© 2026 Cupo. Tất cả quyền được bảo lưu.') }}</div>
                    <div class="small">Quốc gia & Khu vực: <strong>Việt Nam</strong></div>
                </div>
            </div>
        </div>
    </div>
</footer>
