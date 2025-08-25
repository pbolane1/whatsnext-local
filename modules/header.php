<div class='header_area'>
	<div class='container'>
		<div class='row'>
			<div class='col-md-3'>
				<div class='logo'>
					<a href="/"><img src='/images/logo.png'></a>
				</div>
			</div>	
			<div class='col-md-6'>
				<h1><?php echo($__headline__) ?></h1>
			</div>
			<div class='col-md-3'>
				<div class='navigation_area'>
					<nav class="navbar navbar-inverse">
					  <div class="navbar-header">
					    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					      <span class="icon-bar"></span>
					      <span class="icon-bar"></span>
					      <span class="icon-bar"></span>
					    </button>
					  </div>
					  <div class="navbar-collapse collapse">
					    <ul class="nav navbar-nav">
					      <li><a href="#">Dashboard</a></li>
					      <li><a href="#">Vendors</a></li>
					      <li><a href="#">Archived</a></li>
					      <li><a href="#">Templates</a></li>
					      <li><a href="#">Settings</a></li>
					      <li><a href="#">Logout</a></li>
					    </ul>
					  </div>
					</nav>				
				</div>
			</div>
		</div>
	</div>
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


<!--INFOBUBBLE-->
<div id='info_bubble'>
	<div id='info_bubble_content'></div>
</div>


<!--LOADER-->
<div id='loader'></div>
