<?php

class feature extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('features','feature_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function ToURL()
	{
		if($this->Get('feature_url'))
			return $this->Get('feature_url');
//		if($this->Get('feature_file'))
//			return file::GetPath('feature_display').$this->Get('feature_file');
		return '#';		
	}

	static function ListAll()
	{
	  	$list=new DBRowSetEX('features','feature_id','feature',"feature_active=1 AND feature_type='FEATURE'",'feature_order');
	  	$list->Retrieve();
	  	
	  	echo("<div class='row'>");
	  	foreach($list->items as $i=>$feature)
		{
		 	$url=$feature->ToURL();
		  	echo("<div class='col-md-4 col-sm-6 col-xs-12'>");
			echo("<div class='feature feature".($i%6)." feature_".$feature->Get('feature_class')."' onclick=\"document.location='".$url."';\">");
			echo("<div class='feature_image'>");
			if($feature->Get('feature_file'))
				echo("<img src='".$feature->GetThumb(440,240,true)."'>");
			echo("</div>");
			echo("<div class='feature_headline'>".$feature->Get('feature_headline')."</div>");
			echo("<div class='feature_content'><div class='feature_intro'>".$feature->Get('feature_intro')."</div></div>");
			if($feature->Get('feature_headline2'))
				echo("<div class='feature_link'><a class='feature_link1' href='".$url."'>".$feature->Get('feature_headline2')."</a></div>");
			echo("</div>");
			echo("</div>");
		}
		echo("</div>");
	}	
 	
	public function DisplayEditable()
	{
		$this->SortLink('feature_order');
	 	echo("<td><a target='_blank' href='".$this->ToURL()."'>".$this->Get('feature_headline')."</a></td>");
		echo("<td>".($this->Get('feature_active')?'X':'')."</td>");
	}

			
	public function EditForm()
	{
		global $HTTP_POST_VARS;

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");
		echo("<tr><td class='label'>Headline<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('feature_headline'),$this->Get('feature_headline'),array('class'=>$this->GetError('feature_headline')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Status<div class='hint'></div></td><td>");
		form::DrawSelect($this->GetFieldName('feature_active'),array('Active'=>1,'Disabled'=>0),$this->Get('feature_active'),array('class'=>$this->GetError('feature_active')?'error':'text'));
		echo("</td></tr>");	

		echo("<tr><td class='section' colspan='2'>Image</td></tr>");
		if($this->Get('feature_file'))
		{
			echo("<tr><td class='label'>Image</td><td>");
			echo("<img src='".$this->GetThumb(440,240,true)."'>");
			echo("</td></tr>");
		}		
		echo("<tr><td class='label'>Upload Image</td><td><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('feature_file_ul'),'',array('class'=>$this->GetError('news_title')?'error':'file'));
		echo("</td></tr>");			
		echo("<tr><td class='label'>Content</td><td><div class='hint'></div>");
		form::DrawTextArea($this->GetFieldName('feature_intro'),$this->Get('feature_intro'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Link To URL</td><td><div class='hint'></div>");
		form::DrawTextInput($this->GetFieldName('feature_url'),$this->Get('feature_url'),array('class'=>$this->GetError('feature_url')?'error':'text'));
		echo("</td></tr>");	
//		echo("<tr><td class='label'>OR Upload File</td><td><div class='hint'></div>");
//		form::DrawFileInput($this->GetFieldName('feature_file_ul'),'',array('class'=>$this->GetError('news_title')?'error':'file'));
//		echo("</td></tr>");			
//		if($this->Get('feature_file'))
//		{
//			echo("<tr><td class='label'>Current File</td><td>");
//			echo("<a target='_blank' href='".$this->ToURL()."'>View Current File....</a> ");
//			form::DrawCheckBox($this->GetFieldName('feature_file_remove'),1,0,array('class'=>'X'));
//			echo(" Remove");
//			echo("</td></tr>");
//		}		
		echo("<tr><td class='label'>Link Text<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('feature_headline2'),$this->Get('feature_headline2'),array('class'=>$this->GetError('feature_headline2')?'error':'text'));
		echo("</td></tr>");	
		
		echo("<tr><td colspan='2' class='save_actions'>");

 	}


	public function GatherInputs()
	{
		parent::GatherInputs();

		//**FILE**// 
		global $HTTP_POST_VARS;
		if($HTTP_POST_VARS[$this->GetFieldName('feature_file_remove')])
			$this->Set('feature_file','');
		$this->GatherFile($this->GetFieldName('feature_file_ul'),'feature_file');

 	}
 	
 	public function ValidateInputs()
 	{
		if(!parent::ValidateInputs())
		    return false;		
		if(!$this->Get('feature_headline'))
			$this->LogError('Please Enter a Headline For the Feature','feature_headline');
	
		$this->ValidateURL('feature_url');
			
		return count($this->errors)==0;
  	}

	public function Save()
	{
		$keep_editing_name=$this->GetFieldName('keep_editing');

		$ret=parent::Save();
//		$this->Retrieve();

		if($ret)
		{
			$this->SaveImageFile('feature_file',file::GetPath('feature_upload'),$this->id);
			
		}		
		

		global $HTTP_POST_VARS;
		if($HTTP_POST_VARS[$keep_editing_name] and $ret)
		{
			$this->msg='Your Changes Have Been Saved';
			return false;
		}
		return $ret;	
	}
	
	public function Delete()
	{
		//**FILE**//
		$this->DeleteFile('feature_file',file::GetPath('feature_upload'));
		$this->DeleteCrop('feature_file');

		parent::Delete();
	}

 	/**THUMBNAILING**/
	public function GetThumb($width,$height,$crop=false)
 	{ 	  
		if($this->id)
		{	  
//			$src=$this->CropAsSaved(file::GetPath('feature_display'),file::GetPath('feature_upload'),'feature_file',$width,$height);
			$src=$this->Get('feature_file');
			return file::GetPath('feature_display').imaging::ResizeCached($src,file::GetPath('feature_upload'),$width,$height,$crop);
		}
		return '';
	}	
};

?>