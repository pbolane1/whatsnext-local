<?php
	//the one agent
	$agent=new agent(Session::Get('agent_id'));	
	$agent->ProcessLogin();		
?>