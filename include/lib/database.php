<?php
//
//
//database interface class (namespace)
//bear-bones now.  MySQL only.
//Extend or replace class for multi DB support.
//

$_database_error_funct='';
$_database_error_class='';
$_database_query_funct='';
$_database_query_class='';
$_database_query_cnt=0;
$_database_connections=array();
$_database_connection='';//THE Connection.

class database
{
	static function connect($server,$user,$pass,$db,$critical=true,$name='')
	{
		//mysql
		global $_database_connection;
		$_database_connection=mysqli_connect($server, $user, $pass, $db);
		if(mysqli_connect_errno())
		{
			if($critical) 	die(mysqli_connect_error());
			return false;
		}


		//store for other usage!
		global $__database_server,$__database_user,$__database_pass,$__database_db;
		$__database_server=$server;
		$__database_user=$user;
		$__database_pass=$pass;
		$__database_db=$db;

		if($name)
			database::StoreConnection($server,$user,$pass,$db,$name);

		return true;
	}

	static function Disconnect()
	{
		mysqli_close();
	}	

	static function Reconnect($name,$critical=true)
	{
		global $_database_connections;
		if(!$_database_connections[$name])
			return false;
		else
			return database::connect($_database_connections[$name]['server'],$_database_connections[$name]['user'],$_database_connections[$name]['pass'],$_database_connections[$name]['db'],$critical);			  
	}
	
	static function StoreConnection($server,$user,$pass,$db,$name)
	{
		global $_database_connections;
		$_database_connections[$name]=array('server'=>$server,'user'=>$user,'pass'=>$pass,'db'=>$db);	  
	}
	
	static function GetQueryCount()
	{
	  	global $_database_query_cnt;
	  	return $_database_query_cnt;	  
	}
	
	static function fetch_array($res)
	{
		//mysql
		if($res)
			return mysqli_fetch_array($res);
		$empty=array();
		return $empty;
 	}

	static function fetch_row($res)
	{
		//mysql
		if($res)
			return mysqli_fetch_row($res);
		$empty=array();
		return $empty;
 	}


	static function num_rows($res)
	{	  
		//mysql
		if($res)
			return @mysqli_num_rows($res);
		return 0;
 	}

	static function query($sql)
	{	  
	  	global $_database_query_cnt;
	  	$_database_query_cnt++;

		global $_database_connection;		
		
		database::OnQuery($sql);

		//mysql
		$res=mysqli_query($_database_connection,$sql);
		if(!$res)
			database::OnError($sql);
		else
		    return $res;
 	}
 	
 	static function free_result($rs)
 	{
		//mysql
		return mysqli_free_result($rs);
	}

	static function insert_id()
	{
		global $_database_connection;
		//mysql
		return mysqli_insert_id($_database_connection);
	}

	static function OnError($sql='')
	{
	 	global $_database_error_funct,$_database_error_class;
		global $_database_connection;
		if($_database_error_funct)
		{
			if($_database_error_class)
				call_user_func(array($_database_error_class, $_database_error_funct),$sql);
			else
				call_user_func($_database_error_funct,$sql);
		
		}
		else
			die($sql."<br>".mysqli_error($_database_connection));
	  
	}

	static function SetOnError($onerr=false,$onerrclass=false)
	{
	 	global $_database_error_funct,$_database_error_class;
		$_database_error_funct=$onerr;  
		$_database_error_class=$onerrclass;  		
	}

	static function OnQuery($sql)
	{
	 	global $_database_query_funct,$_database_query_class;
		if($_database_query_funct)
		{
			if($_database_query_class)
				call_user_func(array($_database_query_class, $_database_query_funct),$sql);
			else
				call_user_func($_database_query_funct,$sql);
		
		}	  
	}

	static function SetOnQuery($onq,$onerrclass)
	{
	 	global $_database_query_funct,$_database_query_class;
		$_database_query_funct=$onq;  
		$_database_query_class=$onqclass;  		
	}


	static function backup($filename)
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
		$fp = fopen($filename, "w");
		if (!$fp)
			return false;
			
		foreach ($tables as $tblval) 
		{
			fwrite($fp, "\r\nDROP TABLE IF EXISTS ".$tblval.";\r\n" . $CreateTable[$tblval][1] . ";\r\n");
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
				fwrite($fp, rtrim($InsertDump,',') . ");\r\n");
			}
			database::free_result($query4);
		}		
		fclose($fp);
		return true;
	}

	static function restore($filename)
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
			if (($rawdata!="") && ($rawdata[0]!="#"))
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