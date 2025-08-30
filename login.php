<?php
include("common.php");
include("server/connection.php");

if (isset($_SESSION["logged_in"])) {
    $redirect_url = "account.php";

    if (isset($_GET['redirect'])) {
        $redirect_url = $_GET['redirect'] . '.php';
    } elseif (isset($_SESSION['redirect_url'])) {
        $redirect_url = $_SESSION['redirect_url'];
        unset($_SESSION['redirect_url']);
    }

    header("Location: $redirect_url");
    exit();
}

if (isset($_GET['redirect'])) {
    $_SESSION['redirect_url'] = $_GET['redirect'] . '.php';
}

$error = '';

if (isset($_POST["login_btn"])) {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $error = "Please fill all fields";
    } else {
        $stmt = $connection->prepare("SELECT user_id, user_name, user_email, user_password FROM users WHERE user_email = ? LIMIT 1");
        if (!$stmt) {
            die("Prepare failed: " . $connection->error);
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['user_password'])) {
                $_SESSION["user_id"] = $user['user_id'];
                $_SESSION["user_name"] = $user['user_name'];
                $_SESSION["user_email"] = $user['user_email'];
                $_SESSION["logged_in"] = true;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                $redirect_url = "account.php";
                if (isset($_SESSION['redirect_url'])) {
                    $redirect_url = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                }

                header("Location: $redirect_url");
                exit();
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
        $stmt->close();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Zeez Clothier</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous" />

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/c69f68d77b.js" crossorigin="anonymous"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css" />
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
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="form-group mb-3">
                    <label for="login-email">Email Address</label>
                    <input type="email" class="form-control" id="login-email" name="email"
                        placeholder="Enter your email" required
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="login-password">Password</label>
                    <input type="password" class="form-control" id="login-password" name="password"
                        placeholder="Enter your password" required minlength="8">
                    <small class="form-text text-muted">Minimum 8 characters</small>
                </div>
                <div class="form-group mb-3">
                    <button type="submit" id="login-btn" name="login_btn" class="btn btn-primary w-100">Login</button>
                </div>
                <div class="form-group">
                    <p class="text-center">Don't have an account? <a href="signup.php" id="signup-link"
                            class="text-decoration-none">Sign Up</a></p>
                </div>
            </form>
        </div>
    </section>

    <?php include("layouts/footer.php"); ?>