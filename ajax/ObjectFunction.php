<?php include("../include/common.php")?>
<?php include("../include/_admin.php")?>
<?php include("../include/_coordinator.php")?>
<?php include("../include/_agent.php")?>
<?php include("../include/_user_contact.php")?>
<?php include('../pages/agents/include/wysiwyg_settings.php') ?>

<?php	
	// Date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
	// always modified
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	// HTTP/1.0
	header("Pragma: no-cache");
	//XML Header
//	header("content-type:text/xml");	
	
	$function=$HTTP_GET_VARS['object_function'];
	$classname=$HTTP_GET_VARS['object'];
	$object=new $classname($HTTP_GET_VARS['object_id']);
	$object->$function($HTTP_GET_VARS);

?>