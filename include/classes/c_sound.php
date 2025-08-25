<?php

class sound extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('sounds','sound_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{

		}
 	}

	public function DisplayShort()
	{
		$file=file::GetPath('sound_display').$this->Get('sound_file');
		echo("<audio class='flare_sound' src='".$file."' style='display:none'></audio>");		
	}

	public function DisplayFull()
	{
		$file=file::GetPath('sound_display').$this->Get('sound_file');
		echo("<audio controls src='".$file."'></audio>");		
	}
	
	public function DisplayEditable()
	{
	 	echo("<td><a target='_blank' href='".file::GetPath('sound_display').$this->Get('sound_file')."'>".$this->Get('sound_name')."</a></td>");
	 	echo("<td>");
	 	$this->DisplayFull();
	 	echo("</td>");
	}
			
	public function EditForm()
	{
		global $HTTP_POST_VARS;

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");

		$class='';
		$style='';
		echo("<tr><td class='label'>Name<div class='hint'></div></td><td colspan='3'>");
		form::DrawTextInput($this->GetFieldName('sound_name'),$this->Get('sound_name'),array('class'=>$this->GetError('sound_name')?'error':'text'));
		echo("</td></tr>");


		if($this->Get('sound_file'))
		{
			echo("<tr><td class='label'>File</td><td colspan='3'>");
			$this->DisplayFull();
			echo("</td></tr>");
		}		

		echo("<tr><td class='label'>Upload File (mp3/wav)</td><td colspan='3'><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('sound_file_ul'),'',array('class'=>$this->GetError('sound_file_ul')?'error':'file'));
		echo("</td></tr>");			

		echo("<tr><td colspan='2' class='save_actions'>");
 	}


	public function GatherInputs()
	{
		parent::GatherInputs();


		//**FILE**// 
		global $HTTP_POST_VARS;
		if($HTTP_POST_VARS[$this->GetFieldName('sound_file_remove')])
			$this->Set('sound_file','');
		$this->GatherFile($this->GetFieldName('sound_file_ul'),'sound_file');
		
		if(!$this->Get('sound_name'))
			$this->Set('sound_name',$this->upload_files['sound_file']['name']);
		
 	}
 	
 	public function ValidateInputs()
 	{
		if($this->GetFlag('ALLOW_BLANK'))
			return true;

		if(!parent::ValidateInputs())
		    return false;		
		if(!$this->Get('sound_name'))
			$this->LogError('Please Enter a Name For the Item','sound_title');
	
		return count($this->errors)==0;
  	}

	public function Save()
	{
		$ret=parent::Save();

		if($ret)
		{
			$this->SaveFile('sound_file',file::GetPath('sound_upload'),$this->id,array('mp3','wav'));
		}		
		return count($this->errors)==0;
	}
	
	public function Delete()
	{
		//**FILE**//
		$this->DeleteFile('sound_file',file::GetPath('sound_upload'));

		parent::Delete();
	}
};


?>