<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Timeline Items</title>
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
	 	$template=new template($HTTP_GET_VARS['template_id']);
	
		$where=array('agent_id=0');
		$where[]="template_id=".$template->id;
		$where[]="timeline_item_active=1";
	
		$title="Manage Timeline Items For ".$template->Get('template_name');
	
	  	$list=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_order');
	  	$headers=array();
		$headers[]=$list->Header('ORDER');
		$headers[]=$list->Header('HEADLINE');
		$headers[]=$list->Header('TIMING');
		$headers[]=$list->Header('CONDITIONS');
		$headers[]=$list->Header('MODIFIED');
		$headers[]=$list->Header('ACTION','',2);
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='5' class='area'>".$title."</th></tr>");
		$list->SetHTML('BEFORE_EXISTING',"<tr>".implode('',$headers)."</tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='5'>Edit Timeline Item - ".$template->Get('template_name')."</h3></th></tr>");
		$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='5'>New Timeline Item - ".$template->Get('template_name')."</h3></th></tr>");
		$list->SetHTML('EMPTY_SET',"<tr><td colspan='5' class='emptyset'>There are items to display</td></tr>");	
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('timeline_item_order',$list->GetTotalAvailable()+1);	  	
	  	$list->SetEachNew('template_id',$template->id);
		$list->ProcessAction();
		$list->CheckSortOrder();
	  	$list->SetEachNew('timeline_item_order',$list->GetTotalAvailable()+1);	  	
	  	$list->SetEachNew('template_id',$template->id);
		$list->SetFlag('ROWHIGHLIGHT');
		$list->SetFlag('BUTTONHIGHLIGHT');
		$list->SetFlag('DROPSORT');
//	  	$list->Edit('SINGLE_LINK',1);		  		  	      	

		//dummy agent to edit the nono-user timeline
		$temp=new agent();
		$temp->SetFlag('ADMIN');
		$temp->id=0;
		//get standard "custom" css
		$temp->CustomCSS();

		//visual edit.
	 	echo("<div id='intro_container'>");
		$temp->EditTemplateIntro($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_container' class='hidden-xs'>");
		$temp->AgentTools($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_buttons_container' class='hidden-xs'>");
		$temp->AgentToolsButtons($HTTP_GET_VARS);
		echo("</div>");
		echo("<div class='row timeline_row'>");
		echo("<div class='col-sm-7 timeline_col'>");
	 	echo("<div id='timeline_container'>");
		$temp->EditTimeline($HTTP_GET_VARS);
		echo("</div>");
		echo("</div>");
		echo("<div class='col-sm-5 sidebar_col'>");
	 	echo("<div id='sidebar_container'>");
		//$agent->EditSidebar($HTTP_GET_VARS);
		echo("</div>");
	 	echo("<div id='agent_tools_xs_container' class='visible-xs'>");
		$temp->AgentToolsXS($HTTP_GET_VARS);
		echo("</div>");
		echo("</div>");
		echo("</div>");
		$temp->DrawFlares();
	

		echo("<div class='back_actions'><a href='templates.php'>Back To Templates</a></div>");
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