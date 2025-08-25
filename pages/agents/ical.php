<?php include('../../include/common.php') ?>
<?php
	$user=new user($HTTP_GET_VARS['user_id']);
	$agent=new agent($HTTP_GET_VARS['agent_id']);
	$agent->DisplayICal(array('user_id'=>$user->id,'for'=>'AGENT','agent_id'=>$agent->id,'type'=>$HTTP_GET_VARS['type']));
?>