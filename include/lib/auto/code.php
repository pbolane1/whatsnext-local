<?php include('../../../include/common.php')?>
<html>
<head>
<title>BETA AUTOCODER</title>
<meta name="description" content="">
<meta name="keywords" content="">
</head>
<body>
	
<?php
	$path=_navigation::GetBasePath().'/include/classes/';
	$template=_navigation::GetBasePath().'/include/lib/auto/template.php';

  	//get template
  	$tpl=fopen($template,'r');
	if(!$tpl)
		die('FAILURE! no template...');

  	$template_content=fread($tpl,filesize($template));
  	fclose($tpl);


	//get all the tables
	$rs=database::query("SHOW TABLES FROM ".$__database_db."");
	while($table=database::fetch_Array($rs))
	{
	 	//leverage mod_rewrite preg_replace functionality to get a clean tablename 	
		$table_name=$table[0];		
		$table_name=mod_rewrite::ToURL($table_name,'a-z,A-Z,0-9','_');		
		//remove trailing s w/o removing leading s
		$class_name='_'.$table_name;
		$class_name=str_replace('ies','y',$class_name);
		$class_name=str_replace('s_','_',$class_name);
		$class_name=trim(trim($class_name,'s'),'_');
		//file to write to
		$file_name='c_'.$class_name.'.php';
		
		//write or skip.
		echo("<b>".$table_name.":</b><br>");
		if(file_exists($path.$file_name))
			echo("skipping file ".$file_name.'...<br>');
		else
		{
			//write file
			echo("generating file ".$file_name.'(table: '.$table_name.' - class: '.$class_name.')...');
			$f=fopen($path.$file_name,'w');
			if($f)
			{				  
				//the template
				$content=$template_content;
					
				//get fields
				$fields=array();
				$primary_key='';
				$rsf=database::query("SHOW FIELDS FROM ".$table_name);
				while($field=database::fetch_array($rsf))
				{					
					if($field['Key']=='PRI')
						$primary_key=$field['Field'];
					else
						$fields[]=$field['Field'];
				}
												
				//get replace values for template (edit and validate)
				$validate='';
				$gather='';
				$editform='';
				foreach($fields as $field)
				{
 	 				$gather.='';	  

					$validate.="\t\tif(!\$this->Get('".$field."'))\r\n";
					$validate.="\t\t\t\$this->LogError('Please Enter ".$field." For The ".$table_name."','".$field."');\r\n";

					$editform.="\t\techo(\"<tr><td>".$field."</td><td>\");\r\n";
					$editform.="\t\tform::DrawTextInput(\$this->GetFieldName('".$field."'),\$this->Get('".$field."'),array('class'=>\$this->GetError('".$field."')?'error':'text'));\r\n";
					$editform.="\t\techo(\"</td></tr>\");\r\n";
				}

				//quick replace
				$content=str_replace('/*timestamp*/',mktime(),$content);
				$content=str_replace('/*version*/','v0.0 BETA',$content);
				$content=str_replace('/*filename*/',$file_name,$content);
				$content=str_replace('/*tablename*/',$table_name,$content);
				$content=str_replace('/*classname*/',$class_name,$content);
				$content=str_replace('/*primarykey*/',$primary_key,$content);
				$content=str_replace('/*gather*/',$gather,$content);
				$content=str_replace('/*validate*/',$validate,$content);
				$content=str_replace('/*editform*/',$editform,$content);
				
				//write file
				fwrite($f,$content);
				fclose($f);
				echo('success'.'...<br>');
			}
			else
				echo('failure'.'...<br>');
		}
		
		echo("<br><br>");
	}




?>
</body>
</html>
