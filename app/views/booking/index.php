<div class="top-logo-bar"><img id="booking-top-logo" src="public/img/clinic-center.png" alt="Clinic Logo"></div>

<main class="booking-layout">
  <section class="card booking-time">
    <h2>Chọn thời gian thực hiện</h2>
    <div class="date-row">
      <label for="bk-date">Ngày:</label>
      <input type="date" id="bk-date">
    </div>
    <div class="time-groups">
      <div class="time-group">
        <div class="tg-title">Sáng</div>
        <div class="tg-grid" data-period="morning"></div>
      </div>
      <div class="time-group">
        <div class="tg-title">Chiều</div>
        <div class="tg-grid" data-period="afternoon"></div>
      </div>
      <div class="time-group">
        <div class="tg-title">Tối</div>
        <div class="tg-grid" data-period="evening"></div>
      </div>
    </div>
  </section>

  <section class="card booking-summary">
    <h2>Đặt lịch</h2>
    <div class="summary-block">
      <div class="s-label">Chọn thú cưng</div>
      <div class="s-value">
        <select id="pet-select"><option value="">-- Chọn thú cưng --</option></select>
      </div>
    </div>
    <div class="summary-block">
      <div class="s-label">Thời gian</div>
      <div class="s-value" id="sum-time">—</div>
    </div>
    <div class="summary-block">
      <div class="s-label">Phòng khám</div>
      <div class="s-value s-center">
        <img id="sum-center-logo" class="center-logo" alt="logo"/>
        <span id="sum-center">—</span>
      </div>
    </div>
    <div class="summary-block">
      <div class="s-label">Thú cưng</div>
      <div class="s-value" id="sum-pet">—</div>
    </div>
    <div class="summary-block">
      <div class="s-label">Dịch vụ</div>
      <div class="s-value" id="sum-service">—</div>
    </div>
    <div class="summary-block">
      <div class="s-label">Giá</div>
      <div class="s-value" id="sum-price">0 ₫</div>
    </div>
    <hr>
    <div class="summary-block total">
      <div class="s-label">Tổng cộng</div>
      <div class="s-value" id="sum-total">0 ₫</div>
    </div>
    <button id="btn-confirm" class="btn-primary" disabled>Xác nhận</button>
    <div id="bk-message" class="bk-message"></div>
  </section>
</main>

<script src="public/js/booking.js"></script>
<script src="public/js/booking-override.js"></script>
<script src="public/js/booking-logo.js"></script>
