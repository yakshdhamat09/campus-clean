<?php
// Start the session on every page that includes the header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" <?php if(isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') echo 'data-bs-theme="dark"'; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clean Campus Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Clean Campus</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="register_complaint.php">📝 New Complaint</a>
                    </li>
                    <?php if($_SESSION['role_id'] == 3): // Only Supervisors see Master Data ?>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_areas.php">Areas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-info fw-bold" href="reports.php">Reports</a>
                    </li>
                    <?php endif; ?>
                    
                </ul>

                <div class="d-flex text-white align-items-center">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="actions/toggle_theme.php" class="btn btn-sm btn-outline-light me-2">🌓 Theme</a>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">