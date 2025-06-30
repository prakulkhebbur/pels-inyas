<?php
use Dotenv\Dotenv;

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Handle participant deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_participant'])) {
    $participant_id = intval($_POST['participant_id']);
    
    if ($participant_id > 0) {
        $connection = mysqli_connect($_ENV["DBHOST"], $_ENV["DBUSER"], $_ENV["DBPASS"], $_ENV["DBNAME"]);
        
        if ($connection) {
            $stmt = $connection->prepare("UPDATE participents SET status = 'inactive' WHERE id = ?");
            $stmt->bind_param("i", $participant_id);
            
            if ($stmt->execute()) {
                $success_message = "Participant deleted successfully!";
            } else {
                $error_message = "Error deleting participant.";
            }
            
            $stmt->close();
            mysqli_close($connection);
        }
    }
}

// Fetch participants
$connection = mysqli_connect($_ENV["DBHOST"], $_ENV["DBUSER"], $_ENV["DBPASS"], $_ENV["DBNAME"]);
$participants = [];

if ($connection) {
    $query = "SELECT * FROM participents WHERE status = 'active' ORDER BY id DESC";
    $result = mysqli_query($connection, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $participants[] = $row;
        }
    }
    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
            background: rgba(255,255,255,0.1);
            padding: 15px 25px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .content {
            padding: 30px;
        }

        .actions {
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: left;
            font-weight: 600;
            font-size: 16px;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.3s ease;
        }

        tr:hover td {
            background-color: #f8f9ff;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .role-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-student {
            background: #e3f2fd;
            color: #1976d2;
        }

        .role-teacher {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .role-admin {
            background: #fff3e0;
            color: #f57c00;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        .email-icon {
            color: #667eea;
            margin-right: 8px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s;
        }

        .close:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .modal-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #fafafa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background-color: white;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .form-error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .stats {
                flex-direction: column;
                gap: 15px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 12px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Participants Dashboard</h1>
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($participants); ?></span>
                    <span>Total Participants</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo count(array_filter($participants, function($p) { return $p['role'] === 'student'; })); ?></span>
                    <span>Students</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo count(array_filter($participants, function($p) { return $p['role'] === 'teacher'; })); ?></span>
                    <span>Teachers</span>
                </div>
            </div>
        </div>

        <div class="content">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="actions">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="send_emails" class="btn btn-primary">
                        📧 Send Email to All
                    </button>
                </form>
                <button onclick="exportToCSV()" class="btn btn-success">
                    📊 Export CSV
                </button>
                <button onclick="openModal()" class="btn btn-warning">
                    ➕ Add Participant
                </button>
            </div>

            <div class="table-container">
                <?php if (empty($participants)): ?>
                    <div class="empty-state">
                        <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;">👥</div>
                        <h3>No participants found</h3>
                        <p>Start by adding some participants to see them here.</p>
                    </div>
                <?php else: ?>
                    <table id="participantsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participant['id']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['name']); ?></td>
                                    <td>
                                        <span class="email-icon">📧</span>
                                        <?php echo htmlspecialchars($participant['email']); ?>
                                    </td>
                                    <td>
                                        <span class="role-badge role-<?php echo htmlspecialchars($participant['role']); ?>">
                                            <?php echo htmlspecialchars($participant['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick="editParticipant(<?php echo $participant['id']; ?>, '<?php echo addslashes($participant['name']); ?>', '<?php echo addslashes($participant['email']); ?>', '<?php echo $participant['role']; ?>')"
                                                class="btn btn-success" style="font-size: 12px; padding: 6px 12px; margin-right: 5px;">
                                            ✏️ Edit
                                        </button>
                                        <a href="mailto:<?php echo htmlspecialchars($participant['email']); ?>" 
                                           class="btn btn-primary" style="font-size: 12px; padding: 6px 12px; margin-right: 5px;">
                                            📧 Email
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $participant['id']; ?>, '<?php echo addslashes($participant['name']); ?>')"
                                                class="btn btn-danger" style="font-size: 12px; padding: 6px 12px;">
                                            🗑️ Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Participant Modal -->
    <div id="editParticipantModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Edit Participant</h2>
                <button class="close" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editParticipantForm" method="POST">
                    <input type="hidden" name="edit_participant" value="1">
                    <input type="hidden" name="participant_id" id="editParticipantId">
                    
                    <div class="form-group">
                        <label for="editName">Full Name *</label>
                        <input type="text" id="editName" name="name" required placeholder="Enter participant's full name">
                        <div class="form-error" id="editNameError">Please enter a valid name</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editEmail">Email Address *</label>
                        <input type="email" id="editEmail" name="email" required placeholder="Enter email address">
                        <div class="form-error" id="editEmailError">Please enter a valid email address</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editRole">Role *</label>
                        <select id="editRole" name="role" required>
                            <option value="">Select a role</option>
                            <option value="student">👨‍🎓 Student</option>
                            <option value="teacher">👨‍🏫 Teacher</option>
                            <option value="admin">👨‍💼 Admin</option>
                        </select>
                        <div class="form-error" id="editRoleError">Please select a role</div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" onclick="closeEditModal()" class="btn btn-cancel">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            ✅ Update Participant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🗑️ Delete Participant</h2>
                <button class="close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteParticipantName"></strong>?</p>
                <p style="color: #dc3545; font-size: 14px; margin-top: 10px;">This action cannot be undone.</p>
                
                <form id="deleteForm" method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="delete_participant" value="1">
                    <input type="hidden" name="participant_id" id="deleteParticipantId">
                    
                    <div class="form-actions">
                        <button type="button" onclick="closeDeleteModal()" class="btn btn-cancel">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            🗑️ Delete Participant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Participant Modal -->
    <div id="addParticipantModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>➕ Add New Participant</h2>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="participantForm" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required placeholder="Enter participant's full name">
                        <div class="form-error" id="nameError">Please enter a valid name</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required placeholder="Enter email address">
                        <div class="form-error" id="emailError">Please enter a valid email address</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="">Select a role</option>
                            <option value="student">👨‍🎓 Student</option>
                            <option value="teacher">👨‍🏫 Teacher</option>
                            <option value="admin">👨‍💼 Admin</option>
                        </select>
                        <div class="form-error" id="roleError">Please select a role</div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" onclick="closeModal()" class="btn btn-cancel">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            ✅ Add Participant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal Functions
        function openModal() {
            document.getElementById('addParticipantModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeModal() {
            document.getElementById('addParticipantModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
            document.getElementById('participantForm').reset();
            clearErrors();
        }

        // Delete Modal Functions
        function confirmDelete(participantId, participantName) {
            document.getElementById('deleteParticipantId').value = participantId;
            document.getElementById('deleteParticipantName').textContent = participantName;
            document.getElementById('deleteModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Edit Modal Functions
        function editParticipant(participantId, participantName, participantEmail, participantRole) {
            document.getElementById('editParticipantId').value = participantId;
            document.getElementById('editName').value = participantName;
            document.getElementById('editEmail').value = participantEmail;
            document.getElementById('editRole').value = participantRole;
            document.getElementById('editParticipantModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editParticipantModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('editParticipantForm').reset();
            clearEditErrors();
        }

        function clearEditErrors() {
            document.querySelectorAll('#editParticipantModal .form-error').forEach(error => {
                error.style.display = 'none';
            });
        }

        function clearErrors() {
            document.querySelectorAll('.form-error').forEach(error => {
                error.style.display = 'none';
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addParticipantModal');
            const deleteModal = document.getElementById('deleteModal');
            const editModal = document.getElementById('editParticipantModal');
            
            if (event.target === addModal) {
                closeModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        }

        // Form submission with validation
        document.getElementById('participantForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const role = document.getElementById('role').value;
            
            let isValid = true;
            clearErrors();
            
            // Name validation
            if (name.length < 2) {
                document.getElementById('nameError').style.display = 'block';
                isValid = false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').style.display = 'block';
                isValid = false;
            }
            
            // Role validation
            if (!role) {
                document.getElementById('roleError').style.display = 'block';
                isValid = false;
            }
            
            if (isValid) {
                // Show loading state
                const form = document.getElementById('participantForm');
                form.classList.add('loading');
                
                // Submit form via AJAX
                const formData = new FormData();
                formData.append('name', name);
                formData.append('email', email);
                formData.append('role', role);
                
                fetch('app.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    form.classList.remove('loading');
                    
                    // Check if successful (you might want to return JSON from app.php)
                    if (data.includes('Database connected successfully')) {
                        closeModal();
                        // Refresh the page to show new participant
                        window.location.reload();
                    } else {
                        alert('Error adding participant. Please try again.');
                    }
                })
                .catch(error => {
                    form.classList.remove('loading');
                    alert('Network error. Please try again.');
                    console.error('Error:', error);
                });
            }
        });

        // Edit form submission with validation
        document.getElementById('editParticipantForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('editName').value.trim();
            const email = document.getElementById('editEmail').value.trim();
            const role = document.getElementById('editRole').value;
            const participantId = document.getElementById('editParticipantId').value;
            
            let isValid = true;
            clearEditErrors();
            
            // Name validation
            if (name.length < 2) {
                document.getElementById('editNameError').style.display = 'block';
                isValid = false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('editEmailError').style.display = 'block';
                isValid = false;
            }
            
            // Role validation
            if (!role) {
                document.getElementById('editRoleError').style.display = 'block';
                isValid = false;
            }
            
            if (isValid) {
                // Show loading state
                const form = document.getElementById('editParticipantForm');
                form.classList.add('loading');
                
                // Submit form via AJAX
                const formData = new FormData();
                formData.append('edit_participant', '1');
                formData.append('participant_id', participantId);
                formData.append('name', name);
                formData.append('email', email);
                formData.append('role', role);
                
                fetch('app.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    form.classList.remove('loading');
                    
                    // Check if successful
                    if (data.includes('Participant updated successfully')) {
                        closeEditModal();
                        // Refresh the page to show updated participant
                        window.location.reload();
                    } else {
                        alert('Error updating participant. Please try again.');
                    }
                })
                .catch(error => {
                    form.classList.remove('loading');
                    alert('Network error. Please try again.');
                    console.error('Error:', error);
                });
            }
        });

        function exportToCSV() {
            const table = document.getElementById('participantsTable');
            if (!table) {
                alert('No data to export');
                return;
            }

            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [];
                const cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length - 1; j++) { // Exclude actions column
                    let cellText = cols[j].textContent.trim();
                    cellText = cellText.replace(/"/g, '""'); // Escape quotes
                    row.push('"' + cellText + '"');
                }
                csv.push(row.join(','));
            }

            const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            downloadLink.download = 'participants_' + new Date().toISOString().split('T')[0] + '.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        // Auto-refresh every 30 seconds
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
