<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm</title>
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/css/admin-product.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý Sản phẩm</h2>
            <button class="btn btn-primary" id="btnAdd">Thêm sản phẩm mới</button>
        </div>

        <!-- Bộ lọc nhanh -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" id="filterCategory">
                            <option value="">Tất cả danh mục</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchName" placeholder="Tìm theo tên sản phẩm...">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-outline-secondary" id="btnSearch">Tìm kiếm</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div id="productList" class="row"></div>
    </div>

    <!-- Modal Thêm/Sửa Sản phẩm -->
    <div class="modal fade" id="productModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="productForm" enctype="multipart/form-data">
                    <input type="hidden" id="product_id" name="product_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Chọn danh mục</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Màu sắc</label>
                                <input type="text" class="form-control" name="color" placeholder="VD: Đen, Trắng">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kích thước</label>
                                <input type="text" class="form-control" name="size" placeholder="VD: M, L, XL">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="price" min="0" step="1000" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ảnh sản phẩm <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <img id="currentImage" src="" class="img-thumbnail mt-2" style="max-height:200px; display:none;">
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

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/admin-products.js"></script>
</body>
</html>