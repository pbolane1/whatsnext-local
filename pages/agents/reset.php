<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Reset Password</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='agent'>
	<?php $__headline__='Reset Password'; ?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container-fluid'>
			<div class='content_inner'>	
<?php		
		if($HTTP_GET_VARS['agent_reset_code'])
		{		
			$temp_agent=new agent();
			$temp_agent->InitByKeys('agent_reset_code',$HTTP_GET_VARS['agent_reset_code']);
			$temp_agent->SetFlag('PASSWORD');
			$temp_agent->ProcessAction();
			$temp_agent->ResetPassword();
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