<?php
//TODO it woudn't be bad to check for admin rights here ;)
set_time_limit(0);
ini_set("max_input_time", "600");
ini_set("max_execution_time", "600");
ini_set("memory_limit", "104857600");
ini_set("upload_max_filesize", "104857600");
ini_set("post_max_size", "104857600");

require("../../../include/common.php");
require("../../../include/_agent.php");
require("../../../include/_coordinator.php");
if(!$agent->IsLoggedIn() and !$coordinator->IsLoggedIn())
	die();
require("file_manager_config.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $strings["title"]; ?></title>

<link href="file_manager/styles.css" rel="stylesheet" type="text/css">
<?php
require("file_manager/utils.php");
if (isSet($_REQUEST["type"])) {
	$type = $_REQUEST["type"];
}
else {
	$type = -2;
	die('incorrect access');
}
$default_dir = ".";
$ext = array("*");
$url_dir = "";
if ($type != -2) {
	if (isSet($settings[$type])) {
		$default_dir = $settings[$type]["dir"];
		$url_dir = $settings[$type]["url_dir"];
		$ext = $settings[$type]["ext"];
	}
}

if (!isSet($_REQUEST["dir"]) || strlen($_REQUEST["dir"]) == 0) {
	$dir = $default_dir;
	$requested_dir = "";
}
else {
	$requested_dir = $_REQUEST["dir"];
	$dir = $default_dir . "/" . $requested_dir;
}
if (strpos($dir, "..") > 0) //'..' in our path is a big no-no
	$dir = $default_dir;

$errors=array();
$warnings=array();
$messages=array();
if (isSet($_REQUEST["action"]))
{
	if ($_REQUEST["action"] == "upload_file")
	{
	  	$file=$_FILES["uploaded_file"];
	  	$path=$default_dir.$requested_dir.'/';
	  	$allow_types=$ext;
	  	$maxsize_ks=0;
	  	$overwrite=false;
	  	$new_name=mod_rewrite::ToURL($_FILES["uploaded_file"]["name"],'A-Z,a-z,0-9,.-.');
		if(file_exists($path.$new_name))
			$new_name=mktime().'_'.$new_name;
		if(file::Upload($file,$path,$allow_types,$maxsize_ks,$overwrite,$new_name))	  	  
		{
			$messages[]=$new_name." Uploaded";
			chmod($path.$new_name,0644);						
			if($image_max_width and $image_max_height)
			  	imaging::Resize($new_name,$new_name,$path,$image_max_width,$image_max_height);
		}
		else
			$errors[]=file::GetError();
	}
	else if ($_REQUEST["action"] == "create_dir")
	{
		$dir_name=$_REQUEST["dir_name"];
	  	$dir_name=mod_rewrite::ToURL($dir_name,'A-Z,a-z,0-9');
		if (@mkdir($default_dir . "/" . $requested_dir . "/" . $dir_name) === FALSE)
			$errors[]="Could Not Create Directory ".$dir_name;
		else
		{
			$messages[]="Directory ".$dir_name." Created";
			chmod($default_dir . "/" . $requested_dir . "/" . $dir_name,0777);
		}

	}
	else if ($_REQUEST["action"] == "delete_folder" || $_REQUEST["action"] == "delete_file")
	{
		@rmdirr($_REQUEST["item_name"]);
		$messages[]=$_REQUEST["item_name"]." Removed";
	}
}

?>
<script>
function fileSelected(filename) {
	//let our opener know what we want
	window.top.opener.my_win.document.getElementById(window.top.opener.my_field).value
= "<?php echo $url_dir; ?>" + filename;
	window.top.opener.my_win.document.getElementById(window.top.opener.my_field).onchange();
	//we close ourself, cause we don't need us anymore ;)
	window.close();
}

function switchDivs() {
	document.getElementById("upload_div").style.display = "none";
	document.getElementById("uploading_div").style.display = "block";
	return true;
}
</script>
</head>
<body>
<div style='overflow:auto;height:500px;'>
<?php
	if(count($errors))
		echo("<div class='error'>".implode('<br>',$errors)."</div>");
	if(count($warnings))
		echo("<div class='warning'>".implode('<br>',$warnings)."</div>");
	if(count($messages))
		echo("<div class='message'>".implode('<br>',$messages)."</div>");
?>
<table border="0" cellpadding="3" cellspacing="0" width="100%" height="100%">
<tr>
<td class="td_curr_dir" colspan="2"><b><?php echo $strings["curr_dir"]; ?></b><br><?php
if (strlen($requested_dir) > 0)
{
	$requested_dirs = explode("/", $requested_dir);
	$tmp_dirs = "";
	foreach ($requested_dirs AS $tmp_dir)
	{
		if ($tmp_dir != "")
		{
			if ($tmp_dirs == "")
				echo "<a class='back' href='?type=" . $type . "'>/</a>";
			else
				echo "/";
				
			$tmp_dirs .= "/" . $tmp_dir;
			if ($requested_dir == $tmp_dirs)
				echo $tmp_dir;
			else
				echo "<a class='back' href='?type=" . $type . "&dir=" . $tmp_dirs . "'>" . $tmp_dir . "</a>";
		}
	}
}
else
	echo "&nbsp;";
?></td>
</tr>
<tr>
<td align="left" class="td_back">
<?php
if (strlen($requested_dir) > 0) {
	$last_pos = strrpos($requested_dir, "/");
	$prev_dir = "";
	if ($last_pos !== FALSE && $last_pos > 0)
		$prev_dir = substr($requested_dir, 0, $last_pos);
	?>
	<a class="back" href="?type=<?php echo $type; ?>&dir=<?php echo $prev_dir; ?>"><< <?php echo $strings["back"]; ?></a>
	<?php
}
?>
</td>
<td align="right" class="td_close"><a class="close" href="javascript: window.close();"><?php echo $strings["close"]; ?></a></td>
</tr>
<tr>
<td colspan="2" class="td_main" height="100%" valign="top">
<?php

$dirs=array();
$files=array();
$dh  = opendir($dir);


while (false !== ($filename = readdir($dh))) {
	if ($filename != "." && $filename != "..") {
		if (is_dir($dir . "/" . $filename)) {
			$dirs[] = $filename;
		}
		else {
			if (sizeof($ext) > 0) {
				for ($i=0;$i<sizeof($ext);$i++) {
					if ($ext[$i] == "*" || (strtolower($ext[$i]) == strtolower(substr($filename, -strlen($ext[$i]))))) {
						$files[] = $filename;
						break;
					}
				}
			}
			else {
				$files[] = $filename;
			}
		}
	}
}

include($types[$settings[$type]["type"]]);

?>
</td>
</tr>
</table>
</div>
<table border="0" cellpadding="3" cellspacing="0" width="100%" height="100%">
<tr>
<td valign="top">
	<?php echo $strings["create_dir"]; ?>
	<form method="post">
		<input type="hidden" name="action" value="create_dir">
		<input type="text" name="dir_name">
		<input type="submit" value="<?php echo $strings["create_dir_submit"]; ?>">
	</form>
</td>
<td valign="top">
	<div id="upload_div" style="display: block;">
	<?php echo $strings["upload_file"]; ?>
	<form method="post" enctype="multipart/form-data" onSubmit="switchDivs();">
		<input type="hidden" name="action" value="upload_file">
		<input type="hidden" name="MAX_FILE_SIZE" value="104857600" /> <!-- ~100mb -->
		<input type="file" name="uploaded_file">
		<input type="submit" value="<?php echo $strings["upload_file_submit"]; ?>">
	</form>
	</div>
	<div id="uploading_div" style="display: none;">
	<?php echo $strings["sending"]; ?>
	</div>
</td>
</tr>
</table>

<script>
function delete_folder(dir_name)
{
	document.getElementById("hidden_action").value = "delete_folder";
	document.getElementById("hidden_item_name").value = "<?php echo $dir . "/"; ?>" + dir_name;
	document.getElementById("hidden_form").submit();
}
function delete_file(file_name)
{
	document.getElementById("hidden_action").value = "delete_file";
	document.getElementById("hidden_item_name").value = "<?php echo $dir . "/"; ?>" + file_name;
	document.getElementById("hidden_form").submit();
}
</script>
<div style="display: none;">
	<form method="post" id="hidden_form">
	<input type="hidden" name="action" id="hidden_action" value="">
	<input type="hidden" name="item_name" id="hidden_item_name" value="">
	</form>
</div>

</body>
</html>