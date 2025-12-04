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
    <style>
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
    </style>
</head>

<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-end mb-4 product-header">
            <div>
                <h2>Quản lý Sản phẩm</h2>
                <button class="btn btn-primary mt-2" id="btnAdd">Thêm sản phẩm mới</button>
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
                        <h6>Biến thể màu sắc & kích thước</h6>
                        <div id="colorContainer">
                            <div class="color-item">
                                <button type="button" class="btn btn-sm btn-danger remove-color" style="display:none;">×</button>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label>Tên màu</label>
                                        <input type="text" class="form-control" name="colors[0][name]" placeholder="VD: Đen">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Mã màu</label>
                                        <input type="color" class="form-control form-control-color" name="colors[0][code]" value="#000000">
                                    </div>
                                    <div class="col-md-5">
                                        <label>Giá (VNĐ)</label>
                                        <input type="number" class="form-control" name="colors[0][price]" value="0">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label>Kích thước (nhập cách nhau bởi dấu phẩy)</label>
                                    <input type="text" class="form-control" name="colors[0][sizes]" placeholder="S, M, L, XL">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addColor">+ Thêm màu</button>

                        <hr>
                        <h6>Ảnh sản phẩm</h6>
                        <div id="imageContainer">
                            <div class="image-item">
                                <button type="button" class="btn btn-sm btn-danger remove-image" style="display:none;">×</button>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="primary_image" value="0" checked>
                                    <label class="form-check-label">Ảnh chính</label>
                                </div>
                                <input type="file" class="form-control mt-2" name="images[]" accept="image/*">
                                <img class="img-thumbnail mt-2" style="max-height:150px; display:none;">
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addImage">+ Thêm ảnh</button>

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