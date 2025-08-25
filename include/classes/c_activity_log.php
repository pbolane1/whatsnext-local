<?php

class activity_log extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('activity_log','activity_log_id');
		$this->Retrieve();
	}
	
	function ToURL()
	{
	 	return $this->GetCurrentUser()->DirectURL('activity_log.php?user_id='.$this->Get('user_id')).'#'.$this->GetFieldName('anchor');
	}
	
	static function Log($object,$action,$details,$user_id='')
	{
		//don't log anythign google does.
		if(activity_log::IsGoogle())
			return false;

	 	$today=new date();

		if(DBRowEx::GetCurrentUser())
			$object=activity_log::GetCurrentUser();
			
		if(get_class($object)=='agent' and $object->IsProxyLogin())	
			$object=new coordinator(Session::Get('pbt_agent_proxy_login'));
	
		html::HoldOutput();
		debug_print_backtrace();
		$backtrace="http://".$_SERVER['SERVER_NAME'].''.$_SERVER['REQUEST_URI']."\r\n";
		$backtrace.=html::GetHeldOutput();
		html::ResumeOutput();
		


		$activity_log=new activity_log();
		$activity_log->Set('activity_log_action',$action);
		$activity_log->Set('activity_log_name',$object->GetFullName());
		$activity_log->Set('activity_log_details',$details);
		$activity_log->Set('activity_log_timestamp',time());
		$activity_log->Set('activity_log_date',$today->GetDBDate());
		$activity_log->Set('activity_log_ip',$_SERVER['REMOTE_ADDR']);
		$activity_log->Set('activity_log_debug',$backtrace);

		$activity_log->Set('foreign_class',get_class($object));
		$activity_log->Set('foreign_id',$object->id);
		$activity_log->Set('user_id',$object->Get('user_id')?$object->Get('user_id'):$user_id);
		$activity_log->Set('session_id',Session::GetID());
		$activity_log->Update();
		
		return $activity_log->id;
 	}

	public static function IsGoogle() 
	{
	    if (!isset($_SERVER['HTTP_USER_AGENT']))
	        return false;
	
	    $userAgent = $_SERVER['HTTP_USER_AGENT'];
	
	    // List of common Googlebot user agents
	    $googleBots = [
			'AdsBot-Google',		 
	        'Googlebot', 
	        'GoogleImageProxy',
	        'Googlebot-Image', 
			'Googlebot-News', 
			'Googlebot-Video',
			'Mediapartners-Google'
	    ];
	
	    foreach ($googleBots as $bot) 
		{
	        if (stripos($userAgent, $bot) !== false) 
	            return true;
	    }
	
	    return false;
	}

};

?>