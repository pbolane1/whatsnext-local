<?php include('../../include/common.php') ?>
<?php include('../../include/_agent.php') ?>

<?php include('include/wysiwyg_settings.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Agents - Timelie Items</title>
	<?php include('../../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='agent'>
	<?php $__headline__=$agent->IsLoggedIn()?$agent->Get('agent_name'):'Login';?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$agent->IsLoggedIn())
		$agent->LoginForm();
	else
	{
	 	if(!$HTTP_GET_VARS['template_id'] and !$HTTP_GET_VARS['user_id'])
	 		_navigation::Redirect('templates.php');
		$template=new template($HTTP_GET_VARS['template_id']);
		$user=new user($HTTP_GET_VARS['user_id']);
	
		$where=array('agent_id='.$agent->id);
		$where[]="template_id='".$template->id."'";
		$where[]="user_id='".$user->id."'";
		$where[]="timeline_item_active=1";
	
		$title="Manage Timeline Items For ".$template->Get('template_name');
		if($user->id)
			$title="Manage Timeline Items For ".$user->Get('user_name');
	
	  	$list=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_order');
	  	$headers=array();
		$headers[]=$list->Header('ORDER');
		$headers[]=$list->Header('HEADLINE');
		$headers[]=$list->Header('TIMING');
		$headers[]=$list->Header('ACTION','',2);
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='5' class='area'>".$title."</th></tr>");
		$list->SetHTML('BEFORE_EXISTING',"<tr>".implode('',$headers)."</tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='5'>Edit Timeline Item</h3></th></tr>");
		$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='5'>New Timeline Item</h3></th></tr>");
		$list->SetHTML('EMPTY_SET',"<tr><td colspan='5' class='emptyset'>There are items to display</td></tr>");	
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('timeline_item_order',$list->GetTotalAvailable()+1);	  	
	  	$list->SetEachNew('template_id',$template->id);
	  	$list->SetEachNew('agent_id',$agent->id);
		$list->ProcessAction();
		$list->CheckSortOrder();
	  	$list->SetEachNew('timeline_item_order',$list->GetTotalAvailable()+1);	  	
	  	$list->SetEachNew('template_id',$template->id);
	  	$list->SetEachNew('agent_id',$agent->id);
		$list->SetFlag('ROWHIGHLIGHT');
		$list->SetFlag('BUTTONHIGHLIGHT');
		$list->SetFlag('DROPSORT');
	  	$list->Edit('SINGLE_LINK',1);		  		  	      	
	}
?>		
	<div class='back_actions'><a href='templates.php'>Back To Templates</a></div>

 	    </div>
    </div>
</div>
	<?php include('modules/footer.php');?>
	<?php include('../../modules/footer_scripts.php');?>
	<?php include('modules/footer_scripts.php');?>
</body>
</html>