<header class="header">
  <div class="logo"><img src="logo.png" alt="Clinic Logo" height="48"></div>
  <div class="search-bar">
    <input type="text" placeholder="Tìm kiếm dịch vụ, phòng khám...">
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
    <button class="chip" data-service="khac">Khác</button>
  </div>
  <div class="chip-group" aria-label="Loại thú cưng">
    <button class="chip active" data-pet="all">Tất cả</button>
    <button class="chip" data-pet="cho">Chó</button>
    <button class="chip" data-pet="meo">Mèo</button>
  </div>
</section>

<section class="clinics">
  <h2>Kết quả tìm kiếm</h2>
  <div id="clinic-list"></div>
</section>

<script src="public/js/main.js"></script>
