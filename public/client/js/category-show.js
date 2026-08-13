/* ==============================================
   CUPO CLIENT — Category Page JS
   File: public/client/js/category-show.js
   ============================================== */

document.addEventListener('DOMContentLoaded', function () {
    // Submit filter form on radio / select change
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        const radios = filterForm.querySelectorAll('input[type="radio"]');
        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                filterForm.submit();
            });
        });
    }
});
