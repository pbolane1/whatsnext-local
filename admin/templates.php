<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Templates</title>
	<?php include('../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='admin'>
	<?php $__headline__=$admin->IsLoggedIn()?'Manage Timeline Templates':'Admin Login';?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$admin->IsLoggedIn())
		$admin->LoginForm();
	else
	{
		$where=array('agent_id=0 AND coordinator_id=0');
		$where[]='template_active=1';
		$title="Manage Templates";
	  	$list=new DBRowSetEX('templates','template_id','template',implode(' AND ',$where),'template_order');
	  	$headers=array();
		$headers[]=$list->Header('ORDER');
		$headers[]=$list->Header('TEMPLATE');
		$headers[]=$list->Header('TYPE');
		$headers[]=$list->Header('DEFAULT');
		$headers[]=$list->Header('STATUS');
		$headers[]=$list->Header('ITEMS');
		$headers[]=$list->Header('ACTION');
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='5' class='area'>".$title."</th></tr>");
		$list->SetHTML('BEFORE_EXISTING',"<tr>".implode('',$headers)."</tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='5'>Edit Template</h3></th></tr>");
		$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='5'>New Template</h3></th></tr>");
		$list->SetHTML('EMPTY_SET',"<tr><td colspan='5' class='emptyset'>There are templates to display</td></tr>");	
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('template_order',$list->GetTotalAvailable()+1);	  	
		$list->ProcessAction();
		$list->CheckSortOrder();
	  	$list->SetEachNew('template_order',$list->GetTotalAvailable()+1);	  	
		$list->SetFlag('ROWHIGHLIGHT');
		$list->SetFlag('BUTTONHIGHLIGHT');
		$list->SetFlag('DROPSORT');
	  	$list->Edit('SINGLE_LINK',1);		  		  	    
  	}
?>	
 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
</body>
</html>