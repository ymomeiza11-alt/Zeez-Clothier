<?php
include("common.php");
include("server/connection.php");

$success = '';
$error = '';
$orders = [];

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (!isset($_SESSION["logged_in"])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST["change_password"])) {
    $current_password = $_POST["current-password"];
    $new_password = $_POST["new-password"];
    $confirm_password = $_POST["confirm-password"];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill all fields";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords don't match";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        $stmt = $connection->prepare("SELECT user_password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($current_password, $user['user_password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $connection->prepare("UPDATE users SET user_password = ? WHERE user_id = ?");
                $update_stmt->bind_param("si", $hashed_password, $_SESSION["user_id"]);

                if ($update_stmt->execute()) {
                    $_SESSION['success'] = "Password changed successfully!";
                    header("Location: account.php");
                    exit();
                } else {
                    $error = "Failed to update password";
                }
            } else {
                $error = "Current password is incorrect";
            }
        } else {
            $error = "User not found";
        }
    }
}

$stmt = $connection->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$orders = $stmt->get_result();
?>

<?php include("layouts/header.php"); ?>

<section class="my-5 py-5">
    <div class="row container mx-auto">
        <div class="text-center mt-3 pt-5 col-lg-6 col-md-12 col-sm-12">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <h3 class="font-weight-bold">My Account</h3>
            <hr class="mx-auto">
            <div class="account-info">
                <p>Name: <span><?= htmlspecialchars($_SESSION["user_name"] ?? '') ?></span></p>
                <p>Email: <span><?= htmlspecialchars($_SESSION["user_email"] ?? '') ?></span></p>
                <p><a href="#orders" id="Order-btn">Your Orders</a></p>
                <p><a href="account.php?logout=1" id="Logout-btn">Logout</a></p>
            </div>
        </div>

        <div class="col-lg-6 col-md-12 col-sm-12">
            <form method="POST" action="account.php" id="account-form">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <h3 class="font-weight-bold">Change Password</h3>
                <hr class="mx-auto">
                <div class="form-group">
                    <label for="current-password">Current Password</label>
                    <input type="password" name="current-password" id="current-password" class="form-control"
                        placeholder="Enter current password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" name="new-password" id="new-password" class="form-control"
                        placeholder="Enter new password (min 8 characters)" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" name="confirm-password" id="confirm-password" class="form-control"
                        placeholder="Confirm new password" required minlength="8">
                </div>
                <div class="form-group">
                    <button type="submit" name="change_password" class="">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</section>

<section id="orders" class="orders container my-5 py-5">
    <div class="mt-2">
        <h2 class="font-weight-bold text-center">Your Orders</h2>
        <hr class="mx-auto">
    </div>

    <?php if ($orders->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table mt-5">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['order_id']) ?></td>
                            <td>₦<?= number_format($row['order_cost'], 2) ?></td>
                            <td><?= htmlspecialchars($row['order_status']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['order_date'])) ?></td>
                            <td>
                                <form action="order_details.php" method="POST">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['order_id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Details</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center">No orders found</p>
    <?php endif; ?>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        passwordInputs.forEach(input => {
            const toggle = document.createElement('span');
            toggle.innerHTML = ' <i class="bi bi-eye" style="cursor:pointer"></i>';
            toggle.addEventListener('click', function () {
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
            input.parentNode.appendChild(toggle);
        });

        const form = document.getElementById('account-form');
        form.addEventListener('submit', function (e) {
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match. Please try again.');
                return;
            }

            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return;
            }
        });
    });
</script>

<?php include("layouts/footer.php"); ?>