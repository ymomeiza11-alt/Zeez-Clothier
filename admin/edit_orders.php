<?php 
    include('header.php');

    if(isset($_GET["order_id"])){
        $order_id = $_GET["order_id"];
        $stmt = $connection->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        $order = $stmt->get_result();
    }else if(isset($_POST["edit_order"])){
        $order_status = $_POST['order_status'];
        $order_id = $_POST['order_id'];

        $stmt = $connection->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $order_status, $order_id);
        if($stmt->execute()) {
            header("Location: dashboard.php?orderUpdate_success=Order updated successfully");
        } else {
            header("Location: dashboard.php?orderUpdate_failure=Failed to update order");
        }
    }else{
        header("Location: dashboard.php");
        exit();
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

            <h2>Edit Order</h2>
            <div class="table-responsive">
                <div class="mx-auto container">
                    <form id="edit-order-form" method="POST" action="edit_orders.php">
                        <?php foreach($order as $row) {?>
                        <div class="form-group my-3">
                            <label for="">Order ID</label>
                            <p class="my4"><?php echo $row['order_id']; ?></p>
                        </div>
                        <div class="form-group my-3">
                            <label for="">Order Cost</label>
                            <p class="my4"><?php echo $row['order_cost']; ?></p>
                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        </div>
                        <div class="form-group my-3">
                            <label for="">Order Status</label>
                            <select name="order_status" required class="form-select">
                                <option value="not_paid" <?php if($row["order_status"] === "not_paid"){echo "selected";} ?>>Not Paid</option>
                                <option value="pending" <?php if($row["order_status"] === "pending"){echo "selected";} ?>>Pending</option>
                                <option value="shipped" <?php if($row["order_status"] === "shipped"){echo "selected";} ?>>Shipped</option>
                                <option value="delivered" <?php if($row["order_status"] === "delivered"){echo "selected";} ?>>Delivered</option>
                                <option value="canceled" <?php if($row["order_status"] === "canceled"){echo "selected";} ?>>Canceled</option>
                            </select>
                        </div>
                        <div class="form-group my-3">
                            <label for="">Order Date</label>
                            <p class="my4"><?php echo $row['order_date']; ?></p>
                        </div>

                        <div class="form-group my-3">
                            <input type="submit" name="edit_order" value="edit_order" class="btn btn-primary">
                        </div>
                        <?php } ?>
                    </form>
                </div>
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