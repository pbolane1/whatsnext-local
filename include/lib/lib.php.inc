<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

	require_once('php.php');
	require_once('database.php');
	require_once('html.php');
	require_once('form.php');
	require_once('dbtableinterface.php');
	require_once('dbform.php');
	require_once('dbrow.php');	
	require_once('dbrowset.php');		
	require_once('navigation.php');
	
	//OSCOMMERCE COMPATILIBILITY - check that oscommerce is not around....
	if(!function_exists('tep_href_link'))
	{
		require_once('mime.php');
		require_once('mime_email.php');
		require_once('email.php');
	}


	require_once('file.php');
	require_once('image_magick.php');
	require_once('imaging.php');
	require_once('math.php');
	require_once('date.php');
	require_once('text.php');
	require_once('session.php');	
	require_once('cookie.php');		

	require_once('browser.php');		

	require_once("logger.php");

///////////////////////////////////////////
//	used less often; force include by hand	
//
//	require_once('xml_parser.php');
//	require_once('base_calendar.php');
//	require_once('base_googlemap.php');	
?>