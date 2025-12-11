<?php
// ==========================================
// views/admin/reports.php
// ==========================================
$pageTitle = "Reports Dashboard";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Reports Dashboard</h1>
    <p>Generate and view various reports</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    <!-- Occupancy Report Card -->
    <div class="card">
        <div style="text-align: center; padding: 20px;">
            <div style="font-size: 48px; margin-bottom: 15px;">📊</div>
            <h2 style="margin-bottom: 10px;">Occupancy Report</h2>
            <p style="color: #666; margin-bottom: 20px;">View room occupancy statistics and trends</p>
            <a href="<?php echo Router::url('/report/occupancy'); ?>" class="btn btn-primary" style="width: 100%;">
                View Occupancy Report
            </a>
        </div>
    </div>
    
    <!-- Revenue Report Card -->
    <div class="card">
        <div style="text-align: center; padding: 20px;">
            <div style="font-size: 48px; margin-bottom: 15px;">💰</div>
            <h2 style="margin-bottom: 10px;">Revenue Report</h2>
            <p style="color: #666; margin-bottom: 20px;">Track revenue and payment statistics</p>
            <a href="<?php echo Router::url('/report/revenue'); ?>" class="btn btn-success" style="width: 100%;">
                View Revenue Report
            </a>
        </div>
    </div>
    
    <!-- Booking Report Card -->
    <div class="card">
        <div style="text-align: center; padding: 20px;">
            <div style="font-size: 48px; margin-bottom: 15px;">📅</div>
            <h2 style="margin-bottom: 10px;">Booking Report</h2>
            <p style="color: #666; margin-bottom: 20px;">Analyze booking patterns and trends</p>
            <a href="<?php echo Router::url('/admin/dashboard'); ?>" class="btn btn-info" style="width: 100%;">
                View Bookings
            </a>
        </div>
    </div>
    
    <!-- Customer Report Card -->
    <div class="card">
        <div style="text-align: center; padding: 20px;">
            <div style="font-size: 48px; margin-bottom: 15px;">👥</div>
            <h2 style="margin-bottom: 10px;">Customer Report</h2>
            <p style="color: #666; margin-bottom: 20px;">View customer statistics and insights</p>
            <a href="<?php echo Router::url('/admin/customers'); ?>" class="btn btn-warning" style="width: 100%;">
                View Customers
            </a>
        </div>
    </div>
    
    <!-- Room Report Card -->
    <div class="card">
        <div style="text-align: center; padding: 20px;">
            <div style="font-size: 48px; margin-bottom: 15px;">🏨</div>
            <h2 style="margin-bottom: 10px;">Room Inventory</h2>
            <p style="color: #666; margin-bottom: 20px;">Check room status and availability</p>
            <a href="<?php echo Router::url('/admin/rooms'); ?>" class="btn btn-primary" style="width: 100%;">
                View Rooms
            </a>
        </div>
    </div>
    
    <!-- Audit Log Card -->
    <div class="card">
        <div style="text-align: center; padding: 20px;">
            <div style="font-size: 48px; margin-bottom: 15px;">🔒</div>
            <h2 style="margin-bottom: 10px;">Audit Logs</h2>
            <p style="color: #666; margin-bottom: 20px;">View system activity and user actions</p>
            <a href="<?php echo Router::url('/audit/logs'); ?>" class="btn btn-danger" style="width: 100%;">
                View Audit Logs
            </a>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 30px;">
    <div class="card-header">Quick Export Options</div>
    <div style="padding: 20px;">
        <p style="color: #666; margin-bottom: 15px;">Export reports in various formats:</p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="btn btn-success" onclick="alert('PDF export feature coming soon!')">
                📄 Export to PDF
            </button>
            <button class="btn btn-info" onclick="alert('Excel export feature coming soon!')">
                📊 Export to Excel
            </button>
            <button class="btn btn-warning" onclick="alert('CSV export feature coming soon!')">
                📋 Export to CSV
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>
