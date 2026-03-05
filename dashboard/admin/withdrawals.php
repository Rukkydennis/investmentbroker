<?php
require_once "isadmin.php";
require_once "../../asset/config/userdata.php";

$msg = "";
$error = "";

// Handle Actions
if(isset($_POST['action'])) {
    $wd_id = intval($_POST['wd_id']);
    $action = $_POST['action'];
    
    $wdQuery = "SELECT * FROM withdrawals WHERE id = $wd_id AND status = 'pending'";
    $wdResult = $DB->query($wdQuery);
    
    if($wdResult->num_rows > 0) {
        $wd = $wdResult->fetch_assoc();
        $user_id = $wd['user_id'];
        $amount = $wd['amount'];
        
        if($action == 'approve') {
            // Already deducted, just approve
            $updateWd = "UPDATE withdrawals SET status = 'approved' WHERE id = $wd_id";
            if($DB->query($updateWd)) {
                $msg = "Withdrawal approved.";
            } else {
                $error = "Failed to approve withdrawal.";
            }
        } elseif($action == 'reject') {
            // Refund Earnings
            $updateUser = "UPDATE users SET withdrawn_earnings = withdrawn_earnings - $amount WHERE id = $user_id";
            $updateWd = "UPDATE withdrawals SET status = 'rejected' WHERE id = $wd_id";
            
            if($DB->query($updateUser) && $DB->query($updateWd)) {
                $msg = "Withdrawal rejected and earnings balance refunded.";
            } else {
                $error = "Failed to reject withdrawal.";
            }
        }
    }
}

// Fetch Withdrawals
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';
$whereClause = "";
if($filter != 'all') {
    $whereClause = "WHERE w.status = '$filter'";
}

$withdrawalsQuery = "SELECT w.*, u.full_name, u.email FROM withdrawals w JOIN users u ON w.user_id = u.id $whereClause ORDER BY w.created_at DESC";
$withdrawals = $DB->query($withdrawalsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Withdrawals - Futura Admin</title>
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
                <a href="withdrawals.php" class="nav-link active"><i class="fas fa-money-bill-wave"></i> Withdrawals</a>
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
                        <h2 style="font-size: 1.8rem;">Withdrawals</h2>
                        <p style="color: var(--color-text-muted);">Process payouts</p>
                    </div>
                </div>
            </header>

            <div class="glass-card mb-4" style="padding: 15px;">
                <a href="withdrawals.php?filter=pending" class="btn <?php echo $filter == 'pending' ? 'btn-primary' : 'btn-outline'; ?>" style="margin-right: 10px;">Pending</a>
                <a href="withdrawals.php?filter=approved" class="btn <?php echo $filter == 'approved' ? 'btn-primary' : 'btn-outline'; ?>" style="margin-right: 10px;">Approved</a>
                <a href="withdrawals.php?filter=rejected" class="btn <?php echo $filter == 'rejected' ? 'btn-primary' : 'btn-outline'; ?>" style="margin-right: 10px;">Rejected</a>
                <a href="withdrawals.php?filter=all" class="btn <?php echo $filter == 'all' ? 'btn-primary' : 'btn-outline'; ?>">All</a>
            </div>

            <div class="glass-card table-container">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; text-align: left; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 15px;">User</th>
                                <th style="padding: 15px;">Amount</th>
                                <th style="padding: 15px;">Details</th>
                                <th style="padding: 15px;">Date</th>
                                <th style="padding: 15px;">Status</th>
                                <th style="padding: 15px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($withdrawals->num_rows > 0): ?>
                                <?php while($row = $withdrawals->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td style="padding: 15px;">
                                        <div><?php echo $row['full_name']; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--color-text-muted);"><?php echo $row['email']; ?></div>
                                    </td>
                                    <td style="padding: 15px; font-weight: 700;">$<?php echo number_format($row['amount'], 2); ?></td>
                                    <td style="padding: 15px;">
                                        <div style="font-size: 0.9rem;"><?php echo $row['method']; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--color-text-muted); font-family: monospace;"><?php echo $row['wallet_address']; ?></div>
                                    </td>
                                    <td style="padding: 15px; font-size: 0.9rem; color: var(--color-text-muted);"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td style="padding: 15px;">
                                        <?php 
                                            $status = $row['status'];
                                            $class = 'status-pending';
                                            if($status == 'approved') $class = 'status-success';
                                            if($status == 'rejected') $class = 'status-failed';
                                        ?>
                                        <span class="status-badge <?php echo $class; ?>"><?php echo ucfirst($status); ?></span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php if($row['status'] == 'pending'): ?>
                                            <div style="display: flex; gap: 5px;">
                                                <button class="btn" 
                                                    data-id="<?php echo $row['id']; ?>"
                                                    onclick="triggerAdminAction(this, 'approve', 'Confirm payout of <strong>$<?php echo number_format($row['amount'], 2); ?></strong>?')"
                                                    style="padding: 5px 12px; background: var(--color-primary); color: #000; font-size: 0.8rem; border-radius: 4px; border:none; cursor: pointer;">
                                                    Approve
                                                </button>
                                                
                                                <button class="btn" 
                                                    data-id="<?php echo $row['id']; ?>"
                                                    onclick="triggerAdminAction(this, 'reject', 'Reject and refund balance?')"
                                                    style="padding: 5px 12px; background: rgba(255,59,48,0.2); color: #ff3b30; font-size: 0.8rem; border-radius: 4px; border:none; cursor: pointer;">
                                                    Reject
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted" style="padding: 20px;">No withdrawals found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../asset/scripts/main.js"></script>
    <script>
        function triggerAdminAction(btn, action, message) {
            showConfirmation(
                'Confirm Action', 
                message, 
                () => {
                    // Create a hidden form to submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'wd_id';
                    idInput.value = btn.dataset.id;
                    
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = action;
                    
                    form.appendChild(idInput);
                    form.appendChild(actionInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            );
        }
    </script>
    <?php if($msg): ?>
    <script>window.onload = function() { showNotification('<?php echo $msg; ?>', 'success'); }</script>
    <?php endif; ?>
    <?php if($error): ?>
    <script>window.onload = function() { showNotification('<?php echo $error; ?>', 'error'); }</script>
    <?php endif; ?>
</body>
</html>
