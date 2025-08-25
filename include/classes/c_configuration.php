<?php

class configuration_item extends DBRowEx
{
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->EstablishTable('configuration','configuration_key');		
		$this->Init($id);
		$this->AllowFiles();
	}

	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->Get('configuration_editable'))
			$this->SetFlag('DISABLECLICKEDIT');	  
	}

	public function DisplayEditable()
	{
	  	//if special format, make normal for now...
		$fmt=$this->Get('configuration_format');
		$value=$this->Get('configuration_value');

		if($fmt=='SELECT')		
		{
			$fmt='%s';		  
			$options=$this->GetOptions();
			$options=array_flip($options);
			$value=$options[$value];
		}
		if($fmt=='FILE')		$fmt='%s';
		if($fmt=='PASSWORD')		
		{
		  	$fmt='%s';
			$value='';
			for($i=0;$i<strlen($this->Get('configuration_value'));$i++)
				$value.='*';
		}
		if($fmt=='DATE')		
		{
		  	$fmt='%s';
		  	if($value or $this->Get('configuration_min'))
		  	{
				$d=new DBDAte($value);
				$value=$d->GetDate('m/d/Y');
			}
		}
		if($fmt=='TIME')		
		{
		  	$fmt='%s';
		  	if($value or $this->Get('configuration_min'))
		  	{
				$value=calendar::TranslateTime($value);
			}
		}

		$value=str_replace('<','&lt;',$value);
		$value=str_replace('>','&gt;',$value);

		if($fmt=='IMAGE')
		{
		  	$fmt='%s';
			$value="<img src='".$this->GetThumb(200,200)."'>";
		}

		$title=$this->Get('configuration_title');
		if($this->Get('imprint_id'))
		{
			$imprint=new imprint($this->Get('imprint_id'));  
			$title=$imprint->Get('imprint_name').' - '.$title;
		}

		if(strlen($value)>35 and ((strpos($value,'')>35) or (strpos($value,'')===false)))
			$value=substr($value,0,35).'...';

	 	echo("<td>".$title."</td>");
	 	echo("<td>".$this->Get('configuration_hint')."</div></td>");
	 	echo("<td>".sprintf($fmt,$value)."</td>");
	}

	public function GetOptions($optvals=false)
	{
	  	//fmt = opt=>val [\r\n] op2=>val2 etc
	 
	 	if(!$optvals) 
		  	$optvals=$this->Get('configuration_options');
		$optvals=str_replace("\r\n","\n",$optvals);
		$optvals=str_replace("\r","\n",$optvals);
	  
	  	$options=array();
	  	$optvals=explode("\n",$optvals);
		foreach($optvals as $optval)		  	
		{
			$optval=explode('=>',$optval);
			$options[$optval[0]]=$optval[count($optval)-1];
	  	}
		return $options;	  
	}

	public function DisplayFull()
	{

	}

	public function EditLink()
	{
		if($this->Get('configuration_editable'))
			parent::EditLink();
		else
			echo ("<td>&nbsp;");
	}

	public function DeleteLink()
    {
		echo("</td>");
	}
			
	public function Display()
	{
		parent::display();
 	}

	public function Save()
	{
		global $HTTP_POST_VARS;
		
		$keep_editing_name=$this->GetFieldName('keep_editing');

		$psv=parent::Save();
				
		if($this->Get('configuration_format')=='FILE')
			$this->SaveFile('configuration_value',file::GetPath('configuration_upload'),'_'.$this->id);		
		if($this->Get('configuration_format')=='IMAGE')
			$this->SaveFile('configuration_value',file::GetPath('configuration_upload'),'_'.$this->id,array('gif','jpg','png'));
		
		if($HTTP_POST_VARS[$keep_editing_name])
		{
			$this->msg='Your Changes Have Been Saved';
			return false;
		}
		return $psv;
	}

	public function Delete()
	{
		parent::Delete();
	}

	public function GatherInputs()
	{
		parent::GatherInputs();
		if($this->Get('configuration_format')=='FILE' or $this->Get('configuration_format')=='IMAGE')
			$this->GatherFile($this->GetFieldName('configuration_value').'_ul','configuration_value');
		if($this->Get('configuration_format')=='DATE')		
			$this->GatherDate('configuration_value');

		return true;
	}

	public function ValidateInputs()
	{
	  	if(!$this->Get('configuration_value') and $this->Get('configuration_min'))
		  	$this->LogError('Please Enter Value','configuration_value');
		  	
		return count($this->errors)==0;
 	}

	public function EditForm()
	{
		echo("</td></tr>");

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");		

		$title=$this->Get('configuration_title');
		if($this->Get('imprint_id'))
		{
			$imprint=new imprint($this->Get('imprint_id'));  
			$title=$imprint->Get('imprint_name').' - '.$title;
		}

		echo("<tr><td class='label'>".$title."<div class='hint'>".$this->Get('configuration_hint')."</div></td><td style='vertical-align:middle;'>");		
		if($this->Get('configuration_format')=='FILE')
			form::drawInput('file',$this->GetFieldName('configuration_value').'_ul','',array('class'=>$this->GetError('configuration_value')?'error':'text'));		  
		else if($this->Get('configuration_format')=='IMAGE')
		{
			if($this->Get('configuration_value'))		
				echo("<img src='".$this->GetThumb(200,200)."'><br><br>");
			form::drawInput('file',$this->GetFieldName('configuration_value').'_ul','',array('class'=>$this->GetError('configuration_value')?'error':'text'));		  
		}
		else if($this->Get('configuration_format')=='SELECT')
		{
			form::drawSelect($this->GetFieldName('configuration_value'),$this->GetOptions(),$this->Get('configuration_value'),array('class'=>$this->GetError('configuration_value')?'error':'text'));		  
		}
		else if($this->Get('configuration_format')=='DATE')		
			form::drawDAteInput($this->GetFieldName('configuration_value'),new DBDAte($this->Get('configuration_value')),array('class'=>$this->GetError('configuration_value')?'error':''));		  
		else if($this->Get('configuration_format')=='TIME')		
			form::drawTimeSelect($this->GetFieldName('configuration_value'),$this->Get('configuration_value'),array('class'=>$this->GetError('configuration_value')?'error':''));		  
		else if($this->Get('configuration_format')=='PASSWORD')
			form::drawInput('password',$this->GetFieldName('configuration_value'),sprintf($this->Get('configuration_format'),$this->Get('configuration_value')),array('maxlength'=>$this->Get('configuration_max'),'class'=>$this->GetError('configuration_value')?'error':'text'));
		else if($this->Get('configuration_format')=='%s' and !$this->Get('configuration_max'))
			form::drawTextArea($this->GetFieldName('configuration_value'),sprintf($this->Get('configuration_format'),$this->Get('configuration_value')),array('class'=>'configuration_'.$this->Get('configuration_key').' '.($this->GetError('configuration_value')?'error':'text')));
		else if($this->Get('configuration_format')=='%s' and $this->Get('configuration_max')<128)
		{
			form::drawTextInput($this->GetFieldName('configuration_value'),sprintf($this->Get('configuration_format'),$this->Get('configuration_value')),array('maxlength'=>$this->Get('configuration_max'),'class'=>$this->GetError('configuration_value')?'error':'text','onkeyup'=>"document.getElementById('".$this->GetFieldName('length_ind')."').innerHTML='You have '+(".$this->Get('configuration_max')."-this.value.length)+' characters left';"));
			echo("<div class='config_length' id='".$this->GetFieldName('length_ind')."'>You have ".($this->Get('configuration_max')-strlen($this->Get('configuration_value')))." characters left</div>");
		}
		else if($this->Get('configuration_format')=='%s')
			form::drawTextInput($this->GetFieldName('configuration_value'),sprintf($this->Get('configuration_format'),$this->Get('configuration_value')),array('maxlength'=>$this->Get('configuration_max'),'class'=>$this->GetError('configuration_value')?'error':'text'));
		else
			form::drawInput('text',$this->GetFieldName('configuration_value'),sprintf($this->Get('configuration_format'),$this->Get('configuration_value')),array('class'=>$this->GetError('configuration_value')?'error':'text'));
		echo("</td></tr>");

		echo("<tr><td colspan='2' class='save_actions'>");
	}
	
 	/**THUMBNAILING**/
	public function GetThumb($width,$height,$crop=false)
 	{ 	  
		if($this->id)
			return file::GetPath('configuration_display').imaging::ResizeCached($this->Get('configuration_value'),file::GetPath('configuration_upload'),$width,$height,$crop);
		return '';
	}

	public function SaveLink()
    {
		form::DrawHiddenInput($this->GetFieldName('keep_editing'),0);

      	if($this->id)
			form::DrawButton('','Save & Continue',array('onclick'=>"document.getElementById('".$this->GetFieldName('keep_editing')."').value=1;this.form.submit();"));
		else
			form::DrawButton('','Create & Continue',array('onclick'=>"document.getElementById('".$this->GetFieldName('keep_editing')."').value=1;this.form.submit();"));		
			
      	if($this->id)
			form::DrawSubmit('','Save & Exit');
		else
			form::DrawSubmit('','Create & Exit');		
	}
	
};

class configuration
{
	public function __construct()
	{
	}

	static function GetConfigRecordSet()
	{
		return database::query("SELECT * FROM configuration");
 	}
	
	static function Dump($check_override=true)
	{
        $rs=database::query("SELECT * FROM configuration ORDER BY configuration_key");
		while($rec=database::fetch_array($rs))
		{
			echo($rec['configuration_key'].'::'.configuration::Get($rec['configuration_key'],$check_override).'<br>');		  
		}
	  
	}
	
 	static function Get($key,$check_override=true)
	{	 	 
		//override?
		if($check_override and configuration::IsSetOverride($key))
			return configuration::GetOverride($key);
			
		//DB.	
        $rs=database::query("SELECT * FROM configuration WHERE configuration_key='".$key."'");
        if(database::num_rows($rs)>0)
        {
			$rec=database::fetch_array($rs);

		  	//if special format, make normal for now...
			$fmt=$rec['configuration_format'];
			if($fmt=='SELECT')		$fmt='%s';
			if($fmt=='FILE')		$fmt='%s';
			if($fmt=='IMAGE')		$fmt='%s';
			if($fmt=='PASSWORD')	$fmt='%s';
			if($fmt=='DATE')		$fmt='%s';
			if($fmt=='TIME')		$fmt='%s';
			
			return sprintf($fmt,$rec['configuration_value']);
 		}
 		return '';
	}

	static function GetThumb($key,$width,$height,$crop=false)
	{
		$configuration_item=new configuration_item($key);
		if($configuration_item->id)
			return $configuration_item->GetThumb($width,$height,$crop);
		return '';
	}
 
	
 	static function Set($key,$value)
	{
		$configuration_item=new configuration_item($key);
		if($configuration_item->id)
		{
			$configuration_item->Set('configuration_value',$value);
			$configuration_item->Update();
        }
	}

 	static function SetOverride($key,$value)
	{
		$configuration_item=new configuration_item($key);
		Session::Set($configuration_item->GetFieldName($key),$value);
	}

 	static function ClearOverride($key)
	{
		$configuration_item=new configuration_item($key);
		Session::SessionUnSet($configuration_item->GetFieldName($key));
	}


 	static function GetOverride($key)
	{
		$configuration_item=new configuration_item($key);
		return Session::Get($configuration_item->GetFieldName($key));
	}
	
 	static function IsSetOverride($key)
	{
		$configuration_item=new configuration_item($key);
		return Session::SessionIsSet($configuration_item->GetFieldName($key));
	}	
};

?>