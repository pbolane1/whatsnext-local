<?php

class user_condition extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('user_conditions','user_condition_id');
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
	
	public function Save()
	{
		parent::Save();
	}
};

?>