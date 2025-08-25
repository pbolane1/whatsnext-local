<?php

class agent_link extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('agent_links','agent_link_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function Generate($agent_id,$page,$expires_days=3)
	{
		do
		{
			$this->Set('agent_link_hash',Text::GenerateCode(32,40));		
		}while(!$this->ValidateUnique('agent_link_hash'));

		$this->Set('agent_link_page',$page);										//page to redirect to
		$this->Set('agent_link_expires',time()+($expires_days*24*60*60));			//3 day lifetime default
		$this->Set('agent_id',$agent_id);		
		$this->Update();


		return _navigation::GetBaseURL().'agents/login/'.$this->Get('agent_link_hash').'/';
	}
};

?>