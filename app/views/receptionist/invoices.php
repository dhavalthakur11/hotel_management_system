<?php 
$pageTitle = "Invoices";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Invoice Management</h1>
    <p>View and manage customer invoices</p>
</div>

<!-- Filter Section -->
<div class="card">
    <div class="card-header">Filter Invoices</div>
    <form method="GET" action="<?php echo Router::url('/receptionist/invoices'); ?>">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>From Date</label>
                <input type="date" name="date_from" value="<?php echo $_GET['date_from'] ?? date('Y-m-01'); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>To Date</label>
                <input type="date" name="date_to" value="<?php echo $_GET['date_to'] ?? date('Y-m-d'); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Payment Status</label>
                <select name="payment_status">
                    <option value="">All Status</option>
                    <option value="<?php echo PAYMENT_PENDING; ?>">Pending</option>
                    <option value="<?php echo PAYMENT_PAID; ?>">Paid</option>
                    <option value="<?php echo PAYMENT_PARTIAL; ?>">Partial</option>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </div>
        </div>
    </form>
</div>

<!-- Invoices List -->
<div class="card">
    <div class="card-header">All Invoices (<?php echo count($data['invoices']); ?>)</div>
    <?php if (!empty($data['invoices'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Bill #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Payment Method</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['invoices'] as $invoice): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($invoice['BILL_NUMBER']); ?></strong></td>
                        <td><?php echo date('d M Y', strtotime($invoice['BILL_DATE'])); ?></td>
                        <td><?php echo htmlspecialchars($invoice['CUSTOMER_NAME']); ?></td>
                        <td><?php echo $invoice['ROOM_NUMBER']; ?></td>
                        <td><strong>₹<?php echo number_format($invoice['TOTAL_AMOUNT'], 2); ?></strong></td>
                        <td>
                            <?php
                            $statusClass = 'badge-warning';
                            if ($invoice['PAYMENT_STATUS'] === PAYMENT_PAID) $statusClass = 'badge-success';
                            if ($invoice['PAYMENT_STATUS'] === PAYMENT_PARTIAL) $statusClass = 'badge-info';
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo ucfirst($invoice['PAYMENT_STATUS']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($invoice['PAYMENT_METHOD']): ?>
                                <?php echo ucfirst(str_replace('_', ' ', $invoice['PAYMENT_METHOD'])); ?>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo Router::url('/billing/invoice?booking_id=' . $invoice['BOOKING_ID']); ?>" 
                               class="btn btn-info">View</a>
                            <?php if ($invoice['PAYMENT_STATUS'] === PAYMENT_PAID): ?>
                                <button onclick="printInvoice(<?php echo $invoice['BILL_ID']; ?>)" class="btn btn-success">Print</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Summary Section -->
        <div style="padding: 20px; background: #f8f9fa; border-top: 2px solid #dee2e6; margin-top: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <?php
                $totalAmount = 0;
                $paidAmount = 0;
                $pendingAmount = 0;
                foreach ($data['invoices'] as $invoice) {
                    $totalAmount += $invoice['TOTAL_AMOUNT'];
                    if ($invoice['PAYMENT_STATUS'] === PAYMENT_PAID) {
                        $paidAmount += $invoice['TOTAL_AMOUNT'];
                    } else {
                        $pendingAmount += $invoice['TOTAL_AMOUNT'];
                    }
                }
                ?>
                <div>
                    <p style="color: #666; margin: 0; font-size: 14px;">Total Amount</p>
                    <p style="color: #333; margin: 5px 0 0 0; font-size: 24px; font-weight: bold;">
                        ₹<?php echo number_format($totalAmount, 2); ?>
                    </p>
                </div>
                <div>
                    <p style="color: #666; margin: 0; font-size: 14px;">Paid Amount</p>
                    <p style="color: #28a745; margin: 5px 0 0 0; font-size: 24px; font-weight: bold;">
                        ₹<?php echo number_format($paidAmount, 2); ?>
                    </p>
                </div>
                <div>
                    <p style="color: #666; margin: 0; font-size: 14px;">Pending Amount</p>
                    <p style="color: #dc3545; margin: 5px 0 0 0; font-size: 24px; font-weight: bold;">
                        ₹<?php echo number_format($pendingAmount, 2); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No invoices found for the selected period</p>
    <?php endif; ?>
</div>

<script>
    function printInvoice(billId) {
        window.open('<?php echo Router::url('/billing/invoice?bill_id='); ?>' + billId + '&print=1', '_blank');
    }
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>

?>