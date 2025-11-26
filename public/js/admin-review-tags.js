const BASE_URL = '/MayMacCTH';
let tagModalInstance = null;
let allReviews = []; // Lưu toàn bộ đánh giá

document.addEventListener('DOMContentLoaded', () => {
    tagModalInstance = new bootstrap.Modal(document.getElementById('tagModal'));

    loadTags();
    loadAllReviews();

    // --- Thêm Tag ---
    document.getElementById('btnAdd')?.addEventListener('click', () => {
        document.getElementById('tagForm').reset();
        document.getElementById('review_tag_id').value = '';
        document.querySelector('[name="is_active"]').checked = true;
        document.querySelector('#tagModal .modal-title').textContent = 'Thêm Tag mới';
        tagModalInstance.show();
    });

    // --- Submit Tag ---
    document.getElementById('tagForm')?.addEventListener('submit', async e => {
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
            tagModalInstance.hide();
            loadTags();
        }
    });

    // --- Filter Events ---
    document.getElementById('btnFilterReviews')?.addEventListener('click', applyReviewFilter);
    document.getElementById('btnResetFilter')?.addEventListener('click', () => {
        document.getElementById('filterRating').value = '';
        document.getElementById('filterFromDate').value = '';
        document.getElementById('filterToDate').value = '';
        renderReviewTable(allReviews);
    });
});

// ---------------- TAG GRID ----------------
async function loadTags() {
    try {
        const res = await fetch(`${BASE_URL}/api/review_tags/get_review_tags.php`);
        const result = await res.json();
        const container = document.getElementById('tagList');
        container.innerHTML = '';

        if(result.success && result.data.length > 0){
            result.data.forEach(tag => {
                const statusClass = tag.is_active == 1 ? 'active' : 'inactive';
                const statusText = tag.is_active == 1 ? 'Hiển thị' : 'Ẩn';

                const col = document.createElement('div');
                col.className = 'col-6 col-md-3'; // 4 card / hàng

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
            container.innerHTML = '<div class="col-12 text-center py-4 text-muted">Chưa có tag nào</div>';
        }
    } catch(err){
        console.error(err);
        alert('Lỗi tải dữ liệu tag!');
    }
}

async function editTag(id) {
    const res = await fetch(`${BASE_URL}/api/review_tags/get_review_tags.php`);
    const result = await res.json();
    const tag = result.data.find(t => t.review_tag_id == id);
    if (!tag) return alert('Không tìm thấy tag!');

    document.getElementById('review_tag_id').value = tag.review_tag_id;
    document.querySelector('[name="content"]').value = tag.content;
    document.querySelector('[name="is_active"]').checked = tag.is_active == 1;

    document.querySelector('#tagModal .modal-title').textContent = 'Sửa Tag';
    tagModalInstance.show();
}

async function deleteTag(id) {
    if (!confirm('Xóa tag này?\nCác đánh giá cũ vẫn giữ tag, chỉ không cho khách chọn mới nữa.')) return;
    const res = await fetch(`${BASE_URL}/api/review_tags/delete_review_tag.php?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    alert(data.message);
    if (data.success) loadTags();
}

// ---------------- REVIEW TABLE ----------------
async function loadAllReviews() {
    try {
        const res = await fetch(`${BASE_URL}/api/review_products/get_all_reviews.php`);
        const result = await res.json();
        allReviews = result.data || [];
        renderReviewTable(allReviews);
    } catch (err) { console.error(err); alert('Lỗi tải đánh giá!'); }
}

function renderReviewTable(data) {
    const tbody = document.querySelector('#reviewTable tbody');
    tbody.innerHTML = '';

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Chưa có đánh giá nào</td></tr>';
        return;
    }

    data.forEach((r, idx) => {
        const tagsHtml = r.tags?.length
            ? r.tags.map(t => `<span class="badge" style="background:#174DAF;color:white;margin-right:3px">${t}</span>`).join('')
            : '';

        const starsHtml = Array.from({length: 5}, (_, i) =>
            i < r.rating
                ? '<span class="star" style="color:gold">★</span>'
                : '<span class="star" style="color:#ccc">☆</span>'
        ).join('');

        tbody.innerHTML += `
            <tr>
                <td>${idx+1}</td>
                <td>${r.customer_name || 'Ẩn danh'}</td>
                <td>${r.product_name}</td>
                <td>${starsHtml}</td>
                <td>${r.content || '(Không có nội dung)'}</td>
                <td>${tagsHtml}</td>
                <td>${r.created_at}</td>
            </tr>
        `;
    });
}

// ---------------- REVIEW FILTER ----------------
function applyReviewFilter() {
    const rating = document.getElementById('filterRating').value;
    const date = document.getElementById('filterDate').value; // Chỉ 1 ngày

    let filtered = allReviews;

    if (rating) {
        filtered = filtered.filter(r => r.rating == parseInt(rating));
    }

    if (date) {
        filtered = filtered.filter(r => {
            const reviewDate = new Date(r.created_at).toISOString().slice(0, 10);
            return reviewDate === date;
        });
    }

    renderReviewTable(filtered);
}

// Reset filter
document.getElementById('btnResetFilter')?.addEventListener('click', () => {
    document.getElementById('filterRating').value = '';
    document.getElementById('filterDate').value = '';
    renderReviewTable(allReviews);
});
