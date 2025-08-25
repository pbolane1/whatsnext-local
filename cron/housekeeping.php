<?php $__CRON__=1; ?>
<?php 
	//SERVER ENVIRONMENT ~ CLI.
	if(!$_SERVER['HTTP_HOST'])
	{
		$HTTP_SERVER_VARS['HTTP_HOST']='https://app.whatsnext.realestate/';
		$HTTP_SERVER_VARS['DOCUMENT_ROOT']='/home/pbolane1/public_html/app.whatsnext.realestate/';
		$HTTP_SERVER_VARS['HTTPS']='on';
		$_SERVER['HTTP_HOST']='https://app.whatsnext.realestate/';
		$_SERVER['DOCUMENT_ROOT']='/home/pbolane1/public_html/app.whatsnext.realestate/';
		$_SERVER['HTTPS']='on';
	}

	//COMMON
	require('../include/common.php');

	$mintime=time()-30*24*60*60;

	database::query("DELETE FROM agent_links WHERE agent_link_expires<".$mintime);
	database::query("DELETE FROM user_links WHERE user_link_expires<".$mintime);
	database::query("DELETE FROM coordinator_links WHERE coordinator_link_expires<".$mintime);

?>