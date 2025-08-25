<?php include('../../include/common.php') ?>
<?php
	$user=new user($HTTP_GET_VARS['user_id']);
	$coordinator=new coordinator($HTTP_GET_VARS['coordinator_id']);
	$coordinator->DisplayICal(array('user_id'=>$user->id,'for'=>'COORDINATOR','coordinator_id'=>$coordinator->id,'type'=>$HTTP_GET_VARS['type']));
?>