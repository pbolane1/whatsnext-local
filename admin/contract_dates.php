<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Contract Dates</title>
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
		echo("<div class='cards_list'>");
		echo("<div class='card'>");
		echo("<div class='box_inner'>");
	
	 	$where=array("1");
		 	 
	  	$list=new DBRowSetEX('contract_dates','contract_date_id','contract_date',implode(' AND ',$where),'contract_date_order');
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='15' class='area'>Manage Contract Dates</th></tr>");
		$list->SetHTML('BEFORE_EXISTING',"<tr>".$list->Header('ORDER').$list->Header('RPA ITEM').$list->Header('DATE').$list->Header('KEY DATE').$list->Header('TYPE').$list->Header('DEFAULT').$list->Header('CONDITIONS').$list->Header('ACTION')."</tr>");
		$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='15'>New Date</h3></th></tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='15'>Edit Date</h3></th></tr>");
		$list->SetHTML('EMPTY_SET',"<tr><td colspan='15' class='emptyset'>There are items to display</td></tr>");	
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('contract_date_order',$list->GetTotalAvailable()+1);	  	
		$list->ProcessAction();
		$list->CheckSortOrder();
	  	$list->SetEachNew('contract_date_order',$list->GetTotalAvailable()+1);	  	
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
</html>