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
//base calendar class
//
$weekdays=array('Sunday'=>'Sun','Monday'=>'Mon','Tuesday'=>'Tues','Wednesday'=>'Weds','Thursday'=>'Thuurs','Friday'=>'Fri','Saturday'=>'Sat');
/* 	

	herte's a sample stylesheet for the base calendar
	.CalendarTable{width:98%}
	
	.CalendarMonthHeader{height:30;width:100%;background:#666699;font-weight:normal;font-size:36px;text-align:center;border-top:1px solid #ffffff;border-left:1px solid #ffffff}
	.CalendarMonthHeaderInteriorTable{padding:5px;width:100%;text-align:center;}
	.CalendarMonth{height:30;font-weight:normal;font-size:36px;text-align:center;}
	
	.CalendarFooter{height:30;width:100%;background:#666699;font-weight:normal;text-align:center;border-top:1px solid #ffffff;border-bottom:1px solid #ffffff;border-left:1px solid #ffffff}
	.CalendarFooterInteriorTable{padding:5px;width:100%;text-align:center;}
	.CalendarFooterContents{height:30;font-weight:normal;font-size:12px;text-align:center;}
	
	.CalendarPrevLink{width:200px;white-space:nowrap;text-align:left;}
	.CalendarNextLink{width:200px;white-space:nowrap;text-align:right;}
	.CalendarNavLink{}
	
	.CalendarDayHeader{height:50;width:14%;text-align:center;background:#666666;border-top:1px solid #ffffff;border-left:1px solid #ffffff}
	.CalendarDay{height:50;width:14%;vertical-align:top;border-top:1px solid #ffffff;border-left:1px solid #ffffff;background:#BABBC9}
	.CalendarDayPast{height:50;width:14%;vertical-align:top;border-top:1px solid #ffffff;border-left:1px solid #ffffff;background:#BABBC9}
	.CalendarDayFuture{height:50;width:14%;vertical-align:top;border-top:1px solid #ffffff;border-left:1px solid #ffffff;background:#BABBC9}
	.CalendarDayCurrent{height:50;width:14%;vertical-align:top;border-top:1px solid #ffffff;border-left:1px solid #ffffff;background:#FFCC00}
	
	.CalendarDayEmptyPre{height:50;width:14%;vertical-align:top;border-top:1px solid #FFFFFF;border-left:1px solid #BABBC9;background:#FFFFFF}
	.CalendarDayEmptyPost{height:50;width:14%;vertical-align:top;border-top:1px solid #FFFFFF;border-right:1px solid #BABBC9;background:#FFFFFF}
	
	.CalendarDayMarker{font-weight:bold;padding:0px;vertical-align:top;}
*/




$weekdays=array('Sunday'=>'Sun','Monday'=>'Mon','Tuesday'=>'Tues','Wednesday'=>'Weds','Thursday'=>'Thurs','Friday'=>'Fri','Saturday'=>'Sat');

class calendar
{
	var $today;	
	var $start_date; //start here
	var $end_date;	 //do not go past here

	function __construct()
	{
		$this->today=new date();
		$this->start_date=new date();
		$this->GetEnd();
	}

	function SetTimeZone($offset)
	{
		$this->today->ToGMT();
		$this->today->AddTime($offset);
	 
	  
	}
	function SetStart($d,$m,$y)
	{
		$this->start_date=new date($d,$m,$y);
		$this->GetEnd();  
	}
	
	function SetStartTimestamp($t)
	{
		$this->start_date=new date();
		$this->start_date->SetTimestamp($t);  
		$this->GetEnd();
	}

	function CheckStart()
	{
		$m=$this->start_date->GetDate('m');
		$y=$this->start_date->GetDate('Y');

		$this->start_date=new Date(1,$m,$y);
	}
	
	function GetEnd()
	{
	  	$this->CheckStart();

		$m=$this->start_date->GetDate('m');
		$y=$this->start_date->GetDate('Y');

		$this->end_date=new Date(1,$m+1,$y);
	}
	
	function Draw()
	{
		$rows=0;
		
	  	echo("<table class='CalendarTable' cellspacing='0' cellpadding='0'>");
		$this->DrawMonthHeader();
		$this->DrawDaysHeader();

		//current calendar date!
		$cur=new Date();
		$cur->SetTimestamp($this->start_date->GetTimestamp());

		//backup to start of calendar (sunday)
		while($cur->GetDate('D')!='Sun')		
			$cur->Add(-1);
		
		//pre empty cells
		echo("<tr>");
		while($cur->GetTimestamp()<$this->start_date->GetTimestamp())
		{
	  		$this->DrawEmptyCell($cur,true);
	  		$cur->Add(1);
	  	}
	  	//this month
		while($cur->GetTimestamp()<$this->end_date->GetTimestamp())
		{
	  		$this->DrawCell($cur);
			if($cur->GetDate('D')=='Sat')
			{
				echo("</tr><tr>");
				$rows++;
	  		}
			$cur->Add(1);
	  	}

	  	//post empty cells
  		$added_cells=false;
		while($cur->GetDate('D')!='Sun')
		{
	  		$this->DrawEmptyCell($cur,false);
	  		$cur->Add(1);
	  		$added_cells=true;
	  	}
  		if($added_cells)
			$rows++;

		//min rows
		while($rows<$this->min_rows)
		{
			echo("</tr><tr>");
			do
			{
		  		$this->DrawEmptyCell($cur,false);
		  		$cur->Add(1);
		  	}
			while($cur->GetDate('D')!='Sun');
			$rows++;
		}
				
		$this->DrawFooter();
		echo("</tr></table>");
	}

	function DrawMonthHeader()
	{
	  	echo("<tr><td class='CalendarMonthHeader' colspan='7'>");
		echo("<table class='CalendarMonthHeaderInteriorTable' cellpadding='0' cellspacing='0'><tr>");
		echo("<td class='CalendarPrevLink'>");
		$this->DrawLink(-1);
		echo("</td>");
		echo("<td class='CalendarMonth'>");
	  	echo($this->start_date->GetDate('F Y'));
		echo("</td>");
		echo("<td class='CalendarNextLink'>");
		$this->DrawLink(1);
		echo("</td>");
	  	echo("</tr></table>");
		echo("</td></tr>");
	}

	function DrawFooter()
	{
	  	echo("<tr><td class='CalendarFooter' colspan='7'>");
		echo("<table class='CalendarFooterInteriorTable' cellpadding='0' cellspacing='0'><tr>");
		echo("<td class='CalendarPrevLink'>");
		$this->DrawLink(-1);
		echo("</td>");
		echo("<td class='CalendarFooterContents'>");
	  	$this->DrawFooterContents();
		echo("</td>");
		echo("<td class='CalendarNextLink'>");
		$this->DrawLink(1);
		echo("</td>");
	  	echo("</tr></table>");
		echo("</td></tr>");
	}

	function DrawFooterContents()
	{
		echo("&nbsp;");
	}

	function DrawDaysHeader()
	{
		global $weekdays;
		foreach($weekdays as $day=>$abbrev)
			echo("<td class='CalendarDayHeader' >".$day."</td>");
	  
	}

	function DrawCell($date)
	{
		echo("<td ".html::ProcessParams($this->GetCellParams($date))." >");
		$this->DrawDayMarker($date);
		$this->DrawDayContents($date);
		echo("</td>");
	}
	
	function GetCellParams($date)
	{
		$params=array();

	  	$diff=round(date::GetDays($this->today,$date,false)+0.5);
  		$class='CalendarDay';
	  	if($diff==0)
	  		$class='CalendarDayCurrent';
	  	else if($diff<0)
	  		$class='CalendarDayPast';
	  	else if($diff>0)
	  		$class='CalendarDayFuture';

		$params['class']=$class;

		return $params;	  
	}

	function DrawDayMarker($date)
	{
		echo("<div class='CalendarDayMarker'>".$date->GetDate('j')."</div>");  
	}

	//the function most likeley to be overridden
	function DrawDayContents($date)
	{
		echo("&nbsp;");
	}

	function DrawEmptyCellContents($date)
	{
		echo("&nbsp;");
	}


	function DrawEmptyCell($date,$pre=true)
	{
		echo("<td class='CalendarDayEmpty".($pre?'Pre':'Post')."'>");
		$this->DrawEmptyCellContents($date);
		echo("</td>");
	}

	function DrawLink($offset)
	{
	 	$link_date=new date();
	 	$link_date->SetTimestamp($this->start_date->GetTimeStamp());
	 	$link_date->Add(0,$offset);
		echo("<a class='CalendarNavLink' href='".$this->GetCalendarURL($link_date)."'>");
		$this->DrawLinkText($link_date);
		echo("</a>");
	}

	function DrawLinkText($link_date)
	{
		if(date::GetDays($this->start_date,$link_date,false)<0)
			echo("<< ");

		echo($link_date->GetDate('F Y'));

		if(date::GetDays($this->start_date,$link_date,false)>0)
			echo(" >>");
	  
	}

	function GetCalendarURL($link_date)
	{
		return "?calendar_day=".$link_date->GetDate('d')."&calendar_month=".$link_date->GetDate('m')."&calendar_year=".$link_date->GetDate('Y');  
	}
	
	
	
	//STATIC
	//... JS popup calendar interface
	function PopUpCalendarField($fieldname,$date='',$inc_js='',$link_text='',$fmtphp='',$fmtjs='',$params='',$linkjs_extra='')
	{
		if(!$params)	$params=array();
		if(!$link_text)	$link_text='Select...';
		if(!$fmtphp) 	$fmtphp='m/d/Y';
		if(!$fmtjs)		$fmtjs='MM/dd/yyyy';
	
	  	if($inc_js)
			echo("<script language='javascript' src='".$inc_js."'></script>");
	  		
		echo("<table class='calendar'><tr><td>");  
		echo("<script language='javascript'>var ".$fieldname."calendar = new CalendarPopup(\"".$fieldname."_cal_div\");</script>");
		form::drawTextInput($fieldname,$date?$date->GetDate($fmtphp):'',$params);
		echo("</td>");
		echo("<td>");				
		echo("<div id='".$fieldname."_cal_div' style='position:absolute;visibility:hidden;background:#FFFFFF;z-index:10000' class='popup_calendar_container'></div>");
		echo("&nbsp;&nbsp;<a class='popup_calendar_link' id='".($fieldname.'_link')."' href='#' onclick=\"".$fieldname."calendar.select(document.getElementById('".($fieldname)."'),'".($fieldname.'_link')."','".$fmtjs."');".$linkjs_extra.";return false;\">".$link_text."</a>");
		echo("</td></tr></table>");	  
	}

	function TimeSelect($fieldname,$time,$params='',$extra_options='')
	{
		$time_opts=array();
		if(is_array($extra_options))
			$time_opts=$extra_options;
		
		$h=0;
		while($h<24)
		{
			$m=0;
			while($m<60)
			{
			  	$t=$h*100+$m;
				$time_opts[calendar::TranslateTime($t)]=$t;
				$m+=15;
			}
			$h+=1;
		}
		
		form::DrawSelect($fieldname,$time_opts,$time,$params);
	}

	function TranslateTime($time)
	{
		if($time<0)	return '';
		$time=calendar::ExtractTime($time);		
		return $time['h'].':'.sprintf('%02d',$time['m']).' '.$time['ampm'];
	}
	
	function ExtractTime($time)
	{
		if($time<0)	return '';
		$vals=array();

		$vals['h']=0;
		$vals['m']=0;
		$vals['ampm']='a.m.';  
			
		$vals['h']=floor($time/100);
		$vals['m']=$time-($vals['h']*100);
		
		$vals['H']=$vals['h'];
		if($vals['h']>=12)	$vals['ampm']='p.m.';
		if($vals['h']==0)	$vals['h']=12;
		if($vals['h']>12)	$vals['h']-=12;
		
		return $vals;
	}

};

?>