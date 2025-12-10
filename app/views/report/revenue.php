<?php
// ===========================================
// views/report/revenue.php
// ===========================================
$pageTitle = "Revenue Report";
require_once __DIR__ . '/../shared/header.php';
?>
<div class="page-header">
    <h1>Revenue Report</h1>
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
        <h3>Total Revenue</h3>
        <p>₹<?php echo number_format($data['revenue_stats']['TOTAL_REVENUE'] ?? 0, 2); ?></p>
    </div>
    <div class="stat-card">
        <h3>Paid Revenue</h3>
        <p>₹<?php echo number_format($data['revenue_stats']['PAID_REVENUE'] ?? 0, 2); ?></p>
    </div>
    <div class="stat-card">
        <h3>Pending</h3>
        <p>₹<?php echo number_format($data['revenue_stats']['PENDING_REVENUE'] ?? 0, 2); ?></p>
    </div>
</div>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
