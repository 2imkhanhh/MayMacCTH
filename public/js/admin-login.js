// public/js/admin-login.js
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const btnText = btnLogin.querySelector('.btn-text');
    const spinner = btnLogin.querySelector('.spinner-border');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim(); // có thể là name
        const password = document.getElementById('password').value;

        if (!username || !password) {
            showToast('Vui lòng nhập đầy đủ thông tin!', 'danger');
            return;
        }

        btnLogin.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Đang đăng nhập...';

        try {
            const response = await fetch('../../../api/auth/admin-login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username, password })
            });

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('Server trả về không phải JSON:', text);
                showToast('Lỗi server (404 hoặc lỗi cấu hình)', 'danger');
                return;
            }

            if (result.success) {
                showToast('Đăng nhập thành công!', 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.php'; 
                }, 1000);
            } else {
                showToast(result.message || 'Sai tên đăng nhập hoặc mật khẩu!', 'danger');
            }
        } catch (err) {
            console.error(err);
            showToast('Không kết nối được server!', 'danger');
        } finally {
            btnLogin.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'ĐĂNG NHẬP';
        }
    });

    function showToast(message, type = 'danger') {
        const toast = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', toast);
        setTimeout(() => document.querySelector('.alert')?.remove(), 4000);
    }
});