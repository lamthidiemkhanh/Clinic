// appointments.js
(function(){
  function $(s, r=document){ return r.querySelector(s); }
  function $el(tag, cls){ const e=document.createElement(tag); if(cls) e.className=cls; return e; }
  function money(n){ try{ return Number(n||0).toLocaleString('vi-VN') + ' đ'; } catch(e){ return (n||0)+' đ'; } }

  function groupBy(arr, key){
    return arr.reduce((acc, item)=>{ const k=item[key]||'unknown'; (acc[k]=acc[k]||[]).push(item); return acc; },{});
  }

  function render(list){
    const wrap = $('#appt-list'); if(!wrap) return;
    wrap.innerHTML='';
    if (!list.length){ wrap.textContent = 'Chưa có lịch hẹn'; return; }
    const byCenter = groupBy(list,'center_name');
    Object.keys(byCenter).forEach(center => {
      const group = $el('div','appt-group');
      const head = $el('div','appt-group-head');
      head.innerHTML = `<i class="fas fa-hospital"></i> <strong>${center}</strong>`;
      group.appendChild(head);

      byCenter[center].forEach(item => {
        const card = $el('div','appt-card');
        const dt = `${item.time||''}, ${item.date||''}`;
        const status = item.status || 'Chờ xác nhận';
        const svc = item.service_name || '';
        const price = money(item.price);
        card.innerHTML = `
          <div class="row between"><div>${dt}</div><div class="status">${status}</div></div>
          <div class="svc-row">${svc}<span>${price}</span></div>
          <div class="row total"><span>Tổng tiền</span><span>${price}</span></div>
        `;
        group.appendChild(card);
      });

      wrap.appendChild(group);
    });
  }

  async function init(){
    try{
      const res = await fetch('appointment.php');
      const data = await res.json();
      render(Array.isArray(data)?data:[]);
    }catch(e){ $('#appt-list').textContent = 'Không tải được danh sách lịch hẹn'; }
  }

  document.addEventListener('DOMContentLoaded', init);
})();


