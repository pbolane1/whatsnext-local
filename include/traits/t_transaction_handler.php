<?php
//**************************************************************//
//	
//	FILE: t_transaction_handler.php
//  TRAIT: transaction_handler
//  
//	STUBBED BY: 
//  PURPOSE: 
//  STUBBED TIMESTAMP: 
//
//**************************************************************//

trait transaction_handler
{
	abstract protected function GetAgentIds($params=array());
	abstract protected function GetCoordinatorIDs($params=array());
	abstract protected function GetIncludedItemsSQL($params=array());
	abstract protected function GetAvailableTemplateIds($params=array());
	abstract protected function GetUserURL($user,$page='edit_user.php');
	abstract protected function GetSettings();
	

	public function ListTemplates($params=array())
	{
		global $HTTP_POST_VARS;
		if($params['action']=='new_template')
		{
			$copy_template=new template($HTTP_POST_VARS['template_id']);
			
			$template=new template();
			$template->Copy($copy_template);
			$template->Set('template_name',$HTTP_POST_VARS['template_name']);
			$template->Set('template_default',0);
			$template->Set($this->primary,$this->id);
			$template->Update();			
			timeline_item::CopyAll(array('template_id'=>$HTTP_POST_VARS['template_id']),array('template_id'=>$template->id,$this->primary=>$this->id));
			database::query("UPDATE timeline_items SET timeline_item_modified='".time()."' WHERE template_id='".$template->id."'");			
			
			form::DrawHiddenInput('new_template_id',$template->id);
		}
		$where[]="template_active=1";		 	 
		$where[]=$this->primary."=".$this->id;		 	 

	  	$list=new DBRowSetEX('templates','template_id','template',implode(' AND ',$where),'template_active DESC,template_order');
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew($this->primary,$this->id);
	  	$list->SetEachNew('template_order',0);	  	
		$list->SetFlag('ALLOW_BLANK');
		$list->ProcessAction();
	  	$list->CheckSortOrder('template_order');
		$list->SetFlag('ALLOW_BLANK');
	  	$list->ProcessAction();
	  	$list->Retrieve();
	  	$list->Each('Retrieve');
	  	$list->SetEachNew($this->primary,$this->id);

		echo("<div class='agent_dashboard'>");

		//FULL SIDE:
		echo("<div class='hidden-sm hidden-xs'>");		
		form::Begin('','POST',false,array('id'=>'new_template'));
		echo("<h1 class='agent_color1'>Create New Template</h1>");
		echo("<div class='row'>");
		echo("<div class='col-md-3 col-xs-6'>Select a template to copy:</div>");
		echo("<div class='col-md-2 col-xs-6'>");
		$attr=array('data-info'=>'TEMPLATES_NEW');
		form::DrawSelectFromSQL('template_id',"SELECT * FROM templates WHERE template_active=1 AND template_status=1 AND template_id IN(".implode(',',$this->GetAvailableTemplateIds()).") ORDER BY template_order",'template_name','template_id','',$attr);
		echo("</div>");
		echo("<div class='col-md-3 col-xs-6'>Name Your New Template:</div>");
		echo("<div class='col-md-2 col-xs-6'>");
		form::DrawTextInput('template_name','');
		echo("</div>");
		echo("<div class='col-md-2 col-xs-6'>");
		$js2="document.location='edit_timeline.php?template_id='+jQuery('#new_template_id').val();";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListTemplates','".$this->GetFieldName('ListTemplatesContainer')."','new_template','','action=new_template',function(){".$js2."height_handler();});";
		form::DrawButton('','Create Template',array('onclick'=>$js));
		echo("</div>");
		echo("</div>");
		form::End();
		echo("</div>");

		//SMALL
		echo("<div class='visible-sm visible-xs'>");		
		form::Begin('','POST',false,array('id'=>'new_template_mobile'));
		echo("<table class='listing'>");
		echo("<tr class='agent_bg_color1'>");
		echo("<th>Create New Template</th>");
		echo("</tr>");
		echo("<tr class='list_item'>");
		echo("<td>Select a template to copy</td>");
		echo("</tr>");
		echo("<tr class='list_item'>");
		echo("<td>");
		$attr=array('data-info'=>'TEMPLATES_NEW','style'=>'width:100%');
		form::DrawSelectFromSQL('template_id',"SELECT * FROM templates WHERE template_active=1 AND template_status=1 AND (".$this->primary."='".$this->id."' OR (coordinator_id=0 AND agent_id=0)) ORDER BY template_order",'template_name','template_id','',$attr);
		echo("</td>");
		echo("</tr>");
		echo("<tr class='list_item'>");
		echo("<td>Name your new template</td>");
		echo("</tr>");
		echo("<tr class='list_item'>");
		echo("<td>");
		form::DrawTextInput('template_name','');
		echo("</td>");
		echo("</tr>");
		echo("<tr class='list_item'>");
		echo("<td>");
		$js2="document.location='edit_timeline.php?template_id='+jQuery('#new_template_id').val();";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListTemplates','".$this->GetFieldName('ListTemplatesContainer')."','new_template_mobile','','action=new_template',function(){".$js2."height_handler();});";
		form::DrawButton('','Create Template',array('onclick'=>$js));
		echo("</td>");
		echo("</tr>");
		echo("</table>");
		echo("</div>");
		form::End();
		echo("</div>");

		//FULL SIZE
		echo("<div class='hidden-sm hidden-xs'>");		
		form::Begin('','POST',false,array('id'=>'templates'));
		if(count($list->items))
		{
			echo("<div class='agent_dashboard'>");
			echo("<h1 class='agent_color1'>Customize Your Template(s):</h1>");
			echo("<table class='listing'>");
			echo("<tr class='agent_bg_color1'><th>Template Name</th><th>Type</th><th>Status</th><th>Default</th><th>Last Modified</th><th>Edit Template</th></tr>");
			foreach($list->items as $template)
			{
			 	$dm=new date();
			 	$rec=database::fetch_array(database::query("SELECT MAX(timeline_item_modified) AS max FROM timeline_items WHERE template_id='".$template->id."'"));
			 	$dm->SetTimestamp($rec['max']);
			 
				echo("<tr class='list_item'>");
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListTemplates','".$this->GetFieldName('ListTemplatesContainer')."','templates','','action=".$template->GetFormAction('save')."',function(){height_handler();});";
				echo("<td>");
				form::DrawTextInput($template->GetFieldName('template_name'),$template->Get('template_name'),array('onchange'=>$js));
				echo("</td>");
				echo("<td>");
				form::DrawSelect($template->GetFieldName('template_type'),array('Buyer'=>'BUYER','Seller'=>'SELLER'),$template->Get('template_type'),array('onchange'=>$js));
				echo("</td>");
				echo("<td>");
				form::DrawSelect($template->GetFieldName('template_status'),array('Draft'=>0,'Active'=>1),$template->Get('template_status'),array('onchange'=>$js));
				echo("</td>");
				echo("<td>");
				if($template->Get('template_status'))
					form::DrawSelect($template->GetFieldName('template_default'),array(''=>'0','Default'=>'1'),$template->Get('template_default'),array('onchange'=>$js));
				echo("</td>");
				echo("<td>".$dm->GetDate('m/d/Y')."</td>");
				echo("<td>");
				echo("<a data-info='TEMPLATES_TIMELINE' data-info-none='none' href='edit_timeline.php?template_id=".$template->id."' data-toggle='tooltip' title='Manage Timeline'><i class='fas fa-pencil-alt'></i></a>");
				echo("&nbsp;&nbsp;&nbsp;");
				$js="ObjectFunctionAjaxPopup('Share This Tempalte','".get_class($this)."','".$this->id."','ShareTemplate','NULL','','template_id=".$template->id."',function(){height_handler();});";
				echo("<a data-info='TEMPLATES_SHARE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Share This Template'><i class='fa fa-share-nodes'></i></a>");
				echo("&nbsp;&nbsp;&nbsp;");
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListTemplates','".$this->GetFieldName('ListTemplatesContainer')."','NULL','','action=".$template->GetFormAction('delete')."',function(){height_handler();});";
				$js.="";
				$js="if(confirm('Delete this template?')){".$js."}";			
				echo("<a data-info='TEMPLATES_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
				echo("</td>");
				echo("</tr>");
			}
			echo("</table>");
			echo("</div>");
		}
		form::End();
		echo("</div>");

		//SMALL
		echo("<div class='visible-sm visible-xs'>");		
		form::Begin('','POST',false,array('id'=>'templates_mobile'));
		if(count($list->items))
		{
			echo("<div class='agent_dashboard'>");
			echo("<h1 class='agent_color1'>Customize Your Template(s):</h1>");
			echo("<table class='listing'>");
			foreach($list->items as $template)
			{
			 	$dm=new date();
			 	$rec=database::fetch_array(database::query("SELECT MAX(timeline_item_modified) AS max FROM timeline_items WHERE template_id='".$template->id."'"));
			 	$dm->SetTimestamp($rec['max']);
			 
				echo("<tr class='agent_bg_color1'>");
				$js="ObjectFunctionAjax('agent','".$this->id."','ListTemplates','".$this->GetFieldName('ListTemplatesContainer')."','templates_mobile','','action=".$template->GetFormAction('save')."',function(){height_handler();});";
				echo("<th>");
				form::DrawTextInput($template->GetFieldName('template_name'),$template->Get('template_name'),array('onchange'=>$js));
				echo("</th>");
				echo("</tr>");
				echo("<tr class='list_item'>");
				echo("<td>");
				form::DrawSelect($template->GetFieldName('template_type'),array('Buyer'=>'BUYER','Seller'=>'SELLER'),$template->Get('template_type'),array('onchange'=>$js));
				echo("</td>");
				echo("</tr>");
				echo("<tr class='list_item'>");
				echo("<td>");
				form::DrawSelect($template->GetFieldName('template_status'),array('Draft'=>0,'Active'=>1),$template->Get('template_status'),array('onchange'=>$js));
				echo("</td>");
				echo("</tr>");
				if($template->Get('template_status'))
				{
					echo("<tr class='list_item'>");
					echo("<td>");
					form::DrawSelect($template->GetFieldName('template_default'),array(''=>'0','Default'=>'1'),$template->Get('template_default'),array('onchange'=>$js));
					echo("</td>");
					echo("</tr>");
				}
				echo("<tr class='list_item'>");
				echo("<td>Last Modified ".$dm->GetDate('m/d/Y')."</td>");
				echo("</tr>");
				echo("<tr class='list_item'>");
				echo("<td>");
				echo("<a data-info='TEMPLATES_TIMELINE' data-info-none='none' href='edit_timeline.php?template_id=".$template->id."' data-toggle='tooltip' title='Manage Timeline'><i class='fas fa-pencil-alt'></i></a>");
				echo("&nbsp;&nbsp;&nbsp;");
				$js="ObjectFunctionAjaxPopup('Share This Tempalte','".get_class($this)."','".$this->id."','ShareTemplate','NULL','','template_id=".$template->id."',function(){height_handler();});";
				echo("<a data-info='TEMPLATES_SHARE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Share This Template'><i class='fa fa-share-nodes'></i></a>");
				echo("&nbsp;&nbsp;&nbsp;");
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListTemplates','".$this->GetFieldName('ListTemplatesContainer')."','NULL','','action=".$template->GetFormAction('delete')."',function(){height_handler();});";
				$js.="";
				$js="if(confirm('Delete this template?')){".$js."}";			
				echo("<a data-info='TEMPLATES_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
				echo("</td>");
				echo("</tr>");
				echo("<tr class='list_item_empty'><td><br></td></tr>");
			}
			echo("</table>");
			echo("</div>");
		}
		form::End();
		echo("</div>");

	}

	public function ShareTemplate($params=array())
	{
		$template=new template($params['template_id']);
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ShareTemplate','popup_content','".$template->GetFieldName('share')."','','action=".$template->GetFieldName('share')."&template_id=".$template->id."',function(){height_handler();});";

	  	$coordinators=new DBRowSetEX('coordinators','coordinator_id','coordinator',"coordinator_id IN (".implode(',',$this->GetCoordinatorIDs(array('related'=>true))).")");
		$coordinators->Retrieve();
	  	$agents=new DBRowSetEX('agents','agent_id','agent',"agent_id IN (".implode(',',$this->GetAgentIDs(array('related'=>true))).")");
		$agents->Retrieve();

		global $HTTP_POST_VARS;
		if($params['action']==$template->GetFormAction('share'))
		{
			database::query("DELETE FROM templates_to_transaction_handlers WHERE template_id='".$template->id."'");
			foreach($HTTP_POST_VARS['agent_ids'] as $agent_id)				
			{
				$template_to_transaction_handler=new template_to_transaction_handler();
				$template_to_transaction_handler->CreateFromKeys(array('template_id','foreign_class','foreign_id'),array($template->id,'agent',$agent_id));
			}
			foreach($HTTP_POST_VARS['coordinator_ids'] as $coordinator_id)				
			{
				$template_to_transaction_handler=new template_to_transaction_handler();
				$template_to_transaction_handler->CreateFromKeys(array('template_id','foreign_class','foreign_id'),array($template->id,'coordinator',$coordinator_id));
			}
		}

		form::Begin('','POST',false,array('id'=>$template->GetFieldName('share')));
		echo("<H1>".$template->Get('template_name')."</H1>");
		echo("<div class='line'>Share this template with:</div>");
		echo("<div class='row'>");
		echo("<div class='col-md-6'>");
		foreach($agents->items as $agent)
		{
			if(get_class($this)!=='agent' or $agent->id!=$this->id)
			{
				$template_to_transaction_handler=new template_to_transaction_handler();
				$template_to_transaction_handler->InitByKeys(array('template_id','foreign_class','foreign_id'),array($template->id,'agent',$agent->id));
				echo("<div class='line'>");			
				echo("<label>");			
				form::DrawCheckbox('agent_ids[]',$agent->id,$template_to_transaction_handler->id?true:false,array('onchange'=>$js));
				echo($agent->get('agent_name')."</label>");
				echo("</div>");
			}
		}
		echo("</div>");
		echo("<div class='col-md-6'>");
		foreach($coordinators->items as $coordinator)
		{
			if(get_class($this)!=='coordinator' or $coordinator->id!=$this->id)
			{
				$template_to_transaction_handler=new template_to_transaction_handler();
				$template_to_transaction_handler->InitByKeys(array('template_id','foreign_class','foreign_id'),array($template->id,'coordinator',$coordinator->id));
				echo("<div class='line'>");			
				echo("<label>");			
				form::DrawCheckbox('coordinator_ids[]',$coordinator->id,$template_to_transaction_handler->id?true:false,array('onchange'=>$js));
				echo($coordinator->get('coordinator_name')."</label>");
				echo("</div>");
			}
		}
		echo("</div>");
		echo("</div>");
	}


	public function EditUserArea($params=array())
	{
		echo("<a name='EditUserContainer'></a>");
		echo("<div id='".$this->GetFieldName('EditUserContainer')."'>");
		if($params['action']=='EditUser')
			$this->EditUser($params);
		echo("</div>");
	}
	


	public function EditUser($params=array())
	{
		$user=new user($params['user_id']);
		$agent=new agent($user->Get('agent_id'));
		$user->SetFlag('ALLOW_BLANK');
		if($this->IsAgent())//proably a better way to do this.
			$user->Set('agent_id',$this->id);
		$user->ProcessAction();				
		if($params['action']=='new_user')
			$user->Update();
	
		if(!$params['fn'])
			$params['fn']='ListUsers';


		//USERS LIST.
	 	$where=array("user_id='".$user->id."'");
		$where[]="user_contact_primary=0";
 	  	$user_contacts=new DBRowSetEX('user_contacts','user_contact_id','user_contact',implode(' AND ',$where),'user_contact_id');
		$user_contacts->num_new=1;
	  	$user_contacts->Retrieve();
	  	$user_contacts->SetEachNew('user_id',$user->id);
		$user_contacts->SetFlag('ALLOW_BLANK');
		$user_contacts->SetFlag('AGENT');
		$user_contacts->ProcessAction();
	  	$user_contacts->Retrieve();
	  	$user_contacts->SetEachNew('user_id',$user->id);
		$user_contacts->SetFlag('AGENT');
		//if(!count($user_contacts->items))
		//{
		//	$user_contacts->newitems[0]->Set('user_contact_primary',1);
		//	$user_contacts->newitems[0]->Save();
		// 	$user_contacts->Retrieve();
		// 	$user_contacts->SetEachNew('user_id',$user->id);
		//}

		//PRIMARY CONTACT DETAILS
		$user_contact=new user_contact();
		$user_contact->CreateFromKeys(array('user_id','user_contact_primary'),array($user->id,1));
		$user_contact->SetFlag('AGENT');
		$user_contact->ProcessAction();

	  	$user_contacts->Each('Retrieve');



		// "name" went away on us
		$user->Set('user_name',$user_contact->Get('user_contact_name'));
		if(!$user->Get('user_name'))
			$user->Set('user_name','TBD');
		$user->Update();

		//BASIC PROEPRTY DETAILS
		$user_list_js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','',function(){height_handler();});";		


		$user_js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','NULL','user','','action=".$user->GetFormAction('save')."&user_id=".$user->id."',function(){height_handler();});";
		//$user_js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','user','','action=".$user->GetFormAction('save')."&user_id=".$user->id."',function(){height_handler();});";
		$user_js_ext="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','NULL','user','','action=".$user->GetFormAction('save')."&user_id=".$user->id."',function(){".$user_list_js."});";

		$settings=json_decode($user_contact->Get('user_contact_settings'),true);
		$user_contact_js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','NULL','user','','action=".$user_contact->GetFormAction('save')."&user_id=".$user->id."',function(){height_handler();});";
//		$user_contact_js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','user','','action=".$user_contact->GetFormAction('save')."&user_id=".$user->id."',function(){height_handler();});";
		$user_contact_js_ext="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','NULL','user','','action=".$user_contact->GetFormAction('save')."&user_id=".$user->id."',function(){".$user_list_js."});";
//		$user_contact_js_ext="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','user','','action=".$user_contact->GetFormAction('save')."&user_id=".$user->id."',function(){".$user_list_js."});";
		
		form::Begin('','POST',true,array('id'=>'user'));
		form::DrawHiddenInput('AGENT',1);
		echo("<div class='agent_dashboard'>");

		echo("<table class='listing editing'>");
		echo("<tr class='agent_bg_color1'>");
		echo("<th class='edit_label'>Client Info</th><th><div class='edit_label_small'>Client Info</div></th>");
		echo("</tr>");
		echo("<tr class='list_item'>");
		echo("<td class='edit_label'>Type of Client:</td>");
		echo("<td>");
		echo("<span class='edit_label_small'>Type of Client:</span>");
		if(!$user->Get('user_active'))
			echo("<label>".Text::Capitalize(strtolower($user->Get('user_type')))."</label>");
		else
		{
			echo("<label>");
			$js="jQuery('#".$user->GetFieldName('template_id')."').val(".$agent->GetDefaultTemplateID('BUYER').");";
			form::DrawRadioButton($user->GetFieldName('user_type'),'BUYER',$user->Get('user_type')=='BUYER',array('onchange'=>$js.$user_js_ext));
			echo(" Buyer</label>");
			echo("<label>");
			$js="jQuery('#".$user->GetFieldName('template_id')."').val(".$agent->GetDefaultTemplateID('SELLER').");";
			form::DrawRadioButton($user->GetFieldName('user_type'),'SELLER',$user->Get('user_type')=='SELLER',array('onchange'=>$js.$user_js_ext));
			echo(" Seller</label>");
		}
		echo("</td>");
		echo("</tr>");
		if($user->Get('user_active') or $user_contact->Get('user_contact_name'))
		{
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Primary Contact Full Name</td>");
			echo("<td>");
			if(!$user->Get('user_active'))
				echo($user_contact->Get('user_contact_name'));
			else
				form::DrawTextInput($user_contact->GetFieldName('user_contact_name'),$user_contact->Get('user_contact_name'),array('onchange'=>$user_contact_js_ext,'placeholder'=>'Primary Contact Full Name'));
			echo("</td>");
			echo("</tr>");
		}
		if($user->Get('user_active') or $user_contact->Get('user_contact_email'))
		{
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Primary Contact Email Address</td>");
			echo("<td>");
			if(!$user->Get('user_active'))
				echo($user_contact->Get('user_contact_email'));
			else
				form::DrawTextInput($user_contact->GetFieldName('user_contact_email'),$user_contact->Get('user_contact_email'),array('onchange'=>$user_contact_js,'placeholder'=>'Primary Contact Email Address'));
			echo("</td>");
			echo("</tr>");
		}
//		if($user->Get('user_active'))
//		{
//			echo("<tr class='list_item'>");
//			echo("<td class='edit_label'>Primary Contact Password</td>");
//			echo("<td>");
//			if(!$user->Get('user_active'))
//				echo($user_contact->Get('user_contact_password'));
//			else
//				form::DrawTextInput($user_contact->GetFieldName('user_contact_password'),$user_contact->Get('user_contact_password'),array('onchange'=>$user_contact_js,'placeholder'=>'Primary Contact Password'));
//			echo("</td>");
//			echo("</tr>");
//		}
		if($user->Get('user_active') or $user_contact->Get('user_contact_phone'))
		{
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Primary Contact Phone Number</td>");
			echo("<td>");
			if(!$user->Get('user_active'))
				echo($user_contact->Get('user_contact_phone'));
			else
				form::DrawTextInput($user_contact->GetFieldName('user_contact_phone'),$user_contact->Get('user_contact_phone'),array('onchange'=>$user_contact_js,'placeholder'=>'Primary Contact Phone Number'));
			echo("</td>");
			echo("</tr>");
		}
		echo("<tr class='list_item'>");
		echo("<td class='edit_label'>Primary Contact Notifications</td>");
		echo("<td>");
		echo("<span class='edit_label_small'>Primary Contact Notifications </span>");
		if(!$user->Get('user_active'))
			echo($settings['notifications']['phone']?"<label>SMS</label> ":'');
		else
		{
			echo("<label>");
			form::DrawCheckbox($user_contact->GetFieldName('X_user_contact_settings').'[notifications][phone]',1,$settings['notifications']['phone'],array('disabled'=>'disabled','onchange'=>$user_contact_js,'placeholder'=>''));
			form::DrawHiddenInput($user_contact->GetFieldName('user_contact_settings').'[notifications][phone]',$settings['notifications']['phone']);
			echo(" SMS</label>");
		}
		if(!$user->Get('user_active'))
			echo($settings['notifications']['email']?"<label>Email</label> ":'');
		else
		{
			echo("<label>");
			form::DrawCheckbox($user_contact->GetFieldName('user_contact_settings').'[notifications][email]',1,$settings['notifications']['email'],array('onchange'=>$user_contact_js,'placeholder'=>''));
			echo(" Email</label>");
		}
		if(!$user->Get('user_active'))
			echo($settings['notifications']['user']?"<label>Client Items</label> ":'');
		else
		{
			echo("<label>");
			form::DrawCheckbox($user_contact->GetFieldName('user_contact_settings').'[notifications][user]',1,$settings['notifications']['user'],array('onchange'=>$user_contact_js,'placeholder'=>''));
			echo(" Client Items</label>");
		}
		if(!$user->Get('user_active'))
			echo($settings['notifications']['other']?"<label>Agent Items</label> ":'');
		else
		{
			echo("<label>");
			form::DrawCheckbox($user_contact->GetFieldName('user_contact_settings').'[notifications][other]',1,$settings['notifications']['other'],array('onchange'=>$user_contact_js,'placeholder'=>''));
			echo(" Agent Items</label>");
		}
		echo("</td>");
		echo("</tr>");
		if($user->Get('user_active'))// and $user_contact->Get('user_contact_email'))
		{
			if($user_contact->Get('user_contact_welcome_timestamp')>0)
			{
				echo("<tr class='list_item'>");
				echo("<td class='edit_label'>Primary Contact Welcome Email:</td><td>");
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','action=".$user_contact->GetFormAction('welcome')."&user_id=".$user->id."',function(){".$user_list_js."});";
				$js.="";
				$confirm="Are you sure you are ready to send a welcome email to this contact?";
			 	$date=new date();
			 	$date->Settimestamp($user_contact->Get('user_contact_welcome_timestamp'));
				if($user_contact->Get('user_contact_welcome_timestamp')>0)
					$confirm="Are you sure you would like to re-send a welcome email to this contact?  Welcome mail has already been sent on ".$date->GetDate('m/d/Y');
				$js="if(confirm('".$confirm."')){".$js."}";			
				echo("&nbsp;");
				if($user_contact->Get('user_contact_welcome_timestamp')>0)
					echo("<a href='#' onclick=\"".$js."return false;\">Welcome mail sent to ".$user_contact->Get('user_contact_welcome_email')." on ".$date->GetDate('m/d/Y')."</a>");
				else if(false)//don't show it here unless sent form dashboard..
					echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."return false;\">Send Welcome Email</a>");
	
				//echo("<a data-info='CONTACTS_WELCOME' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Send Welcome Email'><i class='fas fa-door-open'></i></a>");
				echo("</td>");
				echo("</tr>");
			}

			if(count($user_contacts->items))			
			{
				echo("<tr class='list_item'>");
				echo("<td class='edit_label'>Actions:</td><td>");
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','action=".$user_contact->GetFormAction('delete')."&user_id=".$user->id."',function(){".$user_list_js.";height_handler();});";
				$js.="";
				$js="if(confirm('Permanently delete this contact?')){".$js."}";			
				echo("<a data-info='CONTACTS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete' class='button agent_bg_color1'>Delete Contact</a>");
				echo("</td>");
				echo("</tr>");
			}
		}
		echo("<tr class='list_item_empty'>");
		echo("<td colspan='*'><br></td>");
		echo("</tr>");



		foreach($user_contacts->items as $i=>$user_contact)
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','NULL','user','','action=".$user_contact->GetFormAction('save')."&user_id=".$user->id."',function(){height_handler();});";

			$settings=json_decode($user_contact->Get('user_contact_settings'),true);

			echo("<tr class='agent_bg_color1'>");
			echo("<th class='edit_label'>Contact ".($i+2)."</th><th><div class='edit_label_small'>Contact ".($i+2)."</div></th>");
			echo("</tr>");

			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Name:</td><td>");
			if(!$user->Get('user_active'))
				echo($user_contact->Get('user_contact_name'));
			else
				form::DrawTextInput($user_contact->GetFieldName('user_contact_name'),$user_contact->Get('user_contact_name'),array('onchange'=>$js,'placeholder'=>"Contact ".($i+2)." Name"));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Email Address:</td><td>");
			if(!$user->Get('user_active'))
				echo($user_contact->Get('user_contact_email'));
			else
				form::DrawTextInput($user_contact->GetFieldName('user_contact_email'),$user_contact->Get('user_contact_email'),array('onchange'=>$js,'placeholder'=>"Contact ".($i+2)." Email Address"));
			echo("</td>");
			echo("</tr>");
//			echo("<tr class='list_item'>");
//			echo("<td class='edit_label'>Password:</td><td>");
//			if(!$user->Get('user_active'))
//				echo($user_contact->Get('user_contact_password'));
//			else
//				form::DrawTextInput($user_contact->GetFieldName('user_contact_password'),$user_contact->Get('user_contact_password'),array('onchange'=>$js,'placeholder'=>"Contact ".($i+2)." Password"));
//			echo("</td>");
//			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Phone:</td><td>");
			if(!$user->Get('user_active'))
				echo($user_contact->Get('user_contact_phone'));
			else
				form::DrawTextInput($user_contact->GetFieldName('user_contact_phone'),$user_contact->Get('user_contact_phone'),array('onchange'=>$js,'placeholder'=>"Contact ".($i+2)." Phone"));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Notifications:</td><td nowrap>");
			echo("<span class='edit_label_small'>Notifications:</span>");
			if(!$user->Get('user_active'))
				echo($settings['notifications']['email']?"<label>SMS</label> ":'');
			else
			{
				echo("<label>");
				form::DrawCheckbox($user_contact->GetFieldName('X_user_contact_settings').'[notifications][phone]',1,$settings['notifications']['phone'],array('disabled'=>'disabled','onchange'=>$user_contact_js,'placeholder'=>''));
				form::DrawHiddenInput($user_contact->GetFieldName('user_contact_settings').'[notifications][phone]',$settings['notifications']['phone']);
				echo(" SMS</label>");
			}
			if(!$user->Get('user_active'))
				echo($settings['notifications']['email']?"<label>Email</label> ":'');
			else
			{
				echo("<label>");
				form::DrawCheckbox($user_contact->GetFieldName('user_contact_settings').'[notifications][email]',1,$settings['notifications']['email'],array('onchange'=>$js,'placeholder'=>""));
				echo(" Email</label>");
			}
			echo("</td>");
			echo("</tr>");
			if($user->Get('user_active'))
			{
				echo("<tr class='list_item'>");
				echo("<td class='edit_label'>Actions:</td><td>");
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','action=".$user_contact->GetFormAction('delete')."&user_id=".$user->id."',function(){height_handler();});";
				$js.="";
				$js="if(confirm('Permanently delete this contact?')){".$js."}";			
				echo("<a data-info='CONTACTS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete' class='button agent_bg_color1'>Delete Contact</a>");
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','action=".$user_contact->GetFormAction('primary')."&user_id=".$user->id."',function(){height_handler();});";
				//$js.="";
				//$js="if(confirm('Make primary contact for this transaciton?')){".$js."}";			
				echo("&nbsp;");
				//echo("<a data-info='CONTACTS_PRIMARY' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' class='button agent_bg_color1'>Make Primary Contact</a>");

				//if($user_contact->Get('user_contact_email'))
				{
					$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','action=".$user_contact->GetFormAction('welcome')."&user_id=".$user->id."',function(){".$user_list_js."});";
					$js.="";
					$confirm="Are you sure you are ready to send a welcome email to this contact?";
					if($user_contact->Get('user_contact_welcome_timestamp')>0)
					{
					 	$date=new date();
					 	$date->Settimestamp($user_contact->Get('user_contact_welcome_timestamp'));
						$confirm="Are you sure you would like to re-send a welcome email to this contact?  Welcome mail has already been sent on ".$date->GetDate('m/d/Y');
					}
					$js="if(confirm('".$confirm."')){".$js."}";			
					if($user_contact->Get('user_contact_welcome_timestamp')>0)
						echo("<div style='margin:5px 0px;'><a href='#' onclick=\"".$js."return false;\">Welcome mail sent to ".$user_contact->Get('user_contact_welcome_email')." on ".$date->GetDate('m/d/Y')."</a></div>");
					else
						echo("&nbsp;<a class='button agent_bg_color1' href='#' onclick=\"".$js."return false;\">Send Welcome Email</a>");
					//echo("<a data-info='CONTACTS_WELCOME' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Send Welcome Email'><i class='fas fa-door-open'></i></a>");
				}
				echo("</td>");
				echo("</tr>");
			}

			echo("<tr class='list_item_empty'>");
			echo("<td colspan='*'><br></td>");
			echo("</tr>");			
		}
		if($user->Get('user_active'))
		{
			echo("<tr class='footer_actions'>");
			echo("<td colspan='1000'>");
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','action=".$user_contacts->newitems[0]->GetFormAction('save')."&user_id=".$user->id."&fn=".$params['fn']."',function(){height_handler();});";
			echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."return false;\">Add Additional Contact to this Transaction</a>");
			echo("</td>");
			echo("</tr>");
		}
		echo("<tr class='list_item_empty'><td><br></td></tr>");
		echo("</table>");

		echo("<table class='listing editing'>");
		echo("<tr class='agent_bg_color1'>");
		echo("<th class='edit_label'>Property Info</th><th><div class='edit_label_small'>Property Info</div></th>");
		echo("</tr>");
//		echo("<tr class='list_item'>");
//		echo("<td>Enter Client Name:</td>");
//		echo("<td>");
//		if(!$user->Get('user_active'))
//			echo($user->Get('user_name'));
//		else
//			form::DrawTextInput($user->GetFieldName('user_name'),$user->Get('user_name'),array('onchange'=>$user_js));
//		echo("</td>");
//		echo("</tr>");
		if($user->Get('user_active') or $user->Get('user_address'))
		{
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Enter Property Address (if/when applicable):</td>");
			echo("<td>");
			if(!$user->Get('user_active'))
				echo($user->Get('user_address'));
			else
				form::DrawTextInput($user->GetFieldName('user_address'),$user->Get('user_address'),array('onchange'=>$user_js_ext,'placeholder'=>"Enter Property Address (if/when applicable)"));
			echo("</td>");
			echo("</tr>");
		}
		if($user->Get('user_active') or $user->Get('template_id'))
		{
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Template for this transaction:</td>");
			echo("<td>");
			echo("<span class='edit_label_small'>Template for this transaction:</span>");
			$template=new template($user->Get('template_id'));
			if(!$user->Get('user_active'))
				echo($template->Get('template_name'));
			else
				form::DrawSelectFromSQL($user->GetFieldName('template_id'),"SELECT * FROM templates WHERE (template_active=1 AND template_status=1 AND template_id IN (".implode(',',$this->GetAvailableTemplateIds()).") OR template_id='".$user->Get('template_id')."') ORDER BY template_order",'template_name','template_id',$user->Get('template_id'),array('onchange'=>$user_js_ext),$user->Get('template_id')?'':array('--Choose Template--'=>''));
			echo("</td>");
			echo("</tr>");
		}
		if($user->Get('user_active') or $user->Get('user_mls_listing_url'))
		{
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Link To MLS Listing</td>");
			echo("<td>");
			if(!$user->Get('user_active'))
				echo("<a href='".$user->Get('user_mls_listing_url')."' target='_blank'>".$user->Get('user_mls_listing_url')."</a>");
			else
				form::DrawTextInput($user->GetFieldName('user_mls_listing_url'),$user->Get('user_mls_listing_url'),array('onchange'=>$user_js,'placeholder'=>"Link To MLS Listing"));
			echo("</div>");
			echo("</td>");
			echo("</tr>");
		}
		if($user->Get('user_active') or $user->Get('user_property_url'))
		{
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Link to Property Website (if applicable)</td>");
			echo("<td>");
			if(!$user->Get('user_active'))
				echo("<a href='".$user->Get('user_property_url')."' target='_blank'>".$user->Get('user_property_url')."</a>");
			else
				form::DrawTextInput($user->GetFieldName('user_property_url'),$user->Get('user_property_url'),array('onchange'=>$user_js,'placeholder'=>"Link to Property Website (if applicable)"));
			echo("</td>");
			echo("</tr>");
		}
		if($user->Get('user_active'))
		{
			echo("<tr class='list_item'>");
			echo("<td class='edit_label'>Show Agent Only Reminders for this transaction:</td>");
			echo("<td>");
			echo("<span class='edit_label_small'>Show Agent Only Reminders for this transaction</span>");
			if(!$user->Get('user_active'))
				echo("<label>".($user->Get('user_agent_only_notifications')?'Yes':'No')."</label>");
			else
			{
				form::DrawSelect($user->GetFieldName('user_agent_only_notifications'),array('Yes'=>1,'No'=>0),$user->Get('user_agent_only_notifications'),array('onchange'=>$user_js));
			}
			echo("</td>");
			echo("</tr>");
		}
/*
		if($user->Get('user_active'))
		{
			echo("<tr class='footer_actions'><td colspan='1000'>");
			$js="jQuery('#".$this->GetFieldName('EditUserContainer')."').html('');";
			$js2='';
			if(!$user->Get('user_initialized'))
				$js2="document.location='".$this->GetUserURL($user)."';";			
			$js.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('user_initialized')."',function(){".$js2."height_handler();});";		
			$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('ListUsersContainer')."').offset().top}, 1500);";
			echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."return false;\">Save & Close</a>");
			echo("</td>");
			echo("</tr>");
		}		
		echo("</table>");
*/		

		echo("<br><br>");

		$user_to_transaction_hander=new user_to_transaction_handler();
		$user_to_transaction_hander->InitByKeys(array('user_id','foreign_id','foreign_class'),array($user->id,get_class($this),$this->id));
		if(!$user_to_transaction_hander->Get('user_to_transaction_handler_settings_updated'))
		{
			$user_to_transaction_hander->CreateFromKeys(array('user_id','foreign_id','foreign_class'),array($user->id,get_class($this),$this->id));
			$user_to_transaction_hander->Set('user_to_transaction_handler_settings',$this->GetSettings());  //mirror my settings as they are today.
			$user_to_transaction_hander->Update();
		}
		$user_to_transaction_hander->ProcessAction();
		
		$settings=json_decode($user_to_transaction_hander->Get('user_to_transaction_handler_settings'),true);
		$user_to_transaction_hander_js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','NYLL','user','','action=".$user_to_transaction_hander->GetFormAction('save')."&user_id=".$user->id."',function(){height_handler();});";
		$user_to_transaction_hander_js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','user','','action=".$user_to_transaction_hander->GetFormAction('save')."&user_id=".$user->id."',function(){height_handler();});";

		echo("<table class='listing editing'>");
		if($user->Get('user_active'))
		{
			echo("<tr class='agent_bg_color1'>");
			echo("<th class='edit_label'>Notifications</th>");
			echo("</tr>");
			echo("<tr class='list_item'><td>");
			echo("<label>");
			form::DrawCheckbox('notifications[user]',1,$settings['notifications']['user'],array('onchange'=>$user_to_transaction_hander_js));
			echo(" Receive reminders for client items</label>");
			echo("</td></tr>");
			echo("<tr class='list_item'><td>");
			echo("<label>");
			form::DrawCheckbox('notifications[other]',1,$settings['notifications']['other'],array('onchange'=>$user_to_transaction_hander_js));
			echo(" Receive reminders for agent/other items</label>");
			echo("</td></tr>");
			echo("<tr class='list_item'><td>");
			echo("<label>");
			form::DrawCheckbox('notifications[agent]',1,$settings['notifications']['agent'],array('onchange'=>$user_to_transaction_hander_js));
			echo(" Receive reminders for agent-only items</label>");
			echo("</td></tr>");

			echo("<tr class='footer_actions'><td colspan='1000'>");
			$js="jQuery('#".$this->GetFieldName('EditUserContainer')."').html('');";
			$js2='';
			if(!$user->Get('user_initialized'))
				$js2="document.location='".$this->GetUserURL($user)."';";			
			$js.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('user_initialized')."',function(){".$js2."height_handler();});";		
			$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('ListUsersContainer')."').offset().top}, 1500);";
			echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."return false;\">Save & Close</a>");
			echo("</td>");
			echo("</tr>");
		}
		echo("</table>");

		echo("</div>");		
		form::End();	
	}


	public function IsCoordinator()
	{			
		return false;
	}

	public function IsAgent()
	{			
		return false;
	}

	public function ProgressMeter($params)
	{
		if(!$params['user_id'])
			return;
	
	 	$user=new user($params['user_id']);
	
	 	echo("<div class='progress_meter_full'>");
		echo("<h2 class=''>Progress Meter</h2>");
	 	echo("<div class='progress_meter'>");
		$total=$user->CountSteps("timeline_item_for='AGENT' OR timeline_item_for='USER'");
		$done=min($user->CountSteps("(timeline_item_for='AGENT' OR timeline_item_for='USER') AND timeline_item_complete>0"),$total);
		if($total>0)
		{
			$percent=round(($done*100)/$total);
		 	echo("<h5>Client Items</h3>");
		 	echo("<div class='progress_bar_container'><div class='progress_bar agent_bg_color2' style='width:".($percent)."%'></div></div>");
		 	echo("<div class='progress_info'>".$percent."% Complete (".$done." of ".$total." items)</div>");
		}
		echo("</div>");
	
	
	 	echo("<div class='progress_meter'>");
		$total=$user->CountSteps("timeline_item_for!='USER'");
		$done=min($user->CountSteps("timeline_item_for!='USER' AND timeline_item_complete>0"),$total);
		if($total>0)
		{
			$percent=round(($done*100)/$total);
		 	echo("<h5>Agent & Agent Only Items</h3>");
		 	echo("<div class='progress_bar_container'><div class='progress_bar agent_bg_color1' style='width:".($percent)."%'></div></div>");
		 	echo("<div class='progress_info'>".$percent."% Complete (".$done." of ".$total." items)</div>");
		}
		echo("</div>");
	
	
	 	echo("<div class='progress_meter'>");
		$total=$user->CountSteps();
		$done=min($user->CountSteps("timeline_item_complete>0"),$total);
		$total2=$user->CountSteps("(timeline_item_for='AGENT' OR timeline_item_for='USER')");
		$done2=min($user->CountSteps("(timeline_item_for='AGENT' OR timeline_item_for='USER') AND timeline_item_complete>0"),$total2);
	
		if($total>0)
		{
			$percent=round(($done*100)/$total);
			$percent2=round(($done2*100)/$total2);
		 	echo("<h5>Overall</h3>");
		 	echo("<div class='progress_bar_container'><div class='progress_bar agent_bg_color1' style='width:".($percent)."%'></div></div>");
		 	echo("<div class='progress_info'>".$percent."% Complete (".$done." of ".$total." items). Client overall is ".$percent2."%</div>");
		}
		echo("</div>");
		echo("</div>");
	}	
	
	public function ProgressMeterMobile($params)
	{
		if(!$params['user_id'])
			return;
	
		$user=new user($params['user_id']);
	
	 	echo("<div class='progress_meter_mobile'>");
	 	echo("<div class='progress_meter_mobile_short'>");
	 	echo("<div class='progress_meter'>");
		$total=$user->CountSteps();
		$done=min($user->CountSteps("timeline_item_complete>0"),$total);
		if($total>0)
		{
			$percent=round(($done*100)/$total);
		 	echo("<div class='progress_bar_container'><div class='progress_bar' style='width:".($percent)."%'></div><div class='progress_percent' style='left:".($percent)."%'>".$percent."%</div></div>");
		}
		echo("</div>");
		echo("</div>");
	
	 	echo("<div class='progress_meter_mobile_full'>");
		$this->ProgressMeter($params);
		echo("</div>");
	
		echo("</div>");
	}

	public function ListContactInfo($params)
	{
	 	$user=new user($params['user_id']);
	 	if($params['action']==$user->GetFormAction('toggle_contact_info'))
	 	{
			$user->Set($params['toggle_which'],!$user->Get($params['toggle_which']));
			$user->Update();
		}
		
		if($this->Get('agent_phone'))
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListContactInfo','contact_info_container','NULL','','user_id=".$user->id."&agent=1&action=".$user->GetFormAction('toggle_contact_info')."&toggle_which=user_sidebar_phone_off');";
			$js.="return false;";
			if($user->Get('user_sidebar_phone_off'))
				echo("<a class='button button_disabled' target='_blank' href='tel:".$this->Get('agent_phone')."'><i class='icon fas fa-phone'></i><i class='icon icon2 fa fa-trash-restore' onclick=\"".$js."\"></i><span class='text'>Call Me</span></a>");
			else
				echo("<a class='button agent_bg_color1 agent_bg_color2_hover' target='_blank' href='tel:".$this->Get('agent_phone')."'><i class='icon fas fa-phone'></i><i class='icon icon2 fa fa-trash' onclick=\"".$js."\"></i><span class='text'>Call Me</span></a>");
		}
		if($this->Get('agent_email'))
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListContactInfo','contact_info_container','NULL','','user_id=".$user->id."&agent=1&action=".$user->GetFormAction('toggle_contact_info')."&toggle_which=user_sidebar_email_off');";
			$js.="return false;";
			if($user->Get('user_sidebar_email_off'))
				echo("<a class='button button_disabled' target='_blank' href='mailto:".$this->Get('agent_email')."'><i class='icon fas fa-envelope'></i><i class='icon icon2 fa fa-trash-restore' onclick=\"".$js."\"></i><span class='text'>Email Me</span></a>");
			else
				echo("<a class='button agent_bg_color1 agent_bg_color2_hover' target='_blank' href='mailto:".$this->Get('agent_email')."'><i class='icon fas fa-envelope'></i><i class='icon icon2 fa fa-trash' onclick=\"".$js."\"></i><span class='text'>Email Me</span></a>");
		}
		if($user->Get('user_mls_listing_url'))
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListContactInfo','contact_info_container','NULL','','user_id=".$user->id."&agent=1&action=".$user->GetFormAction('toggle_contact_info')."&toggle_which=user_sidebar_mls_off');";
			$js.="return false;";
			if($user->Get('user_sidebar_mls_off'))
				echo("<a class='button button_disabled' target='_blank' href='".$user->Get('user_mls_listing_url')."'><i class='icon fas fa-home'></i><i class='icon icon2 fa fa-trash-restore' onclick=\"".$js."\"></i><span class='text'>Link To MLS Listing</span></a>");
			else
				echo("<a class='button agent_bg_color1 agent_bg_color2_hover' target='_blank' href='".$user->Get('user_mls_listing_url')."'><i class='icon fas fa-home'></i><i class='icon icon2 fa fa-trash' onclick=\"".$js."\"></i><span class='text'>Link To MLS Listing</span></a>");
		}
		if($user->Get('user_property_url'))
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListContactInfo','contact_info_container','NULL','','user_id=".$user->id."&agent=1&action=".$user->GetFormAction('toggle_contact_info')."&toggle_which=user_sidebar_property_url_off');";
			$js.="return false;";
			if($user->Get('user_sidebar_property_url_off'))
				echo("<a class='button button_disabled' target='_blank' href='".$user->Get('user_property_url')."'><i class='icon fas fa-home'></i><i class='icon icon2 fa fa-trash-restore' onclick=\"".$js."\"></i><span class='text'>Link To Property Website</span></a>");
			else
				echo("<a class='button agent_bg_color1 agent_bg_color2_hover' target='_blank' href='".$user->Get('user_property_url')."'><i class='icon fas fa-home'></i><i class='icon icon2 fa fa-trash' onclick=\"".$js."\"></i><span class='text'>Link To Property Website</span></a>");
		}
		if(trim($user->Get('user_address')))
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListContactInfo','contact_info_container','NULL','','user_id=".$user->id."&agent=1&action=".$user->GetFormAction('toggle_contact_info')."&toggle_which=user_sidebar_directions_off');";
			$js.="return false;";
			if($user->Get('user_sidebar_directions_off'))
				echo("<a class='button button_disabled' target='_blank' href='https://www.google.com/maps/dir//".$user->Get('user_address')."'><i class='icon fas fa-directions'></i><i class='icon icon2 fa fa-trash-restore' onclick=\"".$js."\"></i><span class='text'>Directions To Property</span></a>");
			else
				echo("<a class='button agent_bg_color1 agent_bg_color2_hover' target='_blank' href='https://www.google.com/maps/dir//".$user->Get('user_address')."'><i class='icon fas fa-directions'></i><i class='icon icon2 fa fa-trash' onclick=\"".$js."\"></i><span class='text'>Directions To Property</span></a>");
		}
	}


	public function AddWidget()
	{
		global $HTTP_POST_VARS;

		$widget=new widget();
		$widget->Set('user_id',$HTTP_POST_VARS['user_id']);
		$widget->Set('agent_id',$this->id);
		$widget->Save();

		if($widget->Get('widget_type')!='CONTENT')
		{
			$vendor=new vendor($widget->Get('vendor_id'));
			if(!$vendor->id)//not ideall.... ? 
				$vendor->Set($this->primary,$this->id);
			$vendor->Save();
			if($vendor->Get('vendor_type_id')=='NEW_TYPE')
			{
				$vendor_type=new vendor_type();
				$vendor_type->Save();
				$vendor->Set('vendor_type_id',$vendor_type->id);		
				$vendor->Update();
			}


			$widget->Set('vendor_id',$vendor->id);
			$widget->Update();
		}

	}
	
	public function EditWidgets($params=array())
	{
		global $HTTP_POST_VARS;

		$this->ProcessAction();
		
		$user=new user($params['user_id']);	

		if($params['action']=='add_widget')
		{

		  	$widget=new widget();
		  	$widget->Set('user_id',$params['user_id']);
		  	$widget->Set('agent_id',$user->Get('agent_id'));			
		  	$widget->Save();
		}	

	 	$where=array("user_id='".$params['user_id']."'");

	  	$list=new DBRowSetEX('widgets','widget_id','widget',implode(' AND ',$where),'widget_order');
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('vendor_id',$HTTP_POST_VARS['vendor_id']);
	  	$list->SetEachNew('widget_type',$HTTP_POST_VARS['widget_type']);
	  	$list->SetEachNew('user_id',$params['user_id']);
	  	$list->SetEachNew('agent_id',$this->id);
		$list->SetFlag('ALLOW_BLANK');
		$list->ProcessAction();
	  	$list->CheckSortOrder('widget_order');
		$list->SetFlag('ALLOW_BLANK');
	  	$list->ProcessAction();
	  	$list->Retrieve();
	  	$list->SetEachNew('vendor_id',$HTTP_POST_VARS['vendor_id']);
	  	$list->SetEachNew('widget_type',$HTTP_POST_VARS['widget_type']);
	  	$list->SetEachNew('user_id',$params['user_id']);
	  	$list->SetEachNew('agent_id',$user->Get('agent_id'));

		foreach($list->items as $widget)
		{
			echo("<div id='".$widget->GetFieldName('AgentCardContainer')."'>");
			$widget->AgentCard($params);
			echo("</div>");

		}
		foreach($list->newitems as $widget)
		{
			echo("<div id='".$widget->GetFieldName('AgentCardContainer')."'>");
			$widget->NewAgentCard($params);
			echo("</div>");
		}
	}


	public function GetTimelineItems($params=array())
	{
		$template=new template($params['template_id']);
		$user=new user($params['user_id']);

		$where=array("1");
		if($params["template_id"])
			$where[]="template_id='".$params["template_id"]."'";
		if($params["coordinator_id"])
			$where[]="coordinator_id='".$params["coordinator_id"]."'";
		if($params["agent_id"])
			$where[]="agent_id='".$params["agent_id"]."'";
		if($params["user_id"])
			$where[]="user_id='".$params["user_id"]."'";
		if($params['where'])
			$where[]=$params['where'];
		if($user->Get('user_under_contract'))
			$where[]="(timeline_item_type='TIMELINE' OR timeline_item_hide_uc=0)";

		if(!Session::Get($this->GetFieldName('show_deleted')) and !$params['show_deleted'])
			$where[]="(timeline_item_active=1 OR timeline_item_id='".$params['include_timeline_item_id']."')";
		if(!Session::Get($this->GetFieldName('show_completed')) and !$params['show_completed'])
			$where[]="(timeline_item_complete=0 OR timeline_item_id='".$params['include_timeline_item_id']."')";

		$order='timeline_item_order';
		if($user->Get('user_under_contract'))
			$order="timeline_item_date,timeline_item_order";
			//$order="CASE WHEN timeline_item_reference_date_type='NONE' OR timeline_item_date<'1970-01-01' THEN timeline_item_order ELSE timeline_item_date END,timeline_item_order";

	  	$list=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order);
		if($params['debug'])
			die($list->GetQuery());
	  	$list->Retrieve();


	  	return $list;
				
	}
	
	public function EditTimeline($params=array())
	{
	 	if(!$params['template_id'] and !$params['user_id'])
	 		_navigation::Redirect('index.php');

		$user=new user($params['user_id']);
		//$template=new template($params['template_id']); // no... dont add to the template.

		echo("<div class='timeline'>");
	  	$list=$this->GetTimelineItems($params);
		$list->num_new=0;
	  	$list->Retrieve();
	  	$list->SetFlag('ALLOW_BLANK');
	  	$list->SetEachNew('timeline_item_order',0);	  	
	  	$list->SetEachNew('template_id',$template->id);
	  	$list->SetEachNew('agent_id',$user->Get('agent_id'));
	  	$list->SetEachNew('user_id',$user->id);
  		$list->ProcessAction();
		$list->CheckSortOrder('timeline_item_order');
	  	$list->SetEachNew('timeline_item_order',0);	  	
	  	$list->SetEachNew('template_id',$template->id);
	  	$list->SetEachNew('agent_id',$user->Get('agent_id'));
	  	$list->SetEachNew('user_id',$user->id);
	  	$list->SetFlag('ADMIN',$this->GetFlag('ADMIN'));

		$has_date=false;
		foreach($list->items as $timeline_item)
		{
			$d=new date($timeline_item->Get('timeline_item_date'));
			if(!$has_date and $d->IsValid())
			{
				$this->DisplayContractBoundary($params);
				$has_date=true;	
			}


			echo("<div id='".$timeline_item->GetFieldName('AgentCardContainer')."'>");			
			$timeline_item->AgentCard($params);
			echo("</div>");
		}
		echo("</div>");
	}
	
	public function DisplayContractBoundary($params=array())
	{
		$user=new user($params['user_id']);
		if(!$user->Get('user_under_contract'))
		 	return;
		 	
		$d=new Date();
		$d->SetTimestamp($user->Get('user_under_contract'));
		$js="ObjectFunctionAjaxPopup('Property Fell Out of Escrow','".get_class($this)."','".$this->id."','OutOfEscrowConfirmation','NULL','','user_id=".$params['user_id']."',function(){});return false;";
		echo ("<div class='timeline_contract_boundary'>");
		echo ("<div class='row'>");
		echo ("<div class='col-md-6'><div class='timeline_contract_boundary_info'>Went under contract ".$d->GetDate('m/d/Y')."</div></div>");
		echo ("<div class='col-md-6'><div class='timeline_contract_boundary_action'><a href='' onclick=\"".$js."\">Click here if property falls out of contract</a></div></div>");
		echo ("</div>");
		echo ("</div>");
	}


	public function EditIntro($params=array())
	{
	 	$user=new user($params['user_id']);
	 	$user->SetFlag('ALLOW_BLANK');
	 	$user->ProcessAction();

		$savejs="UpdateWYSIWYG();";
		$savejs.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditIntro','','".$this->GetFieldName('intro')."','','user_id=".$user->id."&action=".$user->GetFormAction('save')."',function(){});";
		$savejs2="UpdateWYSIWYG();";
		$savejs2.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditIntro','intro_container','".$this->GetFieldName('intro')."','','user_id=".$user->id."&action=".$user->GetFormAction('save')."',function(){});";

		form::Begin('','POST',true,array('id'=>$this->GetFieldName('intro')));
		echo("<div class='client_intro'>");
		echo("<div class='intro_agent_edit'>");
		//$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTimeline','timeline_container','NULL','','user_id=".$user->id."&agent_id=".$this->id."&agent=1');";
		//$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditIntro','intro_container','".$this->GetFieldName('reset')."','','user_id=".$user->id."&agent_id=".$this->id."&agent=1&action=".$user->GetFormAction('reset')."',function(){".$js2."});";
		//echo("<a href='#' onclick=\"if(confirm('Retore template To Default?')){".$js."}return false;\" data-tggle='tooltip' title='Reset to Default'><i class='fa fa-sync'></i></a>");
		echo("</div>");
		echo("<div class='client_intro_address'>");
		form::DrawTextInput($user->GetFieldName('user_address'),$user->Get('user_address'),array('class'=>'text H2','placeholder'=>'Address','onchange'=>$savejs));
		echo("</div>");
		echo("<div class='client_intro_image drop_target' data-target='".$user->GetFieldName('user_image_file_ul')."'>");
		if($user->Get('user_image_file'))
			echo("<img src='".$user->GetThumb(1170,513)."'>	");
		else if($user->Get('user_type')=='BUYER')
			echo("<img src=' /uploads/pics/buyer-placeholder-image.jpg'>");
		else if($user->Get('user_type')=='SELLER')
			echo("<img src=' /uploads/pics/seller-placeholder.png'>");
		else
			echo("<img src=' /images/placeholder.png'>");
		echo("<br>");
		form::DrawFileInput($user->GetFieldName('user_image_file_ul'),$user->Get('user_image_file'),array('placeholder'=>'Image','onchange'=>$savejs2));		
		echo("</div>");
		echo("<div class='client_intro_text'>");
		if($params['action']==$user->GetFormAction('edit_user_content'))
		{
			$canceljs="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditIntro','intro_container','NULL','','user_id=".$user->id."&agent_id=".$this->id."&agent=1',function(){});";

			form::DrawTextArea($user->GetFieldName('user_content'),$user->Get('user_content'),array('class'=>'wysiwyg_input wysiwyg_intro','onchange'=>$xsavejs));
			$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
			$wysiwyg_info.="onchange_callback:function(){".$xsavejs."},\r\n";
			wysiwyg::RegisterMode($user->GetFieldName('SIMPLE_LINK_HEADLINES'),$wysiwyg_info);
			form::MakeWYSIWYG($user->GetFieldName('user_content'),$user->GetFieldName('SIMPLE_LINK_HEADLINES'));
			echo("<div class='line' style='text-align:right;margin:10px 0px;'>");
			echo("<a class='button ".$this->GetDarkerHoverClass()."' href='#' onclick=\"".$canceljs."return false;\">Cancel</a> ");
			echo("<a class='button ".$this->GetDarkerHoverClass()."' href='#' onclick=\"".$savejs2."return false;\">Save Changes</a>");
			echo("</div class='line'>");
		}
		else
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditIntro','intro_container','NULL','','user_id=".$user->id."&agent_id=".$this->id."&agent=1&action=".$user->GetFormAction('edit_user_content')."',function(){});";
			echo("<div class='client_intro_text_editable' onclick=\"".$js."\">".$user->Get('user_content')."</div>");
		}
		echo("</div>");
		echo("</div>");	
		form::End();
	}

	public function AgentToolsNewTemplateConfirmation($params=array())
	{
	 	$user=new user($params['user_id']);

		if(!$params['user_id'])
			return false;
		
	 	$template=new template($user->Get('template_id'));
		//modify this one.
		if($params['action']=='update_template')
		{
			$user->Save();			
			$new_template=new template($user->Get('template_id'));
			$params['action']='copy_template';
			$message='Template Updated';
		}
		else
		{
		 	//create a copy.
			$new_template=new template();
			$new_template->Copy($template);
			$new_template->Set('template_name','Copy Of '.$new_template->Get('template_name'));
			$new_template->Set('template_copied',1);
			$new_template->Set('agent_id',$this->id);
			$new_template->Set('original_id',0);
			$new_template->GatherInputs();

			$message='New template saved.';
		}
		if($params['action']=='copy_template')
		{
			$new_template->Update();
			timeline_item::CopyAll(array('user_id'=>$user->id),array('agent_id'=>$this->id,'template_id'=>$new_template->id));
			$user->Set('template_id',$new_template->id);
			$user->Update();
			
			//Javascript::Begin();
			//echo("document.location='';");
			//Javascript::End();
			

			echo("<h3 class='agent_color1'>".$message."</h3>");
			echo("<div>Would you like to <a href='".$this->DirectURL("edit_timeline.php?template_id=".$new_template->id)."'>edit this template</a> or <a href='#' onclick=\"PopupClose();return false;\">go back to your client's timeline</a>?</div>");
		}
		else
		{
			form::Begin('','POST',false,array('id'=>'AgentToolsNewTemplateConfirmation'));
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsNewTemplateConfirmation','popup_content','AgentToolsNewTemplateConfirmation','','user_id=".$params['user_id']."&action=copy_template',function(){});return false;";
			echo("<div class='save_as_new_template'>");
			echo("<h3 class='agent_color1'>Save As New Template?</h3>");
			echo("<div class='line'>");
			echo("<div class='row'>");
			echo("<div class='col-md-3'>");
			echo("<lable>Name:</label>");
			echo("</div>");
			echo("<div class='col-md-9'>");
			form::DrawTextInput($new_template->GetFieldName('template_name'),$new_template->Get('template_name'));
			echo("</div>");
			echo("</div>");
			echo("</div>");
			echo("<div class='line'>");
			echo("<div class='row'>");
			echo("<div class='col-md-3'>");
			echo("</div>");
			echo("<div class='col-md-9'>");
			echo("<a class='button agent_bg_color1' onclick=\"".$js."\">Save</a>");
			echo("</div>");
			echo("</div>");
			echo("</div>");
			
			if($template->Get('agent_id'))		
			{
				echo("<h3 class='agent_color1'>OR Update Template</h3>");
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsNewTemplateConfirmation','popup_content','AgentToolsNewTemplateConfirmation','','user_id=".$params['user_id']."&action=update_template',function(){});return false;";
				echo("<div class='line'>");
				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Name:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawSelectFromSQL($user->GetFieldName('template_id'),"SELECT * FROM templates WHERE template_active=1 AND template_status=1 AND agent_id='".$template->Get('agent_id')."'","template_name","template_id",$template->id);
				//echo($new_template->Get('template_name'));
				echo("</div>");
				echo("</div>");
				echo("</div>");	
				echo("<div class='line'>");
				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				echo("<a href='#' class='button agent_bg_color1' onclick=\"".$js."\">Update Template</a>");
				echo("</div>");
				echo("</div>");
				echo("</div>");
			}
			echo("</div>");
			form::End();
		}
	}

	public function AgentToolsPopup($params=array())
	{
	 	$user=new user($params['user_id']);

		echo("<h3 class='agent_color1'>Agent Tools</h3>");
		
		$this->AgentTools($params);

		echo("<div class='line'>");
		echo("<a href='#' class='button agent_bg_color1' onclick=\"PopupClose();return false;\">Close</a>");
		echo("</div>");
	}

	public function AgentToolsFilterPopup($params=array())
	{
		global $HTTP_POST_VARS;
		
		if($params['action']=='UpdateAgentToolsFilters')
		{
			Session::Set($this->GetFieldName('condensed_view'),$HTTP_POST_VARS[$this->GetFieldName('condensed_view')]);
			Session::Set($this->GetFieldName('show_deleted'),$HTTP_POST_VARS[$this->GetFieldName('show_deleted')]);
			Session::Set($this->GetFieldName('show_completed'),$HTTP_POST_VARS[$this->GetFieldName('show_completed')]);
		}
		
	 	$user=new user($params['user_id']);

		$js2="PopupClose();";
		$js2.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsButtons','agent_tools_buttons_container','NULL','','user_id=".$params['user_id']."&template_id=".$params['template_id']."',function(){".$js3."});";
		$js2.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTimeline','timeline_container','NULL','','agent_id=".$this->id."&user_id=".$params['user_id']."&template_id=".$params['template_id']."&agent=1');";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsFilterPopup','popup_content','".$this->GetFieldName('AgentToolsFilterForm')."','','action=UpdateAgentToolsFilters&user_id=".$params['user_id']."&template_id=".$params['template_id']."',function(){".$js2."});return false;";

		form::Begin('?action=','POST',true,array('id'=>$this->GetFieldName('AgentToolsFilterForm')));
		echo("<h3 class='agent_color1'>Filter Displayed Items</h3>");
		echo("<div class='line'>");
		echo("<label>");
		form::DrawCheckbox($this->GetFieldName('condensed_view'),1,Session::Get($this->GetFieldName('condensed_view')));
		echo(" Condensed View</label>");
		echo("</div>");	
		echo("<div class='line'>");
		echo("<label>");
		form::DrawCheckbox($this->GetFieldName('show_deleted'),1,Session::Get($this->GetFieldName('show_deleted')));
		echo(" Show Deleted Items</label>");
		echo("</div>");	
		if($params['user_id'])
		{
			echo("<div class='line'>");
			echo("<label>");
			form::DrawCheckbox($this->GetFieldName('show_completed'),1,Session::Get($this->GetFieldName('show_completed')));
			echo(" Show Completed Items</label>");
			echo("</div>");	
		}
		form::End();

		echo("<div class='line'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6' style='text-align:center'>");
		echo("<a href='#' class='button agent_bg_color1' onclick=\"".$js."return false;\">Apply</a>");
		echo("</div>");
		echo("<div class='col-xs-6' style='text-align:center'>");
		echo("<a href='#' class='button agent_bg_color1' onclick=\"PopupClose();return false;\">Close</a>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
	}

	public function AgentToolsXS($params)
	{
		//small size
		echo("<div class='agent_tools agent_tools_xs'>");

		$js="ObjectFunctionAjaxPopup('Filter Displayed Items','".get_class($this)."','".$this->id."','AgentToolsFilterPopup','NULL','','user_id=".$params['user_id']."',function(){});return false;";
		echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Filter Displayed Items' href='#' onclick=\"".$js."\"><i class='icon fas fa-sliders-h'></i><span class='text'>Filter Displayed Items<span></a>");

		$js="ObjectFunctionAjaxPopup('Agent Tools','".get_class($this)."','".$this->id."','AgentToolsPopup','NULL','','user_id=".$params['user_id']."',function(){});return false;";
		echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Open Agent Tools' href='#' onclick=\"".$js."\"><i class='icon fa-solid fa-gear'></i><span class='text'>Agent Tools<span></a>");

		echo("</div>");
	}

	public function AgentTools($params)
	{
		if(!$params['user_id'])
			return false;

	 	$user=new user($params['user_id']);
		$user->ProcessAction();
		$this->ProcessAction();

		//full size
		echo("<div class='agent_tools'>");

		echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Add/edit contact and notification info' href='".$this->DirectURL("?action=EditUser&user_id=".$user->id)."#EditUserContainer'><i class='icon fas fa-user'></i><span class='text'>Edit Client Info<span></a>");

		if(!$user->Get('user_under_contract'))
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=".$user->GetFormAction('under_contract')."',function(){document.location='".$this->GetUserURL($user,'edit_user_dates.php')."';});return false;";
			echo("<a class='not_under_contract ".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Click when the property goes under contract.' href='#' onclick=\"".$js."\"><i class='icon fas fa-handshake-slash'></i><span class='text'><span><i class='not_under_contract fa fa-circle'></i><i class='under_contract fa fa-circle-check'></i>Under Contract</span></a>");
		}
		if($user->Get('user_under_contract'))
		{
			$js="ObjectFunctionAjaxPopup('Property Fell Out of Escrow','".get_class($this)."','".$this->id."','OutOfEscrowConfirmation','NULL','','user_id=".$params['user_id']."',function(){});return false;";
			echo("<a class='under_contract ".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Click if property falls out of contract.' href='#' onclick=\"".$js."\"><i class='icon fas fa-handshake'></i><span class='text'><i class='under_contract fa fa-circle-check'></i><i class='not_under_contract fa fa-circle-xmark'></i>Under Contract</span></a>");

			echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Click here to edit contract dates/terms' href='".$this->GetUserURL($user,'edit_user_dates.php')."'><i class='icon fas fa-solid fa-list-alt'></i><span class='text'>Edit Contract Terms</span></a>");
		}

//		$js2="ObjectFunctionAjaxPopup('Reminder Sent','".get_class($this)."','".$this->id."','ReminderSentConfirmation','NULL','','user_id=".$params['user_id']."',function(){});return false;";
//		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=".$user->GetFormAction('send_reminder')."',function(){".$js2."});return false;";
		$js="ObjectFunctionAjaxPopup('Send Reminder','".get_class($this)."','".$this->id."','SendRemindersPopup','NULL','','user_id=".$params['user_id']."',function(){".$js2."});return false;";
		echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Send notification for past due and upcoming tasks' href='#' onclick=\"".$js."\"><i class='icon fas fa-solid fa-envelope'></i><span class='text'>Send Client Reminder</span></a>");

		$js2="document.location='".$this->DirectURL("past.php")."';";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=".$user->GetFormAction('archive_transaction')."',function(){".$js2."});return false;";
		$js="if(confirm('Archive this transaction?  This will turn off all reminders (to agent and client), but this transaction can be reviewed and restored at any time by clicking on the &quot;Archived&quot; link at the top of the page')){".$js."}return false;";			
		echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Click when you are finished with this transaction' href='#' onclick=\"".$js."\"><i class='icon fas fa-archive'></i><span class='text'>Archive</span></a>");

		$js="ObjectFunctionAjaxPopup('Save As New Template','".get_class($this)."','".$this->id."','AgentToolsNewTemplateConfirmation','NULL','','user_id=".$params['user_id']."',function(){});return false;";
		echo("<a class='".$this->GetDarkerHoverClass()." save_as_new_template' data-toggle='tooltip' title='Save this timeline as a new template' href='#' onclick=\"".$js."\"><i class='icon fas fa-solid fa-save'></i><span class='text'>Save as New Template</span></a>");

		echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='See when tasks were completed' href='".$this->GetUserURL($user,'activity_log.php')."'><i class='icon fas fa-chart-line'></i><span class='text'>View activity log</span></a>");

		echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Print Timeline' href='".$this->GetUserURL($user,'print_timeline.php')."' target='_blank'><i class='icon fas fa-print'></i><span class='text'>Print Timeline</span></a>");

		echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Return to Dashboard' href='".$this->DirectURL()."'><i class='icon fas fa-sign-in-alt'></i><span class='text'>Return to Dashboard</span></a>");

		//$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=inspection',function(){});return false;";
		//echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='' href='#' onclick=\"".$js."\"><i class='icon fas fa-search'></i><span class='text'>Click here if/when you want to book the inspection</span></a>");

		//$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=termite',function(){});return false;";
		//echo("<a class='".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='' href='#' onclick=\"".$js."\"><i class='icon fas fa-bug'></i><span class='text'>Click here if/when you want to book termite inspection</span></a>");

		echo("</div>");				
	}

	public function AgentToolsButtons($params)
	{
	 	$user=new user($params['user_id']);
		$user->ProcessAction();
		$this->ProcessAction();

		echo("<div class='agent_tools_buttons'>");				
		echo("Filter Timeline View:");				
		if(Session::Get($this->GetFieldName('condensed_view')))
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsButtons','agent_tools_buttons_container','NULL','','user_id=".$params['user_id']."&template_id=".$params['template_id']."&action=".$this->GetFormAction('toggle_condensed_view')."',function(){});return false;";
			Javascript::Begin();
			echo("jQuery('BODY').addClass('timeline_condensed');");
			echo("jQuery('.timeline_item_expanded').removeClass('timeline_item_expanded');");
			Javascript::End();
			echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."\"><span class='text'>Turn Off Condensed View</span></a>");
		}
		else
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsButtons','agent_tools_buttons_container','NULL','','user_id=".$params['user_id']."&template_id=".$params['template_id']."&action=".$this->GetFormAction('toggle_condensed_view')."',function(){});return false;";
			Javascript::Begin();
			echo("jQuery('BODY').removeClass('timeline_condensed');");
			echo("jQuery('.timeline_item_expanded').removeClass('timeline_item_expanded');");
			Javascript::End();
			echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."\"></i><span class='text'>Turn On Condensed View</span></a>");
		}
		if(Session::Get($this->GetFieldName('show_deleted')))
		{
			$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTimeline','timeline_container','NULL','','agent_id=".$this->id."&user_id=".$params['user_id']."&template_id=".$params['template_id']."&agent=1');";
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsButtons','agent_tools_buttons_container','NULL','','user_id=".$params['user_id']."&template_id=".$params['template_id']."&action=".$this->GetFormAction('toggle_deleted_view')."',function(){".$js2."});return false;";
			echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Hide Deleted Items</span></a>");
		}
		else
		{
			$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTimeline','timeline_container','NULL','','agent_id=".$this->id."&user_id=".$params['user_id']."&template_id=".$params['template_id']."&agent=1');";
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsButtons','agent_tools_buttons_container','NULL','','user_id=".$params['user_id']."&template_id=".$params['template_id']."&action=".$this->GetFormAction('toggle_deleted_view')."',function(){".$js2."});return false;";
			echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Show Deleted Items</span></a>");
		}
		if($params['user_id'])
		{
			if(Session::Get($this->GetFieldName('show_completed')))
			{
				$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTimeline','timeline_container','NULL','','agent_id=".$this->id."&user_id=".$params['user_id']."&template_id=".$params['template_id']."&agent=1');";
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsButtons','agent_tools_buttons_container','NULL','','user_id=".$params['user_id']."&template_id=".$params['template_id']."&action=".$this->GetFormAction('toggle_commpleted_view')."',function(){".$js2."});return false;";
				echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Hide Completed Items</span></a>");
			}
			else
			{
				$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTimeline','timeline_container','NULL','','agent_id=".$this->id."&user_id=".$params['user_id']."&template_id=".$params['template_id']."&agent=1');";
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentToolsButtons','agent_tools_buttons_container','NULL','','user_id=".$params['user_id']."&template_id=".$params['template_id']."&action=".$this->GetFormAction('toggle_commpleted_view')."',function(){".$js2."});return false;";
				echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Show Completed Items</span></a>");
			}
		}

		echo("</div>");
	}

	public function __X__AgentTools($params)
	{
	 	$user=new user($params['user_id']);

		if($params['action']=='archive_transaction')
		{
			$user->Delete();	
			Javascript::Begin();
			echo("document.location='/agents/past.php';");
			Javascript::End();
		}
		if($params['action']=='under_contract')
		{
			$user->Set('user_under_contract',time());
			$user->Update();
		}
		if($params['action']=='send_reminder')
		{
			$this->SendReminders(array('user_ids'=>array($user->id)));
		}
		if($params['action']=='inspection')
		{
			
		}
		if($params['action']=='termite')
		{
			
		}
		if($params['action']=='toggle_condensed_view')
		{
			Session::Set($user->GetFieldName('condensed_view'),!Session::Get($user->GetFieldName('condensed_view')));			
		}
	 

	 
		echo("<h2 class='agent_color1 agent_border_color1'>Agent Tools</h2>");

		$js="ObjectFunctionAjaxPopup('Save As New Template','".get_class($this)."','".$this->id."','AgentToolsNewTemplateConfirmation','NULL','','user_id=".$params['user_id']."',function(){});return false;";
		echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."\"><i class='icon far fa-save'></i><span class='text'>Save this as a New Template</span></a>");

		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=archive_transaction',function(){});return false;";
		echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."\"><i class='icon fas fa-archive'></i><span class='text'>Archive this Transaction</span></a>");

		if(!$user->Get('user_under_contract'))
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=under_contract',function(){});return false;";
			echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."\"><i class='icon far fa-handshake'></i><span class='text'></span>Property Now Under Contract</a>");
		}
		if($user->Get('user_under_contract'))
		{
			echo("<a class='button agent_bg_color1' href='".$this->GetUserURL($user,'edit_user_dates.php')."'><i class='icon far fa-list-alt'></i><span class='text'>Edit Contract Terms</span></a>");

			echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."\"><i class='icon fas fa-times-circle'></i><span class='text'>Property Fell Out of Escrow</span></a>");
		}

		echo("<a class='button agent_bg_color1' href='".$this->DirectURL("?action=EditUser&user_id=".$user->id)."'><i class='icon fas fa-user'></i><span class='text'>Edit Client Info</span></a>");

		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=send_reminder',function(){});return false;";
		echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."\"><i class='icon far fa-envelope'></i><span class='text'>Send Client Reminder</span></a>");

		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=inspection',function(){});return false;";
		echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."\"><i class='icon fas fa-search'></i><span class='text'>Click here if/when you want to book the inspection</span></a>");

		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=termite',function(){});return false;";
		echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."\"><i class='icon fas fa-bug'></i><span class='text'>Click here if/when you want to book termite inspection</span></a>");

		echo("<a class='button agent_bg_color1' href='".$this->DirectURL()."'><i class='icon fas fa-sign-in-alt'></i><span class='text'>Return to Dashboard</span></a>");

		echo("<a class='button agent_bg_color1' href='".$this->GetUserURL($user,'activity_log.php')."'><i class='icon fas fa-chart-line'></i><span class='text'>View activity log</span></a>");
		
		if(Session::Get($user->GetFieldName('condensed_view')))
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=toggle_condensed_view',function(){});return false;";
			Javascript::Begin();
			echo("jQuery('BODY').addClass('timeline_condensed');");
			Javascript::End();
			echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."\"><i class='icon fas fa-expand-alt'></i><span class='text'>Turn Off Condensed View</span></a>");
		}
		else
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=toggle_condensed_view',function(){});return false;";
			Javascript::Begin();
			echo("jQuery('BODY').removeClass('timeline_condensed');");
			Javascript::End();
			echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."\"><i class='icon fas fa-compress-alt'></i><span class='text'>Turn On Condensed View</span></a>");
		}
		
	}

	public function EditHeadline($params=array())
	{
	 	$user=new user($params['user_id']);
	 	$user->SetFlag('ALLOW_BLANK');
	 	$user->ProcessAction();

		$savejs="UpdateWYSIWYG();";
		$savejs.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditHeadline','','".$this->GetFieldName('headline')."','','user_id=".$user->id."&action=".$user->GetFormAction('save')."',function(){});";
		$savejs2="UpdateWYSIWYG();";
		$savejs2.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditHeadline','headline_container','".$this->GetFieldName('headline')."','','user_id=".$user->id."&action=".$user->GetFormAction('save')."',function(){});";

		form::Begin('','POST',true,array('id'=>$this->GetFieldName('headline')));
		echo("<div class='client_intro'>");
		echo("<div class='client_intro_headline'>");
		form::DrawTextInput($user->GetFieldName('user_headline'),$user->Get('user_headline'),array('class'=>'text H2','placeholder'=>'Headline','onchange'=>$savejs));
		echo("</div>");
		echo("</div>");	
		form::End();
	}	

	public function EditTemplateIntro($params=array())
	{
	 	$template=new template($params['template_id']);
	 	$template->SetFlag('ALLOW_BLANK');
	 	$template->ProcessAction();

		$savejs="UpdateWYSIWYG();";
		$savejs.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTemplateIntro','','".$this->GetFieldName('intro')."','','template_id=".$template->id."&action=".$template->GetFormAction('save')."',function(){});";
		$savejs2="UpdateWYSIWYG();";
		$savejs2.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTemplateIntro','intro_container','".$this->GetFieldName('intro')."','','template_id=".$template->id."&action=".$template->GetFormAction('save')."',function(){});";

		form::Begin('','POST',true,array('id'=>$this->GetFieldName('intro')));
		echo("<div class='client_intro'>");
		echo("<div class='intro_agent_edit'>");
		//$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTimeline','timeline_container','NULL','','template_id=".$template->id."&agent_id=".$this->id."&agent=1');";
		//$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTemplateIntro','intro_container','".$this->GetFieldName('reset')."','','template_id=".$template->id."&agent_id=".$this->id."&agent=1&action=".$template->GetFormAction('reset')."',function(){".$js2."});";
		//echo("<a href='#' onclick=\"if(confirm('Retore template To Default?')){".$js."}return false;\" data-tggle='tooltip' title='Reset to Default'><i class='fa fa-sync'></i></a>");
		echo("</div>");
		form::DrawTextInput($template->GetFieldName('template_headline'),$template->Get('template_headline'),array('class'=>'text H2','placeholder'=>'Headline','onchange'=>$savejs,'data-info'=>'TIMELINE_HEADLINE'));
		echo("<div class='client_intro_image>");
		echo("<img src='/images/placeholder.png'>");
		echo("</div>");
		echo("<div class='client_intro_address'>");
		form::DrawTextInput($template->GetFieldName('template_address'),$template->Get('template_address'),array('placeholder'=>'Address','onchange'=>$savejs,'data-info'=>'TIMELINE_ADDRESS'));
		echo("</div>");
		echo("<div class='client_intro_text' data-info='TIMELINE_INTRO' data-info-none='none'>");
		form::DrawTextArea($template->GetFieldName('template_content'),$template->Get('template_content'),array('class'=>'wysiwyg_input wysiwyg_intro','onchange'=>$savejs));
		$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
		$wysiwyg_info.="onchange_callback:function(){".$savejs."},\r\n";
		wysiwyg::RegisterMode($template->GetFieldName('SIMPLE_LINK_HEADLINES'),$wysiwyg_info);
		form::MakeWYSIWYG($template->GetFieldName('template_content'),$template->GetFieldName('SIMPLE_LINK_HEADLINES'));
		echo("</div>");
		echo("</div>");	
		form::End();
	}
	
	public function EditSidebar($params=array())
	{
	 	$user=new user($params['user_id']);
	 
		echo("<div class='sidebar'>");
		echo("<h2 class='agent_color1 agent_border_color1'>Helpful Links</h2>");
	 	echo("<div id='contact_info_container'>");
		$this->ListContactInfo($params);
		echo("</div>");
		$user->DisplayKeyDates();
		$this->AddKeyDatesButton($params);
	 	echo("<div id='widgets_container'>");
		$this->EditWidgets($params);
		echo("</div>");
	 	echo("<div id='progress_meter_container_outer'>");
	 	echo("<div id='progress_meter_container'>");
		$this->ProgressMeter($params);
		echo("</div>");
		echo("</div>");
		echo("</div>");
	}

	public function AddKeyDatesButton($params=array())
	{
	 	$user=new user($params['user_id']);
	
		if($user->Get('user_under_contract'))
		{		
			$jspopup="ObjectFunctionAjaxPopup('Add Dates to Calendar','".get_class($this)."','".$this->id."','AddToCalendarInfo','NULL','','user_id=".$user->id."&agent=1');";
			$jspopup.="return false;";
			
			//if($user->Get('user_sidebar_calendar_off'))
			//	echo("<a class='button button_disabled' href='#' onclick=\"".$jspopup."\"><i class='icon fas fa-calendar'></i><i class='icon icon2 fa fa-trash-restore' onclick=\"".$js."\"></i><span class='text'>Add To Calendar</span></a>");
			//else
				echo("<a class='button agent_bg_color1 agent_bg_color2_hover' href='#' onclick=\"".$jspopup."\"><i class='icon fas fa-calendar'></i><span class='text'>Add To Calendar</span></a>");
		}
	}

	public function AddToCalendarInfo($params=array())
	{
		global $HTTP_POST_VARS;
		
	 	$user=new user($params['user_id']);

		$type=$HTTP_POST_VARS['type'];
		//if(!$type)
		//	$type='KEY_DATES';

		if($params['action']==$this->GetFormAction('calendar'))
		{
			$ical_url=$this->DirectURL("/ical/".md5(get_class($this).$this->id)."/".md5('user'.$user->id).'.ics?time='.time().'&type='.$type);

			echo("<div class='line'>");
			echo("<div>".html::ProcessTemplateFile(file::GetPath('add_to_calendar_info'),array('<ical_url/>'=>$ical_url))."</div>");
			echo("</div>");
			echo("<div class='line'>");
			echo("<a href='#' class='button agent_bg_color1' onclick=\"PopupClose();\">Close</a>");
			echo("</div>");
		}
		else
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AddToCalendarInfo','popup_content','".$this->GetFieldName('types')."','','action=".$this->GetFormAction('calendar')."&user_id=".$user->id."&agent=1');";
			echo("<div class='line'>What Dates Would You Like to Add to Your Calendar?</div>");
			form::Begin('?action='.$this->GetFormAction('calendar'),'POST',false,array('id'=>$this->GetFieldName('types')));
			echo("<div class='line'>");
			echo("<div class='row'>");
			echo("<div class='col-md-3'>");
			echo("<label>");
			form::DrawRadioButton('type','KEY_DATES',$type=='KEY_DATES',array('onchange'=>$js));
			echo(" Key Dates</label>");
			echo("</div>");
			echo("<div class='col-md-6'>");
			echo("<label>");
			form::DrawRadioButton('type','KEY_DATES_PLUS',$type=='KEY_DATES_PLUS',array('onchange'=>$js));
			echo(" Key Dates + Agent Items</label>");
			echo("</div>");
			echo("<div class='col-md-3'>");
			echo("<label>");
			form::DrawRadioButton('type','ALL',$type=='ALL',array('onchange'=>$js));
			echo(" All Items</label>");
			echo("</div>");
			echo("</div>");
			echo("</div>");
			form::end();
			
		}
	}
	
	public function SendKeyDates($params=array())
	{
		global $HTTP_POST_VARS;

		$user=new user($params['user_id']);
	  	$user_contacts=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"user_id='".$user->id."'");
		$user_contacts->Retrieve()		;

		$agent=new agent($user->Get('agent_id'));

	  	$coordinators=new DBRowSetEX('agents_to_coordinators','coordinator_id','coordinator',"agent_id='".$agent->id."'");
		$coordinators->Retrieve();
		
		$mail_params=array();
		$mail_params+=$user->GetMailParams();
		$mail_params+=$this->GetMailParams();


		$where=array("user_id='".$user->id."'");
		$where[]="user_contract_date_key_date=1";
		$where[]="user_contract_date_na=0";
	  	$user_contract_dates=new DBRowSetEX('user_contract_dates','user_contract_date_id','user_contract_date',implode(' AND ',$where),'user_contract_date_date');
		$user_contract_dates->Retrieve();
		foreach($user_contract_dates->items as $user_contract_date)
		{
			$contract_date=new contract_date($user_contract_date->Get('contract_date_id'));
			$d=new DBDate($user_contract_date->Get('user_contract_date_date'));

			$mail_params['user_contract_dates'][$user_contract_date->id]=array();
			foreach($contract_date->attributes as $k=>$v)
				$mail_params['user_contract_dates'][$user_contract_date->id][$k]=$v;
			foreach($user_contract_dates->attributes as $k=>$v)
				$mail_params['user_contract_dates'][$user_contract_date->id][$k]=$v;
			$mail_params['user_contract_dates'][$user_contract_date->id]['user_contract_date_date']=$d->GetDate('m/d/Y');
		}


		//$debug=get_class($this).' '.$this->id.' '.$this->GetName().' - '.$user->id.' - ';
		$subject=$debug."Congratulations! ".$user->GetPropertyName()." Is Now Under Contract";

		$sms_content=array();
		$sms_content[]=$subject;
		$sms_content[]="Note the following key dates for this contract";
		$sms_content[]="";
		foreach($user_contract_dates->items as $user_contract_date)
		{
			$contract_date=new contract_date($user_contract_date->Get('contract_date_id'));
			$d=new DBDate($user_contract_date->Get('user_contract_date_date'));
			$sms_content[]=$d->GetDate('m/d/Y').': '.$contract_date->Get('contract_date_name');
		}
			
		foreach($user_contacts->items as $user_contact)
		{
			if(in_array($user_contact->id,$HTTP_POST_VARS['email_to']))
			{
				$settings=json_decode($user_contact->Get('user_contact_settings'),true);

				$mail_params['email_for_agent']=false;
				$mail_params['email_for_user']=true;
				$mail_params['user_url']=$user_contact->ToURL();
				$mail_params['user_calendar_url']=$user_contact->ToURL('?action='.$user_contact->GetFormAction('add_to_calendar'));
				$mail_params['user_full_name']=$user->GetFullName();
				$mail_params['user_property_name']=$user->GetPropertyName();
				$mail_params['opt_out_link']=$user_contact->ToURL('optout.php');
				if($settings['notifications']['email'])
					email::templateMail($user_contact->Get('user_contact_email'),email::GetEmail(),$subject,file::GetPath('email_key_dates'),$mail_params+array('base_url'=>_navigation::GetBaseURL()));
				if($settings['notifications']['phone'])
				{
					$content=array();
					$content[]="";
					$content[]="Add These Key Dates to Your Calendar";
					$content[]=$mail_params['user_calendar_url'];
					$content[]="";
					$content[]="Access Your Account";
					$content[]=$mail_params['user_url'];
					$content[]="";
					$content[]="To opt out of text notifications, reply PAUSE to this message.";
					try
					{
						$phone=$user_contact->TwillioFormat($user_contact->Get('user_contact_phone'));
						$client = new \Twilio\Rest\Client(TWILLIO_SID,TWILLIO_KEY);
						$res=$client->messages->create($phone,array('from'=>TWILLIO_NUMBER,'body'=>implode("\r\n",$sms_content)."\r\n".implode("\r\n",$content)));
					}
					catch(Exception $e)
					{
						//$this->LogError($e->getMessage());
					}				
				}

				$user->Set('user_key_dates_sent',1);
			}
		}
		
		$people=array();
		foreach($coordinators->items as $coordinator)
		{
			if(is_array($HTTP_POST_VARS['email_to_coordinators']) and in_array($coordinator->id,$HTTP_POST_VARS['email_to_coordinators']))
				$people[]=$coordinator;
		}
		if(is_array($HTTP_POST_VARS['email_to_agents']) and in_array($agent->id,$HTTP_POST_VARS['email_to_agents']))
			$people[]=$agent;
			
		foreach($people as $object)
		{
			$settings=json_decode($object->GetSettings(),true);

			$mail_params['email_for_agent']=false;
			$mail_params['email_for_user']=true;
			$mail_params['user_url']=$object->ToURL($object->GetUserURL('edit_user.php'));
			$mail_params['user_calendar_url']=$object->ToURL($object->GetUserURL('edit_user.php').'?action='.$object->GetFormAction('add_to_calendar'));
			$mail_params['user_full_name']=$user->GetFullName();
			$mail_params['user_property_name']=$user->GetPropertyName();
			$mail_params['opt_out_link']=$object->ToURL('optout.php');
			if($settings['notifications']['email'])
				email::templateMail($object->GetEmail(),email::GetEmail(),$subject,file::GetPath('email_key_dates'),$mail_params+array('base_url'=>_navigation::GetBaseURL()));
			if($settings['notifications']['phone'])
			{
				$content=array();
				$content[]="";
				$content[]="Add These Key Dates to Your Calendar";
				$content[]=$mail_params['user_calendar_url'];
				$content[]="";
				$content[]="Access Your Account";
				$content[]=$mail_params['user_url'];
				$content[]="";
				$content[]="To opt out of text notifications, reply PAUSE to this message.";
				try
				{
					$phone=$object->TwillioFormat($object->GetPhone());
					$client = new \Twilio\Rest\Client(TWILLIO_SID,TWILLIO_KEY);
					$res=$client->messages->create($phone,array('from'=>TWILLIO_NUMBER,'body'=>implode("\r\n",$sms_content)."\r\n".implode("\r\n",$content)));
				}
				catch(Exception $e)
				{
					//$this->LogError($e->getMessage());
				}				
			}

			$user->Set('user_key_dates_sent',1);
		}

		if(in_array('self',$HTTP_POST_VARS['email_to']))
		{
			$settings=json_decode($this->GetSettings(),true);

			$mail_params['email_for_agent']=true;
			$mail_params['email_for_user']=false;
			$mail_params['user_url']=$this->ToURL($this->GetUserURL($user,'edit_user.php',true));
			$mail_params['user_calendar_url']=$this->ToURL($this->GetUserURL($user,'edit_user.php',true).'?action='.$this->GetFormAction('add_to_calendar'));
			$mail_params['opt_out_link']=$this->ToURL('optout.php');
			if($settings['notifications']['email'])
				email::templateMail($this->GetEmail(),email::GetEmail(),$subject,file::GetPath('email_key_dates'),$mail_params+array('base_url'=>_navigation::GetBaseURL()));
			if($settings['notifications']['phone'])
			{
				$content=array();
				$content[]="";
				$content[]="Add These Key Dates to Your Calendar";
				$content[]=$mail_params['user_calendar_url'];
				$content[]="";
				$content[]="Access Your Account";
				$content[]=$mail_params['user_url'];
				$content[]="";
				$content[]="To opt out of text notifications, reply PAUSE to this message.";
				try
				{
					$phone=$this->TwillioFormat($this->GetPhone());
					$client = new \Twilio\Rest\Client(TWILLIO_SID,TWILLIO_KEY);
					$res=$client->messages->create($phone,array('from'=>TWILLIO_NUMBER,'body'=>implode("\r\n",$sms_content)."\r\n".implode("\r\n",$content)));
				}
				catch(Exception $e)
				{
					//$this->LogError($e->getMessage());
				}				
			}
		}
		if(!count($this->errors))
			$user->Update();
		
		activity_log::Log($this,'DATES_SENT','Key Dates Email Sent',$user->id);
	}

	public function SendKeyDatesPopup($params=array())
	{
		global $HTTP_POST_VARS;
		
		$user=new user($params['user_id']);
	  	$user_contacts=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"user_id='".$user->id."'");
		$user_contacts->Retrieve()		;

		$agent=new agent($user->Get('agent_id'));

	  	$coordinators=new DBRowSetEX('agents_to_coordinators','coordinator_id','coordinator',"agent_id='".$agent->id."'");
		$coordinators->Retrieve();

		
		if($params['action']==$this->GetFormAction('send_emails'))
			$this->SendKeyDates($params);

		form::Begin($this->GetFieldName('SendKeyDatesPopupForm'),'POST',true,array('id'=>$this->GetFieldName('SendKeyDatesPopupForm')));		
		echo("<div class='line'>");
		echo("Please review carefully before sending, as local holidays vary and those may adjust your due dates.");
		echo("</div>");
		echo("<h3>List of key dates:</h3>");
		$where=array("user_id='".$user->id."'");
		$where[]="user_contract_date_key_date=1";
		$where[]="user_contract_date_na=0";
	  	$user_contract_dates=new DBRowSetEX('user_contract_dates','user_contract_date_id','user_contract_date',implode(' AND ',$where),'user_contract_date_date');
		$user_contract_dates->Retrieve();
		echo("<ul>");
		foreach($user_contract_dates->items as $user_contract_date)
		{
			$contract_date=new contract_date($user_contract_date->Get('contract_date_id'));
			$d=new DBDate($user_contract_date->Get('user_contract_date_date'));
			echo("<li>".$contract_date->Get('contract_date_name')."</b>: ".$d->GetDate('M j')."</li>");
		}
		echo("</ul>");
		echo("<h3>Send Key Dates To:</h3>");
		foreach($user_contacts->items as $user_contact)
		{
			echo("<div class='line'>");		
			echo "<div class='timeline_item_content'>";
			echo "<label>";
			$boxparams=array();
			if(!$user_contact->HasNotifications())
				$boxparams['disabled']='disabled';
			$checked=!isset($HTTP_POST_VARS['email_to']) or in_array($user_contact->id,$HTTP_POST_VARS['email_to']);
			if(!$user_contact->HasNotifications())
				$checked=false;
			form::DrawCheckbox('email_to[]',$user_contact->id,$checked,$boxparams);
			if($user_contact->HasNotifications())
				echo " Send a Copy to ".$user_contact->Get('user_contact_name');
			else
				echo $user_contact->Get('user_contact_name').' has notifications turned off';
			echo "</label>";
			echo("</div>");
			echo("</div>");			
		}
		
		$people=array();			
		$fieldname='';
		if(get_class($this)=='coordinator')
		{
			$fieldname='email_to_agents';
			$people[]=$agent;
		}
		if(get_class($this)=='agent')
		{
			$fieldname='email_to_coordinators';
			foreach($coordinators->items as $coordinator)
				$people[]=$coordinator;
		}

		foreach($people as $object)
		{
			echo("<div class='line'>");		
			echo "<div class='timeline_item_content'>";
			echo "<label>";
			$boxparams=array();
			if(!$object->HasNotifications())
				$boxparams['disabled']='disabled';
			$checked=!isset($HTTP_POST_VARS[$fieldname]) or in_array($object->id,$HTTP_POST_VARS[$fieldname]);
			if(!$object->HasNotifications())
				$checked=false;
			form::DrawCheckbox($fieldname.'[]',$object->id,$checked,$boxparams);
			if($object->HasNotifications())
				echo " Send a Copy to ".$object->GetName();
			else
				echo $object->GetName().' has notifications turned off';
			echo "</label>";
			echo("</div>");
			echo("</div>");			
		}
		echo("<div class='line'>");		
		echo "<div class='timeline_item_content'>";
		echo "<label>";
		form::DrawCheckbox('email_to[]','self',!isset($HTTP_POST_VARS['email_to']) or in_array('agent',$HTTP_POST_VARS['email_to']));
		echo " Send a copy to me";
		echo "</label>";
		echo("</div>");
		echo("</div>");			
		form::End();	

		if(count($this->errors))
			echo("<div class='errors'>".implode('<br>',$this->errors)."</div>");
		
		$js3="ObjectFunctionAjax('".get_class($this)."','".$this->id."','SendKeyDatesNotice','SendKeyDatesNoticeContainer','NULL','','user_id=".$params['user_id']."',function(){});";					
		$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','SendKeyDatesButton','SendKeyDatesButtonContainer','NULL','','user_id=".$params['user_id']."',function(){".$js3."});";
		$js2.="if(!jQuery('#popup_content .errors').length)PopupClose();";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','SendKeyDatesPopup','popup_content','".$this->GetFieldName('SendKeyDatesPopupForm')."','','agent=1&user_id=".$user->id."&agent=1&action=".$this->GetFormAction('send_emails')."',function(){".$js2."});";
		echo("<div class='line'>");
		echo("<a class='button' href='#' onclick=\"".$js."return false;\">Continue</a>");
		echo("</div>");		
		
	}

	public function WelcomeEmailNotice($params=array())
	{
		$user=new user($params['user_id']);

		$user_contact=new user_contact($params['user_contact_id']);
		$user_contact->ProcessAction();

	  	$user_contacts=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"user_contact_email!='' AND user_id='".$user->id."' AND user_contact_welcome_timestamp=0");
		if(!$user_contacts->GetTotalAvailable())
			return;
			
		//form::DrawButton('','Send Key Dates to Client',array('onclick'=>$js2));

		echo("<div class='terms_reference_note' style='margin-top:30px'>");
		echo("<div>".html::ProcessTemplateFile(file::GetPath('welcome_email_notice'),array())."</div>");

	  	$user_contacts=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"user_id='".$user->id."'");
		$user_contacts->Retrieve();
		foreach($user_contacts->items as $user_contact)
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','WelcomeEmailNotice','WelcomeEmailNoticeContainer','NULL','','action=".$user_contact->GetFormAction('welcome')."&user_id=".$params['user_id']."&user_contact_id=".$user_contact->id."',function(){});";
			//$js="ObjectFunctionAjaxPopup('Send Welcome Email','".get_class($this)."','".$this->id."','SendWelcomeEmailsPopup','NULL','','user_id=".$params['user_id']."&user_contact_id=".$user_contact->id."',function(){});";
			$js.="";
			$confirm="Are you sure you would like to send a welcome email?";
		 	$date=new date();
		 	$date->Settimestamp($user_contact->Get('user_contact_welcome_timestamp'));
			if($user_contact->Get('user_contact_welcome_timestamp')>0)
				$confirm="Are you sure you would like to re-send a welcome email to this contact? A welcome email was already sent on ".$date->GetDate('m/d/Y');
			$js="if(confirm('".$confirm."')){".$js."}";			

			echo("<div class='row'>");
			echo("<div class='col-md-1'><br></div>");
			echo("<div class='col-md-2'>");
			echo($user_contact->Get('user_contact_name'));
			echo("</div>");
			echo("<div class='col-md-6'>");
			if($user_contact->Get('user_contact_welcome_timestamp')<0)
				echo("<a href='#' onclick=\"".$js."return false;\">Welcome email not sent</a>");
			else if($user_contact->Get('user_contact_welcome_timestamp'))
				echo("<a href='#' onclick=\"".$js."return false;\">Welcome mail sent to ".$user_contact->Get('user_contact_welcome_email')." on ".$date->GetDate('m/d/Y')."</a>");
			else
				echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."return false;\">Send Welcome Email</a>");
			echo("</div>");
			echo("<div class='col-md-3'>");
			if(!$user_contact->Get('user_contact_welcome_timestamp'))
			{
				$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','WelcomeEmailNotice','WelcomeEmailNoticeContainer','NULL','','user_id=".$params['user_id']."',function(){});";
				$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','WelcomeEmailNotice','WelcomeEmailNoticeContainer','NULL','','user_id=".$params['user_id']."&user_contact_id=".$user_contact->id."&action=".$user_contact->GetFormAction('dismiss_welcome_notice')."',function(){".$js2."});";					
				form::DrawButton('','Dismiss This Notice',array('onclick'=>$js));
			}
			echo("</div>");
			echo("</div>");
		}
		echo("</div>");

	}


	public function EditUserDates($params=array())
	{
		global $HTTP_POST_VARS;
		
		$user=new user($params['user_id']);

		if(!$user->Get('user_under_contract'))
			_navigation::Redirect($this->GetUserURL($user));


		$where=array(1);
	  	$conditions=new DBRowSetEX('conditions','condition_id','condition',implode(' AND ',$where),'condition_order');
		$conditions->Retrieve();
		foreach($conditions->items as $condition)
		{
		 	$user_condition=new user_condition();
		 	$user_condition->InitByKeys(array('condition_id','user_id'),array($condition->id,$params['user_id']));
		 	if(!$user_condition->id)
		 	{
			 	$user_condition->CreateFromKeys(array('condition_id','user_id'),array($condition->id,$params['user_id']));
				$user_condition->Set('user_condition_checked',$condition->Get('condition_default')); 
			}
			if(isset($HTTP_POST_VARS[$this->GetFieldName('user_conditions')]))
			{
				if(in_array($user_condition->id,$HTTP_POST_VARS[$this->GetFieldName('user_conditions')]))
					$user_condition->Set('user_condition_checked',1); 
				else
					$user_condition->Set('user_condition_checked',0); 
			}
		 	$user_condition->Save();
		}
		
		$where=array(1);
	  	$contract_dates=new DBRowSetEX('contract_dates','contract_date_id','contract_date',implode(' AND ',$where),'contract_date_order');
		$contract_dates->Retrieve();
		foreach($contract_dates->items as $contract_date)
		{
		 	$user_contract_date=new user_contract_date();
			$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($contract_date->id,$params['user_id']));
		 	if(!$user_contract_date->id)
		 	{
				$user_contract_date->CreateFromKeys(array('contract_date_id','user_id'),array($contract_date->id,$params['user_id']));
				$user_contract_date->Set('user_contract_date_key_date',$contract_date->Get('contract_date_key_date')); 
			}
			$user_contract_date->Set('user_contract_date_na',false);

//**THESE N/As might need to daisy chain too...?
//turn off one date, turs off a date that dependsin on it, etc.
//turn off the card too.  but maybe just in the GUI?

		  	$list=new DBRowSetEX('conditions_to_contract_dates','condition_id','condition',"condition_to_contract_date_action='HIDE' AND contract_date_id='".$contract_date->id."'",'condition_order');
		  	$list->join_tables="conditions";
		  	$list->join_where="conditions_to_contract_dates.condition_id=conditions.condition_id";
			$list->Retrieve();
			foreach($list->items as $condition)
			{
			 	$user_condition=new user_condition();
			 	$user_condition->CreateFromKeys(array('condition_id','user_id'),array($condition->id,$params['user_id']));
			 	if($user_condition->Get('user_condition_checked'))
					$user_contract_date->Set('user_contract_date_na',true);
			}
		 	$user_contract_date->Save();
		 	
		 	if($params['action']==$user_contract_date->GetFormAction('custom_date'))
		 	{
				$contract_date=new contract_date($user_contract_date->Get('contract_date_id'));

				$relative_to=new contract_date($contract_date->Get('contract_date_default_days_relative_to_id'));
			 	$user_contract_date2=new user_contract_date();
			 	$user_contract_date2->CreateFromKeys(array('contract_date_id','user_id'),array($relative_to->id,$params['user_id']));
				$relative_date=new DBDate($user_contract_date2->Get('user_contract_date_date'));
				$days=$HTTP_POST_VARS[$user_contract_date->GetFieldName('contract_date_default_days')];

				if($relative_date->IsValid())
				{
					//business days - only count by business days.
					if($contract_date->Get('contract_date_business_days'))
				 	{
						$count_days=0;
						while($count_days<$days)
						{
						 	$relative_date->Add(1);
							if(!holiday::IsHoliday($relative_date) and ($relative_date->GetDate('w')!=0) and ($relative_date->GetDate('w')!=6))
								$count_days++;
						}
					}	
					else
						$relative_date->Add($days);
					$user_contract_date->Set('user_contract_date_moved',0);
					if(!$contract_date->Get('contract_date_holiday'))
					{
						while(holiday::IsHoliday($relative_date) or ($relative_date->GetDate('w')==0) or ($relative_date->GetDate('w')==6))
						{
							$relative_date->Add(1);
							$user_contract_date->Set('user_contract_date_moved',1);
						}
					}
				}
				$user_contract_date->Set('user_contract_date_override',1);
				$user_contract_date->Set('user_contract_date_date',$relative_date->GetDBDate());				
				$user_contract_date->Update();
			}
		 	if($params['action']==$user_contract_date->GetFormAction('no_custom_date'))
		 	{
				$user_contract_date->Set('user_contract_date_moved',0);
				$user_contract_date->Set('user_contract_date_override',0);
				$user_contract_date->Update();
				$user_contract_date->CalculateDate();
			}
		 	if($params['action']==$user_contract_date->GetFormAction('yes_custom_date'))
		 	{
				$user_contract_date->Set('user_contract_date_moved',0);
				$user_contract_date->Set('user_contract_date_override',1);
				$user_contract_date->Update();
				$user_contract_date->CalculateDate();
			}
		}
		
		if($params['recalculate_dates_id'])
		{
			$user->UpdateDates($params['recalculate_dates_id']);
			
			$contract_date=new contract_date($params['recalculate_dates_id']);
			if($contract_date->Get('contract_date_special')=='ACCEPTANCE')
			{
				$user->Set('user_key_dates_sent',0);
				$user->Update();
			}
		}		
		$user->CalculateFullContingencyRemovalDate();
		

		$referencejs="ObjectFunctionAjaxPopup('RPA Section 3','".get_class($this)."','".$this->id."','ContractReferencePopup','NULL','','',function(){},'popup_medium');return false;";
		$opts=array('Start & End Dates'=>array('where'=>'contract_date_primary=1'),'All Other Dates'=>array('where'=>'contract_date_primary=0','info'=>"<div class='terms_reference_note'>The following information can be found on Pages 1 & 2 of the Residential Purchase Agreement, Section 3.<!-- Not sure where? <a href='#' onclick=\"".$referencejs."\">Check this reference image</a>--></div>"));

		//set up our types of dates.  don't include the others unless primaries have been filled in.
		$primary_dates_entered=true;
	  	$contract_dates=new DBRowSetEX('contract_dates','contract_date_id','contract_date','contract_date_primary=1','contract_date_order');
		$contract_dates->Retrieve();
		foreach($contract_dates->items as $contract_date)
		{
		 	$user_contract_date=new user_contract_date();
		 	$user_contract_date->CreateFromKeys(array('contract_date_id','user_id'),array($contract_date->id,$params['user_id']));
			$d=new DBDate($user_contract_date->Get('user_contract_date_date'));
			if(!$d->IsValid())			
				$primary_dates_entered=false;
		}
		
		if(!$user->Get('user_under_contract'))
			return;
		if(!$primary_dates_entered)
			unset($opts['All Other Dates']);
		
		echo("<div class='agent_tools' style='border:none'>");
		$js="ObjectFunctionAjaxPopup('Property Fell Out of Escrow','".get_class($this)."','".$this->id."','OutOfEscrowConfirmation','NULL','','user_id=".$user->id."',function(){});return false;";
//		echo("<a class='under_contract ".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='Click if property falls out of contract.' href='#' onclick=\"".$js."\"><i class='icon fas fa-handshake'></i><span class='text'><i class='under_contract fa fa-circle-check'></i><i class='not_under_contract fa fa-circle-xmark'></i>Under Contract</span></a>");
		echo("<a class='button' href='#' onclick=\"".$js."\">Click if property falls out of contract</a>");
		echo("</div>");
	
		$section=0;
		
		foreach($opts as $title=>$data)
		{
			$where=$data['where'];
			$info=$data['info'];

		  	$contract_dates=new DBRowSetEX('contract_dates','contract_date_id','contract_date',$where,'contract_date_order');
			$contract_dates->Retrieve();

			echo("<div class='agent_dashboard'>");
			echo("<h1 class='agent_color1'>".$title."</h1>");
			if($info)
				echo("<div class='agent_dashboard_info'>".$info."</div>");

			
			//start full screen - repeats for each type of date
			echo("<div class='hidden-sm hidden-xs'>");		
			form::Begin('','POST',true,array('id'=>$this->GetFieldName('dates'.$section)));
			$js1="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates'.$section)."','','user_id=".$params['user_id']."&agent=1',function(){".$js2."});";
			echo("<table class='listing'>");
			
			$keydate_tooltip='Key transaction due dates.  By default, these are Start of Escrow, Close of Escrow, and Contingency Removals, although you can select whatever dates you like.  These will be displayed on the timeline page under "Helpful Links" so you and your client can easily reference these dates.';
			echo("<tr class='agent_bg_color1'><th>RPA Item #</th><th>Description</th><th># of Days</th><th>Date</th><th>Additional Terms</th><th data-toggle='tooltip' title='".$keydate_tooltip."'>Key Date? <i class='fa fa-solid fa-circle-info'></i></th></tr>");	
			foreach($contract_dates->items as $contract_date)
			{
			 	$user_contract_date=new user_contract_date();
			 	$user_contract_date->CreateFromKeys(array('contract_date_id','user_id'),array($contract_date->id,$params['user_id']));
				$d=new DBDate($user_contract_date->Get('user_contract_date_date'));
	
				$class='';
				if($user_contract_date->Get('user_contract_date_na'))
					$class='user_contract_date_na';
	
				echo("<tr class='list_item ".$class."'>");
				echo("<td>".$contract_date->Get('contract_date_rpa_item')."</td>");
				echo("<td>".$contract_date->Get('contract_date_name')."</td>");
				if(($contract_date->Get('contract_date_special')=='FULL_CONTINGENCY_REMOVAL'))
				{
				 	//logic is duplicate below.... 1/2, 1/2 + mobile make this a function
					echo("<td>");
					if($user_contract_date->Get('user_contract_date_override'))
						$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates'.$section)."','','user_id=".$params['user_id']."&agent=1&action=".$user_contract_date->GetFormAction('no_custom_date')."&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
					else
						$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates'.$section)."','','user_id=".$params['user_id']."&agent=1&action=".$user_contract_date->GetFormAction('yes_custom_date')."&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
					echo("<div><label>");				
					form::DrawCheckbox($user_contract_date->GetFieldName('user_contract_date_override'),1,$user_contract_date->Get('user_contract_date_override'),array('onchange'=>$js,'class'=>'x'));
					echo(' Agent Specified');
					echo("</label></div>");
					echo("</td>");
				 	//logic is duplicate below.... 1/2, 1/2 + mobile make this a function
					echo("<td>");
					if(!$user_contract_date->Get('user_contract_date_override'))
						echo($d->IsValid()?$d->GetDate('m/d/Y'):'');
					else
					{
						$js2='';
						$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates'.$section)."','','user_id=".$params['user_id']."&agent=1&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
						$js0="jQuery('#".$user_contract_date->GetFieldName('user_contract_date_override')."').val(1);";
						form::DrawTextInput($user_contract_date->GetFieldName('user_contract_date_date'),($d->IsValid()?$d->GetDate('m/d/Y'):''),array('placeholder'=>'Enter Date','onchange'=>$js0.$js,'class'=>'text datepicker center'));			
					}
					if($user_contract_date->Get('user_contract_date_moved'))
						echo("<div class='user_contract_date_note'>Moved From Weekend Or Holiday</div>");
					if(!$contract_date->Get('contract_date_holiday') and (holiday::IsHoliday($d) or ($d->GetDate('w')==0) or ($d->GetDate('w')==6)))
						echo("<div class='user_contract_date_note'>Falls on Weekend Or Holiday</div>");

					echo("</td>");
				}
				else
				{
				 	//logic is duplicate below.... 1/2, 1/2 + mobile make this a function
					echo("<td style='white-space:nowrap;'>");
					if($contract_date->Get('contract_date_default_days_relative_to_id'))
					{
						$relative_to=new contract_date($contract_date->Get('contract_date_default_days_relative_to_id'));
					 	$user_contract_date2=new user_contract_date();
					 	$user_contract_date2->CreateFromKeys(array('contract_date_id','user_id'),array($relative_to->id,$params['user_id']));
						$relative_date=new DBDate($user_contract_date2->Get('user_contract_date_date'));
						$days=$contract_date->Get('contract_date_default_days');
						if($user_contract_date->Get('user_contract_date_override') and $relative_date->IsValid())
							$days=$user_contract_date->CountDays($relative_date);
						$days=round($days);
					
						$days_type=$user_contract_date->GetDaysType();
						
						$opts=array();
						$opts['']='-0.01';
						for($i=-10;$i<0;$i++)
							$opts[(0-$i).' '.$days_type.' Prior To']=$i;
						for($i=0;$i<=365;$i++)
							$opts[$i.' '.$days_type.' After']=$i;
							
						$js2='';
						$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates'.$section)."','','user_id=".$params['user_id']."&agent=1&action=".$user_contract_date->GetFormAction('custom_date')."&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
						$js0="jQuery('#".$user_contract_date->GetFieldName('user_contract_date_override')."').val(1);";
						form::DrawSelect($user_contract_date->GetFieldName('contract_date_default_days'),$opts,$days,array('style'=>'width:auto','onchange'=>$js));
						echo($relative_to->Get('contract_date_name'));
						
						if($user_contract_date->Get('user_contract_date_override') and $contract_date->Get('contract_date_default_days_relative_to_id'))
						{
							$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates'.$section)."','','user_id=".$params['user_id']."&agent=1&action=".$user_contract_date->GetFormAction('no_custom_date')."&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
							echo("<div><label>");				
							form::DrawCheckbox($user_contract_date->GetFieldName('user_contract_date_override'),1,$user_contract_date->Get('user_contract_date_override'),array('onchange'=>$js,'class'=>'x'));
							echo(' Agent Specified');
							echo("</label></div>");
						}	
						else
							form::DrawHiddenInput($user_contract_date->GetFieldName('user_contract_date_override'),0);
					}
					echo("</td>");
				 	//logic is duplicate below.... 1/2, 1/2 + mobile make this a function
					echo("<td>");
					$js2='';
					$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates'.$section)."','','user_id=".$params['user_id']."&agent=1&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
					$js0="jQuery('#".$user_contract_date->GetFieldName('user_contract_date_override')."').val(1);";
					form::DrawTextInput($user_contract_date->GetFieldName('user_contract_date_date'),($d->IsValid()?$d->GetDate('m/d/Y'):''),array('placeholder'=>'Enter Date','onchange'=>$js0.$js,'class'=>'text datepicker center'));			
					if($user_contract_date->Get('user_contract_date_moved'))
						echo("<div class='user_contract_date_note'>Moved From Weekend Or Holiday</div>");
					if(!$contract_date->Get('contract_date_holiday') and (holiday::IsHoliday($d) or ($d->GetDate('w')==0) or ($d->GetDate('w')==6)))
						echo("<div class='user_contract_date_note'>Falls on Weekend Or Holiday</div>");
					echo("</td>");
				}
				echo("<td class='actions'>");
	
			  	$list=new DBRowSetEX('conditions_to_contract_dates','condition_id','condition','contract_date_id='.$contract_date->id,'condition_order');
			  	$list->join_tables="conditions";
			  	$list->join_where="conditions_to_contract_dates.condition_id=conditions.condition_id";
				$list->Retrieve();
				foreach($list->items as $condition)
				{
				 	$user_condition=new user_condition();
				 	$user_condition->CreateFromKeys(array('condition_id','user_id'),array($condition->id,$params['user_id']));
					$js2='';
					$js="jQuery('.".$user_condition->GetFieldName('checkbox')."').prop('checked',jQuery(this).prop('checked'));";
					$js.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates'.$section)."','','user_id=".$params['user_id']."&agent=1&recalculate_dates_id=".$condition->Get('contract_date_id')."',function(){".$js2."});";	
					echo("<div class='line'>");
					echo("<label>");
//					form::DrawCheckbox($user_condition->GetFieldName('user_condition_checked'),1,$user_condition->Get('user_condition_checked'),array('onchange'=>$js,'class'=>$user_condition->GetFieldName('checkbox')));			
					if(count($list->items)==1)
						form::DrawCheckbox($this->GetFieldName('user_conditions').'['.$contract_date->id.']',$user_condition->id,$user_condition->Get('user_condition_checked'),array('onchange'=>$js,'class'=>$user_condition->GetFieldName('checkbox')));			
					else
						form::DrawRadioButton($this->GetFieldName('user_conditions').'['.$contract_date->id.']',$user_condition->id,$user_condition->Get('user_condition_checked'),array('onchange'=>$js,'class'=>$user_condition->GetFieldName('checkbox')));			
					echo(' '.$condition->Get('condition_text'));
					echo("</label>");
					echo("</div>");					
				}				
				echo("</td>");
				echo("<td>");
				form::DrawCheckbox($user_contract_date->GetFieldName('user_contract_date_key_date'),1,$user_contract_date->Get('user_contract_date_key_date'),array('onchange'=>$js1,'class'=>'x'));			
				echo("</td>");
			}		
			echo("</tr>");
			echo("</table>");
			form::End();
			echo("</div>");
			
			//start mobile - repeats for each type of date
			echo("<div class='visible-sm visible-xs agent_dashboard_mobile'>");		
			form::Begin('','POST',true,array('id'=>$this->GetFieldName('dates_mobile'.$section)));
			$js1="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates_mobile'.$section)."','','user_id=".$params['user_id']."&agent=1',function(){".$js2."});";
			foreach($contract_dates->items as $contract_date)
			{
			 	$user_contract_date=new user_contract_date();
			 	$user_contract_date->CreateFromKeys(array('contract_date_id','user_id'),array($contract_date->id,$params['user_id']));
				$d=new DBDate($user_contract_date->Get('user_contract_date_date'));


				$class='';
				if($user_contract_date->Get('user_contract_date_na'))
					$class='user_contract_date_na';
	
				echo("<table class='listing'>");
				echo("<tr class='agent_bg_color1'><th>Contract Date</th><th><br></th></tr>");
				if($contract_date->Get('contract_date_rpa_item'))
				{
					echo("<tr class='list_item".$class."'>");
					echo("<td>RPA Item #	</td><td>");
					echo($contract_date->Get('contract_date_rpa_item'));
					echo("</td>");
					echo("</tr>");
				}
				if($contract_date->Get('contract_date_name'))
				{
					echo("<tr class='list_item".$class."'>");
					echo("<td>Description</td><td>");
					echo($contract_date->Get('contract_date_name'));
					echo("</td>");
					echo("</tr>");
				}
				if(($contract_date->Get('contract_date_special')!='ACCEPTANCE'))
				{
					echo("<tr class='list_item".$class."'>");
					echo("<td># of Days</td>");
					if(($contract_date->Get('contract_date_special')=='FULL_CONTINGENCY_REMOVAL'))
					{
						echo("<td>");
						if($user_contract_date->Get('user_contract_date_override'))
							$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates_mobile'.$section)."','','user_id=".$params['user_id']."&agent=1&action=".$user_contract_date->GetFormAction('no_custom_date')."&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
						else
							$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates_mobile'.$section)."','','user_id=".$params['user_id']."&agent=1&action=".$user_contract_date->GetFormAction('yes_custom_date')."&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
						echo("<div><label>");				
						form::DrawCheckbox($user_contract_date->GetFieldName('user_contract_date_override'),1,$user_contract_date->Get('user_contract_date_override'),array('onchange'=>$js,'class'=>'x'));
						echo(' Agent Specified');
						echo("</label></div>");
						echo("</td>");
					}
					else
					{
						echo("<td style='white-space:nowrap;' class='actions'>");
						if($contract_date->Get('contract_date_default_days_relative_to_id'))
						{
							$relative_to=new contract_date($contract_date->Get('contract_date_default_days_relative_to_id'));
						 	$user_contract_date2=new user_contract_date();
						 	$user_contract_date2->CreateFromKeys(array('contract_date_id','user_id'),array($relative_to->id,$params['user_id']));
	
							$relative_date=new DBDate($user_contract_date2->Get('user_contract_date_date'));
							$days=$contract_date->Get('contract_date_default_days');
							if($user_contract_date->Get('user_contract_date_override') and $relative_date->IsValid())
								$days=$user_contract_date->CountDays($relative_date);
							$days=round($days);	

							$days_type=$user_contract_date->GetDaysType();
						
							$opts=array();
							$opts['']='-0.01';
							for($i=-10;$i<0;$i++)
								$opts[(0-$i).' '.$days_type.' Prior To']=$i;
							for($i=0;$i<=365;$i++)
								$opts[$i.' '.$days_type.' After']=$i;
							$js2='';
							$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates_mobile'.$section)."','','user_id=".$params['user_id']."&agent=1&action=".$user_contract_date->GetFormAction('custom_date')."&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
							$js0="jQuery('#".$user_contract_date->GetFieldName('user_contract_date_override')."').val(1);";
							form::DrawSelect($user_contract_date->GetFieldName('contract_date_default_days'),$opts,$days,array('style'=>'width:auto','onchange'=>$js));
							echo($relative_to->Get('contract_date_name'));
							
							if($user_contract_date->Get('user_contract_date_override') and $contract_date->Get('contract_date_default_days_relative_to_id'))
							{
								$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates_mobile'.$section)."','','user_id=".$params['user_id']."&agent=1&action=".$user_contract_date->GetFormAction('no_custom_date')."&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
								echo("<div><label>");				
								form::DrawCheckbox($user_contract_date->GetFieldName('user_contract_date_override'),1,$user_contract_date->Get('user_contract_date_override'),array('onchange'=>$js,'class'=>'x'));
								echo(' Agent Specified');
								echo("</label></div>");
							}	
							else
								form::DrawHiddenInput($user_contract_date->GetFieldName('user_contract_date_override'),0);
						}
						echo("</td>");
					}
					echo("</tr>");
				}
				echo("<tr class='list_item'>");
				echo("<td>Date</td>");
				$d=new DBDate($user_contract_date->Get('user_contract_date_date'));
				if(($contract_date->Get('contract_date_special')=='FULL_CONTINGENCY_REMOVAL'))
				{
					echo("<td>");
					if(!$user_contract_date->Get('user_contract_date_override'))
						echo($d->IsValid()?$d->GetDate('m/d/Y'):'');
					else
					{
						$js2='';
						$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates_mobile'.$section)."','','user_id=".$params['user_id']."&agent=1&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
						$js0="jQuery('#".$user_contract_date->GetFieldName('user_contract_date_override')."').val(1);";
						form::DrawTextInput($user_contract_date->GetFieldName('user_contract_date_date'),($d->IsValid()?$d->GetDate('m/d/Y'):''),array('placeholder'=>'Enter Date','onchange'=>$js0.$js,'class'=>'text datepicker center'));			
					}
					echo("</td>");
				}
				else
				{
					echo("<td>");
					$js2='';
					$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates_mobile'.$section)."','','user_id=".$params['user_id']."&agent=1&recalculate_dates_id=".$contract_date->id."',function(){".$js2."});";
					$js0="jQuery('#".$user_contract_date->GetFieldName('user_contract_date_override')."').val(1);";
					form::DrawTextInput($user_contract_date->GetFieldName('user_contract_date_date'),($d->IsValid()?$d->GetDate('m/d/Y'):''),array('placeholder'=>'Enter Date','onchange'=>$js0.$js,'class'=>'text datepicker center'));			
					echo("</td>");
				}
				echo("</tr>");
			  	$list=new DBRowSetEX('conditions_to_contract_dates','condition_id','condition','contract_date_id='.$contract_date->id,'condition_order');
			  	$list->join_tables="conditions";
			  	$list->join_where="conditions_to_contract_dates.condition_id=conditions.condition_id";
				$list->Retrieve();
				if(count($list->items))
				{
					echo("<tr class='list_item'>");
					echo("<td>Additional Terms</td><td>");
					foreach($list->items as $condition)
					{
					 	$user_condition=new user_condition();
					 	$user_condition->CreateFromKeys(array('condition_id','user_id'),array($condition->id,$params['user_id']));
						$js2='';
						$js="jQuery('.".$user_condition->GetFieldName('checkbox')."').prop('checked',jQuery(this).prop('checked'));";
						$js.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates_mobile'.$section)."','','user_id=".$params['user_id']."&agent=1&recalculate_dates_id=".$condition->Get('contract_date_id')."',function(){".$js2."});";	
						echo("<div class='line'>");
						echo("<label>");
		//					form::DrawCheckbox($user_condition->GetFieldName('user_condition_checked'),1,$user_condition->Get('user_condition_checked'),array('onchange'=>$js,'class'=>$user_condition->GetFieldName('checkbox')));			
						if(count($list->items)==1)
							form::DrawCheckbox($this->GetFieldName('user_conditions').'['.$contract_date->id.']',$user_condition->id,$user_condition->Get('user_condition_checked'),array('onchange'=>$js,'class'=>$user_condition->GetFieldName('checkbox')));			
						else
							form::DrawRadioButton($this->GetFieldName('user_conditions').'['.$contract_date->id.']',$user_condition->id,$user_condition->Get('user_condition_checked'),array('onchange'=>$js,'class'=>$user_condition->GetFieldName('checkbox')));			
						echo(' '.$condition->Get('condition_text'));
						echo("</label>");
						echo("</div>");					
					}			echo("</td>");
					echo("</tr>");
				}
				echo("<tr class='list_item'>");
				$keydate_tooltip='Key transaction due dates.  By default, these are Start of Escrow, Close of Escrow, and Contingency Removals, although you can select whatever dates you like.  These will be displayed on the timeline page under "Helpful Links" so you and your client can easily reference these dates.';
				echo("<td>Key Date? <i class='fa fa-solid fa-circle-info' data-toggle='tooltip' title='".$keydate_tooltip."'></i></td><td>");
				form::DrawCheckbox($user_contract_date->GetFieldName('user_contract_date_key_date'),1,$user_contract_date->Get('user_contract_date_key_date'),array('onchange'=>$js1,'class'=>'x'));			
				echo("</td>");
				echo("</tr>");
	
				echo("<tr class='list_item_empty'>");
				echo("<td colspan='*'><br></td>");
				echo("</tr>");
	
				echo("</table>");
				
			}
			form::End();
			echo("</div>");			
			
			$section++;
		}
		echo("</div>");

		$used=array(-1);
		$rs=database::query("SELECT * FROM conditions_to_contract_dates");
		while($rec=database::fetch_array($rs))
			$used[]=$rec['condition_id'];

	  	$conditions=new DBRowSetEX('conditions','condition_id','condition',"condition_id NOT IN (".implode(',',$used).")",'condition_order');
		$conditions->Retrieve();

		if(count($conditions->items) and isset($opts['All Other Dates']))//should just make UC and check if valid...
		{
			echo("<div class='agent_dashboard'>");
			echo("<h1 class='agent_color1'>Other Conditions</h1>");
			echo("<table class='listing'>");
			echo("<tr class='agent_bg_color1'><th colspan='2'>Condition</th></tr>");
			foreach($conditions->items as $condition)
			{
				echo("<tr class='list_item'>");
				echo("<td style='text-align:left'>".$condition->Get('condition_name')."</td>");
				echo("<td style='text-align:left'>");
			 	$user_condition=new user_condition();
			 	$user_condition->CreateFromKeys(array('condition_id','user_id'),array($condition->id,$params['user_id']));
				$js2='';
				$js="jQuery('.".$user_condition->GetFieldName('checkbox')."').prop('checked',jQuery(this).prop('checked'));";
				$js.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','".$this->GetFieldName('dates_mobile'.$section)."','','user_id=".$params['user_id']."&agent=1&recalculate_dates_id=".$condition->Get('contract_date_id')."',function(){".$js2."});";	
				echo("<div class='line'>");
				echo("<label>");
				form::DrawCheckbox($user_condition->GetFieldName('user_condition_checked'),1,$user_condition->Get('user_condition_checked'),array('onchange'=>$js,'class'=>$user_condition->GetFieldName('checkbox')));			
				echo(' '.$condition->Get('condition_text'));
				echo("</label>");
				echo("</div>");					
				echo("</td>");
				echo("</tr>");
			}				
			echo("</tr>");
			echo("</table>");
			echo("</div>");
		}

		echo("<div class='row'>");
		echo("<div class='col-sm-4'>");
		echo("<div style='text-align:center'>");
		form::DrawButton('','View Timeline',array('onclick'=>"document.location='".$this->GetUserURL($user,'edit_user.php')."';"));
		echo("</div>");
		echo("</div>");
		echo("<div class='col-sm-4'>");
		if($primary_dates_entered)
		{
			echo("<div style='text-align:center'>");
			$js="ObjectFunctionAjaxPopup('Add To Calendar','".get_class($this)."','".$this->id."','AddToCalendarInfo','NULL','','user_id=".$user->id."&agent=1');";
			form::DrawButton('','Add These Key Dates to Your Calendar',array('onclick'=>$js,'class'=>'button agent_bg_color1'));
			echo("</div>");
		}
		echo("</div>");
		echo("<div class='col-sm-4'>");
		echo("<div style='text-align:center'>");
		form::DrawButton('','Back To Dashboard',array('onclick'=>"document.location='index.php';"));
		echo("</div>");
		echo("</div>");
		echo("</div>");

		if($primary_dates_entered)
		{
			echo("<div id='SendKeyDatesNoticeContainer'>");
			$this->SendKeyDatesNotice($params);	
			echo("</div>");
		}

		//$this->EditUserDates__OLD($params);

		$acceptance_date=new contract_date();
		$acceptance_date->InitByKeys('contract_date_special','ACCEPTANCE');
	 	$user_acceptance_date=new user_contract_date();
	 	$user_acceptance_date->CreateFromKeys(array('contract_date_id','user_id'),array($acceptance_date->id,$params['user_id']));

		$d=new DBDate($user_acceptance_date->Get('user_contract_date_date'));
		if(!$d->IsValid())
		{
			Javascript::Begin();
			echo("jQuery(function(){
			 		jQuery('#".$user_acceptance_date->GetFieldName('user_contract_date_date')."').datepicker();
			 		jQuery('#".$user_acceptance_date->GetFieldName('user_contract_date_date')."').datepicker('show');		 		
				});");
			Javascript::End();
		}
	}

	public function ContractReferencePopup($params)
	{
		echo("<a href='/uploads/pics/rpa-example-larger.gif' target='_blank'><img src='/uploads/pics/rpa-example-larger.gif'></a>");
	}

	public function SendKeyDatesButton($params=array())
	{
		$user=new user($params['user_id']);
		if($user->Get('user_key_dates_sent'))
			return;

		$js2="ObjectFunctionAjaxPopup('Send Emails','".get_class($this)."','".$this->id."','SendKeyDatesPopup','NULL','','user_id=".$params['user_id']."',function(){});";					
		//form::DrawButton('','Send Key Dates to Client',array('onclick'=>$js2));
		echo "<a href='#' onclick=\"".$js2."return false;\" class='button agent_bg_color1'>Send Key Dates to Client</a>";
	}

	public function SendKeyDatesNotice($params=array())
	{
		$user=new user($params['user_id']);

	  	$user_contacts=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"user_id='".$user->id."'");
		$user_contacts->Retrieve()		;
		
		if($params['action']==$this->GetFormAction('dismiss_keydates_notice'))
		{
			$user->Set('user_key_dates_sent',-1);
			$user->Update();

			activity_log::Log($this,'DATES_DISMISSEDD','Key Dates Notice Dismissed',$user->id);
		}

		if($user->Get('user_key_dates_sent'))
			return;
		if(!$user->Get('user_under_contract'))
			return;

		//form::DrawButton('','Send Key Dates to Client',array('onclick'=>$js2));

		echo("<div class='terms_reference_note' style='margin-top:30px'>");
		echo("<div>".html::ProcessTemplateFile(file::GetPath('key_dates_notice'),array())."</div>");
		echo("<div style='text-align:center'>");
		$js="ObjectFunctionAjaxPopup('Send Emails','".get_class($this)."','".$this->id."','SendKeyDatesPopup','NULL','','user_id=".$params['user_id']."',function(){});";					
		echo "<a href='#' onclick=\"".$js."return false;\" class='button agent_bg_color1'>Send Key Dates to Client</a>";
		echo("&nbsp;");
		$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','SendKeyDatesButton','SendKeyDatesButtonContainer','NULL','','user_id=".$params['user_id']."',function(){});";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','SendKeyDatesNotice','SendKeyDatesNoticeContainer','NULL','','user_id=".$params['user_id']."&action=".$this->GetFormAction('dismiss_keydates_notice')."',function(){".$js2."});";					
		form::DrawButton('','Dismiss This Notice',array('onclick'=>$js));
		echo("</div>");
		echo("</div>");
		echo("</div>");		

	}

	public function EditPropertyInfo($params=array())
	{
	 	$user=new user($params['user_id']);
	 	$user->SetFlag('ALLOW_BLANK');
	 	$user->ProcessAction();

		$savejs="UpdateWYSIWYG();";
		$savejs.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditPropertyInfo','','".$this->GetFieldName('property_info')."','','user_id=".$user->id."&action=".$user->GetFormAction('save')."',function(){});";
		$savejs2="UpdateWYSIWYG();";
		$savejs2.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditPropertyInfo','property_info_container','".$this->GetFieldName('property_info')."','','user_id=".$user->id."&action=".$user->GetFormAction('save')."',function(){});";

		form::Begin('','POST',true,array('id'=>$this->GetFieldName('property_info')));
		echo("<div class='client_intro property_info'>");


		echo("<div class='agent_dashboard'>");
		echo("<h1 class='agent_color1'>Property Information</h1>");
		echo("<table class='listing'>");
		echo("<tr class='agent_bg_color1'><th>Property Image</th><th>Property Location & Links</th></tr>");	
		echo("<tr class='list_item'>");
		echo("<td style='width:20%;'>");
		echo("<div class='client_intro_image drop_target' data-target='".$user->GetFieldName('user_image_file_ul')."'>");
		if($user->Get('user_image_file'))
			echo("<img src='".$user->GetThumb(1170,513)."'>	");
		else if($user->Get('user_type')=='BUYER')
			echo("<img src=' /uploads/pics/buyer-placeholder-image.jpg'>");
		else if($user->Get('user_type')=='SELLER')
			echo("<img src=' /uploads/pics/seller-placeholder.png'>");
		else
			echo("<img src=' /images/placeholder.png'>");
		echo("<br>");
		form::DrawFileInput($user->GetFieldName('user_image_file_ul'),$user->Get('user_image_file'),array('placeholder'=>'Image','onchange'=>$savejs2));		
		echo("</td>");
		echo("<td>");
		echo("<div class='row'>");
		echo("<div class='col-md-3'>Address</div>");
		echo("<div class='col-md-9'>");
		echo("<div class='line'>");
		form::DrawTextInput($user->GetFieldName('user_address'),$user->Get('user_address'),array('placeholder'=>'Address','onchange'=>$savejs));
		echo("</div>");
		echo("</div>");
		echo("</div>");

		echo("<div class='row'>");
		echo("<div class='col-md-3'>MLS Link</div>");
		echo("<div class='col-md-9'>");
		echo("<div class='line'>");
		form::DrawTextInput($user->GetFieldName('user_mls_listing_url'),$user->Get('user_mls_listing_url'),array('onchange'=>$savejs,'placeholder'=>"Link To MLS Listing"));
		echo("</div>");
		echo("</div>");
		echo("</div>");

		echo("<div class='row'>");
		echo("<div class='col-md-3'>Property Website</div>");
		echo("<div class='col-md-9'>");
		echo("<div class='line'>");
		form::DrawTextInput($user->GetFieldName('user_property_url'),$user->Get('user_property_url'),array('onchange'=>$savejs,'placeholder'=>"Link to Property Website (if applicable)"));
		echo("</div>");
		echo("</div>");
		echo("</div>");

		echo("</td>");
		echo("</tr>");
		echo("</table>");
		echo("</div>");	


		echo("</div>");	
		form::End();
	}


	public function OutOfEscrowConfirmation($params=array())
	{
	 	$user=new user($params['user_id']);
		$user->ProcessAction();
	
		echo("<h3 class='agent_color1'>Property Fell Out Of Escrow</h3>");
		$js_last="height_handler();";
		//if we are on the dates page....	
		$js7="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUserDates','user_dates_container','NULL','','user_id=".$user->id."&agent=1',function(){".$js_last."});";
		//if we are on the dashboard
		$js6="ObjectFunctionAjax('".get_class($this)."','".$this->id."','DrawUCToggle','".$user->GetFieldName('UCToggleContainer')."','NULL','','user_id=".$user->id."',function(){".$js7."});jQuery('.content_area').removeClass('loading');";				
		$js5="ObjectFunctionAjax('".get_class($this)."','".$this->id."','DrawUCToggle','".$user->GetFieldName('UCToggleContainerMobile')."','NULL','','user_id=".$user->id."',function(){".$js6."});";
		//if we are on the dashboard page....
		$js4="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditTimeline','timeline_container','NULL','','user_id=".$user->id."&agent_id=".$this->id."&agent=1',function(){".$js5."});return false;";
		$js3="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditSidebar','sidebar_container','NULL','','user_id=".$user->id."&agent_id=".$this->id."&agent=1',function(){".$js4."});return false;";
		$js2="PopupClose();";
		$js2.="jQuery('.content_area').addClass('loading');";
		$js2.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."',function(){".$js3."});return false;";
		//extra loading while stuff goes down...
		$js3.="jQuery('#popup_content').addClass('loading');";
		//taking care of it.
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','OutOfEscrowConfirmation','popup_content','NULL','','user_id=".$params['user_id']."&action=".$user->GetFormAction('toggle_under_contract')."',function(){".$js2."});return false;";
		echo("<div class='line'>".$user->GetFullName()."</div>");	
		echo("<div class='line'>Are you sure? this will clear all contract terms and dates?</div>");	
		echo("<div class='line'>");
		echo("<a href='#' class='button agent_bg_color1' onclick=\"".$js."\">Confirm</a>");
		echo("</div>");
	}
	
	public function DrawUCToggle($params)
	{
		$user=new user($params['user_id']);
		$user->ProcessAction();

		//redraw mobile/desktop
		$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','DrawUCToggle','".$params['secondary_container']."','NULL','','user_id=".$user->id."&primary_container=".$params['secondary_container']."&secondary_container=".$params['primary_container']."',function(){height_handler();});";				
		//OR if we are going UC now, then just go to terms page.
		if(!$user->Get('user_under_contract'))
			$js2="document.location='".$this->GetUserURL($user,'edit_user_dates.php')."';";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','DrawUCToggle','".$params['primary_container']."','NULL','','user_id=".$user->id."&action=".$user->GetFormAction('toggle_under_contract')."&primary_container=".$params['primary_container']."&secondary_container=".$params['secondary_container']."',function(){".$js2."height_handler();});";

		if($user->Get('user_under_contract'))
			$js="ObjectFunctionAjaxPopup('Property Fell Out of Escrow','".get_class($this)."','".$this->id."','OutOfEscrowConfirmation','NULL','','user_id=".$params['user_id']."',function(){});return false;";

		echo("<label>");
		form::DrawRadioButton($user->GetFieldName('user_under_contract'),0,!$user->Get('user_under_contract'),array('onclick'=>$js));
		echo(" No</label>");
		echo("<label>");
		form::DrawRadioButton($user->GetFieldName('user_under_contract'),time(),$user->Get('user_under_contract'),array('onclick'=>$js));
		echo(" Yes</label>");

		if($user->Get('user_under_contract'))
		{
			if($params['COE'])
			{
				$contract_date=new contract_date();
				$contract_date->InitByKeys(array('contract_date_special'),array('CLOSE_ESCROW'));
				$user_contract_date=new user_contract_date();
				$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($contract_date->id,$user->id));
				echo("<div class='coe_date'>");
			 	$d=new DBDate($user_contract_date->Get('user_contract_date_date'));
				echo("COE: <a href='".$this->GetUserURL($user,'edit_user_dates.php')."'>".$d->GetDate('m/d/Y')."</a>");
				echo("</div>");
			}
			else
				echo("<br><a href='".$this->GetUserURL($user,'edit_user_dates.php')."'>Edit Terms</a> ");			
		}
	}

	public function GetNextTasks($user,$add_where,$params=array())
	{
/* 
		//optimized out....
		//keeping just in case next task starts being wrong.

	  	$user_conditions=new DBRowSetEX('user_conditions','user_condition_id','user_condition',"user_id='".$user->id."' AND user_condition_checked=1");
		$user_conditions->Retrieve();

	 	$user_conditions_condition_ids=array(-1);
		foreach($user_conditions->items as $user_condition)
		 	$user_conditions_condition_ids[]=$user_condition->Get('condition_id');

	  	$conditions_to_timeline_items=new DBRowSetEX('conditions_to_timeline_items','timeline_item_id','timeline_item',"condition_id IN(".implode(',',$user_conditions_condition_ids).")");//wasn;'t look at user id.
		$conditions_to_timeline_items->Retrieve();
		
		$timeline_items_na=array(-1);
		foreach($conditions_to_timeline_items->items as $timeline_item)
			$timeline_items_na[]=$timeline_item->id;
*/
		$timeline_items_na=array(-1);
		$rs=database::query("SELECT * FROM user_conditions uc,conditions_to_timeline_items c2ti,timeline_items ti WHERE c2ti.condition_to_timeline_item_action='HIDE' AND uc.condition_id=c2ti.condition_id AND ti.timeline_item_id=c2ti.timeline_item_id AND uc.user_id='".$user->id."' AND ti.user_id='".$user->id."' AND user_condition_checked=1");		
		while($rec=database::fetch_array($rs))
			$timeline_items_na[]=$rec['timeline_item_id'];
		
		$today=new date();
		$where=array("user_id='".$user->id."' AND timeline_item_active=1");
		$where[]=$add_where;
		$where[]="timeline_item_id NOT IN (".implode(',',$timeline_items_na).")";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete=0";
//		$where[]="(timeline_item_date>='".$today->GetDBDate()."' OR timeline_item_reference_date_type='NONE')";
		$order='timeline_item_order';
		if($user->Get('user_under_contract'))
			$order='timeline_item_date,timeline_item_order';
	  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order);
		$timeline_items->Retrieve();

		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		
		//we only want one.
		if(count($timeline_items->items))
			$timeline_items->items=array($timeline_items->items[0]);

		return $timeline_items->items;
	}

	public function DrawNextTask($user,$add_where,$params=array())
	{
		$timeline_items=$this->GetNextTasks($user,$add_where,$params);
		if(count($timeline_items))
		{
			foreach($timeline_items as $timeline_item)
			{
			 	$class='';
			 	$today=new date();			 
			 	$today->Round();
			 	$d=new DBDate($timeline_item->Get('timeline_item_date'));
			 	
				if($timeline_item->Get('timeline_item_reference_date_type')=='NONE')
			 		$class='timeline_none';
				else if(!$user->Get('user_under_contract'))
			 		$class='timeline_none';
			 	else if(!$d->IsValid())
			 		$class='timeline_none';
			 	else if(date::GetDays($today,$d)<0)
			 		$class='timeline_overdue';
			 	else if(date::GetDays($today,$d)<2)
			 		$class='timeline_due';
			 	//else
			 	//	$class='timeline_upcoming';
			 	
				echo("<td class='".$class."'>");
				echo($params['text']);
				echo("<a class='next_task' data-toggle='tooltip' title='View Timeline' href='".$this->GetUserURL($user)."#".$timeline_item->GetFieldName('anchor')."'>".$timeline_item->Get('timeline_item_title')."</a> ");
				if($params['reminder_button'])
				{
					$js="ObjectFunctionAjaxPopup('Send Reminders','".get_class($this)."','".$this->id."','SendRemindersPopup','NULL','','user_ids[]=".$user->id."',function(){height_handler();});";
					if(!$user->HasNotifications())
						$js="alert('The client has all notifications turned off');";
					form::DrawButton('','Send Reminder',array('id'=>'timeline_reminder_button','class'=>'agent_bg_color1','onclick'=>$js));					
				}
				if($params['show_notification_date'])
				{
					if($timeline_item->Get('timeline_item_notified'))
					{
					 	$notified=new Date();
						$notified->SetTimestamp($timeline_item->Get('timeline_item_notified'));
						echo("<div>Sent reminder on ".$notified->GetDate('m/d/Y')."</div>");
					}
				}
				echo("</td>");
			}		
		}		
		else
			echo("<td></td>");
	}

	public function ListVendors($params)
	{
		global $HTTP_POST_VARS;

		if(!$params['fn'])
			$params['fn']='ListVendors';
			
		if($HTTP_POST_VARS)
			Session::Set('show_vendor_type_id',$HTTP_POST_VARS['show_vendor_type_id']);
			

	 	$where=array($this->primary."='".$this->id."'");
		if(Session::Get('show_vendor_type_id'))
			$where[]="vendor_type_id='".Session::Get('show_vendor_type_id')."'";

 	  	$list=new DBRowSetEX('vendors','vendor_id','vendor',implode(' AND ',$where),'vendor_id');
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew($this->primary,$this->id);
	  	$list->SetEachNew('vendor_type_id',Session::Get('show_vendor_type_id'));
		$list->SetFlag('ALLOW_BLANK');
		$list->ProcessAction();
	  	$list->Retrieve();
	  	$list->SetEachNew($this->primary,$this->id);
	  	$list->SetEachNew('vendor_type_id',Session::Get('show_vendor_type_id'));
		$list->ProcessAction();

		echo("<div class='agent_dashboard'>");
		echo("<h1 class='agent_color1'>Vendors</h1>");
	 	echo("<p>Here you can add, edit and delete Vendors and Other Sidebar Items to your account.  They will be saved here so you can easily add one or more to new transactions.</p>");
		form::Begin('','POST',true,array('id'=>$this->GetFieldName('ListVendorsFiltersForm')));
		echo("<div class='row'>");
		echo("<div class='col-md-2 col-sm-3'>View Vendors</div>");
		echo("<div class='col-md-10 col-sm-9'>");
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListVendors','".$this->GetFieldName('ListVendorsContainer')."','".$this->GetFieldName('ListVendorsFiltersForm')."','','action=".$this->GetFormAction('filter_vendors')."',function(){height_handler();});";
		form::DrawSelectFromSQL('show_vendor_type_id',"SELECT * FROM vendor_types WHERE (".$this->GetIncludedItemsSQL().") ORDER BY vendor_type_name","vendor_type_name","vendor_type_id",Session::Get('show_vendor_type_id'),array('onchange'=>$js),array('Show All Vendors'=>''));
		echo("</div>");
		echo("</div>");
		form::End();
		echo("</div>");

		//FULL SIZE
		echo("<div class='hidden-sm hidden-xs'>");		
		form::Begin('','POST',true,array('id'=>'vendors'));
		echo("<div class='agent_dashboard'>");


		echo("<table class='listing'>");
		echo("<tr class='agent_bg_color1'>".$list->Header('Type of Vendor','').$list->Header('Name','vendor_title').$list->Header('Company','vendor_title').$list->Header('Email','vendor_title').$list->Header('Phone','vendor_title').$list->Header('Additional Info','vendor_title').$list->Header('Delete')."</tr>");
		foreach($list->items as $vendor)
		{
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListVendors','X_".$this->GetFieldName('ListVendorsContainer')."','vendors','','action=".$vendor->GetFormAction('save')."',function(){height_handler();});";

			echo("<tr class='list_item'>");
			echo("<td>");
			//form::DrawSelect($vendor->GetFieldName('vendor_type'),array(''=>'')+vendor::GetTypes(),$vendor->Get('vendor_type'),array('onchange'=>$js));
			$opts=array(''=>'');
			$rs=database::query("SELECT * FROM vendor_types WHERE  (".$this->GetIncludedItemsSQL()." OR vendor_type_id='".$vendor->Get('vendor_type_id')."') ORDER BY vendor_type_name");
			while($rec=database::fetch_array($rs))
				$opts[$rec['vendor_type_name']]=$rec['vendor_type_id'];
			$opts['---------']=' ';
			$opts['Create new type']='NEWTYPE';
			$opts['Create new type']='NEWVENDORTYPE';
			$vendorjs="if(jQuery(this).val()=='NEWVENDORTYPE'){ObjectFunctionAjaxPopup('New Vendor Type','".get_class($this)."','".$this->id."','CreateVendorType','vendors_mobile','','vendor_id=".$vendor->id."',function(){});}else{".$js."}";
			form::DrawSelect($vendor->GetFieldName('vendor_type_id'),$opts,$vendor->Get('vendor_type_id'),array('placeholder'=>'','onchange'=>$vendorjs));
			echo("</td>");
			//echo("<td>");
			//form::DrawTextInput($vendor->GetFieldName('vendor_title'),$vendor->Get('vendor_title'),array('class'=>'text vendor_title','onchange'=>$js));
			//echo("</td>");
			echo("<td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_name'),$vendor->Get('vendor_name'),array('placeholder'=>'Name','onchange'=>$js));
			echo("</td>");
			echo("<td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_company'),$vendor->Get('vendor_company'),array('placeholder'=>'Company','onchange'=>$js));
			echo("</td>");
			echo("<td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_email'),$vendor->Get('vendor_email'),array('placeholder'=>'Email','onchange'=>$js));
			echo("</td>");
			echo("<td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_phone'),$vendor->Get('vendor_phone'),array('placeholder'=>'Phone','onchange'=>$js));
			echo("</td>");
			echo("<td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_info'),$vendor->Get('vendor_info'),array('placeholder'=>'Additional Info','onchange'=>$js));
			echo("</td>");
			echo("<td>");
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListVendors','".$this->GetFieldName('ListVendorsContainer')."','".$this->GetFieldName('ListVendorsFiltersForm')."','','action=".$vendor->GetFormAction('delete')."&fn=".$params['fn']."',function(){height_handler();});";
			$js.="";
			$js="if(confirm('Permanently delete this vendor?')){".$js."}";			
			echo("<a data-info='VENDORS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
			echo("</td>");
			echo("</tr>");
		}

		echo("<tr class='footer_actions_mobile'>");
		echo("<td colspan='1000'>");
		$js2="height_handler();";
		$js2.="jQuery('INPUT.vendor_title:visible').focus();";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListVendors','".$this->GetFieldName('ListVendorsContainer')."','NULL','','action=".$list->newitems[0]->GetFormAction('save')."&user_id=".$user->id."&fn=".$params['fn']."',function(){".$js2."});";
		echo("<a href='#' class='button agent_bg_color1' onclick=\"".$js."return false;\">Add Vendor</a>");
		echo("</td>");
		echo("<td></td>");
		echo("</tr>");
		echo("</table>");

		echo("</div>");
		form::End();
		echo("</div>");
		
		//MOBILE:
		echo("<div class='agent_dashboard visible-sm visible-xs agent_dashboard_mobile'>");
		echo("<h1 class='agent_color1'>Vendors</h1>");
		echo("<div class='row'>");
		echo("<div class='col-xs-12' style='text-align:center'>");
		$js2="height_handler();";
		$js2.="jQuery('SELECT.vendor_type_id').last().trigger('focus');";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListVendors','".$this->GetFieldName('ListVendorsContainer')."','NULL','','action=".$list->newitems[0]->GetFormAction('save')."&fn=".$params['fn']."',function(){".$js2."});";
		$js.="$('html, body').animate({scrollTop:$('.list_item_vendor').last().offset().top}, 1500);";
		form::DrawButton('','Add New Vendor',array('class'=>'agent_bg_color1','onclick'=>$js,'style'=>'padding:10px;'));
		echo("</div>");
		echo("</div>");


		echo("<div class='visible-sm visible-xs agent_dashboard_mobile'>");		
		form::Begin('','POST',true,array('id'=>'vendors_mobile'));
		echo("<div class='agent_dashboard'>");
		foreach($list->items as $i=>$vendor)
		{
			echo("<table class='listing'>");
			echo("<tr class='agent_bg_color1'><th>Vendor Info</th><th><br></th></tr>");
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListVendors','X_".$this->GetFieldName('ListVendorsContainer')."','vendors_mobile','','action=".$vendor->GetFormAction('save')."&user_id=".$user->id."',function(){height_handler();});";

//			echo("<tr class='list_item'>");
//			echo("<td>Title</td><td>");
//			form::DrawTextInput($vendor->GetFieldName('vendor_title'),$vendor->Get('vendor_title'),array('class'=>'vendor_title text','onchange'=>$js));
//			echo("</td>");
//			echo("</tr>");
			echo("<tr class='list_item list_item_vendor'>");
			echo("<td>Type of Vendor</td><td>");
			//form::DrawSelect($vendor->GetFieldName('vendor_type'),array(''=>'')+vendor::GetTypes(),$vendor->Get('vendor_type'),array('onchange'=>$js));
			$opts=array(''=>'');
			$rs=database::query("SELECT * FROM vendor_types WHERE  (".$this->GetIncludedItemsSQL()." OR vendor_type_id='".$vendor->Get('vendor_type_id')."') ORDER BY vendor_type_name");
			while($rec=database::fetch_array($rs))
				$opts[$rec['vendor_type_name']]=$rec['vendor_type_id'];
			$opts['---------']=' ';
			$opts['Create new type']='NEWVENDORTYPE';
			$vendorjs="if(jQuery(this).val()=='NEWVENDORTYPE'){ObjectFunctionAjaxPopup('New Vendor Type','".get_class($this)."','".$this->id."','CreateVendorType','vendors_mobile','','vendor_id=".$vendor->id."',function(){});}else{".$js."}";
			form::DrawSelect($vendor->GetFieldName('vendor_type_id'),$opts,$vendor->Get('vendor_type_id'),array('onchange'=>$vendorjs,'class'=>'vendor_type_id'));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>Name</td><td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_name'),$vendor->Get('vendor_name'),array('placeholder'=>'Name','onchange'=>$js));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>Company</td><td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_company'),$vendor->Get('vendor_company'),array('placeholder'=>'Company','onchange'=>$js));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>Email</td><td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_email'),$vendor->Get('vendor_email'),array('placeholder'=>'Email','onchange'=>$js));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>Phone</td><td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_phone'),$vendor->Get('vendor_phone'),array('placeholder'=>'Phone','onchange'=>$js));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>Additional Information</td><td>");
			form::DrawTextInput($vendor->GetFieldName('vendor_info'),$vendor->Get('vendor_info'),array('placeholder'=>'Additional Info','onchange'=>$js));
			echo("</td>");
			echo("</tr>");

			echo("<tr class='list_item'>");
			echo("<td colspan='2'>");
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListVendors','".$this->GetFieldName('ListVendorsContainer')."','NULL','','action=".$vendor->GetFormAction('delete')."&fn=".$params['fn']."',function(){height_handler();});";
			$js.="";
			$js="if(confirm('Permanently delete this vendor?')){".$js."}";			
			echo("<a data-info='CONTACTS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
			echo("</td>");
			echo("</tr>");

			echo("<tr class='list_item_empty'>");
			echo("<td colspan='*'><br></td>");
			echo("</tr>");

			echo("</table>");
			
		}
		echo("<div id='".$this->GetFieldName('NewVendorPosition')."'></div>");
		echo("</div>");
		form::End();
		echo("</div>");		

		echo("</div>");		
	}

	public function CreateVendorType($params=array())
	{
		$vendor=new vendor($params['vendor_id']);
		$vendor_type=new vendor_type();
		$vendor_type->Set($this->primary,$this->id);
		$vendor_type->ProcessAction();
		$vendor->Set('vendor_type_id',$vendor_type->id);
		$vendor->Update();
		if($vendor_type->id)
		{
			$js2="height_handler();";
			$js="PopupClose();";
			$js.="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListVendors','".$this->GetFieldName('ListVendorsContainer')."','".$this->GetFieldName('ListVendorsFiltersForm')."','','',function(){".$js2."});";
			Javascript::Begin();
			echo($js);
			Javascript::End();
		}

		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','CreateVendorType','popup_content','CreateVendorType','','vendor_id=".$params['vendor_id']."&action=".$vendor_type->GetFormAction('save')."',function(){});return false;";
		echo("<h3 class='agent_color1'>New Vendor Type</h3>");
		if(count($vendor_type->GetErrors()))
			echo("<div class='errors'>".implode('<br>',$vendor_type->GetErrors())."</div>");
		form::Begin('','POST',false,array('id'=>'CreateVendorType'));
		echo("<div class='line'>");
		echo("<div class='row'>");
		echo("<div class='col-md-3'>");
		echo("<lable>Type Of Vendor:</label>");
		echo("</div>");
		echo("<div class='col-md-9'>");
		form::DrawTextInput($vendor_type->GetFieldName('vendor_type_name'),$vendor_type->Get('vendor_type_name'));
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("<div class='line'>");
		echo("<div class='row'>");
		echo("<div class='col-md-3'>");
		echo("</div>");
		echo("<div class='col-md-9'>");
		echo("<a class='button agent_bg_color1' onclick=\"".$js."\">Create</a>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		form::End();
	}

	public function ListPastUsers($params=array())
	{
		$this->ProcessAction();

	 	$where=array("agent_id IN(".implode(',',$this->GetAgentIds()).")");
		$where[]="user_active=0";		 	 

	  	$list=new DBRowSetEX('users','user_id','user',implode(' AND ',$where),'user_active DESC,user_order');
		$list->num_new=0;
	  	$list->Retrieve();
	  	$list->SetEachNew('user_order',0);	  	
		$list->SetFlag('ALLOW_BLANK');
		$list->ProcessAction();
	  	$list->CheckSortOrder('user_order');
		$list->SetFlag('ALLOW_BLANK');
	  	$list->ProcessAction();
	  	$list->Retrieve();

		//FULL SIZE:
		echo("<div class='agent_dashboard hidden-sm hidden-xs'>");
		form::Begin('view_tasks.php','POST',false,array('id'=>'list_users'));
		echo("<div class='agent_dashboard'>");
		echo("<h1 class='agent_color1'>View & Manage Old Transactions</h1>");
		echo("<p>Archived transactions will be saved to your account indefinitely.  You can always restore or delete a particular transaction by clicking on the appropriate button below. You can also view client contact information, the timeline when the account was archived as well as the activity log.</p>");
		echo("<table class='listing'>");
		echo("<tr class='agent_bg_color1'><th>Client Name</th><th>Address</th><th>Client Info</th><th>% Complete</th><th>Last Modified Date</th><th>View Timeline</th><th>View Activity Log</th><th>Restore Client</th><th>Permanently Delete</th></tr>");
		if(!count($list->items))
			echo("<tr><td class='emptyset' colspan='100'>There are no archived transactions to display</tr>");	
		foreach($list->items as $user)
		{
			echo("<tr class='list_item ".$user->GetFieldName('row')."'>");
			echo("<td>".$user->Get('user_name')."</a></td>");
			echo("<td>".$user->Get('user_address')."</td>");
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','user_id=".$user->id."&fn=ListPastUsers',function(){height_handler();});";
			$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('EditUserContainer')."').offset().top}, 1500);";
			echo("<td><a href='#' data-toggle='tooltip' title='View Client Details' onclick=\"".$js."\"><i class='fa fa-search'></i></a></td>");
			echo("<td>");
			$user->DisplayProgress(array('id'=>$user->GetFieldName('progress'),'height'=>'100','width'=>'100'));
			echo("</td>");
			$d=$user->GetLastModifiedDate();
			echo("<td>".$d->GetDate('m/d/Y')."</td>");
			echo("<td><a href='".$this->GetUserURL($user,'user_timeline.php')."' data-toggle='tooltip' title='View Timeline'><i class='fa fa-search'></i></a></td>");
			echo("<td><a href='".$this->GetUserURL($user,'activity_log.php')."' data-toggle='tooltip' title='View Activity Log'><i class='fa fa-search'></i></a></td>");
			echo("<td>");
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListPastUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('undelete')."',function(){height_handler();});";
			$js.="";
			$js="if(confirm('Restore this transaction?')){".$js."}";			
			echo("<a data-info='USERS_RESTORE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Restore'><i class='fas fa-trash-restore'></i></a>");
			echo("</td>");
			echo("<td>");
			$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListPastUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('delete_permanent')."',function(){height_handler();});";
			$js="jQuery('.".$user->GetFieldName('row')."').animate({opacity:0.2},2000);";
			$js.="DeleteFlare(jQuery('#".$user->GetFieldName('AgentCardPermanentDelete')."').get(0),function(){".$js2."});";
			$js="if(confirm('Permanently delete transaction?')){".$js."}";			
			echo("<a data-info='USERS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete' id='".$user->GetFieldName('AgentCardPermanentDelete')."'><i class='fa fa-trash'></i></a>");
			echo("</td>");
			echo("</tr>");
		}
		echo("</table>");
		echo("</div>");
		form::End();
		echo("</div>");

		//MOBILE:
		echo("<div class='agent_dashboard visible-sm visible-xs agent_dashboard_mobile'>");

		form::Begin('view_tasks.php','POST',false,array('id'=>'list_users_mobile'));
		echo("<div class='agent_dashboard'>");
		echo("<h1 class='agent_color1'>Archived/Past Transactions</h1>");
		if(!count($list->items))
			echo("<table class='listing'><tr><td class='emptyset' colspan='100'>There are no archived transactions to display</tr></table>");	
		foreach($list->items as $user)
		{
			echo("<table class='listing'>");
			echo("<tr class='agent_bg_color1 ".$user->GetFieldName('row')."'>");
			echo("<th>");
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','user_id=".$user->id."&fn=ListPastUsers',function(){height_handler();});";
			$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('EditUserContainer')."').offset().top}, 1500);";
			echo("<a href='#' onclick=\"".$js."return false;\">".$user->Get('user_name')." <i class='fa-solid fa-magnifying-glass'></i></a>");
			echo("</th>");
			echo("</tr>");
			echo("<tr class='list_item ".$user->GetFieldName('row')."'>");
			echo("<td>".$user->Get('user_address')."</td>");
			echo("</tr>");
			echo("<tr class='list_item ".$user->GetFieldName('row')."'>");
			echo("<td>");
			$user->DisplayProgress(array('id'=>$user->GetFieldName('progress_mobile'),'height'=>'100','width'=>'100'));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item ".$user->GetFieldName('row')."'>");
			$d=$user->GetLastModifiedDate();
			echo("<td>Last Modified ".$d->GetDate('m/d/Y')."</td>");
			echo("</tr>");
//			echo("<tr>");
//			echo("<td>");
//			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','user_id=".$user->id."&fn=ListPastUsers',function(){height_handler();});";
//			$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('EditUserContainer')."').offset().top}, 1500);";
//			form::DrawButton('','View Client Info',array('class'=>'agent_bg_color1','onclick'=>$js));
//			echo("</td>");
//			echo("</tr>");
//			echo("<tr class='list_item'>");
//			echo("<td>");
//			$js="document.location='".$this->GetUserURL($user,'user_timeline.php')."';";
//			//form::DrawButton('','View Timeline',array('class'=>'agent_bg_color1','onclick'=>$js));
//			echo("<a href='".$this->GetUserURL($user,'user_timeline.php')."'>View Timeline</a>");
//			echo("</td>");
//			echo("</tr>");
			echo("<tr class='list_item ".$user->GetFieldName('row')."'>");
			echo("<td>");
			$js="document.location='".$this->GetUserURL($user,'activity_log.php')."';";
			//form::DrawButton('','View Activity Log',array('class'=>'agent_bg_color1','onclick'=>$js));
			echo("<a href='".$this->GetUserURL($user,'activity_log.php')."'>View Activity Log</a>");
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item ".$user->GetFieldName('row')."'>");
			echo("<td>");
			$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListPastUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('undelete')."',function(){height_handler();});";
			$js.="";
			$js="if(confirm('Restore this transaction?')){".$js."}";			
			//form::DrawButton('','Restore Client',array('class'=>'agent_bg_color1','onclick'=>$js));
			echo("<a href='#' onclick=\"".$js."\">Restore Client</a>");
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item ".$user->GetFieldName('row')."'>");
			echo("<td>");
			$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListPastUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('delete_permanent')."',function(){height_handler();});";
			$js="jQuery('.".$user->GetFieldName('row')."').animate({opacity:0.2},2000);";
			$js.="DeleteFlare(jQuery('#".$user->GetFieldName('AgentCardPermanentDeleteMobile')."').get(0),function(){".$js2."});";
			$js="if(confirm('Permanently delete transaction?')){".$js."}";			
			//form::DrawButton('','Permanently Delete',array('class'=>'agent_bg_color1','onclick'=>$js));
			echo("<a href='#' onclick=\"".$js."return false;\" id='".$user->GetFieldName('AgentCardPermanentDeleteMobile')."'>Permanently delete transaction?</a>");
			echo("</td>");
			echo("</tr>");
			echo("</table>");
			echo("<br>");
		}
		echo("</div>");
		echo("</div>");
		
		echo("<div id='".$this->GetFieldName('EditUserContainer')."'></div>");
	}

	public function ViewActivity($params=array())
	{
		$this->ProcessAction();

		$user=new user($params['user_id']);

	 	$where=array("user_id='".$user->id."'");

	  	$list=new DBRowSetEX('activity_log','activity_log_id','activity_log',implode(' AND ',$where),'activity_log_timestamp');
		$list->num_new=0;
	  	$list->Retrieve();
		$list->ProcessAction();
	  	$list->Retrieve();

		echo("<div class='agent_dashboard'>");
		form::Begin('activity_log.php','POST',false,array('id'=>'activity_log'));
		echo("<div class='agent_dashboard'>");
		echo("<h1 class='agent_color1'>Activity Log for ".$user->Get('user_name')."</h1>");
		echo("<table class='listing'>");
		echo("<tr class='agent_bg_color1'><th>Date/Time</th><th>Action/Details</th><th>Performed By</th><th>IP Address</th></tr>");
		if(!count($list->items))
			echo("<tr><td class='emptyset' colspan='100'>There is no activity to display</tr>");	
		foreach($list->items as $activity_log)
		{
			$class=$activity_log->Get('foreign_class');
		 	$object=new $class($activity_log->Get('foreign_id'));
			$d=new date();
			$d->SetTimestamp($activity_log->Get('activity_log_timestamp'));
			
		 
			echo("<tr class='list_item'>");
			echo("<td><a name='".$activity_log->GetFieldName('anchor')."'></a>".$d->GetDate('m/d/Y h:i a')."</td>");
			echo("<td>".nl2br($activity_log->Get('activity_log_details'))."</td>");
			echo("<td>".$activity_log->Get('activity_log_name')."</td>");
			echo("<td>".$activity_log->Get('activity_log_ip')."</td>");
			echo("</tr>");
		}
		echo("</table>");
		echo("</div>");
		form::End();
		echo("</div>");
	}

	public function SendRemindersPopup($params=array())
	{
		global $HTTP_POST_VARS;

		$user=new user($params['user_id']?$params['user_id']:$params['user_ids'][0]);		
		$timeline_items=$this->GetNextTasks($user,"timeline_item_for IN('USER')");

		$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','',function(){height_handler();});";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','SendRemindersPopup','popup_content','".$this->GetFieldName('SendReminders')."','','action=".$this->GetFieldName('SendReminders')."&user_ids[]=".$user->id."',function(){".$js2."height_handler();});";

		form::Begin('','POST',false,array('id'=>$this->GetFieldName('SendReminders')));
		foreach($HTTP_POST_VARS['timeline_reminders'] as $id)
			form::DrawHiddenInput('timeline_reminders[]',$id);
		echo("<H1>Reminder</H1>");
		echo("<div class='line'>Send Reminder to ".$user->Get('user_name')."</div>");
		echo("<ul>");
		foreach($timeline_items as $timeline_item)
			echo("<li>".$timeline_item->Get('timeline_item_title')."</li>");
		echo("</ul>");

		if($params['action']==$this->GetFieldName('SendReminders'))
	 	{
			$params['message']=$HTTP_POST_VARS['message'];
			$this->SendReminders($params);
			
			echo("<div class='message'>Reminder Has Been Sent</div>");
			echo("<div class='line'>");
			form::DrawButton('','Close',array('onclick'=>'PopupClose();'));
			echo("</div>");
		}
		else
		{		
			echo("<div class='line'>");
			form::DrawTextArea('message',$HTTP_POST_VARS['message'],array('placeholder'=>'Optional: Add a message to '.$user->Get('user_name')));
			echo("</div>");
			echo("<div class='line'>");
			form::DrawButton('','Send Reminder',array('onclick'=>$js));
			echo("</div>");
		}
		form::End();
		
	}

	public function SendWelcomeEmailsPopup($params=array())
	{
		global $HTTP_POST_VARS;

		$user=new user($params['user_id']?$params['user_id']:$params['user_ids'][0]);		

		$js3="height_handler();";
		//$js4="ObjectFunctionAjax('".get_class($this)."','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','user_id=".$user->id."',function(){".$js5."});";
		//$js3="ObjectFunctionAjax('".get_class($this)."','".$this->id."','WelcomeEmailNotice','WelcomeEmailNoticeContainer','NULL','','user_id=".$params['user_id']."&user_contact_id=".$user_contact->id."',function(){".$js4."});";
		$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','',function(){".$js3."});";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','SendWelcomeEmailsPopup','popup_content','".$this->GetFieldName('SendWelcomeEmails')."','','action=".$this->GetFieldName('SendWelcomeEmails')."&user_ids[]=".$user->id."',function(){".$js2."height_handler();});";

		form::Begin('','POST',false,array('id'=>$this->GetFieldName('SendWelcomeEmails')));
		echo("<H1>Welcome Email</H1>");
		echo("<div class='line'>Send Welcome Email to ".$user->Get('user_name')."</div>");
	 	if($params['action']==$this->GetFieldName('SendWelcomeEmails'))
	 	{
			$params['message']=$HTTP_POST_VARS['message'];
			$this->SendWelcomeEmails($params);
			
			echo("<div class='message'>Welcome Email Has Been Sent</div>");
			echo("<div class='line'>");
			form::DrawButton('','Close',array('onclick'=>'PopupClose();'));
			echo("</div>");
		}
		else
		{		
			echo("<div class='line'>");
			form::DrawTextArea('message',$HTTP_POST_VARS['message'],array('placeholder'=>'Optional: Add a message to '.$user->Get('user_name')));
			echo("</div>");
			echo("<div class='line'>");
			form::DrawButton('','Send Welcome Email',array('onclick'=>$js));
			echo("</div>");
		}
		form::End();
	}

	public function SendLoginRemindersPopup($params=array())
	{
		global $HTTP_POST_VARS;

		$user=new user($params['user_id']?$params['user_id']:$params['user_ids'][0]);		

		$js2="ObjectFunctionAjax('".get_class($this)."','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','',function(){height_handler();});";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','SendLoginRemindersPopup','popup_content','".$this->GetFieldName('SendLoginReminders')."','','action=".$this->GetFieldName('SendLoginReminders')."&user_ids[]=".$user->id."',function(){".$js2."height_handler();});";

		form::Begin('','POST',false,array('id'=>$this->GetFieldName('SendLoginReminders')));
		echo("<H1>Login Reminder</H1>");
		echo("<div class='line'>Send Reminder to ".$user->Get('user_name')."</div>");
	 	if($params['action']==$this->GetFieldName('SendLoginReminders'))
	 	{
			$params['message']=$HTTP_POST_VARS['message'];
			$this->SendLoginReminders($params);
			
			echo("<div class='message'>Reminder Has Been Sent</div>");
			echo("<div class='line'>");
			form::DrawButton('','Close',array('onclick'=>'PopupClose();'));
			echo("</div>");
		}
		else
		{		
			echo("<div class='line'>");
			form::DrawTextArea('message',$HTTP_POST_VARS['message'],array('placeholder'=>'Optional: Add a message to '.$user->Get('user_name')));
			echo("</div>");
			echo("<div class='line'>");
			form::DrawButton('','Send Reminder',array('onclick'=>$js));
			echo("</div>");
		}
		form::End();
	}


	public function SendReminders($params=array())
	{
		global $HTTP_POST_VARS;	
		if($HTTP_POST_VARS['timeline_reminders'])
		 	$where=array("user_id>0 AND user_id IN (".implode(',',$HTTP_POST_VARS['timeline_reminders']).")");		 	 
		else if($params['user_ids'])
		 	$where=array("user_id>0 AND user_id IN (".implode(',',$params['user_ids']).")");		 	 
		else 	
			return;
			
	  	$list=new DBRowSetEX('user_contacts','user_contact_id','user_contact',implode(' AND ',$where));
		$list->Retrieve();
		$list->Each('SendReminder',array($params));
	}

	public function SendLoginReminders($params=array())
	{
		global $HTTP_POST_VARS;	
		if($params['user_ids'])
		 	$where=array("user_id>0 AND user_id IN (".implode(',',$params['user_ids']).")");		 	 
		else 	
			return;
			
	  	$list=new DBRowSetEX('user_contacts','user_contact_id','user_contact',implode(' AND ',$where));
		$list->Retrieve();
		$list->Each('SendLoginReminder',array($params));
	}


	public function SendWelcomeEmails($params=array())
	{
		global $HTTP_POST_VARS;	
		if($params['user_ids'])
		 	$where=array("user_id>0 AND user_id IN (".implode(',',$params['user_ids']).")");		 	 
		else 	
			return;
			
	  	$list=new DBRowSetEX('user_contacts','user_contact_id','user_contact',implode(' AND ',$where));
		$list->Retrieve();
		$list->Each('SendWelcomeMessage',array($params));
	}

	public function DisplayICal($params)
	{
		$user=new user($params['user_id']);
		$filename=mod_rewrite::ToURL($user->GetFullName()).$params['type'].'.ics';

		$where=array();

		$key_date_ids=array(-1);
		$ucd_where=array("user_id='".$user->id."'");
		$ucd_where[]="user_contract_date_key_date=1";
		$ucd_where[]="user_contract_date_na=0";
	  	$user_contract_dates=new DBRowSetEX('user_contract_dates','user_contract_date_id','user_contract_date',implode(' AND ',$ucd_where),'user_contract_date_date');
		$user_contract_dates->Retrieve();
		foreach($user_contract_dates->items as $user_contract_date)
			$key_date_ids[]=$user_contract_date->Get('contract_date_id');
		$where[]="timeline_item_reference_date_type='CONTRACT' AND timeline_item_reference_date IN(".implode(',',$key_date_ids).")";

		//items that depend on other items and the item they depend on is complete.
		$dependant_item_ids=array(-1);
		$dependant_items=$this->GetTimeLineItems(array('user_id'=>$user->id,'where'=>"depends_on_timeline_item_id!=0"));
		foreach($dependant_items->items as $dependant_item)
		{
			if($dependant_item->DependsOnCompleteItem())	
				$dependant_item_ids[]=$dependant_item->id;
		}
		$where[]="timeline_item_id IN(".implode(',',$dependant_item_ids).")";

		$type_where=array();
		if($params['type']=='KEY_DATES')
			$null;
		if($params['type']=='KEY_DATES_PLUS')
		{
			$type_where[]="timeline_item_reference_date_type!='NONE'";
			if($user->Get('user_agent_only_notifications'))
				$type_where[]="timeline_item_for IN('AGENT','OTHER')";
			else
				$type_where[]="timeline_item_for IN('AGENT')";
		}
		if($params['type']=='ALL')
		{
			$type_where[]="timeline_item_reference_date_type!='NONE'";
			if($user->Get('user_agent_only_notifications'))
				$type_where[]="timeline_item_for IN('USER','AGENT','OTHER')";
			else
				$type_where[]="timeline_item_for IN('USER','AGENT')";
		}
		if(count($type_where))
			$where[]=implode(" AND ",$type_where);
		
		//all choices.
		$where="((".implode(') OR (',$where)."))";

		$timeline_items=$this->GetTimeLineItems(array('user_id'=>$user->id,'where'=>$where,'show_completed'=>1));
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		
		
		header('Content-type:text/calendar');
		header('Content-Disposition:attachment;filename=' . $filename);
	
		echo("BEGIN:VCALENDAR"."\r\n");
		echo("PRODID:WHATSNEXT-".strtoupper(get_class($this))."-".$this->id."-USER-".$user->id.""."\r\n");
		echo("VERSION:2.0"."\r\n");
		echo("X-WR-CALNAME:What's Next Real Estate: ".$user->GetFullName()."\r\n");
		$timeline_items->Each('DisplayICal',array($params));
		echo("END:VCALENDAR"."\r\n");
	}

	function PrintTimeline($params=array()) 
	{
		$user=new user($params['user_id']);

		$params['show_completed']=1;
	  	$list=$this->GetTimelineItems($params);
		echo("<div class='print_timeline_items'>");
		echo("<table>");
		echo("<tr class='agent_bg_color1'><th>TO DO</th><th>DUE DATE</th><th>AGENT COMPLETED</th><th>CLIENT COMPLETED</th></tr>");
		foreach($list->items as $timeline_item)
		{
			$d=new DBDate($timeline_item->Get('timeline_item_date'));
		 	$d2=new date();
			$d2->SetTimestamp($timeline_item->Get('timeline_item_complete'));
			
			echo("<tr>");
			echo("<td>".$timeline_item->Get('timeline_item_title')."</td>");
			echo("<td class='date'>".(($user->Get('user_under_contract') and $d->IsValid())?$d->GetDBDate():'')."</td>");
//			if($timeline_item->Get('timeline_item_complete') and $timeline_item->Get('timeline_item_completed_class')!='user_contact')
//				echo("<td class='date'>".$d2->GetDate('m/d/Y')."</td>");				
//			else
//				echo("<td class='checkbox'><input type='checkbox' class='cehckbox'></td>");
//			if($timeline_item->Get('timeline_item_complete') and $timeline_item->Get('timeline_item_completed_class')=='user_contact')
//				echo("<td class='date'>".$d2->GetDate('m/d/Y')."</td>");				
//			else
//				echo("<td class='checkbox'><input type='checkbox' class='cehckbox'></td>");
			echo("<td class='checkbox_cell'>");
			if($timeline_item->Get('timeline_item_complete') and ($timeline_item->Get('timeline_item_completed_class')!='user_contact'))		
				form::DrawCheckbox('',1,true);
			else if(!$timeline_item->Get('timeline_item_complete') and $timeline_item->Get('timeline_item_for')!='USER')		
				form::DrawCheckbox('',2,false);
			else
				echo("<br>");
			echo("</td>");
			echo("<td class='checkbox_cell'>");
			if($timeline_item->Get('timeline_item_complete') and ($timeline_item->Get('timeline_item_completed_class')=='user_contact'))		
				form::DrawCheckbox('',3,true);
			else if(!$timeline_item->Get('timeline_item_complete') and $timeline_item->Get('timeline_item_for')=='USER')		
				form::DrawCheckbox('',4,false);
			else
				echo("<br>");
			echo("</td>");

			echo("</tr>");			
		}	
		echo("</table>");
		echo("</div>");
	}

	public function GetNotificationTypesForUser($user)
	{
	 	//use my dfault settings, 
		$settings=json_decode($this->GetSettings(),true);			
		//per TX settings?
		$user_to_transaction_hander=new user_to_transaction_handler();
		$user_to_transaction_hander->InitByKeys(array('user_id','foreign_id','foreign_class'),array($user->id,get_class($this),$this->id));
		if($user_to_transaction_hander->Get('user_to_transaction_handler_settings_updated'))
			$settings=json_decode($this->Get('user_to_transaction_handler_settings_updated'),true);		
		$types=array('-1');
		if($settings['notifications']['agent'])
			$types[]="AGENT";
		if($settings['notifications']['other'])
			$types[]="OTHER";
		if($settings['notifications']['user'])
			$types[]="USER";		
			
		return $types;
	}
}	  
?>