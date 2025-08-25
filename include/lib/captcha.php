<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


class captcha
{
	static function SetPath($path)
	{
		file::SetPath($path,'_captcha_files');
	}

	static function GetPath()
	{
		if(!file::GetPath('_captcha_files'))
			captcha::SetPath('captcha/');
		return file::GetPath('_captcha_files');  
	}

	static function GetRelPath()
	{
		$relpath=captcha::GetPath().'temp/';
		if(!file_exists(_navigation::GetBasePath().$relpath))
			mkdir(_navigation::GetBasePath().$relpath,0777);
			
		return $relpath;
	}
	
	static function SetTextColor($r,$g,$b)
	{
		global $_captcha_text_color;	  
		$_captcha_text_color=array('r'=>$r,'g'=>$g,'b'=>$b);	
	}

	static function GetTextColor()
	{
		global $_captcha_text_color;	  	
		if(!$_captcha_text_color)
			return array('r'=>0,'g'=>0,'b'=>0);
		return $_captcha_text_color;
	}
	
	static function SetPublicKey($key)
	{
		global $_captcha_public_key;	  
		$_captcha_public_key=$key;
	}

	static function SetPrivateKey($key)
	{
		global $_captcha_private_key;	  
		$_captcha_private_key=$key;
	}

	static function SetSize($w,$h)
	{
		global $_captcha_size;	  
		$_captcha_size=array($w,$h);
	}

	static function GetSize()
	{
		global $_captcha_size;	  
		if(!$_captcha_size)
			$_captcha_size=array(200,60);
		return $_captcha_size;  
	  
	}

	static function SetFontSize($sz)
	{
		global $_captcha_font_size;	  
		$_captcha_font_size=$sz;
	}

	static function GetFontSize()
	{
		global $_captcha_font_size;	  
		if(!$_captcha_font_size)
			$_captcha_font_size=20;
		return $_captcha_font_size;  
	  
	}
	static function GetError()
	{
		global $_captcha_error;	  
		return $_captcha_error;	  	  
	}

	static function Draw($fieldname='captcha',$params='',$full=true)
	{
	  	global $_captcha_count;
	  	$_captcha_count++;

		if($full)
			return captcha::DrawFull($fieldname,$params);


		//get the captcha value
		$value=captcha::GetValue();

		echo ("<div class='captcha'>");
		//draw the image
		captcha::Render($value);
		//draw the validation field
		form::DrawHiddenInput(captcha::GetValidationFieldName($fieldname,$value),captcha::Hash($value));
		//draw the text input
		form::DrawTextInput($fieldname,'',$params);
		
		//draw the options
//		echo("<div class='captca_options'>");
		captcha::OptionalImages($value);
//		echo("</div>");
		
		
		echo("</div>");


  	}		

	static function DrawSimple($fieldname='captcha',$params='')
	{
	  	global $_captcha_count;
	  	$_captcha_count++;


		//get the captcha value
		$value=captcha::GetValue();

		echo ("<div class='captcha'>");

				//draw the image and validation field
//			echo("<div class='captcha_top'>");
//			echo("<table><tr><td class='captcha_top_left'>");
				echo("<div class='captcha_image'>");
				captcha::Render($value);
				form::DrawHiddenInput(captcha::GetValidationFieldName($fieldname,$value),captcha::Hash($value));
				echo("</div>");
//			echo("</td><td class='captcha_top_right'>");
				//draw the options
				echo("<div class='captcha_options'>");
				echo("Hard to read? ");
				captcha::OptionalImages($value,'See a new code.');
				echo("</div>");
//			echo("</td></tr></table>");
//			echo("</div>");
			echo("<div class='captcha_input'>");
			form::DrawTextInput($fieldname,'',$params);
			echo("</div>");		
		
		echo("</div>");


  	}		


	static function DrawFull($fieldname='captcha',$params='')
	{
	  	global $_captcha_count;
	  	$_captcha_count++;


		//get the captcha value
		$value=captcha::GetValue();

		echo ("<div class='captcha'>");

				//draw the image and validation field
//			echo("<div class='captcha_top'>");
//			echo("<table><tr><td class='captcha_top_left'>");
				echo("<div class='captcha_image'>");
				captcha::Render($value);
				form::DrawHiddenInput(captcha::GetValidationFieldName($fieldname,$value),captcha::Hash($value));
				echo("</div>");
//			echo("</td><td class='captcha_top_right'>");
				//draw the options
				echo("<div class='captcha_options'>");
				echo("Hard to read? ");
				captcha::OptionalImages($value,'See a new code.');
				echo("</div>");
//			echo("</td></tr></table>");
//			echo("</div>");
			//draw the text input and instructions
			echo("<div class='captcha_input_header'>");
			echo("Please type the letters you see in the image above. This helps us to prevent spam submissions.");			
			echo("</div>");
			echo("<div class='captcha_input'>");
			echo("Enter code here:");
			form::DrawTextInput($fieldname,'',$params);
			echo("</div>");		
		
		echo("</div>");


  	}		

	
	static function Process($fieldname='captcha')
	{	
		global $_captcha_public_key;	  
		global $_captcha_private_key;	  
		global $_captcha_error;	  
	  	global $HTTP_POST_VARS;

        $_captcha_error = false;
		if(!$HTTP_POST_VARS[$fieldname])
	        $_captcha_error = 'Please enter validation code';	
		else if($HTTP_POST_VARS[captcha::GetValidationFieldName($fieldname,$HTTP_POST_VARS[$fieldname])]==captcha::Hash($HTTP_POST_VARS[$fieldname]))
			return true;
		else
			$_captcha_error = 'Validation code does not match';	

        return !$_captcha_error;
	}
	
	static function Render($value)
	{
	  	global $_captcha_count;
	  	
	  	$captcha_image=captcha::GetImage($value);
	  	if($captcha_image)
			echo("<img id='capthca_".$_captcha_count."' src='".$captcha_image."'>");
		else
			echo($value);
	}

	static function OptionalImages($value,$linktext='Get New Image')
	{
	 	global $_captcha_count;
		  
		$total=count(captcha::GetFonts())*count(captcha::GetBGs());
		$total=min(20,2*pow($total,.5));
		Javascript::Begin();
		echo("var captcha_option_".$_captcha_count."=0;");
		echo("var captcha_options_".$_captcha_count."=new Array();");
		for($i=0;$i<$total;$i++)
			echo("captcha_options_".$_captcha_count."[".$i."]='".captcha::GetImage($value,$i+1)."';");			
		Javascript::End();
		if($total)
			echo("<a href='#' onclick=\"document.getElementById('capthca_".$_captcha_count."').src=captcha_options_".$_captcha_count."[(captcha_option_".$_captcha_count."++)%(captcha_options_".$_captcha_count.".length)];return false;\">".$linktext."</a>");	  
	}
	
	static function GetValidationFieldName($fieldname='captcha',$value='')
	{
		global $_captcha_public_key;	  
		global $_captcha_private_key;
		
		//hash field name against public key
			  
	  	return(md5(strtoupper($value).$_captcha_public_key.$fieldname));
	
	}
	
	static function Hash($value)
	{
		global $_captcha_public_key;	  
		global $_captcha_private_key;
		
		//hash value against private key (should be validation reference value or user passed value...)
			  
	  	return(md5($_captcha_private_key.strtoupper($value)));
	}

	static function GetValue()
	{	  
		$value='';		
		$len=6;	
		
		//add $len characters to our string	
		for($i=0;$i<$len;$i++)
		{
		  	//get a random character
		  	$chr='';
			while(!$chr)
			{
			  	//randomly choose upper, lower or number
				$type=rand(0,2);
				switch($type)
				{
				  	//random by ascii value
				  	case 0:
						$chr=rand(ord('0'),ord('9'));
						break;
				  	case 1:
						$chr=rand(ord('A'),ord('Z'));
						break;
				  	case 2:
						$chr=rand(ord('a'),ord('z'));
						break;
				}
				//shwitch to character
				$chr=chr($chr);		
				//make sure it is not a potentially ambiguous character		
				if(in_array($chr,array('0','O','o','1','l','I','2','Z','z','5','S','s')))
					$chr='';			
			}
			$value.=$chr;
		}
		return $value;		
	}

	static function GetFonts()
	{
		global $_captha_fonts;
		if(!$_captha_fonts)
			$_captha_fonts=file::GetFilesInDirectory(_navigation::GetBasePath().captcha::GetPath(),false,array('ttf'));  
		return $_captha_fonts;
	}
	
	static function GetBGs()
	{
		global $_captha_bgs;
		if(!$_captha_bgs)
			$_captha_bgs=file::GetFilesInDirectory(_navigation::GetBasePath().captcha::GetPath(),false,array('png','jpg','gif'));  	  
		return $_captha_bgs;
	}

	
	static function GetImage($value,$opt='')
	{
		//save disk space - remove old captcha images.
		if(!$opt)
			captcha::AutoClean();

	  	//optional...
	  	if($opt)$opt='o'.$opt;
	  
	  	//get fonts and bgs
		$fonts=captcha::GetFonts();
		$bgs=captcha::GetBGs();
		
		//if no fonts or bgs available, bailout
		if(!count($fonts))
			return false;
		if(!count($bgs))
			return false;

		//choose font and bg
		$font = $fonts[rand(0,count($fonts)-1)];
		$bg = $bgs[rand(0,count($bgs)-1)];
		
//		putenv('GDFONTPATH=' . _navigation::GetBasePath().captcha::GetPath());
//		$font=str_replace(_navigation::GetBasePath().captcha::GetPath(),'',$font);
//		$font=str_replace('.ttf','',$font));
//		chmod($font,0777);
//		chmod($bg,0777);
		//relative path to captch files (create if DNE)
		$relpath=captcha::GetRelPath();
		//get a unique filename for the image
		$unique=0;
		do
		{
			$filename=mktime().'_'.($unique++).$opt.'.jpg';
		}while(file_exists(_navigation::GetBasePath().$relpath.$filename));	
		
		//the paths to the image to write and display
		$fpath=_navigation::GetBasePath().$relpath.$filename;
		$impath=_navigation::GetBaseURL().$relpath.$filename;

		//image size (orig)
		list($owidth, $oheight) = getimagesize($bg);
		
		//font size...
		$fontsize=20+rand(0,10);		

		//image size (target), font position...
		//duplicated in Imaging.php, but done here for easier backwards compatilibility
		
		$pad=5+rand(0,5);
		$box = imagettfbbox($fontsize, 0, $font, $value);
	    $min_x = min(array($box[0], $box[2], $box[4], $box[6]));
	    $max_x = max(array($box[0], $box[2], $box[4], $box[6]));
	    $min_y = min(array($box[1], $box[3], $box[5], $box[7]));
	    $max_y = max(array($box[1], $box[3], $box[5], $box[7]));	
	    $size=array('left' => ($min_x >= -1) ? -abs($min_x + 1) : abs($min_x + 2),
			        'top' => abs($min_y),
	    		    'width' => $max_x - $min_x,
			        'height' => $max_y - $min_y);
		
		$height=$size['height']+($pad*2);
		$width=$size['width']+($pad*2);				
		
		$xpos=$size['left']+($pad);
		$ypos=$size['top']+($pad);
		
		list($min_width,$min_height )=captcha::GetSize();
		if($height<$min_height)
		{
			$ypos+=(($min_height-$height)/2);
			$height=$min_height;
		}
		if($width<$min_width)
		{
			$xpos+=(($min_width-$width)/2);
			$width=$min_width;
		}
		
/*imge magick version...requires GS and some updating.*/
//		ImageMagick::Convert($bg,$fpath,array('-resize','"'.$width.'x'.$height.'"','-gravity','Center','-crop',$owidth.'x'.$oheight.'+0+0'));//,"-gravity southwest -fill black -draw \"text 5 5 'TEST IMAGEMMAGIC'\" -fill white -draw \"text 6 6 'TEST IMAGEMMAGIC'"));//;			


		//open bg image
		switch(file::GetExtension($bg))
		{
		  	case 'jpg':
				$bg = imagecreatefromjpeg($bg);
				break;
		  	case 'gif':
				$bg = imagecreatefromgif($bg);
				break;
		  	case 'png':
				$bg = imagecreatefrompng($bg);
				break;
		}		
		imagejpeg($bg,$fpath,100);	
		$im=imagecreatetruecolor($width,$height);
		imagecopyresized($im, $bg, 0, 0, 0, 0, $width, $height, $owidth, $oheight);


		//font color...
		$rgb=captcha::GetTextColor();
		$color = imagecolorallocate($im, $rgb['r'], $rgb['g'], $rgb['b']);


		//standard
		//imagettftext($im, $fontsize, 0, $xpos, $ypos, $color, $font, $value);

		//random rotation and offset
	  	$wvar=(rand(6,9))*$width/10;
	  	$wvar=max($size['width'],$wvar);
		for($i=0;$i<strlen($value);$i++)
		{
		  	$yvar=($height-$size['height'])/3;
		  	$yvar=rand(0-$yvar,$yvar);
		  	$avar=rand(5,-5);
		  	$xpos=($i*$wvar)/(strlen($value))+($width-$wvar)/2;
			imagettftext($im, $fontsize, $avar, $xpos, $ypos+$yvar, $color, $font, $value[$i]);
		}
		
		//output image
		imagejpeg($im,$fpath,100);	

		return $impath;
	}

	static function AutoClean()
	{
//	  return;
	  
		//get a list of all captcha images created in the past
		$relpath=captcha::GetRelPath();
		$images=file::GetFilesInDirectory(_navigation::GetBasePath().$relpath); 
		
		//find timestamp in name and delete any more than 10 minutes (600 seconds) old
		$vstime=mktime()-600;
		foreach($images as $file) 
		{		  			  
		  	$timestamp=str_replace(_navigation::GetBasePath().$relpath,'',$file);
		  	$timestamp=explode('_',$timestamp);
			$timestamp=$timestamp[0];
			
			if($timestamp<$vstime)
				unlink($file);
		}			
	}
	
};