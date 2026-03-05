<?php
require_once "../../asset/config/isuser.php";
require_once "../../asset/config/userdata.php";

$msg = "";
$error = "";

// Fetch Wallets for JS
$walletsQuery = "SELECT * FROM admin_wallets";
$walletsResult = $DB->query($walletsQuery);
$wallets = [];
while ($row = $walletsResult->fetch_assoc()) {
    $wallets[$row['network']] = $row;
}
$walletsJSON = json_encode($wallets);

// Handle Deposit
if(isset($_POST['deposit'])) {
    $amount = $_POST['amount'];
    $method = $_POST['method']; // 'Bitcoin', 'Ethereum', etc.
    
    if($amount >= 100) {
        $proofPath = null;
        
        // Handle Proof Upload
        if(isset($_FILES['proof']) && $_FILES['proof']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $filename = $_FILES['proof']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newName = uniqid('proof_', true) . "." . $ext;
                $targetDir = "../../asset/uploads/proofs/";
                
                // Ensure directory exists
                if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                
                if(move_uploaded_file($_FILES['proof']['tmp_name'], $targetDir . $newName)) {
                    $proofPath = "asset/uploads/proofs/" . $newName;
                } else {
                    $error = "Failed to upload proof image.";
                }
            } else {
                $error = "Invalid file type. Only JPG, PNG, and PDF allowed.";
            }
        }

        if(empty($error)) {
            $query = "INSERT INTO deposits (user_id, amount, method, proof_image, status) VALUES (" . $user['id'] . ", '$amount', '$method', '$proofPath', 'pending')";
            if($DB->query($query)) {
                header("Location: deposit.php?msg=success");
                exit();
            } else {
                $error = "Failed to create deposit request.";
            }
        }
    } else {
        $error = "Minimum deposit amount is $100.";
    }
}

if(isset($_GET['msg']) && $_GET['msg'] == 'success') {
    $msg = "Deposit request created successfully! Please wait for approval.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit - Futura Brokerage</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .method-card { transition: all 0.3s ease; }
        .method-card.selected { border-color: var(--color-primary); background: rgba(0, 255, 136, 0.1); transform: translateY(-5px); }
        .wallet-address-box { display: none; margin-top: 20px; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .file-upload-box {
            border: 2px dashed var(--border-color);
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        .file-upload-box:hover { border-color: var(--color-primary); background: rgba(0, 255, 136, 0.05); }
        .file-upload-input {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0;
            cursor: pointer;
        }
    </style>
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
                <a href="deposit.php" class="nav-link active"><i class="fas fa-wallet"></i> Deposit</a>
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
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div style="display: flex; align-items: center;">
                    <div class="mobile-toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.8rem;">Deposit Funds</h2>
                        <p class="text-muted">Add funds to your wallet securely</p>
                    </div>
                </div>
                <!-- Profile Area (Same as other pages, skipping duplication for brevity if needed) -->
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

            <div class="glass-card" style="max-width: 800px; margin: 0 auto;">
                <form method="POST" enctype="multipart/form-data">
                    <h3 class="mb-4">1. Choose Payment Method</h3>
                    <div class="stats-grid mb-5" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                        <?php foreach($wallets as $network => $w): ?>
                        <div class="method-card" onclick="selectMethod('<?php echo htmlspecialchars($network); ?>', '<?php echo htmlspecialchars($w['address']); ?>', this)">
                            <i class="fas fa-wallet"></i>
                            <h4><?php echo htmlspecialchars($network); ?></h4>
                            <p class="text-muted" style="font-size: 0.8rem;">Network</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <input type="hidden" name="method" id="selected_method" required>
                    <input type="hidden" name="network" id="selected_network">

                    <div id="wallet_section" class="wallet-address-box mb-4">
                        <label class="text-muted mb-2" style="display:block;">Send funds to this address:</label>
                        <div class="d-flex" style="gap: 10px;">
                            <input type="text" id="wallet_address" class="form-control" readonly style="font-family: monospace; letter-spacing: 1px;">
                            <button type="button" class="btn btn-outline" onclick="copyAddress()"><i class="fas fa-copy"></i></button>
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-primary"><i class="fas fa-info-circle"></i> Only send to the specified network.</small>
                        </div>
                    </div>

                    <h3 class="mb-4">2. Enter Amount</h3>
                    <div class="mb-4">
                        <label style="display: block; margin-bottom: 10px; color: var(--color-text-muted);">Amount in USD</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); font-weight: 600; color: #fff;">$</span>
                            <input type="number" name="amount" class="form-control" placeholder="0.00" style="padding-left: 40px; font-size: 1.2rem; font-weight: 600;" min="100" required>
                        </div>
                        <p class="text-muted" style="font-size: 0.85rem; margin-top: 10px; text-align: right;">Min Deposit: $100.00</p>
                    </div>

                    <h3 class="mb-4">3. Upload Proof of Payment (Required)</h3>
                    <div class="mb-5">
                        <div class="file-upload-box">
                            <input type="file" name="proof" class="file-upload-input" accept="image/*,.pdf" onchange="showFileName(this)" required>
                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--color-primary); margin-bottom: 10px;"></i>
                            <h4 id="file_label" style="font-weight: 500;">Click to upload Screenshot</h4>
                            <p class="text-muted" style="font-size: 0.85rem;">Max file size: 5MB</p>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary w-100" onclick="confirmDeposit(this)">Proceed to Payment</button>
                    <button type="submit" name="deposit" style="display: none;"></button>
                </form>
            </div>
        </main>
    </div>

    <script src="../../asset/scripts/main.js"></script>
    <script>
        function selectMethod(network, address, element) {
            // UI
            document.querySelectorAll('.method-card').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            
            // Logic
            document.getElementById('selected_method').value = network;
            document.getElementById('selected_network').value = network;
            document.getElementById('wallet_address').value = address;
            
            // Show Wallet Box
            document.getElementById('wallet_section').style.display = 'block';
        }
        
        function copyAddress() {
            const addr = document.getElementById('wallet_address');
            addr.select();
            document.execCommand('copy');
            showNotification('Wallet address copied!', 'success');
        }

        function showFileName(input) {
            const label = document.getElementById('file_label');
            if (input.files && input.files.length > 0) {
                label.textContent = input.files[0].name;
                label.style.color = 'var(--color-primary)';
            } else {
                label.textContent = 'Click to upload Screenshot';
                label.style.color = '#fff';
            }
        }

        function confirmDeposit(btn) {
            const form = btn.closest('form');
            const amount = form.amount.value;
            const method = form.method.value;
            const proof = form.querySelector('input[type="file"]').value;
            
            if(!amount || !method) {
                showNotification('Please select a method and enter amount', 'error');
                return;
            }
            
            if(!proof) {
                showNotification('Please upload proof of payment', 'error');
                return;
            }

            showConfirmation(
                'Confirm Deposit', 
                `Create deposit request for <strong>$${amount}</strong> via ${method}?`, 
                () => {
                    form.querySelector('[name="deposit"]').click();
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
