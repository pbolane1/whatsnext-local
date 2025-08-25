<?php header("HTTP/1.0 200 OK"); ?>
<html>
<head>
<title>response</title>
</head>
<body>
<?php
	include('../include/common.php');
	
	//params to pass for seteach
	$set=array();
	for($i=0;$i<count($seteach);$i++)
		$set[$seteach[$i]]=$seteachas[$i];

	//the jupload processor
  	$list=new DBRowSetEx($tablename,$primary,$classname,stripslashes($where),$order,$limit,$start);
	$list->SaveMultiUpload($filefield,$path,$sortfield,$set,$imagesonly,$validationfunction);
	
	
	
?>

</body>
</html>