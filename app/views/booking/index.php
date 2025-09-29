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
      <div class="s-label">Chọn loài thú</div>
      <div class="s-value">
        <select id="pet-type">
          <option value="">-- Chọn loài --</option>
          <option value="dog">Chó</option>
          <option value="cat">Mèo</option>
          <option value="other">Khác</option>
        </select>
      </div>
    </div>
    <div class="summary-block">
      <div class="s-label">Tên chủ sở hữu</div>
      <div class="s-value">
        <input type="text" id="owner-name" placeholder="Nhập tên chủ sở hữu">
      </div>
    </div>
    <div class="summary-block">
      <div class="s-label">Tên thú cưng</div>
      <div class="s-value">
        <input type="text" id="pet-name" placeholder="Nhập tên thú cưng">
      </div>
    </div>
    <div class="summary-block">
      <div class="s-label">Màu sắc</div>
      <div class="s-value">
        <input type="text" id="pet-color" placeholder="Ví dụ: Trắng đen">
      </div>
    </div>
    <div class="summary-block">
      <div class="s-label">Trọng lượng (g)</div>
      <div class="s-value">
        <input type="number" id="pet-weight" min="0" step="1" placeholder="Ví dụ: 600">
      </div>
    </div>
    <div class="summary-block">
      <div class="s-label">Ngày sinh</div>
      <div class="s-value">
        <input type="date" id="pet-birth">
      </div>
    </div>

    <div class="summary-block">
      <div class="s-label">Thời gian</div>
      <div class="s-value" id="sum-time">Chưa chọn</div>
    </div>
    <div class="summary-block">
      <div class="s-label">Phòng khám</div>
      <div class="s-value s-center">
        <img id="sum-center-logo" class="center-logo" alt="logo"/>
        <span id="sum-center">Phòng khám</span>
      </div>
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
<script src="public/js/booking-logo.js"></script>
