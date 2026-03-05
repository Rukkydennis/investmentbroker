<?php
require_once "isadmin.php";
require_once "../../asset/config/userdata.php"; // Reuse for DB connection $DB

// 1. Total Users
$usersQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
$usersCount = $DB->query($usersQuery)->fetch_assoc()['count'];

// 2. Total Invested (Active + Completed)
$investedQuery = "SELECT SUM(amount) as total FROM investments";
$totalInvested = $DB->query($investedQuery)->fetch_assoc()['total'] ?? 0;

// 3. Pending Withdrawals
$pendingWithdrawalsQuery = "SELECT SUM(amount) as total FROM withdrawals WHERE status = 'pending'";
$pendingWithdrawals = $DB->query($pendingWithdrawalsQuery)->fetch_assoc()['total'] ?? 0;

// 4. Total Paid (Approved Withdrawals)
$paidQuery = "SELECT SUM(amount) as total FROM withdrawals WHERE status = 'approved'";
$totalPaid = $DB->query($paidQuery)->fetch_assoc()['total'] ?? 0;

// 5. Global Earning % (Aggregate ROI)
// We need to calculate accrued profit for all running investments + completed profits
$allInvQuery = "SELECT i.*, p.roi_percent, p.duration_days FROM investments i JOIN plans p ON i.plan_id = p.id";
$allInvResult = $DB->query($allInvQuery);
$totalGlobalProfit = 0;
$totalGlobalVolume = 0;

while($inv = $allInvResult->fetch_assoc()) {
    $totalGlobalVolume += $inv['amount'];
    if($inv['status'] == 'completed' || $inv['status'] == 'cancelled') {
        $totalGlobalProfit += $inv['total_profit'];
    } elseif($inv['status'] == 'running') {
        $start = strtotime($inv['start_date']);
        $days_elapsed = floor((time() - $start) / 86400);
        if($days_elapsed > $inv['duration_days']) $days_elapsed = $inv['duration_days'];
        $totalGlobalProfit += $inv['amount'] * ($inv['roi_percent'] / 100) * $days_elapsed;
    }
}

$globalEarningPercent = ($totalGlobalVolume > 0) ? ($totalGlobalProfit / $totalGlobalVolume) * 100 : 0.00;

// 6. Recent Pending Withdrawals for Table
$recentWithdrawalsQuery = "SELECT w.*, u.full_name FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.status = 'pending' ORDER BY w.created_at DESC LIMIT 5";
$recentWithdrawals = $DB->query($recentWithdrawalsQuery);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Futura Brokerage</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background: rgba(15, 10, 20, 0.95); } /* Admin Purple Theme */
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
                <a href="index.php" class="nav-link active"><i class="fas fa-chart-pie"></i> Overview</a>
                <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Manage Users</a>
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
                        <h2 style="font-size: 1.8rem;">Admin Overview</h2>
                        <p style="color: var(--color-text-muted);">Platform statistics and tasks</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px; background: var(--color-bg-card); padding: 5px 15px; border-radius: 50px; border: 1px solid var(--border-color);">
                        <span style="font-weight: 600; color: var(--color-accent);">Administrator</span>
                    </div>
                </div>
            </header>

            <!-- Admin Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <p style="color: var(--color-text-muted); font-size: 0.9rem;">Total Users</p>
                    <h3 style="font-size: 2rem;"><?php echo number_format($usersCount); ?></h3>
                </div>
                <div class="stat-card">
                    <p style="color: var(--color-text-muted); font-size: 0.9rem;">Total Invested</p>
                    <h3 style="font-size: 2rem;">$<?php echo number_format($totalInvested, 2); ?></h3>
                </div>
                <div class="stat-card">
                    <p style="color: var(--color-text-muted); font-size: 0.9rem;">Pending Withdrawals</p>
                    <h3 style="font-size: 2rem; color: #ffc107;">$<?php echo number_format($pendingWithdrawals, 2); ?></h3>
                </div>
                <div class="stat-card">
                    <p style="color: var(--color-text-muted); font-size: 0.9rem;">Total Paid</p>
                    <h3 style="font-size: 2rem; color: var(--color-primary);">$<?php echo number_format($totalPaid, 2); ?></h3>
                </div>
                <div class="stat-card">
                    <p style="color: var(--color-text-muted); font-size: 0.9rem;">Global Earning %</p>
                    <h3 style="font-size: 2rem; color: var(--color-accent);"><?php echo number_format($globalEarningPercent, 2); ?>%</h3>
                </div>
            </div>

            <!-- Pending Withdrawals Table (Preview) -->
            <div class="glass-card table-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h4 style="margin: 0;">Recent Pending Withdrawals</h4>
                    <a href="withdrawals.php" style="color: var(--color-accent); font-size: 0.9rem;">View All</a>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; text-align: left; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 15px;">User</th>
                                <th style="padding: 15px;">Amount</th>
                                <th style="padding: 15px;">Method</th>
                                <th style="padding: 15px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentWithdrawals->num_rows > 0): ?>
                                <?php while($row = $recentWithdrawals->fetch_assoc()): ?>
                                <tr>
                                    <td style="padding: 15px;"><?php echo $row['full_name']; ?></td>
                                    <td style="padding: 15px; font-weight: 700;">$<?php echo number_format($row['amount'], 2); ?></td>
                                    <td style="padding: 15px;"><?php echo $row['method']; ?></td>
                                    <td style="padding: 15px;"><span class="status-badge status-pending">Pending</span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="padding: 20px; text-align: center; color: var(--color-text-muted);">No pending withdrawals</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../asset/scripts/main.js"></script>
</body>
</html>
