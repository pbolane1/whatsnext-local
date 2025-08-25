<?php

class contract_date extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('contract_dates','contract_date_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function ToURL()
	{
		if($this->Get('contract_date_url'))
			return $this->Get('contract_date_url');
//		if($this->Get('contract_date_file'))
//			return file::GetPath('contract_date_display').$this->Get('contract_date_file');
		return '#';		
	}


 	
	public function DisplayEditable()
	{
		$this->SortLink('contract_date_order');
		echo("<td>".$this->Get('contract_date_rpa_item')."</td>");
		echo("<td>".($this->Get('contract_date_contingency')?'*':'').$this->Get('contract_date_name')."</td>");
		echo("<td>".($this->Get('contract_date_key_date')?'x':'')."</td>");
		echo("<td>".$this->GetType()."</td>");
		echo("<td>".$this->GetRelativeText()."</td>");
		echo("<td>");
		echo("<ul>");
	  	$list=new DBRowSetEX('conditions_to_contract_dates','condition_id','condition',"contract_date_id='".$this->id."'",'condition_order');
	  	$list->join_tables="conditions";
	  	$list->join_where="conditions_to_contract_dates.condition_id=conditions.condition_id";
		$list->Retrieve();
		foreach($list->items as $condition)
			echo("<li>".$condition->Get('condition_name')."</li>");		
		echo("</ul>");
		echo("</td>");
	}

	public function DeleteLink()
	{
		if($this->Get('contract_date_special'))
			echo("</td>");
		else
			parent::DeleteLink();
	}

	public function GetRelativeText()
	{
		$relative_to=new contract_date($this->Get('contract_date_default_days_relative_to_id'));

		if(!$this->Get('contract_date_default_days_relative_to_id'))
			return '';

		if($this->Get('contract_date_default_days')>0)
			return $this->Get('contract_date_default_days').' After '.$relative_to->Get('contract_date_name');
		else
			return (0-$this->Get('contract_date_default_days')).' Prior To '.$relative_to->Get('contract_date_name');
	}
	
	public function GetTypes()
	{
		$types=array();
		$types['Date Selection']='DATE';
		$types['Date Selection Or Relative To Another Date']='DATE_RELATIVE';
		$types['Relative To Another Date']='RELATIVE';
		return $types;
	}

	public function GetType()
	{
		$types=array_flip($this->GetTypes());
		return $types[$this->Get('contract_date_type')];
	}

	public function SetDefaultDates()	
	{
		//find everyone that depends on this and fill in their date based on mine.	
	}
			
	public function EditForm()
	{
		global $HTTP_POST_VARS;

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");
		echo("<tr><td class='label'>RPA Item<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('contract_date_rpa_item'),$this->Get('contract_date_rpa_item'),array('class'=>$this->GetError('contract_date_rpa_item')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Date / Label<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('contract_date_name'),$this->Get('contract_date_name'),array('class'=>$this->GetError('contract_date_name')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Section<div class='hint'></div></td><td>");
		form::DrawSelect($this->GetFieldName('contract_date_primary'),array('Other Dates'=>'0','Start/End Dates'=>'1'),$this->Get('contract_date_primary'),array('class'=>$this->GetError('contract_date_primary')?'error':'text'));
		echo("</td></tr>");	
		echo("<tr><td class='label'>Key Date<div class='hint'></div></td><td>");
		form::DrawSelect($this->GetFieldName('contract_date_key_date'),array('No'=>0,'Yes'=>1),$this->Get('contract_date_key_date'),array('class'=>$this->GetError('contract_date_key_date')?'error':'text'));
		echo("</td></tr>");	
		echo("<tr><td class='label'>Contingency<div class='hint'></div></td><td>");
		form::DrawSelect($this->GetFieldName('contract_date_contingency'),array('No'=>'0','Yes'=>'1'),$this->Get('contract_date_contingency'),array('class'=>$this->GetError('contract_date_contingency')?'error':'text'));
		echo("</td></tr>");	
		echo("<tr><td class='label'>Type<div class='hint'></div></td><td>");
		form::DrawSelect($this->GetFieldName('contract_date_type'),$this->GetTypes(),$this->Get('contract_date_type'),array('class'=>$this->GetError('contract_date_type')?'error':'text'));
		echo("</td></tr>");	
		echo("<tr><td class='label'>Holidays<div class='hint'></div></td><td>");
		form::DrawSelect($this->GetFieldName('contract_date_holiday'),array('Cannot Occur on Weekend or Holiday'=>'0','CAN Occur on Weekend or Holiday'=>'1'),$this->Get('contract_date_holiday'),array('class'=>$this->GetError('contract_date_primary')?'error':'text'));
		echo("</td></tr>");	
		echo("<tr><td class='label'>Business Days<div class='hint'></div></td><td>");
		form::DrawSelect($this->GetFieldName('contract_date_business_days'),array('Count All Days'=>'0','Count By Business Days Only'=>'1'),$this->Get('contract_date_business_days'),array('class'=>$this->GetError('contract_date_business_days')?'error':'text'));
		echo("</td></tr>");	

//		echo("<tr><td class='label'>Has Checkbox<div class='hint'></div></td><td>");
//		form::DrawSelect($this->GetFieldName('contract_date_key_date'),array('No'=>0,'Yes'=>1),$this->Get('contract_date_checkbox'),array('class'=>$this->GetError('contract_date_checkbox')?'error':'text'));
//		echo("</td></tr>");	
//		echo("<tr><td class='label'>Checkbox Text<div class='hint'></div></td><td>");
//		form::DrawTextInput($this->GetFieldName('contract_date_checkbox_text'),$this->Get('contract_date_checkbox_text'),array('class'=>$this->GetError('contract_date_checkbox_text')?'error':'text'));
//		echo("</td></tr>");
		echo("<tr><td class='label'>Default Date<div class='hint'></div></td><td>");
		$opts=array();
		$opts['']='-0.01';
		for($i=-10;$i<0;$i++)
			$opts[(0-$i).' Days Prior To']=$i;
		for($i=0;$i<=365;$i++)
			$opts[$i.' Days After']=$i;
		form::DrawSelect($this->GetFieldName('contract_date_default_days'),$opts,$this->Get('contract_date_default_days'),array('style'=>'width:50%','class'=>$this->GetError('contract_date_default_days')?'error':'text'));
		form::DrawSelectFromSQL($this->GetFieldName('contract_date_default_days_relative_to_id'),"SELECT * FROM contract_dates WHERE contract_date_id!='".$this->id."'",'contract_date_name','contract_date_id',$this->Get('contract_date_default_days_relative_to_id'),array('style'=>'width:50%','class'=>$this->GetError('contract_date_default_days_relative_to_id')?'error':'text'),array(''=>''));
		echo("</td></tr>");

		if(!count($this->errors))
			$this->RetrieveRelated('conditon_ids','conditions_to_contract_dates',"contract_date_id='".$this->id."'",'','condition_id');

	  	$list=new DBRowSetEX('conditions','condition_id','condition',1,'condition_order');
		$list->Retrieve();
		echo("<tr><td class='label'>Conditions<div class='hint'></div></td><td style='text-align:left'>");
		foreach($list->items as $condition)
		{
			$condition_to_contract_date=new condition_to_contract_date();
			$condition_to_contract_date->InitByKeys(array('contract_date_id','condition_id'),array($this->id,$condition->id));
			if($HTTP_POST_VARS['condition_actions'][$condition->id])
				$condition_to_contract_date->Set('condition_to_contract_date_action',$HTTP_POST_VARS['condition_actions'][$condition->id]);
			echo("<div class='row'>");		
			echo("<div class='col-md-9'>");		
			echo("<label>");		
			form::DrawCheckbox('condition_ids['.$condition->id.']',$condition->id,in_array($condition->id,$this->related['conditon_ids']));
			echo(" ".$condition->Get('condition_name'));		
			echo("</label>");		
			echo("</div>");		
			echo("<div class='col-md-3'>");		
			form::DrawSelect('condition_actions['.$condition->id.']',array_flip($condition_to_contract_date->GetActions()),$condition_to_contract_date->Get('condition_to_contract_date_action'));
			echo("</div>");		
			echo("</div>");		
		}
		echo("</td></tr>");

		echo("<tr><td colspan='2' class='save_actions'>");
 	}


	public function GatherInputs()
	{
		global $HTTP_POST_VARS;
		
		parent::GatherInputs();

		$this->related['conditon_ids']=$HTTP_POST_VARS['condition_ids'];
 	}
 	
 	public function ValidateInputs()
 	{
		if(!parent::ValidateInputs())
		    return false;		
		if(!$this->Get('contract_date_name'))
			$this->LogError('Please Enter a Date/Label','contract_date_name');
	
		$this->ValidateURL('contract_date_url');
			
		return count($this->errors)==0;
  	}

	public function Save()
	{
		global $HTTP_POST_VARS;
		
		$keep_editing_name=$this->GetFieldName('keep_editing');

		$ret=parent::Save();
//		$this->Retrieve();

		if($ret)
		{
		  	database::query("DELETE FROM conditions_to_contract_dates WHERE contract_date_id=".$this->id."");
			foreach($this->related['conditon_ids'] as $foreign_id)
			{
				if($foreign_id>0)	  				
				{
					$condition_to_contract_date=new condition_to_contract_date();
					$condition_to_contract_date->CreateFromKeys(array('contract_date_id','condition_id'),array($this->id,$foreign_id));
					$condition_to_contract_date->Set('condition_to_contract_date_action',$HTTP_POST_VARS['condition_actions'][$foreign_id]);
					$condition_to_contract_date->Update();
				}
			}
			
		}		
		
		if($HTTP_POST_VARS[$keep_editing_name] and $ret)
		{
			$this->msg='Your Changes Have Been Saved';
			return false;
		}
		return $ret;	
	}
	
	public function Delete()
	{
		parent::Delete();
	}
};

?>