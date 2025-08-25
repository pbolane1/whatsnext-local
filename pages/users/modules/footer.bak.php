<?php
 	echo("<div id='progress_meter_container_mobile'>");
	if($user_contact->IsLoggedIn())
		$user->ProgressMeterMobile($HTTP_GET_VARS);
	echo("</div>");
?>

<div class="scroll_to_top">
	<a href="#" onclick="$('HTML,BODY').animate({scrollTop:'0'});return false;"><i class="fas fa-arrow-up"></i></a></div>
</div>

<div class='footer'>
	<div class='container'>
		<div class='row'>
			<div class='col-md-4 left'>
				<?php
					$user->DrawFooter1()
				?>
			</div>
			<div class='col-md-4 center'>
				<img src='/images/eh6.png'><img src='/images/realtor5.png'><br>
				<br>
				Copyright &copy; <a href='https://whatsnext.realestate'>What's Next App, LLC</a><br>				
			</div>
			<div class='col-md-4 right'>
				<?php
					$user->DrawFooter2()
				?>
			</div>
		</div>			
	</div>
</div>	
<div class='disclaimer'>
	<div class='container'>
	The purpose of this website is solely for informational purposes and is not intended to provide professional, financial, or legal advice. Should you require guidance on any aspect of your purchase or sale, What's Next advises that you seek assistance from your real estate agent, financial advisor, accountant, and/or legal counsel. It should be noted that the information has been obtained from trustworthy sources, but its accuracy cannot be guaranteed and may contain errors or omissions. All aspects of a real estate transaction are subject to change. You should perform your own investigations as to the accuracy of the provided information and you should always consult the appropriate professional instead of acting on any information presented on this site. By using this site, you are indicating that you have read and agree to the above disclaimer as well as our <a href="https://whatsnext.realestate/terms-of-service/" target="_blank">Terms of Service</a>.
</div>

<!--  HIDE FOR NOW 
<div class='copyright'>
	<div class='container'>
		All Rights Reserved.  All information should be independently reviewed and verified for accuracy. <br>			
	</div>
</div>
-->

<?php
	$user->DrawFlares();
	$user_contact->TOS();
?>