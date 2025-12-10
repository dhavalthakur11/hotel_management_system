<?php
$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Overview of hotel operations</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Rooms</h3>
        <p><?php echo $data['room_stats']['TOTAL_ROOMS'] ?? 0; ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Available Rooms</h3>
        <p><?php echo $data['room_stats']['AVAILABLE_ROOMS'] ?? 0; ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Occupied Rooms</h3>
        <p><?php echo $data['room_stats']['OCCUPIED_ROOMS'] ?? 0; ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Total Revenue</h3>
        <p>₹<?php echo number_format($data['revenue_stats']['TOTAL_REVENUE'] ?? 0, 2); ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header">Today's Check-ins</div>
    <?php if (!empty($data['todays_checkins'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['todays_checkins'] as $booking): ?>
                    <tr>
                        <td>#<?php echo $booking['BOOKING_ID']; ?></td>
                        <td><?php echo htmlspecialchars($booking['CUSTOMER_NAME']); ?></td>
                        <td><?php echo $booking['ROOM_NUMBER']; ?></td>
                        <td><?php echo $booking['PHONE']; ?></td>
                        <td>
                            <span class="badge badge-warning">
                                <?php echo ucfirst(str_replace('_', ' ', $booking['STATUS'])); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo Router::url('/booking/view?id=' . $booking['BOOKING_ID']); ?>" 
                               class="btn btn-info">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No check-ins scheduled for today</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">Today's Check-outs</div>
    <?php if (!empty($data['todays_checkouts'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['todays_checkouts'] as $booking): ?>
                    <tr>
                        <td>#<?php echo $booking['BOOKING_ID']; ?></td>
                        <td><?php echo htmlspecialchars($booking['CUSTOMER_NAME']); ?></td>
                        <td><?php echo $booking['ROOM_NUMBER']; ?></td>
                        <td><?php echo $booking['PHONE']; ?></td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo ucfirst(str_replace('_', ' ', $booking['STATUS'])); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo Router::url('/booking/view?id=' . $booking['BOOKING_ID']); ?>" 
                               class="btn btn-info">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No check-outs scheduled for today</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">Recent Bookings</div>
    <?php if (!empty($data['recent_bookings'])): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['recent_bookings'] as $booking): ?>
                    <tr>
                        <td>#<?php echo $booking['BOOKING_ID']; ?></td>
                        <td><?php echo htmlspecialchars($booking['CUSTOMER_NAME']); ?></td>
                        <td><?php echo $booking['ROOM_NUMBER']; ?></td>
                        <td><?php echo date('d M Y', strtotime($booking['CHECK_IN_DATE'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($booking['CHECK_OUT_DATE'])); ?></td>
                        <td>₹<?php echo number_format($booking['TOTAL_AMOUNT'], 2); ?></td>
                        <td>
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
                        </td>
                        <td>
                            <a href="<?php echo Router::url('/booking/view?id=' . $booking['BOOKING_ID']); ?>" 
                               class="btn btn-info">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No recent bookings</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>