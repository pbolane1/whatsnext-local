<?php

class user_to_transaction_handler extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('users_to_transaction_handlers','user_to_transaction_handler_id');
		$this->Retrieve();
	}
	

	public function GatherInputs()
	{
		global $HTTP_POST_VARS;

		$settings=json_decode($this->Get('user_to_transaction_handler_settings'),true);
		if($HTTP_POST_VARS['notifications'])
		{
			$settings['notifications']=$HTTP_POST_VARS['notifications'];

			$this->Set('user_to_transaction_handler_settings',json_encode($settings));
			$this->Set('user_to_transaction_handler_settings_updated',time());			
		}
			

	}	
};

?>