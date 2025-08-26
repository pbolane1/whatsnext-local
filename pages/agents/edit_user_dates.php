<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Transaction Dates</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='x_agent buyer'>
	<?php 
		$__headline__='Agent Login';
		if($agent->IsLoggedIn())
		{
			$__headline__="Terms Of Contract";
			$temp=new user($HTTP_GET_VARS['user_id']);
			if($temp->Get('user_name'))
				$__headline__.=' - '.$temp->Get('user_name');
			else if($temp->Get('user_address'))
				$__headline__.=' - '.$temp->Get('user_address');
		}	
	?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('CLIENT_DATES','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
	{
	 	echo("<div id='property_info_container'>");
		$agent->EditPropertyInfo($HTTP_GET_VARS);
		echo("</div>");

	 	echo("<div id='user_dates_container'>");
		$agent->EditUserDates($HTTP_GET_VARS);
		echo("</div>");
	}
?>		

	<div class='disclaimer'>While What's Next makes every attempt to automatically calculate due dates so that they don't land on weekends or holidays,<br>please note that you should double-check all for accuracy as <b>official holidays vary by county.</b></div>

 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('CLIENT_DATES','AGENT'); ?>
</body>
</html>