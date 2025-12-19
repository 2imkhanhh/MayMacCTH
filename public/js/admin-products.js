const BASE_URL = '/MayMacCTH';
let currentEditId = null;

let colorsList = [];     
let sizesList = [];      
let variantsList = [];    
let colorCounter = 0;

const PRESET_COLORS = [
    { name: 'Đen', code: '#000000' },
    { name: 'Trắng', code: '#FFFFFF' },
    { name: 'Xanh dương', code: '#0000FF' },
    { name: 'Đỏ', code: '#FF0000' },
    { name: 'Hồng', code: '#FFC0CB' },
    { name: 'Xanh lá', code: '#008000' },
    { name: 'Xám', code: '#808080' }
];
const PRESET_SIZES = ['S', 'M', 'L', 'XL'];

let imageIndex = 0; 

document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadProducts();

    document.getElementById('btnAdd').onclick = () => openModal();
    document.getElementById('btnSearch').onclick = loadProducts;
    document.getElementById('searchName').addEventListener('keypress', e => e.key === 'Enter' && loadProducts());

    document.getElementById('addColorBtn').onclick = addCustomColor;
    document.getElementById('addSizeBtn').onclick = addCustomSize;
    document.getElementById('generateVariantsBtn').onclick = generateAllVariants;
    document.getElementById('clearAllVariants').onclick = clearAllVariants;
    document.getElementById('presetColors').onchange = handlePresetCheckbox;
    document.getElementById('presetSizes').onchange = handlePresetCheckbox;

    document.querySelectorAll('.preset-radio').forEach(radio => {
        radio.onclick = handlePresetChange;
    });

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

    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => toast.classList.remove('show'), type === 'error' ? 5000 : 3000);
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
    const confirmed = await showConfirm('Xóa sản phẩm?');
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

function openModal(product = null) {
    currentEditId = product ? product.product_id : null;
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    const form = document.getElementById('productForm');
    const title = document.getElementById('modalTitle');

    form.reset();

    colorsList = [];
    sizesList = [];
    variantsList = [];
    colorCounter = 0;

    const presetColorsCb = document.getElementById('presetColors');
    const presetSizesCb = document.getElementById('presetSizes');
    presetColorsCb.checked = false;
    presetSizesCb.checked = false;

    if (product) {
        title.textContent = 'Sửa sản phẩm';

        form.name.value = product.name;
        form.category_id.value = product.category_id;
        form.price.value = product.price || 0;
        form.description.value = product.description || '';
        document.getElementById('isActiveCheckbox').checked = product.is_active == 1;

        if (product.colors && product.colors.length > 0) {
            product.colors.forEach(c => {
                const tempId = colorCounter++;
                colorsList.push({
                    id: tempId,
                    name: c.color_name || '',
                    code: c.color_code || '#000000'
                });

                if (c.sizes && c.sizes.length > 0) {
                    c.sizes.forEach(size => {
                        const normalized = size.trim().toUpperCase();
                        if (!sizesList.includes(normalized)) {
                            sizesList.push(normalized);
                        }
                        variantsList.push({
                            colorId: tempId,
                            size: normalized
                        });
                    });
                }
            });
        }

        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        imagePreviewContainer.innerHTML = ''; 

        if (product && product.images && product.images.length > 0) {
            product.images.forEach((img, idx) => {
                const div = document.createElement('div');
                div.className = 'col-6 col-md-4 col-lg-3 position-relative';

                const checked = img.is_primary == 1 ? 'checked' : '';

                div.innerHTML = `
                    <div class="image-preview-item border rounded overflow-hidden shadow-sm bg-light">
                        <img src="../../assets/images/upload/${img.image}" class="w-100" style="height:200px; object-fit:cover;" onerror="this.src='../../assets/images/no-image.jpg'">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-image-btn">×</button>
                        <input type="hidden" name="existing_images[]" value="${img.image_id}">
                        <div class="p-2 bg-white border-top">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="primary_image" value="${idx}" ${checked}>
                                <label class="form-check-label small">Ảnh chính</label>
                            </div>
                        </div>
                    </div>
                `;

                imagePreviewContainer.appendChild(div);

                div.querySelector('.remove-image-btn').onclick = () => {
                    div.remove();
                    if (!document.querySelector('input[name="primary_image"]:checked')) {
                        const firstRadio = imagePreviewContainer.querySelector('input[name="primary_image"]');
                        if (firstRadio) firstRadio.checked = true;
                    }
                };
            });
        }

        const presetColorNames = PRESET_COLORS.map(p => p.name.toLowerCase());
        const hasPresetColor = colorsList.some(color =>
            presetColorNames.includes(color.name.toLowerCase())
        );

        const hasPresetSize = sizesList.some(size =>
            PRESET_SIZES.includes(size)
        );

        if (hasPresetColor) {
            presetColorsCb.checked = true;
        }
        if (hasPresetSize) {
            presetSizesCb.checked = true;
        }
    } else {
        title.textContent = 'Thêm sản phẩm mới';
        form.price.value = 0;
        document.getElementById('isActiveCheckbox').checked = true;

        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        if (imagePreviewContainer) {
            imagePreviewContainer.innerHTML = '';
        }
        imageIndex = 0; 
    }
    renderColors();
    renderSizes();
    renderVariants();

    modal.show();
}

function renderColors() {
    const container = document.getElementById('colorList');
    container.innerHTML = '';
    colorsList.forEach(color => {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center justify-content-between p-3 mb-2 bg-light rounded border';
        div.style.borderLeft = `5px solid ${color.code}`;
        div.innerHTML = `
            <div class="d-flex align-items-center gap-3">
                <div class="color-swatch" style="background:${color.code}; width:32px; height:32px; border-radius:8px; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.15);"></div>
                <span class="fw-medium">${color.name}</span>
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-color-btn" data-id="${color.id}">×</button>
        `;
        container.appendChild(div);
    });

    document.querySelectorAll('.remove-color-btn').forEach(btn => {
        btn.onclick = () => {
            const id = parseInt(btn.dataset.id);
            colorsList = colorsList.filter(c => c.id !== id);
            variantsList = variantsList.filter(v => v.colorId !== id);
            renderColors();
            renderVariants();
        };
    });
}

function renderSizes() {
    const container = document.getElementById('sizeList');
    container.innerHTML = '';
    sizesList.forEach(size => {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center justify-content-between p-3 mb-2 bg-light rounded border';
        div.style.borderLeft = '5px solid #174DAF';
        div.innerHTML = `
            <span class="fw-medium fs-5">${size}</span>
            <button type="button" class="btn btn-sm btn-danger remove-size-btn">×</button>
        `;
        container.appendChild(div);
    });

    document.querySelectorAll('.remove-size-btn').forEach(btn => {
        btn.onclick = () => {
            const parent = btn.closest('div');
            const sizeText = parent.querySelector('span').textContent.trim();
            sizesList = sizesList.filter(s => s !== sizeText);
            variantsList = variantsList.filter(v => v.size !== sizeText);
            renderSizes();
            renderVariants();
        };
    });
}

function renderVariants() {
    const tbody = document.getElementById('variantsTable').querySelector('tbody');
    tbody.innerHTML = '';
    document.getElementById('variantCount').textContent = variantsList.length;

    if (variantsList.length === 0) {
        document.getElementById('variantsTableContainer').style.display = 'none';
        return;
    }
    document.getElementById('variantsTableContainer').style.display = 'block';

    variantsList.forEach((v, idx) => {
        const color = colorsList.find(c => c.id === v.colorId);
        if (!color) return;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${color.name}</td>
            <td><span style="background:${color.code}; width:20px; height:20px; border-radius:4px; display:inline-block; border:1px solid #ccc;"></span></td>
            <td>${v.size}</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-variant-btn" data-idx="${idx}">×</button></td>
        `;
        tbody.appendChild(row);
    });

    document.querySelectorAll('.remove-variant-btn').forEach(btn => {
        btn.onclick = () => {
            variantsList.splice(parseInt(btn.dataset.idx), 1);
            renderVariants();
        };
    });
}

function addCustomColor() {
    const nameInput = document.getElementById('newColorName');
    const codeInput = document.getElementById('newColorCode');
    const name = nameInput.value.trim();
    if (!name) {
        showToast('Vui lòng nhập tên màu!', 'error');
        return;
    }
    colorsList.push({
        id: colorCounter++,
        name,
        code: codeInput.value || '#000000'
    });
    nameInput.value = '';
    renderColors();
}

function addCustomSize() {
    const input = document.getElementById('newSize');
    const size = input.value.trim().toUpperCase();
    if (!size) {
        showToast('Vui lòng nhập kích thước!', 'error');
        return;
    }
    if (sizesList.includes(size)) {
        showToast('Kích thước đã tồn tại!', 'error');
        return;
    }
    sizesList.push(size);
    input.value = '';
    renderSizes();
}

function generateAllVariants() {
    if (colorsList.length === 0 || sizesList.length === 0) {
        showToast('Cần có ít nhất 1 màu và 1 kích thước!', 'error');
        return;
    }
    variantsList = [];
    colorsList.forEach(color => {
        sizesList.forEach(size => {
            variantsList.push({ colorId: color.id, size });
        });
    });
    renderVariants();
    showToast(`Đã tạo ${variantsList.length} biến thể!`, 'success');
}

function clearAllVariants() {
    variantsList = [];
    renderVariants();
}

function handlePresetCheckbox() {
    const colorsChecked = document.getElementById('presetColors').checked;
    const sizesChecked = document.getElementById('presetSizes').checked;

    if (colorsChecked) {
        PRESET_COLORS.forEach(p => {
            if (!colorsList.some(c => c.name.toLowerCase() === p.name.toLowerCase())) {
                colorsList.push({ id: colorCounter++, name: p.name, code: p.code });
            }
        });
        renderColors();
    } else {
        const presetNames = PRESET_COLORS.map(p => p.name.toLowerCase());
        const toRemoveIds = colorsList
            .filter(c => presetNames.includes(c.name.toLowerCase()))
            .map(c => c.id);
        
        colorsList = colorsList.filter(c => !presetNames.includes(c.name.toLowerCase()));
        variantsList = variantsList.filter(v => !toRemoveIds.includes(v.colorId));
        renderColors();
        renderVariants();
    }

    if (sizesChecked) {
        PRESET_SIZES.forEach(s => {
            if (!sizesList.includes(s)) {
                sizesList.push(s);
            }
        });
        renderSizes();
    } else {
        sizesList = sizesList.filter(s => !PRESET_SIZES.includes(s));
        variantsList = variantsList.filter(v => !PRESET_SIZES.includes(v.size));
        renderSizes();
        renderVariants();
    }
}

async function handleSubmit(e) {
    e.preventDefault();
    const form = this;

    if (colorsList.length === 0 && sizesList.length === 0) {
        showToast('Vui lòng thêm ít nhất một màu sắc hoặc một kích thước!', 'error');
        return;
    }

    document.querySelectorAll('input[name^="colors["]').forEach(el => el.remove());
    if (colorsList.length > 0) {
        colorsList.forEach((color, idx) => {
            const nameInp = document.createElement('input');
            nameInp.type = 'hidden';
            nameInp.name = `colors[${idx}][name]`;
            nameInp.value = color.name;
            form.appendChild(nameInp);

            const codeInp = document.createElement('input');
            codeInp.type = 'hidden';
            codeInp.name = `colors[${idx}][code]`;
            codeInp.value = color.code;
            form.appendChild(codeInp);

            const sizesForThisColor = variantsList
                .filter(v => v.colorId === color.id)
                .map(v => v.size);

            sizesForThisColor.forEach(size => {
                const sizeInp = document.createElement('input');
                sizeInp.type = 'hidden';
                sizeInp.name = `colors[${idx}][sizes]`;
                sizeInp.value = size;
                form.appendChild(sizeInp);
            });
        });
    } else {
        const defaultColorIdx = 0;
        const nameInp = document.createElement('input');
        nameInp.type = 'hidden';
        nameInp.name = `colors[${defaultColorIdx}][name]`;
        nameInp.value = 'Default'; 
        form.appendChild(nameInp);

        const codeInp = document.createElement('input');
        codeInp.type = 'hidden';
        codeInp.name = `colors[${defaultColorIdx}][code]`;
        codeInp.value = '#000000';
        form.appendChild(codeInp);

        sizesList.forEach(size => {
            const sizeInp = document.createElement('input');
            sizeInp.type = 'hidden';
            sizeInp.name = `colors[${defaultColorIdx}][sizes]`;
            sizeInp.value = size;
            form.appendChild(sizeInp);
        });
    }

    const formData = new FormData(form);
    const url = currentEditId
        ? `${BASE_URL}/api/product/update_product.php?id=${currentEditId}`
        : `${BASE_URL}/api/product/create_product.php`;

    try {
        const res = await fetch(url, { method: 'POST', body: formData });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } 
        catch { console.error(text); showToast('Lỗi server!', 'error'); return; }

        showToast(data.message || (data.success ? 'Lưu thành công!' : 'Lưu thất bại!'), data.success ? 'success' : 'error');
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            loadProducts();
        }
    } catch (err) {
        showToast('Lỗi kết nối!', 'error');
        console.error(err);
    }
}

document.getElementById('selectImagesBtn').onclick = () => {
    document.getElementById('bulkImageInput').click();
};

document.getElementById('bulkImageInput').onchange = function(e) {
    const files = e.target.files;
    if (files.length === 0) return;

    const container = document.getElementById('imagePreviewContainer');

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (!file.type.match('image.*')) continue;

        const reader = new FileReader();
        reader.onload = function(ev) {
            const div = document.createElement('div');
            div.className = 'col-6 col-md-4 col-lg-3 position-relative';
            const noPrimaryYet = !document.querySelector('input[name="primary_image"]:checked');
            const checked = (container.children.length === 0 && i === 0 && noPrimaryYet) ? 'checked' : '';

            div.innerHTML = `
                <div class="image-preview-item border rounded overflow-hidden shadow-sm bg-light">
                    <img src="${ev.target.result}" class="w-100" style="height:200px; object-fit:cover;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-image-btn">×</button>
                    <div class="p-2 bg-white border-top">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="primary_image" value="${imageIndex}" ${checked}>
                            <label class="form-check-label small">Ảnh chính</label>
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(div);
            imageIndex++;

            div.querySelector('.remove-image-btn').onclick = () => {
                div.remove();
                if (!document.querySelector('input[name="primary_image"]:checked')) {
                    const firstRadio = container.querySelector('input[name="primary_image"]');
                    if (firstRadio) firstRadio.checked = true;
                }
            };
        };
        reader.readAsDataURL(file);
    }
    e.target.value = '';
};