{{-- ===== TÌM KIẾM HÀNG ĐẦU (DỮ LIỆU FIX CỨNG - CHỜ DATA THẬT) ===== --}}
@php
    $topSearchProducts = [
        [
            'name' => "Nước Tẩy Trang L'Oreal Paris 3 In 1",
            'image' => 'https://picsum.photos/seed/ts1/300/300',
            'sold' => '177k',
        ],
        ['name' => 'Giấy Vệ Sinh Cuộn', 'image' => 'https://picsum.photos/seed/ts2/300/300', 'sold' => '171k'],
        ['name' => 'Mi Giả 3D Cao Cấp', 'image' => 'https://picsum.photos/seed/ts3/300/300', 'sold' => '148k'],
        ['name' => 'Sữa Rửa Mặt Cerave', 'image' => 'https://picsum.photos/seed/ts4/300/300', 'sold' => '139k'],
        ['name' => 'Quạt Mini Cầm Tay', 'image' => 'https://picsum.photos/seed/ts5/300/300', 'sold' => '133k'],
        ['name' => 'Quần Lót Nữ Cotton', 'image' => 'https://picsum.photos/seed/ts6/300/300', 'sold' => '119k'],
        ['name' => 'Khẩu Trang Y Tế 4 Lớp', 'image' => 'https://picsum.photos/seed/ts7/300/300', 'sold' => '105k'],
        ['name' => 'Bấm Móng Tay Cao Cấp', 'image' => 'https://picsum.photos/seed/ts8/300/300', 'sold' => '98k'],
        [
            'name' => 'Kem Dưỡng Da Ban Đêm Vitamin C',
            'image' => 'https://picsum.photos/seed/ts9/300/300',
            'sold' => '92k',
        ],
        [
            'name' => 'Chảo Chống Dính Đáy Từ 24cm',
            'image' => 'https://picsum.photos/seed/ts10/300/300',
            'sold' => '87k',
        ],
        ['name' => 'Dép Lê Nam Nữ Đế Êm', 'image' => 'https://picsum.photos/seed/ts11/300/300', 'sold' => '79k'],
        [
            'name' => 'Bột Giặt Cao Cấp Hương Nước Hoa',
            'image' => 'https://picsum.photos/seed/ts12/300/300',
            'sold' => '74k',
        ],
        [
            'name' => 'Túi Đựng Đồ Trang Điểm Mini',
            'image' => 'https://picsum.photos/seed/ts13/300/300',
            'sold' => '68k',
        ],
        ['name' => 'Cáp Sạc Nhanh Type-C 1m', 'image' => 'https://picsum.photos/seed/ts14/300/300', 'sold' => '61k'],
        ['name' => 'Nến Thơm Tinh Dầu Thư Giãn', 'image' => 'https://picsum.photos/seed/ts15/300/300', 'sold' => '55k'],
    ];
@endphp

<div class="topsearch-slider-section">
    <div class="topsearch-slider-header">
        <div class="text">
            <h2 class="h4 fw-bold">Tìm kiếm hàng đầu</h2>
            <p>Những sản phẩm được săn đón nhiều nhất tháng này</p>
        </div>
        <a href="#" class="view-all topsearch">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>

    <div class="topsearch-slider-wrap">
        <button type="button" class="fs-nav-left" id="topsearchNavLeft" aria-label="Trước">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="topsearch-slider-track" id="topsearchTrack">
            @foreach ($topSearchProducts as $index => $item)
                <div class="topsearch-card">
                    <span class="topsearch-rank {{ $index < 3 ? 'is-top' : '' }}">{{ $index + 1 }}</span>
                    <div class="topsearch-image">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                    </div>
                    <div class="topsearch-info">
                        <h3>{{ $item['name'] }}</h3>
                        <span class="topsearch-sold">
                            <i class="fa-solid fa-fire"></i> Đã bán {{ $item['sold'] }}/tháng
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="fs-nav-right" id="topsearchNavRight" aria-label="Sau">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>

<script>
    (function() {
        const track = document.getElementById('topsearchTrack');
        const btnLeft = document.getElementById('topsearchNavLeft');
        const btnRight = document.getElementById('topsearchNavRight');
        if (!track || !btnLeft || !btnRight) return;

        const PER_PAGE = 5; // mỗi lần cuộn = 5 thẻ
        const cards = track.querySelectorAll('.topsearch-card');
        const totalItems = cards.length; // 15
        const totalPages = Math.ceil(totalItems / PER_PAGE); // 15 / 5 = 3 phần

        let currentPage = 0; // 0 = phần 1 (thẻ 1-5), 1 = phần 2 (thẻ 6-10), 2 = phần 3 (thẻ 11-15)

        function getStep() {
            // Khoảng cách giữa 2 thẻ = chiều rộng 1 thẻ + gap (đọc trực tiếp từ CSS để luôn khớp mọi kích thước màn hình)
            const cardWidth = cards[0].getBoundingClientRect().width;
            const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
            return cardWidth + gap;
        }

        function updateNavButtons() {
            btnLeft.style.display = currentPage === 0 ? 'none' : 'flex';
            btnRight.style.display = currentPage === totalPages - 1 ? 'none' : 'flex';
        }

        function goToPage(page) {
            currentPage = Math.max(0, Math.min(page, totalPages - 1));
            track.scrollTo({
                left: currentPage * PER_PAGE * getStep(),
                behavior: 'smooth',
            });
            updateNavButtons();
        }

        btnRight.addEventListener('click', () => goToPage(currentPage + 1));
        btnLeft.addEventListener('click', () => goToPage(currentPage - 1));

        window.addEventListener('resize', () => {
            // Khi resize, căn lại đúng vị trí đầu phần hiện tại (tránh lệch do đổi kích thước thẻ ở mobile)
            track.scrollTo({
                left: currentPage * PER_PAGE * getStep(),
                behavior: 'auto'
            });
        });

        // Trạng thái ban đầu: đang ở phần 1 (thẻ 1-5) -> chỉ hiện nút phải
        updateNavButtons();
    })();
</script>
