<?php include('../../include/common.php') ?>
<?php include('../../include/_coordinator.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Transaction Coordinators - Vendors</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='coordinator'>
	<?php $__headline__=$coordinator->IsLoggedIn()?'Vendors & Other Sidebar Items':'Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('VENDORS','coordinator'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$coordinator->IsLoggedIn())
		$coordinator->LoginForm();
	else
	{
		echo("<div id='".$coordinator->GetFieldName('ListVendorsContainer')."'>");
		$coordinator->ListVendors($HTTP_GET_VARS);
		echo("</div>");
  	}
?>	
	 	    </div>
	    </div>
	</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('CLIENTS','coordinator'); ?>
</body>
</html>