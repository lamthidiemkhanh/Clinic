<header class="header">
  <div class="logo"><img src="public/img/clinic-center.png" alt="Clinic Logo" height="48"></div>
  <div class="search-bar">
    <input type="text" placeholder="Tìm kiếm dịch vụ, phòng khám..." value="<?= htmlspecialchars($q ?? '') ?>">
    <button title="Tìm"><i class="fas fa-search"></i></button>
  </div>
</header>

<section class="filters sticky">
  <div class="chip-group" aria-label="Loại dịch vụ">
    <button class="chip active" data-service="all">Tất cả</button>
    <button class="chip" data-service="kham-benh">Khám bệnh</button>
    <button class="chip" data-service="tiem-phong">Tiêm phòng</button>
    <button class="chip" data-service="spa">Spa & Grooming</button>
    <button class="chip" data-service="khach-san">Khách sạn</button>
    <button class="chip" data-service="phau-thuat">Phẫu thuật</button>
    <button class="chip" data-service="khac">Khác</button>
  </div>
  <div class="chip-group" aria-label="Loại thú cưng">
    <button class="chip active" data-pet="all">Tất cả</button>
    <button class="chip" data-pet="cho">Chó</button>
    <button class="chip" data-pet="meo">Mèo</button>
    <button class="chip" data-pet="khac">Khác</button>
  </div>
</section>

<section class="clinics">
  <h2>Kết quả tìm kiếm</h2>
  <div id="clinic-list">
    <?php if (!empty($clinics)): ?>
      <?php foreach ($clinics as $c): ?>
        <div class="clinic-card">
          <div class="clinic-logo"><img src="public/img/clinic-center.png" alt="Logo" style="width:32px;height:32px;object-fit:contain;"></div>
          <div class="clinic-info">
            <div class="clinic-name"><?= htmlspecialchars($c['name'] ?? '') ?></div>
            <div class="clinic-address"><?= htmlspecialchars(($c['address'] ?? '') ?: ($c['description'] ?? '')) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<script src="public/js/main.js?v=9"></script>
<script>
  // Robust fetch override: use demo data if API is empty or fails
  window.fetchClinics = async function(){
    try{
      const res = await fetch('index.php?page=api.clinic');
      if(!res.ok) throw new Error('fetch_error');
      const data = await res.json();
      if (!Array.isArray(data) || data.length === 0) throw new Error('empty');
      window.ALL_CLINICS = data;
    }catch(e){
      window.ALL_CLINICS = [
        { id:1, name:'Benh vien Thu y Petcare', address:'Q10 - TP.HCM', description:'Kham benh; Tiem phong', service_category:'kham-benh tiem-phong', logo:'public/img/clinic-center.png', rating:4.7 },
        { id:2, name:'Benh vien Thu y Nong Lam', address:'TP. Thu Duc - TP.HCM', description:'Kham benh', service_category:'kham-benh', logo:'public/img/clinic-center.png', rating:4.6 },
        { id:3, name:'ThiThi Pet Clinic', address:'Q. Binh Thanh - TP.HCM', description:'Spa - Grooming', service_category:'spa', logo:'public/img/clinic-center.png', rating:4.5 }
      ];
    }
  }
</script>



