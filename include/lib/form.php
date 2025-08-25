<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

//
//form namespace
//

class form extends html
{
	static function DrawSelect($name,$list,$sel='',$params=array())
	{
		echo("<select ".form::DoStandardParams($name,$params['id'])." ".form::ProcessParams($params,'select').">");
		form::DrawOptionsFromArray($list,$sel,$params['strict']);
		echo("</select>");
	}

	static function DrawSelectFromSQL($name,$query,$field,$passfield,$sel='',$params=array(),$addtl=array(),$addtl_end=array())
	{
		echo("<select ".form::DoStandardParams($name,$params['id'])." ".form::ProcessParams($params,'select').">");
		form::DrawOptionsFromArray($addtl,$sel,$params['strict']);
	    form::DrawOptionsFromSQL($query,$field,$passfield,$sel,$params['strict']);
		form::DrawOptionsFromArray($addtl_end,$sel,$params['strict']);
		echo("</select>");
 	}

	static function DrawComboBox($name,$list,$sel='',$params=array(),$text='',$new='_new',$params_new='')
	{
		if(!is_array($text)) 	$text=array('before'=>'Existing:','between'=>' or New:','after'=>'');
		if(!is_array($addtl))	$addtl=array(''=>'');//the null option for the following
		if(!$params)			$params=array();
		if(!$params_new)		$params_new=array();
		
		$idnew=$params_new['id']?$params_new['id']:($name.$new);
		$idsel=$params['id']?$params['id']:$name;
		$params_new['onchange'].="document.getElementById('".$idsel."').selectedIndex=0;".$params['onchange'];
		$params['onchange'].="document.getElementById('".$idnew."').value='';";
		//changed 08-23-2008 was:
		//if(!$params_new['onchange']) 	$params_new['onchange']="document.getElementById('".$idsel."').selectedIndex=0;".$params['onchange'];
		//if(!$params['onchange']) 		$params['onchange']="document.getElementById('".$idnew."').value='';";
		//changed to call existing callback -and utilize default combo box behavior
		//if want to completely overrride (like under old), can have return; after your onchange param(s)
		
		echo($text['before']);
		form::DrawSelect($name,$list,$sel,$params);
		echo($text['between']);
		form::DrawTextInput($name.$new,'',$params_new);
		echo($text['after']);
	}

	static function DrawComboBoxFromSQL($name,$query,$field,$passfield,$sel='',$params=array(),$addtl='',$text='',$new='_new',$params_new='')
	{
		if(!is_array($text)) 	$text=array('before'=>'Existing:','between'=>' or New:','after'=>'');
		if(!is_array($addtl))	$addtl=array(''=>'');//the null option for the following
		if(!$params)			$params=array();
		if(!$params_new)		$params_new=array();

		$idnew=$params_new['id']?$params_new['id']:($name.$new);
		$idsel=$params['id']?$params['id']:$name;
		$params_new['onchange'].="document.getElementById('".$idsel."').selectedIndex=0;".$params['onchange'];
		$params['onchange'].="document.getElementById('".$idnew."').value='';";
		//changed 08-23-2008 was:
		//if(!$params_new['onchange']) 	$params_new['onchange']="document.getElementById('".$idsel."').selectedIndex=0;".$params['onchange'];
		//if(!$params['onchange']) 		$params['onchange']="document.getElementById('".$idnew."').value='';";
		//changed to call existing callback -and utilize default combo box behavior
		//if want to completely overrride (like under old), can have return; after your onchange param(s)


		echo($text['before']);
		form::DrawSelectFromSQL($name,$query,$field,$passfield,$sel,$params,$addtl);
		echo($text['between']);
		form::DrawTextInput($name.$new,'',$params_new);
		echo($text['after']);




 	}

	static function ProcessComboBox($name,$new='_new')
	{		
		global $HTTP_POST_VARS,$HTTP_GET_VARS;

		$newname=$name.$new;

		$selected='';
		$entered='';
		$selected=$HTTP_POST_VARS[$name]?$HTTP_POST_VARS[$name]:$HTTP_GET_VARS[$name];
		$entered=$HTTP_POST_VARS[$newname]?$HTTP_POST_VARS[$newname]:$HTTP_GET_VARS[$newname];

		return($entered?$entered:$selected);
		
	}

	static function DrawOptionsFromSQL($query,$field,$passfield,$sel='',$strict=false)
	{
	    $rs = database::query($query);
	    while($record=database::fetch_array($rs))
			form::DrawOption($record[$field],$record[$passfield],$sel,$strict);
	}

	static function DrawOptionsFromArray($array,$sel='',$strict=false)
    {
		if(is_array($array))
		{
			foreach($array as $item=>$val)
				form::DrawOption($item,$val,$sel,$strict);
		}
	}

	static function DrawOption($disp,$val,$sel,$strict=false)
    {
      	if(!is_array($sel))	$sel=array($sel); 
		echo("<option value='".$val."' ".(in_array($val,$sel,$strict)?" SELECTED ":"").">".$disp."</option>");
	}


	static function DrawCheckbox($name,$value='true',$checked=false,$params=array())
	{
		echo("<input".form::DoStandardParams($name,$params['id'],$value,'checkbox')." ".form::ProcessParams($params,'checkbox'));
		if($checked)
		    echo(" CHECKED ");
		echo(html::SelfCloseTags()?"/>":">");
	}

	static function DrawRadioButton($name,$value='true',$checked=false,$params=array())
	{
		echo("<input ".form::DoStandardParams($name,$params['id'],$value,'radio')." ".form::ProcessParams($params,'radio'));
		if($checked)
		    echo(" CHECKED ");
		echo(html::SelfCloseTags()?"/>":">");
	}

	static function DrawInput($type,$name,$value,$params=array())
	{
		echo("<input ".form::DoStandardParams($name,$params['id'],$value,$type)." ".form::ProcessParams($params,$type).(html::SelfCloseTags()?"/>":">"));
 	}

	static function DrawFileInput($name,$value,$params=array())
	{
		form::DrawInput('file',$name,$value,$params);
 	}

	static function DrawTextInput($name,$value,$params=array())
	{
		form::DrawInput('text',$name,$value,$params);
 	}

	static function DrawHiddenInput($name,$value,$params=array())
	{
		form::DrawInput('hidden',$name,$value,$params);
 	}

	static function DrawSubmit($name,$value,$params=array())
	{
		form::DrawInput('submit',$name,$value,$params);
 	}

	static function DrawImageSubmit($name,$image,$params=array())
	{
	  	$params['src']=$image;
		form::DrawInput('image',$name,'',$params);
 	}

	static function DrawReset($name,$value,$params=array())
	{
		form::DrawInput('reset',$name,$value,$params);
 	}

	static function DrawButton($name,$value,$params=array())
	{
		form::DrawInput('button',$name,$value,$params);
 	}

	static function DrawTextArea($name,$value,$params=array())
	{
		echo("<textarea ".form::DoStandardParams($name,$params['id'])." ".form::ProcessParams($params,'textarea').">".$value."</textarea>");
	}
	

	static function DoStandardParams($name,$id='',$value='',$type='')
	{
	  	$id=$id?$id:$name;
		return " ".($type?form::ProcessParam('type',$type):'')." ".form::ProcessParam('name',$name)." ".form::ProcessParam('id',$id)." ".($value!==''?form::ProcessParam('value',$value):'')." ";
 	}

	static function ProcessParams($params,$type='')
	{
		//which ones were specified?
	  	$speced=array();
	  	if(!is_array($params))	$params=array();
	  	
		foreach($params as $k=>$v)
			$speced[]=$k;
			
		//add additional information based on type if we nee4d to	
		if($type and !in_array('class',$speced))
			$params['class']=$type;
		
		//let base class handle parameters list
		return html::ProcessParams($params);
	}

	static function Begin($action,$method='post',$allow_files=false,$params=array())
	{
		echo("<form action='".$action."' ".($allow_files?" enctype='multipart/form-data' ":"")." method='".$method."' ".form::ProcessParams($params).">");
 	}
 	
 	static function End()
 	{
		echo("</form>");
  	}
  	
  	//shorcuts to calendar functions
	static function DrawDateInput($fieldname,$date,$params=array(),$linkjs_extra='')
	{
		calendar::PopUpCalendarField($fieldname,$date,'','','','',$params,$linkjs_extra);	  
	}

	static function DrawTimeSelect($fieldname,$time,$params=array(),$extra_options='')
	{
		calendar::TimeSelect($fieldname,$time,$params,$extra_options);  
	}  	

	static function DrawWYSIYWG($name,$value,$params=array(),$allow_uploads=true,$add_directives='')
	{
		form::DrawTextArea($name,$value,$params);
		form::MakeWYSIWYG($name,'',$allow_uploads,$add_directives);
	}

	static function MakeWYSIWYG($field,$level='',$allow_uploads=true,$add_directives='')
	{
		wysiwyg::MakeWYSIWYG($field,$level,$allow_uploads,$add_directives);
	}
			

};


$_wysiwyg_path=$_SERVER['HTTP_HOST'].'style.css';
$_wysisyg_styles=array('Body'=>'wysiwyg-body','Header'=>'header','Header 2'=>'subhead');
$_wysiwyg_default_mode='FULL';
$_wysiwyg_debug=false;

$_wysiwyg_modes=array();

$_wysiwyg_version=3;

$_wysiwyg_filemanager='/js/file_manager.php';

class wysiwyg extends html
{
 	static function SetVersion($v)
 	{
		global $_wysiwyg_version;
		$_wysiwyg_version=$v;
	}
 
 	static function GetVersion()
 	{
		global $_wysiwyg_version;
		return $_wysiwyg_version;
	}
 
	static function GatherCleanContent($content)
	{
		while(strpos($content,'../../')!==false)
			$content=str_replace('../../','../',$content);		
		$content=str_replace('../',_navigation::GetBaseURL(),$content);		
		$content=text::ReplaceExternalCharacters($content);
		return $content;
	}
		
	static function GetCleanContent($content)
	{
	 	global $_wysiwyg_oncleancontent_funct,$_wysiwyg_oncleancontent_class;

		$content=wysiwyg::ThumbImages($content);
		//$content=wysiwyg::ProtectEmail($content);
		if($_wysiwyg_oncleancontent_funct)
		{
			if($_wysiwyg_oncleancontent_class)
				$content=call_user_func(array($_wysiwyg_oncleancontent_class, $_wysiwyg_oncleancontent_funct),$content);
			else
				$content=call_user_func($_wysiwyg_oncleancontent_funct,$content);		
		}

		return $content;
	}

	static function SetOnGetCleanContent($oncleancontent,$oncleancontentclass)
	{
	 	global $_wysiwyg_oncleancontent_funct,$_wysiwyg_oncleancontent_class;
		$_wysiwyg_oncleancontent_funct=$oncleancontent;  
		$_wysiwyg_oncleancontent_class=$oncleancontentclass;  		
	}


	static function MakeWYSIWYG($field='',$level='',$allow_uploads=true,$add_directives='')
	{
		if(wysiwyg::GetVersion()==4)	
			wysiwyg::MakeWYSIWYG4($field,$level,$allow_uploads,$add_directives);			
		else
			wysiwyg::MakeWYSIWYG3($field,$level,$allow_uploads,$add_directives);	
	}

	static function MakeWYSIWYG3($field='',$level='',$allow_uploads=true,$add_directives='')
	{
	  	global $_wysiwyg_default_mode;;
	  	global $_wysiwyg_filemanager;
	  	if(!$level)
	  		$level=$_wysiwyg_default_mode;
	  
		echo("<script language=\"javascript1.2\">\r\n");
		echo "tinyMCE.init({\r\n";
		echo "mode : 'exact',\r\n";
		//useful.
		if($_wysiwyg_debug)
			echo "debug:true,\r\n";
		echo "elements : '".$field."',\r\n";
		echo "theme : 'advanced',\r\n";
		echo "content_css : '".wysiwyg::GetPath()."',\r\n";
//		echo "plugins : 'devkit,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\r\n";

		$wysiwyg_directives=wysiwyg::GetMode($level);
		if($wysiwyg_directives)
			echo($wysiwyg_directives);
		else
		{
			switch($level)
			{
				case 'SIMPLE':
				case 'BASIC':			
					echo "plugins : 'layer,table,save,advimage,advlink,iespell,insertdatetime,preview,media,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\r\n";
					echo "theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
					echo "theme_advanced_disable: 'styleselect,contextmenu,table,link,unlink,style,styleprops,formatselect,hr,anchor,delete_col,delete_row,col_after,col_before,row_after,row_before,row_after,row_before,split_cells,merge_cells,sub,sup,visualaid',"; 
					echo "theme_advanced_buttons1_add_before : 'undo,redo,separator',\r\n";
					echo "theme_advanced_buttons1_add : '',\r\n";
					echo "theme_advanced_buttons2 : '',\r\n";
					echo "theme_advanced_buttons3_add_before : '',\r\n";
					echo "theme_advanced_buttons3_add : '',\r\n";
					echo "theme_advanced_buttons3 : '',\r\n";
					echo "theme_advanced_buttons4 : '',\r\n";
					break;
				case 'SIMPLE_LINK':
				case 'BASIC_LINK':			
					echo "plugins : 'layer,table,save,advimage,advlink,iespell,insertdatetime,preview,media,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\r\n";
					echo "theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
					echo "theme_advanced_disable: 'styleselect,contextmenu,table,style,styleprops,formatselect,hr,anchor,delete_col,delete_row,col_after,col_before,row_after,row_before,row_after,row_before,split_cells,merge_cells,sub,sup,visualaid',"; 
					echo "theme_advanced_buttons1_add_before : 'undo,redo,separator',\r\n";
					echo "theme_advanced_buttons1_add : 'link,unlink',\r\n";
					echo "theme_advanced_buttons2 : '',\r\n";
					echo "theme_advanced_buttons3_add_before : '',\r\n";
					echo "theme_advanced_buttons3_add : '',\r\n";
					echo "theme_advanced_buttons3 : '',\r\n";
					echo "theme_advanced_buttons4 : '',\r\n";
					break;
				case 'MID':
				case 'INTERMEDIATE':			
					echo "plugins : 'style,layer,table,save,advimage,advlink,iespell,insertdatetime,preview,media,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\r\n";
					echo "theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
					echo "theme_advanced_disable: 'styleselect,contextmenu,table,style,styleprops,formatselect,hr,anchor,delete_col,delete_row,col_after,col_before,row_after,row_before,row_after,row_before,split_cells,merge_cells,sub,sup,visualaid',"; 
					echo "theme_advanced_buttons1_add_before : 'undo,redo,separator',\r\n";
					echo "theme_advanced_buttons1_add : 'indent,outdent,bullist,numlist,|,link,unlink,|,removeformat,|,charmap',\r\n";
					echo "theme_advanced_buttons2 : '',\r\n";
					echo "theme_advanced_buttons3_add_before : '',\r\n";
					echo "theme_advanced_buttons3_add : '',\r\n";
					echo "theme_advanced_buttons3 : '',\r\n";
					echo "theme_advanced_buttons4 : '',\r\n";
					break;
				case 'MID2':
				case 'INTERMEDIATE2':			
					echo "plugins : 'style,layer,table,save,advimage,advlink,iespell,insertdatetime,preview,media,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\r\n";
					echo "theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
					echo "theme_advanced_disable: 'styleselect,contextmenu,table,formatselect,hr,anchor,delete_col,delete_row,col_after,col_before,row_after,row_before,row_after,row_before,split_cells,merge_cells,sub,sup,visualaid',"; 
					echo "theme_advanced_buttons1_add_before : 'undo,redo,separator',\r\n";
					echo "theme_advanced_buttons1_add : 'indent,outdent,bullist,numlist,|,link,unlink,|,removeformat,|,charmap',\r\n";
					echo "theme_advanced_buttons2 : '',\r\n";
					echo "theme_advanced_buttons3_add_before : '',\r\n";
					echo "theme_advanced_buttons3_add : '',\r\n";
					echo "theme_advanced_buttons3 : '',\r\n";
					echo "theme_advanced_buttons4 : '',\r\n";
					break;
				case 'FULL':
				case 'ADVANCED':
					echo "plugins : 'style,layer,table,save,advimage,advlink,iespell,insertdatetime,preview,media,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\r\n";
					echo "theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
					echo "theme_advanced_disable: 'formatselect,contextmenu,table,hr,anchor,delete_col,delete_row,col_after,col_before,row_after,row_before,row_after,row_before,split_cells,merge_cells,sub,sup,visualaid',"; 
					echo "theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
					echo "theme_advanced_buttons1_add_before : 'pastetext,pasteword,|,undo,redo,separator',\r\n";
					echo "theme_advanced_buttons1_add : 'fontselect,fontsizeselect,|,forecolor,backcolor',\r\n";
					echo "theme_advanced_buttons2 : 'indent,outdent,bullist,numlist,|,link,unlink,image, |,removeformat,|,charmap,|,cleanup,code',\r\n";
					echo "theme_advanced_buttons3_add_before : '',\r\n";
					echo "theme_advanced_buttons3_add : '',\r\n";
					echo "theme_advanced_buttons3 : '',\r\n";
					echo "theme_advanced_buttons4 : '',\r\n";
					break;
				case 'ALL':
				default:				
					echo "plugins : 'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\r\n";
					echo "theme_advanced_styles : '".wysiwyg::GetMCEStyles()."',\r\n";
					echo "theme_advanced_buttons1_add_before : 'save,newdocument,separator',\r\n";
					echo "theme_advanced_buttons1_add : 'fontselect,fontsizeselect',\r\n";
					echo "theme_advanced_buttons2_add : 'separator,insertdate,inserttime,preview,separator,forecolor,backcolor,advsearchreplace',\r\n";
					echo "theme_advanced_buttons2_add_before: 'cut,copy,paste,pastetext,pasteword,separator,search,replace,separator',\r\n";
					echo "theme_advanced_buttons3_add_before : 'tablecontrols,separator',\r\n";
					echo "theme_advanced_buttons3_add : 'emotions,iespell,media,advhr,separator,print,separator,ltr,rtl,separator,fullscreen',\r\n";
					echo "theme_advanced_buttons4 : 'insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking',\r\n";
					break;
			}
		}

		echo "theme_advanced_toolbar_location : 'top',\r\n";
		echo "theme_advanced_toolbar_align : 'left',\r\n";
		echo "theme_advanced_path_location : 'bottom',\r\n";
		echo "plugin_insertdate_dateFormat : '%Y-%m-%d',\r\n";
		echo "plugin_insertdate_timeFormat : '%H:%M:%S',\r\n";
		echo "valid_elements : '*[*]',\r\n";
		echo "extended_valid_elements : 'map[name|id],area[id|href|coords|target|onclick|shape],form[name|id|action|method|enctype|accept-charset|onsubmit|onreset|target],input[id|name|type|value|size|maxlength|checked|accept|src|width|height|disabled|readonly|tabindex|accesskey|onfocus|onblur|onchange|onselect],textarea[id|name|rows|cols|disabled|readonly|tabindex|accesskey|onfocus|onblur|onchange|onselect],option[name|id|value],select[id|name|type|value|size|maxlength|checked|accept|src|width|height|disabled|readonly|tabindex|accesskey|onfocus|onblur|onchange|onselect|length|options|selectedIndex],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]',\r\n";
		echo "theme_advanced_resize_horizontal : false,\r\n";
		echo "theme_advanced_resizing : true,\r\n";
		echo "nonbreaking_force_tab : true,\r\n";
		echo "apply_source_formatting : true,\r\n";
		if($allow_uploads)
			echo "file_browser_callback : 'fileBrowserCallBack',\r\n";
		echo "paste_use_dialog : false,\r\n";
		echo "force_br_newlines : true,\r\n";
		echo "force_p_newlines : false\r\n";
		if($add_directives)
			echo(",".$add_directives."\r\n");
		echo "});\r\n";

		if($allow_uploads)
		{
			echo "function fileBrowserCallBack(field_name, url, type, win) {";
			echo "	var connector = '".$_wysiwyg_filemanager."';";
			
			echo "	my_field = field_name;";
			echo "	my_win = win;";
			
			echo "	switch (type) {";
			echo "		case 'image':";
			echo "		connector += '?type=img';";
			echo "		break;";
			echo "	case 'flash':";
			echo "		connector += '?type=flash';";
			echo "		break;";
			echo "	case 'file':";
			echo "		connector += '?type=files';";
			echo "		break;";
			echo "	case 'media':";
			echo "		connector += '?type=media';";
			echo "		break;";
			echo "	}";
			
			echo "	window.open(connector, 'file_manager', 'modal,width=450,height=600');";
			echo "}\r\n";
		}
		echo("</script>");
	}

	static function MakeWYSIWYG4($field='',$level='',$allow_uploads=true,$add_directives='')
	{
	  	global $_wysiwyg_default_mode;;
	  	global $_wysiwyg_filemanager;
	  	if(!$level)
	  		$level=$_wysiwyg_default_mode;
	  
		Javascript::Begin();
		echo "tinyMCE.baseURL='/js/tinymce4/';";
		echo "tinyMCE.init({\r\n";
		echo "mode : 'exact',\r\n";
		//useful.
		if($_wysiwyg_debug)
			echo "debug:true,\r\n";
		echo "selector : 'textarea#".$field."',\r\n";
		echo "theme : 'modern',\r\n";
		echo "relative_urls: false,\r\n";
		echo "remove_script_host: true,\r\n";
		echo "document_base_url: '"._navigation::GetBaseURL()."',\r\n";
		echo "content_css : '".wysiwyg::GetPath()."',\r\n";

		$wysiwyg_directives=wysiwyg::GetMode($level);
		if($wysiwyg_directives)
			echo($wysiwyg_directives);
		else
		{
			switch($level)
			{
				case 'SIMPLE':
				case 'BASIC':			
					echo "plugins: [],"."\r\n";
					echo "menubar : false,"."\r\n";
					echo "toolbar1: 'undo redo | bold italic underline strikethrough',"."\r\n";
					echo "\r\n";
					break;
				case 'SIMPLE_LINK':
				case 'BASIC_LINK':			
					echo "plugins: ["."\r\n";
					echo "  'link',"."\r\n";
					echo "],";
					echo "menubar : false,"."\r\n";
					echo "toolbar1: 'undo redo | bold italic underline strikethrough | link',"."\r\n";
					echo "\r\n";
					break;
				case 'MID':
				case 'INTERMEDIATE':			
				case 'MID2':
				case 'INTERMEDIATE2':			
					echo "plugins: ["."\r\n";
					echo "  'advlist autolink lists link charmap hr',"."\r\n";
					echo "  'searchreplace wordcount visualblocks visualchars fullscreen',"."\r\n";
					echo "  'insertdatetime nonbreaking contextmenu',"."\r\n";
					echo "  'emoticons template paste textcolor'"."\r\n";
					echo "],";
					echo "menubar : false,"."\r\n";
					echo "toolbar1: 'undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',"."\r\n";
					echo "\r\n";
					break;
				case 'FULL':
				case 'ADVANCED':
				case 'ALL':
				default:				
					echo "plugins: ["."\r\n";
					echo "  'advlist autolink lists link image charmap print preview hr anchor',"."\r\n";
					echo "  'searchreplace wordcount visualblocks visualchars code fullscreen',"."\r\n";
					echo "  'insertdatetime media nonbreaking table contextmenu directionality',"."\r\n";
					echo "  'emoticons template paste textcolor'"."\r\n";
					echo "],";
					echo "toolbar1: 'insertfile undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image forecolor backcolor',"."\r\n";
					echo "\r\n";
					break;
			}
		}
		if($add_directives)
			echo(",".$add_directives."\r\n");
		if($allow_uploads)
		{
			echo "file_browser_callback: function(field_name, url, type, win) {";
			echo "	var connector = '".$_wysiwyg_filemanager."';";
			
			echo "	my_field = field_name;";
			echo "	my_win = win;";
			
			echo "	switch (type) {";
			echo "		case 'image':";
			echo "		connector += '?type=img';";
			echo "		break;";
			echo "	case 'flash':";
			echo "		connector += '?type=flash';";
			echo "		break;";
			echo "	case 'file':";
			echo "		connector += '?type=files';";
			echo "		break;";
			echo "	case 'media':";
			echo "		connector += '?type=media';";
			echo "		break;";
			echo "	}";
			
			echo "	window.open(connector, 'file_manager', 'modal,width=450,height=600');";
			echo "}\r\n";		
		}

		echo "});\r\n";
		Javascript::End();
	}

	static function RegisterMode($mode,$output)
	{
		global $_wysiwyg_modes;
		$_wysiwyg_modes[$mode]=$output;	  
	}

	static function GetMode($mode)
	{
	  	global $_wysiwyg_modes;	  	
		return $_wysiwyg_modes[$mode];;  
	}

	static function SetDefaultMode($mode)
	{
		global $_wysiwyg_default_mode;
		$_wysiwyg_default_mode=$mode;	  
	}

	static function GetDefaultMode()
	{
		global $_wysiwyg_default_mode;
		return $_wysiwyg_default_mode;	  
	}
	
	static function SetPath($path)
	{
		global $_wysiwyg_path;
		$_wysiwyg_path=$path;	  
	}

	static function GetPath()
	{
		global $_wysiwyg_path;
		return $_wysiwyg_path;	  
	}

	static function SetFileManager($path)
	{
		global $_wysiwyg_filemanager;
		$_wysiwyg_filemanager=$path;	  
	}

	static function GetFileManager()
	{
		global $_wysiwyg_filemanager;
		return $_wysiwyg_filemanager;	  
	}

	static function GetMCEStyles()
	{
	  	global $_wysisyg_styles;
	  	$strs=array();
	  	foreach($_wysisyg_styles as $title=>$style)
	  		$strs[]=$title.'='.$style;
		return implode(';',$strs);

	}

	static function AddMCEStyle($title,$style)
	{
	  	global $_wysisyg_styles;
		$_wysisyg_styles[$title]=$style;
	}
  
};
?>