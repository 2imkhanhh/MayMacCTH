<?php
require_once 'auth_middleware.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm</title>
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/css/admin-product.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- <style>
        .color-item,
        .image-item {
            border: 1px dashed #ccc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            position: relative;
        }

        .remove-color,
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
        }

        .size-item {
            margin: 5px 0;
        }
    </style> -->
</head>

<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 product-header">
            <h2>Quản lý Sản phẩm & Kho hàng</h2>
        </div>

        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab">
                    <i class="bi bi-box"></i> Quản lý Sản phẩm
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                    <i class="bi bi-box-seam"></i> Quản lý Tồn kho
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="warehouses-tab" data-bs-toggle="tab" data-bs-target="#warehouses" type="button" role="tab">
                    <i class="bi bi-building"></i> Danh sách Nhà kho
                </button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">
            <div class="tab-pane fade show active" id="products" role="tabpanel" aria-labelledby="products-tab">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <button class="btn btn-primary" id="btnAdd">Thêm sản phẩm mới</button>
                    </div>
                </div>

                <div class="card mb-4 admin-search-card">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Lọc theo Danh mục</label>
                                <select class="form-select admin-select" id="filterCategory">
                                    <option value="">Tất cả danh mục</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted">Tìm kiếm theo Tên</label>
                                <input type="text" class="form-control admin-input-search" id="searchName" placeholder="Tìm theo tên...">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100 admin-search-btn" id="btnSearch">
                                    <i class="bi bi-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="productList" class="row"></div>
            </div>

            <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                <div class="card mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-funnel"></i> Bộ lọc tìm kiếm
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Tên sản phẩm</label>
                                <input type="text" class="form-control" id="filterName" placeholder="Nhập tên...">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Màu sắc</label>
                                <input type="text" class="form-control" id="filterColor" placeholder="Vd: Đen, Đỏ...">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Kích thước</label>
                                <input type="text" class="form-control" id="filterSize" placeholder="Vd: XL...">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Kho hàng</label>
                                <select class="form-select" id="filterWarehouse">
                                    <option value="">Tất cả kho</option>
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="filterLowStock">
                                    <label class="form-check-label text-danger fw-bold" for="filterLowStock">
                                        Sắp hết hàng
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 text-end border-top pt-3 mt-3">
                                <button class="btn btn-secondary me-2" id="btnClearFilter">
                                    <i class="bi bi-x-circle"></i> Xóa lọc
                                </button>
                                <button class="btn" id="btnApplyFilter">
                                    <i class="bi bi-search"></i> Tìm kiếm
                                </button>
                                <button class="btn btn-success ms-2" id="btnAddToInventory">
                                    <i class="bi bi-plus-circle"></i> Thêm hàng vào kho
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Danh sách tồn kho</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered align-middle" id="inventoryTable">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Màu sắc</th>
                                <th>Kích thước</th>
                                <th>Kho hàng</th>
                                <th class="text-center">Tồn kho</th>
                                <th class="text-center">Cảnh báo</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryTableBody"></tbody>
                    </table>
                </div>

                <div id="noInventoryMessage" class="text-center py-5 text-muted d-none">
                    <i class="bi bi-inbox fs-1"></i>
                    <h5 class="mt-3">Không tìm thấy dữ liệu phù hợp</h5>
                </div>
            </div>
            <div class="tab-pane fade" id="warehouses" role="tabpanel" aria-labelledby="warehouses-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Danh sách Nhà kho</h5>
                    <button class="btn btn-primary" id="btnAddWarehouse">
                        <i class="bi bi-plus-lg"></i> Thêm Nhà kho mới
                    </button>
                </div>

                <div class="table-responsive bg-white rounded shadow-sm p-3">
                    <table class="table table-hover align-middle" id="warehouseTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên Nhà kho</th>
                                <th>Số điện thoại</th>
                                <th>Địa chỉ</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="warehouseTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade" id="warehouseModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="warehouseModalTitle">Thêm Nhà kho</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="warehouseForm">
                            <div class="modal-body">
                                <input type="hidden" id="warehouse_id" name="warehouse_id">
                                <div class="mb-3">
                                    <label class="form-label">Tên Nhà kho <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="whName" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control" name="phone" id="whPhone">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <textarea class="form-control" name="address" id="whAddress" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-primary">Lưu thông tin</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="productForm" enctype="multipart/form-data">
                    <input type="hidden" id="product_id" name="product_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label>Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-4">
                                <label>Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Chọn danh mục</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label>Giá sản phẩm (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price" min="0" value="0" required>
                        </div>

                        <div class="mt-3">
                            <label>Mô tả sản phẩm</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Nhập mô tả chi tiết..."></textarea>
                        </div>

                        <hr>
                        <h6>Thuộc tính sản phẩm</h6>
                        <p class="text-muted small">Tick vào ô để thêm nhanh màu sắc hoặc kích thước.</p>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input preset-checkbox" type="checkbox" id="presetColors">
                                <label class="form-check-label fw-bold" for="presetColors">
                                    Màu sắc
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input preset-checkbox" type="checkbox" id="presetSizes">
                                <label class="form-check-label fw-bold" for="presetSizes">
                                    Kích thước
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="fw-bold">Màu sắc</label>
                                <div id="colorList" class="mb-3">
                                </div>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="newColorName" placeholder="Tên màu">
                                    <input type="color" class="form-control form-control-color" id="newColorCode" value="#FFFF00">
                                    <button class="btn btn-outline-primary" type="button" id="addColorBtn">+ Thêm màu</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Kích thước</label>
                                <div id="sizeList" class="mb-3">
                                </div>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="newSize" placeholder="Kích thước">
                                    <button class="btn btn-outline-primary" type="button" id="addSizeBtn">+ Thêm kích thước</button>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-success" id="generateVariantsBtn">Tạo tổ hợp</button>
                        </div>

                        <div id="variantsTableContainer" style="display:none;">
                            <h6>Tổ hợp đã tạo (<span id="variantCount">0</span>)</h6>
                            <table class="table table-sm table-bordered" id="variantsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Màu</th>
                                        <th>Mã màu</th>
                                        <th>Kích thước</th>
                                        <th><button type="button" class="btn btn-sm btn-danger" id="clearAllVariants">Xóa tất cả</button></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <hr>
                        <h6>Ảnh sản phẩm</h6>
                        <input type="file" id="bulkImageInput" name="images[]" accept="image/*" multiple style="display:none;">
                        <div class="text-start mb-3">
                            <button type="button" class="btn btn-primary" id="selectImagesBtn">
                                <i class="bi bi-images me-2"></i> Chọn ảnh sản phẩm
                            </button>
                        </div>
                        <div id="imagePreviewContainer" class="row g-3"></div>

                        <hr>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="isActiveCheckbox" checked>
                            <label class="form-check-label" for="isActiveCheckbox">Hiển thị sản phẩm</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">Lưu sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adjustStockModalLabel">Điều chỉnh tồn kho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold mb-1">Biến thể:</p>
                    <p id="adjustVariantInfo" class="mb-3 text-muted"></p>

                    <p class="fw-bold mb-1">Tồn kho hiện tại:</p>
                    <p id="adjustCurrentQty" class="fs-4 fw-bold mb-3"></p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Loại thao tác:</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustType" id="typeImport" value="import" checked>
                                <label class="form-check-label text-success fw-bold" for="typeImport">
                                    <i class="bi bi-box-arrow-in-down"></i> Nhập hàng
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustType" id="typeExport" value="export">
                                <label class="form-check-label text-danger fw-bold" for="typeExport">
                                    <i class="bi bi-box-arrow-up"></i> Xuất hàng
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="adjustChangeQty" class="form-label fw-bold">Số lượng:</label>
                        <input type="number" class="form-control" id="adjustChangeQty" placeholder="Nhập số lượng (Ví dụ: 10)" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="adjustNote" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="adjustNote" rows="2" placeholder="Lý do nhập/xuất..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="confirmAdjustBtn">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addToInventoryModal" tabindex="-1" aria-labelledby="addToInventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addToInventoryModalLabel">Thêm variant chưa có kho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kho hàng</label>
                            <select class="form-select" id="addWarehouseSelect">
                                <option value="1">Kho chính (mặc định)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số lượng</label>
                            <input type="number" class="form-control" id="addBulkInitialQty" min="0" value="0">
                            <small class="text-muted">Số lượng chung cho tất cả variant được chọn.</small>
                        </div>
                    </div>

                    <div class="table-responsive mt-3" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th><input type="checkbox" id="selectAllMissing"> Chọn tất cả</th>
                                    <th>Sản phẩm</th>
                                    <th>Màu sắc</th>
                                    <th>Kích thước</th>
                                </tr>
                            </thead>
                            <tbody id="missingVariantsTableBody"></tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3">
                        <strong>Lưu ý:</strong> Chỉ các sản phẩm cùng với màu sắc, kích thước chưa có trong kho mới hiển thị ở đây.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" id="confirmAddBulkBtn">Thêm các variant đã chọn vào kho</button>
                </div>
            </div>
        </div>
    </div>

    <div id="globalToast" class="global-toast">
        <i id="toastIcon" class='bx'></i>
        <span id="toastMessage">Thông báo</span>
    </div>
    <div id="confirmModal" class="confirm-modal">
        <div class="confirm-box">
            <i class='bx bxs-trash' style="font-size: 3rem; color: #dc3545;"></i>
            <h4>Xóa sản phẩm?</h4>
            <p>Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?</p>
            <div class="d-flex gap-3 mt-4">
                <button id="cancelBtn" class="btn btn-secondary flex-fill">Hủy</button>
                <button id="confirmBtn" class="btn btn-danger flex-fill">Xóa</button>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/admin-products.js"></script>
</body>

</html>