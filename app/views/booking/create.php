<?php
// views/booking/create.php
$pageTitle = "Create Booking";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Create New Booking</h1>
    <p>Book a room for your stay</p>
</div>

<div class="card">
    <div class="card-header">Check Availability</div>
    <form method="GET" action="<?php echo Router::url('/booking/create'); ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; margin-bottom: 20px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Check-in Date</label>
                <input type="date" name="check_in" value="<?php echo $data['check_in'] ?? ''; ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Check-out Date</label>
                <input type="date" name="check_out" value="<?php echo $data['check_out'] ?? ''; ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Room Type (Optional)</label>
                <select name="room_type">
                    <option value="">All Types</option>
                    <option value="Standard">Standard</option>
                    <option value="Deluxe">Deluxe</option>
                    <option value="Suite">Suite</option>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">Check Availability</button>
            </div>
        </div>
    </form>
</div>

<?php if (isset($data['available_rooms'])): ?>
<div class="card">
    <div class="card-header">Available Rooms</div>
    <?php if (!empty($data['available_rooms'])): ?>
        <form method="POST" action="<?php echo Router::url('/booking/create'); ?>">
            <input type="hidden" name="check_in_date" value="<?php echo $data['check_in']; ?>">
            <input type="hidden" name="check_out_date" value="<?php echo $data['check_out']; ?>">
            
            <div style="display: grid; gap: 15px; margin-bottom: 20px;">
                <?php foreach ($data['available_rooms'] as $room): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                        <input type="radio" name="room_id" value="<?php echo $room['ROOM_ID']; ?>" required>
                        <strong>Room <?php echo $room['ROOM_NUMBER']; ?></strong> - 
                        <?php echo $room['ROOM_TYPE']; ?> | 
                        Max Occupancy: <?php echo $room['MAX_OCCUPANCY']; ?> | 
                        <strong>₹<?php echo number_format($room['BASE_PRICE'], 2); ?>/night</strong>
                        <?php if ($room['DESCRIPTION']): ?>
                            <p style="margin: 5px 0 0 20px; color: #666; font-size: 13px;">
                                <?php echo htmlspecialchars($room['DESCRIPTION']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="form-group">
                <label>Number of Guests *</label>
                <input type="number" name="num_guests" min="1" value="1" required>
            </div>
            
            <?php if (AuthController::getUserRole() === ROLE_CUSTOMER): ?>
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea name="special_requests" rows="3"></textarea>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Customer Name *</label>
                    <input type="text" name="customer_name" required>
                </div>
                <div class="form-group">
                    <label>Customer Email *</label>
                    <input type="email" name="customer_email" required>
                </div>
                <div class="form-group">
                    <label>Customer Phone *</label>
                    <input type="tel" name="customer_phone" required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="customer_address" rows="2"></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>ID Proof Type</label>
                        <select name="id_proof_type">
                            <option value="">Select</option>
                            <option value="Aadhar Card">Aadhar Card</option>
                            <option value="PAN Card">PAN Card</option>
                            <option value="Passport">Passport</option>
                            <option value="Driving License">Driving License</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ID Proof Number</label>
                        <input type="text" name="id_proof_number">
                    </div>
                </div>
                <div class="form-group">
                    <label>Advance Payment</label>
                    <input type="number" name="advance_paid" min="0" step="0.01" value="0">
                </div>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-success">Confirm Booking</button>
        </form>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">
            No rooms available for the selected dates. Please try different dates.
        </p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>

<?php