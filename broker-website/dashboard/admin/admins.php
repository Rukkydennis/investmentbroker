<?php
require_once "isadmin.php";
require_once "../../asset/config/userdata.php";

$msg = "";
$error = "";

// Handle New Admin Creation
if(isset($_POST['create_admin'])) {
    $username = $DB->real_escape_string($_POST['username']);
    $email = $DB->real_escape_string($_POST['email']);
    $fullname = $DB->real_escape_string($_POST['fullname']);
    $password = $_POST['password'];
    
    // Basic Validation
    if(empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check duplication
        $check = $DB->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        if($check->num_rows > 0) {
            $error = "Username or Email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password_hash, email, full_name, role) VALUES ('$username', '$hashed', '$email', '$fullname', 'admin')";
            
            if($DB->query($query)) {
                $msg = "New Admin created successfully.";
            } else {
                $error = "Database Error: " . $DB->error;
            }
        }
    }
}

// Fetch Existing Admins
$adminsQuery = "SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC";
$admins = $DB->query($adminsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Futura Brokerage</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background: rgba(15, 10, 20, 0.95); }
        .main-content { background: radial-gradient(circle at 50% 0%, rgba(112, 0, 255, 0.1) 0%, rgba(5, 5, 5, 0) 70%); }
        .nav-link.active { border-left-color: var(--color-accent); background: rgba(112, 0, 255, 0.1); color: var(--color-accent); }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
                <a href="../../index.html" class="logo"
                    style="font-size: 1.5rem; font-weight: 700; color: var(--color-text-main); font-family: 'Outfit', sans-serif; margin-bottom: 40px; padding-left: 10px; display: block;">
                    FUTURA<span style="color: var(--color-accent);">.ADMIN</span>
                </a>
                <div class="mobile-close" style="display: none; cursor: pointer; color: var(--color-text-muted); margin-bottom: 40px;">
                    <i class="fas fa-times"></i>
                </div>
            </div>

            <nav style="flex: 1;">
                <p style="padding-left: 15px; margin-bottom: 10px; font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 1px;">Admin Panel</p>
                <a href="index.php" class="nav-link"><i class="fas fa-chart-pie"></i> Overview</a>
                <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Manage Users</a>
                <a href="deposits.php" class="nav-link"><i class="fas fa-wallet"></i> Deposits</a>
                <a href="withdrawals.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Withdrawals</a>
                <a href="plans.php" class="nav-link"><i class="fas fa-chart-line"></i> Manage Plans</a>
                <a href="tickets.php" class="nav-link"><i class="fas fa-headset"></i> Support Tickets</a>
                <a href="admins.php" class="nav-link active"><i class="fas fa-user-shield"></i> Admins</a>
            </nav>

            <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="logout.php" class="nav-link" style="color: #ff3b30;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div style="display: flex; align-items: center;">
                    <div class="mobile-toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.8rem;">Manage Admins</h2>
                        <p style="color: var(--color-text-muted);">Create and manage system administrators</p>
                    </div>
                </div>
            </header>

            <div class="d-flex" style="gap: 30px; flex-wrap: wrap;">
                <!-- Create Admin Form -->
                <div class="glass-card" style="flex: 1; min-width: 300px;">
                    <h3 class="mb-4">Create New Admin</h3>
                    <form method="POST">
                        <div class="form-group mb-3">
                            <label class="text-muted mb-2 d-block">Full Name</label>
                            <input type="text" name="fullname" class="form-control" required placeholder="Ex: Admin One">
                        </div>
                        <div class="form-group mb-3">
                            <label class="text-muted mb-2 d-block">Email Address</label>
                            <input type="email" name="email" class="form-control" required placeholder="admin@example.com">
                        </div>
                        <div class="form-group mb-3">
                            <label class="text-muted mb-2 d-block">Username</label>
                            <input type="text" name="username" class="form-control" required placeholder="admin_user">
                        </div>
                        <div class="form-group mb-4">
                            <label class="text-muted mb-2 d-block">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="********">
                        </div>
                        
                        <button type="submit" name="create_admin" class="btn btn-primary w-100">Create Admin Account</button>
                    </form>
                </div>

                <!-- Admin List -->
                <div class="glass-card" style="flex: 1.5; min-width: 300px;">
                    <h3 class="mb-4">Existing Admins</h3>
                    <div class="table-container">
                        <table style="width: 100%; text-align: left; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <th style="padding: 10px;">Name</th>
                                    <th style="padding: 10px;">Username</th>
                                    <th style="padding: 10px;">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($admins->num_rows > 0): ?>
                                    <?php while($row = $admins->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td style="padding: 10px;">
                                            <div style="font-weight: 500;"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                        </td>
                                        <td style="padding: 10px; font-family: monospace;"><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td style="padding: 10px; font-size: 0.85rem; color: var(--color-text-muted);">
                                            <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="p-3 text-center text-muted">No admins found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../asset/scripts/main.js"></script>
    <?php if($msg): ?>
    <script>window.onload = function() { showNotification('<?php echo $msg; ?>', 'success'); }</script>
    <?php endif; ?>
    <?php if($error): ?>
    <script>window.onload = function() { showNotification('<?php echo $error; ?>', 'error'); }</script>
    <?php endif; ?>
</body>
</html>
