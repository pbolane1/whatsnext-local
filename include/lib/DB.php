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
//
//database interface class (namespace)
//bear-bones now.  MySQL only.
//Extend or replace class for multi DB support.
//

$_database_error_funct='';
$_database_error_class='';

class database
{
	function connect($server,$user,$pass,$db)
	{
		//mysql
		mysql_connect($server, $user, $pass) or die(mysql_error());
        mysql_select_db($db) or die("Could not select database (".$db.")");



		//store for other usage!
		global $__database_server,$__database_user,$__database_pass,$__database_db;
		$__database_server=$server;
		$__database_user=$user;
		$__database_pass=$pass;
		$__database_db=$db;

	}
	
	function fetch_array($res)
	{
		//mysql
		if($res)
			return mysql_fetch_array($res);
		$empty=array();
		return $empty;
 	}

	function fetch_row($res)
	{
		//mysql
		if($res)
			return mysql_fetch_row($res);
		$empty=array();
		return $empty;
 	}


	function num_rows($res)
	{
		//mysql
		if($res)
			return @mysql_num_rows($res);
		return 0;
 	}

	function query($sql)
	{	  
		//mysql
		$res=mysql_query($sql);
		if(!$res)
			database::OnError($sql);
		else
		    return $res;
 	}
 	
 	function free_result($rs)
 	{
		//mysql
		return mysql_free_result($rs);
	}

	function insert_id()
	{
		//mysql
		return mysql_insert_id();
	}

	function OnError($sql)
	{
	 	global $_database_error_funct,$_database_error_class;
		if($_database_error_funct)
		{
			if($_database_error_class)
				call_user_func(array($_database_error_class, $_database_error_funct),$sql);
			else
				call_user_func($_database_error_funct,$sql);
		
		}
		else
			die($sql."<br>".mysql_error());
	  
	}

	function SetOnError($onerr,$onerrclass)
	{
	 	global $_database_error_funct,$_database_error_class;
		$_database_error_funct=$onerr;  
		$_database_error_class=$onerrclass;  		
	}


	function backup($filename)
	{
//		global $__database_server,$__database_user,$__database_pass,$__database_db;
//		system("mysqldump --opt -h ".$__database_server." -u ".$__database_user." -p ".$__database_pass." ".$__database_db." > ".$backupFile);	  


		$tables=array();
		array_pop($tables);
		$query1 = database::query("SHOW TABLES");
		while ($result1 = database::fetch_row($query1))
			list(,$tables[]) = each($result1);
		database::free_result($query1);
		
		/* Store the "Create Tables" SQL in variable $CreateTable[$tblval] */
		foreach ($tables as $tblval) 
		{
			$query2 = database::query("SHOW CREATE TABLE $tblval");
			while ($result2 = database::fetch_array($query2)) {
				$CreateTable[$tblval] = $result2;
			}
		}
		database::free_result($query2);
		
		/* Store all the FIELD TYPES being backed-up (text fields need to be delimited) in variable $FieldType*/
		foreach ($tables as $tblval) 
		{
			$query3 = database::query("SHOW FIELDS FROM $tblval");
			while ($result3 = database::fetch_row($query3)) {
				$FieldType[$tblval][$result3[0]] = preg_replace("/[(0-9)]/",'', $result3[1]);
			}
			database::free_result($query3);
		}

		
		//so sort it all out from information so far
		foreach ($tables as $tblval) 
		{
			$OutBuffer .= "\r\nDROP TABLE IF EXISTS ".$tblval.";\r\n" . $CreateTable[$tblval][1] . ";\r\n";
			$query4 = database::query("SELECT * FROM ".$tblval);
			while ($result4 = database::fetch_array($query4)) 
			{
				$InsertDump = "INSERT INTO $tblval VALUES (";
				while (list($key, $value) = each ($result4)) 
				{
				  	if(!is_numeric($key))
				  	{
						if (preg_match ("/\b" . $FieldType[$tblval][$key] . "\b/i", "TIMESTAMP DATE TIME DATETIME CHAR VARCHAR TEXT TINYTEXT MEDIUMTEXT LONGTEXT BLOB TINYBLOB MEDIUMBLOB LONGBLOB ENUM SET"))
							$InsertDump .= "'" . addslashes($value) . "',";
					 	else
							$InsertDump .= "$value,";
					}
				}
				$OutBuffer .= rtrim($InsertDump,',') . ");\r\n";
			}
			database::free_result($query4);
		}
		
		$fp = fopen($filename, "w");
		if ($fp)
		{
			fwrite($fp, $OutBuffer);
			fclose($fp);
		}
		else
			return false;
		return true;
	}

	function restore($filename)
	{
		global $__database_server,$__database_user,$__database_pass,$__database_db;
	  	//drop the database
		//system("mysqladmin -u ".$__database_user." -p ".$__database_pass." drop".$__database_db);
		//Recreate the database
		//system("mysqladmin -u ".$__database_user." -p  ".$__database_pass." create ".$__database_db);
		//Import the backup data
		//system("mysql -u ".$__database_user." -p  ".$__database_pass." ".$__database_db." < ".$filename);	  
		
		
		$fp=fopen($filename,"r");
		if ((!$fp))
			die("Could not open".$filename);
		if (filesize($filename)==0)
			die($filename.' empty');

		//get the content...
		$content=fread($fp,filesize($filename));
		fclose($fp);

		//break up by liines
		$decodedIn=explode(chr(10),$content);
		$decodedOut="";
		$queries=0;

		//run through the lines
		foreach ($decodedIn as $rawdata)
		{
			$rawdata=trim($rawdata);
			if (($rawdata!="") && ($rawdata{0}!="#"))
			{
				$decodedOut .= $rawdata;
				if (substr($rawdata,-1)==";")
				{
					if  ((substr($rawdata,-2)==");") || (strtoupper(substr($decodedOut,0,6))!="INSERT"))
					{
						if (eregi('^(DROP|CREATE)[[:space:]]+(IF EXISTS[[:space:]]+)?(DATABASE)[[:space:]]+(.+)', $decodedOut))
							return "ERROR. Foriegn statements in this file.";
						$query = database::query($decodedOut);
						
						$decodedOut="";
						$queries++;
					}
				}
			}
		}
		//success!
	    return true;
		
	}


};
?>