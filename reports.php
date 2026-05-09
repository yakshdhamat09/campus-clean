<?php 
require_once 'includes/header.php'; 
require_once 'config/db_connect.php';

// Kick out anyone who isn't a Supervisor
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: dashboard.php");
    exit();
}

// Complex Query: Group complaints by Area and calculate trends
$sql = "SELECT a.department_name, a.section_name, a.facility_spot,
               COUNT(c.id) as total_complaints,
               SUM(CASE WHEN s.status_name IN ('Resolved', 'Closed') THEN 1 ELSE 0 END) as resolved_count,
               SUM(CASE WHEN s.status_name NOT IN ('Resolved', 'Closed') THEN 1 ELSE 0 END) as pending_count
        FROM area_master a
        LEFT JOIN complaints c ON a.id = c.area_id
        LEFT JOIN status_master s ON c.status_id = s.id
        GROUP BY a.id, a.department_name, a.section_name, a.facility_spot
        ORDER BY total_complaints DESC, a.department_name ASC";

$result = $conn->query($sql);
?>

<div class="row mt-4">
    <div class="col-md-12">
        <h3 class="mb-4 text-info">System Analytics</h3>
        
        <div class="card shadow-sm border-info">
            <div class="card-header bg-info text-dark fw-bold">
                Mandatory Report: Area-Wise Complaint Trends
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Location (Department > Section)</th>
                                <th>Total Complaints</th>
                                <th class="text-success">Resolved / Closed</th>
                                <th class="text-danger">Pending / Active</th>
                                <th>Resolution Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($result->num_rows > 0): 
                                while($row = $result->fetch_assoc()): 
                                    // Calculate resolution percentage safely
                                    $total = $row['total_complaints'];
                                    $resolved = $row['resolved_count'] ? $row['resolved_count'] : 0;
                                    $pending = $row['pending_count'] ? $row['pending_count'] : 0;
                                    $rate = ($total > 0) ? round(($resolved / $total) * 100) : 0;
                            ?>
                                <tr>
                                    <td class="text-start">
                                        <strong><?php echo htmlspecialchars($row['department_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['section_name'] . ' > ' . $row['facility_spot']); ?></small>
                                    </td>
                                    <td><span class="badge bg-secondary fs-6"><?php echo $total; ?></span></td>
                                    <td><span class="badge bg-success fs-6"><?php echo $resolved; ?></span></td>
                                    <td><span class="badge bg-danger fs-6"><?php echo $pending; ?></span></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $rate; ?>%;" aria-valuenow="<?php echo $rate; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $rate; ?>%</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr><td colspan="5" class="py-4">No area data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>