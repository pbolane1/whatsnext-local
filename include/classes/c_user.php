<?php
//**************************************************************//
//	
//	FILE: c_user.php
//  CLASS: user
//  
//	STUBBED BY: PoCo Technologies LLC CoreLib Autocoder v0.0 BETA
//  PURPOSE: database abstraction for the users table
//  STUBBED TIMESTAMP: 1212155116
//
//**************************************************************//

class user extends DBRowEx
{
	use public_user;

	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('users','user_id');
		$this->Retrieve();
	}

	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{
			$today=new Date();
			$this->Set('user_reset_date',$today->GetDBDate());
			$this->Set('user_active',1);

			$this->Set('user_agent_only_notifications',1);
		}
	}

	public function GetName()
	{
		return $this->GetFullName();
	}

	public function GetPhone()
	{
		$user_contact=new user_contact();
		$user_contact->InitByKeys(array('contact_id','user_contact_primary'),array($this->id,1));
		return $user_contact->Get('user_contact_phone');
	}

	public function GetEmail()
	{
		$user_contact=new user_contact();
		$user_contact->InitByKeys(array('contact_id','user_contact_primary'),array($this->id,1));
		return $user_contact->Get('user_contact_email');
	}

	public function GetSettings()
	{
		return false;
	}


	public function GetFullName()
	{
		if($this->Get('user_address'))
			return $this->Get('user_name')." - ".$this->Get('user_address');
		return $this->Get('user_name');
	}

	public function GetPropertyName()
	{
		if($this->Get('user_address'))
			return $this->Get('user_address');
		if($this->Get('user_type')=='BUYER')
			return 'Your Real Estate Purchase';
		if($this->Get('user_type')=='SELLER')
			return 'Your Real Estate Sale';

		return 'Your Real Estate Transaction';
	}


	public function GetBannerText()
	{
		$parts=array();
		$parts[]=$this->Get('user_name');
		if($this->Get('user_type')=='BUYER')
			$parts[]='Purchase';
		if($this->Get('user_type')=='SELLER')
			$parts[]='Sale';
		if($this->Get('user_address'))
			$parts[]=' of '.$this->Get('user_address');
		return implode(' ',$parts);
	}

	function GetUserType()
	{
		if($this->Get('user_type')=='BUYER')
			return "Buyer";
		if($this->Get('user_type')=='SELLER')
			return "Seller";		
	}
		
	public function DeleteLink()
    {
		if($this->Get('user_active'))
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('user_active'),0);
			form::DrawSubmit('','DELETE',array('onclick'=>"return confirm('Are you sure you want to disable this user?');"));
			form::end();
		}
		echo("</td>");
	}

	public function EditLink()
    {
		if(!$this->Get('user_active'))
		{	
		 	echo("<td class='edit_actions'>");
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('user_active'),1);
			form::DrawSubmit('','RE-ACTIVATE',array('onclick'=>"return confirm('Are you sure you want to reactivate this user?');"));
			form::end();			
		}
		else
		{
			parent::EditLink();

			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('reset').$this->GetFormExtraParams());
			form::DrawSubmit('','Reset',array('onclick'=>"return confirm('Are you sure you want to reset this user's tempalte?');"));
			form::end();


		}
	}

	public function DoAction($action)
	{
	 	parent::DoAction($action);
	 	if($action==$this->GetFormAction('reset'))
			$this->ResetTemplates();
	 	if($action==$this->GetFormAction('undelete'))
	 	{
			$this->Set('user_order',10000);
			$this->Set('user_active',1);
			$this->Update();
			
			$agent = new agent($this->Get('agent_id'));
			activity_log::Log($agent,'ACCOUNT_RESTORED','Account Restored',$this->id);
			
		}
	 	if($action==$this->GetFormAction('toggle_under_contract'))
	 	{
			$this->Set('user_under_contract',$this->Get('user_under_contract')?0:time());
			$this->Set('user_key_dates_sent',0)	;
			if(!$this->Get('user_under_contract'))
			{
				$this->Set('user_image_file','');
				$this->Set('user_address','');
				$this->Set('user_mls_listing_url','');
				$this->Set('user_property_url','');				
			}
			$this->Update();

			$agent = new agent($this->Get('agent_id'));
			if($this->Get('user_under_contract'))
				activity_log::Log($agent,'ACCOUNT_UNDER_CONTRACT','Account Marked As Under Contract',$this->id);
			else
				activity_log::Log($agent,'ACCOUNT_UNDER_CONTRACT','Account Marked As Not Under Contract',$this->id);

		}
	 	if($action==$this->GetFormAction('delete_permanent'))
	 	{
			$this->SetFlag('PERMENENT_DELETE');
			$this->Delete();
		}	
		if($action==$this->GetFormAction('under_contract'))
		{
			$this->Set('user_under_contract',time());
			$agent = new agent($this->Get('agent_id'));
			activity_log::Log($agent,'ACCOUNT_UNDER_CONTRACT','Account Marked As Under Contract',$this->id);
			$this->Update();
		}
		if($action==$this->GetFormAction('send_reminder'))
		{
		 	$agent=new agent($this->Get('agent_id'));
			$agent->SendReminders(array('user_ids'=>array($this->id)));
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
		if($action==$this->GetFormAction('user_initialized'))
		{
			$this->Set('user_initialized',time());
			$this->Update();
		}
		if($action==$this->GetFormAction('archive_transaction'))
		{
			$this->ArchiveTransaction();	
		}
	}	

	function ArchiveTransaction()
	{
		$this->Set('user_active',0);
		$this->Update();

	 	$where=array("user_id='".$this->id."'");
	  	$list=new DBRowSetEX('activity_log','activity_log_id','activity_log',implode(' AND ',$where),'activity_log_timestamp');
		$list->num_new=0;
	  	$list->Retrieve();
		$list->ProcessAction();
	  	$list->Retrieve();

		$filename=mod_rewrite::ToURL($this->Get('user_name')).'-Activity-Log'.'.csv';
		$temp_file_path = file::GetPath('temp').$filename;
		$f=fopen($temp_file_path,'w');
		
		// Check if file was opened successfully
		if($f === false) {
			// Log error and continue without CSV
			error_log("Failed to create CSV file: " . $temp_file_path);
			$f = null;
		} else {
			// Add header row with client info and archive date
			$archive_date = new date();
			$archive_date->SetTimestamp(time());
			$header_text = "Activity Log for ".$this->Get('user_name')." - ".$this->GetPropertyName()." - Archived ".$archive_date->GetDate('F j, Y \a\t g:ia');
			fwrite($f,'"'.$header_text.'"'.",");
			fwrite($f,",");
			fwrite($f,",");
			fwrite($f,",");
			fwrite($f,"\r\n");
			
			fwrite($f,"Date/Time".",");
			fwrite($f,"Action".",");
			fwrite($f,"Performed By".",");
			fwrite($f,"IP".",");
			fwrite($f,"\r\n");
		}
		
		if(!count($list->items))
			echo("<tr><td class='emptyset' colspan='100'>There is no activity to display</tr>");	
		
		foreach($list->items as $activity_log)
		{
			if($f !== null) {
				$class=$activity_log->Get('foreign_class');
				$object=new $class($activity_log->Get('foreign_id'));
				$d=new date();
				$d->SetTimestamp($activity_log->Get('activity_log_timestamp'));
				fwrite($f,'"'.$d->GetDate('m/d/Y h:i a').'"'.',');
				fwrite($f,'"'.$activity_log->Get('activity_log_details').'"'.',');
				fwrite($f,'"'.$activity_log->Get('activity_log_name').'"'.',');
				fwrite($f,'"'.$activity_log->Get('activity_log_ip').'"'.',');
				fwrite($f,"\r\n");
			}
		}
		
		if($f !== null) {
			fclose($f);
		}

		$agent=new agent($this->Get('agent_id'));
		
		$subject="Activity log for archived transaction - ".$this->Get('user_name');		
		$mail_params=array();
		foreach($this->attributes as $k=>$v)
			$mail_params[$k]=$v;
		foreach($agent->attributes as $k=>$v)
			$mail_params[$k]=$v;
		$mail_params['user_property_name']=$this->GetPropertyName();
		
		// Generate opt-out link for email template
		$opt_out_url = _navigation::GetBaseURL() . "pages/agents/index.php?action=opt_out&user_id=" . $this->id . "&token=" . md5($this->id . $this->Get('user_email') . 'opt_out');
		$mail_params['opt_out_link'] = $opt_out_url;
		
		// Add missing template variables
		$mail_params['user_url'] = _navigation::GetBaseURL() . "pages/users/index.php?user_id=" . $this->id;
		$mail_params['user_image_file'] = $this->Get('user_image_file') ? _navigation::GetBaseURL() . "dynamic/images/users/" . $this->Get('user_image_file') : '';
		$mail_params['agent_image_file1'] = $agent->Get('agent_image_file1') ? _navigation::GetBaseURL() . "dynamic/images/agents/" . $agent->Get('agent_image_file1') : '';
		
		$headers=array();
		$files=array();
		
		// Only attach file if it was successfully created and exists
		if($f !== null && file_exists($temp_file_path)) {
			$files[$filename] = $temp_file_path;
			error_log("CSV file created successfully: " . $temp_file_path . " (size: " . filesize($temp_file_path) . " bytes)");
		} else {
			error_log("CSV file creation failed or file doesn't exist: " . $temp_file_path);
			$files = array(); // Ensure files is empty if CSV creation failed
		}
		
		// Debug logging
		error_log("Email parameters - Subject: " . $subject);
		error_log("Email parameters - Files count: " . count($files));
		error_log("Email parameters - Mail params keys: " . implode(', ', array_keys($mail_params)));
		error_log("Email parameters - Opt out link: " . ($mail_params['opt_out_link'] ?? 'NOT SET'));
		
		// Send emails to agent and coordinators (restored original functionality)
		$emails=array();
		$emails[]=$agent->Get('agent_email');
		foreach($agent->GetCoordinatorIDs() as $coordinator_id)
		{
			$coordinator=new coordinator($coordinator_id);
			$emails[]=$coordinator->Get('coordinator_email');
		}
		
		email::SetMailer('PHPMAILER');
		foreach($emails as $email) {
			error_log("Sending email to: " . $email . " with " . count($files) . " attachments");
			$result = email::templateMail($email,email::GetEmail(),$subject,file::GetPath('email_activity_log'),$mail_params+array('base_url'=>_navigation::GetBaseURL()),'FROM:'.email::GetEmail(),$files);
			error_log("Email send result: " . ($result ? 'SUCCESS' : 'FAILED'));
		}
		
		// Clean up CSV file after emailing
		if($f !== null && file_exists($temp_file_path)) {
			unlink($temp_file_path);
		}
	}

	public function ResetTemplates()
	{
		timeline_item::CopyAll(array('template_id'=>$this->Get('template_id'),'agent_id'=>$this->Get('agent_id'),'user_id'=>0),array('agent_id'=>$this->Get('agent_id'),'user_id'=>$this->id));
		$template=new template($this->Get('template_id'));
		$this->Set('user_headline',$template->Get('template_headline'));
		$this->Set('user_content',$template->Get('template_content'));
		$this->Update();
	}

	public function DisplayDashboard()
	{
		$agent=new agent($this->Get('agent_id'));

		$progress_where="(timeline_item_for='AGENT' OR timeline_item_for='USER')";
		$total=$this->CountSteps($progress_where);
		$done=min($this->CountSteps($progress_where." AND timeline_item_complete>0"),$total);
		$percent=round(($done*100)/$total);

		echo("<div class='client_intro'>");
		echo("<h2>".$this->Get('user_address')."</h2>");
		echo("<div class='client_intro_image'>");
		if($this->Get('user_image_file'))
			echo("<img src='".$this->GetThumb(1170,513)."'>	");
		else
			echo("<br>");
		echo("</div>");
		if($this->Get('user_content'))
			echo("<div class='client_intro_content'>".$this->Get('user_content')."</div>");
		echo("</div>");

		echo("<div class='client_dashboard'>");
		echo("<div class='client_dashboard_body'>");

/*
		echo("<div class='row'>");
		echo("<div class='col col-md-3 hidden-xs'><div class='box_inner'><br></div></div>");
		echo("<div class='col col-md-3 col-xs-6'><div class='box_inner'>");
		$this->DisplayProgress(array('where'=>$progress_where));
		echo("</div></div>");					
		echo("<div class='col col-md-3 col-xs-6'><div class='box_inner'>");
		echo("<div class='col_content client_dashboard_steps'>");
		echo("<h3>Step ".($done)." of ".$total."</h3>");
		echo("</div>");					
		echo("</div></div>");					
		echo("<div class='col col-md-3 hidden-xs'><div class='box_inner'><br></div></div>");
		echo("</div>");					
*/

		if($this->Get('user_under_contract'))
		{
		 	$today=new date();
			$contract_date=new contract_date();
			$contract_date->InitByKeys(array('contract_date_special'),array('CLOSE_ESCROW'));
			$user_contract_date=new user_contract_date();
			$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($contract_date->id,$this->id));
			echo("<div class='client_dashboard_next_date'>");				
			echo("<h3>You Close Escrow in</h3>");
		 	$d=new DBDate($user_contract_date->Get('user_contract_date_date'));
			echo("<div class='col_content client_dashboard_calendar agent_color1 agent_bg_color1'>");				
			echo("<div class='client_dashboard_calendar_text agent_color1'>");
			echo(round(date::GetDays($today,$d)));
			echo('<br>');
			echo('Days');
			echo("</div>");					
			echo("</div>");					
			echo("</div>");					
		}

		$timeline_items=$this->GetTimeLineItems(array("timeline_item_for='USER'"));
		if($timeline_items->GetTotalAvailable())
		{
			echo("<div class='client_dashboard_next_timeline_item'>");
			echo("<h3>What's <span class='next agent_color1'>Next ?</span></h3>");				
			$timeline_item=$timeline_items->items[0];
		 	$d=new DBDate($timeline_item->Get('timeline_item_date'));
			echo("<a href='/users/#".$timeline_item->GetFieldName('anchor')."'>".$timeline_item->Get('timeline_item_title')."</a> ");
			if($d->ISValid())
				echo(" <a href='/users/#".$timeline_item->GetFieldName('anchor')."'>".$d->GetDate('F j')."</a>");
			echo("</div>");					
		}
/*		
		$today=new date();
		$where=array("user_id='".$this->id."'");
		$where[]="user_contract_date_key_date=1";
		$where[]="user_contract_date_na=0";
		$where[]="user_contract_date_date>'".$today->GetDBDate()."'";
	  	$user_contract_dates=new DBRowSetEX('user_contract_dates','user_contract_date_id','user_contract_date',implode(' AND ',$where),'user_contract_date_date',1);
		$user_contract_dates->Retrieve();
		if($user_contract_dates->GetTotalAvailable())
		{
			echo("<div class='client_dashboard_next_date'>");				
			foreach($user_contract_dates->items as $user_contract_date)
			{
				$contract_date=new contract_date($user_contract_date->Get('contract_date_id'));
				echo("<h3>".$contract_date->Get('contract_date_name')."</h3>");
			}		
			echo("</div>");					
			foreach($user_contract_dates->items as $user_contract_date)
			{
			 	$d=new DBDate($user_contract_date->Get('user_contract_date_date'));
				echo("<div class='col_content client_dashboard_calendar agent_color1 agent_bg_color1'>");				
				echo("<div class='client_dashboard_calendar_text'>");
				echo(round(date::GetDays($today,$d)));
				echo('<br>');
				echo('Days');
				echo("</div>");					
				echo("</div>");					
			}	
		}
*/
		echo("</div>");					
		echo("</div>");					
	}

	public function GetTimeLineItems($params=array())
	{
		$today=new date();
		$where=array("user_id='".$this->id."' AND timeline_item_active=1");
//		$where[]="timeline_item_for!='AGENT'";
		//$where[]="timeline_item_agent_only=0";
		//$where[]="timeline_item_type='TIMELINE'";
		if($this->Get('user_under_contract'))
			$where[]="(timeline_item_type='TIMELINE' OR timeline_item_hide_uc=0)";
		$where[]="timeline_item_complete=0";
		$where[]="timeline_item_active=1";
		$where[]="(timeline_item_date>='".$today->GetDBDate()."' OR timeline_item_reference_date_type='NONE')";
		if($params['where'])
			$where[]=$params['where'];
			
		$order='timeline_item_order';
		if($this->Get('user_under_contract'))
			$order='timeline_item_date,timeline_item_order';


	  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order);
		$timeline_items->Retrieve();

		if($params['debug'])
			die($timeline_items->GetQuery());
		
		return $timeline_items;
	}

	public function DisplayProgress($params=array())
	{
		$agent=new agent($this->Get('agent_id'));

		$where="1";
		if($params['where'])
			$where=$params['where'];

		$total=$this->CountSteps($where);
		$done=min($this->CountSteps($where." AND timeline_item_complete>0"),$total);
		$percent=($total>0)?round(($done*100)/$total):0;
		$steps=10;

		//no steps?  you gonna crash.
		if(!$total)
			return;

		if(!$params['id'])
			$params['id']=$this->GetFieldName('progress');
		if(!$params['width'])
			$params['width']=150;
		if(!$params['height'])
			$params['height']=150;
		if(!$params['line-height'])
			$params['line-height']=22;
		if(!$params['font-size'])
			$params['font-size']=25;
		if(!$params['font-weight'])
			$params['font-weight']='bold';
		if(!$params['duration'])
			$params['duration']=1400;
//		if(!$params['color'])
//			$params['color']=$agent->Get('agent_color1_hex');
		if(!$params['color'])
			$params['color']=colors::GetDarkest(array($agent->Get('agent_color1_hex')=>$agent->Get('agent_color1_hex'),$agent->Get('agent_color2_hex')=>$agent->Get('agent_color2_hex')));			
		if(!$params['trailColor'])
			$params['trailColor']='#CCCCCC';
		if(!$params['strokeWidth'])
			$params['strokeWidth']=12;
		if(!$params['trailWidth'])
			$params['trailWidth']=12;


		echo("<style type='text/css'>");
		echo("#".$params['id']."{width:".$params['width']."px;height:".$params['width']."px;position: relative;}");
		echo("#".$params['id']."_progress {text-align:center;color:#000000;position: absolute;top:".(($params['height']-($params['line-height']))/2)."px;;left:0px; width:100%;height:100%;line-height:".$params['line-height']."px;font-size:".$params['font-size']."px;font-weight:".$params['font-weight']."}");		
		echo("</style>");
		
		echo("<div class='progress-cirlce' id='".$params['id']."'><div class='progress-cirlce-progress' id='".$params['id']."_progress'></div></div>");					
		Javascript::Begin();
		echo("jQuery(function(){
				var ".$params['id']."_bar = new ProgressBar.Circle(".$params['id'].", {
				    strokeWidth: ".$params['strokeWidth'].",
					easing: 'easeInOut',
					duration: ".$params['duration'].",
					color: '".$params['color']."',
				  	trailColor: '".$params['trailColor']."',
					trailWidth: ".$params['trailWidth'].",
	 				svgStyle: null
				});
				".$params['id']."_bar.animate(".($percent/100).");  // Number from 0.0 to 1.0	
				
					let progress = 0;
				    let count = 0;
					let interval=setInterval(() => {
					progress+=".($percent/$steps).";
					if(progress>".$percent.")
						progress=".$percent.";
					jQuery('#".$params['id']."_progress').html(Math.round(progress)+'%');
				    if((count++)>=".$steps.")
				    	clearInterval(interval);				    	
				}, 100);				
			});
		");	
		Javascript::End();
	}


	public function X_DisplayProgress()
	{
		$agent=new agent($this->Get('agent_id'));

		$total=$this->CountSteps("timeline_item_for!='AGENT'");
		$done=min($this->CountSteps("timeline_item_for!='AGENT' AND timeline_item_complete>0"),$total);
		$percent=round(($done*100)/$total);
		$steps=10;

		echo("<div class='col_content client_dashboard_percent cpb-progress-container' id='client_dashboard_percent'>");
		echo("</div>");					
		Javascript::Begin();
		echo("jQuery(function(){
				let progressbar = new CircularProgressBar(160, 160, 'client_dashboard_percent', {
			            strokeSize: 10,
			            showProgressNumber:true,
			            backgroundColor: '#FFFFFF',
			            strokeColor: '".$agent->Get('agent_color1_hex')."',
			            showProgressNumber: true
				    });
				progressbar.showProgressNumber(true);
				let progress = 0;
				let count = 0;
				let interval=setInterval(() => {
					progress+=".($percent/$steps).";
					if(progress>".$percent.")
						progress=".$percent.";
				    progressbar.setProgress(Math.round(progress));
					jQuery('.progress-text').html(Math.round(progress)+'%<br>complete');
				    if((count++)>=".$steps.")
				    	clearInterval(interval);				    	
				}, ".$params['duration']."/".$steps.");	
			});
		");	
		Javascript::End();
	}
	
	public function DisplayProgressPopup($params=array())
	{
		$agent=new agent($this->Get('agent_id'));

		$total=$this->CountSteps("timeline_item_for!='AGENT'");
		$done=min($this->CountSteps("timeline_item_for!='AGENT' AND timeline_item_complete>0"),$total);
		$percent=round(($done*100)/$total);

		echo("<div class='progress_popup'>");
		echo("<div class='client_dashboard'>");
		echo("<div class='client_dashboard_steps'>");
		echo("<h3>You Are</h3>");
		echo("</div>");
		$this->DisplayProgress(array('id'=>$this->GetFieldName('progress_popup')));
		echo("<div class='client_dashboard_steps'>");
		echo("<h3>Step ".($done)." of ".$total."</h3>");
		echo("</div>");					
		echo("</div>");					
	}

	public function CountSteps($add_where=1)
	{
		$where=array("user_id='".$this->id."' AND timeline_item_active=1");
		//$where[]="timeline_item_for IN('USER')";
		$where[]="timeline_item_type='TIMELINE'";		
		$where[]="timeline_item_active=1";		
		//$where[]="timeline_item_agent_only=0";		
		if($add_where)
			$where[]="(".$add_where.")";
	  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_order');
	  	$timeline_items->Retrieve();

	 	$user_conditions_condition_ids=array(-1);
	  	$user_conditions=new DBRowSetEX('user_conditions','user_condition_id','user_condition',"user_id='".$this->Get('user_id')."' AND user_condition_checked=1");
		$user_conditions->Retrieve();
		foreach($user_conditions->items as $user_condition)
		 	$user_conditions_condition_ids[]=$user_condition->Get('condition_id');

		//OPTIMIZE THIS? TRY TO DO AS A SINLE QUERY?//
		//OR EASY WAY TO SEE IF CONDITIONS N/A
		$count=0;
	  	foreach($timeline_items->items as $timeline_item)
	  	{
			$user_contract_date=new user_contract_date();
			$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($timeline_item->Get('timeline_item_reference_date'),$this->id));

			//optimized out..
		  	//$conditions_to_timeline_items=new DBRowSetEX('conditions_to_timeline_items','condition_id','condition',"condition_to_timeline_item_action='HIDE' AND timeline_item_id='".$timeline_item->id."' AND condition_id IN(".implode(',',$user_conditions_condition_ids).")");
			//$conditions_to_timeline_items->Retrieve();
			//if(!count($conditions_to_timeline_items->items) and !$user_contract_date->Get('user_contract_date_na') and !$timeline_item->DependsOnIncompleteItem())

			$rec=database::fetch_array(database::query("SELECT COUNT(1) AS cnt FROM conditions_to_timeline_items WHERE condition_to_timeline_item_action='HIDE' AND timeline_item_id='".$timeline_item->id."' AND condition_id IN(".implode(',',$user_conditions_condition_ids).")"));
			if(!$rec['cnt'] and !$user_contract_date->Get('user_contract_date_na') and !$timeline_item->DependsOnIncompleteItem())
				$count++;
		}
		return $count;
	}

	public function DisplayIntro()
	{
	 	$template=new template($this->Get('template_id'));
	 
		echo("<div class='client_intro'>");
		echo("<h2>".$this->Get('user_address')."</h2>");

		echo("<div class='client_intro_image'>");
		if($this->Get('user_image_file'))
			echo("<img src='".$this->GetThumb(1170,513)."'>	");
		else
			echo("<br>");
		echo("</div>");
		echo("<div class='client_content'>".$this->Get('user_content')."</div>");
		echo("</div>");	
	}
	
	public function DisplayHeading()	
	{
		echo("<div class='client_intro_headline'>");
		echo("<h2>".$this->Get('user_headline')."</h2>");
		echo("</div>");	
	}
	
	public function DisplayTimeline()
	{
		echo("<div class='timeline'>");
		$where=array("user_id='".$this->id."' AND timeline_item_active=1");
		//$where[]="timeline_item_for IN('USER')"; //no; this hides agent action items that are not agent only
		$where[]="timeline_item_for!='AGENT'";
		//$where[]="timeline_item_agent_only=0";		
		if($this->Get('user_under_contract'))
			$where[]="(timeline_item_type='TIMELINE' OR timeline_item_hide_uc=0)";
		$order='timeline_item_order';
		if($this->Get('user_under_contract'))
			$order='timeline_item_date,timeline_item_order';
		if(!Session::Get($this->GetFieldName('show_completed')) and $this->Get('user_active'))
			$where[]="timeline_item_complete=0";		
//		if($this->Get('user_under_contract'))
//			$order="CASE WHEN timeline_item_reference_date_type='NONE' OR timeline_item_date<'1970-01-01' THEN timeline_item_order ELSE timeline_item_date END,timeline_item_order";

	  	$list=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order);
	  	$list->Retrieve();
		$list->ProcessAction();
	  	$list->SetFlag('WHICH','USER');
	  	$list->SetFlag('AGENT',$this->GetFlag('AGENT'));
		foreach($list->items as $timeline_item)
		{
			echo("<div id='".$timeline_item->GetFieldName('AgentCardContainer')."'>");			
			$timeline_item->DisplayFull($params);
			echo("</div>");
		}
		echo("</div>");
	}

	public function UserToolsFilterPopup($params=array())
	{
		global $HTTP_POST_VARS;
		
		if($params['action']=='UpdateUserToolsFilters')
		{
			Session::Set($this->GetFieldName('condensed_view'),$HTTP_POST_VARS[$this->GetFieldName('condensed_view')]);
			Session::Set($this->GetFieldName('show_completed'),$HTTP_POST_VARS[$this->GetFieldName('show_completed')]);
		}
		
		$js2="PopupClose();";
		$js2.="ObjectFunctionAjax('user','".$this->id."','UserTools','user_tools_container','NULL','','',function(){".$js3."});";
		$js2.="ObjectFunctionAjax('user','".$this->id."','DisplayTimeline','timeline_container','NULL','','');";
		$js="ObjectFunctionAjax('user','".$this->id."','UserToolsFilterPopup','popup_content','".$this->GetFieldName('UserToolsFilterForm')."','','action=UpdateUserToolsFilters&user_id=".$params['user_id']."',function(){".$js2."});return false;";

		form::Begin('?action=','POST',true,array('id'=>$this->GetFieldName('UserToolsFilterForm')));
		echo("<h3 class='agent_color1'>Filter Displayed Items</h3>");
		echo("<div class='line'>");
		echo("<label>");
		form::DrawCheckbox($this->GetFieldName('condensed_view'),1,Session::Get($this->GetFieldName('condensed_view')));
		echo(" Condensed View</label>");
		echo("</div>");	
		echo("<div class='line'>");
		echo("<label>");
		form::DrawCheckbox($this->GetFieldName('show_completed'),1,Session::Get($this->GetFieldName('show_completed')));
		echo(" Show Completed Items</label>");
		echo("</div>");	
		form::End();

		echo("<div class='line'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-6' style='text-align:center'>");
		echo("<a href='#' class='button agent_bgcolor1' onclick=\"".$js."return false;\">Apply</a>");
		echo("</div>");
		echo("<div class='col-xs-6' style='text-align:center'>");
		echo("<a href='#' class='button agent_bgcolor1' onclick=\"PopupClose();return false;\">Close</a>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
	}

	public function UserToolsXS($params)
	{
		//small size
		echo("<div class='agent_tools agent_tools_xs'>");

		$js="ObjectFunctionAjaxPopup('Filter Displayed Items','user','".$this->id."','UserToolsFilterPopup','NULL','','user_id=".$params['user_id']."',function(){});return false;";
		echo("<a class='agent_color1 agent_color2_hover' data-toggle='tooltip' title='Filter Displayed Items' href='#' onclick=\"".$js."\"><i class='icon fas fa-sliders-h'></i><span class='text'>Filter Displayed Items<span></a>");

		echo("</div>");
	}

	public function UserTools($params)
	{
		$this->ProcessAction();
	
		echo("<div class='agent_tools'><br></div>");				 //cheating a little bit here to buy some space and add the border
		echo("<div class='agent_tools_buttons'>");				
		echo("Filter Timeline View:");				
		if(Session::Get($this->GetFieldName('condensed_view')))
		{
			$js="ObjectFunctionAjax('user','".$this->id."','UserTools','user_tools_container','NULL','','action=".$this->GetFormACtion('toggle_condensed_view')."',function(){});return false;";
			Javascript::Begin();
			echo("jQuery('BODY').addClass('timeline_condensed');");
			echo("jQuery('.timeline_item_expanded').removeClass('timeline_item_expanded');");
			Javascript::End();
			echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."\"><span class='text'>Turn Off Condensed View<span></a>");
		}
		else
		{
			$js="ObjectFunctionAjax('user','".$this->id."','UserTools','user_tools_container','NULL','','action=".$this->GetFormACtion('toggle_condensed_view')."',function(){});return false;";
			Javascript::Begin();
			echo("jQuery('BODY').removeClass('timeline_condensed');");
			echo("jQuery('.timeline_item_expanded').removeClass('timeline_item_expanded');");
			Javascript::End();
			echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."\"></i><span class='text'>Turn On Condensed View<span></a>");
		}
		if(Session::Get($this->GetFieldName('show_completed')))
		{
			$js2="ObjectFunctionAjax('user','".$this->id."','DisplayTimeline','timeline_container','NULL','','');";
			$js="ObjectFunctionAjax('user','".$this->id."','UserTools','user_tools_container','NULL','','action=".$this->GetFormACtion('toggle_commpleted_view')."',function(){".$js2."});return false;";
			echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Hide Completed Items<span></a>");
		}
		else
		{
			$js2="ObjectFunctionAjax('user','".$this->id."','DisplayTimeline','timeline_container','NULL','','');";
			$js="ObjectFunctionAjax('user','".$this->id."','UserTools','user_tools_container','NULL','','action=".$this->GetFormACtion('toggle_commpleted_view')."',function(){".$js2."});return false;";
			echo("<a class='' data-toggle='tooltip' title='' href='#' onclick=\"".$js."return false;\"><span class='text'>Show Completed Items<span></a>");
		}
		echo("</div>");				
	}

	public function DisplaySidebar()
	{
		$where=array("user_id='".$this->id."'");
		$widgets=new DBRowSetEX('widgets','widget_id','widget',implode(' AND ',$where),'widget_order');
		$widgets->Retrieve();
		
		echo("<div class='sidebar'>");
		echo("<h2 class='agent_color1 agent_border_color1'>Helpful Links</h2>");
		$this->ListContactInfo();
		$this->DisplayKeyDates();
		$this->AddKeyDatesButton($params);
		$widgets->ListFull();
	 	echo("<div id='progress_meter_container_outer'>");
	 	echo("<div id='progress_meter_container'>");
		$this->ProgressMeter($params);
		echo("</div>");
		echo("</div>");
		echo("</div>");
	}

	public function AddKeyDatesButton($params=array())
	{
		if($this->Get('user_under_contract'))
		{		
			global $user_contact;
			
			$js="ObjectFunctionAjaxPopup('Add To Calendar','user','".$this->id."','AddToCalendarInfo','NULL','','user_id=".$this->id."&user_contact_id=".$user_contact->id."');";
			$js.="return false;";
			echo("<a class='button agent_bg_color1' href='#' onclick=\"".$js."return false;\"><i class='icon fas fa-calendar'></i><span class='text'>Add To Calendar<span></a>");
		}
	}

	public function ProgressMeter($params)
	{
	 	echo("<div class='progress_meter_full'>");
		echo("<h2 class=''>Progress Meter</h2>");
	 	echo("<div class='progress_meter'>");
		$total=$this->CountSteps("timeline_item_for='USER'");
		$done=min($this->CountSteps("timeline_item_for='USER' AND timeline_item_complete>0"),$total);
		if($total>0)
		{
			$percent=round(($done*100)/$total);
		 	echo("<h5>Client Items</h3>");
		 	echo("<div class='progress_bar_container'><div class='progress_bar agent_bg_color2' style='width:".($percent)."%'></div></div>");
		 	echo("<div class='progress_info'>".$percent."% Complete (".$done." of ".$total." items)</div>");
		}
		echo("</div>");


	 	echo("<div class='progress_meter'>");
		$total=$this->CountSteps("timeline_item_for='AGENT'");
		$done=min($this->CountSteps("timeline_item_for='AGENT' AND timeline_item_complete>0"),$total);
		if($total>0)
		{
			$percent=round(($done*100)/$total);
		 	echo("<h5>Agent Items</h3>");
		 	echo("<div class='progress_bar_container'><div class='progress_bar agent_bg_color1' style='width:".($percent)."%'></div></div>");
		 	echo("<div class='progress_info'>".$percent."% Complete (".$done." of ".$total." items)</div>");
		}
		echo("</div>");


	 	echo("<div class='progress_meter'>");
		$total=$this->CountSteps("timeline_item_for='AGENT' OR timeline_item_for='USER'");
		$done=min($this->CountSteps("(timeline_item_for='AGENT' OR timeline_item_for='USER') AND timeline_item_complete>0"),$total);
		if($total>0)
		{
			$percent=round(($done*100)/$total);
		 	echo("<h5>Overall</h3>");
		 	echo("<div class='progress_bar_container'><div class='progress_bar agent_bg_color1' style='width:".($percent)."%'></div></div>");
		 	echo("<div class='progress_info'>".$percent."% Complete (".$done." of ".$total." items).</div>");
		}
		echo("</div>");
		echo("</div>");

	}	

	public function ProgressMeterMobile($params)
	{
	 	echo("<div class='progress_meter_mobile'>");
	 	echo("<div class='progress_meter_mobile_short'>");
	 	echo("<div class='progress_meter'>");
		$total=$this->CountSteps("(timeline_item_for='AGENT' OR timeline_item_for='USER')");
		$done=min($this->CountSteps("(timeline_item_for='AGENT' OR timeline_item_for='USER') AND timeline_item_complete>0"),$total);
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

	public function ListContactInfo()
	{
		$agent=new agent($this->Get('agent_id'));
		
		if($agent->Get('agent_phone') and !$this->Get('user_sidebar_phone_off'))
			echo("<a class='button agent_bg_color1 agent_bg_color2_hover' href='tel:".$agent->Get('agent_phone')."'><i class='icon fas fa-phone'></i><span class='text'>Call Me<span></a>");
		if($agent->Get('agent_email') and !$this->Get('user_sidebar_email_off'))
			echo("<a class='button agent_bg_color1 agent_bg_color2_hover' href='mailto:".$agent->Get('agent_email')."'><i class='icon fas fa-envelope'></i><span class='text'>Email Me<span></a>");
		if($this->Get('user_mls_listing_url') and !$this->Get('user_sidebar_mls_off'))
			echo("<a class='button agent_bg_color1 agent_bg_color2_hover' target='_blank' href='".$this->Get('user_mls_listing_url')."'><i class='icon fas fa-home'></i><span class='text'>Link To MLS Listing<span></a>");
		if($this->Get('user_property_url') and !$this->Get('user_sidebar_property_url_off'))
			echo("<a class='button agent_bg_color1 agent_bg_color2_hover' target='_blank' href='".$this->Get('user_property_url')."'><i class='icon fas fa-home'></i><span class='text'>Link To Property Website<span></a>");
		if(trim($this->Get('user_address')) and !$this->Get('user_sidebar_directions_off') and ($this->Get('user_type')!='SELLER'))
			echo("<a class='button agent_bg_color1 agent_bg_color2_hover' target='_blank' href='https://www.google.com/maps/dir//".$this->Get('user_address')."'><i class='icon fas fa-directions'></i><span class='text'>Directions To Property<span></a>");
	}

	public function AddToCalendarInfo($params=array())
	{
		global $HTTP_POST_VARS;
		
	 	$user=new user($params['user_id']);
	 	$user_contact=new user_contact($params['user_contact_id']);

		$type=$HTTP_POST_VARS['type'];
		//if(!$type)
		//	$type='KEY_DATES';

		if($params['action']==$this->GetFormAction('calendar'))
		{
			$ical_url=_navigation::GetBaseURL()."/users/ical/".md5('user_contact'.$user_contact->id).'.ics?time='.time().'&type='.$type;

			echo("<div class='line'>");
			echo("<div>".html::ProcessTemplateFile(file::GetPath('add_to_calendar_info'),array('<ical_url/>'=>$ical_url))."</div>");
			echo("</div>");
			echo("<div class='line'>");
			echo("<a href='#' class='button agent_bgcolor1' onclick=\"PopupClose();\">Close</a>");
			echo("</div>");
		}
		else
		{
			$js="ObjectFunctionAjax('user','".$this->id."','AddToCalendarInfo','popup_content','".$this->GetFieldName('types')."','','action=".$this->GetFormAction('calendar')."&user_id=".$this->id."&user_contact_id=".$user_contact->id."');";
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



	public function DisplayKeyDates()
	{
	 	if(!$this->Get('user_under_contract'))
	 		return;
	 
		echo("<div class='toggle'>");
		echo("<a class='toggle_header agent_bg_color1 agent_bg_color2_hover' data-toggle='collapse' href='#toggle_key_dates' role='button' aria-expanded='false' aria-controls='toggle_key_dates' onclick=\"if(jQuery('I.icon',this).hasClass('fa-plus')){jQuery('I.icon',this).removeClass('fa-plus');jQuery('I.icon',this).addClass('fa-minus');}else{jQuery('I.icon',this).removeClass('fa-minus');jQuery('I.icon',this).addClass('fa-plus');}\"><i class='icon fas fa-plus'></i> Key Dates</a>");
		echo("<div class='toggle_body collapse' id='toggle_key_dates'>");
		echo("<div class='toggle_content'>");
		echo("<div class='key_dates'>");
		$where=array("user_id='".$this->id."'");
		$where[]="user_contract_date_key_date=1";
		$where[]="user_contract_date_na=0";
	  	$user_contract_dates=new DBRowSetEX('user_contract_dates','user_contract_date_id','user_contract_date',implode(' AND ',$where),'user_contract_date_date');
		$user_contract_dates->Retrieve();
		foreach($user_contract_dates->items as $user_contract_date)
		{
			$contract_date=new contract_date($user_contract_date->Get('contract_date_id'));
			$d=new DBDate($user_contract_date->Get('user_contract_date_date'));
			$today=new date();
			$today->Round();
			$class='';
			
			$complete=false;
			$days=date::GetDays($today,$d);
			if($days<0)
				$complete=true;
			
		 	if(!$complete and $days<0)
		 		$class='key_date_overdue';
		 	else if(!$complete and date::GetDays($today,$d)<2)
		 		$class='key_date_due';
		 	else if(!$complete)
		 		$class='key_date_upcoming';
		 		
		 	$days_text="";
		 	if($days<0)
		 		$days_text="<i class='fa fa-check key_date_completed'></i>";
		 	else if($days<0)
		 		$days_text='('.abs($days).' days past due)';
		 	else if($days==0)
		 		$days_text='(Today)';
		 	else if($days>0)
		 		$days_text='('.abs($days).' days left)';
		 			 		
			echo("<p class='".$class."'><b>".$contract_date->Get('contract_date_name')."</b>: ".$d->GetDate('M j')." ".$days_text."</p>");
		}		
		echo("</div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		
	}

	public function DrawFooter1()
	{
		if(!$this->id) 
			return;
		$agent=new agent($this->Get('agent_id'));
		$agent->DrawFooter1();
	}

	public function DrawFooter2()
	{
		if(!$this->id) 
			return;
		$agent=new agent($this->Get('agent_id'));
		$agent->DrawFooter2();
	}

	public function CustomCSS()
	{
		$agent=new agent($this->Get('agent_id'));
		$agent->CustomCSS();
	}
	
	public function DrawLogo()
	{
		if(!$this->id) 
			return;
		$agent=new agent($this->Get('agent_id'));
		$agent->DrawLogo();
	}	

	public function AgentCardJS($params=array(),$container='')
	{
		$js2='';
		if($params['edit'])
			$js2.="jQuery('#".$this->GetFormAction('edit')."').focus();";
		$js2.="height_handler();";

		$passparams=array();
		foreach($params as $k=>$v)
			$passparams[]=$k.'='.$v;
		$js="ObjectFunctionAjax('user','".$this->id."','AgentCard','".$container."','".$this->GetFieldName('AgentCardForm')."','','".implode('&',$passparams)."',function(){".$js2."});";
		
		return $js;
	}

	public function NewAgentCard($params=array())
	{
		$agent=new agent($this->Get('agent_id'));
		$js="ObjectFunctionAjax('agent','".$agent->id."','ListUsers','".$agent->GetFieldName('ListUsersContainer')."','null','','action=".$this->GetFormAction('save')."',function(){height_handler();});";
		echo("<div class='card card_new' onclick=\"".$js."\" data-info='CLIENTS_NEW' data-info-none='none'>");
		echo("<div class='box_inner'>");
		echo('<div class="card_heading">');
		echo("<h3><i class='fa fa-plus'></i> New Property</h3>");
		echo('</div>');
		echo('<div class="card_body">');
		echo('<div class="card_content">');
		echo("<br>");
		echo('</div>');
		echo('</div>');
		echo('</div>');
		echo('</div>');		
	}
	
	public function EditForm()
	{
		global $HTTP_POST_VARS;
	
		echo("<td colspan='2' align='center'></td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Name</td><td>");
		form::DrawTextInput($this->GetFieldName('user_name'),$this->Get('user_name'),array('class'=>$this->GetError('user_name')?'error':'text'));
		echo("</td></tr>");
//		echo("<tr><td class='label'>".REQUIRED." Type</td><td>");
//		form::DrawSelect($this->GetFieldName('user_type'),array('Buyer'=>'BUYER','Seller'=>'SELLER'),$this->Get('user_type'),array('class'=>$this->GetError('user_type')?'error':'text'));
//		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Template</td><td>");
		form::DrawSelectFromSQL($this->GetFieldName('template_id'),"SELECT * FROM templates WHERE agent_id='".$this->Get('agent_id')."' ORDER BY template_order",'template_name','template_id',$this->Get('template_id'),array('class'=>$this->GetError('template_id')?'error':'text'));
		echo("</td></tr>");

		echo("<tr><td class='label'>".REQUIRED." Email</td><td>");
		form::DrawTextInput($this->GetFieldName('user_email'),$this->Get('user_email'),array('class'=>$this->GetError('user_email')?'error':'text'));
		echo("</td></tr>");
		if(!$this->id)
		{
			echo("<tr><td class='label'>".REQUIRED." Password</td><td>");
			form::DrawTextInput($this->GetFieldName('user_password_new'),$HTTP_POST_VARS[$this->GetFieldName('user_password_new')],array('class'=>$this->GetError('user_password_new')?'error':'text'));
			echo("</td></tr>");
		}
		else
		{
			echo("<tr><td class='label'>Password</td><td>*******</td></tr>");
			echo("<tr><td class='label'>Change Password</td><td>");
			form::DrawTextInput($this->GetFieldName('user_password_new'),$HTTP_POST_VARS[$this->GetFieldName('user_password_new')],array('class'=>$this->GetError('user_password_new')?'error':'text'));
			echo("</td></tr>");
		}
		echo("<tr><td class='label'>".REQUIRED." Address</td><td>");
		form::DrawTextInput($this->GetFieldName('user_address'),$this->Get('user_address'),array('class'=>$this->GetError('user_address')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Link To MLS Listing</td><td>");
		form::DrawTextInput($this->GetFieldName('user_mls_listing_url'),$this->Get('user_mls_listing_url'));
		echo("</td></tr>");

		
		
		echo("<tr><td class='section' colspan='2'>Home Image</td></tr>");
		if($this->Get('user_image_file'))
		{
			echo("<tr><td class='label'>Image</td><td>");
			echo("<img src='".$this->GetThumb(1170/4,513/4,true)."'>");
			echo("</td></tr>");
		}		
		echo("<tr><td class='label'>Upload Image</td><td><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('user_image_file_ul'),'',array('class'=>$this->GetError('news_title')?'error':'file'));
		echo("</td></tr>");			

		echo("<tr><td class='label'>".REQUIRED." user_mls_listing_url</td><td>");
		form::DrawTextInput($this->GetFieldName('user_headline'),$this->Get('user_headline'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Content</td><td>");
		form::DrawTextArea($this->GetFieldName('user_content'),$this->Get('user_content'),array('class'=>'wysiwyg wysiwyg_input wysiwyg_intro'));
		form::MakeWYSIWYG($this->GetFieldName('user_content'),'SIMPLE_LINK_HEADLINES');
		echo("</td></tr>");

		echo("<tr><td colspan='2' class='save_actions'>");		
 	}

	public function PopupEdit($params)
	{
		$this->ProcessAction();
		if($this->saved)
		{
			echo("<div class='message success'>Your Changes Have Been Saved</div>");
			return;
		}
		else if(count($this->errors))
			echo("<div class='error'>".implode('<br>',$this->errors)."</div>");
		
		$this->Set('agent_id',$params['agent_id']);
		$this->Set('user_id',$params['user_id']);
		
		form::Begin('?action=','POST',false,array('id'=>$this->GetFieldName('save')));
		form::DrawHiddenInput($this->GetFieldName('user_id'),$this->Get('user_id'));
		form::DrawHiddenInput($this->GetFieldName('agent_id'),$this->Get('agent_id'));
		echo("<table class='listing'>");
		echo("<tr><td class='edit_wrapper'>");
		echo("<table class='edit_wrapper'><tr>");
		$this->PreserveInputs();
		$this->EditForm();
		$js3="ObjectFunctionAjax('agent','".$params['agent_id']."','EditIntro','intro_container','NULL','','agent_id=".$params['agent_id']."&user_id=".$params['user_id']."&agent=1');";
		$js3.="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditTimeline','timeline_container','NULL','','agent_id=".$params['agent_id']."&user_id=".$params['user_id']."&agent=1');";
		$js2="if(jQuery('#popup_content .success').length){".$js3."PopupClose();};";
		$js="ObjectFunctionAjax('user','".$this->Get('user_id')."','PopupEdit','popup_content','".$this->GetFieldName('save')."','','action=".$this->GetFormAction('save')."&agent_id=".$params['agent_id']."&user_id=".$params['user_id']."&agent=1',function(){".$js2."});";
		//have to force tiny mce to update text areas;
		$js0="UpdateTinyMCE();";
		form::DrawButton('','Save',array("onclick"=>$js0.$js));
		echo("</tr></table>");		
		echo("</td></tr></table>");				
		form::End();
	}

	public function GatherInputs()
	{
		//parent is default
		parent::GatherInputs();

//		$this->Set('user_intro_headline',wysiwyg::GatherCleanContent($this->Get('user_intro_headline')));		
//		$this->Set('user_intro_content',wysiwyg::GatherCleanContent($this->Get('user_intro_content')));		

		$this->GatherFile($this->GetFieldName('user_image_file_ul'),'user_image_file');		
		
	}

	public function ValidateInputs()
	{
		global $HTTP_POST_VARS;
		
		if($this->GetFlag('ALLOW_BLANK'))
			return true;

		if(!$this->Get('user_name') and $HTTP_POST_VARS[$this->GetFieldName('user_name_required')])
			$this->LogError('Please Enter Name','user_name');
		if(!$this->Get('user_email'))
			$this->LogError('Please Enter Email','user_email');
		else if(!email::ValidateEmail($this->Get('user_email')))
			$this->LogError('Email Address Does Not Appear To Be Valid','user_email');
		else if(!$this->ValidateUnique('user_email'))
			$this->LogError('An Account Already Exists For This Email Address.  Please Login.','user_email');
		else if($HTTP_POST_VARS[$this->GetFieldName('email_verify')])
		{
			if(!$HTTP_POST_VARS[$this->GetFieldName('user_email2')])  
				$this->LogError('Please Re-Enter Email','user_email2');
			else if($HTTP_POST_VARS[$this->GetFieldName('user_email2')]!=$this->Get('user_email'))  
				$this->LogError('Email Entries Do Not Match','user_email2');
		}	

		$newpwd=$HTTP_POST_VARS[$this->GetFieldName('user_password_new')];			
		$newpwd2=$HTTP_POST_VARS[$this->GetFieldName('user_password_new2')];			
		if(!$this->Get('user_password') and !$newpwd)
			$this->LogError('Please Enter Password','user_password_new');
		else if($newpwd)
		{
			if(strlen($newpwd)<8)
				$this->LogError('Password must be at least 8 characters','user_password_new');
			else if(!preg_match("#[0-9]+#",$newpwd) or !preg_match("#[a-z]+#",$newpwd) or !preg_match("#[A-Z]+#",$newpwd) or !preg_match("#\W+#",$newpwd))
				$this->LogError('Password must include at least one uppercase letter, one lowercase letter, one number and one symbol','user_password_new');
			else if($HTTP_POST_VARS[$this->GetFieldName('password_verify')])
			{
				if(!$newpwd2)  
					$this->LogError('Please Re-Enter Password','user_password_new2');
				else if($newpwd!=$newpwd2)  
					$this->LogError('Passwords Do Not Match','user_password_new2');
			}	
		}
				
		if(!count($this->errors) and $newpwd)
		{
			$this->Set('user_password',md5($newpwd)); 
		}

		return count($this->errors)==0;
 	}

	public function Save()
	{	  	  
		global $HTTP_POST_VARS;
		
		$old=new user($this->id);
	  	$new=!$this->id;
		$psv=parent::Save();
		if($psv)
		{
			$this->SaveImageFile('user_image_file',file::GetPath('user_upload'),$this->id);

			$this->Set('user_reset_code','');
			$this->Update();

			if($old->Get('template_id')!=$this->Get('template_id'))
			{			 	
				//replace my timeline items from the new template
				timeline_item::CopyAll(array('template_id'=>$this->Get('template_id'),'user_id'=>0),array('agent_id'=>$this->Get('agent_id'),'user_id'=>$this->id));
				$template=new template($this->Get('template_id'));
				$this->Set('user_headline',$template->Get('template_headline'));
				$this->Set('user_content',$template->Get('template_content'));
				$this->Set('user_under_contract',0);//knowck out of UC becuase dates are all gone.
				$this->Update();
			}

			$this->saved=true;
			
			$agent = new agent($this->Get('agent_id'));
			if($new)
				activity_log::Log($agent,'ACCOUNT_CREATED','Account Created',$this->id);
			else
				activity_log::Log($agent,'ACCOUNT_UPDATED','Account Updated',$this->id);
		}
		return count($this->errors)==0;
	}

	public function Update()
	{	  	  
		global $HTTP_POST_VARS;
		
		$old=new user($this->id);

		parent::Update();
		if($old->Get('user_under_contract') and !$this->Get('user_under_contract'))
		{
			$user_contract_dates=new DBRowSetEX('user_contract_dates','user_contract_date_id','user_contract_date',"user_id='".$this->id."'");
			$user_contract_dates->Retrieve();
			$user_contract_dates->Delete();
	
		  	$user_conditions=new DBRowSetEX('user_conditions','user_condition_id','user_condition',"user_id='".$this->id."'");
			$user_conditions->Retrieve();
			$user_conditions->Delete();		
	
		  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',"user_id='".$this->id."'");
			$timeline_items->Retrieve();
			$timeline_items->SetEach('timeline_item_date','0000-00-00');		
			$timeline_items->Each('Update');
		}
	}


	public function Delete()
	{
		if($this->GetFlag('PERMENENT_DELETE'))
		{
		 	$agent = new agent($this->Get('agent_id'));
			activity_log::Log($agent,'ACCOUNT_DELETED','Account Deleted',$this->id);

			parent::Delete();
			return;
		}

		$this->Set('user_active',0);
		$this->Update();		

	 	$agent = new agent($this->Get('agent_id'));
		activity_log::Log($agent,'ACCOUNT_DELETED','Account Archived',$this->id);

	}

	public function xDelete()

	{
		parent::Delete();
	}


	public function IsLoggedIn()
	{
		return(Session::Get('pbt_user_login') and Session::Get('user_id'));	  	  
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
		if($this->Get('user_reset_code') or !$requirecode)
		{
			if($action=='send_pwd' and $this->msg)
				echo('<div class="message">Please check your email for details on resetting your password.</div>');
			foreach($this->GetErrors() as $e)
				echo('<div class="error">'.$e.'</div>');
			form::begin('?action='.$this->GetFormAction('save').$this->GetFormExtraParams(),'POST',false,array('id'=>'login'));
			form::DrawHiddenInput($this->GetFieldName('password_reset'),1);
			form::DrawInput('password',$this->GetFieldName('user_password_new'),$HTTP_POST_VARS[$this->GetFieldName('user_password_new')],array('class'=>'text password','placeholder'=>'Enter New Password'));
			form::DrawHiddenInput($this->GetFieldName('password_verify'),1);
			form::DrawInput('password',$this->GetFieldName('user_password_new2'),$HTTP_POST_VARS[$this->GetFieldName('user_password_new2')],array('class'=>'text password','placeholder'=>'Re-Enter New Password'));
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
			echo "<span class='class'>Logged In As ".$this->Get('user_name')."<br /></span>";
			echo '<br><span class="class"><a href="?action=logout" >Log Out</a></span>';
			return;
		}

		if($this->GetError('login'))
			echo('<div class="error">Wrong email or password.</div>');
			
		echo("<div id='login_div' style='display:".(($action!='send_pwd' or $this->msg)?'block':'none')."'>");
		echo("<div class='login_form card'>");
		echo("<div class='card_heading user_bg_color2'><h3>Log In</h3></div>");
		echo("<div class='card_body'>");
		if($action=='send_pwd' and $this->msg)
			echo('<div class="error">Please check your email for details on resetting your password.</div>');
		form::begin('?action=login'.$this->GetFormExtraParams(),'POST',false,array('id'=>'login'));
		form::DrawTextInput('user_email',$HTTP_POST_VARS['user_email'],array('placeholder'=>'Email Address'));
		form::DrawInput('password','user_password',$HTTP_POST_VARS['user_password'],array('placeholder'=>'Password'));
		form::DrawSubmit('','Sign In');
		form::End();
		echo '<a href="#" onclick="document.getElementById(\'forgot-password\').style.display=\'block\';document.getElementById(\'login_div\').style.display=\'none\';return false;">Forget your password?</a>';
		echo('</div>');
		echo('</div>');
		echo('</div>');

		echo("<div id='forgot-password' style='display:".(($action=='send_pwd' and  !$this->msg)?'block':'none')."'>");
		echo("<div class='login_form card'>");
		echo("<div class='card_heading user_bg_color2'><h3>Reset Password</h3></div>");
		echo("<div class='card_body'>");
		if($action=='send_pwd' and $this->GetError('send_pwd'))
			echo('<div class="error">Email Not Found.</div>');
		form::Begin('?action=send_pwd','POST',false,array('class'=>"forgot-password"));
		form::DrawTextInput('user_email',$HTTP_POST_VARS['user_email'],array('placeholder'=>'Email Address'));
		form::DrawSubmit('','Reset Password');
		form::End();
		echo('</div>');
		echo('</div>');

	}


	public function Login($in=true,$silent=false)
	{
		Session::Set('pbt_user_login',$in?1:0);
		Session::Set('user_id',$in?$this->id:0);	  
		if($in)
		{
			$this->Set('user_reset_code','');
			$this->Update();
		}
		
		//clear out on logout
		if(!$in)
			$this->__construct();		
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
			$rs=database::query("SELECT user_id FROM users WHERE user_email='".$this->MakeDBSafe($user_email)."' AND user_password!='' AND user_active=1 AND user_password='".md5($user_password)."'");		  
			if($rec=database::fetch_array($rs))		  
			{
				$this->__construct($rec['user_id']);
				$this->Login();
				$this->msg="You have been logged in";
				if($redir)
					_navigation::Redirect($redir);
			}
			else if($rec=database::fetch_array($rs2))		  
			{
				
			}
		  	else
		  	{
		  		$this->LogError("Account not found.",$action);
			}		  
		}
		if($action=='X__view_timeline')  
		{
			$this->__construct($HTTP_GET_VARS['user_id']);
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
			$rs=database::query("SELECT * FROM users WHERE user_email='".$user_email."'");		  
		  	if(!$user_email)
		  		$this->LogError("Please Enter Email Address",$action);
			else if($rec=database::fetch_array($rs))		  
			{
			  	$date=new Date();
			  	$date->Add('1');
				$tempuser=new user($rec['user_id']);
				$tempuser->Set('user_reset_code',Text::GenerateCode(30,40));
				$tempuser->Set('user_reset_date',$date->GetDBDate());
				$tempuser->Update();
				email::templateMail($user_email,email::GetEmail(),'Your Account',file::GetPath('email_user_password'),$tempuser->attributes+array('base_url'=>_navigation::GetBaseURL()));
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
	public function GetThumb($width,$height,$crop=false)
 	{ 	  
		if($this->Get('user_image_file'))
		{	  
//			$src=$this->CropAsSaved(file::GetPath('user_display'),file::GetPath('user_upload'),'user_file',$width,$height);
			$src=$this->Get('user_image_file');
			return file::GetPath('user_display').imaging::ResizeCached($src,file::GetPath('user_upload'),$width,$height,$crop);
		}
		return _navigation::GetBaseURL().'/images/spacer.gif';
	}	

	public function GetData($key)
	{
		$user_data=new user_data();
		$user_data->InitByKeys(array('user_id','user_data_key'),array($this->id,$key));
		return $user_data->Get('user_data_value');
	}

	public function ListUserContacts($params=array())
	{
		$this->ProcessAction();

	 	$where=array("user_id='".$this->Get('user_id')."'");

	  	$list=new DBRowSetEX('user_contacts','user_contact_id','user_contact',implode(' AND ',$where),'user_contact_name');
		$list->num_new=1;
	  	$list->Retrieve();
	  	$list->SetEachNew('user_id',$this->Get('user_id'));
		$list->SetFlag('ALLOW_BLANK');
		$list->ProcessAction();
	  	$list->Retrieve();
	  	$list->SetEachNew('user_id',$this->Get('user_id'));

		echo("<div class='cards_list'>");
		echo("<div class='row'>");
		foreach($list->newitems as $user_contact)
		{
			echo('<div class="col-sm-6">');
			echo("<div id='".$user_contact->GetFieldName('UserCardContainer')."'>");
			$user_contact->NewUserCard($params);
			echo("</div>");
			echo("</div>");
		}
		foreach($list->items as $user_contact)
		{
			echo('<div class="col-sm-6">');
			echo("<div id='".$user_contact->GetFieldName('UserCardContainer')."'>");
			$user_contact->UserCard($params);
			echo("</div>");
			echo("</div>");
		}
		echo("</div>");
		echo("</div>");
		
	}	

	public function UpdateDates($recalculate_dates_id)
	{
		//find any dates that are relative to the one that was changed
		$where=array(1);		
		$where[]="contract_date_default_days_relative_to_id='".$recalculate_dates_id."'";
	  	$contract_dates=new DBRowSetEX('contract_dates','contract_date_id','contract_date',implode(' AND ',$where),'contract_date_order');
		$contract_dates->Retrieve();
		foreach($contract_dates->items as $contract_date)
		{
		 	$where2=array(1);
		 	$where2[]="contract_date_id='".$contract_date->id."'";
			$where2[]="user_id='".$this->id."'";
		  	$user_contract_dates=new DBRowSetEX('user_contract_dates','user_contract_date_id','user_contract_date',implode(' AND ',$where2));
			$user_contract_dates->Retrieve();
			foreach($user_contract_dates->items as $user_contract_date)
			 	$user_contract_date->CalculateDate();
		}

	 	//set the timeline items related to this as well.
		$this->UpdateTimlineDates($recalculate_dates_id);

		//AND update any datees relatedto this one  Daisy chaing them.		
		foreach($contract_dates->items as $contract_date)
			$this->UpdateDates($contract_date->id);
	}

	public function UpdateTimlineDates($recalculate_dates_id)
	{
	 	$where2=array(1);
	 	$where2[]="timeline_item_reference_date='".$recalculate_dates_id."'";
		$where2[]="user_id='".$this->id."'";
	  	$list=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where2),'timeline_item_order');
	  	$list->Retrieve();
	  	foreach($list->items as $timeline_item)
	  	 	$timeline_item->CalculateDate();
	}

	public function GetLastModifiedDate()
	{
		$rec=database::fetch_array(database::query("SELECT MAX(timeline_item_complete) AS timestamp FROM timeline_items WHERE user_id='".$this->id."'"));
		if(!$rec['timeline_item_completed'])
			$rec=database::fetch_array(database::query("SELECT MAX(activity_log_timestamp) AS timestamp FROM activity_log WHERE user_id='".$this->id."'"));
		$d=new date();
		$d->SetTimestamp($rec['timestamp']);
		return $d;
	}
	
	public function CalculateFullContingencyRemovalDate()
	{

		if(!$this->Get('user_under_contract'))
			return;

		$contingency_removal=new contract_date();
		$contingency_removal->InitByKeys('contract_date_special','FULL_CONTINGENCY_REMOVAL');
	 	$user_contingency_removal=new user_contract_date();
		$user_contingency_removal->InitByKeys(array('contract_date_id','user_id'),array($contingency_removal->id,$this->id));

		$latest=false;

		$where=array('contract_date_contingency=1');
	  	$contract_dates=new DBRowSetEX('contract_dates','contract_date_id','contract_date',implode(' AND ',$where),'contract_date_order');
		$contract_dates->Retrieve();
		foreach($contract_dates->items as $contract_date)
		{
		 	$user_contract_date=new user_contract_date();
			$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($contract_date->id,$this->id));
			if(!$user_contract_date->Get('user_contract_date_na'))
			{	
				if(!$latest or (date::GetDays(new DBDate($user_contract_date->Get('user_contract_date_date')),$latest)<0))
					$latest=new DBDate($user_contract_date->Get('user_contract_date_date'));
			}				
		}
		if($latest)
		{
			$user_contingency_removal->Set('user_contract_date_date',$latest->GetDBDate());
			$user_contingency_removal->Set('user_contract_date_override',true);
			$user_contingency_removal->Update();
			
			$this->UpdateDates($contingency_removal->id);
		}
		return;	
	}	
	
	public function HasNotifications()
	{
		$user_contacts=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"user_id='".$this->id."'");
		$user_contacts->Retrieve();
		foreach($user_contacts->items as $user_contact)
		{
			if($user_contact->HasNotifications())
				return true;
		}		
		return false;
	}


	public function DisplayICal($params)
	{
		$filename=mod_rewrite::ToURL($this->GetFullName()).$params['type'].'.ics';
		$agent=new agent($this->Get('agent_id'));
		$where=array();

		//key dates.
		$key_date_ids=array(-1);
		$ucd_where=array("user_id='".$user->id."'");
		$ucd_where[]="user_contract_date_key_date=1";
		$ucd_where[]="user_contract_date_na=0";
	  	$user_contract_dates=new DBRowSetEX('user_contract_dates','user_contract_date_id','user_contract_date',implode(' AND ',$ucd_where),'user_contract_date_date');
		$user_contract_dates->Retrieve();
		foreach($user_contract_dates->items as $user_contract_date)
			$key_date_ids[]=$user_contract_date->Get('contract_date_id');
		$where[]="timeline_item_reference_date_type='CONTRACT' AND timeline_item_reference_date IN(".implode(',',$key_date_ids).") AND timeline_item_for !='AGENT'";

		//items that depend on other items and the item they depend on is complete.
		$dependant_item_ids=array(-1);
		$dependant_items=$this->GetTimeLineItems(array('where'=>"depends_on_timeline_item_id!=0"));
		foreach($dependant_items->items as $dependant_item)
		{
			if($dependant_item->DependsOnCompleteItem())	
				$dependant_item_ids[]=$dependant_item->id;
		}
		$where[]="timeline_item_id IN(".implode(',',$dependant_item_ids).") AND timeline_item_for !='AGENT'";

		//key includsiosn for the selected type
		$type_where=array();
		if($params['type']=='KEY_DATES')
			$null;
		if($params['type']=='KEY_DATES_PLUS')
		{
			$type_where[]="timeline_item_reference_date_type!='NONE'";
			$type_where[]="timeline_item_for IN('USER')";
		}
		if($params['type']=='ALL')
		{
			$type_where[]="timeline_item_reference_date_type!='NONE'";
			$type_where[]="timeline_item_for IN('USER','OTHER')";
		}
		if(count($type_where))
			$where[]=implode(" AND ",$type_where);
		
		//all choices.
		$where="((".implode(') OR (',$where)."))";


		$timeline_items=$agent->GetTimeLineItems(array('user_id'=>$this->id,'where'=>$where,'show_completed'=>1));
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		

	
		header('Content-type: text/calendar');
		header('Content-Disposition: attachment; filename=' . $filename);
	
		echo("BEGIN:VCALENDAR"."\r\n");
		echo("PRODID:WHATSNEXT-USER-".$this->id.""."\r\n");
		echo("VERSION:2.0"."\r\n");
		echo("X-WR-CALNAME:What's Next Real Estate: ".$this->GetFullName()."\r\n");
		$timeline_items->Each('DisplayICal',array($params));
		echo("END:VCALENDAR"."\r\n");
	}


	public function GetMailParams()
	{
		$mail_params=array();
		$mail_params+=$this->attributes;
		$mail_params['user_image_file']=$this->GetThumb(500,500,false);
		
		return $mail_params;
	}

};

class user_data extends DBRowEx
{
	public function user_data($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('user_data','user_data_id');
		$this->Retrieve();
	}
}
?>