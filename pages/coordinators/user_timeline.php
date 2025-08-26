<?php include('../../include/common.php') ?>
<?php include('../../include/_coordinator.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Settings</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='agent'>
	<?php $__headline__=$coordinator->IsLoggedIn()?'Transaction Timeline':'Manager Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('SETTINGS','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php		
 	$temp=new user($HTTP_GET_VARS['user_id']);
	if(!$coordinator->IsLoggedIn())
		$coordinator->LoginForm();
	else
	{	
	 	$temp=new user($HTTP_GET_VARS['user_id']);
		$temp->DisplayIntro();
		echo("<div class='row'>");
		echo("<div class='col-sm-7'>");
		$temp->DisplayTimeline();
		echo("</div>");
		echo("<div class='col-sm-5'>");
		$temp->DisplaySidebar();
		echo("</div>");
		echo("</div>");
	}
?>
			</div>
		</div>
	</div>
	
	<?php include ('modules/footer.php');?>
	<?php include ('../../modules/footer_scripts.php');?>
	<?php include ('modules/footer_scripts.php');?>
</body>
</html>