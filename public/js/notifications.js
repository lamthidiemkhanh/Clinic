// notifications.js - render notifications from localStorage
(function(){
  function el(tag, cls){ var e=document.createElement(tag); if(cls) e.className=cls; return e; }
  function iconHtml(name){
    var map = { star:'fa-star', comments:'fa-comments', cut:'fa-cut', vial:'fa-vial', rss:'fa-rss' };
    return '<i class="fas '+(map[name]||'fa-bell')+'"></i>';
  }
  function renderItem(n){
    var card = el('article','notif-card info');
    if (n.type==='review') card.className = 'notif-card info';
    else if (n.type==='comment') card.className = 'notif-card info';
    else if (n.type==='booking') card.className = 'notif-card success';
    else if (n.type==='success') card.className = 'notif-card success';
    else if (n.type==='cancelled') card.className = 'notif-card cancelled';
    var head = el('div','notif-head'); head.textContent = n.title || 'Thông báo';
    var body = el('div','notif-body');
    var ic = el('div','notif-icon'); ic.innerHTML = iconHtml(n.icon||'bell');
    var content = el('div','notif-content');
    var title = el('div','notif-title'); title.textContent = (n.payload && (n.payload.subject || n.payload.user)) || 'Chi tiết';
    var sub = el('div','notif-sub');
    if (n.type==='review') sub.textContent = (n.payload.rating?`Đánh giá ${n.payload.rating}/5`:'Đánh giá mới') + (n.payload.clinic?` - ${n.payload.clinic}`:'');
    else if (n.type==='comment') sub.textContent = (n.payload.post?`Bài: ${n.payload.post}`:'Bình luận mới');
    else if (n.type==='booking') {
      var parts = [];
      if (n.payload && n.payload.center_name) parts.push(n.payload.center_name);
      var timeDetail = '';
      if (n.payload && n.payload.time_label) timeDetail = n.payload.time_label;
      else if (n.payload && (n.payload.time || n.payload.date)) {
        var rawTime = [];
        if (n.payload.time) rawTime.push(n.payload.time);
        if (n.payload.date) rawTime.push(n.payload.date);
        timeDetail = rawTime.join(', ');
      }
      if (timeDetail) parts.push(timeDetail);
      var fallback = 'Đặt lịch thành công';
      var text = (n.payload && n.payload.text) || parts.join(' - ') || fallback;
      sub.textContent = text;
    } else {
      sub.textContent = (n.payload && n.payload.text) || '';
    }
    content.appendChild(title); content.appendChild(sub);
    body.appendChild(ic); body.appendChild(content);
    card.appendChild(head); card.appendChild(body);
    card.addEventListener('click', function(){
      var msg = 'Chi tiết thông báo:\n' + JSON.stringify(n.payload||{}, null, 2);
      try{ alert(msg); }catch(e){}
    });
    return card;
  }
  function loadList(){
    try{ return JSON.parse(localStorage.getItem('notifications')||'[]'); }catch(e){ return []; }
  }
  function render(){
    var list = document.getElementById('notif-list'); if(!list) return;
    list.innerHTML='';
    var data = loadList();
    if (!data.length){
      var empty = el('div'); empty.textContent = 'Chưa có thông báo'; empty.style.color='#666'; empty.style.textAlign='center'; empty.style.padding='12px';
      list.appendChild(empty);
      return;
    }
    data.forEach(n=> list.appendChild(renderItem(n)));
  }
  document.addEventListener('DOMContentLoaded', render);
})();
