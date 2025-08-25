<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

class Timer
{
	var $stime;
	var $etime;
	
	static function Timer()
	{
		$this->etime=array()  ;
	}
	
	static function get_microtime()
	{
		$tmp=split(" ",microtime());
		$rt=$tmp[0]+$tmp[1];
		return $rt;
	}
	
	static function start()
	{
		$this->stime = $this->get_microtime();
	}
	
	static function getTime($index=0)
	{
	  	if(!$index)
	  		$index=count($this->etime);
		$this->etime[$index] = $this->get_microtime();
	}
	
	static function elapsed_time($index=0)
	{
		return ($this->etime[$index] - $this->stime);
	}
	
	static function Dump($show=false)
	{
	  	
		foreach($this->etime as $index=>$time)	  
			echo(($show?"":"<!--").$index.':'.$this->elapsed_time($index).($show?"<br>":"-->\n"));
	}
}
?>