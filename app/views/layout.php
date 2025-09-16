<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($title)? htmlspecialchars($title):'Clinic' ?></title>
  <link rel="stylesheet" href="public/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body id="<?= isset($pageId)? htmlspecialchars($pageId):'' ?>">
  <?= $content ?>

  <footer class="footer-nav">
    <div class="footer-menu">
      <div class="footer-item"><i class="fas fa-home"></i><span>Trang chủ</span></div>
      <div class="footer-item"><i class="fas fa-bell"></i><span>Thông báo</span></div>
      <div class="footer-item"><i class="fas fa-calendar-check"></i><span>Lịch hẹn</span></div>
      <div class="footer-item"><i class="fas fa-cogs"></i><span><?= ($pageId ?? '') === 'admin-page' ? 'Role' : 'Cài đặt' ?></span></div>
    </div>
  </footer>

  <script src="public/js/nav.js"></script>
</body>
</html>

