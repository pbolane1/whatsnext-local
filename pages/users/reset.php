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
	<?php $__headline__='Reset Password'; ?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container-fluid'>
			<div class='content_inner'>	
<?php		
		if($HTTP_GET_VARS['user_contact_reset_code'])
		{		
			$temp_user_contact=new user_contact();
			$temp_user_contact->InitByKeys('user_contact_reset_code',$HTTP_GET_VARS['user_contact_reset_code']);
			$temp_user_contact->SetFlag('PASSWORD');
			$temp_user_contact->ProcessAction();
			$temp_user_contact->ResetPassword();
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