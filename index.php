<?php
// index.php
require_once 'db.php';
require_once 'Deepink.php';

session_start();
$db = new Database();
$pdo = $db->getPDO();

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Handle report generation
if (isset($_POST['url']) && isset($_SESSION['user_id'])) {
    try {
        $web = new DeepPink($_POST['url']);
        $report_html = $web->generateReport();
        
        $stmt = $pdo->prepare("INSERT INTO reports (user_id, url, report_html) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $_POST['url'], $report_html]);
        
        $_SESSION['message'] = "Report generated successfully!";
        header("Location: index.php?page=reports");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle report deletion
if (isset($_GET['delete']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    $_SESSION['message'] = "Report deleted successfully!";
    header("Location: index.php?page=reports");
    exit();
}

// Get user's reports
$reports = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $reports = $stmt->fetchAll();
}

// Determine current page
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html>
<head>
    <title>DeepPink Analysis Tool</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/dashicons@9.4.0/css/dashicons.min.css" rel="stylesheet">
</head>
<body id="wpwrap">
    <?php if (isset($_SESSION['user_id'])): ?>
        <div id="wpadminbar">
            <div class="ab-item">DeepPink Analysis Tool</div>
            <div class="ab-item">Howdy, <?= htmlspecialchars($_SESSION['name']) ?></div>
            <a href="?logout" class="ab-item">Log Out</a>
        </div>
    <?php endif; ?>
    
    <div id="wpcontent">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div id="adminmenu">
                <ul>
                    <li class="<?= $current_page == 'dashboard' ? 'current' : '' ?>">
                        <a href="?page=dashboard">
                            <span class="dashicons dashicons-dashboard"></span>
                            Dashboard
                        </a>
                    </li>
                    <li class="<?= $current_page == 'new-report' ? 'current' : '' ?>">
                        <a href="?page=new-report">
                            <span class="dashicons dashicons-plus-alt"></span>
                            Create New Report
                        </a>
                    </li>
                    <li class="<?= $current_page == 'reports' ? 'current' : '' ?>">
                        <a href="?page=reports">
                            <span class="dashicons dashicons-list-view"></span>
                            View Reports
                        </a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
        
        <div id="wpbody">
            <div class="wrap">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="notice notice-success">
                        <p><?= $_SESSION['message'] ?></p>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="notice notice-error">
                        <p><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <h1>DeepPink Login</h1>
                    <form method="POST" class="form-table">
                        <table>
                            <tr>
                                <th><label for="username">Username</label></th>
                                <td><input type="text" name="username" id="username" required></td>
                            </tr>
                            <tr>
                                <th><label for="password">Password</label></th>
                                <td><input type="password" name="password" id="password" required></td>
                            </tr>
                            <tr>
                                <th></th>
                                <td><input type="submit" name="login" value="Log In" class="button"></td>
                            </tr>
                        </table>
                    </form>
                <?php else: ?>
                    <?php if ($current_page == 'dashboard'): ?>
                        <h1>Dashboard</h1>
                        <p>Welcome to DeepPink Analysis Tool. Use the navigation menu to create or view reports.</p>
                        <div class="card">
                            <h2>Recent Reports</h2>
                            <?php if (empty($reports)): ?>
                                <p>No reports yet. Create your first report!</p>
                            <?php else: ?>
                                <table class="wp-list-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>URL</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($reports, 0, 5) as $report): ?>
                                            <tr>
                                                <td><?= date('Y-m-d H:i', strtotime($report['created_at'])) ?></td>
                                                <td><?= htmlspecialchars($report['url']) ?></td>
                                                <td>
                                                    <span class="row-actions">
                                                        <a href="?page=view-report&id=<?= $report['id'] ?>">View</a>
                                                        <a href="?delete=<?= $report['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    
                    <?php elseif ($current_page == 'new-report'): ?>
                        <h1>Create New Report</h1>
                        <form method="POST" class="form-table">
                            <table>
                                <tr>
                                    <th><label for="url">Website URL</label></th>
                                    <td>
                                        <input type="url" name="url" id="url" required placeholder="https://example.com">
                                        <p class="description">Enter the full URL including http:// or https://</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td><input type="submit" value="Analyze" class="button button-primary"></td>
                                </tr>
                            </table>
                        </form>
                    
                    <?php elseif ($current_page == 'reports'): ?>
                        <h1>Reports</h1>
                        <?php if (empty($reports)): ?>
                            <p>No reports yet. <a href="?page=new-report">Create your first report</a>.</p>
                        <?php else: ?>
                            <table class="wp-list-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>URL</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report): ?>
                                        <tr>
                                            <td><?= date('Y-m-d H:i', strtotime($report['created_at'])) ?></td>
                                            <td><?= htmlspecialchars($report['url']) ?></td>
                                            <td>
                                                <span class="row-actions">
                                                    <a href="?page=view-report&id=<?= $report['id'] ?>">View</a>
                                                    <a href="?delete=<?= $report['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    
                    <?php elseif ($current_page == 'view-report' && isset($_GET['id'])): ?>
                        <?php 
                        $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ? AND user_id = ?");
                        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
                        $report = $stmt->fetch();
                        ?>
                        <?php if ($report): ?>
                            <h1>Report for <?= htmlspecialchars($report['url']) ?></h1>
                            <p>Generated on <?= date('Y-m-d H:i', strtotime($report['created_at'])) ?></p>
                            <div class="report-content">
                                <?= $report['report_html'] ?>
                            </div>
                            <a href="?page=reports" class="button">Back to reports</a>
                        <?php else: ?>
                            <p>Report not found.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>