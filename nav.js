// nav.js - global footer navigation bindings
(function(){
  function go(url){ try { window.location.href = url; } catch(e){} }
  document.addEventListener('DOMContentLoaded', function(){
    var items = document.querySelectorAll('.footer-menu .footer-item');
    if (!items || !items.length) return;
    // Home
    if (items[0]) items[0].addEventListener('click', function(){ go('index.html'); });
    // Notifications
    if (items[1]) items[1].addEventListener('click', function(){ go('notifications.html'); });
    // Appointments
    if (items[2]) items[2].addEventListener('click', function(){ go('appointments.html'); });
    // Settings
    if (items[3]) items[3].addEventListener('click', function(){ go('settings.html'); });
  });
})();
