<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Vendors</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='agent'>
	<?php $__headline__=$agent->IsLoggedIn()?'Vendors & Other Sidebar Items':'Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('VENDORS','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
	{
		echo("<div id='".$agent->GetFieldName('ListVendorsContainer')."'>");
		$agent->ListVendors($HTTP_GET_VARS);
		echo("</div>");

		echo("<div id='".$agent->GetFieldName('ListWidgetsContainer')."'>");
		$agent->ListWidgets($HTTP_GET_VARS);
		echo("</div>");

  	}
?>	
 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('CLIENTS','AGENT'); ?>
</body>
</html>