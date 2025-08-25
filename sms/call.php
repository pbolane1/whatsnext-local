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

	$response = new Twilio\TwiML\VoiceResponse;
	$response->say("This voicemail is not monitored and you should contact your agent directly or use the contact page on whatsnext.realestate", array('voice' => 'alice'));
//	$response->play(_navigation::GetBaseURL().'audio/message.wav');
	print $response;

	email::SetEmail('paul@pocotechnology.com');
	mail(email::GetEmail(),'Incoming Phone Call From '.$HTTP_POST_VARS['Caller'],implode("\r\n",$vars),'FROM:'.email::GetEmail());
?>
