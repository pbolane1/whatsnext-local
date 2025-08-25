<?php
//**************************************************************//
//	
//	FILE: c_agent.php
//  CLASS: agent
//  
//	STUBBED BY: PoCo Technologies LLC CoreLib Autocoder v0.0 BETA
//  PURPOSE: database abstraction for the agents table
//  STUBBED TIMESTAMP: 1212155116
//
//**************************************************************//

class agent extends DBRowEx
{
	use public_user;
	use transaction_handler;
	
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('agents','agent_id');
		$this->Retrieve();
	}

	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{
			$today=new Date();
			$this->Set('agent_reset_date',$today->GetDBDate());
			$this->Set('agent_active',1);
			
			$settings=array();
			$settings['notifications']=array();
			$settings['notifications']['phone']=0;
			$settings['notifications']['email']=0;
			$settings['notifications']['user']=0;
			$settings['notifications']['other']=1;
			$settings['notifications']['agent']=1;
			$this->Set('agent_settings',json_encode($settings));						

			$this->DefaultColors();
		}
	}


	public function IsAgent()
	{			
		return true;
	}

	public function DefaultColors()
	{
		$this->Set('agent_color1_hex','#009901');
		$this->Set('agent_color1_fg_hex','#FFFFFF');
		$this->Set('agent_color2_hex','#000000');
		$this->Set('agent_color2_fg_hex','#FFFFFF');		


		$coordinator=$this->GetDefaultCoordinator();
		if($coordinator)
		{
			$this->Set('agent_color1_hex',$coordinator->Get('coordinator_color1_hex'));
			$this->Set('agent_color1_fg_hex',$coordinator->Get('coordinator_color1_fg_hex'));
			$this->Set('agent_color2_hex',$coordinator->Get('coordinator_color2_hex'));
			$this->Set('agent_color2_fg_hex',$coordinator->Get('coordinator_color2_fg_hex'));
			
		}

		
		$this->Set('agent_colors_default',true);		
	}

	public function ToURL($page='',$expires=3)
	{
		if($page[0]=='?')
			$page='/agents/'.$page;

		$agent_link=new agent_link;
		return $agent_link->Generate($this->id,$page,$expires);
	}

	public function DirectURL($page='')
	{
		return _navigation::GetBaseURL().'agents/'.$page;
	}

	public function GetUserURL($user,$page='edit_user.php',$force_agent=false)
	{
		return _navigation::GetBaseURL()."agents/user/".md5('user'.$user->id)."/".$page;
		//return _navigation::GetBaseURL()."agents/".$page."?user_id=".$this->id;
	}

	public function GetName()
	{
		return $this->Get('agent_name');
	}

	public function GetPhone()
	{
		return $this->Get('agent_phone');
	}

	public function GetEmail()
	{
		return $this->Get('agent_email');
	}

	public function GetSettings()
	{
		return $this->Get('agent_settings');
	}

	public function GetFullName()
	{
		return $this->Get('agent_name');
	}

	public function DisplayEditable()
	{
		$login=new Date();
		$login->SetTimestamp($this->Get('agent_last_login'));

	 	echo("<td><a href='/agents/?action=login_as&agent_id=".$this->id."' target='_blank'>".$this->Get('agent_name')."</a></td>");
	 	echo("<td class='hidden-sm hidden-xs'>".$this->Get('agent_email')."</td>");
	 	echo("<td class='hidden-sm hidden-xs'>".($this->Get('agent_last_login')?$login->GetDate('m/d/Y'):'')."</td>");
	 	//$this->GenericLink("clients.php?&agent_id=".$this->id,'Clients');
	}
	
	public function DeleteLink()
    {
		if($this->Get('agent_active') and !$this->Get('agent_special'))
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('agent_active'),0);
			form::DrawSubmit('','Delete',array('onclick'=>"return confirm('Are you sure you want to disable this agent?');"));
			form::end();
		}
		echo("</td>");
	}

	public function EditLink()
    {
		if(!$this->Get('agent_active'))
		{	
		 	echo("<td class='edit_actions'>");
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('agent_active'),1);
			form::DrawSubmit('','RE-ACTIVATE',array('onclick'=>"return confirm('Are you sure you want to reactivate this agent?');"));
			form::end();			
		}
		else
		{
			parent::EditLink();

			$confirm="Are you sure you are ready to send a welcome email to this agent?";
			if($this->Get('agent_welcome_timestamp'))
			{
			 	$date=new date();
			 	$date->Settimestamp($this->Get('agent_welcome_timestamp'));
				$confirm="Are you sure you would like to re-send a welcome email to this agent?  Welcome mail has already been sent on ".$date->GetDate('m/d/Y');
			}

			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('welcome').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawSubmit('','Send Welcome Email',array('onclick'=>"return confirm('".$confirm."');"));
			form::end();			
		}
	}

	public function DoAction($action)
	{
		global $HTTP_POST_VARS;
		global $HTTP_GET_VARS;
		
	 	parent::DoAction($action);
	 	//if($action==$this->GetFormAction('reset'))
		//	$this->ResetTemplates();
	 	if($action==$this->GetFormAction('filter_users'))
			$this->FilterUsers();
	 	if($action==$this->GetFormAction('filter_templates'))
			$this->FilterTemplates();
	 	if($action==$this->GetFormAction('add_widget'))
			$this->AddWidget();
	 	if($action==$this->GetFormAction('default_colors'))
	 	{
			$this->DefaultColors();
			$this->Update();
		}
	 	if($action==$this->GetFormAction('custom_colors'))
	 	{
			$this->Set('agent_colors_default',false);		
			$this->Update();
		}
		if($action==$this->GetFormAction('toggle_condensed_view'))
		{
			Session::Set($this->GetFieldName('condensed_view'),!Session::Get($this->GetFieldName('condensed_view')));			
		}
		if($action==$this->GetFormAction('toggle_deleted_view'))
		{
			Session::Set($this->GetFieldName('show_deleted'),!Session::Get($this->GetFieldName('show_deleted')));			
		}
		if($action==$this->GetFormAction('toggle_commpleted_view'))
		{
			Session::Set($this->GetFieldName('show_completed'),!Session::Get($this->GetFieldName('show_completed')));			
		}		
		if($action==$this->GetFormAction('welcome'))
			$this->SendWelcomeMessage();
		if($action==$this->GetFormAction('accept_tc_invite'))
			$this->AcceptCoordinatorInvite($HTTP_GET_VARS);
		if($action==$this->GetFormAction('decline_tc_invite'))
			$this->DeclineCoordinatorInvite($HTTP_GET_VARS);
		if($action==$this->GetFormAction('remove_image_file2'))
		{
			$this->Set('agent_image_file2','');			
			$this->Update();
		}
		if($action==$this->GetFormAction('remove_image_file3'))
		{
			$this->Set('agent_image_file3','');
			$this->Update();
		}

	}			

	public function ResetTemplates()
	{
		return;
		//never do this.

 	 	$old=array();	 	
 	 	$map=array();	 	

		///remember the old templates.
		$where=array('agent_id='.$this->id);
		$where[]='template_active=1';
	  	$list=new DBRowSetEX('templates','template_id','template',implode(' AND ',$where),'template_order');
		$list->Retrieve();
	  	foreach($list->items as $template)
			$old[$template->Get('original_id')]=$template->id;

		//give me new templates
		template::CopyAll(array('agent_id'=>0),array('agent_id'=>$this->id));
		
		
		///map the old templates to the new ones.
		$where=array('agent_id='.$this->id);
		$where[]='template_active=1';
	  	$list=new DBRowSetEX('templates','template_id','template',implode(' AND ',$where),'template_order');
		$list->Retrieve();
	  	foreach($list->items as $template)
	  	{
			foreach($old as $original_id=>$template_id)
			{
				if($original_id==$template->Get('original_id'))
					$map[$template_id]=$template->id;
			}
		}
		//give my user new templates.
	 	$where=array("agent_id='".$this->id."'");
	 	$where[]="user_active=1";
	  	$list=new DBRowSet('users','user_id','user',implode(' AND ',$where),'user_name');
		$list->Retrieve();
	  	foreach($list->items as $user)
	  	{
			timeline_item::CopyAll(array('template_id'=>$map[$user->Get('template_id')],'agent_id'=>$this->Get('agent_id'),'user_id'=>0),array('agent_id'=>$user->Get('agent_id'),'user_id'=>$user->id));		
			$user->Set('template_id',$map[$user->Get('template_id')]);
			$user->Update();			
		}
	}

	public function DrawFooter1()
	{
		if(!$this->id) 
			return;
		echo "<div class='row'>";
		echo "<div class='col-md-4'>";
		if($this->Get('agent_image_file3'))
			echo("<img src='".$this->GetThumb(120,120,false,'agent_image_file3')."'>");
		echo "</div>";
		echo "<div class='col-md-8'>";
		echo $this->Get('agent_fullname')."<br>";
		echo $this->Get('agent_company')."<br>";
		echo "DRE#: ".$this->Get('agent_number')."<br>";
		echo "<a href='tel:".$this->Get('agent_phone')."'>".$this->Get('agent_phone')."</a><br>";
		echo "<a href='mailto:".$this->Get('agent_email')."'>".$this->Get('agent_email')."</a><br>";
		echo "</div>";
		echo "</div>";		
	}

	public function DrawFooter2()
	{
		if(!$this->id) 
			return;
		if($this->Get('agent_image_file2'))
			echo("<a href='https://www.google.com/maps/dir/".$this->Get('agent_company')." ".$this->Get('agent_address')."' target='_blank' title='Directions to ".$this->Get('agent_company')."'><img src='".$this->GetThumb(200,65,false,'agent_image_file2')."'></a>");
		echo("<a href='https://www.google.com/maps/dir/".$this->Get('agent_company')." ".$this->Get('agent_address')."' target='_blank' alt='".$this->Get('agent_company')."' title='".$this->Get('agent_company')."'>".nl2br($this->Get('agent_address'))."</a>");
	}

	public function DrawLogo()
	{
		if($this->Get('agent_image_file2'))
			echo("<img src='".$this->GetThumb(225,100,false,'agent_image_file2')."'></a>");
	}

	public function GetIncludedItemsSQL($params=array())
	{
	 	$where=array();
		//stock items
		$where[]="agent_id=0 AND coordinator_id=0";
		//items I created.
		$where[]="agent_id='".$this->id."'";
		//items my coordinators created
		$where[]="coordinator_id IN(".implode(',',$this->GetCoordinatorIDs()).")";
		
		return "((".implode(') OR (',$where)."))";
	}

	public function GetAvailableTemplateIds($params=array())
	{
		$template_ids=array(-1=>-1);
		
		$rs=database::query("SELECT template_id FROM templates WHERE agent_id=0 AND coordinator_id=0 AND user_id=0");
		while($rec=database::fetch_array($rs))	
			$template_ids[$rec['template_id']]=$rec['template_id'];
		$rs=database::query("SELECT template_id FROM templates WHERE agent_id='".$this->id."'");
		while($rec=database::fetch_array($rs))	
			$template_ids[$rec['template_id']]=$rec['template_id'];
		$rs=database::query("SELECT template_id FROM templates_to_transaction_handlers WHERE foreign_class='agent' AND foreign_id='".$this->id."'");
		while($rec=database::fetch_array($rs))	
			$template_ids[$rec['template_id']]=$rec['template_id'];
		return $template_ids;
	}

	public function GetDefaultTemplateID($type='')
	{
		if($type=='BUYER' and $this->Get('template_id_buyer'))
			return $this->Get('template_id_buyer');
		if($type=='SELLER' and $this->Get('template_id_seller'))
			return $this->Get('template_id_seller');
						
		if($rec=database::fetch_array(database::query("SELECT template_id FROM templates WHERE template_type='".$type."' AND template_default='1' AND coordinator_id IN(".implode(',',$this->GetCoordinatorIDs()).") AND template_active=1 AND template_status=1")))
			return $rec['template_id'];
		if($rec=database::fetch_array(database::query("SELECT template_id FROM templates WHERE template_type='".$type."' AND template_default='1' AND agent_id='".$this->id."' AND template_active=1 AND template_status=1")))
			return $rec['template_id'];
		if($rec=database::fetch_array(database::query("SELECT template_id FROM templates WHERE template_type='".$type."' AND template_default='1' AND coordinator_id=0 AND agent_id=0 AND template_active=1 AND template_status=1")))
			return $rec['template_id'];
		
		
		return -1;
	}
		
	
	public function GetAgentIDs($params=array())
	{
		$agent_ids=array($this->id);
		if($params['related'])
		{
			$rs=database::query("SELECT agent_id FROM agents_to_coordinators WHERE coordinator_id IN(".implode(',',$this->GetCoordinatorIDs()).") AND agents_to_coordinators_accepted>0");
			while($rec=database::fetch_array($rs))	
				$agent_ids[]=$rec['agent_id'];
		}
		return $agent_ids;
	}


	public function GetCoordinatorIDs($params=array())
	{
	 	$coordinator_ids=array(-1);
		$rs=database::query("SELECT coordinator_id FROM agents_to_coordinators WHERE agent_id='".$this->id."' AND agents_to_coordinators_accepted>0 AND agents_to_coordinators_rejected=0");
		while($rec=database::fetch_array($rs))	
			$coordinator_ids[]=$rec['coordinator_id'];

		return $coordinator_ids;
	}
	
	public function GetDefaultCoordinator($params=array())
	{	 	
		$rs=database::query("SELECT coordinator_id FROM agents_to_coordinators WHERE agent_id='".$this->id."' AND agents_to_coordinators_accepted>0 AND agents_to_coordinators_rejected=0 LIMIT 1");
		if($rec=database::fetch_array($rs))	
			$coordinator=new coordinator($rec['coordinator_id']);

		return $coordinator;
	}	

	public function ListWidgets($params)
	{
		if(!$params['fn'])
			$params['fn']='ListWidgets';

	 	$where=array("agent_id='".$this->id."' AND user_id=0 AND widget_type='CONTENT'");

 	  	$list=new DBRowSetEX('widgets','widget_id','widget',implode(' AND ',$where),'widget_id');
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('agent_id',$this->id);
	  	$list->SetEachNew('widget_type','CONTENT');
		$list->SetFlag('ALLOW_BLANK');
		$list->ProcessAction();
	  	$list->Retrieve();
	  	$list->SetEachNew('agent_id',$this->id);
	  	$list->SetEachNew('widget_type','CONTENT');
		$list->ProcessAction();

		//MOBILE:
		echo("<div class='agent_dashboard visible-sm visible-xs agent_dashboard_mobile'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-12' style='text-align:center'>");
		$js2="height_handler();";
		$js2.="jQuery('INPUT.widget_title:visible').focus();";
		$js="ObjectFunctionAjax('agent','".$this->id."','ListWidgets','".$this->GetFieldName('ListWidgetsContainer')."','NULL','','action=".$list->newitems[0]->GetFormAction('save')."&fn=".$params['fn']."',function(){".$js2."});";
		$js.="$('html, body').animate({scrollTop:$('.list_item_widget').last().offset().top}, 1500);";
		form::DrawButton('','Add Custom Item',array('class'=>'agent_bg_color1','onclick'=>$js,'style'=>'padding:10px;'));
		echo("</div>");
		echo("</div>");		
		echo("</div>");
		
		//FULL SIZE & MOBILE
		echo("<div class=''>");		
		form::Begin('','POST',true,array('id'=>'widgets'));
		echo("<div class='agent_dashboard'>");
		echo("<h1 class='agent_color1'>Custom Sidebar Items</h1>");
		echo("<table class='listing listing_widgets'>");
		foreach($list->items as $widget)
		{
			$js="UpdateWYSIWYG(jQuery('#widgets'));";
			$js.="ObjectFunctionAjax('agent','".$this->id."','ListWidgets','X_".$this->GetFieldName('ListWidgetsContainer')."','widgets','','action=".$widget->GetFormAction('save')."',function(){height_handler();});";

			echo("<tr class='agent_bg_color1'>");
			echo("<th>");
			form::DrawTextInput($widget->GetFieldName('widget_title'),$widget->Get('widget_title'),array('class'=>'widget_title text','onchange'=>$js));
			echo("</th>");
			echo("</tr>");
			echo("<tr class='list_item list_item_widget'>");
			echo("<td>");
			if(($params['action']==$widget->GetFormAction('edit_widget_content')) or $params['edit_widget_content'])
			{
				form::DrawTextArea($widget->GetFieldName('widget_content'),$widget->Get('widget_content'),array('class'=>'wysiwyg_input wysiwyg_intro','onchange'=>$xsavejs));
				$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
				$wysiwyg_info.="onchange_callback:function(){".$xsavejs."},\r\n";
				wysiwyg::RegisterMode($widget->GetFieldName('SIMPLE_LINK_HEADLINES'),$wysiwyg_info);
				form::MakeWYSIWYG($widget->GetFieldName('widget_content'),$widget->GetFieldName('SIMPLE_LINK_HEADLINES'));
			}
			else
			{
				$editjs="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','ListWidgets','".$this->GetFieldName('ListWidgetsContainer')."','NULL','','user_id=".$user->id."&agent_id=".$this->id."&agent=1&action=".$widget->GetFormAction('edit_widget_content')."',function(){});";
				echo("<div class='widget_text_editable' onclick=\"".$editjs."\">".$widget->Get('widget_content')."</div>");
			}
			echo("<div>");
			if(($params['action']==$widget->GetFormAction('edit_widget_content')) or $params['edit_widget_content'])
			{
				$js="UpdateWYSIWYG(jQuery('#widgets'));";
				$js.="ObjectFunctionAjax('agent','".$this->id."','ListWidgets','".$this->GetFieldName('ListWidgetsContainer')."','widgets','','action=".$widget->GetFormAction('save')."',function(){height_handler();});";

				echo("<a data-info='VENDORS_DONE' data-info-none='none' href='#' onclick=\"".$js."return false;\" class='button agent_bg_color1'>Done</a>");
				echo("&nbsp;&nbsp;&nbsp;");
			}

			$js="ObjectFunctionAjax('agent','".$this->id."','ListWidgets','".$this->GetFieldName('ListWidgetsContainer')."','".$this->GetFieldName('ListWidgetsContainerForm')."','','action=".$widget->GetFormAction('delete')."&fn=".$params['fn']."',function(){height_handler();});";
			$js.="";
			$js="if(confirm('Permanently delete this item?')){".$js."}";			
			//echo("<a data-info='VENDORS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
			echo("<a data-info='VENDORS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" class='button agent_bg_color1'>Delete</a>");
			echo("</div>");
			echo("</td>");
			echo("</tr>");

			echo("<tr class='list_item_empty'>");
			echo("<td colspan='*'><br></td>");
			echo("</tr>");
		}

		echo("<tr class='footer_actions_mobile hidden-sm hidden-xs'>");
		echo("<td colspan='1000'>");
		$js2="height_handler();";
		$js2.="jQuery('INPUT.widget_title:visible').focus();";
		$js="ObjectFunctionAjax('agent','".$this->id."','ListWidgets','".$this->GetFieldName('ListWidgetsContainer')."','NULL','','action=".$list->newitems[0]->GetFormAction('save')."&edit_widget_content=1&user_id=".$user->id."&fn=".$params['fn']."',function(){".$js2."});";
		echo("<a href='#' class='button agent_bg_color1' onclick=\"".$js."return false;\">Add Custom Item</a>");
		echo("</td>");
		echo("<td></td>");
		echo("</tr>");

		echo("</table>");
		echo("<div id='".$this->GetFieldName('NewWidgetPosition')."'></div>");
		echo("</div>");
		form::End();
		echo("</div>");


		

	}		
	
	public function ListUsers($params=array())
	{
		$this->ProcessAction();
	 	if($params['action']=='SendReminders')
			$this->SendReminders($params);
	 	if($params['action']=='SendLoginReminders')
			$this->SendLoginReminders($params);
	 	if($params['action']=='SendWelcomeEmails')
			$this->SendWelcomeEmails($params);


	 	$where=array("agent_id='".$this->id."'");
		$where[]="user_active=1";		 	 

	  	$list=new DBRowSetEX('users','user_id','user',implode(' AND ',$where),'user_active DESC,user_order');
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('agent_id',$this->id);
	  	$list->SetEachNew('user_order',0);	  	
		$list->SetFlag('ALLOW_BLANK');
		$list->ProcessAction();
	  	$list->CheckSortOrder('user_order');
		$list->SetFlag('ALLOW_BLANK');
	  	$list->ProcessAction();
	  	$list->Retrieve();
	  	$list->SetEachNew('agent_id',$this->id);

		//FULL SIZE:
		echo("<div class='agent_dashboard hidden-sm hidden-xs'>");
		form::Begin('view_tasks.php','POST',false,array('id'=>'list_users'));
		echo("<div class='agent_dashboard'>");
		echo("<h1 class='agent_color1'>Active Transactions</h1>");
		echo("<table class='listing'>");
		echo("<tr class='agent_bg_color1'><th>Client</th><th>Last Login</th><th>Address</th><th>% Complete</th><th>Under Contract</th><th>What's Next: Client</th><th>What's Next: Agent</th><th>View Timelines</th><th>Archive</th></tr>");
		if(!count($list->items))
			echo("<tr><td class='emptyset' colspan='100'>There are no active transactions to display</tr>");	
		foreach($list->items as $user)
		{
			echo("<tr class='list_item'>");
			$js="ObjectFunctionAjax('agent','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','user_id=".$user->id."&fn=ListUsers',function(){height_handler();});";
			$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('EditUserContainer')."').offset().top}, 1500);";
			echo("<td nowrap><a href='#' data-toggle='tooltip' title='Edit Client Details' onclick=\"".$js."\">".$user->Get('user_name')."</a><br>(".Text::Capitalize(strtolower($user->Get('user_type'))).")</td>");
			echo("<td>");
			$this->DrawLastLogin($user);
			echo("</td>");
			echo("<td><a href='".$this->GetUserURL($user)."'>".$user->Get('user_address')."</a></td>");
			echo("<td>");
			$user->DisplayProgress(array('id'=>$user->GetFieldName('progress'),'height'=>'100','width'=>'100'));
			echo("</td>");
			echo("<td id='".$user->GetFieldName('UCToggleContainer')."'>");
			$this->DrawUCToggle(array('user_id'=>$user->id,'primary_container'=>$user->GetFieldName('UCToggleContainer'),'secondary_container'=>$user->GetFieldName('UCToggleContainerMobile')));
			echo("</td>");
			$this->DrawNextTask($user,"timeline_item_for IN('USER')",array('reminder_button'=>true,'show_notification_date'=>true));
			$this->DrawNextTask($user,"timeline_item_for IN('AGENT','OTHER')");
			echo("<td>");
			$js="if(jQuery('.timeline_view:checked').length){jQuery('#timeline_view_button').removeClass('disabled')}else{jQuery('#timeline_view_button').addClass('disabled');}";
			form::DrawCheckbox('user_ids[]',$user->id,false,array('class'=>'timeline_view','onclick'=>$js));
			echo("</td>");
			echo("<td>");
			$js="ObjectFunctionAjax('agent','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('archive_transaction')."',function(){height_handler();});";
			$js.="";
			$js="if(confirm('Archive this transaction?  This will turn off all reminders (to agent and client), but this transaction can be reviewed and restored at any time by clicking on the &quot;Archived&quot; link at the top of the page')){".$js."}return false;";			
			echo("<a data-info='USERS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Archive'><i class='fa fa-archive'></i></a>");
			echo("</td>");
			echo("</tr>");
		}
		echo("<tr class='footer_actions'>");
		echo("<td></td>");
		echo("<td></td>");
		echo("<td></td>");
		echo("<td></td>");
		echo("<td></td>");
		echo("<td></td>");
		echo("<td>");
		if(count($list->items))
		{		
//			$js="ObjectFunctionAjax('agent','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','list_users','','action=SendReminders&user_ids[]=".$user->id."',function(){height_handler();});";
//			form::DrawButton('','Send Now',array('id'=>'timeline_reminder_button','class'=>'disabled','onclick'=>$js,'data-toggle'=>'tooltip','title'=>'Send client reminder to complete next item'));
		}
		echo("</td>");
		echo("<td>");
		if(count($list->items))
		{
			$js="if(jQuery('.timeline_view:checked').length==1){document.location='edit_user.php?user_id='+jQuery('.timeline_view:checked').val()}";
			$js.="else if(jQuery('.timeline_view:checked').length){this.form.submit();}";
			form::DrawButton('','View',array('class'=>'agent_bg_color1 disabled','id'=>'timeline_view_button','onclick'=>$js));
		}
		echo("</td>");
		echo("<td></td>");
		echo("</tr>");

		echo("<tr class='key'>");	
		echo("<td><br></td>");	
		echo("<td>");	
		$js2="height_handler();";
		$js2.="jQuery('#".$this->GetFieldName('EditUserContainer')." INPUT:visible').get(0).focus();";
		$js="ObjectFunctionAjax('agent','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','action=new_user&fn=ListUsers',function(){".$js2."});";
		$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('EditUserContainer')."').offset().top}, 1500);";
		form::DrawButton('','Add new client',array('class'=>'agent_bg_color1','onclick'=>$js));
		echo("</td>");	
		echo("<td><br></td>");	
		echo("<td><br></td>");	
		echo("<td><br></td>");	
		echo("<td><br></td>");	
		echo("<td colspan='3'>");
		if(count($list->items))
		{
			//echo("<div class='key_line'><div class='key_item timeline_upcoming'></div> = Item is due</div>");
			echo("<div class='key_line'><div class='key_item timeline_due'></div> = Item is due in two days or less</div>");
			echo("<div class='key_line'><div class='key_item timeline_overdue'></div> = Item is past due</div>");
		}
		echo("</td>");	
		echo("</tr>");	
		echo("</table>");
		echo("</div>");
		form::End();
		echo("</div>");

		//MOBILE:
		echo("<div class='agent_dashboard visible-sm visible-xs agent_dashboard_mobile'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-12'>");
		echo("<div style='text-align:center'>");
		$js2="height_handler();";
		$js2.="jQuery('#".$this->GetFieldName('EditUserContainer')." INPUT:visible').get(0).focus();";
		$js="ObjectFunctionAjax('agent','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','action=new_user',function(){".$js2."});";
		$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('EditUserContainer')."').offset().top}, 1500);";
		form::DrawButton('','Add new client',array('class'=>'agent_bg_color1','onclick'=>$js));
		echo("</div>");
		echo("</div>");
		echo("</div>");

		form::Begin('view_tasks.php','POST',false,array('id'=>'list_users_mobile'));
		echo("<div class='agent_dashboard'>");
		echo("<h1 class='agent_color1'>Active Transactions</h1>");
		if(!count($list->items))
			echo("<table class='listing'><tr><td class='emptyset' colspan='100'>There are no active transactions to display</tr></table>");	
		foreach($list->items as $user)
		{
			echo("<table class='listing'>");
			echo("<tr class='agent_bg_color1'>");
			echo("<th>");
			$js="ObjectFunctionAjax('agent','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','user_id=".$user->id."&fn=ListUsers',function(){height_handler();});";
			$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('EditUserContainer')."').offset().top}, 1500);";
			echo("<a href='#' data-toggle='tooltip' title='Edit Client Details' onclick=\"".$js."\">".$user->Get('user_name')." (".Text::Capitalize(strtolower($user->Get('user_type'))).") <i class='fa fa-pencil'></i></a>");
			echo("</th>");
			echo("</tr>");
			if($user->Get('user_address'))
			{
				echo("<tr class='list_item'>");
				echo("<td>".$user->Get('user_address')."</td>");
				echo("</tr>");
			}
			echo("<tr class='list_item'>");
			echo("<td>");
			$user->DisplayProgress(array('id'=>$user->GetFieldName('progress_mobile'),'height'=>'100','width'=>'100'));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td id='".$user->GetFieldName('UCToggleContainerMobile')."'>Under Contract: ");
			$this->DrawUCToggle(array('user_id'=>$user->id,'primary_container'=>$user->GetFieldName('UCToggleContainerMobile'),'secondary_container'=>$user->GetFieldName('UCToggleContainer')));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			$this->DrawNextTask($user,"timeline_item_for IN('USER')",array('reminder_button'=>true,'show_notification_date'=>true,'text'=>"What's Next For Client:"));
			echo("</tr>");
			echo("<tr class='list_item'>");
			$this->DrawNextTask($user,"timeline_item_for IN('AGENT','OTHER')",array('text'=>"What's Next For Agent:"));
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>");
			$js="ObjectFunctionAjax('agent','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('archive_transaction')."',function(){height_handler();});";
			$js.="";
			$js="if(confirm('Archive this transaction? This will turn off all reminders (to agent and client), but this transaction can be reviewed and restored at any time by clicking on the &quot;Past Tx&quot; link at the top of the page')){".$js."}";			
			echo("<a data-info='USERS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Archive'><i class='fa fa-archive'></i> Archive Transaction</a>");
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>");
			$js="document.location='".$this->GetUserURL($user)."';";
			form::DrawButton('','View Timeline',array('class'=>'agent_bg_color1','onclick'=>$js));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>");
			echo(" Or <label>Select Multiple Timelines To View ");
			$js="if(jQuery('.timeline_view_mobile:checked').length){jQuery('#timeline_view_button_mobile').removeClass('disabled')}else{jQuery('#timeline_view_button_mobile').addClass('disabled');}";
			form::DrawCheckbox('user_ids[]',$user->id,false,array('class'=>'timeline_view_mobile','onclick'=>$js));
			echo("</label>");
			echo("</td>");
			echo("</tr>");


			echo("</table>");
			echo("<br>");
		}
		
		if(count($list->items))
		{
			echo("<div class='row'>");
			echo("<div class='col-md-6'>");
			echo("<div class='button_container'>");
			$js="if(jQuery('.timeline_view_mobile:checked').length){this.form.submit();}";
			form::DrawButton('','View Selected Timelines',array('id'=>'timeline_view_button_mobile','class'=>'agent_bg_color1 disabled','onclick'=>$js));
			echo("</div>");
			echo("</div>");
			echo("<div class='col-md-6'>");
			echo("<div class='key'>");
			//echo("<div class='key_line'><div class='key_item timeline_upcoming'></div> = Item is due</div></div>");
			echo("<div class='key_line'><div class='key_item timeline_due'></div> = Item is due in two days or less</div>");
			echo("<div class='key_line'><div class='key_item timeline_overdue'></div> = Item is past due</div>");
			echo("</div>");
			echo("</div>");
			echo("</div>");
		}
		echo("</div>");
		form::End();
		echo("</div>");
		//$this->ListUsers__OLD();
	}
	
	public function DrawLastLogin($user)
	{
//		$js="if(jQuery('.timeline_reminder:checked').length){jQuery('#timeline_reminder_button').removeClass('disabled')}else{jQuery('#timeline_reminder_button').addClass('disabled');}";
//		form::DrawCheckbox('timeline_reminders[]',$user->id,false,array('class'=>'timeline_reminder','onclick'=>$js));
		$login_date=new DBDate('1969-12-31');
		$notified_date=new DBDate('1969-12-31');
		$user_contacts=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"user_id='".$user->id."'");
		$user_contacts->Retrieve();
		$welcome='';
		foreach($user_contacts->items as $user_contact)
		{
			$d=new Date();
			$d->SetTimestamp($user_contact->Get('user_contact_last_login'));
			if(date::GetDays($d,$login_date)<0)
				$login_date->SetTimestamp($user_contact->Get('user_contact_last_login'));

			$d=new Date();
			$d->SetTimestamp($user_contact->Get('user_contact_last_login_reminder'));

			if(date::GetDays($d,$notified_date)<0)
				$notified_date->SetTimestamp($user_contact->Get('user_contact_last_login_reminder'));

			if($user_contact->Get('user_contact_welcome_timestamp'))
				$welcome=true;
		}
		if($login_date->IsValid())
			echo($login_date->GetDate('m/d/Y'));
		else //if(!count($user_contacts->items))
			echo("(Never)");

		if($welcome)
		{
			//$js="ObjectFunctionAjax('agent','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','list_users','','action=SendLoginReminders&user_ids[]=".$user->id."',function(){height_handler();});";
			$js="ObjectFunctionAjaxPopup('Send Login Reminder','agent','".$this->id."','SendLoginRemindersPopup','NULL','','user_ids[]=".$user->id."',function(){height_handler();});";
			if(!$user->HasNotifications())
				$js="alert('The client has all notifications turned off');";
			form::DrawButton('','Send Reminder',array('class'=>'agent_bg_color1','id'=>'timeline_reminder_button','onclick'=>$js,'data-toggle'=>'tooltip','title'=>'Send client reminder to access the site'));
		}
		else
		{
			//$js="ObjectFunctionAjax('agent','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','list_users','','action=SendWelcomeEmails&user_ids[]=".$user->id."',function(){height_handler();});";
			$js="ObjectFunctionAjaxPopup('Send Welcome Email','agent','".$this->id."','SendWelcomeEmailsPopup','NULL','','user_ids[]=".$user->id."',function(){height_handler();});";
			if(!$user->HasNotifications())
				$js="alert('The client has all notifications turned off');";
			form::DrawButton('','Send Welcome Email',array('class'=>'agent_bg_color1','id'=>'timeline_reminder_button','onclick'=>$js,'data-toggle'=>'tooltip','title'=>'Send welcome email to client'));
		}

		if($notified_date->IsValid() and date::GetDays($login_date,$notified_date)>0)
			echo("<div>Sent reminder on ".$notified_date->GetDate('m/d/Y')."</div>");
	}
	
	public function X_DrawPercentComplete($user)
	{
		echo("<div class='agent_dashboard_percent cpb-progress-container' id='".$user->GetFieldName('agent_dashboard_percent')."'></div>");					
		$total=$user->CountSteps("timeline_item_for!='AGENT'");
		$done=min($user->CountSteps("timeline_item_for!='AGENT' AND timeline_item_complete>0"),$total);
		$percent=round(($done*100)/$total);
		Javascript::Begin();
		echo("jQuery(function(){
				let ".$user->GetFieldName('progressbar')." = new CircularProgressBar(150, 150, '".$user->GetFieldName('agent_dashboard_percent')."', {
			            strokeSize: 3,
			            showProgressNumber:true,
			            backgroundColor: '#FFFFFF',
			            strokeColor: '".$this->Get('agent_color2_hex')."',
			            showProgressNumber: true
				    });
				".$user->GetFieldName('progressbar').".showProgressNumber(true);
				let ".$user->GetFieldName('progress')." = 0;
				let ".$user->GetFieldName('interval')."=setInterval(() => {
				    ".$user->GetFieldName('progressbar').".setProgress(++".$user->GetFieldName('progress').");
				    jQuery('.progress-text').html(".$user->GetFieldName('progress')."+'%<br>complete');
				    if(".$user->GetFieldName('progress').">=".$percent.")
				    	clearInterval(".$user->GetFieldName('interval').");
				}, 100);			
			});
		");	
		Javascript::End();
	}
	
	public function X__DrawPercentComplete($user,$append='')
	{
		if(!$user->Get('template_id'))
			return;
		

		$total=$user->CountSteps("timeline_item_for!='AGENT'");
		$done=min($user->CountSteps("timeline_item_for!='AGENT' AND timeline_item_complete>0"),$total);
		$percent=round(($done*100)/$total);

		echo('<div class="progress_bar">');
		echo('<div id="'.$user->GetFieldName('progress'.$append).'">');
		if($percent>0)
			echo('<div class="loader-bg"><div class="text">'.$percent.'%</div></div>');
		else
			echo('<div class="text">'.$percent.'%</div>');
		echo('</div>');
		echo('</div>');

		if($percent>0)
		{
			Javascript::Begin();
			echo("jQuery(function(){
					 $('#".$user->GetFieldName('progress'.$append)."').Circlebar({
						  startValue: 0,
						  maxValue: ".$percent.",
						  counter: 5000,
						  triggerPercentage: true,
						  type: 'progress',
						  dialWidth: 10,
						  fontSize: '12px',
						  fontColor: '".$this->Get('agent_color1_hex')."',
						  skin: '',
						  size: '60px'  
						});
				});		 
			");	
			Javascript::End();
		}
		echo("<style type='text/css'>");
		echo(".progress_bar .loader{margin:0px auto;float:none;}");
		echo(".progress_bar .loader-spinner{color:".$this->Get('agent_color1_hex').";border-color:".$this->Get('agent_color1_hex')."}");
		echo(".progress_bar .text{color:".$this->Get('agent_color1_hex').";}");
		echo("</style>");
	}

	public function ListTemplates___OLD($params=array())
	{
		$this->ProcessAction();

	 	$where=array("agent_id='".$this->id."'");
		if(Session::Get('show_templates_type')=='INACTIVE')
			$where[]="template_active=0";
		else if(Session::Get('show_templates_type')=='ALL')
			$where[]="1=1";
		else
			$where[]="template_active=1";	

		//$where[]='template_active=1';
	  	$list=new DBRowSetEX('templates','template_id','template',implode(' AND ',$where),'template_active DESC,template_order');
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('agent_id',$this->id);
	  	$list->SetEachNew('template_order',0);	  	
		$list->SetFlag('ALLOW_BLANK');
		$list->ProcessAction();
	  	$list->CheckSortOrder('template_order');
	  	$list->Retrieve();
	  	$list->SetEachNew('agent_id',$this->id);


		echo("<div class='cards_list'>");
		echo("<div class='row'>");
		foreach($list->newitems as $template)
		{
			echo('<div class="col-sm-6">');
			echo("<div id='".$template->GetFieldName('AgentCardContainer')."'>");
			$template->NewAgentCard($params);
			echo("</div>");
			echo("</div>");
		}
		foreach($list->items as $template)
		{
			echo('<div class="col-sm-6">');
			echo("<div id='".$template->GetFieldName('AgentCardContainer')."'>");
			$template->AgentCard($params);
			echo("</div>");
			echo("</div>");
		}
		echo("</div>");
		echo("</div>");
		
	}

	public function EditSettings()
	{
		$savejs="UpdateWYSIWYG();";
		$savejs.="ObjectFunctionAjax('agent','".$this->id."','EditSettings','','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('save')."',function(){});";
		$savejs2="UpdateWYSIWYG();";
		$savejs2.="ObjectFunctionAjax('agent','".$this->id."','EditSettings','EditSettingsContainer','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('save')."',function(){});";
		if(!$this->id)
			$savejs2='';

		$savejscolor2="ObjectFunctionAjax('agent','".$this->id."','CustomCSS','CustomCSSContainer','".$this->GetFieldName('EditSettingsForm')."','','',function(){});";
		$savejscolor="UpdateWYSIWYG();";
		$savejscolor.="ObjectFunctionAjax('agent','".$this->id."','EditSettings','','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('save')."',function(){".$savejscolor2."});";


		$this->SetFlag('ALLOW_BLANK');
		if(!$params['parent_action'])
			$this->ProcessAction();

		$settings=json_decode($this->Get('agent_settings'),true);


		$acceptance_date=new contract_date();
		$acceptance_date->InitByKeys('contract_date_special','ACCEPTANCE');

		$agent_timeline_item=new timeline_item();
		$agent_timeline_item->Set('timeline_item_title','Sample Agent Timeline Item');
		$agent_timeline_item->Set('timeline_item_summary','This is a preview of how agent timeline items will be displayed with the seleted colors');
		$agent_timeline_item->Set('timeline_item_for','AGENT');
		$agent_timeline_item->Set('timeline_item_reference_date',$acceptance_date->id);
		$agent_timeline_item->Set('timeline_item_reference_date_days',2);
		$agent_timeline_item->Set('timeline_item_url',_navigation::GetBaseURL());
		$agent_timeline_item->SetFlag('AGENT');
		$agent_timeline_item->SetFlag('PREVIEW');

		$buyer_timeline_item=new timeline_item();
		$buyer_timeline_item->Set('timeline_item_title','Sample Buyer/Seller Timeline Item');
		$buyer_timeline_item->Set('timeline_item_summary','This is a preview of how the buyer/seller timeline items will be displayed with the seleted colors');
		$buyer_timeline_item->Set('timeline_item_for','USER');
		$buyer_timeline_item->Set('timeline_item_reference_date',$acceptance_date->id);
		$buyer_timeline_item->Set('timeline_item_reference_date_days',5);
		$buyer_timeline_item->Set('timeline_item_url',_navigation::GetBaseURL());
		$buyer_timeline_item->SetFlag('AGENT');
		$buyer_timeline_item->SetFlag('PREVIEW');



		echo("<div class='card'>");
		form::Begin('','POST',true,array('id'=>$this->GetFieldName('EditSettingsForm')));
		echo("<div class='card_heading agent_bg_color1'>");
		form::DrawTextInput($this->GetFieldName('agent_name'),$this->Get('agent_name'),array('class'=>'text H3','placeholder'=>'Name','onchange'=>$savejs,'data-info'=>'SETTINGS_NAME'));
		echo("</div>");
		echo("<div class='card_body'>");
		echo("<div class='card_content'>");
		echo("<a name='login_info'></a>");
		echo("<div class='card_label'>Login Information</div>");
		echo("<div class='card_section' data-info='SETTINGS_NOTIFICATIONS' data-info-none='none'>");
		echo("<div class='line'>");
		//form::DrawTextInput($this->GetFieldName('agent_email'),$this->Get('agent_email'),array('disabled'=>'disabled','placeholder'=>'Email','onchange'=>$savejs,'data-info'=>'SETTINGS_EMAIL'));
		echo($this->Get('agent_email'));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('agent_password_new'),$HTTP_POST_VARS[$this->GetFieldName('agent_password_new')],array('class'=>$this->Get('agent_password')?'':'highlighted','placeholder'=>!$this->Get('agent_password')?'Set a Password':'Change Password','onchange'=>$savejs,'data-info'=>'SETTINGS_PASSWORD'));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('agent_cellphone'),$this->Get('agent_cellphone'),array('placeholder'=>'Cell Phone','onchange'=>$savejs));
		echo("</div>");
		echo("</div>");
		echo("<div class='card_label'>Notifications</div>");
		echo("<div class='card_section' data-info='SETTINGS_NOTIFICATIONS' data-info-none='none'>");
		echo("<div class='line'>");
		echo("<label>");
		form::DrawCheckbox('notifications[phone]',1,$settings['notifications']['phone'],array('onchange'=>$savejs2));
		echo(" SMS</label>");
		echo("<label>");
		form::DrawCheckbox('notifications[email]',1,$settings['notifications']['email'],array('onchange'=>$savejs2));
		echo(" Email</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox('notifications[user]',1,$settings['notifications']['user'],array('onchange'=>$savejs2));
		echo(" Receive reminders for client items</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox('notifications[other]',1,$settings['notifications']['other'],array('onchange'=>$savejs2));
		echo(" Receive reminders for agent/other items</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox('notifications[agent]',1,$settings['notifications']['agent'],array('onchange'=>$savejs2));
		echo(" Receive reminders for agent-only items</label>");
		echo("<br>");
		echo("</div>");
		echo("</div>");

	 	$where=array("agent_id='".$this->id."'");
	 	$where[]="agents_to_coordinators_accepted>0 AND agents_to_coordinators_rejected=0";
	  	$list=new DBRowSetEX('agents_to_coordinators','agents_to_coordinators_id','agent_to_coordinator',implode(' AND ',$where),'coordinator_name');
		$list->join_tables="coordinators";
		$list->join_where="agents_to_coordinators.coordinator_id=coordinators.coordinator_id AND coordinators.coordinator_active=1";
		$list->num_new=0;
	  	$list->Retrieve();
	  	$list->ProcessAction();
		if(count($list->items))		
		{
			echo("<div class='card_label'>Transaction Coordinators & Managers</div>");
			echo("<div class='card_section' data-info='SETTINGS_COORDINATORS' data-info-none='none'>");
			foreach($list->items as $agent_to_coordinator)
			{
				$coordinator=new coordinator($agent_to_coordinator->Get('coordinator_id'));
				echo("<div class='line'>");
				$js="ObjectFunctionAjax('agent','".$this->id."','EditSettings','EditSettingsContainer','".$this->GetFieldName('EditSettingsForm')."','','action=".$agent_to_coordinator->GetFormAction('decline')."',function(){});";
				$js="if(confirm('Are you sure you would like to remove this transaction coordinator or manager from your account?')){".$js."}";
				echo("<a data-info='AGENTS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
				echo(" ".$coordinator->Get('coordinator_name'));
				echo("</div>");
			}
	
			echo("</div>");
		}

		
		echo("<div class='card_label'>Footer</div>");
		echo("<div class='card_section' data-info='SETTINGS_FOOTER' data-info-none='none'>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('agent_fullname'),$this->Get('agent_fullname'),array('placeholder'=>'Full Name','onchange'=>$savejs));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('agent_company'),$this->Get('agent_company'),array('placeholder'=>'Company','onchange'=>$savejs));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('agent_number'),$this->Get('agent_number'),array('placeholder'=>'Agent Number','onchange'=>$savejs));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('agent_phone'),$this->Get('agent_phone'),array('placeholder'=>'Phone','onchange'=>$savejs));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextArea($this->GetFieldName('agent_address'),$this->Get('agent_address'),array('placeholder'=>'Address','onchange'=>$savejs));
		echo("</div>");
		echo("</div>");

		echo("<div class='row'>");
		echo("<div class='col-md-4'>");
		echo("<div class='card_label'>Company/Brand Colors</div>");
		echo("</div>");
		echo("<div class='col-md-8'>");
		echo("<div class='card_label'>Preview Area</div>");
		echo("</div>");
		echo("</div>");

		$custom_color_js.="ObjectFunctionAjax('agent','".$this->id."','EditSettings','','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('custom_colors')."',function(){});";
		echo("<div class='card_section' data-info='SETTINGS_COLORS' data-info-none='none'>");
		echo("<div class='row'>");
		echo("<div class='col-md-4'>");
		echo("<div class='line agent_preview_color1_hex'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6'>Color 1:</div>");
		echo("<div class='col-xs-6'>");
		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color1_hex'),$this->Get('agent_color1_hex'),array('placeholder'=>'Color 1','onchange'=>$savejs2.$custom_color_js));
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("<div class='line agent_preview_color1_fg_hex'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6'>Color 1 Header:</div>");
		echo("<div class='col-xs-6'>");
		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color1_fg_hex'),$this->Get('agent_color1_fg_hex'),array('placeholder'=>'Color 1 Header','onchange'=>$savejs2.$custom_color_js));
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("<div class='visible-sm visible-xs'>");
		echo("<div class='timeline timeline_preview'>");
		$agent_timeline_item->DisplayFull();
		echo("</div>");
		echo("</div>");
		echo("<div class='line agent_preview_color2_hex'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6'>Color 2:</div>");
		echo("<div class='col-xs-6'>");
		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color2_hex'),$this->Get('agent_color2_hex'),array('placeholder'=>'Color 2','onchange'=>$savejs2.$custom_color_js));
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("<div class='line agent_preview_color2_fg_hex_hex'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6'>Color 2 Header:</div>");
		echo("<div class='col-xs-6'>");
		echo("<div class='colorpicker colorpicker-component''>");
		form::DrawTextInput($this->GetFieldName('agent_color2_fg_hex'),$this->Get('agent_color2_fg_hex'),array('placeholder'=>'Color 2 Header','onchange'=>$savejs2.$custom_color_js));
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("<div class='visible-sm visible-xs'>");
		echo("<div class='timeline timeline_preview'>");
		$buyer_timeline_item->DisplayFull();
		echo("</div>");
		echo("</div>");

		$restorejscolor="UpdateWYSIWYG();";
		$restorejscolor.="ObjectFunctionAjax('agent','".$this->id."','EditSettings','EditSettingsContainer','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('default_colors')."',function(){".$savejscolor."});";
		if(!$this->Get('agent_colors_default'))
			echo("<div class='line'><a href='#' onclick=\"".$restorejscolor."return false;\">Reset to Default</a></div>");
		$this->CheckColors();
		echo("</div>");
		echo("<div class='col-md-4 hidden-sm hidden-xs'>");
		echo("<div class='timeline timeline_preview'>");
		$agent_timeline_item->DisplayFull();
		echo("</div>");
		echo("</div>");
		echo("<div class='col-md-4 hidden-sm hidden-xs'>");
		echo("<div class='timeline timeline_preview'>");
		$buyer_timeline_item->DisplayFull();
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");

		//to see it as we change it.
		echo("<div id='CustomCSSContainer'>");
		$this->CustomCSS();
		echo("</div>");
		
		echo("<div class='card_label'>Images</div>");
		echo("<div class='card_section account_images' data-info='SETTINGS_IMAGES' data-info-none='none'>");
		echo("<div class='row'>");
/*
		echo("<div class='col-sm-4' data-info='SETTINGS_PRIMARY_LOGO' data-info-none='none'>");
		echo("<div class='card_label2'>Primary Logo</div>");
		echo("<div class='line drop_target' data-target='".$this->GetFieldName('agent_image_file1_ul')."'>");
		$th=_navigation::GetBaseURL().'/images/placeholder.png';
		if($this->Get('agent_image_file1'))
			$th=$this->GetThumb(225,100,false,'agent_image_file1');
		list($width, $height) = getimagesize(str_replace(_navigation::GetBaseURL(),_navigation::GetBasePath(),$th));
		echo("<div style='height:225px;padding-top:".((225-$height)/2)."px;text-align:center;'><img src='".$th."'></div>");
		form::DrawFileInput($this->GetFieldName('agent_image_file1_ul'),'',array('placeholder'=>'Upload Primary Logo','onchange'=>$savejs2));
		echo("</div>");
		echo("</div>");
*/
		echo("<div class='col-sm-6' data-info='SETTINGS_COMPANY_LOGO' data-info-none='none'>");
		echo("<div class='line drop_target' data-target='".$this->GetFieldName('agent_image_file2_ul')."'>");
		echo("<div class='card_label2'>Company Logo</div>");
		$th=_navigation::GetBaseURL().'/images/placeholder.png';
		$th=$this->GetThumb(200,65,false,'agent_image_file2',true);
		list($width, $height) = getimagesize(str_replace(_navigation::GetBaseURL(),_navigation::GetBasePath(),$th));
		echo("<div style='height:225px;padding-top:".((225-$height)/2)."px;text-align:center;'><img src='".$th."' style='max-width:100%;max-height:100%;'></div>");
		echo("<div class='row'>");
		echo("<div class='col-sm-6' style='text-align:left'>");
		form::DrawFileInput($this->GetFieldName('agent_image_file2_ul'),'',array('placeholder'=>'Upload Company Logo','onchange'=>$savejs2));
		echo("</div>");
		echo("<div class='col-sm-6' style='text-align:right;padding:8px 0px;'>");
		$js="UpdateWYSIWYG();";
		$js.="ObjectFunctionAjax('agent','".$this->id."','EditSettings','EditSettingsContainer','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('remove_image_file2')."',function(){});";
		if($this->Get('agent_image_file2'))
			echo("<a href='#' onclick=\"".$js."return false;\">Remove Image</a>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("<div class='col-sm-6' data-info='SETTINGS_HEADSHOT' data-info-none='none'>");
		echo("<div class='card_label2'>Headshot</div>");
		echo("<div class='line drop_target' data-target='".$this->GetFieldName('agent_image_file3_ul')."'>");
		$th=$this->GetThumb(120,120,false,'agent_image_file3',true);
		list($width, $height) = getimagesize(str_replace(_navigation::GetBaseURL(),_navigation::GetBasePath(),$th));
		echo("<div style='height:225px;padding-top:".((225-$height)/2)."px;text-align:center;'><img src='".$th."' style='max-width:100%;max-height:100%;'></div>");
		echo("<div class='row'>");
		echo("<div class='col-sm-6' style='text-align:left'>");
		form::DrawFileInput($this->GetFieldName('agent_image_file3_ul'),'',array('placeholder'=>'Upload Headshot','onchange'=>$savejs2));
		echo("</div>");
		echo("<div class='col-sm-6' style='text-align:right;padding:8px 0px;'>");
		$js="UpdateWYSIWYG();";
		$js.="ObjectFunctionAjax('agent','".$this->id."','EditSettings','EditSettingsContainer','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('remove_image_file3')."',function(){});";
		if($this->Get('agent_image_file3'))
			echo("<a href='#' onclick=\"".$js."return false;\">Remove Image</a>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");	
		echo("</div>");	
		echo("</div>");	
		echo("</div>");	
		form::end();
		echo("</div>");	
	}

	public function CustomCSS($params=array())
	{
		if($this->Get('agent_colors_default'))
		{
			$coordinator=$this->GetDefaultCoordinator();
			if($coordinator)
			{
				$coordinator->CustomCSS();
				return;
			}
		}

		$this->GatherInputs();
		if($params['action']==$this->GetFormAction('default_colors'))
			$this->DefaultColors();

		echo("<style type='text/css'>");
		if($this->Get('agent_color1_hex'))
		{
			echo("
				.agent_color1{color:".$this->Get('agent_color1_hex')." !important}
				.agent_color1_hover:hover{color:".$this->Get('agent_color1_hex')." !important}
				.agent_bg_color1{background-color:".$this->Get('agent_color1_hex')." !important;color:".$this->Get('agent_color1_fg_hex')." !important;}
				.agent_bg_color1 *{color:".$this->Get('agent_color1_fg_hex')." !important;}
 				.agent_bg_color1_hover:hover{background-color:".$this->Get('agent_color1_hex')." !important;color:".$this->Get('agent_color1_fg_hex')." !important;}
				.agent_bg_color1_hover:hover *{color:".$this->Get('agent_color1_fg_hex')." !important;}
				.agent_bg_color1 TH{background-color:".$this->Get('agent_color1_hex')." !important;color:".$this->Get('agent_color1_fg_hex')." !important;}
				.agent_color1 .agent_color1{color:".$this->Get('agent_color1_hex')." !important;}
				.agent_border_color1{border-color:".$this->Get('agent_color1_hex')." !important}
				.agent_border-r_color1{border-right-color:".$this->Get('agent_color1_hex')." !important}
				.agent_border-l_color1{border-left-color:".$this->Get('agent_color1_hex')." !important}
				.agent_border-t_color1{border-top-color:".$this->Get('agent_color1_hex')." !important}
				.agent_border-b_color1{border-bottom-color:".$this->Get('agent_color1_hex')." !important}
				.choose_icon_OTHER I{background-color:".$this->Get('agent_color1_hex')." !important;color:".$this->Get('agent_color1_fg_hex')." !important;}
			");			
		}	
		if(colors::ColorTooLight($this->Get('agent_color1_hex')))
		{
			echo("
				.agent_color1{text-shadow:1px 1px 1px #000,-1px 1px 1px #000,-1px -1px 0 #000,1px -1px 0 #000}
				.agent_color1_hover:hover{text-shadow:1px 1px 1px #000,-1px 1px 1px #000,-1px -1px 0 #000,1px -1px 0 #000}
				.timeline_item_image .agent_bg_color1{background-color:".$this->Get('agent_color1_fg_hex')." !important;}
			");			
		}
		else
		{
			echo("
				.agent_color1{text-shadow:none;}
				.agent_color1_hover:hover{text-shadow:none;}
			");			
		}

		if($this->Get('agent_color2_hex'))
		{
			echo("				
				.agent_color2{color:".$this->Get('agent_color2_hex')." !important}
				.agent_color2_hover:hover{color:".$this->Get('agent_color2_hex')." !important}
				.agent_bg_color2{background-color:".$this->Get('agent_color2_hex')." !important;color:".$this->Get('agent_color2_fg_hex')." !important;}
				.agent_bg_color2 *{color:".$this->Get('agent_color2_fg_hex')." !important;}
 				.agent_bg_color2_hover:hover{background-color:".$this->Get('agent_color2_hex')." !important;color:".$this->Get('agent_color2_fg_hex')." !important;}
 				.agent_bg_color2_hover:hover *{color:".$this->Get('agent_color2_fg_hex')." !important;}
 				.agent_bg_color2 TH{background-color:".$this->Get('agent_color2_hex')." !important;color:".$this->Get('agent_color2_fg_hex')." !important;}
				.agent_color2 .agent_color2{color:".$this->Get('agent_color2_hex')." !important;}
				.agent_border_color2{border-color:".$this->Get('agent_color2_hex')." !important}
				.agent_border-r_color2{border-right-color:".$this->Get('agent_color2_hex')." !important}
				.agent_border-l_color2{border-left-color:".$this->Get('agent_color2_hex')." !important}
				.agent_border-t_color2{border-top-color:".$this->Get('agent_color2_hex')." !important}
				.agent_border-b_color2{border-bottom-color:".$this->Get('agent_color2_hex')." !important}
				.choose_icon_USER I{background-color:".$this->Get('agent_color2_hex')." !important;color:".$this->Get('agent_color2_fg_hex')." !important;}
			");			
		}	
		if(colors::ColorTooLight($this->Get('agent_color2_hex')))
		{
			echo("
				.agent_color2{text-shadow:1px 1px 1px #000,-1px 1px 1px #000,-1px -1px 0 #000,1px -1px 0 #000}
				.agent_color2_hover:hover{text-shadow:1px 1px 1px #000,-1px 1px 1px #000,-1px -1px 0 #000,1px -1px 0 #000}
				.timeline_item_image .agent_bg_color2{background-color:".$this->Get('agent_color2_fg_hex')." !important;}
			");			
		}
		else
		{
			echo("
				.agent_color2{text-shadow:none;}
				.agent_color2_hover:hover{text-shadow:none;}
			");			
		}
		
		//color picker overrides
		echo("
			.agent_preview_color1_hex :not(.minicolors-focus) .minicolors-swatch-color{background-color:".$this->Get('agent_color1_hex')." !important;}
			.agent_preview_color1_fg_hex :not(.minicolors-focus) .minicolors-swatch-color{background-color:".$this->Get('agent_color1_fg_hex')." !important;}
			.agent_preview_color2_hex :not(.minicolors-focus) .minicolors-swatch-color{background-color:".$this->Get('agent_color2_hex')." !important;}
			.agent_preview_color2_fg_hex :not(.minicolors-focus) .minicolors-swatch-color{background-color:".$this->Get('agent_color2_fg_hex')." !important;}
		");
		
		echo("</style>");
	}
		
	public function X_EditSettings()
	{

		global $HTTP_GET_VARS;
		$this->ProcessAction();
		if($this->saved)
			_navigation::Redirect('?saved=1');

		if($HTTP_GET_VARS['saved'])
			echo("<div class='message'>Your Changes Have Been Saved</div>");
		else if(count($this->errors))
			echo("<div class='error'>".implode('<br>',$this->errors)."</div>");
		
		echo("<table class='listing'>");
		echo("<tr><th colspan='100'>Edit Profile</th></tr>");
		echo("<tr><td class='edit_wrapper'>");
		echo("<table class='edit_wrapper'><tr>");
		form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams(),$this->form_method,$this->file_upload);		
		$this->PreserveInputs();
		$this->EditForm();
		$this->SaveLink();
		form::End();
		echo("</tr></table>");		
		echo("</td></tr></table>");		
		
	}

	public function ViewTasks($params)
	{
		$this->ProcessAction();


		$pass_params=array();
		foreach($params['user_ids'] as $user_id)
			$pass_params[]="user_ids[]=".$user_id."";

		echo("<div class='agent_tools_buttons'>");				
		echo("<div class='row'>");				
		echo("<div class='col-md-4'>");				
		if(Session::Get($this->GetFieldName('condensed_view')))
		{
			$js="ObjectFunctionAjax('agent','".$this->id."','ViewTasks','timeline_container','NULL','','".implode('&',$pass_params)."&action=".$this->GetFormAction('toggle_condensed_view')."',function(){});return false;";
			Javascript::Begin();
			echo("jQuery('BODY').addClass('timeline_condensed');");
			echo("jQuery('.timeline_item_expanded').removeClass('timeline_item_expanded');");
			Javascript::End();
			echo("<a class='button ".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='' href='#' onclick=\"".$js."\"><span class='text'>Turn Off Condensed View<span></a>");
		}
		else
		{
			$js="ObjectFunctionAjax('agent','".$this->id."','ViewTasks','timeline_container','NULL','','".implode('&',$pass_params)."&action=".$this->GetFormAction('toggle_condensed_view')."',function(){});return false;";
			Javascript::Begin();
			echo("jQuery('BODY').removeClass('timeline_condensed');");
			echo("jQuery('.timeline_item_expanded').removeClass('timeline_item_expanded');");
			Javascript::End();
			echo("<a class='button ".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='' href='#' onclick=\"".$js."\"></i><span class='text'>Turn On Condensed View<span></a>");
		}
		echo("</div>");				
		echo("<div class='col-md-4'>");				
		if(Session::Get($this->GetFieldName('show_deleted')))
		{
			$js="ObjectFunctionAjax('agent','".$this->id."','ViewTasks','timeline_container','NULL','','".implode('&',$pass_params)."&action=".$this->GetFormAction('toggle_deleted_view')."',function(){".$js2."});return false;";
			echo("<a class='button ".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Hide Deleted Items<span></a>");
		}
		else
		{
			$js="ObjectFunctionAjax('agent','".$this->id."','ViewTasks','timeline_container','NULL','','".implode('&',$pass_params)."&action=".$this->GetFormAction('toggle_deleted_view')."',function(){".$js2."});return false;";
			echo("<a class='button ".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Show Deleted Items<span></a>");
		}
		echo("</div>");				
		echo("<div class='col-md-4'>");				
		if(Session::Get($this->GetFieldName('show_completed')))
		{
			$js="ObjectFunctionAjax('agent','".$this->id."','ViewTasks','timeline_container','NULL','','".implode('&',$pass_params)."&action=".$this->GetFormAction('toggle_commpleted_view')."',function(){".$js2."});return false;";
			echo("<a class='button ".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Hide Completed Items<span></a>");
		}
		else
		{
			$js="ObjectFunctionAjax('agent','".$this->id."','ViewTasks','timeline_container','NULL','','".implode('&',$pass_params)."&action=".$this->GetFormAction('toggle_commpleted_view')."',function(){".$js2."});return false;";
			echo("<a class='button ".$this->GetDarkerHoverClass()."' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Show Completed Items<span></a>");
		}

		echo("</div>");				
		echo("</div>");				
		echo("</div>");		
	
		$where=array('timeline_items.agent_id='.$this->id);
		$where[]="timeline_items.user_id IN(".implode(',',$params['user_ids']).")";
		if(!Session::Get($this->GetFieldName('show_deleted')))
			$where[]='timeline_item_active=1';
		if(!Session::Get($this->GetFieldName('show_completed')))
			$where[]='timeline_item_complete=0';
		$where[]="timeline_item_reference_date_type!='NONE'";
		//$where[]="user_under_contract!=0";


	  	$list=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_date');
	  	$list->join_tables='users';
	  	$list->join_where='users.user_id=timeline_items.user_id';
		$list->num_new=0;
	  	$list->Retrieve();
	  	$list->SetFlag('MULTIVIEW');
	  	$list->SetFlag('AGENT');
		echo("<div class='timeline'>");
		foreach($list->items as $timeline_item)
		{
			echo("<div id='".$timeline_item->GetFieldName('AgentCardContainer')."'>");			
			$timeline_item->DisplayFull($params);
			echo("</div>");
		}
		echo("</div>");			
	}

	public function x__EditTimeline($params=array())
	{
	 	if(!$params['template_id'] and !$params['user_id'])
	 		_navigation::Redirect('index.php');
		$template=new template($params['template_id']);
		$user=new user($params['user_id']);
		$where=array('agent_id='.$this->id);
		$where[]="template_id='".$template->id."'";
		$where[]="user_id='".$user->id."'";
		//$where[]="timeline_item_active=1";
	
		$order='timeline_item_order';
		if($user->Get('user_under_contract'))
			$order="CASE WHEN timeline_item_reference_date_type='NONE' OR timeline_item_date<'1970-01-01' THEN timeline_item_order ELSE timeline_item_date END,timeline_item_order";

		$lists=array();
		$lists['DELETED']=array('where'=>'timeline_item_active=0','title'=>'Deleted Items');
		$lists['COMPLETED']=array('where'=>'timeline_item_complete>0','title'=>'Completed Items');
		$lists['ACTIVE']=array('where'=>'timeline_item_complete=0 AND timeline_item_active=1','title'=>'');
		echo("<div class='timeline'>");
		foreach($lists as $key=>$data)
		{
		  	$list=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where).' AND '.$data['where'],$order);
			$list->num_new=0;
		  	$list->Retrieve();
		  	$list->SetFlag('ALLOW_BLANK');
		  	$list->SetEachNew('timeline_item_order',0);	  	
		  	$list->SetEachNew('template_id',$template->id);
		  	$list->SetEachNew('agent_id',$this->id);
		  	$list->SetEachNew('user_id',$user->id);
	  		$list->ProcessAction();
			$list->CheckSortOrder('timeline_item_order');
		  	$list->SetEachNew('timeline_item_order',0);	  	
		  	$list->SetEachNew('template_id',$template->id);
		  	$list->SetEachNew('agent_id',$this->id);
		  	$list->SetEachNew('user_id',$user->id);

			echo("<div class='timeline_".$key."'>");
			foreach($list->items as $timeline_item)
			{
				echo("<div id='".$timeline_item->GetFieldName('AgentCardContainer')."'>");			
				$timeline_item->AgentCard($params);
				echo("</div>");
			}
			
			if($key!='ACTIVE')// and count($list->items))
			{
				$js="jQuery('BODY').toggleClass('show_".$key."');";
			
				echo("<div class='timeline_item timeline_item_".$key." toggle_timeline_item_".$key."' onclick=\"".$js."\" data-info='TIMELINE_".$key."' data-info-none='none'>");
				echo("<div class='box_inner'>");
				echo('<div class="card_heading">');
				echo "<div class='timeline_item_heading'>";
				echo("<h3><i class='fa fa-plus'></i> ".$data['title']." (".count($list->items)." items)</h3>");
				echo('</div>');
				echo('</div>');
				echo('<div class="card_body">');
				echo('<div class="card_content">');
				echo("<br>");
				echo('</div>');
				echo('</div>');
				echo('</div>');
				echo('</div>');
			}
			echo('</div>');
		}
		echo("</div>");
	}

	public function ReminderSentConfirmation($params=array())
	{
	 	$user=new user($params['user_id']);

		$reminders=array();

		$today=new date();
		$where=array("user_id='".$user->id."' AND timeline_item_active=1");
		$where[]="timeline_item_for IN('USER')";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete=0";
//		$where[]="(timeline_item_date>='".$today->GetDBDate()."' OR timeline_item_reference_date='NONE')";
		$order='timeline_item_order';
		if($user->Get('user_under_contract'))
			$order='timeline_item_date,timeline_item_order';
	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order);
		$timeline_items->Retrieve();
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		
		//we only want one.
		if(count($timeline_items->items))
			$timeline_items->items=array($timeline_items->items[0]);
		
		foreach($timeline_items->items as $timeline_item)
		{
			$reminders[]=$timeline_item->Get('timeline_item_title');
		}
			
		echo("<h3 class='agent_color1'>Reminder Sent</h3>");
		echo("<div class='line'><b>".implode(',',$reminders)."</b> ".((count($reminders)>1)?'have':'has')." been sent to <b>".$user->Get('user_name')."</b></div>");	
		echo("<div class='line'>");
		echo("<a href='#' class='button agent_bg_color1' onclick=\"PopupClose();return false;\">Close</a>");
		echo("</div>");
	}


	public function EditForm()
	{
		global $HTTP_POST_VARS;

		$settings=json_decode($this->Get('agent_settings'),true);
	
		echo("<td colspan='3' align='center'></td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Name</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('agent_name'),$this->Get('agent_name'),array('class'=>$this->GetError('agent_name')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Email</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('agent_email'),$this->Get('agent_email'),array('class'=>$this->GetError('agent_email')?'error':'text'));
		echo("</td></tr>");
		if(!$this->id)
		{
			echo("<tr><td class='label'>".REQUIRED." Password</td><td colspan='2'>");
			form::DrawTextInput($this->GetFieldName('agent_password_new'),$HTTP_POST_VARS[$this->GetFieldName('agent_password_new')],array('class'=>$this->GetError('agent_password_new')?'error':'text'));
			echo("</td></tr>");
		}
		else
		{
			echo("<tr><td class='label'>Password</td><td colspan='2'>*******</td></tr>");
			echo("<tr><td class='label'>Change Password</td><td colspan='2'>");
			form::DrawTextInput($this->GetFieldName('agent_password_new'),$HTTP_POST_VARS[$this->GetFieldName('agent_password_new')],array('class'=>$this->GetError('agent_password_new')?'error':'text'));
			echo("</td></tr>");
		}
		echo("<tr><td class='label'>".REQUIRED." Cell Phone</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('agent_cellphone'),$this->Get('agent_cellphone'),array('class'=>$this->GetError('agent_cellphone')?'error':'text'));
		echo("</td></tr>");

		echo("<tr><td class='label'>".REQUIRED." Notifications</td><td style='text-align:left' colspan='2'>");
		echo("<label>");
		form::DrawCheckbox('X_'.'notifications[phone]',$settings['notifications']['phone'],$settings['notifications']['phone'],array('disabled'=>'disabled'));
		form::DrawHiddenInput('notifications[phone]',$settings['notifications']['phone']);
		echo(" SMS</label>");
		echo("<label>");
		form::DrawCheckbox('notifications[email]',1,$settings['notifications']['email']);
		echo(" Email</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox('notifications[user]',1,$settings['notifications']['user'],array('onchange'=>$savejs2));
		echo(" Receive reminders for client items</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox('notifications[other]',1,$settings['notifications']['other'],array('onchange'=>$savejs2));
		echo(" Receive reminders for agent/other items</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox('notifications[agent]',1,$settings['notifications']['agent'],array('onchange'=>$savejs2));
		echo(" Receive reminders for agent-only items</label>");
		echo("<br>");
		echo("</td></tr>");


		echo("<tr><td class='section' colspan='3'>Footer</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Full Name</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('agent_fullname'),$this->Get('agent_fullname'),array('class'=>$this->GetError('agent_fullname')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Company</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('agent_company'),$this->Get('agent_company'),array('class'=>$this->GetError('agent_company')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Agent Number</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('agent_number'),$this->Get('agent_number'),array('class'=>$this->GetError('agent_number')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Phone</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('agent_phone'),$this->Get('agent_phone'),array('class'=>$this->GetError('agent_phone')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Address</td><td colspan='2'>");
		form::DrawTextArea($this->GetFieldName('agent_address'),$this->Get('agent_address'),array('class'=>$this->GetError('agent_address')?'error':'text'));
		echo("</td></tr>");


		echo("<tr><td class='section' colspan='3'>Company/Brand Colors</td></tr>");
		echo("<tr><td class='label' id='".$this->GetFieldName('CustomCSSContainer')."'>");
		$this->CustomCSS();
		echo("</td>");
		echo("<td id='".$this->GetFieldName('ChooseColorsContainer')."'>");	
		$this->ChooseColors();
		echo("</td>");
		echo("<td id='".$this->GetFieldName('PreviewColorsContainer')."'>");
//		$this->PreviewColors();
		echo("</td></tr>");


		echo("<tr><td class='section' colspan='3'>Images</td></tr>");
/*
		if($this->Get('agent_image_file1'))
		{
			echo("<tr><td class='label'>Primary Logo</td><td>");
			echo("<img src='".$this->GetThumb(225,100,false,'agent_image_file1')."'>");
			echo("</td></tr>");
		}		
		echo("<tr><td class='label'>Upload Primary Logo</td><td><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('agent_image_file1_ul'),'',array('class'=>$this->GetError('agent_image_file1')?'error':'file'));
		echo("</td></tr>");	
*/
		if($this->Get('agent_image_file2'))
		{
			echo("<tr><td class='label'>Company Logo</td><td colspan='2'>");
			echo("<img src='".$this->GetThumb(120,120,false,'agent_image_file2')."'>");
			echo("</td></tr>");
		}		
		echo("<tr><td class='label'>Upload Company Logo</td><td colspan='2'><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('agent_image_file2_ul'),'',array('class'=>$this->GetError('agent_image_file2')?'error':'file'));
		echo("</td></tr>");	
		if($this->Get('agent_image_file3'))
		{
			echo("<tr><td class='label'>Headshot</td><td colspan='2'>");
			echo("<img src='".$this->GetThumb(200,65,false,'agent_image_file3')."'>");
			echo("</td></tr>");
		}		
		echo("<tr><td class='label'>Upload Headshot</td><td colspan='2'><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('agent_image_file3_ul'),'',array('class'=>$this->GetError('agent_image_file3')?'error':'file'));
		echo("</td></tr>");	
		


		echo("<tr><td colspan='3' class='save_actions'>");		
 	}

	//edit / create display
	public function Edit()
	{
		form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams(),$this->form_method,$this->file_upload,array('id'=>$this->GetFieldName('edit_form')));		
		$this->PreserveInputs();
		$this->EditForm();
		$this->SaveLink();
		form::End();
		$this->CancelLink();	  
	}


	public function ChooseColors($params=array())
	{
		$savejscolor="ObjectFunctionAjax('agent','".$this->id."','CustomCSS','".$this->GetFieldName('CustomCSSContainer')."','".$this->GetFieldName('edit_form')."','','',function(){});";

	 	if($params['action']==$this->GetFormAction('default_colors'))
			$this->DefaultColors();


		echo("<div class='line agent_preview_color1_hex'>");
//		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color1_hex'),$this->Get('agent_color1_hex'),array('placeholder'=>'Color 1','onchange'=>$savejscolor));
//		echo("</div>");
		echo("</div>");
		echo("<br><br>");
		echo("<div class='line agent_preview_color1_fg_hex'>");
//		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color1_fg_hex'),$this->Get('agent_color1_fg_hex'),array('placeholder'=>'Color 1 Header','onchange'=>$savejscolor));
//		echo("</div>");
		echo("</div>");
		echo("<br><br>");
		echo("<div class='line agent_preview_color2_hex'>");
//		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color2_hex'),$this->Get('agent_color2_hex'),array('placeholder'=>'Color 2','onchange'=>$savejscolor));
//		echo("</div>");
		echo("</div>");
		echo("<br><br>");
		echo("<div class='line agent_preview_color2_fg_hex'>");
//		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color2_fg_hex'),$this->Get('agent_color2_fg_hex'),array('placeholder'=>'Color 2 Header','onchange'=>$savejscolor));
//		echo("</div>");
		echo("</div>");
		echo("<br><br>");
		$restorejscolor2="ObjectFunctionAjax('agent','".$this->id."','ChooseColors','".$this->GetFieldName('ChooseColorsContainer')."','".$this->GetFieldName('edit_form')."','','action=".$this->GetFormAction('default_colors')."',function(){});";
		$restorejscolor="ObjectFunctionAjax('agent','".$this->id."','CustomCSS','".$this->GetFieldName('CustomCSSContainer')."','".$this->GetFieldName('edit_form')."','','action=".$this->GetFormAction('default_colors')."',function(){".$restorejscolor2."});";
//		echo("<div class='line'><a href='#' onclick=\"".$restorejscolor."return false;\">Reset to Default</a></div>");
	}

	public function CheckColors()
	{
		$luma1=colors::luma($this->Get('agent_color1_hex'));
		$luma2=colors::luma($this->Get('agent_color2_hex'));

		//$max=.85;
		//if($luma1>$max and $luma2>$max)
		//	echo("<div class='error'>Note: It is not recommended to use two bright colors for background colors</div>");		
	}

	public function GetDarkerHoverClass()
	{			
		$colors=array();
		$colors['agent_color1_hover']=$this->Get('agent_color1_hex');
		$colors['agent_color2_hover']=$this->Get('agent_color2_hex');
				
		return colors::GetDarkest($colors);
	}


	public function PreviewColors($params=array())
	{
		$this->CheckColors();
		
		echo("<div class='row'>");
		echo("<div class='col-md-6'>");
		$acceptance_date=new contract_date();
		$acceptance_date->InitByKeys('contract_date_special','ACCEPTANCE');
		$timeline_item=new timeline_item();
		$timeline_item->Set('timeline_item_title','Sample Agent Timeline Item');
		$timeline_item->Set('timeline_item_summary','This is a preview of how agent timeline items will be displayed with the seleted colors');
		$timeline_item->Set('timeline_item_for','AGENT');
		$timeline_item->Set('timeline_item_reference_date',$acceptance_date->id);
		$timeline_item->Set('timeline_item_reference_date_days',2);
		$timeline_item->Set('timeline_item_url',_navigation::GetBaseURL());
		$timeline_item->SetFlag('AGENT');
		$timeline_item->SetFlag('PREVIEW');
		echo("<div class='timeline timeline_preview'>");
		$timeline_item->DisplayFull();
		echo("</div>");
		echo("</div>");
		echo("<div class='col-md-6'>");
		$acceptance_date=new contract_date();
		$acceptance_date->InitByKeys('contract_date_special','ACCEPTANCE');
		$timeline_item=new timeline_item();
		$timeline_item->Set('timeline_item_title','Sample Buyer/Seller Timeline Item');
		$timeline_item->Set('timeline_item_summary','This is a preview of how the buyer/seller timeline items will be displayed with the seleted colors');
		$timeline_item->Set('timeline_item_for','USER');
		$timeline_item->Set('timeline_item_reference_date',$acceptance_date->id);
		$timeline_item->Set('timeline_item_reference_date_days',5);
		$timeline_item->Set('timeline_item_url',_navigation::GetBaseURL());
		$timeline_item->SetFlag('AGENT');
		$timeline_item->SetFlag('PREVIEW');
		echo("<div class='timeline timeline_preview'>");
		$timeline_item->DisplayFull();
		echo("</div>");
		echo("</div>");
		
	}

	public function GatherInputs()
	{
		global $HTTP_POST_VARS;

		if($HTTP_POST_VARS['REGISTER'])
			$this->SetFlag('REGISTER');
		
		//parent is default
		parent::GatherInputs();

		//$this->Set('agent_cellphone',$this->NormalizePhone($this->Get('agent_cellphone')));

		$settings=json_decode($this->Get('agent_settings'),true);
		if($HTTP_POST_VARS['notifications'])
			$settings['notifications']=$HTTP_POST_VARS['notifications'];
		$this->Set('agent_settings',json_encode($settings));

		$this->GatherFile($this->GetFieldName('agent_image_file1_ul'),'agent_image_file1');		
		$this->GatherFile($this->GetFieldName('agent_image_file2_ul'),'agent_image_file2');		
		$this->GatherFile($this->GetFieldName('agent_image_file3_ul'),'agent_image_file3');		

		//if(!$this->id)
		//	$this->Set('agent_password',text::GenerateCode(10,10));
			
		if($this->GetFlag('REGISTER'))
		{
			$this->Set('agent_cctype',$HTTP_POST_VARS['cctype']);
			$this->Set('agent_ccexp',$HTTP_POST_VARS['ccexp']);
			$this->Set('agent_ccccv',$HTTP_POST_VARS['ccccv']);
			$this->Set('agent_cclast4',$HTTP_POST_VARS['cclast4']);
		}
	}

	public function ValidateInputs()
	{
		global $HTTP_POST_VARS;

		if(!$this->Get('agent_name') and $HTTP_POST_VARS[$this->GetFieldName('agent_name_required')])
			$this->LogError('Please Enter Name','agent_name');

		if(!$this->Get('agent_email'))
			$this->LogError('Please Enter Email','agent_email');
		else if(!email::ValidateEmail($this->Get('agent_email'),false))
			$this->LogError('Email Address Does Not Appear To Be Valid','agent_email');
		else if(!$this->ValidateUnique('agent_email'))
			$this->LogError('An Account Already Exists For This Email Address.  Please Login.','agent_email');
		else if($HTTP_POST_VARS[$this->GetFieldName('email_verify')])
		{
			if(!$HTTP_POST_VARS[$this->GetFieldName('agent_email2')])  
				$this->LogError('Please Re-Enter Email','agent_email2');
			else if($HTTP_POST_VARS[$this->GetFieldName('agent_email2')]!=$this->Get('agent_email'))  
				$this->LogError('Email Entries Do Not Match','agent_email2');
		}	

		$newpwd=$HTTP_POST_VARS[$this->GetFieldName('agent_password_new')];			
		$newpwd2=$HTTP_POST_VARS[$this->GetFieldName('agent_password_new2')];			
//		if(!$this->Get('agent_password') and !$newpwd)
//			$this->LogError('Please Enter Password','agent_password_new');
//		else 
		if($newpwd)
		{
			//if(strlen($newpwd)<8)
			//	$this->LogError('Password must be at least 8 characters','agent_password_new');
			//else if(!preg_match("#[0-9]+#",$newpwd) or !preg_match("#[a-z]+#",$newpwd) or !preg_match("#[A-Z]+#",$newpwd) or !preg_match("#\W+#",$newpwd))
			//	$this->LogError('Password must include at least one uppercase letter, one lowercase letter, one number and one symbol','agent_password_new');
			if($HTTP_POST_VARS[$this->GetFieldName('password_verify')])
			{
				if(!$newpwd2)  
					$this->LogError('Please Re-Enter Password','agent_password_new2');
				else if($newpwd!=$newpwd2)  
					$this->LogError('Passwords Do Not Match','agent_password_new2');
			}	
		}
				
		if(!count($this->errors) and $newpwd)
		{
			$this->Set('agent_password',md5($newpwd)); 
		}

		if($HTTP_POST_VARS['discount_code'])
		{
			$discount_code=new discount_code();
			$discount_code->InitByKeys('discount_code_code',$HTTP_POST_VARS['discount_code']);
			if(!$discount_code->id)
				$this->LogError('Discount Code \''.$HTTP_POST_VARS['discount_code'].'\' Not Found','discount_code');
			$this->Set('discount_code_id',$discount_code->id);
		}
		
		if($this->GetFlag('REGISTER'))
		{
			//if(!$this->Get('STRIPE_CUSTOMER_ID'))
			//	$this->LogError('Please enter card information','STRIPE_CUSTOMER_ID');
		}

		return count($this->errors)==0;
 	}

	public function Save()
	{	  	  
		global $HTTP_POST_VARS;
		
	  	$new=!$this->id;
		$old=new agent($this->id);
		$psv=parent::Save();
		if($psv)
		{
			$this->SaveImageFile('agent_image_file1',file::GetPath('agent_upload'),$this->id);
			$this->SaveImageFile('agent_image_file2',file::GetPath('agent_upload'),$this->id);
			$this->SaveImageFile('agent_image_file3',file::GetPath('agent_upload'),$this->id);

			$this->Set('agent_reset_code','');
			$this->Update();
			
			if($new)
			{
				//DO NOT! get all the default templates.
				//template::CopyAll(array('agent_id'=>0),array('agent_id'=>$this->id));
			}
			if($new)
			{
				//setup their WYSIWYG folder if needed..
				$paths=array();
				$paths[]="uploads/agents/".$this->id."/files";
				$paths[]="uploads/agents/".$this->id."/pics";
				foreach($paths as $path)
				{
				 	$fullpath=_navigation::GetBasePath();
				 	$pathparts=explode('/',$path);
					foreach($pathparts as $pathpart)
					{
						$fullpath.=$pathpart.'/';
						if(!is_dir($fullpath))
							mkdir($fullpath,0755);
					}
					
				}
			}	
			$this->saved=true;		
			
			if($new)
				activity_log::Log($this,'AGENT_CREATED','Account Created');
			else
				activity_log::Log($this,'AGENT_UPDATES','Account Updated');

			$settings=json_decode($this->Get('agent_settings'),true);
			$old_settings=json_decode($old->Get('agent_settings'),true);
			if($settings['notifications']['phone'] and !$old_settings['notifications']['phone'])
			 	activity_log::Log($this,'SMS_ENABLED','Phone notifications enabled',$this->Get('user_id'));			
			if(!$settings['notifications']['phone'] and $old_settings['notifications']['phone'])
			 	activity_log::Log($this,'SMS_DISABLED','Phone notifications disabled',$this->Get('user_id'));			

			
			
		}
		return count($this->errors)==0;
	}

	public function Delete()
	{
		$this->Set('agent_active',0);
		$this->Update();
		
	}

	public function xDelete()
	{
		parent::Delete();
	}


	public function IsLoggedIn()
	{
		return(Session::Get('pbt_agent_login') and Session::Get('agent_id'));	  	  
	}

	public function ResetPassword($redir='',$requirecode=true)
	{
	  	global $HTTP_POST_VARS,$HTTP_GET_VARS;
	  	foreach($HTTP_GET_VARS as $k=>$v)
	  		$$k=$v;
	  	foreach($HTTP_POST_VARS as $k=>$v)
	  		$$k=$v;

		echo '<div class="login_form card">';
		echo("<div class='card_heading'><h3>Reset Password</h3></div>");
		echo '<div class="card_body">';
		if($this->Get('agent_reset_code') or !$requirecode)
		{
			if($action=='send_pwd' and $this->msg)
				echo('<div class="message">Please check your email for details on resetting your password.</div>');
			foreach($this->GetErrors() as $e)
				echo('<div class="error">'.$e.'</div>');
			form::begin('?action='.$this->GetFormAction('save').$this->GetFormExtraParams(),'POST',false,array('id'=>'login'));
			form::DrawHiddenInput($this->GetFieldName('password_reset'),1);
			form::DrawInput('password',$this->GetFieldName('agent_password_new'),$HTTP_POST_VARS[$this->GetFieldName('agent_password_new')],array('class'=>'text password','placeholder'=>'Enter New Password'));
			form::DrawHiddenInput($this->GetFieldName('password_verify'),1);
			form::DrawInput('password',$this->GetFieldName('agent_password_new2'),$HTTP_POST_VARS[$this->GetFieldName('agent_password_new2')],array('class'=>'text password','placeholder'=>'Re-Enter New Password'));
			form::DrawSubmit('','Reset Password');
			form::End();

		}
		else if(!$this->id)
		{
			echo('<div class="error">Rest Code Not Found</div>');
		}
		else
		{
			echo('<div class="message">Your Password Has Been Reset.</div>');
			echo("<div><a href='/agents/'>Login</a></div>");
//			$this->LogIn();
//			if($this->IsLoggedIn())
//				_navigation::Redirect(_navigation::GetBaseURL().'tasks.php');
		}
		echo '</div>';
		echo '</div>';
	}

	public function LoginForm($redir='')
	{
	  	global $HTTP_POST_VARS,$HTTP_GET_VARS;
	  	foreach($HTTP_GET_VARS as $k=>$v)
	  		$$k=$v;
	  	foreach($HTTP_POST_VARS as $k=>$v)
	  		$$k=$v;

		if($this->IsLoggedIn())
		{
			echo "<span class='class'>Logged In As ".$this->Get('agent_name')."<br /></span>";
			echo '<br><span class="class"><a href="?action=logout" >Log Out</a></span>';
			return;
		}

		echo("<div id='login_div' style='display:".(($action!='send_pwd' or $this->msg)?'block':'none')."'>");
		echo("<div class='login_form card'>");
		echo("<div class='card_heading agent_bg_color2'><h3>Log In</h3></div>");
		echo("<div class='card_body'>");
		if($this->GetError('login'))
			echo('<div class="error">Wrong email or password.</div>');
		if($action=='send_pwd' and $this->msg)
			echo('<div class="error">Please check your email for details on resetting your password.</div>');
		form::begin('?action=login'.$this->GetFormExtraParams(),'POST',false,array('id'=>'login'));
		form::DrawTextInput('agent_email',$HTTP_POST_VARS['agent_email'],array('placeholder'=>'Email Address'));
		form::DrawInput('password','agent_password',$HTTP_POST_VARS['agent_password'],array('placeholder'=>'Password'));
		form::DrawSubmit('','Sign In');
		form::End();
		echo '<a href="#" onclick="document.getElementById(\'forgot-password\').style.display=\'block\';document.getElementById(\'login_div\').style.display=\'none\';return false;">Forget your password?</a>';
		echo('</div>');
		echo('</div>');
		echo "<div style='margin:20px;text-align:center;'>";
		echo "<a href='/agents/register.php'>New to What's Next?  Register Now!</a>";
		echo('</div>');
		echo('</div>');

		echo("<div id='forgot-password' style='display:".(($action=='send_pwd' and  !$this->msg)?'block':'none')."'>");
		echo("<div class='login_form card'>");
		echo("<div class='card_heading agent_bg_color2'><h3>Reset Password</h3></div>");
		echo("<div class='card_body'>");
		if($action=='send_pwd' and $this->GetError('send_pwd'))
			echo('<div class="error">Email Not Found.</div>');
		form::Begin('?action=send_pwd','POST',false,array('class'=>"forgot-password"));
		form::DrawTextInput('agent_email',$HTTP_POST_VARS['agent_email'],array('placeholder'=>'Email Address'));
		form::DrawSubmit('','Reset Password');
		form::End();
		echo('</div>');
		echo('</div>');

	}


	public function RegisterForm($redir='')
	{
	  	global $HTTP_POST_VARS,$HTTP_GET_VARS;
	  	foreach($HTTP_GET_VARS as $k=>$v)
	  		$$k=$v;
	  	foreach($HTTP_POST_VARS as $k=>$v)
	  		$$k=$v;

		if($this->IsLoggedIn())
		{
			echo("<div id='register_div'>");
			echo("<div class='login_form card'>");
			echo("<div class='card_heading agent_bg_color2'><h3>Signup Now!</h3></div>");
			echo("<div class='card_body'>");
			if($this->GetFlag('REGISTER'))
			{
				echo "<div class='message'>Congratulations you have been registered!</div>";
				echo "<div><a class='' href='/agents/' >Continue to What's Next....</a></div>";
				Javascript::Begin();
				echo("window.setTimeout(function(){window.top.location.href='/agents/';},5000);");
				Javascript::End();			
			}
			else
			{
				echo "<div class='class'>Logged In As ".$this->Get('agent_name')."</div>";
				echo '<div class="class"><a href="/agents/" >Continue</a></div>';
			}
			echo('</div>');
			echo('</div>');
			echo('</div>');
			return;
 		}

		echo("<div id='register_div'>");
		echo("<div class='login_form card'>");
		echo("<div class='card_heading agent_bg_color2'><h3>Signup Now!</h3></div>");
		echo("<div class='card_body'>");
		if(count($this->GetErrors()))
			echo('<div class="error">'.implode('<br>',$this->GetErrors()).'</div>');
		form::begin('?action=register'.$this->GetFormExtraParams(),'POST',false,array('id'=>'register'));
		form::DrawHiddenInput('REGISTER',1);
		form::DrawTextInput($this->GetFieldName('agent_name'),$this->Get('agent_name'),array('placeholder'=>'Your Name'));
		form::DrawTextInput($this->GetFieldName('agent_company'),$this->Get('agent_company'),array('placeholder'=>'Company/Brokerage'));
		form::DrawTextInput($this->GetFieldName('agent_email'),$this->Get('agent_email'),array('placeholder'=>'Email Address'));
		form::DrawTextInput($this->GetFieldName('agent_number'),$this->Get('agent_number'),array('placeholder'=>'Agent Number'));
		form::DrawTextInput($this->GetFieldName('agent_phone'),$this->Get('agent_phone'),array('placeholder'=>'Phone'));
		form::DrawTextInput($this->GetFieldName('agent_cellphone'),$this->Get('agent_cellphone'),array('placeholder'=>'Cell'));
		form::DrawTextInput('discount_code',$HTTP_POST_VARS['discount_code'],array('placeholder'=>'Promo Code (if any)'));
		form::DrawInput('password',$this->GetFieldName('agent_password'),$this->Get('agent_discount_code'),array('placeholder'=>'Password'));
		$ccparams=array();
		$ccparams['functionname']='ProcessCard';
		$ccparams['onsuccess']="$('#register').submit();";
		$ccparams['onerror'].="jQuery('BODY').removeClass('loading');";
		$this->CCEntry($ccparams);					
		//form::DrawSubmit('','Sign Up');
		echo("<a href='#' class='button' onclick=\"jQuery('#card-element').val('');jQuery('BODY').addClass('loading');ProcessCard(event);return false;\">Sign Up!</a>");
		form::End();
		echo('</div>');
		echo('</div>');
		echo('</div>');

		echo "<div style='margin:20px;text-align:center;'>";
		echo '<a href="/agents/" target="_top">Already have an account?  Login.</a>';
		echo('</div>');
	}

	function CCEntry($params=array())
	{
		Javascript::IncludeJS('https://js.stripe.com/v3/',array(),true);
		echo('<style>');
		echo(".error:empty{display:none;}");
		echo("#card-element{border: 1px solid #000000;padding: 5px;background: #FFFFFF;margin:5px 0px;}");
		echo('</style>');

		echo('<div id="card-element"></div>');
		echo('<div class="error" id="card-error"></div>');
		form::DrawHiddenInput('stripe_token_id','');
		form::DrawHiddenInput('cctype','');
		form::DrawHiddenInput('ccexp','');
		form::DrawHiddenInput('ccccv','');
		form::DrawHiddenInput('cclast4','');
		Javascript::Begin();
		echo("
			const stripe = Stripe('".STRIPE_PUBLIC_KEY."');
			const elements = stripe.elements();
			const card = elements.create('card');
			const style = {
			    base: {
			        border:'1px solid #000000',
			        fontFamily: 'Arial, sans-serif',
			        fontSize: '16px',
			        '::placeholder': {color: '#aab7c4'}
			    },
			    invalid: {
			        color: '#fa755a',
			        iconColor: '#fa755a'
			    }
			};
			card.mount('#card-element', { style: style });

			function ".$params['functionname']."(event)
			{
			    event.preventDefault(); // Prevent default form submission
			
				console.log('".$params['functionname']."');
			    // Create a Stripe token
			    stripe.createToken(card).then(function(result) {
					console.log('token');
			        if (result.error)
			        {
						console.log('error '+result.error.message);

						$('#card-error').text(result.error.message);
		                ".$params['onerror'].";
			            return false;
			        }
			        else
			        {
						console.log('success');
						
		                jQuery('#stripe_token_id').val(result.token.id);
		                jQuery('#cctype').val(result.token.card.brand);
		                jQuery('#ccexp').val(String(result.token.card.exp_month).padStart(2,'0')+'/'+result.token.card.exp_year);
		                jQuery('#ccccv').val('');
		                jQuery('#cclast4').val(result.token.card.last4);
		                ".$params['onsuccess'].";
						return true;
		            }
			    });
			}
		");
		Javascript::End();		
	}

	function UpdateCIM($params,$debug=false)
	{ 
		/* don't need the token; getting this from the outside world.
		if(!$params)
		{
			$params=array();
			$params['ccexp']='20'.substr($this->Get('cc_expires'),2,2).'-'.substr($this->Get('cc_expires'),0,2);
			$params['ccnum']=order::hashcc($this->Get('cc_number'),false);
			$params['ccccv']=$this->Get('cc_ccv');		  
		}
		$ccexp=explode('-',$params['ccexp_cim']);

		try 
		{
			$token=\Stripe\Token::create(array(
			  "card" => array(
			    "number" => $params['ccnum'],
			    "exp_month" => $ccexp[1],
			    "exp_year" => substr($ccexp[0],2,2),
			    "cvc" => $params['ccccv']
			  )
			));
		}
		catch(\Stripe\Error\InvalidRequest\RateLimit $e)
		{
			$this->LogError('1:'.$e->getMessage(),'CIM');
			return false;
		}
		catch(\Stripe\Error\InvalidRequest $e)
		{
			$this->LogError('2:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error\Authentication $e)
		{
			$this->LogError('3:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error\Card $e)
		{
			$this->LogError('4:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error\Api $e)
		{
			$this->LogError('5:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error\Base $e)
		{
			$this->LogError('6:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error $e)
		{
			$this->LogError('7:'.$e->getMessage(),'CIM');		
			return false;
		}		
		*/
		try
		{
			$customer = \Stripe\Customer::create(array("source" => $params['token_id'],"description" => $this->Get('agent_name'),"email"=>$this->Get('agent_email')));
			$this->Set('STRIPE_CUSTOMER_ID',$customer->id);	
			if(!$params['NO_UPDATE'])
				$this->Update();			

		}
		catch(\Stripe\Error\InvalidRequest\RateLimit $e)
		{
			$this->LogError('8:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error\InvalidRequest $e)
		{
			$this->LogError('9:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error\Authentication $e)
		{
			$this->LogError('10:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error\Card $e)
		{
			$this->LogError('11:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error\Api $e)
		{
			$this->LogError('12:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error\Base $e)
		{
			$this->LogError('13:'.$e->getMessage(),'CIM');		
			return false;
		}
		catch(\Stripe\Error $e)
		{
			$this->LogError('14:'.$e->getMessage(),'CIM');		
			return false;
		}		
		return true;
	}	

	function ChargeCIM($params)
	{
	 	if($params['payment_for'])			$for=$params['payment_for'];
	 	else if($this->Get('orders_id'))	$for=$this->Get('orders_id');
		else if($this->Get('cc_owner'))		$for=$this->Get('cc_owner');


		$params['amount']*=100;
		if($params['amount']<=0)
			return true;
		if(!$this->Get('STRIPE_CUSTOMER_ID'))
			return false;
		try 
		{
			$charge = \Stripe\Charge::create(array("amount" => $params['amount'],
													"currency" => "usd",
												    "customer" => $this->Get('STRIPE_CUSTOMER_ID'),
												    "description" => $for));
		} 
		catch(\Stripe\Error\InvalidRequest\RateLimit $e)
		{
		 	$this->LogError('15:'.$e->getMessage(),'CIM');
			return false;
		}
		catch(\Stripe\Error\InvalidRequest $e)
		{
		 	$this->LogError('16:'.$e->getMessage(),'CIM');
			return false;
		}
		catch(\Stripe\Error\Authentication $e)
		{
		 	$this->LogError('17:'.$e->getMessage(),'CIM');
			return false;
		}
		catch(\Stripe\Error\Card $e)
		{
		 	$this->LogError('18:'.$e->getMessage(),'CIM');
			return false;
		}
		catch(\Stripe\Error\Api $e)
		{
		 	$this->LogError('19:'.$e->getMessage(),'CIM');
			return false;
		}
		catch(\Stripe\Error\Base $e)
		{
		 	$this->LogError('20:'.$e->getMessage(),'CIM');
			return false;
		}
		catch(Exception $e) 
		{
		 	$this->LogError('21:'.$e->getMessage(),'CIM');
			return false;
		}
		return $charge->id;		
	}

	function VoidCIM($params)
	{
		return $this->ChargeCIM($params,'Void');
	}

	function RefundCIM($params)
	{
		die('need to fix this.');
		$res=$this->ChargeCIM($params,'Refund');
		if(!$res)
		{
		 	$e=$this->GetError('CIM');
			$this->ClearErrors();
			$res=$this->ChargeCIM($params,'Void');
		}
		return $res;
	}

	public function Register()
	{
		global $HTTP_POST_VARS;

		$this->GatherInputs();
		$this->ValidateInputs();
		if(!count($this->errors))
		{
			$this->UpdateCIM(array('token_id'=>$HTTP_POST_VARS['stripe_token_id']));

			//no....
			//$res=$this->ChargeCIM(array('amount'=>1.00,'for'=>'test'));
			//$this->RefundCIM($res);//needsd fixing....

			if(!count($this->errors))
			{
			 	$this->Update();
				$this->Login();
			}		
		}
	}
	
	public function Login($in=true,$silent=false)
	{
		parent::Login($in);

		Session::Set('pbt_agent_login',$in?1:0);
		Session::Set('agent_id',$in?$this->id:0);	  
		if($in)
		{
			$this->Set('agent_reset_code','');
			$this->Set('agent_last_login',time());
			$this->Update();
		}
		
		if(!$silent)
		{
			if($in)
				activity_log::Log($this,'LOGIN',$this->GetFullName().' Logged In');
			else
				activity_log::Log($this,'LOGOUT',$this->GetFullName().' Logged Out');
		}

		//clear out on logout
		if(!$in)
			$this->__construct();	
			
		if(!$in and $this->IsProxyLogIn())	
		{
			$coordinator=new coordinator(Session::Get('pbt_agent_proxy_login'));
			activity_log::Log($coordinator,'PROXY_LOGOUT',"Proxy Log Out");
			Session::Set('pbt_agent_proxy_login','');

			_navigation::Redirect($coordinator->ToURL());
		}
				
	}

	public function IsProxyLogIn()
	{
		return(Session::Get('pbt_agent_proxy_login'));	  	  
	}

	public function ProxyLogin($coordinator,$in=true)
	{
		if($in)
			activity_log::Log($coordinator,'PROXY_LOGIN',"Logged In As ".$this->GetFullName());

		Session::Set('pbt_agent_proxy_login',$in?$coordinator->id:'');
		$this->Login($in,true);
	}
	
	public function OptoutEmail($params=array())
	{
		$settings=json_decode($this->Get('agent_settings'),true);
		$settings['notifications']['email']=$params['email_notifications'];
		$this->Set('agent_settings',json_encode($settings));
		$this->Update();
		
		if($params['email_notifications'])
			echo("<div class='message'>Email Notificaitions have been re-enabled</div>");
		else
		{
			echo("<div class='message'>You will no loner receive Email Notificaitions</div>");
			echo("<div class='info'>Turned off notifications by accident? <a href='?email_notifications=true'>Click here to re-enable email notifications.</a></div>");
		}		
	}
	
	public function ProcessLogin($no_redir=false)
	{
	  	global $HTTP_POST_VARS,$HTTP_GET_VARS;
	  	foreach($HTTP_POST_VARS as $k=>$v)
	  		$$k=$v;
	  	foreach($HTTP_GET_VARS as $k=>$v)
	  		$$k=$v;

		if($action=='login')  
		{
			$rs=database::query("SELECT agent_id FROM agents WHERE agent_email='".$this->MakeDBSafe($agent_email)."' AND agent_password=''");		  
			if($rec=database::fetch_array($rs))
			{
			  	$date=new Date();
			  	$date->Add('1');
				$tempagent=new agent($rec['agent_id']);
				$tempagent->Set('agent_reset_code',Text::GenerateCode(30,40));
				$tempagent->Set('agent_reset_date',$date->GetDBDate());
				$tempagent->Update();
				_navigation::Redirect('reset.php?agent_reset_code='.$tempagent->Get('agent_reset_code'));
			}		  

			$rs=database::query("SELECT agent_id FROM agents WHERE agent_email='".$this->MakeDBSafe($agent_email)."' AND agent_password!='' AND agent_active=1 AND agent_password='".md5($agent_password)."'");		  
			if($rec=database::fetch_array($rs))		  
			{
				$this->__construct($rec['agent_id']);
				$this->Login();
				$this->msg="You have been logged in";
				if($redir)
					_navigation::Redirect($redir);
				else
					_navigation::Redirect('?action=loggedin');
			}
			else if($rec=database::fetch_array($rs2))		  
			{
				
			}
		  	else
		  	{
		  		$this->LogError("Account not found.",$action);
			}		  
		}
		if($action=='register')  
		{
			$this->Register();
			unset($HTTP_GET_VARS['action']);
		}
		if($action=='agent_link')  
		{
		 	$agent_link=new agent_link();
		 	$agent_link->InitByKeys('agent_link_hash',$HTTP_GET_VARS['agent_link_hash']);
		 	if(!$agent_link->id)
		 		$this->LogError('Plase Log In','login');
		 	else if($agent_link->Get('agent_link_expires')<time())
		 		$this->LogError('Link has expired.  Please Log In','login');
		 	else
		 	{
				$this->__construct($agent_link->Get('agent_id'));
				$this->Login();
				$this->msg="You have been logged in";
				if($redir)
					_navigation::Redirect($redir);
			 	else if($agent_link->Get('agent_link_page'))
					_navigation::Redirect($agent_link->Get('agent_link_page'));
				else
				{
					javascript::Begin();
					//echo("alert(window.location);");
					//echo("alert(window.location.hash);");
					Javascript::End();
					//_navigation::Redirect('?action=loggedin');
				}
			}		 		
		}	
		if($action=='login_as')  
		{
			$this->__construct($HTTP_GET_VARS['agent_id']);
			$this->Login();
			_navigation::Redirect('?action=redir');
		}
		if($action=='proxy_login')
		{
			$coordinator=new coordinator($HTTP_GET_VARS['coordinator_id']);			
			$this->__construct($HTTP_GET_VARS['agent_id']);			
			$check=$HTTP_GET_VARS['check'];			

			if($check=md5("proxy_login".$this->id.$agent->id))
				$this->ProxyLogin($coordinator);

		}
		if($action=='logout')  
		{
			$this->Login(false);
			//_navigation::Redirect('index.php');
			$this->msg="You have been logged out";
		}
		if($action=='send_pwd')  
		{
			$rs=database::query("SELECT * FROM agents WHERE agent_email='".$agent_email."'");		  
		  	if(!$agent_email)
		  		$this->LogError("Please Enter Email Address",$action);
			else if($rec=database::fetch_array($rs))		  
			{
			  	$date=new Date();
			  	$date->Add('1');
				$tempagent=new agent($rec['agent_id']);
				$tempagent->Set('agent_reset_code',Text::GenerateCode(30,40));
				$tempagent->Set('agent_reset_date',$date->GetDBDate());
				$tempagent->Update();
				email::templateMail($agent_email,email::GetEmail(),'Your Account',file::GetPath('email_agent_password'),$tempagent->attributes+array('base_url'=>_navigation::GetBaseURL()));
				$this->msg='You have been emailed a link to reset your password';
			}
		  	else if($rec=database::fetch_array($rs2))		  
		  	{
				$this->msg='You have been emailed a link to reset your password';
			}
		  	else
		  		$this->LogError("Email Address Not Found",$action);
		}	
		/*
			if($action==$this->GetFormAction('save'))
			{
				if($this->Save())
				{
					$this->Login();
					if($redir)
						_navigation::Redirect($redir);
				}
			}		
		*/
	}

 	/**THUMBNAILING**/
	public function GetThumb($width,$height,$crop=false,$which='agent_image_file2',$placeholder=false)
 	{ 	  
//		$src=$this->CropAsSaved(file::GetPath('user_display'),file::GetPath('user_upload'),$which,$width,$height);
		$src=$this->Get($which);
		if($src)
			return file::GetPath('agent_display').imaging::ResizeCached($src,file::GetPath('agent_upload'),$width,$height,$crop);

		$coordinator=$this->GetDefaultCoordinator();
		if($which=='agent_image_file2')
			$which='coordinator_image_file';		
		if($coordinator and $coordinator->Get($which))
			return $coordinator->GetThumb($width,$height,$crop,$which);

		if($placeholder)
			return _navigation::GetBaseURL().'/images/placeholder.png';
		

		return _navigation::GetBaseURL().'/images/spacer.gif';

	}	



	public function RecentActivityCheck($params=array())
	{
		$user=new user($params['user_id']);
		$where=array("timeline_item_active=1");
		$where[]="users.user_id!=0";
		$where[]="users.agent_id='".$this->id."'";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete>user_agent_last_view";
		if($params['user_id'])
			$where[]="users.user_id='".$params['user_id']."'";
		if($params['user_ids'] and !is_array($params['user_ids']))
			$params['user_ids']=array($params['user_ids']);
		if($params['user_ids'])
			$where[]="users.user_id IN ('".implode(',',$params['user_ids'])."')";
		else
			$params['user_ids']=array();

		$where[]="(timeline_item_completed_class!='".get_class($this)."' OR timeline_item_completed_id!='".$this->id."')";
	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_complete');
	  	$timeline_items->join_tables="users";
	  	$timeline_items->join_where="users.user_id=timeline_items.user_id";
echo("<div style='display:none'>".get_class($this)."[".$this->id."]::".$timeline_items->GetQuery()."</div>");

		if($timeline_items->GetTotalAvailable())
		{
			Javascript::Begin();	
			echo("jQuery(function(){
				ObjectFunctionAjaxPopup('Recent Updates','agent','".$this->id."','RecentActivityList','NULL','','user_id=".$params['user_id']."&user_ids=".implode(',',$params['user_ids'])."',function(){});
			});");
			Javascript::End();	
		}
		else
		{
		 	//a little hacky.... how to know we logged in AFTER we show the popup if there is somethign we missed.
			global $__VIEWIED_USER_ID__;
			if($__VIEWIED_USER_ID__)
			{
				$user=new user($__VIEWIED_USER_ID__);
				$user->Set('user_agent_last_view',time());
				$user->Update();
			}
		}
		
		
		
	}

	public function RecentActivityList($params=array())
	{
		$where=array("timeline_item_active=1");
		$where[]="users.user_id!=0";
		$where[]="users.agent_id='".$this->id."'";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete>user_agent_last_view";
		if($params['user_id'])
			$where[]="users.user_id='".$params['user_id']."'";
		if($params['user_ids'] and !is_array($params['user_ids']))
			$params['user_ids']=array($params['user_ids']);
		if($params['user_ids'])
			$where[]="users.user_id IN ('".$params['user_ids']."')";

		$where[]="(timeline_item_completed_class!='".get_class($this)."' OR timeline_item_completed_id!='".$this->id."')";
	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_complete');
	  	$timeline_items->join_tables="users";
	  	$timeline_items->join_where="users.user_id=timeline_items.user_id";
		if($timeline_items->GetTotalAvailable())
	 	 	$timeline_items->Retrieve();
echo("<div style='display:none'>".get_class($this)."[".$this->id."]::".$timeline_items->GetQuery()."</div>");

		echo("<div class='timeline_item_digest'>");
		echo("<h2>Recent Updates</h2>");
		$final_user_ids=array();
	  	foreach($timeline_items->items as $timeline_item)
	  	{
			$d=new date();
			$d->SetTimestamp($timeline_item->Get('timeline_item_complete'));
			$user=new user($timeline_item->Get('user_id'));
			echo("<div class='timeline_item_info'>");
			echo("<div class='timeline_item_info_date'>".$d->GetDate('F j:')."</div>");
			if(!$params['user_id'])
				echo("<a class='timeline_item_info_title' href='#".$timeline_item->GetFieldName('anchor')."'>".$user->GetFullName().': '.$timeline_item->Get('timeline_item_title')."</a>");
			else
				echo("<a class='timeline_item_info_title' href='#".$timeline_item->GetFieldName('anchor')."'>".$timeline_item->Get('timeline_item_title')."</a>");
			echo("<div class='timeline_item_info_label'>Marked Complete By</div>");
			echo("<div class='timeline_item_info_completed_by'>".$timeline_item->Get('timeline_item_completed_by')."</div>");
			echo("</div>");
			
			$final_user_ids[$user->id]=$user->id;
		}
		echo("</div>");
		
		foreach($final_user_ids as $user_id)
		{
			$user=new user($user_id);
			$user->Set('user_agent_last_view',time());
			$user->Update();
		}
	}

	public function SendNotifications()
	{
		$where=array("timeline_item_active=1");
		$where[]="agent_id=".$this->id;

		$where[]="timeline_item_type='TIMELINE'";
		$where[]="user_id!=0";
		$where[]="timeline_item_complete>timeline_item_notified";
		$where[]="(timeline_item_completed_class!='".get_class($this)."' OR timeline_item_completed_id!='".$this->id."')";



	  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'user_id,timeline_item_complete');
	  	$timeline_items->Retrieve();
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		

		if(count($timeline_items->items))		
		{
		 	if($settings['notifications']['email'])
		 	{
				$mail_params=array('base_url'=>_navigation::GetBaseURL());
				$mail_params+=$this->GetMailParams();
		
				$mail_params['users']=array();
			  	foreach($timeline_items->items as $i=>$timeline_item)
			  	{
					//if(!$timeline_item->DependsOnIncompleteItem())
					if(in_array($timeline_item->Get('timeline_item_for'),$this->GetNotificationTypesForUser($user)))
					{
						$user=new user($timeline_item->Get('user_id'));
						$d=new date();
						$d->SetTimestamp($timeline_item->Get('timeline_item_complete'));
						if(!is_array($mail_params['users'][$user->id]))
							$mail_params['users'][$user->id]=array();
						$mail_params['users'][$user->id]+=$user->attributes;
						if(!is_array($mail_params['users'][$user->id]['timeline_items']))
							$mail_params['users'][$user->id]['timeline_items']=array();
						$cnt=count($mail_params['users'][$user->id]['timeline_items']);
						$mail_params['users'][$user->id]['timeline_items'][$cnt]=array();
						$mail_params['users'][$user->id]['timeline_items'][$cnt]+=$timeline_item->attributes;
						$mail_params['users'][$user->id]['timeline_items'][$cnt]['date_completed']=$d->GetDate('F j');
						$mail_params['users'][$user->id]['timeline_items'][$cnt]['url']='#'.$timeline_item->GetFieldName('anchor');
						if(!$mail_params['users'][$user->id]['user_url'])
							$mail_params['users'][$user->id]['user_url']=$this->ToURL($this->GetUserURL($user).'#'.$timeline_item->GetFieldName('anchor'));
					}
				}
				$mail_params['opt_out_link']=$this->ToURL('optout.php');
		
				foreach($mail_params['users'] as $user_id=>$data)
				{
					echo('AGENT '.$this->Get('agent_name').' '.$data['user_address'].' : '.$this->Get('agent_email').' ('.count($data['timeline_items']).' Updates) - '.$data['user_url']);
					echo("<br>");
				}

				email::templateMail($this->Get('agent_email'),email::GetEmail(),"What's Next Real Estate Updates for ".$this->Get('agent_name'),file::GetPath('email_agent_notifications'),$mail_params);
			}

			if($settings['notifications']['phone'])
			{
				$phone=$this->TwillioFormat($this->Get('agent_cellphone'));
				$client = new \Twilio\Rest\Client(TWILLIO_SID,TWILLIO_KEY);
	
			 	$updates=array();
			  	foreach($timeline_items->items as $i=>$timeline_item)
			  	{
					//if(!$timeline_item->DependsOnIncompleteItem())
					if(in_array($timeline_item->Get('timeline_item_for'),$this->GetNotificationTypesForUser($user)))
					{
						$user=new user($timeline_item->Get('user_id'));
						$d=new date();
						$d->SetTimestamp($timeline_item->Get('timeline_item_complete'));
						if(!is_array($updates[$user->id]))
							$updates[$user->id]=array();
						$updates[$user->id][]=$timeline_item->Get('timeline_item_title').' Marked as completed by '.$timeline_item->Get('timeline_item_completed_by').' on '.$d->GetDate('F j');	
						if(!$user_url)
							$user_url=$this->ToURL($this->GetUserURL($user).'#'.$timeline_item->GetFieldName('anchor'));
					}
				}
				foreach($updates as $user_id=>$data)
				{
					try
					{
	
						$content=array();
						$content[]="What's Next Real Estate Updates for ".$user->GetFullName();
						$content[]="";
					  	foreach($data as $i=>$update)
					  	{
							if($i<3)
								$content[]=$update;
						}
						if(count($data)>3)
							$content[]='There are '.(count($data)-3).' additional items marked as compelte as well';

						$content[]="";
						$content[]="To opt out of text notifications, reply PAUSE to this message.";

						$res=$client->messages->create($phone,array('from'=>TWILLIO_NUMBER,'body'=>implode("\r\n",$content)));
						
						echo('AGENT '.$this->Get('agent_name').' '.$user->Get('user_address').' : '.$phone.' ('.count($data).' Updates)');
						echo("<br>");
						
					}
					catch(Exception $e)
					{
						echo($e->getMessage()."<br>\r\n");
					}				
					
					try
					{
						$content=$user_url;
						$res=$client->messages->create($phone,array('from'=>TWILLIO_NUMBER,'body'=>$content));
					}
					catch(Exception $e)
					{
						echo($e->getMessage()."<br>\r\n");
					}				
				}
			}		
		}		
	}
	
	public function SendEmailReminders($where=array())
	{
		$date=new date();
		$date->Add(7);

		$where[]="timeline_item_active=1";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete=0";
//		$where[]="(timeline_item_date>='".$today->GetDBDate()."' OR timeline_item_reference_date_type='NONE')";
		$where[]="(timeline_item_date<='".$date->GetDBDate()."' AND timeline_item_reference_date_type!='NONE')";
		$where[]="users.agent_id='".$this->id."'";
		$where[]="users.user_active='1'";


//		$order='timeline_item_order';
//		if($user->Get('user_under_contract'))
			$order='timeline_item_date,timeline_item_order';
		
	  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'users.user_id,'.$order);
		$timeline_items->join_tables='users';
		$timeline_items->join_where='users.user_id=timeline_items.user_id';
	  	$timeline_items->Retrieve();
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		

		if(count($timeline_items->items))		
		{
		 	if($settings['notifications']['email'])
		 	{
				$mail_params=array('base_url'=>_navigation::GetBaseURL());
				$mail_params+=$this->GetMailParams();
		
				$mail_params['users']=array();
			  	foreach($timeline_items->items as $i=>$timeline_item)
			  	{
					//if(!$timeline_item->DependsOnIncompleteItem())
					if(in_array($timeline_item->Get('timeline_item_for'),$this->GetNotificationTypesForUser($user)))
					{
						$user=new user($timeline_item->Get('user_id'));
						//if(!$timeline_item->Get('timeline_item_agent_only') or $user->Get('user_agent_only_notifications'))
						if(($timeline_item->Get('timeline_item_for')!=='AGENT') or $user->Get('user_agent_only_notifications'))
						{
							$d=new dbdate($timeline_item->Get('timeline_item_date'));
							if(!is_array($mail_params['users'][$user->id]))
								$mail_params['users'][$user->id]=array();
							$mail_params['users'][$user->id]+=$user->attributes;
							$mail_params['users'][$user->id]['timeline_items'][$i]=array();
							$mail_params['users'][$user->id]['timeline_items'][$i]+=$timeline_item->attributes;
							$mail_params['users'][$user->id]['timeline_items'][$i]['has_date']=$d->IsValid();
							$mail_params['users'][$user->id]['timeline_items'][$i]['date']=$d->GetDate('F j');
							$mail_params['users'][$user->id]['timeline_items'][$i]['url']='#'.$timeline_item->GetFieldName('anchor');
							if(!$mail_params['users'][$user->id]['user_url'])
								$mail_params['users'][$user->id]['user_url']=$this->GetUserURL($user).'#'.$timeline_item->GetFieldName('anchor');


							activity_log::Log($this,'TIMELINE_ITEM_REMINDER','Email Reminder Sent To Agent for '.$timeline_item->Get('timeline_item_title').' to '.$this->GetFullName(),$user->id);
						}	
					}
				}
				$mail_params['opt_out_link']=$this->ToURL('optout.php');
		
				foreach($mail_params['users'] as $user_id=>$data)
				{
					echo('AGENT '.$this->Get('agent_name').' '.$data['user_address'].' : '.$this->Get('agent_email').' ('.count($data['timeline_items']).' Reminders) - '.$data['user_url']);
					echo("<br>");
				}
		
				email::templateMail($this->Get('agent_email'),email::GetEmail(),"What's Next Real Estate Reminder for ".$this->Get('agent_name'),file::GetPath('email_agent_reminders'),$mail_params);
			}	
		}		
	}	
	
	public function SendSMSReminders($where=array())
	{
		$date=new date();
		$date->Add(7);

		$where[]="timeline_item_active=1";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete=0";
//		$where[]="(timeline_item_date>='".$today->GetDBDate()."' OR timeline_item_reference_date_type='NONE')";
		$where[]="(timeline_item_date<='".$date->GetDBDate()."' AND timeline_item_reference_date_type!='NONE')";
		$where[]="users.agent_id='".$this->id."'";

//		$order='timeline_item_order';
//		if($user->Get('user_under_contract'))
			$order='timeline_item_date,timeline_item_order';

	  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'users.user_id,'.$order);
		$timeline_items->join_tables='users';
		$timeline_items->join_where='users.user_id=timeline_items.user_id';
	  	$timeline_items->Retrieve();
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		

		if(count($timeline_items->items))		
		{
			if($settings['notifications']['phone'])
			{
				$phone=$this->TwillioFormat($this->Get('agent_cellphone'));
				$client = new \Twilio\Rest\Client(TWILLIO_SID,TWILLIO_KEY);

			 	$updates=array();
			  	foreach($timeline_items->items as $i=>$timeline_item)
			  	{
					//if(!$timeline_item->DependsOnIncompleteItem())
					if(in_array($timeline_item->Get('timeline_item_for'),$this->GetNotificationTypesForUser($user)))
					{
						$user=new user($timeline_item->Get('user_id'));
						//if(!$timeline_item->Get('timeline_item_agent_only') or $user->Get('user_agent_only_notifications'))
						if(!$timeline_item->Get('timeline_item_for')!='AGENT' or $user->Get('user_agent_only_notifications'))
						{
							$d=new dbdate($timeline_item->Get('timeline_item_date'));
							if(!is_array($updates[$user->id]))
								$updates[$user->id]=array();
							if($d->IsValid())
								$updates[$user->id][]=$timeline_item->Get('timeline_item_title').' - Due '.$d->GetDate('F j');
							else
								$updates[$user->id][]=$timeline_item->Get('timeline_item_title').' - Due '.$d->GetDate('F j');
							if(!$user_url)
								$user_url=$this->ToURL($this->GetUserURL($user).'#'.$timeline_item->GetFieldName('anchor'));
	
							activity_log::Log($this,'TIMELINE_ITEM_REMINDER','SMS Reminder Sent To Agent for '.$timeline_item->Get('timeline_item_title').' to '.$this->GetFullName(),$user->id);
						}
					}
				}

				foreach($updates as $user_id=>$data)
				{
					$user=new user($user_id);

					echo('AGENT '.$this->Get('agent_name').' '.$user->Get('user_address').' : '.$phone.' ('.count($data).' Reminders)');
					echo("<br>");
					try
					{

						$content=array();
						$content[]="Reminders for ".$user->GetFullName();
						$content[]="";
					  	foreach($data as $i=>$update)
					  	{
							if($i<3)
								$content[]=$update;
						}
						if(count($data)>3)
							$content[]=' * '.' there are '.(count($data)-3).' additional items due as well';
						$content[]="";
						$content[]="To opt out of text notifications, reply PAUSE to this message.";

						$res=$client->messages->create($phone,array('from'=>TWILLIO_NUMBER,'body'=>implode("\r\n",$content)));
					}
					catch(Exception $e)
					{
						echo($e->getMessage()."<br>\r\n");
					}				
					
					try
					{
					 	$content=$user_url;
						$res=$client->messages->create($phone,array('from'=>TWILLIO_NUMBER,'body'=>$content));
					}
					catch(Exception $e)
					{
						echo($e->getMessage()."<br>\r\n");
					}				
				}
			}		
		}		
	}		

	public function TOS()
	{
		if($this->IsLoggedIn() and !$this->Get('agent_tos_timestamp') and !$this->GetFlag('REGISTER'))	
		{
			Javascript::Begin();
			echo("jQuery(function(){
				ObjectFunctionAjaxPopup('Terms Of Service','agent','".$this->id."','TermsOfService','NULL','','',function(){},'','modeless');
			});");
			Javascript::End();
			activity_log::Log($this,'SHOWN_TOS','Show Terms Of Service');
		}
	}
	
	public function TermsOfService($params=array())
	{
		$content_params=array();
	 	foreach($this->attributes as $k=>$v)
	 		$content_params["<".$k."/>"]=$v;
		$tos=html::ProcessTemplateFile(file::GetPath('agent_terms_of_service'),$content_params);

	 	if($params['action']==$this->GetFormAction('tos'))
	 	{
			$this->Set('agent_tos_timestamp',time());
			$this->Set('agent_tos',$tos);
			$this->Update();

			activity_log::Log($this,'ACCEPTED_TOS','Terms Of Service Accepted');
			
			if(!$this->Get('agent_password'))
				_navigation::Redirect("settings.php#login_info");
			
		}
		
		$js="ObjectFunctionAjax('agent','".$this->id."','TermsOfService','popup_content','TermsOfService','','&action=".$this->GetFormAction('tos')."',function(){PopupClose();});return false;";
		echo("<div class='tos'>");
		form::Begin('','POST',false,array('id'=>'TermsOfService'));
		echo("<div class='tos_content'>".$tos."</div>");
		form::DrawButton('','I agree',array('onclick'=>$js));
		form::End();
		echo("</div>");
	}

	public function AcceptCoordinatorInvite($params)
	{
		$coordinator=new coordinator($params['coordinator_id']);
		if($params['tchash'])	
		{
			$rec=database::fetch_array(database::query("SELECT coordinator_id FROM coordinators WHERE md5(coordinator_id)='".$params['tchash']."'"));	
			$coordinator=new coordinator($rec['coordinator_id']);
		}
		if(!$coordinator->id)
		{
			Javascript::Begin();
			echo("jQuery(function(){alert('Invitation Not Found');});");
			Javascript::End();		
			
			return;
		}

		$params=array('base_url'=>_navigation::GetBaseURL());
		$params+=$coordinator->attributes;
		$params+=$this->attributes;
		$params['coordinator_url']=$coordinator->ToURL();
		email::templateMail($coordinator->Get('coordinator_email'),email::GetEmail(),$this->Get('agent_name')." has accepted your invitation to What's Next",file::GetPath('email_cordinator_invite_accepted'),$params);

		$agent_to_coordinator=new agent_to_coordinator();
		$agent_to_coordinator->CreateFromKeys(array('agent_id','coordinator_id'),array($this->id,$coordinator->id));
		$agent_to_coordinator->Set('agents_to_coordinators_accepted',time());
		$agent_to_coordinator->Update();
		
		Javascript::Begin();
		echo("jQuery(function(){alert('You have accepted the invitation from ".$coordinator->Get('coordinator_name')."');});");
		Javascript::End();		
	}

	public function DeclineCoordinatorInvite($params)
	{
		$coordinator=new coordinator($params['coordinator_id']);
		if($params['tchash'])	
		{
			$rec=database::fetch_array(database::query("SELECT coordinator_id FROM coordinators WHERE md5(coordinator_id)='".$params['tchash']."'"));	
			$coordinator=new coordinator($rec['coordinator_id']);
		}
		if(!$coordinator->id)
		{
			Javascript::Begin();
			echo("jQuery(function(){alert('Invitation Not Found');});");
			Javascript::End();		
			
			return;
		}

		$params=array('base_url'=>_navigation::GetBaseURL());
		$params+=$coordinator->attributes;
		$params+=$this->attributes;
		$params['coordinator_url']=$coordinator->ToURL();
		email::templateMail($coordinator->Get('coordinator_email'),email::GetEmail(),$this->Get('agent_name')." has declined your invitation to What's Next",file::GetPath('email_cordinator_invite_declined'),$params);

		$agent_to_coordinator=new agent_to_coordinator();
		$agent_to_coordinator->CreateFromKeys(array('agent_id','coordinator_id'),array($this->id,$coordinator->id));
		$agent_to_coordinator->Decline();
		
		Javascript::Begin();
		echo("jQuery(function(){alert('You have declined the invitation from ".$coordinator->Get('coordinator_name')."');});");
		Javascript::End();		
	}

	public function SendWelcomeMessage()
	{
		$params=array('base_url'=>_navigation::GetBaseURL());
		$params+=$this->attributes;
		$params['agent_url']=$this->ToURL();
		email::templateMail($this->Get('agent_email'),email::GetEmail(),"Welcome to What's Next",file::GetPath('email_agent_welcome'),$params);

		$this->Set('agent_welcome_timestamp',time());
		$this->Update();
	}

	public function SendLoginReminder($user,$params=array())
	{
		$settings=json_decode($this->Get('agent_settings'),true);		
		if($settings['notifications']['email'])
		{
			$date=new date();
			$date->Add(2);
			
			$where=array("user_id='".$user->id."' AND timeline_item_active=1");
			$where[]="timeline_item_active=1";
			$where[]="timeline_item_type='TIMELINE'";
			$where[]="timeline_item_complete=0";
			$where[]="timeline_item_for IN('AGENT','OTHER')";
			$where[]="(timeline_item_date<='".$date->GetDBDate()."' AND timeline_item_reference_date_type!='NONE')";
			$where[]="user_id='".$user->id."'";
			$order='timeline_item_date,timeline_item_order';
		  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order);
		  	$timeline_items->Retrieve();
			//remove depends on incomplete and N/A items
			$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		

		  	
			$mail_params=array('base_url'=>_navigation::GetBaseURL());
			$mail_params+=$user->attributes;
			$mail_params+=$this->attributes;	
			$mail_params['user_full_name']=$user->GetFullName();
			$mail_params['agent_url']=$this->ToURL();
			$mail_params['user_url']=$this->ToURL($this->GetUserURL($user));
			$mail_params['timeline_items']=array();			
			$mail_params['message']=nl2br($params['message']);
			if($user->Get('user_under_contract'))
			{
			  	foreach($timeline_items->items as $i=>$timeline_item)
			  	{
					$timeline_item_data=array();
					$timeline_item_data+=$timeline_item->attributes;
					$timeline_item_data['url']=$this->ToURL($this->GetUserURL($user).'#'.$timeline_item->GetFieldName('anchor'));
					$d=new dbdate($timeline_item->Get('timeline_item_date'));
					$timeline_item_data['due']=$d->GetDate('F j');
					$mail_params['timeline_items'][]=$timeline_item_data;
				}
			}			
			email::templateMail($this->Get('agent_email'),email::GetEmail(),"Reminder to Login to ".$user->GetName()." on What's Next",file::GetPath('email_agent_login_reminder'),$mail_params);
	
			$user->Set('user_agent_last_login_reminder',time());
			$user->Update();
			
			//activity_log::Log($this->GetAgent(),'LOGIN_EMAIL','Login Reminder Sent To - '.$this->Get('agent_name').' '.$this->Get('agent_email'),$this->Get('user_id'));
			
		}
	}
	
	public function SendNotice($subject,$contents)
	{
		$settings=json_decode($this->Get('agent_settings'),true);
		if(!$settings['notifications']['email'])
			return;

		$mail_params=array('base_url'=>_navigation::GetBaseURL());
		$mail_params['contents']=implode('<br>',$contents);
		email::templateMail($this->Get('agent_email'),email::GetEmail(),$subject,file::GetPath('email_agent_general'),$mail_params);		
	}

	function FooterScripts($params=array())
	{
		if($params['action']==$this->GetFormAction('add_to_calendar'))	
		{
			Javascript::Begin();
			echo("jQuery(function(){ObjectFunctionAjaxPopup('Add To Calendar','agent','".$this->id."','AddToCalendarInfo','NULL','','user_id=".$params['user_id']."&agent=1');});");
			Javascript::End();
		}		
		else
			$this->RecentActivityCheck($params);
	}

	public function GetMailParams()
	{
		$agent=new agent($this->Get('agent_id'));
		
		$mail_params=array();
		$mail_params+=$this->attributes;
		$mail_params['agent_image_file']=$agent->GetThumb(500,500,false);
		$mail_params['agent_image_file1']=$agent->GetThumb(500,500,false);
		$mail_params['agent_image_file2']=$agent->GetThumb(120,120,false,'agent_image_file2');
		$mail_params['agent_image_file3']=$agent->GetThumb(200,65,false,'agent_image_file3');

		return $mail_params;
	}

	function HasNotifications()
	{
		$settings=json_decode($this->Get('agent_settings'),true);		
		return($settings['notifications']['phone'] or $settings['notifications']['email']);
		
	}


};

?>