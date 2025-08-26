<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Conditions</title>
	<?php include('../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='admin'>
	<?php $__headline__=$admin->IsLoggedIn()?'Manage Conditions':'Admin Login';?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$admin->IsLoggedIn())
		$admin->LoginForm();
	else
	{
		echo("<div class='cards_list'>");
		echo("<div class='card'>");
		echo("<div class='box_inner'>");
	
	 	$where=array("1");
		 	 
	  	$list=new DBRowSetEX('conditions','condition_id','condition',implode(' AND ',$where),'condition_order');
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='15' class='area'>Manage Conditions</th></tr>");
		$list->SetHTML('BEFORE_EXISTING',"<tr>".$list->Header('ORDER').$list->Header('CONDITION').$list->Header('LABEL').$list->Header('DEFAULT').$list->Header('ACTION','',2)."</tr>");
		$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='5'>New Condition</h3></th></tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='5'>Edit Condition</h3></th></tr>");
		$list->SetHTML('EMPTY_SET',"<tr><td colspan='15' class='emptyset'>There are items to display</td></tr>");	
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('condition_order',$list->GetTotalAvailable()+1);	  	
		$list->ProcessAction();
		$list->CheckSortOrder();
	  	$list->SetEachNew('condition_order',$list->GetTotalAvailable()+1);	  	
		$list->SetFlag('ROWHIGHLIGHT');
		$list->SetFlag('BUTTONHIGHLIGHT');
		$list->SetFlag('DROPSORT');
	  	$list->Edit('SINGLE_LINK',1);
	
		echo("</div>");
		echo("</div>");
		echo("</div>");
	}	
?>	

			</div>
		</div>
	</div>


	<?php include ('modules/footer.php');?>
	<?php include ('../modules/footer_scripts.php');?>
</body>
</html>l>