<?php
$pageTitle = "Employee Management";
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
        margin: 3% auto;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 700px;
        max-height: 85vh;
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
</style>

<div class="page-header">
    <h1>Employee Management</h1>
    <button onclick="showAddModal()" class="btn btn-primary">Add New Employee</button>
</div>

<div class="card">
    <div class="card-header">All Employees (<?php echo count($data['employees']); ?>)</div>
    <?php if (!empty($data['employees'])): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Role</th>
                    <th>Salary</th>
                    <th>Joining Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['employees'] as $employee): ?>
                    <tr>
                        <td><?php echo $employee['EMPLOYEE_ID']; ?></td>
                        <td><strong><?php echo htmlspecialchars($employee['FULL_NAME']); ?></strong></td>
                        <td><?php echo htmlspecialchars($employee['EMAIL']); ?></td>
                        <td><?php echo htmlspecialchars($employee['PHONE']); ?></td>
                        <td><?php echo htmlspecialchars($employee['DEPARTMENT']); ?></td>
                        <td><?php echo htmlspecialchars($employee['DESIGNATION']); ?></td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo ucfirst($employee['ROLE']); ?>
                            </span>
                        </td>
                        <td>₹<?php echo number_format($employee['SALARY'], 2); ?></td>
                        <td><?php echo date('d M Y', strtotime($employee['JOINING_DATE'])); ?></td>
                        <td>
                            <button onclick='editEmployee(<?php echo json_encode($employee); ?>)' class="btn btn-info">Edit</button>
                            <button onclick="deleteEmployee(<?php echo $employee['EMPLOYEE_ID']; ?>, '<?php echo htmlspecialchars($employee['FULL_NAME']); ?>')" class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding: 20px; text-align: center; color: #999;">No employees found</p>
    <?php endif; ?>
</div>

<!-- Add/Edit Employee Modal -->
<div id="employeeModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Add New Employee</h2>
        
        <form id="employeeForm" method="POST" action="<?php echo Router::url('/admin/employees'); ?>">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="employee_id" id="employeeId">
            
            <div id="userSection">
                <h3 style="margin: 20px 0 15px 0; color: #667eea;">User Account Details</h3>
                
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" id="username" required>
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" id="password" required>
                    <small style="color: #666;">Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="email" required>
                </div>
                
                <div class="form-group">
                    <label>User Role *</label>
                    <select name="role" id="role" required>
                        <option value="">Select Role</option>
                        <option value="<?php echo ROLE_ADMIN; ?>">Admin</option>
                        <option value="<?php echo ROLE_RECEPTIONIST; ?>">Receptionist</option>
                    </select>
                </div>
            </div>
            
            <h3 style="margin: 20px 0 15px 0; color: #667eea;">Employee Details</h3>
            
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" id="fullName" required>
            </div>
            
            <div class="form-group">
                <label>Phone *</label>
                <input type="tel" name="phone" id="phone" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department" id="department" required>
                        <option value="">Select Department</option>
                        <option value="Front Desk">Front Desk</option>
                        <option value="Housekeeping">Housekeeping</option>
                        <option value="Kitchen">Kitchen</option>
                        <option value="Management">Management</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Security">Security</option>
                        <option value="Accounts">Accounts</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Designation *</label>
                    <input type="text" name="designation" id="designation" required placeholder="e.g., Receptionist, Manager">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Salary (per month) *</label>
                    <input type="number" name="salary" id="salary" min="0" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Joining Date *</label>
                    <input type="date" name="joining_date" id="joiningDate" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Shift Timing</label>
                <select name="shift_timing" id="shiftTiming">
                    <option value="">Select Shift</option>
                    <option value="Morning (6 AM - 2 PM)">Morning (6 AM - 2 PM)</option>
                    <option value="Afternoon (2 PM - 10 PM)">Afternoon (2 PM - 10 PM)</option>
                    <option value="Night (10 PM - 6 AM)">Night (10 PM - 6 AM)</option>
                    <option value="General (9 AM - 6 PM)">General (9 AM - 6 PM)</option>
                    <option value="Flexible">Flexible</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="closeModal()" class="btn btn-info">Cancel</button>
                <button type="submit" class="btn btn-success">Save Employee</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Employee';
        document.getElementById('formAction').value = 'add';
        document.getElementById('employeeForm').reset();
        document.getElementById('userSection').style.display = 'block';
        document.getElementById('username').required = true;
        document.getElementById('password').required = true;
        document.getElementById('email').required = true;
        document.getElementById('role').required = true;
        document.getElementById('employeeModal').style.display = 'block';
    }
    
    function editEmployee(employee) {
        document.getElementById('modalTitle').textContent = 'Edit Employee';
        document.getElementById('formAction').value = 'update';
        document.getElementById('employeeId').value = employee.EMPLOYEE_ID;
        document.getElementById('fullName').value = employee.FULL_NAME;
        document.getElementById('phone').value = employee.PHONE;
        document.getElementById('department').value = employee.DEPARTMENT;
        document.getElementById('designation').value = employee.DESIGNATION;
        document.getElementById('salary').value = employee.SALARY;
        document.getElementById('joiningDate').value = employee.JOINING_DATE.split(' ')[0];
        document.getElementById('shiftTiming').value = employee.SHIFT_TIMING || '';
        
        // Hide user section for edit
        document.getElementById('userSection').style.display = 'none';
        document.getElementById('username').required = false;
        document.getElementById('password').required = false;
        document.getElementById('email').required = false;
        document.getElementById('role').required = false;
        
        document.getElementById('employeeModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('employeeModal').style.display = 'none';
    }
    
    function deleteEmployee(employeeId, employeeName) {
        if (confirm('Are you sure you want to delete employee ' + employeeName + '?\nThis will also delete their user account.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo Router::url('/admin/employees'); ?>';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'employee_id';
            idInput.value = employeeId;
            form.appendChild(idInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('employeeModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>