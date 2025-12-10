<?php
// ===========================================
// views/receptionist/checkout.php
// ===========================================
$pageTitle = "Check-out";
require_once __DIR__ . '/../shared/header.php';
?>
<div class="page-header">
    <h1>Guest Check-out</h1>
</div>
<div class="card">
    <div class="card-header">Pending Check-outs</div>
    <?php if (!empty($data['pending_checkouts'])): ?>
        <?php foreach ($data['pending_checkouts'] as $booking): ?>
            <div style="border:1px solid #ddd;padding:15px;margin:10px 0;border-radius:5px;">
                <strong>Booking #<?php echo $booking['BOOKING_ID']; ?></strong><br>
                Customer: <?php echo $booking['CUSTOMER_NAME']; ?><br>
                Room: <?php echo $booking['ROOM_NUMBER']; ?><br>
                <form method="POST" action="<?php echo Router::url('/receptionist/checkout'); ?>" style="margin-top:10px;">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['BOOKING_ID']; ?>">
                    <button type="submit" class="btn btn-primary">Complete Check-out</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="padding:20px;text-align:center;">No pending check-outs</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
