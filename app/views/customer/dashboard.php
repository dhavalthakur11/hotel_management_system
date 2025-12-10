<?php
// ===========================================
// views/customer/dashboard.php
// ===========================================
$pageTitle = "My Dashboard";
require_once __DIR__ . '/../shared/header.php';
?>
<div class="page-header">
    <h1>Welcome, <?php echo AuthController::getUserName(); ?></h1>
    <a href="<?php echo Router::url('/customer/rooms'); ?>" class="btn btn-primary">Book a Room</a>
</div>
<div class="card">
    <div class="card-header">My Upcoming Bookings</div>
    <?php if (!empty($data['upcoming'])): ?>
        <table>
            <tr><th>Booking ID</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Action</th></tr>
            <?php foreach ($data['upcoming'] as $b): ?>
                <tr>
                    <td>#<?php echo $b['BOOKING_ID']; ?></td>
                    <td><?php echo $b['ROOM_NUMBER']; ?></td>
                    <td><?php echo date('d M Y', strtotime($b['CHECK_IN_DATE'])); ?></td>
                    <td><?php echo date('d M Y', strtotime($b['CHECK_OUT_DATE'])); ?></td>
                    <td><span class="badge badge-success"><?php echo $b['STATUS']; ?></span></td>
                    <td><a href="<?php echo Router::url('/booking/view?id='.$b['BOOKING_ID']); ?>" class="btn btn-info">View</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="padding:20px;text-align:center;">No upcoming bookings</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
