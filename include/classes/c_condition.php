<?php

class condition extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('conditions','condition_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function DisplayEditable()
	{
		$contract_date=new contract_date($this->Get('contract_date_id'));
	 
		$this->SortLink('condition_order');
		echo("<td>".$this->Get('condition_name')."</td>");
		echo("<td>".$this->Get('condition_text')."</td>");
		echo("<td>".($this->Get('condition_default')?'Yes':'No')."</td>");
	}
			
	public function EditForm()
	{
		global $HTTP_POST_VARS;

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");
		echo("<tr><td class='label'>Full Name<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('condition_name'),$this->Get('condition_name'),array('class'=>$this->GetError('condition_name')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Label<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('condition_text'),$this->Get('condition_text'),array('class'=>$this->GetError('condition_text')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Select By Default<div class='hint'></div></td><td>");
		form::DrawSelect($this->GetFieldName('condition_default'),array('No'=>'0','Yes'=>'1'),$this->Get('condition_default'),array('class'=>$this->GetError('condition_default')?'error':'text'));
		echo("</td></tr>");

		echo("<tr><td colspan='2' class='save_actions'>");
 	}


	public function GatherInputs()
	{
		parent::GatherInputs();

		if(!$this->Get('condition_name'))
			$this->Set('condition_name',$this->Get('condition_text'));
		if(!$this->Get('condition_text'))
			$this->Set('condition_text',$this->Get('condition_name'));
 	}
 	
 	public function ValidateInputs()
 	{
		if(!parent::ValidateInputs())
		    return false;		
		if(!$this->Get('condition_text'))
			$this->LogError('Please Enter a Date/Label','condition_text');
			
		return count($this->errors)==0;
  	}

	public function Save()
	{
		$keep_editing_name=$this->GetFieldName('keep_editing');

		$ret=parent::Save();
//		$this->Retrieve();

		if($ret)
		{
			
		}		
		

		global $HTTP_POST_VARS;
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