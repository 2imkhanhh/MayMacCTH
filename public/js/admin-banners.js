const BASE_URL = '/MayMacCTH';

// Khi DOM load xong
document.addEventListener('DOMContentLoaded', () => {
    loadBanners();

    const btnAdd = document.getElementById('btnAdd');
    if (btnAdd) {
        btnAdd.addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('addBannerModal'));
            modal.show();
            clearAddForm();
        });
    }

    // Preview ảnh thêm Banner
    const addImageInput = document.querySelector('#addBannerForm input[name="image"]');
    const addPreviewImg = document.createElement('img');
    addPreviewImg.id = 'addPreviewImg';
    addPreviewImg.className = 'img-thumbnail mt-2';
    addPreviewImg.style.maxHeight = '200px';
    addPreviewImg.style.display = 'none';

    if (addImageInput && addImageInput.parentNode) {
        addImageInput.parentNode.appendChild(addPreviewImg);
    }

    if (addImageInput) {
        addImageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                addPreviewImg.src = URL.createObjectURL(file);
                addPreviewImg.style.display = 'block';
            } else {
                addPreviewImg.src = '';
                addPreviewImg.style.display = 'none';
            }
        });
    }

    // Submit thêm banner
    const addForm = document.getElementById('addBannerForm');
    if (addForm) {
        addForm.addEventListener('submit', addBanner);
    }
});

// ==================== Load danh sách banner ====================
async function loadBanners() {
    try {
        const res = await fetch(`${BASE_URL}/api/banner/get_banner.php`);
        const data = await res.json();
        const grid = document.getElementById('bannerList');
        if (!grid) return;
        grid.innerHTML = '';

        if (data.success && data.data.length) {
            data.data.forEach(b => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4 mb-4';

                const statusClass = (b.is_active == 1) ? 'status-active' : 'status-inactive';
                const statusText = (b.is_active == 1) ? 'Hiển thị' : 'Ẩn';

                col.innerHTML = `
                    <div class="banner-card">
                        <div class="banner-img-wrapper">
                            <img src="../../assets/images/upload/${b.image}" alt="${b.title}">
                            <span class="banner-status ${statusClass}">${statusText}</span>
                        </div>
                        <div class="banner-body">
                            <h5 class="banner-title" title="${b.title}">${b.title}</h5>
                            
                            <div class="banner-actions">
                                <button class="btn btn-action btn-edit edit-banner-btn" data-id="${b.banner_id}">
                                    <i class="bi bi-pencil-square"></i> Sửa
                                </button>

                                <button class="btn btn-action btn-delete delete-banner-btn" data-id="${b.banner_id}" data-title="${b.title}">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                grid.appendChild(col);
            });

            // Gắn sự kiện
            document.querySelectorAll('.edit-banner-btn').forEach(btn => {
                btn.addEventListener('click', () => editBanner(btn.dataset.id));
            });
            document.querySelectorAll('.delete-banner-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const title = btn.dataset.title || 'banner này';

                    const confirmed = await showConfirm(
                        "Xóa banner?",
                        `Bạn có chắc muốn xóa banner <strong style="color:#e74c3c;">${title}</strong> không?`
                    );

                    if (confirmed) {
                        // Gọi xóa thật
                        try {
                            const res = await fetch(`${BASE_URL}/api/banner/delete_banner.php?id=${btn.dataset.id}`, {
                                method: 'DELETE'
                            });
                            const data = await res.json();

                            if (data.success) {
                                showToast(data.message || 'Xóa banner thành công!', 'success');
                                loadBanners();
                            } else {
                                showToast(data.message || 'Xóa thất bại!', 'error');
                            }
                        } catch (err) {
                            showToast('Lỗi kết nối server!', 'error');
                        }
                    }
                });
            });

        } else {
            grid.innerHTML = '<div class="col-12"><p class="text-muted text-center">Chưa có banner nào.</p></div>';
        }

    } catch (err) {
        showToast('Lỗi kết nối server!', 'error');
        console.error(err);
    }
}

// ==================== Thêm Banner ====================
async function addBanner(e) {
    e.preventDefault();
    const form = document.getElementById('addBannerForm');
    const formData = new FormData(form);

    try {
        const res = await fetch(`${BASE_URL}/api/banner/create_banner.php`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message || 'Thêm banner thành công!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addBannerModal')).hide();
            form.reset();
            const preview = document.getElementById('addPreviewImg');
            if (preview) preview.style.display = 'none';
            loadBanners();
        } else {
            showToast(data.message || 'Thêm banner thất bại!', 'error');
        }
    } catch (err) {
        showToast('Lỗi kết nối server!', 'error');
        console.error(err);
    }
}

// ==================== Sửa Banner ====================
async function editBanner(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/banner/get_banner.php`);
        const data = await res.json();
        const banner = data.data.find(b => b.banner_id == id);
        if (!banner) {
            showToast('Không tìm thấy banner!', 'error');
            return;
        }

        const modalEl = document.getElementById('editBannerModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        document.getElementById('editBannerId').value = banner.banner_id;
        document.getElementById('editTitle').value = banner.title;
        document.getElementById('editIsActive').checked = (banner.is_active == 1);

        const currentImg = document.getElementById('currentImage');
        if (currentImg) {
            currentImg.src = `../../assets/images/upload/${banner.image}`;
            currentImg.style.display = 'block';
        }

        // Gỡ listener cũ (tránh trùng)
        const form = document.getElementById('editBannerForm');
        form.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const res = await fetch(`${BASE_URL}/api/banner/update_banner.php?id=${banner.banner_id}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message || 'Cập nhật banner thành công!', 'success');
                    bootstrap.Modal.getInstance(modalEl).hide();
                    loadBanners();
                } else {
                    showToast(data.message || 'Cập nhật thất bại!', 'error');
                }
            } catch (err) {
                showToast('Lỗi kết nối server!', 'error');
                console.error(err);
            }
        };

    } catch (err) {
        showToast('Lỗi tải thông tin banner!', 'error');
        console.error(err);
    }
}

// ==================== Xóa Banner (dùng modal confirm đẹp) ====================
async function deleteBanner(id, title = 'banner này') {
    const confirmed = await showConfirm(
        "Xóa banner?",
        `Bạn có chắc muốn xóa banner <strong style="color:#e74c3c;">${title}</strong> không?`
    );

    if (!confirmed) return;

    try {
        const res = await fetch(`${BASE_URL}/api/banner/delete_banner.php?id=${id}`, {
            method: 'DELETE'
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message || 'Xóa banner thành công!', 'success');
            loadBanners();
        } else {
            showToast(data.message || 'Xóa banner thất bại!', 'error');
        }
    } catch (err) {
        showToast('Lỗi kết nối server!', 'error');
        console.error(err);
    }
}

// ==================== Các hàm hỗ trợ ====================
function clearAddForm() {
    const form = document.getElementById('addBannerForm');
    if (form) form.reset();

    const preview = document.getElementById('addPreviewImg');
    if (preview) {
        preview.src = '';
        preview.style.display = 'none';
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('globalToast');
    const icon = document.getElementById('toastIcon');
    const msg = document.getElementById('toastMessage');

    // Xóa class cũ
    toast.classList.remove('success', 'error', 'show');

    // Set nội dung + icon
    msg.textContent = message;

    if (type === 'success') {
        toast.classList.add('success');
        icon.className = 'bx bxs-check-circle';
    } else {
        toast.classList.add('error');
        icon.className = 'bx bxs-error';
    }

    // Hiển thị
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
        toast.classList.remove('show');
    }, type === 'error' ? 5000 : 3000);
}
function showConfirm(title = "Xóa banner?", message = "Bạn có chắc chắn muốn xóa?") {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmModal');
        const confirmBtn = document.getElementById('confirmBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        // Set nội dung
        document.querySelector('.confirm-box h4').textContent = title;
        document.querySelector('.confirm-box p').innerHTML = message;

        modal.style.display = 'flex';

        const close = () => {
            modal.style.display = 'none';
            confirmBtn.onclick = null;
            cancelBtn.onclick = null;
        };

        confirmBtn.onclick = () => {
            close();
            resolve(true);
        };

        cancelBtn.onclick = () => {
            close();
            resolve(false);
        };

        modal.onclick = (e) => {
            if (e.target === modal) {
                close();
                resolve(false);
            }
        };
    });
}