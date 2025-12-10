<?php
// ===========================================
// views/receptionist/checkin.php
// ===========================================
$pageTitle = "Check-in";
require_once __DIR__ . '/../shared/header.php';
?>
<div class="page-header">
    <h1>Guest Check-in</h1>
</div>
<div class="card">
    <div class="card-header">Pending Check-ins</div>
    <?php if (!empty($data['pending_checkins'])): ?>
        <?php foreach ($data['pending_checkins'] as $booking): ?>
            <div style="border:1px solid #ddd;padding:15px;margin:10px 0;border-radius:5px;">
                <strong>Booking #<?php echo $booking['BOOKING_ID']; ?></strong><br>
                Customer: <?php echo $booking['CUSTOMER_NAME']; ?><br>
                Room: <?php echo $booking['ROOM_NUMBER']; ?><br>
                Phone: <?php echo $booking['PHONE']; ?><br>
                <form method="POST" action="<?php echo Router::url('/receptionist/checkin'); ?>" style="margin-top:10px;">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['BOOKING_ID']; ?>">
                    <button type="submit" class="btn btn-success">Complete Check-in</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="padding:20px;text-align:center;">No pending check-ins</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
