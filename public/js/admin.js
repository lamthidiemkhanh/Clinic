// admin.js - behavior for admin dashboard
(function(){
  function go(url){ try{ window.location.href=url; }catch(e){} }
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.admin-tile').forEach(btn=>{
      btn.addEventListener('click', function(){
        const k = btn.getAttribute('data-key');
        // Hook up to real pages later; for now basic routing
        // 'appointments' tile was removed per request
        if (k==='clinics') return go('search.html');
        if (k==='customers') return alert('Đi tới quản lý Khách hàng (đang phát triển)');
        if (k==='schedules') return alert('Đi tới quản lý Lịch khám (đang phát triển)');
        if (k==='users') return alert('Đi tới quản lý User (đang phát triển)');
        if (k==='reviews') return alert('Đi tới quản lý Đánh giá (đang phát triển)');
        if (k==='comments') return alert('Đi tới quản lý Bình luận (đang phát triển)');
      });
    });
  });
})();

