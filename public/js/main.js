// Search + Home interactions (UTF-8)
let ALL_CLINICS = [];

const $ = (sel, root=document) => root.querySelector(sel);
const $all = (sel, root=document) => Array.from(root.querySelectorAll(sel));

function norm(s){
  try { return (s||'').toString().normalize('NFD').replace(/\p{Diacritic}+/gu,'').toLowerCase(); }
  catch(e){ return (s||'').toString().toLowerCase(); }
}

async function fetchClinics(){
  try{
    const res = await fetch('index.php?page=api.clinic');
    if(!res.ok) throw new Error('fetch_error');
    const data = await res.json();
    ALL_CLINICS = Array.isArray(data)? data: [];
  }catch(e){
    // Minimal fallback to keep UI functional
    ALL_CLINICS = [
      { id:1, name:'Bệnh viện Thú y Petcare', address:'Q10 - TP.HCM', description:'Dịch vụ thú y', logo:'public/img/clinic-center.png', rating:4.7 },
      { id:3, name:'ThiThi Pet Clinic', address:'Bình Thạnh - TP.HCM', description:'Khám – tiêm phòng – grooming', logo:'public/img/clinic-center.png', rating:4.6 }
    ];
  }
}

function renderClinics(list){
  const el = $('#clinic-list');
  if (!el) return;
  el.innerHTML='';
  (list||[]).forEach(c=>{
    const card = document.createElement('div');
    card.className = 'clinic-card';
    const logo = c.logo || c.image || c.image_url || c.avatar || c.photo || 'public/img/clinic-center.png';
    card.innerHTML = `
      <div class="clinic-logo"><img src="${logo}" alt="Logo" style="width:32px;height:32px;object-fit:contain;"></div>
      <div class="clinic-info">
        <div class="clinic-name">${c.name || 'Phòng khám'}</div>
        <div class="clinic-address">${c.address || c.description || ''}</div>
        <div class="clinic-meta"><i class="fas fa-star"></i><span>${(c.rating ?? c.score ?? '').toString()}</span></div>
      </div>`;
    if (c.id){ card.style.cursor='pointer'; card.addEventListener('click', ()=>{ location.href=`index.php?page=clinic-detail&id=${encodeURIComponent(c.id)}`; }); }
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
    const sKeyRaw = (c.service_category!=null? c.service_category : (c.category!=null? c.category : ''));
    const pKeyRaw = (c.pet_type!=null? c.pet_type : '');
    const sKey = norm(sKeyRaw.toString());
    const pKey = norm(pKeyRaw.toString());
    // Nếu dữ liệu không có trường service/pet thì coi như khớp
    const matchS = (s==='all') || (!sKey && s!=='all' ? true : sKey.includes(s));
    const matchP = (p==='all') || (!pKey && p!=='all' ? true : pKey.includes(p));
    return matchQ && matchS && matchP;
  });
  // If no results and user typed a query, relax service/pet filters
  if (!filtered.length && q) {
    filtered = (ALL_CLINICS||[]).filter(c=> norm(`${c.name??''} ${c.description??''} ${c.address??''}`).includes(q));
  }
  renderClinics(filtered);
}

function parseParams(){ const p = new URLSearchParams(location.search); return { q:p.get('q')||'', service:p.get('service')||'all', pet:p.get('pet')||'all' }; }

function setupSearchPage(){
  let { q, service, pet } = parseParams();
  const input = $('.search-bar input');
  let currentInput = (input?.value || '').trim();
  if (!q || q.toLowerCase() === 'search') q = currentInput;
  if (q && q.toLowerCase() === 'search') q = '';
  if (currentInput && currentInput.toLowerCase() === 'search') currentInput = '';
  if (input){ input.value = q || currentInput; input.addEventListener('input', applyFilters); }
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

function setupHomePage(){
  const formInput = document.querySelector('.home-search .search-bar input');
  const formBtn = document.querySelector('.home-search .search-bar button');
  const go=()=>{ const q=encodeURIComponent(formInput?.value||''); const base='index.php?page=search'; location.href = q? `${base}&q=${q}`: base; };
  if (formInput) formInput.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); go(); }});
  if (formBtn) formBtn.addEventListener('click', e=>{ e.preventDefault(); go(); });
}

window.addEventListener('DOMContentLoaded', ()=>{
  if (document.getElementById('clinic-search-page')) {
    setupSearchPage();
  } else {
    setupHomePage();
    // Nạp danh sách trên trang Home
    fetchClinics().then(()=>{ applyFilters(); });
  }
});
