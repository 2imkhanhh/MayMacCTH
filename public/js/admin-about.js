const BASE_URL = '/MayMacCTH';

function decodeHtml(text) {
    const div = document.createElement('div');
    div.innerHTML = text || '';
    return div.textContent || div.innerText || '';
}

let existingSections = { header: false, banner: false, footer: false };
const sectionTypeSelect = document.getElementById('sectionTypeSelect');

document.addEventListener('DOMContentLoaded', () => {
    loadAbout();

    document.getElementById('btnAdd').addEventListener('click', openModal);

    // Preview ảnh khi chọn file
    document.querySelector('#aboutForm input[name="image"]')?.addEventListener('change', e => {
        const file = e.target.files[0];
        const preview = document.getElementById('previewImage');
        if (file) {
            preview.src = URL.createObjectURL(file); 
            preview.style.display = 'block';
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    });
});

async function loadAbout() {
    try {
        const res = await fetch(`${BASE_URL}/api/about/get_about.php`);
        if (!res.ok) throw new Error('Không kết nối được server');

        const result = await res.json();
        const container = document.getElementById('aboutList');
        container.innerHTML = '';

        existingSections = { header: false, banner: false, footer: false };

        if (result.success && result.data?.length > 0) {
            result.data.forEach(item => {
                if (['header', 'banner', 'footer'].includes(item.section_type)) {
                    existingSections[item.section_type] = true;
                }

                const typeText = {
                    banner: 'Banner (ảnh đội ngũ)',
                    header: 'Câu chuyện thương hiệu',
                    grid_item: 'Card nhỏ',
                    footer: 'Cam kết cuối trang'
                }[item.section_type] || item.section_type;

                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4 mb-4';

                col.innerHTML = `
                    <div class="card h-100 shadow-sm border-0">
                        ${item.image ? `<img src="${item.image}" class="card-img-top" style="height:180px; object-fit:cover;" alt="Ảnh">` : ''}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2">${decodeHtml(item.title)}</h5>
                            <p class="text-muted small mb-3"><strong>${typeText}</strong></p>
                            <div class="banner-actions">
                                <button class="btn btn-action btn-edit edit-banner-btn" onclick="editItem(${item.about_id})">
                                    <i class="bi bi-pencil-square"></i> Sửa
                                </button>
                                <button class="btn btn-action btn-delete delete-banner-btn" onclick="deleteItem(${item.about_id})">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });
        } else {
            container.innerHTML = '<div class="col-12 text-center py-5 text-muted fs-5">Chưa có nội dung nào.</div>';
        }

        updateAddButtonState();

    } catch (err) {
        console.error('Lỗi tải dữ liệu About Us:', err);
        alert('Không thể tải dữ liệu. Vui lòng kiểm tra kết nối!');
    }
}

function updateAddButtonState() {
    const btn = document.getElementById('btnAdd');
    const allFilled = existingSections.header && existingSections.banner && existingSections.footer;

    btn.disabled = allFilled;
    btn.textContent = allFilled ? 'Đã đủ các phần chính' : 'Thêm nội dung mới';
    btn.classList.toggle('btn-primary', !allFilled);
    btn.classList.toggle('btn-secondary', allFilled);
}

function openModal() {
    document.getElementById('aboutForm').reset();
    document.getElementById('about_id').value = '';
    document.getElementById('previewImage').style.display = 'none';

    const available = Object.keys(existingSections).find(key => !existingSections[key]);
    sectionTypeSelect.value = available || 'grid_item';

    new bootstrap.Modal(document.getElementById('aboutModal')).show();
}

async function editItem(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/about/get_about.php`);
        const result = await res.json();
        const item = result.data.find(x => x.about_id == id);

        if (!item) return alert('Không tìm thấy nội dung!');

        document.getElementById('about_id').value = item.about_id;
        document.querySelector('#aboutForm [name="title').value = decodeHtml(item.title);
        document.querySelector('#aboutForm [name="content"]').value = decodeHtml(item.content || '');
        sectionTypeSelect.value = item.section_type;

        const preview = document.getElementById('previewImage');
        if (item.image) {
            preview.src = item.image;
            preview.style.display = 'block';
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('aboutModal')).show();
    } catch (err) {
        alert('Lỗi khi mở form sửa!');
    }
}

document.getElementById('aboutForm').addEventListener('submit', async e => {
    e.preventDefault();

    const type = sectionTypeSelect.value;
    const id = document.getElementById('about_id').value;

    if (!id && existingSections[type]) {
        alert(`Đã tồn tại phần "${type}" rồi! Chỉ được tạo 1 lần.`);
        return;
    }

    const formData = new FormData(e.target);
    const url = id
        ? `${BASE_URL}/api/about/update_about.php?id=${id}`
        : `${BASE_URL}/api/about/create_about.php`;

    try {
        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        alert(data.message);

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('aboutModal')).hide();
            loadAbout(); 
        }
    } catch (err) {
        console.error(err);
        alert('Lỗi kết nối server!');
    }
});

async function deleteItem(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa nội dung này?\nHành động này không thể hoàn tác!')) return;

    try {
        const res = await fetch(`${BASE_URL}/api/about/delete_about.php?id=${id}`, {
            method: 'DELETE'
        });
        const data = await res.json();

        alert(data.message);
        if (data.success) loadAbout();
    } catch (err) {
        alert('Lỗi khi xóa!');
    }
}