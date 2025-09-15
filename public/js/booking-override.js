// Copied from root booking-override.js
(function(){
  function fixSummary(){
    try {
      var params = new URLSearchParams(location.search);
      var logo = params.get('center_logo') || '';
      var logoEl = document.getElementById('sum-center-logo');
      if (logoEl) { if (logo) { logoEl.src = logo; logoEl.style.display='inline-block'; } else { logoEl.style.display='none'; } }
      var petText = document.getElementById('sum-pet'); if (petText && petText.textContent) { petText.textContent = petText.textContent.replace('kg','g'); }
      var sel = document.getElementById('pet-select'); if (sel && sel.options) { for (var i=0;i<sel.options.length;i++){ sel.options[i].text = sel.options[i].text.replace('kg','g'); } }
    } catch(e){}
  }
  document.addEventListener('DOMContentLoaded', function(){
    fixSummary();
    var tries = 0; var iv = setInterval(function(){ fixSummary(); if(++tries>30) clearInterval(iv); }, 200);
    var date = document.getElementById('bk-date'); if (date) date.addEventListener('change', fixSummary);
    document.body.addEventListener('click', function(e){ if (e.target.classList && e.target.classList.contains('tg-btn')) fixSummary(); });
    var sel = document.getElementById('pet-select'); if (sel) sel.addEventListener('change', fixSummary);
  });
})();

