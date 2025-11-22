<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Thông tin Liên hệ</title>
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/css/admin-category.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <h2 class="mb-4">Quản lý Thông tin Liên hệ</h2>

        <!-- Nút duy nhất – tự đổi thành Cập nhật khi có dữ liệu -->
        <button class="btn btn-primary mb-4" id="btnAdd">
            <i class="bi bi-plus-circle"></i> Thêm thông tin liên hệ
        </button>

        <!-- Hiển thị thông tin liên hệ -->
        <div id="contactList" class="row"></div>
    </div>

    <!-- Modal Thêm / Sửa (chỉ 1 cái) -->
    <div class="modal fade" id="contactModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm thông tin liên hệ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="contactForm">
                    <input type="hidden" id="contact_id" name="contact_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="address" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" placeholder="https://example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone_number" required maxlength="50">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check2"></i> <span id="saveBtnText">Lưu thông tin</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/admin-contacts.js"></script>
</body>
</html>