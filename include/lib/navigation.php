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
//navigation namespace
//

class _navigation
{
	static function Redirect($page,$params='')
	{
		$pass=array();
	  	if(is_array($params))
	  	{
	  	  	$pass=array();
	  		foreach($params as $k=>$v)
			  	$pass[]=$k.'='.$v;
			$params=implode('&',$pass);
	  	}
	  	if($params)
	  	{
		  	if(strpos($page,'?')) 		$page.='&'.$params;
		  	else						$page.='?'.$params;
	  	}

		// if no headers are sent, redirect in http header
		//otherwise, meta redirect.
		if (!headers_sent())
		    header('Location: '.$page);
		else
	 		echo ("<meta http-equiv='refresh' content='0;url=".$page."'/>");
 		die();//stop the page from loading any more
	}

	static function PreservePostVars()
	{
		global $HTTP_POST_VARS;
		Session::Set('REDIR_KEEP_POST',$HTTP_POST_VARS);
	  	Session::Close();
	}

	static function RestorePostVars($globalize=true)
	{
		global $HTTP_POST_VARS;
		if(is_array(Session::Get('REDIR_KEEP_POST')))
		{
			foreach(Session::Get('REDIR_KEEP_POST') as $k=>$v)
			{
				$HTTP_POST_VARS[$k]=$v;	  
				if($globalize)
				{
				  	global $$k;
					$$k=$v;
				}
			}
			Session::SessionUnSet('REDIR_KEEP_POST');
		}
	}


	static function GetFilePath($full_path='')
	{
		return _navigation::GetPath($full_path,false);
 	}

	static function GetServerPath($full_url='')
	{
		return _navigation::GetPath($full_url,true);
 	}


	static function GetPath($full_path='',$is_url=true)
	{
		global $PHP_SELF,$HTTP_HOST;
		if(!$full_path)
		{
            $self=$PHP_SELF;
			while($self[0]=='/' or $self[0]=='\\')
			    $self=substr($self,1);
			if($is_url)
			    $full_path=_navigation::GetBaseURL().$self;
			else
			    $full_path=_navigation::GetBasePath().$self;
		}
		$last=strlen($full_path);
		$res='';
		while($res!==false)
		{
			$last=$res;
			$res=max(array(strpos($full_path,'/',(int)$res+1),strpos($full_path,'\\',(int)$res+1)));
		}
		if($last)
			$path=substr($full_path,0,$last+1);
		return($path);
	}

	static function GetFile($full_path='',$is_url=true)
	{
		global $PHP_SELF,$HTTP_HOST;
		if(!$full_path)
		{
            $self=$PHP_SELF;
			while($self[0]=='/' or $self[0]=='\\')
			    $self=substr($self,1);
			if($is_url)
			    $full_path=_navigation::GetBaseURL().$self;
			else
			    $full_path=_navigation::GetBasePath().$self;
		}
		$last=strlen($full_path);
		$res='';
		while($res!==false)
		{
			$last=$res;
			$res=max(array(strpos($full_path,'/',$res+1),strpos($full_path,'\\',$res+1)));
		}
		if($last)
			$path=substr($full_path,$last+1);
		return($path);
	}

	static function MakeBaseHref($path='')
	{
		echo("<BASE HREF='".($path?$path:_navigation::GetBaseURL())."'/>");
 	}

	static function GetBasePath()
	{
		global $_file_base_path;

		//cached / changable via Set... funtion
		if(!$_file_base_path) 
		{	 	  
		  	//determine
			global $_SERVER;
			$path=$_SERVER['DOCUMENT_ROOT'];
			
			// Docker environment fix - if DOCUMENT_ROOT is empty, use the actual path
			if(empty($path) && file_exists('/.dockerenv')) {
				$path = '/var/www/html/';
			}
			
			if($path[strlen($path)-1]!='/' and $path[strlen($path)-1]!='\\')
			    $path.='/';

			//cache
			$_file_base_path=$path;
		}
		return $_file_base_path;
 	}

 	static function GetBaseURL()
 	{
 	 	global $_file_base_url;
		//cached / changable via Set... funtion
		if(!$_file_base_url) 
		{	 	  
		  
		  	//dtermine
			global $_SERVER;
			$url=$_SERVER['HTTP_HOST'];
			$ssl=$_SERVER['HTTPS'];
			$protocol='http://';
			if($ssl and strtolower($ssl)!='off')
			    $protocol='https://';
			if(strpos($url,$protocol)===false)
			    $url=$protocol.$url;
			if($url[strlen($url)-1]!='/' and $url[strlen($url)-1]!='\\')
			    $url.='/';
			    
			//cache
			$_file_base_url=$url;
		}
		return $_file_base_url;

  	}

	static function SetBasePath($path)
	{
		global $_file_base_path;
		$_file_base_path=$path;  
	}

	static function SetBaseUrl($url)
	{
		global $_file_base_url;
		$_file_base_url=$url;  	  
	}

	static function MakeHref($page,$vars='',$absolute=false)
	{
		$str="";
		//add the domain if it is not an "?asfas=sdfsad" href (local form action)
		if($absolute)
			$str.=_navigation::GetBaseURL();
		//add the page thay passed in
		$str.=$page;

		//process key/value pairs passes in (if passed in)
		$pairs=array();
		if(!is_array($vars))
		    $vars=array();
	 	foreach($vars as $v)
        {
			global $$v;
		  	if(strpos($str,$v,strpos($str,"?"))===false and $$v)
				$pairs[]=$v.'='.$$v;
		}
		return $str.(count($pairs)?((strpos($str,"?")===false)?'?':'&').implode('&',$pairs):'');
	}

	static function MakeLink($title,$url,$params='')
	{
		echo("<a href='".$url."'".html::ProcessParams($params).">".$title."</a>");
 	}


	static function GetDomain($url='')
	{
		if(!$url)
		    $url=_navigation::GetBaseURL();

		$url=str_replace('https:','http:',$url);
		preg_match("/^(http:\/\/)?([^\/]+)/i",$url, $matches);

		return $matches[2];
	}

	static function GetSubDomain($url)
	{
		$url=_navigation::GetDomain($url);

	  	$parts=explode('.',$url);
	  	$subdomain=array();
	  	
	  	
	  	for($i=0;$i<(count($parts)-2);$i++)
		  	$subdomain[]=$parts[$i];
		
		return(implode('.',$subdomain));
	}

	static function IsURLLocal($url,$subdomain_ok=true)
	{
		$url_domain=_navigation::GetDomain($url);
		$local_domain=_navigation::GetDomain(_navigation::GetBaseURL());

		if($subdomain_ok)
		{
			$url_subdomain=_navigation::GetSubDomain($url_domain);
			$local_subdomain=_navigation::GetSubDomain($local_domain);
			
			if($url_subdomain)		$url_domain=str_replace($url_subdomain.'.','',$url_domain);
			if($local_subdomain)	$local_domain=str_replace($local_subdomain.'.','',$local_domain);
		}
		
		return($url_domain==$local_domain);
	}
};


class mod_rewrite extends _navigation
{
	static function ToURL($url,$allow='a-z,A-Z,0-9',$replace='-')
	{	  
	  	//try the cache.  
		$try=mod_rewrite::CacheURL($url);
		if($try) return $try;
		
	  	//allow custom sets - comma separated.  generate an array of sets.
		$allow=explode(',',$allow);
	  
		//replace non-allowed characters with desired characrer
	  	$final='';
	  	for($i=0;$i<strlen($url);$i++)
	  	{
	  	  	$chr=$url[$i];
			$ok=false;			
			//try each character against each set until within a set	
			foreach($allow as $set)
			{
			  	$set=explode('-',$set);
			  	if($chr>=$set[0] and $chr<=$set[1])
			  	{
			  		$ok=true;
			  		break;
			  	}
			}
	
			//if ok, leave it.  otherwise, replace it
			if($ok)	$final.=$chr;
			else	$final.=$replace;			
		}

		//remove duplicates and trim from start and end.
		while(strpos($final,$replace.$replace)!==false)
			$final=str_replace($replace.$replace,$replace,$final);
	  	$final=trim($final,$replace);
	  	
	  	return mod_rewrite::CacheURL($url,$final);
	}

	static function FromURL($url,$replace='-')
	{
	  	//undo url mod_rewrite encoding for an SQL 'LIKE' query
		return "%".str_replace($replace,'%',$url)."%";
	}
	
	static function CacheURL($orig,$final='')
	{
		global $_mod_rewrite_cache;
		global $_mod_rewrite_cache_disable; 
		
		if($_mod_rewrite_cache_disable) return false;
		
		if(!$orig)
			return false;
		 
		if($final)
			$_mod_rewrite_cache[$orig]=$final;
		
		return $_mod_rewrite_cache[$orig]?$_mod_rewrite_cache[$orig]:false;		
	}
	
	static function DisableCache($disable)
	{
		global $_mod_rewrite_cache_disable;			  
		$_mod_rewrite_cache_disable=$disable;
	}

};
?>