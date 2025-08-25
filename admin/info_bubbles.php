<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration - Info Bubbles</title>
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
		global $HTTP_POST_VARS;
		if($HTTP_POST_VARS['show_info_bubble_section'])
			Session::Set('show_info_bubble_section',$HTTP_POST_VARS['show_info_bubble_section']);
		if(!Session::Get('show_info_bubble_section'))
			Session::Set('show_info_bubble_section','TIMELINE');

		form::Begin('?action=filter');
		echo("<div class='card card_filters card_filters_sticky'>");
		echo("<div class='card_heading card_heading_toggle'>");
		echo("<h3>Select Area</h3>");
		echo("</div>");
		echo("<div class='card_body' id='user_filters'>");
		echo("<div class='card_content'>");
		echo("<div class='line'>");
		form::DrawSelectFromSQL('show_info_bubble_section',"SELECT DISTINCT info_bubble_section FROM info_bubbles ORDER BY info_bubble_section","info_bubble_section","info_bubble_section",Session::Get('show_info_bubble_section'),array('onchange'=>'this.form.submit();'));
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		form::End();
	
	
		echo("<div class='cards_list'>");
		echo("<div class='card'>");
		echo("<div class='box_inner'>");
	
	 	$where=array("1");
	 	$where[]="info_bubble_section='".Session::Get('show_info_bubble_section')."'";
		 	 
	  	$list=new DBRowSetEX('info_bubbles','info_bubble_id','info_bubble',implode(' AND ',$where),'info_bubble_order');
		$list->SetHTML('BEFORE_NEW',"<tr><th colspan='5' class='area'>Manage Info Popups</th></tr>");
		$list->SetHTML('BEFORE_EXISTING',"<tr>".$list->Header('ORDER').$list->Header('ITEM').$list->Header('ACTION','',2)."</tr>");
		$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='5'>Edit Info Popup</h3></th></tr>");
		$list->SetHTML('EMPTY_SET',"<tr><td colspan='5' class='emptyset'>There are items to display</td></tr>");	
		$list->num_new=0;
	  	$list->Retrieve();
	  	$list->SetEachNew('info_bubble_order',$list->GetTotalAvailable()+1);	  	
	  	$list->SetEachNew('agent_id',$agent->id);
		$list->ProcessAction();
		$list->CheckSortOrder();
	  	$list->SetEachNew('info_bubble_order',$list->GetTotalAvailable()+1);	  	
	  	$list->SetEachNew('agent_id',$agent->id);
		$list->SetFlag('ROWHIGHLIGHT');
		$list->SetFlag('BUTTONHIGHLIGHT');
		$list->SetFlag('DROPSORT');
	  	$list->Edit('SINGLE_LINK',0);
	
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