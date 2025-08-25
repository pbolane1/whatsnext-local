<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

	
	class XMLParser
	{
		var $xml_obj = null;
		var $output = array();
		var $attrs;
		
		static function XMLParser()
		{
			$this->xml_obj = xml_parser_create();
			xml_set_object($this->xml_obj,$this);
			xml_set_character_data_handler($this->xml_obj, 'dataHandler');
			xml_set_element_handler($this->xml_obj, "startHandler", "endHandler");
		}
		
		static function Parse($path)
		{
			if (!($fp = fopen($path, "r"))) 
			{
				die("Cannot open XML data file: $path");
				return false;
			}
			
			$xml='';
			while ($data = fread($fp, 4096)) 
				$xml.=$data;
				
			$this->output=$this->ParseXML($data);			
			return true;
		}

   		static function ParseUrl($xml_url) 
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $xml_url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			$store = curl_exec ($ch);
			$data = curl_exec ($ch);
			curl_close ($ch);

			$this->output=$this->ParseXML($data);
			return true;
		}

		static function GetNode($name,$parent='',$instance=1)
		{
		  	$cnt=1;
		  	if(!$parent)
			  	$parent=$this->output;
			else if($parent['elements'])
				$parent=$parent['elements'];
			foreach($parent as $k=>$v)
			{
				if($v['name']==$name)
				{
				  	if($cnt==$instance)  
						return $v;			  
					$cnt++;				
				}
			}  		    
		}

		//taken from a submission on the php.net manual
	static function ParseXML($xml)
	{
		$xmlary = array ();
		
		if ((strlen ($xml) < 256) && is_file ($xml))
		   	$xml = file_get_contents ($xml);
		 
		$ReElements = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*?)<(\/\s*\1\s*)>)/s';
		$ReAttributes = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';
		 
		preg_match_all ($ReElements, $xml, $elements);
		foreach ($elements[1] as $ie => $xx) 
		{
	   		$xmlary[$ie]["name"] = $elements[1][$ie];
         	$xmlary[$ie]['raw_xml']=$elements[3][$ie];
	     	if ( $attributes = trim($elements[2][$ie])) 
			{
	         	preg_match_all ($ReAttributes, $attributes, $att);
	         	foreach ($att[1] as $ia => $xx)// all the attributes for current element are added here
	           		$xmlary[$ie]["attributes"][$att[1][$ia]] = $att[2][$ia];
	     	}
	    
		    // get text if it's combined with sub elements
		   	$cdend = strpos($elements[3][$ie],"<");
	   		if ($cdend > 0)
	           	$xmlary[$ie]["text"] = substr($elements[3][$ie],0,$cdend -1);
	      
	     	if (preg_match ($ReElements, $elements[3][$ie]))
			{       
	         	$xmlary[$ie]["elements"] = $this->ParseXML($elements[3][$ie]);
	        }
	     	else if (isset($elements[3][$ie]))
	         	$xmlary[$ie]["text"] = $elements[3][$ie];
		    $xmlary[$ie]["closetag"] = $elements[4][$ie];
	   	}
	   	return $xmlary;
	}
}
?>