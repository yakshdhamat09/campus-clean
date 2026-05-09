<?php 
require_once 'includes/header.php'; 

// If already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">System Login</h4>
            </div>
            <div class="card-body">
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            echo $_SESSION['error']; 
                            unset($_SESSION['error']); // Clear error after showing
                        ?>
                    </div>
                <?php endif; ?>

                <form action="actions/login_action.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>