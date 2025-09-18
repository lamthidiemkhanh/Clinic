<main class="detail-main">
  <div class="clinic-hero">
    <img id="clinic-hero-img" src="public/img/logo.png" alt="Ảnh phòng khám">
  </div>

  <section class="clinic-summary card">
    <div class="summary-left">
      <div class="clinic-title" id="clinic-name">Tên phòng khám</div>
      <div class="clinic-address" id="clinic-address">Địa chỉ</div>
      <div class="summary-stats">
        <div class="stat"><i class="fas fa-star"></i><span id="clinic-rating">—</span></div>
        <div class="stat" id="clinic-verified" title="Đã xác thực" style="display:none"><i class="fas fa-check"></i><span>Đã xác thực</span></div>
        <div class="stat"><i class="fas fa-comment"></i><span id="clinic-reviews">0 đánh giá</span></div>
      </div>
    </div>
  </section>

  <section class="clinic-services card">
    <h2>Dịch vụ</h2>
    <div id="service-groups"></div>
  </section>
</main>

<script src="public/js/clinic-detail.js?v=2"></script>
