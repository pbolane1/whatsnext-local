<?php include('../../include/common.php') ?>
<?php
	$user=new user($HTTP_GET_VARS['user_id']);
	$user_contact=new user_contact($HTTP_GET_VARS['user_contact_id']);

	$user->DisplayICal(array('user_id'=>$user->id,'for'=>'USER','user_contact_id'=>$user_contact->id,'type'=>$HTTP_GET_VARS['type']));

?>
