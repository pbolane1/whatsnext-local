<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Agents</title>
	<?php include('../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='admin'>
	<?php $__headline__=$admin->IsLoggedIn()?'Manage Agents':'Admin Login';?>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php
	if(!$admin->IsLoggedIn())
		$admin->LoginForm();
	else
	{
		if(isset($HTTP_POST_VARS['manage_agent_type']))
			Session::Set('manage_agent_type',$HTTP_POST_VARS['manage_agent_type']);
		if(isset($HTTP_POST_VARS['manage_agent_name']))
			Session::Set('manage_agent_name',$HTTP_POST_VARS['manage_agent_name']);

	 	if($HTTP_POST_VARS['filter_action']=='Reset')
	 	{
			Session::Set('manage_agent_type','');
			Session::Set('manage_agent_name','');			
		}

		echo("<div class='filters'>");
		form::Begin('?action=filer&agents_start=0');
		echo("<table class='listing'>");
		echo("<tr><th colspan='4'>Filter Agents</th></tr>");
		echo("<tr class='edit_wrapper list_item'>");
		echo("<td>");
		form::DrawSelect('manage_agent_type',array('Active'=>'','Inactive'=>'INACTIVE','All'=>'ALL'),Session::Get('manage_agent_type'),array('onchange'=>'this.form.submit();'));
		echo("</td>");
		echo("<td>");
		form::DrawTextInput('manage_agent_name',Session::Get('manage_agent_name'),array('onchange'=>'this.form.submit();','placeholder'=>'name'));
		echo("</td>");
		echo("<td>");
		form::DrawSubmit('filter_action','Go');
		echo("</td>");
		echo("<td>");
		form::DrawSubmit('filter_action','Reset');
		echo("</td>");
		echo("</tr>");
		echo("</table>");
		form::End();
		echo("</div>");

		$where=array("1");
		$where[]="agent_special=''";
		if(Session::Get('manage_agent_name'))
			$where[]="agent_name LIKE '%".Session::Get('manage_agent_name')."%'";
		if(Session::Get('manage_agent_type')=='INACTIVE')
			$where[]="agent_active=0";
		else if(Session::Get('manage_agent_type')=='ALL')
			$where[]="1=1";
		else
			$where[]="agent_active=1";
		
	  	$list=new DBRowSetEX('agents','agent_id','agent',implode(' AND ',$where),'agent_name',10);
	
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='5' class='area'>Manage Agents</th></tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='5'>Edit Agent</th></tr>");
		$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='5'>New Agent</th></tr>");
		$list->SetHTML('BEFORE_EXISTING',"<tr>".$list->Header('USER','agent_name').$list->Header('EMAIL','agent_email','','hidden-sm hidden-xs').$list->Header('LAST LOGIN','agent_last_login','','hidden-sm hidden-xs').$list->Header('ACTION','',2)."</tr>");
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
</html>1