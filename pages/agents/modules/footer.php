<div class="scroll_to_top">
	<a href="#" onclick="$('HTML,BODY').animate({scrollTop:'0'});return false;"><i class="fas fa-arrow-up"></i></a></div>
</div>


<!-- CHATBOT -->
<?php	if($agent->IsLoggedIn()){   ?>
<script src='https://cdn.jotfor.ms/agent/embedjs/0196daad8b647851bcc47848f75be98c1e45/embed.js?skipWelcome=1&maximizable=1'></script>
<?php	}   ?>


<?php	
	if($agent->IsProxyLogIn())
	{
		echo("<div class='proxy_notice'>Managing ".$agent->get('agent_name')." Account. <a href='/pages/agents/index.php?action=logout' style='color: #000000; text-decoration: underline; font-weight: bold;'>Exit Account and Return to Coordinator Dashboard</a></div>");
		javascript::Begin();
		echo("jQuery(function(){
			jQuery('BODY').addClass('proxy');	
		});");
		javascript::End();		
	}
?>

<!-- Agent Info Section -->
<div class="agent_info_section agent_border_color1">
	<div class="container">
		<div class="agent_info_content">
			<!-- Left Side - Agent Information -->
			<div class="agent_info_left">
				<?php if($agent->Get('agent_image_file3')): ?>
					<div class="agent_headshot">
						<img src="<?php echo $agent->GetThumb(120,120,false,'agent_image_file3',true); ?>" alt="Agent Headshot">
					</div>
				<?php endif; ?>
				<div class="agent_details">
					<div class="agent_name"><?php echo $agent->Get('agent_name') ?: 'Agent Name'; ?></div>
					<div class="agent_company"><?php echo $agent->Get('agent_company') ?: 'Company Name'; ?></div>
					<div class="agent_dre">DRE# <?php echo $agent->Get('agent_number') ?: '00000000'; ?></div>
					<div class="agent_phone"><?php echo $agent->Get('agent_cellphone') ?: 'Phone Number'; ?></div>
				</div>
			</div>
			
			<!-- Middle Spacer -->
			<div class="agent_info_spacer"></div>
			
			<!-- Right Side - Company Logo and Address -->
			<div class="agent_info_right">
				<?php if($agent->Get('agent_image_file2')): ?>
					<div class="company_logo">
						<img src="<?php echo $agent->GetThumb(200,65,false,'agent_image_file2',true); ?>" alt="Company Logo">
					</div>
				<?php endif; ?>
				<div class="company_address">
					<?php 
					$address = $agent->Get('agent_address') ?: 'Company Address';
					echo nl2br($address);
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class='disclaimer'>
	<div class='container'>
	The purpose of this website is solely for informational purposes and does not constitute legal advice. The concepts presented here, legal or otherwise, are a broad overview and may not apply to your circumstances or transaction.  While What's Next App, LLC. makes every attempt to automatically calculate due dates so they don't land on weekends or holidays, please note that you should double-check all for accuracy as official holidays vary by county. Should you require guidance on any aspect of your purchase or sale, What's Next App, LLC. advises that you seek assistance from your agent, personal advisor, and/or legal counsel. It should be noted that the information has been obtained from trustworthy sources, but its accuracy cannot be guaranteed and may contain errors and/or omissions. As all aspects of a real estate transaction are unique and subject to change, this information should only be used as a rough guide. You should perform your investigations to ensure the provided information's accuracy, pertinence, and completeness. For more information, visit our <a class="disclink" href='https://whatsnext.realestate/disclaimer/' target='_blank'>Disclaimer</a> and <a href='https://whatsnext.realestate/terms-of-service/' class="disclink" target='_blank'>Terms of Use</a>.
 
	</div>
</div>


 <nav id='footmenu'>
    <ul>
      <li><a href='/agents/index.php'>DASHBOARD</a></li>
      <li><a href='/agents/vendors.php'>VENDORS</a></li>
      <li><a href='/agents/past.php'>ARCHIVED</a></li>
      <li><a href='/agents/templates.php'>TEMPLATES</a></li>
      <li><a href='/agents/settings.php'>SETTINGS</a></li>
      <li><a href='https://whatsnext.realestate/privacy-policy/' target='_blank'>PRIVACY POLICY</a></li>
      <li><a href='https://whatsnext.realestate/disclaimer/' target='_blank'>Disclaimer</a></li>
      <li><a href='https://whatsnext.realestate/terms-of-service/' target='_blank'>TERMS OF USE</a></li>
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
	$agent->DrawFlares();
	$agent->TOS();
?>