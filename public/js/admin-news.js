const PUBLIC_URL = '/MayMacCTH/';          
const API_BASE   = '/MayMacCTH/api/news';        

let editor = null;

async function initEditor(data = { blocks: [] }) {
    if (editor && typeof editor.destroy === 'function') {
        await editor.destroy();
        editor = null;
    }

    const holder = document.getElementById('editorjs');
    if (holder) holder.innerHTML = '';

    editor = await new EditorJS({
        holder: 'editorjs',
        tools: {
            header: window.Header,
            list: window.List,
            quote: window.Quote,
            embed: window.Embed,
            image: {
                class: window.ImageTool,
                config: {
                    uploader: {
                        async uploadByFile(file) {
                            const res = await uploadImage(file);
                            if (!res.success || !res.path) {
                                throw new Error(res.message || 'Upload ảnh thất bại');
                            }
                            return {
                                success: 1,
                                file: { url: PUBLIC_URL + res.path }  
                            };
                        },
                        uploadByUrl(url) {
                            return Promise.resolve({ success: 1, file: { url } });
                        }
                    }
                }
            }
        },
        data: data,
        placeholder: 'Bắt đầu viết bài tại đây...',
        onReady: () => console.log('Editor.js đã sẵn sàng!')
    });
}

async function loadNews() {
    try {
        const res = await fetch(`${API_BASE}/get_news.php`);
        const data = await res.json();
        const container = document.getElementById('newsList');
        container.innerHTML = '';

        if (data.success && data.data?.length > 0) {
            data.data.forEach(article => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4 mb-4';
                col.innerHTML = `
                    <div class="card h-100 shadow-sm hover-shadow">
                        ${article.thumbnail
                        ? `<img src="${PUBLIC_URL}${article.thumbnail}" class="card-img-top" style="height:180px; object-fit:cover;" alt="Thumbnail">`
                        : '<div class="bg-light d-flex align-items-center justify-content-center" style="height:180px;"><i class="bi bi-image fs-1 text-muted"></i></div>'
                    }
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title text-truncate-2">${article.title}</h6>
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> ${new Date(article.created_at).toLocaleDateString('vi-VN')}
                                | ${article.author || 'Admin CTH'}
                            </small>
                            <div class="mt-auto pt-3 text-end">
                                <button class="btn btn-sm btn-outline-primary me-2" onclick="editNews(${article.id})">Sửa</button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNews(${article.id})">Xóa</button>
                            </div>
                        </div>
                    </div>`;
                container.appendChild(col);
            });
        } else {
            container.innerHTML = `<div class="col-12 text-center py-5"><h5 class="text-muted">Chưa có bài viết nào.</h5></div>`;
        }
    } catch (err) {
        console.error('Lỗi load danh sách:', err);
    }
}

async function openModal(id = null) {
    const modal = new bootstrap.Modal(document.getElementById('newsModal'));
    document.getElementById('newsForm').reset();
    document.getElementById('article_id').value = '';
    document.getElementById('thumbnailPreview').innerHTML = '';

    await initEditor();
    await loadCategories();

    document.getElementById('modalTitle').textContent = id ? 'Sửa bài viết' : 'Viết bài mới';
    if (id) await loadArticleForEdit(id);

    const thumbnailInput = document.querySelector('[name="thumbnail"]');
    thumbnailInput.replaceWith(thumbnailInput.cloneNode(true));
    document.querySelector('[name="thumbnail"]').addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => {
                document.getElementById('thumbnailPreview').innerHTML = 
                    `<img src="${ev.target.result}" class="img-thumbnail" style="max-height:150px;">`;
            };
            reader.readAsDataURL(file);
        }
    });

    modal.show();
}

async function loadArticleForEdit(id) {
    try {
        const res = await fetch(`${API_BASE}/get_news.php?id=${id}`);
        const response = await res.json();
        if (!response.success || !response.data) return alert('Không tìm thấy bài viết!');

        const a = response.data;

        document.querySelector('[name="title"]').value = a.title || '';
        document.querySelector('[name="slug"]').value = a.slug || '';
        document.querySelector('[name="author"]').value = a.author || 'Admin CTH';
        document.querySelector('[name="is_published"]').checked = a.is_published == 1;
        document.getElementById('article_id').value = a.id;
        document.querySelector('[name="new_category_id"]').value = a.new_category_id || '';
        document.querySelector('[name="is_featured"]').checked = a.is_featured == 1;

        if (a.thumbnail) {
            document.getElementById('thumbnailPreview').innerHTML = 
                `<img src="${PUBLIC_URL}${a.thumbnail}" class="img-thumbnail" style="max-height:150px;">`;
        }

        let contentObj = { blocks: [] };
        if (a.content) {
            try { contentObj = JSON.parse(a.content); }
            catch (e) { console.warn('Lỗi parse content:', e); }
        }
        await initEditor(contentObj);
    } catch (err) {
        console.error('Lỗi load bài viết:', err);
    }
}

async function uploadImage(file) {
    const formData = new FormData();
    formData.append('image', file);
    const res = await fetch(`${API_BASE}/upload_image.php`, {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

document.getElementById('newsForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!editor) return alert('Editor chưa sẵn sàng!');

    try {
        const savedData = await editor.save();
        const formData = new FormData(this);
        formData.append('content', JSON.stringify(savedData));

        const id = document.getElementById('article_id').value;
        const url = id
            ? `${API_BASE}/update_news.php?id=${id}`
            : `${API_BASE}/create_news.php`;

        const res = await fetch(url, { method: 'POST', body: formData });
        const data = await res.json();

        alert(data.message || (data.success ? 'Lưu thành công!' : 'Có lỗi xảy ra!'));

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('newsModal')).hide();
            loadNews();
        }
    } catch (err) {
        console.error(err);
        alert('Lỗi lưu bài viết!');
    }
});

function editNews(id) { openModal(id); }

async function deleteNews(id) {
    if (confirm('Xóa bài viết này thật chứ?')) {
        try {
            await fetch(`${API_BASE}/delete_news.php?id=${id}`, { method: 'DELETE' });
            loadNews();
        } catch (err) {
            alert('Lỗi xóa!');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btnAdd').addEventListener('click', () => openModal());
    loadNews();
});

async function loadCategories() {
    try {
        const res = await fetch('/MayMacCTH/api/news/get_news_categories.php');
        const data = await res.json();
        const select = document.querySelector('[name="new_category_id"]');
        select.innerHTML = '<option value="">-- Chọn danh mục --</option>';
        if (data.success && data.data) {
            data.data.forEach(cat => {
                select.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
            });
        }
    } catch (err) {
        console.error('Lỗi load danh mục:', err);
    }
}