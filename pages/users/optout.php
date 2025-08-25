<?php include('../../include/common.php') ?>
<?php include('../../include/_user.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>My Account</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='buyer'>
	<?php $__headline__='Notifications'; ?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container-fluid'>
			<div class='content_inner'>	
				<div class='container'>	
<?php		
	echo("<div id='EditSettingsContainer'>");
	$user_contact->OptoutEmail($HTTP_GET_VARS);
	$user_contact->EditSettings();
	echo("</div>");
?>	
				</div>
			</div>
		</div>
	</div>


	<?php include ('modules/footer.php');?>
	<?php include ('../../modules/footer_scripts.php');?>
	<?php include ('modules/footer_scripts.php');?>
</body>
</html>