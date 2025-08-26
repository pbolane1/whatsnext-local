<div class='header_area'>
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
				  <a href="agents.php"><img src='/images/logo.png'></a>
				</div>
			  </div>
			  <div class="navbar-collapse collapse">
			    <ul class="nav navbar-nav">
			      <li><a href="index.php"><?php echo($__DEV__?'DEV':$__VERSION__)?></a></li>
			      <li><a href="agents.php">Agents</a></li>
			      <li><a href="coordinators.php">Coordinators</a></li>
			      <li><a href="templates.php">Timeline Templates</a></li>
				  <li class="dropdown"><a href='#' class="dropdown-toggle" data-toggle="dropdown">System Settings</a>
				  	<ul class="dropdown-menu">
				      <li><a href="contract_dates.php">Contract Dates</a></li>
				      <li><a href="conditions.php">Conditions</a></li>
				      <li><a href="holidays.php">Holidays</a></li>
<?php
	$demo=new agent();
	$demo->CreateFromKeys('agent_special','DEFAULTS');
	echo("<li><a target='_blank' href='/pages/agents/?action=login_as&agent_id=".$demo->id."'>Manage Default Agent Account</a></li>");
?>	
					</ul>
				  </li>
				  <li class="dropdown"><a href='#' class="dropdown-toggle" data-toggle="dropdown">UX Settings</a>
				  	<ul class="dropdown-menu">
				      <li><a href="discount_codes.php">Discount Codes</a></li>
				      <li><a href="animations.php">Animations</a></li>
				      <li><a href="sounds.php">Sounds</a></li>
				      <li><a href="info_bubbles.php">Info Popups</a></li>
					</ul>
				  </li>
			    </ul>
			  </div>
			</nav>				
		</div>
	</div>
</div>	

<script>
	$(document).on('click', '.dropdown-toggle', function (e) {
		e.preventDefault();
		$(this).dropdown('toggle');
	});
</script>

<div class='headline'>	
	<h1><?php echo(isset($__headline__)?$__headline__:'Administration');?></h1>
</div>	

<!--POPUP-->
<div class="modal fade" id="popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
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