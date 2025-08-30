<?php
$isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1');

$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

$cookieParams = [
    'lifetime' => 86400,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict'
];

if (!$isLocalhost) {
    $cookieParams['domain'] = $_SERVER['HTTP_HOST'];
}

session_set_cookie_params($cookieParams);
session_start();

include("server/connection.php");

$redirect_url = "index.php";
if (isset($_SESSION['redirect_url'])) {
    $redirect_url = $_SESSION['redirect_url'];
    unset($_SESSION['redirect_url']);
}

if (isset($_SESSION["logged_in"])) {
    header("Location: $redirect_url");
    exit();
}

$error = '';

if (isset($_POST["signup"])) {
    $name = htmlspecialchars($_POST["name"], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm-password"];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        $stmt = $connection->prepare("SELECT user_id FROM users WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already exists";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $insert_stmt = $connection->prepare("INSERT INTO users (user_name, user_email, user_password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $name, $email, $password_hash);

            if ($insert_stmt->execute()) {
                $user_id = $insert_stmt->insert_id;

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user_id;
                $_SESSION["user_email"] = $email;
                $_SESSION["user_name"] = $name;
                $_SESSION["logged_in"] = true;

                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header("Location: $redirect_url");
                exit();
            } else {
                $error = "Registration failed. Please try again";
            }
            $insert_stmt->close();
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
    <title>Sign Up - Zeez Clothier</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous" />

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/c69f68d77b.js" crossorigin="anonymous"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>
    <!-- signup -->
    <section class="my-5 py-5">
        <div class="container text-center mt-3 pt-5">
            <h2 class="form-weight-bold">Sign Up</h2>
            <hr class="mx-auto">
        </div>
        <div class="mx-auto container">
            <form id="signup-form" method="POST" action="signup.php">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="form-group mb-3">
                    <label for="signup-name">Name</label>
                    <input type="text" class="form-control" id="signup-name" name="name" placeholder="Enter your name"
                        required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="signup-email">Email Address</label>
                    <input type="email" class="form-control" id="signup-email" name="email"
                        placeholder="Enter your email" required
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="signup-password">Password</label>
                    <input type="password" class="form-control" id="signup-password" name="password"
                        placeholder="Set password (min 8 characters)" required minlength="8">
                    <small class="form-text text-muted">Minimum 8 characters</small>
                </div>
                <div class="form-group mb-3">
                    <label for="signup-confirm-password">Confirm Password</label>
                    <input type="password" class="form-control" id="signup-confirm-password" name="confirm-password"
                        placeholder="Confirm password" required minlength="8">
                </div>
                <div class="form-group mb-3">
                    <button type="submit" id="signup-btn" name="signup" class="btn btn-primary w-100">Sign Up</button>
                </div>
                <div class="form-group">
                    <p class="text-center">Already have an account? <a href="login.php" id="login-link"
                            class="text-decoration-none">Log In</a></p>
                </div>
            </form>
        </div>
    </section>

    <?php include("layouts/footer.php"); ?>