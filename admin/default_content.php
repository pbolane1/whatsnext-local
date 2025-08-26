<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Content</title>
	<?php include('../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='admin'>
	<?php $__headline__=$admin->IsLoggedIn()?'Manage Default Content':'Admin Login';?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$admin->IsLoggedIn())
		$admin->LoginForm();
	else
	{
	 	if(isset($HTTP_GET_VARS['content_parent_id']))
			Session::Set('content_parent_id',$HTTP_GET_VARS['content_parent_id']);
		if(!Session::Get('content_parent_id'))
			Session::Set('content_parent_id',0);
	
		$parent=new content(Session::Get('content_parent_id'));
		$where=array("content_area LIKE '%INTRO%'");
	
		
		$list=new DBRowSetEx('content','content_id','default_content',implode(' AND ',$where),'content_order');
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='5' class='area'>MANAGE DEFAULT CONTENT</th></tr>");
		$headers=array();
		$headers[]=$list->Header('PAGE');
		$headers[]=$list->Header('ACTION','',2);
	
		$list->SetHTML('BEFORE_EXISTING',"<tr>".implode('',$headers)."</tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='5' class='area'>EDIT DEFAULT CONTENT</th></td></tr>");
		$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='5' class='area'>NEW DEFAULT CONTENT</th></tr>");
		$list->SetHTML('EMPTY_SET',"<tr><td colspan='5' class='emptyset'>There are items to display</td></tr>");	
		$list->num_new=0;
	  	$list->Retrieve();
	  	$list->SetEachNew('parent_id',$parent->id);
	  	$list->SetEachNew('content_order',$list->GetTotalAvailable()+1);
	  	$list->ProcessAction();
	  	$list->CheckSortOrder();
	  	$list->Retrieve();
	  	$list->SetEachNew('parent_id',$parent->id);
	  	$list->SetEachNew('content_order',$list->GetTotalAvailable()+1);		  	
		$list->SetFlag('SUBPAGES',0);
		$list->SetFlag('ROWHIGHLIGHT');
		$list->SetFlag('BUTTONHIGHLIGHT');
		$list->SetFlag('DROPSORT',0);
		
	  	$list->Edit('SINGLE_LINK',0);  		  	    
?>	
			</div>
		</div>
	</div>


	<?php include ('modules/footer.php');?>
	<?php include ('../modules/footer_scripts.php');?>
</body>
</html>