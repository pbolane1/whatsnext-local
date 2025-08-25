<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


$_HTML_selfclose_tags=true;
class html
{
 	static function SelfCloseTags()
 	{
		global $_HTML_selfclose_tags;
		return $_HTML_selfclose_tags; 		
	}
 
 	static function SetSelfCloseTags($selfclose_tags)
 	{
		global $_HTML_selfclose_tags;
		$_HTML_selfclose_tags=$selfclose_tags; 		
	}
	
	static function ProcessParams($params)
	{
		if(!is_array($params))
		    return ' '.$params.' ';
		if(count($params)==0)
			return '';

		$strs=array();
		foreach($params as $k=>$v)
		    $strs[]=html::ProcessParam($k,$v);
		return implode(' ',$strs);
 	}


	static function ProcessParam($name,$value)
    {
		//if there's a single quote or it's a javascript call, encapsulate in double quotes,
		//otherwise, single quotes
		$value=htmlspecialchars($value);
		$quotes=(strpos($name,'on')===0 or strpos($value,"'")!==false);
        return $name.'='.($quotes?'"':"'").$value.($quotes?'"':"'");
	}

	static function ProcessTemplate($templatexml,$replace_array,$process_conditionals=true)
	{
		$html=$templatexml;
		//conditionals
		if($process_conditionals)
		{
			foreach($replace_array as $replace=>$value)
			{
			 	//IF
		 	 	$open=str_replace("<","<if ",$replace);    //<if KEY>
		 	 	$close=str_replace("<","</if ",$replace);  //</if KEY>
			 	if($value)
			 	{
			 	 	//just remove open and closing tags.
				    $html=str_replace($open,'',$html);
				    $html=str_replace($close,'',$html);
				}
				else
				{
					$html=preg_replace('#'.str_replace('#','\#',$open).'(.*?)'.str_replace('#','\#',$close).'#is','',$html);
				}
				
				//IF !
		 	 	$open=str_replace("<","<if !",$replace);    //<if !KEY>
		 	 	$close=str_replace("<","</if !",$replace);  //</if !KEY>
			 	if(!$value)
			 	{
			 	 	//just remove open and closing tags.
				    $html=str_replace($open,'',$html);
				    $html=str_replace($close,'',$html);
				}
				else
				{
					$html=preg_replace('#'.str_replace('#','\#',$open).'(.*?)'.str_replace('#','\#',$close).'#is','',$html);
				}			
				
				//EACH
		 	 	$open=str_replace("<","<each ",$replace);    //<each KEY>
		 	 	$close=str_replace("<","</each ",$replace);  //</each KEY>
				preg_match_all('#'.str_replace('#','\#',$open).'(.*?)'.str_replace('#','\#',$close).'#is',$html,$matches);
	
				foreach($matches[0] as $match)
				{
					$each=array();
					foreach($value as $key=>$data)
					{
						if(!$data['<KEY/>'])
							$data['<KEY/>']=$key;
						$each[]=html::ProcessTemplate($match,$data);
					}
					$html=str_replace($match,implode('',$each),$html);
				}
				$html=str_replace($open,'',$html);
				$html=str_replace($close,'',$html);
			}
		}
		
		//straight replacements
		foreach($replace_array as $replace=>$with)
		{
		 	if(!is_array($with))
			    $html=str_replace($replace,html::RemoveMagicQuotes($with),$html);
		}

		return $html;
 	}
 	
 	static function ProcessTemplateFile($file,$replace_array)
 	{
        $f=fopen($file,'r');

		if(!$f)
		    return false;

		$res=html::ProcessTemplate(fread($f,filesize($file)),$replace_array);
		fclose($f);
		return $res;
  	}

	static function RemoveMagicQuotes($str)
	{
		$str=str_replace("\'","'",$str);
		$str=str_replace('\"','"',$str);
		return $str;
	}

	//wrapper for outpout bufferinf
	static function HoldOutput()
	{
	  	ob_start();
	}

	static function GetHeldOutput()
	{
	  	return ob_get_contents();
	}

	static function ResumeOutput($dump=false)
	{
	  	if($dump)
	  		ob_end_flush();
	  	else
		  	ob_end_clean();
	}

	static function GetHeldOutputDepth()
	{
	  	return ob_get_level();
	}

	//JS and XML/RSS clean
	static function CleanRSS()
	{
  		$v=nl2br($v);
  		$v=str_replace('<br>',"<br/>",$v);
  		$v=str_replace("\r\n",'<br/>',$v);
  		$v=str_replace("\r",'<br/>',$v);
  		$v=str_replace("\n",'<br/>',$v);
  		$v=str_replace('&','&amp;',$v);
  		$v=str_replace('"','',$v);
  		$v=str_replace("'",'',$v);
  		$v=str_replace("’",'',$v);
  		$v=strip_tags($v);

		foreach(get_html_translation_table(HTML_ENTITIES) as $ent=>$rep)
		{
		  	if($ent!='&')
		  		$v=str_replace($ent,' ',$v);
		}
	  	return $v;
	  
	}

	static function CleanJS($html)
	{
		return Text::ReplaceAllOf($html,array("\r","\n","'",'"'),'');
	}


	static function ProtectEmail($content)
	{
		$regexp="/mailto:\b[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z0-9._%-]{2,4}\b/i";
	    $matches=array();
	    do
	    {
			preg_match($regexp, $content,$matches);
			foreach($matches as $emailto)
			{		  
			  	$mailto=str_replace('mailto:','',$emailto);
				$repl="javascript:document.location.href='mail'+'to:'";
			  	for($i=0;$i<strlen($mailto);$i++)
			  		$repl.="+'".$mailto[$i]."'";
				$repl.=";";		  
			  
				$content=str_replace($emailto,$repl,$content);
			}
		}
		while(count($matches));
		  
	  
	  
		$regexp="/\b[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z0-9._%-]{2,4}\b/i";
	    $matches=array();
		do
		{
			preg_match($regexp, $content,$matches);
			foreach($matches as $email)
			{
			  	$repl='';
			  	for($i=0;$i<strlen($email);$i++)
			  		$repl.='&#'.ord($email[$i]).';';
			  
			  
				$content=str_replace($email,$repl,$content);
			}
		}
		while(count($matches));
	
		return $content;  
	}


	static function ThumbImages($content)
	{
	    $matches=array();
		preg_match_all('/<img[^>]*>/i', $content, $matches); 
		$tothumb=array();
		if($matches[0])
		{			
			foreach($matches[0] as $idx=>$imgtag)
			{	
				preg_match('/height=["\']([0-9]*)["\']/i', $imgtag,$height);
				$height=$height[1];

				preg_match('/width=["\']([0-9]*)["\']/i', $imgtag,$width);
				$width=$width[1];
				preg_match('/src=["\']([^"\'>]*)["\']/i', $imgtag,$src);
				$src=$src[1];
			  		 
					   
	    		if(($height or $width) and $src and _navigation::IsURLLocal($src))
	    		{
					$height=max($tothumb[$src]['height'],$height);
					$height=$height?$height:10000;
					$width=max($tothumb[$src]['width'],$width);
					$width=$width?$width:10000;
					$tothumb[$src]=array('height'=>$height,'width'=>$width);
				}
			}

			foreach($tothumb as $src=>$size)
			{
				$url=parse_url($src);			  
			  	$imgsrc=$url['path'];//str_replace(_navigation::GetBaseURL(),'',$src);

				//if base url not = root, then remove path from file - otherwise gets doubled up
				$refurl=parse_url(_navigation::GetBaseURL());			  
				if($refurl['path']!='/')
					$imgsrc=str_replace($refurl['path'],'',$imgsrc);

			  	$server_path=_navigation::GetBasePath();
				$thumbsrc=imaging::ResizeCached($imgsrc,$server_path,$size['width'],$size['height']);	
				if($thumbsrc)
					$content=str_replace($imgsrc,$thumbsrc,$content);
			}
		}
	  	return $content;
	}

	static function ThumbImagesPHP5($content)
	{
		/* - requires php5, COMXML, simpleXML.....?*/
		$doc=new DOMDocument();
		$doc->loadHTML("<html><body>".$content."</body></html>");
		$xml=simplexml_import_dom($doc); // just to make xpath more simple
		$images=$xml->xpath('//img');
		foreach ($images as $img) 
		{
	    	if($img['height'] and $img['width'] and $img['src'] and _navigation::IsURLLocal($img['src']))
	    	{
				echo '<br>'.$img['src'] . ' ' . $img['height'] . ' ' . $img['width']." :::";
	
				$matches=$xml->xpath("//img[@src='".$img['src']."' and @height='".$img['height']."' and @width='".$img['width']."']");
				//we got em, but straight replacement on image URL is not ok... 
				//need to know which ones to replace.  come back t this some day.
				  
			}
		}
		/**/
	
		return $content;  

	}

};

$_js_include_path='/js/';
$_js_include_files=array();

class JavaScript extends html
{
	static function SetIncludePath($p)  
	{
		global $_js_include_path;
		$_js_include_path=$p;	  
	}

	static function GetIncludePath()  
	{
		global $_js_include_path;
		return $_js_include_path;	  
	}

	static function IncludeJS($file,$params='',$ignore_path=false,$once=true)
	{
	  	//onclude (once) logic/info...
		if($once and JavaScript::IsIncluded($file))
	  		return false;
  	  	global $_js_include_files;
		$_js_include_files[$file]++;	  
	  
	  	//proces params
		if(!is_array($params)) $params=array();
		if(!$params['src'])  
			$params['src']=($ignore_path?'':JavaScript::GetIncludePath()).$file;  

		//include the file [JS tag]
		javascript::Begin($params);
		javascript::End();
		return true;
	}

	static function Begin($params='')
	{
		if(!is_array($params)) $params=array();
		if(!$params['type'])  
			$params['type']='text/javascript';	  
		if(!$params['language'])  
			$params['language']='javascript';

		echo("<script ".JavaScript::ProcessParams($params).">");
	}
	  
  	static function End()
  	{
		echo("</script>");
	}
	
	static function Alert($msg)
	{
		echo("alert('".addslashes($msg)."');");  
	}
	
	static function IsIncluded($file)
	{
	  	global $_js_include_files;
		return ($_js_include_files[$file]);	  
	}
	
	static function GetIncluded()
	{
	  	global $_js_include_files;
		return ($_js_include_files);	  
	}	
};

?>