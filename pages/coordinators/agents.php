<?php include('../../include/common.php') ?>
<?php include('../../include/_coordinator.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agent Manager - Transaction Dashboard</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='coordinator'>
	<?php $__headline__=$coordinator->IsLoggedIn()?'Manager Dashboard':'Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('CLIENTS','COORDINATOR'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$coordinator->IsLoggedIn())
		$coordinator->LoginForm();
	else
	{
		
		echo("<p>&nbsp;</p><h1 class='agent_color1'>My Agents</h1>");
		echo("<div id='".$coordinator->GetFieldName('ListAgentsFiltersContainer')."'>");
		$coordinator->ListAgentsFilters();
		echo("</div>");
		echo("<div id='".$coordinator->GetFieldName('ListAgentsContainer')."'>");
		$coordinator->ListAgents($HTTP_GET_VARS);
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