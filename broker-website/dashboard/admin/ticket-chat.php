<?php
require_once "isadmin.php";
require_once "../../asset/config/DB.php";

$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id) {
    header("Location: tickets.php");
    exit();
}

// Fetch Ticket Info with User Details
$ticketQuery = "SELECT t.*, u.full_name, u.email, u.balance FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = $ticket_id";
$ticketResult = $DB->query($ticketQuery);

if ($ticketResult->num_rows == 0) {
    header("Location: tickets.php");
    exit();
}

$ticket = $ticketResult->fetch_assoc();

// Handle Reply
if (isset($_POST['send_reply'])) {
    $message = $DB->real_escape_string($_POST['message']);
    if (!empty($message)) {
        $query = "INSERT INTO ticket_messages (ticket_id, sender_type, message) VALUES ($ticket_id, 'admin', '$message')";
        if ($DB->query($query)) {
            // Update ticket status to answered
            $DB->query("UPDATE tickets SET status = 'answered' WHERE id = $ticket_id");
            header("Location: ticket-chat.php?id=$ticket_id");
            exit();
        }
    }
}

// Handle Status Toggle
if (isset($_POST['toggle_status'])) {
    $newStatus = ($ticket['status'] == 'closed') ? 'open' : 'closed';
    $DB->query("UPDATE tickets SET status = '$newStatus' WHERE id = $ticket_id");
    header("Location: ticket-chat.php?id=$ticket_id");
    exit();
}

// Fetch Messages
$messagesQuery = "SELECT * FROM ticket_messages WHERE ticket_id = $ticket_id ORDER BY created_at ASC";
$messagesResult = $DB->query($messagesQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ticket #<?php echo $ticket_id; ?> - Admin</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .chat-container {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 200px);
            background: var(--color-bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.02);
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .message {
            max-width: 70%;
            padding: 15px;
            border-radius: 12px;
            font-size: 0.95rem;
            line-height: 1.5;
            position: relative;
        }

        .message.support {
            align-self: flex-end;
            background: rgba(0, 204, 255, 0.1);
            border: 1px solid rgba(0, 204, 255, 0.2);
            color: #fff;
            border-bottom-right-radius: 2px;
        }

        .message.user {
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: #ccc;
            border-bottom-left-radius: 2px;
        }

        .message-time {
            font-size: 0.75rem;
            color: var(--color-text-muted);
            margin-top: 5px;
            text-align: right;
            display: block;
        }

        .chat-input-area {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            gap: 15px;
        }

        .chat-input {
            flex: 1;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            color: #fff;
            outline: none;
            resize: none;
        }

        .chat-input:focus {
            border-color: var(--color-primary);
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: capitalize;
        }
        .status-open { background: rgba(0, 255, 136, 0.1); color: var(--color-primary); }
        .status-answered { background: rgba(0, 204, 255, 0.1); color: var(--color-secondary); }
        .status-closed { background: rgba(255, 59, 48, 0.1); color: #ff3b30; }
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
                <p style="padding-left: 15px; margin-bottom: 10px; font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 1px;">Admin Panel</p>
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
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="mobile-toggle-sidebar" style="font-size: 1.5rem; cursor: pointer; margin-right: 15px;">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.8rem;">Manage Ticket #<?php echo $ticket_id; ?></h2>
                        <a href="tickets.php" style="color: var(--color-text-muted); font-size: 0.9rem;"><i
                                class="fas fa-arrow-left"></i> Back to Tickets</a>
                    </div>
                </div>
            </header>

            <div class="chat-container">
                <div class="chat-header">
                    <div>
                        <div style="margin-bottom: 10px; font-weight: 500; display: flex; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <span>User: <span style="color: #fff;"><?php echo $ticket['full_name']; ?></span></span> 
                            <span style="color: var(--color-text-muted); font-size: 0.85rem;"><i class="fas fa-envelope"></i> <?php echo $ticket['email']; ?></span>
                            <span style="color: var(--color-text-muted); font-size: 0.85rem;"><i class="fas fa-wallet"></i> Balance: <span style="color: var(--color-primary);">$<?php echo number_format($ticket['balance'], 2); ?></span></span>
                        </div>
                        <h4 style="margin-bottom: 5px;">Subject: <?php echo htmlspecialchars($ticket['subject']); ?></h4>
                        <span class="status-badge status-<?php echo $ticket['status']; ?>">Status: <?php echo $ticket['status']; ?></span>
                    </div>
                    <div>
                        <form method="POST">
                            <button type="submit" name="toggle_status" class="btn btn-sm" style="background: <?php echo $ticket['status'] == 'closed' ? 'rgba(0, 255, 136, 0.1)' : 'rgba(255, 59, 48, 0.1)'; ?>; color: <?php echo $ticket['status'] == 'closed' ? 'var(--color-primary)' : '#ff3b30'; ?>; border: 1px solid currentColor;">
                                <?php echo $ticket['status'] == 'closed' ? 'Re-open Ticket' : 'Close Ticket'; ?>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <?php while($msg = $messagesResult->fetch_assoc()): ?>
                        <div class="message <?php echo $msg['sender_type'] == 'admin' ? 'support' : 'user'; ?>">
                            <div style="font-weight: 700; font-size: 0.8rem; margin-bottom: 5px; color: <?php echo $msg['sender_type'] == 'admin' ? 'var(--color-secondary)' : 'var(--color-primary)'; ?>;">
                                <?php echo $msg['sender_type'] == 'admin' ? ($admin['full_name'] ?? 'Admin') . ' (You)' : $ticket['full_name']; ?>
                            </div>
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            <span class="message-time"><?php echo date('M d, H:i', strtotime($msg['created_at'])); ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($ticket['status'] != 'closed'): ?>
                <form method="POST" class="chat-input-area">
                    <input type="text" name="message" class="chat-input" placeholder="Type your reply..." required>
                    <button type="submit" name="send_reply" class="btn btn-primary" style="padding: 10px 20px;"><i
                            class="fas fa-paper-plane"></i></button>
                </form>
                <?php else: ?>
                <div class="chat-input-area" style="justify-content: center; color: var(--color-text-muted);">
                    <p><i class="fas fa-lock"></i> This ticket is closed. Re-open to send messages.</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../../asset/scripts/main.js"></script>
    <script>
        // Auto scroll to bottom
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    </script>
</body>

</html>
