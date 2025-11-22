const BASE_URL = '/MayMacCTH';

document.addEventListener('DOMContentLoaded', () => {
    loadContact();

    // Nút chính (duy nhất) – dùng để thêm hoặc sửa
    document.getElementById('btnAdd').addEventListener('click', () => openModal());
});

document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = document.getElementById('contact_id').value;

    const url = id 
        ? `${BASE_URL}/api/contact/update_contact.php`
        : `${BASE_URL}/api/contact/create_contact.php`;

    try {
        const res = await fetch(url, { method: 'POST', body: formData });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } 
        catch (err) { alert("Lỗi server! Kiểm tra console."); console.error(text); return; }

        alert(data.message || (data.success ? 'Thành công!' : 'Thất bại!'));

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
            loadContact();
        }
    } catch (err) {
        console.error(err);
        alert('Lỗi kết nối');
    }
});

async function loadContact() {
    try {
        const res = await fetch(`${BASE_URL}/api/contact/get_contact.php?t=${Date.now()}`);
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } 
        catch (e) {
            document.getElementById('contactList').innerHTML = `<div class="col-12"><div class="alert alert-danger">Lỗi API get_contact.php</div></div>`;
            return;
        }

        const container = document.getElementById('contactList');
        const btnAdd = document.getElementById('btnAdd');

        container.innerHTML = '';

        if (data.success && data.data && data.data.length > 0) {
            const c = data.data[0];

            // Hiển thị thông tin (không có nút sửa)
            container.innerHTML = `
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row text-center text-md-start g-4">
                                <div class="col-md-4">
                                    <i class="bi bi-geo-alt-fill fs-1 text-primary"></i>
                                    <h6 class="mt-3 fw-bold">Địa chỉ</h6>
                                    <p class="text-muted">${c.address || 'Chưa cập nhật'}</p>
                                </div>
                                <div class="col-md-4">
                                    <i class="bi bi-globe fs-1 text-success"></i>
                                    <h6 class="mt-3 fw-bold">Website</h6>
                                    <p>${c.website ? `<a href="${c.website}" target="_blank" class="text-decoration-none">${c.website}</a>` : '<em class="text-muted">Chưa có</em>'}</p>
                                </div>
                                <div class="col-md-4">
                                    <i class="bi bi-telephone-fill fs-1 text-warning"></i>
                                    <h6 class="mt-3 fw-bold">Điện thoại</h6>
                                    <p class="text-muted">${c.phone_number || 'Chưa cập nhật'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Đổi nút thành "Cập nhật thông tin liên hệ"
            btnAdd.innerHTML = '<i class="bi bi-pencil"></i> Cập nhật thông tin liên hệ';
            btnAdd.classList.remove('btn-primary');
            btnAdd.classList.add('btn-success');
        } else {
            // Chưa có dữ liệu
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-info-circle display-1 text-secondary opacity-50"></i>
                    <h5 class="mt-3 text-muted">Chưa có thông tin liên hệ</h5>
                    <p class="text-muted">Nhấn nút bên trên để thêm thông tin cửa hàng</p>
                </div>
            `;

            // Nút thêm
            btnAdd.innerHTML = '<i class="bi bi-plus-circle"></i> Thêm thông tin liên hệ';
            btnAdd.classList.remove('btn-success');
            btnAdd.classList.add('btn-primary');
        }
    } catch (err) {
        console.error(err);
    }
}

function openModal() {
    const modal = new bootstrap.Modal(document.getElementById('contactModal'));
    const title = document.getElementById('modalTitle');
    const saveBtn = document.getElementById('saveBtnText');
    const form = document.getElementById('contactForm');

    form.reset();
    document.getElementById('contact_id').value = '';

    // Kiểm tra xem có dữ liệu chưa để đổi tiêu đề
    fetch(`${BASE_URL}/api/contact/get_contact.php`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                const c = data.data[0];
                title.textContent = 'Cập nhật thông tin liên hệ';
                saveBtn.textContent = 'Cập nhật';
                form.address.value = c.address || '';
                form.website.value = c.website || '';
                form.phone_number.value = c.phone_number || '';
                document.getElementById('contact_id').value = c.contact_id;
            } else {
                title.textContent = 'Thêm thông tin liên hệ';
                saveBtn.textContent = 'Lưu thông tin';
            }
        })
        .catch(() => {
            title.textContent = 'Thêm thông tin liên hệ';
            saveBtn.textContent = 'Lưu thông tin';
        })
        .finally(() => modal.show());
}