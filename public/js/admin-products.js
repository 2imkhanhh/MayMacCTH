const BASE_URL = '/MayMacCTH';

document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadProducts();

    document.getElementById('btnAdd').addEventListener('click', () => openModal());
    document.getElementById('btnSearch').addEventListener('click', () => loadProducts());
    document.getElementById('searchName').addEventListener('keypress', e => e.key === 'Enter' && loadProducts());

    // Preview ảnh
    document.querySelector('#productForm input[name="image"]').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const img = document.getElementById('currentImage');
        if (file) {
            img.src = URL.createObjectURL(file);
            img.style.display = 'block';
        } else {
            img.style.display = 'none';
        }
    });
});

async function loadCategories() {
    try {
        const res = await fetch(`${BASE_URL}/api/category/get_category.php`);
        const data = await res.json();
        const selects = document.querySelectorAll('select[name="category_id"]');
        const filterSelect = document.getElementById('filterCategory');

        selects.forEach(sel => {
            sel.innerHTML = '<option value="">Chọn danh mục</option>';
            if (data.success) {
                data.data.forEach(cat => {
                    sel.innerHTML += `<option value="${cat.category_id}">${cat.name}</option>`;
                });
            }
        });

        filterSelect.innerHTML = '<option value="">Tất cả danh mục</option>';
        if (data.success) {
            data.data.forEach(cat => {
                filterSelect.innerHTML += `<option value="${cat.category_id}">${cat.name}</option>`;
            });
        }
    } catch (err) {
        console.error('Lỗi tải danh mục:', err);
    }
}

// Load danh sách sản phẩm
async function loadProducts() {
    const categoryId = document.getElementById('filterCategory').value;
    const name = document.getElementById('searchName').value.trim();

    let url = `${BASE_URL}/api/product/get_product.php`;
    const params = new URLSearchParams();
    if (categoryId) params.append('category_id', categoryId);
    if (name) params.append('name', name);
    if (params.toString()) url += `?${params.toString()}`;

    try {
        const res = await fetch(url);
        const data = await res.json();
        const container = document.getElementById('productList');
        container.innerHTML = '';

        if (data.success && data.data.length > 0) {
            data.data.forEach(p => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4 mb-4';

                // Badge trạng thái giống hệt Banner
                const statusBadge = p.is_active == 1
                    ? '<span class="position-absolute top-0 end-0 bg-success text-white px-3 py-1 rounded-start shadow-sm fw-semibold" style="font-size:0.75rem; z-index:2;">Hiển thị</span>'
                    : '<span class="position-absolute top-0 end-0 bg-secondary text-white px-3 py-1 rounded-start shadow-sm fw-semibold" style="font-size:0.75rem; z-index:2;">Ẩn</span>';

                col.innerHTML = `
                    <div class="card h-100 product-card position-relative overflow-hidden">
                        ${statusBadge}
                        <img src="../../assets/images/upload/${p.image}" 
                             class="card-img-top" 
                             alt="${p.name}" 
                             style="height:200px; object-fit:cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${p.name}</h5>
                            <p class="text-muted small">Danh mục: ${p.category_name || 'Chưa có'}</p>
                            <p class="text-primary fw-bold">${parseInt(p.price).toLocaleString('vi-VN')}đ</p>
                            ${p.color || p.size
                        ? `<p class="text-secondary small">${p.color ? 'Màu: ' + p.color : ''} ${p.size ? '· Size: ' + p.size : ''}</p>`
                        : ''
                    }
                            <div class="mt-auto d-flex gap-2">
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

            // Gắn sự kiện cho các nút
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', () => editProduct(btn.dataset.id));
            });
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', () => deleteProduct(btn.dataset.id));
            });

        } else {
            container.innerHTML = '<div class="col-12 text-center text-muted py-5"><h5>Chưa có sản phẩm nào.</h5></div>';
        }
    } catch (err) {
        alert('Lỗi tải sản phẩm');
        console.error(err);
    }
}

// Mở modal thêm/sửa
function openModal(product = null) {
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    const form = document.getElementById('productForm');
    const title = document.getElementById('modalTitle');
    const img = document.getElementById('currentImage');

    form.reset();
    img.style.display = 'none';

    if (product) {
        title.textContent = 'Sửa sản phẩm';
        document.getElementById('product_id').value = product.product_id;
        form.name.value = product.name;
        form.category_id.value = product.category_id;
        form.color.value = product.color || '';
        form.size.value = product.size || '';
        form.price.value = product.price;

        // Chỉ cần set checked = true/false → FormData sẽ tự xử lý
        document.getElementById('isActiveCheckbox').checked = (product.is_active == 1);

        if (product.image) {
            img.src = `../../assets/images/upload/${product.image}`;
            img.style.display = 'block';
        }
    } else {
        title.textContent = 'Thêm sản phẩm mới';
        document.getElementById('product_id').value = '';
        document.getElementById('isActiveCheckbox').checked = true; // mặc định hiển thị
    }

    modal.show();
}

// Sửa sản phẩm
async function editProduct(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/product/get_product.php?id=${id}`);
        const data = await res.json();
        if (data.success && data.data[0]) {
            openModal(data.data[0]);
        }
    } catch (err) {
        alert('Lỗi tải thông tin sản phẩm');
    }
}

// Xóa sản phẩm
async function deleteProduct(id) {
    if (!confirm('Xóa sản phẩm này? Không thể khôi phục!')) return;
    try {
        const res = await fetch(`${BASE_URL}/api/product/delete_product.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        alert(data.message || (data.success ? 'Xóa thành công' : 'Xóa thất bại'));
        if (data.success) loadProducts();
    } catch (err) {
        alert('Lỗi xóa sản phẩm');
    }
}

// Submit form
document.getElementById('productForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = document.getElementById('product_id').value;

    const url = id
        ? `${BASE_URL}/api/product/update_product.php?id=${id}`
        : `${BASE_URL}/api/product/create_product.php`;

    try {
        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        alert(data.message || (data.success ? 'Thành công!' : 'Thất bại'));
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            loadProducts();
        }
    } catch (err) {
        alert('Lỗi kết nối');
        console.error(err);
    }
});