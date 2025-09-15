// Copied from root booking-logo.js with API route
(function(){
  function $(s, r=document){ return r.querySelector(s); }
  async function init(){
    const params = new URLSearchParams(location.search);
    const centerId = params.get('center_id') || '';
    let logo = params.get('center_logo') || '';
    let nameFromParam = params.get('center_name') || '';

    try {
      if (!logo || !nameFromParam) {
        const url = 'index.php?page=api.clinic' + (centerId? ('&id='+encodeURIComponent(centerId)) : '');
        const res = await fetch(url);
        if (res.ok) {
          const data = await res.json();
          const fields = ['logo','image','image_url','avatar','photo'];
          for (const f of fields) { if (data && data[f]) { logo = logo || data[f]; break; } }
          if (!nameFromParam && data && data.name) nameFromParam = data.name;
        }
      }
    } catch(e) {}

    const logoEl = document.getElementById('sum-center-logo');
    if (logoEl) { if (!logo) logo = 'logo.png'; logoEl.src = logo; logoEl.style.display='inline-block'; }
    const topLogo = document.getElementById('booking-top-logo'); if (topLogo) { topLogo.src = logo || 'logo.png'; topLogo.style.display = 'inline-block'; }
    if (nameFromParam) { const nameEl = $('#sum-center'); if (nameEl) nameEl.textContent = nameFromParam; }
  }
  document.addEventListener('DOMContentLoaded', init);
})();

