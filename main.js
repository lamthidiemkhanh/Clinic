// main.js
// Dùng chung cho trang chủ (index) và trang tìm kiếm (search.html)

let ALL_CLINICS = [];

function $(sel, root=document){ return root.querySelector(sel); }
function $all(sel, root=document){ return Array.from(root.querySelectorAll(sel)); }

async function fetchClinics() {
    try {
        const response = await fetch('clinic.php');
        if (!response.ok) throw new Error('Lỗi khi tải dữ liệu phòng khám');
        const clinics = await response.json();
        ALL_CLINICS = Array.isArray(clinics) ? clinics : [];
        applyFilters();
    } catch (err) {
        const el = document.getElementById('clinic-list');
        if (el) el.innerHTML = '<div style="color:red">Không thể tải danh sách phòng khám.</div>';
    }
}

function renderClinics(clinics) {
    const list = document.getElementById('clinic-list');
    if (!list) return;
    list.innerHTML = '';
    (clinics || []).forEach(clinic => {
        const card = document.createElement('div');
        card.className = 'clinic-card';
        const ratingText = (clinic.rating ?? clinic.score ?? '').toString();
        card.innerHTML = `
            <div class="clinic-logo">
                <img src="${clinic.logo || 'logo.png'}" alt="Logo" style="width:32px;height:32px;object-fit:contain;">
            </div>
            <div class="clinic-info">
                <div class="clinic-name">${clinic.name || 'Tên phòng khám'}</div>
                <div class="clinic-address">${clinic.address || clinic.description || ''}</div>
                <div class="clinic-meta">
                    <i class="fas fa-star"></i>
                    <span>${ratingText}</span>
                </div>
            </div>
        `;
        list.appendChild(card);
    });
}

function applyFilters() {
    const inputEl = $('.search-bar input');
    const query = (inputEl?.value || '').toLowerCase();
    const activeService = $('.chip-group [data-service].active')?.dataset.service || 'all';
    const activePet = $('.chip-group [data-pet].active')?.dataset.pet || 'all';

    const filtered = (ALL_CLINICS || []).filter(c => {
        const text = `${c.name ?? ''} ${c.description ?? ''} ${c.address ?? ''}`.toLowerCase();
        const matchText = !query || text.includes(query);
        const serviceKey = (c.service_category || c.category || '').toString().toLowerCase();
        const petKey = (c.pet_type || '').toString().toLowerCase();
        const matchService = activeService === 'all' || serviceKey.includes(activeService);
        const matchPet = activePet === 'all' || petKey.includes(activePet);
        return matchText && matchService && matchPet;
    });

    renderClinics(filtered);
}

function parseParams() {
    const params = new URLSearchParams(location.search);
    return {
        q: params.get('q') || '',
        service: params.get('service') || 'all',
        pet: params.get('pet') || 'all'
    };
}

// Trang tìm kiếm
function setupSearchPageUI() {
    const { q, service, pet } = parseParams();
    const input = $('.search-bar input');
    if (input) input.value = q;

    // set active chips from params
    const serviceBtn = $(`[data-service="${service}"]`);
    if (serviceBtn) {
        const group = serviceBtn.parentElement;
        $all('[data-service]', group).forEach(el => el.classList.remove('active'));
        serviceBtn.classList.add('active');
    }
    const petBtn = $(`[data-pet="${pet}"]`);
    if (petBtn) {
        const group = petBtn.parentElement;
        $all('[data-pet]', group).forEach(el => el.classList.remove('active'));
        petBtn.classList.add('active');
    }

    if (input) input.addEventListener('input', applyFilters);
    const btn = $('.search-bar button');
    if (btn) btn.addEventListener('click', applyFilters);

    // chip toggles
    $all('.chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const groupSelector = chip.hasAttribute('data-service') ? '[data-service]' : '[data-pet]';
            chip.parentElement.querySelectorAll(groupSelector).forEach(el => el.classList.remove('active'));
            chip.classList.add('active');
            applyFilters();
            // update URL (no reload)
            const params = new URLSearchParams(location.search);
            if (chip.dataset.service) params.set('service', chip.dataset.service);
            if (chip.dataset.pet) params.set('pet', chip.dataset.pet);
            history.replaceState(null, '', `${location.pathname}?${params.toString()}`);
        });
    });

    fetchClinics();
}

// Trang chủ
function setupIndexPageUI() {
    const input = $('.header .search-bar input');
    const btn = $('.header .search-bar button');
    function goToSearch() {
        const q = encodeURIComponent(input?.value || '');
        location.href = `search.html?q=${q}`;
    }
    if (input) input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); goToSearch(); }});
    if (btn) btn.addEventListener('click', goToSearch);

    // service grid click
    const serviceItems = $all('.service-item');
    const map = ['spa','kham-benh','tiem-phong','khach-san','phau-thuat','khac'];
    serviceItems.forEach((el, idx) => {
        el.addEventListener('click', () => {
            const svc = el.dataset.service || map[idx] || 'all';
            location.href = `search.html?service=${encodeURIComponent(svc)}`;
        });
    });
}

window.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('clinic-search-page')) {
        setupSearchPageUI();
    } else {
        setupIndexPageUI();
        // Hiển thị danh sách phòng khám trên trang chủ
        fetchClinics();
    }
});
