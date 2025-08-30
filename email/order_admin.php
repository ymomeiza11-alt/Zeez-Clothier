<?php
/**
 * @param int $order_id
 * @param string $user_email
 */
?>
New order #<?= $order_id ?> from <?= htmlspecialchars($user_email) ?>.
View order: http://zeezclothier.com/admin/dashboard/<?= $order_id ?>