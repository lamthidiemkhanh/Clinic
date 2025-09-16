<div class="top-logo-bar"><img src="public/img/logo.png" alt="Clinic Logo"></div>
<main class="notifications-main">
  <section class="card">
    <div id="notif-list" class="notif-list">
      <?php if (!empty($serverNotifs)): ?>
        <?php foreach ($serverNotifs as $n): ?>
          <article class="notif-card info">
            <div class="notif-head"><?= htmlspecialchars($n['title'] ?? 'Thông báo') ?></div>
            <div class="notif-body">
              <div class="notif-icon"><i class="fas fa-<?= htmlspecialchars($n['icon'] ?? 'bell') ?>"></i></div>
              <div class="notif-content">
                <div class="notif-title">Chi tiết</div>
                <div class="notif-sub">Server</div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</main>
<script src="public/js/notifications.js"></script>
