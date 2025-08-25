<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Transaction Coordinators</title>
	<?php include('../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='admin'>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$admin->IsLoggedIn())
		$admin->LoginForm();
	else
	{
		$where=array("coordinator_active=1");
	  	$list=new DBRowSetEX('coordinators','coordinator_id','coordinator',implode(' AND ',$where),'coordinator_name',10);
	
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='5' class='area'>Manage Transaction Coordinators</th></tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='5'>Edit Transaction Coordinator</th></tr>");
		$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='5'>New Transaction Coordinator</th></tr>");
		$list->SetHTML('BEFORE_EXISTING',"<tr>".$list->Header('USER','coordinator_name').$list->Header('EMAIL','coordinator_email','','hidden-sm hidden-xs').$list->Header('LAST LOGIN','coordinator_last_login','','hidden-sm hidden-xs').$list->Header('ACTION','',2)."</tr>");
		$list->SetHTML('EMPTY_SET',"<tr><td class='emptyset' colspan='10'>There are no items to display</td></tr>");
	
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->ProcessAction();
		$list->SetFlag('BUTTONHIGHLIGHT');
		$list->SetFlag('ROWHIGHLIGHT');
	  	$list->Edit('SINGLE_LINK',1);		
	}	
?>	
			</div>
		</div>
	</div>


	<?php include ('modules/footer.php');?>
	<?php include ('../modules/footer_scripts.php');?>
</body>
</html>