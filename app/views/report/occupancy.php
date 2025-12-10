<?php
// ===========================================
// views/report/occupancy.php
// ===========================================
$pageTitle = "Occupancy Report";
require_once __DIR__ . '/../shared/header.php';
?>
<div class="page-header">
    <h1>Occupancy Report</h1>
</div>
<div class="card">
    <form method="GET">
        <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:15px;">
            <input type="date" name="start_date" value="<?php echo $data['start_date']; ?>">
            <input type="date" name="end_date" value="<?php echo $data['end_date']; ?>">
            <button type="submit" class="btn btn-primary">Generate</button>
        </div>
    </form>
</div>
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Bookings</h3>
        <p><?php echo count($data['bookings']); ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Rooms</h3>
        <p><?php echo $data['room_stats']['TOTAL_ROOMS']; ?></p>
    </div>
    <div class="stat-card">
        <h3>Occupancy Rate</h3>
        <p><?php echo round((count($data['bookings']) / ($data['room_stats']['TOTAL_ROOMS'] ?: 1)) * 100, 2); ?>%</p>
    </div>
</div>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
