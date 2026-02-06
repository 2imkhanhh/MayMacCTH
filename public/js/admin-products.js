const BASE_URL = '/MayMacCTH';
let currentEditId = null;

let colorsList = [];
let sizesList = [];
let variantsList = [];
let colorCounter = 0;
let selectedImages = [];
let imageIndex = 0;
let defaultInitialQty = 0;
let defaultLowStockThreshold = 10;
let isEditMode = false;

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

document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('products')) return;

    loadCategories();
    loadProducts();

    const btnAdd = document.getElementById('btnAdd');
    if (btnAdd) btnAdd.onclick = () => openModal();

    const btnSearch = document.getElementById('btnSearch');
    if (btnSearch) btnSearch.onclick = loadProducts;

    const searchInput = document.getElementById('searchName');
    if (searchInput) searchInput.addEventListener('keypress', e => e.key === 'Enter' && loadProducts());

    document.getElementById('addColorBtn').onclick = addCustomColor;
    document.getElementById('addSizeBtn').onclick = addCustomSize;
    document.getElementById('generateVariantsBtn').onclick = generateAllVariants;
    document.getElementById('clearAllVariants').onclick = clearAllVariants;

    const presetColors = document.getElementById('presetColors');
    if (presetColors) presetColors.onchange = handlePresetCheckbox;

    const presetSizes = document.getElementById('presetSizes');
    if (presetSizes) presetSizes.onchange = handlePresetCheckbox;

    document.getElementById('productForm').onsubmit = handleSubmit;

    document.getElementById('selectImagesBtn').onclick = () => {
        document.getElementById('bulkImageInput').click();
    };

    document.getElementById('bulkImageInput').onchange = function (e) {
        handleImageSelect(e);
    };

    document.addEventListener('click', function (e) {
        if (e.target.id === 'applyBulkBtn') {
            const bulkQty = parseInt(document.getElementById('bulkInitialQty')?.value) || 0;
            const bulkThreshold = parseInt(document.getElementById('bulkLowStock')?.value) || 10;

            variantsList.forEach(v => {
                v.initialQty = bulkQty;
                v.lowStockThreshold = bulkThreshold;
            });

            renderVariants();  // render lại bảng để hiển thị giá trị mới
            showToast(`Đã áp dụng số lượng ${bulkQty} và ngưỡng ${bulkThreshold} cho tất cả biến thể!`, 'success');
        }
    });
});

function showToast(message, type = 'success') {
    const toast = document.getElementById('globalToast');
    const icon = document.getElementById('toastIcon');
    const msg = document.getElementById('toastMessage');

    if (!toast) return;

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

function openModal(product = null) {
    currentEditId = product ? product.product_id : null;
    isEditMode = !!product;

    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    const form = document.getElementById('productForm');
    const title = document.getElementById('modalTitle');

    const qtyHeader = document.getElementById('initialQtyHeader');
    if (qtyHeader) {
        qtyHeader.textContent = isEditMode ? 'Tồn kho hiện tại' : 'Số lượng ban đầu';
    }

    form.reset();

    colorsList = [];
    sizesList = [];
    variantsList = [];
    colorCounter = 0;
    selectedImages = [];
    imageIndex = 0;

    document.getElementById('presetColors').checked = false;
    document.getElementById('presetSizes').checked = false;

    if (product) {
        title.textContent = 'Sửa sản phẩm';
        form.name.value = product.name || '';
        form.category_id.value = product.category_id || '';
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

                if (c.variants && typeof c.variants === 'object' && !Array.isArray(c.variants)) {
                    Object.values(c.variants).forEach(v => {
                        const normalizedSize = (v.size || '').trim().toUpperCase();
                        if (normalizedSize) {
                            if (!sizesList.includes(normalizedSize)) {
                                sizesList.push(normalizedSize);
                            }

                            variantsList.push({
                                colorId: tempId,
                                size: normalizedSize,
                                initialQty: v.quantity !== undefined ? parseInt(v.quantity) : 0,
                                lowStockThreshold: v.low_stock_threshold !== undefined ? parseInt(v.low_stock_threshold) : 10
                            });
                        }
                    });
                }

                else if (c.sizes && c.sizes.length > 0) {
                    c.sizes.forEach(size => {
                        const normalized = size.trim().toUpperCase();
                        if (normalized && !sizesList.includes(normalized)) {
                            sizesList.push(normalized);
                        }
                        variantsList.push({
                            colorId: tempId,
                            size: normalized,
                            initialQty: 0,
                            lowStockThreshold: 10
                        });
                    });
                }
            });
        }

        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        imagePreviewContainer.innerHTML = '';
        if (product.images && product.images.length > 0) {
            product.images.forEach((img) => {
                const div = document.createElement('div');
                div.className = 'col-6 col-md-4 col-lg-3 position-relative';
                div.dataset.isExisting = 'true';
                div.dataset.imageId = img.image_id;
                const checked = img.is_primary == 1 ? 'checked' : '';
                div.innerHTML = `
                    <div class="image-preview-item border rounded overflow-hidden shadow-sm bg-light">
                        <img src="../../assets/images/upload/${img.image}" class="w-100" style="height:200px; object-fit:cover;" onerror="this.src='../../assets/images/no-image.jpg'">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-image-btn">×</button>
                        <input type="hidden" name="existing_images[]" value="${img.image_id}">
                        <div class="p-2 bg-white border-top">
                            <div class="form-check">
                                <input class="form-check-input primary-radio" type="radio" name="primary_image" value="${img.image_id}" data-type="existing" ${checked}>
                                <label class="form-check-label small">Ảnh chính</label>
                            </div>
                        </div>
                    </div>`;
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
    } else {
        title.textContent = 'Thêm sản phẩm mới';
        form.price.value = 0;
        document.getElementById('isActiveCheckbox').checked = true;
        document.getElementById('imagePreviewContainer').innerHTML = '';
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

        const initialQty = v.initialQty !== undefined ? v.initialQty : defaultInitialQty;
        const lowThreshold = v.lowStockThreshold !== undefined ? v.lowStockThreshold : defaultLowStockThreshold;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${color.name}</td>
            <td>
                <span style="background:${color.code}; width:20px; height:20px; border-radius:4px; display:inline-block; border:1px solid #ccc;"></span>
            </td>
            <td>${v.size}</td>
            <td>
                <input type="number" class="form-control form-control-sm qty-input" 
                       value="${initialQty}" min="0" 
                       data-idx="${idx}" data-field="initialQty"
                       ${isEditMode ? 'disabled' : ''}>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm threshold-input" 
                       value="${lowThreshold}" min="0" 
                       data-idx="${idx}" data-field="lowStockThreshold">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-variant-btn" data-idx="${idx}">×</button>
            </td>
        `;
        tbody.appendChild(row);
    });

    if (!isEditMode) {
        tbody.querySelectorAll('input[data-field="initialQty"]').forEach(input => {
            input.addEventListener('input', function () {
                const idx = parseInt(this.dataset.idx);
                const value = parseInt(this.value) || 0;
                if (variantsList[idx]) {
                    variantsList[idx].initialQty = value;
                }
            });
        });
    }

    tbody.querySelectorAll('input[data-field="lowStockThreshold"]').forEach(input => {
        input.addEventListener('input', function () {
            const idx = parseInt(this.dataset.idx);
            const value = parseInt(this.value) || 0;
            if (variantsList[idx]) {
                variantsList[idx].lowStockThreshold = value;
            }
        });
    });

    tbody.querySelectorAll('.remove-variant-btn').forEach(btn => {
        btn.onclick = () => {
            const idx = parseInt(btn.dataset.idx);
            variantsList.splice(idx, 1);
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
    colorsList.push({ id: colorCounter++, name, code: codeInput.value || '#000000' });
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
            variantsList.push({
                colorId: color.id,
                size,
                initialQty: defaultInitialQty,
                lowStockThreshold: defaultLowStockThreshold
            });
        });
    });
    renderVariants();
    showToast(`Đã tạo ${variantsList.length}`, 'success');
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
        const toRemoveIds = colorsList.filter(c => presetNames.includes(c.name.toLowerCase())).map(c => c.id);
        colorsList = colorsList.filter(c => !presetNames.includes(c.name.toLowerCase()));
        variantsList = variantsList.filter(v => !toRemoveIds.includes(v.colorId));
        renderColors();
        renderVariants();
    }

    if (sizesChecked) {
        PRESET_SIZES.forEach(s => {
            if (!sizesList.includes(s)) sizesList.push(s);
        });
        renderSizes();
    } else {
        sizesList = sizesList.filter(s => !PRESET_SIZES.includes(s));
        variantsList = variantsList.filter(v => !PRESET_SIZES.includes(v.size));
        renderSizes();
        renderVariants();
    }
}

function handleImageSelect(e) {
    const files = Array.from(e.target.files || []);
    if (files.length === 0) return;
    const container = document.getElementById('imagePreviewContainer');

    files.forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const fileIndex = selectedImages.length;
        selectedImages.push(file);

        const reader = new FileReader();
        reader.onload = function (ev) {
            const div = document.createElement('div');
            div.className = 'col-6 col-md-4 col-lg-3 position-relative';
            div.dataset.fileIndex = fileIndex;
            const isFirstNew = container.querySelectorAll('.image-preview-item').length === 0;
            const checked = isFirstNew ? 'checked' : '';
            div.innerHTML = `
                <div class="image-preview-item border rounded overflow-hidden shadow-sm bg-light">
                    <img src="${ev.target.result}" class="w-100" style="height:200px; object-fit:cover;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-image-btn">×</button>
                    <div class="p-2 bg-white border-top">
                        <div class="form-check">
                            <input class="form-check-input primary-radio" type="radio" name="primary_image" value="${fileIndex}" data-type="new" ${checked}>
                            <label class="form-check-label small">Ảnh chính</label>
                        </div>
                    </div>
                </div>`;
            container.appendChild(div);

            div.querySelector('.remove-image-btn').onclick = () => {
                const idx = parseInt(div.dataset.fileIndex);
                div.remove();
                if (!isNaN(idx)) selectedImages.splice(idx, 1);
                if (!container.querySelector('input[name="primary_image"]:checked')) {
                    const first = container.querySelector('input[name="primary_image"]');
                    if (first) first.checked = true;
                }
            };
        };
        reader.readAsDataURL(file);
    });
    e.target.value = '';
}

async function handleSubmit(e) {
    e.preventDefault();
    const form = this;

    if (colorsList.length === 0 && sizesList.length === 0) {
        showToast('Vui lòng thêm ít nhất một màu sắc hoặc một kích thước!', 'error');
        return;
    }

    const hasNewImages = selectedImages.length > 0;
    const hasExisting = form.querySelectorAll('input[name="existing_images[]"]').length > 0;

    if (!hasNewImages && !hasExisting) {
        showToast('Phải có ít nhất 1 ảnh sản phẩm!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('name', form.querySelector('[name="name"]').value.trim());
    formData.append('category_id', form.querySelector('[name="category_id"]').value);
    formData.append('price', form.querySelector('[name="price"]').value);
    formData.append('description', form.querySelector('[name="description"]').value.trim());
    formData.append('is_active', form.querySelector('#isActiveCheckbox').checked ? '1' : '0');

    const existingImageInputs = form.querySelectorAll('input[name="existing_images[]"]');
    existingImageInputs.forEach(input => formData.append('existing_images[]', input.value));

    selectedImages.forEach(file => formData.append('images[]', file));

    const primaryRadio = form.querySelector('input[name="primary_image"]:checked');
    if (primaryRadio) {
        const value = primaryRadio.value;
        const type = primaryRadio.dataset.type;
        if (type === 'existing') formData.append('primary_image_id', value);
        else if (type === 'new') formData.append('primary_image_index', value);
    } else {
        const firstExisting = form.querySelector('input[name="existing_images[]"]');
        if (firstExisting) formData.append('primary_image_id', firstExisting.value);
        else if (selectedImages.length > 0) formData.append('primary_image_index', '0');
    }

    if (colorsList.length > 0) {
        colorsList.forEach(function (color, cIdx) {
            const colorName = color.name ? color.name.trim() : '';
            if (!colorName) return;

            formData.append(`colors[${cIdx}][name]`, colorName);
            formData.append(`colors[${cIdx}][code]`, color.code || '#000000');

            const colorVariants = variantsList.filter(v => v.colorId === color.id);

            colorVariants.forEach((v, vIdx) => {
                formData.append(`colors[${cIdx}][variants][${vIdx}][size]`, v.size || '');
                formData.append(`colors[${cIdx}][variants][${vIdx}][initial_qty]`, v.initialQty ?? 0);
                formData.append(`colors[${cIdx}][variants][${vIdx}][low_stock_threshold]`, v.lowStockThreshold ?? 10);
            });
        });
    } else if (sizesList.length > 0) {
        formData.append('colors[0][name]', 'Default');
        formData.append('colors[0][code]', '#000000');

        sizesList.forEach((size, idx) => {
            const variant = variantsList.find(v => v.size === size) || {};
            formData.append(`colors[0][variants][${idx}][size]`, size);
            formData.append(`colors[0][variants][${idx}][initial_qty]`, variant.initialQty ?? 0);
            formData.append(`colors[0][variants][${idx}][low_stock_threshold]`, variant.lowStockThreshold ?? 10);
        });
    }

    const url = currentEditId
        ? `${BASE_URL}/api/product/update_product.php?id=${currentEditId}`
        : `${BASE_URL}/api/product/create_product.php`;

    try {
        const res = await fetch(url, { method: 'POST', body: formData });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch { showToast('Lỗi server response!', 'error'); return; }

        if (data.success) {
            showToast(data.message || 'Lưu sản phẩm thành công!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            loadProducts();
            selectedImages = [];
        } else {
            showToast(data.message || 'Lưu sản phẩm thất bại!', 'error');
        }
    } catch (err) {
        console.error('Lỗi khi gửi request:', err);
        showToast('Lỗi kết nối server!', 'error');
    }
}