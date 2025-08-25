<?php
	//the one coordinator
	$coordinator=new coordinator(Session::Get('coordinator_id'));	
	$coordinator->ProcessLogin();		
?>