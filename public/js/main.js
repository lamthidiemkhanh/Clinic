console.log("main.js loaded ");
// Main interactions for Home and Search pages
let ALL_CLINICS = [];
const $ = (sel, root=document) => root.querySelector(sel);
const $all = (sel, root=document) => Array.from(root.querySelectorAll(sel));

function norm(s){
  // Avoid Unicode property escapes for broad browser support
  try {
    return (s||'')
      .toString()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '') // strip combining marks
      .toLowerCase();
  } catch(e){
    try { return (s||'').toString().toLowerCase(); }
    catch(_) { return ''; }
  }
}

async function fetchClinics(){
  try {
    const res = await fetch('index.php?page=api.clinic');
    if(!res.ok) throw new Error('fetch_error');
    const data = await res.json();
    console.log("API data:", data);

    if (!Array.isArray(data) || data.length === 0) throw new Error('empty');

    ALL_CLINICS = data;
    renderClinics(ALL_CLINICS); // 👈 render trực tiếp
  } catch(e){
    console.error("API fetch error:", e);
  }
}


function renderClinics(list){
    console.log("Rendering...", list);
  const el = document.querySelector('#clinic-list');
  if (!el) {
    console.warn("Không tìm thấy #clinic-list");
    return;
  }
  el.innerHTML = '';
  

  (list||[]).forEach(c=>{
    const card = document.createElement('div');
    card.className = 'clinic-card';

    const logo = c.logo || c.image || c.image_url || c.avatar || c.photo || 'public/img/clinic-center.png';
    const rating = c.rating ?? c.score; // chỉ lấy nếu có

    card.innerHTML = `
      <div class="clinic-logo">
        <img src="${logo}" alt="Logo" style="width:32px;height:32px;object-fit:contain;">
      </div>
      <div class="clinic-info">
        <div class="clinic-name">${c.name || 'Phòng khám'}</div>
        <div class="clinic-address">${c.address || c.description || ''}</div>
        ${rating ? `<div class="clinic-meta"><i class="fas fa-star"></i><span>${rating}</span></div>` : ''}
      </div>
    `;

    if (c.id){
      card.style.cursor='pointer';
      card.addEventListener('click', ()=>{
        location.href=`index.php?page=clinic-detail&id=${encodeURIComponent(c.id)}`;
      });
    }
    el.appendChild(card);
  });
}


function applyFilters(){
  const inputEl = $('.search-bar input');
  const q = norm(inputEl?.value || '');
  const s = ($('.chip-group [data-service].active')?.dataset.service)||'all';
  const p = ($('.chip-group [data-pet].active')?.dataset.pet)||'all';
  let filtered = (ALL_CLINICS||[]).filter(c=>{
    const text = norm(`${c.name??''} ${c.description??''} ${c.address??''}`);
    const matchQ = !q || text.includes(q);
    const sKey = norm((c.service_category||c.category||'').toString());
    const pKey = norm((c.pet_type||'').toString());
    const matchS = (s==='all') || (!sKey && s!=='all' ? true : sKey.includes(s));
    const matchP = (p==='all') || (!pKey && p!=='all' ? true : pKey.includes(p));
    return matchQ && matchS && matchP;
  });
  if (!filtered.length && q) {
    filtered = (ALL_CLINICS||[]).filter(c=> norm(`${c.name??''} ${c.description??''} ${c.address??''}`).includes(q));
  }
  renderClinics(filtered);
}

function parseParams(){ const p = new URLSearchParams(location.search); return { q:p.get('q')||'', service:p.get('service')||'all', pet:p.get('pet')||'all' }; }

function setupSearchPage(){
  let { q, service, pet } = parseParams();
  const input = $('.search-bar input');
  if (input){ input.value = q; input.addEventListener('input', applyFilters); }
  const btn = $('.search-bar button'); if (btn){ btn.addEventListener('click', e=>{ e.preventDefault(); applyFilters(); }); }
  const sBtn = document.querySelector(`[data-service="${service}"]`); if (sBtn){ const g=sBtn.parentElement; g.querySelectorAll('[data-service]').forEach(x=>x.classList.remove('active')); sBtn.classList.add('active'); }
  const pBtn = document.querySelector(`[data-pet="${pet}"]`); if (pBtn){ const g=pBtn.parentElement; g.querySelectorAll('[data-pet]').forEach(x=>x.classList.remove('active')); pBtn.classList.add('active'); }
  $all('.chip').forEach(chip=> chip.addEventListener('click', ()=>{
    const sel = chip.hasAttribute('data-service')? '[data-service]' : '[data-pet]';
    chip.parentElement.querySelectorAll(sel).forEach(x=>x.classList.remove('active'));
    chip.classList.add('active');
    const u = new URLSearchParams(location.search);
    if (chip.dataset.service) u.set('service', chip.dataset.service);
    if (chip.dataset.pet) u.set('pet', chip.dataset.pet);
    history.replaceState(null,'',`${location.pathname}?${u.toString()}`);
    applyFilters();
  }));
  fetchClinics().then(applyFilters);
}

function applyFilters(){
  const inputEl = $('.search-bar input');
  const q = norm(inputEl?.value || '');
  const s = ($('.chip-group [data-service].active')?.dataset.service)||'all';
  const p = ($('.chip-group [data-pet].active')?.dataset.pet)||'all';

  // nếu không có filter gì → show tất cả
  if (!q && s==='all' && p==='all') {
    renderClinics(ALL_CLINICS);
    return;
  }

  let filtered = (ALL_CLINICS||[]).filter(c=>{
    const text = norm(`${c.name??''} ${c.description??''} ${c.address??''}`);
    const matchQ = !q || text.includes(q);
    const sKey = norm((c.service_category||c.category||'').toString());
    const pKey = norm((c.pet_type||'').toString());
    const matchS = (s==='all') || sKey.includes(s);
    const matchP = (p==='all') || pKey.includes(p);
    return matchQ && matchS && matchP;
  });
  renderClinics(filtered);
}


window.addEventListener('DOMContentLoaded', ()=>{
  if (document.getElementById('clinic-search-page')) {
    setupSearchPage();
  } else {
    setupHomePage();
  }
});
function setupHomePage(){
  console.log("setupHomePage chạy...");
  fetchClinics(); // fetchClinics đã tự render rồi
}

