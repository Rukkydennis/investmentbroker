<?php
require_once "isadmin.php";
require_once "../../asset/config/userdata.php";

// Handle Delete User
if(isset($_POST['delete_user'])) {
    $id = intval($_POST['user_id']);
    // Optional: Check if admin to prevent self-delete if applicable, but this is user management
    if($DB->query("DELETE FROM users WHERE id = $id")) {
        $msg = "User deleted successfully.";
    } else {
        $error = "Failed to delete user.";
    }
}

$usersQuery = "SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC";
$usersResult = $DB->query($usersQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Futura Admin</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background: rgba(15, 10, 20, 0.95); }
        .main-content { 
            background: radial-gradient(circle at 50% 0%, rgba(112, 0, 255, 0.1) 0%, rgba(5, 5, 5, 0) 70%); 
            height: 100vh;
            overflow-y: auto;
        }
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
                <a href="users.php" class="nav-link active"><i class="fas fa-users"></i> Manage Users</a>
                <a href="deposits.php" class="nav-link"><i class="fas fa-wallet"></i> Deposits</a>
                <a href="withdrawals.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Withdrawals</a>
                <a href="plans.php" class="nav-link"><i class="fas fa-chart-line"></i> Manage Plans</a>
                <a href="tickets.php" class="nav-link"><i class="fas fa-headset"></i> Support Tickets</a>
                <a href="admins.php" class="nav-link"><i class="fas fa-user-shield"></i> Admins</a>
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
                        <h2 style="font-size: 1.8rem;">Users</h2>
                        <p style="color: var(--color-text-muted);">Manage registered users</p>
                    </div>
                </div>
            </header>

            <div class="glass-card table-container">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; text-align: left; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 15px;">ID</th>
                                <th style="padding: 15px;">Name</th>
                                <th style="padding: 15px;">Email</th>
                                <th style="padding: 15px;">Balance</th>
                                <th style="padding: 15px;">Joined</th>
                                <th style="padding: 15px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($usersResult->num_rows > 0): ?>
                                <?php while($row = $usersResult->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td style="padding: 15px;">#<?php echo $row['id']; ?></td>
                                    <td style="padding: 15px; font-weight: 500;"><?php echo $row['full_name']; ?></td>
                                    <td style="padding: 15px; color: var(--color-text-muted);"><?php echo $row['email']; ?></td>
                                    <td style="padding: 15px; font-weight: 700; color: var(--color-primary);">$<?php echo number_format($row['balance'], 2); ?></td>
                                    <td style="padding: 15px; font-size: 0.9rem; color: var(--color-text-muted);"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td style="padding: 15px;">
                                        <button class="btn" 
                                            data-id="<?php echo $row['id']; ?>"
                                            onclick="triggerDeleteUser(this)"
                                            style="padding: 5px 12px; background: rgba(255,59,48,0.2); color: #ff3b30; font-size: 0.8rem; border-radius: 4px; border:none; cursor: pointer;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted" style="padding: 20px;">No users found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../asset/scripts/main.js"></script>
    <script>
        function triggerDeleteUser(btn) {
            showConfirmation(
                'Delete User?', 
                'Are you sure you want to delete this user? This action cannot be undone.', 
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'user_id';
                    idInput.value = btn.dataset.id;
                    
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'delete_user';
                    actionInput.value = 'true';
                    
                    form.appendChild(idInput);
                    form.appendChild(actionInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            );
        }
    </script>
    <?php if(isset($msg) && $msg): ?>
    <script>window.onload = function() { showNotification('<?php echo $msg; ?>', 'success'); }</script>
    <?php endif; ?>
    <?php if(isset($error) && $error): ?>
    <script>window.onload = function() { showNotification('<?php echo $error; ?>', 'error'); }</script>
    <?php endif; ?>
</body>
</html>
