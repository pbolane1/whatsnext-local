<?php
class info_bubble extends DBRowEx
{
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles(true);
		$this->EstablishTable('info_bubbles','info_bubble_id');
		$this->Retrieve();
	}

	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{

		}
	}

	public function DisplayEditable()
	{
		$this->SortLink('info_bubble_order');
	 	echo("<td>".$this->Get('info_bubble_heading')."</td>");
	}

	public function Create($params)
	{
		$info_bubble=new info_bubble();	
		$info_bubble->InitByKeys(array('info_bubble_key','info_bubble_section'),array($params['key'],$params['section']));
		if(!$info_bubble->id)
		{
			$rec=database::fetch_array(database::query("SELECT MAX(info_bubble_order) AS max FROM info_bubbles WHERE info_bubble_section='".$params['section']."'"));

			$name=explode('_',$params['key']);
			array_shift($name);
			$name=implode(' ',$name);

			$info_bubble->CreateFromKeys(array('info_bubble_key','info_bubble_section'),array($params['key'],$params['section']));
			$info_bubble->Set('info_bubble_heading',Text::Capitalize(strtolower($name)));
			$info_bubble->Set('info_bubble_order',$rec['max']+1);
			$info_bubble->Update();
		}
	}

	static function ShowInfo($section,$type)
	{
		//disabled
		return false;

		$agent=new agent(Session::Get('agent_id'));		
		$user_contact=new user_contact(Session::Get('user_contact_id'));	
		
		if($type=='AGENT')
		{
			$settings=json_decode($agent->Get('agent_settings'),true);
			if($settings['info_bubbles'][$section])
				return false;
		}
		if($type=='USER')
		{
			if(!$user_contact->id)
				return false;
			$settings=json_decode($user_contact->Get('agent_settings'),true);
			if($settings['info_bubbles'][$section])
				return false;
		}		
		return true;
	}

	static function ListAll($section,$type)
	{
		//disabled
		return false;
		
		//draw them all	 
		$where=array(1);
	 	$where[]="info_bubble_section='".$section."'";
	 	$list=new DBRowSetEX('info_bubbles','info_bubble_id','info_bubble',implode(' AND ',$where),'info_bubble_order');
	 	$list->Retrieve();
		$list->SetFlag('TYPE',$type);
		$list->ListFull();
		
		javascript::Begin();
		echo("jQuery('BODY').attr('info_bubble_section','".$section."');");
		//hide them if we don't need to see them.
	 	if(!info_bubble::ShowInfo($section,$type))
			echo("jQuery('BODY').addClass('hide_info_bubbles');");
		javascript::End();
		
		$js="ObjectFunctionAjax('info_bubble','','ToggleInfoSection','NULL','x','','section=".$section."&type=".$type."',function(){});";
		$js.="jQuery('BODY').removeClass('hide_info_bubbles');";
		$js.="InfoBubbleSelect(jQuery('.info_bubble').get(0).id);";
		echo("<div class='info_bubble_info'><a href='#' onclick=\"".$js.";return false\"><i class='fa fa-question-circle' aria-hidden='true'></i></a></div>");
		
	}

	static function AutoLaunch($section,$type)
	{
		//disabled
		return false;
		
	 	if(!info_bubble::ShowInfo($section,$type))
	 		return;
/*
		$where=array(1);
	 	$where[]="info_bubble_section='".$section."'";
	 	$list=new DBRowSetEX('info_bubbles','info_bubble_id','info_bubble',implode(' AND ',$where),'info_bubble_order',1);
		$list->Retrieve();
		if(count($list->items))
		{
			javascript::Begin();
			echo("jQuery(function(){InfoBubbleSelect('".$list->items[0]->Get('info_bubble_key')."');});");
			javascript::End();
		}
*/
		javascript::Begin();
		echo("jQuery(function(){InfoBubbleSelect(jQuery('.info_bubble').get(0).id);});");
		javascript::End();
	}

	public function DisplayFull()
	{
		echo("<div class='info_bubble' id='".$this->Get('info_bubble_key')."' data-order='".$this->Get('info_bubble_order')."'>");
		echo("<div class='info_bubble_inner'>");

		echo("<h1 class='info_bubble_heading'>".$this->Get('info_bubble_heading')."</h1>");
		echo("<div class='info_bubble_content'>".$this->Get('info_bubble_content')."</div>");
		
		echo("<div class='info_bubble_links'>");
		echo("<div class='row'>");
		echo("<div class='col-xs-4'>");
//		$query="SELECT info_bubble_key FROM info_bubbles WHERE info_bubble_section='".$this->Get('info_bubble_section')."' AND info_bubble_order<'".$this->Get('info_bubble_order')."' ORDER BY info_bubble_order DESC";
//		$rec=database::fetch_array(database::query($query));
//		if($rec['info_bubble_key'])
//			echo("<div class='info_bubble_previous'><a class='button' href='#' onclick=\"InfoBubbleSelect('".$rec['info_bubble_key']."');return false\">&lt;&lt;</a></div>");
		echo("<div class='info_bubble_previous'><a class='button' href='#' onclick=\"InfoBubblePrev();return false\">&lt;&lt;</a></div>");
		echo("</div>");
		echo("<div class='col-xs-4'>");
		$js="ObjectFunctionAjax('info_bubble','".$this->id."','ToggleInfoSection','NULL','x','','section=".$this->Get('info_bubble_section')."&type=".$this->GetFlag('TYPE')."',function(){});";
		$js.="jQuery('BODY').addClass('hide_info_bubbles');";		
		$js.="InfoBubbleClose();";
		echo("<div class='info_bubble_dismiss'><a class='button' href='#' onclick=\"".$js.";return false\">Dismiss</a></div>");
		echo("</div>");
		echo("<div class='col-xs-4'>");
//		$query="SELECT info_bubble_key FROM info_bubbles WHERE info_bubble_section='".$this->Get('info_bubble_section')."' AND info_bubble_order>'".$this->Get('info_bubble_order')."' ORDER BY info_bubble_order ASC";
//		$rec=database::fetch_array(database::query($query));
//		if($rec['info_bubble_key'])
//			echo("<div class='info_bubble_next'><a class='button' href='#' onclick=\"InfoBubbleSelect('".$rec['info_bubble_key']."');return false\">&gt;&gt;</a></div>");
		echo("<div class='info_bubble_next'><a class='button' href='#' onclick=\"InfoBubbleNext();return false\">&gt;&gt;</a></div>");
		echo("</div>");
		echo("</div>");
		echo("</div>");

		echo("</div>");
		echo("</div>");
	}

	public function ToggleInfoSection($params)
	{
		$agent=new agent(Session::Get('agent_id'));		
		$user_contact=new user_contact(Session::Get('user_contact_id'));	
		
		if($params['type']=='AGENT')
		{
			$settings=json_decode($agent->Get('agent_settings'),true);
			$settings['info_bubbles'][$params['section']]=!$settings['info_bubbles'][$params['section']];
			$agent->Set('agent_settings',json_encode($settings));
			$agent->Update();
		}
		if($params['type']=='USER')
		{
			$settings=json_decode($user_contact->Get('user_contact_settings'),true);
			$settings['info_bubbles'][$params['section']]=!$settings['info_bubbles'][$params['section']];
			$user_contact->Set('user_contact_settings',json_encode($settings));
			$user_contact->Update();
		}
	}

			
	public function EditForm()
	{
		global $HTTP_POST_VARS;

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");
		echo("<tr><td class='label'>Heading<div class='hint'></div></td><td>");
		form::DrawTextInput($this->GetFieldName('info_bubble_heading'),$this->Get('info_bubble_heading'),array('class'=>$this->GetError('info_bubble_heading')?'error':'text'));
		echo("</td></tr>");
		echo("<tr><td class='label'>Content<div class='hint'></div></td><td>");
		form::DrawTextArea($this->GetFieldName('info_bubble_content'),$this->Get('info_bubble_content'),array('class'=>$this->GetError('info_bubble_content')?'error':'text'));
		form::MakeWYSIWYG($this->GetFieldName('info_bubble_content'),'SIMPLE_LINK');
		echo("</td></tr>");

		echo("<tr><td colspan='2' class='save_actions'>");
 	}

	public function GatherInputs()
	{
		parent::GatherInputs();
 	}
 	
 	public function ValidateInputs()
 	{
		if($this->GetFlag('ALLOW_BLANK'))
			return true;

		if(!parent::ValidateInputs())
		    return false;		
		
		return count($this->errors)==0;
  	}

	public function Save()
	{
		$ret=parent::Save();
//		$this->Retrieve();

		if($ret)
		{

		}		
		return count($this->errors)==0;
	}

};
?>