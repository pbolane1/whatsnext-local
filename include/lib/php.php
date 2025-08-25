<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

//
//form namespace
//

class php
{
	static function info()
	{
		phpinfo();
	}

	static function Dump()
	{
		phpinfo(INFO_VARIABLES);	  
	}

	static function Set($which,$value)
	{
		ini_set($which,$value);		
	}

	static function Get($which)
	{
		return ini_get($which);  
	}

	static function SetMode($mode)
	{
	  	//this may be called more than once....
		switch($mode)
		{
			case 'LARGEFILES':			
				php::Set('max_execution_time',600);
				php::Set('memory_limit','64M');
				php::Set('post_max_size','64M');
				php::Set('upload_max_filesize','64M');
				break;				
			case 'PRODUCTION':
			case 'LIVE':
				error_reporting(E_PARSE | E_ERROR);			
				php::Set('error_reporting',E_PARSE | E_ERROR);
				php::Set('display_errors','off');
				break;	  
			case 'DEVELOPMENT':
			default:
				error_reporting(E_PARSE | E_ERROR);			
				php::Set('error_reporting',E_PARSE | E_ERROR);
				php::Set('display_errors','on');
				break;	  
		}  
	}
	
	static function IncludeAll($dir,$types='',$mode='include')
	{
		if(!$types)					$types=array('php');
		else if(!is_array($types))	$types=explode(',',$types);


		$files=file::GetFilesInDirectory($dir,true,$types);
		foreach($files as $file)
		{	
		  	switch($mode)
		  	{
				case 'include':
					include($file);
					break;		  
				case 'require':
					require($file);
					break;		  
				case 'include_once':
					include_once($file);
					break;		  
				case 'require_once':
					require_once($file);
					break;		  
			}
		}
	}
	
	static function RequireAll($dir,$types='')
	{
		php::IncludeAll($dir,$types,'require');	  
	}

	static function IncludeAllOnce($dir,$types='')
	{
		php::IncludeAll($dir,$types,'include_once');	  
	}

	static function RequireAllOnce($dir,$types='')
	{
		php::IncludeAll($dir,$types,'require_once');	  
	}
}
?>