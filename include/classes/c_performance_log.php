<?php
//**************************************************************//
//	
//	FILE: c_performance_log.php
//  CLASS: performance_log
//  
//	STUBBED BY: hand
//  PURPOSE: abstraction for performance_log database table, performance_log tracking & reporting functions
//  STUBBED TIMESTAMP: n/a
//
//**************************************************************//

class performance_log extends DBRowEx
{
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(false);
		$this->EstablishTable('performance_log','performance_log_id');
		//$this->Retrieve();

	 	global $HTTP_SERVER_VARS,$HTTP_GET_VARS;

		$this->data=array();		
		$this->start=$this->Microtime();
		$this->MarkThis('START');			
		$this->Set('performance_log_time',time());
		$this->Set('performance_log_datetime',date('Y-m-d H:i:s'));
		$this->Set('performance_log_page',"http://".$HTTP_SERVER_VARS['SERVER_NAME'].''.$HTTP_SERVER_VARS['REQUEST_URI']);		
		$this->Set('performance_log_domain',$HTTP_SERVER_VARS['SERVER_NAME']);		
		$this->Set('performance_log_file',$HTTP_SERVER_VARS['SCRIPT_NAME']);		
		$this->Set('performance_log_ip',$HTTP_SERVER_VARS['REMOTE_ADDR']);		
		$this->Set('window_id',$HTTP_GET_VARS['window_id']);	
	}
	
	static function First()
	{
		global $performance_log;		
		if($performance_log)
			$performance_log->Update();
	}

	public function EstablishTable($table,$primarykey)
	{
		$this->tableName=$table;
		$this->primary=$primarykey;

		//dont' call show fields.
 	}

	public function Microtime()
	{
		$tmp=explode(" ",microtime());
		$rt=$tmp[0]+$tmp[1];
		return $rt;
	}
		
	public function ElapsedTime()
	{
		return $this->Microtime()-$this->start;	
	}		

	static function Mark($milestone)
	{
		global $performance_log;		
		if($performance_log)
			$performance_log->MarkThis($milestone);
	}


	public function MarkThis($milestone)
	{
	 	$precision=10000;
	 
		$info=array();
		$info[]=sprintf('%-16s',$milestone);
		$info[]="Q:".sprintf('%-6s',database::GetQueryCount());
		$info[]="T:".sprintf('%-10s',round($this->ElapsedTime()*$precision)/$precision);
		$info[]="M:".sprintf('%-10s',$this->GetMemoryUsed());
		$this->data[]=implode(' - ',$info);
	}
	
	public function GetEnvironment()
	{
		global $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_SESSION_VARS, $HTTP_SERVER_VARS;
		$extra=array();
		$track=array('HTTP_POST_VARS'=>$HTTP_POST_VARS,'HTTP_GET_VARS'=>$HTTP_GET_VARS);//,'HTTP_SESSION_VARS'=>$HTTP_SESSION_VARS);//,'HTTP_SERVER_VARS'=>$HTTP_SERVER_VARS);
		foreach ($track as $name=>$arr)
		{
			foreach($arr as $k=>$v)
				$extra[]=$name.'['.$k.']='.$v;	
		}
		
		$extra[]='HTTP_SERVER_VARS['.'HTTP_REFERER'.']='.$HTTP_SERVER_VARS['HTTP_REFERER'];	
		$extra[]='HTTP_SERVER_VARS['.'HTTP_USER_AGENT'.']='.$HTTP_SERVER_VARS['HTTP_USER_AGENT'];	
		$extra[]='HTTP_SERVER_VARS['.'REMOTE_HOST'.']='.$HTTP_SERVER_VARS['REMOTE_HOST'];	
		return implode("\r\n",$extra);
		
	}
	
	public function GetMemoryUsed()
	{
		$pid = getmypid(); 
		exec('ps --pid '.$pid.' --no-headers -orss  2>&1',$result);
		return $result[0];
	}
	
	public function Commit()	
	{	 
//	 	if(!Session::Get('TRACK_PERFORMANCE'))
//	 		return;
	 
	 	$this->MarkThis('DONE');

		$this->Set('performance_log_duration',$this->ElapsedTime());
		$this->Set('performance_log_queries',database::GetQueryCount());
		$this->Set('performance_log_memory',$this->GetMemoryUsed());
		$this->Set('performance_log_details',implode("\r\n",$this->data));
		$this->Set('performance_log_environment',$this->GetEnvironment());
		$this->Set('performance_log_time_end',time());
		$this->Set('performance_log_datetime_end',date('Y-m-d H:i:s'));		
		$this->Set('process_id',getmypid());
		$this->Set('session_id',Session::GetID());

		$this->Update();
	}
};

?>