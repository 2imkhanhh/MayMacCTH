document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const btnText = btnLogin.querySelector('.btn-text');
    const spinner = btnLogin.querySelector('.spinner-border');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        btnLogin.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Đang đăng nhập...';

        try {
            const response = await fetch('../../api/auth/admin-login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username, password })
            });

            const result = await response.json();

            if (result.success) {
                showToast('Đăng nhập thành công!', 'success');
                setTimeout(() => {
                    window.location.href = '../views/admin/dashboard.html'; 
                }, 1000);
            } else {
                showToast(result.message || 'Đăng nhập thất bại!', 'danger');
            }
        } catch (err) {
            console.error(err);
            showToast('Lỗi kết nối đến server!', 'danger');
        } finally {
            btnLogin.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'ĐĂNG NHẬP';
        }
    });

    // Toast đẹp hơn alert
    function showToast(message, type = 'danger') {
        const toast = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', toast);

        setTimeout(() => document.querySelector('.alert')?.remove(), 4000);
    }
});