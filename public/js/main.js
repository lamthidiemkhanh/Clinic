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

  function bindSearchForm(form) {
    if (!form || form.dataset.searchBound === '1') {
      return;
    }
    form.dataset.searchBound = '1';
    form.addEventListener('submit', event => {
      const formData = new FormData(form);
      const qInput = form.querySelector('input[name="q"]');
      const serviceInput = form.querySelector('input[name="service"]');
      let q = (formData.get('q') || '').trim();
      let service = (formData.get('service') || 'all').trim() || 'all';

      if (qInput) {
        qInput.value = q;
      }
      if (serviceInput) {
        serviceInput.value = service;
      }

      const params = new URLSearchParams();
      params.set('page', 'search');
      params.set('service', service);
      if (q !== '') {
        params.set('q', q);
      }

      event.preventDefault();
      window.location.href = `index.php?${params.toString()}`;
    });
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
      const categories = item.service_categories || '';
      const services = item.services || '';
      const pets = item.pets || '';
      card.innerHTML = `
        <div class="clinic-logo"><img src="${logo}" alt="Logo" style="width:32px;height:32px;object-fit:contain;"></div>
        <div class="clinic-info">
          <div class="clinic-name"><a href="index.php?page=clinic-detail&id=${encodeURIComponent(item.id)}">${item.name ?? ''}</a></div>
          <div class="clinic-address">${item.address ?? item.description ?? ''}</div>
          ${categories ? `<div class="clinic-meta">Danh mục: ${categories}</div>` : ''}
          ${services ? `<div class="clinic-meta">Dịch vụ: ${services}</div>` : ''}
          ${pets ? `<div class="clinic-meta">Thú cưng: ${pets}</div>` : ''}
        </div>
      `;
      container.appendChild(card);
    });
  }

  function applySearchFilters() {
    const params = new URLSearchParams(location.search);
    const q = params.get('q') || '';
    const service = params.get('service') || 'all';

    const filtered = ALL_CLINICS.filter(item => {
      const nameSlug = slug(item.name || '');
      const serviceSlug = slug(item.service_categories || item.services || '');
      const servicesSlug = slug(item.services || '');
      const addressSlug = slug(item.address || '');
      const petsSlug = slug(item.pets || '');
      const keywordSlug = slug(q);

      const matchKeyword = !q
        || nameSlug.includes(keywordSlug)
        || serviceSlug.includes(keywordSlug)
        || servicesSlug.includes(keywordSlug)
        || addressSlug.includes(keywordSlug)
        || petsSlug.includes(keywordSlug);

      const matchService = service === 'all' || serviceSlug.includes(service);
      return matchKeyword && matchService;
    });

    const container = document.getElementById('clinic-list');
    renderClinics(filtered, container);
  }

  function setupSearchPage() {
    const container = document.getElementById('clinic-search-page');
    if (!container) return;

    const params = new URLSearchParams(location.search);
    const q = params.get('q') || '';
    const listContainer = document.getElementById('clinic-list');

    if (listContainer && listContainer.dataset.server === '1') {
      const input = container.querySelector('.search-bar input[name="q"]');
      if (input) {
        input.value = q;
      }
      return;
    }

    fetchClinics({ q }).then(data => {
      ALL_CLINICS = data;
      applySearchFilters();
    });

    $all('.chip[data-service]').forEach(chip => {
      chip.addEventListener('click', () => {
        params.set('service', chip.dataset.service);
        history.replaceState(null, '', `${location.pathname}?${params.toString()}`);
        applySearchFilters();
      });
    });
  }

  function setupHomePage() {
    const container = document.getElementById('clinic-list');
    if (!container || container.dataset.server === '1') {
      return;
    }
    fetchClinics().then(data => {
      ALL_CLINICS = data;
      renderClinics(ALL_CLINICS, container);
    });
  }

  window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-search-form="1"]').forEach(bindSearchForm);

    if (document.getElementById('clinic-search-page')) {
      setupSearchPage();
    } else {
      setupHomePage();
    }
  });
})();
