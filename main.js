// main.js
// Hàm fetch danh sách phòng khám từ API (ví dụ: clinic.php)
async function fetchClinics() {
    try {
        const response = await fetch('clinic.php');
        if (!response.ok) throw new Error('Lỗi khi lấy dữ liệu phòng khám');
        const clinics = await response.json();
        renderClinics(clinics);
    } catch (err) {
        document.getElementById('clinic-list').innerHTML = '<div style="color:red">Không thể tải danh sách phòng khám.</div>';
    }
}

// Hàm render danh sách phòng khám ra giao diện
function renderClinics(clinics) {
    const list = document.getElementById('clinic-list');
    list.innerHTML = '';
    clinics.forEach(clinic => {
        const card = document.createElement('div');
        card.className = 'clinic-card';
        card.innerHTML = `
            <div class="clinic-logo">
                <img src="${clinic.logo || 'logo.png'}" alt="Logo" style="width:32px;height:32px;object-fit:contain;">
            </div>
            <div class="clinic-info">
                <div class="clinic-name">${clinic.name}</div>
                <div class="clinic-desc">${clinic.description}</div>
            </div>
        `;
        list.appendChild(card);
    });
}

// Khởi động khi trang tải
window.addEventListener('DOMContentLoaded', fetchClinics);