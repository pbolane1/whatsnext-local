<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


class cookie
{
	static function Get($key)
	{
		global $_COOKIE;
		return $_COOKIE[$key];
	}

	static function Set($key,$value='',$days='',$path='',$secure='')
	{
		global $_COOKIE;

		if($days)		$exp=time()+(24*3600*$days);
		else			$exp='';

//		if(headers_sent())
//			return false;

		//set the cookie in the global for use immediately!
		$_COOKIE[$key]=$value;

		//set the cookie via set cookie call for use in next page load
		return setCookie($key,$value,$exp,$path,'',$secure);
	}
	
	static function Dump()
	{
		global $_COOKIE;
		var_dump($_COOKIE);
	}
};

?>