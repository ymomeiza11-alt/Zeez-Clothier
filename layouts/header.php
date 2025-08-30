<?php
include('common.php');
$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
$httponly = true;
$samesite = 'Strict';

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$total_quantity = 0;
foreach ($_SESSION['cart'] as $item) {
  $total_quantity += $item['product_quantity'] ?? 0;
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: "
  . "default-src 'self'; "
  . "script-src 'self' 'unsafe-inline' https://js.paystack.co https://cdn.jsdelivr.net https://kit.fontawesome.com; "
  . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://paystack.com; "
  . "font-src 'self' https://cdn.jsdelivr.net https://kit.fontawesome.com https://fonts.gstatic.com; "
  . "frame-src https://checkout.paystack.com; "
  . "img-src 'self' data: https://paystack.com;");
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Zeez Clothier - Premium Fashion Brand">
  <title>Zeez Clothier</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/c69f68d77b.js" crossorigin="anonymous"></script>

  <!-- CSS -->
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>
  <!-- navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 fixed-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">Zeez Clothier</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse nav-buttons" id="navbarSupportedContent">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Home</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="shop.php">Shop</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="about.php">About</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="contact.php">Contact</a>
          </li>

          <li class="nav-item">
            <a href="<?php echo isset($_SESSION['logged_in']) ? 'cart.php' : 'login.php?redirect=cart'; ?>"
              class="nav-link" aria-label="Shopping Cart">
              <i class="bi bi-bag position-relative">
                <?php if ($total_quantity > 0): ?>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $total_quantity ?>
                  </span>
                <?php endif; ?>
              </i>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?php echo isset($_SESSION['logged_in']) ? 'account.php' : 'login.php?redirect=account'; ?>"
              class="nav-link" aria-label="Account">
              <i class="i-resize bi bi-person"></i>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- end navbar -->