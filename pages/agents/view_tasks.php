<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Transaction Timeline</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='x_agent buyer'>
	<?php $__headline__=$agent->IsLoggedIn()?$agent->Get('agent_name'):'Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('CLIENT_TIMELINE','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
	{
		echo("<div class='row'>");
		echo("<div class='col-sm-7'>");
	 	echo("<div id='timeline_container'>");
		$agent->ViewTasks($HTTP_POST_VARS);
		echo("</div>");
		echo("</div>");
		echo("<div class='col-sm-5'>");
	 	echo("<div id='sidebar_container'>");
//		$agent->EditSidebar($HTTP_GET_VARS);
		echo("</div>");
		echo("</div>");
		echo("</div>");

		$agent->RecentActivityCheck($HTTP_POST_VARS);
	}
?>		

 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('CLIENT_TIMELINE','AGENT'); ?>
</body>
</html>