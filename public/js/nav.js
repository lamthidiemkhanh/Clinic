// nav.js - global footer navigation bindings
(function(){
  function go(url){ try { window.location.href = url; } catch(e){} }
  function getUnread(){ try { return parseInt(localStorage.getItem('notif_unread')||'0',10)||0; } catch(e){ return 0; } }
  function setUnread(n){ try { localStorage.setItem('notif_unread', String(Math.max(0,n|0))); } catch(e){} }
  function updateBadge(){
    try{
      var items = document.querySelectorAll('.footer-menu .footer-item');
      if (!items || !items[1]) return;
      var bell = items[1];
      var count = getUnread();
      var badge = bell.querySelector('.badge');
      if (!badge){ badge = document.createElement('span'); badge.className='badge'; bell.appendChild(badge); }
      badge.textContent = count>99? '99+' : String(count);
      badge.style.display = count>0 ? 'inline-flex' : 'none';
    }catch(e){}
  }
  // Public helpers to raise notifications when user reviews/comments
  function pushNotif(notif){
    try{
      var list = JSON.parse(localStorage.getItem('notifications')||'[]');
      notif.id = Date.now();
      notif.time = new Date().toISOString();
      list.unshift(notif);
      localStorage.setItem('notifications', JSON.stringify(list));
      setUnread(getUnread()+1);
      updateBadge();
    }catch(e){}
  }
  window.notifyNewReview = function(data){
    pushNotif({ type:'review', title:'Có đánh giá mới', icon:'star', payload:data||{} });
  };
  window.notifyNewComment = function(data){
    pushNotif({ type:'comment', title:'Có bình luận mới', icon:'comments', payload:data||{} });
  };
  window.notifyBookingSuccess = function(data){
    var payload = data || {};
    if (!payload.subject){
      payload.subject = payload.service_name || 'Dịch vụ';
    }
    if (!payload.text){
      var parts = [];
      if (payload.center_name) parts.push(payload.center_name);
      if (payload.time_label) {
        parts.push(payload.time_label);
      } else if (payload.time || payload.date) {
        parts.push([payload.time, payload.date].filter(Boolean).join(', '));
      }
      payload.text = 'Đặt lịch thành công' + (parts.length ? ' - ' + parts.join(' - ') : '');
    }
    pushNotif({ type:'booking', title:'Đặt lịch thành công', icon:'calendar-check', payload:payload });
  };
  document.addEventListener('DOMContentLoaded', function(){
    var items = document.querySelectorAll('.footer-menu .footer-item');
    if (!items || !items.length) return;
    // Route via MVC front controller when available
    var isAdmin = (document.body && (document.body.id === 'admin-page'));
    if (items[0]) items[0].addEventListener('click', function(){ go('index.php?page=' + (isAdmin ? 'admin' : 'home')); });
    if (items[1]) items[1].addEventListener('click', function(){ go('index.php?page=notifications'); });
    if (items[2]) items[2].addEventListener('click', function(){ go('index.php?page=appointments'); });
    if (items[3]) items[3].addEventListener('click', function(){ go('index.php?page=settings'); });
    // Show badge status
    updateBadge();
    // If on notifications page, clear unread
    if (document.body && (document.body.id === 'notifications-page')){
      setUnread(0); updateBadge();
    }
  });
})();
