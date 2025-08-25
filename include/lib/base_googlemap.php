<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


$_google_map_key='';
$_google_map_count='0';
$_google_map_js_included=false;

class BaseGoogleMap
{
	var $center;
	var $range=5;
	var $map_id=0;
	var $controls='';
	var $markers='';
	var $polylines='';
	var $type='map';

		
	function __construct($c='',$r='',$t='')
	{
		//map id/etc...			
	  	global $_google_map_count;
		$this->map_id=$_google_map_count;
		$_google_map_count++;

		//defaults
	  	$this->center=array('lat'=>'0','lon'=>'0','address'=>'');
		$this->controls=array();
		$this->markers=array();
		$this->polylines=array();		
		$this->handlers=array();
	  
		$this->SetCenter($c);
		$this->SetRange($r);		
		$this->SetType($t);
		
		
		$this->Init();
	}

	function Init()
	{
		$this->controls=array('GLargeMapControl','GMapTypeControl');			  
	}
	
  	function SetAPIKey($key,$service='GOOGLE')
  	{
		global $_google_map_key;
		$_google_map_key[$service]=$key;		    
	}
  
  	function GetAPIKey($service='GOOGLE')
  	{
		global $_google_map_key;
		return $_google_map_key[$service];		    
	}
    
  	function SetCenter($c)
  	{
		if($c)
		 	$this->center=$c;
	}

  	function SetRange($r)
  	{
		if($r)
			$this->range=$r;	  
	}

	function SetType($t)
	{
		$valid=array('map','satellite','terrain');
		if(in_array($t,$valid))
			$this->type=$t;	 
		else if(in_array($this->type,$valid))
			$null;
		else
			$this->type='map';
		

	}

	function IncludeJSFiles()
	{
		echo("<script defer src='https://maps.googleapis.com/maps/api/js?key=".$this->GetAPIKey()."'></script>");		      	  
		echo("<script language='javascript' type='text/javascript'>");
	  	echo("function ".$this->GetGeocodeFN()."(address,callback)\n");
		echo("{\n");
		echo(	$this->GetGeocoderObj().".getLatLng(address,callback);");		
		echo("}\n");
	  	echo("function CenterAddress(address)\n");
		echo("{\n");
		echo(	$this->GetGeocodeFN()."(address,function(point){\r\n");
		echo("		if(point)\r\n");
		echo("		{\r\n");
		echo("			".$this->GetMapObj().".setCenter(point);\r\n");
		echo("			".$this->center['address_success_handler']."\r\n");
		echo("		}\r\n");
		echo("		else\r\n");
		echo("		{\r\n");
		echo("			".$this->center['address_error_handler']."\r\n");
		echo("		}\r\n");
		echo("													 });\r\n");		
		echo("}\n");
		echo("</script>");
	}
  
	function Load()  
	{
	  	if(!$this->map_loaded)
	  	{
		  	global $_google_map_js_included;
		  	if(!$_google_map_js_included)
		  	{
				$this->IncludeJSFiles();
				$_google_map_js_included=true;
			}
		  
			echo("<script language='javascript' type='text/javascript'>");
			echo("var ".$this->GetMapObj().";");
			echo("var ".$this->GetGeocoderObj().";");
			echo("function ".$this->GetLoadFN()."()\n");
			echo("{\n");
			echo($this->GetMapObj()." = new google.maps.Map(document.getElementById('".$this->MakeUnique('googlemap')."'),{center:new google.maps.LatLng(".$this->center['lat'].",".$this->center['lon']."),zoom:".$this->range."});\n");
			echo($this->GetGeocoderObj()." = new google.maps.Geocoder();\n");
			echo($this->GetLoadJS());
//B			foreach($this->controls as $c)
//				echo($this->GetMapObj().".addControl(new ".$c."());\n");
			foreach($this->markers as $m)
				echo($this->AddMarker($m));				
			foreach($this->polylines as $p)
				echo($this->AddPolyline($p));				
			foreach($this->handlers as $e=>$f)
				echo("google.maps.event.addListener(".$this->GetMapObj().",\"".$e."\",".$f.");\n");


			//if centering on address, must happen after load.
			if($this->center['address'])
			{
				$addr=str_replace("'","",str_replace("\n"," ",str_replace("\r"," ",$this->center['address'])));
				echo "CenterAddress('".$this->center['address']."');\n";		
			}

			echo("}\n");
			echo("</script>");
		}		
		$this->map_loaded=true;
  	}
  	

	function Draw($div_params=array())
	{
	  	if(!$div_params['class'])
		  	$div_params['class']='googlemap'; 
	  	echo("<div id='".$this->MakeUnique('googlemap')."' ".html::ProcessParams($div_params)."><br></div>");
	  	$this->Load();
  	}
  	
  	function MakeUnique($str)
  	{
		return $str.'_'.$this->map_id;
	}
  	
  	function GetMapObj()
  	{
		return ($this->MakeUnique('googleMapObject'))  ;  
	}

  	function GetGeocoderObj()
  	{
		return ($this->MakeUnique('googleGeocoderObject'))  ;  
	}

	function GetGeocodeFN()
	{
		return ($this->MakeUnique('Geocode'))  ;  	  
	}

  	function GetLoadFN()
  	{
		return ($this->MakeUnique('LoadGoogleMap'))  ;  
	}
  	
  	function GetLoadJS()
  	{
		$js="";    
		if(isset($this->center['lat']) and isset($this->center['lon']))
			$js.=$this->GetMapObj().".setCenter(new google.maps.LatLng(".$this->center['lat'].",".$this->center['lon']."));";
		$js.=$this->GetMapObj().".setZoom(".$this->range.");\n";

//		if($this->type)
//			$js.=$this->GetMapObj().".setMapTypeId('".$this->type."');\n";
   		return $js;	    
	}
	
	function AddMarker($m)
	{
	  	////error trap//
	  	//
	  	//
	  	if(!$m['lat'])
		  	$m['lat']=0;
	  	if(!$m['lon'])
		  	$m['lon']=0;
		$m['address']=str_replace("'","",str_replace("\n"," ",str_replace("\r"," ",$m['address'])));
		if(!is_array($m['listeners']))
			$m['listeners']=array();
	  	
		////setup/////
		//
		//
		
		//is this a new marker, or a global (named) marker?
   	  	$do_var=false;
   	  	if(!$m['name'])
   	  	{
   	  		$m['name']='newmarker';
	   	  	$do_var=true;
		}

		//do we have options to spefiy for the maker?
		if ($m['custom_marker']) //or are we making a custom marker icon?  
		{
			$opts[]="icon:'".$m['custom_marker']['image']."'";
		}
		if($m['tooltip'])
			$opts[]="title:'".$m['tooltip']."'";

		$options_str=$m['options'];
		if($options_str)
			$options_str=','.$options_str.'';						
		else if(count($opts))
			$options_str=','.implode(',',$opts).''; 
	
		////javascript output/////
		//
		// /at/lon from inputs or geocoder
   		echo("var latlon=new google.maps.LatLng(".$m['lat'].",".$m['lon'].");");
	   	if($m['address'])//have to wait out geocoder
	   	{
	   		echo($this->GetGeocodeFN()."('".$m['address']."',");
			echo("	function(latlon)");
			echo("	{");
			echo("	   if(!latlon)");
			echo("		{");
			if($m['address_error_handler'])
				echo("".$m['address_error_handler']."");
			else
				echo("		 latlon=new google.maps.LatLng(".$this->center['lat'].",".$this->center['lon'].");");			  
			echo("		}");
			echo("	    else");
			echo("		{");
			if($m['address_success_handler'])
				echo("".$m['address_success_handler']."");
			echo("		}");

	  		echo(($do_var?"var ":"").$m['name']."=new google.maps.Marker({position:latlon,map:".$this->GetMapObj().$options_str."});\n");
			//events for the marker
			foreach($m['listeners'] as $e=>$f)
				echo("google.maps.event.addListener(".$m['name'].",'".$e."',".$f.");");
			//html/info window foer the marker
			if($m['html'])	
			{
			  	echo("google.maps.event.addListener(".$m['name'].",'click',function(){");

				//tabbed or not
				if(!is_array($m['html']))
				{
					echo("var el=document.createElement('div');");
					echo("el.innerHTML=\"".$m['html']."\";");
					echo("this.openInfoWindow(el);");		
				}
				else
				{
					foreach($m['html'] as $title=>$html)
						$tabs[]="new GInfoWindowTab(\"".($title)."\", \"".($html)."\")";
					echo("var infoTabs = [".implode(',',$tabs)."];");
					echo("this.openInfoWindowTabsHtml(infoTabs);");		
				  
				  
				}

				if($m['click_range'])				
				{
					echo($this->GetMapObj().".setCenter(".$this->GetMapObj().".getCenter());");
					echo($this->GetMapObj().".setZoom(".$m['click_range'].");");
				}
				
				echo("});");
			}	
			if($m['mouseover_html'])	
			{
			  	echo("google.maps.event.addListener(".$m['name'].",'mouseover',function(){");
				echo("var el=document.createElement('div');");
				echo("el.innerHTML=\"".$m['mouseover_html']."\";");
				echo("this.openInfoWindow(el);");		
				echo("});");
			}	
			echo("});");
		}
		else //can add directly
		{
			//make & add the marker
	  		echo(($do_var?"var ":"").$m['name']."=new google.maps.Marker({position:latlon,map:".$this->GetMapObj().$options_str."});\n");
			//events for the marker
			foreach($m['listeners'] as $e=>$f)
				echo("google.maps.event.addListener(".$m['name'].",'".$e."',".$f.");");
			//html/info window foer the marker
			if($m['html'])	
			{
			  	echo("google.maps.event.addListener(".$m['name'].",'click',function(){");
				//tabbed or not
				if(!is_array($m['html']))
				{
					echo("var infowindow = new google.maps.InfoWindow({content: \"".$m['html']."\"});");
					echo("infowindow.open(".$this->GetMapObj().", ".$m['name'].");");
				}
				else
				{
					//tabs not supoprted in maps v3.  If you actually need this send the tabs in as the html.
					echo("var infowindow = new google.maps.InfoWindow({content: \"".$m['html'][0]."\"});");
					echo("infowindow.open(".$this->GetMapObj().", ".$m['name'].");");

				}
				
				if($m['click_range'])				
				{
					echo($this->GetMapObj().".setCenter(".$this->GetMapObj().".getCenter());");
					echo($this->GetMapObj().".setZoom(".$m['click_range'].");");
				}			
				echo("});");
			}	
			if($m['mouseover_html'])	
			{
			  	echo("google.maps.event.addListener(".$m['name'].",'mouseover',function(){");
				echo("var infowindow = new google.maps.InfoWindow({content: \"".$m['mouseover_html']."\"});");
				echo("infowindow.open(".$this->GetMapObj().", ".$m['name'].");");
				echo("});");
			}	
		}
	}
	
	function AddHandler($eventname,$function)
	{
		$this->handlers[$eventname]=$function;
	}
	
	function AddPolyline($p)
	{
	  	////error trap//
	  	//
	  	//
	  	$pts=array();

	  	if(!is_array($p['points']))
	  		return;
	  	if(!$p['color'])
	  		$p['color']='#000000';
	  	if(!$p['width'])
	  		$p['width']='2';
	  	if(!$p['opacity'])
	  		$p['opacity']='1';
	  	
	  	//setup
	  	foreach($p['points'] as $latlonpair)
	  	{
			if(!$latlonpair['lat'])$latlonpair['lat']=0;
			if(!$latlonpair['lon'])$latlonpair['lon']=0;
			
			$pts[]="new google.maps.LatLng(".$latlonpair['lat'].",".$latlonpair['lon'].")";
		}
		$pts=implode(',',$pts);
	  	echo("var points=Array(".$pts.");");

	  	
		//make & add the polyline
  		echo("var pl=new google.maps.Polyline({path:points,strokeColor:'".$p['color']."',strokeWeight:".$p['width'].",strokeOpacity:".$p['opacity'].");\n");
		echo("pl.setMap(".$this->GetMapObj().");\n");	  
	}	

	function GetLatLon($address)
	{
		$app_id=BaseGoogleMap::GetAPIKey('YAHOO');		
	  	$latlon=array('lat'=>0,'lon'=>0);
	
	
	
		if(!$address)	return false;
		if(!$app_id) 	return false; 
			
		$params = array('appid='.$app_id,'location='.urlencode($address));
		$result=file::ReadURL('http://api.local.yahoo.com/MapsService/V1/geocode?'.implode('&',$params));
		$result=strip_tags($result,'<latitude><longitude>');
		
		//lat
		$st=strpos($result,">");
		if($st!==false)
		{
			$end=strpos($result,"<",$st+1);
			if($end)
				$latlon['lat']=substr($result,$st+1,$end-$st-1);

			$st=strpos($result,">",$end+1);
			$st=strpos($result,">",$st+1);
			//lon
			if($st!==false)
			{
				$end=strpos($result,"<",$st+1);
				if($end)
					$latlon['lon']=substr($result,$st+1,$end-$st-1);
			}	 
		}
		return $latlon;  
	}
};

?>