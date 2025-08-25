<?php
	class FormEx extends Form
	{
		static function DrawAsyncFileInput($ulname,$name,$value,$ulpath,$params='',$upload_params='',$custom_handlers=array())
		{
		  	$path=_navigation::GetBaseURL().'swfupload/';
			form::DrawFileInput($ulname,$value,$params);//the inpout for non SWF loads / normal mode/ default
			echo('<table><tr>');
			echo('	<td><span id="spanButtonPlaceholder'.$name.'"></span></td>');
			echo('	<td><div class="flash" id="fsUploadProgress'.$name.'"></div>');
			echo('</tr></table>');
			form::DrawHiddenInput($name,$value);//file name 

			//params check/translate.
			$types_orig=$upload_params['file_types'];
			if(!is_array($upload_params))						$upload_params=array();
			if(!$upload_params['max_size'])						$upload_params['max_size']="64 MB";
			if(!$upload_params['file_types'])					$upload_params['file_types']=array("*");
			if(!$upload_params['file_types_description'])		$upload_params['file_types_description']="All Files";
			for($i=0;$i<count($upload_params['file_types']);$i++)
				$upload_params['file_types'][$i]="*.".$upload_params['file_types'][$i];

			//handlers/custom handlers
			$handlers=array();
			$handlers['swfupload_loaded_handler']='swfUploadLoaded';
			$handlers['file_dialog_start_handler']='fileDialogStart';
			$handlers['file_queued_handler']='fileQueued';
			$handlers['file_queue_error_handler']='fileQueueError';
			$handlers['file_dialog_complete_handler']='fileDialogComplete';
			$handlers['upload_start_handler']='uploadStart';
			$handlers['upload_progress_handler']='uploadProgress';
			$handlers['upload_error_handler']='uploadError';
			$handlers['upload_success_handler']='uploadSuccess';
			$handlers['upload_complete_handler']='uploadComplete';
			
			foreach($custom_handlers as $k=>$v)
				$handlers[$k]=$v;


			//css, js utilities for the uploader
			echo('<link href="'.$path.'default.css" rel="stylesheet" type="text/css" />');
			Javascript::IncludeJS($path.'swfupload.js','',true);
			Javascript::IncludeJS($path.'swfupload.swfobject.js','',true);
			Javascript::IncludeJS($path.'fileprogress.js','',true);
			Javascript::IncludeJS($path.'handlers.js','',true);
			//			Javascript::IncludeJS('jquery_lib.js');  // required, but assumed to be provided outside.
			Javascript::Begin();
			echo("  var swfupload".$name.";\r\n");
			echo("  jQuery(document).ready(function()\r\n"); 
			echo("  {\r\n");		  	
			echo("  	swfupload".$name." = new SWFUpload(\r\n");
			echo("  	{\r\n");
			//// Backend settings ////
			echo("  		upload_url: '".$path."process.php',\r\n");
			echo("  		file_post_name: 'swfupload',\r\n");
			echo("  		post_params : { 'path' : '".$ulpath."', 'inputname' : '".$name."','allow_types' : '".(implode(',',$types_orig))."', 'maxsize_ks' : '".($upload_params['max_size']*1024)."' },\r\n"); 
			//// Flash file settings ////
			echo("  		file_size_limit : '".$upload_params['max_size']." MB',\r\n");
			echo("  		file_types : '".implode(';',$upload_params['file_types'])."',\r\n");
			echo("  		file_types_description :'".$upload_params['file_types_description']."',\r\n");
			echo("  		file_upload_limit : '0',\r\n");
			echo("  		file_queue_limit : '1',\r\n");		
			// Event handler settings ////
			foreach($handlers as $h=>$fn)
				echo("  		".$h." : ".$fn.",\r\n");									
			//// Button Settings ////
			echo("  		button_image_url : '".$path."button.png',\r\n");
			echo("  		button_placeholder_id : 'spanButtonPlaceholder".$name."',\r\n");
			echo("  		button_width: 61,\r\n");
			echo("  		button_height: 22,\r\n");
			echo("  		//button_text : 'Browse...',\r\n");						
			//// Flash Settings ////
			echo("  		flash_url : '".$path."swfupload.swf',\r\n");		
			echo("  		custom_settings :\r\n"); 
			echo("  		{\r\n");
			echo("  			progressTarget : 'fsUploadProgress".$name."',\r\n");
			echo("  			successTarget : '".$name."',\r\n");
			echo("  			fileupload : '".$ulname."',\r\n");
			echo("  			upload_successful : false\r\n");
			echo("  		},\r\n");							
			//// Debug settings ////
			echo("  		debug: false\r\n");
			echo("  	});\r\n");
			echo("	});\r\n");
			
			Javascript::End();			
	 	}
			  	  
		static function DrawPairSelect($field_name,$opts,$opts_sel,$params_avail='',$params_sel='',$params_btn='')
		{
		  	if(!$opts) 			$opts=array();
		  	if(!$opts_sel) 		$opts_sel=array();
		  	if(!$params_avail) 	$params_avail=array();
		  	if(!$params_sel) 	$params_sel=array();
		  	if(!$params_btn) 	$params_btn=array();

			$field_name_avail=$field_name.'_avail';
			$field_name_sel=$field_name.'_sel';
		  
		  
			$params_avail['multiple']='true';
			$params_sel['multiple']='true';
		  
			Javascript::IncludeJS('selectbox.js');
			echo("<table>");
			echo("<tr><td><b>Available:</b><br>");
			form::DrawSelect($field_name_avail,$opts,'',$params_avail);
			echo("</td><td align='center' style='vertical-align:middle'>");
			form::DrawButton('move','ADD >',array('multiple'=>'true','onclick'=>"moveSelectedOptions(this.form['".$field_name_avail."'],this.form['".$field_name_sel."'],false);this.form['".$field_name."'].value='';for(var i=0;i<this.form['".$field_name_sel."'].options.length;i++){this.form['".$field_name."'].value+=(this.form['".$field_name_sel."'].options[i].value+',');}this.form['".$field_name."'].value+=-1;sortSelect(this.form['".$field_name_sel."'])")+$params_btn);
			echo("<br><br>");
			form::DrawButton('move2','< REMOVE',array('multiple'=>'true','onclick'=>"moveSelectedOptions(this.form['".$field_name_sel."'],this.form['".$field_name_avail."'],false);this.form['".$field_name."'].value='';for(var i=0;i<this.form['".$field_name_sel."'].options.length;i++){this.form['".$field_name."'].value+=(this.form['".$field_name_sel."'].options[i].value+',');}this.form['".$field_name."'].value+=-1;//sortSelect(this.form['".$field_name_avail."'])")+$params_btn);
			echo("</td><td><b>Selected:</b><br>");
			form::DrawSelect($field_name_sel,$opts_sel,'',$params_sel);
			echo("</td></tr>");
			echo("</table>");
			form::DrawHiddenInput($field_name,implode(',',$opts_sel)); 
		}
	  
		static function DrawPairSelectFromSQL($field_name,$query,$field,$passfield,$selected='',$params_avail='',$params_sel='',$params_btn='')	  
		{
		  	if(!$selected) 			$selected=array();

			$opts=array();
			$opts_sel=array();		
			$rs=database::query($query);
			while($rec=database::Fetch_Array($rs))
			{
			  	if(in_array($rec[$passfield],$selected))
					$opts_sel[$rec[$field]]=$rec[$passfield];
				else
					$opts[$rec[$field]]=$rec[$passfield];
			}
			FormEx::DrawPairSelect($field_name,$opts,$opts_sel,$params_avail,$params_sel,$params_btn);
		  
		}	

		static function DrawPairSelectAutoComplete($field_name,$opts_sel,$params,$params_sel='',$params_btn='',$rules='',$select_text="\$rec['name']",$select_value="\$object->id",$show_loading='loading')
		{
		  	if(!$opts) 			$opts=array();
		  	if(!$opts_sel) 		$opts_sel=array();
		  	if(!$params) 		$params=array();
		  	if(!$params_sel) 	$params_sel=array();
		  	if(!$params_btn) 	$params_btn=array();

			$field_name_avail=$field_name.'_avail';
			$field_name_sel=$field_name.'_sel';
		  		  
			$params_sel['multiple']='true';
		  
			Javascript::IncludeJS('selectbox.js');
			echo("<table>");
			echo("<tr><td><b>Available:</b><br>");
			foreach($rules as $index=>$rule)
 				$rules[$index]['onclick']="\"<a href='#' onclick=\\\"addOption(jQuery('#".$field_name_sel."').get(0),'\".(".$select_text.").\"','\".(".$select_value.").\"',false);jQuery('#".$field_name."').get(0).value='';for(var i=0;i<jQuery('#".$field_name_sel."').get(0).options.length;i++){jQuery('#".$field_name."').get(0).value+=(jQuery('#".$field_name_sel."').get(0).options[i].value+',');}jQuery('#".$field_name."').get(0).value+=-1;sortSelect(jQuery('#".$field_name_sel."').get(0));ShowSearchResults('".$field_name_avail."_results_container',true);return false;\\\"><b>\".(\$rec['name']).\"</b><br>\".(".$select_text.").\"</a>\"";		
			formEx::DrawAutoCompleteInput($field_name_avail,'',$params,$rules,$show_loading);
			echo("</td><td><b>Selected:</b><br>");
			form::DrawSelect($field_name_sel,$opts_sel,'',$params_sel);
			echo("<br>");
			form::DrawButton('move2','REMOVE SELECTED',array('multiple'=>'true','onclick'=>"removeSelectedOptions(this.form['".$field_name_sel."']);this.form['".$field_name."'].value='';for(var i=0;i<this.form['".$field_name_sel."'].options.length;i++){this.form['".$field_name."'].value+=(this.form['".$field_name_sel."'].options[i].value+',');}this.form['".$field_name."'].value+=-1;sortSelect(this.form['".$field_name_avail."'])")+$params_btn);
			form::DrawHiddenInput($field_name,implode(',',$opts_sel)); 
			echo("</td></tr>");
			echo("</table>");
		}


	  
		static function DrawAutoCompleteInput($name,$value,$params='',$rules='',$show_loading='loading')  
		{
		 	if(!$params) 	$params=array();
		 	$params['autocomplete']='off';

			Session::Set('AutoComplete_'.$name,$rules);

			$min_length=false;
			foreach($rules as $rule)
			{
				if($rule['min_length']<$min_length or $min_length===false)  
					$min_length=$rule['min_length'];
			}


			echo("<div class='auto_complete' id='".$name."_container'>");
			form::DrawTextInput($name,$value,$params);  			
			echo("<div class='auto_complete_results' id='".$name."_results_container'></div>");
			echo("</div>");
			//Javascript::IncludeJS('jquery_lib.js');
			//Javascript::IncludeJS('AjaxRequest.js');
			//Javascript::IncludeJS('auto_complete.js');
			Javascript::Begin();
			echo("jQuery(document).ready(function(){EnableAutoComplete('".$name."_results_container','".$name."','".$show_loading."','".$min_length."');});");
			Javascript::End();
		}
	  
		static function PopulateAutoComplete($search_term,$identifier)  
		{
  		    $results=FormEx::GetAutoCompleteResults(addslashes($search_term),$identifier);
			if(is_array($results))
			{
				echo("<div class='auto_complete_results_content'>");
				if(count($results))
			  		echo(implode('',$results));
				else
			  		echo("<div class='auto_complete_results_content_none'>[No results found]</div>");
			  	echo("</div>");
			}
	  	}	  

		static function GetAutoCompleteResults($search_term,$identifier)  
		{	  
			$search_term=trim($search_term);

			//get all the results from the session'ed rules for this auto complete.
			$results=array();		
			$found_ids=array();
			$show_results=false;
			foreach(Session::Get('AutoComplete_'.$identifier) as $rules)
			{						
				$primary_key=$rules['primary_key'];
				$class=$rules['class'];
				$onclick=$rules['onclick'];
				$query=$rules['query'];
				$once_per_id=$rules['once_per_id'];
				$min_length=$rules['min_length'];
				$header=$rules['header'];
				$empty_text=$rules['empty_text'];

				if(strlen($search_term)>=$min_length)
				{
					//can show the results.
					$show_results|=true;
					
				  	//format the query w search term
				  	if(!$notrim)
				  		$query=trim($query);
//				  	if(!$nowildcards)
//				  		$query=str_replace('*','%',$query);
					$query=sprintf($query,$search_term,$search_term,$search_term,$search_term,$search_term,$search_term);										
					
					//do the query, and show header/none if results/no results as per options
					$rs=database::query($query);
					$res_count=database::num_rows($rs);
					//header for this section?  Empty string for this section?
					if($header and $res_count)
						$results[]="<div class='header'>".$header."</div>";				
					else if($header and !$res_count and $empty_text)
					{
						$results[]="<div class='header'>".$header."</div>";									
						$results[]="<div class='empty'>".$empty_text."</div>";				
					}
					
					//get retuls
					while($rec=database::fetch_array($rs))
					{
					  	if(!$once_per_id or !in_array($rec[$primary_key],$found_ids))
					  	{
							//mark as found and make the object.
						  	$found_ids[$primary_key]=$rec[$primary_key];
						  	$object=new $class($rec[$primary_key]);				  
	
							//clean to not break js.
							foreach($rec as $k=>$v)
								$rec[$k]=str_replace("'","",$v);//htmlspecialchars($v,ENT_QUOTES);
							foreach($object->attributes as $k=>$v)
								$object->Set($k,str_replace("'","",$v));//htmlspecialchars($v,ENT_QUOTES));	
//							foreach($object->attributes as $k=>$v)
//								echo($k.'...'.htmlspecialchars($v,ENT_QUOTES).'<br>');

							//eval the eval sting and include in results
						  	html::HoldOutput();
							eval("echo(".$onclick.");");

							$results[]=html::GetHeldOutput();
							html::ResumeOutput();
						}
					}
				}
			}
			return $show_results?$results:false;
		}	  
};	
?>