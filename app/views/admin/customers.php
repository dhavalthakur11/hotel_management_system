<?php
// ==========================================
// views/admin/customers.php
// ==========================================
$pageTitle = "Customer Management";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Customer Management</h1>
</div>

<div class="card">
    <div class="card-header">Search Customers</div>
    <form method="GET" action="<?php echo Router::url('/admin/customers'); ?>">
        <div style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search by name, email, or phone..." 
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                   style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if (!empty($_GET['search'])): ?>
                <a href="<?php echo Router::url('/admin/customers'); ?>" class="btn btn-info">Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">All Customers (<?php echo count($data['customers']); ?>)</div>
    <?php if (!empty($data['customers'])): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>State</th>
                    <th>ID Proof</th>
                    <th>Registered On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['customers'] as $customer): ?>
                    <tr>
                        <td><?php echo $customer['CUSTOMER_ID']; ?></td>
                        <td><strong><?php echo htmlspecialchars($customer['FULL_NAME']); ?></strong></td>
                        <td><?php echo htmlspecialchars($customer['EMAIL']); ?></td>
                        <td><?php echo htmlspecialchars($customer['PHONE']); ?></td>
                        <td><?php echo htmlspecialchars($customer['CITY'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($customer['STATE'] ?? '-'); ?></td>
                        <td>
                            <?php if ($customer['ID_PROOF_TYPE']): ?>
                                <?php echo htmlspecialchars($customer['ID_PROOF_TYPE']); ?>: 
                                <?php echo htmlspecialchars($customer['ID_PROOF_NUMBER']); ?>
                            <?php else: ?>
                                <span style="color: #999;">Not provided</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($customer['CREATED_AT'])); ?></td>
                        <td>
                            <a href="<?php echo Router::url('/customer/bookings?customer_id=' . $customer['CUSTOMER_ID']); ?>" 
                               class="btn btn-info">View Bookings</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">
            <?php echo !empty($_GET['search']) ? 'No customers found matching your search' : 'No customers found'; ?>
        </p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>
