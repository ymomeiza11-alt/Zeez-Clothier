<?php
include('header.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

try {
    $orders = [];
    $total_no_of_pages = 1;
    $page_no = isset($_GET["page_no"]) ? (int) $_GET["page_no"] : 1;
    $page_no = max(1, $page_no); 

    $stmt = $connection->prepare("SELECT COUNT(*) AS total_records FROM orders");
    $stmt->execute();
    $stmt->bind_result($total_records);
    $stmt->fetch();
    $stmt->close();

    $total_records_per_page = 9;
    $offset = ($page_no - 1) * $total_records_per_page;
    $total_no_of_pages = max(1, ceil($total_records / $total_records_per_page));

    $stmt = $connection->prepare("
        SELECT 
            o.order_id, 
            o.order_status, 
            o.user_id, 
            o.order_date, 
            o.user_phone, 
            o.user_address, 
            o.payment_method,
            m.time_span
        FROM orders o
        LEFT JOIN measurements m ON o.order_id = m.order_id
        ORDER BY o.order_date DESC
        LIMIT ?, ?
    ");
    $stmt->bind_param("ii", $offset, $total_records_per_page);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while processing your request. Please try again later.";
}
?>

<div class="container-fluid">
    <div class="row" style="min-height: 1000px;">
        <?php include('sidemenu.php'); ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <!-- Your buttons here -->
                    </div>
                </div>
            </div>

            <h2>Orders</h2>
            <?php if (isset($_GET['orderUpdate_success'])) { ?>
                <div class="alert text-center alert-success">
                    <?php echo $_GET['orderUpdate_success']; ?>
                </div>
            <?php } ?>
            <?php if (isset($_GET['orderUpdate_failure'])) { ?>
                <div class="alert text-center alert-danger">
                    <?php echo $_GET['orderUpdate_failure']; ?>
                </div>
            <?php } ?>
            <?php if (isset($error_message)) { ?>
                <div class="alert text-center alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php } ?>

            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th scope="col">Order ID</th>
                            <th scope="col">Order Status</th>
                            <th scope="col">User ID</th>
                            <th scope="col">Order Date</th>
                            <th scope="col">User Phone</th>
                            <th scope="col">User Address</th>
                            <th scope="col">Prod Time</th>
                            <th scope="col">Payment Method</th>
                            <th scope="col">Measurements</th>
                            <th scope="col">Edit</th>
                            <th scope="col">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_id']) ?></td>
                                <td><?= htmlspecialchars($order['order_status']) ?></td>
                                <td><?= htmlspecialchars($order['user_id']) ?></td>
                                <td><?= htmlspecialchars($order['order_date']) ?></td>
                                <td><?= htmlspecialchars($order['user_phone']) ?></td>
                                <td><?= htmlspecialchars($order['user_address']) ?></td>
                                <td>
                                    <?= isset($order['time_span']) ?
                                        htmlspecialchars($order['time_span']) . ' weeks' :
                                        'N/A' ?>
                                </td>
                                <td>
                                    <?= isset($order['payment_method']) ?
                                        ucfirst(str_replace('_', ' ', $order['payment_method'])) :
                                        'N/A' ?>
                                </td>
                                <td>
                                    <?php if (isset($order['time_span'])): ?>
                                        <a href="measurements.php?order_id=<?= $order['order_id'] ?>"
                                            class="btn btn-sm btn-info">
                                            View
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_orders.php?order_id=<?= $order['order_id'] ?>"
                                        class="btn btn-sm btn-warning">
                                        Edit
                                    </a>
                                </td>
                                <td>
                                    <a href="delete_order.php?order_id=<?= $order['order_id'] ?>"
                                        class="btn btn-sm btn-danger">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page_no > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="dashboard.php?page_no=<?= $page_no - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_no_of_pages; $i++): ?>
                        <li class="page-item <?= $i == $page_no ? 'active' : '' ?>">
                            <a class="page-link" href="dashboard.php?page_no=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page_no < $total_no_of_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="dashboard.php?page_no=<?= $page_no + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"></script>
</body>

</html>