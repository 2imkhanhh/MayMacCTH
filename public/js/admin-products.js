const BASE_URL = '/MayMacCTH';
let colorIndex = 1;
let imageIndex = 1;
let currentEditId = null;

document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadProducts();

    document.getElementById('btnAdd').onclick = () => openModal();
    document.getElementById('btnSearch').onclick = loadProducts;
    document.getElementById('searchName').addEventListener('keypress', e => e.key === 'Enter' && loadProducts());

    document.getElementById('addColor').onclick = addColorField;
    document.getElementById('addImage').onclick = addImageField;

    document.getElementById('productForm').onsubmit = handleSubmit;
});

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
        showToast('Lỗi tải danh mục!', 'error');
        console.error('Lỗi load danh mục:', err);
    }
}

async function loadProducts() {
    const categoryId = document.getElementById('filterCategory').value;
    const name = document.getElementById('searchName').value.trim();
    let url = `${BASE_URL}/api/product/get_all_product.php`;
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
                col.className = 'col-6 col-md-4 col-lg-3 mb-4';

                const status = p.is_active == 1
                    ? '<span class="badge bg-success position-absolute top-0 start-0 m-2 z-3">Hiển thị</span>'
                    : '<span class="badge bg-secondary position-absolute top-0 start-0 m-2 z-3">Ẩn</span>';

                const star = '★'.repeat(Math.floor(p.star || 5)) + '☆'.repeat(5 - Math.floor(p.star || 5));

                col.innerHTML = `
                    <div class="card h-100 position-relative shadow-sm hover-shadow">
                        ${status}
                        <img src="../../assets/images/upload/${p.primary_image || 'no-image.jpg'}" 
                             class="card-img-top" style="height:200px; object-fit:cover;" 
                             onerror="this.src='../../assets/images/no-image.jpg'">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fs-6 fw-bold">${p.name}</h5>
                            <div class="d-flex flex-column gap-1 mb-2">
                                <p class="text-muted small mb-0">${p.category_name || 'Chưa có danh mục'}</p>
                                <p class="text-primary fw-bold fs-5 mb-0">${parseInt(p.price || 0).toLocaleString()}đ</p>
                            </div>
                            <small class="text-warning">${star} (${p.review_count || 0} đánh giá)</small>
                            <div class="product-actions mt-auto">
                                <button class="btn-action btn-edit w-100 d-flex align-items-center justify-content-center gap-2" data-id="${p.product_id}">
                                    <i class="bi bi-pencil-square"></i> Sửa
                                </button>
                                <button class="btn-action btn-delete w-100 d-flex align-items-center justify-content-center gap-2" data-id="${p.product_id}">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });

            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.onclick = () => editProduct(btn.dataset.id);
            });
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.onclick = () => deleteProduct(btn.dataset.id);
            });
        } else {
            container.innerHTML = '<div class="col-12 text-center py-5 text-muted"><h5>Chưa có sản phẩm nào</h5></div>';
        }
    } catch (err) {
        showToast('Lỗi tải danh sách sản phẩm!', 'error');
        console.error(err);
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('globalToast');
    const icon = document.getElementById('toastIcon');
    const msg = document.getElementById('toastMessage');

    toast.classList.remove('success', 'error', 'show');

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
    setTimeout(() => toast.classList.remove('show'), type === 'error' ? 5000 : 3000);
}

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
        form.price.value = product.price || 0;
        form.description.value = product.description || '';
        document.getElementById('isActiveCheckbox').checked = product.is_active == 1;

        if (product.colors && product.colors.length > 0) {
            product.colors.forEach(c => {
                const sizesStr = c.sizes ? c.sizes.join(', ') : '';
                addColorField(c.color_name || '', c.color_code || '#000000', sizesStr);
            });
        } else {
            addColorField();
        }

        if (product.images && product.images.length > 0) {
            product.images.forEach(img => {
                addImageField(img.image || '', img.is_primary == 1, img.image_id);
            });
        } else {
            addImageField();
        }
    } else {
        title.textContent = 'Thêm sản phẩm mới';
        form.price.value = 0;
        document.getElementById('isActiveCheckbox').checked = true;
        addColorField();
        addImageField();
    }

    modal.show();
}

function addColorField(name = '', code = '#000000', sizes = '') {
    const container = document.getElementById('colorContainer');
    const div = document.createElement('div');
    div.className = 'color-item mt-3';
    div.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger remove-color position-absolute" style="top: -10px; right: -10px; z-index: 10;">×</button>
        <div class="row g-3">
            <div class="col-md-5">
                <label>Tên màu</label>
                <input type="text" class="form-control" name="colors[${colorIndex}][name]" value="${name}" placeholder="VD: Đen">
            </div>
            <div class="col-md-3">
                <label>Mã màu</label>
                <input type="color" class="form-control form-control-color" name="colors[${colorIndex}][code]" value="${code}">
            </div>
            <div class="col-md-4">
                <label>Kích thước</label>
                <input type="text" class="form-control" name="colors[${colorIndex}][sizes]" value="${sizes}" placeholder="S, M, L">
            </div>
        </div>
    `;
    container.appendChild(div);
    div.querySelector('.remove-color').onclick = () => div.remove();
    colorIndex++;
}

function addImageField(imageUrl = '', isPrimary = false, imageId = null) {
    const container = document.getElementById('imageContainer');
    const div = document.createElement('div');
    div.className = 'image-item mt-3 position-relative';
    div.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger remove-image position-absolute" style="top: -10px; right: -10px; z-index: 10;">×</button>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="primary_image" value="${imageIndex}" ${isPrimary ? 'checked' : ''}>
            <label class="form-check-label">Ảnh chính</label>
        </div>
        <input type="file" class="form-control mt-2" name="images[]" accept="image/*">
        ${imageId ? `<input type="hidden" name="existing_images[]" value="${imageId}">` : ''}
        <img src="${imageUrl ? '../../assets/images/upload/' + imageUrl : ''}" 
             class="img-thumbnail mt-2" style="max-height:150px; ${imageUrl ? '' : 'display:none;'}">
    `;
    container.appendChild(div);
    div.querySelector('.remove-image').onclick = () => div.remove();
    div.querySelector('input[type="file"]').onchange = function (e) {
        if (e.target.files[0]) {
            div.querySelector('img').src = URL.createObjectURL(e.target.files[0]);
            div.querySelector('img').style.display = 'block';
        }
    };
    imageIndex++;
}

async function editProduct(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/product/get_product_by_id.php?id=${id}`);
        const result = await res.json();
        if (result.success && result.data[0]) {
            openModal(result.data[0]);
        } else {
            showToast('Không tìm thấy sản phẩm!', 'error');
        }
    } catch (err) {
        showToast('Lỗi tải thông tin sản phẩm!', 'error');
        console.error(err);
    }
}

async function deleteProduct(id) {
    const confirmed = await showConfirm('Xóa sản phẩm?', 'Tất cả dữ liệu liên quan sẽ bị xóa vĩnh viễn!');

    if (!confirmed) return;

    try {
        const res = await fetch(`${BASE_URL}/api/product/delete_product.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();

        if (data.success) {
            showToast(data.message || 'Xóa sản phẩm thành công!', 'success');
            loadProducts();
        } else {
            showToast(data.message || 'Xóa sản phẩm thất bại!', 'error');
        }
    } catch (err) {
        showToast('Lỗi kết nối server!', 'error');
        console.error(err);
    }
}

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
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Server trả về lỗi:", text);
            showToast('Lỗi server! Vui lòng thử lại.', 'error');
            return;
        }

        showToast(data.message || (data.success ? 'Lưu sản phẩm thành công!' : 'Lưu thất bại!'), data.success ? 'success' : 'error');

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            loadProducts();
        }
    } catch (err) {
        showToast('Lỗi kết nối server!', 'error');
        console.error(err);
    }
}

function showConfirm(title = "Xác nhận", message = "Bạn có chắc chắn?") {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmModal');
        const confirmBtn = document.getElementById('confirmBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        document.querySelector('.confirm-box h4').textContent = title;
        document.querySelector('.confirm-box p').innerHTML = message;

        modal.style.display = 'flex';

        const close = () => {
            modal.style.display = 'none';
            confirmBtn.onclick = null;
            cancelBtn.onclick = null;
        };

        confirmBtn.onclick = () => { close(); resolve(true); };
        cancelBtn.onclick = () => { close(); resolve(false); };
        modal.onclick = (e) => { if (e.target === modal) { close(); resolve(false); } };
    });
}