<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Banner</title>
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex">
    <?php include 'partials/sidebar.html'; ?>

    <div class="main-content flex-grow-1 p-4">
        <h2 class="mb-4">Quản lý Banner</h2>
        <button class="btn btn-primary mb-3" id="btnAdd">Thêm Banner mới</button>

        <!-- Grid hiển thị banner -->
        <div id="bannerList" class="row"></div>
    </div>

    <!-- Modal Thêm Banner -->
    <div class="modal fade" id="addBannerModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addBannerForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ảnh Banner</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="is_active" checked>
                            <label class="form-check-label">Hiển thị</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success" id="saveBannerBtn">Lưu Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa Banner -->
    <div class="modal fade" id="editBannerModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editBannerForm" enctype="multipart/form-data">
                    <input type="hidden" id="editBannerId" name="banner_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" id="editTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ảnh Banner</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <img id="currentImage" src="" class="img-thumbnail mt-2" style="max-height:200px;">
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="is_active" id="editIsActive">
                            <label class="form-check-label">Hiển thị</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success" id="saveEditBannerBtn">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/admin-banners.js"></script>
</body>
</html>
