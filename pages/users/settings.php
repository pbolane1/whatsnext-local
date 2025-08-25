<?php
	include('../../include/common.php');
	include('../../include/_user.php');

	$content=new content();
	$content->CreateFromKeys('content_area','HOME');
?>
<!DOCTYPE html>
<html>
<head>
<title>Buyers/ Sellers - My Timeline</title>
<meta name="description" content="<?php echo($content->GetMetaDescription());?>">
<meta name="keywords" content="<?php echo($content->GetMetaKeywords());?>">
<?php include ('../../modules/head.php');?>
<?php include ('modules/head.php');?>
</head>

<body class='buyer'>
	<?php $__headline__=$user_contact->IsLoggedIn()?'Settings':'Login';?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container content_wrapper'>
			<div class='content_inner'>	
<?php			
	if(!$user_contact->IsLoggedIn())
		$user_contact->LoginForm();
	else
	{
		echo("<div id='EditSettingsContainer'>");
		$user_contact->EditSettings();
		echo("</div>");
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