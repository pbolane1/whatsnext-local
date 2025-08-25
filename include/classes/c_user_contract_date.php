<?php

class user_contract_date extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('user_contract_dates','user_contract_date_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function GatherInputs()
	{
		parent::GatherInputs();
		$this->GatherDate('user_contract_date_date');
	}

	public function Save()
	{
		parent::Save();	
		$this->CalculateDate();
	}

	public function CalculateDate()
	{
	 	//my contract date
		$contract_date=new contract_date($this->Get('contract_date_id'));
		//what it is relative to
		$relative_to=new contract_date($contract_date->Get('contract_date_default_days_relative_to_id'));

		if($this->Get('contract_date_special')=='FULL_CONTINGENCY_REMOVAL')
			return;
			
		//agent manually specified date?
		if($this->Get('user_contract_date_override'))
		{
			$this->Set('user_contract_date_moved',0);
			$this->Update();
			return;
		}
		
		//nothing?  then don't recalculate
		if(!$relative_to->id)
			return;

			
		//what I ahve for that dsdatw
		$mydate=new user_contract_date();
		$mydate->InitByKeys(array('contract_date_id','user_id'),array($relative_to->id,$this->Get('user_id')));

		//adjsut according to how many days I offset from that..
		$d=new DBDAte($mydate->Get('user_contract_date_date'));
		if(!$d->IsValid())
			$this->Set('user_contract_date_date',$d->GetDBDate());
		else
		{
		 	//business days - only count by business days.
			if($contract_date->Get('contract_date_business_days'))
		 	{
				$this->Set('user_contract_date_moved',0);
				$d=new DBDAte($mydate->Get('user_contract_date_date'));
				$count_days=0;
				while($count_days<$contract_date->Get('contract_date_default_days'))
				{
				 	$d->Add(1);
					if(!holiday::IsHoliday($d) and ($d->GetDate('w')!=0) and ($d->GetDate('w')!=6))
						$count_days++;
				}
			}	
		 	else //all days.  just add the # of days.
		 	{
				$d=new DBDate($mydate->Get('user_contract_date_date'));
				$d->Add($contract_date->Get('contract_date_default_days'));
			}

			//exclude wekends & holidays -- canot end on a weekend or holiday.
			if(!$contract_date->Get('contract_date_holiday'))
			{
				while(holiday::IsHoliday($d) or ($d->GetDate('w')==0) or ($d->GetDate('w')==6))
				{
					$d->Add(1);
					$this->Set('user_contract_date_moved',1);
				}
			}
		}
		
		//save.
		$this->Set('user_contract_date_date',$d->GetDBDate());
		$this->Update();		
	}

	function CountDays($relative_date)
	{
		$days=0;
		$d=new DBDate($this->Get('user_contract_date_date'));								
		$contract_date=new contract_date($this->Get('contract_date_id'));
		
		//if I fall on a weekend or holiday,it is not busines days.
		$business_days=$contract_date->Get('contract_date_business_days');
		if(!$contract_date->Get('contract_date_holiday') and (holiday::IsHoliday($d) or ($d->GetDate('w')==0) or ($d->GetDate('w')==6)))
			$business_days=false;
		if($business_days)
		{
			$count_days=0;
			if(date::GetDays($d,$relative_date)>0)//count up to it - we are N days BEFORE this date.			
			{
				while(date::GetDays($d,$relative_date)>0)
				{
				 	$d->Add(1);
					if(!holiday::IsHoliday($d) and ($d->GetDate('w')!=0) and ($d->GetDate('w')!=6))
						$days--;
				}
			}
			else //count down - we are N days AFTER this date.
			{
				while(date::GetDays($d,$relative_date)<0)
				{
				 	$d->Add(-1);
					if(!holiday::IsHoliday($d) and ($d->GetDate('w')!=0) and ($d->GetDate('w')!=6))
						$days++;
				}
			}			
		}
		else
			$days=date::GetDays($relative_date,$d);


		return $days;		
	}

	function GetDaysType()
	{
		$d=new DBDate($this->Get('user_contract_date_date'));								
		$contract_date=new contract_date($this->Get('contract_date_id'));
		
		//if I fall on a weekend or holiday,it is not busines days.
		$business_days=$contract_date->Get('contract_date_business_days');
		if(!$contract_date->Get('contract_date_holiday') and (holiday::IsHoliday($d) or ($d->GetDate('w')==0) or ($d->GetDate('w')==6)))
			$business_days=false;
		if($business_days)
			return "Business Days";		
		else
			return "Days";				
	}

};

?>