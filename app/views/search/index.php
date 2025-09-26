<?php
  $q = $q ?? '';
  $service = $service ?? 'all';
  $pet = $pet ?? 'all';
  $serviceOptions = $serviceOptions ?? [];
  $petOptions = $petOptions ?? [];
  $resultsCount = $resultsCount ?? 0;
  $buildUrl = function(array $overrides = []) use ($q, $service, $pet) {
      $params = ['page' => 'search'];
      $params['service'] = $overrides['service'] ?? $service;
      $params['pet'] = $overrides['pet'] ?? $pet;
      if ($q !== '') {
          $params['q'] = $q;
      }
      return 'index.php?' . http_build_query($params);
  };
?>
<header class="header">
  <div class="logo"><img src="public/img/clinic-center.png" alt="Clinic Logo" height="48"></div>
  <form class="search-bar" method="get" action="index.php">
    <input type="hidden" name="page" value="search">
    <input type="hidden" name="service" value="<?= htmlspecialchars($service, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="pet" value="<?= htmlspecialchars($pet, ENT_QUOTES, 'UTF-8') ?>">
    <input type="text" name="q" placeholder="Tim kiem dich vu, phong kham..." value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" title="Tim"><i class="fas fa-search"></i></button>
  </form>
</header>

<section class="filters sticky">
  <div class="chip-group" aria-label="Loai dich vu">
    <?php foreach ($serviceOptions as $key => $label): ?>
      <a class="chip<?= $service === $key ? ' active' : '' ?>" href="<?= htmlspecialchars($buildUrl(['service' => $key]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
    <?php endforeach; ?>
  </div>
  <div class="chip-group" aria-label="Loai thu cung">
    <?php foreach ($petOptions as $key => $label): ?>
      <a class="chip<?= $pet === $key ? ' active' : '' ?>" href="<?= htmlspecialchars($buildUrl(['pet' => $key]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
    <?php endforeach; ?>
  </div>
</section>

<section class="clinics" id="clinic-search-page" data-server="1">
  <div class="search-summary">
    <h2>Ket qua tim kiem</h2>
    <p><?= (int)$resultsCount ?> phong kham</p>
  </div>
  <div id="clinic-list" data-server="1">
    <?php if (!empty($clinics)): ?>
      <?php foreach ($clinics as $clinic): ?>
        <div class="clinic-card" aria-label="<?= htmlspecialchars($clinic['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div class="clinic-logo">
            <img src="<?= htmlspecialchars($clinic['logo'] ?? $clinic['image'] ?? $clinic['image_url'] ?? 'public/img/clinic-center.png', ENT_QUOTES, 'UTF-8') ?>" alt="Logo" style="width:32px;height:32px;object-fit:contain;">
          </div>
          <div class="clinic-info">
            <div class="clinic-name"><a href="index.php?page=clinic-detail&id=<?= urlencode((string)($clinic['id'] ?? '')) ?>"><?= htmlspecialchars($clinic['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></a></div>
            <div class="clinic-address"><?= htmlspecialchars($clinic['address'] ?? $clinic['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($clinic['service_categories'])): ?>
              <div class="clinic-meta">Danh muc: <?= htmlspecialchars($clinic['service_categories'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (!empty($clinic['services'])): ?>
              <div class="clinic-meta">Dich vu: <?= htmlspecialchars($clinic['services'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (!empty($clinic['pets'])): ?>
              <div class="clinic-meta">Thu cung: <?= htmlspecialchars($clinic['pets'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="empty-state">Khong tim thay phong kham phu hop.</p>
    <?php endif; ?>
  </div>
</section>

<script src='public/js/main.js?v=10'></script>
