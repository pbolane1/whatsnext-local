<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Reset Password</title>
	<?php include('../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='admin'>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>		
<?php		
		if($HTTP_GET_VARS['admin_reset_code'])
		{		
			$temp_admin=new admin();
			$temp_admin->InitByKeys('admin_reset_code',$HTTP_GET_VARS['admin_reset_code']);
			$temp_admin->SetFlag('PASSWORD');
			$temp_admin->ProcessAction();
			$temp_admin->ResetPassword();
		}

?>	
			</div>
		</div>
	</div>


	<?php include ('modules/footer.php');?>
	<?php include ('../modules/footer_scripts.php');?>
</body>
</html>