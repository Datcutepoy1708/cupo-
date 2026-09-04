<section class="hero-slider">
    @if(isset($heroBanners) && $heroBanners->count() > 0)

        @if($heroBanners->count() === 1)
            {{-- ===== TRƯỜNG HỢP 1: Chỉ có 1 banner -> Hiển thị 1 banner đơn tĩnh (không trượt, không nút) ===== --}}
            @php
                $banner = $heroBanners->first();
                $rawPath = $banner->image_path ?? '';
                if (!empty($banner->image_url)) {
                    $imgPath = $banner->image_url;
                } elseif (\Illuminate\Support\Str::contains($rawPath, '/storage/')) {
                    $imgPath = asset('storage/' . explode('/storage/', $rawPath)[1]);
                } elseif (\Illuminate\Support\Str::startsWith($rawPath, ['http://', 'https://', '/'])) {
                    $imgPath = $rawPath;
                } else {
                    $imgPath = asset('storage/' . $rawPath);
                }
            @endphp
            <div class="hero-single-banner overflow-hidden" style="border-radius: 0;">
                <a href="{{ $banner->link_url ?: '#' }}"
                   class="d-block text-decoration-none"
                   @if(empty($banner->link_url)) onclick="return false;" style="cursor: default;" @endif>
                    <div class="hero-slide-dynamic">
                        <img src="{{ $imgPath }}" alt="{{ $banner->title }}">
                        <div class="hero-slide-overlay"></div>
                        <div class="hero-slide-content">
                            <h2 class="text-white fw-bold h3">{{ $banner->title }}</h2>
                            @if($banner->link_url)
                                <span class="btn btn-sm btn-light text-danger fw-semibold">Xem chi tiết <i class="fa-solid fa-arrow-right ms-1"></i></span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>

        @else
            {{-- ===== TRƯỜNG HỢP 2: Có nhiều banner (> 1) -> Hiển thị dạng Carousel Slider chuyển động ===== --}}
            <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">

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
                <div class="carousel-inner" style="border-radius: 0; overflow: hidden;">
                    @foreach($heroBanners as $index => $banner)
                        @php
                            $rawPath = $banner->image_path ?? '';
                            if (!empty($banner->image_url)) {
                                $imgPath = $banner->image_url;
                            } elseif (\Illuminate\Support\Str::contains($rawPath, '/storage/')) {
                                $imgPath = asset('storage/' . explode('/storage/', $rawPath)[1]);
                            } elseif (\Illuminate\Support\Str::startsWith($rawPath, ['http://', 'https://', '/'])) {
                                $imgPath = $rawPath;
                            } else {
                                $imgPath = asset('storage/' . $rawPath);
                            }
                        @endphp
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <a href="{{ $banner->link_url ?: '#' }}" class="d-block text-decoration-none"
                               @if(empty($banner->link_url)) onclick="return false;" style="cursor: default;" @endif>
                                <div class="hero-slide-dynamic">
                                    <img src="{{ $imgPath }}" alt="{{ $banner->title }}">
                                    <div class="hero-slide-overlay"></div>
                                    <div class="hero-slide-content">
                                        <h2 class="text-white fw-bold h3">{{ $banner->title }}</h2>
                                        @if($banner->link_url)
                                            <span class="btn btn-sm btn-light text-danger fw-semibold">Xem chi tiết <i class="fa-solid fa-arrow-right ms-1"></i></span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
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
        @endif
    @endif
</section>
