const BASE_URL = 'http://localhost:81/MayMacCTH';

// Khi DOM load xong
document.addEventListener('DOMContentLoaded', () => {
    loadBanners();

    // Mở modal thêm Banner
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
    addImageInput.parentNode.appendChild(addPreviewImg);

    addImageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if(file){
            addPreviewImg.src = URL.createObjectURL(file);
            addPreviewImg.style.display = 'block';
        } else {
            addPreviewImg.src = '';
            addPreviewImg.style.display = 'none';
        }
    });

    // Submit thêm banner
    const addForm = document.getElementById('addBannerForm');
    addForm.addEventListener('submit', addBanner);
});

// ==================== Load danh sách banner ====================
async function loadBanners(){
    try{
        const res = await fetch(`${BASE_URL}/api/banner/get_banner.php`);
        const data = await res.json();
        const grid = document.getElementById('bannerList');
        if(!grid) return;
        grid.innerHTML = '';

        if(data.success && data.data.length){
            const row = document.createElement('div');
            row.className = 'row';

            data.data.forEach(b => {
                const card = document.createElement('div');
                card.className = 'col-md-4 mb-4';
                card.innerHTML = `
                    <div class="card h-100">
                        <img src="../../assets/images/upload/${b.image}" class="card-img-top" style="height:200px; object-fit:cover;">
                        <div class="card-body">
                            <h5 class="card-title">${b.title}</h5>
                            <span class="badge bg-${b.is_active ? 'success' : 'secondary'}">
                                ${b.is_active ? 'Hiển thị' : 'Ẩn'}
                            </span>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-warning btn-sm edit-banner-btn" data-id="${b.banner_id}">Sửa</button>
                            <button class="btn btn-danger btn-sm delete-banner-btn" data-id="${b.banner_id}">Xóa</button>
                        </div>
                    </div>
                `;
                row.appendChild(card);
            });

            grid.appendChild(row);

            // Gắn sự kiện sửa / xóa
            document.querySelectorAll('.edit-banner-btn').forEach(btn => {
                btn.addEventListener('click', () => editBanner(btn.dataset.id));
            });
            document.querySelectorAll('.delete-banner-btn').forEach(btn => {
                btn.addEventListener('click', () => deleteBanner(btn.dataset.id));
            });

        } else {
            grid.innerHTML = '<p class="text-muted">Chưa có banner nào.</p>';
        }

    } catch(err){
        alert('Lỗi kết nối API');
        console.error(err);
    }
}

// ==================== Thêm Banner ====================
async function addBanner(e){
    e.preventDefault();
    const form = document.getElementById('addBannerForm');
    const formData = new FormData(form);

    try{
        const res = await fetch(`${BASE_URL}/api/banner/create_banner.php`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        alert(data.message);
        if(data.success){
            bootstrap.Modal.getInstance(document.getElementById('addBannerModal')).hide();
            form.reset();
            document.getElementById('addPreviewImg').style.display = 'none';
            loadBanners();
        }
    } catch(err){
        alert('Lỗi kết nối API');
        console.error(err);
    }
}

// ==================== Sửa Banner ====================
async function editBanner(id){
    try{
        const res = await fetch(`${BASE_URL}/api/banner/get_banner.php`);
        const data = await res.json();
        const banner = data.data.find(b => b.banner_id == id);
        if(!banner) return alert('Không tìm thấy banner');

        // Mở modal sửa
        const modalEl = document.getElementById('editBannerModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        const form = document.getElementById('editBannerForm');
        document.getElementById('editBannerId').value = banner.banner_id;
        document.getElementById('editTitle').value = banner.title;
        document.getElementById('editIsActive').checked = banner.is_active == 1;
        document.getElementById('currentImage').src = `../../assets/images/banners/${banner.image}`;

        // Submit sửa banner
        form.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            try{
                const res = await fetch(`${BASE_URL}/api/banner/update_banner.php?id=${banner.banner_id}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                alert(data.message);
                if(data.success){
                    bootstrap.Modal.getInstance(modalEl).hide();
                    loadBanners();
                }
            } catch(err){
                alert('Lỗi kết nối API');
                console.error(err);
            }
        };

    } catch(err){
        alert('Lỗi tải thông tin banner');
        console.error(err);
    }
}

// ==================== Xóa Banner ====================
async function deleteBanner(id){
    if(!confirm('Xóa banner này? Không thể khôi phục!')) return;
    try{
        const res = await fetch(`${BASE_URL}/api/banner/delete_banner.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        alert(data.message);
        loadBanners();
    } catch(err){
        alert('Lỗi xóa banner');
        console.error(err);
    }
}

// ==================== Reset form thêm banner ====================
function clearAddForm(){
    const form = document.getElementById('addBannerForm');
    form.reset();
    const preview = document.getElementById('addPreviewImg');
    if(preview){
        preview.src = '';
        preview.style.display = 'none';
    }
}
