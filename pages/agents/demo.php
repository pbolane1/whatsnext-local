<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Demo Info Popups</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
	
	<!--DEMO-->
	<style type='text/css'>
		.info_bubble{position:absolute;left:0px;right:0px;}
	</style>
	
</head>
<body class='agent DEMO'>
	<?php $__headline__=$agent->IsLoggedIn()?$agent->Get('agent_name'):'Agent Login';?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
	{
		echo("<div class='row'>");
		echo("<div class='col-sm-9 col-xs-11'>");
		$agent->ListUsersFilters();
		echo("<div id='".$agent->GetFieldName('ListUsersContainer')."'>");
		$agent->DEMOListUsers();
		echo("</div>");
		echo("</div>");
		echo("<div class='col-sm-3 col-xs-1'>");
		echo("<div style='position:relative'>");
		info_bubble::ListAll('CLIENTS','AGENT');
		echo("</div>");
		echo("</div>");
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