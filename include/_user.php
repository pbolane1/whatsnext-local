<?php
	//the logged in contact
	$user_contact=new user_contact(Session::Get('user_contact_id'));	
	$user_contact->ProcessLogin();		

	//their user account
	$user=new user($user_contact->Get('user_id'));	
?>