<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Giới thiệu</title>
    <link rel="stylesheet" href="../../assets/css/admin-about.css">
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/css/admin-banner.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <h2 class="mb-4">Về chúng tôi</h2>
        <button class="btn btn-primary mb-3" id="btnAdd">Thêm nội dung mới</button>
        <div id="aboutList" class="row"></div>
    </div>

    <!-- Modal Thêm/Sửa -->
    <div class="modal fade" id="aboutModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm / Sửa nội dung</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="aboutForm" enctype="multipart/form-data">
                    <input type="hidden" id="about_id" name="about_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label>Tiêu đề <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label>Nội dung</label>
                                    <textarea class="form-control" name="content" rows="8"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label>Vị trí hiển thị <span class="text-danger">*</span></label>
                                    <select class="form-select" name="section_type" id="sectionTypeSelect">
                                        <option value="banner">Banner - Ảnh đội ngũ (trên cùng)</option>
                                        <option value="header">Header - Câu chuyện thương hiệu</option>
                                        <option value="grid_item">Grid Item - Card nhỏ 3 cột</option>
                                        <option value="footer">Footer - Cam kết cuối trang</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label>Ảnh (nếu có)</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <img id="previewImage" src="" class="img-thumbnail mt-2" style="max-height:200px; display:none;">
                                </div>
                            </div>
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
    <script src="../../js/admin-about.js"></script>
</body>

</html>