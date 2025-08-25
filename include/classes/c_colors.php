<?php
	class colors 
	{
		static public function luma($hex)
		{
			$hex=str_replace('#','',$hex);
	
			$r = hexdec($hex[0].$hex[1]);
			$g = hexdec($hex[2].$hex[3]);
			$b = hexdec($hex[4].$hex[5]);
	
	  		return (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
		}
	
		static public function ColorTooLight($color,$max=.94)
		{
			return(colors::luma($color)>$max);
		}
	
		
		static public function GetDarkest($colors=array())
		{
			$min=1;
			$winner='null';
			foreach($colors as $index=>$color)
			{
				if(colors::luma($color)<$min)
				{
					$winner=$index;
					$min=colors::luma($color);
				}
			}
	
			return $winner;
		}
	
		static public function GetLightest($colors=array())
		{
			$max=0;
			$winner='null';
			foreach($colors as $index=>$color)
			{
				if(colors::luma($color)>$max)
				{
					$winner=$index;
					$min=colors::luma($color);
				}
			}
	
			return $winner;
		}
	}
?>