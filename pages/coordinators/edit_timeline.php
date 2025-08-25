<?php include('../../include/common.php') ?>
<?php include('../../include/_coordinator.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agent Manager - Transacrtion Timeline</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='x_agent buyer'>
	<?php $__headline__=$coordinator->IsLoggedIn()?'Timeline Template':'Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('TIMELINE','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	$agent=new agent();
	if(!$coordinator->IsLoggedIn())
		$coordinator->LoginForm();
	else
	{
	 	echo("<div id='intro_container'>");
		$coordinator->EditTemplateIntro($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_container' class='hidden-xs'>");
		$coordinator->AgentTools($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_buttons_container' class='hidden-xs'>");
		$coordinator->AgentToolsButtons($HTTP_GET_VARS);
		echo("</div>");
		echo("<div class='row timeline_row'>");
		echo("<div class='col-sm-7 timeline_col'>");
	 	echo("<div id='timeline_container'>");
		$coordinator->EditTimeline($HTTP_GET_VARS);
		echo("</div>");
		echo("</div>");
		echo("<div class='col-sm-5 sidebar_col'>");
	 	echo("<div id='sidebar_container'>");
		//$coordinator->EditSidebar($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_xs_container' class='visible-xs'>");
		$coordinator->AgentToolsXS($HTTP_GET_VARS);
		echo("</div>");
		echo("</div>");
		echo("</div>");		
	}
?>		
	<div class='back_actions'><a href='templates.php'>Back To Templates</a> | <a href='index.php'>Back To Clients</a></div>

 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('TIMELINE','AGENT'); ?>
</body>
</html>