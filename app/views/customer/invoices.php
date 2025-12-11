<?php
// ==========================================
// views/customer/invoices.php
// ==========================================
$pageTitle = "My Invoices";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>My Invoices</h1>
    <p>View your payment history and invoices</p>
</div>

<div class="card">
    <div class="card-header">All Invoices (<?php echo count($data['invoices']); ?>)</div>
    <?php if (!empty($data['invoices'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Booking ID</th>
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
                        <td>#<?php echo $invoice['BOOKING_ID']; ?></td>
                        <td><?php echo $invoice['ROOM_NUMBER']; ?></td>
                        <td><strong style="color: #28a745;">₹<?php echo number_format($invoice['TOTAL_AMOUNT'], 2); ?></strong></td>
                        <td>
                            <?php
                            $statusClass = 'badge-warning';
                            if ($invoice['PAYMENT_STATUS'] === PAYMENT_PAID) {
                                $statusClass = 'badge-success';
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo ucfirst($invoice['PAYMENT_STATUS']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($invoice['PAYMENT_METHOD']): ?>
                                <?php echo ucfirst(str_replace('_', ' ', $invoice['PAYMENT_METHOD'])); ?>
                            <?php else: ?>
                                <span style="color: #999;">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="viewInvoiceDetails(<?php echo htmlspecialchars(json_encode($invoice)); ?>)" class="btn btn-info">View Details</button>
                            <?php if ($invoice['PAYMENT_STATUS'] === PAYMENT_PAID): ?>
                                <button onclick="downloadInvoice(<?php echo $invoice['BILL_ID']; ?>)" class="btn btn-success">Download</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Payment Summary -->
        <div style="padding: 20px; background: #f8f9fa; border-top: 2px solid #dee2e6; margin-top: 20px;">
            <h3 style="margin-bottom: 15px;">Payment Summary</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <?php
                $totalPaid = 0;
                $totalPending = 0;
                foreach ($data['invoices'] as $invoice) {
                    if ($invoice['PAYMENT_STATUS'] === PAYMENT_PAID) {
                        $totalPaid += $invoice['TOTAL_AMOUNT'];
                    } else {
                        $totalPending += $invoice['TOTAL_AMOUNT'];
                    }
                }
                ?>
                <div>
                    <p style="color: #666; margin: 0; font-size: 14px;">Total Paid</p>
                    <p style="color: #28a745; margin: 5px 0 0 0; font-size: 24px; font-weight: bold;">
                        ₹<?php echo number_format($totalPaid, 2); ?>
                    </p>
                </div>
                <div>
                    <p style="color: #666; margin: 0; font-size: 14px;">Pending Payment</p>
                    <p style="color: #dc3545; margin: 5px 0 0 0; font-size: 24px; font-weight: bold;">
                        ₹<?php echo number_format($totalPending, 2); ?>
                    </p>
                </div>
                <div>
                    <p style="color: #666; margin: 0; font-size: 14px;">Total Amount</p>
                    <p style="color: #333; margin: 5px 0 0 0; font-size: 24px; font-weight: bold;">
                        ₹<?php echo number_format($totalPaid + $totalPending, 2); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No invoices found</p>
    <?php endif; ?>
</div>

<!-- Invoice Details Modal -->
<div id="invoiceModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 5% auto; padding: 30px; border-radius: 10px; width: 90%; max-width: 600px;">
        <span onclick="closeInvoiceModal()" style="float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2 style="margin-bottom: 20px;">Invoice Details</h2>
        <div id="invoiceDetails"></div>
        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button onclick="closeInvoiceModal()" class="btn btn-info">Close</button>
        </div>
    </div>
</div>

<script>
    function viewInvoiceDetails(invoice) {
        const details = `
            <div style="line-height: 2;">
                <p><strong>Invoice Number:</strong> ${invoice.BILL_NUMBER}</p>
                <p><strong>Date:</strong> ${new Date(invoice.BILL_DATE).toLocaleDateString()}</p>
                <p><strong>Booking ID:</strong> #${invoice.BOOKING_ID}</p>
                <p><strong>Room:</strong> ${invoice.ROOM_NUMBER}</p>
                <hr style="margin: 15px 0;">
                <p><strong>Room Charges:</strong> ₹${parseFloat(invoice.ROOM_CHARGES).toFixed(2)}</p>
                <p><strong>Additional Charges:</strong> ₹${parseFloat(invoice.ADDITIONAL_CHARGES || 0).toFixed(2)}</p>
                <p><strong>Tax Amount:</strong> ₹${parseFloat(invoice.TAX_AMOUNT).toFixed(2)}</p>
                <p><strong>Discount:</strong> -₹${parseFloat(invoice.DISCOUNT || 0).toFixed(2)}</p>
                <hr style="margin: 15px 0;">
                <p style="font-size: 18px;"><strong>Total Amount:</strong> <span style="color: #28a745;">₹${parseFloat(invoice.TOTAL_AMOUNT).toFixed(2)}</span></p>
                <p><strong>Payment Status:</strong> <span class="badge badge-${invoice.PAYMENT_STATUS === 'paid' ? 'success' : 'warning'}">${invoice.PAYMENT_STATUS}</span></p>
                ${invoice.PAYMENT_METHOD ? `<p><strong>Payment Method:</strong> ${invoice.PAYMENT_METHOD.replace('_', ' ')}</p>` : ''}
            </div>
        `;
        document.getElementById('invoiceDetails').innerHTML = details;
        document.getElementById('invoiceModal').style.display = 'block';
    }
    
    function closeInvoiceModal() {
        document.getElementById('invoiceModal').style.display = 'none';
    }
    
    function downloadInvoice(billId) {
        alert('Invoice download feature will be implemented with PDF generation.');
    }
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>
