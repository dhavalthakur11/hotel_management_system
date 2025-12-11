<?php
// ==========================================
// views/admin/tariffs.php
// ==========================================
$pageTitle = "Tariff Management";
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
        margin: 10% auto;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
</style>

<div class="page-header">
    <h1>Tariff Management</h1>
    <button onclick="showAddModal()" class="btn btn-primary">Add New Tariff</button>
</div>

<div class="card">
    <div class="card-header">All Tariffs (<?php echo count($data['tariffs']); ?>)</div>
    <?php if (!empty($data['tariffs'])): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tariff Name</th>
                    <th>Base Price (per night)</th>
                    <th>Description</th>
                    <th>Created On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['tariffs'] as $tariff): ?>
                    <tr>
                        <td><?php echo $tariff['TARIFF_ID']; ?></td>
                        <td><strong><?php echo htmlspecialchars($tariff['TARIFF_NAME']); ?></strong></td>
                        <td><strong style="color: #28a745;">₹<?php echo number_format($tariff['BASE_PRICE'], 2); ?></strong></td>
                        <td><?php echo htmlspecialchars($tariff['DESCRIPTION'] ?? 'N/A'); ?></td>
                        <td><?php echo date('d M Y', strtotime($tariff['CREATED_AT'])); ?></td>
                        <td>
                            <button onclick='editTariff(<?php echo json_encode($tariff); ?>)' class="btn btn-info">Edit</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No tariffs found</p>
    <?php endif; ?>
</div>

<!-- Add/Edit Tariff Modal -->
<div id="tariffModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Add New Tariff</h2>
        
        <form id="tariffForm" method="POST" action="<?php echo Router::url('/admin/tariffs'); ?>">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="tariff_id" id="tariffId">
            
            <div class="form-group">
                <label>Tariff Name *</label>
                <input type="text" name="tariff_name" id="tariffName" required placeholder="e.g., Standard Room, Deluxe Room">
            </div>
            
            <div class="form-group">
                <label>Base Price (per night) *</label>
                <input type="number" name="base_price" id="basePrice" min="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" rows="3" placeholder="Tariff description..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="closeModal()" class="btn btn-info">Cancel</button>
                <button type="submit" class="btn btn-success">Save Tariff</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Tariff';
        document.getElementById('formAction').value = 'add';
        document.getElementById('tariffForm').reset();
        document.getElementById('tariffModal').style.display = 'block';
    }
    
    function editTariff(tariff) {
        document.getElementById('modalTitle').textContent = 'Edit Tariff';
        document.getElementById('formAction').value = 'update';
        document.getElementById('tariffId').value = tariff.TARIFF_ID;
        document.getElementById('tariffName').value = tariff.TARIFF_NAME;
        document.getElementById('basePrice').value = tariff.BASE_PRICE;
        document.getElementById('description').value = tariff.DESCRIPTION || '';
        document.getElementById('tariffModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('tariffModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('tariffModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>
