<?php
require_once 'auth_middleware.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Kho hàng</title>
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/css/admin-product.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 product-header">
            <h2>Quản lý Kho hàng</h2>
        </div>

        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                    <i class="bi bi-box-seam"></i> Quản lý tồn kho
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="warehouses-tab" data-bs-toggle="tab" data-bs-target="#warehouses" type="button" role="tab">
                    <i class="bi bi-building"></i> Danh sách Nhà kho
                </button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">
            <div class="tab-pane fade show active" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
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
                                <th class="text-center">Lịch sử</th>
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
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kho hàng</label>
                            <select class="form-select" id="addWarehouseSelect">
                                <option value="1">Kho chính (mặc định)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Số lượng ban đầu</label>
                            <input type="number" class="form-control" id="addBulkInitialQty" min="0" value="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ngưỡng cảnh báo</label>
                            <input type="number" class="form-control" id="addBulkLowStock" min="0" value="10">
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
                        <strong>Lưu ý:</strong> Chỉ các sản phẩm chưa có trong kho mới hiển thị ở đây.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" id="confirmAddBulkBtn">Thêm vào kho</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lịch sử nhập/xuất kho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="historyTitle" class="fw-bold mb-3"></h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Loại</th>
                                    <th>Thay đổi</th>
                                    <th>Tồn cũ</th>
                                    <th>Tồn mới</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <div id="globalToast" class="global-toast">
        <i id="toastIcon" class='bx'></i>
        <span id="toastMessage">Thông báo</span>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/admin-inventory.js"></script>
</body>
</html>