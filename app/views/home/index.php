<div class="top-logo-bar index-hero"></div>
<div class="home-search">
  <form class="search-bar" action="index.php" method="get">
    <input type="hidden" name="page" value="search">
    <input type="text" name="q" placeholder="Tìm kiếm dịch vụ, phòng khám...">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</div>
<script src="public/js/main.js?v=9"></script>

<section class="services">
  <h2>Dịch vụ chính</h2>
  <div class="service-grid">
    <a class="service-item" href="index.php?page=search&service=spa"><i class="fas fa-spa"></i><span>Spa & Grooming</span></a>
    <a class="service-item" href="index.php?page=search&service=kham-benh"><i class="fas fa-stethoscope"></i><span>Khám bệnh</span></a>
    <a class="service-item" href="index.php?page=search&service=tiem-phong"><i class="fas fa-syringe"></i><span>Tiêm phòng</span></a>
    <a class="service-item" href="index.php?page=search&service=khach-san"><i class="fas fa-hotel"></i><span>Khách sạn</span></a>
    <a class="service-item" href="index.php?page=search&service=phau-thuat"><i class="fas fa-kit-medical"></i><span>Phẫu thuật</span></a>
    <a class="service-item" href="index.php?page=search&service=khac"><i class="fas fa-ellipsis-h"></i><span>Khác</span></a>
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
  <h2>Danh sách phòng khám</h2>
  <div id="clinic-list">
   
  </div>
</section>

