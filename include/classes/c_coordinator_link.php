<?php

class coordinator_link extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('coordinator_links','coordinator_link_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function Generate($coordinator_id,$page,$expires_days=3)
	{
		do
		{
			$this->Set('coordinator_link_hash',Text::GenerateCode(32,40));		
		}while(!$this->ValidateUnique('coordinator_link_hash'));

		$this->Set('coordinator_link_page',$page);										//page to redirect to
		$this->Set('coordinator_link_expires',time()+($expires_days*24*60*60));			//3 day lifetime default
		$this->Set('coordinator_id',$coordinator_id);		
		$this->Update();


		return _navigation::GetBaseURL().'coordinators/login/'.$this->Get('coordinator_link_hash').'/';
	}
};

?>