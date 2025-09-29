(() => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $all = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  let ALL_CLINICS = [];

  const SERVICE_KEYWORDS = {
    'kham-benh': ['khám', 'kham', 'bệnh', 'benh', 'khám bệnh', 'kham benh'],
    'tiem-phong': ['tiêm', 'tiem', 'phòng', 'phong', 'vaccine', 'chích', 'tiêm phòng', 'tiem phong'],
    'spa': ['spa', 'groom', 'grooming'],
    'khach-san': ['khách sạn', 'khach san', 'lưu trú', 'luu tru', 'hotel'],
    'phau-thuat': ['phẫu thuật', 'phau thuat', 'surgery', 'phẫu'],
    'khac': ['khác', 'khac'],
  };

  function slug(str = '') {
    return str
      .toString()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/\s+/g, '-');
  }

  function tokenize(text = '') {
    const trimmed = text.trim();
    if (!trimmed) return [];
    const normalized = trimmed
      .toLowerCase()
      .replace(/[\s,;]+/g, ' ')
      .trim();
    if (!normalized) return [];
    const parts = normalized.split(' ').filter(Boolean);
    const tokens = [];
    parts.forEach(part => {
      tokens.push(part);
      const slugPart = slug(part);
      if (slugPart && slugPart !== part) {
        tokens.push(slugPart);
      }
    });
    return Array.from(new Set(tokens));
  }

  function collectCandidates(item = {}) {
    const fields = [
      item.name,
      item.description,
      item.address,
      item.service_categories,
      item.services,
      item.pets,
    ];
    return fields
      .filter(value => typeof value === 'string' && value.trim() !== '')
      .map(value => {
        const lower = value.toLowerCase();
        return [lower, slug(value)];
      });
  }

  function containsAllTokens(candidates, tokens) {
    return tokens.every(token => {
      return candidates.some(([lower, slugValue]) => {
        if (lower && lower.includes(token)) return true;
        if (slugValue && slugValue.includes(token)) return true;
        return false;
      });
    });
  }

  function serviceSlugList(service) {
    if (service === 'all') return [];
    const base = [slug(service)].filter(Boolean);
    const keywords = SERVICE_KEYWORDS[service] || [];
    const aliasSlugs = keywords
      .map(v => slug(v))
      .filter(Boolean);
    return Array.from(new Set([...base, ...aliasSlugs]));
  }

  function matchesService(candidates, serviceSlugs) {
    if (serviceSlugs.length === 0) return true;
    return candidates.some(([, slugValue]) => {
      if (!slugValue) return false;
      return serviceSlugs.some(needle => slugValue.includes(needle));
    });
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

      if (q !== '') {
        service = 'all';
      }

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
    const service = (params.get('service') || 'all').trim() || 'all';

    const tokenList = tokenize(q);
    const serviceSlugs = serviceSlugList(service);

    const filtered = ALL_CLINICS.filter(item => {
      const candidates = collectCandidates(item);
      return containsAllTokens(candidates, tokenList) && matchesService(candidates, serviceSlugs);
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
    const input = container.querySelector('.search-bar input[name="q"]');

    if (listContainer && listContainer.dataset.server === '1') {
      if (input) {
        input.value = q;
      }
      return;
    }

    if (input) {
      input.value = q;
    }

    fetchClinics({ q }).then(data => {
      ALL_CLINICS = data;
      applySearchFilters();
    });

    $all('.chip[data-service]').forEach(chip => {
      chip.addEventListener('click', () => {
        params.set('service', chip.dataset.service);
        params.delete('q');
        if (input) {
          input.value = '';
        }
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