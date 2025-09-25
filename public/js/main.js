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
    console.log("API trả về:", data);
    if (Array.isArray(data) && data.length > 0){
      ALL_CLINICS = data;
    } else {
      ALL_CLINICS = [];
    }
  } catch(e){
    console.error("❌ Lỗi fetchClinics:", e);
    ALL_CLINICS = [];
  }
  console.log("ALL_CLINICS từ API:", ALL_CLINICS);

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

// Gắn event cho các nút service và pet
document.querySelectorAll('[data-service], [data-pet]').forEach(btn => {
  btn.addEventListener('click', () => {
    const { q, service, pet } = parseParams(); // lấy tham số hiện tại từ URL
    const newService = btn.getAttribute('data-service') || service;
    const newPet = btn.getAttribute('data-pet') || pet;

    window.location.href =
      `index.php?page=search&service=${slugify(newService)}&pet=${slugify(newPet)}&q=${slugify(q)}`;
  });
});


function parseParams(){
  const urlParams = new URLSearchParams(window.location.search);
  return {
    q: urlParams.get('q') || '',
    service: urlParams.get('service') || 'all',
    pet: urlParams.get('pet') || 'all'
  };
}

async function setupSearchPage(){
  console.log("🔧 setupSearchPage chạy...");

  const { q } = parseParams();

  const input = document.querySelector('.search-bar input');
  if (input){
    input.value = q;
    input.addEventListener('input', applyFilters);
  }

  // load dữ liệu API trước
  await fetchClinics();
  console.log("📦 ALL_CLINICS:", ALL_CLINICS);

  // gọi filter lần đầu để render
  applyFilters();
  const btn = document.querySelector('.search-bar button');
if (btn && input) {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const q = input.value.trim();
    const params = parseParams(); // lấy service, pet hiện tại
    const service = params.service || 'all';
    const pet = params.pet || 'all';
    window.location.href = `index.php?page=search&q=${encodeURIComponent(q)}&service=${service}&pet=${pet}`;
  });

  // hỗ trợ nhấn Enter
  input.addEventListener('keypress', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      btn.click();
    }
  });
}

}

  
const { service, pet } = parseParams();
const sBtn = document.querySelector(`[data-service="${service}"]`);
if (sBtn){
  const g = sBtn.parentElement;
  g.querySelectorAll('button').forEach(b => b.classList.remove('active'));
  sBtn.classList.add('active');
}

const pBtn = document.querySelector(`[data-pet="${pet}"]`);
if (pBtn){
  const g = pBtn.parentElement;
  g.querySelectorAll('button').forEach(b => b.classList.remove('active'));
  pBtn.classList.add('active');
}

  
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

function normalize(str){
  return str
    .toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // bỏ dấu
    .replace(/\s+/g, '-'); // thay khoảng trắng bằng dấu gạch ngang
}

function slugify(str){
  return str
    .toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // bỏ dấu
    .replace(/[^a-z0-9\s-]/g, '') // giữ lại chữ, số, khoảng trắng, và dấu gạch
    .trim()
    .replace(/\s+/g, '-'); // thay khoảng trắng bằng dấu gạch ngang
}


function applyFilters(){
  const { q, service, pet } = parseParams();

  console.log("🔍 Query:", q, "Service:", service, "Pet:", pet);

  let results = ALL_CLINICS.filter(clinic => {
    const name = slugify(clinic.name);
    const svc  = slugify(clinic.service || '');
    const ani  = slugify(clinic.pet || '');

    return (
      (!q || name.includes(slugify(q))) &&
      (!service || service === 'all' || svc === service) &&
      (!pet || pet === 'all' || ani === pet)
    );
  });

  console.log("✅ Kết quả filter:", results);
  renderClinics(results);
}



window.addEventListener('DOMContentLoaded', ()=>{
  if (document.getElementById('clinic-search-page')) {
    setupSearchPage();
  } else {
    setupHomePage();
  }
});
async function setupHomePage(){
  console.log("setupHomePage chạy...");
  await fetchClinics();
  renderClinics(ALL_CLINICS);   // 👈 thêm dòng này
}


