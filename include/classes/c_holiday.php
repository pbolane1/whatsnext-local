<?php

class holiday extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('holidays','holiday_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{
			$this->Set('holiday_date_type','EXACT');			
		}
 	}

	public function DisplayEditable()
	{
		$next=$this->GetNextDate();
		
		$this->SortLink('holiday_order');
	 	echo("<td>".$this->Get('holiday_name')."</td>");
	 	echo("<td>".$next->GetDate('m/d/Y')."</td>");
	}
			
	public function GetNextDate()
	{
		$today=new date();
		$d=$this->GetDate(date('Y'));		
		if(date::GetDays($today,$d)<0)
			$d=$this->GetDate(date('Y')+1);
		return $d;
	}

	static function IsHoliday($date)
	{
		if(in_array($date->GetDBDate(),holiday::GetAllHolidays($date->GetDate('Y'))))
			return true;
		return false;
	}
	
	function GetObservedDate($year)
	{
		$date=sprintf('%4d-%02d-%02d',$year,$this->Get('holiday_month'),$this->Get('holiday_day'));
		$date=new DBDate($date);

	    $day = $date->GetDate('w');
	    if($day == 6)		
	        $date->Add(-1);
		elseif ($day == 0)
	        $date->Add(1);
	    return $date;
	}
	
	function GetDate($year) 
	{	
	 	$date=new date();
		if($this->Get('holiday_date_type')=='NTH')
		{
			if($this->Get('holiday_weekday_number')=='last')
			{
				$desc=$this->Get('holiday_weekday_number')." ".$this->Get('holiday_weekday')." of ".$this->Get('holiday_weekday_month')." ".$year;
				$dt=new DateTime($desc);
				$str=$dt->format("Ymd");
				$date=new DBDate($str);
				$date->SetTimestamp(strtotime($desc));
			}
			else
			{
				$desc=$this->Get('holiday_weekday_month')." $year ".$this->Get('holiday_weekday_number')." ".$this->Get('holiday_weekday');
				$date->SetTimestamp(strtotime($desc));				
			}
		}
		if($this->Get('holiday_date_type')=='EXACT')
        	$date = $this->GetObservedDate($year);		

	    return $date;	
	}	
	
	static function GetAllHolidays($year)
	{
	  	$where=array(1);
		$list=new DBRowSetEX('holidays','holiday_id','holiday',implode(' AND ',$where),'holiday_order');
		$list->Retrieve();

		foreach($list->items as $holiday)
			$holidays[$holiday->Get('holiday_name')]=$holiday->GetDate($year)->GetDBDate();
	    return $holidays;			
	}

	public function GetDescription()
	{
		
	}

	public function EditForm()
	{
		global $HTTP_POST_VARS;

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");

		echo("<tr><td class='label'>Name<div class='hint'></div></td><td colspan='3'>");
		form::DrawTextInput($this->GetFieldName('holiday_name'),$this->Get('holiday_name'),array('class'=>$this->GetError('holiday_name')?'error':'text'));
		echo("</td></tr>");

		echo("<tr><td class='label'>Date Style<div class='hint'></div></td><td colspan='3'>");
		$js="jQuery('TR.holiday_date_type').css({display:'none'});jQuery('TR.holiday_date_type_'+jQuery(this).val()).css({display:''});";
		$opts=array();
		$opts['Exact Date']='EXACT';
		$opts['Nth XXXDay of Month']='NTH';
		form::DrawSelect($this->GetFieldName('holiday_date_type'),$opts,$this->Get('holiday_date_type'),array('onchange'=>$js,'class'=>$this->GetError('holiday_name')?'error':'text'));
		echo("</td></tr>");

		echo("<tr class='holiday_date_type holiday_date_type_EXACT' style='display:".(($this->Get('holiday_date_type')=='EXACT')?'':'none')."'><td class='label'>Month<div class='hint'></div></td><td colspan='3'>");
		$opts=array();
		$opts['January']='1';
		$opts['February']='2';
		$opts['March']='3';
		$opts['April']='4';
		$opts['May']='5';
		$opts['June']='6';
		$opts['July']='7';
		$opts['August']='8';
		$opts['September']='9';
		$opts['October']='10';
		$opts['November']='11';
		$opts['December']='12';
		form::DrawSelect($this->GetFieldName('holiday_month'),$opts,$this->Get('holiday_month'),array('class'=>$this->GetError('holiday_month')?'error':'text'));
		echo("</td></tr>");

		echo("<tr class='holiday_date_type holiday_date_type_EXACT' style='display:".(($this->Get('holiday_date_type')=='EXACT')?'':'none')."'><td class='label'>Day<div class='hint'></div></td><td colspan='3'>");
		$opts=array();
		for($i=1;$i<=31;$i++)
			$opts[$i]=$i;
		form::DrawSelect($this->GetFieldName('holiday_day'),$opts,$this->Get('holiday_day'),array('class'=>$this->GetError('holiday_day')?'error':'text'));
		echo("</td></tr>");
		
		echo("<tr class='holiday_date_type holiday_date_type_NTH' style='display:".(($this->Get('holiday_date_type')=='NTH')?'':'none')."'><td class='label'>Which<div class='hint'></div></td><td colspan='3'>");
		$opts=array();
		$opts['first']='first';
		$opts['second']='second';
		$opts['third']='third';
		$opts['fourth']='fourth';
		$opts['fifth']='fifth';
		$opts['last']='last';
		form::DrawSelect($this->GetFieldName('holiday_weekday_number'),$opts,$this->Get('holiday_weekday_number'),array('class'=>$this->GetError('holiday_weekday_number')?'error':'text'));
		echo("</td></tr>");

		echo("<tr class='holiday_date_type holiday_date_type_NTH' style='display:".(($this->Get('holiday_date_type')=='NTH')?'':'none')."'><td class='label'>Weekday<div class='hint'></div></td><td colspan='3'>");
		$opts=array();
		$opts['Monday']='Monday';
		$opts['Tuesday']='Tuesday';
		$opts['Wednesday']='Wednesday';
		$opts['Thursday']='Thursday';
		$opts['Friday']='Friday';
		form::DrawSelect($this->GetFieldName('holiday_weekday'),$opts,$this->Get('holiday_weekday'),array('class'=>$this->GetError('holiday_weekday')?'error':'text'));
		echo("</td></tr>");		

		echo("<tr class='holiday_date_type holiday_date_type_NTH' style='display:".(($this->Get('holiday_date_type')=='NTH')?'':'none')."'><td class='label'>Month<div class='hint'></div></td><td colspan='3'>");
		$opts=array();
		$opts['January']='January';
		$opts['February']='February';
		$opts['March']='March';
		$opts['April']='April';
		$opts['May']='May';
		$opts['June']='June';
		$opts['July']='July';
		$opts['August']='August';
		$opts['September']='September';
		$opts['October']='October';
		$opts['November']='November';
		$opts['December']='December';
		form::DrawSelect($this->GetFieldName('holiday_weekday_month'),$opts,$this->Get('holiday_weekday_month'),array('class'=>$this->GetError('holiday_weekday_month')?'error':'text'));
		echo("</td></tr>");		



		echo("<tr><td colspan='2' class='save_actions'>");
 	}


	public function GatherInputs()
	{
		parent::GatherInputs();
 	}
 	
 	public function ValidateInputs()
 	{
		if($this->GetFlag('ALLOW_BLANK'))
			return true;

		if(!parent::ValidateInputs())
		    return false;		
		if(!$this->Get('holiday_name'))
			$this->LogError('Please Enter a Name For the Item','holiday_title');
	
		return count($this->errors)==0;
  	}

	public function Save()
	{
		$ret=parent::Save();

		if($ret)
		{

		}		
		return count($this->errors)==0;
	}
	
	public function Delete()
	{
		parent::Delete();
	}
};


?>