<?php
require_once "isadmin.php";
require_once "../../asset/config/userdata.php";

$msg = "";
$error = "";
$editPlan = null;

// Handle Delete
if(isset($_POST['delete_plan'])) {
    $id = intval($_POST['plan_id']);
    // Check if any investments exist for this plan to prevent breaking FK
    $checkInv = $DB->query("SELECT id FROM investments WHERE plan_id = $id");
    if($checkInv->num_rows > 0) {
        $error = "Cannot delete this plan. It has active or past investments linked to it.";
    } else {
        if($DB->query("DELETE FROM plans WHERE id = $id")) {
            $msg = "Plan deleted successfully.";
        } else {
            $error = "Failed to delete plan.";
        }
    }
}

// Handle Create/Update
if(isset($_POST['save_plan'])) {
    $name = $DB->real_escape_string($_POST['name']);
    $roi = floatval($_POST['roi']);
    $duration = intval($_POST['duration']);
    $min = floatval($_POST['min']);
    $max = floatval($_POST['max']);
    
    if(isset($_POST['plan_id']) && !empty($_POST['plan_id'])) {
        // Update
        $id = intval($_POST['plan_id']);
        $query = "UPDATE plans SET name='$name', roi_percent=$roi, duration_days=$duration, min_amount=$min, max_amount=$max WHERE id=$id";
        if($DB->query($query)) {
            $msg = "Plan updated successfully.";
        } else {
            $error = "Failed to update plan: " . $DB->error;
        }
    } else {
        // Create
        $query = "INSERT INTO plans (name, roi_percent, duration_days, min_amount, max_amount) VALUES ('$name', $roi, $duration, $min, $max)";
        if($DB->query($query)) {
            $msg = "New plan created successfully.";
        } else {
            $error = "Failed to create plan: " . $DB->error;
        }
    }
}

// Fetch Plan for Edit
if(isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $DB->query("SELECT * FROM plans WHERE id = $id");
    if($res->num_rows > 0) {
        $editPlan = $res->fetch_assoc();
    }
}

// Fetch All Plans
$plans = $DB->query("SELECT * FROM plans ORDER BY min_amount ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Plans - Futura Admin</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background: rgba(15, 10, 20, 0.95); }
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
                <a href="index.php" class="nav-link"><i class="fas fa-chart-pie"></i> Overview</a>
                <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Manage Users</a>
                <a href="deposits.php" class="nav-link"><i class="fas fa-wallet"></i> Deposits</a>
                <a href="withdrawals.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Withdrawals</a>
                <a href="plans.php" class="nav-link active"><i class="fas fa-chart-line"></i> Manage Plans</a>
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
                        <h2 style="font-size: 1.8rem;">Investment Plans</h2>
                        <p style="color: var(--color-text-muted);">Create and modify packages</p>
                    </div>
                </div>
            </header>

            <div class="d-flex" style="gap: 30px; flex-wrap: wrap;">
                <!-- Form -->
                <div class="glass-card" style="flex: 1; min-width: 300px;">
                    <h3 class="mb-4"><?php echo $editPlan ? 'Edit Plan' : 'Create New Plan'; ?></h3>
                    <form method="POST">
                        <?php if($editPlan): ?>
                            <input type="hidden" name="plan_id" value="<?php echo $editPlan['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group mb-3">
                            <label class="text-muted mb-2 d-block">Plan Name</label>
                            <input type="text" name="name" class="form-control" required value="<?php echo $editPlan ? $editPlan['name'] : ''; ?>" placeholder="Ex: Starter Plan">
                        </div>
                        
                        <div class="row" style="display: flex; gap: 15px;">
                            <div class="col" style="flex: 1;">
                                <div class="form-group mb-3">
                                    <label class="text-muted mb-2 d-block">Daily ROI (%)</label>
                                    <input type="number" step="0.01" name="roi" class="form-control" required value="<?php echo $editPlan ? $editPlan['roi_percent'] : ''; ?>" placeholder="3.00">
                                </div>
                            </div>
                            <div class="col" style="flex: 1;">
                                <div class="form-group mb-3">
                                    <label class="text-muted mb-2 d-block">Duration (Days)</label>
                                    <input type="number" name="duration" class="form-control" required value="<?php echo $editPlan ? $editPlan['duration_days'] : ''; ?>" placeholder="30">
                                </div>
                            </div>
                        </div>

                        <div class="row" style="display: flex; gap: 15px;">
                            <div class="col" style="flex: 1;">
                                <div class="form-group mb-3">
                                    <label class="text-muted mb-2 d-block">Min Amount ($)</label>
                                    <input type="number" step="0.01" name="min" class="form-control" required value="<?php echo $editPlan ? $editPlan['min_amount'] : ''; ?>" placeholder="100.00">
                                </div>
                            </div>
                            <div class="col" style="flex: 1;">
                                <div class="form-group mb-4">
                                    <label class="text-muted mb-2 d-block">Max Amount ($)</label>
                                    <input type="number" step="0.01" name="max" class="form-control" required value="<?php echo $editPlan ? $editPlan['max_amount'] : ''; ?>" placeholder="1000.00">
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="save_plan" class="btn btn-primary" style="flex: 1;"><?php echo $editPlan ? 'Update Plan' : 'Create Plan'; ?></button>
                            <?php if($editPlan): ?>
                                <a href="plans.php" class="btn btn-outline" style="flex: 0.5; text-align: center;">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- List -->
                <div class="glass-card" style="flex: 1.5; min-width: 300px;">
                    <h3 class="mb-4">Existing Plans</h3>
                    <div class="table-container">
                        <table style="width: 100%; text-align: left; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <th style="padding: 10px;">Name</th>
                                    <th style="padding: 10px;">ROI</th>
                                    <th style="padding: 10px;">Days</th>
                                    <th style="padding: 10px;">Range</th>
                                    <th style="padding: 10px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($plans->num_rows > 0): ?>
                                    <?php while($row = $plans->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td style="padding: 10px; font-weight: 500;"><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td style="padding: 10px; color: var(--color-primary);"><?php echo $row['roi_percent']; ?>%</td>
                                        <td style="padding: 10px;"><?php echo $row['duration_days']; ?></td>
                                        <td style="padding: 10px; font-size: 0.85rem; color: var(--color-text-muted);">
                                            $<?php echo number_format($row['min_amount']); ?> - $<?php echo number_format($row['max_amount']); ?>
                                        </td>
                                        <td style="padding: 10px;">
                                            <div style="display: flex; gap: 5px;">
                                                <a href="plans.php?edit=<?php echo $row['id']; ?>" class="btn" style="padding: 4px 10px; font-size: 0.8rem; background: rgba(255,255,255,0.1); color: #fff; border-radius: 4px;">Edit</a>
                                                <button class="btn" 
                                                    data-id="<?php echo $row['id']; ?>" 
                                                    onclick="triggerDelete(this)"
                                                    style="padding: 4px 10px; font-size: 0.8rem; background: rgba(255,59,48,0.2); color: #ff3b30; border-radius: 4px; border: none; cursor: pointer;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="p-3 text-center text-muted">No plans found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../asset/scripts/main.js"></script>
    <script>
        function triggerDelete(btn) {
            showConfirmation(
                'Delete Plan?', 
                'Are you sure? This is risky if users have active investments in this plan.',
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'plan_id';
                    idInput.value = btn.dataset.id;
                    
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'delete_plan';
                    actionInput.value = 'true';
                    
                    form.appendChild(idInput);
                    form.appendChild(actionInput);
                    document.body.appendChild(form);
                    form.submit();
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
