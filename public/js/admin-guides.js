const BASE_URL = '/MayMacCTH';

document.addEventListener('DOMContentLoaded', () => {
    loadGuides();
    document.getElementById('btnAdd').addEventListener('click', () => openModal());

    // Upload ảnh
    document.getElementById('imageUpload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('image', file);

            fetch(`${BASE_URL}/api/about/upload_image.php`, { 
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('imagePath').value = data.path;
                    alert('Upload ảnh thành công!');
                }
            });
        }
    });

    document.getElementById('guideForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('guide_id').value;

        const url = id 
            ? `${BASE_URL}/api/guide/update_guide.php?id=${id}`
            : `${BASE_URL}/api/guide/create_guide.php`;

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            alert(data.message || (data.success ? 'Thành công!' : 'Thất bại'));
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('guideModal')).hide();
                loadGuides();
            }
        } catch (err) {
            alert('Lỗi kết nối');
        }
    });
});

async function loadGuides() {
    const res = await fetch(`${BASE_URL}/api/guide/get_guide.php`);
    const result = await res.json();
    const container = document.getElementById('guideList');
    container.innerHTML = '';

    if (result.success && result.data.length > 0) {
        result.data.forEach(g => {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 mb-4';
            col.innerHTML = `
                <div class="category-card">
                    ${g.image ? `<img src="../../assets/images/upload/${g.image.split('/').pop()}" class="img-fluid rounded mb-3" style="height:150px; object-fit:cover;">` : ''}
                    <h5>${g.title}</h5>
                    <p class="text-muted small">${g.content.substring(0, 100)}...</p>
                    <div class="category-actions">
                        <button class="btn btn-category btn-edit" onclick="editGuide(${g.guide_id})">
                            <i class="bi bi-pencil"></i> Sửa
                        </button>
                        <button class="btn btn-category btn-delete" onclick="deleteGuide(${g.guide_id})">
                            <i class="bi bi-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(col);
        });
    } else {
        container.innerHTML = '<div class="col-12 text-center py-5"><h5 class="text-muted">Chưa có hướng dẫn nào.</h5></div>';
    }
}

function openModal(id = null) {
    const modal = new bootstrap.Modal(document.getElementById('guideModal'));
    const title = document.querySelector('#guideModal .modal-title');
    document.getElementById('guideForm').reset();
    document.getElementById('guide_id').value = '';
    document.getElementById('currentImage').style.display = 'none';
    document.getElementById('imagePath').value = '';

    if (id) {
        fetch(`${BASE_URL}/api/guide/get_guide.php`)
            .then(res => res.json())
            .then(result => {
                const guide = result.data.find(g => g.guide_id == id);
                if (guide) {
                    document.querySelector('[name="title"]').value = guide.title;
                    document.querySelector('[name="catalog"]').value = guide.catalog;
                    document.querySelector('[name="content"]').value = guide.content;
                    document.getElementById('guide_id').value = guide.guide_id;
                    document.getElementById('imagePath').value = guide.image || '';
                    if (guide.image) {
                        const imgName = guide.image.split('/').pop();
                        document.getElementById('currentImage').src = `../../assets/images/upload/${imgName}`;
                        document.getElementById('currentImage').style.display = 'block';
                    }
                    title.textContent = 'Sửa hướng dẫn';
                }
            });
    } else {
        title.textContent = 'Thêm hướng dẫn mới';
    }
    modal.show();
}

function editGuide(id) {
    openModal(id);
}

async function deleteGuide(id) {
    if (!confirm('Xóa hướng dẫn này?')) return;
    const res = await fetch(`${BASE_URL}/api/guide/delete_guide.php?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    alert(data.message);
    if (data.success) loadGuides();
}