<?php
// views/audit/logs.php
$pageTitle = "Audit Logs";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Audit Logs</h1>
    <p>Track all system activities and user actions</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Logs</h3>
        <p><?php echo number_format($data['stats']['total']); ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Today</h3>
        <p><?php echo number_format($data['stats']['today']); ?></p>
    </div>
    
    <div class="stat-card">
        <h3>This Week</h3>
        <p><?php echo number_format($data['stats']['this_week']); ?></p>
    </div>
    
    <div class="stat-card">
        <h3>This Month</h3>
        <p><?php echo number_format($data['stats']['this_month']); ?></p>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">Filter Logs</div>
    <form method="GET" action="<?php echo Router::url('/audit/logs'); ?>">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>User</label>
                <select name="user_id">
                    <option value="">All Users</option>
                    <?php foreach ($data['users'] as $user): ?>
                        <option value="<?php echo $user['USER_ID']; ?>" 
                                <?php echo ($data['filters']['user_id'] == $user['USER_ID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['FULL_NAME']); ?> (<?php echo $user['ROLE']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label>Action</label>
                <select name="action">
                    <option value="">All Actions</option>
                    <?php foreach ($data['actions'] as $action): ?>
                        <option value="<?php echo $action; ?>" 
                                <?php echo ($data['filters']['action'] === $action) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($action); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label>Table</label>
                <select name="table_name">
                    <option value="">All Tables</option>
                    <?php foreach ($data['tables'] as $table): ?>
                        <option value="<?php echo $table; ?>" 
                                <?php echo ($data['filters']['table_name'] === $table) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($table); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label>From Date</label>
                <input type="date" name="date_from" value="<?php echo $data['filters']['date_from']; ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label>To Date</label>
                <input type="date" name="date_to" value="<?php echo $data['filters']['date_to']; ?>">
            </div>
            
            <div style="align-self: flex-end; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="<?php echo Router::url('/audit/logs'); ?>" class="btn btn-info">Clear</a>
            </div>
        </div>
    </form>
</div>

<!-- Statistics by Action -->
<?php if (!empty($data['stats']['by_action'])): ?>
    <div class="card">
        <div class="card-header">Actions Summary</div>
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                <?php foreach (array_slice($data['stats']['by_action'], 0, 6) as $action => $count): ?>
                    <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 24px; font-weight: bold; color: #667eea;"><?php echo $count; ?></div>
                        <div style="font-size: 13px; color: #666; margin-top: 5px;"><?php echo ucfirst($action); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Audit Logs Table -->
<div class="card">
    <div class="card-header">
        Audit Logs (<?php echo number_format($data['total_logs']); ?> total)
        <div style="float: right;">
            <button onclick="exportLogs()" class="btn btn-success" style="padding: 5px 10px; font-size: 13px;">
                📥 Export CSV
            </button>
        </div>
    </div>
    
    <?php if (!empty($data['logs'])): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record ID</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['logs'] as $log): ?>
                    <tr>
                        <td><?php echo $log['AUDIT_ID']; ?></td>
                        <td style="white-space: nowrap;">
                            <?php echo date('d M Y', strtotime($log['CREATED_AT'])); ?><br>
                            <small style="color: #999;"><?php echo date('H:i:s', strtotime($log['CREATED_AT'])); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($log['FULL_NAME'] ?? 'Unknown'); ?></strong><br>
                            <small style="color: #999;"><?php echo htmlspecialchars($log['USERNAME'] ?? 'N/A'); ?></small>
                        </td>
                        <td>
                            <?php
                            $badgeClass = 'badge-info';
                            if ($log['ACTION'] === ACTION_CREATE) $badgeClass = 'badge-success';
                            if ($log['ACTION'] === ACTION_UPDATE) $badgeClass = 'badge-warning';
                            if ($log['ACTION'] === ACTION_DELETE) $badgeClass = 'badge-danger';
                            if ($log['ACTION'] === ACTION_LOGIN) $badgeClass = 'badge-primary';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo strtoupper($log['ACTION']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($log['TABLE_NAME'] ?? '-'); ?></td>
                        <td><?php echo $log['RECORD_ID'] ?? '-'; ?></td>
                        <td style="max-width: 300px;">
                            <?php 
                            $desc = htmlspecialchars($log['DESCRIPTION']);
                            echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($log['IP_ADDRESS']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($data['total_pages'] > 1): ?>
            <div style="padding: 20px; text-align: center; border-top: 1px solid #dee2e6;">
                <div style="display: inline-flex; gap: 5px;">
                    <?php if ($data['current_page'] > 1): ?>
                        <a href="?page=<?php echo $data['current_page'] - 1; ?><?php echo http_build_query(array_filter($data['filters'])) ? '&' . http_build_query(array_filter($data['filters'])) : ''; ?>" 
                           class="btn btn-info">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $data['current_page'] - 2); $i <= min($data['total_pages'], $data['current_page'] + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo http_build_query(array_filter($data['filters'])) ? '&' . http_build_query(array_filter($data['filters'])) : ''; ?>" 
                           class="btn <?php echo $i === $data['current_page'] ? 'btn-primary' : 'btn-info'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($data['current_page'] < $data['total_pages']): ?>
                        <a href="?page=<?php echo $data['current_page'] + 1; ?><?php echo http_build_query(array_filter($data['filters'])) ? '&' . http_build_query(array_filter($data['filters'])) : ''; ?>" 
                           class="btn btn-info">Next</a>
                    <?php endif; ?>
                </div>
                <div style="margin-top: 10px; color: #666;">
                    Page <?php echo $data['current_page']; ?> of <?php echo $data['total_pages']; ?>
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No audit logs found</p>
    <?php endif; ?>
</div>

<!-- Most Active Users -->
<?php if (!empty($data['stats']['by_user'])): ?>
    <div class="card">
        <div class="card-header">Most Active Users</div>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Actions</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $topUsers = array_slice($data['stats']['by_user'], 0, 10);
                foreach ($topUsers as $userName => $count): 
                    $percentage = round(($count / $data['stats']['total']) * 100, 1);
                ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($userName); ?></strong></td>
                        <td><?php echo number_format($count); ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; background: #f0f0f0; border-radius: 10px; height: 20px; overflow: hidden;">
                                    <div style="width: <?php echo $percentage; ?>%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%;"></div>
                                </div>
                                <span style="min-width: 50px; text-align: right;"><?php echo $percentage; ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
    function exportLogs() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '<?php echo Router::url('/audit/export-csv'); ?>?' + params.toString();
    }
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>