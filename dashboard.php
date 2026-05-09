<?php 
require_once 'includes/header.php'; 
require_once 'config/db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$role_id = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];

// 1. Build the Role-Based Query
$where_clause = "";
if ($role_id == 1) { // Complainant
    $where_clause = "WHERE c.complainant_id = " . $user_id;
} elseif ($role_id == 2) { // Staff
    $where_clause = "WHERE c.assigned_staff_id = " . $user_id;
} 
// If role_id is 3 (Supervisor), $where_clause stays empty so they see all records.

// 2. Fetch the data using JOINs
$sql = "SELECT c.id, c.tracking_id, c.title, c.priority, c.created_at, 
               cat.category_name, 
               a.department_name, a.section_name, a.facility_spot,
               s.status_name
        FROM complaints c
        JOIN complaint_categories cat ON c.category_id = cat.id
        JOIN area_master a ON c.area_id = a.id
        JOIN status_master s ON c.status_id = s.id
        $where_clause
        ORDER BY c.created_at DESC";

$result = $conn->query($sql);
?>

<div class="row mt-4">
    <div class="col-md-12">
        <h3 class="mb-4 text-primary">System Dashboard</h3>
        
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body">
                <h5 class="card-title text-secondary">Quick Track</h5>
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="trackingInput" class="form-control" placeholder="Enter Tracking ID (e.g., COMP-2026-001)">
                            <button class="btn btn-primary" id="trackBtn" type="button">Track via AJAX</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="trackResult" class="text-primary fw-bold mt-2 mt-md-0"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Complaint Tickets</h5>
                <?php if($role_id == 1): ?>
                    <a href="register_complaint.php" class="btn btn-sm btn-success">New Complaint</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tracking ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                
                                <?php 
                                    // SLA Highlighting Logic (8h Response / 54h Resolution)
                                    $created_time = strtotime($row['created_at']);
                                    $hours_passed = (time() - $created_time) / 3600;
                                    
                                    $row_class = "";
                                    if ($hours_passed > 54 && !in_array($row['status_name'], ['Resolved', 'Closed'])) {
                                        $row_class = "table-danger"; // Breached 54h Resolution
                                    } elseif ($hours_passed > 8 && $row['status_name'] == 'Submitted') {
                                        $row_class = "table-warning"; // Breached 8h Initial Response
                                    }
                                ?>
                                
                                <tr class="<?php echo $row_class; ?>">
                                    <td><a href="view_complaint.php?id=<?php echo $row['id']; ?>" class="text-decoration-none"><strong><?php echo $row['tracking_id']; ?></strong></a></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($row['department_name'] . ' > ' . $row['section_name'] . ' > ' . $row['facility_spot']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                            $p_color = 'bg-secondary';
                                            if($row['priority'] == 'Low') $p_color = 'bg-info';
                                            if($row['priority'] == 'Medium') $p_color = 'bg-primary';
                                            if($row['priority'] == 'High') $p_color = 'bg-warning text-dark';
                                            if($row['priority'] == 'Critical') $p_color = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $p_color; ?>"><?php echo $row['priority']; ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            $s_color = 'bg-secondary';
                                            if($row['status_name'] == 'Submitted') $s_color = 'bg-primary';
                                            if($row['status_name'] == 'Assigned') $s_color = 'bg-info text-dark';
                                            if($row['status_name'] == 'In Progress') $s_color = 'bg-warning text-dark';
                                            if($row['status_name'] == 'Resolved') $s_color = 'bg-success';
                                            if($row['status_name'] == 'Reopened') $s_color = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $s_color; ?>"><?php echo $row['status_name']; ?></span>
                                    </td>
                                    <td><?php echo date("d-M-Y H:i", strtotime($row['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No complaints found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#trackBtn').click(function() {
        var trackingId = $('#trackingInput').val().trim();
        var resultDiv = $('#trackResult');
        
        if(trackingId === "") {
            resultDiv.html("<span class='text-danger'>Please enter a Tracking ID.</span>");
            return;
        }

        resultDiv.html("<span class='text-muted'>Searching...</span>");

        $.ajax({
            url: 'api/track_complaint.php',
            type: 'GET',
            data: { tracking_id: trackingId },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    var c = response.data;
                    resultDiv.html(
                        "Status: <span class='badge bg-info text-dark'>" + c.status_name + "</span> | " + 
                        "Priority: " + c.priority + " | " + 
                        "Title: " + c.title
                    );
                } else {
                    resultDiv.html("<span class='text-danger'>" + response.message + "</span>");
                }
            },
            error: function() {
                resultDiv.html("<span class='text-danger'>Error connecting to API.</span>");
            }
        });
    });
});
</script>