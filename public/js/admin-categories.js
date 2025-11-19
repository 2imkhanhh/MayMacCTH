const BASE_URL = '/MayMacCTH';

document.addEventListener('DOMContentLoaded', () => {
    loadCategories();

    document.getElementById('btnAdd').addEventListener('click', () => openModal());

    document.getElementById('categoryForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('category_id').value;

        const url = id 
            ? `${BASE_URL}/api/category/update_category.php?id=${id}`
            : `${BASE_URL}/api/category/create_category.php`;

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            alert(data.message || (data.success ? 'Thành công!' : 'Thất bại'));
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                loadCategories();
            }
        } catch (err) {
            alert('Lỗi kết nối');
            console.error(err);
        }
    });
});

async function loadCategories() {
    try {
        const res = await fetch(`${BASE_URL}/api/category/get_category.php`);
        const data = await res.json();
        const container = document.getElementById('categoryList');
        container.innerHTML = '';

        if (data.success && data.data.length > 0) {
            data.data.forEach(cat => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4 mb-4';
                col.innerHTML = `
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="bi bi-tag-fill"></i>
                        </div>
                        <h5 class="category-name">${cat.name}</h5>
                        <p class="category-count">Sản phẩm: ${cat.product_count || 0}</p>
                        <div class="category-actions">
                            <button class="btn btn-category btn-edit" onclick="editCategory(${cat.category_id}, '${cat.name}')">
                                <i class="bi bi-pencil"></i> Sửa
                            </button>
                            <button class="btn btn-category btn-delete" onclick="deleteCategory(${cat.category_id})">
                                <i class="bi bi-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });
        } else {
            container.innerHTML = '<div class="col-12 text-center py-5"><h5 class="text-muted">Chưa có danh mục nào.</h5></div>';
        }
    } catch (err) {
        alert('Lỗi tải danh mục');
        console.error(err);
    }
}

function openModal(id = null, name = '') {
    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    const title = document.getElementById('modalTitle');
    const form = document.getElementById('categoryForm');

    form.reset();
    document.getElementById('category_id').value = '';

    if (id) {
        title.textContent = 'Sửa danh mục';
        form.name.value = name;
        document.getElementById('category_id').value = id;
    } else {
        title.textContent = 'Thêm danh mục mới';
    }
    modal.show();
}

function editCategory(id, name) {
    openModal(id, name);
}

async function deleteCategory(id) {
    if (!confirm('Xóa danh mục này? Các sản phẩm thuộc danh mục sẽ bị ảnh hưởng!')) return;
    try {
        const res = await fetch(`${BASE_URL}/api/category/delete_category.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        alert(data.message);
        if (data.success) loadCategories();
    } catch (err) {
        alert('Lỗi xóa danh mục');
    }
}