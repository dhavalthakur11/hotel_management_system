<?php
$pageTitle = "Room Management";
require_once __DIR__ . '/../shared/header.php';
?>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover {
        color: #000;
    }
    
    .filter-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
</style>

<div class="page-header">
    <h1>Room Management</h1>
    <button onclick="showAddModal()" class="btn btn-primary">Add New Room</button>
</div>

<!-- Filter Section -->
<div class="card">
    <div class="card-header">Filter Rooms</div>
    <form method="GET" action="<?php echo Router::url('/admin/rooms'); ?>">
        <div class="filter-section">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="<?php echo ROOM_AVAILABLE; ?>" <?php echo ($_GET['status'] ?? '') === ROOM_AVAILABLE ? 'selected' : ''; ?>>Available</option>
                    <option value="<?php echo ROOM_OCCUPIED; ?>" <?php echo ($_GET['status'] ?? '') === ROOM_OCCUPIED ? 'selected' : ''; ?>>Occupied</option>
                    <option value="<?php echo ROOM_MAINTENANCE; ?>" <?php echo ($_GET['status'] ?? '') === ROOM_MAINTENANCE ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="<?php echo ROOM_RESERVED; ?>" <?php echo ($_GET['status'] ?? '') === ROOM_RESERVED ? 'selected' : ''; ?>>Reserved</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Room Type</label>
                <select name="room_type">
                    <option value="">All Types</option>
                    <option value="Standard" <?php echo ($_GET['room_type'] ?? '') === 'Standard' ? 'selected' : ''; ?>>Standard</option>
                    <option value="Deluxe" <?php echo ($_GET['room_type'] ?? '') === 'Deluxe' ? 'selected' : ''; ?>>Deluxe</option>
                    <option value="Suite" <?php echo ($_GET['room_type'] ?? '') === 'Suite' ? 'selected' : ''; ?>>Suite</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Floor</label>
                <select name="floor">
                    <option value="">All Floors</option>
                    <?php for($i = 1; $i <= 10; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($_GET['floor'] ?? '') == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="<?php echo Router::url('/admin/rooms'); ?>" class="btn btn-info">Clear</a>
            </div>
        </div>
    </form>
</div>

<!-- Rooms List -->
<div class="card">
    <div class="card-header">All Rooms (<?php echo count($data['rooms']); ?>)</div>
    <?php if (!empty($data['rooms'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Room No</th>
                    <th>Type</th>
                    <th>Floor</th>
                    <th>Capacity</th>
                    <th>Tariff</th>
                    <th>Price/Night</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['rooms'] as $room): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($room['ROOM_NUMBER']); ?></strong></td>
                        <td><?php echo htmlspecialchars($room['ROOM_TYPE']); ?></td>
                        <td><?php echo $room['FLOOR_NUMBER']; ?></td>
                        <td><?php echo $room['MAX_OCCUPANCY']; ?> guests</td>
                        <td><?php echo htmlspecialchars($room['TARIFF_NAME']); ?></td>
                        <td><strong>₹<?php echo number_format($room['BASE_PRICE'], 2); ?></strong></td>
                        <td>
                            <?php
                            $statusClass = 'badge-success';
                            if ($room['STATUS'] === ROOM_OCCUPIED) $statusClass = 'badge-danger';
                            if ($room['STATUS'] === ROOM_MAINTENANCE) $statusClass = 'badge-warning';
                            if ($room['STATUS'] === ROOM_RESERVED) $statusClass = 'badge-info';
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo ucfirst($room['STATUS']); ?>
                            </span>
                        </td>
                        <td>
                            <button onclick='editRoom(<?php echo json_encode($room); ?>)' class="btn btn-info">Edit</button>
                            <button onclick="deleteRoom(<?php echo $room['ROOM_ID']; ?>, '<?php echo htmlspecialchars($room['ROOM_NUMBER']); ?>')" class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No rooms found</p>
    <?php endif; ?>
</div>

<!-- Add/Edit Room Modal -->
<div id="roomModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Add New Room</h2>
        
        <form id="roomForm" method="POST" action="<?php echo Router::url('/admin/rooms'); ?>">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="room_id" id="roomId">
            
            <div class="form-group">
                <label>Room Number *</label>
                <input type="text" name="room_number" id="roomNumber" required>
            </div>
            
            <div class="form-group">
                <label>Room Type *</label>
                <select name="room_type" id="roomType" required>
                    <option value="">Select Type</option>
                    <option value="Standard">Standard</option>
                    <option value="Deluxe">Deluxe</option>
                    <option value="Suite">Suite</option>
                    <option value="Executive">Executive</option>
                    <option value="Presidential">Presidential</option>
                </select>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Floor Number *</label>
                    <input type="number" name="floor_number" id="floorNumber" min="1" max="50" required>
                </div>
                
                <div class="form-group">
                    <label>Max Occupancy *</label>
                    <input type="number" name="max_occupancy" id="maxOccupancy" min="1" max="10" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Tariff *</label>
                <select name="tariff_id" id="tariffId" required>
                    <option value="">Select Tariff</option>
                    <?php foreach ($data['tariffs'] as $tariff): ?>
                        <option value="<?php echo $tariff['TARIFF_ID']; ?>">
                            <?php echo htmlspecialchars($tariff['TARIFF_NAME']); ?> - ₹<?php echo number_format($tariff['BASE_PRICE'], 2); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" id="roomStatus" required>
                    <option value="<?php echo ROOM_AVAILABLE; ?>">Available</option>
                    <option value="<?php echo ROOM_OCCUPIED; ?>">Occupied</option>
                    <option value="<?php echo ROOM_MAINTENANCE; ?>">Maintenance</option>
                    <option value="<?php echo ROOM_RESERVED; ?>">Reserved</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Amenities</label>
                <textarea name="amenities" id="amenities" rows="3" placeholder="AC, WiFi, TV, Mini Bar, etc."></textarea>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" rows="3" placeholder="Room description..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="closeModal()" class="btn btn-info">Cancel</button>
                <button type="submit" class="btn btn-success">Save Room</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Room';
        document.getElementById('formAction').value = 'add';
        document.getElementById('roomForm').reset();
        document.getElementById('roomModal').style.display = 'block';
    }
    
    function editRoom(room) {
        document.getElementById('modalTitle').textContent = 'Edit Room';
        document.getElementById('formAction').value = 'update';
        document.getElementById('roomId').value = room.ROOM_ID;
        document.getElementById('roomNumber').value = room.ROOM_NUMBER;
        document.getElementById('roomType').value = room.ROOM_TYPE;
        document.getElementById('floorNumber').value = room.FLOOR_NUMBER;
        document.getElementById('maxOccupancy').value = room.MAX_OCCUPANCY;
        document.getElementById('tariffId').value = room.TARIFF_ID;
        document.getElementById('roomStatus').value = room.STATUS;
        document.getElementById('amenities').value = room.AMENITIES || '';
        document.getElementById('description').value = room.DESCRIPTION || '';
        document.getElementById('roomModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('roomModal').style.display = 'none';
    }
    
    function deleteRoom(roomId, roomNumber) {
        if (confirm('Are you sure you want to delete Room ' + roomNumber + '?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo Router::url('/admin/rooms'); ?>';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'room_id';
            idInput.value = roomId;
            form.appendChild(idInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('roomModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>