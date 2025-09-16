// Main UI logic (UTF-8)
let ALL_CLINICS = [];

function $(sel, root=document){ return root.querySelector(sel); }
function $all(sel, root=document){ return Array.from(root.querySelectorAll(sel)); }

async function fetchClinics() {
  try {
    const response = await fetch('index.php?page=api.clinic');
    if (!response.ok) throw new Error('fetch_error');
    const clinics = await response.json();
    ALL_CLINICS = Array.isArray(clinics) ? clinics : [];
    if (!ALL_CLINICS.length) throw new Error('empty');
    applyFilters();
  } catch (err) {
    // Client-side fallback list when API/DB is unavailable
    ALL_CLINICS = [
      { id: 1, name: 'PhÃ²Ngũ Hành Sơn, Đà Nẵng', rating: 4.7 },
      { id: 2, name: 'PetCare Center', address: 'Cáº§u Giáº¥y, HÃ  Ná»™i', description: 'KhÃ¡m bá»‡nh, tiÃªm phÃ²Ngũ Hành Sơn, Đà Nẵng', logo: 'public/img/clinic-center.png', rating: 4.5 },
      { id: 3, name: 'Happy Paw Clinic', address: 'Ngũ Hành Sơn, Đà Nẵng', description: 'KhÃ¡m â€“ pháº«u thuáº­t â€“ lÆ°u trÃº', logo: 'public/img/clinic-center.png', rating: 4.6 }
    ];
    applyFilters();
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
    const logoUrl = clinic.logo || clinic.image || clinic.image_url || clinic.avatar || clinic.photo || 'public/img/clinic-center.png';
    card.innerHTML = `
      <div class="clinic-logo">
        <img src="${logoUrl}" alt="Logo" style="width:32px;height:32px;object-fit:contain;">
      </div>
      <div class="clinic-info">
        <div class="clinic-name">${clinic.name || 'PhÃ²ng khÃ¡m'}</div>
        <div class="clinic-address">${clinic.address || clinic.description || ''}</div>
        <div class="clinic-meta">
          <i class="fas fa-star"></i>
          <span>${ratingText}</span>
        </div>
      </div>
    `;
    if (clinic.id) {
      card.style.cursor = 'pointer';
      card.addEventListener('click', () => {
        location.href = `index.php?page=clinic-detail&id=${encodeURIComponent(clinic.id)}`;
      });
    }
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
  return { q: params.get('q') || '', service: params.get('service') || 'all', pet: params.get('pet') || 'all' };
}

// Search page
function setupSearchPageUI() {
  const { q, service, pet } = parseParams();
  const input = $('.search-bar input');
  if (input) input.value = q;
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
  $all('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      const groupSelector = chip.hasAttribute('data-service') ? '[data-service]' : '[data-pet]';
      chip.parentElement.querySelectorAll(groupSelector).forEach(el => el.classList.remove('active'));
      chip.classList.add('active');
      applyFilters();
      const params = new URLSearchParams(location.search);
      if (chip.dataset.service) params.set('service', chip.dataset.service);
      if (chip.dataset.pet) params.set('pet', chip.dataset.pet);
      history.replaceState(null, '', `${location.pathname}?${params.toString()}`);
    });
  });
  fetchClinics();
}

// Home page
function setupIndexPageUI() {
  const input = $('.header .search-bar input');
  const btn = $('.header .search-bar button');
  function goToSearch() {
    const q = encodeURIComponent(input?.value || '');
    location.href = `index.php?page=search?q=${q}`;
  }
  if (input) input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); goToSearch(); }});
  if (btn) btn.addEventListener('click', goToSearch);
  const serviceItems = $all('.service-item');
  const map = ['spa','kham-benh','tiem-phong','khach-san','Phòng khám Thú y Khang Việt','khac'];
  serviceItems.forEach((el, idx) => {
    el.addEventListener('click', () => {
      const svc = el.dataset.service || map[idx] || 'all';
      location.href = `index.php?page=search?service=${encodeURIComponent(svc)}`;
    });
  });
}

window.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('clinic-search-page')) {
    setupSearchPageUI();
  } else {
    setupIndexPageUI();
    const list = document.getElementById('clinic-list');
    // Náº¿u Ä‘Ã£ cÃ³ item render sáºµn tá»« server thÃ¬ khÃ´ng cáº§n gá»i API ná»¯a
    if (!list || list.children.length === 0) {
      fetchClinics();
    }
  }
});

