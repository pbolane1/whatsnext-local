<?php 
	require('../include/common.php');
	
	$vars=array();
	foreach($HTTP_POST_VARS as $k=>$v)
		$vars[]='HTTP_POST_VARS['.$k.']='.$v;
	foreach($HTTP_GET_VARS as $k=>$v)
		$vars[]='HTTP_GET_VARS['.$k.']='.$v;
//	$notification=new notification();
//	$notification->Set('notification_email_content',implode("\r\n",$vars));
//	$notification->Update();


	$body=$HTTP_POST_VARS['Body'];	
	$action=$HTTP_POST_VARS['Body'];
	$action=trim($action);	
	$action=preg_replace('/\s+$/u', '', $action);
	$action=strtoupper($action);
	$from=user_contact::NormalizePhone($HTTP_POST_VARS['From']);
	$user_contact=new user_contact();
	$user_contact->InitByKeys('user_contact_phone',$from);
	$name=$user_contact->Get('user_firstname').' '.$user_contact->Get('user_lastname');

	$debug=array();
	$debug[]='FROM '.$from;
	$debug[]='NAME '.$name;
	$debug[]='';
	$debug[]='ACTION:'.$action;
	mail('paul@pocotechnology.com','SMS test',implode("\r\n",$debug));

	performance_log::Mark("SMS FROM ".$from." ".$body);


	if(stripos($action,'PAUSE')!==false and strlen($action)<10)
	{
	 	$where=array("user_contact_phone='".$from."'");
	  	$list=new DBRowSetEX('user_contacts','user_contact_id','user_contact',implode(' AND ',$where));
		$list->Retrieve();
		foreach($list->items as $user_contact)	
		{
			$settings=json_decode($user_contact->Get('user_contact_settings'),true);
			$settings['notifications']['phone']=false;
			$user_contact->Set('user_contact_settings',json_encode($settings));
			$user_contact->Update();
			
			activity_log::Log($user_contact,'SMS_DISABLED','Phone notifications disabled for '.$user_contact->Get('user_contact_name').' via SMS');
			performance_log::Mark("SMS disabled for user_contact ".$user_contact->id);
		}

	 	$where=array("agent_cellphone='".$from."'");
	  	$list=new DBRowSetEX('agents','agent_id','agent',implode(' AND ',$where));
		$list->Retrieve();
		foreach($list->items as $agent)	
		{
			$settings=json_decode($agent->Get('agent_settings'),true);
			$settings['notifications']['phone']=false;
			$agent->Set('agent_settings',json_encode($settings));
			$agent->Update();

			activity_log::Log($agent,'SMS_DISABLED','Phone notifications disabled via SMS');
			performance_log::Mark("SMS disabled for agent ".$agent->id);
		}

	 	$where=array("coordinator_cellphone='".$from."'");
	  	$list=new DBRowSetEX('coordinators','coordinator_id','coordinator',implode(' AND ',$where));
		$list->Retrieve();
		foreach($list->items as $coordinator)	
		{
			$settings=json_decode($coordinator->Get('coordinator_settings'),true);
			$settings['notifications']['phone']=false;
			$coordinator->Set('coordinator_settings',json_encode($settings));
			$coordinator->Update();

			activity_log::Log($coordinator,'SMS_DISABLED','Phone notifications disabled via SMS');
			performance_log::Mark("SMS disabled for coordinator ".$coordinator->id);
		}


		$response = new Twilio\TwiML\MessagingResponse();
		$response->message("Text notifications have been disabled for your account.  You can always text RESUME to this number to join back in, or update your account online.");
		print $response;

	}
	else if(stripos($action,'RESUME')!==false and strlen($action)<10)
	{
	 	$where=array("user_contact_phone='".$from."'");
	  	$list=new DBRowSetEX('user_contacts','user_contact_id','user_contact',implode(' AND ',$where));
		$list->Retrieve();
		foreach($list->items as $user_contact)	
		{
			$settings=json_decode($user_contact->Get('user_contact_settings'),true);
			$settings['notifications']['phone']=true;
			$user_contact->Set('user_contact_settings',json_encode($settings));
			$user_contact->Update();

			activity_log::Log($user_contact,'SMS_ENABLED','Phone notifications enabled for '.$user_contact->Get('user_contact_name').' via SMS');
			performance_log::Mark("SMS enabled for user_contact ".$user_contact->id);
		}

	 	$where=array("agent_cellphone='".$from."'");
	  	$list=new DBRowSetEX('agents','agent_id','agent',implode(' AND ',$where));
		$list->Retrieve();
		foreach($list->items as $agent)	
		{
			$settings=json_decode($agent->Get('agent_settings'),true);
			$settings['notifications']['phone']=true;
			$agent->Set('agent_settings',json_encode($settings));
			$agent->Update();

			activity_log::Log($agent,'SMS_ENABLED','Phone notifications enabled via SMS');
			performance_log::Mark("SMS enabled for agent ".$agent->id);

		}
		
	 	$where=array("coordinator_cellphone='".$from."'");
	  	$list=new DBRowSetEX('coordinators','coordinator_id','coordinator',implode(' AND ',$where));
		$list->Retrieve();
		foreach($list->items as $coordinator)	
		{
			$settings=json_decode($coordinator->Get('coordinator_settings'),true);
			$settings['notifications']['phone']=true;
			$coordinator->Set('coordinator_settings',json_encode($settings));
			$coordinator->Update();

			activity_log::Log($coordinator,'SMS_ENABLED','Phone notifications enabled via SMS');
			performance_log::Mark("SMS enabled for coordinator ".$coordinator->id);

		}		

		$response = new Twilio\TwiML\MessagingResponse();
		$response->message("Text notifications have been enabled for your account.  To opt out of text notifications, reply PAUSE to this message.");
		print $response;

	}
	else
	{
		$response = new Twilio\TwiML\MessagingResponse();
		$response->message("This is an automated text message and replies are not received.  Please contact your agent directly with any questions or concerns.");
		print $response;
	}
?>