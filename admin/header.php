<?php
session_start();
include("../server/connection.php");
?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>

    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr"
      crossorigin="anonymous"
    />

    <!-- Font Awesome -->
    <script
      src="https://kit.fontawesome.com/c69f68d77b.js"
      crossorigin="anonymous"
    ></script>

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css" />
  </head>

  <body>
    <header class="navbar navbar-dark stickytop bg-dark flex-md-nowrap p-0 shadow">
      <a href="#" class="navbar-brand col-md-3 col-lg-2 me-0 px-3">Zeez Clothier</a>
      <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="navbar-nav">
        <div class="nav-item text-nowrap">
          <a href="logout.php?logout=1" class="nav-link px-3">Logout</a>
        </div>
      </div>
    </header>