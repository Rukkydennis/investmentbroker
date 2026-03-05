<?php
require_once "isadmin.php";
require_once "../../asset/config/DB.php";

$msg = "";
$error = "";

// Handle status updates
if (isset($_POST['update_status'])) {
    $ticket_id = $_POST['ticket_id'];
    $status = $_POST['status'];
    $query = "UPDATE tickets SET status = '$status' WHERE id = $ticket_id";
    if ($DB->query($query)) {
        $msg = "Ticket status updated to $status.";
    } else {
        $error = "Failed to update status.";
    }
}

// Fetch all tickets with user details
$ticketsQuery = "SELECT t.*, u.full_name, u.email FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC";
$ticketsResult = $DB->query($ticketsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - Admin</title>
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
                    Admin Panel</p>
                <a href="index.php" class="nav-link"><i class="fas fa-chart-pie"></i> Overview</a>
                <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Manage Users</a>
                <a href="deposits.php" class="nav-link"><i class="fas fa-wallet"></i> Deposits</a>
                <a href="withdrawals.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Withdrawals</a>
                <a href="plans.php" class="nav-link"><i class="fas fa-chart-line"></i> Manage Plans</a>
                <a href="tickets.php" class="nav-link active"><i class="fas fa-headset"></i> Support Tickets</a>
                <a href="admins.php" class="nav-link"><i class="fas fa-user-shield"></i> Admins</a>
            </nav>

            <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="logout.php" class="nav-link" style="color: #ff3b30;"><i class="fas fa-sign-out-alt"></i>
                    Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div style="display: flex; align-items: center;">
                    <div class="mobile-toggle-sidebar" style="margin-right: 15px;">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.8rem;">Support Tickets</h2>
                        <p class="text-muted">Manage user inquiries and complaints</p>
                    </div>
                </div>
            </header>

            <div class="glass-card">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color); text-align: left;">
                                <th style="padding: 15px; color: var(--color-text-muted);">User</th>
                                <th style="padding: 15px; color: var(--color-text-muted);">Subject</th>
                                <th style="padding: 15px; color: var(--color-text-muted);">Date</th>
                                <th style="padding: 15px; color: var(--color-text-muted);">Status</th>
                                <th style="padding: 15px; color: var(--color-text-muted);">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($ticketsResult->num_rows > 0): ?>
                                <?php while($row = $ticketsResult->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td style="padding: 15px;">
                                            <div style="font-weight: 500;"><?php echo $row['full_name']; ?></div>
                                            <div style="font-size: 0.8rem; color: var(--color-text-muted);"><?php echo $row['email']; ?></div>
                                        </td>
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
                                        <td style="padding: 15px; display: flex; gap: 10px;">
                                            <a href="ticket-chat.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-reply"></i> Reply
                                            </a>
                                            <?php if ($status != 'closed'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="ticket_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="status" value="closed">
                                                <button type="submit" name="update_status" class="btn btn-sm" style="background: rgba(255, 59, 48, 0.1); color: #ff3b30; border: 1px solid rgba(255, 59, 48, 0.2);">
                                                    Close
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="ticket_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="status" value="open">
                                                <button type="submit" name="update_status" class="btn btn-sm" style="background: rgba(0, 255, 136, 0.1); color: var(--color-primary); border: 1px solid rgba(0, 255, 136, 0.2);">
                                                    Re-open
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted" style="padding: 20px;">No tickets found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../../asset/scripts/main.js"></script>
    <?php if($msg): ?>
    <script>window.onload = function() { showNotification('<?php echo $msg; ?>', 'success'); }</script>
    <?php endif; ?>
</body>

</html>
