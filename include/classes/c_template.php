<?php

class template extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('templates','template_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{
			$this->Set('template_active',1);
		}
 	}

	static function CopyAll($params_from=array(),$params_to=array())
	{
		if($params_to['agent_id']==$params_from['agent_id'])
			return;
		if(!$params_to['agent_id'] and !$params_to['template_id'])
			return false;

		//clear to.
		$where=array();
		if($params_to['template_id'])
			$where[]="template_id='".$params_to['template_id']."'";
		$where[]="agent_id='".$params_to['agent_id']."'";//copy from me (to a user) OR default to a user.
		database::query("DELETE FROM templates WHERE ".implode(' AND ',$where));

		//copy from
		$where=array("template_active=1");
		if($params_from['template_id'])
			$where[]="template_id='".$params_from['template_id']."'";
		$where[]="agent_id='".$params_from['agent_id']."'";//copy from me (to a user) OR default to a user.
		$where[]="coordinator_id='".$params_from['coordinator_id']."'";//copy from me (to a user) OR default to a user.
		$list=new DBRowSetEX('templates','template_id','template',implode(' AND ',$where),'template_order');
		$list->Retrieve();
		foreach($list->items as $template)
		{
			//copy to.
			//NOPE.//$new_template=new template($params_to['template_id']);
			$new_template=new template();
			$new_template->Copy($template);
			$new_template->Set("agent_id",$params_to['agent_id']);
			$new_template->Set("original_id",$template->id);
			$new_template->Update();

			timeline_item::CopyAll(array('template_id'=>$template->id),array('template_id'=>$new_template->id,'agent_id'=>$params_to['agent_id'],'coordinator_id'=>$params_to['coordinator_id']));
		}
		
	}

	public function DisplayEditable()
	{
		$this->SortLink('template_order');
	 	echo("<td>".$this->Get('template_name')."</td>");
	 	echo("<td>".$this->Get('template_type')."</td>");
	 	echo("<td>".($this->Get('template_default')?'x':'')."</td>");
	 	echo("<td>".($this->Get('template_status')?'Active':'Draft')."</td>");
	 	echo("<td><a href='timeline_items.php?template_id=".$this->id."'>Manage Items</a></td>");
	}

	public function DeleteLink()
    {
		if($this->Get('template_active'))
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('template_active'),0);
			form::DrawSubmit('','DELETE',array('onclick'=>"return confirm('Are you sure you want to disable this agent?');"));
			form::end();
		}
		echo("</td>");
	}


	public function EditLink()
    {
		if(!$this->Get('template_active'))
		{	
		 	echo("<td class='edit_actions'>");
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('template_active'),1);
			form::DrawSubmit('','RE-ACTIVATE',array('onclick'=>"return confirm('Are you sure you want to reactivate this template?');"));//>>>>>?????
			form::end();			
		}
		else
		{
			parent::EditLink();
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('copy').$this->GetFormExtraParams());
			form::DrawSubmit('','Clone',array('onclick'=>"return confirm('Are you sure you want to duplicate this template?');"));
			form::end();

			if($this->Get('agent_id'))
			{
				form::Begin("?".$this->action_parameter."=".$this->GetFormAction('reset').$this->GetFormExtraParams());
				form::DrawSubmit('','Reset',array('onclick'=>"return confirm('Are you sure you want to reset this template?');"));
				form::end();
			}
		}
	}

	public function DoAction($action)
	{
	 	parent::DoAction($action);
	 	if($action==$this->GetFormAction('reset'))
			$this->ResetTemplates();
	 	if($action==$this->GetFormAction('undelete'))
	 	{
			$this->Set('template_order',10000);
			$this->Set('template_active',1);
			$this->Update();
		}
	 	if($action==$this->GetFormAction('copy'))
		{
			$new_template=new template();
			$new_template->Copy($this);
			$new_template->Set('template_name','Copy Of '.$new_template->Get('template_name'));
			$new_template->Set('template_default',0);
			$new_template->Set('original_id',0);
			$new_template->Update();
			timeline_item::CopyAll(array('agent_id'=>$this->Get('agent_id'),'template_id'=>$this->id),array('agent_id'=>$new_template->Get('agent_id'),'template_id'=>$new_template->id));
		}
	}	

	public function ResetTemplates()
	{
 	 	$old=array();	 	
 	 	$map=array();	 	

		///remember the old tempaltes.
		$where=array('template_id='.$this->id);
		$where[]='template_active=1';
	  	$list=new DBRowSetEX('templates','template_id','template',implode(' AND ',$where),'template_order');
		$list->Retrieve();
	  	foreach($list->items as $template)
			$old[$template->Get('original_id')]=$template->id;

		//give me new tempaltes
		template::CopyAll(array('template_id'=>$this->Get('original_id'),'agent_id'=>0),array('template_id'=>$this->id,'agent_id'=>$this->Get('agent_id')));

		///map the old tempaltes to the new ones.
		$where=array('template_id='.$this->id);
	  	$list=new DBRowSetEX('templates','template_id','template',implode(' AND ',$where),'template_order');
		$list->Retrieve();
	  	foreach($list->items as $template)
	  	{
			foreach($old as $original_id=>$tempalte_id)
			{
				if($original_id==$template->Get('original_id'))
					$map[$tempalte_id]=$template->id;
			}
		}
		//give my user new templates.
	 	$where=array("template_id='".$this->id."'");
	 	$where[]="user_active=1";
	  	$list=new DBRowSet('users','user_id','user',implode(' AND ',$where),'user_name');
		$list->Retrieve();
	  	foreach($list->items as $user)
	  	{
			//timeline_item::CopyAll(array('template_id'=>$map[$user->Get('template_id')],'agent_id'=>$this->Get('agent_id'),'user_id'=>0),array('agent_id'=>$user->Get('agent_id'),'user_id'=>$user->id));		
			$user->Set('template_id',$map[$user->Get('template_id')]);
			$user->Update();			
		}
	}

	public function AgentCardJS($params=array(),$container='')
	{
		$js2='';
		if($params['edit'])
			$js2.="jQuery('#".$this->GetFormAction('edit')."').focus();";
		$js2.="height_handler();";

		$passparams=array();
		foreach($params as $k=>$v)
			$passparams[]=$k.'='.$v;
		$js="ObjectFunctionAjax('template','".$this->id."','AgentCard','".$container."','".$this->GetFieldName('AgentCardForm')."','','".implode('&',$passparams)."',function(){".$js2."});";
		
		return $js;
	}

	public function NewAgentCard($params=array())
	{
		$agent=new agent($this->Get('agent_id'));
		$js="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."''ListTemplates','".$agent->GetFieldName('ListTemplatesContainer')."','null','','action=".$this->GetFormAction('save')."',function(){height_handler();});";
		echo("<div class='card card_new' onclick=\"".$js."\" data-info='TEMPLATES_NEW' data-info-none='none'>");
		echo("<div class='box_inner'>");
		echo('<div class="card_heading">');
		echo("<h3><i class='fa fa-plus'></i> New Template</h3>");
		echo('</div>');
		echo('<div class="card_body">');
		echo('<div class="card_content">');
		echo("<br>");
		echo('</div>');
		echo('</div>');
		echo('</div>');
		echo('</div>');		
	}

	public function AgentCard($params=array())
	{
		$agent=new agent($this->Get('agent_id'));
		$savejs="UpdateWYSIWYG();";
		$savejs.=$this->AgentCardJS(array('action'=>$this->GetFormAction('save')));
		if(!$this->id)
			$savejs='';

		$this->SetFlag('ALLOW_BLANK');
		if(!$params['parent_action'])
			$this->ProcessAction();

		$class='';
		if(!$this->id)	
			$class='card_new';
		if(!$this->Get('template_active'))	
			$class='card_deleted';

		form::Begin($this->GetFieldName('AgentCardContainer'),'POST',true,array('id'=>$this->GetFieldName('AgentCardForm')));
		echo('<div class="card '.$class.'">');
		echo("<div class='box_inner'>");
		echo('<div class="card_heading agent_bg_color2">');
//		if($params['edit']=='user_name' or !$this->id)
		$placeholder=$this->id?"Template Name":"New Template";
		$placeholder="Template Name";
		$attr=array('Placeholder'=>$placeholder,'onchange'=>$savejs,'class'=>'H3','data-info'=>'TEMPLATES_NAME');
		if(!$this->Get('template_active'))
			$attr['disabled']='disabled';
		form::DrawTextInput($this->GetFieldName('template_name'),$this->Get('template_name'),$attr);
		echo('</div>');
		echo('<div class="card_body">');
		echo('<div class="card_content">');
		echo('<div class="line">');
		$attr=array('placeholder'=>'Headline','onchange'=>$savejs,'data-info'=>'TEMPLATES_HEADLINE');
		if(!$this->Get('template_active'))
			$attr['disabled']='disabled';
		form::DrawTextInput($this->GetFieldName('template_headline'),$this->Get('template_headline'),$attr);
		echo('</div>');
		echo('<div class="line" data-info="TEMPLATES_CONTENT" data-info-none="none">');
		if(!$this->Get('template_active'))
			echo("<div class='wysiwyg_inactive'>".$this->Get('template_content')."</div>");
		else
			form::DrawTextArea($this->GetFieldName('template_content'),$this->Get('template_content'),array('class'=>'wysiwyg_input','onchange'=>$savejs));
		$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
		$wysiwyg_info.="onchange_callback:function(){".$savejs."},\r\n";
		wysiwyg::RegisterMode($this->GetFieldName('SIMPLE_LINK_HEADLINES'),$wysiwyg_info);
		form::MakeWYSIWYG($this->GetFieldName('template_content'),$this->GetFieldName('SIMPLE_LINK_HEADLINES'));
		echo('</div>');
		echo('<div class="card_links">');
		if(!$this->id)
		{
			$js="UpdateWYSIWYG();";
			$js.="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."''ListTemplates','".$agent->GetFieldName('ListTemplatesContainer')."','".$this->GetFieldName('AgentCardForm')."','','action=".$this->GetFormAction('save')."&parent_action=1',function(){height_handler();});";
			echo("<a data-info='TEMPLATES_ADD' data-info-none='none' class='button' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title=''><i class='fa fa-plus'></i> Add Template</a>");
		}
		else if(!$this->Get('template_active'))
		{
			$js="UpdateWYSIWYG();";
			$js.="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."''ListTemplates','".$agent->GetFieldName('ListTemplatesContainer')."','".$this->GetFieldName('AgentCardForm')."','','action=".$this->GetFormAction('undelete')."&parent_action=1',function(){height_handler();});";
			echo("<a data-info='TEMPLATES_RESTORE' data-info-none='none' class='button' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Restore'><i class='fa fa-trash-restore'></i></a>");
		}
		else
		{
			echo("<a data-info='TEMPLATES_TIMELINE' data-info-none='none' class='button' href='edit_timeline.php?template_id=".$this->id."' data-toggle='tooltip' title='Manage Timeline'><i class='fa fa-list'></i></a>");
			$js="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."''ListTemplates','".$agent->GetFieldName('ListTemplatesContainer')."','null','','action=".$this->GetFormAction('delete')."&parent_action=1',function(){height_handler();});";
			echo("<a data-info='TEMPLATES_DELETE' data-info-none='none' class='button' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
			$js="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."''ListTemplates','".$agent->GetFieldName('ListTemplatesContainer')."','null','','action=".$this->GetFormAction('copy')."&parent_action=1',function(){height_handler();});";
			echo("<a data-info='TEMPLATES_COPY' data-info-none='none' class='button' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Make A Copy'><i class='fa fa-copy'></i></a>");
			if($this->Get('agent_id') and $this->Get('original_id'))
			{
				$js="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."'e'ListTemplates','".$agent->GetFieldName('ListTemplatesContainer')."','".$this->GetFieldName('AgentCardForm')."','','action=".$this->GetFormAction('reset')."&parent_action=1',function(){height_handler();});";
				echo("<a data-info='TEMPLATES_RESET' data-info-none='none' class='button' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Reset'><i class='fa fa-sync'></i> </a>");
			}
		}
		echo('</div>');

		echo('</div>');
		echo('</div>');
		echo('</div>');
		echo('</div>');
		form::End();
	}
			
	public function EditForm()
	{
		global $HTTP_POST_VARS;

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");
		echo("<tr><td class='label'>Name<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('template_name'),$this->Get('template_name'),array('class'=>$this->GetError('template_name')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Type</td><td>");
		form::DrawSelect($this->GetFieldName('template_type'),array('Buyer'=>'BUYER','Seller'=>'SELLER'),$this->Get('template_type'),array('class'=>$this->GetError('template_type')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Default</td><td>");
		form::DrawSelect($this->GetFieldName('template_default'),array(''=>'','Default'=>'1'),$this->Get('template_default'),array('class'=>$this->GetError('user_type')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>".REQUIRED." Status</td><td>");
		form::DrawSelect($this->GetFieldName('template_status'),array('Draft'=>'0','Active'=>'1'),$this->Get('template_status'),array('class'=>$this->GetError('template_status')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Headline<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('template_headline'),$this->Get('template_headline'),array('class'=>$this->GetError('template_headline')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Content<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('template_content'),$this->Get('template_content'),array('class'=>$this->GetError('template_content')?'error':'text'));
		form::MakeWYSIWYG($this->GetFieldName('template_content'),$this->Get('agent_id')?'SIMPLE_LINK_HEADLINES':'');
		echo("</td></tr>");
//		echo("<tr><td class='label'>Status<div class='hint'></div></td><td>");
//		form::DrawSelect($this->GetFieldName('template_active'),array('Active'=>1,'Disabled'=>0),$this->Get('template_active'),array('class'=>$this->GetError('template_active')?'error':'text'));
//		echo("</td></tr>");	

		echo("<tr><td colspan='2' class='save_actions'>");

 	}


	public function GatherInputs()
	{
		parent::GatherInputs();

		//**FILE**// 
		global $HTTP_POST_VARS;
		if($HTTP_POST_VARS[$this->GetFieldName('template_file_remove')])
			$this->Set('template_file','');
		$this->GatherFile($this->GetFieldName('template_file_ul'),'template_file');

 	}
 	
 	public function ValidateInputs()
 	{
		if($this->GetFlag('ALLOW_BLANK'))
			return true;

		if(!parent::ValidateInputs())
		    return false;		
		if(!$this->Get('template_name'))
			$this->LogError('Please Enter a Name For the template','template_name');
	
		$this->ValidateURL('template_url');
			
		return count($this->errors)==0;
  	}

	public function Save()
	{
		$keep_editing_name=$this->GetFieldName('keep_editing');
		$ret=parent::Save();
//		$this->Retrieve();
		if($ret)
		{
			$this->SaveImageFile('template_file',file::GetPath('template_upload'),$this->id);			
			if($this->Get('template_default'))
			{
				database::query("UPDATE templates SET template_default=0 WHERE template_type='".$this->Get('template_type')."' AND template_id!='".$this->id."' AND coordinator_id='".$this->Get('coordinator_id')."' AND agent_id='".$this->Get('agent_id')."'");				
			}
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
		$this->Set('template_active',0);
		$this->Update();
	}

	public function xDelete()

	{
		//**FILE**//
		$this->DeleteFile('template_file',file::GetPath('template_upload'));
		$this->DeleteCrop('template_file');

		parent::Delete();
	}

 	/**THUMBNAILING**/
	public function GetThumb($width,$height,$crop=false)
 	{ 	  
		if($this->id)
		{	  
//			$src=$this->CropAsSaved(file::GetPath('template_display'),file::GetPath('template_upload'),'template_file',$width,$height);
			$src=$this->Get('template_file');
			return file::GetPath('template_display').imaging::ResizeCached($src,file::GetPath('template_upload'),$width,$height,$crop);
		}
		return '';
	}	
};

?>