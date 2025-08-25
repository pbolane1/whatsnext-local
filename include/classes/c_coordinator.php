<?php
//**************************************************************//
//	
//	FILE: c_coordinator.php
//  CLASS: coordinator
//  
//	STUBBED BY: PoCo Technologies LLC CoreLib Autocoder v0.0 BETA
//  PURPOSE: database abstraction for the coordinators table
//  STUBBED TIMESTAMP: 1212155116
//
//**************************************************************//

class coordinator extends DBRowEx
{
	use public_user;
	use transaction_handler
	{
		DrawUCToggle as transaction_handler_DrawUCToggle;	
	}

	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('coordinators','coordinator_id');
		$this->Retrieve();
	}

	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{
			$today=new Date();
			$this->Set('coordinator_reset_date',$today->GetDBDate());
			$this->Set('coordinator_active',1);
			
			$settings=array();
			$settings['notifications']=array();
			$settings['notifications']['phone']=0;
			$settings['notifications']['email']=0;
			$settings['notifications']['agent']=1;
			$settings['notifications']['other']=1;
			$settings['notifications']['user']=0;
			$this->Set('coordinator_settings',json_encode($settings));						

			$this->DefaultColors();
		}
	}

	public function DefaultColors()
	{
		$this->Set('coordinator_color1_hex','#009901');
		$this->Set('coordinator_color1_fg_hex','#FFFFFF');
		$this->Set('coordinator_color2_hex','#000000');
		$this->Set('coordinator_color2_fg_hex','#FFFFFF');		
	}

	public function IsCoordinator()
	{			
		return true;
	}

	public function ToURL($page='',$expires=3)
	{
		$coordinator_link=new coordinator_link;
		return $coordinator_link->Generate($this->id,$page,$expires);
	}

	public function DirectURL($page='')
	{
		//bypass to cordinator for shared links
		return _navigation::GetBaseURL().'coordinators/'.$page;
	}

	public function GetUserURL($user,$page='edit_user.php')
	{
		return _navigation::GetBaseURL()."coordinators/user/".md5('user'.$user->id)."/".$page;
	}

	public function AgentProxyLink($agent,$page='')
	{
		$check=md5("proxy_login".$this->id.$agent->id);

		return _navigation::GetBaseURL()."proxy/".md5('coordinator'.$this->id)."/".md5('agent'.$agent->id)."/".$check."/".$page;
	}

	public function GetFullName()
	{
		return $this->Get('coordinator_name');
	}

	public function DisplayEditable()
	{
		$login=new Date();
		$login->SetTimestamp($this->Get('coordinator_last_login'));

	 	echo("<td><a href='/coordinators/?action=login_as&coordinator_id=".$this->id."' target='_blank'>".$this->Get('coordinator_name')."</a></td>");
	 	echo("<td class='hidden-sm hidden-xs'>".$this->Get('coordinator_email')."</td>");
	 	echo("<td class='hidden-sm hidden-xs'>".($this->Get('coordinator_last_login')?$login->GetDate('m/d/Y'):'')."</td>");
	 	//$this->GenericLink("clients.php?&coordinator_id=".$this->id,'Clients');
	}
	
	public function DeleteLink()
    {
		if($this->Get('coordinator_active'))
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('coordinator_active'),0);
			form::DrawSubmit('','Delete',array('onclick'=>"return confirm('Are you sure you want to disable this coordinator?');"));
			form::end();
		}
		echo("</td>");
	}

	public function EditLink()
    {
		if(!$this->Get('coordinator_active'))
		{	
		 	echo("<td class='edit_actions'>");
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('coordinator_active'),1);
			form::DrawSubmit('','RE-ACTIVATE',array('onclick'=>"return confirm('Are you sure you want to reactivate this coordinator?');"));
			form::end();			
		}
		else
		{
			parent::EditLink();

			$confirm="Are you sure you are ready to send a welcome email to this coordinator?";
			if($this->Get('coordinator_welcome_timestamp'))
			{
			 	$date=new date();
			 	$date->Settimestamp($this->Get('coordinator_welcome_timestamp'));
				$confirm="Are you sure you would like to re-send a welcome email to this coordinator?  Welcome mail has already been sent on ".$date->GetDate('m/d/Y');
			}

			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('welcome').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawSubmit('','Send Welcome Email',array('onclick'=>"return confirm('".$confirm."');"));
			form::end();			
		}
	}

	public function DoAction($action)
	{
	 	parent::DoAction($action);
	 	if($action==$this->GetFormAction('default_colors'))
	 	{
			$this->DefaultColors();
			$this->Update();
		}
		if($action==$this->GetFormAction('welcome'))
			$this->SendWelcomeMessage();
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
	 	if($action==$this->GetFormAction('add_widget'))
			$this->AddWidget();
	}			


	public function GetIncludedItemsSQL($params=array())
	{
	 	$where=array();
		//stock items
		$where[]="agent_id=0 AND coordinator_id=0";
		//items I created.
		$where[]="coordinator_id='".$this->id."'";
		//items my coordinators created
		
		
		return "((".implode(') OR (',$where)."))";
	}

	public function GetAvailableTemplateIds($params=array())
	{
		$template_ids=array(-1);
		
		$rs=database::query("SELECT template_id FROM templates WHERE agent_id=0 AND coordinator_id=0 AND user_id=0");
		while($rec=database::fetch_array($rs))	
			$template_ids[]=$rec['template_id'];
		$rs=database::query("SELECT template_id FROM templates WHERE coordinator_id='".$this->id."'");
		while($rec=database::fetch_array($rs))	
			$template_ids[]=$rec['template_id'];
		$rs=database::query("SELECT template_id FROM templates_to_transaction_handlers WHERE foreign_class='coordinator' AND foreign_id='".$this->id."'");
		while($rec=database::fetch_array($rs))	
			$template_ids[]=$rec['template_id'];
		return $template_ids;
	}
		
	public function GetAgentIDs($params=array())
	{
		$agent_ids=array(-1);
		
		$rs=database::query("SELECT agent_id FROM agents_to_coordinators WHERE coordinator_id='".$this->id."' AND agents_to_coordinators_accepted>0");
		while($rec=database::fetch_array($rs))	
			$agent_ids[]=$rec['agent_id'];
		return $agent_ids;
	}

	public function GetCoordinatorIDs($params=array())
	{
		$coordinator_ids=array($this->id);
		if($params['related'])
		{
			$rs=database::query("SELECT coordinator_id FROM agents_to_coordinators WHERE agent_id IN(".implode(',',$this->GetAgentIDs()).") AND agents_to_coordinators_accepted>0");
			while($rec=database::fetch_array($rs))	
				$coordinator_ids[]=$rec['coordinator_id'];
		}
		return $coordinator_ids;
	}


	public function GetName()
	{
		return $this->Get('coordinator_name');
	}

	public function GetPhone()
	{
		return $this->Get('coordinator_phone');
	}

	public function GetEmail()
	{
		return $this->Get('coordinator_email');
	}

	public function GetSettings()
	{
		return $this->Get('coordinator_settings');
	}


	public function ListUsersFilters()
	{
		$this->FilterUsers();

		$js2="ObjectFunctionAjax('coordinator','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','".$this->GetFieldName('ListUsersFiltersForm')."','','action=".$this->GetFormAction('filter_users')."',function(){height_handler();});";
		$js="ObjectFunctionAjax('coordinator','".$this->id."','ListUsersFilters','".$this->GetFieldName('ListUsersFiltersContainer')."','".$this->GetFieldName('ListUsersFiltersForm')."','','action=".$this->GetFormAction('filter_users')."',function(){".$js2."});";

		form::Begin('','POST',true,array('id'=>$this->GetFieldName('ListUsersFiltersForm')));
		echo("<div class='dashboard_filters'>");
		echo("<div>");
		echo("<b>Filter Agents</b>");
		echo("<label>");
		form::DrawRadioButton('coordinator_show_agent_limit','ALL',Session::Get('coordinator_show_agent_limit')=='ALL',array('onchange'=>$js));
		echo(" All </label>");
		echo("<label>");
		form::DrawRadioButton('coordinator_show_agent_limit','AGENT',Session::Get('coordinator_show_agent_limit')=='AGENT',array('onchange'=>$js));
		echo(" Selected Agent </label>");
		if(Session::Get('coordinator_show_agent_limit')=='AGENT')
		{
			echo("<label>");
			form::DrawSelectFromSQL('coordinator_show_agent_id',"SELECT * FROM agents WHERE agent_id IN (".implode(',',$this->GetAgentIDs()).")",'agent_name','agent_id',Session::Get('coordinator_show_agent_id'),array('onchange'=>$js));
			echo("</label>");
		}
		echo("</div>");
		echo("</div>");

		echo("<div class='dashboard_filters'>");
		echo("<div>");
		echo("<b>View</b>");
		echo("<label>");
		form::DrawCheckbox('coordinator_show_types[]','BUYERS',in_array('BUYERS',Session::Get('coordinator_show_types')),array('onchange'=>$js));
		echo(" Buyers </label>");
		echo("<label>");
		form::DrawCheckbox('coordinator_show_types[]','SELLERS',in_array('SELLERS',Session::Get('coordinator_show_types')),array('onchange'=>$js));
		echo(" Sellers </label>");
		echo("<label>");
		form::DrawCheckbox('coordinator_show_types[]','UC',in_array('UC',Session::Get('coordinator_show_types')),array('onchange'=>$js));
		echo(" Under Contract </label>");
		echo("<label>");
		form::DrawCheckbox('coordinator_show_types[]','NOTUC',in_array('NOTUC',Session::Get('coordinator_show_types')),array('onchange'=>$js));
		echo(" Not Under Contract </label>");
		echo("<label>");
		echo("</div>");
		echo("</div>");


		echo("<div class='dashboard_filters'>");
		echo("<div>");
		echo("<b>View</b>");
		echo("<label>");
		form::DrawRadioButton('coordinator_show_timing','ALL',Session::Get('coordinator_show_timing')=='ALL',array('onchange'=>$js));
		echo(" All Transactions </label>");
		echo("<label>");
		form::DrawRadioButton('coordinator_show_timing','DUESOON',Session::Get('coordinator_show_timing')=='DUESOON',array('onchange'=>$js));
		echo(" Due Soon </label>");
		echo("<label>");
		form::DrawRadioButton('coordinator_show_timing','PASTDUE',Session::Get('coordinator_show_timing')=='PASTDUE',array('onchange'=>$js));
		echo(" Past Due </label>");
		echo("</div>");
		echo("</div>");

		form::End();
	}

	public function FilterUsers($params=array())
	{
		global $HTTP_POST_VARS;
		if(isset($HTTP_POST_VARS['coordinator_show_agent_limit']))
			Session::Set('coordinator_show_agent_limit',$HTTP_POST_VARS['coordinator_show_agent_limit']);
		if(isset($HTTP_POST_VARS['coordinator_show_agent_id']))
			Session::Set('coordinator_show_agent_id',$HTTP_POST_VARS['coordinator_show_agent_id']);
		if(isset($HTTP_POST_VARS['coordinator_show_types']))
			Session::Set('coordinator_show_types',$HTTP_POST_VARS['coordinator_show_types']);
		if(isset($HTTP_POST_VARS['coordinator_show_timing']))
			Session::Set('coordinator_show_timing',$HTTP_POST_VARS['coordinator_show_timing']);

		
		if(!Session::Get('coordinator_show_agent_limit'))
			Session::Set('coordinator_show_agent_limit','ALL');
			
		if(!Session::Get('coordinator_show_types'))
			Session::Set('coordinator_show_types',array('BUYERS','SELLERS','UC','NOTUC'));

		if(!Session::Get('coordinator_show_timing'))
			Session::Set('coordinator_show_timing','ALL');

	}
		
	public function ListUsers($params=array())
	{
		$this->FilterUsers();

		$this->ProcessAction();
	 	if($params['action']=='SendAgentReminders')
			$this->SendAgentReminders($params);
	 	if($params['action']=='SendAgentLoginReminders')
			$this->SendAgentLoginReminders($params);
	 	if($params['action']=='SendAgentWelcomeEmails')
			$this->SendAgentWelcomeEmails($params);



	 	$where=array("agent_id IN (".implode(',',$this->GetAgentIDs()).")");
		$where[]="user_active=1";		 	 

		if((Session::Get('coordinator_show_agent_limit')=='AGENT') and Session::Get('coordinator_show_agent_id'))
			$where[]="agent_id='".Session::Get('coordinator_show_agent_id')."'";
		if(!in_array('BUYERS',Session::Get('coordinator_show_types')))
			$where[]="user_type!='BUYER'";
		if(!in_array('SELLERS',Session::Get('coordinator_show_types')))
			$where[]="user_type!='SELLER'";
		if(Session::Get('coordinator_show_timing')=='DUESOON')
		{
		 	$user_ids=array(-1);
		 	$today=new date();
		 	$date=new date();
		 	$date->Add(2);
			$rs=database::Query("SELECT DISTINCT(u.user_id) FROM timeline_items ti, users u WHERE ti.user_id=u.user_id AND u.agent_id IN (".implode(',',$this->GetAgentIDs()).") AND user_active=1 AND user_under_contract>0 AND timeline_item_complete=0 AND timeline_item_reference_date_type!='NONE' AND timeline_item_date<='".$date->GetDBDate()."'  AND timeline_item_date>='".$today->GetDBDate()."' AND depends_on_timeline_item_id=0"); //have to check if that item is actually not complete thouth otherwise depends on items could be eligible here.
			while($rec=database::fetch_array($rs))
				$user_ids[]=$rec['user_id'];
			$where[]="user_id IN(".implode(',',$user_ids).")";
		}
		if(Session::Get('coordinator_show_timing')=='PASTDUE')
		{
		 	$user_ids=array(-1);
		 	$today=new date();
			$rs=database::Query("SELECT DISTINCT(u.user_id) FROM timeline_items ti, users u WHERE ti.user_id=u.user_id AND u.agent_id IN (".implode(',',$this->GetAgentIDs()).") AND user_active=1 AND user_under_contract>0 AND timeline_item_complete=0 AND timeline_item_reference_date_type!='NONE' AND timeline_item_date<'".$today->GetDBDate()."' AND depends_on_timeline_item_id=0"); //have to check if that item is actually not complete thouth otherwise depends on items could be eligible here.
			while($rec=database::fetch_array($rs))
				$user_ids[]=$rec['user_id'];
			$where[]="user_id IN(".implode(',',$user_ids).")";
		}
		if(!in_array('UC',Session::Get('coordinator_show_types')))
			$where[]="user_under_contract=0";
		if(!in_array('NOTUC',Session::Get('coordinator_show_types')))
			$where[]="user_under_contract>0";


			
	  	$list=new DBRowSetEX('users','user_id','user',implode(' AND ',$where),'user_under_contract DESC');
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('coordinator_id',$this->id);
	  	$list->SetEachNew('user_order',0);	  	
		$list->SetFlag('ALLOW_BLANK');
		$list->ProcessAction();
	  	$list->CheckSortOrder('user_order');
		$list->SetFlag('ALLOW_BLANK');
	  	$list->ProcessAction();
	  	$list->Retrieve();
	  	$list->SetEachNew('coordinator_id',$this->id);

		//FULL SIZE:
		echo("<div class='agent_dashboard hidden-sm hidden-xs'>");
		//form::Begin('view_tasks.php','POST',false,array('id'=>'list_users'));
		echo("<div class='agent_dashboard'>");
		echo("<table class='listing'>");
		echo("<tr class='agent_bg_color1'><th>Agent</th><th>Agent's Last Login</th>".$list->Header('Name','user_name').$list->Header('Address','user_address')."<th>Representing</th><th>% Complete</th>".$list->Header('Under Contract','user_under_contract')."<th>WN: Client</th><th>WN: Agent</th><th>Archive</th></tr>");
		if(!count($list->items))
			echo("<tr><td class='emptyset' colspan='100'>There are no active transactions to display</tr>");	
		foreach($list->items as $user)
		{
		 	$agent=new agent($user->Get('agent_id'));

			echo("<tr class='list_item'>");
			echo("<td>".$agent->Get('agent_name')."</td>");
			echo("<td>");
			$this->DrawLastLogin($user);
			echo("</td>");
			$js="ObjectFunctionAjax('coordinator','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','user_id=".$user->id."&fn=ListUsers',function(){height_handler();});";
			$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('EditUserContainer')."').offset().top}, 1500);";
			echo("<td nowrap><a href='#' data-toggle='tooltip' title='Edit Client Details' onclick=\"".$js."\">".$user->Get('user_name')."</a><br>(".Text::Capitalize(strtolower($user->Get('user_type'))).")</td>");
			//echo("<td><a href='".$this->GetUserURL($user)."'>".$user->Get('user_name')."</a></td>");
			echo("<td><a href='".$this->GetUserURL($user)."'>".$user->Get('user_address')."</a></td>");
			echo("<td>".$user->GetUserType()."</td>");
			echo("<td>");
			$user->DisplayProgress(array('id'=>$user->GetFieldName('progress'),'height'=>'100','width'=>'100'));
			echo("</td>");
			echo("<td id='".$user->GetFieldName('UCToggleContainer')."'>");
			$this->DrawUCToggle(array('user_id'=>$user->id,'primary_container'=>$user->GetFieldName('UCToggleContainer'),'secondary_container'=>$user->GetFieldName('UCToggleContainerMobile')));
			echo("</td>");
			$this->DrawNextTask($user,"timeline_item_for IN('USER')",array('reminder_button'=>true,'show_notification_date'=>true));
			$this->DrawNextTask($user,"timeline_item_for IN('AGENT','OTHER')");
			echo("<td>");
			$js="ObjectFunctionAjax('coordinator','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('archive_transaction')."',function(){height_handler();});";
			$js.="";
			$js="if(confirm('Archive this transaction?  This will turn off all reminders (to coordinator and client), but this transaction can be reviewed and restored at any time by clicking on the &quot;Archived&quot; link at the top of the page')){".$js."}return false;";			
			echo("<a data-info='USERS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Archive'><i class='fa fa-archive'></i></a>");
			echo("</td>");
			echo("</tr>");
		}

		echo("<tr class='key'>");	
		echo("<td><br></td>");	
		echo("<td><br></td>");	
		echo("<td><br></td>");	
		echo("<td><br></td>");	
		echo("<td>");	
		$js="ObjectFunctionAjaxPopup('Add New Agent','coordinator','".$this->id."','AddNewAgent','NULL','','',function(){});";
		form::DrawButton('','Add New Agent',array('class'=>'agent_bg_color1','onclick'=>$js));
		echo("</td>");	
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
		//form::End();
		echo("</div>");

		//MOBILE:
		echo("<div class='agent_dashboard visible-sm visible-xs agent_dashboard_mobile'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-12'>");
		echo("<div style='text-align:center'>");
		$js="ObjectFunctionAjaxPopup('Add New Agent','coordinator','".$this->id."','AddNewAgent','NULL','','',function(){});";
		form::DrawButton('','Add New Agent',array('class'=>'agent_bg_color1','onclick'=>$js));
		echo("</div>");
		echo("</div>");
		echo("</div>");

		form::Begin('view_tasks.php','POST',false,array('id'=>'list_users_mobile'));
		echo("<div class='agent_dashboard'>");
		if(!count($list->items))
			echo("<table class='listing'><tr><td class='emptyset' colspan='100'>There are no active transactions to display</tr></table>");	
		foreach($list->items as $user)
		{
		 	$agent=new agent($user->Get('agent_id'));
		 
			echo("<table class='listing'>");
			echo("<tr class='agent_bg_color1'>");
			echo("<th>".$agent->Get('agent_name')."</th>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td><a href='".$this->GetUserURL($user)."'>".$user->Get('user_name')."</a></td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>");
			$js="ObjectFunctionAjax('coordinator','".$this->id."','EditUser','".$this->GetFieldName('EditUserContainer')."','NULL','','user_id=".$user->id."&fn=ListUsers',function(){height_handler();});";
			$js.="$('html, body').animate({scrollTop:$('#".$this->GetFieldName('EditUserContainer')."').offset().top}, 1500);";
			echo("<a href='#' data-toggle='tooltip' title='Edit Client Details' onclick=\"".$js."\">".$user->Get('user_address')." (".Text::Capitalize(strtolower($user->Get('user_type'))).") <i class='fa fa-pencil'></i></a>");
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>");
			$user->DisplayProgress(array('id'=>$user->GetFieldName('progress_mobile'),'height'=>'100','width'=>'100'));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>Agent's Last Login:");
			$this->DrawLastLogin($user);
			echo("</td>");
			echo("</tr>");

			echo("<tr class='list_item'>");
			echo("<td id='".$user->GetFieldName('UCToggleContainerMobile')."'>Under Contract: ");
			$this->DrawUCToggle(array('user_id'=>$user->id,'primary_container'=>$user->GetFieldName('UCToggleContainerMobile'),'secondary_container'=>$user->GetFieldName('UCToggleContainer')));
			echo("</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			$this->DrawNextTask($user,"timeline_item_for IN('USER')",array('reminder_button'=>false,'show_notification_date'=>false,'text'=>"What's Next For Client:"));
			echo("</tr>");
			echo("<tr class='list_item'>");
			$this->DrawNextTask($user,"timeline_item_for IN('AGENT','OTHER')",array('text'=>"What's Next For coordinator:"));
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>");
			$js="ObjectFunctionAjax('coordinator','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','action=".$user->GetFormAction('archive_transaction')."',function(){height_handler();});";
			$js.="";
			$js="if(confirm('Archive this transaction? This will turn off all reminders (to coordinator and client), but this transaction can be reviewed and restored at any time by clicking on the &quot;Past Tx&quot; link at the top of the page')){".$js."}";			
			echo("<a data-info='USERS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Archive'><i class='fa fa-archive'></i> Archive Transaction</a>");
			echo("</td>");
			echo("</tr>");
			echo("</table>");
			echo("<br>");
		}
		
		echo("</div>");
		form::End();
		echo("</div>");
	}

	public function DrawLastLogin($user)
	{
		$agent=new agent($user->Get('agent_id'));

		$login_date=new DBDate('1969-12-31');
		$notified_date=new DBDate('1969-12-31');

		$d=new Date();
		$d->SetTimestamp($user->Get('user_agent_last_view'));
		if(date::GetDays($d,$login_date)<0)
			$login_date->SetTimestamp($user->Get('user_agent_last_view'));

		$d=new Date();
		$d->SetTimestamp($agent->Get('agent_last_login_reminder'));

		if(date::GetDays($d,$notified_date)<0)
			$notified_date->SetTimestamp($user->Get('user_agent_last_login_reminder'));

		$welcome=false;
		if($agent->Get('agent_welcome_timestamp') and $agent->Get('agent_last_login'))
			$welcome=true;

		$last_view=new date();
		$last_view->SetTimestamp($user->Get('user_agent_last_view'));
		if($user->Get('user_agent_last_view'))
			echo($last_view->GetDate('m/d/Y'));
		else
			echo("(Never)");
			
		if($welcome)
		{
			$js="ObjectFunctionAjaxPopup('Send Agent Login Reminder','coordinator','".$this->id."','SendAgentLoginRemindersPopup','list_users','','action=SendAgentLoginRemindersPopup&user_ids[]=".$user->id."',function(){height_handler();});";
			if(!$agent->HasNotifications())
				$js="alert('The agent has all notifications turned off');";
			form::DrawButton('','Send Reminder',array('class'=>'agent_bg_color1','id'=>'timeline_reminder_button','onclick'=>$js,'data-toggle'=>'tooltip','title'=>'Send agent reminder to access the site'));
		}
		else
		{
			$js="ObjectFunctionAjax('coordinator','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','list_users','','action=SendAgentWelcomeEmails&user_ids[]=".$user->id."',function(){height_handler();});";
			if(!$agent->HasNotifications())
				$js="alert('The agent has all notifications turned off');";
			form::DrawButton('','Send Welcome Email',array('class'=>'agent_bg_color1','id'=>'timeline_reminder_button','onclick'=>$js,'data-toggle'=>'tooltip','title'=>'Send welcome email to agent'));
		}

		if($notified_date->IsValid() and date::GetDays($login_date,$notified_date)>0)
			echo("<div>Sent reminder on ".$notified_date->GetDate('m/d/Y')."</div>");

			
	}

	public function ListAgentsFilters()
	{
		$this->FilterAgents();

		$js2="ObjectFunctionAjax('coordinator','".$this->id."','ListAgents','".$this->GetFieldName('ListAgentsContainer')."','".$this->GetFieldName('ListAgentsFiltersForm')."','','action=".$this->GetFormAction('filter_agents')."',function(){height_handler();});";
		$js="ObjectFunctionAjax('coordinator','".$this->id."','ListAgentsFilters','".$this->GetFieldName('ListAgentsFiltersContainer')."','".$this->GetFieldName('ListAgentsFiltersForm')."','','action=".$this->GetFormAction('filter_agents')."',function(){".$js2."});";

		form::Begin('','POST',true,array('id'=>$this->GetFieldName('ListAgentsFiltersForm')));
		echo("<div class='dashboard_filters'>");
		echo("<div>");
		echo("<b>Filter Agents</b>");
		echo("<label>");
		form::DrawRadioButton('coordinator_show_agent_status','ALL',Session::Get('coordinator_show_agent_status')=='ALL',array('onchange'=>$js));
		echo(" All </label>");
		echo("<label>");
		form::DrawRadioButton('coordinator_show_agent_status','ACCEPTED',Session::Get('coordinator_show_agent_status')=='ACCEPTED',array('onchange'=>$js));
		echo(" Accepted </label>");
		echo("<label>");
		form::DrawRadioButton('coordinator_show_agent_status','PENDING',Session::Get('coordinator_show_agent_status')=='PENDING',array('onchange'=>$js));
		echo(" Pending </label>");
		echo("<label>");
		form::DrawRadioButton('coordinator_show_agent_status','REJECTED',Session::Get('coordinator_show_agent_status')=='REJECTED',array('onchange'=>$js));
		echo(" Rejected </label>");
		echo("</div>");
		echo("</div>");
		form::End();
	}

	public function FilterAgents($params=array())
	{
		global $HTTP_POST_VARS;
		if(isset($HTTP_POST_VARS['coordinator_show_agent_status']))
			Session::Set('coordinator_show_agent_status',$HTTP_POST_VARS['coordinator_show_agent_status']);
		if(!Session::Get('coordinator_show_agent_status'))
			Session::Set('coordinator_show_agent_status','ALL');

	}
		
	public function ListAgents($params=array())
	{
		$this->FilterAgents();

	 	$where=array("coordinator_id='".$this->id."'");
		if(Session::Get('coordinator_show_agent_status')=='ACCEPTED')	 	
	 		$where[]="agents_to_coordinators_accepted>0 AND agents_to_coordinators_rejected=0";
		if(Session::Get('coordinator_show_agent_status')=='PENDING')	 	
	 		$where[]="agents_to_coordinators_accepted=0 AND agents_to_coordinators_rejected=0";
		if(Session::Get('coordinator_show_agent_status')=='REJECTED')	 	
	 		$where[]="agents_to_coordinators_rejected>0";
	 		
	  	$list=new DBRowSetEX('agents_to_coordinators','agents_to_coordinators_id','agent_to_coordinator',implode(' AND ',$where),'agent_name');
		$list->join_tables="agents";
		$list->join_where="agents_to_coordinators.agent_id=agents.agent_id AND agents.agent_active=1";
		$list->num_new=0;
	  	$list->Retrieve();
	  	$list->ProcessAction();

		//FULL SIZE:
		echo("<div class='agent_dashboard hidden-sm hidden-xs'>");
		//form::Begin('view_tasks.php','POST',false,array('id'=>'list_agents'));
		echo("<div class='agent_dashboard'>");
		echo("<table class='listing'>");
		echo("<tr class='agent_bg_color1'><th>Agent Name</th><th>Sent Invitation</th><th>Status</th><th>Buyer Template</th><th>Seller Template</th><th>Access Account</th><th>Remove From Account</th></tr>");
		if(!count($list->items))
			echo("<tr><td class='emptyset' colspan='100'>There are no active transactions to display</tr>");	
		foreach($list->items as $agent_to_coordinator)
		{
		 	$agent=new agent($agent_to_coordinator->Get('agent_id'));
		 	$agent->ProcessAction();
			$invited=new Date();
			$invited->SetTimestamp($agent_to_coordinator->Get('agents_to_coordinators_invited'));

			echo("<tr class='list_item'>");
			echo("<td>".$agent->Get('agent_name')."</td>");
			echo("<td>".$invited->GetDate('m/d/Y')."</td>");
			echo("<td>".$agent_to_coordinator->GetStatus()."</td>");
			echo("<td>");
			if($agent_to_coordinator->Get('agents_to_coordinators_accepted'))
			{
				$js="ObjectFunctionAjax('coordinator','".$this->id."','ListAgents','".$this->GetFieldName('ListAgentsContainer')."','".$agent->GetFieldName('default_buyer_form')."','','action=".$agent->GetFormAction('save')."',function(){});";		
				form::Begin('','POST',false,array('id'=>$agent->GetFieldName('default_buyer_form')));
				form::DrawSelectFromSQL($agent->GetFieldName('template_id_buyer'),"SELECT * FROM templates WHERE (template_active=1 AND template_status=1 AND template_id IN(".implode(',',$agent->GetAvailableTemplateIDs()).")) OR template_id='".$agent->Get('template_id_buyer')."'","template_name","template_id",$agent->Get('template_id_buyer'),array('onchange'=>$js),array("(Use Default)"=>0));
				form::End();
			}
			echo("</td>");
			echo("<td>");
			if($agent_to_coordinator->Get('agents_to_coordinators_accepted'))
			{
				$js="ObjectFunctionAjax('coordinator','".$this->id."','ListAgents','".$this->GetFieldName('ListAgentsContainer')."','".$agent->GetFieldName('default_seller_form')."','','action=".$agent->GetFormAction('save')."',function(){});";		
				form::Begin('','POST',false,array('id'=>$agent->GetFieldName('default_seller_form')));
				form::DrawSelectFromSQL($agent->GetFieldName('template_id_seller'),"SELECT * FROM templates WHERE (template_active=1 AND template_status=1 AND template_id IN(".implode(',',$agent->GetAvailableTemplateIDs()).")) OR template_id='".$agent->Get('template_id_seller')."'","template_name","template_id",$agent->Get('template_id_seller'),array('onchange'=>$js),array("(Use Default)"=>0));
				form::End();
			}
			echo("</td>");
			echo("<td>");
			if($agent_to_coordinator->Get('agents_to_coordinators_accepted'))
				echo("<a data-info='AGENTS_LOGIN' data-info-none='none' href='".$this->AgentProxyLink($agent)."' data-toggle='tooltip' title='Login As Agent'><i class='fas fa-sign-in-alt'></i></a>");
			echo("</td>");
			$js="ObjectFunctionAjax('coordinator','".$this->id."','ListAgents','".$this->GetFieldName('ListAgentsContainer')."','NULL','','action=".$agent_to_coordinator->GetFormAction('delete')."',function(){});";		
			$js="if(confirm('Are you sure you would like to remove this agent from your account?')){".$js."}";
			echo("<td><a data-info='AGENTS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a></td>");
			echo("</tr>");
		}

		echo("<tr class='key'>");	
		echo("<td><br></td>");	
		echo("<td><br></td>");	
		echo("<td><br></td>");	
		echo("<td>");	
		$js="ObjectFunctionAjaxPopup('Add New Agent','coordinator','".$this->id."','AddNewAgent','NULL','','',function(){});";
		form::DrawButton('','Add New Agent',array('class'=>'agent_bg_color1','onclick'=>$js));
		echo("</td>");	
		echo("</tr>");	
		echo("</table>");
		echo("</div>");
		//form::End();
		echo("</div>");

		//MOBILE:
		echo("<div class='agent_dashboard visible-sm visible-xs agent_dashboard_mobile'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-12'>");
		echo("<div style='text-align:center'>");
		$js="ObjectFunctionAjaxPopup('Add New Agent','coordinator','".$this->id."','AddNewAgent','NULL','','',function(){});";
		form::DrawButton('','Add New Agent',array('class'=>'agent_bg_color1','onclick'=>$js));
		echo("</div>");
		echo("</div>");
		echo("</div>");

		form::Begin('view_tasks.php','POST',false,array('id'=>'list_agents_mobile'));
		echo("<div class='agent_dashboard'>");
		if(!count($list->items))
			echo("<table class='listing'><tr><td class='emptyset' colspan='100'>There are no active transactions to display</tr></table>");	
		foreach($list->items as $agent_to_coordinator)
		{
		 	$agent=new agent($agent_to_coordinator->Get('agent_id'));
			$invited=new Date();
			$invited->SetTimestamp($agent_to_coordinator->Get('agents_to_coordinators_invited'));
		 
			echo("<table class='listing'>");
			echo("<tr class='agent_bg_color1'>");
			echo("<th>".$agent->Get('agent_name')."</th>");
			echo("</tr>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>".$invited->GetDate('m/d/Y')."</td>");
			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td>");
			if($agent_to_coordinator->Get('agents_to_coordinators_accepted'))
				$agent_to_coordinator->GetStatus();
			echo("</td>");
			echo("<td>");
			if($agent_to_coordinator->Get('agents_to_coordinators_accepted'))
			{
				$js="ObjectFunctionAjax('coordinator','".$this->id."','ListAgents','".$this->GetFieldName('ListAgentsContainer')."','".$agent->GetFieldName('default_buyer_form2')."','','action=".$agent->GetFormAction('save')."',function(){});";		
				form::Begin('','POST',false,array('id'=>$agent->GetFieldName('default_buyer_form2')));
				form::DrawSelectFromSQL($agent->GetFieldName('template_id_buyer'),"SELECT * FROM templates WHERE (template_active=1 AND template_status=1 AND template_id IN(".implode(',',$agent->GetAvailableTemplateIDs()).")) OR template_id='".$agent->Get('template_id_buyer')."'","template_name","template_id",$agent->Get('template_id_buyer'),array('onchange'=>$js),array("(Use Default BuyerTemplate)"=>0));
				form::End();
			}
			echo("</td>");
			echo("<td>");
			if($agent_to_coordinator->Get('agents_to_coordinators_accepted'))
			{
				$js="ObjectFunctionAjax('coordinator','".$this->id."','ListAgents','".$this->GetFieldName('ListAgentsContainer')."','".$agent->GetFieldName('default_seller_form2')."','','action=".$agent->GetFormAction('save')."',function(){});";		
				form::Begin('','POST',false,array('id'=>$agent->GetFieldName('default_seller_form2')));
				form::DrawSelectFromSQL($agent->GetFieldName('template_id_seller'),"SELECT * FROM templates WHERE (template_active=1 AND template_status=1 AND template_id IN(".implode(',',$agent->GetAvailableTemplateIDs()).")) OR template_id='".$agent->Get('template_id_seller')."'","template_name","template_id",$agent->Get('template_id_seller'),array('onchange'=>$js),array("(Use Default Seller Tempalte)"=>0));
				form::End();
			}
			echo("</td>");

			echo("</tr>");
			echo("<tr class='list_item'>");
			echo("<td><a data-info='AGENTS_LOGIN' data-info-none='none' href='".$this->AgentProxyLink($agent)."' data-toggle='tooltip' title='Login As Agent'><i class='fas fa-sign-in-alt'></i></a></td>");
			echo("</tr>");
			$js="ObjectFunctionAjax('coordinator','".$this->id."','ListAgents','".$this->GetFieldName('ListAgentsContainer')."','NULL','','action=".$agent_to_coordinator->GetFormAction('delete')."',function(){});";		
			$js="if(confirm('Are you sure you would like to remove this agent from your account?')){".$js."}";
			echo("<tr class='list_item'>");
			echo("<td><a data-info='AGENTS_DELETE' data-info-none='none' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a></td>");
			echo("</tr>");
			echo("</table>");			
		}
		
		echo("</div>");
		form::End();
		echo("</div>");
	}


	public function SendAgentInvite($params=array())
	{
		$agent=new agent($params['agent_id']);
	
		$params=array('base_url'=>_navigation::GetBaseURL());
		$params+=$this->attributes;
		$params+=$agent->attributes;
		$params['accept_url']=$agent->ToURL("?action=".$agent->GetFormACtion('accept_tc_invite')."&tchash=".md5($this->id));
		$params['decline_url']=$agent->ToURL("?action=".$agent->GetFormACtion('decline_tc_invite')."&tchash=".md5($this->id));
		$params['agent_url']=$agent->ToURL();
		

		email::templateMail($agent->Get('agent_email'),email::GetEmail(),"Invitation to join ".$this->Get('coordinator_name')." on What's Next",$new_agent?file::GetPath('email_agent_tc_invite_new'):file::GetPath('email_agent_tc_invite'),$params);
	}

	public function AddNewAgent($params)
	{
		global $HTTP_POST_VARS;

		$new_agent=false;
		$agent_to_coordinator=new agent_to_coordinator();
		$agent=new agent();
		if($params['action']==$this->GetFormAction('add_agent'))
		{
			if(!$HTTP_POST_VARS['agent_email'] and !$HTTP_POST_VARS['agent_number'])
				$this->LogError('Please Enter Agent Email or Number');
			if(!count($this->errors))	
			{
				$agent->InitByKeys('agent_email',$HTTP_POST_VARS['agent_email']);
				if(!$agent->id and $HTTP_POST_VARS['agent_number'])
					$agent->InitByKeys('agent_number',$HTTP_POST_VARS['agent_number']);
				if(!$agent->id)
				{
					$agent->Set('agent_name',$HTTP_POST_VARS['agent_name']);
					$agent->Set('agent_email',$HTTP_POST_VARS['agent_email']);
					$agent->Set('agent_number',$HTTP_POST_VARS['agent_number']);

					//default color
					$agent->Set('agent_color1_hex',$this->Get('coordinator_color1_hex'));
					$agent->Set('agent_color1_fg_hex',$this->Get('coordinator_color1_fg_hex'));
					$agent->Set('agent_color2_hex',$this->Get('coordinator_color2_hex'));
					$agent->Set('agent_color2_fg_hex',$this->Get('coordinator_color2_fg_hex'));

					$agent->Set('agent_company',$this->Get('coordinator_company'));
					$agent->Set('agent_address',$this->Get('coordinator_address'));

					$agent->Save();

					//default logo @ signup...
					$mine=file::GetPath('coordinator_upload').$this->Get('coordinator_image_file');
					$agents=$agent->id.'_'.$this->Get('coordinator_image_file');
					$res=copy($mine,file::GetPath('agent_upload').$agents);

					$agent->Set('agent_image_file2',$agents);
					$agent->Update();
					
					$new_agent=true;
				}
				if(count($agent->GetErrors()))
					$this->errors+=$agent->GetErrors();
				if(!count($this->errors))
				{
					$agent_to_coordinator->CreateFromKeys(array('agent_id','coordinator_id'),array($agent->id,$this->id));
					if($agent_to_coordinator->Get('agents_to_coordinators_acccepted'))
						$this->LogError('This agent has already accepted your invitation');
					else
					{
					 	//
						$agent_to_coordinator->Set('agents_to_coordinators_rejected',0);
						$agent_to_coordinator->Set('agents_to_coordinators_invited',time());
						$agent_to_coordinator->Update();
					}
				}

				if(!count($this->errors))
				{
					$this->SendAgentInvite(array('agent_id'=>$agent->id,'new_agent'=>$new_agent));						
					echo("<div class='message'>An invitation has been sent to the agent.  You will be notified if the agent accepts your request.</div>");
				}	
			}
		}
		if($params['action']==$this->GetFormAction('update_agent'))		
		{
			$new_agent=true;
			$agent=new agent($HTTP_POST_VARS['agent_id']);
		 	$agent->Save();
		 	
		 	$agent_to_coordinator->CreateFromKeys(array('agent_id','coordinator_id'),array($agent->id,$this->id));

			echo("<div class='message'>Agent details have been updated.  You can access the agent's account if you need to make additional changes later.</div>");
		}

		if(!$agent_to_coordinator->id)
		{
			$js2="ObjectFunctionAjax('coordinator','".$this->id."','ListAgents','".$this->GetFieldName('ListAgentsContainer')."','NULL','','',function(){});";		
			$js="ObjectFunctionAjax('coordinator','".$this->id."','AddNewAgent','popup_content','AddNewAgent','','action=".$this->GetFormAction('add_agent')."',function(){".$js2."});return false;";
			echo("<h3 class='agent_color1'>Add New Agent</h3>");
			if(count($this->GetErrors()))
				echo("<div class='errors'>".implode('<br>',$this->GetErrors())."</div>");
			form::Begin('','POST',false,array('id'=>'AddNewAgent'));
			echo("<div class='line'>");

			echo("<div class='row'>");
			echo("<div class='col-md-3'>");
			echo("<lable>Name:</label>");
			echo("</div>");
			echo("<div class='col-md-9'>");
			form::DrawTextInput('agent_name',$HTTP_POST_VARS['agent_name']);
			echo("</div>");
			echo("</div>");
	
			echo("<div class='row'>");
			echo("<div class='col-md-3'>");
			echo("<lable>Email:</label>");
			echo("</div>");
			echo("<div class='col-md-9'>");
			form::DrawTextInput('agent_email',$HTTP_POST_VARS['agent_email']);
			echo("</div>");
			echo("</div>");
	
			echo("<div class='row'>");
			echo("<div class='col-md-3'>");
			echo("<lable>Agent #:</label>");
			echo("</div>");
			echo("<div class='col-md-9'>");
			form::DrawTextInput('agent_number',$HTTP_POST_VARS['agent_number']);
			echo("</div>");
			echo("</div>");
	
			echo("</div>");

			echo("<div class='line'>");
			echo("<div class='row'>");
			echo("<div class='col-md-3'>");
			echo("</div>");
			echo("<div class='col-md-9'>");
			echo("<a class='button agent_bg_color1' onclick=\"".$js."\">Add Agent</a>");
			echo("</div>");
			echo("</div>");
			echo("</div>");

			echo("<div class='line'>");
			echo("<div class='row'>");
			echo("<div class='col-md-3'>");
			echo("</div>");
			echo("<div class='col-md-9'>");
			echo("<a class='button agent_bg_color2' onclick=\"PopupClose()\">Exit</a>");
			echo("</div>");
			echo("</div>");
			echo("</div>");
				
			form::End();	
		}
		else
		{
			if($new_agent)
			{
				$js2="ObjectFunctionAjax('coordinator','".$this->id."','ListAgents','".$this->GetFieldName('ListAgentsContainer')."','NULL','','',function(){});";		
				$js="ObjectFunctionAjax('coordinator','".$this->id."','AddNewAgent','popup_content','AgentDetails','','action=".$this->GetFormAction('update_agent')."',function(){".$js2."});return false;";

				echo("<div class='agent_to_coordiator_agent_details'>");
				form::Begin('','POST',true,array('id'=>'AgentDetails'));
				form::DrawHiddenInput('agent_id',$agent->id);
				echo("<div class='line'>");
				
				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Name:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawTextInput($agent->GetFieldName('agent_name'),$agent->Get('agent_name'));
				echo("</div>");
				echo("</div>");
		
				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Email:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawTextInput($agent->GetFieldName('agent_email'),$agent->Get('agent_email'),array('readonly'=>'readonly'));
				echo("</div>");
				echo("</div>");
		
				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Cell Phone:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawTextInput($agent->GetFieldName('agent_cellphone'),$agent->Get('agent_cellphone'));
				echo("</div>");
				echo("</div>");

				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Full Name:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawTextInput($agent->GetFieldName('agent_fullname'),$agent->Get('agent_fullname'));
				echo("</div>");
				echo("</div>");

				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Company:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawTextInput($agent->GetFieldName('agent_company'),$agent->Get('agent_company'));
				echo("</div>");
				echo("</div>");

				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Number:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawTextInput($agent->GetFieldName('agent_number'),$agent->Get('agent_number'));
				echo("</div>");
				echo("</div>");

				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Phone:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawTextInput($agent->GetFieldName('agent_phone'),$agent->Get('agent_phone'));
				echo("</div>");
				echo("</div>");

				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Address:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawTextArea($agent->GetFieldName('agent_address'),$agent->Get('agent_address'));
				echo("</div>");
				echo("</div>");
		
				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Headshot:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				if($agent->Get('agent_image_file3'))
				{
					$th=$agent->GetThumb(120,120,false,'agent_image_file3',true);
					echo("<div class='agent_image'><img src='".$th."' style='max-width:100%;max-height:100%;'></div>");
				
				}
				form::DrawFileInput($agent->GetFieldName('agent_image_file3_ul'),'',array('placeholder'=>'Upload Headshot','onchange'=>$savejs2));
				echo("</div>");
				echo("</div>");

				echo("</div>");							

				echo("<div class='line'>");
				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				echo("<a class='button agent_bg_color1' onclick=\"".$js."\">Update Agent Details</a>");
				echo("</div>");
				echo("</div>");
				echo("</div>");
					
				form::End();										
				echo("</div>");					

			}
		
		
		
			$js="ObjectFunctionAjax('coordinator','".$this->id."','AddNewAgent','popup_content','AddNewAgent','','',function(){});return false;";

			echo("<div class='line'>");
			echo("<a class='button agent_bg_color1' onclick=\"".$js."\">Add Another Agent</a>");
			echo("</div>");
			
			echo("<div class='line'>");
			echo("<a class='button agent_bg_color2' onclick=\"PopupClose()\">Exit</a>");
			echo("</div>");
		}
	}
	
	public function EditSettings()
	{
		$savejs="UpdateWYSIWYG();";
		$savejs.="ObjectFunctionAjax('coordinator','".$this->id."','EditSettings','','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('save')."',function(){});";
		$savejs2="UpdateWYSIWYG();";
		$savejs2.="ObjectFunctionAjax('coordinator','".$this->id."','EditSettings','EditSettingsContainer','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('save')."',function(){});";
		if(!$this->id)
			$savejs2='';

		$savejscolor2="ObjectFunctionAjax('coordinator','".$this->id."','CustomCSS','CustomCSSContainer','".$this->GetFieldName('EditSettingsForm')."','','',function(){});";
		$savejscolor="UpdateWYSIWYG();";
		$savejscolor.="ObjectFunctionAjax('coordinator','".$this->id."','EditSettings','','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('save')."',function(){".$savejscolor2."});";


		$this->SetFlag('ALLOW_BLANK');
		if(!$params['parent_action'])
			$this->ProcessAction();

		$settings=json_decode($this->Get('coordinator_settings'),true);


		$acceptance_date=new contract_date();
		$acceptance_date->InitByKeys('contract_date_special','ACCEPTANCE');

		$coordinator_timeline_item=new timeline_item();
		$coordinator_timeline_item->Set('timeline_item_title','Sample coordinator Timeline Item');
		$coordinator_timeline_item->Set('timeline_item_summary','This is a preview of how coordinator timeline items will be displayed with the seleted colors');
		$coordinator_timeline_item->Set('timeline_item_for','AGENT');
		$coordinator_timeline_item->Set('timeline_item_reference_date',$acceptance_date->id);
		$coordinator_timeline_item->Set('timeline_item_reference_date_days',2);
		$coordinator_timeline_item->Set('timeline_item_url',_navigation::GetBaseURL());
		$coordinator_timeline_item->SetFlag('coordinator');
		$coordinator_timeline_item->SetFlag('PREVIEW');

		$buyer_timeline_item=new timeline_item();
		$buyer_timeline_item->Set('timeline_item_title','Sample Buyer/Seller Timeline Item');
		$buyer_timeline_item->Set('timeline_item_summary','This is a preview of how the buyer/seller timeline items will be displayed with the seleted colors');
		$buyer_timeline_item->Set('timeline_item_for','USER');
		$buyer_timeline_item->Set('timeline_item_reference_date',$acceptance_date->id);
		$buyer_timeline_item->Set('timeline_item_reference_date_days',5);
		$buyer_timeline_item->Set('timeline_item_url',_navigation::GetBaseURL());
		$buyer_timeline_item->SetFlag('coordinator');
		$buyer_timeline_item->SetFlag('PREVIEW');



		echo("<div class='card'>");
		form::Begin('','POST',true,array('id'=>$this->GetFieldName('EditSettingsForm')));
		echo("<div class='card_heading agent_bg_color1'>");
		form::DrawTextInput($this->GetFieldName('coordinator_name'),$this->Get('coordinator_name'),array('class'=>'text H3','placeholder'=>'Name','onchange'=>$savejs,'data-info'=>'SETTINGS_NAME'));
		echo("</div>");
		echo("<div class='card_body'>");
		echo("<div class='card_content'>");
		echo("<div class='card_label'>Login Information</div>");
		echo("<div class='card_section' data-info='SETTINGS_NOTIFICATIONS' data-info-none='none'>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('coordinator_email'),$this->Get('coordinator_email'),array('placeholder'=>'Email','onchange'=>$savejs,'data-info'=>'SETTINGS_EMAIL'));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('coordinator_password_new'),$HTTP_POST_VARS[$this->GetFieldName('coordinator_password_new')],array('placeholder'=>'Change Password','onchange'=>$savejs,'data-info'=>'SETTINGS_PASSWORD'));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('coordinator_cellphone'),$this->Get('coordinator_cellphone'),array('placeholder'=>'Cell Phone','onchange'=>$savejs));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('coordinator_fullname'),$this->Get('coordinator_fullname'),array('placeholder'=>'Full Name','onchange'=>$savejs));
		echo("</div>");
		echo("</div>");
		echo("<div class='card_label'>Notificaitons</div>");
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
		
		echo("<div class='row'>");
		echo("<div class='col-md-4'>");
		echo("<div class='card_label'>Company/Brand Colors</div>");
		echo("</div>");
		echo("<div class='col-md-8'>");
		echo("<div class='card_label'>Preview Area</div>");
		echo("</div>");
		echo("</div>");

		echo("<div class='card_section' data-info='SETTINGS_COLORS' data-info-none='none'>");
		echo("<div class='row'>");
		echo("<div class='col-md-4'>");
		echo("<div class='line coordinator_preview_color1_hex'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6'>Color 1:</div>");
		echo("<div class='col-xs-6'>");
		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color1_hex'),$this->Get('coordinator_color1_hex'),array('placeholder'=>'Color 1','onchange'=>$savejs2));
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("<div class='line coordinator_preview_color1_fg_hex'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6'>Color 1 Header:</div>");
		echo("<div class='col-xs-6'>");
		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color1_fg_hex'),$this->Get('coordinator_color1_fg_hex'),array('placeholder'=>'Color 1 Header','onchange'=>$savejs2));
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("<div class='visible-sm visible-xs'>");
		echo("<div class='timeline timeline_preview'>");
		$coordinator_timeline_item->DisplayFull();
		echo("</div>");
		echo("</div>");
		echo("<div class='line coordinator_preview_color2_hex'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6'>Color 2:</div>");
		echo("<div class='col-xs-6'>");
		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('agent_color2_hex'),$this->Get('coordinator_color2_hex'),array('placeholder'=>'Color 2','onchange'=>$savejs2));
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("<div class='line coordinator_preview_color2_fg_hex_hex'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6'>Color 2 Header:</div>");
		echo("<div class='col-xs-6'>");
		echo("<div class='colorpicker colorpicker-component''>");
		form::DrawTextInput($this->GetFieldName('agent_color2_fg_hex'),$this->Get('coordinator_color2_fg_hex'),array('placeholder'=>'Color 2 Header','onchange'=>$savejs2));
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
		$restorejscolor.="ObjectFunctionAjax('coordinator','".$this->id."','EditSettings','EditSettingsContainer','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('default_colors')."',function(){".$savejscolor."});";
		echo("<div class='line'><a href='#' onclick=\"".$restorejscolor."return false;\">Reset to Default</a></div>");
		$this->CheckColors();
		echo("</div>");
		echo("<div class='col-md-4 hidden-sm hidden-xs'>");
		echo("<div class='timeline timeline_preview'>");
		$coordinator_timeline_item->DisplayFull();
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

		echo("<div class='card_label'>Company / Address</div>");
		echo("<div class='card_section' data-info='SETTINGS_FOOTER' data-info-none='none'>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('coordinator_company'),$this->Get('coordinator_company'),array('placeholder'=>'Company','onchange'=>$savejs));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextArea($this->GetFieldName('coordinator_address'),$this->Get('coordinator_address'),array('placeholder'=>'Address','onchange'=>$savejs));
		echo("</div>");
		echo("</div>");
		
		echo("<div class='card_label'>Images</div>");
		echo("<div class='card_section account_images' data-info='SETTINGS_IMAGES' data-info-none='none'>");
		echo("<div class='row'>");
		echo("<div class='col-sm-6' data-info='SETTINGS_COMPANY_LOGO' data-info-none='none'>");
		echo("<div class='line drop_target' data-target='".$this->GetFieldName('coordinator_image_file_ul')."'>");
		echo("<div class='card_label2'>Company Logo</div>");
		$th=_navigation::GetBaseURL().'/images/placeholder.png';
		if($this->Get('coordinator_image_file'))
			$th=$this->GetThumb(200,65,false,'coordinator_image_file');
		list($width, $height) = getimagesize(str_replace(_navigation::GetBaseURL(),_navigation::GetBasePath(),$th));
		echo("<div style='height:225px;padding-top:".((225-$height)/2)."px;text-align:center;'><img src='".$th."'></div>");
		echo("<div>");
		form::DrawFileInput($this->GetFieldName('coordinator_image_file_ul'),'',array('placeholder'=>'Upload Company Logo','onchange'=>$savejs2));
		echo("</div>");
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
		$this->GatherInputs();
		if($params['action']==$this->GetFormAction('default_colors'))
			$this->DefaultColors();

		echo("<style type='text/css'>");
		if($this->Get('coordinator_color1_hex'))
		{
			echo("
				.agent_color1{color:".$this->Get('coordinator_color1_hex')." !important}
				.agent_color1_hover:hover{color:".$this->Get('coordinator_color1_hex')." !important}
				.agent_bg_color1{background-color:".$this->Get('coordinator_color1_hex')." !important;color:".$this->Get('coordinator_color1_fg_hex')." !important;}
				.agent_bg_color1 *{color:".$this->Get('coordinator_color1_fg_hex')." !important;}
 				.agent_bg_color1_hover:hover{background-color:".$this->Get('coordinator_color1_hex')." !important;color:".$this->Get('coordinator_color1_fg_hex')." !important;}
				.agent_bg_color1_hover:hover *{color:".$this->Get('coordinator_color1_fg_hex')." !important;}
				.agent_bg_color1 TH{background-color:".$this->Get('coordinator_color1_hex')." !important;color:".$this->Get('coordinator_color1_fg_hex')." !important;}
				.agent_color1 .agent_color1{color:".$this->Get('coordinator_color1_hex')." !important;}
				.agent_border_color1{border-color:".$this->Get('coordinator_color1_hex')." !important}
				.agent_border-r_color1{border-right-color:".$this->Get('coordinator_color1_hex')." !important}
				.agent_border-l_color1{border-left-color:".$this->Get('coordinator_color1_hex')." !important}
				.agent_border-t_color1{border-top-color:".$this->Get('coordinator_color1_hex')." !important}
				.agent_border-b_color1{border-bottom-color:".$this->Get('coordinator_color1_hex')." !important}
				.choose_icon_OTHER I{background-color:".$this->Get('coordinator_color1_hex')." !important;color:".$this->Get('coordinator_color1_fg_hex')." !important;}
			");			
		}	
		if(colors::ColorTooLight($this->Get('coordinator_color1_hex')))
		{
			echo("
				.agent_color1{text-shadow:1px 1px 1px #000,-1px 1px 1px #000,-1px -1px 0 #000,1px -1px 0 #000}
				.agent_color1_hover:hover{text-shadow:1px 1px 1px #000,-1px 1px 1px #000,-1px -1px 0 #000,1px -1px 0 #000}
				.timeline_item_image .agent_bg_color1{background-color:".$this->Get('coordinator_color1_fg_hex')." !important;}
			");			
		}
		else
		{
			echo("
				.agent_color1{text-shadow:none;}
				.agent_color1_hover:hover{text-shadow:none;}
			");			
		}

		if($this->Get('coordinator_color2_hex'))
		{
			echo("				
				.agent_color2{color:".$this->Get('coordinator_color2_hex')." !important}
				.agent_color2_hover:hover{color:".$this->Get('coordinator_color2_hex')." !important}
				.agent_bg_color2{background-color:".$this->Get('coordinator_color2_hex')." !important;color:".$this->Get('coordinator_color2_fg_hex')." !important;}
				.agent_bg_color2 *{color:".$this->Get('coordinator_color2_fg_hex')." !important;}
 				.agent_bg_color2_hover:hover{background-color:".$this->Get('coordinator_color2_hex')." !important;color:".$this->Get('coordinator_color2_fg_hex')." !important;}
 				.agent_bg_color2_hover:hover *{color:".$this->Get('coordinator_color2_fg_hex')." !important;}
 				.agent_bg_color2 TH{background-color:".$this->Get('coordinator_color2_hex')." !important;color:".$this->Get('coordinator_color2_fg_hex')." !important;}
				.agent_color2 .agent_color2{color:".$this->Get('coordinator_color2_hex')." !important;}
				.agent_border_color2{border-color:".$this->Get('coordinator_color2_hex')." !important}
				.agent_border-r_color2{border-right-color:".$this->Get('coordinator_color2_hex')." !important}
				.agent_border-l_color2{border-left-color:".$this->Get('coordinator_color2_hex')." !important}
				.agent_border-t_color2{border-top-color:".$this->Get('coordinator_color2_hex')." !important}
				.agent_border-b_color2{border-bottom-color:".$this->Get('coordinator_color2_hex')." !important}
				.choose_icon_USER I{background-color:".$this->Get('coordinator_color2_hex')." !important;color:".$this->Get('coordinator_color2_fg_hex')." !important;}
			");			
		}	
		if(colors::ColorTooLight($this->Get('coordinator_color2_hex')))
		{
			echo("
				.agent_color2{text-shadow:1px 1px 1px #000,-1px 1px 1px #000,-1px -1px 0 #000,1px -1px 0 #000}
				.agent_color2_hover:hover{text-shadow:1px 1px 1px #000,-1px 1px 1px #000,-1px -1px 0 #000,1px -1px 0 #000}
				.timeline_item_image .agent_bg_color2{background-color:".$this->Get('coordinator_color2_fg_hex')." !important;}
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
			.agent_preview_color1_hex :not(.minicolors-focus) .minicolors-swatch-color{background-color:".$this->Get('coordinator_color1_hex')." !important;}
			.agent_preview_color1_fg_hex :not(.minicolors-focus) .minicolors-swatch-color{background-color:".$this->Get('coordinator_color1_fg_hex')." !important;}
			.agent_preview_color2_hex :not(.minicolors-focus) .minicolors-swatch-color{background-color:".$this->Get('coordinator_color2_hex')." !important;}
			.agent_preview_color2_fg_hex :not(.minicolors-focus) .minicolors-swatch-color{background-color:".$this->Get('coordinator_color2_fg_hex')." !important;}
		");
		
		echo("</style>");
	}
		
	public function CoordinatorToolsNewTemplateConfirmation($params=array())
	{
	 	$user=new user($params['user_id']);

		if(!$params['user_id'])
			return false;
		
	 	$template=new template($user->Get('template_id'));
		//modify this one.
		if($params['action']=='update_template')
		{
			$new_template=new template($user->Get('template_id'));
			$params['action']='copy_template';
		}
		else
		{
		 	//create a copy.
			$new_template=new template();
			$new_template->Copy($template);
			$new_template->Set('template_name','Copy Of '.$new_template->Get('template_name'));
			$new_template->Set('template_copied',1);
			$new_template->Set('coordinator_id',$this->id);
			$new_template->Set('original_id',0);
			$new_template->GatherInputs();
		}
		if($params['action']=='copy_template')
		{
			$new_template->Update();
			timeline_item::CopyAll(array('user_id'=>$user->id),array('coordinator_id'=>$this->id,'template_id'=>$new_template->id));
			$user->Set('template_id',$new_template->id);
			$user->Update();
			
			//Javascript::Begin();
			//echo("document.location='';");
			//Javascript::End();
			
			echo("<h3 class='agent_color1'>New template saved.</h3>");
			echo("<div>Would you like to <a href='".$this->DirectURL("edit_timeline.php?template_id=".$new_template->id)."'>edit this template</a> or <a href='#' onclick=\"PopupClose();return false;\">go back to your client's timeline</a>?</div>");
		}
		else
		{
			$js="ObjectFunctionAjax('coordinator','".$this->id."','coordinatorToolsNewTemplateConfirmation','popup_content','coordinatorToolsNewTemplateConfirmation','','user_id=".$params['user_id']."&action=copy_template',function(){});return false;";
			echo("<h3 class='agent_color1'>Save As New Tempalte?</h3>");
			form::Begin('','POST',false,array('id'=>'coordinatorToolsNewTemplateConfirmation'));
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
			form::End();
			
			if($template->Get('coordinator_id'))		
			{
				echo("<h3 class='agent_color1'>OR Update Template</h3>");
				$js="ObjectFunctionAjax('coordinator','".$this->id."','coordinatorToolsNewTemplateConfirmation','popup_content','coordinatorToolsNewTemplateConfirmation','','user_id=".$params['user_id']."&action=update_template',function(){});return false;";
				echo("<div class='line'>");
				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<lable>Name:</label>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				echo($new_template->Get('template_name'));
				echo("</div>");
				echo("</div>");
				echo("</div>");	
				echo("<div class='line'>");
				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				echo("<a href='#' class='button agent_bg_color1' onclick=\"".$js."\">Update ".$template->Get('template_name')."</a>");
				echo("</div>");
				echo("</div>");
				echo("</div>");
			}
		}
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
	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order,1);
		$timeline_items->Retrieve();
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

		$settings=json_decode($this->Get('coordinator_settings'),true);
	
		echo("<td colspan='3' align='center'></td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Name</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('coordinator_name'),$this->Get('coordinator_name'),array('class'=>$this->GetError('coordinator_name')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Email</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('coordinator_email'),$this->Get('coordinator_email'),array('class'=>$this->GetError('coordinator_email')?'error':'text'));
		echo("</td></tr>");
		if(!$this->id)
		{
			echo("<tr><td class='label'>".REQUIRED." Password</td><td colspan='2'>");
			form::DrawTextInput($this->GetFieldName('coordinator_password_new'),$HTTP_POST_VARS[$this->GetFieldName('coordinator_password_new')],array('class'=>$this->GetError('coordinator_password_new')?'error':'text'));
			echo("</td></tr>");
		}
		else
		{
			echo("<tr><td class='label'>Password</td><td colspan='2'>*******</td></tr>");
			echo("<tr><td class='label'>Change Password</td><td colspan='2'>");
			form::DrawTextInput($this->GetFieldName('coordinator_password_new'),$HTTP_POST_VARS[$this->GetFieldName('coordinator_password_new')],array('class'=>$this->GetError('coordinator_password_new')?'error':'text'));
			echo("</td></tr>");
		}
		echo("<tr><td class='label'>".REQUIRED." Cell Phone</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('coordinator_cellphone'),$this->Get('coordinator_cellphone'),array('class'=>$this->GetError('coordinator_cellphone')?'error':'text'));
		echo("</td></tr>");

		echo("<tr><td class='label'>".REQUIRED." Company</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('coordinator_company'),$this->Get('coordinator_company'),array('class'=>$this->GetError('coordinator_company')?'error':'text'));
		echo("</td></tr>");

		echo("<tr><td class='label'>".REQUIRED." Address</td><td colspan='2'>");
		form::DrawTextInput($this->GetFieldName('coordinator_address'),$this->Get('coordinator_address'),array('class'=>$this->GetError('coordinator_address')?'error':'text'));
		echo("</td></tr>");


		echo("<tr><td class='label'>".REQUIRED." Notifications</td><td style='text-align:left' colspan='2'>");
		echo("<label>");
		form::DrawCheckbox('notifications[phone]',1,$settings['notifications']['phone']);
		echo(" SMS</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox('notifications[email]',1,$settings['notifications']['email']);
		echo(" Email</label>");
		echo("<br>");
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

		echo("<tr><td class='section' colspan='3'>Company/Brand Colors</td></tr>");
		echo("<tr><td class='label' id='".$this->GetFieldName('CustomCSSContainer')."'>");
		$this->CustomCSS();
		echo("</td>");
		echo("<td id='".$this->GetFieldName('ChooseColorsContainer')."'>");	
		$this->ChooseColors();
		echo("</td>");
		echo("<td id='".$this->GetFieldName('PreviewColorsContainer')."'>");
		$this->PreviewColors();
		echo("</td></tr>");

		echo("<tr><td class='section' colspan='3'>Images</td></tr>");

		if($this->Get('coordinator_image_file'))
		{
			echo("<tr><td class='label'>Company Logo</td><td colspan='2'>");
			echo("<img src='".$this->GetThumb(120,120,false,'coordinator_image_file')."'>");
			echo("</td></tr>");
		}		
		echo("<tr><td class='label'>Upload Company Logo</td><td colspan='2'><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('coordinator_image_file_ul'),'',array('class'=>$this->GetError('coordinator_image_file')?'error':'file'));
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
		$savejscolor="ObjectFunctionAjax('coordinator','".$this->id."','CustomCSS','".$this->GetFieldName('CustomCSSContainer')."','".$this->GetFieldName('edit_form')."','','',function(){});";

	 	if($params['action']==$this->GetFormAction('default_colors'))
			$this->DefaultColors();


		echo("<div class='line coordinator_preview_color1_hex'>");
//		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('coordinator_color1_hex'),$this->Get('coordinator_color1_hex'),array('placeholder'=>'Color 1','onchange'=>$savejscolor));
//		echo("</div>");
		echo("</div>");
		echo("<br><br>");
		echo("<div class='line coordinator_preview_color1_fg_hex'>");
//		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('coordinator_color1_fg_hex'),$this->Get('coordinator_color1_fg_hex'),array('placeholder'=>'Color 1 Header','onchange'=>$savejscolor));
//		echo("</div>");
		echo("</div>");
		echo("<br><br>");
		echo("<div class='line coordinator_preview_color2_hex'>");
//		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('coordinator_color2_hex'),$this->Get('coordinator_color2_hex'),array('placeholder'=>'Color 2','onchange'=>$savejscolor));
//		echo("</div>");
		echo("</div>");
		echo("<br><br>");
		echo("<div class='line coordinator_preview_color2_fg_hex'>");
//		echo("<div class='colorpicker colorpicker-component'>");
		form::DrawTextInput($this->GetFieldName('coordinator_color2_fg_hex'),$this->Get('coordinator_color2_fg_hex'),array('placeholder'=>'Color 2 Header','onchange'=>$savejscolor));
//		echo("</div>");
		echo("</div>");
		echo("<br><br>");
		$restorejscolor2="ObjectFunctionAjax('coordinator','".$this->id."','ChooseColors','".$this->GetFieldName('ChooseColorsContainer')."','".$this->GetFieldName('edit_form')."','','action=".$this->GetFormAction('default_colors')."',function(){});";
		$restorejscolor="ObjectFunctionAjax('coordinator','".$this->id."','CustomCSS','".$this->GetFieldName('CustomCSSContainer')."','".$this->GetFieldName('edit_form')."','','action=".$this->GetFormAction('default_colors')."',function(){".$restorejscolor2."});";
//		echo("<div class='line'><a href='#' onclick=\"".$restorejscolor."return false;\">Reset to Default</a></div>");
	}

	public function CheckColors()
	{
		$luma1=colors::luma($this->Get('coordinator_color1_hex'));
		$luma2=colors::luma($this->Get('coordinator_color2_hex'));

		//$max=.85;
		//if($luma1>$max and $luma2>$max)
		//	echo("<div class='error'>Note: It is not recommended to use two bright colors for background colors</div>");		
	}

	public function GetDarkerHoverClass()
	{			
		$colors=array();
		$colors['agent_color1_hover']=$this->Get('coordinator_color1_hex');
		$colors['agent_color2_hover']=$this->Get('coordinator_color2_hex');
				
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
		$timeline_item->Set('timeline_item_title','Sample coordinator Timeline Item');
		$timeline_item->Set('timeline_item_summary','This is a preview of how coordinator timeline items will be displayed with the seleted colors');
		$timeline_item->Set('timeline_item_for','AGENT');
		$timeline_item->Set('timeline_item_reference_date',$acceptance_date->id);
		$timeline_item->Set('timeline_item_reference_date_days',2);
		$timeline_item->Set('timeline_item_url',_navigation::GetBaseURL());
		$timeline_item->SetFlag('coordinator');
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
		$timeline_item->SetFlag('coordinator');
		$timeline_item->SetFlag('PREVIEW');
		echo("<div class='timeline timeline_preview'>");
		$timeline_item->DisplayFull();
		echo("</div>");
		echo("</div>");
		
	}

	public function GatherInputs()
	{
		global $HTTP_POST_VARS;
		
		//parent is default
		parent::GatherInputs();

		//$this->Set('coordinator_cellphone',$this->NormalizePhone($this->Get('coordinator_cellphone')));

		$settings=json_decode($this->Get('coordinator_settings'),true);
		if($HTTP_POST_VARS['notifications'])
			$settings['notifications']=$HTTP_POST_VARS['notifications'];
		$this->Set('coordinator_settings',json_encode($settings));

		$this->GatherFile($this->GetFieldName('coordinator_image_file_ul'),'coordinator_image_file');				
	}

	public function ValidateInputs()
	{
		global $HTTP_POST_VARS;

		if(!$this->Get('coordinator_name') and $HTTP_POST_VARS[$this->GetFieldName('coordinator_name_required')])
			$this->LogError('Please Enter Name','coordinator_name');

		if(!$this->Get('coordinator_email'))
			$this->LogError('Please Enter Email','coordinator_email');
		else if(!email::ValidateEmail($this->Get('coordinator_email'),false))
			$this->LogError('Email Address Does Not Appear To Be Valid','coordinator_email');
		else if(!$this->ValidateUnique('coordinator_email'))
			$this->LogError('An Account Already Exists For This Email Address.  Please Login.','coordinator_email');
		else if($HTTP_POST_VARS[$this->GetFieldName('email_verify')])
		{
			if(!$HTTP_POST_VARS[$this->GetFieldName('coordinator_email2')])  
				$this->LogError('Please Re-Enter Email','coordinator_email2');
			else if($HTTP_POST_VARS[$this->GetFieldName('coordinator_email2')]!=$this->Get('coordinator_email'))  
				$this->LogError('Email Entries Do Not Match','coordinator_email2');
		}	

		$newpwd=$HTTP_POST_VARS[$this->GetFieldName('coordinator_password_new')];			
		$newpwd2=$HTTP_POST_VARS[$this->GetFieldName('coordinator_password_new2')];			
		//if(!$this->Get('coordinator_password') and !$newpwd)
		//	$this->LogError('Please Enter Password','coordinator_password_new');
		//else 
		if($newpwd)
		{
			//if(strlen($newpwd)<8)
			//	$this->LogError('Password must be at least 8 characters','coordinator_password_new');
			//else if(!preg_match("#[0-9]+#",$newpwd) or !preg_match("#[a-z]+#",$newpwd) or !preg_match("#[A-Z]+#",$newpwd) or !preg_match("#\W+#",$newpwd))
			//	$this->LogError('Password must include at least one uppercase letter, one lowercase letter, one number and one symbol','coordinator_password_new');
			//else 
			if($HTTP_POST_VARS[$this->GetFieldName('password_verify')])
			{
				if(!$newpwd2)  
					$this->LogError('Please Re-Enter Password','coordinator_password_new2');
				else if($newpwd!=$newpwd2)  
					$this->LogError('Passwords Do Not Match','coordinator_password_new2');
			}	
		}
				
		if(!count($this->errors) and $newpwd)
		{
			$this->Set('coordinator_password',md5($newpwd)); 
		}

		return count($this->errors)==0;
 	}

	public function Save()
	{	  	  
		global $HTTP_POST_VARS;
		
	  	$new=!$this->id;
		$old=new coordinator($this->id);
		$psv=parent::Save();
		if($psv)
		{
			$this->SaveImageFile('coordinator_image_file',file::GetPath('coordinator_upload'),$this->id);

			$this->Set('coordinator_reset_code','');
			$this->Update();
			
			$this->saved=true;		

			if($new)
			{
				//setup their WYSIWYG folder if needed..
				$paths=array();
				$paths[]="uploads/coordinators/".$this->id."/files";
				$paths[]="uploads/coordinators/".$this->id."/pics";
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

			
			if($new)
				activity_log::Log($this,'COORDINATOR_CREATED','Coordinator Account Created');
			else
				activity_log::Log($this,'COORDINATOR_UPDATES','Coordinator Account Updated');

			$settings=json_decode($this->Get('coordinator_settings'),true);
			$old_settings=json_decode($old->Get('coordinator_settings'),true);
			if($settings['notifications']['phone'] and !$old_settings['notifications']['phone'])
			 	activity_log::Log($this,'SMS_ENABLED','Phone notifications enabled');
			if(!$settings['notifications']['phone'] and $old_settings['notifications']['phone'])
			 	activity_log::Log($this,'SMS_DISABLED','Phone notifications disabled');			
		}
		return count($this->errors)==0;
	}

	public function Delete()
	{
		$this->Set('coordinator_active',0);
		$this->Update();
		
	}

	public function xDelete()
	{
		parent::Delete();
	}


	public function IsLoggedIn()
	{
		return(Session::Get('pbt_coordinator_login') and Session::Get('coordinator_id'));	  	  
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
		if($this->Get('coordinator_reset_code') or !$requirecode)
		{
			if($action=='send_pwd' and $this->msg)
				echo('<div class="message">Please check your email for details on resetting your password.</div>');
			foreach($this->GetErrors() as $e)
				echo('<div class="error">'.$e.'</div>');
			form::begin('?action='.$this->GetFormAction('save').$this->GetFormExtraParams(),'POST',false,array('id'=>'login'));
			form::DrawHiddenInput($this->GetFieldName('password_reset'),1);
			form::DrawInput('password',$this->GetFieldName('coordinator_password_new'),$HTTP_POST_VARS[$this->GetFieldName('coordinator_password_new')],array('class'=>'text password','placeholder'=>'Enter New Password'));
			form::DrawHiddenInput($this->GetFieldName('password_verify'),1);
			form::DrawInput('password',$this->GetFieldName('coordinator_password_new2'),$HTTP_POST_VARS[$this->GetFieldName('coordinator_password_new2')],array('class'=>'text password','placeholder'=>'Re-Enter New Password'));
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
			echo("<div><a href='/users/'>Login</a></div>");
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
			echo "<span class='class'>Logged In As ".$this->Get('coordinator_name')."<br /></span>";
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
		form::DrawTextInput('coordinator_email',$HTTP_POST_VARS['coordinator_email'],array('placeholder'=>'Email Address'));
		form::DrawInput('password','coordinator_password',$HTTP_POST_VARS['coordinator_password'],array('placeholder'=>'Password'));
		form::DrawSubmit('','Sign In');
		form::End();
		echo '<a href="#" onclick="document.getElementById(\'forgot-password\').style.display=\'block\';document.getElementById(\'login_div\').style.display=\'none\';return false;">Forget your password?</a>';
		echo('</div>');
		echo('</div>');
		echo('</div>');

		echo("<div id='forgot-password' style='display:".(($action=='send_pwd' and  !$this->msg)?'block':'none')."'>");
		echo("<div class='login_form card'>");
		echo("<div class='card_heading agent_bg_color2'><h3>Reset Password</h3></div>");
		echo("<div class='card_body'>");
		if($action=='send_pwd' and $this->GetError('send_pwd'))
			echo('<div class="error">Email Not Found.</div>');
		form::Begin('?action=send_pwd','POST',false,array('class'=>"forgot-password"));
		form::DrawTextInput('coordinator_email',$HTTP_POST_VARS['coordinator_email'],array('placeholder'=>'Email Address'));
		form::DrawSubmit('','Reset Password');
		form::End();
		echo('</div>');
		echo('</div>');

	}


	public function Login($in=true,$silent=false)
	{
		parent::Login($in);

		Session::Set('pbt_coordinator_login',$in?1:0);
		Session::Set('coordinator_id',$in?$this->id:0);	  
		if($in)
		{
			$this->Set('coordinator_reset_code','');
			$this->Set('coordinator_last_login',time());
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
	}
	
	public function OptoutEmail($params=array())
	{
		$settings=json_decode($this->Get('coordinator_settings'),true);
		$settings['notifications']['email']=$params['email_notifications'];
		$this->Set('coordinator_settings',json_encode($settings));
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
			$rs=database::query("SELECT coordinator_id FROM coordinators WHERE coordinator_email='".$this->MakeDBSafe($coordinator_email)."' AND coordinator_password=''");		  
			if($rec=database::fetch_array($rs))
			{
			  	$date=new Date();
			  	$date->Add('1');
				$tempcoordinator=new coordinator($rec['coordinator_id']);
				$tempcoordinator->Set('coordinator_reset_code',Text::GenerateCode(30,40));
				$tempcoordinator->Set('coordinator_reset_date',$date->GetDBDate());
				$tempcoordinator->Update();
				_navigation::Redirect('reset.php?coordinator_reset_code='.$tempcoordinator->Get('coordinator_reset_code'));
			}		  


			$rs=database::query("SELECT coordinator_id FROM coordinators WHERE coordinator_email='".$this->MakeDBSafe($coordinator_email)."' AND coordinator_password!='' AND coordinator_active=1 AND coordinator_password='".md5($coordinator_password)."'");
			if($rec=database::fetch_array($rs))		  
			{
				$this->__construct($rec['coordinator_id']);
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
		if($action=='coordinator_link')  
		{
		 	$coordinator_link=new coordinator_link();
		 	$coordinator_link->InitByKeys('coordinator_link_hash',$HTTP_GET_VARS['coordinator_link_hash']);
		 	if(!$coordinator_link->id)
		 		$this->LogError('Please Log In','login');
		 	else if($coordinator_link->Get('coordinator_link_expires')<time())
		 		$this->LogError('Link has expired.  Please Log In','login');
		 	else
		 	{
				$this->__construct($coordinator_link->Get('coordinator_id'));
				$this->Login();
				$this->msg="You have been logged in";

				if($redir)
					_navigation::Redirect($redir);
			 	else if($coordinator_link->Get('coordinator_link_page'))
					_navigation::Redirect($coordinator_link->Get('coordinator_link_page'));
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
			$this->__construct($HTTP_GET_VARS['coordinator_id']);
			$this->Login();
			_navigation::Redirect('?action=redir');
		}
		
		if($action=='logout')  
		{
			$this->Login(false);
			//_navigation::Redirect('index.php');
			$this->msg="You have been logged out";
		}
		if($action=='send_pwd')  
		{
			$rs=database::query("SELECT * FROM coordinators WHERE coordinator_email='".$coordinator_email."'");		  
		  	if(!$coordinator_email)
		  		$this->LogError("Please Enter Email Address",$action);
			else if($rec=database::fetch_array($rs))		  
			{
			  	$date=new Date();
			  	$date->Add('1');
				$tempcoordinator=new coordinator($rec['coordinator_id']);
				$tempcoordinator->Set('coordinator_reset_code',Text::GenerateCode(30,40));
				$tempcoordinator->Set('coordinator_reset_date',$date->GetDBDate());
				$tempcoordinator->Update();
				email::templateMail($coordinator_email,email::GetEmail(),'Your Account',file::GetPath('email_coordinator_password'),$tempcoordinator->attributes+array('base_url'=>_navigation::GetBaseURL()));
				$this->msg='You have been emailed a link to reset your password';
			}
		  	else if($rec=database::fetch_array($rs2))		  
		  	{
				$this->msg='You have been emailed a link to reset your password';
			}
		  	else
		  		$this->LogError("Email Address Not Found",$action);
		}
		//no this i bad news.  was trying to handle the back button - this is not the way to do it.
		//if(Session::Get('pbt_agent_proxy_login'))	
		//{
		//	$this->__construct(Session::Get('pbt_agent_proxy_login'));
		//	$this->Login();
		//}
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
	public function GetThumb($width,$height,$crop=false,$which='coordinator_iamge_file1')
 	{ 	  
		$src=$this->Get($which);
		if($src)
		{	  
			return file::GetPath('coordinator_display').imaging::ResizeCached($src,file::GetPath('coordinator_upload'),$width,$height,$crop);
		}
		return _navigation::GetBaseURL().'/images/spacer.gif';

	}	

	public function TOS()
	{
		if($this->IsLoggedIn() and !$this->Get('coordinator_tos_timestamp'))	
		{
			Javascript::Begin();
			echo("jQuery(function(){
				ObjectFunctionAjaxPopup('Terms Of Service','coordinator','".$this->id."','TermsOfService','NULL','','',function(){},'','modeless');
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
			$this->Set('coordinator_tos_timestamp',time());
			$this->Set('coordinator_tos',$tos);
			$this->Update();

			activity_log::Log($this,'ACCEPTED_TOS','Terms Of Service Accepted');
		}
		
		$js="ObjectFunctionAjax('coordinator','".$this->id."','TermsOfService','popup_content','TermsOfService','','&action=".$this->GetFormAction('tos')."',function(){PopupClose();});return false;";
		echo("<div class='tos'>");
		form::Begin('','POST',false,array('id'=>'TermsOfService'));
		echo("<div class='tos_content'>".$tos."</div>");
		form::DrawButton('','I agree',array('onclick'=>$js));
		form::End();
		echo("</div>");
	}

	public function SendWelcomeMessage()
	{
		$params=array('base_url'=>_navigation::GetBaseURL());
		$params+=$this->attributes;
		$params['coordinator_url']=$this->ToURL();
		email::templateMail($this->Get('coordinator_email'),email::GetEmail(),"Welcome to What's Next",file::GetPath('email_coordinator_welcome'),$params);

		$this->Set('coordinator_welcome_timestamp',time());
		$this->Update();
	}

	function FooterScripts($params=array())
	{
		$user=new user($params['user_id']);
		$agent=new agent($user->Get('agent_id'));

		if($params['action']==$this->GetFormAction('add_to_calendar'))	
		{
			Javascript::Begin();
			echo("jQuery(function(){ObjectFunctionAjaxPopup('Add To Calendar','coordinator','".$this->id."','AddToCalendarInfo','NULL','','user_id=".$params['user_id']."&agent=1');});");
			Javascript::End();
		}
		else
			$this->RecentActivityCheck($params);		
	}
	
	function HasNotifications()
	{
		$settings=json_decode($this->Get('coordinator_settings'),true);		
		return($settings['notifications']['phone'] or $settings['notifications']['email']);
		
	}

	public function GetLastView($params=array(),$operator='MAX')
	{
		$where=array("coordinator_id='".$this->id."'");
		$where[]="user_id>0";
		if($params['user_id'])
			$where[]="user_id='".$params['user_id']."'";
		if($params['user_ids'] and !is_array($params['user_ids']))
			$params['user_ids']=array($params['user_ids']);
		if($params['user_ids'])
			$where[]="user_id IN ('".implode(',',$params['user_ids'])."')";

		$rec=database::fetch_array(database::query("SELECT ".$operator."(coordinator_to_user_last_view) AS coordinator_to_user_last_view FROM coordinators_to_users WHERE ".implode(' AND ',$where)));
		return $rec['coordinator_to_user_last_view']?$rec['coordinator_to_user_last_view']:'0';
	}

	public function RecentActivityCheck($params=array())
	{
	 	//this is not quite right, but works for now since we are eigher cecking 1 user or all f em
	 
		$where=array("timeline_item_active=1");
		$where[]="users.user_id!=0";
		$where[]="users.agent_id IN(".implode(',',$this->GetAgentIDs()).")";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete>".$this->GetLastView($params);
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
				ObjectFunctionAjaxPopup('Recent Updates','".get_class($this)."','".$this->id."','RecentActivityList','NULL','','user_id=".$params['user_id']."&user_ids=".implode(',',$params['user_ids'])."',function(){});
			});");
			Javascript::End();	
		}
	}

	public function RecentActivityList($params=array())
	{
		$where=array("timeline_item_active=1");
		$where[]="users.user_id!=0";
		$where[]="users.agent_id IN(".implode(',',$this->GetAgentIDs()).")";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete>".$this->GetLastView($params,'MIN');
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
			$coordinator_to_user=new coordinator_to_user();
			$coordinator_to_user->CreateFromKeys(array('coordinator_id','user_id'),array($this->id,$user_id));
			if($coordinator_to_user->Get('coordinator_to_user_last_view')<$timeline_item->Get('timeline_item_complete'))
			{
				$d=new date();
				$d->SetTimestamp($timeline_item->Get('timeline_item_complete'));
				$user=new user($timeline_item->Get('user_id'));
				echo("<div class='timeline_item_info'>");
				echo("<div class='timeline_item_info_date'>".$d->GetDate('F j:')."</div>");
				if($params['user_id'])
					echo("<a class='timeline_item_info_title' href='#".$timeline_item->GetFieldName('anchor')."'>".$user->GetFullName().': '.$timeline_item->Get('timeline_item_title')."</a>");
				else
					echo("<a class='timeline_item_info_title' href='#".$timeline_item->GetFieldName('anchor')."'>".$timeline_item->Get('timeline_item_title')."</a>");
				echo("<div class='timeline_item_info_label'>Marked Complete By</div>");
				echo("<div class='timeline_item_info_completed_by'>".$timeline_item->Get('timeline_item_completed_by')."</div>");
				echo("</div>");
				
				$final_user_ids[$user->id]=$user->id;
			}
		}
		echo("</div>");
		
		foreach($final_user_ids as $user_id)
		{
			$coordinator_to_user=new coordinator_to_user();
			$coordinator_to_user->CreateFromKeys(array('coordinator_id','user_id'),array($this->id,$user_id));
			$coordinator_to_user->Set('coordinator_to_user_last_view',time());
			$coordinator_to_user->Update();
		}
	}

	public function DrawUCToggle($params)
	{
		$params['COE']=true;
		$this->transaction_handler_DrawUCToggle($params);
	}
	

	public function SendAgentReminders($params=array())
	{
		global $HTTP_POST_VARS;	
		if($HTTP_POST_VARS['timeline_reminders'])
		 	$where=array("agent_id>0 AND agent_id IN (".implode(',',$HTTP_POST_VARS['timeline_reminders']).")");		 	 
		else if($params['agent_ids'])
		 	$where=array("agent_id>0 AND agent_id IN (".implode(',',$params['agent_ids']).")");		 	 
		else 	
			return;
	  	$list=new DBRowSetEX('agents','agent_id','agent',implode(' AND ',$where));
		$list->Retrieve();
		$list->Each('SendReminder');
	}

	public function SendAgentLoginRemindersPopup($params=array())
	{
		global $HTTP_POST_VARS;

		$user=new user($params['user_ids'][0]);		
		$agent=new agent($user->Get('agent_id'));

		$js2="ObjectFunctionAjax('coordinator','".$this->id."','ListUsers','".$this->GetFieldName('ListUsersContainer')."','NULL','','',function(){height_handler();});";
		$js="ObjectFunctionAjax('".get_class($this)."','".$this->id."','SendAgentLoginRemindersPopup','popup_content','".$this->GetFieldName('SendAgentReminders')."','','action=".$this->GetFieldName('SendAgentReminders')."&user_ids[]=".$user->id."',function(){".$js2."height_handler();});";

		form::Begin('','POST',false,array('id'=>$this->GetFieldName('SendAgentReminders')));
		echo("<H1>Login Reminder</H1>");
		echo("<div class='line'>Send Reminder to ".$agent->Get('agent_name')." Re: ".$user->Get('user_name')."</div>");
	 	if($params['action']==$this->GetFieldName('SendAgentReminders'))
	 	{
			$params['message']=$HTTP_POST_VARS['message'];
			$this->SendAgentLoginReminders($params);
			
			echo("<div class='message'>Reminder Has Been Sent</div>");
			echo("<div class='line'>");
			form::DrawButton('','Close',array('onclick'=>'PopupClose();'));
			echo("</div>");
		}
		else
		{		
			echo("<div class='line'>");
			form::DrawTextArea('message',$HTTP_POST_VARS['message'],array('placeholder'=>'Optional: Add a message to '.$agent->Get('agent_name')));
			echo("</div>");
			echo("<div class='line'>");
			form::DrawButton('','Send Reminder',array('onclick'=>$js));
			echo("</div>");
		}
		echo("</div>");
		echo("</div>");
	}

	public function SendAgentLoginReminders($params=array())
	{
		global $HTTP_POST_VARS;	
		foreach($params['user_ids'] as $user_id)
		{
			
			$user=new user($user_id);
			$agent=new agent($user->Get('agent_id'));
			$agent->SendLoginReminder($user,$params);
		}
	}


	public function SendAgentWelcomeEmails($params=array())
	{
		global $HTTP_POST_VARS;	
		if($params['agent_ids'])
		 	$where=array("agent_id>0 AND agent_id IN (".implode(',',$params['agent_ids']).")");		 	 
		else 	
			return;
			
	  	$list=new DBRowSetEX('agents','agent_id','agent',implode(' AND ',$where));
		$list->Retrieve();
		$list->Each('SendWelcomeMessage');
	}	

	
	public function SendNotifications()
	{
		$where=array("timeline_item_active=1");
		$where[]="agent_id IN(".implode(',',$this->GetAgentIDs()).")";
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
		$where[]="users.agent_id IN(".implode(',',$this->GetAgentIDs()).")";
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
		$where[]="users.agent_id IN(".implode(',',$this->GetAgentIDs()).")";

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

	
};


class agent_to_coordinator extends DBRowEx
{
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('agents_to_coordinators','agents_to_coordinators_id');
		$this->Retrieve();
	}
	
	public function GetStatus()
	{
		if($this->Get('agents_to_coordinators_rejected'))	
			return "Rejected";
		if($this->Get('agents_to_coordinators_accepted'))	
			return "Accepted";
		return "Pending";
	}
	
	public function Decline()
	{
		$this->Set('agents_to_coordinators_rejected',time());
		$this->Update();
	}
	
	public function DoAction($action)
	{
		parent::DoAction($action);
		if($action==$this->GetFormAction('decline'))
			$this->Decline();
	}

	
};

class coordinator_to_user extends DBRowEx
{
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('coordinators_to_users','coordinators_to_users_id');
		$this->Retrieve();
	}
};

?>