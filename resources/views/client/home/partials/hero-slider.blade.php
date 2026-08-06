<section class="hero-slider">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">

        {{-- Chấm điều hướng --}}
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"
                aria-current="true"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>

        <div class="carousel-inner">

            {{-- Slide 1 --}}
            <div class="carousel-item active">
                <div class="hero-slide slide-1 text-white">
                    <div class="container py-5">
                        <div class="row align-items-center py-4">
                            <div class="col-lg-6">
                                <h1 class="display-4 fw-bold mb-3">Mua sắm dễ dàng<br>giá tốt mỗi ngày</h1>
                                <p class="fs-5 mb-4 opacity-75">Hàng ngàn sản phẩm chính hãng, freeship toàn quốc cho
                                    đơn từ 200k.</p>
                                <a href="#" class="btn btn-light btn-lg text-danger fw-semibold px-4">Mua ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slide 2 --}}
            <div class="carousel-item">
                <div class="hero-slide slide-2 text-white">
                    <div class="container py-5">
                        <div class="row align-items-center py-4">
                            <div class="col-lg-6">
                                <h1 class="display-4 fw-bold mb-3">Điện thoại, laptop<br>giảm đến 40%</h1>
                                <p class="fs-5 mb-4 opacity-75">Trả góp 0%, bảo hành chính hãng 12 tháng.</p>
                                <a href="#" class="btn btn-light btn-lg text-danger fw-semibold px-4">Khám phá
                                    ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slide 3 --}}
            <div class="carousel-item">
                <div class="hero-slide slide-3 text-white">
                    <div class="container py-5">
                        <div class="row align-items-center py-4">
                            <div class="col-lg-6">
                                <h1 class="display-4 fw-bold mb-3">Bộ sưu tập mới<br>xu hướng 2026</h1>
                                <p class="fs-5 mb-4 opacity-75">Miễn phí đổi trả trong 7 ngày cho mọi đơn hàng.</p>
                                <a href="#" class="btn btn-light btn-lg text-danger fw-semibold px-4">Mua ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Nút chuyển slide --}}
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-icon" aria-hidden="true">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-icon" aria-hidden="true">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        </button>
    </div>
</section>
