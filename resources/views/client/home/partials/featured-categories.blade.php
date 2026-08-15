<section class="py-5">
    <div class="container">
        <h2 class="h4 fw-bold mb-4">Danh mục nổi bật</h2>

        <div class="fcat-scroll-wrap">
            <div class="fcat-inner" id="fcatInner">
                @forelse ($featuredCategories as $cat)
                    @php
                        $imgUrl = null;
                        if (!empty($cat->image)) {
                            $rawPath = $cat->image;
                            if (\Illuminate\Support\Str::startsWith($rawPath, ['http://', 'https://'])) {
                                $imgUrl = $rawPath;
                            } elseif (\Illuminate\Support\Str::contains($rawPath, '/storage/')) {
                                $imgUrl = asset('storage/' . explode('/storage/', $rawPath)[1]);
                            } else {
                                $imgUrl = asset('storage/' . ltrim($rawPath, '/'));
                            }
                        }
                        $icons = [
                            'điện tử' => 'fa-mobile-screen',
                            'laptop' => 'fa-laptop',
                            'máy tính' => 'fa-desktop',
                            'thời trang' => 'fa-shirt',
                            'gia dụng' => 'fa-blender',
                            'làm đẹp' => 'fa-spray-can-sparkles',
                            'mỹ phẩm' => 'fa-face-smile-beam',
                            'mẹ' => 'fa-baby-carriage',
                            'thể thao' => 'fa-dumbbell',
                            'sách' => 'fa-book-open',
                            'xe' => 'fa-car',
                            'đồng hồ' => 'fa-clock',
                            'túi' => 'fa-bag-shopping',
                            'giày' => 'fa-shoe-prints',
                            'sức khỏe' => 'fa-heart-pulse',
                            'thực phẩm' => 'fa-bowl-food',
                        ];
                        $icon = 'fa-tag';
                        foreach ($icons as $kw => $ic) {
                            if (str_contains(mb_strtolower($cat->name), $kw)) {
                                $icon = $ic;
                                break;
                            }
                        }
                    @endphp
                    <a href="{{ url('/categories/' . $cat->slug) }}" class="fcat-item text-decoration-none text-dark">
                        <div class="category-icon mx-auto mb-2 d-flex align-items-center justify-content-center">
                            @if ($imgUrl)
                                <img src="{{ $imgUrl }}" alt="{{ $cat->name }}" loading="lazy"
                                    class="w-100 h-100 object-fit-cover rounded-circle">
                            @else
                                <i class="fa-solid {{ $icon }}"></i>
                            @endif
                        </div>
                        <span class="small fw-medium">{{ $cat->name }}</span>
                    </a>
                @empty
                    <p class="text-muted small px-3 py-2">Chưa có danh mục.</p>
                @endforelse
                <a href="{{ url('/categories') }}" class="fcat-item fcat-item-all text-decoration-none text-dark">
                    <div
                        class="category-icon category-icon-all mx-auto mb-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <span class="small fw-medium">Xem tất cả</span>
                </a>
            </div>

            <div class="fcat-track">
                <div class="fcat-thumb" id="fcatThumb"></div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.getElementById('fcatInner');
        const thumb = document.getElementById('fcatThumb');
        if (!track || !thumb) return;

        function updateThumb() {
            const ratio = track.clientWidth / track.scrollWidth;
            const thumbWidth = Math.max(ratio * 100, 10);
            const maxScroll = track.scrollWidth - track.clientWidth;
            const scrollRatio = maxScroll > 0 ? track.scrollLeft / maxScroll : 0;
            const maxThumbLeft = 100 - thumbWidth;

            thumb.style.width = thumbWidth + '%';
            thumb.style.left = (scrollRatio * maxThumbLeft) + '%';
        }

        track.addEventListener('scroll', updateThumb);
        window.addEventListener('resize', updateThumb);
        updateThumb();

        // Cuộn chuột (wheel) -> cuộn ngang
        track.addEventListener('wheel', function(e) {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                e.preventDefault();
                track.scrollLeft += e.deltaY;
            }
        }, {
            passive: false
        });

        let isDragging = false;
        let startX = 0;
        let startScrollLeft = 0;

        thumb.addEventListener('mousedown', function(e) {
            isDragging = true;
            startX = e.clientX;
            startScrollLeft = track.scrollLeft;
            e.preventDefault();
        });

        document.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            const trackEl = thumb.parentElement;
            const deltaX = e.clientX - startX;
            const scrollableWidth = track.scrollWidth - track.clientWidth;
            const trackWidth = trackEl.clientWidth;
            track.scrollLeft = startScrollLeft + (deltaX / trackWidth) * scrollableWidth * (trackWidth /
                (trackWidth - thumb.clientWidth));
        });

        document.addEventListener('mouseup', function() {
            isDragging = false;
        });
    });
</script>