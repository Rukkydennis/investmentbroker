<?php
require_once "../../asset/config/isuser.php";
require_once "../../asset/config/userdata.php";

$msg = "";
$error = "";

if(isset($_POST['withdraw'])) {
    $amount = floatval($_POST['amount']); // Ensure number
    $method = $DB->real_escape_string($_POST['method']); 
    $wallet_address = $DB->real_escape_string($_POST['wallet_address']);
    
    // Enforcement: Active Investment Required
    if($total_active_investment <= 0) {
        $error = "You must have an active investment to withdraw funds.";
    } else {
        // Validations
        if($amount >= 50) {
            if($total_earnings >= $amount) {
                
                // For this platform, we assume withdrawals are deducted from the conceptual 'earnings' 
                // In a real system, you might have an 'earnings' column. 
                // Here, we deduct from 'balance' which represents the withdrawable pool.
                $updateEarnings = "UPDATE users SET withdrawn_earnings = withdrawn_earnings + $amount WHERE id = " . $user['id'];
                
                if($DB->query($updateEarnings)) {
                    // Create Withdrawal Record
                    $query = "INSERT INTO withdrawals (user_id, amount, method, wallet_address, status) VALUES (" . $user['id'] . ", '$amount', '$method', '$wallet_address', 'pending')";
                    
                    if($DB->query($query)) {
                        header("Location: withdraw.php?msg=success&amount=$amount");
                        exit();
                    } else {
                        // Refund if insert fails
                        $DB->query("UPDATE users SET withdrawn_earnings = withdrawn_earnings - $amount WHERE id = " . $user['id']);
                        $error = "Withdrawal request failed. Earnings balance refunded.";
                    }
                } else {
                    $error = "Failed to update balance.";
                }
                
            } else {
                $error = "Insufficient earnings balance.";
            }
        } else {
            $error = "Minimum withdrawal is $50.";
        }
    }
}

if(isset($_GET['msg']) && $_GET['msg'] == 'success') {
    $amount_display = isset($_GET['amount']) ? '$' . number_format(floatval($_GET['amount']), 2) : 'your funds';
    $msg = "Your withdrawal of <strong>$amount_display</strong> is sent out to the admins for review.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw - Futura Brokerage</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .balance-card {
            background: linear-gradient(135deg, var(--color-accent), #4a00e0);
            padding: 30px;
            border-radius: 16px;
            color: #fff;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .balance-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
    </style>
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
                <a href="withdraw.php" class="nav-link active"><i class="fas fa-money-bill-wave"></i> Withdraw</a>
                <a href="transactions.php" class="nav-link"><i class="fas fa-history"></i> Transactions</a>
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
                        <h2 style="font-size: 1.8rem;">Withdraw Funds</h2>
                        <p class="text-muted">Request a payout to your wallet</p>
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

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <div class="glass-card">
                    <h3 style="margin-bottom: 25px;">Withdrawal Request</h3>
                    <form method="POST" id="withdrawForm">
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; margin-bottom: 10px; color: var(--color-text-muted);">Select Method</label>
                            <select name="method" class="form-control" style="cursor: pointer;" required>
                                <option value="Bitcoin (BTC)">Bitcoin (BTC)</option>
                                <option value="USDT (TRC20)">USDT (TRC20)</option>
                                <option value="Ethereum (ETH)">Ethereum (ETH)</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label style="display: block; margin-bottom: 10px; color: var(--color-text-muted);">Amount</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); font-weight: 600; color: #fff;">$</span>
                                <input type="number" name="amount" class="form-control" placeholder="0.00" style="padding-left: 40px; font-size: 1.2rem; font-weight: 600;" min="50" max="<?php echo $total_earnings; ?>" required>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 10px;">Withdrawable Earnings: $<?php echo number_format($total_earnings, 2); ?></p>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <label style="display: block; margin-bottom: 10px; color: var(--color-text-muted);">Wallet Address</label>
                            <input type="text" name="wallet_address" class="form-control" placeholder="Paste your wallet address here" required>
                        </div>

                        <button type="button" class="btn btn-primary" style="width: 100%;" onclick="confirmWithdrawal()">Submit Request</button>
                        <button type="submit" name="withdraw" style="display: none;"></button>
                    </form>
                </div>
                
                <div>
                    <div class="balance-card">
                        <p style="font-size: 0.9rem; opacity: 0.8;">Withdrawable Earnings</p>
                        <h2 style="font-size: 2.5rem; margin-bottom: 20px;">$<?php echo number_format($total_earnings, 2); ?></h2>
                        <div style="display: flex; gap: 10px;">
                            <span style="background: <?php echo $total_active_investment > 0 ? 'rgba(0, 255, 136, 0.2)' : 'rgba(255, 59, 48, 0.2)'; ?>; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem;">
                                <i class="fas <?php echo $total_active_investment > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i> 
                                <?php echo $total_active_investment > 0 ? 'Active Investment' : 'No Active Investment'; ?>
                            </span>
                        </div>
                    </div>

                    </div>
                </div>
            </div> <!-- End Grid -->
        </main>
    </div> <!-- End Dashboard Container -->

    <script src="../../asset/scripts/main.js"></script>
    <script>
        function confirmWithdrawal() {
            const form = document.querySelector('#withdrawForm');
            if (!form) {
                console.error("Form not found");
                return;
            }
            
            const amountInput = form.querySelector('[name="amount"]');
            const addressInput = form.querySelector('[name="wallet_address"]');
            
            const amount = amountInput ? parseFloat(amountInput.value) : 0;
            const address = addressInput ? addressInput.value : '';
            
            if(!amount || !address) {
                showNotification('Please fill all fields', 'error');
                return;
            }

            if (amount < 50) {
                showNotification('Minimum withdrawal is $50', 'error');
                return;
            }

            if(typeof showConfirmation === 'function') {
                showConfirmation(
                    'Confirm Withdrawal', 
                    `Withdraw <strong>$${amount}</strong> to <br><small>${address}</small>?`, 
                    () => {
                        const existing = form.querySelector('input[name="withdraw"]');
                        if(existing) existing.remove();

                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'withdraw';
                        input.value = 'true';
                        form.appendChild(input);
                        form.submit();
                    }
                );
            } else {
                if(confirm(`Withdraw $${amount} to ${address}?`)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'withdraw';
                    input.value = 'true';
                    form.appendChild(input);
                    form.submit();
                }
            }
        }
    </script>
    <?php if($msg): ?>
    <script>setTimeout(() => { showNotification('<?php echo $msg; ?>', 'success'); }, 100);</script>
    <?php endif; ?>
    <?php if($error): ?>
    <script>setTimeout(() => { showNotification('<?php echo $error; ?>', 'error'); }, 100);</script>
    <?php endif; ?>
</body>

</html>
