<?php
session_start(); 
include("../server/connection.php");
if(isset($_SESSION["admin_logged_in"])){
  header("Location: dashboard.php");
  exit();
}

if(isset($_POST["login_btn"])){
  $email = $_POST["email"];
  $password = md5($_POST["password"]);

  $stmt = $connection->prepare("SELECT admin_id, admin_name, admin_email, admin_password FROM admins WHERE admin_email = ? AND admin_password = ? LIMIT 1");
  $stmt->bind_param("ss", $email, $password);
  if($stmt->execute() ){
    $stmt->bind_result($admin_id, $admin_name, $admin_email, $admin_password);
    $stmt->store_result();
    if($stmt->num_rows == 1){
      $stmt->fetch();

      $_SESSION["admin_id"] = $admin_id;
      $_SESSION["admin_name"] = $admin_name;
      $_SESSION["admin_email"] = $admin_email;
      $_SESSION["admin_logged_in"] = true;

      header("Location: dashboard.php?login=You have logged in successfully");
    }else {
      header("Location: login.php?error=Invalid email or password");
    } 
  } else {
    header("Location: login.php?error=Something went wrong, please try again");
  }
}
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
    <!-- login -->
     <section class="my-5 py-5">
        <div class="container text-center mt-3 pt-5">
            <h2 class="form-weight-bold">Login</h2>
            <hr class="mx-auto">
        </div>
        <div class="mx-auto container">
            <form id="login-form" action="login.php" method="POST">
              <p style="color: red;">
                <?php
                if(isset($_GET["error"])){
                  echo $_GET["error"];
                }
                ?>
              </p>
                <div class="form-group">
                    <label for="">Email Address</label>
                    <input type="text" class="form-control" id="login-email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="">Password</label>
                    <input type="password" class="form-control" id="login-password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <button type="submit" id="login-btn" value="Login" name="login_btn" class="btn">Login</button>
                </div>
            </form>
        </div>
     </section>
    <!-- end of login -->


 