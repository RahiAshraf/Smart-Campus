document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            const icon = button.querySelector('i');

            if (!input) {
                return;
            }

            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');

            if (icon) {
                icon.className = reveal ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", () => {

    const alert = document.querySelector(".auth-alert");

    if(alert){

        setTimeout(() => {

            alert.style.opacity = "0";
            alert.style.transform = "translateY(-10px)";

        }, 1500);

        setTimeout(() => {

            alert.remove();

        }, 1800);

    }

});
