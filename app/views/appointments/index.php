<div class="top-logo-bar"><img src="logo.png" alt="Clinic Logo"></div>
<main class="appointments-main">
  <section class="card">
    <h2>Lịch hẹn</h2>
    <div id="appt-list" class="appt-list">
      <?php if (!empty($appts)): ?>
        <?php 
          // group by center_name
          $groups = [];
          foreach ($appts as $a){ $groups[$a['center_name'] ?? 'Khác'][] = $a; }
        ?>
        <?php foreach ($groups as $center => $rows): ?>
          <div class="appt-group">
            <div class="appt-group-head"><i class="fas fa-hospital"></i> <strong><?= htmlspecialchars($center) ?></strong></div>
            <?php foreach ($rows as $item): ?>
              <div class="appt-card">
                <div class="row between"><div><?= htmlspecialchars(($item['time']??'') . ', ' . ($item['date']??'')) ?></div><div class="status"><?= htmlspecialchars($item['status'] ?? 'Chờ xác nhận') ?></div></div>
                <div class="svc-row"><?= htmlspecialchars($item['service_name'] ?? '') ?><span><?= number_format((float)($item['price'] ?? 0),0,',','.') ?> đ</span></div>
                <div class="row total"><span>Tổng tiền</span><span><?= number_format((float)($item['price'] ?? 0),0,',','.') ?> đ</span></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div>Chưa có lịch hẹn.</div>
      <?php endif; ?>
    </div>
  </section>
</main>
