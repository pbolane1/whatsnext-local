<!--bootstrap-->
<script src="/bootstrap/js/bootstrap.min.js"></script>

<!-- Bootstrap Colorpicker JavaScript -->
<script src="/vendors/Color-Picker-Plugin-jQuery-MiniColors/jquery.minicolors.js"></script>

<!-- lottie -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<!-- better progress bar -->
<script src="/js/progresssbar.js"></script>

<!--DEV-->
<?php
	if($__DEV__)
	{
		Javascript::Begin();
		echo("jQuery('BODY').addClass('dev');");
		echo("jQuery('BODY').append('<div class=\'dev_notice\'>DEV</div>');");
		Javascript::End();
	}
?>