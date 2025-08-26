<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Templates</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='agent'>
	<?php $__headline__=$agent->IsLoggedIn()?'Manage Templates':'Agent Login';?>
	<?php include ('modules/header.php');?>
	<?php info_bubble::ListAll('TEMPLATES','AGENT'); ?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
	{
		echo("<div id='".$agent->GetFieldName('ListTemplatesContainer')."'>");
		$agent->ListTemplates();
		echo("</div>");	  	    

	}  	
?>
 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
	<?php info_bubble::AutoLaunch('TEMPLATES','AGENT'); ?>
</body>
</html>