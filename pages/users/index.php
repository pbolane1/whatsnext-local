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
	<?php $__headline__=$user_contact->IsLoggedIn()?'Transaction Timeline':'Client Login';?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container content_wrapper'>
			<div class='content_inner'>	
<?php			
	if(!$user_contact->IsLoggedIn())
		$user_contact->LoginForm();
	else
	{
	 	echo("<div id='dashboard_container'>");
		$user->DisplayDashboard();
	 	echo("</div>");

		//$user->DisplayIntro();
		$user->DisplayHeading();
	 	echo("<div id='user_tools_container' class='hidden-xs'>");
		$user->UserTools($HTTP_GET_VARS);
		echo("</div>");
		echo("<div class='row timeline_row'>");
		echo("<div class='col-sm-7 timeline_col'>");
	 	echo("<div id='timeline_container'>");
		$user->DisplayTimeline();
		echo("</div>");
		echo("</div>");
		echo("<div class='col-sm-5 sidebar_col'>");
	 	echo("<div id='sidebar_container'>");
		$user->DisplaySidebar();
		echo("</div>");
	 	echo("<div id='user_tools_xs_container' class='visible-xs'>");
		$user->UserToolsXS($HTTP_GET_VARS);
		echo("</div>");
		echo("</div>");
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