<?php

class discount_code extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('discount_codes','discount_code_id');
		$this->Retrieve();
	}
	
	public function DisplayEditable()
	{
	 	echo("<td>".$this->Get('discount_code_name')."</td>");
	 	echo("<td>".$this->Get('discount_code_code')."</td>");
	}
			
	public function EditForm()
	{
		global $HTTP_POST_VARS;

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");

		echo("<tr><td class='label'>Discount<div class='hint'></div></td><td colspan='3'>");
		form::DrawTextInput($this->GetFieldName('discount_code_name'),$this->Get('discount_code_name'),array('class'=>$this->GetError('discount_code_name')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Code<div class='hint'></div></td><td colspan='3'>");
		form::DrawTextInput($this->GetFieldName('discount_code_code'),$this->Get('discount_code_code'),array('class'=>$this->GetError('discount_code_code')?'error':'text'));
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
		if(!$this->Get('discount_code_name'))
			$this->LogError('Please Enter a Name For the Item','discount_code_name');
		if(!$this->Get('discount_code_code'))
			$this->LogError('Please Enter Code','discount_code_code');
		if(!$this->ValidateUnique('discount_code_code'))
			$this->LogError('Discount Code Already Exsits','discount_code_code');
	
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