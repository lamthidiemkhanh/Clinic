<header class="header">
  <div class="logo"><img src="public/img/clinic-center.png" alt="Clinic Logo" height="48"></div>
  <div class="search-bar">
    <input type="text" placeholder="Tiềm kiếm danh sách phòng khám...">
    <button title="TÃ¬m"><i class="fas fa-search"></i></button>
  </div>
</header>

<section class="filters sticky">
  <div class="chip-group" aria-label="Loáº¡i dá»‹ch vá»¥">
    <button class="chip active" data-service="all">Tất cả</button>
    <button class="chip" data-service="kham-benh">Khám bệnh</button>
    <button class="chip" data-service="tiem-phong">Tiêm phòng</button>
    <button class="chip" data-service="spa">Spa & Grooming</button>
    <button class="chip" data-service="khach-san">Khách sạn</button>
    <button class="chip" data-service="khac">KhÃ¡c</button>
  </div>
  <div class="chip-group" aria-label="Loáº¡i thÃº cÆ°ng">
    <button class="chip active" data-pet="all">Tất cả</button>
    <button class="chip" data-pet="cho">Chó</button>
    <button class="chip" data-pet="meo">Mèo</button>
  </div>
</section>

<section class="clinics">
  <h2>Káº¿t quáº£ tÃ¬m kiáº¿m</h2>
  <div id="clinic-list"></div>
</section>

<script src="public/js/main.js"></script>
