<div class='header_area agent_border_color1'>
	<div class='container'>
		<div class='navigation_area'>
			<nav class="navbar navbar-inverse">
			  <div class="navbar-header">
			    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			      <span class="icon-bar"></span>
			      <span class="icon-bar"></span>
			      <span class="icon-bar"></span>
			    </button>
				<div class='logo'>
				  <a href="/pages/agents/"><img src='/images/logo.png'></a>
				</div>
			  </div>
			  <div class="navbar-collapse collapse">
			    <ul class="nav navbar-nav">
<?php
	if($agent->IsLoggedin())			    
	{
		echo("<li><a href='/pages/agents/index.php'>Dashboard</a></li>");
		echo("<li><a href='/pages/agents/vendors.php'>Vendors</a></li>");
		echo("<li><a href='/pages/agents/past.php'>Archived</a></li>");
		echo("<li><a href='/pages/agents/templates.php'>Templates</a></li>");
		echo("<li><a href='/pages/agents/settings.php'>Settings</a></li>");
		if($agent->IsProxyLogin())			    
			echo("<li><a href='/pages/agents/index.php?action=logout'>Exit</a></li>");
		else
			echo("<li><a href='/pages/agents/index.php?action=logout'>Logout</a></li>");
	}
	else
		echo("<li><a href='https://whatsnext.realestate'>Back to Main Site</a></li>");	
?>
			    </ul>
			  </div>
			</nav>				
		</div>
	</div>
</div>
<div class='headline'>	
	<h1><?php echo($__headline__) ?></h1>
</div>	

<!--POPUP-->
<div class="modal fade" id="popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document" id='popup_dialog'>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
				<h4 class="modal-title" id="popup_headline">Modal title</h4>
			</div>
			<div class='modal-body' id='popup_content'>
				<?php //make sure to include body and footer in ajax calls. ?>
			</div>
		</div>
	</div>
</div>

<!--LOADER-->
<div id='loader'></div>