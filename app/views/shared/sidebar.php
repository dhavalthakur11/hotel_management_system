<?php
// app/views/shared/sidebar.php
// Note: this file expects AuthController and Router classes and ROLE_* constants to be defined.
$userRole = AuthController::getUserRole();
?>
<style>
    .sidebar {
        width: 250px;
        background: white;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        padding: 20px 0;
    }
    
    .sidebar-menu {
        list-style: none;
    }
    
    .sidebar-menu li {
        margin-bottom: 5px;
    }
    
    .sidebar-menu a {
        display: block;
        padding: 12px 20px;
        color: #666;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: #f0f0f0;
        color: #667eea;
        border-left: 3px solid #667eea;
    }
</style>

<div class="sidebar">
    <ul class="sidebar-menu">
        <?php if ($userRole === ROLE_ADMIN): ?>
            <li><a href="<?php echo Router::url('/admin/dashboard'); ?>">Dashboard</a></li>
            <li><a href="<?php echo Router::url('/admin/rooms'); ?>">Rooms</a></li>
            <li><a href="<?php echo Router::url('/admin/employees'); ?>">Employees</a></li>
            <li><a href="<?php echo Router::url('/admin/customers'); ?>">Customers</a></li>
            <li><a href="<?php echo Router::url('/admin/tariffs'); ?>">Tariffs</a></li>
            <li><a href="<?php echo Router::url('/report/occupancy'); ?>">Occupancy Report</a></li>
            <li><a href="<?php echo Router::url('/report/revenue'); ?>">Revenue Report</a></li>
            <li><a href="<?php echo Router::url('/audit/logs'); ?>">Audit Logs</a></li>
            <li><a href="<?php echo Router::url('/feedback'); ?>">Feedback</a></li>
        <?php elseif ($userRole === ROLE_RECEPTIONIST): ?>
            <li><a href="<?php echo Router::url('/receptionist/dashboard'); ?>">Dashboard</a></li>
            <li><a href="<?php echo Router::url('/booking/create'); ?>">New Booking</a></li>
            <li><a href="<?php echo Router::url('/receptionist/checkin'); ?>">Check-in</a></li>
            <li><a href="<?php echo Router::url('/receptionist/checkout'); ?>">Check-out</a></li>
            <li><a href="<?php echo Router::url('/receptionist/rooms'); ?>">Rooms</a></li>
            <li><a href="<?php echo Router::url('/receptionist/invoices'); ?>">Invoices</a></li>
        <?php elseif ($userRole === ROLE_CUSTOMER): ?>
            <li><a href="<?php echo Router::url('/customer/dashboard'); ?>">Dashboard</a></li>
            <li><a href="<?php echo Router::url('/customer/rooms'); ?>">Browse Rooms</a></li>
            <li><a href="<?php echo Router::url('/customer/bookings'); ?>">My Bookings</a></li>
            <li><a href="<?php echo Router::url('/customer/invoices'); ?>">My Invoices</a></li>
            <li><a href="<?php echo Router::url('/feedback'); ?>">Feedback</a></li>
        <?php endif; ?>
    </ul>
</div>
