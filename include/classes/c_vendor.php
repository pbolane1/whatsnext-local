<?php

class vendor extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('vendors','vendor_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{

		}
 	}

	public function GetType()
	{
		$vendor_type=new vendor_type($this->Get('vendor_type_id'));
		return $vendor_type->Get('vendor_type_name');
	}

	public function xGetTypes()
	{
		$types=array();
		$types['Escrow']='ESCROW';
		$types['Inspector']='INSPECTOR';
		$types['Lender']='LENDER';
		$types['Termite Inspector']='TERMITE';
		return $types;
	}

	public function xGetType()
	{
		$types=$this->GetTypes();
		$types=array_flip($types);
		return $types[$this->Get('vendor_type')];
	}

	public function GetFullName()
	{
		return $this->Get('vendor_name');
	}

	public function GatherInputs()
	{	 
		global $HTTP_POST_VARS;

		parent::GatherInputs();

		$this->Set('vendor_phone',$this->NormalizePhone($this->Get('vendor_phone')));
		
		//not ideal....
		if(!$this->id)
			$this->Set($this->GetCurrentUser()->primary,$this->GetCurrentUser()->id);
	}

	public function Delete()
	{
		parent::Delete();
	}

	public function ValidateInputs()
	{
		global $HTTP_POST_VARS;
		
		if($this->GetFlag('ALLOW_BLANK'))
			return true;


		return count($this->errors)==0;
 	}

	public function Save()
	{	  	  
		global $HTTP_POST_VARS;

		$old=new user($this->id);
	  	$new=!$this->id;
		$psv=parent::Save();
		if($psv)
		{
			$this->Update();
			
			$this->saved=true;			
		}
		return count($this->errors)==0;
	}

};

?>