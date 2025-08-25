<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agent Manager - Transaction Timeline</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='x_agent buyer printable' onload="window.print();window.setTimeout(function(){X_window.close();},100);">
<?php
	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
		$agent->PrintTimeline($HTTP_GET_VARS);
?>		

	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
</body>
</html>