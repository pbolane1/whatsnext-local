<?php

class template_to_transaction_handler extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('templates_to_transaction_handlers','template_to_transaction_handler_id');
		$this->Retrieve();
	}
};

?>