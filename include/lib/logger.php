<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

//default...
file::SetPath(str_replace('public_html','',_navigation::GetBasePath()).'__log/','LOG');

//class...
class logger
{
	static function Log($dirs,$filename,$data)
	{
	 	if(!is_array($dirs))
	 		$dirs=array($dirs);

	 	if(!is_array($data))
	 		$data=array($data);

		$path=file::GetPath('LOG');
		if(!is_dir($path))
			mkdir($path,0755);
		foreach($dirs as $dir)
		{
			$path.=$dir.'/';
			if(!is_dir($path))
				mkdir($path,0755);
		}

		$f=fopen($path.$filename,'a');
		foreach($data as $line)
			fwrite($f,date('Y-m-d H:i:s').' '.$line."\r\n");
		fclose($f);
	}


	static function Purge($dirs,$days=30)
	{
	 	if(!is_array($dirs))
	 		$dirs=array($dirs);
		$path=file::GetPath('LOG').implode('/',$dir);
		if(!is_dir($path))
			mkdir($path,0755);

		$files=file::GetFilesInDirectory($path); 

		//remove old..		
		$vstime=mktime()-60*60*24*$days;
		foreach($files as $file) 
		{		  			  
		  	$timestamp=filemtime($file);

			if($timestamp<$vstime)
				unlink($file);
		}			
	}
};

?>