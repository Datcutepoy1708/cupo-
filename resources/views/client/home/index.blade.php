@extends('layouts.client.app')
@section('content')
    {{-- ===== HERO SLIDER ===== --}}
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
    <section class="py-5">
        <div class="container">
            <h2 class="h4 fw-bold mb-4">Danh mục nổi bật</h2>
            <div class="row g-3 text-center">
                <div class="col-4 col-md-2">
                    <a href="#" class="text-decoration-none text-dark">
                        <div class="category-icon mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </div>
                        <span class="small fw-medium">Đồ điện tử</span>
                    </a>
                </div>
                <div class="col-4 col-md-2">
                    <a href="#" class="text-decoration-none text-dark">
                        <div class="category-icon mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-laptop"></i>
                        </div>
                        <span class="small fw-medium">Máy tính</span>
                    </a>
                </div>
                <div class="col-4 col-md-2">
                    <a href="#" class="text-decoration-none text-dark">
                        <div class="category-icon mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-shirt"></i>
                        </div>
                        <span class="small fw-medium">Thời trang</span>
                    </a>
                </div>
                <div class="col-4 col-md-2">
                    <a href="#" class="text-decoration-none text-dark">
                        <div class="category-icon mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-house"></i>
                        </div>
                        <span class="small fw-medium">Đồ gia dụng</span>
                    </a>
                </div>
                <div class="col-4 col-md-2">
                    <a href="#" class="text-decoration-none text-dark">
                        <div class="category-icon mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-spray-can-sparkles"></i>
                        </div>
                        <span class="small fw-medium">Làm đẹp</span>
                    </a>
                </div>
                <div class="col-4 col-md-2">
                    <a href="#" class="text-decoration-none text-dark">
                        <div class="category-icon mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-baby-carriage"></i>
                        </div>
                        <span class="small fw-medium">Mẹ & bé</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
