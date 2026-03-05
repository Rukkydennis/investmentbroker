<?php
require_once "../../asset/config/isuser.php";
require_once "../../asset/config/userdata.php";

$msg = "";
if(isset($_POST['update_profile'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email']; // Usually immutable, but let's allow it for now or make readonly
    $password = $_POST['password'];
    
    // Basic validation
    if(!empty($fullname)) {
        $updateQuery = "UPDATE users SET full_name = '$fullname' WHERE id = " . $user['id'];
        $DB->query($updateQuery);
        
        if(!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $DB->query("UPDATE users SET password_hash = '$hashed' WHERE id = " . $user['id']);
        }
        
        // Refresh user data
        header("Location: profile.php?msg=success");
        exit();
    }
}

if(isset($_GET['msg']) && $_GET['msg'] == 'success') {
    echo "<script>window.onload = function() { showNotification('Profile updated successfully!', 'success'); }</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Futura Brokerage</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
                <a href="../../index.html" class="logo"
                    style="font-size: 1.5rem; font-weight: 700; color: var(--color-text-main); font-family: 'Outfit', sans-serif; margin-bottom: 40px; padding-left: 10px; display: block;">
                    FUTURA<span style="color: var(--color-primary);">.IO</span>
                </a>
                <div class="mobile-close" style="display: none; cursor: pointer; color: var(--color-text-muted); margin-bottom: 40px;">
                    <i class="fas fa-times"></i>
                </div>
            </div>

            <nav style="flex: 1;">
                <p style="padding-left: 15px; margin-bottom: 10px; font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 1px;">
                    Menu</p>
                <a href="index.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="deposit.php" class="nav-link"><i class="fas fa-wallet"></i> Deposit</a>
                <a href="plans.php" class="nav-link"><i class="fas fa-chart-line"></i> Invest Plans</a>
                <a href="withdraw.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Withdraw</a>
                <a href="transactions.php" class="nav-link"><i class="fas fa-history"></i> Transactions</a>
                <a href="referrals.php" class="nav-link"><i class="fas fa-users"></i> Referrals</a>
                <a href="support.php" class="nav-link"><i class="fas fa-headset"></i> Support</a>
            </nav>

            <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="profile.php" class="nav-link active"><i class="fas fa-user-circle"></i> Profile</a>
                <a href="logout.php" class="nav-link" style="color: #ff3b30;"><i class="fas fa-sign-out-alt"></i>
                    Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div style="display: flex; align-items: center;">
                    <div class="mobile-toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.8rem;">Settings</h2>
                        <p class="text-muted">Manage your account information</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="position: relative;">
                        <i class="fas fa-bell" style="font-size: 1.2rem; color: var(--color-text-muted); cursor: pointer;"></i>
                        <span style="position: absolute; top: -5px; right: -5px; width: 10px; height: 10px; background: var(--color-primary); border-radius: 50%;"></span>
                    </div>
                    <div class="profile-dropdown-container" onclick="toggleProfileDropdown()">
                        <div class="d-flex align-center gap-1" style="background: var(--color-bg-card); padding: 5px 15px; border-radius: 50px; border: 1px solid var(--border-color);">
                            <div style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #333;">
                                <i class="fas fa-user" style="font-size: 0.8rem;"></i>
                            </div>
                            <span style="font-weight: 500;"><?php echo $user['full_name']; ?></span>
                            <i class="fas fa-chevron-down" style="font-size: 0.8rem; margin-left: 5px;"></i>
                        </div>
                        
                        <div class="profile-dropdown" id="profileDropdown">
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user-circle"></i> Profile Settings
                            </a>
                            <div style="height: 1px; background: var(--border-color); margin: 5px 0;"></div>
                            <a href="logout.php" class="dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="glass-card" style="max-width: 800px;">
                <h4 class="mb-4">Personal Information</h4>
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="text-muted mb-1" style="display:block;">Full Name</label>
                        <input type="text" name="fullname" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="text-muted mb-1" style="display:block;">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" readonly style="opacity: 0.7; cursor: not-allowed;">
                        <small class="text-muted">Email cannot be changed directly. Contact support.</small>
                    </div>
                    
                    <h4 class="mb-4" style="margin-top: 40px;">Security</h4>
                    <div class="form-group">
                        <label class="text-muted mb-1" style="display:block;">New Password (Optional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary mt-4">Save Changes</button>
                </form>
            </div>
        </main>
    </div>

    <script src="../../asset/scripts/main.js"></script>
</body>

</html>