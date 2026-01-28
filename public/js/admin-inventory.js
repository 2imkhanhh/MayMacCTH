const BASE_URL = '/MayMacCTH';

document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('inventory-tab')) return;

    loadWarehouseOptions();
    loadInventory();

    const btnApplyFilter = document.getElementById('btnApplyFilter');
    if (btnApplyFilter) btnApplyFilter.onclick = loadInventory;

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

    const warehousesTab = document.getElementById('warehouses-tab');
    if (warehousesTab) {
        warehousesTab.addEventListener('shown.bs.tab', function () {
            loadWarehousesList();
        });
    }

    const btnAddToInventory = document.getElementById('btnAddToInventory');
    if (btnAddToInventory) {
        btnAddToInventory.onclick = function () {
            loadMissingVariants(); 
            loadWarehouseOptions(); 
            const modal = new bootstrap.Modal(document.getElementById('addToInventoryModal'));
            modal.show();
        };
    }

    const confirmAddBulkBtn = document.getElementById('confirmAddBulkBtn');
    if (confirmAddBulkBtn) confirmAddBulkBtn.onclick = handleAddBulkVariants;

    const inventoryTableBody = document.getElementById('inventoryTableBody');
    if (inventoryTableBody) {
        inventoryTableBody.addEventListener('click', function (e) {
            const btnHistory = e.target.closest('.btn-view-history');
            if (btnHistory) {
                loadHistoryLogs(btnHistory.dataset.id, btnHistory.dataset.title);
            }
            const btnAdjust = e.target.closest('.adjust-stock-btn');
            if (btnAdjust) {
                openAdjustModal(btnAdjust);
            }
        });
    }

    const confirmAdjustBtn = document.getElementById('confirmAdjustBtn');
    if (confirmAdjustBtn) confirmAdjustBtn.onclick = handleAdjustStock;

    // --- WAREHOUSE EVENTS ---
    const btnAddWarehouse = document.getElementById('btnAddWarehouse');
    if (btnAddWarehouse) btnAddWarehouse.onclick = () => openWarehouseModal();

    const warehouseForm = document.getElementById('warehouseForm');
    if (warehouseForm) warehouseForm.onsubmit = handleWarehouseSubmit;
});

// --- HELPER FUNCTIONS ---
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

// --- LOGIC INVENTORY ---
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

    if (Array.from(params).length > 0) url += '?' + params.toString();

    try {
        const res = await fetch(url);
        const result = await res.json();
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
            if (filterSelect) filterSelect.innerHTML = '<option value="">Tất cả kho</option>' + optionsHTML;
        }
    } catch (err) {
        console.error('Lỗi tải danh sách kho:', err);
    }
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
                <td>${item.color_name || 'Không màu'} <span style="background:${item.color_code}; width:16px; height:16px; border-radius:4px; display:inline-block; vertical-align:middle; border:1px solid #ccc;"></span></td>
                <td>${item.size || '-'}</td>
            `;
            tbody.appendChild(row);
        });

        const selectAll = document.getElementById('selectAllMissing');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.onclick = function () {
                const isChecked = this.checked;
                document.querySelectorAll('.missing-checkbox').forEach(cb => cb.checked = isChecked);
            };
        }
    } catch (err) {
        showToast('Lỗi tải danh sách variant chưa có kho', 'error');
        console.error(err);
    }
}

async function handleAddBulkVariants() {
    const selected = Array.from(document.querySelectorAll('.missing-checkbox:checked'))
        .map(cb => parseInt(cb.dataset.variantId));

    if (selected.length === 0) {
        showToast('Vui lòng chọn ít nhất 1 variant', 'error');
        return;
    }

    const initialQty = parseInt(document.getElementById('addBulkInitialQty').value) || 0;
    const lowStock = parseInt(document.getElementById('addBulkLowStock').value) || 10;
    const warehouseId = parseInt(document.getElementById('addWarehouseSelect').value) || 1;

    try {
        const formData = new FormData();
        formData.append('variant_ids', JSON.stringify(selected));
        formData.append('quantity', initialQty);
        formData.append('low_stock_threshold', lowStock); 
        formData.append('warehouse_id', warehouseId);

        const res = await fetch(`${BASE_URL}/api/inventory/add_bulk_variants.php`, {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            showToast(`Đã thêm ${selected.length} variant thành công!`, 'success');
            const modalEl = document.getElementById('addToInventoryModal');
            bootstrap.Modal.getInstance(modalEl).hide();
            loadInventory();
        } else {
            showToast(result.message || 'Thêm thất bại', 'error');
        }
    } catch (err) {
        showToast('Lỗi khi thêm kho', 'error');
        console.error(err);
    }
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
                tbody.innerHTML += `
                    <tr>
                        <td class="small">${log.created_at}</td>
                        <td>${typeBadge}</td>
                        <td class="${changeClass}">${changeSign}${log.change_quantity}</td>
                        <td>${log.previous_quantity}</td>
                        <td class="fw-bold">${log.new_quantity}</td>
                        <td class="text-start small">${log.note || '-'}</td>
                    </tr>`;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Chưa có lịch sử</td></tr>';
        }
    } catch (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi tải dữ liệu lịch sử</td></tr>';
    }
}

function openAdjustModal(btn) {
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

async function handleAdjustStock() {
    const inventoryId = this.dataset.id;
    const quantity = document.getElementById('adjustChangeQty').value;
    const note = document.getElementById('adjustNote').value;
    const type = document.querySelector('input[name="adjustType"]:checked').value;

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
            bootstrap.Modal.getInstance(document.getElementById('adjustStockModal')).hide();
            loadInventory();
        } else {
            showToast(result.message || 'Điều chỉnh thất bại', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Lỗi kết nối server', 'error');
    }
}

// --- LOGIC WAREHOUSE LIST ---
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
                    </tr>`;
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
                btn.onclick = function() { deleteWarehouse(this.dataset.id); };
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Chưa có nhà kho nào</td></tr>';
        }
    } catch (err) {
        console.error(err);
        showToast('Lỗi tải danh sách nhà kho', 'error');
    }
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

async function handleWarehouseSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('warehouse_id').value;
    const formData = new FormData(this);
    let url = id 
        ? `${BASE_URL}/api/warehouse/update_warehouse.php?id=${id}`
        : `${BASE_URL}/api/warehouse/create_warehouse.php`;

    try {
        const res = await fetch(url, { method: 'POST', body: formData });
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