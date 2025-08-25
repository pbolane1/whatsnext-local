<?php
 	echo("<div id='progress_meter_container_mobile'>");
	if($user_contact->IsLoggedIn())
		$user->ProgressMeterMobile($HTTP_GET_VARS);
	echo("</div>");
?>

<div class="scroll_to_top">
	<a href="#" onclick="$('HTML,BODY').animate({scrollTop:'0'});return false;"><i class="fas fa-arrow-up"></i></a></div>
</div>


<div class='disclaimer'>
	<div class='container'>
	The purpose of this website is solely for informational purposes and does not constitute legal advice. The concepts presented here, legal or otherwise, are a broad overview and may not apply to your circumstances or transaction.  While What's Next App, LLC. makes every attempt to automatically calculate due dates so they don't land on weekends or holidays, please note that you should double-check all for accuracy as official holidays vary by county. Should you require guidance on any aspect of your purchase or sale, What's Next App, LLC. advises that you seek assistance from your agent, personal advisor, and/or legal counsel. It should be noted that the information has been obtained from trustworthy sources, but its accuracy cannot be guaranteed and may contain errors and/or omissions. As all aspects of a real estate transaction are unique and subject to change, this information should only be used as a rough guide. You should perform your investigations to ensure the provided information's accuracy, pertinence, and completeness. For more information, visit our <a class="disclink" href='https://whatsnext.realestate/disclaimer/' target='_blank'>Disclaimer</a> and <a class="disclink" href='https://whatsnext.realestate/terms-of-service/' target='_blank'>Terms of Use</a>.
		</div>
</div>


<nav id='footmenu'>
  <ul>
    <li><a href='/users/index.php'>Timeline</a></li>
    <li><a href='/users/settings.php'>Settings</a></li>
    <li><a href='https://whatsnext.realestate/privacy-policy/' target='_blank'>Privacy Policy</a></li>
	<li><a href='https://whatsnext.realestate/disclaimer/' target='_blank'>Disclaimer</a></li>
    <li><a href='https://whatsnext.realestate/terms-of-service/' target='_blank'>Terms of Use</a></li>
	<li><a href='https://whatsnext.realestate/contact/' target='_blank'>CONTACT</a></li>
  </ul>
</nav>




<div class="copyright">
	<div class="container">
		Copyright © What's Next App, LLC. &nbsp;All Rights Reserved. <br>			
	</div>
</div>
<div class='site_version'><?php echo($__VERSION__); ?></div>

<?php
	$user->DrawFlares();
	$user_contact->TOS();
?>