<?php

class user_link extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('user_links','user_link_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function Generate($user_contact_id,$page='',$expires_days=3)
	{
		do
		{
			$this->Set('user_link_hash',Text::GenerateCode(32,40));		
		}while(!$this->ValidateUnique('user_link_hash'));

		$this->Set('user_link_page',$page);											//page to redirect to.
		$this->Set('user_link_expires',time()+($expires_days*24*60*60));			//3 day lifetime default
		$this->Set('user_contact_id',$user_contact_id);				
		$this->Update();

		
		return _navigation::GetBaseURL().'users/login/'.$this->Get('user_link_hash').'/';
	}
};

?>