<?php
// ===========================================
// views/billing/invoice.php
// ===========================================
$pageTitle = "Invoice";
require_once __DIR__ . '/../shared/header.php';
$booking = $data['booking'];
?>
<div class="page-header">
    <h1>Invoice</h1>
</div>
<div class="card">
    <h2>Booking #<?php echo $booking['BOOKING_ID']; ?></h2>
    <p>Customer: <?php echo $booking['CUSTOMER_NAME']; ?></p>
    <p>Room: <?php echo $booking['ROOM_NUMBER']; ?></p>
    <?php if (isset($data['bill'])): ?>
        <h3>Bill Details</h3>
        <p>Room Charges: ₹<?php echo $data['bill']['ROOM_CHARGES']; ?></p>
        <p>Tax: ₹<?php echo $data['bill']['TAX_AMOUNT']; ?></p>
        <p><strong>Total: ₹<?php echo $data['bill']['TOTAL_AMOUNT']; ?></strong></p>
        <?php if ($data['bill']['PAYMENT_STATUS'] === PAYMENT_PENDING): ?>
            <form method="POST" action="<?php echo Router::url('/billing/payment'); ?>">
                <input type="hidden" name="bill_id" value="<?php echo $data['bill']['BILL_ID']; ?>">
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="upi">UPI</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Record Payment</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <form method="POST" action="<?php echo Router::url('/billing/generate'); ?>">
            <input type="hidden" name="booking_id" value="<?php echo $booking['BOOKING_ID']; ?>">
            <button type="submit" class="btn btn-primary">Generate Bill</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
