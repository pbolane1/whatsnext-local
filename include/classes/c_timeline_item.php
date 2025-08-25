<?php

class timeline_item extends DBRowEx
{ 
	public function __construct($id='')
	{
		parent::__construct($id);
		$this->AllowFiles();
		$this->EstablishTable('timeline_items','timeline_item_id');
		$this->Retrieve();
	}
	
	public function Retrieve($rec='')
	{
		parent::Retrieve();
		if(!$this->id)
		{
			$this->Set('timeline_item_modified',time());
			$this->Set('timeline_item_active',1);
			$this->Set('timeline_item_type','TIMELINE');
			$this->Set('timeline_item_reference_date','');		
			$this->Set('timeline_item_reference_date_type','NONE');		
			$this->Set('timeline_item_fa_class','fas fa-clock');
		}
 	}

	public function GetFullName()
	{
		return $this->Get('timeline_item_title');
	}

	public function DoAction($action)
	{
		global $HTTP_POST_VARS;

	 	parent::DoAction($action);
	 	if($action==$this->GetFormAction('restore'))
	 	{
			$seteachnew=array();
			$seteachnew['timeline_item_date']=$this->Get('timeline_item_date');
			$seteachnew['timeline_item_order']=$this->Get('timeline_item_order');


			//can PROBABLY standardize this for one copy all call regardless of what typ eof item we are restoring from/to, but need to sanity cehck what migth get nuked/restoed, etc.
			//for example, restoring agent => agent or agent => user might not work here.  might have to just slave my tempalte,agent,user against original tempalte,agent,user
	 	 	$original=new timeline_item($this->Get('original_id'));
	 	 	if($this->Get('user_id')) //copy form the agent's tempalte.
			 	timeline_item::CopyAll(array('timeline_item_id'=>$this->Get('original_id'),'template_id'=>$original->Get('template_id'),'agent_id'=>'','user_id'=>0),array('timeline_item_id'=>$this->id,'template_id'=>$this->Get('template_id'),'agent_id'=>$this->Get('agent_id'),'user_id'=>$this->Get('user_id')),$seteachnew);
	 	 	else if($this->Get('agent_id')) //copy from the main template.
			 	timeline_item::CopyAll(array('timeline_item_id'=>$this->Get('original_id'),'template_id'=>$original->Get('template_id'),'agent_id'=>'','user_id'=>0),array('timeline_item_id'=>$this->id,'template_id'=>$this->Get('template_id'),'agent_id'=>$this->Get('agent_id'),'user_id'=>$this->Get('user_id')),$seteachnew);
	 	 	else //restore for a master adin item.
			 	timeline_item::CopyAll(array('timeline_item_id'=>$this->Get('original_id'),'template_id'=>$original->Get('template_id'),'agent_id'=>0,'user_id'=>0),array('timeline_item_id'=>$this->id,'template_id'=>$this->Get('template_id'),'agent_id'=>$this->Get('agent_id'),'user_id'=>$this->Get('user_id')),$seteachnew);

			activity_log::Log(new agent($this->Get('agent_id')),'TIMELINE_ITEM_RESTORED','Timeline Item '.$this->Get('timeline_item_title').' Restored to default',$this->Get('user_id'));

	 	}
	 	if($action==$this->GetFormAction('undelete'))
	 	{
			$this->UnDelete();
		}
	 	if($action==$this->GetFormAction('permanentdelete'))
	 	{
			activity_log::Log(new agent($this->Get('agent_id')),'TIMELINE_ITEM_DELETED','Timeline Item '.$this->Get('timeline_item_title').' Permanently Deleted',$this->Get('user_id'));

			parent::Delete();
		}
	 	if($action==$this->GetFormAction('sort_up'))
	 	{
			$this->Set('timeline_item_order',$this->Get('timeline_item_order')-1.01);
			$this->Update();
			
			activity_log::Log(new agent($this->Get('agent_id')),'TIMELINE_ITEM_SORTED','Timeline Item '.$this->Get('timeline_item_title').' Sorted Up',$this->Get('user_id'));
		}
	 	if($action==$this->GetFormAction('sort_down'))
	 	{
			$this->Set('timeline_item_order',$this->Get('timeline_item_order')+1.01);
			$this->Update();

			activity_log::Log(new agent($this->Get('agent_id')),'TIMELINE_ITEM_SORTED','Timeline Item '.$this->Get('timeline_item_title').' Sorted Down',$this->Get('user_id'));
		}
	 	if($action==$this->GetFormAction('add_after'))
	 	{
			$temp=new timeline_item();
			$copy=new timeline_item($HTTP_POST_VARS['copy_timeline_item_id']);
//			$temp->Set('depends_on_timeline_item_id',0);
			if($HTTP_POST_VARS['copy_timeline_item_id']=='UPDATE')
			{
				$temp->Set('timeline_item_for','USER');
				$temp->Set('timeline_item_image_file','SPECIAL-update.png');
				$temp->Set('timeline_item_fa_class',' fas fa-exclamation');
			}
			else if($HTTP_POST_VARS['copy_timeline_item_id'])
			{
				$temp->Copy($copy);
				$temp->Set('timeline_item_title','Copy Of '.$temp->Get('timeline_item_title'));
			}
			$temp->Set('user_id',$this->Get('user_id'));
			$temp->Set('agent_id',$this->Get('agent_id'));
			$temp->Set('template_id',$this->Get('template_id'));
			$temp->Set('timeline_item_order',$this->Get('timeline_item_order')+0.01);
			$temp->Set('timeline_item_date',$this->Get('timeline_item_date'));
			$temp->Set('timeline_item_reference_date_type',$this->Get('timeline_item_reference_date_type'));
			$temp->Set('timeline_item_reference_date',$this->Get('timeline_item_reference_date'));
			$temp->Set('timeline_item_reference_date_days',$this->Get('timeline_item_reference_date_days'));
			$temp->Update();



			activity_log::Log(new agent($this->Get('agent_id')),'TIMELINE_ITEM_CREATED'.'Timeline Item '.($HTTP_POST_VARS['copy_timeline_item_id']?'Copied From '.$temp->Get('timeline_item_title'):'Created'),$this->Get('user_id'));
		
			//this acts super wierd
			//no//Session::Set('timeline_item_id',$temp->id);			
			//force js to execute on what we just made instead.
			Javascript::Begin();
			echo($temp->AgentCardJS(array("edit_timeline_item_id"=>$temp->id,'reset_date'=>1),$temp->GetFieldName('AgentCardContainer'),"jQuery('#".$temp->GetFieldName('timeline_item_title')."').focus();"));
			Javascript::End();
			
		}
	}		

	static public function CopyAll($params_from=array(),$params_to=array(),$seteachnew=array())
	{
		if($params_to['template_id']==$params_from['template_id'])
			return;
		if(!$params_to['agent_id'] and !$params_to['user_id'] and !$params_to['template_id'])
			return false;

		//clear to.
		$where=array();
		if($params_to['template_id'])
			$where[]="template_id='".$params_to['template_id']."'";	
		if($params_to['coordinator_id'])
			$where[]="coordinator_id='".$params_to['coordinator_id']."'";//copy from me (to a user) OR default to a user.
		if($params_to['agent_id'])
			$where[]="agent_id='".$params_to['agent_id']."'";//copy from me (to a user) OR default to a user.
		$where[]="user_id='".$params_to['user_id']."'";//copy from a specifilc user if we want
		if($params_to['timeline_item_id'])
			$where[]="timeline_item_id='".$params_to['timeline_item_id']."'";	
		database::query("DELETE FROM timeline_items WHERE ".implode(' AND ',$where));

		//copy from
		$where=array("timeline_item_active=1");
		if($params_from['template_id'])
			$where[]="template_id='".$params_from['template_id']."'";
		if($params_from['coordinator_id'])
			$where[]="coordinator_id='".$params_from['coordinator_id']."'";//copy from me (to a user) OR default to a user.
		if($params_from['agent_id'])
			$where[]="agent_id='".$params_from['agent_id']."'";//copy from me (to a user) OR default to a user.
		$where[]="user_id='".$params_from['user_id']."'";//copy from a specifilc user if we want
		if($params_from['timeline_item_id'])
			$where[]="timeline_item_id='".$params_from['timeline_item_id']."'";	
		//not deleted either.
		$where[]="timeline_item_active!=0";

		$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_order');
		$timeline_items->Retrieve();

		foreach($timeline_items->items as $timeline_item)
		{
			//copy to.
			$new_timeline_item=new timeline_item();
			$new_timeline_item->Copy($timeline_item);
			$new_timeline_item->Set("coordinator_id",$params_to['coordinator_id']);
			$new_timeline_item->Set("agent_id",$params_to['agent_id']);
			$new_timeline_item->Set("user_id",$params_to['user_id']);
			$new_timeline_item->Set("timeline_item_date",'');
			$new_timeline_item->Set("timeline_item_complete",0);

			$new_timeline_item->Set("timeline_item_completed_by",'');
			$new_timeline_item->Set("timeline_item_completed_class",'');
			$new_timeline_item->Set("timeline_item_completed_id",0);
			$new_timeline_item->Set("template_id",$params_to['template_id']);
			$new_timeline_item->Set("original_id",$timeline_item->id);
			foreach($seteachnew as $k=>$v)
				$new_timeline_item->Set($k,$v);
			$new_timeline_item->Update();
			
		  	$list=new DBRowSetEX('conditions_to_timeline_items','condition_to_timeline_item_id','condition_to_timeline_item',"timeline_item_id='".$timeline_item->id."'",'');
			$list->Retrieve();
			foreach($list->items as $condition_to_timeline_item)
			{
				$new_condition_to_timeline_item=new condition_to_timeline_item();
				$new_condition_to_timeline_item->Copy($condition_to_timeline_item);
				$new_condition_to_timeline_item->Set('timeline_item_id',$new_timeline_item->id);
				$new_condition_to_timeline_item->Update();
			}			
		}				

		//now make sure all of them get dependant on the right one
		foreach($timeline_items->items as $timeline_item)
		{
			if($timeline_item->Get('depends_on_timeline_item_id'))
			{
				$new_timeline_item=new timeline_item();
				$new_timeline_item->InitByKeys(array("agent_id","user_id","template_id","original_id"),array($params_to['agent_id'],$params_to['user_id'],$params_to['template_id'],$timeline_item->id));
	
				$depends_on=new timeline_item();
				$depends_on->InitByKeys(array("agent_id","user_id","template_id","original_id"),array($params_to['agent_id'],$params_to['user_id'],$params_to['template_id'],$timeline_item->Get('depends_on_timeline_item_id')));

				$new_timeline_item->Set('depends_on_timeline_item_id',$depends_on->id);
				$new_timeline_item->Update();
			}		
		}				

	}

	public function DeleteLink()
    {
		if(!$this->Get('agent_id'))
			parent::DeleteLink();
		else if($this->Get('timeline_item_active'))
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
			$this->PreserveInputs();
			form::DrawHiddenInput($this->GetFieldName('timeline_item_active'),0);
			form::DrawSubmit('','DELETE',array('onclick'=>"return confirm('Are you sure you want to disable this item?');"));
			form::end();
		}
		echo("</td>");
	}

	public function GetBehaviors()
	{
		$types=array();
		$types['(None)']='';
		$types['Close Of Escrow']='COE';
		$types['Congratulations']='CONGRATULATIONS';
		$types['Archive Transaction']='ARCHIVE';
		$types['Under Contract Button']='UNDERCONTRACTBUTTON';

		return $types;		
	}

	public function GetBehavior()
	{
 		$types=array_flip($this->GetBehaviors());
 		
 		return $types[$this->Get('timeline_item_behavior')];
 	}
 
	public function GetReferenceDateType()
	{
		$types=array();
		if($this->Get('timeline_item_reference_date_type')=='NONE')
			return 'Not Date Specific';
		if($this->Get('timeline_item_reference_date_type')=='EXACT')
			return 'Specific Date';
		if($this->Get('timeline_item_reference_date_type')=='RELATIVE')
		{
			$contract_date=new contract_date($this->Get('timeline_item_reference_date'));
			return $contract_date->Get('contract_date_name');
		}
 	}
 	
	public function GetFAClasses($current='')
	{
		$types=array();
		$types['Clock']='fa fas fa-clock';
		$types['Search']='fa fas fa-search';
		$types['Paperwork']='fa fas fa-file-alt';
		$types['Damage']='fa fas fa-house-damage';

		if($current and !in_array($current,$types))
			$types['(Custom Icon)']=$current;

		return $types;		
	}

	public function x_DisplayEditable()
	{
		$this->SortLink('timeline_item_order');
	 	$d=new DBDate($this->Get('timeline_item_date'));
	 	$dm=new Date();
	 	$dm->SetTimestamp($this->Get('timeline_item_modified'));
	
	 	echo("<td>".$this->Get('timeline_item_title')."</td>");
	 	echo("<td>");;
	 	if($this->Get('timeline_item_reference_date_type')=='NONE')
		 	echo('(none)');
	 	else if($this->Get('depends_on_timeline_item_id'))
	 	{
		 	$timeline_item=new timeline_item($this->Get('depends_on_timeline_item_id'));
		 	echo("Waiting for <b>".$timeline_item->Get('timeline_item_title')." to be marked as complete</b>");
		}
	 	else if($this->Get('timeline_item_reference_date_type')=='EXACT')
		 	echo($d->GetDate('m/d/Y'));
		else
			echo($this->Get('timeline_item_reference_date_days').' Days '.($this->Get('timeline_item_reference_date_before')?"Before":"After").' '.$this->GetReferenceDateType());
	 	echo("</td>");
	 	echo("<td>");
		echo("<ul>");
	  	$list=new DBRowSetEX('conditions_to_timeline_items','condition_id','condition',"timeline_item_id='".$this->id."'",'condition_order');
	  	$list->join_tables="conditions";
	  	$list->join_where="conditions_to_timeline_items.condition_id=conditions.condition_id";
		$list->Retrieve();
		foreach($list->items as $condition)
			echo("<li>".$condition->Get('condition_name')."</li>");		
		echo("</ul>");
	 	echo("</td>");
	 	echo("<td>".$dm->GetDate('m/d/Y')."</td>");
	}

	public function GetUserTiming()
	{
		$days=0;

		//how many days am I from a certain contract date?
		$contract_date=new contract_date($this->Get('timeline_item_reference_date'));
		$num=$this->Get('timeline_item_reference_date_days');
		if($this->Get('timeline_item_reference_date_before'))
			$days-=$num;
		else
			$days+=$num;

		//how many days is that from another date?
		if($contract_date->Get('contract_date_default_days'))
			$days+=$contract_date->Get('contract_date_default_days');

		//echo("Which is ".$contract_date->GetRelativeText()."<br>");


		//is that date a number of days form another date?
		$contract_date=new contract_date($contract_date->Get('contract_date_default_days_relative_to_id'));
		while($contract_date->id)
		{
			$days+=$contract_date->Get('contract_date_default_days');
			$contract_date=new contract_date($contract_date->Get('contract_date_default_days_relative_to_id'));
			
			//echo("Which is ".$contract_date->GetRelativeText()."<br>");
		}
		return "Day ".$days;
	}
			
	public function X_EditForm()
	{
		global $HTTP_POST_VARS;

		echo("<td colspan='2' align='center'>");
		if($this->msg)
			echo("<div class='message'>".$this->msg."</div>");
		echo("</td></tr>");

		$class='';
		$style='';
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Headline<div class='hint'></div></td><td colspan='3'>");
		form::DrawTextInput($this->GetFieldName('timeline_item_title'),$this->Get('timeline_item_title'),array('class'=>$this->GetError('timeline_item_title')?'error':'text'));
		echo("</td></tr>");

		$class='';
		$style='';
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Type<div class='hint'></div></td><td colspan='3'>");
		$js="jQuery('TR.timeline_item_type').css({display:'none'});jQuery('.timeline_item_type_'+jQuery(this).val()).css({display:''});";
		form::DrawSelect($this->GetFieldName('timeline_item_type'),array('Timeline Item'=>'TIMELINE','Content Only'=>'CONTENT'),$this->Get('timeline_item_type'),array('onchange'=>$js));	
		echo("</td></tr>");			

		$class='';
		$style='';
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Special Behavior<div class='hint'></div></td><td colspan='3'>");
		form::DrawSelect($this->GetFieldName('timeline_item_behavior'),$this->GetBehaviors(),$this->Get('timeline_item_behavior'),array('class'=>$this->GetError('timeline_item_behavior')?'error':'text'));
		echo("</td></tr>");


		$class='timeline_item_type timeline_item_type_CONTENT';
		$style="display:".(in_array($this->Get('timeline_item_type'),array('CONTENT'))?'':'none');
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Hide When Under Contract?<div class='hint'></div></td><td colspan='3'>");
		form::DrawSelect($this->GetFieldName('timeline_item_hide_uc'),array('No'=>0,'Yes'=>1),$this->Get('timeline_item_hide_uc'));
		echo("</td></tr>");			

		$class='';
		$style='';
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Depends on<div class='hint'></div></td><td colspan='3'>");
		$dependsjs="jQuery('TR.timeline_item_depends_on').css({display:''});if(jQuery(this).val()){jQuery('.timeline_item_depends_on').css({display:'NONE'});}";		
		form::DrawSelectFromSQL($this->GetFieldName('depends_on_timeline_item_id'),"SELECT * FROM timeline_items WHERE timeline_item_id!='".$this->id."' AND template_id='".$this->Get('template_id')."' AND agent_id='".$this->Get('agent_id')."' AND user_id='".$this->Get('user_id')."' AND timeline_item_active=1","timeline_item_title","timeline_item_id",$this->Get('depends_on_timeline_item_id'),array('onchange'=>$dependsjs),array('(None)'=>'0'));
		echo("</td></tr>");			

		$class='timeline_item_type timeline_item_type_TIMELINE';
		$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Action Item For<div class='hint'></div></td><td colspan='3'>");
		form::DrawSelect($this->GetFieldName('timeline_item_for'),array('Client'=>'USER','Agent/Other'=>'OTHER','Agent Only'=>'AGENT'),$this->Get('timeline_item_for'));	
		echo("</td></tr>");	
		
//		$class='timeline_item_type timeline_item_type_TIMELINE';
//		$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
//		echo("<tr class='".$class."' style='".$style."'><td class='label'>Agent Only<div class='hint'></div></td><td colspan='3'>");
//		form::DrawSelect($this->GetFieldName('timeline_item_agent_only'),array('No'=>'0','Agent Only'=>'1'),$this->Get('timeline_item_agent_only'));	
//		echo("</td></tr>");			
				
		$class='timeline_item_type timeline_item_type_TIMELINE timeline_item_depends_on';
		$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
		if($this->Get('depends_on_timeline_item_id'))
			$style="display:none";
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Date<div class='hint'></div></td>");
		echo("<td class='timeline_item_reference_date' style='display:".(in_array($this->Get('timeline_item_reference_date'),array('EXACT','NONE'))?'none':'')."'>");
		$opts=array();
		for($i=0;$i<365;$i++)
			$opts[$i.' Days']=$i;
		form::DrawSelect($this->GetFieldName('timeline_item_reference_date_days'),$opts,$this->Get('timeline_item_reference_date_days'));
		echo("</td>");
		echo("<td class='timeline_item_reference_date' style='display:".(in_array($this->Get('timeline_item_reference_date'),array('EXACT','NONE'))?'none':'')."'>");
		form::DrawSelect($this->GetFieldName('timeline_item_reference_date_before'),array('After'=>0,'Before'=>1),$this->Get('timeline_item_reference_date_before'),array('class'=>'timeline_item_reference_date_reference','style'=>'display:'.($this->Get('timeline_item_reference_date')?'inline-block':'none')));
		echo("</td>");
		echo("<td>");
		$js="jQuery('.timeline_item_reference_date').css({display:'none'});";
		$js.="if(jQuery(this).val()=='EXACT'){jQuery('.timeline_item_reference_date_EXACT').css({display:''});}";
		$js.="else if(jQuery(this).val()=='NONE'){}";
		$js.="else{jQuery('.timeline_item_reference_date').css({display:''});jQuery('.timeline_item_reference_date_EXACT').css({display:'none'});}";
		form::DrawSelect($this->GetFieldName('timeline_item_reference_date'),$this->GetReferenceDates(),$this->Get('timeline_item_reference_date'),array('onchange'=>$js));
		echo("</td>");
		echo("<td class='timeline_item_reference_date timeline_item_reference_date_EXACT' style='display:".(in_array($this->Get('timeline_item_reference_date'),array('EXACT'))?'':'none')."'>");
		$d=new date();
		if($this->Get('timeline_item_reference_date'))
			$d=new DBDAte($this->Get('timeline_item_date'));			
		form::DrawTextInput($this->GetFieldName('timeline_item_date'),$d->GetDate('m/d/Y'),array('class'=>'text datepicker'));	
		echo("</td></tr>");			

		if(!$this->Get('agent_id')) //MASTER ADMIN.
		{
			$class='timeline_item_type timeline_item_type_TIMELINE';
			$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
			echo("<tr class='".$class."' style='".$style."'><td class='label'>Font Awesome Class<div class='hint'></div></td><td colspan='3'>");
			form::DrawTextInput($this->GetFieldName('timeline_item_fa_class'),$this->Get('timeline_item_fa_class'),array('class'=>$this->GetError('timeline_item_fa_class')?'error':'text'));
			echo("</td></tr>");			
		}
		else
		{
			$class='timeline_item_type timeline_item_type_TIMELINE';
			$style="display:".(($this->Get('timeline_item_type')=='TIMELINE')?'':'none');
			echo("<tr class='".$class."' style='".$style."'><td class='label'>Icon<div class='hint'></div></td><td colspan='3'>");
			form::DrawSelect($this->GetFieldName('timeline_item_fa_class'),$this->GetFAClasses($this->Get('timeline_item_fa_class')),$this->Get('timeline_item_fa_class'));	
			echo("</td></tr>");			
		}
		if($this->Get('timeline_item_image_file'))
		{
			$class='timeline_item_type timeline_item_type_TIMELINE';
			$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
			echo("<tr class='".$class."' style='".$style."'><td class='label'>Image</td><td colspan='3'>");
			echo("<img src='".$this->GetThumb(440,240,true)."'>");
			echo("</td></tr>");
		}		

		$class='timeline_item_type timeline_item_type_TIMELINE';
		$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Upload Image</td><td colspan='3'><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('timeline_item_image_file_ul'),'',array('class'=>$this->GetError('timeline_item_image_file_ul')?'error':'file'));
		echo("</td></tr>");			

		$class='';
		$style='';
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Image Alt Tag<div class='hint'></div></td><td colspan='3'>");
		form::DrawTextInput($this->GetFieldName('timeline_item_image_alt'),$this->Get('timeline_item_image_alt'),array('class'=>$this->GetError('timeline_item_image_alt')?'error':'text'));
		echo("</td></tr>");


		$class='timeline_item_type timeline_item_type_TIMELINE timeline_item_type_CONTENT';
		$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE','CONTENT'))?'':'none');
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Text</td><td colspan='3'><div class='hint'></div>");
		form::DrawTextArea($this->GetFieldName('timeline_item_summary'),$this->Get('timeline_item_summary'),array('class'=>'wysiwyg_input wysiwyg_timeline'));
		form::MakeWYSIWYG($this->GetFieldName('timeline_item_summary'),'SIMPLE_LINK');
		echo("</td></tr>");

		$class='timeline_item_type timeline_item_type_TIMELINE';
		$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
		echo("<tr class='".$class."' style='".$style."'><td class='label'>Full Content</td><td colspan='3'><div class='hint'></div>");
		form::DrawTextArea($this->GetFieldName('timeline_item_content'),$this->Get('timeline_item_content'),array('class'=>'wysiwyg_input wysiwyg'));
		form::MakeWYSIWYG($this->GetFieldName('timeline_item_content'),$this->Get('agent_id')?'SIMPLE_LINK_HEADLINES':'');
		echo("</td></tr>");

		$class='timeline_item_type timeline_item_type_TIMELINE';
		$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
		echo("<tr class='".$class."' style='".$style."'><td class='label'>OR Link To URL</td><td><div class='hint'></div>");
		form::DrawTextInput($this->GetFieldName('timeline_item_url'),$this->Get('timeline_item_url'),array('class'=>$this->GetError('timeline_item_url')?'error':'text'));
		echo("</td></tr>");	

		$class='timeline_item_type timeline_item_type_TIMELINE';
		$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
		echo("<tr class='".$class."' style='".$style."'><td class='label'>OR Upload File</td><td colspan='3'><div class='hint'></div>");
		form::DrawFileInput($this->GetFieldName('timeline_item_file_ul'),'',array('class'=>$this->GetError('news_title')?'error':'file'));
		echo("</td></tr>");			
		if($this->Get('timeline_item_file'))
		{
			$class='timeline_item_type timeline_item_type_TIMELINE';
			$style="display:".(in_array($this->Get('timeline_item_type'),array('TIMELINE'))?'':'none');
			echo("<tr class='".$class."' style='".$style."'><td class='label'>Current File</td><td colspan='3'>");
			echo("<a target='_blank' href='".$this->ToURL()."'>View Current File....</a> ");
			form::DrawCheckBox($this->GetFieldName('timeline_item_file_remove'),1,0,array('class'=>'X'));
			echo(" Remove");
			echo("</td></tr>");
		}		

		echo("<tr class='".$class."' style='".$style."'><td class='label'>Anchor<div class='hint'></div></td><td colspan='3'>");
		form::DrawTextInput($this->GetFieldName('timeline_item_anchor'),$this->Get('timeline_item_anchor'),array('class'=>$this->GetError('timeline_item_anchor')?'error':'text'));
		echo("</td></tr>");

		if(!count($this->errors))
			$this->RetrieveRelated('conditon_ids','conditions_to_timeline_items',"timeline_item_id='".$this->id."'",'','condition_id');

	  	$list=new DBRowSetEX('conditions','condition_id','condition',1,'condition_order');
		$list->Retrieve();
		echo("<tr><td class='label'>Conditions<div class='hint'></div></td><td style='text-align:left'>");
		foreach($list->items as $condition)
		{
			echo("<div>");		
			echo("<label>");		
			form::DrawCheckbox('condition_ids[]',$condition->id,in_array($condition->id,$this->related['conditon_ids']));
			echo(" ".$condition->Get('condition_name'));		
			echo("</label>");		
			echo("</div>");		
		}
		echo("</td></tr>");

		echo("<tr><td colspan='4' class='save_actions'>");

 	}


	public function GatherInputs()
	{
		global $HTTP_POST_VARS;

		$old=new timeline_item($this->id);

		parent::GatherInputs();

		if($HTTP_POST_VARS[$this->GetFieldName('timeline_item_date')])
			$this->GatherDate('timeline_item_date');

		//**FILE**// 
		if($HTTP_POST_VARS[$this->GetFieldName('timeline_item_image_file_remove')])
			$this->Set('timeline_item_image_file','');
		$this->GatherFile($this->GetFieldName('timeline_item_image_file_ul'),'timeline_item_image_file');
		if($HTTP_POST_VARS[$this->GetFieldName('timeline_item_file_remove')])
			$this->Set('timeline_item_file','');
		$this->GatherFile($this->GetFieldName('timeline_item_file_ul'),'timeline_item_file');
		
		$this->Set('timeline_item_modified',time());
		$this->Set('timeline_item_modified_by',$this->GetCurrentUser()->GetFullName());
		
		$this->Set('timeline_item_anchor',mod_rewrite::ToURL($this->Get('timeline_item_anchor'),'A-Z,a-z,0-9---,_-_'));		

		$this->related['conditon_ids']=$HTTP_POST_VARS['condition_ids'];
		
		//depends on.
		if($this->Get('depends_on_timeline_item_id'))
		{
			$this->Set('timeline_item_reference_date_type','EXACT');
			//$this->Set('timeline_item_date','');
		}

		if($HTTP_POST_VARS[$this->GetFieldName('timeline_item_reference_date_type_contract')])
			$this->Set('timeline_item_reference_date_type','CONTRACT');			
		if(!$HTTP_POST_VARS[$this->GetFieldName('timeline_item_reference_date_type_contract')] and ($this->Get('timeline_item_reference_date_type')=='CONTRACT'))
			$this->Set('timeline_item_reference_date_type','NONE');			
		if(!$this->Get('timeline_item_reference_date_type'))		
			$this->Set('timeline_item_reference_date_type','NONE');			
		
		//if we changed types, clear the reference date.
		if($this->Get('timeline_item_reference_date_type')!=$old->Get('timeline_item_reference_date_type'))		
			$this->Set('timeline_item_reference_date','');
		
		if($this->GetFlag('NL2BR'))
			$this->Set('timeline_item_summary',nl2br($this->Get('timeline_item_summary')));
			
 	}
 	
 	public function ValidateInputs()
 	{
		if($this->GetFlag('ALLOW_BLANK'))
			return true;

		if(!parent::ValidateInputs())
		    return false;		
		if(!$this->Get('timeline_item_title'))
			$this->LogError('Please Enter a Headline For the Item','timeline_item_title');
	
		$this->ValidateURL('timeline_item_url');

		if($this->Get('timeline_item_anchor') and !$this->ValidateUnique('timeline_item_anchor',"template_id='".$this->Get('template_id')."' AND user_id='".$this->Get('user_id')."'"))
			$this->LogError('This Anchor is already in use','timeline_item_anchor');
		
					
		return count($this->errors)==0;
  	}

	public function Save()
	{
		global $HTTP_POST_VARS;
		
		$old=new timeline_item($this->id);

		$keep_editing_name=$this->GetFieldName('keep_editing');

		$ret=parent::Save();
//		$this->Retrieve();

		if($ret)
		{
			$this->SaveImageFile('timeline_item_image_file',file::GetPath('timeline_item_upload'),$this->id);
			$this->SaveImageFile('timeline_item_file',file::GetPath('timeline_item_upload'),$this->id);
			
			if(!count($this->errors))
				$this->saved=true;

			if($this->Get('user_id'))
			{
				$user=new user($this->Get('user_id'));

			 	//flow date back to contract.
				if($this->Get('timeline_item_reference_date_type')=='CONTRACT' and $user->Get('user_under_contract'))
				{
					$user_contract_date=new user_contract_date();
					$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($this->Get('timeline_item_reference_date'),$user->id));
					$user_contract_date->Set('user_contract_date_override',0);
					$user_contract_date->CalculateDate();

					if($this->Get('timeline_item_date')!=$user_contract_date->Get('user_contract_date_date'))
						$user_contract_date->Set('user_contract_date_override',1);
					else
						$user_contract_date->Set('user_contract_date_override',0);

					$user_contract_date->Set('user_contract_date_date',$this->Get('timeline_item_date'));
					$user_contract_date->Update();

				}

				$this->CalculateDate();
			}

			if($old->Get('timeline_item_complete')!=$this->Get('timeline_item_complete'))
			{
				$status=$this->Get('timeline_item_complete')?'Complete':' Not Complete';
				$class=$this->Get('timeline_item_completed_class');
				$object=new $class($this->Get('timeline_item_completed_id'));
				$this->Set('activity_log_id',activity_log::Log($object,'TIMELINE_ITEM_STATUS',$this->Get('timeline_item_title').' Marked As '.$status,$this->Get('user_id')));
				$this->Update();
			}

		  	database::query("DELETE FROM conditions_to_timeline_items WHERE timeline_item_id=".$this->id."");
			foreach($this->related['conditon_ids'] as $foreign_id)
			{
				if($foreign_id>0)	  
				{
					$condition_to_timeline_item=new condition_to_timeline_item();
					$condition_to_timeline_item->CreateFromKeys(array('timeline_item_id','condition_id'),array($this->id,$foreign_id));
					$condition_to_timeline_item->Set('condition_to_timeline_item_action',$HTTP_POST_VARS['condition_actions'][$foreign_id]);
					$condition_to_timeline_item->Update();
				}
			}

			if($this->id and !$this->Get('timeline_item_complete'))
			  	database::query("UPDATE timeline_items SET timeline_item_complete=0 WHERE depends_on_timeline_item_id=".$this->id." AND timeline_item_complete!=0");

			if($new)
				activity_log::Log($this,'TIMELINE_ITEM_CREATED',$this->Get('timeline_item_title').' Created',$this->Get('user_id'));
			else
				$this->RecordChanges($old);
		}		
		

		if($HTTP_POST_VARS[$keep_editing_name] and $ret)
		{
			$this->msg='Your Changes Have Been Saved';
			return false;
		}
		return $ret;	
	}

	public function RecordChanges($old)
	{

		$changes=array();
		if($old->Get('timeline_item_title')!=$this->Get('timeline_item_title'))
			$changes[]="Heading changed from ".$old->Get('timeline_item_title').' to '.$this->Get('timeline_item_title');
		if($old->Get('timeline_item_image_file')!=$this->Get('timeline_item_image_file'))
		{
			$orig="";
			if($old->Get('timeline_item_image_file'))
				$orig="<a href='".file::GetPath('timeline_item_upload').$old->Get('timeline_item_image_file')."' target='_blank'><img src='".$old->GetThumb(100,100)."'></a>";			
			$new="";
			if($this->Get('timeline_item_image_file'))
				$new="<a href='".file::GetPath('timeline_item_upload').$this->Get('timeline_item_image_file')."' target='_blank'><img src='".$this->GetThumb(100,100)."'></a>";			

			if($orig and $new)
				$changes[]="Image changed from ".$orig." to ".$new;
			else if($new)
				$changes[]="Image added ".$new;
			else if($orig)
				$changes[]="Image ".$orig." removed";
		}
		if($old->Get('timeline_item_image_active')!=$this->Get('timeline_item_image_active'))
		{
		 	if($this->Get('timeline_item_image_active'))
				$changes[]="Item Restored";
			else
				$changes[]="Item Delted";
		}
		if($old->Get('timeline_item_fa_class')!=$this->Get('timeline_item_fa_class'))
		{
			$orig="<i class='".$old->Get('timeline_item_fa_class')."'></i>";
			$new="<i class='".$this->Get('timeline_item_fa_class')."'></i>";
			$changes[]="Icon changed from ".$orig.' to '.$new;
		}
		if(html_entity_decode($old->Get('timeline_item_summary'))!=html_entity_decode($this->Get('timeline_item_summary')))
			$changes[]="Content changed from <div>".$old->Get('timeline_item_summary')."</div> to  <div>".$this->Get('timeline_item_summary')."</div>";
		if(html_entity_decode($old->Get('timeline_item_content'))!=html_entity_decode($this->Get('timeline_item_content')))
			$changes[]="Learn More Content changed from <div>".$old->Get('timeline_item_content')."</div> to  <div>".$this->Get('timeline_item_content')."</div>";
		if($old->Get('timeline_item_file')!=$this->Get('timeline_item_file'))
		{
			$orig="";
			if($old->Get('timeline_item_image_file'))
				$orig="<a href='".file::GetPath('timeline_item_upload').$old->Get('timeline_item_image_file')."' target='_blank'>".$old->Get('timeline_item_image_file')."</a>";			
			$new="";
			if($this->Get('timeline_item_image_file'))
				$orig="<a href='".file::GetPath('timeline_item_upload').$this->Get('timeline_item_image_file')."' target='_blank'>".$this->Get('timeline_item_image_file')."</a>";			

			if($orig and $new)
				$changes[]="Learn More File changed from ".$orig." to ".$new;
			else if($new)
				$changes[]="Learn More File added ".$new;
			else if($orig)
				$changes[]="Learn More File ".$orig." removed";
		}

		if($old->Get('timeline_item_url')!=$this->Get('timeline_item_url'))
		{
			$orig="";
			if($old->Get('timeline_item_image_file'))
				$orig="<a href='".$old->Get('timeline_item_url')."' target='_blank'>".$old->Get('timeline_item_url')."</a>";			
			$new="";
			if($this->Get('timeline_item_image_file'))
				$orig="<a href='".$this->Get('timeline_item_url')."' target='_blank'>".$this->Get('timeline_item_url')."</a>";			

			if($orig and $new)
				$changes[]="Learn More Link changed from ".$orig." to ".$new;
			else if($new)
				$changes[]="Learn More Link added ".$new;
			else if($orig)
				$changes[]="Learn More Link ".$orig." removed";
		}
		if($old->Get('timeline_item_for')!=$this->Get('timeline_item_for'))
		{
			$orig='';
			if($old->Get('timeline_item_for')=='USER')
				$orig='Client';
			if($old->Get('timeline_item_for')=='OTHER')
				$orig='Agent/Other';
			if($old->Get('timeline_item_for')=='AGENT')
				$orig='Agent Only';
			$new='';
			if($this->Get('timeline_item_for')=='USER')
				$new='Client';
			if($this->Get('timeline_item_for')=='OTHER')
				$new='Agent/Other';
			if($this->Get('timeline_item_for')=='AGENT')
				$new='Agent Only';
			$changes[]="Responsibility changed from ".$orig.' to '.$new;
		}
//		if($old->Get('timeline_item_agent_only')!=$this->Get('timeline_item_agent_only'))
//		{
//			$orig=$old->Get('timeline_item_agent_only')?'Yes':'No';
//			$new=$this->Get('timeline_item_agent_only')?'Yes':'No';
//			$changes[]="Agent Only changed from ".$orig.' to '.$new;
//		}
		if($old->Get('timeline_item_anchor')!=$this->Get('timeline_item_anchor'))
		{
			$orig=$old->Get('timeline_item_anchor');
			$new=$this->Get('timeline_item_anchor');
			$changes[]="Anchor changed from ".$orig.' to '.$new;
		}
		if($old->Get('timeline_item_function')!=$this->Get('timeline_item_function'))
			$changes[]="public function changed from ".$old->Get('timeline_item_function').' to '.$this->Get('timeline_item_function');
		if($old->Get('depends_on_timeline_item_id')!=$this->Get('depends_on_timeline_item_id'))
		{
		 	$orig_timeline_item=new timeline_item($old->Get('depends_on_timeline_item_id'));
			$orig='(none)';
			if($old->Get('depends_on_timeline_item_id'))	
				$orig=$orig_timeline_item->Get('timeline_item_title');
		 	$new_timeline_item=new timeline_item($this->Get('depends_on_timeline_item_id'));
			$new='(none)';
			if($this->Get('depends_on_timeline_item_id'))	
				$new=$new_timeline_item->Get('timeline_item_title');
			$changes[]="Depnds on timeline item changed from ".$orig." to ".$new;
		}
		if(!$old->Get('depends_on_timeline_item_id') and !$this->Get('depends_on_timeline_item_id'))
		{
			if($old->Get('timeline_item_reference_date')!=$this->Get('timeline_item_reference_date'))
			{
				$orig=$old->GetReferenceDateType();
				$new=$this->GetReferenceDateType();
				$changes[]="Reference Date changed from ".$orig.' to '.$new;
				
			}
			if(($old->Get('timeline_item_reference_date_before')!=$this->Get('timeline_item_reference_date_before')) or ($old->Get('timeline_item_reference_date_days')!=$this->Get('timeline_item_reference_date_days')))
			{
				$orig=($old->Get('timeline_item_reference_date_days').' Days '.($old->Get('timeline_item_reference_date_before')?"Before":"After").' '.$old->GetReferenceDateType());
				$new=($this->Get('timeline_item_reference_date_days').' Days '.($this->Get('timeline_item_reference_date_before')?"Before":"After").' '.$this->GetReferenceDateType());
				$changes[]="Timing changed from ".$orig.' to '.$new;
			}
			if($old->Get('timeline_item_date')!=$this->Get('timeline_item_date'))
			{
				$orig=new DBDate($old->Get('timeline_item_date'));
				$new=new DBDate($this->Get('timeline_item_date'));
				$changes[]="Date changed from ".$orig->GetDate('m/d/Y').' to '.$new->GetDate('m/d/Y');
			}
		}

		if(count($changes))
			activity_log::Log(new agent($this->Get('agent_id')),'TIMELINE_ITEM_UPDATED','Timeline Item '.$this->Get('timeline_item_title').' Updated'."\r\n\r\n".implode("\r\n",$changes),$this->Get('user_id'));
	}
	
	public function Delete()
	{
		//delete any dependant items.
	  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',"depends_on_timeline_item_id!=0 AND depends_on_timeline_item_id=".$this->id."");
		$timeline_items->Retrieve();
		$timeline_items->Each('Delete');

		$this->Set('timeline_item_active',0);
		$this->Update();		

		
		activity_log::Log(new agent($this->Get('agent_id')),'TIMELINE_ITEM_UPDATED','Timeline Item '.$this->Get('timeline_item_title').' Deleted',$this->Get('user_id'));

	}

	public function UnDelete()
	{
		//undelete any dependant items.
	  	$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',"depends_on_timeline_item_id!=0 AND depends_on_timeline_item_id=".$this->id."");
		$timeline_items->Retrieve();
		$timeline_items->Each('UnDelete');

		//$this->Set('timeline_item_order',10000);
		$this->Set('timeline_item_active',1);
		$this->Update();

		activity_log::Log(new agent($this->Get('agent_id')),'TIMELINE_ITEM_UNDELETED','Timeline Item '.$this->Get('timeline_item_title').' Restored',$this->Get('user_id'));
	}


	public function xDelete()
	{
		//**FILE**//
		$this->DeleteFile('timeline_item_image_file',file::GetPath('timeline_item_upload'));
		$this->DeleteFile('timeline_item_file',file::GetPath('timeline_item_upload'));
		$this->DeleteCrop('timeline_item_file');

		parent::Delete();
	}

	public function NewAgentCard($params=array())
	{
		$agent=new agent($this->Get('agent_id'));
		$js="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."','EditTimeline','timeline_container','null','','action=".$this->GetFormAction('save')."&template_id=".$params['template_id']."&user_id=".$params['user_id']."',function(){height_handler();});";
		echo("<div class='timeline_item timeline_item_NEW' onclick=\"".$js."\" data-info='TIMELINE_NEW' data-info-none='none'>");
		echo("<div class='box_inner'>");
		echo('<div class="card_heading">');
		echo "<div class='timeline_item_heading'>";
		echo("<h3><i class='fa fa-plus'></i> New Timeline Item</h3>");
		echo('</div>');
		echo('</div>');
		echo('<div class="card_body">');
		echo('<div class="card_content">');
		echo("<br>");
		echo('</div>');
		echo('</div>');
		echo('</div>');
		echo('</div>');		
	}

	public function AgentCardJS($params=array(),$container='',$js2='',$loadingclass='')
	{
		if($params['edit'])
			$js2.="jQuery('#".$this->GetFormAction($params['edit'])."').focus();";
		$js2.="height_handler();";

		$passparams=array();
		foreach($params as $k=>$v)
			$passparams[]=$k.'='.$v;
		$js="ObjectFunctionAjax('timeline_item','".$this->id."','AgentCard','".$container."','".$this->GetFieldName('AgentCardForm')."','".$loadingclass."','".implode('&',$passparams)."',function(){".$js2."});";
		
		return $js;
	}

	public function IsEditing($params)
	{
		return ($params['edit_timeline_item_id']==$this->id);
	}

	public function AgentCard($params=array())
	{
//		if($this->IsNotApplicable())
//			return;

		//$user_contract_date=new user_contract_date();
		//$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($this->Get('timeline_item_reference_date'),$this->Get('user_id')));
		//if($this->Get('user_id') and $user_contract_date->Get('user_contract_date_na'))
		//	return;

		//new => edit.
		if(Session::Get('timeline_item_id')==$this->id)
		{
			$params['edit_timeline_item_id']=$this->id;
			Session::Set('timeline_item_id','');
		}
		if($params['reset_date'])
		{
			$this->Set('timeline_item_date','');
			$this->Set('timeline_item_reference_date_type','NONE');
			$this->Set('timeline_item_reference_date_days',0);
			$this->Update();
		}

		$agent=new agent($this->Get('agent_id'));
		$savejs="UpdateWYSIWYG();";
		$savejs.=$this->AgentCardJS(array('action'=>$this->GetFormAction('save')));
		if(!$this->id)
			$savejs='';
		$savejs2="UpdateWYSIWYG();";
		$savejs2.=$this->AgentCardJS(array('action'=>$this->GetFormAction('save'),'edit_timeline_item_id'=>$this->id),$this->GetFieldName('AgentCardContainer'));
		if(!$this->id)
			$savejs2='';
		$savejs_advanced="UpdateWYSIWYG();";
		$savejs_advanced.=$this->AgentCardJS(array('action'=>$this->GetFormAction('save'),'edit_timeline_item_id'=>$this->id,'advanced'=>1),$this->GetFieldName('AgentCardContainer'));
		if(!$this->id)
			$savejs_advanced='';


		$this->SetFlag('ALLOW_BLANK');
		if(!$params['parent_action'])
			$this->ProcessAction();


		$class=array();
//		$class[]="timeline_item_".$this->Get('timeline_item_for');
		if(!$this->id)		
			$class[]="timeline_item_NEW";			
		if($this->DependsOnIncompleteItem())
			$class[]="timeline_item_DEPENDANT";
		if(!$this->Get('timeline_item_active'))	
			$class[]='timeline_item_DELETED';
		if($this->Get('timeline_item_complete'))			
			$class[]="timeline_item_COMPLETED";
		if($this->IsEditing($params))
			$class[]="timeline_item_EDITING";
		$class[]="timeline_item_AGENT";

		$user=new user($this->Get('user_id'));

	  	$user_conditions=new DBRowSetEX('user_conditions','user_condition_id','user_condition',"user_id='".$user->id."' AND user_condition_checked=1");
		if($user->Get('user_under_contract'))//must be a user and under contract to considr conditions.
			$user_conditions->Retrieve();

	 	$user_conditions_condition_ids=array(-1);
		foreach($user_conditions->items as $user_condition)
		 	$user_conditions_condition_ids[]=$user_condition->Get('condition_id');
		 	
		$user_contract_date=new user_contract_date();
		if($user->Get('user_under_contract')) //must be a user and under contract to have a relevant contract date.
			$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($this->Get('timeline_item_reference_date'),$this->Get('user_id')));

	  	$conditions_to_timeline_items=new DBRowSetEX('conditions_to_timeline_items','condition_id','condition',"condition_to_timeline_item_action='HIDE' AND timeline_item_id='".$this->id."' AND condition_id IN(".implode(',',$user_conditions_condition_ids).")");
		if($user->Get('user_under_contract'))//must be a user and under contract to consider conditions.
			$conditions_to_timeline_items->Retrieve();

		$bgclass='';
		$textclass='';
		if($user_contract_date->Get('user_contract_date_na'))
			$null;
		else if(count($conditions_to_timeline_items->items))
			$null;
		else if(!$this->Get('timeline_item_active'))	
			$null;
		else if($this->Get('timeline_item_complete'))	
			$null;
		else if($this->DependsOnIncompleteItem())
			$null;
		else if($this->Get('timeline_item_for')=='OTHER')
		{
			$bgclass='agent_bg_color1';
			$borderclass='agent_border_color1';
			$borderrightclass='agent_background_color1';
			$textclass='agent_color1';
		}
		else if($this->Get('timeline_item_for')=='AGENT')
		{
			$bgclass='agent_bg_color1';
			$borderclass='agent_border_color1';
			$borderrightclass='agent_background_color1';
			$textclass='agent_color1';
		}
		else if($this->Get('timeline_item_for')=='USER')
		{
			$bgclass='agent_bg_color2';
			$borderclass='agent_border_color2';
			$borderrightclass='agent_background_color2';
			$textclass='agent_color2';
		}


		echo("<a name='".$this->GetFieldName('anchor')."'></a>");
		if($this->Get('timeline_item_anchor'))
			echo("<a name='".$this->Get('timeline_item_anchor')."'></a>");

		form::Begin($this->GetFieldName('AgentCardContainer'),'POST',true,array('id'=>$this->GetFieldName('AgentCardForm')));
		if($this->Get('timeline_item_type')=='CONTENT')
		{
			echo("<div class='timeline_item ".implode(' ',$class)." timeline_milestone info_group' id='".$this->GetFieldName('timeline_item')."'>");
			$js="jQuery('#".$this->GetFieldName('timeline_item')."').toggleClass('timeline_item_expanded');";
			echo "<div class='timeline_item_heading' onclick=\"".$js."\">";
			$this->AgentLinks($params);			
			echo("</div>");
			if($this->IsEditing($params))
			{
				//echo("<div class='timeline_item_body'>");
				//if($this->id and $this->GetFlag('ADMIN'))
				{
					echo "<div class='row admin_only'>";
					echo "<div class='col-md-3'>";
					echo "<div class='line'>Item Type</div>";
					echo("</div>");		
					echo "<div class='col-md-9'>";
					echo("<div class='line'>");		
					form::DrawSelect($this->GetFieldName('timeline_item_type'),array('Timeline Item'=>'TIMELINE','Content Only'=>'CONTENT'),$this->Get('timeline_item_type'),array('onchange'=>$savejs2,'data-info'=>'TIMELINE_TYPE'));	
					echo("</div>");
					echo("</div>");
					echo("</div>");
				}
//				if($this->id and $this->GetFlag('ADMIN'))
				{
					echo "<div class='row admin_only'>";
					echo "<div class='col-md-3'>";
					echo "<div class='line'>Special Behavior</div>";
					echo("</div>");		
					echo "<div class='col-md-9'>";
					echo("<div class='line'>");		
					form::DrawSelect($this->GetFieldName('timeline_item_behavior'),$this->GetBehaviors(),$this->Get('timeline_item_behavior'),array('class'=>$this->GetError('timeline_item_behavior')?'error':'text'));
					echo("</div>");
					echo("</div>");
					echo("</div>");
				}

				echo("<div class='line'>");		
				echo("<div class='client_intro_text'>");
				$attr=array('Placeholder'=>"Item Name",'onchange'=>$savejs,'class'=>'H3','data-info'=>'TIMELINE_HEADING');
				if(!$this->Get('timeline_item_active'))
					$attr['disabled']=true;	
				//form::DrawTextInput($this->GetFieldName('timeline_item_title'),$this->Get('timeline_item_title'),$attr);
				if(!$this->Get('timeline_item_active'))
					echo("<div class='wysiwyg_inactive'>".$this->Get('timeline_item_summary')."</div>");
				else
					form::DrawTextArea($this->GetFieldName('timeline_item_summary'),$this->Get('timeline_item_summary'),array('class'=>'wysiwyg_input wysiwyg','onchange'=>$savejs));
				$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
				$wysiwyg_info.="onchange_callback:function(){".$savejs."},\r\n";
				wysiwyg::RegisterMode($this->GetFieldName('SIMPLE_LINK_HEADLINES'),$wysiwyg_info);
				form::MakeWYSIWYG($this->GetFieldName('timeline_item_summary'),$this->GetFieldName('SIMPLE_LINK_HEADLINES'));
				echo("</div>");
				echo("</div>");

				//echo("</div>");

			}
			else
			{
				echo("<div class='timeline_item timeline_milestone'>");
				echo("<div class='client_intro_text'>".$this->Get('timeline_item_summary')."</div>");
				echo("</div>");				
			}
		}
		else if($this->Get('timeline_item_type')=='TIMELINE')
		{
			//$user=new user($this->Get('user_id'));
			$date=false;
			$user=new user($this->Get('user_id'));
			$date=new DBDAte($this->Get('timeline_item_date'));	
			$depends_on=new timeline_item($this->Get('depends_on_timeline_item_id'));

			if($user_contract_date->Get('user_contract_date_na'))
				$class[]="timeline_item_NA";			
			else if(count($conditions_to_timeline_items->items))
				$class[]="timeline_item_NA";			

			echo "<div class='timeline_item ".implode(' ',$class)." info_group' id='".$this->GetFieldName('timeline_item')."'>";
			if(!$this->Get('timeline_item_active'))	
				echo("<h1>Deleted From Timeline</h1>");
			else if(count($conditions_to_timeline_items->items))
			{
				$info="Not Applicable";
				foreach($conditions_to_timeline_items->items as $condition)
				{
					$info="Not Applicable - ".$condition->Get('condition_name');
				}
				echo("<h1>".$info."</h1>");				
			}			
			else if($user_contract_date->Get('user_contract_date_na'))
			{
				$info="Not Applicable";
			  	$list=new DBRowSetEX('conditions_to_contract_dates','condition_id','condition',"condition_to_contract_date_action='HIDE' AND contract_date_id='".$user_contract_date->Get('contract_date_id')."'");
				$list->Retrieve();
				foreach($list->items as $condition)
				{
				 	$user_condition=new user_condition();
				 	$user_condition->CreateFromKeys(array('condition_id','user_id'),array($condition->id,$params['user_id']));
				 	if($user_condition->Get('user_condition_checked'))
						$info="Not Applicable - ".$condition->Get('condition_name');
				}
				echo("<h1>".$info."</h1>");
			}
			else if($this->Get('timeline_item_complete'))			
				echo("<h1>Complete</h1>");
			else if($this->DependsOnIncompleteItem())
			 	echo("<h3 class='".$textclass."'>Waiting for ".$depends_on->Get('timeline_item_title')." to be marked as complete</h3>");
			else if($user->Get('user_under_contract') and $date->IsValid() and ($this->Get('timeline_item_reference_date_type')!=='NONE'))// and $user->Get('user_under_contract'))
				echo("<h1 class='".$textclass."'>".$date->GetDate('l, F j')."</h1>");
			else if(!$user->Get('user_under_contract') and $depends_on->Get('timeline_item_complete'))
				$null;
			else if($this->Get('timeline_item_reference_date_type')!=='NONE')
				echo("<h1 class='".$textclass."'>".$this->GetUserTiming()."</h1>");

			$js="jQuery('#".$this->GetFieldName('timeline_item')."').toggleClass('timeline_item_expanded');";
			echo "<div class='timeline_item_heading ".$bgclass."' onclick=\"".$js."\">";
			echo "<div class='arrow-left ".$bgclass."'></div>";
			$icon=$this->Get('timeline_item_fa_class');
			if($this->Get('timeline_item_complete'))			
				$icon='fa fa-check';
			if(!$icon and !$this->id)
				$icon='fas fa-icons';
			//if($this->IsEditing($params))
				$js="ObjectFunctionAjaxPopup('Choose Icon','timeline_item','".$this->id."','ChooseIcon','NULL','','',function(){});";
			if($this->Get('timeline_item_complete'))
				$js='';
			echo "<div class='timeline_item_icon' data-info='TIMELINE_ICON' data-info-click='click'><i onclick=\"".$js."\" id='".$this->GetFieldName('timeline_item_fa_icon')."' class='".$icon." ".$bgclass."'></i></div>";
			form::DrawHiddenInput($this->GetFieldName('timeline_item_fa_class'),$this->Get('timeline_item_fa_class'));
			$attr=array('Placeholder'=>"Item Name",'onchange'=>$savejs,'class'=>'H3','data-info'=>'TIMELINE_HEADING');
			if(!$this->Get('timeline_item_active'))
				$attr['disabled']=true;	
			if($this->IsEditing($params))
				form::DrawTextInput($this->GetFieldName('timeline_item_title'),$this->Get('timeline_item_title'),$attr);
			else
				echo "<h3>".$this->Get('timeline_item_title')."</h3>";				
			$this->AgentLinks($params);
			echo "</div>";
			
			if($this->IsEditing($params))
			{
				echo "<div class='timeline_item_body' id='".$this->GetFieldName('timeline_item_body')."'>";
				echo("<div class='line'>");
				echo("Who is responsible for this item?");
				echo("</div>");
				$opts=array('Who is responsible for this item?'=>'','Client'=>'USER','Agent/Other'=>'OTHER','Agent Only'=>'AGENT');
				echo("<div class='line'>");
				echo("<div class='row'>");
				echo("<div class='col-sm-4'>");
				echo("<label>");
				form::DrawRadioButton($this->GetFieldName('timeline_item_for'),'USER',$this->Get('timeline_item_for')=='USER',array('onchange'=>$savejs2,'data-info'=>'TIMELINE_FOR','class'=>'x'));
				echo(" Client </label>");
				echo("</div>");
				echo("<div class='col-sm-4'>");
				echo("<label>");
				form::DrawRadioButton($this->GetFieldName('timeline_item_for'),'OTHER',$this->Get('timeline_item_for')=='OTHER',array('onchange'=>$savejs2,'data-info'=>'TIMELINE_FOR','class'=>'x'));
				echo(" Agent/Other </label>");
				echo("</div>");
				echo("<div class='col-sm-4'>");
				echo("<label>");
				form::DrawRadioButton($this->GetFieldName('timeline_item_for'),'AGENT',$this->Get('timeline_item_for')=='AGENT',array('onchange'=>$savejs2,'data-info'=>'TIMELINE_FOR','class'=>'x'));
				echo(" Agent Only </label>");
				echo("</div>");
				echo("</div>");
				echo("</div>");
				
				if(!$this->Get('depends_on_timeline_item_id') and $this->Get('timeline_item_reference_date_type')!='CONTRACT')
				{
					echo("<div class='line'>");
					echo("When would you like to schedule this item?");
					echo("</div>");
					echo("<div class='line'>");
					echo("<div class='row'>");
					echo("<div class='col-sm-4'>");
					echo("<label>");
					form::DrawRadioButton($this->GetFieldName('timeline_item_reference_date_type'),'NONE',$this->Get('timeline_item_reference_date_type')=='NONE',array('onchange'=>$savejs2,'data-info'=>'TIMELINE_FOR','class'=>'x'));
					echo("  Not Date Specific </label>");
					echo("</div>");
					echo("<div class='col-sm-4'>");
					echo("<label>");
					form::DrawRadioButton($this->GetFieldName('timeline_item_reference_date_type'),'EXACT',$this->Get('timeline_item_reference_date_type')=='EXACT',array('onchange'=>$savejs2,'data-info'=>'TIMELINE_FOR','class'=>'x'));
					echo(" Specify Date </label>");
					if($this->Get('timeline_item_reference_date_type')=='EXACT')
					{
						echo("<div class='line'>");
						$d=new DBDAte($this->Get('timeline_item_date'));			
						form::DrawTextInput($this->GetFieldName('timeline_item_date'),$d->IsValid()?$d->GetDate('m/d/Y'):'',array('placeholder'=>'Choose Date','class'=>'text datepicker','onchange'=>$savejs2));	
						form::DrawHiddenInput($this->GetFieldName('timeline_item_holiday'),1);
						echo("</div>");
					}					
					echo("</div>");
					echo("<div class='col-sm-4'>");
					echo("<label>");
					form::DrawRadioButton($this->GetFieldName('timeline_item_reference_date_type'),'RELATIVE',$this->Get('timeline_item_reference_date_type')=='RELATIVE',array('onchange'=>$savejs2,'data-info'=>'TIMELINE_FOR','class'=>'x'));
					echo(" Relative to Another Date </label>");
					echo("</div>");
					echo("</div>");
					echo("</div>");
					if($this->Get('timeline_item_reference_date_type')=='RELATIVE')
					{
						$opts=array('--Select Date--'=>'');
						$rs=database::query("SELECT * FROM contract_dates ORDER BY contract_date_order");
						while($rec=database::fetch_array($rs))
							$opts["Relative To ".$rec['contract_date_name']]=$rec['contract_date_id'];

						echo("<div class='line'>");
						echo("<div class='row'>");
						echo("<div class='col-md-8'><br></div>");
						echo("<div class='col-md-4'>");
						form::DrawSelect($this->GetFieldName('timeline_item_reference_date'),$opts,$this->Get('timeline_item_reference_date'),array('onchange'=>$savejs2,'data-info'=>'TIMELINE_DATE'));
						echo("</div>");
						echo("</div>");
						echo("</div>");

						echo("<div class='line'>");
						echo("<div class='row'>");
						echo("<div class='col-md-8'><br></div>");
						echo("<div class='col-md-2'>");
						$opts=array();
						for($i=0;$i<365;$i++)
							$opts[$i.' Days']=$i;
						form::DrawSelect($this->GetFieldName('timeline_item_reference_date_days'),$opts,$this->Get('timeline_item_reference_date_days'),array('onchange'=>$savejs2));
						echo("</div>");
						echo("<div class='col-md-2'>");
						form::DrawSelect($this->GetFieldName('timeline_item_reference_date_before'),array('After'=>0,'Before'=>1),$this->Get('timeline_item_reference_date_before'),array('onchange'=>$savejs2,'class'=>'timeline_item_reference_date_reference'));
						echo("</div>");
						echo("</div>");
						echo("</div>");
						echo("<div class='line'>");
						echo("<div class='row'>");
						echo("<div class='col-md-8'><br></div>");
						echo("<div class='col-xs-4'>");
						form::DrawSelect($this->GetFieldName('timeline_item_holiday'),array('Cannot Occur on Weekend or Holiday'=>'0','CAN Occur on Weekend or Holiday'=>'1'),$this->Get('timeline_item_holiday'),array('class'=>$this->GetError('timeline_item_holiday')?'error':'text','onchange'=>$savejs2));
						echo("</div>");		
						echo("</div>");
						echo("</div>");
						
						if(!$this->Get('timeline_item_holiday'))
						{
							echo("<div class='line'>");
							echo("<div class='row'>");
							echo("<div class='col-md-8'><br></div>");
							echo("<div class='col-xs-4'>");
							if($this->Get('timeline_item_date_moved'))	
								echo("<div class='timeline_item_date_note'>Moved From Weekend Or Holiday</div>");
							if(holiday::IsHoliday($date) or ($date->GetDate('w')==0) or ($date->GetDate('w')==6))
								echo("<div class='timeline_item_date_note'>Falls on Weekend Or Holiday</div>");
							echo("</div>");		
							echo("</div>");
							echo("</div>");
						}
							

					}
				}
				else if($depends_on->Get('timeline_item_complete'))
				{
					echo("<div class='line'>");
					echo("<div class='row'>");
					echo("<div class='col-md-4'>Select Date</div>");
					echo("<div class='col-xs-4'>");
					$d=new date();
					if($this->Get('timeline_item_reference_date'))
						$d=new DBDAte($this->Get('timeline_item_date'));			
					form::DrawTextInput($this->GetFieldName('timeline_item_date'),$d->IsValid()?$d->GetDate('m/d/Y'):'',array('placeholder'=>'Choose Date','class'=>'text datepicker','onchange'=>$savejs2));	
					echo("</div>");		
		
					echo("<div class='col-xs-4'>");
					//form::DrawSelect($this->GetFieldName('timeline_item_holiday'),array('Cannot Occur on Weekend or Holiday'=>'0','CAN Occur on Weekend or Holiday'=>'1'),$this->Get('timeline_item_holiday'),array('class'=>$this->GetError('timeline_item_holiday')?'error':'text','onchange'=>$savejs2));
					form::DrawHiddenInput($this->GetFieldName('timeline_item_holiday'),1);						
					echo("</div>");		
					echo("</div>");
					echo("</div>");					
				}				
				else
				{
					/*
						echo("<div class='line'>");
						echo("<div class='row'>");
						echo("<div class='col-md-8'><br></div>");
						echo("<div class='col-xs-4'>");
						form::DrawSelect($this->GetFieldName('timeline_item_holiday'),array('Cannot Occur on Weekend or Holiday'=>'0','CAN Occur on Weekend or Holiday'=>'1'),$this->Get('timeline_item_holiday'),array('class'=>$this->GetError('timeline_item_holiday')?'error':'text','onchange'=>$savejs2));
						echo("</div>");		
						echo("</div>");
						echo("</div>");					
					*/
					form::DrawHiddenInput($this->GetFieldName('timeline_item_holiday'),1);						
				}
				

				echo "<div class='row'>";
				echo "<div class='col-md-3'>";
				echo "<div class='timeline_item_image drop_target' data-target='".$this->GetFieldName('timeline_item_image_file_ul')."' data-info='TIMELINE_IMAGE' data-info-none='NONE'>";
				if($this->Get('timeline_item_for')=='AGENT')
					echo "<img src='/images/agent-only-image-timeline.png'>";
				else if($this->Get('timeline_item_image_file'))
					echo "<img class='".$bgclass."' src='".$this->GetThumb(160*3,107*3)."'>";
				else
					echo "<img src='/images/no-image-timeline.png'>";
				if($this->Get('timeline_item_active') and ($this->Get('timeline_item_for')!='AGENT'))
				{
					echo "<div class='line'>";
					form::DrawFileInput($this->GetFieldName('timeline_item_image_file_ul'),$this->Get('timeline_item_image_file'),array('onchange'=>$savejs2));
					echo "</div>";
					echo "<div class='line'>";
					form::DrawTextInput($this->GetFieldName('timeline_item_image_alt'),$this->Get('timeline_item_image_alt'),array('placeholder'=>'Image Description','onchange'=>$savejs2));
					echo "</div>";
				}
				echo "</div>";
				echo "</div>";
				echo "<div class='col-md-9'>";
				echo "<div class='timeline_item_content' data-info='TIMELINE_CONTENT' data-info-none='none'>";
				if(!$this->Get('timeline_item_active'))
					echo("<div class='wysiwyg_inactive'>".$this->Get('timeline_item_summary')."</div>");
				else
					form::DrawTextArea($this->GetFieldName('timeline_item_summary'),$this->Get('timeline_item_summary'),array('class'=>'wysiwyg_input wysiwyg_timeline','onchange'=>$savejs));
				$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
				$wysiwyg_info.="onchange_callback:function(){".$savejs."},\r\n";
				wysiwyg::RegisterMode($this->GetFieldName('SIMPLE_LINK_HEADLINES'),$wysiwyg_info);
				form::MakeWYSIWYG($this->GetFieldName('timeline_item_summary'),$this->GetFieldName('SIMPLE_LINK_HEADLINES'));
				echo "</div>";
				echo "</div>";
				echo "</div>";

				echo "<div class='row'>";
				echo "<div class='col-md-12'>";
				echo "<div class='line'>";
				echo("<a class='' href='#' data-toggle='collapse' data-target='#".$this->GetFieldName('advanced_options')."' onclick=\"return false;\">Advanced Options</a>");		
				echo "</div>";
				echo "</div>";		
				echo("</div>");		



				echo "<div class='collapse ".($params['advanced']?'in':'')."' id='".$this->GetFieldName('advanced_options')."'>";

				echo("<div class='line'>");
				echo("<label>");
				form::DrawCheckbox($this->GetFieldName('timeline_item_reference_date_type_contract'),'CONTRACT',$this->Get('timeline_item_reference_date_type')=='CONTRACT',array('onchange'=>$savejs_advanced,'data-info'=>'TIMELINE_FOR','class'=>'x'));
				echo(" This item is a specific contract date</label>");
				echo("</div>");
				if($this->Get('timeline_item_reference_date_type')=='CONTRACT')
				{
					echo("<div class='line'>");
					echo("<div class='row'>");
					echo("<div class='col-md-8'>");
					form::DrawSelectFromSQL($this->GetFieldName('timeline_item_reference_date'),"SELECT * FROM contract_dates ORDER BY contract_date_order","contract_date_name","contract_date_id",$this->Get('timeline_item_reference_date'),array('onchange'=>$savejs_advanced,'data-info'=>'TIMELINE_DATE'));
					form::DrawHiddenInput($this->GetFieldName('timeline_item_reference_date_days'),0);
					echo("</div>");
					echo("<div class='col-md-4'>");
					if($user->Get('user_under_contract'))
					{
						$d=new DBDate($this->Get('timeline_item_date'));
						form::DrawTextInput($this->GetFieldName('timeline_item_date'),$d->IsValid()?$d->GetDate('m/d/Y'):'',array('placeholder'=>'Choose Date','class'=>'text datepicker','onchange'=>$savejs_advanced));	
					}
					echo("</div>");
					echo("</div>");
					echo("</div>");
				}


				echo("<div class='row'>");
				echo("<div class='col-md-3'>");
				echo("<div class='line'>Depends On:</div>");
				echo("</div>");
				echo("<div class='col-md-9'>");
				form::DrawSelectFromSQL($this->GetFieldName('depends_on_timeline_item_id'),"SELECT * FROM timeline_items WHERE timeline_item_id!='".$this->id."' AND template_id='".$this->Get('template_id')."' AND agent_id='".$this->Get('agent_id')."' AND user_id='".$this->Get('user_id')."' AND timeline_item_active=1","timeline_item_title","timeline_item_id",$this->Get('depends_on_timeline_item_id'),array('onchange'=>$savejs_advanced),array('(None)'=>'0'));
				echo("</div>");
				echo("</div>");
				echo "<div class='row'>";
				echo "<div class='col-md-3'>";
				echo "<div class='line'>Hide If</div>";
				echo("</div>");		
				echo "<div class='col-md-9'>";
				echo("<div class='line'>");		
				$this->RetrieveRelated('conditon_ids','conditions_to_timeline_items',"timeline_item_id='".$this->id."'",'','condition_id');
			  	$list=new DBRowSetEX('conditions','condition_id','condition',1,'condition_order');
				$list->Retrieve();
				foreach($list->items as $condition)
				{
					$condition_to_timeline_item=new condition_to_timeline_item();
					$condition_to_timeline_item->InitByKeys(array('timeline_item_id','condition_id'),array($this->id,$condition->id));
		
					echo("<div class='row'>");		
					echo("<div class='col-md-12'>");		
					echo("<label>");		
					form::DrawCheckbox('condition_ids[]',$condition->id,in_array($condition->id,$this->related['conditon_ids']),array('class'=>'','onclick'=>$savejs));
					echo(" ".$condition->Get('condition_name'));		
					echo("</label>");		
					echo("</div>");		
//					echo("<div class='col-md-3'>");		
//					form::DrawSelect('condition_actions['.$condition->id.']',array_flip($condition_to_timeline_item->GetActions()),$condition_to_timeline_item->Get('condition_to_timeline_item_action'));
					form::DrawHiddenInput('condition_actions['.$condition->id.']','HIDE');
//					echo("</div>");		
					echo("</div>");		
				}
				echo("</div>");
				echo("</div>");
				echo("</div>");
//				if($this->GetFlag('ADMIN'))
				{
					echo "<div class='row admin_only'>";
					echo "<div class='col-md-3'>";
					echo "<div class='line'>Anchor</div>";
					echo("</div>");		
					echo "<div class='col-md-9'>";
					echo("<div class='line'>");		
					form::DrawTextInput($this->GetFieldName('timeline_item_anchor'),$this->Get('timeline_item_anchor'),array('class'=>$this->GetError('timeline_item_anchor')?'error':'text','onchange'=>$savejs));
					echo("</div>");
					echo("</div>");
					echo("</div>");
				}					
//				if($this->id and $this->GetFlag('ADMIN'))
				{
					echo "<div class='row admin_only'>";
					echo "<div class='col-md-3'>";
					echo "<div class='line'>Special Behavior</div>";
					echo("</div>");		
					echo "<div class='col-md-9'>";
					echo("<div class='line'>");		
					form::DrawSelect($this->GetFieldName('timeline_item_behavior'),$this->GetBehaviors(),$this->Get('timeline_item_behavior'),array('class'=>$this->GetError('timeline_item_behavior')?'error':'text','onchange'=>$savejs));
					echo("</div>");
					echo("</div>");
					echo("</div>");
				}
//				if($this->id and $this->GetFlag('ADMIN'))
				{
					echo "<div class='row admin_only'>";
					echo "<div class='col-md-3'>";
					echo "<div class='line'>Agent Can Edit?</div>";
					echo("</div>");		
					echo "<div class='col-md-9'>";
					echo("<div class='line'>");		
					form::DrawSelect($this->GetFieldName('timeline_item_not_editable'),array('Yes'=>0,'No'=>1),$this->Get('timeline_item_not_editable'),array('class'=>$this->GetError('timeline_item_not_editable')?'error':'text','onchange'=>$savejs));
					echo("</div>");
					echo("</div>");
					echo("</div>");
				}
//				if($this->id and $this->GetFlag('ADMIN'))
				{
					echo "<div class='row admin_only'>";
					echo "<div class='col-md-3'>";
					echo "<div class='line'>Agent Can Delete?</div>";
					echo("</div>");		
					echo "<div class='col-md-9'>";
					echo("<div class='line'>");		
					form::DrawSelect($this->GetFieldName('timeline_item_not_deletable'),array('Yes'=>0,'No'=>1),$this->Get('timeline_item_not_deletable'),array('class'=>$this->GetError('timeline_item_not_deletable')?'error':'text','onchange'=>$savejs));
					echo("</div>");
					echo("</div>");
					echo("</div>");
				}
				
				echo "<div class='row admin_only'>";
				echo "<div class='col-md-3'>";
				echo "<div class='line'>Type</div>";
				echo("</div>");		
				echo "<div class='col-md-9'>";
				echo("<div class='line'>");		
				form::DrawSelect($this->GetFieldName('timeline_item_type'),array('Timeline Item'=>'TIMELINE','Content Only'=>'CONTENT'),$this->Get('timeline_item_type'),array('onchange'=>$savejs_advanced,'data-info'=>'TIMELINE_TYPE'));	
				echo("</div>");
				echo("</div>");
				echo("</div>");

				echo("</div>");

				$class='button_disabled';
				$detail_type='';
				if($this->Get('timeline_item_url'))
				{
					$class='';
					$detail_type='URL';
				}
				else if($this->Get('timeline_item_file'))
				{
					$class='';
					$detail_type='FILE';
				}
				else if(trim(strip_tags($this->Get('timeline_item_content'),'<img><iframe>')))
					$class='';
				if($this->id)
				{
					$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
					$wysiwyg_info.="onchange_callback:function(){".$savejs2."},\r\n";
					wysiwyg::RegisterMode($this->GetFieldName('SIMPLE_LINK_HEADLINES'),$wysiwyg_info);
					html::HoldOutput();
					form::MakeWYSIWYG($this->GetFieldName('timeline_item_content'),$this->GetFieldName('SIMPLE_LINK_HEADLINES'));				
					//$data=strip_tags(html::GetHeldOutput());
					html::ResumeOutput();
					$js="ObjectFunctionAjaxPopup('".$this->Get('timeline_item_title')."','timeline_item','".$this->id."','EditDetail','NULL','','detail_type=".$detail_type."',function(){".$data."},'timeline_item_detail');";
					//fire twice to get tinemce to resize?
					$js="ObjectFunctionAjax('timeline_item','".$this->id."','EditDetail','popup_content','NULL','','detail_type=".$detail_type."',function(){".$js."},'timeline_item_detail');";
					echo "<div class='timeline_item_link'><a data-info='TIMELINE_MORE_INFO' data-info-none='none' class='button ".$class."' href='#' onclick=\"".$js."return false;\"><i class='fas fa-pencil-alt'></i> Learn More</a></div>";
				}
				if(!$this->id)
				{
					echo('<div class="card_links">');
					$js="UpdateWYSIWYG();";
					$js.="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('save')."&parent_action=1');";
					echo("<a data-info='TIMELINE_ADD' data-info-none='none' class='button' href='#' onclick=\"".$js."return false;\" data-toggle='tooltip' title=''><i class='fa fa-plus'></i> Add Item</a>");
					echo "</div>";
				}
				echo "</div>";
			}
			else
			{
			 	$this->SetFlag('AGENT');
				$this->DisplayInner(array('editable'=>true));
			}
			echo "</div>";		
		}
		form::End();		
		$this->DisplayCustomNotice();
		echo "</div>";
		
		if(!$user->Get('user_under_contract') and ($this->Get('timeline_item_behavior')=='UNDERCONTRACTBUTTON') and $this->Get('user_id'))
		{
			$js="ObjectFunctionAjax('agent','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=".$user->GetFormAction('under_contract')."',function(){document.location='".dbRowEx::GetCurrentUser()->GetUserURL($user,'edit_user_dates.php')."';});return false;";
			echo "<div class='timeline_item_behavior_UNDERCONTRACTBUTTON'>";
			echo "<div class='agent_tools timeline_agent_tools'>";
			echo("<a class='not_under_contract agent_color1 agent_color2_hover' data-toggle='tooltip' title='Click when the property goes under contract.' href='#' onclick=\"".$js."\"><i class='icon fas fa-handshake-slash'></i><span class='text'><span><i class='not_under_contract fa fa-circle'></i><i class='under_contract fa fa-circle-check'></i>Under Contract</span></a>");
			echo "</div>";
			echo "</div>";
		}
		
	}

	public function ChooseIcon($params=array())
	{
		$iconsets=array();
		$iconsets['Primary Icons']=fa::GetPrimaryIcons();	
		$iconsets['All Icons']=fa::GetAllIcons();	
		foreach($iconsets as $name=>$iconset)
		{
			echo("<h3>".$name."</h3>");
			echo("<br>");
			echo("<div class='row'>");		
			foreach($iconset as $v)
			{
				echo("<div class='col-md-2 col-xs-3'>");		
				echo("<div class='choose_icon choose_icon_".$this->Get('timeline_item_for')."'>");		
				$js='';
				if($this->id)	
				{
					$js.="jQuery('#".$this->GetFieldName('timeline_item_fa_icon')."').removeClass();";
					$js.="jQuery('#".$this->GetFieldName('timeline_item_fa_icon')."').addClass('".$v." ".($this->Get('timeline_item_for')=='OTHER'?'agent_bg_color1':'agent_bg_color2')."');";
					$js.="jQuery('#".$this->GetFieldName('timeline_item_fa_class')."').val('".$v."');";
					$js.=$this->AgentCardJS(array('action'=>$this->GetFormAction('save')));
				}
				else
				{
					$js.="jQuery('#".$this->GetFieldName('timeline_item_fa_icon')."0').removeClass();";
					$js.="jQuery('#".$this->GetFieldName('timeline_item_fa_icon')."').addClass('".$v." ".($this->Get('timeline_item_for')=='OTHER'?'agent_bg_color1':'agent_bg_color2')."');";
					$js.="jQuery('#".$this->GetFieldName('timeline_item_fa_class')."0').val('".$v."');";
				}
				$js.="PopupClose();";
				echo("<i class='".$v."' onclick=\"".$js."return false;\"></i>");
				echo("</div>");
				echo("</div>");
			}
			echo("</div>");
			//echo("<hr>");
		}
	}

	public function CompletedDetails($params=array())
	{
	 	$d1=new DBDate($this->Get('timeline_item_date'));
	 	$d2=new date();
	 	$d2->SetTimestamp($this->Get('timeline_item_complete'));
		$activity_log=new activity_log($this->Get('activity_log_id'));

		if($d1->IsValid() and $this->Get('timeline_item_reference_date_type')!='NONE')
			echo("<div class='line'>Due ".$d1->GetDate('m/d/y')."</div>");			
		echo("<div class='line'>Completed On ".$d2->GetDate('m/d/y')."</div>");			
		echo("<div class='line'>Completed By ".$this->Get('timeline_item_completed_by')."</div>");			
		if($activity_log->Get('activity_log_ip') and $params['agent'])
			echo("<div class='line'>IP Address: <a href='".$activity_log->ToURL()."'>".$activity_log->Get('activity_log_ip')."</a></div>");			
		if($this->Get('activity_log_id') and $params['agent'])
			echo("<div class='line'><a href='".$activity_log->ToURL()."'>View Activity Log</a></div>");			

		echo("<div class='line'>");
		echo("<a class='button' href='#' onclick=\"PopupClose();return false;\">Close</a>");
		echo("</div>");

	}

	public function EditDetail($params=array())
	{
		$original=new timeline_item($this->Get('original_id'));
		if($params['action']==$this->GetFormAction('restore_content'))
		{
			$this->Set('timeline_item_content',$original->Get('timeline_item_content'));
			$this->Update();
		}


		$js2='';
		$savejs2="UpdateWYSIWYG();";
		$savejs2.="ObjectFunctionAjax('timeline_item','".$this->id."','AgentCard','".$this->GetFieldName('AgentCardContainer')."','".$this->GetFieldName('AgentCardFormPopup')."','','action=".$this->GetFormAction('save')."',function(){".$js2."});";

		form::Begin($this->GetFieldName('AgentCardContainer'),'POST',true,array('id'=>$this->GetFieldName('AgentCardFormPopup')));
		
		echo("<div class='line'>");		
		echo "<div class='timeline_item_content'>";
		$chjs="ObjectFunctionAjax('timeline_item','".$this->id."','EditDetail','popup_content','".$this->GetFieldName('AgentCardFormPopup')."','','detail_type='+jQuery(this).val(),function(){});";		
		html::ResumeOutput();
		form::DrawSelect('detail_type',array('Text'=>'','Link to File'=>'FILE','Link To a URL'=>'URL'),$params['detail_type'],array('onchange'=>$chjs));
		echo("</div>");	
		echo("</div>");			

		if($params['detail_type']=='')
		{
			echo("<div class='line'>");			
			echo "<div class='timeline_item_content'>";
			form::DrawTextArea($this->GetFieldName('timeline_item_content'),$this->Get('timeline_item_content'),array('class'=>'wysiwyg_input wysiwyg','onchange'=>$savejs2));
			$wysiwyg_info=wysiwyg::GetMode('LINK_BULLET_HEADLINES');
			$wysiwyg_info.="onchange_callback:function(){".$savejs2."},\r\n";
			wysiwyg::RegisterMode($this->GetFieldName('LINK_BULLET_HEADLINES'),$wysiwyg_info);
			form::MakeWYSIWYG($this->GetFieldName('timeline_item_content'),$this->GetFieldName('LINK_BULLET_HEADLINES'));				
			form::DrawHiddenInput($this->GetFieldName('timeline_item_url'),'');
			form::DrawHiddenInput($this->GetFieldName('timeline_item_file'),'');
			echo "</div>";
			echo("</div>");
		}
		if($params['detail_type']=='URL')
		{
			echo("<div class='line'>");			
			echo "<div class='timeline_item_content'>";
			form::DrawTextInput($this->GetFieldName('timeline_item_url'),$this->Get('timeline_item_url'),array('Placeholder'=>'http://','onchange'=>$savejs2));
			form::DrawHiddenInput($this->GetFieldName('timeline_item_content'),'',array('id'=>'NULL'));
			form::DrawHiddenInput($this->GetFieldName('timeline_item_file'),'');
			echo "</div>";
			echo("</div>");			
		}
		if($params['detail_type']=='FILE')
		{
			echo("<div class='line'>");			
			echo "<div class='timeline_item_content'>";
			form::DrawFileInput($this->GetFieldName('timeline_item_file_ul'),'',array('Placeholder'=>'Or Upload File','class'=>'file','onchange'=>$js1.$savejs2));
			form::DrawHiddenInput($this->GetFieldName('timeline_item_content'),'',array('id'=>'NULL'));
			form::DrawHiddenInput($this->GetFieldName('timeline_item_file'),'');
			echo "</div>";
			echo("</div>");			
		}				
		form::End();	
		
		if(($params['detail_type']=='') and $original->id and $original->Get('timeline_item_content')!=$this->Get('timeline_item_content'))
		{
			$js="ObjectFunctionAjax('timeline_item','".$this->id."','EditDetail','popup_content','NULL','','action=".$this->GetFormAction('restore_content')."&detail_type=".$params['detail_type']."',function(){});";		
			echo("<div class='line'>");
			echo("<a class='button' href='#' data-info='TIMELINE_CONTENT_RESTORE' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Restore'>Restore To Original Content <i class='fa fa-trash-restore'></i></a>");
			echo("</div>");
		}
		echo("<div class='line'>");
		echo("<a class='button' href='#' onclick=\"PopupClose();return false;\">Close</a>");
		echo("</div>");
	}

	public function x_EditSettings($params=array())
	{
		$js2='';
		$savejs2="UpdateWYSIWYG();";
		$savejs2.="ObjectFunctionAjax('timeline_item','".$this->id."','AgentCard','".$this->GetFieldName('AgentCardContainer')."','".$this->GetFieldName('AgentCardFormPopup')."','','action=".$this->GetFormAction('save')."',function(){".$js2."});";

		form::Begin($this->GetFieldName('AgentCardContainer'),'POST',true,array('id'=>$this->GetFieldName('AgentCardFormPopup')));

		echo("<div class='line timeline_item_reference_date_type' style='display:".(in_array($this->Get('timeline_item_reference_date_type'),array('EXACT','NONE'))?'none':'')."'>");
		$opts=array();
		for($i=0;$i<365;$i++)
			$opts[$i.' Days']=$i;
		form::DrawSelect($this->GetFieldName('timeline_item_reference_date_days'),$opts,$this->Get('timeline_item_reference_date_days'),array('onchange'=>$savejs2));
		echo("</div>");
		echo("<div class='line timeline_item_reference_date' style='display:".(in_array($this->Get('timeline_item_reference_date'),array('EXACT','NONE'))?'none':'')."'>");
		form::DrawSelect($this->GetFieldName('timeline_item_reference_date_before'),array('After'=>0,'Before'=>1),$this->Get('timeline_item_reference_date_before'),array('onchange'=>$savejs2,'class'=>'timeline_item_reference_date_reference','style'=>'display:'.($this->Get('timeline_item_reference_date')?'inline-block':'none')));
		echo("</div>");
		echo("<div class='line'>");
		$js="jQuery('.timeline_item_reference_date').css({display:'none'});";
		$js.="if(jQuery(this).val()=='EXACT'){jQuery('.timeline_item_reference_date_EXACT').css({display:''});}";
		$js.="else if(jQuery(this).val()=='NONE'){}";
		$js.="else{jQuery('.timeline_item_reference_date').css({display:''});jQuery('.timeline_item_reference_date_EXACT').css({display:'none'});}";
		form::DrawSelect($this->GetFieldName('timeline_item_reference_date'),$this->GetReferenceDates(),$this->Get('timeline_item_reference_date'),array('onchange'=>$js.$savejs2));
		echo("</div>");
		echo("<div class='line timeline_item_reference_date timeline_item_reference_date_EXACT' style='display:".(in_array($this->Get('timeline_item_reference_date'),array('EXACT'))?'':'none')."'>");
		$d=new date();
		if($this->Get('timeline_item_reference_date'))
			$d=new DBDAte($this->Get('timeline_item_date'));			
		form::DrawTextInput($this->GetFieldName('timeline_item_date'),$d->GetDate('m/d/Y'),array('class'=>'text datepicker','onchange'=>$savejs2));	
		echo("</div>");			

		echo("<div class='row'>");
		echo("<div class='col-md-3'>");
		echo("<div class='line'>Depends</div>");
		echo("</div>");
		echo("<div class='col-md-9'>");
		echo("<div class='line'>");
		echo "Depends"; // You can replace this with your actual form or logic
		// For example, if you want a select box:
		// form::DrawSelect(
		//   $this->GetFieldName('depends_on_timeline_item_id'),
		//   $optionsArray,
		//   $this->Get('depends_on_timeline_item_id'),
		//   array('onchange'=>$savejs2)
		// );
		echo "</div>";
		echo("</div>");
		echo("</div>");

		form::End();		
	}

	public function DisplayFull($params=array())
	{
		$user_contact=new user_contact(Session::Get('user_contact_id'));

		$user=new user($this->Get('user_id'));
		$user_contract_date=new user_contract_date();
		$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($this->Get('timeline_item_reference_date'),$this->Get('user_id')));
		if($user_contract_date->Get('user_contract_date_na'))
			return;

	  	$user_conditions=new DBRowSetEX('user_conditions','user_condition_id','user_condition',"user_id='".$user->id."' AND user_condition_checked=1");
		$user_conditions->Retrieve();

	 	$user_conditions_condition_ids=array(-1);
		foreach($user_conditions->items as $user_condition)
		 	$user_conditions_condition_ids[]=$user_condition->Get('condition_id');
		 	
		$user_contract_date=new user_contract_date();
		if($user->Get('user_under_contract')) //must be a user and under contract to have a relevant contract date.
			$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($this->Get('timeline_item_reference_date'),$this->Get('user_id')));

	  	$conditions_to_timeline_items=new DBRowSetEX('conditions_to_timeline_items','condition_id','condition',"timeline_item_id='".$this->id."' AND condition_id IN(".implode(',',$user_conditions_condition_ids).")");
		if($user->Get('user_under_contract'))
			$conditions_to_timeline_items->Retrieve();

		if($this->IsNotApplicable())
			return;

		$this->ProcessAction();

		if($params['which'])		
			$this->SetFlag('WHICH',$params['which']);
		if($params['agent'])		
			$this->SetFlag('AGENT',$params['agent']);
		if($params['multiview'])		
			$this->SetFlag('MULTIVIEW',$params['multiview']);

		echo("<a name='".$this->GetFieldName('anchor')."'></a>");
		if($this->Get('timeline_item_anchor'))
			echo("<a name='".$this->Get('timeline_item_anchor')."'></a>");
		
		if($this->Get('timeline_item_type')=='CONTENT')
		{
			echo("<div class='timeline_item timeline_milestone'>");
			echo("<div class='client_intro_text'>".$this->Get('timeline_item_summary')."</div>");
			echo("</div>");
		}
		else if($this->Get('timeline_item_type')=='TIMELINE')
		{
			$user=new user($this->Get('user_id'));
			$agent=new agent($user->Get('agent_id'));
			$date=new DBDAte($this->Get('timeline_item_date'));	
			$depends_on=new timeline_item($this->Get('depends_on_timeline_item_id'));

			$bgclass='';
			$textclass='';
			if($this->Get('timeline_item_for')=='AGENT')
			{
				$bgclass='agent_bg_color1';
				$borderclass='agent_border_color1';
				$borderrightclass='agent_border-r_color1';
				$textclass='agent_color1';
			}
			if($this->Get('timeline_item_for')=='OTHER')
			{
				$bgclass='agent_bg_color1';
				$borderclass='agent_border_color1';
				$borderrightclass='agent_border-r_color1';
				$textclass='agent_color1';
			}
			if($this->Get('timeline_item_for')=='USER')
			{
				$bgclass='agent_bg_color2';
				$borderclass='agent_border_color2';
				$borderrightclass='agent_border-r_color2';
				$textclass='agent_color2';
			}

			$class=array();
			//$class[]="timeline_item_".$this->Get('timeline_item_for');
			if($this->Get('timeline_item_complete'))			
			{
				$class[]="timeline_item_COMPLETED";
				$bgclass='';
				$borderclass='';
				$borderrightclass='';
				$textclass='';
			}
			if(!$this->Get('timeline_item_active'))			
			{
				$class[]="timeline_item_DELETED";
				$bgclass='';
				$borderclass='';
				$borderrightclass='';
				$textclass='';
			}
			if($this->DependsOnIncompleteItem())
			{
				$class[]="timeline_item_HIDDEN";
				$bgclass='';
				$borderclass='';
				$borderrightclass='';
				$textclass='';
			}
			
			echo "<div class='timeline_item ".implode(' ',$class)."' id='".$this->GetFieldName('timeline_item')."'>";
			if(!$this->Get('timeline_item_active'))	
				echo("<h1>Deleted From Timeline</h1>");
			else if($this->Get('timeline_item_complete'))			
				echo("<h1>Complete</h1>");
			else if($this->DependsOnIncompleteItem())
			 	echo("<h3 class='".$textclass."'>Waiting for ".$depends_on->Get('timeline_item_title')." to be marked as complete</h3>");
			else if($user->Get('user_under_contract') and $date->IsValid() and ($this->Get('timeline_item_reference_date_type')!=='NONE'))// and $user->Get('user_under_contract'))
				echo("<h1 class='".$textclass."'>".$date->GetDate('l, F j')."</h1>");
			else if(!$user->Get('user_under_contract') and $depends_on->Get('timeline_item_complete'))
				$null;
			else if($this->Get('timeline_item_reference_date_type')!=='NONE')
				echo("<h1 class='".$textclass."'>".$this->GetUserTiming()."</h1>");
			if($this->GetFlag('MULTIVIEW'))				
				echo("<h3>".$user->GetFullName()."</h3>");
			$js="jQuery('#".$this->GetFieldName('timeline_item')."').toggleClass('timeline_item_expanded');";
			echo "<div class='timeline_item_heading ".$bgclass."' onclick=\"".$js."\">";
			echo "<div class='arrow-left ".$bgclass."'></div>";

			$icon=$this->Get('timeline_item_fa_class');
			if($this->Get('timeline_item_complete'))			
				$icon='fa fa-check';
			echo "<div class='timeline_item_icon'><i class='".$icon." ".$bgclass."'></i></div>";
			echo "<h3>".$this->Get('timeline_item_title')."</h3>";
			if(($this->GetFlag('WHICH')==$this->Get('timeline_item_for')) or $this->GetFlag('AGENT'))
			{
				if($user->Get('user_active') and ($user->Get('user_under_contract') or ($this->Get('timeline_item_reference_date_type')=='NONE')))
				{
					echo("<div class='timeline_item_checkbox'>");
					//$js2="jQuery('#".$this->GetFieldName('timeline_item')."').toggleClass('timeline_item_COMPLETED');";
					//if($this->GetFlag('WHICH')=='USER' and !$this->Get('timeline_item_complete'))
//					if(!$this->Get('timeline_item_complete'))
//						$js3="SetOneTimeTimeout('progress',function(){ObjectFunctionAjaxPopup('Progress','user','".$this->Get('user_id')."','DisplayProgressPopup','NULL','','',function(){window.setTimeout(function(){PopupClose();},3000);})},progress_timeout_length);";
					$js4="";
					if(!$this->Get('timeline_item_complete'))
						$js4="ExpandMobileProgressMeter();";
					$js3="ObjectFunctionAjax('agent','".$this->id."','ProgressMeter','progress_meter_container','NULL','noloading','user_id=".$this->Get('user_id')."',function(){});";
					$js3.="ObjectFunctionAjax('user','".$this->Get('user_id')."','ProgressMeterMobile','progress_meter_container_mobile','NULL','noloading','user_id=".$this->Get('user_id')."',function(){".$js4."});";
					$js2="ObjectFunctionAjax('user','".$this->Get('user_id')."','DisplayDashboard','dashboard_container','NULL','','',function(){".$js3."height_handler();});";
					$js="ObjectFunctionAjax('timeline_item','".$this->id."','DisplayFull','".$this->GetFieldName('AgentCardContainer')."','".$this->GetFieldName('timeline_item_complete_form')."','noloading','agent=".$this->GetFlag('AGENT')."&which=".$this->GetFlag('WHICH')."&multiview=".$this->GetFlag('MULTIVIEW')."&action=".$this->GetFormAction('save')."',function(){".$js2."});";
					form::Begin('','POST',false,array('id'=>$this->GetFieldName('timeline_item_complete_form')));

					$checkbboxparams=array();
					if(($this->Get('timeline_item_behavior')=='CONGRATULATIONS') and !$this->Get('timeline_item_complete'))
						$js.="CongratulationsFlare(jQuery('#".$this->GetFieldName('timeline_item_complete')."').get(0),function(){});";
					else if(($this->Get('timeline_item_behavior')=='ARCHIVE') and !$this->Get('timeline_item_complete'))
					{
						$js.="CongratulationsFlare(jQuery('#".$this->GetFieldName('timeline_item_complete')."').get(0),function(){});";
						$js3="window.setTimeout(function(){document.location='".$this->GetCurrentUser()->DirectURL('past.php')."';},2500);";
						$js2="ObjectFunctionAjax('agent','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=".$user->GetFormAction('archive_transaction')."',function(){".$js3."});return false;";
					}
					else
						$checkbboxparams['class']='has-flare';
					$checkbboxparams['onclick']=$js;
					if(!$this->Get('timeline_item_complete'))
						$checkbboxparams['onclick'].="ShowFlare(this);";//stop propagation prevents this from firing
					$checkbboxparams['onclick'].="event.stopPropagation();window.event.cancelBubble = true;";//why do we need to stop propagaitn???

					form::DrawCheckbox($this->GetFieldName('timeline_item_complete'),time(),$this->Get('timeline_item_complete'),$checkbboxparams);
					form::DrawHiddenInput($this->GetFieldName('timeline_item_completed_by'),$this->GetCurrentUser(true)->GetFullName(),array('class'=>'has-flare','onclick'=>$js.$js2));
					form::DrawHiddenInput($this->GetFieldName('timeline_item_completed_class'),get_class($this->GetCurrentUser(true)),array('class'=>'has-flare','onclick'=>$js.$js2));
					form::DrawHiddenInput($this->GetFieldName('timeline_item_completed_id'),$this->GetCurrentUser(true)->id,array('class'=>'has-flare','onclick'=>$js.$js2));
					form::End();
					echo "</div>";
				}
			}
			if($this->Get('timeline_item_complete'))
			{
				echo("<div class='timeline_item_complete_details'>");
				$js="ObjectFunctionAjaxPopup('".$this->Get('timeline_item_title')."','timeline_item','".$this->id."','CompletedDetails','NULL','','agent=0',function(){});";
				echo("<a href='#' data-info='TIMELINE_EDIT' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Details'><i class='fa fa-circle-info'></i></a>");
				echo("</div>");
			}
			echo "</div>";
			$this->DisplayInner();
			echo "</div>";
			$this->DisplayCustomNotice();
			echo "</div>";		
		}
	}

	public function DisplayInner($params=array())
	{
		$user=new user($this->Get('user_id'));
		$agent=new agent($user->Get('agent_id'));
		$date=new DBDAte($this->Get('timeline_item_date'));	

		$bgclass='';
		$textclass='';
		if($this->Get('timeline_item_for')=='AGENT')
		{
			$bgclass='agent_bg_color1';
			$borderclass='agent_border_color1';
			$borderrightclass='agent_border-r_color1';
			$textclass='agent_color1';
		}
		if($this->Get('timeline_item_for')=='OTHER')
		{
			$bgclass='agent_bg_color1';
			$borderclass='agent_border_color1';
			$borderrightclass='agent_border-r_color1';
			$textclass='agent_color1';
		}
		if($this->Get('timeline_item_for')=='USER')
		{
			$bgclass='agent_bg_color2';
			$borderclass='agent_border_color2';
			$borderrightclass='agent_border-r_color2';
			$textclass='agent_color2';
		}

		echo "<div class='timeline_item_body' onclick=\"".$this->GetLearnMoreJS($params)."\" id='".$this->GetFieldName('timeline_item_body')."'>";
		if($this->Get('timeline_item_for')=='AGENT')
			echo "<div class='timeline_item_image'><img onclick=\"event.stopPropagation();window.event.cancelBubble = true;return false;\" class='".$bgclass."' alt='".($this->Get('timeline_item_image_alt')?$this->Get('timeline_item_image_alt'):$this->Get('timeline_item_title'))."' src='/images/agent-only-image-timeline.png'></div>";
		else if($this->Get('timeline_item_image_file'))
			echo "<div class='timeline_item_image'><img onclick=\"event.stopPropagation();window.event.cancelBubble = true;return false;\" class='".$bgclass."' alt='".($this->Get('timeline_item_image_alt')?$this->Get('timeline_item_image_alt'):$this->Get('timeline_item_title'))."' src='".$this->GetThumb(160*3,107*3)."'></div>";
		echo "<div class='timeline_item_content'>";
		echo($this->Get('timeline_item_summary'));
		if($this->Get('timeline_item_url'))
			echo "<div class='timeline_item_link'><a class='button' target='_blank' href='".$this->Get('timeline_item_url')."'>Learn More</a></div>";		
		else if($this->Get('timeline_item_file'))
			echo "<div class='timeline_item_link'><a class='button' target='_blank' href='".file::GetPath('timeline_item_display').$this->Get('timeline_item_file')."'>Learn More</a></div>";		
		else if(trim(strip_tags($this->Get('timeline_item_content'),'<img><iframe>')))
			echo "<div class='timeline_item_link'><a class='button' href='#' onclick=\"ObjectFunctionAjaxPopup('".$this->Get('timeline_item_title')."','timeline_item','".$this->id."','DisplayDetail','NULL','','editable=".$params['editable']."',function(){},'timeline_item_detail');return false;\">Learn More</a></div>";
		echo "</div>";
	}

	public function GetLearnMoreJS($params=array())
	{
		$clickjs='';

		if($this->Get('timeline_item_complete'))			
			return '';
		if(!$this->Get('timeline_item_active'))			
			return '';
		if($this->DependsOnIncompleteItem())
			return '';

		if($this->Get('timeline_item_url'))
			$clickjs="window.open('".$this->Get('timeline_item_url')."', '_blank').focus();";
		else if($this->Get('timeline_item_file'))
			$clickjs="window.open('".file::GetPath('timeline_item_display').$this->Get('timeline_item_file')."', '_blank').focus();";
		else if(trim(strip_tags($this->Get('timeline_item_content'),'<img><iframe>')))
			$clickjs="ObjectFunctionAjaxPopup('".$this->Get('timeline_item_title')."','timeline_item','".$this->id."','DisplayDetail','NULL','','editable=".$params['editable']."',function(){},'timeline_item_detail');";
		return $clickjs;
	}

	public function AgentLinks($params=array())
	{
	 	$agent=new agent($this->Get('agent_id'));	 
	 	$user=new user($this->Get('user_id'));	 

		$user_contract_date=new user_contract_date();
		if($user->Get('user_under_contract'))
			$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($this->Get('timeline_item_reference_date'),$this->Get('user_id')));

	  	$user_conditions=new DBRowSetEX('user_conditions','user_condition_id','user_condition',"user_id='".$this->Get('user_id')."' AND user_condition_checked=1");
		if($user->Get('user_under_contract'))
			$user_conditions->Retrieve();

	 	$user_conditions_condition_ids=array(-1);
		foreach($user_conditions->items as $user_condition)
		 	$user_conditions_condition_ids[]=$user_condition->Get('condition_id');
		 	
		$user_contract_date=new user_contract_date();
		if($user->Get('user_under_contract')) //must be a user and under contract to have a relevant contract date.
			$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($this->Get('timeline_item_reference_date'),$this->Get('user_id')));

	  	$conditions_to_timeline_items=new DBRowSetEX('conditions_to_timeline_items','condition_id','condition',"timeline_item_id='".$this->id."' AND condition_id IN(".implode(',',$user_conditions_condition_ids).")");
		if($user->Get('user_under_contract'))
			$conditions_to_timeline_items->Retrieve();

		$dependantjs='';
		$dependant_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',"depends_on_timeline_item_id='".$this->id."'",'timeline_item_order');
		$dependant_items->Retrieve();
		foreach($dependant_items->items as $dependant)
			$dependantjs.=$dependant->AgentCardJS(array(),$dependant->GetFieldName('AgentCardContainer'));

		echo("<div class='timeline_agent_edit'>");
		if(!$this->id)
		{
			$js="UpdateWYSIWYG();";
			$js.="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('save')."&parent_action=1');";
			echo("<a href='#' data-info='TIMELINE_ADD' data-info-none='none' onclick=\"".$js."return false;\"><i class='fa fa-plus'></i></a>");
		}
		else if($user_contract_date->Get('user_contract_date_na'))
		{
		 	$null;
		}
		else if(count($conditions_to_timeline_items->items))
		{
		 	$null;
		}
		else if(!$this->Get('timeline_item_active'))
		{
			//$js=$this->AgentCardJS(array('action'=>$this->GetFormAction('undelete')),$this->GetFieldName('AgentCardContainer'),$dependantjs);
			$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','include_timeline_item_id=".$this->id."&template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('undelete')."&parent_action=1',function(){".$dependantjs."height_handler();});";
			echo("<a href='#' class='timeline_item_restore' data-info='TIMELINE_RESTORE' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Restore'><i class='fa fa-trash-restore'></i></a>");
			if(!$this->Get('original_id'))
			{
				$js="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('permanentdelete')."&parent_action=1',function(){jQuery('#".$this->GetFieldName('AgentCardContainer')."').animate({opacity:1});".$dependantjs."height_handler();});";				
				$js="jQuery('#".$this->GetFieldName('AgentCardContainer')."').animate({opacity:0.2},2000);DeleteFlare(jQuery('#".$this->GetFieldName('AgentCardPermanentDelete')."').get(0),function(){".$js."});";
				//PREFERRABLY.... just act on this card and then hide it.... DOM does not seem to like that though; somethign going on there...
				echo("<a id='".$this->GetFieldName('AgentCardPermanentDelete')."' class='flare-action' href='#' data-info='TIMELINE_PERMANENTDELETE' data-info-none='none' onclick=\"if(confirm('PERMANENTLY Delete This Item? This action cannot be reversed.')){".$js."}return false;\" data-toggle='tooltip' title='Permanently Delete'><i class='fa fa-dumpster'></i></a>");
			}
		}
		else 
		{
			if($this->IsEditing($params))
			{
				$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','action=".$this->GetFormAction('save')."&template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&save_timeline_item_id=".$this->id."&parent_action=1');";
				//$js=$this->AgentCardJS(array("save_timeline_item_id"=>$this->id),$this->GetFieldName('AgentCardContainer'));
				echo("<a href='#' data-info='TIMELINE_EDIT' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Done Editing This Item'><i class='fa fa-check'></i></a>");
			}
			else if(!$this->Get('timeline_item_complete') and (!$this->Get('timeline_item_not_editable') or !$this->Get('agent_id')))// and !$this->DependsOnIncompleteItem())
			{
				$js=$this->AgentCardJS(array("edit_timeline_item_id"=>$this->id),$this->GetFieldName('AgentCardContainer'));
				echo("<a href='#' data-info='TIMELINE_EDIT' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Edit This Item'><i class='fa fa-pencil-alt'></i></a>");
			}

			if($this->Get('timeline_item_complete'))
			{
				$js="ObjectFunctionAjaxPopup('".$this->Get('timeline_item_title')."','timeline_item','".$this->id."','CompletedDetails','NULL','','agent=1',function(){});";
				echo("<a href='#' data-info='TIMELINE_EDIT' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Details'><i class='fa fa-circle-info'></i></a>");
			}


			if(!$user->Get('user_under_contract') and !$this->Get('timeline_item_complete'))
			{
				$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('sort_up')."&parent_action=1');";
				echo("<a href='#' data-info='TIMELINE_SORT_UP' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Move Up'><i class='fa fa-arrow-up'></i></a>");
				$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('sort_down')."&parent_action=1');";
				echo("<a href='#' data-info='TIMELINE_SORT_DOWN' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Move Down'><i class='fa fa-arrow-down'></i></a>");
			}
			if(!$this->Get('timeline_item_complete'))
			{
				//$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('add_after')."&parent_action=1');";
				$js="ObjectFunctionAjaxPopup('Create New Item Or Copy Existing','timeline_item','".$this->id."','AddNewTimelineItemPopup','NULL','','timeline_item_id=".$this->id."');";
				echo("<a href='#' data-info='TIMELINE_ADD_ITEM' data-info-none='none' onclick=\"".$js."return false;\" data-toggle='tooltip' title='Add a New Timeline Item Below.'><i class='fa fa-plus'></i></a>");
			}
			if(!$this->Get('timeline_item_complete') and (!$this->Get('timeline_item_not_deletable') or !$this->Get('agent_id')))
			{
				$js=$this->AgentCardJS(array('action'=>$this->GetFormAction('delete')),$this->GetFieldName('AgentCardContainer'),"jQuery('#".$this->GetFieldName('AgentCardContainer')."').animate({opacity:1});".$dependantjs);
				$js="jQuery('#".$this->GetFieldName('AgentCardContainer')."').animate({opacity:0.2},2000);DeleteFlare(jQuery('#".$this->GetFieldName('AgentCardDelete')."').get(0),function(){".$js."});";
				echo("<a id='".$this->GetFieldName('AgentCardDelete')."'  class='flare-action' href='#' data-info='TIMELINE_DELETE' data-info-none='none' onclick=\"if(confirm('Remove This Item From Timeline? You can always restore this item if you change your mind later.')){".$js."}return false;\" data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></a>");
			}
			//if($this->Get('original_id'))
			if($this->IsModified() and !$this->Get('timeline_item_complete'))
			{
				$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('restore')."&parent_action=1');";
				echo("<a href='#' data-info='TIMELINE_RESTORE_TO_DEFAULT' data-info-none='none' onclick=\"if(confirm('Restore To Default?')){".$js."}return false;\" data-toggle='tooltip' title='Restore Item to Default Values'><i class='fa fa-sync'></i></a>");
			}

			//setup the compelted info
			$js1="jQuery('#".$this->GetFieldName('timeline_item_complete')."').val(".($this->Get('timeline_item_complete')?0:time()).");";
			$js1.="jQuery('#".$this->GetFieldName('timeline_item_completed_by')."').val('".$this->GetCurrentUser(true)->GetFullName()."');";
			$js1.="jQuery('#".$this->GetFieldName('timeline_item_completed_class')."').val('".get_class($this->GetCurrentUser(true))."');";
			$js1.="jQuery('#".$this->GetFieldName('timeline_item_completed_id')."').val('".$this->GetCurrentUser(true)->id."');";
			$js2="";
			$icon='fa-square';
			$title='Mark Complete';
			//$js3="SetOneTimeTimeout('progress',function(){ObjectFunctionAjaxPopup('Progress','user','".$this->Get('user_id')."','DisplayProgressPopup','NULL','','',function(){window.setTimeout(function(){PopupClose();},3000);})},progress_timeout_length);";
			$js3="ObjectFunctionAjax('agent','".$this->id."','ProgressMeter','progress_meter_container','NULL','noloading','user_id=".$this->Get('user_id')."',function(){});";
			$js3.="ObjectFunctionAjax('user','".$this->Get('user_id')."','ProgressMeterMobile','progress_meter_container_mobile','NULL','noloading','user_id=".$this->Get('user_id')."',function(){ExpandMobileProgressMeter()});";

			$compeltedjs=$this->AgentCardJS(array('action'=>$this->GetFormAction('save')),$this->GetFieldName('AgentCardContainer'),$js3,'noloading');//,$dependantjs);

			//$class='flare-action';
			$class='has-flare';
			if(($this->Get('timeline_item_behavior')=='CONGRATULATIONS') and !$this->Get('timeline_item_complete'))
			{
				$compeltedjs.="CongratulationsFlare(jQuery('#".$this->GetFieldName('timeline_item_complete')."').get(0),function(){".$js3."});";
				$class='';
			}
			else if(($this->Get('timeline_item_behavior')=='ARCHIVE') and !$this->Get('timeline_item_complete') and $this->Get('user_id'))
			{
				$class='';
				$compeltedjs.="CongratulationsFlare(jQuery('#".$this->GetFieldName('timeline_item_complete')."').get(0),function(){".$js3."});";
				$js3="window.setTimeout(function(){document.location='".$this->GetCurrentUser()->DirectURL('past.php')."';},2500);";
				$js2="ObjectFunctionAjax('agent','".$this->id."','AgentTools','agent_tools_container','NULL','','user_id=".$params['user_id']."&action=".$user->GetFormAction('archive_transaction')."',function(){".$js3."});return false;";
			}

			if($this->Get('timeline_item_complete'))
			{
				$icon='fa-check-square';
				$title='Mark Incomplete';
				$class='';
				$compeltedjs="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditTimeline','timeline_container','".$this->GetFieldName('AgentCardForm')."','','include_timeline_item_id=".$this->id."&template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('save')."&parent_action=1',function(){".$dependantjs.$js3."});";
			}
			else if(count($dependant_items->items))
			{			
				$compeltedjs="ObjectFunctionAjaxPopup('".$this->Get('timeline_item_title')."','timeline_item','".$this->id."','ConfirmItemCompletePopup','NULL','','');";
				$class='';
			}

			$depends_on=new timeline_item($this->Get('depends_on_timeline_item_id'));
			if($this->DependsOnIncompleteItem())
				$null;				
			else if($this->Get('user_id') and (($this->Get('timeline_item_type')!=='CONTENT') and ($user->Get('user_under_contract')) or ($this->Get('timeline_item_reference_date_type')=='NONE')) or $this->DependsOnCompleteItem())
			{
				echo("<a href='#' class='".$class." timeline_item_checkbox' data-info='TIMELINE_COMPLETE' data-info-none='none' onclick=\"".$js1.$compeltedjs.$js2.";return false;\" data-toggle='tooltip' title='".$title."'><i id='".$this->GetFieldNamE('flare-action')."' class='".$class." fa ".$icon."'></i></a>");
				form::DrawHiddenInput($this->GetFieldName('timeline_item_complete'),$this->Get('timeline_item_complete'));
				form::DrawHiddenInput($this->GetFieldName('timeline_item_completed_by'),$this->Get('timeline_item_completed_by'));
				form::DrawHiddenInput($this->GetFieldName('timeline_item_completed_class'),$this->Get('timeline_item_completed_class'),array('onclick'=>$js.$js2));
				form::DrawHiddenInput($this->GetFieldName('timeline_item_completed_id'),$this->Get('timeline_item_completed_id'),array('onclick'=>$js.$js2));
			}
		}
		echo("</div>");
		
	}

	public function AddNewTimelineItemPopup($params=array())
	{
		$timeline_item=new timeline_item($params['timeline_item_id']);

		form::Begin($this->GetFieldName('AddNewTimelineItemPopupForm'),'POST',true,array('id'=>$this->GetFieldName('AddNewTimelineItemPopupForm')));		
		echo("<div class='line'>");		
		echo("<h3>Create New Item Or Copy Existing</h3>");
		echo("</div>");
		echo("<div class='line'>");		
		echo "<div class='timeline_item_content'>";
		form::DrawSelectFromSQL('copy_timeline_item_id',"SELECT * FROM timeline_items WHERE agent_id='".$this->Get('agent_id')."' AND user_id='".$this->Get('user_id')."' AND template_id='".$this->Get('template_id')."' AND timeline_item_active=1",'timeline_item_title','timeline_item_id','',array(),array('-- Create New Item --'=>0,'-- General Update --'=>'UPDATE'));
		echo("</div>");
		echo("</div>");			
		form::End();	
		
		$js="ObjectFunctionAjax('agent','".$this->Get('agent_id')."','EditTimeline','timeline_container','".$this->GetFieldName('AddNewTimelineItemPopupForm')."','','template_id=".$this->Get('template_id')."&agent_id=".$this->Get('agent_id')."&user_id=".$this->Get('user_id')."&agent=1&action=".$this->GetFormAction('add_after')."&parent_action=1',function(){PopupClose();});";
		echo("<div class='line'>");
		echo("<a class='button' href='#' onclick=\"".$js."return false;\">Insert</a>");
		echo("</div>");		
	}
	
	public function ConfirmItemCompletePopup($params=array())
	{
		global $HTTP_POST_VARS;

		$agent=new agent($this->Get('agent_id'));
		$dependant_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',"depends_on_timeline_item_id='".$this->id."'",'timeline_item_order');
		$dependant_items->Retrieve();

		if($params['action']==$this->GetFormAction('complete'))
		{
			$this->Set('timeline_item_complete',time());
			$this->Set('timeline_item_completed_by',$this->GetCurrentUser(true)->GetFullName());
			$this->Set('timeline_item_completed_class',get_class($this->GetCurrentUser(true)));
			$this->Set('timeline_item_completed_id',$this->GetCurrentUser(true)->id);
			$this->Update();

			$dependant_items->SetFlag('NL2BR');
			$dependant_items->Each('Save');
			
			if($HTTP_POST_VARS[$this->GetFieldName('notifty_client')])
			{
			  	$list=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"user_id='".$this->Get('user_id')."'",'user_contact_name');
			  	$list->Retrieve();
				$timeline_item_ids=array();
				foreach($dependant_items->items as $dependant)
					$timeline_item_ids[]=$dependant->id;
		  		$list->Each('NotifyClient',array(array('timeline_item_ids'=>$timeline_item_ids)));				
			}
		}

		form::Begin($this->GetFieldName('ConfirmItemCompletePopupForm'),'POST',true,array('id'=>$this->GetFieldName('ConfirmItemCompletePopupForm')));		
		echo("<div class='line'>");		
		echo("<h3>".$this->Get('timeline_item_title')."</h3>");
		echo("</div>");
		$s=count($dependant_items->items)>1?'s':'';
		echo("<div class='line'>Please enter date".$s.", time".$s.", and any appropriate details below for the following item".$s." to mark ".$this->Get('timeline_item_title')." as complete</div>");		
		foreach($dependant_items->items as $dependant)
		{
			echo("<div class='line'><b>".$dependant->Get('timeline_item_title')."</b></div>");
			echo("<div class='line'>");		
			echo("<div class='row'>");		
			echo("<div class='col-md-3'>Date:</div>");		
			echo("<div class='col-md-9'>");		
//			$d=new DBDate($dependant->Get('timeline_item_date'));
//			$d=new DBDate($this->Get('timeline_item_date'));
			$d=new Date();
			form::DrawTextInput($dependant->GetFieldName('timeline_item_date'),$d->GetDate('m/d/Y'),array('class'=>'text datepicker'));
			echo("</div>");
			echo("</div>");
			echo("</div>");
			echo("<div class='line'>");		
			echo("<div class='row'>");		
			echo("<div class='col-md-3'>Details:</div>");		
			echo("<div class='col-md-9'>");		
			form::DrawTextArea($dependant->GetFieldName('timeline_item_summary'),'',array('class'=>'wysiwyg_input wysiwyg_timeline','placeholder'=>html_entity_decode(strip_tags(br2nl($dependant->Get('timeline_item_summary'))))));
			//$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
			//$wysiwyg_info.="onchange_callback:function(){UpdateWYSIWYG('#popup_content');},\r\n";
			//wysiwyg::RegisterMode($dependant->GetFieldName('SIMPLE_LINK_HEADLINES'),$wysiwyg_info);
			//form::DrawTextArea($dependant->GetFieldName('timeline_item_summary'),$dependant->Get('timeline_item_summary'),array('class'=>'wysiwyg_input wysiwyg_timeline','placeholder'=>'Enter time and details here'));
			//form::MakeWYSIWYG($dependant->GetFieldName('timeline_item_summary'),$dependant->GetFieldName('SIMPLE_LINK_HEADLINES'));
			echo "</div>";
			echo("</div>");			
			echo "</div>";
			echo "</div>";
		}
		echo("<div class='line'>");		
		echo("<div class='row'>");		
		echo("<div class='col-md-3'><br></div>");		
		echo("<div class='col-md-9'>");		
		echo("<label>");
		form::DrawCheckbox($this->GetFieldName('notifty_client'),true,false);					
		echo(" Notify Client of This Update?");		
		echo("</label>");		
		echo "</div>";
		echo "</div>";
		echo "</div>";


		form::End();	
		
		$js2="PopupClose();";
		$js2.="ShowFlare(jQuery('#".$this->GetFieldNamE('flare-action')."').get(0),function(){});";
		$js2.="ObjectFunctionAjax('".get_class($this->GetCurrentUser())."','".$this->GetCurrentUser()->id."','EditTimeline','timeline_container','null','','user_id=".$this->Get('user_id')."',function(){height_handler();});";
		$js="ObjectFunctionAjax('timeline_item','".$this->id."','ConfirmItemCompletePopup','popup_content','".$this->GetFieldName('ConfirmItemCompletePopupForm')."','','action=".$this->GetFormAction('complete')."',function(){".$js2."});";
		echo("<div class='line'>");
		echo("<a class='button' href='#' onclick=\"".$js."return false;\">Post Update</a>");
		echo("</div>");		
		echo("<div class='line'>");
		echo("<a class='button' href='#' onclick=\"PopupClose();return false;\">Cancel</a>");
		echo("</div>");		
	}	
	

	public function IsModified()
	{
	 	if(!$this->id)
	 		return false;
	 	if(!$this->Get('original_id'))
	 		return false;
	 
 	 	$ignore=array('timeline_item_id','user_id','coordinator_id','agent_id','template_id','original_id','timeline_item_order','timeline_item_active','timeline_item_date','timeline_item_date_moved','timeline_item_complete','timeline_item_completed_by','timeline_item_completed_class','timeline_item_completed_id','timeline_item_notified','timeline_item_modified','timeline_item_modified_by','depends_on_timeline_item_id','activity_log_id');
		$original=new timeline_item($this->Get('original_id'));
		if(!$original->Get('timeline_item_active'))
			return false;
		foreach($original->attributes as $k=>$v)
		{
			if(!in_array($k,$ignore) and html_entity_decode($v)!=html_entity_decode($this->Get($k)))	
			{
				//echo($k);
				return true;
			}
		}
 	 	return false;
	}

	public function IsCustom()
	{
		if(!$this->Get('coordinator_id') and !$this->Get('agent_id') and !$this->Get('user_id'))
			return false;
		if(!$this->Get('original_id'))
			return true;
		if($this->IsModified())
			return true;
 	 	return false;
	}

	public function DisplayCustomNotice()
	{
		if($this->IsCustom())
		{
			$d=new date();
			$d->SetTimestamp($this->Get('timeline_item_modified'));
			if(!$this->Get('timeline_item_modified_by'))
				$this->Set('timeline_item_modified_by','Agent');
			echo("<div class='timeline_item_custom_notice'>Item customized by ".$this->Get('timeline_item_modified_by')." on ".$d->GetDate('m/d/Y')."</div>");
		}
	}

	public function PopupEdit($params)
	{
		$this->ProcessAction();
		if($this->saved)
		{
			echo("<div class='message success'>Your Changes Have Been Saved</div>");
			return;
		}
		else if(count($this->errors))
			echo("<div class='error'>".implode('<br>',$this->errors)."</div>");
		
		$this->Set('template_id',$params['template_id']);
		$this->Set('agent_id',$params['agent_id']);
		$this->Set('user_id',$params['user_id']);
		
		form::Begin('?action=','POST',false,array('id'=>$this->GetFieldName('save')));
		form::DrawHiddenInput($this->GetFieldName('template_id'),$this->Get('template_id'));
		form::DrawHiddenInput($this->GetFieldName('agent_id'),$this->Get('agent_id'));
		form::DrawHiddenInput($this->GetFieldName('user_id'),$this->Get('user_id'));
		echo("<table class='listing'>");
		echo("<tr><td class='edit_wrapper'>");
		echo("<table class='edit_wrapper'><tr>");
		$this->PreserveInputs();
		$this->EditForm();
		$js3="ObjectFunctionAjax('agent','".$params['agent_id']."','EditTimeline','timeline_container','NULL','','template_id=".$params['template_id']."&agent_id=".$params['agent_id']."&user_id=".$params['user_id']."&agent=1"."');";
		$js2="if(jQuery('#popup_content .success').length){".$js3."PopupClose();};";
		$js="ObjectFunctionAjax('timeline_item','".$this->Get('timeline_item_id')."','PopupEdit','popup_content','".$this->GetFieldName('save')."','','action=".$this->GetFormAction('save')."',function(){".$js2."});";
		//have to force tiny mce to update text areas;
		$js0="UpdateWYSIWYG();";
		form::DrawButton('','Save',array("onclick"=>$js0.$js));
		echo("</tr></table>");		
		echo("</td></tr></table>");				
		form::End();
	}

	public function DisplayDetail($params=array())
	{
		$wysiwyg_info=wysiwyg::GetMode('SIMPLE_LINK_HEADLINES');
		$wysiwyg_info.="onchange_callback:function(){".$savejs2."},\r\n";
		wysiwyg::RegisterMode($this->GetFieldName('SIMPLE_LINK_HEADLINES'),$wysiwyg_info);
		html::HoldOutput();
		form::MakeWYSIWYG($this->GetFieldName('timeline_item_content'),$this->GetFieldName('SIMPLE_LINK_HEADLINES'));				
		//$data=strip_tags(html::GetHeldOutput());
		html::ResumeOutput();
		$js="ObjectFunctionAjaxPopup('".$this->Get('timeline_item_title')."','timeline_item','".$this->id."','EditDetail','NULL','','detail_type=".$detail_type."',function(){".$data."},'timeline_item_detail');";
		//fire twice to get tinemce to resize?
		$js="ObjectFunctionAjax('timeline_item','".$this->id."','EditDetail','popup_content','NULL','','detail_type=".$detail_type."',function(){".$js."},'timeline_item_detail');";
		

		echo "<div class='timeline_item_content'>".$this->Get('timeline_item_content')."</div>";
		
		echo("<div class='detail_popup_close'>");
		if($params['editable'] and (!$this->Get('timeline_item_not_editable') or !$this->Get('agent_id')))
		{
			echo("<a data-info='TIMELINE_MORE_INFO' data-info-none='none' class='button ".$class."' href='#' onclick=\"".$js."return false;\"><i class='fas fa-pencil-alt'></i> Make Changes</a>");
			echo("&nbsp;");
		}
		echo("<a class='button' onclick=\"PopupClose();\">Close</a>");
		echo("</div>");
	}

 	/**THUMBNAILING**/
	public function GetThumb($width,$height,$crop=false)
 	{ 	  
		if($this->id)
		{	  
//			$src=$this->CropAsSaved(file::GetPath('timeline_item_display'),file::GetPath('timeline_item_upload'),'timeline_item_file',$width,$height);
			$src=$this->Get('timeline_item_image_file');
			return file::GetPath('timeline_item_display').imaging::ResizeCached($src,file::GetPath('timeline_item_upload'),$width,$height,$crop);
		}
		return '';
	}	
	
	public function CalculateDate()
	{
	 	//what I am relative to
		$user_contract_date=new user_contract_date();
		$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($this->Get('timeline_item_reference_date'),$this->Get('user_id')));
		$contract_date=new contract_date($user_contract_date->Get('contract_date_id'));
		$user=new user($this->Get('user_id'));
		$depends_on=new timeline_item($this->Get('depends_on_timeline_item_id'));

		//this date
		$d=new DBDAte($user_contract_date->Get('user_contract_date_date'));

		//has it been adjusted?  not yet...
		$this->Set('timeline_item_date_moved',0);


		//if I depend on asomeone and they are not complete then they are going to calcaulte my date.
		if($this->DependsOnIncompleteItem())
			return;
		else if($this->Get('timeline_item_reference_date_type')=='EXACT')
			$this->Set('timeline_item_date',$this->Get('timeline_item_date'));
		else if(!$d->IsValid() or !$user->Get('user_under_contract') or ($this->Get('timeline_item_reference_date_type')=='NONE'))
			$this->Set('timeline_item_date','0000-00-00');
		else
		{
		 	if(!$this->Get('timeline_item_reference_date'))
 				$this->Set('timeline_item_date','0000-00-00');
			else
			{
				//adjust by how I am related to that.
				$days=$this->Get('timeline_item_reference_date_days');
				if($this->Get('timeline_item_reference_date_before'))
					$days=0-$days;
				$d->Add($days);
				if(!$this->Get('timeline_item_holiday'))
				{
					while(holiday::IsHoliday($d) or ($d->GetDate('w')==0) or ($d->GetDate('w')==6))
					{
						$d->Add(1);
						$this->Set('timeline_item_date_moved',1);
					}
				}
				$this->Set('timeline_item_date',$d->GetDBDate());
			}
		}
		//save.
		$this->Update();		

		//update anyone that depends on me - if I am not comeplte.
		if($this->id and !$this->Get('timeline_item_complete'))
		{
			$timeline_items=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',"depends_on_timeline_item_id='".$this->id."'",'timeline_item_order');
			$timeline_items->Retrieve();
			foreach($timeline_items->items as $timeline_item)
			{			
				$timeline_item->Set('timeline_item_date',$this->Get('timeline_item_date'));				
				$timeline_item->Update();
			}
		}
		
	}

	public function DependsOnIncompleteItem()
	{
		$depends_on=new timeline_item($this->Get('depends_on_timeline_item_id'));
		return $this->Get('depends_on_timeline_item_id') and !$depends_on->Get('timeline_item_complete') and $this->Get('user_id');
	}

	public function DependsOnCompleteItem()
	{
		$depends_on=new timeline_item($this->Get('depends_on_timeline_item_id'));
		return $this->Get('depends_on_timeline_item_id') and $depends_on->Get('timeline_item_complete') and $this->Get('user_id');
	}

	public function IsNotApplicable()
	{
		$user=new user($this->Get('user_id'));
		if(!$user->id)
			return false;
		if(!$user->Get('user_under_contract'))
			return false;

	  	$user_conditions=new DBRowSetEX('user_conditions','user_condition_id','user_condition',"user_id='".$user->id."' AND user_condition_checked=1");
		$user_conditions->Retrieve();

	 	$user_conditions_condition_ids=array(-1);
		foreach($user_conditions->items as $user_condition)
		 	$user_conditions_condition_ids[]=$user_condition->Get('condition_id');
		 	
		$user_contract_date=new user_contract_date();
		$user_contract_date->InitByKeys(array('contract_date_id','user_id'),array($this->Get('timeline_item_reference_date'),$user->id));

	  	$conditions_to_timeline_items=new DBRowSetEX('conditions_to_timeline_items','condition_id','condition',"condition_to_timeline_item_action='HIDE' AND timeline_item_id='".$this->id."' AND condition_id IN(".implode(',',$user_conditions_condition_ids).")");
		$conditions_to_timeline_items->Retrieve();
		
		if(count($conditions_to_timeline_items->items))
		{
			return true;
		}
		else if($user_contract_date->Get('user_contract_date_na'))
		{
			return true;
		}
		return false;
	}

	public function DisplayICal($params=array())
	{
		$agent=new agent($this->Get('agent_id'));
		$user=new user($this->Get('user_id'));

	 	$today=new date();
	 	$today->ToGMT();
		$d=new dbdate($this->Get('timeline_item_date'));
		if($d->IsValid())
		{
			$url=$this->GetLink($params);
			
			//html?
			$description_spacer="\n";//or <br>...
			$description=array();
			$description[]=$this->Get('timeline_item_summary');
			$description[]='';
			$description[]="<a href='".$url."'>View Online</a>";

			//no HTML
			$text=$this->Get('timeline_item_summary');
			$text=preg_replace('/<\/(p|div|br\s*\/?)>/i',"\r\n",$text);
			$text=strip_tags($text);
			$text=html_entity_decode($text);
			$text=trim($text);

			//no htmll
			$description_spacer="           ";
			$description=array();
			$description[]=$text.
			$description[]=$url;

			//completed info
			if($this->Get('timeline_item_complete'))
			{
				$completed=new DBDate();
			 	$completed->SetTimestamp($this->Get('timeline_item_complete'));
				$description[]="Completed By ".$this->Get('timeline_item_completed_by')." On ".$completed->GetDate('m/d/y');
			}
			
			$title=$this->Get('timeline_item_title');
			if($this->Get('timeline_item_complete'))
				$title="Completed: ".$title;
			

			echo("BEGIN:VEVENT"."\r\n");
			echo("DTSTAMP:".$today->GetDate('Ymd').'T'.$today->GetDate('His')."Z"."\r\n");
			echo("UID:".$this->GetFieldName('ID').""."\r\n");
			echo("ORGANIZER:".$agent->Get('agent_email')."\r\n");
			//start/end times
			$fmt='ALLDAY';
			if($fmt=='TZ')
			{
				$d=new dbdate($this->Get('timeline_item_date'));
				$d->AddTime(0);
				$d2=new dbdate($this->Get('timeline_item_date'));
				$d2->AddTime(24);
				echo("DTSTART;TZID=".$d->GetDate('e').":".$d->GetDate('Ymd').'T'.$d->GetDate('His')."\r\n");
				echo("DTEND;TZID=".$d2->GetDate('e').":".$d2->GetDate('Ymd').'T'.$d2->GetDate('His')."\r\n");
			}
			else if($fmt=='GMT')
			{
				$d=new dbdate($this->Get('timeline_item_date'));
				$d->AddTime(10);
				$d->ToGMT();
				$d2=new dbdate($this->Get('timeline_item_date'));
				$d2->AddTime(17);
				$d2->ToGMT();
				echo("DTSTART:".$d->GetDate('Ymd').'T'.$d->GetDate('His')."Z"."\r\n");
				echo("DTEND:".$d2->GetDate('Ymd').'T'.$d2->GetDate('His')."Z"."\r\n");
			}
			else if($fmt=='ALLDAY')
			{
				$d=new dbdate($this->Get('timeline_item_date'));
				$d2=new dbdate($this->Get('timeline_item_date'));
				$d2->Add(1);
				echo("DTSTART;VALUE=DATE:".$d->GetDate('Ymd')."\r\n");
				echo("DTEND;VALUE=DATE:".$d2->GetDate('Ymd')."\r\n");
			}
			echo("STATUS:CONFIRMED\r\n");
			echo("CATEGORIES:What's Next Real Estate Updates"."\r\n");
			echo("SUMMARY:".$title."\r\n");
			echo("DESCRIPTION:".implode($description_spacer,$description)."\r\n"); //\n NOT \r\n for newlines inside dscription
			echo("URL:".$url."\r\n");
//			foreach($params as $k=>$v)
//				echo($k.":".$v."\r\n");
			echo("END:VEVENT"."\r\n");
		}
	}
	
	function GetLink($params)
	{
		$user=new user($this->Get('user_id'));

		if($params['for']=='USER')
		{
			$user_contact=new user_contact($params['user_contact_id']);
			$user_link=new user_link();
			return $user_link->Generate($user_contact->id,_navigation::GetBaseURL().'/users/'."#".$this->GetFieldName('anchor'),30);
		}
		else if($params['for']=='AGENT')
		{
			$agent=new agent($params['agent_id']);
			$agent_link=new agent_link();
			return $agent_link->Generate($agent->id,$agent->GetUserURL($user,'edit_user.php')."#".$this->GetFieldName('anchor'),30);
		}
		else if($params['for']=='COORDINATOR')
		{
			$coordinator=new coordinator($params['coordinator_id']);
			$coordinator_link=new coordinator_link();
			return $coordinator_link->Generate($coordinator->id,$coordinator->GetUserURL($user,'edit_user.php')."#".$this->GetFieldName('anchor'),30);
		}

	}

};

?>