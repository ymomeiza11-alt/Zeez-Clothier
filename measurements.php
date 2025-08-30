<?php
session_start();
require_once('server/connection.php');

if (!isset($_SESSION['shipping_details'])) {
    header("Location: checkout.php");
    exit();
}

$has_agbada = false;
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));

    $stmt = $connection->prepare("SELECT COUNT(*) AS agbada_count FROM products 
                                 WHERE product_id IN ($placeholders) 
                                 AND product_category = 'Agbada'");
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $has_agbada = ($result['agbada_count'] > 0);
    $stmt->close();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $measurements = [
        'neck' => filter_input(INPUT_POST, 'neck', FILTER_VALIDATE_FLOAT),
        'lap' => filter_input(INPUT_POST, 'lap', FILTER_VALIDATE_FLOAT),
        'round_head' => filter_input(INPUT_POST, 'round_head', FILTER_VALIDATE_FLOAT),
        'length' => filter_input(INPUT_POST, 'length', FILTER_VALIDATE_FLOAT),
        'trouser_length' => filter_input(INPUT_POST, 'trouser_length', FILTER_VALIDATE_FLOAT),
        'shoulder' => filter_input(INPUT_POST, 'shoulder', FILTER_VALIDATE_FLOAT),
        'hand' => filter_input(INPUT_POST, 'hand', FILTER_VALIDATE_FLOAT),
        'shape_bust' => filter_input(INPUT_POST, 'shape_bust', FILTER_VALIDATE_FLOAT),
        'time_span' => filter_input(INPUT_POST, 'time_span', FILTER_VALIDATE_INT)
    ];

    if ($has_agbada) {
        $measurements['agbada_full_length'] = filter_input(INPUT_POST, 'agbada_full_length', FILTER_VALIDATE_FLOAT);
        $measurements['agbada_full_width'] = filter_input(INPUT_POST, 'agbada_full_width', FILTER_VALIDATE_FLOAT);
    }

    $valid = true;
    foreach ($measurements as $key => $value) {
        if ($value === false || $value === null || ($key !== 'agbada_full_length' && $key !== 'agbada_full_width' && $value <= 0)) {
            $valid = false;
            break;
        }
    }

    if (!$valid || $measurements['time_span'] < 2) {
        $error = "Please provide valid measurements. Time span must be at least 2 weeks.";
    } else {
        $_SESSION['measurements'] = $measurements;
        header("Location: order_summary.php");
        exit();
    }
}
?>

<?php include("layouts/header.php"); ?>

<!-- Measurements Form -->
<section class="my-5 py-5">
    <div class="container text-center mt-3 pt-5">
        <h2 class="font-weight-bold">Body Measurements</h2>
        <hr class="mx-auto">
    </div>
    <div class="mx-auto container">
        <form method="POST" action="measurements.php">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Required Measurements (in cm)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="neck">Neck Circumference</label>
                            <input type="number" step="0.1" class="form-control" id="neck" name="neck" required
                                min="0.1">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="lap">Lap</label>
                            <input type="number" step="0.1" class="form-control" id="lap" name="lap" required min="0.1">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="round_head">Head Circumference</label>
                            <input type="number" step="0.1" class="form-control" id="round_head" name="round_head"
                                required min="0.1">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="length">Body Length</label>
                            <input type="number" step="0.1" class="form-control" id="length" name="length" required
                                min="0.1">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="trouser_length">Trouser Length</label>
                            <input type="number" step="0.1" class="form-control" id="trouser_length"
                                name="trouser_length" required min="0.1">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="shoulder">Shoulder Width</label>
                            <input type="number" step="0.1" class="form-control" id="shoulder" name="shoulder" required
                                min="0.1">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="hand">Arm Length</label>
                            <input type="number" step="0.1" class="form-control" id="hand" name="hand" required
                                min="0.1">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="shape_bust">Bust/Chest Circumference</label>
                            <input type="number" step="0.1" class="form-control" id="shape_bust" name="shape_bust"
                                required min="0.1">
                        </div>

                        <?php if ($has_agbada): ?>
                            <div class="col-md-6 form-group mb-3">
                                <label for="agbada_full_length">Agbada Full Length</label>
                                <input type="number" step="0.1" class="form-control" id="agbada_full_length"
                                    name="agbada_full_length" required min="0.1">
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="agbada_full_width">Agbada Full Width</label>
                                <input type="number" step="0.1" class="form-control" id="agbada_full_width"
                                    name="agbada_full_width" required min="0.1">
                            </div>
                        <?php endif; ?>

                        <div class="col-md-6 form-group mb-3">
                            <label for="time_span">Production Time (weeks)</label>
                            <input type="number" class="form-control" id="time_span" name="time_span" required min="2"
                                value="2">
                            <small class="form-text text-muted">Minimum 2 weeks</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-btn btn-lg btn-block">
                    Review Order Summary
                </button>
            </div>
        </form>
    </div>
</section>

<?php include("layouts/footer.php"); ?>