<?php
class contact extends DBRowEx
{
	public function __construct()
	{

	}

	public function ProcessAction($act='')
	{
		if($act=='contact_email')
			$this->MailToAdmin();			
  	}

	public function DrawContactForm($type,$content,$to_email='',$template='',$get_vars='',$template_vars='')
	{
	  	global $HTTP_GET_VARS,$HTTP_POST_VARS,$user;
		$this->to_email=$to_email;
		$this->template=$template;
		$this->template_vars=$template_vars;
		if($HTTP_GET_VARS['action']=='contact_email')
			$this->ProcessAction($HTTP_GET_VARS['action']);

		foreach($template_vars as $k=>$v)
			$HTTP_POST_VARS[$k]=$HTTP_POST_VARS[$k]?$HTTP_POST_VARS[$k]:$v;
			
		$show_form=true;//(count($this->errors)>0 or $HTTP_GET_VARS['action']!='contact_email');

		//prepopulate
//		$name=email::ParseFullNameForCSV($user->Get('user_name'));
//		if(!$HTTP_GET_VARS['action'] and !$HTTP_POST_VARS['first_name']) 	$HTTP_POST_VARS['first_name']=$name['first_name'];
//		if(!$HTTP_GET_VARS['action'] and !$HTTP_POST_VARS['last_name']) 	$HTTP_POST_VARS['last_name']=$name['last_name'];
//		if(!$HTTP_GET_VARS['action'] and !$HTTP_POST_VARS['name']) 	 		$HTTP_POST_VARS['name']=$user->Get('user_name');
//		if(!$HTTP_GET_VARS['action'] and !$HTTP_POST_VARS['email']) 	 	$HTTP_POST_VARS['email']=$user->Get('user_email');
//		if(!$HTTP_GET_VARS['action'] and !$HTTP_POST_VARS['phone']) 	 	$HTTP_POST_VARS['phone']=$user->Get('user_phone');

		echo("<a name='contact_form'></a>");
		echo("<div class='contactform user_form'>");
		echo("<h4>Contact Us</h4>");
	  	if(count($this->errors))
	  	{
			echo("<div class='error'>OOPS! There was an error.</div>");
			foreach($this->errors as $e)
				echo("<div class='error2'>".$e."</div>"); 
	  	}
		if($this->success)
 		{
			echo("<div class='message'>Thank you for your inquiry.</div>");
			echo("<div class='message2'>We will respond as soon as possible!</div>"); 
			//$this->LogError('Thank You! Your Message Has Been Sent');
			//$this->JSReportErrors();
			//$this->ClearErrors();

			echo("<br>");
			$show_form=false;
		}


		if($show_form)
		{
			Form::Begin('?action=contact_email'.$get_vars.'#contact_form');		  
			Form::DrawHiddenInput('type',$type);
			echo("<div class='formlabel'>Name</div>");
			form::DrawTextInput('name',$HTTP_POST_VARS['name']);
			echo("<div class='formlabel'>Email Address</div>");
			form::DrawTextInput('email',$HTTP_POST_VARS['email']);
			echo("<div class='formlabel'>Phone</div>");
			form::DrawTextInput('phone',$HTTP_POST_VARS['phone']);
			echo("<div class='formlabel'>Comments / Inquiry</div>");
			form::DrawTextArea('comments',$HTTP_POST_VARS['comments']);
			echo("<div class='formlabel'>Enter Code</div>");
			Captcha::DrawSimple('captcha',array('placeholder'=>''));
			form::DrawSubmit('','Submit');
			form::End();
		}
		echo("</div>");

	}	
	
	public function MailToAdmin()
	{
	  	global $HTTP_GET_VARS,$HTTP_POST_VARS;
		if(!$HTTP_POST_VARS['name'])
			$this->LogError('Please Enter Your Name');
		else if(!$HTTP_POST_VARS['email'])
			$this->LogError('Please Enter Your Email Address');
		else if(!email::ValidateEmail($HTTP_POST_VARS['email']))
			$this->LogError('Your Email Address Does Not Appear To Be Valid');			
		else if(!captcha::Process())
			$this->LogError(captcha::GetError());			
		else if($this->ValidateSpam($HTTP_POST_VARS['comments']))
		{	
		  	if(!$this->to_email)		$this->to_email=email::GetEmail();		  
		  	if(!$this->template)		$this->template=file::GetPath('email_contact');		  
		  	if(!$this->template_vars)	$this->template_vars=array();		  

			$subject=$HTTP_POST_VARS['subject']?$HTTP_POST_VARS['subject']:$HTTP_POST_VARS['type'].' - Website inquiry from '.$HTTP_POST_VARS['first_name'].' '.$HTTP_POST_VARS['last_name'];

			$this->template_vars['newsletter_optin']=$HTTP_POST_VARS['newsletter']?'Yes':'No';

			//debug...
			//$this->to_email='paul@pocotechnology.com';

			//admin mailer
			email::templateMail($this->to_email,$HTTP_POST_VARS['email'],$subject,$this->template,$HTTP_POST_VARS+$this->template_vars);
			
			$this->success=true;
		}
 	}

	public function ValidateSpam($comments)
	{
		$compacted=str_replace(" ",'',$comments);
		$compacted=str_replace("\r",'',$compacted);
		$compacted=str_replace("\n",'',$compacted);
		$compacted=strtolower($compacted);
	  	if($comments!=strip_tags($comments) or strpos($compacted,'http')!==false or strpos($compacted,'.com')!==false or strpos($compacted,'.net')!==false or strpos($compacted,'.biz')!==false or strpos($compacted,'.co')!==false or strpos($compacted,'.org')!==false)
	  	{	  		
		  	$this->LogError('You may not post HTML in your inquiry');
		  	return false;
		}
	  	if(strpos($compacted,'viagra')!==false or strpos($compacted,'cialis')!==false)
	  	{	  		
	  		$this->LogError('Spam Inquiries Not Allowed');
		  	return false;
		}
		return true;
	}
	

};
?>