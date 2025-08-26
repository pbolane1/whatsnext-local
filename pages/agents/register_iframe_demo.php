<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Transactions</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='agent'>
	<?php $__headline__=$agent->IsLoggedIn()?'Agent Dashboard':'Agent Login';?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner' style='text-align:center'>	
				<br><br>
				this is included with an iframe.
				<br><br>
				<iframe src='/agents/register_iframe.php' style='margin:0px auto;border:0px;width:400px;max-width:100%;height:800px;overflow-x:hidden;'></iframe>
				<br><br>
				have to give iframe a little extra height because error messages could make it grow taller.  If we stick with iframe implementation we can add messaging to have iframe dynamically resize to its content
			</div>
 	    </div>
    </div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('CLIENTS','AGENT'); ?>
</body>
</html>