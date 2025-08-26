<?php
require_once('../../include/common.php');

// Check if user is logged in as agent
$agent = new agent();
if(!$agent->IsLoggedIn()) {
    header('Location: index.php');
    exit;
}

$action = $_GET['action'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$token = $_GET['token'] ?? '';

if($action == 'opt_out' && $user_id && $token) {
    // Verify token
    $user = new user($user_id);
    if($user->id && $token == md5($user_id . $user->Get('user_email') . 'opt_out')) {
        // Mark user as opted out of email notifications
        $user->Set('user_email_notifications', 0);
        $user->Update();
        
        // Log the opt-out action
        activity_log::Log($agent, 'USER_OPT_OUT', 'User opted out of email notifications', $user_id);
        
        $message = "Successfully opted out of email notifications for this transaction.";
        $message_type = "success";
    } else {
        $message = "Invalid opt-out link. Please contact support if you need assistance.";
        $message_type = "error";
    }
} else {
    $message = "Invalid request.";
    $message_type = "error";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Opt Out - Email Notifications</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../css/global.css">
    <style>
        .message-box {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="message-box <?php echo $message_type; ?>">
        <h2><?php echo $message_type == 'success' ? '✓ Success' : '✗ Error'; ?></h2>
        <p><?php echo $message; ?></p>
        <a href="index.php" class="btn">Return to Dashboard</a>
    </div>
</body>
</html>
