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
//Image Magick Wrapper;
//
$imagemagickpath='/usr/bin/';
$imagemagickpath_debug=false;
class ImageMagick
{
  	static function SetPath($path)
  	{
		global $imagemagickpath;
		$imagemagickpath=$path;	    
	    
	}

	static function Debug($debug)
	{
	 	global $imagemagickpath_debug;
		$imagemagickpath_debug=$debug;
	}

	static function Convert($from,$to,$params,$disp_only=true)
	{
		global $imagemagickpath;
		
		if(is_array($params))
			$params=implode(' ',$params);

		if(imaging::GetQuality())
			$params.=' -quality '.imaging::GetQuality().'%';

		if($disp_only and !in_array(file::GetExtension($to),array('jpg','gif','png','jpeg')))
			$to=file::ReplaceExtension($to,'gif');

		exec($imagemagickpath.'convert '.$from.' '.$params.' '.$to.' 2>&1',$result);

		global $imagemagickpath_debug;
		if($imagemagickpath_debug)
		{
			echo($imagemagickpath.'convert '.$from.' '.$params.' '.$to."<br>");
			foreach ($result as $r=>$v)
				echo($r.":".$v."<br>");
		}

		if(count($result)!=0)
			$to='';
		
		return $to;
	}
	
	static function Watermark($from,$to,$watermark,$pct,$params,$disp_only=true)
	{
		global $imagemagickpath;
		
		if(is_array($params))
			$params=implode(' ',$params);
		if(!$pct)
			$pct=50;

		if($disp_only and !in_array(file::GetExtension($to),array('jpg','gif','png','jpeg')))
			$to=file::ReplaceExtension($to,'gif');			
			
		exec($imagemagickpath.'composite -dissolve '.$pct.' '.$params.' '.$watermark.' '.$from.' '.$to.' 2>&1',$result);

		global $imagemagickpath_debug;
		if($imagemagickpath_debug)
		{
			echo($imagemagickpath.'convert '.$from.' '.$params.' '.$to."<br>");
			foreach ($result as $r=>$v)
				echo($r.":".$v."<br>");
		}

		if(count($result)!=0)
			$to='';
		
		return $to;
	}	

	static function CheckColorProfile($infile,$convert=true)
	{
		global $imagemagickpath;
				
		$out = array();
		//if path is relative, convert to absulte.  this can probably be done better, but should work for 99+% of deployments.
		$file=str_replace('../','',$infile);
		if($file!=$infile)
			$file=_navigation::GetBasePath().$file;
		$res1=exec($imagemagickpath.'identify -verbose '.$file.'',$out);
		$colorspace=false;

		foreach($out as $k=>$v)
		{
		  	//looking for "Colorspace: XXXX"
		  	$find='colorspace:';
		  	$str=trim(strtolower($v));
			if(strpos($str,$find)===0)  
			{
				$str=trim(str_replace($find,'',$str));
				if(!$colorspace)	
					$colorspace=$str;
				//echo($k.'::'.$v.'::'.$str.'<br>')	;
			}
		}
	  	
	  	if($colorspace and $colorspace!='rgb' and $colorspace!='srgb')
	  	{
			if($convert)//to RGB
			{	
			    $out2=array();
			    ImageMagick::Convert($file,$file,' -colorspace rgb');
	  			return ImageMagick::CheckColorProfile($infile,false);//did it work?
			}
		  	return false;	  	
		}
	  	return true;
	  
	}
};

?>