<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Clients</title>
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
		$agent=new agent($HTTP_GET_VARS['agent_id']);
	
	
	 	$where=array("agent_id='".$agent->id."'");
	 	$where[]="user_active=1";
		 	 
	  	$list=new DBRowSetEX('users','user_id','user',implode(' AND ',$where),'user_name',10);
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='5' class='area'>Manage Buyers/Sellers For ".$agent->Get('agent_name')."</th></tr>");
		$list->SetHTML('BEFORE_EXISTING',"<tr>".$list->Header('NAME','user_name').$list->Header('TIMELINE').$list->Header('ACTION','',2)."</tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='5'>Edit Client</h3></th></tr>");
		$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='5'>New Client</h3></th></tr>");
		$list->SetHTML('EMPTY_SET',"<tr><td colspan='5' class='emptyset'>There are items to display</td></tr>");	
		$list->num_new=1;
	  	$list->Retrieve();
	//	  	$list->SetEachNew('user_order',$list->GetTotalAvailable()+1);	  	
	  	$list->SetEachNew('agent_id',$agent->id);
		$list->ProcessAction();
	//		$list->CheckSortOrder();
	//	  	$list->SetEachNew('user_order',$list->GetTotalAvailable()+1);	  	
	  	$list->SetEachNew('agent_id',$agent->id);
		$list->SetFlag('ROWHIGHLIGHT');
		$list->SetFlag('BUTTONHIGHLIGHT');
	//		$list->SetFlag('DROPSORT');
	  	$list->Edit('SINGLE_LINK',1);
	}	
?>	
	<div class='back_actions'><a href='index.php'>Back To All Agents</a></div>

			</div>
		</div>
	</div>


	<?php include ('modules/footer.php');?>
	<?php include ('../modules/footer_scripts.php');?>
</body>
</html>