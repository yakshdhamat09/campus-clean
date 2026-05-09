<?php 
require_once 'includes/header.php'; 
require_once 'config/db_connect.php';

// Kick out anyone who isn't logged in OR isn't a Supervisor
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: dashboard.php");
    exit();
}

// Fetch existing categories from the database
$result = $conn->query("SELECT * FROM complaint_categories ORDER BY id DESC");
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Add New Category</h5>
            </div>
            <div class="card-body">
                <form action="actions/add_category_action.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name" class="form-control" required placeholder="e.g., Blocked Drain">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Save Category</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Existing Categories (Waste & Cleanliness)</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td>
                                <?php if($row['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>