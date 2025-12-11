<?php
// ==========================================
// views/notification/templates.php
// ==========================================
$pageTitle = "Notification Templates";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Notification Templates</h1>
    <p>Manage email and SMS notification templates</p>
</div>

<div class="card">
    <div class="card-header">Available Templates</div>
    
    <div style="padding: 20px;">
        <!-- Booking Confirmation Template -->
        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <h3 style="margin: 0 0 5px 0;">Booking Confirmation</h3>
                    <p style="margin: 0; color: #666; font-size: 14px;">Sent when a booking is confirmed</p>
                </div>
                <span class="badge badge-success">Active</span>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <p style="margin: 0; font-family: monospace; font-size: 13px; white-space: pre-wrap;">Dear {customer_name},

Your booking has been confirmed!

Booking ID: {booking_id}
Room: {room_number} - {room_type}
Check-in: {check_in_date}
Check-out: {check_out_date}
Total Amount: ₹{total_amount}

Thank you for choosing us!

Best regards,
Hotel Management Team</p>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-info">Edit Template</button>
                <button class="btn btn-success">Send Test</button>
            </div>
        </div>
        
        <!-- Check-in Reminder Template -->
        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <h3 style="margin: 0 0 5px 0;">Check-in Reminder</h3>
                    <p style="margin: 0; color: #666; font-size: 14px;">Sent 1 day before check-in</p>
                </div>
                <span class="badge badge-success">Active</span>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <p style="margin: 0; font-family: monospace; font-size: 13px; white-space: pre-wrap;">Dear {customer_name},

This is a reminder that your check-in is scheduled for tomorrow!

Booking ID: {booking_id}
Room: {room_number}
Check-in Date: {check_in_date}
Check-in Time: 2:00 PM

Please ensure you have your booking confirmation and ID proof.

See you soon!

Best regards,
Hotel Management Team</p>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-info">Edit Template</button>
                <button class="btn btn-success">Send Test</button>
            </div>
        </div>
        
        <!-- Payment Success Template -->
        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <h3 style="margin: 0 0 5px 0;">Payment Confirmation</h3>
                    <p style="margin: 0; color: #666; font-size: 14px;">Sent when payment is received</p>
                </div>
                <span class="badge badge-success">Active</span>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <p style="margin: 0; font-family: monospace; font-size: 13px; white-space: pre-wrap;">Dear {customer_name},

We have received your payment successfully!

Invoice Number: {invoice_number}
Amount Paid: ₹{amount_paid}
Payment Method: {payment_method}
Transaction Date: {payment_date}

Your receipt has been generated and will be emailed to you shortly.

Thank you for your payment!

Best regards,
Hotel Management Team</p>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-info">Edit Template</button>
                <button class="btn btn-success">Send Test</button>
            </div>
        </div>
        
        <!-- Check-out Thank You Template -->
        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <h3 style="margin: 0 0 5px 0;">Check-out Thank You</h3>
                    <p style="margin: 0; color: #666; font-size: 14px;">Sent after guest checks out</p>
                </div>
                <span class="badge badge-success">Active</span>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <p style="margin: 0; font-family: monospace; font-size: 13px; white-space: pre-wrap;">Dear {customer_name},

Thank you for staying with us!

We hope you had a wonderful experience at our hotel. Your feedback is valuable to us and helps us improve our services.

Booking ID: {booking_id}
Check-out Date: {check_out_date}

Please take a moment to share your feedback: {feedback_link}

We look forward to welcoming you back soon!

Best regards,
Hotel Management Team</p>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-info">Edit Template</button>
                <button class="btn btn-success">Send Test</button>
            </div>
        </div>
    </div>
</div>

<!-- Available Variables Card -->
<div class="card">
    <div class="card-header">Available Template Variables</div>
    <div style="padding: 20px;">
        <p style="color: #666; margin-bottom: 15px;">Use these variables in your templates. They will be automatically replaced with actual data:</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <div>
                <h4 style="margin: 0 0 10px 0; color: #667eea;">Customer Variables</h4>
                <ul style="list-style: none; padding: 0; color: #666; font-family: monospace; font-size: 13px;">
                    <li>• {customer_name}</li>
                    <li>• {customer_email}</li>
                    <li>• {customer_phone}</li>
                </ul>
            </div>
            
            <div>
                <h4 style="margin: 0 0 10px 0; color: #667eea;">Booking Variables</h4>
                <ul style="list-style: none; padding: 0; color: #666; font-family: monospace; font-size: 13px;">
                    <li>• {booking_id}</li>
                    <li>• {check_in_date}</li>
                    <li>• {check_out_date}</li>
                    <li>• {num_guests}</li>
                </ul>
            </div>
            
            <div>
                <h4 style="margin: 0 0 10px 0; color: #667eea;">Room Variables</h4>
                <ul style="list-style: none; padding: 0; color: #666; font-family: monospace; font-size: 13px;">
                    <li>• {room_number}</li>
                    <li>• {room_type}</li>
                    <li>• {room_price}</li>
                </ul>
            </div>
            
            <div>
                <h4 style="margin: 0 0 10px 0; color: #667eea;">Payment Variables</h4>
                <ul style="list-style: none; padding: 0; color: #666; font-family: monospace; font-size: 13px;">
                    <li>• {total_amount}</li>
                    <li>• {amount_paid}</li>
                    <li>• {invoice_number}</li>
                    <li>• {payment_method}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>
