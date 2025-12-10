<?php
// ===========================================
// views/feedback/index.php
// ===========================================
$pageTitle = "Customer Feedback";
require_once __DIR__ . '/../shared/header.php';
?>
<div class="page-header">
    <h1>Customer Feedback</h1>
</div>
<div class="card">
    <?php foreach ($data['feedbacks'] as $f): ?>
        <div style="border-bottom:1px solid #f0f0f0;padding:15px 0;">
            <strong><?php echo $f['CUSTOMER_NAME']; ?></strong> - 
            <span style="color:#ffc107;">★ <?php echo $f['RATING']; ?>/5</span><br>
            <small><?php echo date('d M Y', strtotime($f['CREATED_AT'])); ?></small>
            <p><?php echo nl2br(htmlspecialchars($f['COMMENTS'])); ?></p>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
