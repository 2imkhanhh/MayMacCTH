const BASE_URL = '/MayMacCTH';
let modalInstance = null;

document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('guideModal');
    if (!modalElement) {
        console.error('Không tìm thấy #guideModal');
        return;
    }
    modalInstance = new bootstrap.Modal(modalElement);

    loadGuides();

    document.querySelectorAll('.add-guide-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const catalog = this.dataset.catalog;
            const container = document.querySelector(`.guide-list[data-catalog="${catalog}"]`);
            const currentCount = container.children.length;

            if (currentCount >= 2) {
                alert(`Danh mục đã đủ 2 hướng dẫn!`);
                return;
            }

            const form = document.getElementById('guideForm');
            const catalogSelect = document.getElementById('modalCatalog');
            const guideIdInput = document.getElementById('guide_id');

            if (!form || !catalogSelect || !guideIdInput) {
                alert('Lỗi: Không tìm thấy form!');
                return;
            }

            form.reset();
            guideIdInput.value = '';
            document.querySelector('#guideModal .modal-title').textContent = 'Thêm hướng dẫn mới';
            catalogSelect.value = catalog;
            modalInstance.show();
        });
    });
    const guideForm = document.getElementById('guideForm');
    if (guideForm) {
        guideForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = document.getElementById('guide_id')?.value || '';

            const url = id
                ? `${BASE_URL}/api/guide/update_guide.php?id=${id}`
                : `${BASE_URL}/api/guide/create_guide.php`;

            try {
                const res = await fetch(url, { method: 'POST', body: formData });
                const data = await res.json();

                alert(data.message || (data.success ? 'Thành công!' : 'Có lỗi xảy ra!'));

                if (data.success) {
                    modalInstance.hide();
                    loadGuides();
                }
            } catch (err) {
                console.error(err);
                alert('Lỗi kết nối server');
            }
        });
    }
    document.querySelectorAll('#guideTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', loadGuides);
    });
});

async function loadGuides() {
    try {
        const res = await fetch(`${BASE_URL}/api/guide/get_guide.php`);
        const result = await res.json();

        document.querySelectorAll('.guide-list').forEach(list => list.innerHTML = '');

        if (!result.success || !result.data || result.data.length === 0) {
            document.querySelectorAll('.guide-list').forEach(list => {
                list.innerHTML = '<div class="text-center text-muted py-5">Chưa có hướng dẫn nào</div>';
            });
            return;
        }

        result.data.forEach(g => {
            const container = document.querySelector(`.guide-list[data-catalog="${g.catalog}"]`);
            if (!container) return;

            const item = document.createElement('div');
            item.className = 'col-12 guide-item p-4 mb-3 border rounded bg-white shadow-sm';
            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 pe-3">
                        <h6 class="fw-bold text-dark mb-2">
                            ${g.title ? g.title : '<em class="text-muted">Không có tiêu đề</em>'}
                        </h6>
                        <div class="text-muted small">
                            ${g.content 
                                ? g.content.replace(/\n/g, '<br>').substring(0, 250) + (g.content.length > 250 ? '...' : '')
                                : '<em>Không có nội dung</em>'
                            }
                        </div>
                    </div>
                    <div class="text-nowrap">
                        <button class="btn btn-warning btn-sm me-2" onclick="editGuide(${g.guide_id})">
                            Sửa
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteGuide(${g.guide_id})">
                            Xóa
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(item);
        });
    } catch (err) {
        console.error('Lỗi load hướng dẫn:', err);
    }
}

async function editGuide(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/guide/get_guide.php`);
        const result = await res.json();
        const guide = result.data.find(g => g.guide_id == id);
        if (!guide) return alert('Không tìm thấy hướng dẫn!');

        const titleInput = document.querySelector('[name="title"]');
        const catalogSelect = document.getElementById('modalCatalog');
        const contentTextarea = document.querySelector('[name="content"]');
        const guideIdInput = document.getElementById('guide_id');

        if (!titleInput || !catalogSelect || !contentTextarea || !guideIdInput) {
            alert('Lỗi: Không tìm thấy các trường trong form!');
            return;
        }

        titleInput.value = guide.title || '';
        catalogSelect.value = guide.catalog;
        contentTextarea.value = guide.content || '';
        guideIdInput.value = guide.guide_id;

        document.querySelector('#guideModal .modal-title').textContent = 'Sửa hướng dẫn';
        modalInstance.show();
    } catch (err) {
        console.error(err);
        alert('Lỗi tải dữ liệu để sửa');
    }
}

async function deleteGuide(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa hướng dẫn này không?')) return;

    try {
        const res = await fetch(`${BASE_URL}/api/guide/delete_guide.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        alert(data.message || 'Đã xóa thành công!');
        if (data.success) loadGuides();
    } catch (err) {
        alert('Lỗi khi xóa');
    }
}