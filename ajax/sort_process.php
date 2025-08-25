<?php
	include('../include/common.php');

	if(!$tablename or !$primary or !$classname)
		return;

  	$list=new DBRowSet($HTTP_POST_VARS['tablename'],$HTTP_POST_VARS['primary'],$HTTP_POST_VARS['classname'],stripslashes($HTTP_POST_VARS['where']),$HTTP_POST_VARS['order'],$HTTP_POST_VARS['limit'],$HTTP_POST_VARS['start']);
  	$list->Retrieve();
  	$list->SetFlag('DROPSORT_SAVE');
  	$list->ProcessAction();
  	$list->CheckSortOrder();
?>