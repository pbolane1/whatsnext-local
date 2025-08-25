<link href="<?php echo(file::FileNameNoCache(_navigation::GetBasePath(),'/admin/css/admin.css'))?>" rel="stylesheet" type="text/css">

<script type="text/javascript" language="JavaScript" src="/admin/js/tiny_mce/tiny_mce.js"></script>

<link href="/css/calendar.css" rel="stylesheet" type="text/css">
<script type="text/javascript" language="JavaScript" src="/js/calendar.js"></script>

<?php
	Javascript::IncludeJS('AjaxRequest.js');
	Javascript::IncludeJS('drop_sort.js');	 
	Javascript::IncludeJS('selectbox.js');
	javascript::IncludeJS('listing_effects.js');
?>