{{-- ===== BANNER SIDEBAR KHUYẾN MÃI (Vị trí Sidebar) ===== --}}
@if (isset($sidebarBanners) && $sidebarBanners->isNotEmpty())
    <div class="cat-sidebar-banners-card p-3 rounded-3 shadow-sm bg-white mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <span class="fw-bold text-dark small text-uppercase letter-spacing-1">
                <i class="fa-solid fa-fire text-danger me-1"></i> Ưu Đãi Nổi Bật
            </span>
            <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 11px;">Hot Deal</span>
        </div>

        <div class="d-flex flex-column gap-3">
            @foreach ($sidebarBanners as $banner)
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

                <a href="{{ $banner->link_url ?: 'javascript:void(0)' }}"
                   class="cat-sidebar-banner-item d-block position-relative text-decoration-none"
                   title="{{ $banner->title }}"
                   @if(empty($banner->link_url)) onclick="return false;" style="cursor: default;" @endif>
                    <div class="cat-sidebar-banner-inner overflow-hidden rounded-3 position-relative">
                        <img src="{{ $imgPath }}"
                             alt="{{ $banner->title }}"
                             class="w-100 cat-sidebar-banner-img"
                             loading="lazy">
                        <div class="cat-sidebar-banner-gloss"></div>

                        @if (!empty($banner->title))
                            <div class="cat-sidebar-banner-footer p-2">
                                <span class="cat-sidebar-banner-title text-truncate d-block fw-semibold text-dark small">
                                    {{ $banner->title }}
                                </span>
                                @if (!empty($banner->link_url))
                                    <span class="text-danger small fw-semibold cat-sidebar-cta">
                                        Khám phá ngay <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
