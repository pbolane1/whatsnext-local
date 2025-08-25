<?php

class content extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->Allowfiles();
		$this->EstablishTable('content','content_id');
		$this->Retrieve();
	}

	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function ToURL()
	{		
	 	if($this->Get('content_static_url'))
	 		return $this->Get('content_static_url');
	 	if($this->Get('content_external_url'))
	 		return $this->Get('content_external_url');
	 	if($this->Get('content_url_slug'))
			return _navigation::GetBaseURL().$this->Get('content_url_slug').'/'.$this->Get('content_url').'/';
	 	if($this->Get('content_url'))
			return _navigation::GetBaseURL().$this->Get('content_url').'/';
		return _navigation::GetBaseURL()."pages/content.php?content_id=".$this->id;
	}
 	
	public function Display()
	{
		if(!$this->id)
		    return;		    
		//if(!trim(strip_tags($this->GetCleanContent(),'A,IFRAME,IMG')))
		//	return;


		echo("<div class='content_".$this->Get('content_special')."'>");
		if($this->Get('content_headline'))
		{
			echo("<h1>".$this->Get('content_headline')."</h1>");
			echo("<div class='sep'></div>");
		}
		if(trim(strip_tags($this->GetCleanContent(),'A,IFRAME,IMG')))
			echo("<div class='wysiwyg-body ".$this->Get('content_editclass')."'>".($this->GetCleanContent())."<br style='clear:both'></div>");
		echo("</div>");
	}
	
	public function DisplayBanner()
	{
		if($this->Get('content_file'))
			echo("<img src='".$this->GetThumb(1440,474,true)."'>");
	}

	public function DisplayNavigation()
	{
		$list=new DBRowSetEx('content','content_id','content',"parent_id='".$this->id."' AND content_navigation=1",'content_order');
		$list->Retrieve();

		if(!$list->items)
			echo("<li><a href='".$this->ToURL()."'>".$this->Get('content_name')."</a></li>");
		else		
		{
			echo("<li class='dropdown'>");
			echo("<a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false' href='#'>".$this->Get('content_name')."</a>");
			{
				echo("<ul class='dropdown-menu'>");
				echo("<li><a href='".$this->ToURL()."'>".$this->Get('content_name')."</a></li>");
				$list->Each('DisplayNavigation');
				echo("</ul>");
			}
			echo("</li>");
		}				
	}

	public function GetCleanContent()
	{
		return wysiwyg::GetCleanContent($this->Get('content_content'));
	}

	public function DisplayEditable()
	{
		if($this->GetFlag('DROPSORT'))
			$this->SortLink('content_order');
	 	echo("<td><a target='_blank' href='".$this->ToURL()."'>".$this->Get('content_name')."</a></td>");
	 	echo("<td>".($this->Get('content_navigation')?'Yes':'No')."</td>");
		echo("<td>");
		if($this->GetFlag('SUBPAGES'))
		{
			$list=new DBRowSetEx('content','content_id','content',"parent_id='".$this->id."'",'content_order');
			echo("<a href='?content_parent_id=".$this->id.$this->GetFormExtraParams('content_parent_id')."'>Sub-Pages (".$list->GetTotalAvailable().")</a><br>");
		}
		echo("</td>");	
	}
	
	
	public function EditLink()
	{
		parent::EditLink();
//		$this->GenericLink('?manage_parent_id='.$this->id,'Sub-Pages',false);		
	}
	
	public function DeleteLink()
    {
		if($this->Get('content_static'))
			return false;
		else 
			parent::DeleteLink();
	}
			

	public function EditForm()
	{
		global $HTTP_POST_VARS;

		if($this->Get('content_css'))
			wysiwyg::SetPath(_navigation::GetBaseURL().$this->Get('content_css'));

		echo("<tr><td class='label'>Page Name<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('content_name'),$this->Get('content_name'),array('class'=>$this->GetError('content_name')?'error':'text'));
		echo("</td></tr>");	

		echo("<tr><td class='label'>Headline<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('content_headline'),$this->Get('content_headline'),array('class'=>$this->GetError('content_headline')?'error':'text'));
		echo("</td></tr>");	
	
		if($this->Get('content_area')!=='HOME')
		{
			echo("<tr><td class='section' colspan='2'>Banner Image</td></tr>");
			if($this->Get('content_file'))
			{
				echo("<tr><td class='label'>Image</td><td>");
				echo("<img src='".$this->GetThumb(1440/4,474/4,true)."'>");
				echo("</td></tr>");
			}		
			echo("<tr><td class='label'>Upload Banner Image</td><td><div class='hint'></div>");
			form::DrawFileInput($this->GetFieldName('content_file_ul'),'',array('class'=>$this->GetError('news_title')?'error':'file'));
			echo("</td></tr>");	
		}

		echo("<tr><td class='section' colspan='2'>Page Content</td></tr>");
		echo("<tr><td colspan='2'>");
		form::DrawTextArea($this->GetFieldName('content_content'),$this->Get('content_content'),array('class'=>'wysiwyg'.($this->Get('content_editclass')?'_'.$this->Get('content_editclass'):'')));
		wysiwyg::MakeWYSIWYG($this->GetFieldName('content_content'));
		echo("</td></tr>");

		echo("<tr><td class='label'>Include In Main Navigation?<div class='hint'></div></td><td>");
		form::DrawSelect($this->GetFieldName('content_navigation'),array('No'=>0,'Yes'=>1),$this->Get('content_navigation'),array('class'=>$this->GetError('content_navigation')?'error':'text'));
		echo("</td></tr>");

		if(!$this->Get('content_special'))
		{
			echo("<tr><td class='label'>Child Of<div class='hint'></div></td><td>");
			form::DrawSelect($this->GetFieldName('parent_id'),array(''=>'')+$this->GetParentOptions(0,'',$this->id?array($this->id):'',' - '),$this->Get('parent_id'),array('class'=>$this->GetError('parent_id')?'error':'text'),array('(none)'=>'0'));
			echo("</td></tr>");	
		}

		echo("<tr><td class='section' colspan='2'>Search Engine Related</td></tr>");
		echo("<tr><td class='label'>Page Title</td><td><div class='hint'>Shown in search engine results and browser title bar</div>");
		form::DrawTextInput($this->GetFieldName('content_meta_title'),$this->Get('content_meta_title'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Page Keywords</td><td><div class='hint'>Separate keyword phrases with commas</div>");
		form::DrawTextArea($this->GetFieldName('content_meta_keywords'),$this->Get('content_meta_keywords'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Page Description</td><td><div class='hint'>3-5 sentances of keyword rich text relevant to the page</div>");
		form::DrawTextArea($this->GetFieldName('content_meta_description'),$this->Get('content_meta_description'));
		echo("</td></tr>");


		echo("<tr><td colspan='2' class='save_actions'>");
 	}

	public function SaveLink()
    {
		form::DrawHiddenInput($this->GetFieldName('keep_editing'),0);


		Javascript::Begin();
		echo("
			public function TestSpelling(theform)
			{
			 	theform.submit();
			 	return true;
			 	
			  	var total_mispelled=0;
				t=tinyMCE.activeEditor.plugins.spellchecker;	
				ed=tinyMCE.activeEditor;	
				ed.setProgressState(1);
				t._sendRPC('checkWords', [t.selectedLang, t._getWords()], function(r) 
				{
					if (r.length > 0) 
					{
						t.active = 1;
						t._markWords(r);
						ed.setProgressState(0);
						ed.nodeChanged();
					} 
					else 
					{
						ed.setProgressState(0);
					}
					total_mispelled+=r.length;

					if(total_mispelled)
					{
					  	if(confirm(total_mispelled+' misspellings found.  Click OK to save anyway; click Cancel to stop and correct misspellings.'))
						 	theform.submit(); 	
					}
					else
						theform.submit();

				});
			}			
		");
		Javascript::End();

      	if($this->id)
			form::DrawButton('','Save & Continue',array('onclick'=>"document.getElementById('".$this->GetFieldName('keep_editing')."').value=1;TestSpelling(form);return false;this.form.submit();"));
		else
			form::DrawButton('','Create & Continue',array('onclick'=>"document.getElementById('".$this->GetFieldName('keep_editing')."').value=1;TestSpelling(form);return false;this.form.submit();"));		
			
      	if($this->id)
			form::DrawSubmit('','Save & Exit',array('onclick'=>"TestSpelling(form);return false;"));
		else
			form::DrawSubmit('','Create & Exit',array('onclick'=>"TestSpelling(form);return false;"));		
	}


	public function GatherInputs()
	{
		parent::GatherInputs();
		$this->Set('content_content',wysiwyg::GatherCleanContent($this->Get('content_content')));		
		
		$this->GuessSlug();
		$this->GenerateURL(!$this->ValidateUnique('content_url',"content_url_slug='".$this->Get('content_url_slug')."'"));				
		
		$this->GatherFile($this->GetFieldName('content_file_ul'),'content_file');		
 	}
 	
 	public function ValidateInputs()
 	{
		if(!parent::ValidateInputs())
		    return false;		
//		if(!$this->Get('content_title'))
//			$this->LogError('Please Enter a Title For the Page','content_title');
		return count($this->errors)==0;
  	}

	public function Save()
	{
		$keep_editing_name=$this->GetFieldName('keep_editing');

		$ret=parent::Save();
//		$this->Retrieve();
		
		if($ret)
		{
			$this->SaveImageFile('content_file',file::GetPath('content_upload'),$this->id);
			
		}		
		

		global $HTTP_POST_VARS;
		if($HTTP_POST_VARS[$keep_editing_name])
		{
			$this->msg='Your Changes Have Been Saved';
			return false;
		}
		return $ret;		
	}

	public function Delete()
	{
		//**FILE**//
		$this->DeleteFile('content_file',file::GetPath('content_upload'));
		$this->DeleteCrop('content_file');

		parent::Delete();
	}

 	/**THUMBNAILING**/
	public function GetThumb($width,$height,$crop=false)
 	{ 	  
		if($this->id)
		{	  
//			$src=$this->CropAsSaved(file::GetPath('content_display'),file::GetPath('content_upload'),'content_file',$width,$height);
			$src=$this->Get('content_file');
			return file::GetPath('content_display').imaging::ResizeCached($src,file::GetPath('content_upload'),$width,$height,$crop);
		}
		return '';
	}	

	public function GetMetaTitle()
	{
	 	$ret=$this->Get('content_meta_title');
	 	if(!$ret and $this->Get('content_name'))
		 	$ret=$this->Get('content_name').' | '.'Whats Next Real Estate' ;
	 	else if(!$ret)
		 	$ret='Whats Next Real Estate | Real Estate Buyers Timeline | Real Estate Sellers Timeline' ;
		$ret=str_replace('"','',$ret);
		$ret=str_replace("'",'',$ret);
		return $ret;
	}

	public function GetMetaKeywords()
	{
	 	$ret=$this->Get('content_meta_keywords');
		$ret.=','.$this->Get('content_meta_keywords');
		$ret.=','.$this->Get('content_headline');
		$ret.=','.$this->Get('content_headline2');
		$ret.=','.$this->Get('content_headline3');

		$ret=str_replace('"','',$ret);
		$ret=str_replace("'",'',$ret);
		return $ret;
	}

	public function GetMetaDescription()
	{
	 	$ret=$this->Get('content_meta_description');
	 	if(!$ret)
	 	{
		 	$ret=$this->Get('content_name').' - '.$this->Get('content_content');
	 		$ret=strip_tags($ret);
	 		$ret=Text::LimitWords($ret,64);
	 	}
	 	
		$ret=str_replace('"','',$ret);
		$ret=str_replace("'",'',$ret);
		return $ret;
	}

	public function GetDepth()
	{
		$depth=1;
		$temp=new content($this->Get('parent_id'));
		while($temp->id)
		{
			$depth++;
			$temp=new content($temp->Get('parent_id'));
		}
		return $depth;	  
	}

	public function GetParentOptions($parent_id=0,$arr_cats='',$exclude='',$sep=' / ',$recursive=true)
	{
	  	if(!is_array($arr_cats))
		  	$arr_cats=array();
		if(!$exclude)
			$exclude=array(-1);
		$list=new DBRowSet('content','content_id','content',"content_id NOT IN (".implode(',',$exclude).") AND parent_id='".$parent_id."'",'content_order,content_name');
		$list->Retrieve();
		foreach($list->items as $content)
		{
		  	$arr_cats[$content->GetFullName($sep,true)]=$content->id;
  			if($recursive)
				$arr_cats=$content->GetParentOptions($content->id,$arr_cats,$exclude,$sep);
		}	  
		return $arr_cats;
	}	

	public function GetFullName($sep=' - ')
	{
		$name=array();
		$name[]=$this->Get('content_name');
		
		$parent_id=$this->Get('parent_id');
		while($parent_id)
		{
			$parent=new content($parent_id);
			$name[]=$parent->Get('content_name');
			$parent_id=$parent->Get('parent_id');
		}
		
		$name=array_reverse($name);
		return implode($sep,$name);
	}
	
	public function GetBreadCrumb($link='ADMIN',$params='',$top=true,$sep=' &gt; ')
	{
		$links=array()  ;
		$temp=new content($this->id);
		do
		{
			if($link=='ADMIN')
				$links[]="<a href='?content_parent_id=".$temp->id.$this->GetFormExtraParams('content_parent_id')."' ".html::ProcessParams($params).">".$temp->Get('content_name')."</a>";
			else if($link=='PUBLIC')
				$links[]="<a href='".$temp->ToURL()."' ".html::ProcessParams($params).">".$temp->Get('content_name')."</a>";
			else 
				$links[]=$this->Get('content_name');
			$temp=new content($temp->Get('parent_id'));
		}
		while($temp->id);
		if($top and $link=='ADMIN')
			$links[]="<a href='?content_parent_id=0".$this->GetFormExtraParams('content_parent_id')."'>Top</a>";		
		else if($top and $link=='PUBLIC')
			$links[]="<a href='/index.php'>Top</a>";		
		else if($top)
			$links[]="Top";		
		$links=array_reverse($links);
		return implode ($sep,$links);
	}	
	
	public function GenerateURL($force=false)
	{
		if(!$this->Get('content_url') or $force)
		{
			if(!$this->Get('content_name') or $this->Get('content_area')=='INDEX')
				return;

			$slug=mod_rewrite::ToURL($this->Get('content_name'),'0-9,a-z,A-Z');					
			$this->Set('content_url',$slug);
			while(!$this->ValidateUnique('content_url',"content_url_slug='".$this->Get('content_url_slug')."'"))
				$this->Set('content_url',$slug.'-'.(++$i));
			$this->Update();
		}
		return $this->Get('content_url');		
	}		
	
	public function GuessSlug()
	{
		if($this->Get('content_url_slug'))
			return;

		if($this->Get('parent_id'))
		{
			$temp=new content($this->Get('parent_id'));
			while($temp->id)
			{
				if($temp->Get('content_url') and !$temp->Get('content_url_slug'))
				{
					$this->Set('content_url_slug',$temp->Get('content_url'));
					return;
				}
				$temp=new content($temp->Get('parent_id'));
			}
		}
		$this->Set('content_url_slug','property-management');		
	}				

	public function GetContent($content_area)
	{
		$content=new content();
		$content->InitByKeys('content_area',$content_area);
		return $content->Get('content_content');
	}

};

class default_content extends content
{
	public function default_content($id='')
	{
		parent::content($id);
	}	

	public function DisplayEditable()
	{
	 	echo("<td>".$this->Get('content_name')."</td>");
	}
	
	public function EditForm()
	{
		global $HTTP_POST_VARS;

		if($this->Get('content_css'))
			wysiwyg::SetPath(_navigation::GetBaseURL().$this->Get('content_css'));

		echo("<tr><td class='label'>Area<div class='hint'></div></td><td>".$this->Get('content_name')."</td></tr>");	

		echo("<tr><td class='section' colspan='2'>Content</td></tr>");
		echo("<tr><td colspan='2'>");
		form::DrawTextArea($this->GetFieldName('content_content'),$this->Get('content_content'),array('class'=>'wysiwyg'.($this->Get('content_editclass')?'_'.$this->Get('content_editclass'):'')));
		wysiwyg::MakeWYSIWYG($this->GetFieldName('content_content'));
		echo("</td></tr>");

		echo("<tr><td colspan='2' class='save_actions'>");
 	}	
}

?>