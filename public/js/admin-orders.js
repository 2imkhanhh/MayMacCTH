const BASE_URL = '/MayMacCTH';
let currentOrderId = null;
let allOrders = []; // Lưu toàn bộ đơn hàng để tìm kiếm + lọc

document.addEventListener('DOMContentLoaded', () => {
    loadOrders();

    // Filter trạng thái + Tìm kiếm
    document.getElementById('filterOrderStatus').addEventListener('change', filterAndSearch);
    document.getElementById('searchInput').addEventListener('input', filterAndSearch);
});

async function loadOrders() {
    try {
        const res = await fetch(`${BASE_URL}/api/order/get_orders.php`);
        const data = await res.json();

        if (data.success && data.data && data.data.length > 0) {
            allOrders = data.data;
            renderOrders(allOrders);
        } else {
            document.getElementById('orderList').innerHTML = `
                <tr><td colspan="9" class="text-center py-5 text-muted">Chưa có đơn hàng nào</td></tr>
            `;
        }
    } catch (err) {
        console.error(err);
        alert('Lỗi tải danh sách đơn hàng');
    }
}

// Hiển thị danh sách đơn hàng
function renderOrders(orders) {
    const tbody = document.getElementById('orderList');
    tbody.innerHTML = '';

    if (orders.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="9" class="text-center py-5 text-muted">Không tìm thấy đơn hàng nào</td></tr>
        `;
        return;
    }

    orders.forEach(order => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td data-label="Mã đơn"><strong>${order.order_code}</strong></td>
            <td data-label="Khách hàng">${order.name}</td>
            <td data-label="SĐT">${order.phone}</td>
            <td data-label="Tổng tiền" class="text-danger fw-bold">
                ${parseInt(order.total).toLocaleString('vi-VN')}đ
            </td>
            <td data-label="Phương thức">${order.payment_method.toUpperCase()}</td>
            <td data-label="Trạng thái đơn">
                <span class="status-${order.order_status}">${formatOrderStatus(order.order_status)}</span>
            </td>
            <td data-label="Trạng thái TT">
                <span class="payment-${order.payment_status}">
                    ${order.payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'}
                </span>
            </td>
            <td data-label="Ngày đặt">${new Date(order.created_at).toLocaleDateString('vi-VN')}</td>
            <td class="text-center">
                <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); showOrderDetail(${order.order_id})">
                    <i class="bi bi-eye-fill"></i>
                </button>
            </td>
        `;
        tr.style.cursor = 'pointer';
        tr.onclick = () => showOrderDetail(order.order_id);
        tbody.appendChild(tr);
    });
}

// Lọc + Tìm kiếm kết hợp
function filterAndSearch() {
    const searchTerm = document.getElementById('searchInput').value.trim().toLowerCase();
    const statusFilter = document.getElementById('filterOrderStatus').value;

    let filtered = [...allOrders];

    // Lọc theo trạng thái
    if (statusFilter) {
        filtered = filtered.filter(order => order.order_status === statusFilter);
    }

    // Tìm kiếm theo mã đơn, tên khách hàng, số điện thoại
    if (searchTerm) {
        filtered = filtered.filter(order => {
            const code = order.order_code.toLowerCase();
            const name = order.name.toLowerCase();
            const phone = order.phone.replace(/\s/g, ''); // bỏ khoảng trắng trong sđt

            return code.includes(searchTerm) ||
                   name.includes(searchTerm) ||
                   phone.includes(searchTerm.replace(/\s/g, ''));
        });
    }

    renderOrders(filtered);
}

// Định dạng trạng thái đơn hàng
function formatOrderStatus(status) {
    const map = {
        pending: 'Chờ xác nhận',
        confirmed: 'Đã xác nhận',
        completed: 'Hoàn thành',
        cancelled: 'Đã hủy'
    };
    return map[status] || status;
}

// Xem chi tiết đơn hàng
async function showOrderDetail(orderId) {
    currentOrderId = orderId;

    try {
        const res = await fetch(`${BASE_URL}/api/order/get_order_detail.php?id=${orderId}`);
        const data = await res.json();

        if (!data.success || !data.order) {
            alert('Không tải được chi tiết đơn hàng');
            return;
        }

        const o = data.order;
        const items = data.items || [];

        document.getElementById('modalOrderCode').textContent = o.order_code;

        // Hàm tự động xác định trạng thái thanh toán
        const getAutoPaymentStatus = (status) => {
            return (status === 'completed' || status === 'cancelled') ? 'paid' : 'unpaid';
        };

        document.getElementById('modalBody').innerHTML = `
            <!-- Thông tin khách hàng -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Khách hàng:</strong> ${o.name}</p>
                    <p class="mb-2"><strong>SĐT:</strong> ${o.phone}</p>
                    <p class="mb-2"><strong>Địa chỉ:</strong> ${o.address}</p>
                    <p class="mb-2"><strong>Ghi chú:</strong> ${o.note ? o.note : '<em class="text-muted">Không có</em>'}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-2"><strong>Ngày đặt:</strong> ${new Date(o.created_at).toLocaleString('vi-VN')}</p>
                    <p class="mb-2"><strong>Phương thức:</strong> ${o.payment_method === 'cod' ? 'COD' : 'Chuyển khoản'}</p>
                    <p class="mb-0"><strong>Tổng tiền:</strong> 
                        <span class="text-danger fs-4 fw-bold">${parseInt(o.total).toLocaleString('vi-VN')}đ</span>
                    </p>
                </div>
            </div>

            <h6 class="border-top pt-3 mb-3 fw-bold">Danh sách sản phẩm</h6>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">SL</th>
                            <th class="text-end">Giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.length > 0 ? items.map(item => `
                            <tr>
                                <td>
                                    <strong>${item.product_name || 'SP ID ' + item.product_id}</strong><br>
                                    <small class="text-muted">
                                        ${item.color_name ? 'Màu: ' + item.color_name : ''}
                                        ${item.size ? (item.color_name ? ' | ' : '') + 'Size: ' + item.size : ''}
                                    </small>
                                </td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-end">${parseInt(item.unit_price).toLocaleString('vi-VN')}đ</td>
                                <td class="text-end fw-bold">${(item.quantity * item.unit_price).toLocaleString('vi-VN')}đ</td>
                            </tr>
                        `).join('') : `
                            <tr><td colspan="4" class="text-center text-muted">Không có sản phẩm</td></tr>
                        `}
                    </tbody>
                </table>
            </div>

            <div class="mt-4 bg-light p-4 rounded border">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="form-label fw-bold mb-1">Trạng thái đơn:</label>
                        <select id="statusSelect" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                            <option value="pending">Chờ xác nhận</option>
                            <option value="confirmed">Đã xác nhận</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label fw-bold mb-1">Thanh toán:</label>
                        <select id="paymentStatusSelect" class="form-select form-select-sm" disabled style="width: auto; min-width: 130px;">
                            <option value="unpaid">Chưa thanh toán</option>
                            <option value="paid">Đã thanh toán</option>
                        </select>
                    </div>
                    <div class="col-auto ms-auto">
                        <button class="btn btn-success px-4" onclick="updateOrderStatus()">
                            Cập nhật
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Gán giá trị hiện tại
        const statusSelect = document.getElementById('statusSelect');
        const paymentSelect = document.getElementById('paymentStatusSelect');

        statusSelect.value = o.order_status;
        paymentSelect.value = getAutoPaymentStatus(o.order_status);

        // Khi đổi trạng thái đơn → tự động đổi thanh toán
        statusSelect.addEventListener('change', function () {
            paymentSelect.value = getAutoPaymentStatus(this.value);
        });

        // Mở modal
        new bootstrap.Modal(document.getElementById('orderDetailModal')).show();

    } catch (err) {
        console.error(err);
        alert('Lỗi tải chi tiết đơn hàng');
    }
}

async function updateOrderStatus() {
    if (!currentOrderId) return;

    const order_status = document.getElementById('statusSelect').value;
    const payment_status = (order_status === 'completed' || order_status === 'cancelled') ? 'paid' : 'unpaid';

    try {
        const res = await fetch(`${BASE_URL}/api/order/update_status.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_id: currentOrderId,
                order_status,
                payment_status
            })
        });

        const data = await res.json();

        if (data.success) {
            alert('Cập nhật trạng thái thành công!');
            bootstrap.Modal.getInstance(document.getElementById('orderDetailModal')).hide();
            loadOrders(); // Tải lại dữ liệu mới nhất
        } else {
            alert(data.message || 'Cập nhật thất bại');
        }
    } catch (err) {
        console.error(err);
        alert('Lỗi kết nối server');
    }
}