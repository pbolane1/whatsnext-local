<?php
//**************************************************************//
//	
//	FILE: c_admin.php
//  CLASS: admin
//  
//	STUBBED BY: PoCo Technologies LLC CoreLib Autocoder v0.0 BETA
//  PURPOSE: database abstraction for the admins table
//  STUBBED TIMESTAMP: 1212155116
//
//**************************************************************//

class admin extends DBRowEx
{
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(false);
		$this->EstablishTable('admins','admin_id');
		$this->Retrieve();
	}

	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{
			$today=new Date();
			$this->Set('admin_reset_date',$today->GetDBDate());
			$this->Set('admin_active',1);
			$this->Set('admin_type','ADMIN');
		}
	}

	public function DisplayEditable()
	{
		echo("<td onclick=\"".$js."\">".$this->Get('admin_name')."</td>");
	 	echo("<td class='hidden-sm hidden-xs'>".$this->Get('admin_email')."</td>");
	 	echo("<td>".$this->GetAdminType()."</td>");
	 //	echo("<td>".implode('<br>',$this->GetPermissions())."</td>");
	 	
	}

	public function GetFullName()
	{
		return "System Administrator";
	}
	

	public function GetAdminType()
	{
		if($this->Get('admin_type')=='ADMIN')	
			return 'Master Admin';
	}

	public function HasPermission($which,$id)
	{
		if($this->IsMaster())
			return true;
		else if(in_array($which,explode(',',$this->Get('admin_permissions'))))
			return true;

		return false;
	}
	
	public function AddPermission($which)
	{
		$permissions=explode(',',$this->Get('admin_permissions'));
		if(!in_array($which,$permissions))
			$permissions[]=$which;
		$this->Set('admin_permissions',implode(',',$permissions));
		$this->Update();
	}	

	public function RemovePermission($which)
	{
		$permissions=explode(',',$this->Get('admin_permissions'));
		if(in_array($which,$permissions))
			$permissions=array_diff($permissions,array($which));
		$this->Set('admin_permissions',implode(',',$permissions));
		$this->Update();
	}	

	public function GetPermissionOptions()
	{
	 	$data['ADMINS']='Manage Admins';
	
		return $data;	
	}
		
	public function IsMaster()
	{
		return $this->Get('admin_type')=='ADMIN';
	}
	
	public function GetPermissions()	
	{
	 	if($this->IsMaster())
	 		return array('Master Administrator');
	 
	 	$names=array();
	 	$opts=$this->GetPermissionOptions();
		$permissions=explode(',',$this->Get('admin_permissions'));
		foreach($permissions as $k)
			$names[]=$opts[$k];
		asort($names);
						
		return $names;
	}
	
	public function DeleteLink()
    {
		if($this->IsMaster())
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('cancel').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('admin_active'),0);
			form::DrawButton('','DELETE',array('class'=>'disabled'));
			form::end();
		}
		else if($this->Get('admin_active'))
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('admin_active'),0);
			form::DrawSubmit('','DELETE',array('onclick'=>"return confirm('Are you sure you want to disable this admin?');"));
			form::end();
		}
		echo("</td>");
	}

	public function EditLink()
    {
		if(!$this->Get('admin_active'))
		{	
		 	echo("<td class='edit_actions'>");
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('admin_active'),1);
			form::DrawSubmit('','RE-ACTIVATE',array('onclick'=>"return confirm('Are you sure you want to reactivate this admin?');"));
			form::end();			
		}
		else
			parent::EditLink();
	}


	public function DisplayFull()
	{	
	}

	public function DisplayShort()
	{	

	}

	public function DisplayNavigation($admin_id=0)
	{
	}

	public function Display()
	{
		parent::display();
 	}
	public function EditForm()
	{
		global $HTTP_POST_VARS;
	
		echo("<td colspan='2' align='center'></td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Name</td><td>");
		form::DrawTextInput($this->GetFieldName('admin_name'),$this->Get('admin_name'),array('class'=>$this->GetError('admin_name')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Email</td><td>");
		form::DrawTextInput($this->GetFieldName('admin_email'),$this->Get('admin_email'),array('class'=>$this->GetError('admin_email')?'error':'text'));
		echo("</td></tr>");
		if(!$this->id)
		{
			echo("<tr><td class='label'>".REQUIRED." Password</td><td>");
			form::DrawTextInput($this->GetFieldName('admin_password_new'),$HTTP_POST_VARS[$this->GetFieldName('admin_password_new')],array('class'=>$this->GetError('admin_password_new')?'error':'text'));
			echo("</td></tr>");
		}
		else
		{
			echo("<tr><td class='label'>Password</td><td>*******</td></tr>");
			echo("<tr><td class='label'>Change Password</td><td>");
			form::DrawTextInput($this->GetFieldName('admin_password_new'),$HTTP_POST_VARS[$this->GetFieldName('admin_password_new')],array('class'=>$this->GetError('admin_password_new')?'error':'text'));
			echo("</td></tr>");
		}

		echo("<tr><td class='label'>Permissions</td><td>");
		$opts=array();
//		foreach($this->GetPermissionOptions() as $k=>$name)
//			$opts[$name]=$k;
//		form::DrawSelect($this->GetFieldName('admin_permissions').'[]',$opts,explode(',',$this->Get('admin_permissions')),array('multiple'=>'multiple','class'=>$this->GetError('admin_permissions')?'error':'text'));
//		form::DrawSelect($this->GetFieldName('admin_master'),array('Master Admin'=>1,'Property Manager'=>0),$this->Get('admin_master'));
		form::DrawSelect($this->GetFieldName('admin_type'),array('Master Admin'=>'ADMIN'),$this->Get('admin_type'));
		echo("</td></tr>");	

		if($this->id)
		{
			echo("<tr><td class='label'>Reassign Properties To</td><td>");
			form::DrawSelectFromSQL($this->GetFieldName('reassign_admin_id'),"SELECT * FROM admins WHERE admin_active=1 AND admin_type IN('ADMIN','PM')","admin_name","admin_id",$HTTP_POST_VARS[$this->GetFieldName('reassign_admin_id')],array(),array(''=>''));
			echo("</td></tr>");	
		}
			
		echo("<tr><td colspan='2' class='save_actions'>");		
 	}

	public function GatherInputs()
	{
		//parent is default
		parent::GatherInputs();

		global $HTTP_POST_VARS;
		if(!$HTTP_POST_VARS[$this->GetFieldName('password_reset')])
			$this->Set('admin_permissions',implode(',',$HTTP_POST_VARS[$this->GetFieldName('admin_permissions')]));

		//departments
//		$this->all_departments=$HTTP_POST_VARS[$this->GetFieldName('all_departments')];
//		if($this->all_departments)
//			$this->GatherRelated('department_ids',$HTTP_POST_VARS['NULL']);
//		$this->GatherRelated('department_ids',$HTTP_POST_VARS['department_ids']);
		
		if($HTTP_POST_VARS['set_property_ids'])
			$this->Set('property_ids',implode(',',$this->Get('property_ids')));
		
	}

	public function ValidateInputs()
	{
		global $HTTP_POST_VARS;

		if(!$this->Get('admin_name') and $HTTP_POST_VARS[$this->GetFieldName('admin_name_required')])
			$this->LogError('Please Enter Name','admin_name');

		if(!$this->Get('admin_email'))
			$this->LogError('Please Enter Email','admin_email');
		else if(!email::ValidateEmail($this->Get('admin_email')))
			$this->LogError('Email Address Does Not Appear To Be Valid','admin_email');
		else if(!$this->ValidateUnique('admin_email'))
			$this->LogError('An Account Already Exists For This Email Address.  Please Login.','admin_email');
		else if($HTTP_POST_VARS[$this->GetFieldName('email_verify')])
		{
			if(!$HTTP_POST_VARS[$this->GetFieldName('admin_email2')])  
				$this->LogError('Please Re-Enter Email','admin_email2');
			else if($HTTP_POST_VARS[$this->GetFieldName('admin_email2')]!=$this->Get('admin_email'))  
				$this->LogError('Email Entries Do Not Match','admin_email2');
		}	

		$newpwd=$HTTP_POST_VARS[$this->GetFieldName('admin_password_new')];			
		$newpwd2=$HTTP_POST_VARS[$this->GetFieldName('admin_password_new2')];			
		if(!$this->Get('admin_password') and !$newpwd)
			$this->LogError('Please Enter Password','admin_password_new');
		else if($newpwd)
		{
			if(strlen($newpwd)<8)
				$this->LogError('Password must be at least 8 characters','admin_password_new');
			else if(!preg_match("#[0-9]+#",$newpwd) or !preg_match("#[a-z]+#",$newpwd) or !preg_match("#[A-Z]+#",$newpwd) or !preg_match("#\W+#",$newpwd))
				$this->LogError('Password must include at least one uppercase letter, one lowercase letter, one number and one symbol','admin_password_new');
			else if($HTTP_POST_VARS[$this->GetFieldName('password_verify')])
			{
				if(!$newpwd2)  
					$this->LogError('Please Re-Enter Password','admin_password_new2');
				else if($newpwd!=$newpwd2)  
					$this->LogError('Passwords Do Not Match','admin_password_new2');
			}	
		}
				
		if(!count($this->errors) and $newpwd)
		{
			$this->Set('admin_password',md5($newpwd)); 
		}

		return count($this->errors)==0;
 	}

	public function Save()
	{	  	  
		global $HTTP_POST_VARS;
		
	  	$new=!$this->id;
		$psv=parent::Save();
		if($psv)
		{
			$this->Set('admin_reset_code','');
			$this->Update();

			if(!$this->GetFlag('ADMIN'))
				$this->msg='Your changes have been saved';

			if($HTTP_POST_VARS[$this->GetFieldName('reassign_admin_id')])
			{
				database::query("UPDATE properties SET admin_id='".$HTTP_POST_VARS[$this->GetFieldName('reassign_admin_id')]."' WHERE admin_id='".$this->id."'");	
			}

			$this->Set('admin_reset_code','');
			$this->Update();
		}
		return count($this->errors)==0;
	}

	public function Delete()
	{
		$this->Set('admin_active',0);
		$this->Update();
		
	}

	public function xDelete()
	{
		parent::Delete();
	}


	public function IsLoggedIn()
	{
		return(Session::Get('pbt_admin_login') and Session::Get('admin_id'));	  	  
	}

	public function ResetPassword($redir='',$requirecode=true)
	{
	  	global $HTTP_POST_VARS,$HTTP_GET_VARS;
	  	foreach($HTTP_GET_VARS as $k=>$v)
	  		$$k=$v;
	  	foreach($HTTP_POST_VARS as $k=>$v)
	  		$$k=$v;

		echo '<div class="login_form">';
		if($this->Get('admin_reset_code') or !$requirecode)
		{
			if($action=='send_pwd' and $this->msg)
				echo('<div class="message">Please check your email for details on resetting your password.</div>');
			foreach($this->GetErrors() as $e)
				echo('<div class="error">'.$e.'</div>');
			form::begin('?action='.$this->GetFormAction('save').$this->GetFormExtraParams(),'POST',false,array('id'=>'login'));
			form::DrawHiddenInput($this->GetFieldName('password_reset'),1);
			form::DrawInput('password',$this->GetFieldName('admin_password_new'),$HTTP_POST_VARS[$this->GetFieldName('admin_password_new')],array('class'=>'text password','placeholder'=>'Enter New Password'));
			form::DrawHiddenInput($this->GetFieldName('password_verify'),1);
			form::DrawInput('password',$this->GetFieldName('admin_password_new2'),$HTTP_POST_VARS[$this->GetFieldName('admin_password_new2')],array('class'=>'text password','placeholder'=>'Re-Enter New Password'));
			form::DrawSubmit('','Reset Password');
			form::End();

		}
		else if(!$this->id)
		{
			echo("<h2>Reset Password</h2>");
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
	}

	public function LoginForm($redir='')
	{
	  	global $HTTP_POST_VARS,$HTTP_GET_VARS;
	  	foreach($HTTP_GET_VARS as $k=>$v)
	  		$$k=$v;
	  	foreach($HTTP_POST_VARS as $k=>$v)
	  		$$k=$v;

		echo("<div class='login_form'>");
		if($this->IsLoggedIn())
		{
			echo "<span class='class'>Logged In As ".$this->Get('admin_name')."<br /></span>";
			echo '<br><span class="class"><a href="?action=logout" >Log Out</a></span>';
			return;
		}

		if($this->GetError('login'))
			echo('<div class="error">Wrong email or password.</div>');
			
		echo("<div id='login_div' style='display:".(($action!='send_pwd' or $this->msg)?'block':'none')."'>");
		if($action=='send_pwd' and $this->msg)
			echo('<div class="error">Please check your email for details on resetting your password.</div>');
		form::begin('?action=login'.$this->GetFormExtraParams(),'POST',false,array('id'=>'login'));
		form::DrawTextInput('admin_email',$HTTP_POST_VARS['admin_email'],array('placeholder'=>'Email Address'));
		form::DrawInput('password','admin_password',$HTTP_POST_VARS['admin_password'],array('placeholder'=>'Password'));
		form::DrawSubmit('','Sign In');
		echo '<a href="#" onclick="document.getElementById(\'forgot-password\').style.display=\'block\';document.getElementById(\'login_div\').style.display=\'none\';return false;">Forget your password?</a>';
		form::End();
		echo('</div>');

		echo("<div id='forgot-password' style='display:".(($action=='send_pwd' and  !$this->msg)?'block':'none')."'>");
		if($action=='send_pwd' and $this->GetError('send_pwd'))
			echo('<div class="error">Email Not Found.</div>');
		form::Begin('?action=send_pwd','POST',false,array('class'=>"forgot-password"));
		form::DrawTextInput('admin_email',$HTTP_POST_VARS['admin_email'],array('placeholder'=>'Email Address'));
		form::DrawSubmit('','Reset Password');
		form::End();
		echo('</div>');

	}


	public function Login($in=true,$silent=false)
	{
		parent::Login($in);

		Session::Set('pbt_admin_login',$in?1:0);
		Session::Set('admin_id',$in?$this->id:0);	  
		if($in)
		{
			$this->Set('admin_reset_code','');
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
	
	public function ProcessLogin($no_redir=false)
	{
	  	global $HTTP_POST_VARS,$HTTP_GET_VARS;
	  	foreach($HTTP_POST_VARS as $k=>$v)
	  		$$k=$v;
	  	foreach($HTTP_GET_VARS as $k=>$v)
	  		$$k=$v;
	  		
		if($action=='login')  
		{
			$rs=database::query("SELECT admin_id FROM admins WHERE admin_email='".$this->MakeDBSafe($admin_email)."' AND admin_password!='' AND admin_active=1 AND admin_password='".md5($admin_password)."'");		  
			if($rec=database::fetch_array($rs))		  
			{
				$this->__construct($rec['admin_id']);
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
		if($action=='login_as')  
		{
			$this->__construct($HTTP_GET_VARS['admin_id']);
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
			$rs=database::query("SELECT * FROM admins WHERE admin_email='".$admin_email."'");		  
		  	if(!$admin_email)
		  		$this->LogError("Please Enter Email Address",$action);
			else if($rec=database::fetch_array($rs))		  
			{
			  	$date=new Date();
			  	$date->Add('1');
				$tempadmin=new admin($rec['admin_id']);
				$tempadmin->Set('admin_reset_code',Text::GenerateCode(30,40));
				$tempadmin->Set('admin_reset_date',$date->GetDBDate());
				$tempadmin->Update();
				email::templateMail($admin_email,email::GetEmail(),'Your Account',file::GetPath('email_admin_password'),$tempadmin->attributes+array('base_url'=>_navigation::GetBaseURL()));
				$this->msg='You have been emailed a link to reset your password';
			}
		  	else if($rec=database::fetch_array($rs2))		  
		  	{
				$this->msg='You have been emailed a link to reset your password';
			}
		  	else
		  		$this->LogError("Email Address Not Found",$action);
		}	
		if($action==$this->GetFormAction('save'))
		{
			if($this->Save())
			{
				$this->Login();
				if($redir)
					_navigation::Redirect($redir);
			}
		}		
	}
};

?>