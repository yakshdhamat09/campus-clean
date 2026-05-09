<?php 
require_once 'includes/header.php'; 
require_once 'config/db_connect.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$complaint_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

// Fetch Main Complaint Details
$sql = "SELECT c.*, cat.category_name, a.department_name, a.section_name, a.facility_spot, s.status_name,
               u1.name as complainant_name, u2.name as staff_name
        FROM complaints c
        JOIN complaint_categories cat ON c.category_id = cat.id
        JOIN area_master a ON c.area_id = a.id
        JOIN status_master s ON c.status_id = s.id
        JOIN users u1 ON c.complainant_id = u1.id
        LEFT JOIN users u2 ON c.assigned_staff_id = u2.id
        WHERE c.id = $complaint_id";
$result = $conn->query($sql);

if($result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Complaint not found or access denied.</div>";
    require_once 'includes/footer.php';
    exit();
}
$complaint = $result->fetch_assoc();

// Fetch Attachments
$att_result = $conn->query("SELECT * FROM complaint_attachments WHERE complaint_id = $complaint_id");

// Fetch History
$hist_result = $conn->query("SELECT h.*, s.status_name, u.name as updater_name 
                             FROM complaint_history h 
                             JOIN status_master s ON h.status_id = s.id
                             JOIN users u ON h.updated_by = u.id
                             WHERE h.complaint_id = $complaint_id ORDER BY h.timestamp ASC");

// Fetch Staff for assignment dropdown (Only needed if user is a Supervisor)
$staff_list = [];
if($role_id == 3) {
    $staff_query = $conn->query("SELECT id, name FROM users WHERE role_id = 2");
    while($staff = $staff_query->fetch_assoc()) {
        $staff_list[] = $staff;
    }
}
?>

<div class="row mt-3">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ticket: <?php echo $complaint['tracking_id']; ?></h5>
                <span class="badge bg-primary fs-6"><?php echo $complaint['status_name']; ?></span>
            </div>
            <div class="card-body">
                <h4 class="text-primary"><?php echo htmlspecialchars($complaint['title']); ?></h4>
                <p class="text-muted mb-4">Submitted by <strong><?php echo $complaint['complainant_name']; ?></strong> on <?php echo date("d-M-Y h:i A", strtotime($complaint['created_at'])); ?></p>
                
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Category:</div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($complaint['category_name']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Location:</div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($complaint['department_name'] . ' > ' . $complaint['section_name'] . ' > ' . $complaint['facility_spot']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Exact Details:</div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($complaint['exact_location']); ?></div>
                </div>
                <div class="row mb-4">
                    <div class="col-sm-4 fw-bold">Description:</div>
                    <div class="col-sm-8"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></div>
                </div>

                <h5 class="border-bottom pb-2">Evidence / Attachments</h5>
                <div class="row mt-3">
                    <?php while($att = $att_result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border-light">
                                <div class="card-body p-2 text-center">
                                    <a href="<?php echo $att['file_path']; ?>" target="_blank">
                                        <img src="<?php echo $att['file_path']; ?>" class="img-fluid rounded" alt="Evidence" style="max-height: 200px; object-fit: cover;">
                                    </a>
                                    <p class="mt-2 mb-0 small text-muted"><?php echo $att['upload_type']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        
        <?php if($role_id == 3): ?>
        <div class="card shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning text-dark fw-bold">Supervisor Action: Assign Staff</div>
            <div class="card-body">
                <form action="actions/assign_staff_action.php" method="POST">
                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                    <div class="mb-3">
                        <select name="staff_id" class="form-select" required>
                            <option value="">-- Select Staff Member --</option>
                            <?php foreach($staff_list as $staff): ?>
                                <option value="<?php echo $staff['id']; ?>" <?php echo ($complaint['assigned_staff_id'] == $staff['id']) ? 'selected' : ''; ?>>
                                    <?php echo $staff['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if($complaint['status_name'] == 'Reopened'): ?>
                    <div class="mb-3 form-check text-danger border border-danger p-2 rounded">
                        <input type="checkbox" class="form-check-input ms-1" id="reopenApprove" name="reopen_approve" required>
                        <label class="form-check-label ms-4 fw-bold" for="reopenApprove">I officially approve this Reopened ticket for reassignment.</label>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-warning w-100">Assign / Reassign</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if($role_id == 2 && ($complaint['status_name'] == 'Assigned' || $complaint['status_name'] == 'In Progress')): ?>
        <div class="card shadow-sm mb-4 border-success">
            <div class="card-header bg-success text-white fw-bold">Staff Action: Resolve Issue</div>
            <div class="card-body">
                <form action="actions/resolve_complaint_action.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Action Taken / Remarks</label>
                        <textarea name="remark" class="form-control form-control-sm" rows="2" required placeholder="Describe how you fixed it..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Upload Action Proof (Photo)</label>
                        <input type="file" name="action_proof" class="form-control form-control-sm" accept=".jpg,.jpeg,.png" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">Mark as Resolved</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Complaint Timeline</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php while($hist = $hist_result->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between w-100">
                            <h6 class="mb-1 fw-bold"><?php echo $hist['status_name']; ?></h6>
                            <small class="text-muted"><?php echo date("d-M H:i", strtotime($hist['timestamp'])); ?></small>
                        </div>
                        <p class="mb-1 small"><?php echo htmlspecialchars($hist['remark']); ?></p>
                        <small class="text-muted">By: <?php echo $hist['updater_name']; ?></small>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>