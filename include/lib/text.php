<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


class text
{
	static function Insert($subject,$insert,$pos)
	{
		if($pos>strlen($subject) or $pos<0)
			return $subject;
			
		$start_str=substr($subject,0,$pos);
		$end_str=substr($subject,$pos);
		$subject=$start_str.$insert.$end_str;
		return $subject;
 	}

	static function GetTextBetween($subject,$s_str,$e_str='',$s_pos=0,$s_cnt=1,$e_cnt=1)
	{
		if(!$subject or !$s_str)
		    return $subject;
		    
		$start_pos=text::GetPosNth($subject,$s_str,$s_cnt,$s_pos);
		$end_pos=text::GetPosNth($subject,$e_str,$e_cnt,$start_pos);
		return(substr($subject,$start_pos,$end_pos-$start_pos));
 	}

	static function GetPosList($subject,$s_list,$start_pos=0,$add_len=false,$case_sensitive=false)
	{
		$answers=array();
		if(!is_array($s_list))
		    return $answers;
		if(!$case_sensitive)
		    $subject=strtolower($subject);

		foreach($s_list as $s)
		{
			if(!$case_sensitive)
			    $s=strtolower($s);
			if($s)
			{
				$pos=strpos($subject,$s,$start_pos);
				if($pos!==false)
					$answers[]=$pos+($add_len?strlen($s):0);
			}
  		}
		if(count($answers)==0)
		    $answers[]=false;
		return $answers;
	}

	static function GetPosNth($subject,$search,$n,$start=0,$case_sensitive=false)
	{
		if(!$case_sensitive)
        {
		    $subject=strtolower($subject);
		    $search=strtolower($search);
		}
		$pos=$start;
		for($i=0;$i<$n;$i++)
			$pos=strpos($subject,$search,$pos+($i>0?strlen($search):0));
	    return $pos;
 	}

	static function FormatPrice($price,$cents=false,$signafter=true)
	{
	  	if($cents)	$fmt='%.2f';
	  	else		$fmt='%d';
		$price=sprintf($fmt,$price);
	  		
	  	$sign='';
		if(strpos($price,'+')!==false)	$sign='+';
		if(strpos($price,'-')!==false)	$sign='-';
		$price=str_replace($sign,'',$price);
	  
	  	$dot=strpos($price,'.');
	  	if($dot)
	  	{
  		  	$cents=substr($price,$dot);
			$price=substr($price,0,$dot);  		  	
		}
		$i=(strlen($price))%3;
		if($i==0)
		    $i=3;
		while($i<(strlen($price)))
		{
			$price=text::Insert($price,',',$i);
			$i+=4;
		}

		//alternate - if signed
		if($signafter)
		{
			if($sign) $sign=' ('.$sign.')';
			return $price.$cents.$sign;
		}
		
		return $sign.$price.$cents;
	}


	static function RemoveAllOf($subject,$rem_array)
	{
		if(!is_array($rem_array))
		    $rem_array=array();
		foreach ($rem_array as $rem)
		    $subject=str_replace($rem,'',$subject);
		return $subject;
	}

	static function ReplaceAllOf($subject,$rem_array)
	{
		if(!is_array($rem_array))
		    $rem_array=array();
		foreach ($rem_array as $rem=>$rep)
		    $subject=str_replace($rem,$rep,$subject);
		return $subject;
	}

	static function ReplaceAllOfWith($subject,$rem_array,$rep)
	{
		if(!is_array($rem_array))
		    $rem_array=array();
		foreach ($rem_array as $rem)
		    $subject=str_replace($rem,$rep,$subject);
		return $subject;
	}

	static function Capitalize($title, $delimiter = " ") 
	{
	
	  /* Capitalizes the words in a title according to the MLA Handbook.
	     $delimiter parameter is optional. It is only needed if delimiter
	     is not a space.    */
	
		$articles = 'a|an|the';
		$prepositions = 'aboard|above|according|across|against|along|around|as|at|because|before|below|beneath|beside|between|beyond|by|concerning|during|except|for|from|inside|into|like|near|next|of|off|on|out|outside|over|past|since|through|to|toward|underneath|until|upon|with';
		$conjunctions = 'and|but|nor|or|so|yet';
		$verbs = 'are|be|did|do|is|was|were|will';
		$exceptions = explode('|',$articles.'|'.$prepositions.'|'.$conjunctions.'|'.$verbs);
		$words = explode($delimiter,$title);
		$lastWord = count($words)-1;   // first & last words are always capitalized
		$words[0] = ucfirst($words[0]);
		$words[$lastWord] = ucfirst($words[$lastWord]);
		for($i=1; $i<$lastWord; $i++) 
		{
			if (!in_array($words[$i],$exceptions)) 
			{
				$words[$i] = ucfirst($words[$i]);
			}
		}
		$newTitle = implode(' ',$words);
		return $newTitle;
	}
	
	static function GetExternalCharacters()
	{
	  	$chars=array();
		$chars['&#8217;']="'";
		$chars['&#8230;']="...";
		$chars['&#8220;']='"';
		$chars['&#8221;']='"';
		$chars['&#8243;']='"';
		$chars['Â©']="(c)";
		$chars['©']="(c)";
		$chars['â€™']="'";
		$chars['â€œ']='"';
		$chars['â€']='"';
		$chars['â€']='"';
		$chars['€']='';
		$chars['â']='';
		$chars['Â']='';	
		return $chars;  
	}
	
	static function ReplaceExternalCharacters($string)
	{
	  	$chars=text::GetExternalCharacters();
	  	foreach($chars as $chr=>$rep)
		  	$string=str_replace($chr,$rep,$string);
		return $string;
	}

	static function GetWords($string,$max=-1)
	{
		$words=explode(' ',$string);
		if($max>0 and $max<count($words))
		{
		  	$wordsf=array();
			for($i=0;$i<$max;$i++)
				$wordsf[]=$words[$i];
			return $wordsf;
		}
		return $words;
	}
	
	static function LimitWords($string,$max=-1,$max_chars=0,$add_truncated='')
	{
		$words=implode(' ',Text::GetWords($string,$max));  
		if($max_chars)
			$words=substr($words,0,$max_chars);
		return $words.((strlen($words) and (strlen($words)<strlen($string)))?$add_truncated:'');
	}

	static function GenerateCode($min,$max,$ranges='0-9,a-z,A-Z')
	{	  
		$code='';
		$len=rand($min,$max);

		$ranges=explode(',',$ranges);
		$useranges=array();
		foreach($ranges as $k=>$set)
		{
		  	$set=explode('-',$set);
		  	$useranges[]=array('min'=>ord($set[0]),'max'=>ord($set[1]));
		}
		for($i=0;$i<$len;$i++)	  
		{
			$which=rand(0,count($useranges)-1);
			$code.=chr(rand($useranges[$which]['min'],$useranges[$which]['max']));
		}	  
		return $code;
	}

};

if(!function_exists('br2nl'))
{
	function br2nl($html)	
	{
		$html=str_ireplace('<br>',"\r\n",$html);
		$html=str_ireplace('<br/>',"\r\n",$html);
		return $html;	
	}
}

?>