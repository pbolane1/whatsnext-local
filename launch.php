- update version in common<br>
- archive live to arvhie/vN.N<br>
    - skip dynamic<br>
    - skip uploads<br>
- copy dev.whatsnext.realestate to whatsnext.realestate<br>
    - skip dynamic<br>
    - skip uploads<br>



<?php

	die();


	include('include/common.php');

	if(!$__DEV__)
		die('DEV ONLY');

	$old_version="0.0";
	$new_version=$__VERSION__;
	
	$dev_path="/home/pbolane1/public_html/dev.whatsnext.realestate/";
	$live_path="/home/pbolane1/public_html/whatsnext.realestate/";
	$archive_path="/home/pbolane1/public_html/archive.whatsnext.realestate/";

	$test_path="/home/pbolane1/public_html/test.whatsnext.realestate/";
	$live_path="/home/pbolane1/public_html/dev.whatsnext.realestate/";


	if(is_dir($archive_path.$old_version) and !$HTTP_GET_VARS['version_check'])
		die("<a href='?version_check=1'>Check your versions ".$old_version. " exists</a>");

	$commands=array();
	$commands[]="whoami";
	$commands[]="pwd";
	$commands[]="mkdir -p ".$archive_path.$old_version."";
	
	$ignore=array();
	$ignore[]=".";
	$ignore[]="..";
	$ignore[]="1";
	$ignore[]="acme-challenge";
	$ignore[]="dynamic";
	$ignore[]="error_log";
	$ignore[]="uploads";
	$directories = scandir($dev_path);
	foreach($directories as $directory)
	{
		if(!in_array($directory,$ignore))
		{
			$commands[]="ls -la ".$live_path.$old_version."/".$directory;

			if(strpos($directory,'.')!==false)
				$commands[]="rsync -av ".$live_path.$old_version."/".$directory.' '.$archive_path.$old_version."/";
			else
				$commands[]="rsync -avr ".$live_path.$old_version."/".$directory."/".' '.$archive_path.$old_version."/";
		}
	}

	$commands[]="ls -lt ".$archive_path.$old_version."";
	
	foreach($commands as $cmd)
	{
		echo($cmd."<br>");
		$status='';
		$output=array();
		exec($cmd." 2>&1", $output, $status);
		echo(' '.$status." :: ".implode("<br>", $output)."<br>");
	}
	echo("<b>BACKED UP</b><br>");
//	$my_path=
	
	
?>