<?php
// ===========================================
// views/receptionist/dashboard.php
// ===========================================
$pageTitle = "Receptionist Dashboard";
require_once __DIR__ . '/../shared/header.php';
?>
<div class="page-header">
    <h1>Receptionist Dashboard</h1>
    <a href="<?php echo Router::url('/booking/create'); ?>" class="btn btn-primary">New Booking</a>
</div>
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Rooms</h3>
        <p><?php echo $data['room_stats']['TOTAL_ROOMS'] ?? 0; ?></p>
    </div>
    <div class="stat-card">
        <h3>Available</h3>
        <p><?php echo $data['room_stats']['AVAILABLE_ROOMS'] ?? 0; ?></p>
    </div>
    <div class="stat-card">
        <h3>Occupied</h3>
        <p><?php echo $data['room_stats']['OCCUPIED_ROOMS'] ?? 0; ?></p>
    </div>
</div>
<div class="card">
    <div class="card-header">Today's Check-ins</div>
    <?php if (!empty($data['todays_checkins'])): ?>
        <table>
            <tr><th>Customer</th><th>Room</th><th>Phone</th><th>Action</th></tr>
            <?php foreach ($data['todays_checkins'] as $b): ?>
                <tr>
                    <td><?php echo $b['CUSTOMER_NAME']; ?></td>
                    <td><?php echo $b['ROOM_NUMBER']; ?></td>
                    <td><?php echo $b['PHONE']; ?></td>
                    <td><a href="<?php echo Router::url('/receptionist/checkin'); ?>" class="btn btn-success">Check-in</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="padding:20px;text-align:center;color:#999;">No check-ins today</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
