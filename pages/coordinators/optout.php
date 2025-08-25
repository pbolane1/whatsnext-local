<?php include('../../include/common.php') ?>
<?php include('../../include/_coordinator.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agent Manager - Reset Password</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='coordinator'>
	<?php $__headline__='Notifications'; ?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container-fluid'>
			<div class='content_inner'>	
				<div class='container'>	
<?php		
	echo("<div id='EditSettingsContainer'>");
	$coordinator->OptoutEmail($HTTP_GET_VARS);
	$coordinator->EditSettings();
	echo("</div>");

?>	
			</div>
		</div>
	</div>


	<?php include ('modules/footer.php');?>
	<?php include ('../../modules/footer_scripts.php');?>
	<?php include ('modules/footer_scripts.php');?>
</body>
</html>