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
//				DBRow - i.e. DBTableInterface 2.0
//
//
//  This class is intended as a base class for encapsulating
//  database access in classes/objects.  While it is possible
//  to use this class on it's own, it is recommended to inherit
//  from this class and customize behaviors.
//
//  Child Class constructors should behave as follows:
//
//      function Child($id='',$rec='')
//      {
//          EstablishTable('TableName','PrimaryKeyName');
//          Init($id,$rec);
//          //etc...
//      }
//
/////////////////////////////////////////////////////////////

class DBRow extends DBForm
{
	//database variables
	public $tableName=''; //name of table in database
	public $primary='';   //name of primary key
	public $attributes=array();//database record
	public $attributes_old=array();//copy of database record, for change comparison duringsaving.
	public $id='';        //value in primary key;

	//file&form related
	public $upload_files=array();
	
	//reporting
	public $errors;
	public $messages;
	
	//misc
	public $confirm_delete=true;
	
	//parameters, mode
	public $action_parameter='action';
	public $edit_state='';
	public $state_functions=array();
	public $state_actions=array();
	public $new_item_number='';
	
	//references - warning, mey not always be set.  be aware
	public $parent;

	//////////////////////////////////////////////////////
	//
	//      CONSTRUCTORS & INITIALIZATION
	//

	//default constructor
	public function __construct($id='')
	{
	  	parent::__construct();
	  	
		$this->id=$id;
		$this->attributes=array();
		$this->attributes_old=array();
		$this->ClearErrors();
		$this->ClearMessages();	
		
		if(!is_array($this->state_actions))		$this->state_actions=array();
		if(!is_array($this->state_functions))		$this->state_functions=array();
 	}
	
	//main table paramteters and schema initialization
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
	
	//cache db schema - array creation/ready? function
	public function InitSchema()
	{
		global $_db_schema;  
		if(!is_array($_db_schema))
			$_db_schema=array();
		return true;  
	}

	//get cached db schema
	public function GetSchema()
	{
		global $_db_schema;  
		if($this->VerifyTable() and $this->InitSchema())
			return $_db_schema[$this->tableName];
		return '';
	}

	//set cached db schema - to prevent extra SHOW queries
	public function SetSchema($rec)
	{
		global $_db_schema;
		if($this->VerifyTable() and $this->InitSchema())
			$_db_schema[$this->tableName]=$rec;	  
	}

	//core initialization.  generally no need to override, call in child contructor. 
	public function Init($id='',$rec='')
	{
		$this->id=$id;
		$this->SetNewItemNumber();
		$this->Retrieve($rec);
		$this->ClearErrors();
		$this->ClearMessages();		
		
		$this->upload_files=array();
	}
	
	//internally called to give each 'new' item unique field stuff.
	public function SetNewItemNumber($number='')
	{
		global $_db_new_count;
		if(!$this->id and $this->VerifyTable())
		{
		  	//can be provided extrnally (safer?)
		  	if($number!=='')
				$this->new_item_number=$number;
		  	else //auto
		  	{
			  	//make sure first is 0.
			  	if(!$_db_new_count[$this->tableName])  $_db_new_count[$this->tableName]=0;
			  	//avoid reassigning on subsequent Init calls (e.g. Save success)
			  	if($this->new_item_number==='')
			  	{
				  	//first one null.  subsequent, integer.
				  	$this->new_item_number=$_db_new_count[$this->tableName];
				  	$_db_new_count[$this->tableName]++;
				}
			}
		}
		else if($this->new_item_number)
		{
			$this->new_item_number='';
		  	$_db_new_count[$this->tableName]--;
		}				
	}

	public function SetParent(&$obj)
	{
		$this->parent=&$obj;  
	}



	
	//////////////////////////////////////////////////////
	//
	//      TOOLS /
	//
	
	//Check Table Is Set Up
	public function VerifyTable()
	{
		if(isset($this->primary) and isset($this->tableName))
		    return true;
		else
      		$this->FatalError("Primary Key=".$this->primary."<br>Table=".$this->tableName);
 	}

	//'Internal' class error
	public function FatalError($err)
	{
		die("<b>Error in class DBTableInterface</b><br>".$err);
 	}


	//////////////////////////////////////////////////////
	//
	//      DATABASE INTERFACE
	//

	public function Retrieve($rec='')
	{
		if($this->VerifyTable() and $this->id)
		{
		  	if(!$rec)//if not passed in, check DB.  otherwise, take what you're given
	            $rec=Database::fetch_array(Database::query("SELECT * FROM ".$this->tableName." WHERE ".$this->primary."='".$this->MakeDBSafe($this->id)."'"));

			//port values
			foreach($this->attributes as $k=>$v)
				$this->attributes[$k]=$rec[$k];
		}
 	}

	public function InitByKeys($keys,$values,$add_where=false)
	{
		if(!is_array($keys))	$keys=array($keys);
		if(!is_array($values))	$values=array($values);

		if(!count($keys) or !count ($values))
			return;

		$where=array('1');
		foreach($keys as $index=>$k)
			$where[]=$keys[$index]."='".$this->MakeDBSafe($values[$index])."'";
		if($add_where)
			$where[]=$add_where;
		$where=implode(' AND ',$where);

		$rec=database::fetch_array(database::query("SELECT ".$this->primary." FROM ".$this->tableName." WHERE ".$where));
				
		$this->Init($rec[$this->primary]);
	}
	
	public function CreateFromKeys($keys,$values,$add_where=false)
	{
		if(!is_array($keys))	$keys=array($keys);
		if(!is_array($values))	$values=array($values);

		if(!count($keys) or !count ($values))
			return;
	
		//try to be this one
		$this->InitByKeys($keys,$values,$add_where);

		//if it didn't work, create.
		if(!$this->id)
		{
		  	//if we had the primary in our list, force the key.
			foreach($keys as $index=>$k)
			{
				if($k==$this->primary)
				{
					database::query("INSERT INTO ".$this->tableName." (".$this->primary.") VALUES (".$this->MakeDBSafe($values[$index]).")");
					$this->id=$values[$index];
				}
			}
			
			//set values
			foreach($keys as $index=>$k)
				$this->Set($keys[$index],$values[$index]);
			
			//update DB
			$this->Update();
		}
 	}
	public function RetrieveRelated($which,$table,$where,$order='',$foreign_id='')
	{
	 	$this->related[$which]=array();
	 	$order=$order?" ORDER BY ".$order:'';
	 	$where=$where?" WHERE ".$where:"WHERE 1";
	 	$rs=database::query("SELECT * FROM ".$table.$where.$order);
	 	while($rec=database::fetch_array($rs))
	 		$this->related[$which][]=$foreign_id?$rec[$foreign_id]:$rec;
	 		
	 	if(!count($this->related[$which]))
		 	$this->related[$which]=array(-1);	 	
	}

	public function GatherRelated($which,$source)
	{
	  	if($source and !is_array($source))
			$this->related[$which]=explode(',',$source);
	  	if($source and is_array($source))
			$this->related[$which]=$source;		
		if(!count($this->related[$which]))
			$this->related[$which]=array(-1);		  			
	}

	public function SaveRelated($which,$table,$foreign_id,$local_id='')		
	{
	  	if(!$local_id) 	$local_id=$this->primary;
	  
		//delete if removed.  Create if not alredy there.		
  	  	database::query("DELETE FROM ".$table." WHERE ".$foreign_id." NOT IN(".implode(',',$this->related[$which]).") AND ".$local_id."=".$this->id);
		foreach($this->related[$which] as $fid)
		{
	  	 	$rec=database::fetch_array(database::query("SELECT COUNT(1) as cnt FROM ".$table." WHERE ".$foreign_id."=".$fid." AND ".$local_id."=".$this->id));
	  	 	if(!$rec['cnt'] and $fid>0)	
			    database::query("INSERT INTO ".$table." (".$local_id.",".$foreign_id.") VALUES(".$this->id.",".$fid.")");
		}
	}
	
	public function CopyTo($copy_to_obj)
	{
		foreach($this->attributes as $k=>$v)
		{
			if($k!=$this->primary)  
				$copy_to_obj->Set($k,$v);
		}
		return $copy_to_obj;
	}

	public function Copy($copy_from_obj)
	{
		foreach($copy_from_obj->attributes as $k=>$v)
		{
			if($k!=$this->primary)  
				$this->Set($k,$v);
		}
	}


	public function Delete()
	{
		if($this->VerifyTable() and $this->id)
			Database::query("DELETE FROM ".$this->tableName." WHERE ".$this->primary."='".$this->id."'");
		$this->id='';
 	}

	public function Save()
	{
	  	$this->attributes_old=$this->attributes;
	  
		//check we can operate on table
        if($this->VerifyTable())
        {

			//get attribs from outside world
			$this->GatherInputs();
			//clean them [magic quotes]
			foreach($this->attributes as $k=>$v)
				$this->attributes[$k]=stripslashes($v);

			//check if inputs valid
			if($this->ValidateInputs())
			{
				$this->Update();
				return true;
			}
	 	}
	 	return false;
	}

	public function Update()
    {
		if($this->id)//save existing
		{
			$pairs=array();
			foreach ($this->attributes as $k=>$v)
			{
				if($k!=$this->primary)
					$pairs[]=$k."=".(($v==='NULL')?('NULL'):("'".$this->MakeDBSafe($v)."'"));
			}
			Database::query("UPDATE ".$this->tableName." SET ".implode(',',$pairs)." WHERE ".$this->primary."='".$this->id."'");
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
					$values[]=(($v==='NULL')?('NULL'):("'".$this->MakeDBSafe($v)."'"));
				}
			}
			Database::query("INSERT INTO ".$this->tableName."(".implode(',',$keys).") VALUES(".implode(',',$values).")");
            $this->id=Database::insert_id();
		}
	}

	public function UpdateData($k,$v)
	{
	 	//update a single enrtry in the dataabse...
		$this->Set($k,$v);
		if($this->id)
			database::query("UPDATE ".$this->tableName." SET ".$k."=".(($v==='NULL')?('NULL'):("'".$this->MakeDBSafe($v)."'"))." WHERE ".$this->primary."='".$this->id."'");
	}

	public function Get($what)
	{
		return($this->attributes[$what]);
 	}

	public function Set($what,$value)
	{
		$this->attributes[$what]=$value;
 	}

	public function GetOld($what)
	{
		return($this->attributes_old[$what]);
 	}


	//////////////////////////////////////////////////////
	//
	//      DISPLAY FUNCTIONS
	//

	//static display
	public function Draw()
	{
		//drawing state
		if($this->edit_state=='DELETED')
			return;
		else if($this->edit_state=='EDIT')
        {
			$this->Edit();
		}
		else if(is_array($this->state_functions) and in_array($this->edit_state,array_flip($this->state_functions)))
		{
		  	$function=$this->state_functions[$this->edit_state];
			$this->$function();
		}
		else
		{
		    if($this->id)
		    {
				$this->DisplayEditable();
				$this->EditLink();
				$this->DeleteLink();
			}
			else //new
				$this->CreateLink();
				
		}
	}

	public function Display()
	{
		$this->Retrieve();
		foreach ($this->attributes as $k=>$v)
			echo("<b>".$k."</b>=".$v."<br>");
 	}

	public function DisplayEditable()
	{
		$this->Retrieve();
		foreach ($this->attributes as $k=>$v)
			echo("<b>".$k."</b>=".$v."<br>");
 	}

	public function DisplayShort()
	{
		$this->Retrieve();
		foreach ($this->attributes as $k=>$v)
			echo("<b>".$k."</b>=".$v."<br>");
 	}

	public function DisplayFull()
	{
		$this->Retrieve();
		foreach ($this->attributes as $k=>$v)
			echo("<b>".$k."</b>=".$v."<br>");
 	}


	//edit / create display
	public function Edit()
	{
		form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams(),$this->form_method,$this->file_upload);		
		$this->PreserveInputs();
		$this->EditForm();
		$this->SaveLink();
		form::End();
		$this->CancelLink();	  
	}

	public function EditForm()
	{
		foreach ($this->attributes as $k=>$v)
        {
			if($k!=$this->primary)
       			echo("<b>".$k."</b><input type='text' name='".$this->GetFieldName($k)."' value='".$v."'/><br>");
		}
 	}

	public function CreateLink()
    {
		if(!$this->id)
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('edit').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawSubmit('','Create');
			form::end();
		}
	}
	
	public function EditLink()
    {
		if($this->id)
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('edit').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawSubmit('','Edit');
			form::end();
		}
	}

	public function DeleteLink()
    {
		if($this->id)
		{
			$delete_params=array();
			if($this->confirm_delete)	$delete_params['onclick']="return confirm('Are you sure you want to permanently delete this item?');";
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('delete').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawSubmit('','Delete',$delete_params);
			form::end();
		}
	}

	public function CancelLink($params='')
    {
		if($this->id)
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('cancel').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawSubmit('','Cancel');
			form::end();
		}
	}

	public function SaveLink()
    {
		form::DrawSubmit('',$this->id?'Save':'Create');
	}


	public function DrawEditSelect($where='1',$show='',$order='',$implicit=true)
	{
	  	if($show)	$show=$this->primary;
	  	if($order)	$order=$this->primary;

		$params=array();
		if($implicit)		$params['onchange']='this.form.submit()';
	  
		form::Begin("?".$this->action_parameter."=".$this->GetFormAction('manage').$this->GetFormExtraParams());
		$this->PreserveInputs();
		form::DrawSelectFromSQL($this->primary,"SELECT * FROM ".$this->table." WHERE ".($where?$where:'1')."  ORDER BY ".$order,$show,$this->primary,$this->id,$params);
		if(!$implicit)	form::DrawSubmit('','edit');
		form::end();
 	}

	//////////////////////////////////////////////////////
	//
	//      FORM FUNCTIONS
	//

	public function GetUniqueName($for)
	{
		return $for.'_'.$this->tableName.'_'.($this->id?$this->id:'new'.$this->new_item_number);
	}

	public function GatherInputs()
	{
		global $HTTP_POST_VARS,$HTTP_GET_VARS;
		$global_vars=$this->form_method=='post'?$HTTP_POST_VARS:$HTTP_GET_VARS;

		foreach($this->attributes as $k=>$v)
		{
			if($k!=$this->primary)
            {
	            $input=$this->GetFieldName($k);
	            if(isset($global_vars[$input]))
					$this->attributes[$k]=$global_vars[$input];
			}
		}
	}

	public function GatherDate($field)
	{
		$d=new Date();
		$d->ParseDate($this->Get($field));
		$this->Set($field,$d->GetDBDate());
	}
	
	public function GatherPrice($field)
	{
		$this->Set($field,Text::RemoveAllOf($this->Get($field),array('$',',','-',' ')));
	}


	public function GatherFile($file_field,$attr)
	{
		global $_FILES;
		if($_FILES[$file_field] and $_FILES[$file_field]['name'])
			$this->upload_files[$attr] = $_FILES[$file_field];
	}

	public function SaveFile($which,$path,$unique_add='',$allow_types='',$maxsize_ks='',$overwrite=false,$image=false)
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

	public function SaveImageFile($which,$path,$unique_add='',$allow_types='',$maxsize_ks='',$overwrite=false)
	{
		return $this->SaveFile($which,$path,$unique_add,$allow_types,$maxsize_ks,$overwrite,true);
	}
	
	public function DeleteFile($which,$path)
	{
		if($this->Get($which) and file_exists($path.$this->Get($which)))
			unlink($path.$this->Get($which));  
	}

	public function DeleteImage($which,$path)
	{
		$this->DeleteFile($which,$path);
	}
	public function GetUniqueFileName($orig,$add,$which='')
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

	public function ValidateInputs()
	{
		return count($this->errors)==0;
 	}

	public function ValidateInput($which)
	{
	    $input=$this->GetFieldName($which);
		global $HTTP_POST_VARS,$HTTP_GET_VARS;				
		$global_vars=$this->form_method=='post'?$HTTP_POST_VARS:$HTTP_GET_VARS;
        return (isset($global_vars[$input]) and $global_vars[$input]!='');

 	}

	public function ValidateUnique($which,$where="1")
	{
	  	if(!is_array($where))	$where=array($where);
	  	
	  	if(!is_array($which))	$which=explode(',',$which);
		foreach($which as $field)
			$where[]=($field."='".$this->MakeDBSafe($this->Get($field))."'");

		$rec=database::fetch_array(database::query("SELECT count(1) as cnt FROM ".$this->tableName." WHERE ".implode(' AND ',$where)." AND ".$this->primary."!='".$this->id."'"));
		return $rec['cnt']==0;
	}

	public function ValidateUpload($which)
	{
		return $this->upload_files[$which] or $this->Get($which);
	}

	public function ValidateURL($which,$require='',$default='http://')
	{
	  	if(!$require)
	  		$require=array('http://','https://','ftp://');
		$found=false;
	  	foreach($require as $chk)
			$found=($found or (strpos($this->Get($which),$chk)!==false)); 	  	
		if($this->Get($which) and !$found)  
			$this->Set($which,$default.$this->Get($which));
	}

	public function MakeSafe($value)
	{
		//make quotes safe gpc on or off.
		$value=str_replace("\\'","",$value);//remove single quotes
		$value=str_replace('\\"','',$value);//remove double quotes
		$value=str_replace("'","",$value);//remove single quotes
		$value=str_replace('"','',$value);//remove double quotes
		return $value;
	}

	public function MakeDBSafe($value)
	{
        $value=str_replace("\\'","'",$value);//in case some or all are \ preceded, remove them, then add them.
		$value=str_replace("'","\'",$value);//remove single quotes
		return $value;
 	}

	//////////////////////////////////////////
	//
	//  ACTIONS
	//

	//BASE ROUTER.  SHOULD NOT OVERRIDE
	public function ProcessAction($act='')
	{

		//if none provided, ready from our expected action source
		if(!$act)	$act=$this->GetCurrentAction();
		$this->DoAction($act);
	}
	
	//OVERRIDABLE
	public function DoAction($act)
	{
		//database state
		if($act==$this->GetFormAction('delete'))
		{
			$this->Delete();
			$this->edit_state='DELETED';
		}
		else if($act==$this->GetFormAction('edit'))
		{
			$this->edit_state='EDIT';
		}
		else if($act==$this->GetFormAction('save'))
		{
			//default to edit (in case of failure)
			$this->edit_state='EDIT';
			if($this->Save())			
			    $this->edit_state='';
		}	
		else 
		{
		  	foreach($this->state_actions as $state=>$s_action)
		  	{
			  	if($this->GetFormAction($s_action)==$act)
					$this->edit_state=$state;
			}
		}	
	}

	public function GetCurrentAction()
	{
		$param=$this->action_parameter;
		global $$param;
		return $$param;
		
	}
	//////////////////////////////////////////
	//
	//  Error And Message Reporting
	//

	public function Dump($title='Dump')
	{
		echo("<b>".$title."</b><br>");
		foreach($this->attributes as $k=>$v)
		    echo($k."=".$v."<br>");
	}

	public function LogError($err='',$key='')
	{
	  	if($err or $key)
	  	{
			if($key)	$this->errors[$key]=$err;
			else		$this->errors[]=$err;			
		}
 	}

	public function ClearErrors()
	{
		$this->errors=array();
 	}

	public function GetErrors()
	{
		return $this->errors;
 	}

	public function GetError($which)
	{
		$e=$this->errors[$which];
		return $e?$e:false;
	}

	public function DrawErrors()
	{
		foreach($this->errors as $err)
		{
		  	if($err and $err!==true)
			    echo('<span class="error"><b>Error:</b>'.$err.'</span><br>');
		}
 	}
 	
 	public function JSReportErrors()
 	{
		echo("<script language='javascript' type='text/javascript'>");
		foreach($this->errors as $err)
		{
		  	if($err and $err!==true)
				echo("alert('".$err."');");
		}
		echo("</script>");

	}
	
	public function LogMessage($msg,$key='')
	{
	  	if($err)
	  	{
			if($key)	$this->messages[$key]=$msg;
			else		$this->messages[]=$msg;
			
		}
 	}

	public function ClearMessages()
	{
		$this->messages=array();
 	}

	public function GetMessages()
	{
		return $this->messages;
 	}

	public function GetMessage($which)
	{
		$msg=$this->messages[$which];
		return $msg?$msg:false;
	}

	public function DrawMessages()
	{
		foreach($this->messages as $msg)
		    echo('<span class="error"><b>Error:</b>'.$msg.'</span><br>');
 	}
 	
 	public function JSReportMessages()
 	{
		JavaScript::Begin();
		foreach($this->messages as $msg)
			JavaScript::Alert($msg);
		JavaScript::End();

	}	
	

	//////////////////////////////////////////
	//
	//  Javascript Multi-New Edit Functions
	//

	public function GetJSEditFunctionName()
	{
		return 'JSEdit'.$this->tableName;
	}

	public function JSEditForm()
	{
	  	//remind to override/customize for specific class
		echo("alert('No JSEditForm Override provided');");
	}

	public function DeclareJSEditFunction()
	{
		Javascript::Begin();
		echo("public function ".$this->GetJSEditFunctionName()."(unique_ident,addtoobj)");
		echo("{");
		$this->JSEditForm();
		echo("}");
		$this->JSHelperFunctions();
		Javascript::End();
	}

	public function JSHelperFunctions()
	{
	  
	}

};

?>