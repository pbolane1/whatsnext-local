<?php

class user_contact extends DBRowEx
{ 
	use public_user;

	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('user_contacts','user_contact_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{
			$settings=array();
			$settings['notifications']=array();
			$settings['notifications']['phone']=0;
			$settings['notifications']['email']=0;
			$settings['notifications']['other']=0;
			$settings['notifications']['user']=1;
			$this->Set('user_contact_settings',json_encode($settings));
			
			$this->Set('user_contact_last_view',time());
		}
 	}

	public function ToURL($page='',$expires=3)
	{
		if($page[0]=='?')
			$page='/users/'.$page;

		$user_link=new user_link;
		return $user_link->Generate($this->id,$page,$expires);
	}

	public function GetName()
	{
		return $this->Get('user_contact_name');
	}

	public function GetPhone()
	{
		return $this->Get('user_contact_phone');
	}

	public function GetEmail()
	{
		return $this->Get('user_contact_email');
	}

	public function GetSettings()
	{
		return $this->Get('user_contact_settings');
	}


	public function GetFullName()
	{
		return $this->Get('user_contact_name');
	}

	public function DoAction($action)
	{
		parent::DoAction($action);
		if($action==$this->GetFormACtion('primary'))	
		{
			database::query("UPDATE user_contacts SET user_contact_primary=0 WHERE user_id='".$this->Get('user_id')."'");
			
			$this->Set('user_contact_primary',1);
			$this->Update();
		}
		if($action==$this->GetFormAction('welcome'))
			$this->SendWelcomeMessage();
		if($action==$this->GetFormAction('dismiss_welcome_notice'))
		{
			$this->Set('user_contact_welcome_timestamp',-1);
			$this->Update();

			activity_log::Log($this->GetAgent(),'WELCOME_DISMISSED','Welcome Email Notice Dismissed - '.$this->Get('user_contact_name'),$this->Get('user_id'));
		}
	}

	public function GetAgent()
	{	 
		$user=new user($this->Get('user_id'));
		$agent=new agent($user->Get('agent_id'));
		return $agent;
	}

	public function GatherInputs()
	{	 
		global $HTTP_POST_VARS;

		$settings=json_decode($this->Get('user_contact_settings'),true);

		parent::GatherInputs();

		//$this->Set('user_contact_phone',$this->NormalizePhone($this->Get('user_contact_phone')));
		//$this->Set('user_contact_settings',json_encode($this->Get('user_contact_settings')));

		$newpwd=$HTTP_POST_VARS[$this->GetFieldName('user_contact_password_new')];			
		$newpwd2=$HTTP_POST_VARS[$this->GetFieldName('user_contact_password_new2')];			
//		if(!$this->Get('user_contact_password') and !$newpwd)
//			$this->LogError('Please Enter Password','user_contact_password_new');
//		else 
		if($newpwd)
		{
			//if(strlen($newpwd)<8)
			//	$this->LogError('Password must be at least 8 characters','user_contact_password_new');
			//else if(!preg_match("#[0-9]+#",$newpwd) or !preg_match("#[a-z]+#",$newpwd) or !preg_match("#[A-Z]+#",$newpwd) or !preg_match("#\W+#",$newpwd))
			//	$this->LogError('Password must include at least one uppercase letter, one lowercase letter, one number and one symbol','user_contact_password_new');
			if($HTTP_POST_VARS[$this->GetFieldName('password_verify')])
			{
				if(!$newpwd2)  
					$this->LogError('Please Re-Enter Password','user_contact_password_new2');
				else if($newpwd!=$newpwd2)  
					$this->LogError('Passwords Do Not Match','user_contact_password_new2');
			}	
		}
		if(!count($this->errors) and $newpwd)
		{
			//$this->Set('agent_password',md5($newpwd)); 
			$this->Set('user_contact_password',$newpwd); 
		}

		if($HTTP_POST_VARS[$this->GetFieldName('user_contact_settings')]['notifications'])
			$settings['notifications']=$HTTP_POST_VARS[$this->GetFieldName('user_contact_settings')]['notifications'];
		$this->Set('user_contact_settings',json_encode($settings));		
	}

	public function UserCardJS($params=array(),$container='')
	{
		$js2='';
		if($params['edit'])
			$js2.="jQuery('#".$this->GetFormAction('edit')."').focus();";
		$js2.="height_handler();";

		$passparams=array();
		foreach($params as $k=>$v)
			$passparams[]=$k.'='.$v;
		$js="ObjectFunctionAjax('user_contact','".$this->id."','UserCard','".$container."','".$this->GetFieldName('UserCardForm')."','','".implode('&',$passparams)."',function(){".$js2."});";
		
		return $js;
	}

	public function NewUserCard($params=array())
	{
		$user=new user($this->Get('user_id'));
		$js="ObjectFunctionAjax('user','".$user->id."','ListUserContacts','".$user->GetFieldName('ListUserContactsContainer')."','null','','action=".$this->GetFormAction('save')."',function(){height_handler();});";
		echo("<div class='card card_new'>");
		echo("<div class='box_inner'>");
		echo("<div class='card_heading' onclick=\"".$js."\" data-info='CONTACTS_NEW' data-info-none='none'>");
		echo("<h3><i class='fa fa-plus'></i> New Contact</h3>");
		echo('</div>');
		echo('<div class="card_body">');
		echo('<div class="card_content">');
		echo("<br>");
		echo('</div>');
		echo('</div>');
		echo('</div>');
		echo('</div>');		
	}

	public function UserCard($params=array())
	{
		$user=new user($this->Get('user_id'));
		
		$savejs="UpdateWYSIWYG();";
		$savejs.=$this->userCardJS(array('action'=>$this->GetFormAction('save')));
		if(!$this->id)
			$savejs='';

		$this->SetFlag('ALLOW_BLANK');
		if(!$params['parent_action'])
			$this->ProcessAction();

		$class='';

		$settings=json_decode($this->Get('user_contact_settings'),true);

		form::Begin('','POST',true,array('id'=>$this->GetFieldName('UserCardForm')));
		echo('<div class="card '.$class.' info_group">');
		echo("<div class='box_inner'>");
		echo('<div class="card_heading agent_bg_color2">');
		$placeholder="Name";
		$attr=array('Placeholder'=>$placeholder,'onchange'=>$savejs,'class'=>'H3','data-info'=>'CONTACTS_NAME');
		form::DrawTextInput($this->GetFieldName('user_contact_name'),$this->Get('user_contact_name'),$attr);
		echo('</div>');
		echo('<div class="card_body">');
		echo('<div class="card_content">');
		echo('<div class="line">');
		$attr=array('placeholder'=>'Email Address','onchange'=>$savejs,'data-info'=>'CONTACTS_ADDRESS');
		form::DrawTextInput($this->GetFieldName('user_contact_email'),$this->Get('user_contact_email'),$attr);
		echo('</div>');
		echo('<div class="line">');
		$attr=array('placeholder'=>'Password','onchange'=>$savejs,'data-info'=>'CONTACTS_PASSWORD');
		form::DrawTextInput($this->GetFieldName('user_contact_password'),$this->Get('user_contact_password'),$attr);
		echo('</div>');
		echo('<div class="line">');
		$attr=array('placeholder'=>'Phone','onchange'=>$savejs,'data-info'=>'CONTACTS_PHONE');
		form::DrawTextInput($this->GetFieldName('user_contact_phone'),$this->Get('user_contact_phone'),$attr);
		echo('</div>');
		echo('<div class="line">');
		$attr=array('placeholder'=>'Phone Notifications','onchange'=>$savejs,'data-info'=>'CONTACTS_PHONE_NOTIFICATIONS');
		form::DrawSelect($this->GetFieldName('user_contact_settings').'[notifications][phone]',array('Opt In Notifications Via SMS'=>1,'Opt Out Of Notifications Via SMS'=>0),$settings['notifications']['phone'],$attr);
		echo('</div>');
		echo('<div class="line">');
		$attr=array('placeholder'=>'Email Notifications','onchange'=>$savejs,'data-info'=>'CONTACTS_EMAIL_NOTIFICATIONS');
		form::DrawSelect($this->GetFieldName('user_contact_settings').'[notifications][email]',array('Opt In Notifications Via Email'=>1,'Opt Out Of Notifications Via Email'=>0),$settings['notifications']['email'],$attr);
		echo('</div>');

		echo('<div class="card_links">');
		if(!$this->id)
		{
			$js="UpdateWYSIWYG();";
			$js.="ObjectFunctionAjax('user','".$user->id."','ListUserContacts','".$user->GetFieldName('ListUserContactsContainer')."','".$this->GetFieldName('userCardForm')."','','action=".$this->GetFormAction('save')."',function(){height_handler();});";
			echo("<a data-info='CONTACTS_ADD' data-info-none='none' class='button' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title=''><i class='fa fa-plus'></i> Add Property</a>");
		}
		else
		{
			$js="ObjectFunctionAjax('user','".$user->id."','ListUserContacts','".$user->GetFieldName('ListUserContactsContainer')."','null','','action=".$this->GetFormAction('delete')."',function(){height_handler();});";
			echo("<a data-info='CONTACTS_DELETE' data-info-none='none' class='button' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
		}
		echo('</div>');

		echo('</div>');
		echo('</div>');
		echo('</div>');
		echo('</div>');
		form::End();		
	}

	public function EditSettings()
	{
		global $HTTP_POST_VARSfs;
	
		$savejs="UpdateWYSIWYG();";
		$savejs.="ObjectFunctionAjax('user_contact','".$this->id."','EditSettings','','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('save')."',function(){});";
		$savejs2="UpdateWYSIWYG();";
		$savejs2.="ObjectFunctionAjax('user_contact','".$this->id."','EditSettings','EditSettingsContainer','".$this->GetFieldName('EditSettingsForm')."','','action=".$this->GetFormAction('save')."',function(){});";
		if(!$this->id)
			$savejs2='';

		$this->SetFlag('ALLOW_BLANK');
		if(!$params['parent_action'])
			$this->ProcessAction();

		$settings=json_decode($this->Get('user_contact_settings'),true);


		echo("<div class='card'>");
		form::Begin('','POST',true,array('id'=>$this->GetFieldName('EditSettingsForm')));
		echo("<div class='card_heading agent_bg_color1'>");
		form::DrawTextInput($this->GetFieldName('user_contact_name'),$this->Get('user_contact_name'),array('class'=>'text H3','placeholder'=>'Name','onchange'=>$savejs,'data-info'=>'SETTINGS_NAME'));
		echo("</div>");
		echo("<div class='card_body'>");
		echo("<div class='card_content'>");
		echo("<div class='card_label'>Login Information</div>");
		echo("<div class='card_section' data-info='SETTINGS_NOTIFICATIONS' data-info-none='none'>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('user_contact_email'),$this->Get('user_contact_email'),array('placeholder'=>'Email','onchange'=>$savejs,'data-info'=>'SETTINGS_EMAIL'));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('user_contact_password_new'),$HTTP_POST_VARS[$this->GetFieldName('user_contact_password_new')],array('placeholder'=>'Change Password','onchange'=>$savejs,'data-info'=>'SETTINGS_PASSWORD'));
		echo("</div>");
		echo("<div class='line'>");
		form::DrawTextInput($this->GetFieldName('user_contact_phone'),$this->Get('user_contact_phone'),array('placeholder'=>'Phone','onchange'=>$savejs));
		echo("</div>");
		echo("</div>");
		echo("<div class='card_label'>Notificaitons</div>");
		echo("<div class='card_section' data-info='SETTINGS_NOTIFICATIONS' data-info-none='none'>");
		echo("<div class='line'>");
		echo("<label>");
		form::DrawCheckbox($this->GetFieldName('user_contact_settings').'[notifications][phone]',1,$settings['notifications']['phone'],array('onchange'=>$savejs));
		echo(" SMS</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox($this->GetFieldName('user_contact_settings').'[notifications][email]',1,$settings['notifications']['email'],array('onchange'=>$savejs));
		echo(" Email</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox($this->GetFieldName('user_contact_settings').'[notifications][user]',1,$settings['notifications']['user'],array('onchange'=>$savejs));
		echo(" Receive reminders for client items</label>");
		echo("<br>");
		echo("<label>");
		form::DrawCheckbox($this->GetFieldName('user_contact_settings').'[notifications][other]',1,$settings['notifications']['other'],array('onchange'=>$savejs));
		echo(" Receive reminders for agent/other items</label>");
		echo("<br>");
		echo("</div>");
		echo("</div>");

		form::end();
		echo("</div>");	
	}

	public function Delete()
	{
		$primary=$this->Get('user_contact_primary');

		parent::Delete();

		$user = new user($this->Get('user_id'));
		if($primary)
		{
			$new_primary = new user_contact();
			$new_primary->InitByKeys("user_id",$user->id);
			$new_primary->Set('user_contact_primary',1);
			$new_primary->Update();
			$user->Set('user_name',$new_primary->Get('user_contact_name'));
			$user->Update();
		}
	}

	public function Update()
	{
		parent::Update();
	}

	public function ValidateInputs()
	{
		global $HTTP_POST_VARS;
/*		
		$newpwd=$HTTP_POST_VARS[$this->GetFieldName('user_contact_password_new')];			
		$newpwd2=$HTTP_POST_VARS[$this->GetFieldName('user_contact_password_new2')];			
		if(!$this->Get('user_contact_password') and !$newpwd)
			$this->LogError('Please Enter Password','user_password_new');
		else if($newpwd)
		{
			if(strlen($newpwd)<8)
				$this->LogError('Password must be at least 8 characters','user_password_new');
			//else if(!preg_match("#[0-9]+#",$newpwd) or !preg_match("#[a-z]+#",$newpwd) or !preg_match("#[A-Z]+#",$newpwd) or !preg_match("#\W+#",$newpwd))
			//	$this->LogError('Password must include at least one uppercase letter, one lowercase letter, one number and one symbol','user_password_new');
			else if($HTTP_POST_VARS[$this->GetFieldName('password_verify')])
			{
				if(!$newpwd2)  
					$this->LogError('Please Re-Enter Password','user_contact_password_new2');
				else if($newpwd!=$newpwd2)  
					$this->LogError('Passwords Do Not Match','user_contact_password_new2');
			}	
		}
				
		if(!count($this->errors) and $newpwd)
		{
		 	//mnot md5ing it doe to plain text entry by agent??
			$this->Set('user_contact_password',$newpwd); 
		}
*/
		if($this->GetFlag('ALLOW_BLANK'))
			return true;


		return count($this->errors)==0;
 	}

	public function Save()
	{	  	  
		global $HTTP_POST_VARS;

		$old=new user_contact($this->id);
	  	$new=!$this->id;
		$psv=parent::Save();
		if($psv)
		{
			$this->Set('user_contact_reset_code','');
			$this->Update();
			
			$this->saved=true;

			$settings=json_decode($this->Get('user_contact_settings'),true);
			$old_settings=json_decode($old->Get('user_contact_settings'),true);
			$user=new user($this->id);
			$agent=$this->GetAgent();
			
			if($new)
				activity_log::Log($this->GetFlag('AGENT')?$agent:$this,'USER_CONTACT_CREATED','Account Created - '.$this->Get('user_contact_name'),$this->Get('user_id'));
			else
				activity_log::Log($this->GetFlag('AGENT')?$agent:$this,'USER_CONTACT_UPDATED','Account Updated - '.$this->Get('user_contact_name'),$this->Get('user_id'));
						
			
			if($user->Get('user_agent_only_notifications') and !$HTTP_POST_VARS['AGENT'])
			{
				if(!$settings['notifications']['phone'] and $old_settings['notifications']['phone'])
				{
					$subject=$this->Get('user_contact_name').' '.$user->Get('user_address').' has turned off SMS notifications';
					$contents=array();
					$contents[]="NOTICE: ".$this->Get('user_contact_name').' '.$user->Get('user_address').' has turned off SMS notifications';
					$agent->SendNotice($subject,$contents);
				}
				if($settings['notifications']['phone'] and !$old_settings['notifications']['phone'])
				{
					$subject=$this->Get('user_contact_name').' '.$user->Get('user_address').' has turned on SMS notifications';
					$contents=array();
					$contents[]="NOTICE: ".$this->Get('user_contact_name').' '.$user->Get('user_address').' has turned on SMS notifications';
					$agent->SendNotice($subject,$contents);
				}
				if(!$settings['notifications']['email'] and $old_settings['notifications']['email'])
				{
					$subject=$this->Get('user_contact_name').' '.$user->Get('user_address').' has turned off emails notifications';
					$contents=array();
					$contents[]="NOTICE: ".$this->Get('user_contact_name').' '.$user->Get('user_address').' has turned off Email notifications';
					$agent->SendNotice($subject,$contents);
				}
				if($settings['notifications']['email'] and !$old_settings['notifications']['email'])
				{
					$subject=$this->Get('user_contact_name').' '.$user->Get('user_address').' has turned on emails notifications';
					$contents=array();
					$contents[]="NOTICE: ".$this->Get('user_contact_name').' '.$user->Get('user_address').' has turned on Email notifications';
					$agent->SendNotice($subject,$contents);
				}
			}
			
			if($settings['notifications']['phone'] and !$old_settings['notifications']['phone'])
			 	activity_log::Log($this->GetFlag('AGENT')?$agent:$this,'SMS_ENABLED','Phone notifications enabled for '.$this->Get('user_contact_name'),$this->Get('user_id'));			
			if(!$settings['notifications']['phone'] and $old_settings['notifications']['phone'])
			 	activity_log::Log($this->GetFlag('AGENT')?$agent:$this,'SMS_DISABLED','Phone notifications disabled for '.$this->Get('user_contact_name'),$this->Get('user_id'));			
		}

		return count($this->errors)==0;
	}

	public function IsLoggedIn()
	{
		return(Session::Get('pbt_user_contact_login') and Session::Get('user_contact_id'));	  	  
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
		if($this->Get('user_contact_reset_code') or !$requirecode)
		{
			if($action=='send_pwd' and $this->msg)
				echo('<div class="message">Please check your email for details on resetting your password.</div>');
			foreach($this->GetErrors() as $e)
				echo('<div class="error">'.$e.'</div>');
			form::begin('?action='.$this->GetFormAction('save').$this->GetFormExtraParams(),'POST',false,array('id'=>'login'));
			form::DrawHiddenInput($this->GetFieldName('password_reset'),1);
			form::DrawInput('password',$this->GetFieldName('user_contact_password_new'),$HTTP_POST_VARS[$this->GetFieldName('user_contact_password_new')],array('class'=>'text password','placeholder'=>'Enter New Password'));
			form::DrawHiddenInput($this->GetFieldName('password_verify'),1);
			form::DrawInput('password',$this->GetFieldName('user_contact_password_new2'),$HTTP_POST_VARS[$this->GetFieldName('user_contact_password_new2')],array('class'=>'text password','placeholder'=>'Re-Enter New Password'));
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
			echo "<span class='class'>Logged In As ".$this->Get('agent_name')."<br /></span>";
			echo '<br><span class="class"><a href="?action=logout" >Log Out</a></span>';
			return;
		}

		echo("<div id='login_div' style='display:".(($action!='send_pwd' or $this->msg)?'block':'none')."'>");
		echo("<div class='login_form card'>");
		echo("<div class='card_heading user_contact_bg_color2'><h3>Client Login</h3></div>");
		echo("<div class='card_body'>");
		if($this->GetError('login'))
			echo('<div class="error">'.$this->GetError('login').'</div>');
		if($action=='send_pwd' and $this->msg)
			echo('<div class="error">Please check your email for details on resetting your password.</div>');
		form::begin('?action=login'.$this->GetFormExtraParams(),'POST',false,array('id'=>'login'));
		form::DrawTextInput('user_contact_email',$HTTP_POST_VARS['user_contact_email'],array('placeholder'=>'Email Address'));
		form::DrawInput('password','user_contact_password',$HTTP_POST_VARS['user_contact_password'],array('placeholder'=>'Password'));
		form::DrawSubmit('','Sign In');
		form::End();
		echo '<a href="#" onclick="document.getElementById(\'forgot-password\').style.display=\'block\';document.getElementById(\'login_div\').style.display=\'none\';return false;">Forget your password?</a>';
		echo('</div>');
		echo('</div>');
		echo('</div>');

		echo("<div id='forgot-password' style='display:".(($action=='send_pwd' and  !$this->msg)?'block':'none')."'>");
		echo("<div class='login_form card'>");
		echo("<div class='card_heading user_contact_bg_color2'><h3>Reset Password</h3></div>");
		echo("<div class='card_body'>");
		if($action=='send_pwd' and $this->GetError('send_pwd'))
			echo('<div class="error">Email Not Found.</div>');
		form::Begin('?action=send_pwd','POST',false,array('class'=>"forgot-password"));
		form::DrawTextInput('user_contact_email',$HTTP_POST_VARS['user_contact_email'],array('placeholder'=>'Email Address'));
		form::DrawSubmit('','Reset Password');
		form::End();
		echo('</div>');
		echo('</div>');

	}


	public function Login($in=true,$silent=false)
	{
		parent::Login($in);

		Session::Set('pbt_user_contact_login',$in?1:0);
		Session::Set('user_contact_id',$in?$this->id:0);	  
		if($in)
		{
			$this->Set('user_contact_reset_code','');
			$this->Set('user_contact_last_login',time());
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
		$settings=json_decode($this->Get('user_contact_settings'),true);
		$settings['notifications']['email']=$params['email_notifications'];
		$this->Set('user_contact_settings',json_encode($settings));
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
		 	//no password
			$rs=database::query("SELECT user_contact_id FROM user_contacts WHERE user_contact_email='".$this->MakeDBSafe($user_contact_email)."' AND user_contact_password=''");
			if($rec=database::fetch_array($rs))
			{
			  	$date=new Date();
			  	$date->Add('1');
				$tempuser_contact=new user_contact($rec['user_contact_id']);
				$tempuser_contact->Set('user_contact_reset_code',Text::GenerateCode(30,40));
				$tempuser_contact->Set('user_contact_reset_date',$date->GetDBDate());
				$tempuser_contact->Update();
				_navigation::Redirect('reset.php?user_contact_reset_code='.$tempuser_contact->Get('user_contact_reset_code'));
			}
			
			//normal login
			$rs=database::query("SELECT user_contact_id FROM user_contacts WHERE user_contact_email='".$this->MakeDBSafe($user_contact_email)."' AND user_contact_password!='' AND user_contact_password='".$user_contact_password."'");
			if($rec=database::fetch_array($rs))
			{
				$this->__construct($rec['user_contact_id']);
				$this->Login();
				$this->msg="You have been logged in";
				if($redir)
					_navigation::Redirect($redir);
				else
					_navigation::Redirect('?action=loggedin');
			}
		  	else
		  	{
		  		$this->LogError("Incorrect email or password.",$action);
			}		  
		}	
		if($action=='user_link')  
		{
		 	$user_link=new user_link();
		 	$user_link->InitByKeys('user_link_hash',$HTTP_GET_VARS['user_link_hash']);
		 	if(!$user_link->id)
		 		$this->LogError('Plase Log In','login');
		 	else if($user_link->Get('user_link_expires')<time())
		 		$this->LogError('Link has expired.  Please Log In','login');
		 	else if($user_link->Get('user_link_page'))
			{
				$this->__construct($user_link->Get('user_contact_id'));
				$this->Login();
				_navigation::Redirect($user_link->Get('user_link_page'));
			}
		 	else
		 	{
				$this->__construct($user_link->Get('user_contact_id'));
				$this->Login();
				$this->msg="You have been logged in";
				if($redir)
					_navigation::Redirect($redir);
				else
				{
					_navigation::Redirect('?action=loggedin');
				}
			}		 		
		}	
		if($action=='logout')  
		{
			$this->Login(false);
			//_navigation::Redirect('index.php');
			$this->msg="You have been logged out";
		}
		if($action=='send_pwd')  
		{
			$rs=database::query("SELECT * FROM user_contacts WHERE user_contact_email='".$user_contact_email."'");		  
		  	if(!$user_contact_email)
		  		$this->LogError("Please Enter Email Address",$action);
			else if($rec=database::fetch_array($rs))		  
			{
			  	$date=new Date();
			  	$date->Add('1');
				$tempuser_contact=new user_contact($rec['user_contact_id']);
				$tempuser_contact->Set('user_contact_reset_code',Text::GenerateCode(30,40));
				$tempuser_contact->Set('user_contact_reset_date',$date->GetDBDate());
				$tempuser_contact->Update();
				email::templateMail($user_contact_email,email::GetEmail(),'Your Account',file::GetPath('email_user_contact_password'),$tempuser_contact->attributes+array('base_url'=>_navigation::GetBaseURL()));
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

	public function RecentActivityCheck()
	{
		$where=array("user_id='".$this->Get('user_id')."' AND timeline_item_active=1");
		$where[]="user_id!=0";
		$where[]="timeline_item_for IN('USER')";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete>".$this->Get('user_contact_last_view');
		$where[]="(timeline_item_completed_class!='user_contact' OR timeline_item_completed_id!='".$this->id."')";
	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_complete');
		if($timeline_items->GetTotalAvailable())
		{
			Javascript::Begin();	
			echo("jQuery(function(){
				ObjectFunctionAjaxPopup('Since Your Last Login','user_contact','".$this->id."','RecentActivityList','NULL','','',function(){});
			});");
			Javascript::End();	
		}			
	}

	public function RecentActivityList()
	{
		$where=array("user_id='".$this->Get('user_id')."' AND timeline_item_active=1");
		$where[]="user_id!=0";
		$where[]="timeline_item_for IN('USER')";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete>".$this->Get('user_contact_last_view');
		$where[]="(timeline_item_completed_class!='user_contact' OR timeline_item_completed_id!='".$this->id."')";
	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_complete');
	  	$timeline_items->Retrieve();
		echo("<div class='timeline_item_digest'>");
		echo("<h2>Since Your Last Visit</h2>");
	  	foreach($timeline_items->items as $timeline_item)
	  	{
			$d=new date();
			$d->SetTimestamp($timeline_item->Get('timeline_item_complete'));
			echo("<div class='timeline_item_info'>");
			echo("<div class='timeline_item_info_date'>".$d->GetDate('F j:')."</div>");
			echo("<a class='timeline_item_info_title' href='/users/#".$timeline_item->GetFieldName('anchor')."'>".$timeline_item->Get('timeline_item_title')."</a>");
			echo("<div class='timeline_item_info_label'>Marked Complete By</div>");
			echo("<div class='timeline_item_info_completed_by'>".$timeline_item->Get('timeline_item_completed_by')."</div>");
			echo("</div>");
		}
		echo("</div>");
		
		$this->Set('user_contact_last_view',time());
		$this->Set('user_contact_last_login',time());
		$this->Update();
	}

	public function SendNotifications($params=array())
	{
		$settings=json_decode($this->Get('user_contact_settings'),true);		

		$user=new user($this->Get('user_id'));
		$agent=new agent($user->Get('agent_id'));
		$where=array("user_id='".$user->id."' AND timeline_item_active=1");
		if(is_array($params['timeline_item_ids']))
			$where[]="timeline_item_id IN('".implode("','",$params['timeline_item_ids'])."')";
		else
		{	
			//$where[]="timeline_item_for IN('USER')";
			$where[]="timeline_item_type='TIMELINE'";
			$where[]="timeline_item_complete>timeline_item_notified";
			$where[]="(timeline_item_completed_class!='user_contact' OR timeline_item_completed_id!='".$this->id."')";
			
			$types=array('-1');
			if($settings['notifications']['agent'])
				$types[]="AGENT";
			if($settings['notifications']['other'])
				$types[]="OTHER";
			if($settings['notifications']['user'])
				$types[]="USER";
			$where[]="timeline_item_for IN('".implode("','",$types)."')";
			
		}
	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_complete');
	  	$timeline_items->Retrieve();
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		


  		if(count($timeline_items->items))
		{
			if($settings['notifications']['email'])
			{
				$mail_params=array('base_url'=>_navigation::GetBaseURL());
				$mail_params+=$user->GetMailParams();
				$mail_params+=$this->GetMailParams();
				$mail_params+=$agent->GetMailParams();	
		
				$mail_params['timeline_items']=array();
			  	foreach($timeline_items->items as $i=>$timeline_item)
			  	{
					//if(!$timeline_item->DependsOnIncompleteItem() and !$timeline_item->IsNotApplicable())
					{
						$d=new date();
						$d->SetTimestamp($timeline_item->Get('timeline_item_complete'));
						$mail_params['timeline_items'][$i]=array();
						$mail_params['timeline_items'][$i]+=$timeline_item->attributes;
						$mail_params['timeline_items'][$i]['date_completed']=$d->GetDate('F j');
						$mail_params['timeline_items'][$i]['url']=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');
						if(!$mail_params['user_url'])
							$mail_params['user_url']=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');;
					}
				}
				$mail_params['opt_out_link']=$this->ToURL('optout.php');
		
				echo('USER CONTACT '.$user->Get('user_name').' '.$user->Get('user_address').' : '.$this->Get('user_contact_email').' ('.$timeline_items->GetTotalAvailable().' Updates)');
				echo("<br>");
		
				$headers=array();
				$headers[]="reply-to:".$agent->Get('agent_email');
				email::templateMail($this->Get('user_contact_email'),email::GetEmail(),"What's Next Real Estate Updates for ".($user->Get('user_address')?$user->Get('user_address'):$user->Get('user_name')),file::GetPath('email_user_contact_notifications'),$mail_params,$headers);
			}
			if($settings['notifications']['phone'])
			{
				$phone=$this->TwillioFormat($this->Get('user_contact_phone'));
				$client = new \Twilio\Rest\Client(TWILLIO_SID,TWILLIO_KEY);
				try
				{
					$content=array();
					$content[]="What's Next Real Estate Updates for ".($user->Get('user_address')?$user->Get('user_address'):$user->Get('user_name'));
					$content[]="";
				  	foreach($timeline_items->items as $i=>$timeline_item)
				  	{
						//if(!$timeline_item->DependsOnIncompleteItem() and !$timeline_item->IsNotApplicable())
						{
							$d=new date();
							$d->SetTimestamp($timeline_item->Get('timeline_item_complete'));
	
							if($i<3)
								$content[]=' * '.$timeline_item->Get('timeline_item_title').' Marked as completed by '.$timeline_item->Get('timeline_item_completed_by').' on '.$d->GetDate('F j');
	
							if(!$user_url)
								$user_url=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');
						}
					}
					if($timeline_items->GetTotalAvailable()>3)
						$content[]=' * '.' there are '.($timeline_items->GetTotalAvailable()-3).' additional items marked compelte as well';

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

					echo('USER CONTACT '.$user->Get('user_name').' '.$user->Get('user_address').' : '.$phone.' ('.$timeline_items->GetTotalAvailable().' Updates)');
					echo("<br>");
				}
				catch(Exception $e)
				{
					echo($e->getMessage()."<br>\r\n");
				}				
			}
		}
	}
	
	public function SendReminder($params=array())
	{
		$settings=json_decode($this->Get('user_contact_settings'),true);		

		$user=new user($this->Get('user_id'));
		$agent=new agent($user->Get('agent_id'));
/*
		$today=new date();
		$where=array("user_id='".$user->id."' AND timeline_item_active=1");
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete=0";
//		$where[]="timeline_item_for='USER'";
//		$where[]="timeline_item_agent_only=0";
//		$where[]="(timeline_item_date>='".$today->GetDBDate()."' OR timeline_item_reference_date_type='NONE')";
//		$where[]="(timeline_item_date<='".$date->GetDBDate()."' AND timeline_item_reference_date_type!='NONE')";
		$order='timeline_item_order';
		if($user->Get('user_under_contract'))
			$order='timeline_item_date,timeline_item_order';

		$types=array('-1');
		if($settings['notifications']['agent'])
			$types[]="AGENT";
		if($settings['notifications']['other'])
			$types[]="OTHER";
		if($settings['notifications']['user'])
			$types[]="USER";
		$where[]="timeline_item_for IN('".implode("','",$types)."')";

	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order);
		$timeline_items->Retrieve();
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		
		//we only want one.
		if(count($timeline_items->items))
			$timeline_items->items=array($timeline_items->items[0]);
*/
		$timeline_items=$agent->GetNextTasks($user,"timeline_item_for IN('USER')");

		$reminder_sent=0;
		if(count($timeline_items))
		{
			if($settings['notifications']['email'])
			{
				$mail_params=array('base_url'=>_navigation::GetBaseURL());
				$mail_params+=$user->GetMailParams();
				$mail_params+=$this->GetMailParams();
				$mail_params+=$agent->GetMailParams();	
				$mail_params['message']=nl2br($params['message']);
		
				$mail_params['timeline_items']=array();
			  	foreach($timeline_items as $i=>$timeline_item)
			  	{
					//if(!$timeline_item->DependsOnIncompleteItem() and !$timeline_item->IsNotApplicable())
					{
						$d=new dbdate($timeline_item->Get('timeline_item_date'));
						$mail_params['timeline_items'][$i]=array();
						$mail_params['timeline_items'][$i]+=$timeline_item->attributes;
						$mail_params['timeline_items'][$i]['date']=$d->GetDate('F j');
						$mail_params['timeline_items'][$i]['has_date']=$d->IsValid();
						$mail_params['timeline_items'][$i]['url']=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');
						if(!$mail_params['user_url'])
							$mail_params['user_url']=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');;

						activity_log::Log($this->GetAgent(),'TIMELINE_ITEM_REMINDER','Email Reminder Sent for '.$timeline_item->Get('timeline_item_title').' to '.$this->GetFullName(),$this->Get('user_id'));

					}
				}
				$mail_params['opt_out_link']=$this->ToURL('optout.php');
		
				$headers=array();
				$headers[]="reply-to:".$agent->Get('agent_email');
				email::templateMail($this->Get('user_contact_email'),email::GetEmail(),"What's Next Real Estate Reminder for ".($user->Get('user_address')?$user->Get('user_address'):$user->Get('user_name')),file::GetPath('email_user_contact_reminders'),$mail_params,$headers);
				
				$reminder_sent++;
			}
			if($settings['notifications']['phone'])
			{
				$phone=$this->TwillioFormat($this->Get('user_contact_phone'));
				$client = new \Twilio\Rest\Client(TWILLIO_SID,TWILLIO_KEY);
				try
				{
					$content=array();
					$content[]="What's Next Real Estate Reminder for ".($user->Get('user_address')?$user->Get('user_address'):$user->Get('user_name'));
					$content[]="";
				  	foreach($timeline_items->items as $i=>$timeline_item)
				  	{
						//if(!$timeline_item->DependsOnIncompleteItem() and !$timeline_item->IsNotApplicable())
						{
							$d=new dbdate($timeline_item->Get('timeline_item_date'));
							if($d->IsValid())
								$content[]=' * '.$timeline_item->Get('timeline_item_title').' due on '.$d->GetDate('F j');
							else
								$content[]=' * '.$timeline_item->Get('timeline_item_title').' due';
	
							if(!$user_url)
								$user_url=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');

							activity_log::Log($this->GetAgent(),'TIMELINE_ITEM_REMINDER','SMS Reminder Sent for '.$timeline_item->Get('timeline_item_title').' to '.$this->GetFullName(),$this->Get('user_id'));

						}
					}
					if($params['message'])
					{
						$content[]="";
						$content[]=$params['message'];
					}
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
					$content=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');
					$res=$client->messages->create($phone,array('from'=>TWILLIO_NUMBER,'body'=>$content));
				}
				catch(Exception $e)
				{
					echo($e->getMessage()."<br>\r\n");
				}	

				$reminder_sent++;							
			}

			if($reminder_sent)
			{
				foreach($timeline_items->items as $i=>$timeline_item)
				{
					$timeline_item->Set('timeline_item_notified',time());
					$timeline_item->Update();
				}
			}
		}		
	}

	public function SendEmailReminders($where=array())
	{
		$settings=json_decode($this->Get('user_contact_settings'),true);		

		$user=new user($this->Get('user_id'));
		$agent=new agent($user->Get('agent_id'));

		$date=new date();
		$date->Add(7);

		$where[]="users.user_id='".$user->id."' AND timeline_item_active=1";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete=0";
//		$where[]="timeline_item_for='USER'";
//		$where[]="timeline_item_agent_only=0";
//		$where[]="(timeline_item_date>='".$today->GetDBDate()."' OR timeline_item_reference_date_type='NONE')";
		$where[]="(timeline_item_date<='".$date->GetDBDate()."' AND timeline_item_reference_date_type!='NONE')";

		$types=array('-1');
		if($settings['notifications']['agent'])
			$types[]="AGENT";
		if($settings['notifications']['other'])
			$types[]="OTHER";
		if($settings['notifications']['user'])
			$types[]="USER";
		$where[]="timeline_item_for IN('".implode("','",$types)."')";


		$order='timeline_item_order';
		if($user->Get('user_under_contract'))
			$order='timeline_item_date,timeline_item_order';
	  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order);
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
				$mail_params+=$user->GetMailParams();
				$mail_params+=$this->GetMailParams();
				$mail_params+=$agent->GetMailParams();	
		
				$mail_params['timeline_items']=array();
			  	foreach($timeline_items->items as $i=>$timeline_item)
			  	{
			  	 	//if(!$timeline_item->DependsOnIncompleteItem() and !$timeline_item->IsNotApplicable())
			  	 	{
						$d=new dbdate($timeline_item->Get('timeline_item_date'));
						$mail_params['timeline_items'][$i]=array();
						$mail_params['timeline_items'][$i]+=$timeline_item->attributes;
						$mail_params['timeline_items'][$i]['date']=$d->GetDate('F j');
						$mail_params['timeline_items'][$i]['has_date']=$d->IsValid();
						$mail_params['timeline_items'][$i]['url']=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');
						if(!$mail_params['user_url'])
							$mail_params['user_url']=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');;

						activity_log::Log($this->GetAgent(),'TIMELINE_ITEM_REMINDER','Email Reminder Sent for '.$timeline_item->Get('timeline_item_title').' to '.$this->GetFullName(),$this->Get('user_id'));
					}
				}
				$mail_params['opt_out_link']=$this->ToURL('optout.php');
		
				echo('USER CONTACT '.$user->Get('user_name').' '.$user->Get('user_address').' : '.$this->Get('user_contact_email').' ('.$timeline_items->GetTotalAvailable().' Reminders)');
				echo("<br>");
		
				$headers=array();
				$headers[]="reply-to:".$agent->Get('agent_email');
				email::templateMail($this->Get('user_contact_email'),email::GetEmail(),"What's Next Real Estate Reminder for ".($user->Get('user_address')?$user->Get('user_address'):$user->Get('user_name')),file::GetPath('email_user_contact_reminders'),$mail_params,$headers);
			}
		}
	}

	public function SendSMSReminders($where=array())
	{
		$settings=json_decode($this->Get('user_contact_settings'),true);		

		$user=new user($this->Get('user_id'));
		$agent=new agent($user->Get('agent_id'));

		$date=new date();
		$date->Add(7);

		$where[]="users.user_id='".$user->id."' AND timeline_item_active=1";
		$where[]="timeline_item_type='TIMELINE'";
		$where[]="timeline_item_complete=0";
		$where[]="timeline_item_for='USER'";
//		$where[]="timeline_item_agent_only=0";
//		$where[]="(timeline_item_date>='".$today->GetDBDate()."' OR timeline_item_reference_date_type='NONE')";
		$where[]="(timeline_item_date<='".$date->GetDBDate()."' AND timeline_item_reference_date_type!='NONE')";

		$order='timeline_item_order';
		if($user->Get('user_under_contract'))
			$order='timeline_item_date,timeline_item_order';
	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),$order,3);
		$timeline_items->join_tables='users';
		$timeline_items->join_where='users.user_id=timeline_items.user_id';
	  	$timeline_items->Retrieve();
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		

		if(count($timeline_items->items))
		{
			if($settings['notifications']['phone'])
			{
				$phone=$this->TwillioFormat($this->Get('user_contact_phone'));
				$client = new \Twilio\Rest\Client(TWILLIO_SID,TWILLIO_KEY);
				echo('USER CONTACT '.$user->Get('user_name').' '.$user->Get('user_address').' : '.$phone.' ('.$timeline_items->GetTotalAvailable().' Reminders)');
				echo("<br>");
				try
				{
					$content=array();
					$content[]="What's Next Real Estate Reminders for ".($user->Get('user_address')?$user->Get('user_address'):$user->Get('user_name'));
					$content[]="";
				  	foreach($timeline_items->items as $i=>$timeline_item)
				  	{
						//if(!$timeline_item->DependsOnIncompleteItem() and !$timeline_item->IsNotApplicable())
						{
							$d=new dbdate($timeline_item->Get('timeline_item_date'));
							if($d->IsValid())
								$content[]=' * '.$timeline_item->Get('timeline_item_title').' due on '.$d->GetDate('F j');
							else
								$content[]=' * '.$timeline_item->Get('timeline_item_title').' due';
							if(!$user_url)
								$user_url=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');
						}

						activity_log::Log($this->GetAgent(),'TIMELINE_ITEM_REMINDER','SMS Reminder Sent for '.$timeline_item->Get('timeline_item_title').' to '.$this->GetFullName(),$this->Get('user_id'));

					}
					if($timeline_items->GetTotalAvailable()>count($timeline_items->items))
						$content[]=' * '.' there are '.($timeline_items->GetTotalAvailable()-count($timeline_items->items)).' additional items to compelte as well';
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

	public function TOS()
	{
		if($this->IsLoggedIn() and !$this->Get('user_contact_tos_timestamp'))	
		{
			Javascript::Begin();
			echo("jQuery(function(){
				ObjectFunctionAjaxPopup('Terms Of Service','user_contact','".$this->id."','TermsOfService','NULL','','',function(){},'','modeless');
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
		$tos=html::ProcessTemplateFile(file::GetPath('user_terms_of_service'),$content_params);

	 	if($params['action']==$this->GetFormAction('tos'))
	 	{
			$this->Set('user_contact_tos_timestamp',time());
			$this->Set('user_contact_tos',$tos);
			$this->Update();

			activity_log::Log($this,'ACCEPTED_TOS','Terms Of Service Accepted');
		}
		
		$js="ObjectFunctionAjax('user_contact','".$this->id."','TermsOfService','popup_content','TermsOfService','','&action=".$this->GetFormAction('tos')."',function(){PopupClose();});return false;";
		echo("<div class='tos'>");
		form::Begin('','POST',false,array('id'=>'TermsOfService'));
		echo("<div class='tos_content'>".$tos."</div>");
		form::DrawButton('','I agree',array('onclick'=>$js));
		form::End();
		echo("</div>");
	}

	public function SendWelcomeMessage($params=array())
	{
		if(!$this->Get('user_contact_email'))
			return;

	 	$user=new user($this->Get('user_id'));
	 	$agent=new agent($user->Get('agent_id'));
	 
		$mail_params=array('base_url'=>_navigation::GetBaseURL());
		$mail_params+=$this->attributes;
		$mail_params+=$user->attributes;
		$mail_params+=$agent->attributes;
		$mail_params['user_url']=$this->ToURL();
		$mail_params['message']=nl2br($params['message']);
		email::templateMail($this->Get('user_contact_email'),email::GetEmail(),"Welcome To What's Next",file::GetPath('email_user_contact_welcome'),$mail_params);

		$this->Set('user_contact_welcome_timestamp',time());
		$this->Set('user_contact_welcome_email',$this->Get('user_contact_email'));
		$this->Update();
		
		activity_log::Log($this->GetAgent(),'WELCOME_EMAIL','Welcome Email Sent To - '.$this->Get('user_contact_name').' '.$this->Get('user_contact_email'),$this->Get('user_id'));
	}

	public function SendLoginReminder($params=array())
	{
		$settings=json_decode($this->Get('user_contact_settings'),true);		
		if($settings['notifications']['email'])
		{
		 	$user=new user($this->Get('user_id'));
		 	$agent=new agent($user->Get('agent_id'));
		 
			$mail_params=array('base_url'=>_navigation::GetBaseURL());
			$mail_params+=$this->attributes;
			$mail_params+=$user->attributes;
			$mail_params+=$agent->attributes;
			$mail_params['user_url']=$this->ToURL();
			$mail_params['message']=nl2br($params['message']);

			email::templateMail($this->Get('user_contact_email'),email::GetEmail(),"Welcome To What's Next",file::GetPath('email_user_contact_login_reminder'),$mail_params);
	
			$this->Set('user_contact_last_login_reminder',time());
			$this->Update();
			
			activity_log::Log($this->GetAgent(),'LOGIN_EMAIL','Login Reminder Sent To - '.$this->Get('user_contact_name').' '.$this->Get('user_contact_email'),$this->Get('user_id'));
			
		}
	}

	public function NotifyClient($params=array())
	{
		$settings=json_decode($this->Get('user_contact_settings'),true);		

		$user=new user($this->Get('user_id'));
		$agent=new agent($user->Get('agent_id'));
		$where=array("user_id='".$user->id."' AND timeline_item_active=1");
		$where[]="timeline_item_id IN('".implode("','",$params['timeline_item_ids'])."')";

	  	$timeline_items=new DBRowSet('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_complete');
	  	$timeline_items->Retrieve();
		//remove depends on incomplete and N/A items
		$timeline_items->items = array_values(array_filter($timeline_items->items, function($timeline_item) { return !$timeline_item->DependsOnIncompleteItem() && !$timeline_item->IsNotApplicable();}));		

  		if(count($timeline_items->items))
		{
			if($settings['notifications']['email'])
			{
				$mail_params=array('base_url'=>_navigation::GetBaseURL());
				$mail_params+=$user->GetMailParams();
				$mail_params+=$this->GetMailParams();
				$mail_params+=$agent->GetMailParams();	
		
				$mail_params['timeline_items']=array();
			  	foreach($timeline_items->items as $i=>$timeline_item)
			  	{
					$d=new dbdate($timeline_item->Get('timeline_item_date'));
					$mail_params['timeline_items'][$i]=array();
					$mail_params['timeline_items'][$i]+=$timeline_item->attributes;
					$mail_params['timeline_items'][$i]['timeline_item_date']=$d->GetDate('m/d/Y');
					$mail_params['timeline_items'][$i]['url']=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');
					if(!$mail_params['user_url'])
						$mail_params['user_url']=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');;
				}
				$mail_params['opt_out_link']=$this->ToURL('optout.php');
		
//				echo('USER CONTACT '.$user->Get('user_name').' '.$user->Get('user_address').' : '.$this->Get('user_contact_email').' ('.$timeline_items->GetTotalAvailable().' Notices)');
//				echo("<br>");
		
				$headers=array();
				$headers[]="reply-to:".$agent->Get('agent_email');
				email::templateMail($this->Get('user_contact_email'),email::GetEmail(),"What's Next Real Estate Notice for ".($user->Get('user_address')?$user->Get('user_address'):$user->Get('user_name')),file::GetPath('email_user_contact_notices'),$mail_params,$headers);
			}
			if($settings['notifications']['phone'])
			{
				$phone=$this->TwillioFormat($this->Get('user_contact_phone'));
				$client = new \Twilio\Rest\Client(TWILLIO_SID,TWILLIO_KEY);
				try
				{
					$content=array();
					$content[]="What's Next Real Estate Notices for ".($user->Get('user_address')?$user->Get('user_address'):$user->Get('user_name'));
					$content[]="";
				  	foreach($timeline_items->items as $i=>$timeline_item)
				  	{
						//if(!$timeline_item->DependsOnIncompleteItem() and !$timeline_item->IsNotApplicable())
						{
							$d=new dbdate($timeline_item->Get('timeline_item_date'));
	
							$content[]=' * '.$timeline_item->Get('timeline_item_title').' * ';
							$content[]=' Date: '.$d->GetDate('m/d/Y');
							$content[]=strip_tags(br2nl($timeline_item->Get('timeline_item_summary')));
							$content[]='';
								
							if(!$user_url)
								$user_url=$this->ToURL().'/#'.$timeline_item->GetFieldName('anchor');
						}
					}

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

//					echo('USER CONTACT '.$user->Get('user_name').' '.$user->Get('user_address').' : '.$phone.' ('.$timeline_items->GetTotalAvailable().' Notices)');
//					echo("<br>");
				}
				catch(Exception $e)
				{
					echo($e->getMessage()."<br>\r\n");
				}				
			}
		}
	}


	function FooterScripts($params=array())
	{
		if($params['action']==$this->GetFormAction('add_to_calendar'))	
		{
			Javascript::Begin();
			echo("jQuery(function(){ObjectFunctionAjaxPopup('Add To Calendar','user','".$this->Get('user_id')."','AddToCalendarInfo','NULL','','user_id=".$this->Get('user_id')."&user_contact_id=".$this->id."');});");	
			Javascript::End();
		}		
		else
			$this->RecentActivityCheck();
	}

	function HasNotifications()
	{
		$settings=json_decode($this->Get('user_contact_settings'),true);		
		return($settings['notifications']['phone'] or $settings['notifications']['email']);
		
	}
};

?>