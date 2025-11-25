<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tag Đánh Giá</title>
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/css/admin-review-tags.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <h2 class="mb-4">Quản lý Tag Đánh Giá</h2>
        <button class="btn btn-primary mb-4" id="btnAdd">Thêm Tag mới</button>
        <div id="tagList" class="row g-4"></div>
    </div>

    <div class="modal fade" id="tagModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm / Sửa Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="tagForm">
                    <input type="hidden" id="review_tag_id" name="review_tag_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nội dung Tag <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="content" required maxlength="100" placeholder="VD: Sản phẩm đẹp">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Hiển thị tag</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">Lưu Tag</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/admin-review-tags.js"></script>
</body>
</html>