<?php

	wysiwyg::SetFileManager('/agents/js/wysiwyg_upload.php');
	wysiwyg::SetPath(_navigation::GetBaseURL().'bootstrap/css/bootstrap.min.css,/css/'.file::FileNameNoCache(_navigation::GetBasePath().'/css/','global.css'));
	$_wysisyg_styles=array();
	wysiwyg::AddMCEStyle('General','wysiwyg-body');
	wysiwyg::AddMCEStyle('Image - Full Width','fullwidth');
	wysiwyg::AddMCEStyle('Image - Float Left','floatleft');
	wysiwyg::AddMCEStyle('Image - Float Right','floatright');
	wysiwyg::AddMCEStyle('Link - Button','button');
	wysiwyg::AddMCEStyle('Link - Button 2','button2');

	$wysiwyg_std='';
	$wysiwyg_std.="plugins : 'style,layer,save,advimage,advlink,iespell,insertdatetime,preview,media,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,spellchecker,inlinepopups',\r\n";
   	$wysiwyg_std.="dialog_type : 'modal',\r\n";
	$wysiwyg_std.="theme_advanced_disable: 'contextmenu,hr,anchor,sub,sup,visualaid',"; 
	$wysiwyg_std.="theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
	$wysiwyg_std.="theme_advanced_buttons1_add_before : 'pastetext,pasteword,|,undo,redo,|',\r\n";
	$wysiwyg_std.="theme_advanced_buttons1_add : '|,indent,outdent,bullist,numlist,|,link,unlink,image,|,charmap,|,removeformat,|,cleanup,|,code',\r\n";
	$wysiwyg_std.="theme_advanced_buttons2 : '',\r\n";
	$wysiwyg_std.="theme_advanced_buttons3 : '',\r\n";
	$wysiwyg_std.="theme_advanced_buttons3_add_before : '',\r\n";
	$wysiwyg_std.="theme_advanced_buttons3_add : '',\r\n";
	$wysiwyg_std.="theme_advanced_buttons4 : '',\r\n";
   	$wysiwyg_std.="force_br_newlines : true,\r\n";
   	$wysiwyg_std.="force_p_newlines : false,\r\n";
	//cleanup on paste.
   	$wysiwyg_std.="paste_auto_cleanup_on_paste : true,\r\n";
   	$wysiwyg_std.="paste_remove_styles: true,\r\n";
   	$wysiwyg_std.="paste_remove_styles_if_webkit: true,\r\n";
   	$wysiwyg_std.="paste_strip_class_attributes: true,\r\n";
   	$wysiwyg_std.="spellchecker_languages : '+English=en',\r\n";
	wysiwyg::RegisterMode('FULL',$wysiwyg_std);
	wysiwyg::RegisterMode('ADVANCED',$wysiwyg_std);

	$wysiwyg_std2='';
	$wysiwyg_std2.="plugins : 'layer,table,save,advimage,advlink,iespell,insertdatetime,preview,media,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\r\n";
	$wysiwyg_std2.="theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
	$wysiwyg_std2.="theme_advanced_disable: 'styleselect,contextmenu,table,style,styleprops,hr,anchor,delete_col,delete_row,col_after,col_before,row_after,row_before,row_after,row_before,split_cells,merge_cells,sub,sup,visualaid',\r\n"; 
	$wysiwyg_std2.="theme_advanced_buttons1: 'undo,redo,separator,bold,italic,underline,strikethrough,|,link,unlink',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons1_add_before : '',\r\n";
 	$wysiwyg_std2.="theme_advanced_buttons1_add : '',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons2 : 'formatselect,|,justifyleft,justifycenter,justifyright,justifyfull',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons3_add_before : '',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons3_add : '',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons3 : '',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons4 : '',\r\n";
	wysiwyg::RegisterMode('SIMPLE_LINK_HEADLINES',$wysiwyg_std2);

	$wysiwyg_std2='';
	$wysiwyg_std2.="plugins : 'layer,table,save,advimage,advlink,iespell,insertdatetime,preview,media,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\r\n";
	$wysiwyg_std2.="theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
	$wysiwyg_std2.="theme_advanced_disable: 'styleselect,contextmenu,table,style,styleprops,hr,anchor,delete_col,delete_row,col_after,col_before,row_after,row_before,row_after,row_before,split_cells,merge_cells,sub,sup,visualaid',"; 
	$wysiwyg_std2.="theme_advanced_buttons1: 'undo,redo,separator,bold,italic,underline,strikethrough,|,link,unlink,|,bullist,numlist',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons1_add_before : '',\r\n";
 	$wysiwyg_std2.="theme_advanced_buttons1_add : '',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons2 : 'formatselect,|,justifyleft,justifycenter,justifyright,justifyfull',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons3_add_before : '',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons3_add : '',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons3 : '',\r\n";
	$wysiwyg_std2.="theme_advanced_buttons4 : '',\r\n";
	wysiwyg::RegisterMode('LINK_BULLET_HEADLINES',$wysiwyg_std2);


?>