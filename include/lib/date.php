<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

  class date
  {
	//constructor
	var $timestamp;
	var $show_day;
	var $show_month;
	var $show_year;
	var $unique_name='';

    function __construct($d=0,$m=0,$y=0,$name='')
	{
		$this->timestamp=@mktime(0,0,0,(int)$m,(int)$d,(int)$y);
		if(!$d and !$m and !$y)
		{
			$this->timestamp=time();
//			$this->Round();
		}
		
		$this->show_day=true;
		$this->show_month=true;
		$this->show_year=true;
		$this->unique_name=$name;

    }
	
	function Round()
	{
		$this->timestamp=mktime(0,0,0,$this->GetDate('m'),$this->GetDate('d'),$this->GetDate('Y'));	  
	}

	function ToGMT()
	{
		$this->AddTime(0-(date("Z")/3600));  	  
	}

	function DaylightSavings()
	{
		return date('I');
	}
	
	function AddTime($hours=0,$minutes=0,$seconds=0)
	{
	  	$tot+=$seconds;
	  	$tot+=$minutes*60;
	  	$tot+=$hours*60*60;

		$this->timestamp+=$tot;
	}

	function ParseDate($datestr,$fmt="")
	{
	  	if($fmt=='strtotime' or $fmt=='')
	  	{
		 	$datestr=str_replace('-','/',$datestr);
		 	$ts=strtotime($datestr);
				 	
 			$this->__construct(date('d',$ts),date('m',$ts),date('Y',$ts),$this->unique_name);
			return;
		}
	  
		if($fmt!="MM/DD/YYYY")
		    die('Date::ParseDate() Cannot Parse date format');

   		$d=substr($datestr,3,2);
		$m=substr($datestr,0,2);
		$y=substr($datestr,6,4);
		$this->date($d,$m,$y,$this->unique_name);

 	}

	function SetTimestamp($t)
	{
		$this->timestamp=$t;
	}

    function SetConstraints($showd,$showm,$showy)
    {
		$this->show_day=$showd;
		$this->show_month=$showm;
		$this->show_year=$showy;
	}

    function DrawForm($startyearsago=15,$endyearsfromnow=0,$class='')
    {
		if($this->show_day)
		{
			echo("<select name='".$this->unique_name."day' class='".$class."'>");
			for($i=1;$i<=31;$i++)
			    echo("<option value='".$i."'".(($this->GetDay()==$i)?" SELECTED ":"").">".$i."</option>");
			echo("</select>");
		}
		if($this->show_month)
		{
			echo("<select name='".$this->unique_name."month' class='".$class."'>");
			for($i=1;$i<=12;$i++)
			    echo("<option value='".$i."'".(($this->GetMonth()==$i)?" SELECTED ":"").">".date('F',mktime(0,0,0,($i+1),0,0))."</option>");
			echo("</select>");
		}
		
		if($this->show_year)
        {
			$curyear=date('Y');
			echo("<select name='".$this->unique_name."year' class='".$class."'>");
			for($i=$curyear-$startyearsago;$i<=$curyear+$endyearsfromnow;$i++)
			    echo("<option value='".$i."'".(($this->GetYear()==$i)?" SELECTED ":"").">".$i."</option>");
			echo("</select>");
		}
	}

    function DrawHiddenInputs()
    {
		if($this->show_day)
		    echo("<input type='hidden' name='".$this->unique_name."day' value='".$this->GetDay()."'>");
		if($this->show_month)
		    echo("<input type='hidden' name='".$this->unique_name."month' value='".$this->GetMonth()."'>");
		if($this->show_year)
		    echo("<input type='hidden' name='".$this->unique_name."year' value='".$this->GetYear()."'>");
	}

	function GetDay()
	{
		return $this->GetDate('d');
 	}

	function GetMonth()
	{
		return $this->GetDate('n');
 	}

	function GetYear()
	{
		return $this->GetDate('Y');
 	}

	function GetDate($fmt)
	{
		return (@date($fmt,$this->timestamp));
 	}

	function GetDBDate()
	{
		return $this->GetDate('Y-m-d');
 	}

	function GetDBDateTime()
	{
		return $this->GetDate('Y-m-d H:i:s');
 	}

	function GetErrors()
	{
        $chkarray=array();
		if($this->show_date)
		    $chkarray[]=$this->unique_name.'day';
		if($this->show_month)
		    $chkarray[]=$this->unique_name.'month';
		if($this->show_year)
		    $chkarray[]=$this->unique_name.'year';
		foreach($chkarray as $k)
		{
			global $k;
			if(!$k)
				return false;
  		}
		return true;
	}

	function GetTimeStamp()
	{
		return $this->timestamp;
 	}

	function Add($adddays=0,$addmonths=0,$addyears=0)
	{
		$oldtime=$this->timestamp;
		$this->timestamp=mktime(0,0,0,date('m',$oldtime)+$addmonths,date('d',$oldtime)+$adddays,date('Y',$oldtime)+$addyears);
 	}

	function IsValid()
	{
		if($this->GetDBDate()=='0000-00-00')
			return false;
		if($this->GetDBDate()=='1969-12-31')
			return false;
		if($this->GetDBDate()=='1999-11-30')
			return false;
		if($this->GetTimestamp()<=0)
			return false;
		
		return true;
	}
	
	function IsHoliday()
	{
		if(in_array($this->GetDBDate(),$this->GetAllHolidays($this->GetDate('Y'))))
			return true;
		return false;
	}
	
	function GetHolidayObservedDate($holiday)
	{
	    $day = date("w", strtotime($holiday));
	    if($day == 6) {
	        $GetHolidayObservedDate = $holiday -1;
	    } elseif ($day == 0) {
	        $GetHolidayObservedDate = $holiday +1;
	    } else {
	        $GetHolidayObservedDate = $holiday;
	    }
	    return $GetHolidayObservedDate;
	}
	
	function GetHoliday($holiday_name,$year) 
	{	
	    switch ($holiday_name) {
	        // New Years Day
	        case "new_year":
	            $holiday = $this->GetHolidayObservedDate(date('Y-m-d', strtotime("first day of january $year")));
	            break;
	        // Martin Luther King, Jr. Day
	        case "mlk_day":
	            $holiday = date('Y-m-d', strtotime("january $year third monday"));
	            break;
	        // President's Day
	        case "presidents_day":
	            $holiday = date('Y-m-d', strtotime("february $year third monday"));
	            break;
	        // Memorial Day
	        case "memorial_day":
	            $holiday = (new DateTime("Last monday of May"))->format("Ymd");
	            break;
	        // Independence Day
	        case "independence_day":
	            $holiday = $this->GetHolidayObservedDate(date('Y-m-d', strtotime("july 4 $year")));
	            break;
	        // Labor Day
	        case "labor_day":
	            $holiday = date('Y-m-d', strtotime("september $year first monday"));
	            break;
	        // Columbus Day
	        case "columbus_day":
	            $holiday = date('Y-m-d', strtotime("october $year second monday"));
	            break;
	        // Veteran's Day
	        case "veterans_day":
	            $holiday = $this->GetHolidayObservedDate(date('Y-m-d', strtotime("november 11 $year")));
	            break;
	        // Thanksgiving Day
	        case "thanksgiving_day":
	            $holiday = date('Y-m-d', strtotime("november $year fourth thursday"));
	            break;
	        // Christmas Day
	        case "christmas_day":
	        	$holiday = $this->GetHolidayObservedDate(date('Y-m-d', strtotime("december 25 $year")));
	            break;
	
	        default:
	            $holiday = "";
	            break;
	    }
	    return $holiday;	
	}	
	
	function GetAllHolidays($year)
	{
		$holidays=array();
		$holiday_names=array("new_year","mlk_day","presidents_day","memorial_day","independence_day","labor_day","columbus_day","veterans_day","thanksgiving_day","christmas_day");
		foreach($holiday_names as $name)
			$holidays[$name]=$this->GetHoliday($name,$year);
	    return $holidays;			
	}
	

	//to be called statically
 	static function GetDays($date1,$date2,$include=false)
 	{
		$days=($date2->GetTimeStamp() - $date1->GetTimeStamp() )/(60*60*24);
	    $days+=$include;
		return $days;
 	}

 	static function GetGreaterDate($date1,$date2)
 	{
		if($date2->GetTimeStamp() > $date1->GetTimeStamp())
		    return $date2;
		return $date1;
 	}

 	static function GetLesserDate($date1,$date2)
    {
		if($date2->GetTimeStamp() < $date1->GetTimeStamp())
		    return $date2;
		return $date1;

	}
	
	static function FormattingHelp()
	{
	  	echo("<table>");
		for ($i=ord('a');$i<=ord('z');$i++)	
		{	  
			echo("<tr><td><b>".chr($i)."</b></td><td>".$this->GetDate(chr($i))."</td></tr>");
			echo("<tr><td><b>".strtoupper(chr($i))."</b></td><td>".$this->GetDate(strtoupper(chr($i)))."</td></tr>");
		}
		echo("</table>");
	}
}

class dbdate extends date
{
	function __construct($datestr='',$name='')
	{
		$d=substr($datestr,8,2);
		$m=substr($datestr,5,2);
		$y=substr($datestr,0,4);
		parent::__construct($d,$m,$y,$name);
	}
}
?>