function moveSlide(sliderId, direction) {
    const slider = document.getElementById(sliderId);
    if (!slider) return;

    const firstSlide = slider.querySelector(".fs-card");
    if (!firstSlide) return;

    const slideWidth = firstSlide.offsetWidth + 20; // +20 = gap giữa các slide (khớp với CSS)

    slider.scrollBy({
        left: direction * slideWidth,
        behavior: "smooth",
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const countdownEl = document.getElementById("flashSaleCountdown");
    if (!countdownEl) return;

    // Ghi chú: nên thay dòng dưới bằng giờ kết thúc thật lấy từ server,
    // ví dụ: const endTime = new Date("{{ $flashSaleEndTime }}").getTime();
    const endTime = new Date().getTime() + 3 * 60 * 60 * 1000; // demo: 3 tiếng kể từ lúc load trang

    const timer = setInterval(function () {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance <= 0) {
            clearInterval(timer);
            countdownEl.textContent = "00:00:00";
            return;
        }

        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const pad = (n) => String(n).padStart(2, "0");
        countdownEl.textContent = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
    }, 1000);
});
