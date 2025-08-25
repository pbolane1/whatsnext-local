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
	<?php info_bubble::ListAll('TIMELINE','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php

	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
	{
	 	echo("<div id='activitylog_container'>");
		$agent->ViewActivity($HTTP_GET_VARS);
		echo("</div>");
	}

	$user=new user($HTTP_GET_VARS['user_id']);	
	if($user->Get('user_active'))
		echo("<div class='back_actions'><a href='".$agent->GetUserURL($user)."'>Back To Client Timeline</a></div>");	
	else
		echo("<div class='back_actions'><a href='past.php'>Back To Past Transactions</a></div>");	
?>		

 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('TIMELINE','AGENT'); ?>
</body>
</html>