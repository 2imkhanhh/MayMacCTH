<?php
require_once 'auth_middleware.php';  
?>

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
    <style>
        .catalog-tab { background:#f8f9fa; border:1px solid #dee2e6; border-radius:12px; padding:1.5rem; margin-bottom:2rem; }
        .catalog-title { font-size:1.4rem; font-weight:600; color:#2c3e50; margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between; }
        .guide-item { background:white; border:1px solid #e9ecef; border-radius:10px; padding:1rem; margin-bottom:1rem; transition:all .2s; }
        .guide-item:hover { border-color:#007bff; box-shadow:0 2px 8px rgba(0,123,255,.15); }
        .guide-actions .btn { font-size:0.9rem; padding:.35rem .8rem; }
    </style>
</head>
<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <h2 class="mb-4">Quản lý Hướng dẫn</h2>

        <!-- Nav tabs -->
        <ul class="nav nav-tabs mb-4" id="guideTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-mua-hang">Hướng dẫn mua hàng</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-chon-size">Hướng dẫn chọn size</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-doi-tra">Chính sách đổi trả</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-giao-hang">Chính sách giao hàng</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-faq">FAQ</button></li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-mua-hang">
                <div class="catalog-tab">
                    <div class="catalog-title">
                        <span>Hướng dẫn mua hàng</span>
                        <button class="btn btn-success btn-sm add-guide-btn" data-catalog="huong-dan-mua-hang">Thêm hướng dẫn</button>
                    </div>
                    <div class="guide-list row" data-catalog="huong-dan-mua-hang"></div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-chon-size">
                <div class="catalog-tab">
                    <div class="catalog-title">
                        <span>Hướng dẫn chọn size</span>
                        <button class="btn btn-success btn-sm add-guide-btn" data-catalog="huong-dan-chon-size">Thêm hướng dẫn</button>
                    </div>
                    <div class="guide-list row" data-catalog="huong-dan-chon-size"></div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-doi-tra">
                <div class="catalog-tab">
                    <div class="catalog-title">
                        <span>Chính sách đổi trả</span>
                        <button class="btn btn-success btn-sm add-guide-btn" data-catalog="chinh-sach-doi-tra">Thêm hướng dẫn</button>
                    </div>
                    <div class="guide-list row" data-catalog="chinh-sach-doi-tra"></div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-giao-hang">
                <div class="catalog-tab">
                    <div class="catalog-title">
                        <span>Chính sách giao hàng</span>
                        <button class="btn btn-success btn-sm add-guide-btn" data-catalog="chinh-sach-giao-hang">Thêm hướng dẫn</button>
                    </div>
                    <div class="guide-list row" data-catalog="chinh-sach-giao-hang"></div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-faq">
                <div class="catalog-tab">
                    <div class="catalog-title">
                        <span>FAQ (Câu hỏi thường gặp)</span>
                        <button class="btn btn-success btn-sm add-guide-btn" data-catalog="faq">Thêm hướng dẫn</button>
                    </div>
                    <div class="guide-list row" data-catalog="faq"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal giữ nguyên như cũ -->
    <div class="modal fade" id="guideModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm hướng dẫn mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="guideForm">
                    <input type="hidden" id="guide_id" name="guide_id">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" class="form-control" name="title">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Loại hướng dẫn</label>
                                <select class="form-select" name="catalog" id="modalCatalog" required>
                                    <option value="huong-dan-mua-hang">Hướng dẫn mua hàng</option>
                                    <option value="huong-dan-chon-size">Hướng dẫn chọn size</option>
                                    <option value="chinh-sach-doi-tra">Chính sách đổi trả</option>
                                    <option value="chinh-sach-giao-hang">Chính sách giao hàng</option>
                                    <option value="faq">FAQ</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="form-label">Nội dung</label>
                            <textarea class="form-control" name="content" rows="10"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">Lưu lại</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/admin-guides.js"></script> 
</body>
</html>