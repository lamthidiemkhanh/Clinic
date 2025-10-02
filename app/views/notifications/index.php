<div class="top-logo-bar"><img src="public/img/logo.png" alt="Clinic Logo"></div>
<main class="notifications-main">
  <section class="card">
    <div id="notif-list" class="notif-list">
      <?php if (!empty($serverNotifs)): ?>
        <?php foreach ($serverNotifs as $n): ?>
          <article class="notif-card <?= htmlspecialchars($n['type'] ?? 'info') ?>">
            <div class="notif-head"><?= htmlspecialchars($n['title'] ?? 'Thong bao') ?></div>
            <div class="notif-body">
              <div class="notif-icon"><i class="fas fa-<?= htmlspecialchars($n['icon'] ?? 'bell') ?>"></i></div>
              <div class="notif-content">
                <?php if (!empty($n['payload'])): ?>
                  <?php foreach ($n['payload'] as $key => $value): ?>
                    <div class="notif-payload" data-key="<?= htmlspecialchars((string)$key) ?>">
                      <?= htmlspecialchars((string)$value) ?>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="notif-sub">No extra data</div>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="notif-empty">No notifications yet</div>
      <?php endif; ?>
    </div>
  </section>
</main>
<script src="public/js/notifications.js"></script>
