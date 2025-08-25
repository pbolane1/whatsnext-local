<?php
	if($coordinator->IsLoggedIn())
		$coordinator->FooterScripts($HTTP_GET_VARS);
?>