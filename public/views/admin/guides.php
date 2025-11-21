<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Hướng dẫn</title>
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/css/admin-category.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Hướng dẫn</h2>
            <button class="btn btn-primary" id="btnAdd">Thêm hướng dẫn mới</button>
        </div>

        <div id="guideList" class="row"></div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="guideModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm hướng dẫn mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="guideForm" enctype="multipart/form-data">
                    <input type="hidden" id="guide_id" name="guide_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Loại hướng dẫn <span class="text-danger">*</span></label>
                                <select class="form-select" name="catalog" required>
                                    <option value="huong-dan-mua-hang">Hướng dẫn mua hàng</option>
                                    <option value="huong-dan-chon-size">Hướng dẫn chọn size</option>
                                    <option value="chinh-sach-doi-tra">Chính sách đổi trả</option>
                                    <option value="chinh-sach-giao-hang">Chính sách giao hàng</option>
                                    <option value="faq">FAQ (Câu hỏi thường gặp)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung</label>
                            <textarea class="form-control" name="content" rows="8"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hình ảnh hiện tại</label><br>
                            <img id="currentImage" src="" style="max-height: 200px; display: none;" class="mb-2 rounded">
                            <input type="hidden" name="image" id="imagePath">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thay ảnh mới</label>
                            <input type="file" class="form-control" accept="image/*" id="imageUpload">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/admin-guides.js"></script>
</body>
</html>