<div class="top-logo-bar"><img src="public/img/clinic-center.png?v=3" alt="Clinic Logo"></div>
<div class="home-search">
  <div class="search-bar">
    <input type="text" placeholder="Tìm kiếm dịch vụ, phòng khám...">
    <button><i class="fas fa-search"></i></button>
  </div>
  </div>
<script src="public/js/main.js?v=3"></script>

<section class="services">
  <h2>Dịch vụ chính</h2>
  <div class="service-grid">
    <div class="service-item"><i class="fas fa-spa"></i><span>Spa & Grooming</span></div>
    <div class="service-item"><i class="fas fa-stethoscope"></i><span>Khám bệnh</span></div>
    <div class="service-item"><i class="fas fa-syringe"></i><span>Tiêm phòng</span></div>
    <div class="service-item"><i class="fas fa-hotel"></i><span>Khách sạn</span></div>
    <div class="service-item"><i class="fas fa-kit-medical"></i><span>Phẫu thuật</span></div>
    <div class="service-item"><i class="fas fa-ellipsis-h"></i><span>Khác</span></div>
  </div>
</section>

<section class="filters">
  <div class="chip-group" aria-label="Loại dịch vụ">
    <button class="chip active" data-service="all">Tất cả</button>
    <button class="chip" data-service="kham-benh">Khám bệnh</button>
    <button class="chip" data-service="tiem-phong">Tiêm phòng</button>
    <button class="chip" data-service="spa">Spa & Grooming</button>
    <button class="chip" data-service="khach-san">Khách sạn</button>
    <button class="chip" data-service="khac">Khác</button>
  </div>
  <div class="chip-group" aria-label="Loại thú cưng">
    <button class="chip active" data-pet="all">Tất cả</button>
    <button class="chip" data-pet="cho">Chó</button>
    <button class="chip" data-pet="meo">Mèo</button>
  </div>
</section>

<section class="clinics">
  <h2>Phòng khám và dịch vụ gần bạn</h2>
  <div id="clinic-list">
    <?php if (!empty($clinics)): ?>
      <?php foreach ($clinics as $c): ?>
        <div class="clinic-card">
          <div class="clinic-logo"><?= htmlspecialchars(first_char($c['name'] ?? 'C')) ?></div>
          <div class="clinic-info">
            <div class="clinic-name"><?= htmlspecialchars($c['name'] ?? '') ?></div>
            <div class="clinic-desc"><?= htmlspecialchars($c['description'] ?? ($c['address'] ?? '')) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="clinic-desc">Chưa có dữ liệu phòng khám.</div>
    <?php endif; ?>
  </div>
  </section>
