
<?php
/**
 * @param string $user_name
 * @param int $order_id
 * @param float $order_total
 * @param string $order_date
 */
?>
<!DOCTYPE html>
<html>
<body style="font-family: Arial; line-height: 1.6;">
  <h2>Hi <?= htmlspecialchars($user_name) ?>,</h2>
  <p>Your order <strong>#<?= $order_id ?></strong> has been received.</p>
  
  <p><strong>Order Total:</strong> ₦<?= number_format($order_total, 2) ?></p>
  <p><strong>Date:</strong> <?= $order_date ?></p>
  
  <p style="color: #666;">If you didn't place this order, contact us immediately.</p>
</body>
</html>