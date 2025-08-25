<?php include('../../include/common.php') ?>
<?php include('../../include/_coordinator.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agent Manager - Settings</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='coordinator'>
	<?php $__headline__=$coordinator->IsLoggedIn()?'Manage Settings':'Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('SETTINGS','coordinator'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$coordinator->IsLoggedIn())
		$coordinator->LoginForm();
	else
	{
		echo("<div id='EditSettingsContainer'>");
		$coordinator->EditSettings();
		echo("</div>");
  	}
?>	
 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('SETTINGS','coordinator'); ?>
</body>
</html>