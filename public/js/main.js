(() => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $all = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  let ALL_CLINICS = [];

  function slug(str = "") {
    return str
      .toString()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/\s+/g, '-');
  }

  async function fetchClinics(params = {}) {
    const search = new URLSearchParams({ per_page: params.perPage || 200 });
    if (params.q) search.set('q', params.q);
    if (params.pageNumber) search.set('page_number', params.pageNumber);

    try {
      const res = await fetch(`index.php?page=api.clinic&${search.toString()}`);
      if (!res.ok) throw new Error('fetch_error');
      const payload = await res.json();
      if (Array.isArray(payload)) return payload;
      if (payload && Array.isArray(payload.data)) return payload.data;
    } catch (err) {
      console.warn('fetchClinics fallback', err);
    }
    return [];
  }

  function renderClinics(list, container) {
    if (!container) return;
    container.innerHTML = '';
    list.forEach(item => {
      const card = document.createElement('div');
      card.className = 'clinic-card';
      const logo = item.logo || item.image || item.image_url || item.avatar || 'public/img/clinic-center.png';
      card.innerHTML = `
        <div class="clinic-logo"><img src="${logo}" alt="Logo" style="width:32px;height:32px;object-fit:contain;"></div>
        <div class="clinic-info">
          <div class="clinic-name"><a href="index.php?page=clinic-detail&id=${encodeURIComponent(item.id)}">${item.name ?? ''}</a></div>
          <div class="clinic-address">${item.address ?? item.description ?? ''}</div>
          ${item.services ? `<div class="clinic-meta">Dịch vụ: ${item.services}</div>` : ''}
        </div>
      `;
      container.appendChild(card);
    });
  }

  function applySearchFilters() {
    const params = new URLSearchParams(location.search);
    const q = params.get('q') || '';
    const service = params.get('service') || 'all';
    const pet = params.get('pet') || 'all';

    let filtered = ALL_CLINICS.filter(item => {
      const nameSlug = slug(item.name || '');
      const serviceSlug = slug(item.services || item.service_category || '');
      const petSlug = slug(item.pets || item.pet || '');
      const matchQ = !q || nameSlug.includes(slug(q));
      const matchService = service === 'all' || serviceSlug.includes(service);
      const matchPet = pet === 'all' || petSlug.includes(pet);
      return matchQ && matchService && matchPet;
    });

    const container = document.getElementById('clinic-list');
    renderClinics(filtered, container);
  }

  function setupSearchPage() {
    const container = document.getElementById('clinic-list');
    if (!container) return;

    const params = new URLSearchParams(location.search);
    const q = params.get('q') || '';
    const input = document.querySelector('.search-bar input');
    if (input) {
      input.value = q;
      input.addEventListener('keypress', e => {
        if (e.key === 'Enter') {
          e.preventDefault();
          params.set('q', input.value.trim());
          location.search = params.toString();
        }
      });
    }

    $all('.chip').forEach(chip => {
      chip.addEventListener('click', () => {
        if (chip.dataset.service) params.set('service', chip.dataset.service);
        if (chip.dataset.pet) params.set('pet', chip.dataset.pet);
        history.replaceState(null, '', `${location.pathname}?${params.toString()}`);
        applySearchFilters();
      });
    });

    fetchClinics({ q }).then(data => {
      ALL_CLINICS = data;
      applySearchFilters();
    });
  }

  function setupHomePage() {
    const container = document.getElementById('clinic-list');
    if (!container || container.dataset.server === '1') {
      return; // server-rendered; pagination handled on backend
    }
    fetchClinics().then(data => {
      ALL_CLINICS = data;
      renderClinics(ALL_CLINICS, container);
    });
  }

  window.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('clinic-search-page')) {
      setupSearchPage();
    } else {
      setupHomePage();
    }
  });
})();
