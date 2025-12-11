
<?php
// ==========================================
// views/customer/bookings.php
// ==========================================
$pageTitle = "My Bookings";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>My Bookings</h1>
    <a href="<?php echo Router::url('/customer/rooms'); ?>" class="btn btn-primary">Book Another Room</a>
</div>

<?php
$upcomingBookings = [];
$pastBookings = [];
$today = date('Y-m-d');

foreach ($data['bookings'] as $booking) {
    if ($booking['CHECK_IN_DATE'] >= $today && $booking['STATUS'] !== BOOKING_CANCELLED) {
        $upcomingBookings[] = $booking;
    } else {
        $pastBookings[] = $booking;
    }
}
?>

<!-- Upcoming Bookings -->
<div class="card">
    <div class="card-header">Upcoming Bookings (<?php echo count($upcomingBookings); ?>)</div>
    <?php if (!empty($upcomingBookings)): ?>
        <?php foreach ($upcomingBookings as $booking): ?>
            <div style="border-bottom: 1px solid #f0f0f0; padding: 20px; display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <h3 style="margin: 0;">Booking #<?php echo $booking['BOOKING_ID']; ?></h3>
                        <?php
                        $statusClass = 'badge-success';
                        if ($booking['STATUS'] === BOOKING_PENDING) $statusClass = 'badge-warning';
                        if ($booking['STATUS'] === BOOKING_CHECKED_IN) $statusClass = 'badge-info';
                        ?>
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $booking['STATUS'])); ?>
                        </span>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; color: #666; font-size: 14px;">
                        <div>
                            <p style="margin: 5px 0;"><strong>Room:</strong> <?php echo $booking['ROOM_NUMBER']; ?> (<?php echo $booking['ROOM_TYPE']; ?>)</p>
                            <p style="margin: 5px 0;"><strong>Guests:</strong> <?php echo $booking['NUM_GUESTS']; ?></p>
                        </div>
                        <div>
                            <p style="margin: 5px 0;"><strong>Check-in:</strong> <?php echo date('d M Y', strtotime($booking['CHECK_IN_DATE'])); ?></p>
                            <p style="margin: 5px 0;"><strong>Check-out:</strong> <?php echo date('d M Y', strtotime($booking['CHECK_OUT_DATE'])); ?></p>
                        </div>
                        <div>
                            <p style="margin: 5px 0;"><strong>Total Amount:</strong> <span style="color: #28a745; font-weight: 600;">₹<?php echo number_format($booking['TOTAL_AMOUNT'], 2); ?></span></p>
                            <p style="margin: 5px 0;"><strong>Advance Paid:</strong> ₹<?php echo number_format($booking['ADVANCE_PAID'], 2); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($booking['SPECIAL_REQUESTS']): ?>
                        <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                            <strong style="font-size: 12px; color: #666;">Special Requests:</strong>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #666;"><?php echo nl2br(htmlspecialchars($booking['SPECIAL_REQUESTS'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 10px; margin-left: 20px;">
                    <a href="<?php echo Router::url('/booking/view?id=' . $booking['BOOKING_ID']); ?>" class="btn btn-info">View Details</a>
                    <?php if ($booking['STATUS'] === BOOKING_CONFIRMED || $booking['STATUS'] === BOOKING_PENDING): ?>
                        <button onclick="showCancelModal(<?php echo $booking['BOOKING_ID']; ?>)" class="btn btn-danger">Cancel Booking</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">
            No upcoming bookings. <a href="<?php echo Router::url('/customer/rooms'); ?>">Book a room now!</a>
        </p>
    <?php endif; ?>
</div>

<!-- Past Bookings -->
<div class="card">
    <div class="card-header">Past Bookings (<?php echo count($pastBookings); ?>)</div>
    <?php if (!empty($pastBookings)): ?>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pastBookings as $booking): ?>
                    <tr>
                        <td><strong>#<?php echo $booking['BOOKING_ID']; ?></strong></td>
                        <td><?php echo $booking['ROOM_NUMBER']; ?> (<?php echo $booking['ROOM_TYPE']; ?>)</td>
                        <td><?php echo date('d M Y', strtotime($booking['CHECK_IN_DATE'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($booking['CHECK_OUT_DATE'])); ?></td>
                        <td>₹<?php echo number_format($booking['TOTAL_AMOUNT'], 2); ?></td>
                        <td>
                            <?php
                            $statusClass = 'badge-primary';
                            if ($booking['STATUS'] === BOOKING_CANCELLED) $statusClass = 'badge-danger';
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $booking['STATUS'])); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo Router::url('/booking/view?id=' . $booking['BOOKING_ID']); ?>" class="btn btn-info">View</a>
                            <?php if ($booking['STATUS'] === BOOKING_CHECKED_OUT): ?>
                                <a href="<?php echo Router::url('/feedback?booking_id=' . $booking['BOOKING_ID']); ?>" class="btn btn-success">Feedback</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No past bookings</p>
    <?php endif; ?>
</div>

<!-- Cancel Booking Modal -->
<div id="cancelModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 15% auto; padding: 30px; border-radius: 10px; width: 90%; max-width: 500px;">
        <h2 style="margin-bottom: 20px;">Cancel Booking</h2>
        <form id="cancelForm" method="POST" action="<?php echo Router::url('/booking/cancel'); ?>">
            <input type="hidden" name="booking_id" id="cancelBookingId">
            <div class="form-group">
                <label>Cancellation Reason *</label>
                <textarea name="cancellation_reason" rows="4" required placeholder="Please provide a reason for cancellation..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="closeCancelModal()" class="btn btn-info">Close</button>
                <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showCancelModal(bookingId) {
        document.getElementById('cancelBookingId').value = bookingId;
        document.getElementById('cancelModal').style.display = 'block';
    }
    
    function closeCancelModal() {
        document.getElementById('cancelModal').style.display = 'none';
    }
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>


<?php