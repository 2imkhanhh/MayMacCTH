const BASE_URL = '/MayMacCTH';

document.addEventListener('DOMContentLoaded', () => {
    loadTags();
    document.getElementById('btnAdd').addEventListener('click', openModal);
});

async function loadTags() {
    try {
        const res = await fetch(`${BASE_URL}/api/review_tags/get_review_tags.php`);
        const result = await res.json();
        const container = document.getElementById('tagList');
        container.innerHTML = '';

        if (result.success && result.data.length > 0) {
            result.data.forEach(tag => {
                const statusClass = tag.is_active == 1 ? 'active' : 'inactive';
                const statusText = tag.is_active == 1 ? 'Hiển thị' : 'Ẩn';

                const col = document.createElement('div');
                col.className = 'col-6 col-sm-4 col-lg-3'; // 4 card mỗi hàng

                col.innerHTML = `
                    <div class="tag-card">
                        <span class="tag-status ${statusClass}">${statusText}</span>
                        <div class="tag-content">
                            <h5>${tag.content}</h5>
                        </div>
                        <div class="tag-actions">
                            <button class="btn-tag-edit" onclick="editTag(${tag.review_tag_id})">
                                <i class="bi bi-pencil-square"></i> Sửa
                            </button>
                            <button class="btn-tag-delete" onclick="deleteTag(${tag.review_tag_id})">
                                <i class="bi bi-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });
        } else {
            container.innerHTML = '<div class="col-12 text-center py-5 text-muted fs-4">Chưa có tag nào.</div>';
        }
    } catch (err) {
        console.error(err);
        alert('Lỗi tải dữ liệu tag!');
    }
}

// Các hàm openModal, editTag, submit, deleteTag giữ nguyên như trước (chỉ đổi tên class nếu cần)
function openModal() {
    document.getElementById('tagForm').reset();
    document.getElementById('review_tag_id').value = '';
    document.querySelector('[name="is_active"]').checked = true;
    new bootstrap.Modal(document.getElementById('tagModal')).show();
}

async function editTag(id) {
    const res = await fetch(`${BASE_URL}/api/review_tags/get_review_tags.php`);
    const result = await res.json();
    const tag = result.data.find(t => t.review_tag_id == id);

    document.getElementById('review_tag_id').value = tag.review_tag_id;
    document.querySelector('[name="content"]').value = tag.content;
    document.querySelector('[name="is_active"]').checked = tag.is_active == 1;

    new bootstrap.Modal(document.getElementById('tagModal')).show();
}

document.getElementById('tagForm').addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('review_tag_id').value;
    const formData = new FormData(e.target);

    const url = id
        ? `${BASE_URL}/api/review_tags/update_review_tag.php`
        : `${BASE_URL}/api/review_tags/create_review_tag.php`;

    const res = await fetch(url, { method: 'POST', body: formData });
    const data = await res.json();
    alert(data.message);

    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('tagModal')).hide();
        loadTags();
    }
});

async function deleteTag(id) {
    if (!confirm('Xóa tag này?\nCác đánh giá cũ vẫn giữ tag, chỉ không cho khách chọn mới nữa.')) return;
    const res = await fetch(`${BASE_URL}/api/review_tags/delete_review_tag.php?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    alert(data.message);
    if (data.success) loadTags();
}