$pageTitle = "Booking Details";
require_once __DIR__ . '/../shared/header.php';
$booking = $data['booking'];
?>

<div class="page-header">
    <h1>Booking Details</h1>
    <p>Booking ID: #<?php echo $booking['BOOKING_ID']; ?></p>
</div>

<div class="card">
    <div class="card-header">Booking Information</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
            <p><strong>Booking ID:</strong> #<?php echo $booking['BOOKING_ID']; ?></p>
            <p><strong>Status:</strong> 
                <?php
                $statusClass = 'badge-warning';
                if ($booking['STATUS'] === BOOKING_CONFIRMED) $statusClass = 'badge-success';
                if ($booking['STATUS'] === BOOKING_CHECKED_IN) $statusClass = 'badge-info';
                if ($booking['STATUS'] === BOOKING_CHECKED_OUT) $statusClass = 'badge-primary';
                if ($booking['STATUS'] === BOOKING_CANCELLED) $statusClass = 'badge-danger';
                ?>
                <span class="badge <?php echo $statusClass; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $booking['STATUS'])); ?>
                </span>
            </p>
            <p><strong>Check-in Date:</strong> <?php echo date('d M Y', strtotime($booking['CHECK_IN_DATE'])); ?></p>
            <p><strong>Check-out Date:</strong> <?php echo date('d M Y', strtotime($booking['CHECK_OUT_DATE'])); ?></p>
            <p><strong>Number of Guests:</strong> <?php echo $booking['NUM_GUESTS']; ?></p>
        </div>
        <div>
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($booking['CUSTOMER_NAME']); ?></p>
            <p><strong>Email:</strong> <?php echo $booking['EMAIL']; ?></p>
            <p><strong>Phone:</strong> <?php echo $booking['PHONE']; ?></p>
            <p><strong>Room Number:</strong> <?php echo $booking['ROOM_NUMBER']; ?></p>
            <p><strong>Room Type:</strong> <?php echo $booking['ROOM_TYPE']; ?></p>
        </div>
    </div>
    
    <?php if ($booking['SPECIAL_REQUESTS']): ?>
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
            <p><strong>Special Requests:</strong></p>
            <p style="color: #666;"><?php echo nl2br(htmlspecialchars($booking['SPECIAL_REQUESTS'])); ?></p>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">Payment Information</div>
    <p><strong>Total Amount:</strong> ₹<?php echo number_format($booking['TOTAL_AMOUNT'], 2); ?></p>
    <p><strong>Advance Paid:</strong> ₹<?php echo number_format($booking['ADVANCE_PAID'], 2); ?></p>
    <p><strong>Balance:</strong> ₹<?php echo number_format($booking['TOTAL_AMOUNT'] - $booking['ADVANCE_PAID'], 2); ?></p>
</div>

<?php if ($booking['STATUS'] === BOOKING_CONFIRMED || $booking['STATUS'] === BOOKING_PENDING): ?>
    <?php if (AuthController::getUserRole() === ROLE_CUSTOMER): ?>
        <div class="card">
            <div class="card-header">Cancel Booking</div>
            <form method="POST" action="<?php echo Router::url('/booking/cancel'); ?>" 
                  onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                <input type="hidden" name="booking_id" value="<?php echo $booking['BOOKING_ID']; ?>">
                <div class="form-group">
                    <label>Cancellation Reason</label>
                    <textarea name="cancellation_reason" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-danger">Cancel Booking</button>
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>