<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Property Contacts</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='agent'>
	<?php $__headline__=$agent->IsLoggedIn()?$agent->Get('agent_name'):'Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('CONTACTS','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	$tempuser=new user($HTTP_GET_VARS['user_id']);
	if(!$agent->IsLoggedIn() or $tempuser->Get('agent_id')!=$agent->id)
		$agent->LoginForm();
	else
	{
		echo("<div id='".$tempuser->GetFieldName('ListUserContactsContainer')."'>");
		$tempuser->ListUserContacts();
		echo("</div>");
  	}
?>	
			<div class='back_actions'><a href='index.php'>Back To Clients</a></div>
 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('CONTACTS','AGENT'); ?>
</body>
</html>