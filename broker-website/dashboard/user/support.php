<?php
require_once "../../asset/config/isuser.php";
require_once "../../asset/config/userdata.php";

$msg = "";
$error = "";

// Handle New Ticket
if(isset($_POST['create_ticket'])) {
    $subject = $_POST['subject'];
    $priority = $_POST['priority'];
    $message = $_POST['message'];
    
    if(!empty($subject) && !empty($message)) {
        // Create Ticket
        $query = "INSERT INTO tickets (user_id, subject, priority, status) VALUES (" . $user['id'] . ", '$subject', '$priority', 'open')";
        if($DB->query($query)) {
            $ticket_id = $DB->insert_id;
            // Create Initial Message
            $msgQuery = "INSERT INTO ticket_messages (ticket_id, sender_type, message) VALUES ($ticket_id, 'user', '$message')";
            $DB->query($msgQuery);
            
            header("Location: support.php?msg=success");
            exit();
        } else {
            $error = "Failed to create ticket.";
        }
    } else {
        $error = "Subject and Message are required.";
    }
}

if(isset($_GET['msg']) && $_GET['msg'] == 'success') {
    $msg = "Ticket created successfully!";
}

// Fetch Tickets
$ticketsQuery = "SELECT * FROM tickets WHERE user_id = " . $user['id'] . " ORDER BY created_at DESC";
$ticketsResult = $DB->query($ticketsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Futura Brokerage</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ticket-status { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .status-open { background: rgba(0, 255, 136, 0.1); color: var(--color-primary); }
        .status-closed { background: rgba(255, 59, 48, 0.1); color: #ff3b30; }
        .status-answered { background: rgba(0, 204, 255, 0.1); color: var(--color-secondary); }
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
                <a href="deposit.php" class="nav-link"><i class="fas fa-wallet"></i> Deposit</a>
                <a href="plans.php" class="nav-link"><i class="fas fa-chart-line"></i> Invest Plans</a>
                <a href="withdraw.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Withdraw</a>
                <a href="transactions.php" class="nav-link"><i class="fas fa-history"></i> Transactions</a>
                <a href="referrals.php" class="nav-link"><i class="fas fa-users"></i> Referrals</a>
                <a href="support.php" class="nav-link active"><i class="fas fa-headset"></i> Support</a>
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
                        <h2 style="font-size: 1.8rem;">Support Center</h2>
                        <p class="text-muted">Get help with your account</p>
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
                <!-- Create Ticket Form -->
                <div class="glass-card">
                    <h3 style="margin-bottom: 25px;"><i class="fas fa-plus-circle" style="color: var(--color-primary); margin-right: 10px;"></i> Open New Ticket</h3>
                    <form method="POST">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 10px; color: var(--color-text-muted);">Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="Briefly describe the issue" required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 10px; color: var(--color-text-muted);">Priority</label>
                            <select name="priority" class="form-control" style="cursor: pointer;">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label style="display: block; margin-bottom: 10px; color: var(--color-text-muted);">Message</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Describe your problem in detail..." required></textarea>
                        </div>

                        <button type="submit" name="create_ticket" class="btn btn-primary" style="width: 100%;">Submit Ticket</button>
                    </form>
                </div>

                <!-- Ticket History -->
                <div>
                    <div class="glass-card">
                        <h3 style="margin-bottom: 20px;"><i class="fas fa-history" style="color: var(--color-text-muted); margin-right: 10px;"></i> Ticket History</h3>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left;">
                                        <th style="padding: 15px; color: var(--color-text-muted);">Subject</th>
                                        <th style="padding: 15px; color: var(--color-text-muted);">Last Update</th>
                                        <th style="padding: 15px; color: var(--color-text-muted);">Status</th>
                                        <th style="padding: 15px; color: var(--color-text-muted);">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($ticketsResult->num_rows > 0): ?>
                                        <?php while($row = $ticketsResult->fetch_assoc()): ?>
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                                <td style="padding: 15px; font-weight: 500;"><?php echo $row['subject']; ?></td>
                                                <td style="padding: 15px; font-size: 0.9rem; color: var(--color-text-muted);"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                                <td style="padding: 15px;">
                                                    <?php 
                                                        $status = $row['status'];
                                                        $class = 'status-open';
                                                        if($status == 'answered') $class = 'status-answered';
                                                        if($status == 'closed') $class = 'status-closed';
                                                    ?>
                                                    <span class="ticket-status <?php echo $class; ?>"><?php echo ucfirst($status); ?></span>
                                                </td>
                                                <td style="padding: 15px;">
                                                    <a href="ticket-chat.php?id=<?php echo $row['id']; ?>" class="btn btn-sm" style="padding: 5px 15px; border: 1px solid var(--color-primary); color: var(--color-primary); border-radius: 4px; font-size: 0.8rem;">
                                                        <i class="fas fa-comments"></i> Chat
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted" style="padding: 20px;">No tickets found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../asset/scripts/main.js"></script>
    <?php if($msg): ?>
    <script>window.onload = function() { showNotification('<?php echo $msg; ?>', 'success'); }</script>
    <?php endif; ?>
    <?php if($error): ?>
    <script>window.onload = function() { showNotification('<?php echo $error; ?>', 'error'); }</script>
    <?php endif; ?>
</body>

</html>
