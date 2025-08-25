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

class math
{
	static function GetClosestMatch($me,$array)
	{
		$closest='';
		$distance=-1;
		foreach($array as $item)
		{
			$dist=abs($item-$me);
			if($dist<$distance or $distance<0)
            {
			    $distance=$dist;
			    $closest=$item;
			}
  		}
		return $closest;
 	}
};

?>