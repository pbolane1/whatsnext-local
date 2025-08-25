<?php

class condition_to_contract_date extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('conditions_to_contract_dates','condition_to_contract_date_id');
		$this->Retrieve();
	}

	function GetActions()
	{
		$actions=array();
		$actions['HIDE']='DISABLE Contract Item and Related Timeline Items';
		$actions['SHOW']='ENABLE Contract Item and Related Timeline Items';
		return $actions;	
	}
};

?>