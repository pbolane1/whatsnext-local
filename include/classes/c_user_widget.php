<?php

class user_widget extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('user_widgets','user_widget_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function DisplayFull()
	{
		echo("<div class='toggle'>");
		echo("<a class='toggle_header agent_bg_color2 agent_bg_color1_hover' data-toggle='collapse' href='#".$this->GetFieldName('toggle')."' role='button' aria-expanded='false' aria-controls='".$this->GetFieldName('toggle')."' onclick=\"if(jQuery('I.icon',this).hasClass('fa-plus')){jQuery('I.icon',this).removeClass('fa-plus');jQuery('I.icon',this).addClass('fa-minus');}else{jQuery('I.icon',this).removeClass('fa-minus');jQuery('I.icon',this).addClass('fa-plus');}\"><i class='icon fas fa-plus'></i> ".$this->Get('user_widget_title')."</a>");
		echo("<div class='toggle_body collapse' id='".$this->GetFieldName('toggle')."'>");
		echo("<div class='toggle_content'>".$this->Get('user_widget_content')."</div>");
		echo("</div>");
		echo("</div>");
	}

	public function DoAction($action)
	{
	 	parent::DoAction($action);
	 	if($action==$this->GetFormAction('sort_up'))
	 	{
			$this->Set('user_widget_order',$this->Get('user_widget_order')-1.01);
			$this->Update();
		}
	 	if($action==$this->GetFormAction('sort_down'))
	 	{
			$this->Set('user_widget_order',$this->Get('user_widget_order')+1.01);
			$this->Update();
		}

	}	

	public function NewAgentCard($params=array())
	{
		$user=new user($params['user_id']);
		$agent=new agent($user->Get('agent_id'));
		$js="ObjectFunctionAjax('agent','".$agent->id."','EditUserWidgets','user_widgets_container','null','','action=".$this->GetFormAction('save')."&user_id=".$params['user_id']."',function(){height_handler();});";

		echo("<div class='toggle toggle_NEW' data-info='WIDGET_NEW' data-info-none='none'>");
		echo("<a class='toggle_header' href='#' onclick=\"".$js."return false;\"><i class='icon fas fa-plus'></i> New Sidebar Item</a>");
		echo("<div class='toggle_body collapse'>");
		echo("<div class='toggle_content'></div>");
		echo("</div>");
		echo("</div>");
	}

	public function AgentCardJS($params=array(),$container='')
	{
		$js2='';
		if($params['edit'])
			$js2.="jQuery('#".$this->GetFormAction($params['edit'])."').focus();";
		$js2.="height_handler();";

		$passparams=array();
		foreach($params as $k=>$v)
			$passparams[]=$k.'='.$v;
		$js="ObjectFunctionAjax('user_widget','".$this->id."','AgentCard','".$container."','".$this->GetFieldName('AgentCardForm')."','','".implode('&',$passparams)."',function(){".$js2."});";
		
		return $js;
	}

	public function AgentCard($params=array())
	{
		$user=new user($params['user_id']);
		$agent=new agent($user->Get('agent_id'));

		$savejs="UpdateWYSIWYG(jQuery('#".$this->GetFieldName('AgentCardForm')."'));";
		$savejs.=$this->AgentCardJS(array('action'=>$this->GetFormAction('save')));  //,$this->GetFieldName('AgentCardContainer'));
		if(!$this->id)
			$savejs='';

		$this->SetFlag('ALLOW_BLANK');
		if(!$params['parent_action'])
			$this->ProcessAction();
echo('s');

		echo("<div class='toggle toggle_EDITING'>");
		form::Begin($this->GetFieldName('AgentCardContainer'),'POST',true,array('class'=>'widget_form','id'=>$this->GetFieldName('AgentCardForm')));
		
		echo("<div class='toggle_header agent_bg_color2 agent_bg_color1_hover'><a data-toggle='collapse' href='#".$this->GetFieldName('toggle')."' role='button' aria-expanded='false' aria-controls='".$this->GetFieldName('toggle')."' onclick=\"if(jQuery('I.icon',this).hasClass('fa-plus')){jQuery('I.icon',this).removeClass('fa-plus');jQuery('I.icon',this).addClass('fa-minus');}else{jQuery('I.icon',this).removeClass('fa-minus');jQuery('I.icon',this).addClass('fa-plus');}\"><i class='icon fas fa-plus'></i></a>");
		form::DrawTextInput($this->GetFieldName('user_widget_title'),$this->Get('user_widget_title'),array('class'=>'user_widget_title','onchange'=>$savejs));
		$this->AgentLinks();
		echo("</div>");

		echo("<div class='toggle_body collapse' id='".$this->GetFieldName('toggle')."'>");
		echo("<div class='toggle_content'>");
		form::DrawTextArea($this->GetFieldName('user_widget_content'),$this->Get('user_widget_content'),array('class'=>'wysiwyg_input','onchange'=>$savejs));
		$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
		$wysiwyg_info.="onchange_callback:function(){".$savejs."},\r\n";
		wysiwyg::RegisterMode($this->GetFieldName('SIMPLE_LINK'),$wysiwyg_info);
		form::MakeWYSIWYG($this->GetFieldName('user_widget_content'),$this->GetFieldName('SIMPLE_LINK'));
		echo("</div>");
		echo("</div>");

		form::End();
		echo("</div>");
	}

	public function AgentLinks()
	{
		$user=new user($params['user_id']);
		$agent=new agent($user->Get('agent_id'));

		echo("<div class='toggle_agent_edit'>");
		if($this->id)
		{
			$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditUserWidgets','user_widgets_container','".$this->GetFieldName('AgentCardForm')."','','user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('sort_up')."&parent_action=1');";
			echo("<a href='#' data-info='WIDGET_SORT_UP' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Move Up'><i class='fa fa-arrow-up'></i></a>");
			$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditUserWidgets','user_widgets_container','".$this->GetFieldName('AgentCardForm')."','','user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('sort_down')."&parent_action=1');";
			echo("<a href='#' data-info='WIDGET_SORT_DOWN' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Move Down'><i class='fa fa-arrow-down'></i></a>");
			$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditUserWidgets','user_widgets_container','".$this->GetFieldName('AgentCardForm')."','','user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('delete')."&parent_action=1');";
			echo("<a href='#' data-info='WIDGET_DELETE' data-info-none='none' onclick=\"if(confirm('Delete This Item?')){".$js."}return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
		}
		echo("</div>");
		
	}

	public function GatherInputs()
	{
		parent::GatherInputs();
 	}  
	
	public function Delete()
	{
		parent::Delete();
	}
	
	public function Save()
	{
		global $HTTP_POST_VARS;
	 	//var_dump($HTTP_POST_VARS);
	 
		parent::Save();

		//$this->Dump();
	}
};

?>