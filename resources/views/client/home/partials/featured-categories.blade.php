{{-- ===== Featured Categories — Shopee compact style ===== --}}
<section class="fcat-section">
    <div class="container">
        <div class="fcat-box">
            <div class="fcat-box-header">
                <span class="fcat-box-title">DANH MỤC</span>
            </div>

            <div class="fcat-scroll-wrap">
                <div class="fcat-inner">
                    @forelse ($featuredCategories as $cat)
                        @php
                            $imgUrl = null;
                            if ($cat->image) {
                                $p = $cat->image;
                                $imgUrl = str_contains($p, '/storage/')
                                    ? '/storage/' . explode('/storage/', $p)[1]
                                    : '/storage/' . ltrim($p, '/');
                            }
                            $icons = [
                                'điện tử'  => 'fa-mobile-screen', 'laptop'    => 'fa-laptop',
                                'máy tính' => 'fa-desktop',       'thời trang'=> 'fa-shirt',
                                'gia dụng' => 'fa-blender',       'làm đẹp'  => 'fa-spray-can-sparkles',
                                'mỹ phẩm'  => 'fa-face-smile-beam','mẹ'      => 'fa-baby-carriage',
                                'thể thao' => 'fa-dumbbell',      'sách'     => 'fa-book-open',
                                'xe'       => 'fa-car',            'đồng hồ' => 'fa-clock',
                                'túi'      => 'fa-bag-shopping',   'giày'    => 'fa-shoe-prints',
                                'sức khỏe' => 'fa-heart-pulse',   'thực phẩm'=> 'fa-bowl-food',
                            ];
                            $icon = 'fa-tag';
                            foreach ($icons as $kw => $ic) {
                                if (str_contains(mb_strtolower($cat->name), $kw)) { $icon = $ic; break; }
                            }
                        @endphp
                        <a href="{{ url('/categories/' . $cat->slug) }}" class="fcat-item">
                            <div class="fcat-thumb">
                                @if ($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="{{ $cat->name }}" loading="lazy">
                                @else
                                    <i class="fa-solid {{ $icon }}"></i>
                                @endif
                            </div>
                            <span class="fcat-name">{{ $cat->name }}</span>
                        </a>
                    @empty
                        <p class="text-muted small px-3 py-2">Chưa có danh mục.</p>
                    @endforelse
                </div>

                {{-- Arrow next --}}
                <button class="fcat-arrow" id="fcatNext" title="Xem thêm">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    (function () {
        const inner = document.querySelector('.fcat-inner');
        const btn   = document.getElementById('fcatNext');
        if (!inner || !btn) return;
        let scrolled = false;
        btn.addEventListener('click', function () {
            if (!scrolled) {
                inner.scrollBy({ left: inner.offsetWidth, behavior: 'smooth' });
                scrolled = true;
                btn.querySelector('i').classList.replace('fa-chevron-right', 'fa-chevron-left');
            } else {
                inner.scrollBy({ left: -inner.offsetWidth, behavior: 'smooth' });
                scrolled = false;
                btn.querySelector('i').classList.replace('fa-chevron-left', 'fa-chevron-right');
            }
        });
    })();
</script>
@endpush
