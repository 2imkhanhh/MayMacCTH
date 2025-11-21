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
    
    // Thêm element ảnh vào sau input
    if(addImageInput && addImageInput.parentNode) {
        addImageInput.parentNode.appendChild(addPreviewImg);
    }

    if(addImageInput){
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
    }

    // Submit thêm banner
    const addForm = document.getElementById('addBannerForm');
    if(addForm){
        addForm.addEventListener('submit', addBanner);
    }
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

                                <button class="btn btn-action btn-delete delete-banner-btn" data-id="${b.banner_id}">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                grid.appendChild(col);
            });

            // Gắn sự kiện sau khi render xong
            document.querySelectorAll('.edit-banner-btn').forEach(btn => {
                btn.addEventListener('click', () => editBanner(btn.dataset.id));
            });
            document.querySelectorAll('.delete-banner-btn').forEach(btn => {
                btn.addEventListener('click', () => deleteBanner(btn.dataset.id));
            });

        } else {
            grid.innerHTML = '<div class="col-12"><p class="text-muted text-center">Chưa có banner nào.</p></div>';
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
        
        if(data.success){
            alert(data.message); // Hoặc dùng toast/notify đẹp hơn
            bootstrap.Modal.getInstance(document.getElementById('addBannerModal')).hide();
            form.reset();
            const preview = document.getElementById('addPreviewImg');
            if(preview) preview.style.display = 'none';
            loadBanners();
        } else {
            alert(data.message || 'Thêm thất bại');
        }
    } catch(err){
        alert('Lỗi kết nối API');
        console.error(err);
    }
}

// ==================== Sửa Banner ====================
async function editBanner(id){
    try{
        // Cách tốt nhất là gọi API get detail 1 banner, ở đây dùng lại list cho tiện nhưng cẩn thận hiệu năng
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
        document.getElementById('editIsActive').checked = (banner.is_active == 1);
        
        // Hiển thị ảnh cũ
        const currentImg = document.getElementById('currentImage');
        if(currentImg) {
            currentImg.src = `../../assets/images/upload/${banner.image}`;
            currentImg.style.display = 'block';
        }

        // Xử lý khi submit form sửa
        form.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            
            // Laravel/PHP đôi khi cần _method=PUT hoặc POST form-data bình thường
            // Ở đây giữ nguyên logic POST update của bạn
            try{
                const res = await fetch(`${BASE_URL}/api/banner/update_banner.php?id=${banner.banner_id}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                if(data.success){
                    alert(data.message);
                    bootstrap.Modal.getInstance(modalEl).hide();
                    loadBanners();
                } else {
                    alert(data.message || 'Cập nhật thất bại');
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

async function deleteBanner(id){
    if(!confirm('Bạn có chắc chắn muốn xóa banner này không?')) return;
    try{
        const res = await fetch(`${BASE_URL}/api/banner/delete_banner.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        
        if(data.success){
            alert(data.message);
            loadBanners();
        } else {
            alert(data.message || 'Xóa thất bại');
        }
    } catch(err){
        alert('Lỗi xóa banner');
        console.error(err);
    }
}

function clearAddForm(){
    const form = document.getElementById('addBannerForm');
    if(form) form.reset();
    
    const preview = document.getElementById('addPreviewImg');
    if(preview){
        preview.src = '';
        preview.style.display = 'none';
    }
}