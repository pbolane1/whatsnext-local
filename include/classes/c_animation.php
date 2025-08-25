<?php

class animation extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('animations','animation_id');
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
		$file=file::GetPath('animation_display').$this->Get('animation_file');
		if(file::GetExtension($file)=='gif')
			echo("<div class='flare flare_gif'><img src='' data-src='".$file."'></div>");			
		if(file::GetExtension($file)=='json')
			echo("<div class='flare flare_json'><lottie-player src='".$file."' background='transparent' speed='1' loop autoplay></lottie-player></div>");
	}

	public function DisplayFull()
	{
		$file=file::GetPath('animation_display').$this->Get('animation_file');
		if(file::GetExtension($file)=='gif')
			echo("<img src='".$file."' style='width:100px'>");			
		if(file::GetExtension($file)=='json')
			echo("<lottie-player  style='width:100px' src='".$file."' background='transparent' speed='1' loop autoplay></lottie-player>");
		
	}

	public function DisplayEditable()
	{
	 	echo("<td><a target='_blank' href='".file::GetPath('animation_display').$this->Get('animation_file')."'>".$this->Get('animation_name')."</a></td>");
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
		form::DrawTextInput($this->GetFieldName('animation_name'),$this->Get('animation_name'),array('class'=>$this->GetError('animation_name')?'error':'text'));
		echo("</td></tr>");


		if($this->Get('animation_file'))
		{
			echo("<tr><td class='label'>File</td><td colspan='3'>");
			$this->DisplayFull();
			echo("</td></tr>");
		}		

		echo("<tr><td class='label'>Upload File (gif or lottie josn)</td><td colspan='3'><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('animation_file_ul'),'',array('class'=>$this->GetError('animation_file_ul')?'error':'file'));
		echo("</td></tr>");			

 		echo("<tr><td colspan='2' class='save_actions'>");
	}


	public function GatherInputs()
	{
		parent::GatherInputs();

		//**FILE**// 
		global $HTTP_POST_VARS;
		if($HTTP_POST_VARS[$this->GetFieldName('animation_file_remove')])
			$this->Set('animation_file','');
		$this->GatherFile($this->GetFieldName('animation_file_ul'),'animation_file');

		if(!$this->Get('animation_name'))
			$this->Set('animation_name',$this->upload_files['animation_file']['name']);

 	}
 	
 	public function ValidateInputs()
 	{
		if($this->GetFlag('ALLOW_BLANK'))
			return true;

		if(!parent::ValidateInputs())
		    return false;		
		if(!$this->Get('animation_name'))
			$this->LogError('Please Enter a Name For the Item','animation_title');
	
		return count($this->errors)==0;
  	}

	public function Save()
	{
		$ret=parent::Save();

		if($ret)
		{
			$this->SaveFile('animation_file',file::GetPath('animation_upload'),$this->id,array('gif','json'));
		}				
		return count($this->errors)==0;
	}
	
	public function Delete()
	{
		//**FILE**//
		$this->DeleteFile('animation_file',file::GetPath('animation_upload'));

		parent::Delete();
	}
};


?>