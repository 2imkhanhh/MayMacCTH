const BASE_URL = '/MayMacCTH';
let currentEditId = null;

let colorsList = [];
let sizesList = [];
let variantsList = [];
let colorCounter = 0;
let selectedImages = [];

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
    selectedImages = [];
    imageIndex = 0;

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

        if (product.images && product.images.length > 0) {
            product.images.forEach((img, idx) => {
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
                                <input class="form-check-input primary-radio" 
                                    type="radio" 
                                    name="primary_image" 
                                    value="${img.image_id}" 
                                    data-type="existing"
                                    ${checked}>
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
    } else {
        title.textContent = 'Thêm sản phẩm mới';
        form.price.value = 0;
        document.getElementById('isActiveCheckbox').checked = true;

        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        imagePreviewContainer.innerHTML = '';
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
    existingImageInputs.forEach(input => {
        formData.append('existing_images[]', input.value);
    });

    selectedImages.forEach(file => {
        formData.append('images[]', file);
    });

    const primaryRadio = form.querySelector('input[name="primary_image"]:checked');
    if (primaryRadio) {
        const value = primaryRadio.value;
        const type = primaryRadio.dataset.type;

        if (type === 'existing') {
            formData.append('primary_image_id', value);
        } else if (type === 'new') {
            formData.append('primary_image_index', value);
        }
    } else {
        const firstExisting = form.querySelector('input[name="existing_images[]"]');
        if (firstExisting) {
            formData.append('primary_image_id', firstExisting.value);
        } else if (selectedImages.length > 0) {
            formData.append('primary_image_index', '0');
        }
    }

    if (colorsList.length > 0) {
        colorsList.forEach((color, idx) => {
            formData.append(`colors[${idx}][name]`, color.name.trim());
            formData.append(`colors[${idx}][code]`, color.code);
            const sizesForColor = variantsList
                .filter(v => v.colorId === color.id)
                .map(v => v.size);
            sizesForColor.forEach(size => {
                formData.append(`colors[${idx}][sizes][]`, size);
            });
        });
    } else if (sizesList.length > 0) {
        formData.append('colors[0][name]', 'Default');
        formData.append('colors[0][code]', '#000000');
        sizesList.forEach(size => {
            formData.append('colors[0][sizes][]', size);
        });
    }

    const url = currentEditId
        ? `${BASE_URL}/api/product/update_product.php?id=${currentEditId}`
        : `${BASE_URL}/api/product/create_product.php`;

    try {
        // Debug
        // for (let [key, value] of formData.entries()) {
        //     console.log(key, value);
        // }

        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const text = await res.text();
        console.log('Raw server response:', text);

        let data;
        try {
            data = JSON.parse(text);
        } catch (parseErr) {
            console.error('Lỗi parse JSON từ server:', parseErr);
            showToast('Phản hồi từ server không đúng định dạng!', 'error');
            return;
        }

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
        showToast('Lỗi kết nối hoặc server không phản hồi!', 'error');
    }
}

async function loadInventory() {
    const name = document.getElementById('filterName')?.value.trim() || '';
    const color = document.getElementById('filterColor')?.value.trim() || '';
    const size = document.getElementById('filterSize')?.value.trim() || '';
    const warehouse = document.getElementById('filterWarehouse')?.value || '';
    const isLowStock = document.getElementById('filterLowStock')?.checked ? '1' : '0';

    let url = `${BASE_URL}/api/inventory/get_all.php`;
    
    const params = new URLSearchParams();
    if (name) params.append('product_name', name);
    if (color) params.append('color', color); 
    if (size) params.append('size', size);    
    if (warehouse) params.append('warehouse_id', warehouse);
    if (isLowStock === '1') params.append('low_stock', '1');

    if (Array.from(params).length > 0) {
        url += '?' + params.toString();
    }

    try {
        const res = await fetch(url);
        const text = await res.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Lỗi JSON:', text);
            return;
        }

        const tbody = document.getElementById('inventoryTableBody');
        const noData = document.getElementById('noInventoryMessage');

        if (!tbody) return;
        tbody.innerHTML = '';

        if (!result.success || !result.data || result.data.length === 0) {
            if (noData) noData.classList.remove('d-none');
            return;
        }

        if (noData) noData.classList.add('d-none');

        result.data.forEach(item => {
            const isLow = item.quantity <= (parseInt(item.low_stock_threshold) || 10);
            const stockClass = isLow ? 'text-danger fw-bold' : 'text-success fw-bold';
            const rowClass = isLow ? 'table-danger' : ''; 
            
            const warehouseName = item.warehouse_name || 'Kho chưa đặt tên';
            const warehouseAddress = item.address ? `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${item.address}</small>` : '';
            const variantText = `${item.color_name || 'Không màu'} ${item.size ? `- ${item.size}` : ''}`.trim();

            const row = `
                <tr class="${rowClass}">
                    <td><span class="fw-bold">${item.product_name}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span style="background:${item.color_code || '#ccc'}; width:24px; height:24px; border-radius:4px; border:1px solid #ddd; display:inline-block;"></span>
                            ${item.color_name || '-'}
                        </div>
                    </td>
                    <td>${item.size || '-'}</td>
                    <td><strong>${warehouseName}</strong>${warehouseAddress}</td>
                    <td class="text-center ${stockClass}">${item.quantity}</td>
                    <td class="text-center">${item.low_stock_threshold || 10}</td>
                    
                    <td class="text-center">
                        <button class="btn btn-sm btn-view-history" 
                                data-id="${item.inventory_id}"
                                data-title="${item.product_name} - ${variantText}">
                            <i class="bi bi-clock-history"></i> Xem
                        </button>
                    </td>

                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary adjust-stock-btn"
                                data-inventory-id="${item.inventory_id}"
                                data-variant="${item.product_name} - ${variantText}"
                                data-current-qty="${item.quantity}">
                            <i class="bi bi-pencil-square"></i> Điều chỉnh
                        </button>
                    </td>
                </tr>`;
            tbody.innerHTML += row;
        });

    } catch (err) {
        showToast('Lỗi tải danh sách kho!', 'error');
        console.error('Load inventory error:', err);
    }
}

async function loadWarehouseOptions() {
    try {
        const res = await fetch(`${BASE_URL}/api/inventory/get_warehouses.php`);
        const result = await res.json();
        
        const modalSelect = document.getElementById('addWarehouseSelect');
        const filterSelect = document.getElementById('filterWarehouse');

        if (result.success && result.data && result.data.length > 0) {
            const optionsHTML = result.data.map(wh => 
                `<option value="${wh.warehouse_id}">${wh.name}</option>`
            ).join('');

            if (modalSelect) modalSelect.innerHTML = optionsHTML;
            
            if (filterSelect) {
                filterSelect.innerHTML = '<option value="">Tất cả kho</option>' + optionsHTML;
            }
        }
    } catch (err) {
        console.error('Lỗi tải danh sách kho:', err);
    }
}

const btnApplyFilter = document.getElementById('btnApplyFilter');
if (btnApplyFilter) {
    btnApplyFilter.onclick = loadInventory;
}

const btnClearFilter = document.getElementById('btnClearFilter');
if (btnClearFilter) {
    btnClearFilter.onclick = function() {
        document.getElementById('filterName').value = '';
        document.getElementById('filterColor').value = '';
        document.getElementById('filterSize').value = '';
        document.getElementById('filterWarehouse').value = '';
        document.getElementById('filterLowStock').checked = false;
        loadInventory();
    };
}

const inventoryTab = document.getElementById('inventory-tab');
if (inventoryTab) {
    inventoryTab.addEventListener('shown.bs.tab', function () {
        loadWarehouseOptions(); 
        loadInventory();        
    });
}

let searchTimeout = null;

const btnAddToInventory = document.getElementById('btnAddToInventory');
if (btnAddToInventory) {
    btnAddToInventory.onclick = function () {
        loadMissingVariants(); 
        loadWarehouseOptions(); 
        
        const modal = new bootstrap.Modal(document.getElementById('addToInventoryModal'));
        modal.show();
    };
}

async function loadMissingVariants() {
    try {
        const res = await fetch(`${BASE_URL}/api/inventory/get_missing_variants.php`);
        const result = await res.json();

        const tbody = document.getElementById('missingVariantsTableBody');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        if (!result.success || result.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Tất cả sản phẩm đã có trong kho</td></tr>';
            return;
        }

        result.data.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="checkbox" class="missing-checkbox" data-variant-id="${item.variant_id}"></td>
                <td>${item.product_name}</td>
                <td>
                    ${item.color_name || 'Không màu'} 
                    <span style="background:${item.color_code}; width:16px; height:16px; border-radius:4px; display:inline-block; vertical-align:middle; border:1px solid #ccc;"></span>
                </td>
                <td>${item.size || '-'}</td>
            `;
            tbody.appendChild(row);
        });

        const selectAll = document.getElementById('selectAllMissing');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.onclick = function () {
                const isChecked = this.checked;
                document.querySelectorAll('.missing-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            };
        }

    } catch (err) {
        showToast('Lỗi tải danh sách variant chưa có kho', 'error');
        console.error('Load missing variants error:', err);
    }
}

const confirmAddBulkBtn = document.getElementById('confirmAddBulkBtn');
if (confirmAddBulkBtn) {
    confirmAddBulkBtn.onclick = async function () {
        const selected = Array.from(document.querySelectorAll('.missing-checkbox:checked'))
            .map(cb => parseInt(cb.dataset.variantId));

        if (selected.length === 0) {
            showToast('Vui lòng chọn ít nhất 1 variant', 'error');
            return;
        }

        const initialQty = parseInt(document.getElementById('addBulkInitialQty').value) || 0;
        const warehouseId = parseInt(document.getElementById('addWarehouseSelect').value) || 1;

        // if (!confirm(`Thêm ${selected.length} variant vào kho đã chọn với số lượng ban đầu ${initialQty}?`)) return;

        try {
            const formData = new FormData();
            formData.append('variant_ids', JSON.stringify(selected));
            formData.append('quantity', initialQty);
            formData.append('warehouse_id', warehouseId);

            const res = await fetch(`${BASE_URL}/api/inventory/add_bulk_variants.php`, {
                method: 'POST',
                body: formData
            });

            const result = await res.json();

            if (result.success) {
                showToast(`Đã thêm ${selected.length} variant thành công!`, 'success');
                const modalEl = document.getElementById('addToInventoryModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
                
                loadInventory(); 
            } else {
                showToast(result.message || 'Thêm thất bại', 'error');
            }
        } catch (err) {
            showToast('Lỗi khi thêm kho', 'error');
            console.error(err);
        }
    };
}

const inventoryTableBody = document.getElementById('inventoryTableBody');
if (inventoryTableBody) {
    inventoryTableBody.addEventListener('click', function (e) {
        const btnHistory = e.target.closest('.btn-view-history');
        if (btnHistory) {
            const inventoryId = btnHistory.dataset.id;
            const title = btnHistory.dataset.title;
            
            loadHistoryLogs(inventoryId, title);
        }
        const btn = e.target.closest('.adjust-stock-btn');
        if (btn) {
            const inventoryId = btn.dataset.inventoryId;
            const variantInfo = btn.dataset.variant;
            const currentQty = btn.dataset.currentQty;

            document.getElementById('adjustVariantInfo').textContent = variantInfo;
            document.getElementById('adjustCurrentQty').textContent = currentQty;
            
            document.getElementById('adjustChangeQty').value = ''; 
            document.getElementById('adjustNote').value = '';
            
            document.getElementById('typeImport').checked = true;

            document.getElementById('confirmAdjustBtn').dataset.id = inventoryId;

            const modal = new bootstrap.Modal(document.getElementById('adjustStockModal'));
            modal.show();
        }
    });
}

async function loadHistoryLogs(inventoryId, title) {
    const tbody = document.getElementById('historyTableBody');
    const modalTitle = document.getElementById('historyTitle');
    
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></td></tr>';
    modalTitle.textContent = title;

    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    modal.show();

    try {
        const res = await fetch(`${BASE_URL}/api/inventory/get_history.php?inventory_id=${inventoryId}`);
        const result = await res.json();

        tbody.innerHTML = '';

        if (result.success && result.data && result.data.length > 0) {
            result.data.forEach(log => {
                let typeBadge = '';
                let changeClass = '';
                
                if (log.transaction_type === 'import') {
                    typeBadge = '<span class="badge bg-success">Nhập hàng</span>';
                    changeClass = 'text-success fw-bold';
                } else if (log.transaction_type === 'export') {
                    typeBadge = '<span class="badge bg-danger">Xuất hàng</span>';
                    changeClass = 'text-danger fw-bold';
                } else if (log.transaction_type === 'sale') {
                    typeBadge = '<span class="badge bg-primary">Bán hàng</span>';
                    changeClass = 'text-primary fw-bold';
                } else {
                    typeBadge = `<span class="badge bg-secondary">${log.transaction_type}</span>`;
                }

                const changeSign = parseInt(log.change_quantity) > 0 ? '+' : '';

                const row = `
                    <tr>
                        <td class="small">${log.created_at}</td>
                        <td>${typeBadge}</td>
                        <td class="${changeClass}">${changeSign}${log.change_quantity}</td>
                        <td>${log.previous_quantity}</td>
                        <td class="fw-bold">${log.new_quantity}</td>
                        <td class="text-start small">${log.note || '-'}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Chưa có lịch sử</td></tr>';
        }

    } catch (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi tải dữ liệu lịch sử</td></tr>';
    }
}

const confirmAdjustBtn = document.getElementById('confirmAdjustBtn');
if (confirmAdjustBtn) {
    confirmAdjustBtn.onclick = async function () {
        const inventoryId = this.dataset.id;
        const quantity = document.getElementById('adjustChangeQty').value;
        const note = document.getElementById('adjustNote').value;
        
        const typeRadio = document.querySelector('input[name="adjustType"]:checked');
        const type = typeRadio ? typeRadio.value : 'import';

        if (!inventoryId) {
            showToast('Lỗi: Không tìm thấy ID kho', 'error');
            return;
        }
        if (!quantity || parseInt(quantity) <= 0) {
            showToast('Vui lòng nhập số lượng lớn hơn 0', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('inventory_id', inventoryId);
            formData.append('quantity', quantity); 
            formData.append('type', type);         
            formData.append('note', note);

            const res = await fetch(`${BASE_URL}/api/inventory/adjust.php`, {
                method: 'POST',
                body: formData
            });
            const result = await res.json();

            if (result.success) {
                showToast(`Đã ${type === 'import' ? 'nhập' : 'xuất'} kho thành công!`, 'success');
                
                const modalEl = document.getElementById('adjustStockModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                loadInventory();
            } else {
                showToast(result.message || 'Điều chỉnh thất bại', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Lỗi kết nối server', 'error');
        }
    };
}

document.getElementById('selectImagesBtn').onclick = () => {
    document.getElementById('bulkImageInput').click();
};

document.getElementById('bulkImageInput').onchange = function (e) {
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
                            <input class="form-check-input primary-radio" 
                                type="radio" 
                                name="primary_image" 
                                value="${fileIndex}" 
                                data-type="new"
                                ${checked}>
                            <label class="form-check-label small">Ảnh chính</label>
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(div);

            div.querySelector('.remove-image-btn').onclick = () => {
                const idx = parseInt(div.dataset.fileIndex);
                div.remove();
                if (!isNaN(idx)) selectedImages.splice(idx, 1);

                const previews = container.querySelectorAll('[data-file-index]');
                previews.forEach((p, newIdx) => {
                    p.dataset.fileIndex = newIdx;
                    p.querySelector('input[name="primary_image"]').value = newIdx;
                });

                if (!container.querySelector('input[name="primary_image"]:checked') && previews.length > 0) {
                    previews[0].querySelector('input').checked = true;
                }
            };
        };
        reader.readAsDataURL(file);
    });

    e.target.value = '';
};

const warehousesTab = document.getElementById('warehouses-tab');
if (warehousesTab) {
    warehousesTab.addEventListener('shown.bs.tab', function () {
        loadWarehousesList();
    });
}

async function loadWarehousesList() {
    try {
        const res = await fetch(`${BASE_URL}/api/warehouse/get_warehouse.php`);
        const result = await res.json();
        const tbody = document.getElementById('warehouseTableBody');
        tbody.innerHTML = '';

        if (result.success && result.data.length > 0) {
            result.data.forEach(wh => {
                const row = `
                    <tr>
                        <td>#${wh.warehouse_id}</td>
                        <td class="fw-bold">${wh.name}</td>
                        <td>${wh.phone || '-'}</td>
                        <td>${wh.address || '-'}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-2 btn-edit-wh" 
                                data-id="${wh.warehouse_id}" 
                                data-name="${wh.name}" 
                                data-phone="${wh.phone}" 
                                data-address="${wh.address}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-wh" data-id="${wh.warehouse_id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            document.querySelectorAll('.btn-edit-wh').forEach(btn => {
                btn.onclick = function() {
                    openWarehouseModal({
                        id: this.dataset.id,
                        name: this.dataset.name,
                        phone: this.dataset.phone,
                        address: this.dataset.address
                    });
                };
            });

            document.querySelectorAll('.btn-delete-wh').forEach(btn => {
                btn.onclick = function() {
                    deleteWarehouse(this.dataset.id);
                };
            });

        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Chưa có nhà kho nào</td></tr>';
        }
    } catch (err) {
        console.error(err);
        showToast('Lỗi tải danh sách nhà kho', 'error');
    }
}

const btnAddWarehouse = document.getElementById('btnAddWarehouse');
if (btnAddWarehouse) {
    btnAddWarehouse.onclick = function() {
        openWarehouseModal();
    };
}

function openWarehouseModal(data = null) {
    const modalEl = document.getElementById('warehouseModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('warehouseForm');
    const title = document.getElementById('warehouseModalTitle');

    form.reset();

    if (data) {
        title.textContent = 'Cập nhật Nhà kho';
        document.getElementById('warehouse_id').value = data.id;
        document.getElementById('whName').value = data.name;
        document.getElementById('whPhone').value = data.phone;
        document.getElementById('whAddress').value = data.address;
    } else {
        title.textContent = 'Thêm Nhà kho mới';
        document.getElementById('warehouse_id').value = '';
    }

    modal.show();
}

const warehouseForm = document.getElementById('warehouseForm');
if (warehouseForm) {
    warehouseForm.onsubmit = async function(e) {
        e.preventDefault();
        
        const id = document.getElementById('warehouse_id').value;
        const formData = new FormData(this);
        
        let url = `${BASE_URL}/api/warehouse/create_warehouse.php`;
        if (id) {
            url = `${BASE_URL}/api/warehouse/update_warehouse.php?id=${id}`;
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const result = await res.json();

            if (result.success) {
                showToast(result.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('warehouseModal')).hide();
                loadWarehousesList(); 
                
            } else {
                showToast(result.message, 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Lỗi kết nối server', 'error');
        }
    };
}

async function deleteWarehouse(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa nhà kho này?')) return;

    try {
        const res = await fetch(`${BASE_URL}/api/warehouse/delete_warehouse.php?id=${id}`);
        const result = await res.json();

        if (result.success) {
            showToast(result.message, 'success');
            loadWarehousesList();
        } else {
            showToast(result.message, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Lỗi kết nối server', 'error');
    }
}