<?php
include('header.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

if (!$order_id) {
    header("Location: dashboard.php");
    exit();
}

try {
    $stmt = $connection->prepare("
        SELECT m.*, GROUP_CONCAT(DISTINCT p.product_category) AS categories 
        FROM measurements m
        JOIN order_items oi ON m.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE m.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $measurement = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$measurement) {
        throw new Exception("Measurements not found for this order");
    }

    $is_agbada = (stripos($measurement['categories'], 'Agbada') !== false);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="row" style="min-height: 1000px;">
        <?php include('sidemenu.php'); ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="h2">Measurements for Order #<?= $order_id ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                        Back to Orders
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php else: ?>
                <!-- Product Categories -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Product Categories</h5>
                    </div>
                    <div class="card-body">
                        <p><?= htmlspecialchars($measurement['categories']) ?></p>
                    </div>
                </div>

                <!-- Basic Measurements -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Basic Measurements (cm)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Neck:</strong> <?= $measurement['neck'] ?></p>
                                <p><strong>Lap:</strong> <?= $measurement['lap'] ?></p>
                                <p><strong>Head Circumference:</strong> <?= $measurement['round_head'] ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Body Length:</strong> <?= $measurement['length'] ?></p>
                                <p><strong>Trouser Length:</strong> <?= $measurement['trouser_length'] ?></p>
                                <p><strong>Shoulder Width:</strong> <?= $measurement['shoulder'] ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Arm Length:</strong> <?= $measurement['hand'] ?></p>
                                <p><strong>Bust/Chest:</strong> <?= $measurement['shape_bust'] ?></p>
                                <p><strong>Production Time:</strong> <?= $measurement['time_span'] ?> weeks</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agbada Measurements (if applicable) -->
                <?php if ($is_agbada && $measurement['agbada_full_length']): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Agbada Measurements (cm)</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Full Length:</strong> <?= $measurement['agbada_full_length'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Full Width:</strong> <?= $measurement['agbada_full_width'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include('footer.php'); ?>