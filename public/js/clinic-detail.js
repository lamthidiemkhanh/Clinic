// Copied from root clinic-detail.js with API route
(function(){
  function $(s, r=document){ return r.querySelector(s); }
  function $el(tag, cls){ const e=document.createElement(tag); if(cls) e.className=cls; return e; }
  const params = new URLSearchParams(location.search);
  const id = params.get('id');
  if(!id){ alert('Thiếu id phòng khám'); location.href = 'index.php?page=search'; return; }

  async function fetchJSON(url){ const res = await fetch(url); if(!res.ok) throw new Error('Fetch error: '+url); return res.json(); }

  let CLINIC_DATA = null;

  function renderClinic(c){
    CLINIC_DATA = c || {};
    $('#clinic-name').textContent = c.name || 'Phòng khám';
    $('#clinic-address').textContent = c.address || c.description || '';
    $('#clinic-rating').textContent = (c.rating ?? c.score ?? '—').toString();
    if (String(c.is_verify) === '1' || String(c.is_verify).toLowerCase() === 'true') { $('#clinic-verified').style.display = ''; }
    var logo = c.logo || c.image || c.image_url || c.avatar || c.photo || '';
    if (logo) { $('#clinic-hero-img').src = logo; const top = document.getElementById('top-logo'); if (top) top.src = logo; }
    else { const top = document.getElementById('top-logo'); if (top) top.src = 'public/img/logo.png'; }
  }

  function groupBy(arr, key){ return arr.reduce((acc, item)=>{ const k = item[key] ?? 'khac'; (acc[k] = acc[k] || []).push(item); return acc; }, {}); }

      function renderServices(services, categories){
        const container = $('#service-groups'); container.innerHTML = '';
        const cats = Array.isArray(categories) && categories.length ? categories.slice() : [{id:'khac', name:'Dịch vụ'}];
        // Map services with null category to 'khac'
        const itemsByCat = services.reduce((acc, s)=>{
          const key = (s.category_service_id ?? 'khac').toString();
          (acc[key] = acc[key] || []).push(s);
          return acc;
        }, {});
        cats.forEach(cat => {
          const cid = (cat.id ?? 'khac').toString();
          const listItems = itemsByCat[cid] || [];
          const group = $el('div', 'service-group');
          const h3 = $el('h3'); h3.textContent = cat.name || 'Khác'; group.appendChild(h3);
          const list = $el('div', 'service-list');
          if (!listItems.length){
            const empty = $el('div', 'service-item');
            const name = $el('div', 's-name'); name.textContent = 'Chưa có dịch vụ';
            empty.appendChild(name); list.appendChild(empty);
          } else {
            listItems.forEach(s => {
              const row = $el('div', 'service-item');
              const name = $el('div', 's-name'); name.textContent = s.name || 'Dịch vụ';
              const price = $el('div', 's-price'); price.textContent = s.price ? Number(s.price).toLocaleString('vi-VN')+ ' đ' : '';
              const desc = $el('div', 's-desc'); desc.textContent = s.description || '';
              const action = $el('div');
              const btn = $el('button', 'btn-book'); btn.textContent = 'Đặt lịch';
              btn.addEventListener('click', (e)=>{
                e.stopPropagation();
                const params = new URLSearchParams({ center_id: String(id), service_id: String(s.id||''), service_name: s.name || '', price: String(s.price||''), center_name: $('#clinic-name').textContent || '', center_logo: (CLINIC_DATA && CLINIC_DATA.logo) ? CLINIC_DATA.logo : '' });
                location.href = 'index.php?page=booking?' + params.toString();
              });
              action.appendChild(btn);
              row.appendChild(name); row.appendChild(price); if (s.description) row.appendChild(desc); row.appendChild(action); list.appendChild(row);
            });
          }
          group.appendChild(list); container.appendChild(group);
        });
        const count = services.length; $('#clinic-reviews').textContent = count + ' đánh giá';
      }

  async function init(){
    try {
      const clinic = await fetchJSON('index.php?page=api.clinic&id='+encodeURIComponent(id));
      renderClinic(clinic || {});
      const [services, categories] = await Promise.all([
        fetchJSON('index.php?page=api.service&center_id='+encodeURIComponent(id)),
        fetchJSON('index.php?page=api.category_service')
      ]);
      renderServices(Array.isArray(services)?services:[], Array.isArray(categories)?categories:[]);
    } catch(err){ console.error(err); alert('Không tải được thông tin phòng khám'); }
  }

  document.addEventListener('DOMContentLoaded', init);
})();
