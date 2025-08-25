<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration</title>
	<?php include('../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='admin'>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	echo("SESSION ID:".Session::GetID().'<br>');
	Session::Dump();
	echo("<hr>");
	Cookie::Dump();
?>	

			</div>
		</div>
	</div>


	<?php include ('modules/footer.php');?>
	<?php include ('../modules/footer_scripts.php');?>
</body>
</html>