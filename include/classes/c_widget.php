<?php

class widget extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('widgets','widget_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
 	}

	public function DisplayFull()
	{
		$vendor=new vendor($this->Get('vendor_id'));

		echo("<div class='toggle'>");
		echo("<a class='toggle_header agent_bg_color2 agent_bg_color1_hover' data-toggle='collapse' href='#".$this->GetFieldName('toggle')."' role='button' aria-expanded='false' aria-controls='".$this->GetFieldName('toggle')."' onclick=\"if(jQuery('I.icon',this).hasClass('fa-plus')){jQuery('I.icon',this).removeClass('fa-plus');jQuery('I.icon',this).addClass('fa-minus');}else{jQuery('I.icon',this).removeClass('fa-minus');jQuery('I.icon',this).addClass('fa-plus');}\"><i class='icon fas fa-plus'></i> ");
		if($vendor->id)
			echo($vendor->GetType());
		else
			echo($this->Get('widget_title'));
		echo("</a>");
		echo("<div class='toggle_body agent_border_color2 agent_border_color1_hover collapse' id='".$this->GetFieldName('toggle')."'>");
		echo("<div class='toggle_content'>");
		if($this->Get('vendor_id'))
		{
			if($vendor->Get('vendor_title'))
				echo("<div class='line'>".$vendor->Get('vendor_title')."</div>");			
			if($vendor->Get('vendor_name'))
				echo("<div class='line'>".$vendor->Get('vendor_name')."</div>");			
			if($vendor->Get('vendor_company'))
				echo("<div class='line'>".$vendor->Get('vendor_company')."</div>");			
			if($vendor->Get('vendor_email'))
				echo("<div class='line'>".$vendor->Get('vendor_email')."</div>");			
			if($vendor->Get('vendor_phone'))
				echo("<div class='line'>".$vendor->Get('vendor_phone')."</div>");			
			if($vendor->Get('vendor_info'))
				echo("<div class='line'>".$vendor->Get('vendor_info')."</div>");
		}
		else		
			echo($this->Get('widget_content'));
		echo("</div>");
		echo("</div>");
		echo("</div>");
	}

	public function DoAction($action)
	{
	 	parent::DoAction($action);
	 	if($action==$this->GetFormAction('sort_up'))
	 	{
			$this->Set('widget_order',$this->Get('widget_order')-1.01);
			$this->Update();
		}
	 	if($action==$this->GetFormAction('sort_down'))
	 	{
			$this->Set('widget_order',$this->Get('widget_order')+1.01);
			$this->Update();
		}

	}	

	public function X__NewAgentCard($params=array())
	{
		$user=new user($params['user_id']);
		$agent=new agent($user->Get('agent_id'));
		$js="ObjectFunctionAjax('agent','".$agent->id."','EditWidgets','widgets_container','null','','action=".$this->GetFormAction('save')."&user_id=".$params['user_id']."',function(){height_handler();});";

		echo("<div class='toggle toggle_NEW' data-info='WIDGET_NEW' data-info-none='none'>");
		echo("<a class='toggle_header' href='#' onclick=\"".$js."return false;\"><i class='icon fas fa-plus'></i> New Sidebar Item</a>");
		echo("<div class='toggle_body collapse'>");
		echo("<div class='toggle_content'></div>");
		echo("</div>");
		echo("</div>");
	}

	public function NewAgentCard($params=array())
	{
		$user=new user($params['user_id']);
		$agent=new agent($user->Get('agent_id'));
		$js2="UpdateWYSIWYG(jQuery('#widgets'));";
		$js="ObjectFunctionAjaxPopup('What type of item would you like to add?','widget','','AddNewWidgetPopup','null','','agent_id=".$user->Get('agent_id')."&user_id=".$this->Get('user_id')."',function(){".$js2."height_handler();});";

		echo("<div class='toggle toggle_NEW' data-info='WIDGET_NEW' data-info-none='none'>");
		echo("<a class='toggle_header' href='#' onclick=\"".$js."return false;\"><i class='icon fas fa-plus'></i> Add Vendor</a>");
		echo("<div class='toggle_body collapse'>");
		echo("<div class='toggle_content'></div>");
		echo("</div>");
		echo("</div>");
		
		$js="ObjectFunctionAjaxPopup('What type of item would you like to add?','widget','','AddNewWidgetPopup','null','','agent_id=".$user->Get('agent_id')."&user_id=".$this->Get('user_id')."&widget_type=CONTENT',function(){height_handler();});";
		echo("<div class='toggle toggle_NEW' data-info='WIDGET_NEW' data-info-none='none'>");
		echo("<a class='toggle_header' href='#' onclick=\"".$js."return false;\"><i class='icon fas fa-plus'></i> Create Custom Item </a>");
		echo("<div class='toggle_body collapse'>");
		echo("<div class='toggle_content'></div>");
		echo("</div>");
		echo("</div>");		
	}

	public function AddNewWidgetPopup($params=array())
	{
		global $HTTP_POST_VARS;

		$widget=new widget($HTTP_POST_VARS['widget_id']);
		$agent=new agent($params['agent_id']);

		if($params['widget_type'])
			$this->Set('widget_type',$params['widget_type']);
		if($HTTP_POST_VARS['widget_id'] and $HTTP_POST_VARS['widget_id']!=$params['widget_id'])
			$this->Copy($widget);
		else
			$this->GatherInputs();

		$js="ObjectFunctionAjax('widget','','AddNewWidgetPopup','popup_content','".$this->GetFieldName('AddNewWidgetPopupForm')."','','agent_id=".$params['agent_id']."&user_id=".$params['user_id']."&agent=1&widget_id=".$HTTP_POST_VARS['widget_id']."',function(){});";
		form::Begin($this->GetFieldName('AddNewWidgetPopupForm'),'POST',true,array('id'=>$this->GetFieldName('AddNewWidgetPopupForm')));		
		form::DrawHiddenInput('user_id',$params['user_id']);
//		echo("<div class='line'>");		
//		echo("<h3>What type of item would you like to add?</h3>");
//		echo("</div>");
//		echo("<div class='line'>");		
//		form::DrawSelect('widget_type',array('Content'=>'CONTENT','Vendor'=>''),$HTTP_POST_VARS['widget_type'],array('onchange'=>$js));
//		echo("</div>");			
		form::DrawHiddenInput($this->GetFieldName('widget_type'),$this->Get('widget_type'));
		if($this->Get('widget_type')=='CONTENT')
		{
			echo("<div class='line'>");		
			form::DrawSelectFromSQL('widget_id',"SELECT * FROM widgets WHERE agent_id='".$agent->id."' AND user_id=0 AND widget_type='CONTENT' ORDER BY widget_title",'widget_title','widget_id',$HTTP_POST_VARS['widget_id'],array('onchange'=>$js),array('--Create New Widget--'=>0));
			echo("</div>");			
		 	$widget=new widget($HTTP_POST_VARS['widget_id']);
		 	$widget->GatherInputs();

			echo("<div class='line'>");		
			form::DrawTextInput($this->GetFieldName('widget_title'),$this->Get('widget_title'),array('placeholder'=>'Title'));
			echo("</div>");			
			echo("<div class='line'>");		
			form::DrawTextArea($this->GetFieldName('widget_content'),$this->Get('widget_content'),array('placeholder'=>'Content','class'=>'wysiwyg_input'));
			$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
			$wysiwyg_info.="onchange_callback:function(){".$savejs."},\r\n";
			wysiwyg::RegisterMode($this->GetFieldName('SIMPLE_LINK'),$wysiwyg_info);
			form::MakeWYSIWYG($this->GetFieldName('widget_content'),$this->GetFieldName('SIMPLE_LINK'));
			echo("</div>");			
		}
		else
		{
			echo("<div class='line'>");		
			echo("<div class='row'>");		
			echo("<div class='col-md-6'>");		
			echo("<label>");		
			form::DrawRadioButton('existing_vendor',0,!$HTTP_POST_VARS['existing_vendor'],array('onchange'=>$js),array('--Create New Vendor--'=>0));
			echo(" Create New Vendor</label>");		
			echo("</div>");		
			echo("<div class='col-md-6'>");		
			echo("<div class='line'>");		
			echo("<label>");		
			form::DrawRadioButton('existing_vendor',1,$HTTP_POST_VARS['existing_vendor'],array('onchange'=>$js),array('--Create New Vendor--'=>0));
			echo(" Select from Existing</label>");		
			echo("</div>");		
			echo("</div>");		
			echo("</div>");		

			if($HTTP_POST_VARS['existing_vendor'])
			{
				echo("<div class='line'>");		
				form::DrawSelectFromSQL('existing_vendor_type_id',"SELECT * FROM vendor_types WHERE ".$agent->GetIncludedItemsSQL()." ORDER BY vendor_type_name",'vendor_type_name','vendor_type_id',$HTTP_POST_VARS['existing_vendor_type_id'],array('onchange'=>$js),array('--All Vendors--'=>0));
				echo("</div>");			


				echo("<div class='line'>");		
				$vendor_where=array($agent->GetIncludedItemsSQL());
				if($HTTP_POST_VARS['existing_vendor_type_id'])
					$vendor_where[]="vendor_type_id=".$HTTP_POST_VARS['existing_vendor_type_id'];
				form::DrawSelectFromSQL($this->GetFieldName('vendor_id'),"SELECT * FROM vendors WHERE ".implode(' AND ',$vendor_where)." ORDER BY vendor_name",'vendor_name','vendor_id',$this->Get('vendor_id'),array('onchange'=>$js),array('--Choose Vendor--'=>0));
				echo("</div>");			
			}
			if(!$HTTP_POST_VARS['existing_vendor'] or $this->Get('vendor_id'))
			{
			 	$vendor=new vendor($this->Get('vendor_id'));
			 	$vendor_type=new vendor_type();
			 	$vendor->GatherInputs();

				$editable=((!$this->Get('vendor_id')) or $vendor->IsOwnedBy($this->GetCurrentUser()));
//				echo("<div class='line'>");		
//				form::DrawTextInput($vendor->GetFieldName('vendor_title'),$vendor->Get('vendor_title'),array('placeholder'=>'Title'));
//				echo("</div>");			
				echo("<div class='line'>");		
				if($editable)
					form::DrawSelectFromSQL($vendor->GetFieldName('vendor_type_id'),"SELECT * FROM vendor_types WHERE ".$agent->GetIncludedItemsSQL()." ORDER BY vendor_type_name","vendor_type_name","vendor_type_id",$vendor->Get('vendor_type_id'),array('onchange'=>$js,'placeholder'=>'Type Of Vendor'),array(),array('--Create New Vendor Type--'=>'NEW_TYPE'));
				else
				{
				 	$vendor_type=new vendor_type($this->Get('vendor_type_id'));
					echo($vendor_type->Get('vendor_type_name'));
				}
					
				echo("</div>");			
				if($vendor->Get('vendor_type_id')=='NEW_TYPE')
				{
					echo("<div class='line'>");		
					form::DrawTextInput($vendor_type->GetFieldName('vendor_type_name'),$vendor->Get('vendor_type_name'),array('placeholder'=>'Vendor Type'));
					echo("</div>");			
				}
				echo("<div class='line'>");		
				if($editable)
					form::DrawTextInput($vendor->GetFieldName('vendor_name'),$vendor->Get('vendor_name'),array('placeholder'=>'Name'));
				else
					echo($vendor->Get('vendor_name'));
				echo("</div>");			
				echo("<div class='line'>");		
				if($editable)
					form::DrawTextInput($vendor->GetFieldName('vendor_company'),$vendor->Get('vendor_company'),array('placeholder'=>'Company'));
				else
					echo($vendor->Get('vendor_company'));
				echo("</div>");			
				echo("<div class='line'>");		
				if($editable)
					form::DrawTextInput($vendor->GetFieldName('vendor_email'),$vendor->Get('vendor_email'),array('placeholder'=>'Email'));
				else
					echo($vendor->Get('vendor_email'));
				echo("</div>");			
				echo("<div class='line'>");		
				if($editable)
					form::DrawTextInput($vendor->GetFieldName('vendor_phone'),$vendor->Get('vendor_phone'),array('placeholder'=>'Phone'));
				else
					echo($vendor->Get('vendor_phone'));
				echo("</div>");			
				echo("<div class='line'>");		
				if($editable)
					form::DrawTextInput($vendor->GetFieldName('vendor_info'),$vendor->Get('vendor_info'),array('placeholder'=>' Additional Info'));
				else
					echo($vendor->Get('vendor_info'));
				echo("</div>");			
			}
		}
		form::End();	
		
		$js="UpdateWYSIWYG('#popup_content');";
		$js.="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."','EditWidgets','widgets_container','".$this->GetFieldName('AddNewWidgetPopupForm')."','','agent_id=".$params['agent_id']."&user_id=".$params['user_id']."&agent=1&action=".$this->GetCurrentUser()->GetFormAction('add_widget')."&parent_action=1',function(){PopupClose();});";
		echo("<div class='line'>");
		echo("<a class='button' href='#' onclick=\"".$js."return false;\">Insert</a>");
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
		$js="ObjectFunctionAjax('widget','".$this->id."','AgentCard','".$container."','".$this->GetFieldName('AgentCardForm')."','','".implode('&',$passparams)."',function(){".$js2."});";
		
		return $js;
	}

	public function AgentCard($params=array())
	{
		$user=new user($params['user_id']);
		$agent=new agent($user->Get('agent_id'));
		$vendor=new vendor($this->Get('vendor_id'));

		$savejs="UpdateWYSIWYG(jQuery('#".$this->GetFieldName('AgentCardForm')."'));";
		$savejs.=$this->AgentCardJS(array('action'=>$this->GetFormAction('save')));  //,$this->GetFieldName('AgentCardContainer'));
		if(!$this->id)
			$savejs='';

		$this->SetFlag('ALLOW_BLANK');
		if(!$params['parent_action'])
			$this->ProcessAction();


		echo("<div class='toggle toggle_EDITING'>");
		form::Begin($this->GetFieldName('AgentCardContainer'),'POST',true,array('class'=>'widget_form','id'=>$this->GetFieldName('AgentCardForm')));
		
		echo("<div class='toggle_header agent_bg_color2 agent_bg_color1_hover'><a data-toggle='collapse' href='#".$this->GetFieldName('toggle')."' role='button' aria-expanded='false' aria-controls='".$this->GetFieldName('toggle')."' onclick=\"if(jQuery('I.icon',this).hasClass('fa-plus')){jQuery('I.icon',this).removeClass('fa-plus');jQuery('I.icon',this).addClass('fa-minus');}else{jQuery('I.icon',this).removeClass('fa-minus');jQuery('I.icon',this).addClass('fa-plus');}\"><i class='icon fas fa-plus'></i></a>");
		if($vendor->id)
			echo($vendor->GetType());
		else
			form::DrawTextInput($this->GetFieldName('widget_title'),$this->Get('widget_title'),array('class'=>'widget_title','onchange'=>$savejs));
			
		$this->AgentLinks();
		echo("</div>");

		echo("<div class='toggle_body agent_border_color2 agent_border_color1_hover collapse' id='".$this->GetFieldName('toggle')."'>");
		echo("<div class='toggle_content'>");
		if($this->Get('vendor_id'))
		{
			$editable=((!$vendor->id) or $vendor->IsOwnedBy($this->GetCurrentUser()));

//			echo("<div class='line'>");		
//			form::DrawTextInput($vendor->GetFieldName('vendor_title'),$vendor->Get('vendor_title'),array('onchange'=>$savejs,'placeholder'=>'Title'));
//			echo("</div>");			
			echo("<div class='line'>");		
			if($editable)			
				form::DrawTextInput($vendor->GetFieldName('vendor_name'),$vendor->Get('vendor_name'),array('onchange'=>$savejs,'placeholder'=>'Name'));
			else
				echo($vendor->Get('vendor_name'));
			echo("</div>");			
			echo("<div class='line'>");		
			if($editable)			
				form::DrawTextInput($vendor->GetFieldName('vendor_company'),$vendor->Get('vendor_company'),array('onchange'=>$savejs,'placeholder'=>'Company'));
			else
				echo($vendor->Get('vendor_company'));
			echo("</div>");			
			echo("<div class='line'>");		
			if($editable)			
				form::DrawTextInput($vendor->GetFieldName('vendor_email'),$vendor->Get('vendor_email'),array('onchange'=>$savejs,'placeholder'=>'Email'));
			else
				echo($vendor->Get('vendor_email'));
			echo("</div>");			
			echo("<div class='line'>");		
			if($editable)			
				form::DrawTextInput($vendor->GetFieldName('vendor_phone'),$vendor->Get('vendor_phone'),array('onchange'=>$savejs,'placeholder'=>'Phone'));
			else
				echo($vendor->Get('vendor_phone'));
			echo("</div>");			
			echo("<div class='line'>");		
			if($editable)			
				form::DrawTextInput($vendor->GetFieldName('vendor_info'),$vendor->Get('vendor_info'),array('onchange'=>$savejs,'placeholder'=>' Additional Info'));
			else
				echo($vendor->Get('vendor_info'));
			echo("</div>");
		}
		else
		{
			form::DrawTextArea($this->GetFieldName('widget_content'),$this->Get('widget_content'),array('class'=>'wysiwyg_input','onchange'=>$savejs));
			$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
			$wysiwyg_info.="onchange_callback:function(){".$savejs."},\r\n";
			wysiwyg::RegisterMode($this->GetFieldName('SIMPLE_LINK'),$wysiwyg_info);
			form::MakeWYSIWYG($this->GetFieldName('widget_content'),$this->GetFieldName('SIMPLE_LINK'));
		}
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
			$js="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."','EditWidgets','widgets_container','".$this->GetFieldName('AgentCardForm')."','','user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('sort_up')."&parent_action=1');";
			echo("<a href='#' data-info='WIDGET_SORT_UP' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Move Up'><i class='fa fa-arrow-up'></i></a>");
			$js="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."','EditWidgets','widgets_container','".$this->GetFieldName('AgentCardForm')."','','user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('sort_down')."&parent_action=1');";
			echo("<a href='#' data-info='WIDGET_SORT_DOWN' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Move Down'><i class='fa fa-arrow-down'></i></a>");
			$js="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."','EditWidgets','widgets_container','".$this->GetFieldName('AgentCardForm')."','','user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('delete')."&parent_action=1');";
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
		if($this->Get('vendor_id'))
		{
			$vendor=new vendor($this->Get('vendor_id'));
			$vendor->Save();
		}

		//$this->Dump();
	}
};

?>