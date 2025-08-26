<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Transacrtion Timeline</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='x_agent buyer'>
	<?php $__headline__=$agent->IsLoggedIn()?$agent->Get('agent_name'):'Agent Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('TIMELINE','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php

	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
	{
	 	echo("<div id='intro_container'>");
		$agent->EditTemplateIntro($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_container' class='hidden-xs'>");
		$agent->AgentTools($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_buttons_container' class='hidden-xs'>");
		$agent->AgentToolsButtons($HTTP_GET_VARS);
		echo("</div>");
		echo("<div class='row timeline_row'>");
		echo("<div class='col-sm-7 timeline_col'>");
	 	echo("<div id='timeline_container'>");
		$agent->EditTimeline($HTTP_GET_VARS);
		echo("</div>");
		echo("</div>");
		echo("<div class='col-sm-5 sidebar_col'>");
	 	echo("<div id='sidebar_container'>");
		//$agent->EditSidebar($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_xs_container' class='visible-xs'>");
		$agent->AgentToolsXS($HTTP_GET_VARS);
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