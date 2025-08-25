<?php
	include('../include/common.php');

	$content=new content($HTTP_GET_VARS['content_id']);
	if($HTTP_GET_VARS['content_area'])
		$content->InitByKeys(array('content_area'),array($HTTP_GET_VARS['content_area'],'/'));
	if(!$content->id)
		$content->InitByKeys(array('content_url','content_url_slug'),array(trim($HTTP_GET_VARS['content_url'],'/'),trim($HTTP_GET_VARS['content_url_slug'],'/')));
	if($content->Get('content_external_url'))
		_navigation::Redirect($content->Get('content_external_url'));
//	$content->CreateFromKeys('content_url',$HTTP_GET_VARS['content_url']);

?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo($content->GetMetaTitle());?></title>
<meta name="description" content="<?php echo($content->GetMetaDescription());?>">
<meta name="keywords" content="<?php echo($content->GetMetaKeywords());?>">
<?php include ('../modules/head.php');?>
</head>

<body class='<?php echo($content->Get('content_file')?'':'no_banner');?>'>
	<?php include ('../modules/header.php');?>
	<?php include ('../modules/nav.php');?>
	<div class='content_area'>	
		<div class='container content_wrapper'>
			<div class='content_header'><?php $content->DisplayBanner()?></div>		
			<div class='content_inner'>
<?php
	$content->Display();
?>				
			</div>
		</div>
	</div>
	
	<?php include ('../modules/footer.php');?>
	<?php include ('../modules/footer_scripts.php');?>
</body>
</html>