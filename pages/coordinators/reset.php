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
	<?php $__headline__='Reset Password'; ?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container-fluid'>
			<div class='content_inner'>	
<?php		
		if($HTTP_GET_VARS['coordinator_reset_code'])
		{		
			$temp_coordinator=new coordinator();
			$temp_coordinator->InitByKeys('coordinator_reset_code',$HTTP_GET_VARS['coordinator_reset_code']);
			$temp_coordinator->SetFlag('PASSWORD');
			$temp_coordinator->ProcessAction();
			$temp_coordinator->ResetPassword();
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