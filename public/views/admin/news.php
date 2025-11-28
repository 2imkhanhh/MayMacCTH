<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tin tức</title>
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="d-flex">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý Tin tức</h2>
            <button class="btn btn-primary" id="btnAdd">
                <i class="bi bi-plus-lg"></i> Viết bài mới
            </button>
        </div>

        <div id="newsList" class="row"></div>
    </div>

    <!-- Modal Viết / Sửa bài -->
    <div class="modal fade" id="newsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Viết bài mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newsForm" enctype="multipart/form-data">
                    <input type="hidden" id="article_id" name="article_id">
                    <div class="modal-body">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" name="title" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Slug (URL) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">/tin-tuc/</span>
                                        <input type="text" class="form-control" name="slug" required placeholder="top-10-mau-ao-dep-2025">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nội dung bài viết</label>
                                    <div id="editorjs" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; min-height: 400px;"></div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">Cài đặt bài viết</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Ảnh thumbnail</label>
                                            <input type="file" class="form-control" name="thumbnail" accept="image/*">
                                            <div class="mt-2" id="thumbnailPreview"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tác giả</label>
                                            <input type="text" class="form-control" name="author" value="Admin CTH">
                                        </div>

                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" checked>
                                            <label class="form-check-label" for="is_published">Xuất bản ngay</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Lưu bài viết
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Editor.js -->
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest/dist/editorjs.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest/dist/header.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest/dist/list.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest/dist/quote.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@latest/dist/embed.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest/dist/image.umd.js"></script>

    <script>
        window.Header = Header;
        window.List = List;
        window.Quote = Quote;
        window.ImageTool = ImageTool;
        window.Embed = Embed;
    </script>

    <script src="../../js/admin-news.js" defer></script>
</body>

</html>