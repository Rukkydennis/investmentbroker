<?php
require_once "../../asset/config/isuser.php";
require_once "../../asset/config/userdata.php";


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Futura Brokerage</title>
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
                <a href="index.php" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="deposit.php" class="nav-link"><i class="fas fa-wallet"></i> Deposit</a>
                <a href="plans.php" class="nav-link"><i class="fas fa-chart-line"></i> Invest Plans</a>
                <a href="withdraw.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Withdraw</a>
                <a href="transactions.php" class="nav-link"><i class="fas fa-history"></i> Transactions</a>
                <a href="referrals.php" class="nav-link"><i class="fas fa-users"></i> Referrals</a>
                <a href="support.php" class="nav-link"><i class="fas fa-headset"></i> Support</a>
            </nav>

            <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="profile.php" class="nav-link"><i class="fas fa-user-circle"></i> Profile</a>
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
                        <h2 style="font-size: 1.8rem;">Dashboard</h2>
                        <p class="text-muted">Welcome back, <?php echo $user['full_name']; ?></p>
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

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="d-flex justify-between" style="align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <p class="text-muted" style="font-size: 0.9rem;">Total Balance</p>
                            <h3 style="font-size: 1.8rem; margin-top: 5px;">$<?php echo number_format($calculated_total_balance, 2); ?></h3>
                        </div>
                        <div style="width: 40px; height: 40px; background: rgba(0,255,136,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-wallet text-primary"></i>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem;">
                        <span class="text-primary">+<?php echo number_format($deposit_increase_percent, 1); ?>%</span> <span class="text-muted">from last week</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="d-flex justify-between" style="align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <p class="text-muted" style="font-size: 0.9rem;">Active Investment</p>
                            <h3 style="font-size: 1.8rem; margin-top: 5px;">$<?php echo number_format($total_active_investment, 2); ?></h3>
                        </div>
                        <div style="width: 40px; height: 40px; background: rgba(0,204,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-pie" style="color: var(--color-secondary);"></i>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem;">
                        <span class="text-muted"><?php echo $active_plans_count; ?> Active Plans</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="d-flex justify-between" style="align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <p class="text-muted" style="font-size: 0.9rem;">Total Earnings</p>
                            <h3 style="font-size: 1.8rem; margin-top: 5px;">$<?php echo number_format($total_earnings, 2); ?></h3>
                        </div>
                        <div style="width: 40px; height: 40px; background: rgba(112,0,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-coins" style="color: var(--color-accent);"></i>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem;">
                        <span class="text-primary">+<?php echo number_format($portfolio_return_percent, 2); ?>%</span> <span class="text-muted">Portfolio Return</span>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;" class="dashboard-grid-layout">
                <!-- Recent Transactions -->
                <div class="glass-card table-container">
                    <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0;">Recent Transactions</h4>
                        <a href="transactions.php" style="font-size: 0.85rem; color: var(--color-primary);">View All</a>
                    </div>
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
                                            <td class="text-muted"><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
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
                                        <td colspan="4" class="text-center text-muted">No recent transactions</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Referral Widget -->
                <div class="glass-card">
                    <h4 class="mb-4">Referral System</h4>
                    <div class="text-center mb-4">
                        <div class="mb-2 text-muted">My Referral ID</div>
                        <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px; border: 1px dashed var(--border-color); font-family: monospace; font-size: 0.9rem; margin-bottom: 15px; word-break: break-all;">
                            <?php echo $user['id']; ?>
                        </div>
                        <button class="btn btn-primary" onclick="copyReferralLink()" style="padding: 8px 20px; font-size: 0.9rem;">Copy ID</button>
                    </div>
                    <div class="d-flex justify-between" style="border-top: 1px solid var(--border-color); padding-top: 15px;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Total Invited</div>
                            <div style="font-weight: 600;"><?php echo $total_referrals; ?> Users</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Total Earned</div>
                            <?php 
                                $earnedQuery = "SELECT SUM(amount) as total FROM referral_bonuses WHERE referrer_id = " . $user['id'];
                                $earned = $DB->query($earnedQuery)->fetch_assoc()['total'] ?? 0;
                            ?>
                            <div style="font-weight: 600; color: var(--color-primary);">$<?php echo number_format($earned, 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../asset/scripts/main.js"></script>
    <script>
        function copyReferralLink() {
            const link = '<?php echo $user['id']; ?>';
            navigator.clipboard.writeText(link).then(() => {
                showNotification('Referral ID copied!', 'success');
            });
        }
    </script>
</body>

</html>