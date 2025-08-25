<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

/////////////////////////////////////////////////////////////
//
//				DBTableInterface
//
//
//  This class is intended as a base class for encapsulating
//  database access in classes/objects.  While it is possible
//  to use this class on it's own, it is recommended to inherit
//  from this class and customize behaviors.
//
//  Child Class constructors should behave as follows:
//
//      function Child($id='')
//      {
//          EstablishTable('TableName','PrimaryKeyName');
//          Init($id);
//          //etc...
//      }
//
/////////////////////////////////////////////////////////////

class DBTableInterface
{
	var $tableName=''; //name of table in database
	var $attributes='';//database record
	var $primary='';   //name of primary key
	var $id='';        //value in primary key;
	var $file_upload=false;   //can upload files for this class
	var $upload_files='';
	var $errors;
	var $always_edit=false;
	var $confirm_delete=true;

	//////////////////////////////////////////////////////
	//
	//      CONSTRUCTORS & INITIALIZATION
	//

	//default constructor
	function DBTableInterface($id='')
	{
		$this->id=$id;
		$this->attributes=array();
		$this->ClearErrors();
 	}
	
	function EstablishTable($table,$primarykey)
	{
		$this->tableName=$table;
		$this->primary=$primarykey;

		$schema=$this->GetSchema();
		if(!$schema)
		{
			$field_rs = Database::query("SHOW FIELDS FROM ".$this->tableName);
			while ($field = Database::fetch_array($field_rs))
			    $this->attributes[$field['Field']]='';
			$this->SetSchema($this->attributes);
		}
		else
		{
			$this->attributes=$schema;  
		}
 	}
	
	function InitSchema()
	{
		global $_db_schema;  
		if(!is_array($_db_schema))
			$_db_schema=array();
		return true;  
	}

	function GetSchema()
	{
		global $_db_schema;  
		if($this->VerifyTable() and $this->InitSchema())
			return $_db_schema[$this->tableName];
		return '';
	}

	function SetSchema($rec)
	{
		global $_db_schema;
		if($this->VerifyTable() and $this->InitSchema())
			$_db_schema[$this->tableName]=$rec;	  
	}

	function SetFilesAllowed($allow)
	{
        $this->file_upload=$allow;
 	}

	function SetAlwaysEdit($state=true)
	{
        $this->always_edit=$state;
        $this->Init('');
 	}

	function Init($id)
	{
		$this->id=$id;
		$this->Retrieve();
		$this->ClearErrors();
		
		$this->upload_files=array();
	}
	
	//////////////////////////////////////////////////////
	//
	//      TOOLS /
	//
	
	//Check Table Is Set Up
	function VerifyTable()
	{
		if(isset($this->primary) and isset($this->tableName))
		    return true;
		else
      		$this->FatalError("Primary Key=".$this->primary."<br>Table=".$this->tableName);
 	}

	//'Internal' class error
	function FatalError($err)
	{
		die("<b>Error in class DBTableInterface</b><br>".$err);
 	}


	//////////////////////////////////////////////////////
	//
	//      DATABASE INTERFACE
	//

	function Retrieve($rec='')
	{
		if($this->VerifyTable() and $this->id)
		{
		  	if(!$rec)//if not passed in, check DB.  otherwise, take what you're given
	            $rec=Database::fetch_array(Database::query("SELECT * FROM ".$this->tableName." WHERE ".$this->primary."=".$this->id));

			//port values
			foreach($this->attributes as $k=>$v)
				$this->attributes[$k]=$rec[$k];
		}
 	}

	function InitByKeys($keys,$values)
	{
		if(!is_array($keys))	$keys=array($keys);
		if(!is_array($values))	$values=array($values);

		if(!count($keys) or !count ($values))
			return;

		$where=array('1');
		foreach($keys as $index=>$k)
			$where[]=$keys[$index]."='".$values[$index]."'";
		$where=implode(' AND ',$where);

		$rec=database::fetch_array(database::query("SELECT ".$this->primary." FROM ".$this->tableName." WHERE ".$where));
				
		$this->Init($rec[$this->primary]);
	}
	
	function CreateFromKeys($keys,$values)
	{
		if(!is_array($keys))	$keys=array($keys);
		if(!is_array($values))	$values=array($values);

		if(!count($keys) or !count ($values))
			return;
	
		//try to be this one
		$this->InitByKeys($keys,$values);

		//if it didn't work, create.
		if(!$this->id)
		{
			foreach($keys as $index=>$k)
				$this->Set($keys[$index],$values[$index]);

			$this->Update();
		}
 	}



	function Delete()
	{
		if($this->VerifyTable() and $this->id)
			Database::query("DELETE FROM ".$this->tableName." WHERE ".$this->primary."=".$this->id);
		$this->id='';
 	}

	function Save()
	{
		//check we can operate on table
        if($this->VerifyTable())
        {
			//get attribs from outside world
			$this->GatherInputs();
			//check if inputs valid
			if($this->ValidateInputs())
			{
				$this->Update();
				return true;
			}
	 	}
	 	return false;
	}

	function Update()
    {
		if($this->id)//save existing
		{
			$pairs=array();
			foreach ($this->attributes as $k=>$v)
			{
				if($k!=$this->primary)
					$pairs[]=$k."='".$this->MakeDBSafe($v)."'";
			}
			Database::query("UPDATE ".$this->tableName." SET ".implode(',',$pairs)." WHERE ".$this->primary."=".$this->id);
		}
		else //create
		{
			$values=array();
			$keys=array();
			foreach ($this->attributes as $k=>$v)
			{
				if($k!=$this->primary)
                {
					$keys[]=$k;
					$values[]="'".$this->MakeDBSafe($v)."'";
				}
			}
			Database::query("INSERT INTO ".$this->tableName."(".implode(',',$keys).") VALUES(".implode(',',$values).")");
			if($this->always_edit)
				$this->Init('');
			else
	            $this->id=Database::insert_id();
		}
	}


	function Get($what)
	{
		return($this->attributes[$what]);
 	}

	function Set($what,$value)
	{
		$this->attributes[$what]=$value;
 	}


	//////////////////////////////////////////////////////
	//
	//      DISPLAY FUNCTIONS
	//

	//static display
	function Draw()
	{
		global $action;
		$this->ProcessAction($action);
		//drawing state
		if($action==$this->GetFormAction('delete'))
			return;
		else if($action==$this->GetFormAction('edit') or !$this->id)
        {
			echo("<form action='?action=".$this->GetFormAction('save').$this->GetFormExtraParams()."' ".($this->file_upload?"enctype='multipart/form-data' ":'')."method='post'>");
			$this->DrawErrors();
			$this->Edit();
			$this->DrawEditActions();
			echo("</form>");
		}
		else
		{
		    $this->Display();
			$this->DrawDisplayActions();
		}
	}

	function Display()
	{
		$this->Retrieve();
		foreach ($this->attributes as $k=>$v)
			echo("<b>".$k."</b>=".$v."<br>");
 	}

	//edit / create display
	function Edit()
	{
		foreach ($this->attributes as $k=>$v)
        {
			if($k!=$this->primary)
       			echo("<b>".$k."</b><input type='text' name='".$this->GetFieldName($k)."' value='".$v."'/><br>");
		}
 	}

	//////////////////////////////////////////////////////
	//
	//      FORM FUNCTIONS
	//

	function GetFormAction($act)
	{
		return $act.'_'.$this->tableName.'_'.($this->id?$this->id:'new');
 	}

	function GetFormExtraParams()
	{
		global $HTTP_GET_VARS;
		$strs="";
		foreach($HTTP_GET_VARS as $k=>$v)
		{
			if($k!='action')
			    $strs.="&".$k."=".$v;
		}
		return $strs;
 	}

	function GetFieldName($field)
	{
		return $this->tableName.'_'.$field.'_'.($this->id?$this->id:'new');
 	}

	function GatherInputs()
	{
		foreach($this->attributes as $k=>$v)
		{
			if($k!=$this->primary)
            {
	            $input=$this->GetFieldName($k);
				global $HTTP_POST_VARS;
	            if(isset($HTTP_POST_VARS[$input]))
					$this->attributes[$k]=$HTTP_POST_VARS[$input];
			}
		}
	}

	function GatherFile($file_field,$attr)
	{
		global $_FILES;
		if($_FILES[$file_field] and $_FILES[$file_field]['name'])
			$this->upload_files[$attr] = $_FILES[$file_field];
	}

	function SaveFile($which,$path,$unique_add='',$allow_types='',$maxsize_ks='',$overwrite=false,$image=false)
	{
		if($this->upload_files[$which])
		{
			$file_name=$this->GetUniqueFileName($this->upload_files[$which]['name'],$unique_add,$which);
			$file_name=file::RemoveIllegalCharacters($file_name);
			$success=false;
			
			if($image) 
				$success=imaging::Upload($this->upload_files[$which],$path,$allow_types,$size,$overwrite,$file_name);
			else
				$success=file::Upload($this->upload_files[$which],$path,$allow_types,$size,$overwrite,$file_name);
	

			if($success)
	        {
				$this->Set($which,$file_name);
				$this->Update();
	            chmod($path.$file_name,0644);
	            return true;
			}
			else
			{
				$this->LogError(file::GetError(),$which);
				return false;
			}  
		}
		return false;
	}

	function SaveImageFile($which,$path,$unique_add='',$allow_types='',$maxsize_ks='',$overwrite=false)
	{
		return $this->SaveFile($which,$path,$unique_add,$allow_types,$maxsize_ks,$overwrite,true);
	}
	
	function GetUniqueFileName($orig,$add,$which='')
	{
		//pieces
	  	$unique=$orig;
		if(!$name)	$name=substr($orig,0,strpos($orig,'.'));
		if(!$ext)	$ext=File::GetExtension($orig);

		//formatting.  can override and check $which for different behavior.
		$fmt="%s".$add.".%s";
		
		//unique'd
		$unique=sprintf($fmt,$name,$ext);	
		return $unique;
	}

	function ValidateInputs()
	{
		return true;
 	}

	function ValidateInput($which)
	{
	    $input=$this->GetFieldName($which);
		global $HTTP_POST_VARS;
        return (isset($HTTP_POST_VARS[$input]) and $HTTP_POST_VARS[$input]!='');

 	}

	function MakeSafe($value)
	{
		//make quotes safe gpc on or off.
		$value=str_replace("\\'","",$value);//remove single quotes
		$value=str_replace('\\"','',$value);//remove double quotes
		$value=str_replace("'","",$value);//remove single quotes
		$value=str_replace('"','',$value);//remove double quotes
		return $value;
	}

	function MakeDBSafe($value)
	{
        $value=str_replace("\\'","'",$value);//in case some or all are \ preceded, remove them, then add them.
		$value=str_replace("'","\'",$value);//remove single quotes
		return $value;
 	}

	//////////////////////////////////////////
	//
	//  ACTIONS
	//
	
	function DrawDisplayActions()
    {
		if($this->id)
		{
			$delete_params=array();
			if($this->confirm_delete)	$delete_params['onclick']="return confirm('Are you sure you want to permanently delete this item?');";


			form::Begin("?action=".$this->GetFormAction('edit').$this->GetFormExtraParams());
			form::DrawSubmit('','edit');
			form::end();

			form::Begin("?action=".$this->GetFormAction('delete').$this->GetFormExtraParams());
			form::DrawSubmit('','delete',$delete_params);
			form::end();
		}
	}

	function DrawEditActions()
    {
		echo("<input type='submit' value='save'/>");
		if($this->id)
		{
			echo("</form>");
			echo("<form action='?action=".$this->GetFormAction('cancel').$this->GetFormExtraParams()."' method='post'>");
			echo("<input type='submit' value='cancel'/>");
		}
	}

	function ProcessAction($act)
	{
		//database state
		global $action;
		if($act==$this->GetFormAction('delete'))
			$this->Delete();
		else if($act==$this->GetFormAction('save'))
		{
			//default to edit (in case of failure)
			$action=$this->GetFormAction('edit');
			if($this->Save())
			    $action='';
		}
	}

	function DrawUtilJavaScript()
	{
		global $_js_drawn_utility;
		if($_js_drawn_utility)
		    return;
        $_js_drawn_utility=true;

		echo("<script language='javascript'>\n");
		echo("function GetElement(id)\n");
		echo("{\n");
		echo("if(document.all)\n");
		echo("return document.all[id];\n");
		echo("else if (document.getElementById)\n");
		echo("return document.getElementById(id);\n");
		echo("else if (document.layers)\n");
		echo("return document.layers[id];\n");
		echo("}\n");
		echo("var stored_values=new Array();");
		echo("function StoreValue(id)\n");
		echo("{\n");
		echo("if(GetElement(id))\n");
		echo("stored_values[id]=GetElement(id).value;\n");
		echo("}\n");
		echo("function RestoreValue(id)\n");
		echo("{\n");
		echo("if(GetElement(id))\n");
		echo("GetElement(id).value=stored_values[id];\n");
		echo("}\n");
		echo("</script>\n");
	}

	function Dump($title='Dump')
	{
		echo("<b>".$title."</b><br>");
		foreach($this->attributes as $k=>$v)
		    echo($k."=".$v."<br>");
	}

	function LogError($err,$key='')
	{
	  	if($err)
	  	{
			if($key)	$this->errors[$key]=$err;
			else		$this->errors[]=$err;
			
		}
 	}

	function ClearErrors()
	{
		$this->errors=array();
 	}

	function GetErrors()
	{
		return $this->errors;
 	}

	function GetError($which)
	{
		$e=$this->errors[$which];
		return $e?$e:false;
	}

	function DrawErrors()
	{
		foreach($this->errors as $err)
		    echo('<span class="error"><b>Error:</b>'.$err.'</span><br>');
 	}
 	
 	function JSReportErrors()
 	{
		echo("<script language='javascript' type='text/javascript'>");
		foreach($this->errors as $er)
			echo("alert('".$er."');");
		echo("</script>");

	}
};

?>