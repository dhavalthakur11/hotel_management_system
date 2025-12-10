<?php
// ===========================================
// views/audit/logs.php
// ===========================================
$pageTitle = "Audit Logs";
require_once __DIR__ . '/../shared/header.php';
?>
<div class="page-header">
    <h1>Audit Logs</h1>
</div>
<div class="card">
    <table>
        <tr><th>Time</th><th>User</th><th>Action</th><th>Table</th><th>Description</th><th>IP</th></tr>
        <?php foreach ($data['logs'] as $log): ?>
            <tr>
                <td><?php echo date('d M Y H:i', strtotime($log['CREATED_AT'])); ?></td>
                <td><?php echo $log['FULL_NAME']; ?></td>
                <td><span class="badge badge-info"><?php echo $log['ACTION']; ?></span></td>
                <td><?php echo $log['TABLE_NAME']; ?></td>
                <td><?php echo $log['DESCRIPTION']; ?></td>
                <td><?php echo $log['IP_ADDRESS']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
