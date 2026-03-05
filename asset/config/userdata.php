<?php
require_once "DB.php";
$username = $_SESSION['user'];

// Fetch User Details
$query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
$result = $DB->query($query);
$user = $result->fetch_assoc();
$user_id = $user['id'];

// 1. Calculate Active Investment
$activeInvQuery = "SELECT SUM(amount) as total FROM investments WHERE user_id = $user_id AND status = 'running'";
$activeInvResult = $DB->query($activeInvQuery);
$activeInvRow = $activeInvResult->fetch_assoc();
$total_active_investment = $activeInvRow['total'] ?? 0.00;

// 2. Calculate Total Earnings (Dynamic ROI Based)
// Fetch all investments with their plan details
$invQuery = "SELECT i.*, p.roi_percent, p.duration_days FROM investments i JOIN plans p ON i.plan_id = p.id WHERE i.user_id = $user_id";
$invResult = $DB->query($invQuery);

$total_earnings = 0.00;
$total_volume_invested = 0.00;

while ($inv = $invResult->fetch_assoc()) {
    $total_volume_invested += $inv['amount'];
    if ($inv['status'] == 'completed' || $inv['status'] == 'cancelled') {
        // For closed investments, trust the valid total_profit stored
         $total_earnings += $inv['total_profit'];
    } elseif ($inv['status'] == 'running') {
        // Calculate accrued profit for running investments
        $start = strtotime($inv['start_date']);
        $now = time();
        $days_elapsed = floor(($now - $start) / (60 * 60 * 24));
        
        // Cap at duration
        if ($days_elapsed > $inv['duration_days']) {
            $days_elapsed = $inv['duration_days'];
        }
        
        // Profit = Amount * (ROI% / 100) * Days
        // Assuming ROI is Daily. If ROI is total, logic differs. Schema says "Daily ROI".
        $accrued = $inv['amount'] * ($inv['roi_percent'] / 100) * $days_elapsed;
        $total_earnings += $accrued;
    }
}

// 2.5 Calculate Referral Bonuses
$refBonusQuery = "SELECT SUM(amount) as total FROM referral_bonuses WHERE referrer_id = $user_id";
$total_referral_earnings = $DB->query($refBonusQuery)->fetch_assoc()['total'] ?? 0.00;

// Add referral bonuses to total earnings pool
$total_earnings += floatval($total_referral_earnings);

// Subtract already withdrawn earnings
$total_earnings = $total_earnings - floatval($user['withdrawn_earnings']);
if($total_earnings < 0) $total_earnings = 0; 

// Calculate Portfolio % Return
$total_lifetime_earnings = $total_earnings + floatval($user['withdrawn_earnings']);
$portfolio_return_percent = ($total_volume_invested > 0) ? ($total_lifetime_earnings / $total_volume_invested) * 100 : 0.00;

// Consolidated Total Balance (Wallet + Earnings)
$calculated_total_balance = floatval($user['balance']) + $total_earnings;

// 3. Calculate Total Plans (Active)
$plansQuery = "SELECT COUNT(*) as total FROM investments WHERE user_id = $user_id AND status = 'running'";
$plansResult = $DB->query($plansQuery);
$plansRow = $plansResult->fetch_assoc();
$active_plans_count = $plansRow['total'] ?? 0;

// 4. Referral Stats
$refQuery = "SELECT COUNT(*) as total FROM users WHERE referrer_id = $user_id";
$refResult = $DB->query($refQuery);
$refRow = $refResult->fetch_assoc();
$total_referrals = $refRow['total'] ?? 0;

// 5. Calculate Weekly Deposit Increase %
$lastWeekDepositsQuery = "SELECT SUM(amount) as total FROM deposits WHERE user_id = $user_id AND status = 'approved' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$lastWeekTotal = $DB->query($lastWeekDepositsQuery)->fetch_assoc()['total'] ?? 0;

$priorDepositsQuery = "SELECT SUM(amount) as total FROM deposits WHERE user_id = $user_id AND status = 'approved' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$priorTotal = $DB->query($priorDepositsQuery)->fetch_assoc()['total'] ?? 0;

if ($priorTotal > 0) {
    $deposit_increase_percent = ($lastWeekTotal / $priorTotal) * 100;
} else {
    $deposit_increase_percent = ($lastWeekTotal > 0) ? 100 : 0;
}

// 6. Recent Transactions (Limit 5)
// We combine Deposits, Withdrawals, and Investments for the list
$trxQuery = "
    (SELECT 'Deposit' as type, amount, status, created_at as date FROM deposits WHERE user_id = $user_id)
    UNION
    (SELECT 'Withdrawal' as type, amount, status, created_at as date FROM withdrawals WHERE user_id = $user_id)
    UNION
    (SELECT 'Investment' as type, amount, status, start_date as date FROM investments WHERE user_id = $user_id)
    ORDER BY date DESC LIMIT 5
";
$trxResult = $DB->query($trxQuery);
?>