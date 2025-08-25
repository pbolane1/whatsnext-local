<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


$_session_is_started=false;
class session
{
	static function Get($key)
	{
		global $_SESSION;
		return $_SESSION[$key];
	}

	static function SessionIsSet($key)
	{
		global $_SESSION;
		return isset($_SESSION[$key]);
	}

	static function Set($key,$value)
	{
		global $_SESSION;
		$_SESSION[$key]=$value;
	}

	static function SessionUnSet($key)
	{
		global $_SESSION;
		unset($_SESSION[$key]);
	}

	static function Start()
	{
	  	global $_session_is_started;
	  	if(!$_session_is_started)
		  	session_start();
	  	$_session_is_started=true;
	}
	
	static function Dump()
	{
		global $_SESSION;
		var_dump($_SESSION);
	}
	
	static function Close()
	{
		session_write_close()	  ;
	}
	

	static function GetID()
	{
		return session_id();  
	}

	static function SetID($id)
	{
		return session_id($id);  
	}
	
	static function GetIDName()
	{
		return(ini_get('session.name'));
	}

	static function SetIDName($name)
	{
	 	session_name($name) ;
	}	
};

?>