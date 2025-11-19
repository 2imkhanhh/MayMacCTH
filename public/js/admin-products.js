const BASE_URL = '/MayMacCTH';
let colorIndex = 1;
let imageIndex = 1;
let currentEditId = null; // Để biết đang sửa sản phẩm nào

document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadProducts();

    // Nút thêm + sửa
    document.getElementById('btnAdd').onclick = () => openModal();
    document.getElementById('btnSearch').onclick = loadProducts;
    document.getElementById('searchName').addEventListener('keypress', e => e.key === 'Enter' && loadProducts());

    // Thêm màu + ảnh
    document.getElementById('addColor').onclick = addColorField;
    document.getElementById('addImage').onclick = addImageField;

    // Submit form
    document.getElementById('productForm').onsubmit = handleSubmit;
});

// ==================== LOAD DATA ====================
async function loadCategories() {
    try {
        const res = await fetch(`${BASE_URL}/api/category/get_category.php`);
        const data = await res.json();
        const selects = document.querySelectorAll('select[name="category_id"], #filterCategory');
        selects.forEach(sel => {
            sel.innerHTML = sel.id === 'filterCategory'
                ? '<option value="">Tất cả danh mục</option>'
                : '<option value="">Chọn danh mục</option>';
            if (data.success) {
                data.data.forEach(cat => {
                    sel.innerHTML += `<option value="${cat.category_id}">${cat.name}</option>`;
                });
            }
        });
    } catch (err) {
        console.error('Lỗi load danh mục:', err);
    }
}

async function loadProducts() {
    const categoryId = document.getElementById('filterCategory').value;
    const name = document.getElementById('searchName').value.trim();
    let url = `${BASE_URL}/api/product/get_product.php`;
    if (categoryId || name) {
        url += '?' + new URLSearchParams({ category_id: categoryId, name });
    }

    try {
        const res = await fetch(url);
        const result = await res.json();
        const container = document.getElementById('productList');
        container.innerHTML = '';

        if (result.success && result.data.length > 0) {
            result.data.forEach(p => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4 mb-4';

                const status = p.is_active == 1
                    ? '<span class="badge bg-success position-absolute top-0 start-0 m-2 z-3">Hiển thị</span>'
                    : '<span class="badge bg-secondary position-absolute top-0 start-0 m-2 z-3">Ẩn</span>';

                const star = '★'.repeat(Math.floor(p.star || 5)) + '☆'.repeat(5 - Math.floor(p.star || 5));

                col.innerHTML = `
                    <div class="card h-100 position-relative shadow-sm hover-shadow">
                        ${status}
                        <img src="../../assets/images/upload/${p.primary_image || 'no-image.jpg'}" 
                             class="card-img-top" style="height:200px; object-fit:cover;" onerror="this.src='../../assets/images/no-image.jpg'">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fs-6 fw-bold">${p.name}</h5>
                            <p class="text-muted small">${p.category_name || 'Chưa có danh mục'}</p>
                            <p class="text-primary fw-bold">${p.price ? parseInt(p.price).toLocaleString() + 'đ' : 'Liên hệ'}</p>
                            <small class="text-warning">${star} (${p.review_count || 0} đánh giá)</small>
                            <div class="mt-auto d-flex gap-2 pt-3">
                                <button class="btn btn-sm btn-outline-primary flex-fill edit-btn" data-id="${p.product_id}">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <button class="btn btn-sm btn-outline-danger flex-fill delete-btn" data-id="${p.product_id}">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });

            // Gắn sự kiện nút Sửa + Xóa
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.onclick = () => editProduct(btn.dataset.id);
            });
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.onclick = () => deleteProduct(btn.dataset.id);
            });
        } else {
            container.innerHTML = '<div class="col-12 text-center py-5 text-muted"><h5>Chưa có sản phẩm nào</h5></div>';
        }
    } catch (err) {
        alert('Lỗi tải sản phẩm');
        console.error(err);
    }
}

// ==================== MODAL & FORM ====================
function openModal(product = null) {
    currentEditId = product ? product.product_id : null;
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    const form = document.getElementById('productForm');
    const title = document.getElementById('modalTitle');

    form.reset();
    document.getElementById('colorContainer').innerHTML = '';
    document.getElementById('imageContainer').innerHTML = '';
    colorIndex = 0;
    imageIndex = 0;

    if (product) {
        title.textContent = 'Sửa sản phẩm';
        form.name.value = product.name;
        form.category_id.value = product.category_id;
        document.getElementById('isActiveCheckbox').checked = product.is_active == 1;

        // Load màu + size
        product.colors.forEach((color, i) => {
            addColorField(color.color_name, color.color_code, color.sizes.join(', '));
        });

        // Load ảnh
        product.images.forEach((img, i) => {
            addImageField(img.image, img.is_primary == 1, img.image_id);
        });
    } else {
        title.textContent = 'Thêm sản phẩm mới';
        document.getElementById('isActiveCheckbox').checked = true;
        addColorField();
        addImageField();
    }

    modal.show();
}

function addColorField(name = '', code = '#000000', sizes = '') {
    const container = document.getElementById('colorContainer');
    const div = document.createElement('div');
    div.className = 'color-item';
    div.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger remove-color">×</button>
        <div class="row g-3">
            <div class="col-md-4">
                <label>Tên màu</label>
                <input type="text" class="form-control" name="colors[${colorIndex}][name]" value="${name}" placeholder="VD: Đen">
            </div>
            <div class="col-md-3">
                <label>Mã màu</label>
                <input type="color" class="form-control form-control-color" name="colors[${colorIndex}][code]" value="${code}">
            </div>
            <div class="col-md-5">
                <label>Giá (VNĐ)</label>
                <input type="number" class="form-control" name="colors[${colorIndex}][price]" value="0">
            </div>
        </div>
        <div class="mt-3">
            <label>Kích thước (cách nhau bởi dấu phẩy)</label>
            <input type="text" class="form-control" name="colors[${colorIndex}][sizes]" value="${sizes}" placeholder="S, M, L, XL">
        </div>
    `;
    container.appendChild(div);
    div.querySelector('.remove-color').onclick = () => div.remove();
    colorIndex++;
}

function addImageField(imageUrl = '', isPrimary = false, imageId = null) {
    const container = document.getElementById('imageContainer');
    const div = document.createElement('div');
    div.className = 'image-item';
    div.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger remove-image">×</button>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="primary_image" value="${imageIndex}" ${isPrimary ? 'checked' : ''}>
            <label class="form-check-label">Ảnh chính</label>
        </div>
        <input type="file" class="form-control mt-2" name="images[]" accept="image/*">
        ${imageId ? `<input type="hidden" name="existing_images[]" value="${imageId}">` : ''}
        <img src="${imageUrl ? '../../assets/images/upload/' + imageUrl : ''}" class="img-thumbnail mt-2" style="max-height:150px; ${imageUrl ? '' : 'display:none;'}">
    `;
    container.appendChild(div);
    div.querySelector('.remove-image').onclick = () => div.remove();
    div.querySelector('input[type="file"]').onchange = function(e) {
        if (e.target.files[0]) {
            div.querySelector('img').src = URL.createObjectURL(e.target.files[0]);
            div.querySelector('img').style.display = 'block';
        }
    };
    imageIndex++;
}

// ==================== SỬA & XÓA ====================
async function editProduct(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/product/get_product.php?id=${id}`);
        const result = await res.json();
        if (result.success && result.data[0]) {
            openModal(result.data[0]);
        }
    } catch (err) {
        alert('Lỗi tải thông tin sản phẩm');
    }
}

async function deleteProduct(id) {
    if (!confirm('Xóa sản phẩm này? Tất cả màu, ảnh, size sẽ bị xóa!')) return;
    try {
        const res = await fetch(`${BASE_URL}/api/product/delete_product.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        alert(data.message || (data.success ? 'Xóa thành công!' : 'Xóa thất bại'));
        if (data.success) loadProducts();
    } catch (err) {
        alert('Lỗi kết nối');
    }
}

// ==================== SUBMIT FORM ====================
async function handleSubmit(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = currentEditId;

    const url = id
        ? `${BASE_URL}/api/product/update_product.php?id=${id}`
        : `${BASE_URL}/api/product/create_product.php`;

    try {
        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        alert(data.message || (data.success ? 'Thành công!' : 'Thất bại!'));
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            loadProducts();
        }
    } catch (err) {
        alert('Lỗi kết nối server');
        console.error(err);
    }
}