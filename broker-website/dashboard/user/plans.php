<?php
require_once "../../asset/config/isuser.php";
require_once "../../asset/config/userdata.php";

$msg = "";
$error = "";

// Fetch Plans
$plansResult = $DB->query("SELECT * FROM plans ORDER BY min_amount ASC");

// Handle Investment Logic
if(isset($_POST['invest'])) {
    $plan_id = $_POST['plan_id'];
    $amount = $_POST['amount'];
    
    // Check consolidated balance (balance + earnings)
    if($calculated_total_balance >= $amount) {
        $p = $DB->query("SELECT * FROM plans WHERE id = $plan_id")->fetch_assoc();
        
        if($amount >= $p['min_amount'] && $amount <= $p['max_amount']) {
            // Deduct from Balance first, then Earnings
            if ($user['balance'] >= $amount) {
                $newBalance = $user['balance'] - $amount;
                $DB->query("UPDATE users SET balance = $newBalance WHERE id = " . $user['id']);
            } else {
                $remainder = $amount - $user['balance'];
                // Empty the balance
                $DB->query("UPDATE users SET balance = 0 WHERE id = " . $user['id']);
                // Deduct the rest from Earnings (by increasing withdrawn_earnings)
                $DB->query("UPDATE users SET withdrawn_earnings = withdrawn_earnings + $remainder WHERE id = " . $user['id']);
            }
            
            // Create Investment
            // Daily Profit = Amount * (ROI / 100)
            $daily = $amount * ($p['roi_percent'] / 100);
            
            $insertStart = "INSERT INTO investments (user_id, plan_id, amount, daily_profit, next_payout, end_date) VALUES ";
            $values = "(" . $user['id'] . ", $plan_id, $amount, $daily, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL " . $p['duration_days'] . " DAY))";
            
            if($DB->query($insertStart . $values)) {
                
                // Referral Bonus Logic
                if ($user['referrer_id']) {
                    $referrer_id = $user['referrer_id'];
                    $bonus_amount = $amount * ($p['roi_percent'] / 100);
                    
                    // Referral Bonus Logic: Insert record (counted in earnings calculation)
                    // We NO LONGER update the balance column directly
                    
                    // Insert Record
                    $DB->query("INSERT INTO referral_bonuses (referrer_id, referred_user_id, amount) VALUES ($referrer_id, " . $user['id'] . ", $bonus_amount)");
                }

                header("Location: plans.php?msg=success");
                exit();
            } else {
                $error = "Investment failed. Please try again.";
            }
        } else {
            $error = "Amount must be between $" . $p['min_amount'] . " and $" . $p['max_amount'];
        }
    } else {
        $error = "Insufficient balance. Please deposit first.";
    }
}

if(isset($_GET['msg']) && $_GET['msg'] == 'success') {
    $msg = 'Investment Plan Started Successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment Plans - Futura Brokerage</title>
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
                <a href="plans.php" class="nav-link active"><i class="fas fa-chart-line"></i> Invest Plans</a>
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
                        <h2 style="font-size: 1.8rem;">Investment Plans</h2>
                        <p class="text-muted">Choose a plan that suits your goals</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <span class="text-muted" style="font-size: 0.9rem;">Total Balance</span>
                    <h3 class="text-gradient" style="margin: 0;">$<?php echo number_format($calculated_total_balance, 2); ?></h3>
                </div>
            </header>

            <div class="grid-3" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                <?php while($plan = $plansResult->fetch_assoc()): ?>
                <div class="glass-card plan-card <?php echo ($plan['name'] == 'Pro Plan') ? 'featured' : ''; ?>">
                    <h3 class="mb-2"><?php echo $plan['name']; ?></h3>
                    <div class="plan-price">
                        <span class="text-primary"><?php echo $plan['roi_percent']; ?>%</span>
                        <span class="text-muted" style="font-size: 1rem;"> daily ROI</span>
                    </div>
                    <ul class="check-list mb-4">
                        <li><i class="fas fa-clock"></i> Duration: <?php echo $plan['duration_days']; ?> Days</li>
                        <li><i class="fas fa-check-circle"></i> Min: $<?php echo number_format($plan['min_amount']); ?></li>
                        <li><i class="fas fa-check-circle"></i> Max: $<?php echo number_format($plan['max_amount']); ?></li>
                        <li><i class="fas fa-headset"></i> 24/7 Support</li>
                    </ul>
                    <form method="POST" class="invest-form" data-amount="<?php echo $plan['min_amount']; ?>" data-roi="<?php echo $plan['roi_percent']; ?>" data-days="<?php echo $plan['duration_days']; ?>">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                        <div class="form-group mb-3">
                            <input type="number" name="amount" class="form-control" placeholder="Enter Amount ($)" min="<?php echo $plan['min_amount']; ?>" max="<?php echo $plan['max_amount']; ?>" required oninput="calculateProfit(this)">
                        </div>
                        
                        <!-- Real-time Calculator -->
                        <div class="calculator-box mb-3" style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 5px;">
                                <span class="text-muted">Daily Profit:</span>
                                <span class="text-success profit-daily">$0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 5px;">
                                <span class="text-muted">Total Return:</span>
                                <span class="text-primary profit-total">$0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem;">
                                <span class="text-muted">Payout Duration:</span>
                                <span class="text-light"><?php echo $plan['duration_days']; ?> Days</span>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary w-100" onclick="confirmInvestment(this)">Invest Now</button>
                        <button type="submit" name="invest" style="display: none;"></button>
                    </form>
                </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>

    <script src="../../asset/scripts/main.js"></script>
    <script>
        function calculateProfit(input) {
            const form = input.closest('form');
            const amount = parseFloat(input.value) || 0;
            const roi = parseFloat(form.dataset.roi);
            const days = parseInt(form.dataset.days);
            
            const dailyProfit = amount * (roi / 100);
            const totalProfit = dailyProfit * days;
            const totalReturn = amount + totalProfit; // Capital + Profit if strictly return, or just profit based on schema. Usually "Return" implies profit. User asked for "Total Profit" but usually users want to know what they get back. Let's show Profit.
            
            // Updating DOM
            form.querySelector('.profit-daily').innerText = '$' + dailyProfit.toFixed(2);
            form.querySelector('.profit-total').innerText = '$' + totalProfit.toFixed(2);
        }

        function confirmInvestment(btn) {
            const form = btn.closest('form');
            const amountInput = form.querySelector('[name="amount"]');
            const amount = amountInput ? parseFloat(amountInput.value) : 0;
            const min = parseFloat(amountInput.min);
            const max = parseFloat(amountInput.max);
            
            if(!amount) {
                showNotification('Please enter an amount', 'error');
                return;
            }

            if(amount < min || amount > max) {
                showNotification(`Amount must be between $${min} and $${max}`, 'error');
                return;
            }

            if(typeof showConfirmation === 'function') {
                showConfirmation(
                    'Confirm Investment', 
                    `Are you sure you want to invest <strong>$${amount}</strong>?`, 
                    () => {
                        form.querySelector('[name="invest"]').click();
                    }
                );
            } else {
                if(confirm(`Invest $${amount}?`)) {
                    form.querySelector('[name="invest"]').click();
                }
            }
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