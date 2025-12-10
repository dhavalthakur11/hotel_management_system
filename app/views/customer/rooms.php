<?php
// ===========================================
// views/customer/rooms.php
// ===========================================
$pageTitle = "Browse Rooms";
require_once __DIR__ . '/../shared/header.php';
?>
<div class="page-header">
    <h1>Available Rooms</h1>
</div>
<div class="card">
    <form method="GET" action="<?php echo Router::url('/customer/rooms'); ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:15px;">
            <div class="form-group">
                <label>Check-in</label>
                <input type="date" name="check_in" value="<?php echo $data['check_in']; ?>" required>
            </div>
            <div class="form-group">
                <label>Check-out</label>
                <input type="date" name="check_out" value="<?php echo $data['check_out']; ?>" required>
            </div>
            <div style="align-self:flex-end;">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>
</div>
<?php if (!empty($data['available_rooms'])): ?>
    <div style="display:grid;gap:20px;">
        <?php foreach ($data['available_rooms'] as $room): ?>
            <div class="card">
                <h3><?php echo $room['ROOM_TYPE']; ?> - Room <?php echo $room['ROOM_NUMBER']; ?></h3>
                <p>Max Occupancy: <?php echo $room['MAX_OCCUPANCY']; ?> guests</p>
                <p><strong>₹<?php echo number_format($room['BASE_PRICE'], 2); ?>/night</strong></p>
                <a href="<?php echo Router::url('/booking/create?check_in='.$data['check_in'].'&check_out='.$data['check_out'].'&room_id='.$room['ROOM_ID']); ?>" class="btn btn-success">Book Now</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
