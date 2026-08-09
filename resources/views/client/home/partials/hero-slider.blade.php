<section class="hero-slider">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">

        @if(isset($heroBanners) && $heroBanners->count() > 0)

            {{-- Chấm điều hướng --}}
            <div class="carousel-indicators">
                @foreach($heroBanners as $index => $banner)
                    <button type="button"
                            data-bs-target="#heroCarousel"
                            data-bs-slide-to="{{ $index }}"
                            class="{{ $index === 0 ? 'active' : '' }}"
                            aria-current="{{ $index === 0 ? 'true' : 'false' }}"></button>
                @endforeach
            </div>

            {{-- Danh sách Slide Banner động từ Database --}}
            <div class="carousel-inner" style="border-radius: 12px; overflow: hidden;">
                @foreach($heroBanners as $index => $banner)
                    @php
                        $rawPath = $banner->image_path;
                        if (\Illuminate\Support\Str::contains($rawPath, '/storage/')) {
                            $imgPath = asset('storage/' . explode('/storage/', $rawPath)[1]);
                        } elseif (\Illuminate\Support\Str::startsWith($rawPath, ['http://', 'https://', '/'])) {
                            $imgPath = $rawPath;
                        } else {
                            $imgPath = asset('storage/' . $rawPath);
                        }
                    @endphp
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <a href="{{ $banner->link_url ?: '#' }}" class="d-block text-decoration-none">
                            <div class="hero-slide-dynamic position-relative" style="height: 380px; background: #111;">
                                <img src="{{ $imgPath }}" alt="{{ $banner->title }}"
                                     class="w-100 h-100" style="object-fit: cover; opacity: 0.9;">
                                <div class="position-absolute bottom-0 start-0 end-0 p-4" style="background: linear-gradient(transparent, rgba(0,0,0,0.75));">
                                    <h2 class="text-white fw-bold h3 mb-1">{{ $banner->title }}</h2>
                                    @if($banner->link_url)
                                        <span class="btn btn-sm btn-light text-danger fw-semibold mt-2">Xem chi tiết <i class="fa-solid fa-arrow-right ms-1"></i></span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

        @else

            {{-- Slide mặc định (mẫu) --}}
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
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
                                    <p class="fs-5 mb-4 opacity-75">Hàng ngàn sản phẩm chính hãng, freeship toàn quốc cho đơn từ 200k.</p>
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
                                    <a href="#" class="btn btn-light btn-lg text-danger fw-semibold px-4">Khám phá ngay</a>
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

        @endif

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
