document.addEventListener('DOMContentLoaded', function () {
    const toggleIcons = document.querySelectorAll('.toggle-password');

    toggleIcons.forEach(function (icon) {
        icon.addEventListener('click', function () {
            // Tìm input password nằm ngay trước icon trong cùng khối cha
            const wrapper = icon.closest('.position-relative');
            const input = wrapper.querySelector('.password-field');

            if (!input) return;

            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';

            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });
});