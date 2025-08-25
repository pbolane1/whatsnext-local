<?php
	//the one admin
	$admin=new admin(Session::Get('admin_id'));	
	$admin->ProcessLogin();		
?>