<?php include('../../../../../include/common.php') ?>
<?php
	$cntrec=database::fetch_array(database::query("SELECT COUNT(1) AS cnt FROM link_lists WHERE foreign_class='".Session::Get('WYSIWYG_CLASS')."' AND foreign_id='".Session::Get('WYSIWYG_ID')."'"));
	if($HTTP_GET_VARS['action']=='newlist')
	{
		if(!$HTTP_POST_VARS['list_name'])
			$HTTP_POST_VARS['list_name']='List '.($cntrec['cnt']+1);
		$list=new link_list();
		$list->Set('link_list_name',$HTTP_POST_VARS['list_name']);
		$list->Set('foreign_class',Session::Get('WYSIWYG_CLASS'));
		$list->Set('foreign_id',Session::Get('WYSIWYG_ID'));
		$list->Update();
		$cntrec['cnt']++;
	}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Insert Link List</title>
	<script language="javascript" type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="../../utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="../../utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="../../utils/validate.js"></script>
	<script language="javascript" type="text/javascript" src="jscripts/functions.js"></script>
	<base target="_self" />
</head>
<body id="insertlist" onload="tinyMCEPopup.executeOnLoad('init();');" style="display: none">
	<div class="tabs">
		<ul>
			<li id="general_tab" class="current" aria-controls="general_panel"><span><a href="javascript:mcTabs.displayTab('general_tab','general_panel');" onmousedown="return false;">General</a></span></li>
			<li id="new_tab" class="" aria-controls="new_panel"><span><a href="javascript:mcTabs.displayTab('new_tab','new_panel');" onmousedown="return false;">New List</a></span></li>
		</ul>
	</div>

	<div class="panel_wrapper">
		<div id="general_panel" class="panel current" style='height:auto;'>
		    <form onsubmit="insertAction();return false;" action="#"> 
				<fieldset>
						<legend>Choose List</legend>

						<table class="properties">
							<tr>
								<td class="column1"><label>Select List</label></td>
								<td colspan="2">
								<?php									  
									if(!$cntrec['cnt'])
										echo("No lists found.  Add a new list first");
									else
										form::DrawSelectFromSQL('list_id',"SELECT * FROM link_lists WHERE foreign_class='".Session::Get('WYSIWYG_CLASS')."' AND foreign_id='".Session::Get('WYSIWYG_ID')."' ORDER BY link_list_name","link_list_name","link_list_id",$list_id);
								?>
								</td> 
							</tr>
							<!--tr>
								<td class="column1"><label>Display Type</label></td>
								<td colspan="2">
								<?php									  
									form::DrawSelect('list_type',array("Teaser List"=>"TEASER","Teaser List + Read More"=>"MORE","Accordian List"=>"ACCORDIAN"),$list_type);
								?>
								</td> 
							</tr-->
						</table>
				</fieldset>
		    </form>
		</div>
		<div id="new_panel" class="panel" style='height:auto;'>
		    <form action="?action=newlist" method="post"> 
				<fieldset>
						<legend>New List</legend>
						<table class="properties">
							<tr>
								<td class="column1"><label>List Name</label></td>
								<td>
									<?php form::DrawTextInput('list_name'); ?>
								</td> 
								<td>
									<?php form::DrawSubmit('','Add'); ?>
								</td> 
							</tr>
						</table>
				</fieldset>
		    </form>
		</div>
	</div>
	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="insert" name="insert" value="Insert" onclick="insertAction();" />
		</div>

		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="Cancel" onclick="cancelAction();" />
		</div>
	</div>
</body> 
</html> 
