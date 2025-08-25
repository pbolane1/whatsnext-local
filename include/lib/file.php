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
//math namespace
//
$_file_upload_error='';

class file
{
    static function Upload($file,$path,$allow_types='',$maxsize_ks='',$overwrite=false,$new_name='')
	{
		global $_file_upload_error;
		$_file_upload_error='';
		if(!is_array($file))
        {
			$_file_upload_error="Improper Function Usage - First Parameter Should Be Files['your upload']";
			return false;
  		}
		$filename=$file['name'];
		$uploadname=$file['tmp_name'];


		if(!file_exists($path))
		{
			mkdir($path);
			chmod($path,0777);
			if(!file_exists($path) and !is_dir($path))
			{
				$_file_upload_error="Directory Does Not Exist";
				return false;
			}  
		}

		$sys_max=file::ParseSystemLimit('upload_max_filesize');
		//try to set the limit...
		if($maxsize_ks>$sys_max)
		{
			ini_set('upload_max_filesize',$maxsize_ks*1024);
			ini_set('post_max_size',($maxsize_ks+16)*1024);
			ini_set('memory_limit',($maxsize_ks+32)*1024);
		}
		$check_limits=array(file::ParseSystemLimit('upload_max_filesize'),file::ParseSystemLimit('memory_limit')-32,file::ParseSystemLimit('post_max_size')-16,$maxsize_ks);
		$final_check=array();
		foreach($check_limits as $l)
		{
			if($l>0)
			    $final_check[]=$l;
  		}
		//determine actual limit
		$maxsize_ks=min($final_check);

		if(!$new_name)
		    $new_name=$filename;
		if(is_array($allow_types) and count($allow_types)>0)
		{	
		  	foreach($allow_types as $k=>$v)
		  		$allow_types[$k]=strtolower($v);
			$type=strtolower(file::GetExtension($new_name));
			if(!in_array($type,$allow_types))
			{
			    $_file_upload_error="File Type Not Allowed \"".$type."\"";
			    return false;
			}
		}
		
		if(strlen($new_name)<=0)
			$_file_upload_error="No File Name Provided";
		else if(file::FindIllegalCharacters($new_name))
			$_file_upload_error="Illegal Characters in File Name \"".$new_name."\"";
		else if(($maxsize_ks and $file['size']>($maxsize_ks*1024)) or $file['size']==0) //0 byte usually means too large.
			$_file_upload_error="File Greater Than Maximum Size: ".$maxsize_ks."KB";
		else if($file['size']==0)
			$_file_upload_error="Cannot Upload 0 Byte File";
		else if (!$overwrite and file_exists($path.$new_name))
			$_file_upload_error="File Already Exists \"".$path.$new_name."\"";
		else if(!move_uploaded_file($uploadname, $path.$new_name))
		{
			$_file_upload_error="Failed To Upload File \"".$filename."\" to Path \"".$path."\".";
			$_file_upload_error.=" ".file::ParseError($file['error']);
		}
		else if(!imaging::CheckColorProfile($path.$new_name))
		{				
			$_file_upload_error="Images must have RGB Color Profile";
			unlink($path.$new_name); //already uploaded so kill it.
		}

		return ($_file_upload_error=='');
	}

	static function ParseError($code)
	{
		switch($code)
		{
			case UPLOAD_ERR_OK:
				return "No Error Reported.";
			case UPLOAD_ERR_INI_SIZE:
				return "File Exceeds The Server's Size Limit.";
			case UPLOAD_ERR_FORM_SIZE:
				return "File Exceeds The Form's Size Limit.";
			case UPLOAD_ERR_PARTIAL:
				return "File Was Only Partially Uploaded.";
			case UPLOAD_ERR_NO_FILE:
				return "No File Was Uploaded.";
			case UPLOAD_ERR_NO_TMP_DIR:
				return "There Is A Problem With The Temporary Directory.";
			default:
			    return "Unknown Error.";
		}
 	}

	static function GetError()
	{
		global $_file_upload_error;
		return $_file_upload_error;
 	}

	static function GetIllegalCharacters()
	{
		return array('!','@','#','$','%','^','&','*','(',')','+','=','{','}','[',']','\\','|','/','?','>','<',',',';',':','\'','`','~','"',' ');
	}

	static function RemoveIllegalCharacters($file_name)
	{
//		$file_name_new=text::ReplaceAllOfWith($file_name,file::GetIllegalCharacters(),'-');
//		if(strlen($file_name_new)==0)
//			return $file_name;

		$file_name_new=mod_rewrite::ToURL($file_name,'A-Z,a-z,0-9,.-.,---,_-_','-');
		return $file_name_new;	  
	}
	
	static function FindIllegalCharacters($str,$illegal='')
	{
		if(!$illegal)
			$illegal=file::GetIllegalCharacters();
		foreach($illegal as $character)
        {
			if(strpos($str,$character))
				return true;
		}
		return false;
 	}

   	static function GetURLContents($url)
	{
		$file=fopen($url,"r");
		if($file)
		{
			clearstatcache();
			//get the file contents
			$contents = "";
			do
			{
				$data = fread($file, 1024);
				if (strlen($data) == 0)
				{
					fclose($file);
					return($contents);
				}
				$contents .= $data;
			}
			while(strlen($data));
			return($contents);
		}
		fclose($file);
		return false;
	}

	static function GetFilesInDirectory($dir,$subdirs=false,$types=array(),$cur_list='')
	{
		//iit the list if none
		if(!is_array($cur_list))
		    $cur_list=array();
		if(!is_array($types))
		    $types=explode(',',$types);
		//sanity check
		if (is_dir($dir))
		{
			//open&read dir,each file
	    	if ($dh = opendir($dir))
			{
		        while (($file = readdir($dh)) !== false)
				{
				    //parse sub dirs or add the file
					if($file!='.' and $file!='..' and is_dir($dir . $file) and $subdirs)
					{
						if($file[strlen($file)-1]!='/' and $file[strlen($file)-1]!='\\' )
							$file.='/';

	            		$cur_list+=file::GetFilesInDirectory($dir.$file,$subdirs,$types,$cur_list);
					}
					else if(is_file($dir.$file))
					{
						//type mask or not?
						if(!$types or !is_array($types) or count($types)==0 or in_array(file::GetExtension($file),$types))
							$cur_list[]=$dir.$file;
					}
		        }
     		    closedir($dh);
		    }
		}
		else
		    echo("<br><b>Warning:</b>GetFilesInDirectory() Supplied Bad Directory[".$dir."]<br>");
		return $cur_list;
	}

	static function ShowImagesInDirectory($dir,$subdirs=true,$types='')	
	{
	  	if(!$types)	$types=array('jpg','gif','png');
	  	if(!is_array($types))	$tyes=implode(',',$types);
		$files=file::GetFilesInDirectory($dir,$subdirs,$types);
		foreach($files as $image)
		{
		  	$path=str_replace(_navigation::GetBasePath(),_navigation::GetBaseURL(),$image);
			echo("<div style='float:left;padding:10px;border:1px solid #000000;margin:10px;'>");
			echo("<img src='".$path."'>");
			echo("<br><a href='".$path."'>".$path."</a>");
			echo("</div>");
		}
	}

	static function ParseSystemLimit($limit_name)//KB
	{
		$limit=ini_get($limit_name);

		if(strpos($limit,'M')!==false)
			$limit=intval($limit)*1024;
		else if(strpos($limit,'G')!==false)
			$limit=intval($limit)*1024*1024;
		else if(strpos($limit,'K')!==false)
			$limit=intval($limit);
		else
			$limit=$limit*1024;
		return $limit;
 	}

	static function SetPath($path,$which='image_upload')
	{
		//psuedo static
		global $_upload_path;
		$_upload_path[$which]=$path;
 	}

	static function GetPath($which='image_upload')
	{
		//psuedo static
		global $_upload_path;
		return $_upload_path[$which];
 	}

	static function GetExtension($filename)
	{
		$filename=explode('.',$filename);
		return strtolower($filename[count($filename)-1]);
	
		$type='';
		$pos=strpos($filename,'.');
		//get the last one (urls for example, etc...)
		while ($pos!==false)
		{
			$sv=$pos;
			$pos=strpos($filename,'.',$pos+1);  
		}				
		if($sv)
			$pos=$sv;
		
		if($pos!==false and $pos+1<strlen($filename))
			$type=strtolower(substr($filename,$pos+1));

		return $type;
	}

	static function ReplaceExtension($filename,$newext)
	{
		$ext=file::GetExtension($filename);
		if($ext)
			$filename=str_replace('.'.$ext,'.'.$newext,$filename);
		return $filename;
	}

	static function ImageExists($path,$filename)
	{
		$imgexts=array('gif','jpg','jpeg','png');
		foreach($imgexts as $ext)
		{
		  	$filecheck=file::ReplaceExtension($filename,$ext);
			if(file_exists($path.$filecheck))		  
			{
					return $filecheck;
			  
			}
		}
	  	return false;
	}

	static function IsImage($filename)
	{
  		return(in_array(file::GetExtension($filename),array('jpg','jpeg','tif','tiff','gif','bmp','JPG','JPEG','TIF','TIFF','GIF','BPM')));
	}
	
	static function ReadURL($url)
	{
		//uses CURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$store = curl_exec ($ch);
		$data = curl_exec ($ch);
		curl_close ($ch);
		return $data;  
	}
	
	static function FileNameNoCache($path,$file)
	{
		//get full path (recognized by sile system), removing any superfluous path in the file, and query string
		$fullfilepath=$path.basename(strpos($file,'?')?substr($file,0,strpos($file,'?')):$file);

		//if does not exist in file system, give back original path
		if(!file_exists($fullfilepath))
			return $file;

		//otherwise, we can check the timestamp, 
		//and return this as a modifier, 
		//so browser will only use cached if not modified since last fetch.
		$time=filemtime($fullfilepath);  

		//return original filename with the path and querystring, appending the modifier.
		return strpos($file,'?')?($file.'&modtime='.$time):($file.'?modtime='.$time);

		//TO DO: add mod_rewrite option(s), support path variations for this.	  
	}
};

if(!function_exists("rmdirr"))
{
	function rmdirr($dirname)
	{
		// Sanity check
	    if (!file_exists($dirname)) {
	        return false;
	    }
	
	    // Simple delete for a file
	    if (is_file($dirname)) {
	        return unlink($dirname);
	    }
	
	    // Loop through the folder
	    $dir = dir($dirname);
	    while (false !== $entry = $dir->read())
		{
	        // Skip pointers
	        if ($entry == '.' || $entry == '..')
	            continue;
	        // Recurse
	        rmdirr("$dirname/$entry");
	    }
	
	    // Clean up
	    $dir->close();
	    return rmdir($dirname);
	}
}
?>