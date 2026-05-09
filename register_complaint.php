<?php 
require_once 'includes/header.php'; 
require_once 'config/db_connect.php';

// Must be logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch active categories
$categories = $conn->query("SELECT id, category_name FROM complaint_categories WHERE is_active = 1");
// Fetch active areas
$areas = $conn->query("SELECT id, department_name, section_name, facility_spot FROM area_master WHERE is_active = 1");
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Register a New Complaint</h4>
            </div>
            <div class="card-body">
                <form action="actions/submit_complaint_action.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Complaint Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="Brief summary of the issue">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php while($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Priority</label>
                            <select name="priority" class="form-select" required>
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Location Area</label>
                            <select name="area_id" class="form-select" required>
                                <option value="">-- Select Area --</option>
                                <?php while($area = $areas->fetch_assoc()): ?>
                                    <option value="<?php echo $area['id']; ?>">
                                        <?php echo $area['department_name'] . " > " . $area['section_name'] . " > " . $area['facility_spot']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Exact Location Details</label>
                            <input type="text" name="exact_location" class="form-control" placeholder="e.g., Near the water cooler">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Provide detailed information about the issue..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Upload Evidence (Photo/Doc)</label>
                        <input type="file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                        <small class="text-muted">Max file size: 2MB. Allowed types: JPG, PNG, PDF.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Submit Complaint</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>