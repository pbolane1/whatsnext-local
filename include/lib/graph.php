<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


// graph base class
//
// can be inherited from or used as is
//
// sample/reference css:
//  
/*
	.graph{border-left:1px solid #000000;border-bottom:1px solid #000000;margin:0px 0px 35px 100px;}
	.graph_xaxis{}
	.graph_yaxis{}
	.graph_xaxis_labels{position:absolute;left:2px;top:0px;}
	.graph_xaxis_tics{}
	.graph_xaxis_labels{}
	.graph_xaxis_guides{}
	
	.graph_yaxis_labels{}
	.graph_yaxis_tics{position:absolute;left:0px;top:0px;}
	.graph_yaxis_labels{position:absolute;left:-80px;top:-5px;width:80px;text-align:right}
	.graph_yaxis_guides{}
	
	
	.graph_xtic{background:#000000;}
	.graph_ytic{background:#000000;}
	.graph_xlabel{font-size:10px;}
	.graph_ylabel{font-size:10px;width:75px;text-align:right}
	.graph_xlabeltext{font-size:10px;font-weight:bold;position:absolute;bottom:-30px;left:400px;}
	.graph_ylabeltext{font-size:10px;font-weight:bold;position:absolute;top:50px;left:-80px;}
	
	.graph_xguide{background:#CCCCCC;}
	.graph_yguide{background:#CCCCCC;}
	
	
	.graph_point{background:#000066;border:1px solid #BBBBBB;border-bottom:0px;overflow:hidden;}
	.graph_pointlabel{background:#FFFFFF;border:1px solid #BBBBBB;white-space:nowrap;}
	.graph_line{background:#000066;height:2px;width:2px;}
	.graph_pointhtml{background:#FFFFFF;border:1px solid #BBBBBB;white-space:nowrap;padding:5px;z-index:3;width:350px;overflow:hidden;}
	
	.series2 .graph_point{background:#006600;border:1px solid #999999;border-bottom:0px;}
	.series2 .graph_pointlabel{background:#DDDDDD;border:1px solid #999999;white-space:nowrap;}
	.series2 .graph_line_point{background:#006600;}
	.series2 .graph_pointhtml{background:#DDDDDD;border:1px solid #999999;white-space:nowrap;padding:5px;z-index:3;}
*/



class graph
{
	var $series='';
	var $tic_size=1;
	var $tic_size2=5;
	
	var $guidelines=true;

	function graph($id='graph')
	{
	  	$this->id=$id;

		//series
		$this->series=array();

		//default axises
		$this->axis=array();		
		$this->axis['x']=array('min'=>0,'max'=>100,'steps'=>10,'stepprecision'=>1,'pxsize'=>500,'format'=>'%d','offsetx'=>0,'offsety'=>0);
		$this->axis['y']=array('min'=>0,'max'=>100,'steps'=>10,'stepprecision'=>1,'pxsize'=>500,'format'=>'%d','offsety'=>-10,'offsetx'=>-13);

	}

	function AddSeries($series)
	{
		$this->series[]=$series;
	}

	function SetAxisParam($axis,$param,$value)
	{
		$this->axis[$axis][$param]=$value;
	}

	function SetAxis($axis,$min,$max,$params='')
	{
	  	//apply any parameters provided
	  	if(!is_array($params))	$params=array();
		foreach($params as $k=>$v)
			$this->axis[$axis][$k]=$v;

		//set min/max as provided
		$this->axis[$axis]['min']=$min;
		$this->axis[$axis]['max']=$max;			
	}
	
	function SetSize($x,$y)
	{
		$this->axis['x']['pxsize']=$x;
		$this->axis['y']['pxsize']=$y;
	}

	function CaluclateScale()
	{
		$this->CheckAxis('x');
		$this->CheckAxis('y');

		//overall size
		$this->axis['x']['size']=$this->axis['x']['max']-$this->axis['x']['min'];
		$this->axis['y']['size']=$this->axis['y']['max']-$this->axis['y']['min'];

		//axis mid point
		$this->axis['x']['mid']=$this->axis['x']['min']+($this->axis['x']['size']/2);
		$this->axis['y']['mid']=$this->axis['y']['min']+($this->axis['y']['size']/2);

	  	//scale factor for view port
		$this->axis['x']['scale']=$this->axis['x']['pxsize']/$this->axis['x']['size'];
		$this->axis['y']['scale']=$this->axis['y']['pxsize']/$this->axis['y']['size'];

		//where the viewport starts
		$this->axis['x']['scale_offset']=$this->axis['x']['min']*$this->axis['x']['scale'];
		$this->axis['y']['scale_offset']=$this->axis['y']['min']*$this->axis['y']['scale'];

	}

	function RoundAxis($which,$value,$function='round')
	{
	  	$value=$value*(1/$this->axis[$which]['stepprecision']);

	  	if(!$function)				$value=$value;	  
	  	else if($function=='round')	$value=round($value);	  
	  	else if($function=='ceil')	$value=ceil($value);	  
	  	else if($function=='floor')	$value=floor($value);	  
			
		return $value*$this->axis['x']['stepprecision'];
	}

	function CalculateMinMax($which,$pad=0,$min_spread=10)
	{	  
		$min=$this->axis[$which]['min']!==''?$this->axis[$which]['min']:'';
		$max=$this->axis[$which]['max']!==''?$this->axis[$which]['max']:'';

		//find min and max based on points in series		
		foreach($this->series as $series)  		
		{
			foreach($series->points as $point)
			{
				if((float)($point->$which)<$min or $min==='')	
					$min=(float)($point->$which);
				if((float)($point->$which)>$max or $max==='')	
					$max=(float)($point->$which);					
			}
		}	  
		
		//round
		$min=$this->RoundAxis($which,$min,'floor');
		$max=$this->RoundAxis($which,$max,'ceil');

		//add padding to provide space before and after min and max
		$min-=$pad;
		$max+=$pad;
		
		//if we need to have a minimum scale, spread the extra out on either side of min/max, if appropriate
		if($min_spread)
		{
			$diff=$min_spread-($max-$min);
			if($diff>0)
			{
				$min-=$diff/2;			  
				$max+=$diff/2;			  
			}
		}

		//round
		$min=$this->RoundAxis($which,$min,'floor');
		$max=$this->RoundAxis($which,$max,'ceil');

		//updated axis; preserve any info already entered
		$this->SetAxis($which,$min,$max,$this->axis[$which]);
	}

	function CheckAxis($which)
	{
	  	//no # steps preferred?
	  	if(!$this->axis[$which]['steps'])
			$this->axis[$which]['steps']=ceil($this->axis[$which]['max']-$this->axis[$which]['min'])/($this->axis[$which]['stepprecision']);

		//determine size required for steps
		$this->axis[$which]['stepsize']=($this->axis[$which]['max']-$this->axis[$which]['min'])/($this->axis[$which]['steps']);
		//round to precision desired
		$this->axis[$which]['stepsize']=$this->RoundAxis($which,$this->axis[$which]['stepsize'],'ceil');

		//update to larger of max, or max that will be displayed
		$max=max($this->axis[$which]['min']+($this->axis[$which]['stepsize']*$this->axis[$which]['steps']),$this->axis[$which]['max']);
		$this->SetAxisParam($which,'max',$max);
	}

	function MapPoint($x,$y,$px_offs_x=0,$px_offs_y=0)
	{
		$xval=$x;
	  
	  	//scale to viewport
	  	$x=$x*$this->axis['x']['scale'];
	  	$y=$y*$this->axis['y']['scale'];

		//adjust by start of axis/viewport in this dimension
		$x-=$this->axis['x']['scale_offset'];
		$y-=$this->axis['y']['scale_offset'];

		//flip y axis
	  	$y=$this->FlipVert($y);	 

		//move by spec # px
		$x+=$px_offs_x;
		$y+=$px_offs_y;				
		  
		return array($x,$y);
	}

	function Draw()
	{
	  	//make sure scaling is ready for drawing
		$this->CaluclateScale();

	  	$params=array();
		$params['id']=$this->id;
		$params['class']='graph';
		$params['style']="height:".$this->axis['y']['pxsize']."px;width:".$this->axis['x']['pxsize']."px;position:relative;";

		echo("<div ".html::ProcessParams($params).">");
		$this->DrawAxis('x');
		$this->DrawAxis('y');
		$this->DrawGraph();	
		echo("</div>");
	}
  
  	function GetAxisMarkers($which)
  	{
		//get the values we wil show on the axis  	  	
  	  	$last='';
		$i=0;
		$this->axis[$which]['markers']=array();
		$stepsize=$this->axis[$which]['stepsize']?$this->axis[$which]['stepsize']:$this->axis[$which]['size']/$this->axis[$which]['steps'];
		do
		{	
		  	//get the value and value to display; only show unique values
		  	$value=$this->axis[$which]['min']+($i*($stepsize));
			$value_formatted=$this->FormatValue($this->axis[$which]['format'],$value);
			if($value_formatted!=$last)
			{
			  	$last=$value_formatted;
				//map point now / cached...
				$is_y=$which=='y';
				list($left,$top)=$this->MapPoint(($is_y?$this->axis['x']['min']:$value),($is_y?$value:$this->axis['y']['min']));	
			  	$this->axis[$which]['markers'][$value]=array('left'=>$left,'top'=>$top,'label'=>$value_formatted);
			}
			$i++;
			
		}while($value<$this->axis[$which]['max'] or $i<$this->axis[$which]['steps']);			
	}

	function FormatValue($format,$value)
	{
		if(!$format)
			return $value;
		if(strpos($format,'DATE')!==false)
		{
		 	$d=new date() ;
		 	$d->SetTimestamp($value);
		 	return $d->GetDate(str_replace('DATE ','',$format));
		}

		return sprintf($format,$value);	  
	}

	function DrawGuidelines($which)
	{
	  	$values=$this->axis[$which]['markers'];
		if($which=='x')
		{
			foreach($values as $value=>$pos)
				echo("<div class='graph_xguide' style='position:absolute;top:0px;left:".$pos['left']."px;height:".$this->axis['y']['pxsize']."px;width:1px;overflow:hidden;'></div>");
		}
		else if($which=='y')
		{
			foreach($values as $value=>$pos)
				echo("<div class='graph_yguide' style='position:absolute;left:0px;top:".$pos['top']."px;width:".$this->axis['x']['pxsize']."px;height:1px;overflow:hidden;'></div>");
		  
		}
	}

	function DrawAxisTics($which)
	{
	  	$values=$this->axis[$which]['markers'];
		if($which=='x')
		{		
			foreach($values as $value=>$pos)
				echo("<div class='graph_xtic' style='overflow:hidden;height:".$this->tic_size2."px;width:".$this->tic_size."px;position:absolute;top:".($pos['top']-$this->tic_size2)."px;left:".$pos['left']."px'></div>");					  				  
		}
		else if($which=='y')
		{
			foreach($values as $value=>$pos)
				echo("<div class='graph_ytic' style='overflow:hidden;height:".$this->tic_size."px;width:".$this->tic_size2."px;position:absolute;top:".$pos['top']."px;left:".$pos['left']."px'></div>");				  		  		  
		}
	}

	function DrawAxisLabels($which)
	{
	  	$values=$this->axis[$which]['markers'];
		if($which=='x')
		{		
			foreach($values as $value=>$pos)
				echo("<div class='graph_xlabel' style='position:absolute;top:".$pos['top']."px;left:".$pos['left']."px'>".$pos['label']."</div>");
		}
		else if($which=='y')
		{
			foreach($values as $value=>$pos)
				echo("<div class='graph_ylabel' style='position:absolute;top:".$pos['top']."px;left:".$pos['left']."px'>".$pos['label']."</div>");
		}
	}

	function DrawAxisLabel($which)
	{
		if($which=='x')
		{		
			if($this->axis['x']['label'])
				echo("<div class='graph_xlabeltext'>".$this->axis['x']['label']."</div>");
		}
		else if($which=='y')
		{
			if($this->axis['y']['label'])
			{
				for($i=0;$i<strlen($this->axis['y']['label']);$i++)
					$label.=$this->axis['y']['label']{$i}."<br>";
				echo("<div class='graph_ylabeltext'>".$label."</div>");
			}
		}
	}
	
  	function DrawAxis($which)
	{  	  		
		$this->GetAxisMarkers($which);

		//draw tics and labels for each step
		echo("<div class='graph_".$which."axis'>");

		if($this->guidelines)
		{
			echo("<div class='graph_".$which."axis_guides'>");
			$this->DrawGuidelines($which);
			echo("</div>");
		}

		echo("<div class='graph_".$which."axis_tics'>");
		$this->DrawAxisTics($which);
		echo("</div>");

		echo("<div class='graph_".$which."axis_labels'>");
		$this->DrawAxisLabels($which);
		echo("</div>");

		$this->DrawAxisLabel($which);
		echo("</div>");
	}

	function DrawGraph()
	{
	  	echo("<div class='graph_data'>");
		foreach($this->series as $series)  		
		{
			$this->DrawDataLines($series);
			$this->DrawDataPoints($series);
		}
		echo("</div>");
	}
	
	function DrawDataLines($series)
	{
		//allowed types
	  	if(!in_array($series->type,array('line','area')))
	  		return;

		//area graph or std line graph
	  	$fill_area=($series->type=='area');
		$point_class=$fill_area?"":"";

		$points=array();
		foreach($series->points as $point)
		{
			$pt=$this->MapPoint($point->x,$point->y);
			//the zeroeth point
		  	if($series->extend_lines and !count($points))
				$points[]=$this->MapPoint($this->axis['x']['min'],$point->y);
			//add the point		
			$points[]=array($pt[0],$pt[1]);
		}
		//the last point
		if($series->extend_lines)
			$points[]=$this->MapPoint($this->axis['x']['max'],$series->points[count($series->points)-1]->y);

		echo("<div class='".$series->id."'>");
		for($i=0;$i<count($points)-1;$i++)
		{
			$x1=$points[$i]['0'];		
			$y1=$points[$i]['1'];
			$x2=$points[$i+1]['0'];		
			$y2=$points[$i+1]['1'];
		
			//$steps=100;
			if($series->line_style=='full')
				$steps=pow(pow($x1-$x2,2)+pow($y1-$y2,2),.5);
			else if($series->line_style=='xstep')
				$steps=abs($x1-$x2);
			else if($series->line_style=='dotted')
				$steps=abs($x1-$x2);
		
			$adj_x=($x2-$x1)/$steps;
			$adj_y=($y2-$y1)/$steps;
			
			echo("<div class='graph_line'>");
			for($j=0;$j<$steps;$j++)
			{		
				//should we fill for area graph?  //option: width:".$adj_x."px;
				if($fill_area)
					echo("<div class='graph_line_area' style='overflow:hidden;position:absolute;height:".($this->FlipVert(0)-$y1)."px;top:".$y1."px;left:".$x1."px;'></div>");			    				

				//draw the line component
				if($series->line_style=='dotted' and ($j%4)!=0)
					$skip;
				else
					echo("<div class='graph_line_point' style='overflow:hidden;position:absolute;top:".$y1."px;left:".$x1."px;width:".ceil(abs($adj_x)+0.01)."px;height:".ceil(abs($adj_y)+0.01)."px;'></div>");			    				
					
			
				$x1+=$adj_x;
				$y1+=$adj_y;
			}
			echo("</div>");
		}
		echo("</div>");
	}
	
	function DrawDataPoints($series)
	{	
		if($series->line_style=='dotted')
			return;				

	  	//apply special class if series has an id
		echo("<div class='".$series->id."'>");
		foreach($series->points as $point)
		{
			//adjust point position / size based on the type of graph we are drawing
			list($left,$top)=$this->MapPoint($point->x,$point->y,$adjx,$adjy);	
			switch($series->type)
			{
				case 'line':  
				case 'point':  
				case 'area':  
					//width/height=those provided, move to center based on h/w
					$width=$point->width;								  
					$height=$point->height;								  
					$adjy=0-$point->height/2;
					$adjx=0-$point->width/2;
					break;
				case 'bar':
				default:
					//width=that provided, height should carry us down to y axis
					$height=$this->FlipVert(0)-$top;
					$width=$point->width;								  
					$adjx=0-$width/2;
					break;
			}
			list($left,$top)=$this->MapPoint($point->x,$point->y,$adjx,$adjy);	

			$pointclass=$point->class?$point->class:'graph_point_container';
			$html_id=$this->GetPopupId('html');
			$js=$this->GetHTMLJS($point,$html_id);
			$value=$point->innerhtml?$point->innerhtml:$value;
			//draw the point
			echo("<div class='".$pointclass."'>");
			echo("<div class='graph_point' ".$js." style='overflow:hidden;position:absolute;top:".$top."px;left:".$left."px;height:".$height."px;width:".$width."px;".$point->style.";'>".$value."</div>");			    				
			//label
			$this->DrawLabel($point);			
			//popup html
			$this->DrawPopupHTML($left,$top,$point,$html_id);
			echo("</div>");
		}
		echo("</div>");  
  	}

	function GetHTMLJS($point,$html_id)
	{
		if($point->html)
			$js=" onmouseover=\"".$html_id.".style.display='block';\" onmouseout=\"".$html_id.".style.display='none';\" ";
		return $js;	  
	}

	function DrawLabel($point)
	{
		if($point->label)
		{
			list($left_l,$top_l)=$this->MapPoint($point->x,$point->y,($point->width/2)+10,$point->height);	
			$p_x='left:'.($left_l).'px';
			$p_y='top:'.($top_l).'px';				
			echo("<div class='graph_pointlabel' style='position:absolute;".$p_y.";".$p_x.";'>".$point->label."</div>");			    								  
		}
	}

	function DrawPopupHTML($left,$top,$point,$html_id)
	{	
		if($point->html)
		{
			if($left<$this->axis['x']['pxsize']/2)	$p2_x='left:'.($left+$point->width+10).'px';
			else									$p2_x='right:'.($this->axis['x']['pxsize']-($left-10)).'px';					
			if($top<$this->axis['y']['pxsize']/2)	$p2_y='top:'.($top+$point->height+10).'px';
			else									$p2_y='bottom:'.($this->axis['y']['pxsize']-($top-10)).'px';									
	
			echo("<div class='graph_pointhtml' id='".$html_id."' style='display:none;position:absolute;".$p2_y.";".$p2_x.";'>".$point->html."</div>");			    				
		}
	}

	function GetPopupId($pre)
	{
		global $_graph_point_count;  
		$_graph_point_count++;
		return($pre.'_'.$_graph_point_count);
	}

	function FlipVert($y)
	{
	 	if($this->hi_low)
	 		return $y;
	  	return $this->axis['y']['pxsize']-$y;  //flip	  
	}

	function AutoColor()
	{
		echo("<style type='text/css'>");
		$cnt=1;
		$total=count($this->series)+1;
		foreach($this->series as $series)
		{	

			$num_segments=6;
	
		  	$color='000000';
			$segment_size=ceil($total/$num_segments);
			$segment=floor($cnt/$segment_size);
		  	
			$segment_pos=($cnt-($segment*$segment_size))+1;	  	
		  	
	  	  	$stp=trim(sprintf('%2s',dechex(round(255*$segment_pos/$segment_size))));
	  	  	while(strlen($stp)<2)
	  	  		$stp='0'.$stp;
		  	switch($segment)
		  	{
		  		case 0:
				  	$color=$stp.'00'.'00';
			  		break;
		  		case 1:
				  	$color='FF'.$stp.'00';
		  			break;
		  		case 2:
				  	$color='00'.$stp.'00';
		  			break;
		  		case 3:
				  	$color='00'.$stp.'FF';
		  			break;
		  		case 4:
				  	$color='00'.'00'.$stp;
		  			break;
		  		case 5:
				  	$color='FF'.'00'.$stp;
		  			break;
		  	}


			/*METHOD #2 - good for -losts-- of points maybe*/
			/*
			$max=4096;
			$r_ratio=256;
			$g_ratio=16;
			$b_ratio=1;
			$ratio=(($max)/$total);
			$mycnt=round($cnt*$ratio);

			$r=max(0,floor($mycnt/$r_ratio));
			$mycnt-=($r*$r_ratio);
			$g=max(0,floor($mycnt/$g_ratio));
			$mycnt-=($g*$g_ratio);
			$b=max(0,floor($mycnt/$b_ratio));
			$mycnt-=($b*$b_ratio);

			$r=trim(sprintf('%2s',dechex(round($r*16))));
	  	  	while(strlen($r)<2)		$r='0'.$r;
			$g=trim(sprintf('%2s',dechex(round($g*16))));
	  	  	while(strlen($g)<2)		$g='0'.$g;
			$b=trim(sprintf('%2s',dechex(round($b*16))));
	  	  	while(strlen($b)<2)		$b='0'.$b;

	  	  	$color=$r.$g.$b;
			*/
							  
			echo(".".$series->id." .graph_point{background:#".$color.";}\r\n");
			echo(".".$series->id." .graph_pointlabel{border:1px solid #".$color.";}\r\n");
			echo(".".$series->id." .graph_line_point{background:#".$color.";}\r\n");
			echo(".".$series->id." .graph_pointhtml{border:1px solid #".$color.";}\r\n");	  
			echo(".".$series->id." .graph_pointhtml{border:1px solid #".$color.";}\r\n");	  
			$cnt++;
		}
		echo("</style>");	  
	}

};

class graph_series
{
  	var $name='series';
  	var $points;
  	var $id='';
  	var $type='bar';
  	var $line_style='xstep';
	function graph_series($id,$name)  
	{
		$this->id=$id;
		$this->name=$name;
		$this->points=array();
	}

	function AddPoint($point)
	{
	 	$this->points[]=$point;
	}
	
	function SortPoints($by='x')
	{
  	
	}	
};

class graph_point
{
	function graph_point($x=0,$y=0,$label='',$params='')  
	{
		$this->x=$x;  
		$this->y=$y;  
		$this->label=$label;  
		
		$this->height=10;
		$this->width=10;
		
		if(!$params)	$params=array();
		foreach($params as $k=>$v)
			$this->$k=$v;
	}

	function SetHoverHTML()
	{
  
  	}
};	


class image_graph
{

	function image_graph($graph)
	{
	  	$this->FromGraph($graph);
	  	
	  	$this->usegd=!imaging::UsingImageMagick();
	  	$this->usegd=true;
	  	
	}
	 
	function FromGraph($graph) 
	{  
	  	$this->graph=$graph;
  	}
  	
  	function Draw($filename='',$filepath='',$type='')
	{
		$this->BeginImage();
		if($this->graph->guidelines)
		{
			$this->DrawGuidelines('x');
			$this->DrawGuidelines('y');
		}
		$this->DrawGraph();		
		$this->EndImage($filename,$filepath,$type);
		
		return $filename;
	} 

	function SetColor($which,$colorcode,$i='default')
	{	
	  	$colorcode=str_replace('#','',$colorcode);
	  	$colorcode=str_replace('0x','',$colorcode);
	  	$r=hexdec(substr($colorcode,0,2));
	  	$g=hexdec(substr($colorcode,2,2));
	  	$b=hexdec(substr($colorcode,4,2));
	  	
	  	if(!is_array($this->colors[$which])) 
		  	$this->colors[$which]=array();
		$this->colors[$which][$i]=array('r'=>$r,'g'=>$g,'b'=>$b);				

		//populate the default automatically if not already done.
		if(!$this->colors[$which]['default'])
			$this->colors[$which]['default']=$this->colors[$which][$i];
		
	}

	function MakeColors()
	{
		foreach($this->colors as $which=>$color)
		{
			foreach($color as $i=>$rgb)
				$this->colorcode[$which][$i]=imagecolorallocate($this->image,$rgb['r'],$rgb['g'],$rgb['b']);	  	
		}
	}

	function GetColorCode($which,$i=0)
	{
		if($this->colorcode[$which][$i])	  
			return $this->colorcode[$which][$i];
		else if($this->colorcode[$which]['default'])	  
			return $this->colorcode[$which]['default'];
		return 0;
	}
	
	function GetColorVector($which,$i=0)
	{
		if($this->colors[$which][$i])	  
			return "'rgb(".$this->colors[$which][$i]["r"].",".$this->colors[$which][$i]["g"].",".$this->colors[$which][$i]["b"].")'";
		else if($this->colors[$which]["default"])	  
			return "'rgb(".$this->colors[$which]["default"]["r"].",".$this->colors[$which]["default"]["g"].",".$this->colors[$which]["default"]["b"].")'";
		return "'rgb(255,255,255)'";
	} 	
	function BeginImage() 	
	{
		if($this->usegd)
		{
			$this->image=imagecreatetruecolor($this->graph->axis['x']['pxsize'],$this->graph->axis['y']['pxsize']);		
		  	$this->MakeColors();
			imagefilledrectangle($this->image,0,0,$this->graph->axis['x']['pxsize'],$this->graph->axis['y']['pxsize'],$this->GetColorCode('bg'));
			if(!$this->image)
				$this->error="Could not create image";
	  	}
	  	else
	  	{
	  		$this->image="convert -size ".$this->graph->axis['x']['pxsize']."x".$this->graph->axis['y']['pxsize']." xc:white ";
//	  		$this->image.=" -draw \"fill ".$this->GetColorVector('bg')." rectangle  0,0 ".$this->graph->axis['x']['pxsize'].",".$this->graph->axis['y']['pxsize']."\" ";
	  	}
		
		return $this->image;
	}
	 
	function EndImage($filename='',$filepath='',$type='') 
	{
	  	if($type)				$out=$type;
	  	else	if($filename)	$type=file::GetExtension($filename);
	  	else					$type='gif';
	  	
		if($this->usegd)
		{
		  	$type=strtolower($type);
			switch($type)
			{
				case 'jpg':
				case 'jpeg':
					imagejpeg($this->image,$filepath.$filename,100);		
					break;
				case 'png':
					imagepng($this->image,$filepath.$filename,100);		
					break;			
				case 'gif':
				default:
					imagepng($this->image,$filepath.$filename);		
					break;
			}
		}
		else
		{
		  	$thefile=str_replace('//','/',$filepath.$filename);
			$this->image.=" ".$thefile;
	
			global $imagemagickpath;
			exec($imagemagickpath.$this->image,$result);
			foreach ($result as $r=>$v)
				echo($r.":".$v."<br>");
	
			//echo("\r\n"."\r\n".$imagemagickpath.$this->image."\r\n"."\r\n");
		}				
		return $filename;
	}
	 	
	function DrawGuidelines($which)
	{
	  	$values=$this->graph->axis[$which]['markers'];
		if($which=='x')
		{
			foreach($values as $value=>$pos)
			{
				if($this->usegd)
					imageline($this->image,$pos['left'],0,$pos['left'],$this->graph->axis['y']['pxsize'],$this->GetColorCode('guide','x'));
			  	else
			  		$this->image.=" -stroke ".$this->GetColorVector('guide','y')." -strokewidth 1 -draw \"line ". $pos['left'].",".'0'." ".$pos['left'].",".$this->graph->axis['y']['pxsize']."\" ";
			}
		}
		else if($which=='y')
		{
			foreach($values as $value=>$pos)
			{
  				if($this->usegd)
					imageline($this->image,0,$pos['top'],$this->graph->axis['x']['pxsize'],$pos['top'],$this->GetColorCode('guide','y'));
			  	else
			  		$this->image.="stroke ".$this->GetColorVector('guide','y')." strokewidth 1  -draw \"line ".'0'.",". $pos['top']." ".$this->graph->axis['x']['pxsize'].",".$pos['top']."\" ";
			}
		}
	}
	
	function DrawGraph()
	{
		foreach($this->graph->series as $series)  		
		{
			$this->DrawDataLines($series);
			$this->DrawDataPoints($series);
		}
	}


	function DrawDataLines($series)
	{
		//allowed types
	  	if(in_array($series->type,array('line','area')))
		{
	
			//area graph or std line graph
		  	$fill_area=($series->type=='area');
			$point_class=$fill_area?"":"";
	
			$points=array();
			foreach($series->points as $point)
			{
				$pt=$this->graph->MapPoint($point->x,$point->y);
				//the zeroeth point
			  	if($series->extend_lines and !count($points))
					$points[]=$this->graph->MapPoint($this->graph->axis['x']['min'],$point->y);
				//add the point		
				$points[]=array($pt[0],$pt[1]);
			}
			//the last point
			if($series->extend_lines)
				$points[]=$this->graph->MapPoint($this->graph->axis['x']['max'],$series->points[count($series->points)-1]->y);
	
			for($i=0;$i<count($points)-1;$i++)
			{
				$x1=($points[$i]['0']);		
				$y1=($points[$i]['1']);
				$x2=($points[$i+1]['0']);		
				$y2=($points[$i+1]['1']);
			
				//should we fill for area graph?  //option: width:".$adj_x."px;
				if($fill_area)
				{
					if($this->usegd)
						imagefilledpolygon($this->image,array($x2,$this->graph->FlipVert(0),$x1,$this->graph->FlipVert(0),$x1,$y1,$x2,$y2),4,$this->GetColorCode('linefill',$series->id));
					else
				  		$this->image.=" -stroke ".$this->GetColorVector('linefill',$series->id)." -fill ".$this->GetColorVector('guide','y')." -draw \"polygon ".$x2.",".$this->graph->FlipVert(0)." ".$x1.",".$this->graph->FlipVert(0)." ".$x1.",".$y1." ".$x2.",".$y2."\" ";
				}					
				//draw the line component
				if($this->usegd)
					imagefilledpolygon($this->image,array($x2,$y2+$this->linesize,$x1,$y1+$this->linesize,$x1,$y1,$x2,$y2),4,$this->GetColorCode('line',$series->id));
			  	else
			  		$this->image.=" -stroke ".$this->GetColorVector('line',$series->id)." -strokewidth 2 -draw \"line ".$x1.",". $y1." ".$x2.",".$y2."\" ";
			}
		}
		if(in_array($series->type,array('bar')))
		{
			$points=array();
			foreach($series->points as $point)
			{
				$pt=$this->graph->MapPoint($point->x,$point->y);
				$width=$point->width;								  
				$adjx=0-$width/2;
				//add the point		
				$points[]=array($pt[0],$pt[1],$adjx);
			}
			for($i=0;$i<count($points);$i++)
			{
	
				$x1=($points[$i]['0']-$points[$i]['2']);		
				$y1=($points[$i]['1']);
				$x2=($points[$i]['0']+$points[$i]['2']);		
				$y2=($points[$i]['1']);
			
				//draw the line component
				if($this->usegd)
					imagefilledpolygon($this->image,array($x2,$this->graph->FlipVert(0),$x1,$this->graph->FlipVert(0),$x1,$y1,$x2,$y2),4,$this->GetColorCode('linefill',$series->id));
			  	else
				  	$this->image.=" -stroke ".$this->GetColorVector('linefill',$series->id)." -fill ".$this->GetColorVector('guide','y')." -draw \"polygon ".$x2.",".$this->graph->FlipVert(0)." ".$x1.",".$this->graph->FlipVert(0)." ".$x1.",".$y1." ".$x2.",".$y2."\" ";
			}
			
		}
	}
	
	function DrawDataPoints($series)
	{	
		return false;

	  	//apply special class if series has an id
		echo("<div class='".$series->id."'>");
		foreach($series->points as $point)
		{
			//adjust point position / size based on the type of graph we are drawing
			list($left,$top)=$this->graph->MapPoint($point->x,$point->y,$adjx,$adjy);	
			switch($series->type)
			{
				case 'line':  
				case 'point':  
				case 'area':  
					//width/height=those provided, move to center based on h/w
					$width=$point->width;								  
					$height=$point->height;								  
					$adjy=0-$point->height/2;
					$adjx=0-$point->width/2;
					break;
				case 'bar':
				default:
					//width=that provided, height should carry us down to y axis
					$height=$this->graph->FlipVert(0)-$top;
					$width=$point->width;								  
					$adjx=0-$width/2;
					break;
			}
			list($left,$top)=$this->graph->MapPoint($point->x,$point->y,$adjx,$adjy);	

			$pointclass=$point->class?$point->class:'graph_point_container';
			$html_id=$this->GetPopupId('html');
			$js=$this->GetHTMLJS($point,$html_id);
			$value=$point->innerhtml?$point->innerhtml:$value;
			//draw the point
			echo("<div class='".$pointclass."'>");			
			echo("<div class='graph_point' ".$js." style='overflow:hidden;position:absolute;top:".$top."px;left:".$left."px;height:".$height."px;width:".$width."px;".$point->style.";'>".$value."</div>");			    				
			//label
			$this->DrawLabel($point);			
			//popup html
			$this->DrawPopupHTML($left,$top,$point,$html_id);
			echo("</div>");
		}
		echo("</div>");  
  	}	
};

?>