<?php $keyword = htmlspecialchars($pagination['keyword'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
<div class="top-logo-bar index-hero"></div>
<div class="home-search">
  <form class="search-bar" action="index.php" method="get">
    <input type="hidden" name="page" value="search">
    <input type="text" name="q" placeholder="Tim kiem dich vu, phong kham..." value="<?= $keyword ?>">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</div>
<script src="public/js/main.js?v=10"></script>

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

<section class="clinics">
  <h2>Danh sách phòng khám</h2>
  <div id="clinic-list" data-server="1">
    <?php if (!empty($clinics)): ?>
      <?php foreach ($clinics as $clinic): ?>
        <div class="clinic-card" aria-label="<?= htmlspecialchars($clinic['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div class="clinic-logo">
            <img src="<?= htmlspecialchars($clinic['logo'] ?? $clinic['image'] ?? $clinic['image_url'] ?? 'public/img/clinic-center.png', ENT_QUOTES, 'UTF-8') ?>" alt="Logo" style="width:32px;height:32px;object-fit:contain;">
          </div>
          <div class="clinic-info">
            <div class="clinic-name"><a href="index.php?page=clinic-detail&id=<?= urlencode($clinic['id']) ?>"><?= htmlspecialchars($clinic['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></a></div>
            <div class="clinic-address"><?= htmlspecialchars($clinic['address'] ?? $clinic['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($clinic['services'])): ?>
              <div class="clinic-meta">Dịch vụ: <?= htmlspecialchars($clinic['services'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (!empty($clinic['pets'])): ?>
              <div class="clinic-meta">Thú cưng: <?= htmlspecialchars($clinic['pets'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Không tìm thấy phòng khám phù hợp.</p>
    <?php endif; ?>
  </div>

  <?php
    $page = $pagination['page'] ?? 1;
    $pages = $pagination['pages'] ?? 1;
    $perPage = $pagination['perPage'] ?? 6;
    if ($pages < 1) { $pages = 1; }
    $queryBase = [
      'page' => 'home',
      'per_page' => $perPage,
    ];
    if ($keyword !== '') {
      $queryBase['q'] = $keyword;
    }
  ?>

  <?php if ($pages > 1): ?>
    <nav class="pagination">
      <?php if ($page > 1): ?>
        <?php $prev = http_build_query($queryBase + ['p' => $page - 1]); ?>
        <a class="page-link" href="index.php?<?= $prev ?>">« Trước</a>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php $qs = http_build_query($queryBase + ['p' => $i]); ?>
        <a class="page-link<?= $i === $page ? ' active' : '' ?>" href="index.php?<?= $qs ?>"><?= $i ?></a>
      <?php endfor; ?>

      <?php if ($page < $pages): ?>
        <?php $next = http_build_query($queryBase + ['p' => $page + 1]); ?>
        <a class="page-link" href="index.php?<?= $next ?>">Sau »</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</section>
