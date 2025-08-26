<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Transactions</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='agent'>
	<?php $__headline__=$agent->IsLoggedIn()?'Agent Dashboard':'Agent Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('CLIENTS','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
	{
		echo("<div id='".$agent->GetFieldName('ListUsersContainer')."'>");
		$agent->ListUsers($HTTP_GET_VARS);
		echo("</div>");
		$agent->EditUserArea($HTTP_GET_VARS);
  	}
?>	

<?php if (isset($_SESSION['user_id'])): ?>
<!-- PB Hardcoded for now -->
<h3>Shortcuts to Useful Docs</h3>
<hr>

<h4>Buyers</h4>
<p><a href="../uploads/files/Buyer-Overview.pdf" target="_blank">Overview of the Process - Buyers</a></p>
<p><a href="../uploads/files/Buyer-Questionnaire.pdf" target="_blank">Buyer Questionnaire</a></p>
<p><a href="../uploads/files/Final-Walkthrough-Checklist.pdf" target="_blank">Final Walkthrough Checklist</a></p>
<p>&nbsp;</p>

<h4>Sellers</h4>
<p><a href="../uploads/files/Seller-Overview.pdf" target="_blank">Overview of the Process - Sellers</a></p>
<p><a href="../uploads/files/Seller-Questionnaire.pdf" target="_blank">Seller Questionnaire</a></p>
<p><a href="../uploads/files/Statement-of-Info.pdf" target="_blank">Title - Statement of Information</a></p>
<p><a href="../uploads/files/Getting-Your-Home-Ready-Checklist.pdf" target="_blank">Getting Your Home Ready To Sell Checklist</a></p>
<p><a href="../uploads/files/Photography-Prep-Checklist.pdf" target="_blank">Photography Prep Checklist</a></p>
<p><a href="../uploads/files/Info-For-Buyer.pdf" target="_blank">Seller Info for Buyer</a></p>
<p><a href="../uploads/files/Services-Directory.pdf" target="_blank">San Diego County Utility &amp; Services Directory</a></p>
<p>&nbsp;</p>

<h4>Agents</h4>
<p><a href="https://docs.google.com/spreadsheets/d/1ed2B2vDotnVfA1mqFzn_bYcc3z4O2U3n-FsKJXPlr2k/copy" target="_blank">Document Review Checklist (Google Sheets)</a></p>
<p><a href="https://docs.google.com/spreadsheets/d/e/2PACX-1vQ25E7L0hg3De_4f3Le_m-kbV1hCib724P3wDz0mcM32EWepkzlUgb_RpiiBk3giw5P3F_7s_JmnqHD/pub?output=xlsx" target="_blank">Document Review Checklist (Excel)</a></p>
<p>&nbsp;</p>

<p><a href="https://docs.google.com/spreadsheets/d/1AUsvlETwP2wQxWN7HKiVboXWZM19a5Q3_B7kJMONF3c/copy" target="_blank">Compare Offers Spreadsheet (Google Sheets)</a></p>
<p><a href="https://docs.google.com/spreadsheets/d/1QSu1FhiT7nW9_CuLejsPupTbdOGAPwE5UFmfJAYKsu0/pub?output=xlsx" target="_blank">Compare Offers Spreadsheet (Excel)</a></p>
<p>&nbsp;</p>

<p><a href="../uploads/files/AVID-Guidelines-Checklist.pdf" target="_blank">Agent Inspection Checklist</a></p>
<?php endif; ?>
 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('CLIENTS','AGENT'); ?>
</body>
</html>