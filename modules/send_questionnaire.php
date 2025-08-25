<?php
include("../include/common.php");
include("../include/_admin.php");

session_start();

// Validate session
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

$user_id = $_SESSION['user_id'];

// Load user info
$db = new db();
$user = $db->get_row("SELECT * FROM users WHERE user_id = ?", [$user_id]);

if (!$user) {
    die("User not found");
}

// Find the user's agent
$agent = $db->get_row("SELECT * FROM agents WHERE agent_id = ?", [$user['agent_id']]);

// Collect form fields
$form_data = $_POST;

// Build email content
$fields_html = "";
foreach ($form_data as $key => $value) {
    $label = ucwords(str_replace('_', ' ', $key));
    $fields_html .= "<tr><td style='padding:8px;border:1px solid #ccc;'><strong>$label</strong></td><td style='padding:8px;border:1px solid #ccc;'>" . nl2br(htmlspecialchars($value)) . "</td></tr>";
}

$html_email = "
<html>
  <head>
    <style>
      body { font-family: sans-serif; font-size: 14px; }
      table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    </style>
  </head>
  <body>
    <p>Hi,</p>
    <p>The following buyer questionnaire was submitted by <strong>{$user['user_name']}</strong>:</p>
    <table>$fields_html</table>
  </body>
</html>
";

// Store submission in DB
$db->insert("questionnaire_submissions", [
    "user_id" => $user_id,
    "agent_id" => $user['agent_id'],
    "submitted_at" => date("Y-m-d H:i:s"),
    "form_data" => json_encode($form_data)
]);

// Email recipients
$to = $user['user_email'];
$cc = $agent ? $agent['agent_email'] : null;
$subject = "Buyer Questionnaire Submitted";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: no-reply@whatsnext.realestate" . "\r\n";
if ($cc) $headers .= "Cc: $cc\r\n";

// Send email
mail($to, $subject, $html_email, $headers);

// Redirect with success message
header("Location: " . $_SERVER['HTTP_REFERER'] . "?submitted=true");
exit;
?>