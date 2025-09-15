// Copied from root booking.js with API routes adjusted
(function(){
  function $(s, r=document){ return r.querySelector(s); }
  function $all(s, r=document){ return Array.from(r.querySelectorAll(s)); }
  const params = new URLSearchParams(location.search);
  const centerId = params.get('center_id') || '';
  const serviceId = params.get('service_id') || '';
  const serviceName = params.get('service_name') || '';
  const price = Number(params.get('price') || 0);
  const centerName = params.get('center_name') || '';

  let selectedDate = '';
  let selectedTime = '';
  let pet = null; // selected pet

  function money(n){ try { return Number(n||0).toLocaleString('vi-VN') + ' đ'; } catch(e){ return (n||0)+' đ'; } }

  function renderSummary(){
    $('#sum-center').textContent = centerName || ('Phòng khám #' + centerId);
    $('#sum-service').textContent = serviceName || ('Dịch vụ #' + serviceId);
    $('#sum-price').textContent = money(price);
    $('#sum-total').textContent = money(price);
    $('#sum-pet').textContent = pet ? `${pet.name} (${pet.weight||'?'}kg)` : 'Chưa chọn';
    $('#sum-time').textContent = (selectedTime && selectedDate) ? `${selectedTime}, ${selectedDate}` : 'Chưa chọn';
    const btn = $('#btn-confirm'); if (btn) btn.disabled = !(selectedTime && selectedDate);
  }

  function timeSlots(){
    return { morning: ['06:00','07:00','08:00','09:00','10:00','11:00'], afternoon: ['12:00','13:00','14:00','15:00','16:00','17:00'], evening: ['18:00','19:00','20:00'] };
  }

  function renderTimeGrids(){
    const slots = timeSlots();
    Object.keys(slots).forEach(period => {
      const grid = document.querySelector(`.tg-grid[data-period="${period}"]`);
      if (!grid) return;
      grid.innerHTML = '';
      slots[period].forEach(t => {
        const btn = document.createElement('button');
        btn.className = 'tg-btn';
        btn.textContent = t;
        btn.addEventListener('click', ()=>{
          $all('.tg-btn').forEach(b=>b.classList.remove('active'));
          btn.classList.add('active');
          selectedTime = t;
          renderSummary();
        });
        grid.appendChild(btn);
      });
    });
  }

  async function submitBooking(){
    const payload = {
      center_id: centerId,
      service_id: serviceId,
      date: selectedDate,
      time: selectedTime,
      price: price,
      service_name: serviceName,
      center_name: centerName,
      pet_id: pet ? pet.id : '',
      email: $('#bk-email')?.value || ''
    };
    try{
      const res = await fetch('index.php?page=api.appointments', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const data = await res.json();
      $('#bk-message').textContent = data.message || 'Đặt lịch thành công';
      $('#bk-message').className = 'bk-message success';
      alert('Đặt lịch thành công');
    }catch(e){
      $('#bk-message').textContent = 'Có lỗi khi đặt lịch';
      $('#bk-message').className = 'bk-message error';
    }
  }

  function init(){
    const d = new Date(); const yyyy = d.getFullYear(); const mm = String(d.getMonth()+1).padStart(2,'0'); const dd = String(d.getDate()).padStart(2,'0');
    selectedDate = `${yyyy}-${mm}-${dd}`;
    const dateInput = $('#bk-date'); if (dateInput){ dateInput.value = selectedDate; dateInput.addEventListener('change', ()=>{ selectedDate = dateInput.value; renderSummary(); }); }
    renderTimeGrids(); renderSummary();
    const btn = $('#btn-confirm'); if (btn) btn.addEventListener('click', submitBooking);
    const petSelect = document.getElementById('pet-select');
    if (petSelect) {
      fetch('index.php?page=api.pet').then(r=>r.json()).then(pets=>{
        (pets||[]).forEach(p=>{ const opt = document.createElement('option'); opt.value = p.id; opt.textContent = `${p.name} (${p.weight||'?'}kg)`; petSelect.appendChild(opt); });
        petSelect.addEventListener('change', ()=>{ const id = petSelect.value; pet = (pets||[]).find(x=> String(x.id)===String(id)) || null; renderSummary(); });
      }).catch(()=>{});
    }
  }

  document.addEventListener('DOMContentLoaded', init);
})();

// Keep overrides from original booking-override.js
(function(){
  function $(s, r=document){ return r.querySelector(s); }
  if (typeof window !== 'undefined') {
    window.renderSummary = function renderSummary(){
      var params = new URLSearchParams(location.search);
      var centerLogo = params.get('center_logo') || '';
      var centerNameEl = $('#sum-center');
      if (centerNameEl) centerNameEl.textContent = centerName || ('Phòng khám #' + centerId);
      var logoEl = document.getElementById('sum-center-logo');
      if (logoEl) { if (centerLogo) { logoEl.src = centerLogo; logoEl.style.display = 'inline-block'; } else { logoEl.style.display = 'none'; } }
      var svcEl = $('#sum-service'); if (svcEl) svcEl.textContent = serviceName || ('Dịch vụ #' + serviceId);
      var priceEl = $('#sum-price'); if (priceEl) priceEl.textContent = money(price);
      var totalEl = $('#sum-total'); if (totalEl) totalEl.textContent = money(price);
      var petEl = $('#sum-pet'); if (petEl) petEl.textContent = pet ? (pet.name + ' (' + (pet.weight||'?') + 'g)') : 'Chưa chọn';
      var timeEl = $('#sum-time'); if (timeEl) timeEl.textContent = (selectedTime && selectedDate) ? (selectedTime + ', ' + selectedDate) : 'Chưa chọn';
      var btn = document.getElementById('btn-confirm'); if (btn) btn.disabled = !(selectedTime && selectedDate);
    };
    document.addEventListener('DOMContentLoaded', function(){
      var tries = 0; var iv = setInterval(function(){ var sel = document.getElementById('pet-select'); if (sel && sel.options && sel.options.length) { for (var i=0;i<sel.options.length;i++){ sel.options[i].text = sel.options[i].text.replace('kg','g'); } clearInterval(iv); } if (++tries > 30) clearInterval(iv); }, 200);
    });
  }
})();
