<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Transactions</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
	<style>
		.dev_notice{display:none}		
	</style/>
</head>
<body class='agent'>
<?php
	$agent->SetFlag('REGISTER',true);
	$agent->RegisterForm();	
?>	
</div>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('CLIENTS','AGENT'); ?>
</body>
</html>