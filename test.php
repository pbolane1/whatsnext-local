<?php
	include('include/common.php');

echo('1:'._navigation::GetBasePath());
echo("<br>");
echo('2:'._navigation::GetBaseURL());
echo("<br>");


foreach($_SERVER as $k=>$v)
	echo('SERVER['.$k.']='.$v.'<br>');

?>