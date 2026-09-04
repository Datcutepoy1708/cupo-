{{-- ===== BANNER GIỮA TRANG CHỦ (Giữa Danh Mục Nổi Bật & Flash Sale) ===== --}}
@if (isset($midBanners) && $midBanners->isNotEmpty())
    <section class="homepage-mid-banner pb-5">
        <div class="container">
            @php
                $bannerCount = $midBanners->count();
                $colClass = match (true) {
                    $bannerCount === 1 => 'col-12',
                    $bannerCount === 2 => 'col-12 col-md-6',
                    $bannerCount === 3 => 'col-12 col-md-4',
                    default => 'col-12 col-sm-6 col-lg-3',
                };
            @endphp
            <div class="row g-3 g-md-4">
                @foreach ($midBanners as $banner)
                    @php
                        $imgPath = !empty($banner->image_url) ? $banner->image_url : (
                            \Illuminate\Support\Str::startsWith($banner->image_path, ['http://', 'https://', '//'])
                                ? $banner->image_path
                                : asset('storage/' . ltrim(explode('/storage/', $banner->image_path)[1] ?? $banner->image_path, '/'))
                        );
                    @endphp
                    <div class="{{ $colClass }}">
                        <a href="{{ $banner->link_url ?: 'javascript:void(0)' }}"
                           class="mid-banner-card d-block position-relative text-decoration-none"
                           title="{{ $banner->title }}"
                           @if(empty($banner->link_url)) onclick="return false;" style="cursor: default;" @endif>
                            <div class="mid-banner-inner overflow-hidden rounded-3 shadow-sm {{ $bannerCount === 1 ? 'is-single' : '' }}">
                                <img src="{{ $imgPath }}"
                                     alt="{{ $banner->title }}"
                                     class="w-100 mid-banner-img {{ $bannerCount === 1 ? 'single-img' : '' }}"
                                     loading="lazy">
                                <div class="mid-banner-gloss"></div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
