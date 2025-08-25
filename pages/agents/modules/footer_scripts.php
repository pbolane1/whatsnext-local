<?php
	if($agent->IsLoggedIn())
		$agent->FooterScripts($HTTP_GET_VARS);
?>