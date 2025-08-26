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
	<?php 
		$__headline__='Agent Login';
		if($agent->IsLoggedIn())
		{
			$__headline__='Manage Timeline';
			$temp=new user($HTTP_GET_VARS['user_id']);
			if($temp->Get('user_name'))
				$__headline__.=' - '.$temp->Get('user_name');
			else if($temp->Get('user_address'))
				$__headline__.=' - '.$temp->Get('user_address');
		}
	?>
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
	 	echo("<div id='intro_container'>");
		$agent->EditIntro($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_container' class='hidden-xs'>");
		$agent->AgentTools($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='headine_container'>");
		$agent->EditHeadline($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_buttons_container' class='hidden-xs'>");
		$agent->AgentToolsButtons($HTTP_GET_VARS);
		echo("</div>");
		echo("<div id='WelcomeEmailNoticeContainer'>");
		$agent->WelcomeEmailNotice($HTTP_GET_VARS);	
		echo("</div>");
		echo("<div id='SendKeyDatesNoticeContainer'>");
		$agent->SendKeyDatesNotice($HTTP_GET_VARS);	
		echo("</div>");

		echo("<div class='row timeline_row'>");
		echo("<div class='col-sm-7 timeline_col'>");
	 	echo("<div id='timeline_container'>");
		$agent->EditTimeline($HTTP_GET_VARS);
		echo("</div>");
		echo("</div>");
		echo("<div class='col-sm-5 sidebar_col'>");
	 	echo("<div id='sidebar_container'>");
		$agent->EditSidebar($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_xs_container' class='visible-xs'>");
		$agent->AgentToolsXS($HTTP_GET_VARS);
		echo("</div>");
		echo("</div>");
		echo("</div>");
		
		//
		$__VIEWIED_USER_ID__=$HTTP_GET_VARS['user_id'];
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