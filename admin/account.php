<?php 
    include("header.php");

    if(!isset($_SESSION["admin_logged_in"])){
        header("location: login.php");
        exit();
    }
?>

<div class="container-fluid">
    <div class="row" style="min-height: 1000px;">
        <?php include('sidemenu.php'); ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="h2">Admin Account</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <!-- Your buttons here -->
                    </div>
                </div>
            </div>

            <div class="container">
                <p>ID: <?php echo $_SESSION["admin_id"]?></p>
                <p>Name: <?php echo $_SESSION["admin_name"]?></p>
                <p>Email: <?php echo $_SESSION["admin_email"]?></p>
            </div>
        </main>
    </div>
</div>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"
></script>
</body>
</html>