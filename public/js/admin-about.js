const BASE_URL = '/MayMacCTH';

const sectionTypeSelect = document.getElementById('sectionTypeSelect');
const orderWrapper      = document.getElementById('orderWrapper');
const displayOrderInput = orderWrapper.querySelector('input[name="display_order"]');
let existingSections = { header: false, banner: false, footer: false };

document.addEventListener('DOMContentLoaded', () => {
    loadAbout();

    document.getElementById('btnAdd').addEventListener('click', () => openModal());

    // Preview ảnh
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

    // Khi đổi loại → ẩn/hiện ô thứ tự
    sectionTypeSelect?.addEventListener('change', updateOrderField);
});

async function loadAbout() {
    try {
        const res = await fetch(`${BASE_URL}/api/about/get_about.php`);
        const result = await res.json();
        const container = document.getElementById('aboutList');
        container.innerHTML = '';

        // Reset trạng thái tồn tại
        existingSections = { header: false, banner: false, footer: false };

        if (result.success && result.data.length > 0) {
            result.data.forEach(item => {
                if (['header', 'banner', 'footer'].includes(item.section_type)) {
                    existingSections[item.section_type] = true;
                }

                const typeText = {
                    header: 'Header (trên cùng)',
                    banner: 'Banner chính',
                    grid_item: 'Card nhỏ',
                    footer: 'Footer (dưới cùng)'
                }[item.section_type] || item.section_type;

                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4 mb-4';
                col.innerHTML = `
                    <div class="card h-100 shadow-sm">
                        ${item.image ? `<img src="${item.image}" class="card-img-top" style="height:180px; object-fit:cover;">` : ''}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${item.title}</h5>
                            <p class="text-muted small"><strong>${typeText}</strong> | Thứ tự: ${item.display_order}</p>
                            <div class="mt-auto">
                                <button class="btn btn-sm btn-warning me-2" onclick="editItem(${item.about_id})">Sửa</button>
                                <button class="btn btn-sm btn-danger" onclick="deleteItem(${item.about_id})">Xóa</button>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });
        } else {
            container.innerHTML = '<div class="col-12 text-center py-5 text-muted">Chưa có nội dung nào.</div>';
        }

        updateAddButtonState();
    } catch (err) {
        console.error(err);
        alert('Lỗi tải dữ liệu');
    }
}

function updateAddButtonState() {
    const btn = document.getElementById('btnAdd');
    if (existingSections.header && existingSections.banner && existingSections.footer) {
        btn.disabled = true;
        btn.textContent = 'Đã đủ các phần chính';
        btn.classList.replace('btn-primary', 'btn-secondary');
    } else {
        btn.disabled = false;
        btn.textContent = 'Thêm nội dung mới';
        btn.classList.replace('btn-secondary', 'btn-primary');
    }
}

// Ẩn/hiện ô thứ tự theo loại
function updateOrderField() {
    const type = sectionTypeSelect.value;
    if (type === 'grid_item') {
        orderWrapper.style.display = 'block';
    } else {
        orderWrapper.style.display = 'none';
        displayOrderInput.value = 1; // luôn = 1 cho header/banner/footer
    }
}

function openModal() {
    document.getElementById('aboutForm').reset();
    document.getElementById('about_id').value = '';
    document.getElementById('previewImage').style.display = 'none';

    // Ưu tiên chọn loại còn trống, nếu không thì grid_item
    const available = Object.keys(existingSections).find(k => !existingSections[k]);
    sectionTypeSelect.value = available || 'grid_item';

    updateOrderField();
    new bootstrap.Modal(document.getElementById('aboutModal')).show();
}

async function editItem(id) {
    const res = await fetch(`${BASE_URL}/api/about/get_about.php`);
    const result = await res.json();
    const item = result.data.find(x => x.about_id == id);

    document.getElementById('about_id').value = item.about_id;
    document.querySelector('#aboutForm [name="title"]').value = item.title;
    document.querySelector('#aboutForm [name="content"]').value = item.content || '';
    sectionTypeSelect.value = item.section_type;

    updateOrderField();
    displayOrderInput.value = item.display_order;

    const preview = document.getElementById('previewImage');
    if (item.image) {
        preview.src = item.image;
        preview.style.display = 'block';
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('aboutModal')).show();
}

// Submit
document.getElementById('aboutForm').addEventListener('submit', async e => {
    e.preventDefault();

    const type = sectionTypeSelect.value;
    const id   = document.getElementById('about_id').value;

    // Chặn tạo mới nếu đã có (frontend bảo vệ)
    if (!id && existingSections[type]) {
        alert(`Đã tồn tại phần "${type}" rồi! Không thể tạo thêm.`);
        return;
    }

    const formData = new FormData(e.target);
    const url = id 
        ? `${BASE_URL}/api/about/update_about.php?id=${id}`
        : `${BASE_URL}/api/about/create_about.php`;

    try {
        const res = await fetch(url, { method: 'POST', body: formData });
        const data = await res.json();
        alert(data.message);

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('aboutModal')).hide();
            loadAbout();
        }
    } catch (err) {
        console.error(err);
        alert('Lỗi kết nối');
    }
});

// Xóa
async function deleteItem(id) {
    if (!confirm('Xóa nội dung này? Không thể hoàn tác!')) return;
    try {
        const res = await fetch(`${BASE_URL}/api/about/delete_about.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        alert(data.message);
        if (data.success) loadAbout();
    } catch (err) {
        alert('Lỗi xóa');
    }
}