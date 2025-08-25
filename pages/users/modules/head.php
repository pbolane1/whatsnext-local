<link href="<?php echo(file::FileNameNoCache(_navigation::GetBasePath(),'/admin/css/admin.css'))?>" rel="stylesheet" type="text/css">
<link href="<?php echo(file::FileNameNoCache(_navigation::GetBasePath(),'/users/css/global.css'))?>" rel="stylesheet" type="text/css">
<link href="<?php echo(file::FileNameNoCache(_navigation::GetBasePath(),'/users/css/medium.css'))?>" rel="stylesheet" type="text/css">
<link href="<?php echo(file::FileNameNoCache(_navigation::GetBasePath(),'/users/css/small.css'))?>" rel="stylesheet" type="text/css">
<link href="<?php echo(file::FileNameNoCache(_navigation::GetBasePath(),'/users/css/xsmall.css'))?>" rel="stylesheet" type="text/css">

<?php 
	$user->CustomCSS();
?>