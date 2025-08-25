<?php

class vendor_type extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('vendor_types','vendor_type_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function GatherInputs()
	{
		parent::GatherInputs();
 	}  

	public function ValidateInputs()
	{
		parent::ValidateInputs();
		
		if(!$this->Get('vendor_type_name'))
			$this->LogError('Please Type Of Vendor','vendor_type_name');

		return count($this->errors)==0;
 	}  
	
	public function Delete()
	{
		parent::Delete();
	}
	
	public function Save()
	{
		global $HTTP_POST_VARS;
	 	//var_dump($HTTP_POST_VARS);
	 
		parent::Save();

		return count($this->errors)==0;
		//$this->Dump();
	}
};

?>