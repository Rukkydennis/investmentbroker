<?php
require_once "../../asset/config/isuser.php";
require_once "../../asset/config/userdata.php";

// Fetch All Transactions (Combined)
$trxQuery = "
    (SELECT 'Deposit' as type, amount, status, created_at as date FROM deposits WHERE user_id = $user_id)
    UNION
    (SELECT 'Withdrawal' as type, amount, status, created_at as date FROM withdrawals WHERE user_id = $user_id)
    UNION
    (SELECT 'Investment' as type, amount, status, start_date as date FROM investments WHERE user_id = $user_id)
    UNION
    (SELECT 'Referral Bonus' as type, amount, 'completed' as status, created_at as date FROM referral_bonuses WHERE referrer_id = $user_id)
    ORDER BY date DESC
";
$trxResult = $DB->query($trxQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Futura Brokerage</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
                <a href="../../index.html" class="logo" style="font-size: 1.5rem; font-weight: 700; color: var(--color-text-main); font-family: 'Outfit', sans-serif; margin-bottom: 40px; padding-left: 10px; display: block;">
                    FUTURA<span style="color: var(--color-primary);">.IO</span>
                </a>
                <div class="mobile-close" style="display: none; cursor: pointer; color: var(--color-text-muted); margin-bottom: 40px;">
                    <i class="fas fa-times"></i>
                </div>
            </div>

            <nav style="flex: 1;">
                <p style="padding-left: 15px; margin-bottom: 10px; font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 1px;">Menu</p>
                <a href="index.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="deposit.php" class="nav-link"><i class="fas fa-wallet"></i> Deposit</a>
                <a href="plans.php" class="nav-link"><i class="fas fa-chart-line"></i> Invest Plans</a>
                <a href="withdraw.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Withdraw</a>
                <a href="transactions.php" class="nav-link active"><i class="fas fa-history"></i> Transactions</a>
                <a href="referrals.php" class="nav-link"><i class="fas fa-users"></i> Referrals</a>
                <a href="support.php" class="nav-link"><i class="fas fa-headset"></i> Support</a>
            </nav>

            <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="profile.php" class="nav-link"><i class="fas fa-user-circle"></i> Profile</a>
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
                        <h2 style="font-size: 1.8rem;">Transaction History</h2>
                        <p class="text-muted">View all your financial activities</p>
                    </div>
                </div>
                <!-- Profile Area -->
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

            <div class="glass-card table-container">
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($trxResult->num_rows > 0): ?>
                                <?php while($row = $trxResult->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if($row['type'] == 'Deposit'): ?>
                                                <i class="fas fa-arrow-down text-primary" style="margin-right: 8px;"></i>
                                            <?php elseif($row['type'] == 'Withdrawal'): ?>
                                                <i class="fas fa-arrow-up" style="color: #ff3b30; margin-right: 8px;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-chart-line" style="color: var(--color-secondary); margin-right: 8px;"></i>
                                            <?php endif; ?>
                                            <?php echo $row['type']; ?>
                                        </td>
                                        <td style="font-weight: 600;">$<?php echo number_format($row['amount'], 2); ?></td>
                                        <td class="text-muted"><?php echo date('M d, Y H:i', strtotime($row['date'])); ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = 'status-pending';
                                                if($row['status'] == 'approved' || $row['status'] == 'completed' || $row['status'] == 'running') $statusClass = 'status-success';
                                                if($row['status'] == 'rejected' || $row['status'] == 'cancelled') $statusClass = 'status-failed';
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted" style="padding: 30px;">No transactions found</td>
                                </tr>
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
