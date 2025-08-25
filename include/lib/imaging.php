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
require_once('file.php');
require_once('image_magick.php');

$useimagemagick=false;

class imaging extends file
{
	
	static function UseImageMagick($use=true)
	{
		global $useimagemagick;
		$useimagemagick=$use;	  
  	}

	static function UsingImageMagick()
	{
		global $useimagemagick;
		return $useimagemagick;
  	}

	static function SetQuality($q)
	{
		global $__imaging_quality;
		$__imaging_quality=$q;	  
  	}

	static function GetQuality()
	{
		global $__imaging_quality;
		return $__imaging_quality;	  
  	}

    static function Upload($file,$path,$allow_types='',$maxsize_ks='',$overwrite=false,$new_name='',$max_dimension='')
    {
	  	global $useimagemagick;
		//default allowed types for GD or ImageMagick
		if(!is_array($allow_types))
		{
		    $allow_types=array('gif','jpg','jpeg','png');
		    if($useimagemagick)
			    $allow_types=array('gif','jpg','jpeg','png','tif','tiff','bmp');
		}
		$success=file::Upload($file,$path,$allow_types,$maxsize_ks,$overwrite,$new_name);

		//check the file dimensions against those provided.  Default GD to 2000 if none provided
     	global $_file_upload_error;
		if(!$max_dimension and !imaging::UsingImageMagick())
			$max_dimension=2000;
		if($success and $max_dimension)
		{
		  	$check_file=$path.($new_name?$new_name:$filename);
			list($width, $height) = getimagesize($path.($new_name?$new_name:$filename));
			if($width>$max_dimension or $height>$max_dimension)
			{				  
				$_file_upload_error="Image size of ".$width."x".$height." is too large. Maximum size is ".$max_dimension."x".$max_dimension;
				unlink($check_file);
				return false;
			}
		}	
		return $success;



	}



	static function CropExact($filename,$newfilename,$path,$newwidth,$newheight,$offs_x,$offs_y)
	{	  	  
		$err=Imaging::CheckResizeErrors($filename,$newfilename,$path,$newwidth,$newheight);
		if($err)
			return $err;
			
		$image_type = strtolower(strstr($filename, '.'));
		$file = $newfilename;
		$fullpath = $path . $file;

		list($width, $height) = getimagesize($path.$filename);

		if($newwidth>$width or !$newwidth)
	        $newwidth=$width;
		if($newheight>$height or !$newheight)
		    $newheight=$height;
		if(!$offs_x)$offs_x=0;
		if(!$offs_y)$offs_y=0;

		global $useimagemagick;
		if($useimagemagick)
		{
			//crop out (only)
			$res=ImageMagick::Convert($path.$filename,$path.$newfilename,array('-crop',$newwidth.'x'.$newheight.'+'.$offs_x.'+'.$offs_y.'',' +repage'));
			if(!$res)
				return('image_magick_failed');
			//return $res;
		}	
		else
		{		
			switch($image_type)
			{
				case '.jpg':
				case '.jpeg':
					$source = imagecreatefromjpeg($path.$filename);
					break;
				case '.png':
					$source = imagecreatefrompng($path.$filename);
					break;
				case '.gif':
					$source = imagecreatefromgif($path.$filename);
					break;
				default:
					return("Error_Invalid_Image_Type_".$image_type);
					break;
			}
	
	
			$dst_r = ImageCreateTrueColor( $newwidth, $newheight );
			
			imagecopyresized($dst_r,$source,0,0,$offs_x,$offs_y,$newwidth,$newheight,$newwidth,$newheight);		
			str_replace('.'.$image_type,'.jpg',$fullpath);
			imagejpeg($dst_r, $fullpath, 100);
		}
		$filepath = $fullpath;
		chmod($filepath,0644);

//ALL DEBUG INFO:
//		echo('0'.','.'0'.','.$offs_x.','.$offs_y.','.$newwidth.','.$newheight.','.$newwidth.','.$newheight."<br>");
//	  	echo($filename.','.$newfilename.','.$path.','.$newwidth.','.$newheight.','.$offs_x.','.$offs_y."<br>");
//		echo("<img src='".(str_replace(_navigation::GetBasePath(),_navigation::GetBaseURL(),$fullpath))."'>");

		return $filepath;
	  
	}

    static function Resize($filename,$newfilename,$path,$newwidth,$newheight)
	{
	  	global $useimagemagick;

		$err=Imaging::CheckResizeErrors($filename,$newfilename,$path,$newwidth,$newheight);
		if($err)
			return $err;

		if($useimagemagick)
		{
			$res=ImageMagick::Convert($path.$filename,$path.$newfilename,array('-thumbnail','"'.$newwidth.'x'.$newheight.'>"'));
			if(!$res)
				return('image_magick_failed');
			return $res;
		}	
		else
		{
	       	//SEARCHES IMAGE NAME STRING TO SELECT EXTENSION (EVERYTHING AFTER . )
			$image_type = strtolower(strstr($filename, '.'));
	
			//SWITCHES THE IMAGE CREATE static function BASED ON FILE EXTENSION
			switch($image_type)
			{
				case '.jpg':
				case '.jpeg':
					$source = imagecreatefromjpeg($path.$filename);
					break;
				case '.png':
					$source = imagecreatefrompng($path.$filename);
					break;
				case '.gif':
					$source = imagecreatefromgif($path.$filename);
					break;
				default:
					return("Error_Invalid_Image_Type_".$image_type);
					break;
			}
			if(!$source)
			    return("could_not_use_image");
			//CREATES THE NAME OF THE SAVED FILE
			$file = $newfilename;
	
			//CREATES THE PATH TO THE SAVED FILE
			$fullpath = $path . $file;
			//FINDS SIZE OF THE OLD FILE
			list($width, $height) = getimagesize($path.$filename);
	
			if($newwidth>$width)
		        $newwidth=$width;
			if($newheight>$height)
			    $newheight=$height;
	
			//maintain aspect ratio
			$pctx=$newwidth/$width;
			$pcty=$newheight/$height;
			if($pctx<$pcty)
				$newheight=$height*$pctx;
			else if($pcty<$pctx)
				$newwidth=$width*$pcty;
	
			//CREATES IMAGE WITH NEW SIZES
			$thumb = imagecreatetruecolor($newwidth, $newheight);
			//RESIZES OLD IMAGE TO NEW SIZES
			imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			//SAVES IMAGE AND SETS QUALITY || NUMERICAL VALUE = QUALITY ON SCALE OF 1-100
			//SWITCHES THE IMAGE CREATE static function BASED ON FILE EXTENSION
			switch($image_type)
			{
				default:
				case '.jpg':
					str_replace('.'.$image_type,'.jpg',$fullpath);
					imagejpeg($thumb, $fullpath, imaging::GetQuality()?imaging::GetQuality():100);
					break;
	/*
				case '.png':
					imagepng($thumb, $fullpath);
					break;
				case '.gif':
					imagegif($thumb, $fullpath);
					break;
				default:
					echo("Error Invalid Image Type");
					die;
					break;
	*/
			}
		}
		//CREATING FILENAME TO WRITE TO DATABSE
		$filepath = $fullpath;
		chmod($filepath,0644);
		//RETURNS FULL FILEPATH OF IMAGE ENDS FUNCTION
		return $filepath;

	}

    static function ResizeCropped($filename,$newfilename,$path,$newwidth,$newheight)
	{
	  	global $useimagemagick;

		$err=Imaging::CheckResizeErrors($filename,$newfilename,$path,$newwidth,$newheight);
		if($err)
			return $err;

		$specwidth=$newwidth;
		$specheight=$newheight;

		//get the desired resize - best fit to smaller (not larger) dimension
		list($width, $height) = getimagesize($path.$filename);

		//don't resize up.
		if($newwidth>$width)
	        $newwidth=$width;
		if($newheight>$height)
		    $newheight=$height;

		//maintain aspect ratio
		$pctx=$newwidth/$width;
		$pcty=$newheight/$height;
		if($pctx>$pcty)
			$newheight=$height*$pctx;
		else if($pcty>$pctx)
			$newwidth=$width*$pcty;

		//create an intermediate image (image magick not always right w/ thumb then crop, easier for GD)
		//thumbed to smaller dimension to crop against.
		$aspct=round(max($pctx/$pcty,$pcty/$pctx)*1000000)/1000000;
		$temp_image=str_replace('cr','cr-int-'.str_replace('.','_',$aspct).'-',$newfilename);
		$intw=ceil($specwidth*$aspct);
		$inth=ceil($specheight*$aspct);
//		if(($pctx/$pcty)>($pcty/$pctx))			$inth=max($specheight,$inth);
//		else									$intw=max($specwidth,$intw);
		imaging::Resize($filename,$temp_image,$path,$intw,$inth);
		if($useimagemagick)
		{
			//resize/crop out if intermediate 
			$res=ImageMagick::Convert($path.$temp_image,$path.$newfilename,array('-thumbnail','"'.$newwidth.'x'.$newheight.' >"','-gravity','Center','-crop',$specwidth.'x'.$specheight.'+0+0',' +repage'));
			if(!$res)
				return('image_magick_failed');
			return $res;
		}	
		else //GD default
		{
			$source = imagecreatefromjpeg($path.$temp_image);
		  
			// assuming that $img holds the image with which you are working
			$img_width  = imagesx($source);
			$img_height = imagesy($source);
			
			// Starting point of crop
			$tlx = floor($img_width / 2) - floor ($specwidth / 2);
			$tly = floor($img_height / 2) - floor($specheight / 2);
			
			// Adjust crop size if the image is too small
			if ($tlx < 0)	$tlx = 0;
			if ($tly < 0)	$tly = 0;
			if (($img_width - $tlx) < $width)		$width = $img_width - $tlx;
			if (($img_height - $tly) < $height)		$height = $img_height - $tly;
		
			$file = $newfilename;	
			$fullpath = $path . $file;

			$thumb = imagecreatetruecolor($specwidth, $specheight);
			imagecopy($thumb, $source, 0, 0, $tlx, $tly, $width, $height);
			imagejpeg($thumb, $fullpath, 100);

			//CREATING FILENAME TO WRITE TO DATABSE
			$filepath = $fullpath;

			//RETURNS FULL FILEPATH OF IMAGE ENDS FUNCTION
			return $filepath;
		}
 	}

	static function Watermark($filename,$newfilename,$path,$watermark,$pct)
	{
	  	global $useimagemagick;
		$err=Imaging::CheckResizeErrors($filename,$newfilename,$path,0,0);
		if($err)
			return $err;

		if($useimagemagick)
		{
			$res=ImageMagick::Watermark($path.$filename,$path.$newfilename,$watermark,$pct,' -gravity center ');
			if(!$res)
				return('image_magick_failed');
			return $res;
		}	
		else
			return $path.$filename;
	}
 	
 	static function CheckResizeErrors($filename,$newfilename,$path,$newwidth,$newheight)
	{
		if(!$filename)
		    return 'no_file_name';
	    if(!file_exists($path.$filename))
			return 'no_orig_file';
		return false;
	}
	
 	static function ResizeCached($filename,$path,$width,$height,$crop=false,$thumbs_dir='thumbs/')
 	{
		//only do for real files
		if($filename and file_exists($path.$filename))
		{
		  	//if filename contains a path, remedy this.
	 	  	$path_extra=_navigation::GetPath($filename);
	 	  	$filename=str_replace($path_extra,'',$filename);
	 	  	$path.=$path_extra;
	 	  	
		  	//create the directory if it DNE
		  	if(!file_exists($path.$thumbs_dir))
		  	{
				mkdir($path.$thumbs_dir);
				chmod($path.$thumbs_dir,0777);
			}
			//get the auto-file name / new file name
			$new_filename=$thumbs_dir.($crop?'cr':'').$width."x".$height.$filename;
			
			//make the thumb if it DNE or older than source
			if(!file_exists($path.$new_filename) or (filemtime($path.$filename) >= filemtime($path.$new_filename)))
			{
			  	if(file_exists($path.$new_filename))
			  		unlink($path.$new_filename);			  	
			  	if($crop)
					imaging::ResizeCropped($filename,$new_filename,$path,$width,$height);
				else
					imaging::Resize($filename,$new_filename,$path,$width,$height);
			}
 
			//return the relative path to the image
			return $path_extra.$new_filename;
		}
		return '';
	}
	
	static function DeleteCached($filename,$path)
	{
	  	//might be able to replace w/ wild crads in an ls or delete sheell fn call?
	 
		$cnt=0;

		//only do for real files
		if($filename)
		{
		  	//get all files in dir
			$list=file::GetFilesInDirectory($path,false);
			
		  	//trim extesnion off of one to match(?) in case we changed extension during thumbnailing
		  	$filename=str_replace(file::GetExtension($filename),'',$filename);
		
			//check each one....
			foreach ($list as $file)
			{
				//does it match against 'NNNxNNNfilename.'?
			  	preg_match("/[0-9]*x[0-9]*".$filename."/",$file,$match);									
				if($match[0])
				{
					unlink($file);
					$cnt++;
				}
			}
		}
		return $cnt;
	}

	static function GetTextSize($fontsize, $angle, $font, $text)
	{
		$box = imagettfbbox($fontsize, $angle, $font, $text);
	    $min_x = min(array($box[0], $box[2], $box[4], $box[6]));
	    $max_x = max(array($box[0], $box[2], $box[4], $box[6]));
	    $min_y = min(array($box[1], $box[3], $box[5], $box[7]));
	    $max_y = max(array($box[1], $box[3], $box[5], $box[7]));	
	    $size=array('left' => ($min_x >= -1) ? -abs($min_x + 1) : abs($min_x + 2),
			        'top' => abs($min_y),
	    		    'width' => $max_x - $min_x,
			        'height' => $max_y - $min_y,
					'box'=>$box);
	 	return $size; 
	}

 	static function ReflectCached($filename,$path,$bgc='',$refl_height=0,$thumbs_dir='reflect/')
 	{
		//only do for real files
		if($filename and file_exists($path.$filename))
		{
		  	//if filename contains a path, remedy this.
	 	  	$path_extra=_navigation::GetPath($filename);
	 	  	$filename=str_replace($path_extra,'',$filename);
	 	  	$path.=$path_extra;
	 	  	
		  	//create the directory if it DNE
		  	if(!file_exists($path.$thumbs_dir))
		  	{
				mkdir($path.$thumbs_dir);
				chmod($path.$thumbs_dir,0777);
			}
			//get the auto-file name / new file name
			$new_filename=$thumbs_dir.mod_rewrite::ToURL($bgc,'A-Z,a-z,0-9').'_'.$filename;
			
			//make the thumb if it DNE or older than source
			if(!file_exists($path.$new_filename) or (filemtime($path.$filename) >= filemtime($path.$new_filename)))
			{
				imaging::Reflect($filename,$new_filename,$path,$bgc,$refl_height);
			}
 
			//return the relative path to the image
			return $path_extra.$new_filename;
		}
		return '';
	}

	static function Reflect($filename,$newfilename,$path,$bgc='',$refl_height=0)
	{
		$err=Imaging::CheckResizeErrors($filename,$newfilename,$path,0,0);
		if($err)
			return $err;

		//	bgc (the background colour used, defaults to black if not given)
		if (!$bgc)
		{
			$red = 0;
			$green = 0;
			$blue = 0;
		}
		else
		{
			//	Extract the hex colour
			$hex_bgc = $bgc;
			//	Does it start with a hash? If so then strip it
			$hex_bgc = str_replace('#', '', $hex_bgc);
			switch (strlen($hex_bgc))
			{
				case 6:
					$red = hexdec(substr($hex_bgc, 0, 2));
					$green = hexdec(substr($hex_bgc, 2, 2));
					$blue = hexdec(substr($hex_bgc, 4, 2));
					break;
				case 3:
					$red = substr($hex_bgc, 0, 1);
					$green = substr($hex_bgc, 1, 1);
					$blue = substr($hex_bgc, 2, 1);
					$red = hexdec($red . $red);
					$green = hexdec($green . $green);
					$blue = hexdec($blue . $blue);
					break;
				default:
					//	Wrong values passed, default to black
					$red = 0;
					$green = 0;
					$blue = 0;
			}
		}
		
		//	height (how tall should the reflection be?)
		if ($refl_height)
		{
			$output_height = $refl_height;
			//	Have they given us a percentage?
			if (substr($output_height, -1) == '%')
			{
				//	Yes, remove the % sign
				$output_height = (int) substr($output_height, 0, -1);
				//	Gotta love auto type casting ;)
				if ($output_height < 10)
					$output_height = "0.0$output_height";
				else
					$output_height = "0.$output_height";
			}
			else
				$output_height = (int) $output_height;
		}
		else  //	No height was given, so default to 50% of the source images height
			$output_height = 0.50;
				
		$alpha_start = 80;
		$alpha_end = 0;
	
		//	How big is the image?
		$image_details = getimagesize($path.$filename);
		if ($image_details === false)
			return '';
		else
		{
			$width = $image_details[0];
			$height = $image_details[1];
			$type = $image_details[2];
			$mime = $image_details['mime'];
		}
		
		//	Calculate the height of the output image
		if ($output_height < 1)	//	The output height is a percentage
			$new_height = $height * $output_height;
		else	//	The output height is a fixed pixel value
			$new_height = $output_height;
	
		//	Detect the source image format - only GIF, JPEG and PNG are supported. If you need more, extend this yourself.
	       	//SEARCHES IMAGE NAME STRING TO SELECT EXTENSION (EVERYTHING AFTER . )
		$image_type = strtolower(strstr($filename, '.'));

		//SWITCHES THE IMAGE CREATE static function BASED ON FILE EXTENSION
		switch($image_type)
		{
			case '.jpg':
			case '.jpeg':
				$source = imagecreatefromjpeg($path.$filename);
				break;
			case '.png':
				$source = imagecreatefrompng($path.$filename);
				break;
			case '.gif':
				$source = imagecreatefromgif($path.$filename);
				break;
			default:
				return("Error_Invalid_Image_Type_".$image_type);
				break;
		}
		if(!$source)
		    return("could_not_use_image");		
		/*
			----------------------------------------------------------------
			Build the reflection image
			----------------------------------------------------------------
		*/
	
		//	We'll store the final reflection in $output. $buffer is for internal use.
		$output = imagecreatetruecolor($width, $new_height);
		$buffer = imagecreatetruecolor($width, $new_height);	
		//	Copy the bottom-most part of the source image into the output
		imagecopy($output, $source, 0, 0, 0, $height - $new_height, $width, $new_height);
		//	Rotate and flip it (strip flip method)
	    for ($y = 0; $y < $new_height; $y++)
	       imagecopy($buffer, $output, 0, $y, 0, $new_height - $y - 1, $width, 1);
		$output = $buffer;		
		/*
			----------------------------------------------------------------
			Apply the fade effect
			----------------------------------------------------------------
		*/		
		//	This is quite simple really. There are 127 available levels of alpha, so we just
		//	step-through the reflected image, drawing a box over the top, with a set alpha level.
		//	The end result? A cool fade into the background colour given.
	
		//	There are a maximum of 127 alpha fade steps we can use, so work out the alpha step rate	
		$alpha_length = abs($alpha_start - $alpha_end);	
		for ($y = 0; $y <= $new_height; $y++)
		{
			//  Get % of reflection height
			$pct = $y / $new_height;	
			//  Get % of alpha
			if ($alpha_start > $alpha_end)
	            $alpha = (int) ($alpha_start - ($pct * $alpha_length));
	        else
	            $alpha = (int) ($alpha_start + ($pct * $alpha_length));
			imagefilledrectangle($output, 0, $y, $width, $y, imagecolorallocatealpha($output, $red, $green, $blue, $alpha));
		}
		/*
			----------------------------------------------------------------
			HACK - Build the reflection image by combining the source 
			image AND the reflection in one new image!
			----------------------------------------------------------------
		*/
		$finaloutput = imagecreatetruecolor($width, $height+$new_height);
		imagecopy($finaloutput, $source, 0, 0, 0, 0, $width, $height);
		imagecopy($finaloutput, $output, 0, $height, 0, 0, $width, $new_height);
		$output = $finaloutput;
	
		/*
			----------------------------------------------------------------
			Output our final PNG
			----------------------------------------------------------------
		*/
		$quality = 90;
		imagejpeg($output, $path.$newfilename, $quality);
//		imagepng($output, $path.$newfilename);


		return $path.$newfilename;
	}


	static function JCropProcess($unique_field,$filename='',$newfilename='',$path='')
	{
		global $HTTP_POST_VARS,$HTTP_POST_VARS;
		$newwidth=$HTTP_POST_VARS["jcrop_cw_".$unique_field]?$HTTP_POST_VARS["jcrop_cw_".$unique_field]:$HTTP_GET_VARS["jcrop_cw_".$unique_field];
		$newheight=$HTTP_POST_VARS["jcrop_ch_".$unique_field]?$HTTP_POST_VARS["jcrop_ch_".$unique_field]:$HTTP_GET_VARS["jcrop_ch_".$unique_field];
		$offs_x=$HTTP_POST_VARS["jcrop_cx_".$unique_field]?$HTTP_POST_VARS["jcrop_cx_".$unique_field]:$HTTP_GET_VARS["jcrop_cx_".$unique_field];
		$offs_y=$HTTP_POST_VARS["jcrop_cy_".$unique_field]?$HTTP_POST_VARS["jcrop_cy_".$unique_field]:$HTTP_GET_VARS["jcrop_cy_".$unique_field];
		$offs_x2=$HTTP_POST_VARS["jcrop_cx2_".$unique_field]?$HTTP_POST_VARS["jcrop_cx2_".$unique_field]:$HTTP_GET_VARS["jcrop_cx2_".$unique_field];
		$offs_y2=$HTTP_POST_VARS["jcrop_cy2_".$unique_field]?$HTTP_POST_VARS["jcrop_cy2_".$unique_field]:$HTTP_GET_VARS["jcrop_cy2_".$unique_field];

		if(!$filename or !$newfilename or !$path)
			return array('w'=>$newwidth,'h'=>$newheight,'x'=>$offs_x,'x2'=>$offs_x2,'y'=>$offs_y,'y2'=>$offs_y2);

//		$newwidth=$offs_x2-$offs_x;
//		$newheight=$offs_y2-$offs_y;
		return imaging::CropExact($filename,$newfilename,$path,$newwidth,$newheight,$offs_x,$offs_y);	  
	}

	static function JCropInvoke($unique_field,$immediate=true)
	{
		if($immediate)
		{
			Javascript::Begin();		
			echo("$(window).load(function(){jcrop_".$unique_field."();});");
			Javascript::End();
		}
		else
			return("jcrop_".$unique_field."();");

	}

	static function JCropDestroy($unique_field,$immediate=true)
	{
		if($immediate)
		{
			Javascript::Begin();		
			echo("$(window).load(function(){jcrop_destroy_".$unique_field."();});");
			Javascript::End();
		}
		else
			return("jcrop_destroy_".$unique_field."();");

	}

	static function JCrop($image_id,$preview_id='',$unique_field,$initial='',$min_w='',$min_h='',$box_w='300',$box_h='300',$max_w='',$max_h='')
	{
			form::DrawHiddenInput("jcrop_cx_".$unique_field,'');
			form::DrawHiddenInput("jcrop_cx2_".$unique_field,'');
			form::DrawHiddenInput("jcrop_cy_".$unique_field,'');
			form::DrawHiddenInput("jcrop_cy2_".$unique_field,'');
			form::DrawHiddenInput("jcrop_cw_".$unique_field,'');
			form::DrawHiddenInput("jcrop_ch_".$unique_field,'');
			Javascript::Begin();		
			echo("
				static function showCoords_".$unique_field."(c)
				{
				      // variables can be accessed here as
				      // c.x, c.y, c.x2, c.y2, c.w, c.h
				      document.getElementById('jcrop_cx_".$unique_field."').value=c.x;
				      document.getElementById('jcrop_cx2_".$unique_field."').value=c.x2;
				      document.getElementById('jcrop_cy_".$unique_field."').value=c.y;
				      document.getElementById('jcrop_cy2_".$unique_field."').value=c.y2;
				      document.getElementById('jcrop_cw_".$unique_field."').value=c.w;
				      document.getElementById('jcrop_ch_".$unique_field."').value=c.h;
				      
				      showPreview_".$unique_field."(c);
				};
			");

			echo("static function showPreview_".$unique_field."(coords){");			
			if($preview_id)
			{
				echo("
							if (parseInt(coords.w) > 0) 
							{
/*
					var rx = 100 / coords.w;
					var ry = 100 / coords.h;

					jQuery('#".$preview_id."').css({
						width: Math.round(rx * 500) + 'px',
						height: Math.round(ry * 370) + 'px',
						marginLeft: '-' + Math.round(rx * coords.x) + 'px',
						marginTop: '-' + Math.round(ry * coords.y) + 'px'
					});

*/
								var rx = ".$min_w." / coords.w;
								var ry = ".$min_h." / coords.h;
								var img_height = $('#".$image_id."').height();
								var img_width = $('#".$image_id."').width();
		 
								jQuery('#".$preview_id."').css({
									width: Math.round(rx * img_width) + 'px',
									height: Math.round(ry * img_height) + 'px',
									marginLeft: '-' + Math.round(rx * coords.x) + 'px',
									marginTop: '-' + Math.round(ry * coords.y) + 'px'
								});
							}
				");
			}
			echo("};\r\n");
			echo("var jcrop_".$unique_field."_api = false;\r\n");
			echo("static function jcrop_".$unique_field."(){\r\n"); 
			echo("	jcrop_destroy_".$unique_field."();\r\n");
			echo("  jcrop_".$unique_field."_api = ");
			echo("	$.Jcrop('#".$image_id."',{");
			echo("		onChange:    showCoords_".$unique_field.",");
			echo("		onSelect:    showCoords_".$unique_field.",");
			if($box_w)
				echo("	boxWidth:     ".$box_w.",");
			if($box_h)
				echo("	boxHeight:     ".$box_h.",");
			echo("		bgColor:     'black',");
			echo("		bgOpacity:   .4,");
			echo("		outerImage:  $('#".$image_id."').get(0).src,");
			echo("		innerImage:  $('#".$image_id."').get(0).src,");
			if($min_w and $min_h)
				echo("	minSize:	[".$min_w.",".$min_h."],");
			if($max_w and $max_h)
				echo("	maxSize:	[".$max_w.",".$max_h."],");
			if(is_array($initial) and isset($initial['x2']) and isset($initial['y2']))
				echo("		setSelect:   [ ".$initial['x'].", ".$initial['y'].", ".$initial['h'].", ".$initial['w']." ],");
			else if(is_array($initial))
				echo("		setSelect:   [ ".$initial['x'].", ".$initial['y'].", ".($initial['x']+$initial['w']).", ".($initial['y']+$initial['h'])." ],");
			if($min_w and $min_h)
				echo("	aspectRatio: ".$min_w." / ".$min_h."");
			echo("	});\r\n");
			echo("jcrop_".$unique_field."_api.setOptions({outerImage:$('#".$image_id."').get(0).src});");
			echo("}\r\n");				    			
			echo("static function jcrop_destroy_".$unique_field."(){\r\n"); 
			echo("	if(jcrop_".$unique_field."_api)\r\n");
			echo("  	jcrop_".$unique_field."_api.destroy();\r\n");
			echo("}\r\n");				    				  
			Javascript::End();

	}

	static function CheckColorProfile($infile,$convert=true)
	{
	  	//can only check if imagemagick only care it is a jpg.
	  	global $useimagemagick;
		if($useimagemagick and in_array(file::GetExtension($infile),array('jpg','jpeg')))
			return ImageMagick::CheckColorProfile($infile,$convert);
		return true;
	}


	static function GetContrastColor($hexColor) 
	{
        //////////// hexColor RGB
        $R1 = hexdec(substr($hexColor, 0, 2));
        $G1 = hexdec(substr($hexColor, 2, 2));
        $B1 = hexdec(substr($hexColor, 4, 2));

        //////////// Black RGB
        $blackColor = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 0, 2));
        $G2BlackColor = hexdec(substr($blackColor, 2, 2));
        $B2BlackColor = hexdec(substr($blackColor, 4, 2));

        //////////// Calc contrast ratio
        $L1 = 0.2126 * pow($R1 / 255, 2.2) +
              0.7152 * pow($G1 / 255, 2.2) +
              0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
              0.7152 * pow($G2BlackColor / 255, 2.2) +
              0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2)
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
		else
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));

        //////////// If contrast is more than 5, return black color; otherwise, white.
        if ($contrastRatio > 5)
            return '000000';
		else 
            return 'FFFFFF';
    }

};

?>