<?php
// ==========================================
// views/room/inventory.php
// ==========================================
$pageTitle = "Room Inventory";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Room Inventory</h1>
    <p>Complete overview of all rooms</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <?php
    $stats = [
        'total' => 0,
        'available' => 0,
        'occupied' => 0,
        'maintenance' => 0,
        'reserved' => 0
    ];
    
    foreach ($data['rooms'] as $room) {
        $stats['total']++;
        $status = strtolower($room['STATUS']);
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }
    ?>
    
    <div class="stat-card">
        <h3>Total Rooms</h3>
        <p><?php echo $stats['total']; ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Available</h3>
        <p style="color: #28a745;"><?php echo $stats['available']; ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Occupied</h3>
        <p style="color: #dc3545;"><?php echo $stats['occupied']; ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Maintenance</h3>
        <p style="color: #ffc107;"><?php echo $stats['maintenance']; ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Reserved</h3>
        <p style="color: #17a2b8;"><?php echo $stats['reserved']; ?></p>
    </div>
    
    <div class="stat-card">
        <h3>Occupancy Rate</h3>
        <p><?php echo $stats['total'] > 0 ? round(($stats['occupied'] / $stats['total']) * 100, 1) : 0; ?>%</p>
    </div>
</div>

<!-- Room Inventory by Floor -->
<?php
$roomsByFloor = [];
foreach ($data['rooms'] as $room) {
    $floor = $room['FLOOR_NUMBER'];
    if (!isset($roomsByFloor[$floor])) {
        $roomsByFloor[$floor] = [];
    }
    $roomsByFloor[$floor][] = $room;
}
ksort($roomsByFloor);
?>

<?php foreach ($roomsByFloor as $floor => $rooms): ?>
    <div class="card">
        <div class="card-header">Floor <?php echo $floor; ?> (<?php echo count($rooms); ?> rooms)</div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; padding: 20px;">
            <?php foreach ($rooms as $room): ?>
                <?php
                $bgColor = '#d4edda';
                $textColor = '#155724';
                $borderColor = '#c3e6cb';
                
                if ($room['STATUS'] === ROOM_OCCUPIED) {
                    $bgColor = '#f8d7da';
                    $textColor = '#721c24';
                    $borderColor = '#f5c6cb';
                } elseif ($room['STATUS'] === ROOM_MAINTENANCE) {
                    $bgColor = '#fff3cd';
                    $textColor = '#856404';
                    $borderColor = '#ffeaa7';
                } elseif ($room['STATUS'] === ROOM_RESERVED) {
                    $bgColor = '#d1ecf1';
                    $textColor = '#0c5460';
                    $borderColor = '#bee5eb';
                }
                ?>
                
                <div style="background: <?php echo $bgColor; ?>; border: 2px solid <?php echo $borderColor; ?>; border-radius: 8px; padding: 15px; text-align: center; cursor: pointer; transition: transform 0.2s;" 
                     onclick="showRoomDetails(<?php echo htmlspecialchars(json_encode($room)); ?>)"
                     onmouseover="this.style.transform='scale(1.05)'" 
                     onmouseout="this.style.transform='scale(1)'">
                    <div style="font-size: 24px; font-weight: bold; color: <?php echo $textColor; ?>; margin-bottom: 5px;">
                        <?php echo $room['ROOM_NUMBER']; ?>
                    </div>
                    <div style="font-size: 12px; color: <?php echo $textColor; ?>; margin-bottom: 5px;">
                        <?php echo $room['ROOM_TYPE']; ?>
                    </div>
                    <div style="font-size: 11px; font-weight: 600; color: <?php echo $textColor; ?>;">
                        <?php echo ucfirst($room['STATUS']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<!-- Room Details Modal -->
<div id="roomDetailsModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 10% auto; padding: 30px; border-radius: 10px; width: 90%; max-width: 600px;">
        <span onclick="closeRoomDetails()" style="float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2 style="margin-bottom: 20px;">Room Details</h2>
        <div id="roomDetailsContent"></div>
        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button onclick="closeRoomDetails()" class="btn btn-info">Close</button>
        </div>
    </div>
</div>

<script>
    function showRoomDetails(room) {
        const details = `
            <div style="line-height: 2;">
                <p><strong>Room Number:</strong> ${room.ROOM_NUMBER}</p>
                <p><strong>Type:</strong> ${room.ROOM_TYPE}</p>
                <p><strong>Floor:</strong> ${room.FLOOR_NUMBER}</p>
                <p><strong>Max Occupancy:</strong> ${room.MAX_OCCUPANCY} guests</p>
                <p><strong>Tariff:</strong> ${room.TARIFF_NAME}</p>
                <p><strong>Price per Night:</strong> ₹${parseFloat(room.BASE_PRICE).toFixed(2)}</p>
                <p><strong>Status:</strong> <span class="badge badge-info">${room.STATUS}</span></p>
                ${room.AMENITIES ? `<p><strong>Amenities:</strong> ${room.AMENITIES}</p>` : ''}
                ${room.DESCRIPTION ? `<p><strong>Description:</strong> ${room.DESCRIPTION}</p>` : ''}
            </div>
        `;
        document.getElementById('roomDetailsContent').innerHTML = details;
        document.getElementById('roomDetailsModal').style.display = 'block';
    }
    
    function closeRoomDetails() {
        document.getElementById('roomDetailsModal').style.display = 'none';
    }
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>