{{-- ===== BANNER ĐẦU TRANG DANH MỤC (Vị trí category_top) ===== --}}
@php
    $banners = isset($categoryBanners) && $categoryBanners->isNotEmpty()
        ? $categoryBanners
        : (isset($categoryBanner) && $categoryBanner ? collect([$categoryBanner]) : collect());
    $bannerCount = $banners->count();
@endphp

@if ($bannerCount > 0)
    <div class="cat-top-banner mb-4">
        @if ($bannerCount === 1)
            {{-- ===== TRƯỜNG HỢP 1: Chỉ có 1 banner -> Hiển thị dạng ảnh đơn tĩnh (không trượt, không nút) ===== --}}
            @php
                $banner = $banners->first();
                $imgPath = !empty($banner->image_url) ? $banner->image_url : (
                    \Illuminate\Support\Str::startsWith($banner->image_path, ['http://', 'https://', '//'])
                        ? $banner->image_path
                        : asset('storage/' . ltrim(explode('/storage/', $banner->image_path)[1] ?? $banner->image_path, '/'))
                );
            @endphp
            <a href="{{ $banner->link_url ?: 'javascript:void(0)' }}"
               class="cat-top-banner-card d-block position-relative text-decoration-none"
               title="{{ $banner->title }}"
               @if(empty($banner->link_url)) onclick="return false;" style="cursor: default;" @endif>
                <div class="cat-top-banner-inner overflow-hidden rounded-3 shadow-sm position-relative">
                    <img src="{{ $imgPath }}"
                         alt="{{ $banner->title }}"
                         class="w-100 cat-top-banner-img"
                         loading="lazy">
                    <div class="cat-top-banner-gloss"></div>
                </div>
            </a>
        @else
            {{-- ===== TRƯỜNG HỢP 2: Có nhiều banner (> 1) -> Hiển thị dạng Carousel Slider chuyển động ===== --}}
            <div id="categoryTopCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500">
                {{-- Chấm điều hướng (indicators) --}}
                <div class="carousel-indicators">
                    @foreach ($banners as $index => $banner)
                        <button type="button"
                                data-bs-target="#categoryTopCarousel"
                                data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }}"
                                aria-current="{{ $index === 0 ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>

                {{-- Danh sách các Slide --}}
                <div class="carousel-inner cat-top-banner-inner rounded-3 shadow-sm overflow-hidden">
                    @foreach ($banners as $index => $banner)
                        @php
                            $imgPath = !empty($banner->image_url) ? $banner->image_url : (
                                \Illuminate\Support\Str::startsWith($banner->image_path, ['http://', 'https://', '//'])
                                    ? $banner->image_path
                                    : asset('storage/' . ltrim(explode('/storage/', $banner->image_path)[1] ?? $banner->image_path, '/'))
                            );
                        @endphp
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <a href="{{ $banner->link_url ?: 'javascript:void(0)' }}"
                               class="cat-top-banner-card d-block position-relative text-decoration-none"
                               title="{{ $banner->title }}"
                               @if(empty($banner->link_url)) onclick="return false;" style="cursor: default;" @endif>
                                <img src="{{ $imgPath }}"
                                     alt="{{ $banner->title }}"
                                     class="w-100 cat-top-banner-img"
                                     loading="lazy">
                                <div class="cat-top-banner-gloss"></div>
                                @if(!empty($banner->title))
                                    <div class="cat-top-banner-caption position-absolute bottom-0 start-0 end-0 p-3"
                                         style="background: linear-gradient(transparent, rgba(0,0,0,0.65));">
                                        <span class="text-white fw-semibold small">{{ $banner->title }}</span>
                                    </div>
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>

                {{-- Nút mũi tên chuyển slide Trái / Phải --}}
                <button class="carousel-control-prev" type="button" data-bs-target="#categoryTopCarousel" data-bs-slide="prev">
                    <span class="carousel-control-icon" aria-hidden="true">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#categoryTopCarousel" data-bs-slide="next">
                    <span class="carousel-control-icon" aria-hidden="true">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                </button>
            </div>
        @endif
    </div>
@endif
