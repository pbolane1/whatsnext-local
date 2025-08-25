<?php
for ($i=0;$i<sizeof($dirs);$i++) {
	echo "<a href='#' onClick='delete_folder(\"" . $dirs[$i] . "\")'><img border=0 src='" . $delete_image . "'></a> <img src='" . $folder_small_image . "'> <a class='dir' href='?type=" . $type . "&dir=" . $requested_dir . "/" . $dirs[$i] . "'>" . $dirs[$i] . "</a><br>\n";
}
for ($i=0;$i<sizeof($files);$i++) {
	echo "<a href='#' onClick='delete_file(\"" . $files[$i] . "\")'><img border=0 src='" . $delete_image . "'></a> <img src='" . $file_small_image . "'> <a class='file' href='#' onClick='fileSelected(\"" . $requested_dir . "/" . $files[$i] . "\");'>" . $files[$i] . "</a><br>\n";
}
?>