<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

/////////////////////////////////////////////////////////////
//
//				DBRowSet 
//
//
//  This class is intended as a container for a list of
//	chlid class items of the DBRow Class.  Shortcuts for edit,
//	multi edit, other standard DB abstration items here. 
//	This class can be used directly, or inhereted from for 
//	advanced behaviors 
//
//  Child Class constructors could behave as follows:
//
//      public function Child(any params...)
//      {
//          $this->table='xyz';
//          $this->class='xyz';
//          $this->order='xyz';
//          $this->limit='xyz';
//          $this->start='xyz';
//          //etc...
//      }
//
/////////////////////////////////////////////////////////////

class DBRowSet extends DBForm
{
  	//query related
  	public $where='1';
  	public $limit='';
  	public $start=0;
  	public $order='';
  
	//support for joiing on extra tables
	public $join_tables='';
	public $join_where='';

  	//database
	public $tablename='';
	public $primary='';  
	public $classname='';  
	public $items;
	
	//unique identifier for this instance
	public $list_identifier='';
		
	//layout control
	public $html='';

	//////////////////////////////////////////
	//
	//  CREATION AND INITIALIZATION
	//	
	public function __construct($tablename,$primary,$classname,$where='1',$order='',$limit='',$start='')
	{
	  	parent::__construct();

	  	//required class config
		$this->tablename=$tablename;
		$this->primary=$primary;
		$this->classname=$classname;

		//optional query config
		if($where)	$this->where=$where;
		if($order)	$this->order=$order;
		if($limit)	$this->limit=$limit;
		if($start)	$this->start=$start;
		
		$this->newitems=array();
		$this->items=array();
		$this->html=array();
	
		$this->SetIdentifier();
	}

	public function SetHtml($which,$html)
	{
		$this->html[$which]=$html;
	}

	public function SetIdentifier($ident='')
	{	
		global $_db_rowset_identifiers;
		if($ident)
			$this->list_identifier=$ident;
		else if(!$this->list_identifier and $this->tablename)
		{
		  	//make sure first is 0.
		  	if(!$_db_rowset_identifiers[$this->tablename])  $_db_rowset_identifiers[$this->tablename]=0;
		  	$this->new_item_number=$_db_rowset_identifiers[$this->tableName];
		  	$_db_rowset_identifiers[$this->tableName]++;
		}
		
	}
/*	
	public function Retrieve()
	{		
		if(true)//!$this->retrieved)
		{
			//retrieve from DB
			$this->items=array();
			$rs=database::query($this->GetQuery());
			while($rec=database::fetch_array($rs))
			    $this->items[]=new $this->classname($rec[$this->primary],$rec);
		}
		else
		{
		 	$this->Resort() ;
		  
		}

		//create new items for editing, etc		
		$old_new=$this->newitems;
		$this->newitems=array();

		//preseve any failed ones at start of new item list
		foreach($old_new as $item)		
		{   
			if(count($item->GetErrors()))  
				$this->newitems[]=$item;
		}

		//add up to this->num_new items, including previous failures
		for($i=0;$i<$this->num_new;$i++)
		    $this->newitems[]=new $this->classname();
		for($i=0;$i<count($this->newitems);$i++)
			$this->newitems[$i]->SetNewItemNumber(($this->list_identifier?$this->list_identifier.'_':'').$i);
			

		$this->retrieved=true;
			

 	}
*/
	public function Retrieve()
	{		
		//copy any existing items to be referenced to avoid recreation
		$existing_items=$this->items;
		$existing_newitems=$this->newitems;
		if(!is_array($existing_items))		$existing_items=array();
		if(!is_array($existing_newitems))	$existing_newitems=array();

		//our class's list of items 
		$this->items=array();
		$this->newitems=array();

		//DATEBASE ITEMS

		//query to see who we should be holding
		$rs=database::query($this->GetQuery());
		while($rec=database::fetch_array($rs))
		{
		  	//try to copy an existing item
		  	$found=false;
		  	foreach($existing_items as $item)
		  	{
				if($item->id==$rec[$this->primary])    
				{
				  	$this->items[]=$item;
					$found=true;
					break;  
				}
			}
			//or a new(ly saved) item
			if(!$found)
			{
				foreach($existing_newitems as $item)
			  	{
					if($item->id==$rec[$this->primary])    
					{
					  	$this->items[]=$item;
						$found=true;
						break;  
					}
				}		  
			}
			//ore create it
			if(!$found)
			    $this->items[]=new $this->classname($rec[$this->primary],$rec);
		}

		//NEW ITEMS

		//any that were around before
		foreach($existing_newitems as $item)
		{
			if(!$item->id)		  
				$this->newitems[]=$item;
		}
		//add up to this->num_new items, including previous failures
		for($i=count($this->newitems);$i<$this->num_new;$i++)
		    $this->newitems[]=new $this->classname();

		//set the unique identifier for the new items
		for($i=0;$i<count($this->newitems);$i++)
			$this->newitems[$i]->SetNewItemNumber(($this->list_identifier?$this->list_identifier.'_':'').$i);
			
		//parent for newbies.
		$this->SetEachParent($this->parent_ref);
	
		$this->retrieved=true;
 	}

	public function GetIndexOf($find)
	{
	  	if($find)
	  	{
		  	$rs=database::query("SELECT * ".$this->GetQueryFromWhere()."  ORDER BY ".($this->order?$this->order:$this->primary)."");
		  	$index=0;
		  	while($rec=database::fetch_array($rs))
		  	{
				if($rec[$this->primary]==$find)
					return $index;
				$index++;
			}
		}	  
		return -1;
	}

	public function SetEachParent(&$obj)
	{
		$this->parent_ref=&$obj;

		//set a reference to the parent
		for($i=0;$i<count($this->items);$i++)
			$this->items[$i]->SetParent($obj);
		for($i=0;$i<count($this->newitems);$i++)
			$this->newitems[$i]->SetParent($obj);	  
	}

	public function GetQueryFromWhere()
	{
	  	//main table, extra tables
		$q="FROM ".$this->tablename;
		if($this->join_tables) $q.=', '.$this->join_tables;
		
		//where, join where
		$q.=" WHERE ".$this->where." ";
		if($this->join_where) $q.=' AND '.$this->join_where;
		
		return $q;
	}

	public function GetQuery()
	{
		$q="SELECT * ".$this->GetQueryFromWhere();
		
		//order
		if($this->order)	$q.=" ORDER BY ".$this->order." ";
		else				$q.=" ORDER BY ".$this->primary." ";
			
		//limit	  
		if($this->limit)	$q.=" LIMIT ".$this->start.", ".$this->limit." ";	  
		
		//return
		return $q;
	}

	public function ReSort()
	{
		$existing=$this->items;
		
		$this->items=array();
		$rs=database::query($this->GetQuery());
		while($rec=database::fetch_array($rs))
		{
			foreach($existing as $ex)  
			{
				if($ex->id==$rec[$this->primary])
				{
					$this->items[]=$ex;
					break;
				}
			}
		}
	}

	//////////////////////////////////////////
	//
	//  DRAWING (NON-EDIT)
	//

	public function ListShort()
	{	  	
		foreach($this->items as $i)
		    $i->DisplayShort();
		if(count($this->items)==0)
			echo($this->html['EMPTY_SET']);
	}

	public function ListFull()
	{	  	
		foreach($this->items as $i)
		    $i->DisplayFull();
		if(count($this->items)==0)
			echo($this->html['EMPTY_SET']);
	}


	public function Rotate($unique_name='',$count=1,$display=true)
	{
		$cur=0;
	
	  	//session name for the rotator
	  	if(!$unique_name)	$unique_name=$this->tablename;
		$unique_name.='_rotator';
		$cur=Session::Get($unique_name);



		//limit range 0-N
		if(!$cur)								 $cur=0;
		else if($cur>=$this->GetTotalAvailable()) $cur=0;

		//Set params and retrieve
		$this->start=$cur;
		$this->limit=$count;
		$this->Retrieve();
		
		if($display)
			$this->ListFull();
		//update session.
		Session::Set($unique_name,$cur+$count);	
	}


	public function Each($fn_name,$args=array())
	{	  
		for($i=0;$i<count($this->items);$i++)
			call_user_func_array(array(&$this->items[$i],$fn_name),$args);
	}

	public function EachNew($fn_name,$args=array())
	{	  
		for($i=0;$i<count($this->newitems);$i++)
			call_user_func_array(array(&$this->newitems[$i],$fn_name),$args);
	}

	//////////////////////////////////////////////////////
	//
	//      Flagging Routines
	//

	public function SetFlag($which,$value=true)
	{
		for($i=0;$i<count($this->items);$i++)
			$this->items[$i]->SetFlag($which,$value);
		for($i=0;$i<count($this->newitems);$i++)
			$this->newitems[$i]->SetFlag($which,$value);
		parent::SetFlag($which,$value);
	}


	//////////////////////////////////////////
	//
	//  ACTIONS
	//
	
	//BASE ROUTER.  SHOULD NOT OVERRIDE
	public function ProcessAction($act='')
	{
		//if none provided, ready from our expected action source
		if(!$act)	$act=$this->GetCurrentAction();
		
		if(!$this->action_processed)
			$this->DoAction($act);
		$this->action_processed=true;

//		$this->Retrieve();
	}
	
	//OVERRIDABLE
/*
	public function DoAction($act)
	{
	  	if($act==$this->GetFormAction('save'))
	  		$this->Save();
		else if($act==$this->GetFormAction('delete'))
			$this->Delete();
		else
		{		 	  
		  	//allow each item to process (the provided or its default) action
			for($i=0;$i<count($this->items);$i++)  
				$this->items[$i]->ProcessAction($action);			
			for($i=0;$i<count($this->newitems);$i++)  
			{
				$this->newitems[$i]->ProcessAction($action);
				//if just created, re-new
				
				if(count($this->newitems[$i]->GetErrors())==0 and $this->newitems[$i]->id)
				{
				  	$this->newitems[$i]->Init($this->newitems[$i]->id);
					$this->items[]=$this->newitems[$i];
					$this->newitems[$i]=new $this->classname();
				}
			}
			
			//adapt existing w/o doing a new retrieve call (don't re-create.)
			$this->ReSort();			
			for($i=0;$i<count($this->newitems);$i++)
				$this->newitems[$i]->SetNewItemNumber(($this->list_identifier?$this->list_identifier.'_':'').$i);
		}
	}
*/

	public function DoAction($act)
	{
	  	if($act==$this->GetFormAction('save'))
	  		$this->Save();
		else if($act==$this->GetFormAction('delete'))
			$this->Delete();
		else
		{		 	  
		  	//allow each item to process (the provided or its default) action
			for($i=0;$i<count($this->items);$i++)  
				$this->items[$i]->ProcessAction($action);			
			for($i=0;$i<count($this->newitems);$i++)  
				$this->newitems[$i]->ProcessAction($action);

			$this->Retrieve();
		}
	}

	public function GetCurrentAction()
	{
		$param=$this->action_parameter;
		global $$param;
		return $$param;		
	}

	//////////////////////////////////////////
	//
	//  FROM RELATED
	//	

	public function GetUniqueName($for)
	{
		return $for.'_'.$this->tablename.$this->list_identifier;
	}

	//////////////////////////////////////////
	//
	//  DATABASE - SAVING, DELETING
	//
	
	public function Save()
  	{
		for($i=0;$i<count($this->items);$i++)  
			$this->items[$i]->Save();
		for($i=0;$i<count($this->newitems);$i++)  
			$this->newitems[$i]->Save();
		$this->Retrieve();
	}	  	

	public function Delete()
	{
		for($i=0;$i<count($this->items);$i++)  
			$this->items[$i]->Delete();	  
	}


	public function CheckSortOrder($orderfield='',$min=1,$orderfieldref='')
	{
		//where to start order at
		$cnt=$min;
	
	  	//order must exist for the queries.  if not given, try using $this->order 
	  	if(!$orderfield)
	  		$orderfield=$this->order;
	  	if(!$orderfield)
	  		return;
	
		//$orderfieldref allows you to set orderfield based on altnate retrieval order
		if(!$orderfieldref)
			$orderfieldref=$orderfield;
		
		//get all, update to their position
		$rs=database::query("SELECT * ".$this->GetQueryFromWhere()." ORDER BY ".$orderfieldref);
		while($rec=database::fetch_array($rs))
			database::query("UPDATE ".$this->tablename." SET ".$orderfield."=".($cnt++)." WHERE ".$this->primary."='".$rec[$this->primary]."'");
			
		//update our list
		$this->Retrieve();
		
		//update everybody's perception of themself
		$cnt=$min+$this->start;
		for($i=0;$i<count($this->items);$i++) 
			$this->items[$i]->Set($orderfield,$cnt++);			
 	}

	public function SetSortOrderNew($orderfield='',$min=1)
	{
	  	//order must exist for the queries.  if not given, try using $this->order 
	  	if(!$orderfield)
	  		$orderfield=$this->order;
	  	if(!$orderfield)
	  		return;

		//where to start order at
	  	$cnt=max($min,count($this->items)+$min);

		//order each
		for($i=0;$i<count($this->newitems);$i++) 
			$this->newitems[$i]->Set($orderfield,$cnt++);			
 	}


	public function Paginate($fieldname='start',$implicit=true)
	{
	  	$total=$this->GetTotalAvailable();
	  	$total=$total?$total:1;
	  	$nperpage=$this->limit?$this->limit:$total;
	  	$npages=ceil($total/$nperpage);
		$start=$this->start;

		$params=array();
		if($implicit)		$params['onchange']='this.form.submit()';
	  	
	  	$opts=array();
	  	for($i=0;$i<$npages;$i++)
	  		$opts[min(($i*$nperpage)+1,$total).' - '.min(($i+1)*$nperpage,$total)]=($i*$nperpage);

		form::Begin("?".$this->action_parameter."=".$this->GetFormAction('page'));
		form::DrawSelect($fieldname,$opts,$this->start,$params);
		if(!$implicit)	form::DrawSubmit('','edit');		
		form::End();
	  	
	}
	
	public function PaginateLinks($fieldname='start',$maxpages=1000,$spc="&nbsp;",$classname='start')
	{
	  	$total=$this->GetTotalAvailable();
	  	$total=$total?$total:1;
	  	$nperpage=$this->limit?$this->limit:$total;
	  	$npages=ceil($total/$nperpage);
		$start=$this->start;
		$curpage=floor($start/$nperpage);

		$links=array();

		$start_i=0;
		if($npages>$maxpages)
		{
			$start_i=$curpage-floor($maxpages/2);
			if(($start_i+$maxpages)>$npages)
				$start_i=$npages-$maxpages;
			if($start_i<0)  
				$start_i=0;
		}

		if($npages>1)
		{
		  	echo("&nbsp;");
//			if($curpage>=$nperpage)
//				echo("<a class='".$fieldname."' href='?".$this->action_parameter."=".$this->GetFormAction('page')."&".$fieldname."=".(((floor($curpage/10)*10)-10)*$total)."'>&lt;&lt;</a>");				
			for($i=$start_i;$i<min(array($npages,$start_i+$maxpages));$i++)
				$links[]=("<a class='".$classname.($start==($i*$nperpage)?'_sel':'')."' href='?".$this->action_parameter."=".$this->GetFormAction('page')."&".$fieldname."=".($i*$nperpage)."'>".($i+1)."</a>");		
//			if((floor($curpage/$nperpage)*$nperpage)<floor($maxpages/$nperpage)*$nperpage)
//				echo("<a class='".$fieldname."' href='?".$this->action_parameter."=".$this->GetFormAction('page')."&".$fieldname."=".(((floor($curpage/10)*10)+10)*$total)."'>&gt;&gt;</a>");		
		}	  
	  	echo implode($spc,$links);
	}
	
	public function NextPageLink($fieldname='start')
	{
	  	$total=$this->GetTotalAvailable();
	  	$total=$total?$total:1;
	  	$nperpage=$this->limit?$this->limit:$total;
	  	$npages=ceil($total/$nperpage);
		$start=$this->start;
		$curpage=floor($start/$nperpage);

		if($curpage<($npages-1))
			return $this->PageLink($fieldname,$curpage+1);
	  	
	}

	public function PrevPageLink($fieldname='start')
	{
	  	$total=$this->GetTotalAvailable();
	  	$total=$total?$total:1;
	  	$nperpage=$this->limit?$this->limit:$total;
	  	$npages=ceil($total/$nperpage);
		$start=$this->start;
		$curpage=floor($start/$nperpage);	  	

		if($curpage>0)
			return $this->PageLink($fieldname,$curpage-1);

	}

	public function PageLink($fieldname='start',$page)
	{
	  	$total=$this->GetTotalAvailable();
	  	$total=$total?$total:1;
	  	$nperpage=$this->limit?$this->limit:$total;
	  	$npages=ceil($total/$nperpage);
		$start=$this->start;
		$curpage=floor($start/$nperpage);

		return "?".$this->action_parameter."=".$this->GetFormAction('page')."&".$fieldname."=".($page*$nperpage)."";			  	
	}

	
	public function GetTotalAvailable()
	{
	  
	  
		$q="SELECT COUNT(1) as total ".$this->GetQueryFromWhere();
		$rec=database::fetch_array(database::query($q));
	  	return $rec['total'];
	}

	//////////////////////////////////////////
	//
	//  EDITING
	//


//these wil need cleanup, more hooks, or.......... 

	public function Edit($editmode='SINGLE_LINK',$numnew=1,$addtoobjname='',$maxnew=0)
	{
	  	$this->num_new=$numnew;
		$this->Retrieve();		
		$this->ProcessAction();		
		$this->JSReportErrors();

	  	switch($editmode)
	  	{
			case 'SINGLE_NEW':
				echo($this->html['BEFORE_EXISTING']);
				for($i=0;$i<count($this->items);$i++)  
				  	$this->items[$i]->Draw();
				if(count($this->items)==0)
					echo($this->html['EMPTY_SET']);
				echo($this->html['AFTER_EXISTING']);
				echo($this->html['BEFORE_NEW']);
				for($i=0;$i<count($this->newitems);$i++) 
					$this->newitems[$i]->Edit();
				echo($this->html['AFTER_NEW']);
				break;
			case 'MULTI_NEW_LINK_EDIT':
				$newitem=new $this->classname;

				$activeitem=$this->GetActiveItem();
				if($activeitem and $activeitem->id)
				{
					echo($activeitem->id?$this->html['BEFORE_EDIT_EXISTING']:$this->html['BEFORE_EDIT_NEW']);			  	
					$activeitem->Draw();
					echo($activeitem->id?$this->html['AFTER_EDIT_EXISTING']:$this->html['AFTER_EDIT_NEW']);			  	
				}
				else
				{
					echo($this->html['BEFORE_EXISTING']);
					for($i=0;$i<count($this->items);$i++)  
					  	$this->items[$i]->Draw();
					if(count($this->items)==0)
						echo($this->html['EMPTY_SET']);
					echo($this->html['AFTER_EXISTING']);
					echo($this->html['BEFORE_NEW']);
					form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams(),$newitem->form_method,$newitem->file_upload);
					for($i=0;$i<count($this->newitems);$i++) 
						$this->newitems[$i]->EditForm();
					echo($this->html['AFTER_NEW']);
					echo($this->html['BEFORE_SAVEBUTTON']);					
					form::DrawSubmit('','save');
					echo($this->html['AFTER_SAVEBUTTON']);					
					form::end();				  	

				}
				break;
			case 'SINGLE_NEW_LINK_EDIT':
				$activeitem=$this->GetActiveItem();
				if($activeitem and $activeitem->id)
				{
					echo($activeitem->id?$this->html['BEFORE_EDIT_EXISTING']:$this->html['BEFORE_EDIT_NEW']);			  	
					$activeitem->Draw();
					echo($activeitem->id?$this->html['AFTER_EDIT_EXISTING']:$this->html['AFTER_EDIT_NEW']);			  	
				}
				else
				{
					echo($this->html['BEFORE_EXISTING']);
					for($i=0;$i<count($this->items);$i++)  
					  	$this->items[$i]->Draw();
					if(count($this->items)==0)
						echo($this->html['EMPTY_SET']);
					echo($this->html['AFTER_EXISTING']);
					echo($this->html['BEFORE_NEW']);
					for($i=0;$i<count($this->newitems);$i++) 
						$this->newitems[$i]->Edit();
					echo($this->html['AFTER_NEW']);
				}
				break;

			case 'ALL':
				$newitem=new $this->classname;

				form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams(),$newitem->form_method,$newitem->file_upload);
				echo($this->html['BEFORE_NEW']);
				for($i=0;$i<count($this->newitems);$i++) 
					$this->newitems[$i]->EditForm();
				echo($this->html['AFTER_NEW']);
				echo($this->html['BEFORE_EXISTING']);
				for($i=0;$i<count($this->items);$i++)  
				  	$this->items[$i]->EditForm();				  	
				if(count($this->items)==0)
					echo($this->html['EMPTY_SET']);
				echo($this->html['AFTER_EXISTING']);
				echo($this->html['BEFORE_SAVEBUTTON']);					
				form::DrawSubmit('','save');
				echo($this->html['AFTER_SAVEBUTTON']);					
				form::end();				  	
				break;
			case 'MULTI':
				$jsnewitem=new $this->classname;
				$jsnewitem->DeclareJSEditFunction();
				
				form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams(),$jsnewitem->form_method,$jsnewitem->file_upload);
				$this->PreserveInputs();
				echo($this->html['BEFORE_MULTIITEMS']);
				echo($this->html['BEFORE_EXISTING']);
				for($i=0;$i<count($this->items);$i++)  
				  	$this->items[$i]->EditForm();				  	
				if(count($this->items)==0)
					echo($this->html['EMPTY_SET']);
				echo($this->html['AFTER_EXISTING']);
				echo($this->html['BEFORE_NEW']);
				for($i=0;$i<count($this->newitems);$i++) 
					$this->newitems[$i]->EditForm();
				echo($this->html['AFTER_NEW']);
				echo($this->html['AFTER_MULTIITEMS']);

				//more items button...
				echo($this->html['BEFORE_MOREITEMSBUTTON']);
				form::DrawHiddenInput($this->GetFieldName('newitemcount'),$this->num_new);				
				
				$more_js='return false;';
				if($maxnew>0)	$more_js="if(document.getElementById('".$this->GetFieldName('newitemcount')."').value<".$maxnew."){".$newjsfn.";".$this->GetFieldName('newitemcount')."').value++;}if(document.getElementById('".$this->GetFieldName('newitemcount')."').value>=".$maxnew."){document.getElementById('".$this->GetFieldName('addnewitem')."').style.display='none';}";
				else 			$more_js="{".$jsnewitem->GetJSEditFunctionName()."(document.getElementById('".$this->GetFieldName('newitemcount')."').value,'".$addtoobjname."');document.getElementById('".$this->GetFieldName('newitemcount')."').value++;}";				
				if($maxnew<=0 or $numnew<$maxnew)				
					form::DrawButton($this->GetFieldName('addnewitem'),'More...',array('onclick'=>$more_js));
				echo($this->html['AFTER_MOREITEMSBUTTON']);
				//end more items button	
					
				echo($this->html['BEFORE_SAVEBUTTON']);					
				form::DrawSubmit('','Save');
				echo($this->html['AFTER_SAVEBUTTON']);					
				form::end();				  	
				break;							
			default: //'SINGLE_LINK'
				$activeitem=$this->GetActiveItem();
				if($activeitem)
				{
					echo($activeitem->id?$this->html['BEFORE_EDIT_EXISTING']:$this->html['BEFORE_EDIT_NEW']);			  	
					$activeitem->Draw();
					echo($activeitem->id?$this->html['AFTER_EDIT_EXISTING']:$this->html['AFTER_EDIT_NEW']);			  	
				}
				else
				{
					echo($this->html['BEFORE_NEW']);
					for($i=0;$i<count($this->newitems);$i++) 
						$this->newitems[$i]->Draw();
					echo($this->html['AFTER_NEW']);
					echo($this->html['BEFORE_EXISTING']);
					for($i=0;$i<count($this->items);$i++)  
					  	$this->items[$i]->Draw();
					if(count($this->items)==0)
						echo($this->html['EMPTY_SET']);
					echo($this->html['AFTER_EXISTING']);
				}
				break;
		}
	}

	public function EditWithNew()
	{
		$this->Edit();	  
	}

	public function EditAll($numnew=1)
	{
		$this->Edit($editmode='ALL',$numnew,$numnew);	  
	}

	public function EditMultiple($numnew=1,$maxnew=1,$newjsfn='')
	{
		$this->Edit($editmode='MULTI',$numnew=1,$maxnew=1,$newjsfn); 
	}


	public function GetActiveItem()
	{
		for($i=0;$i<count($this->newitems);$i++) 
		{
		  	if(strpos($this->newitems[$i]->edit_state,'EDIT')!==false)
				return($this->newitems[$i]);
		}
		for($i=0;$i<count($this->items);$i++) 
		{
		  	if(strpos($this->items[$i]->edit_state,'EDIT')!==false)
				return($this->items[$i]);
		}
		return false;
	  
	}
	

	//////////////////////////////////////////
	//
	//  ERROR REPORTING
	//
	

	public function GetErrors()
	{
		$errors=array();
		for($i=0;$i<count($this->items);$i++)  
		{
			foreach($this->items[$i]->GetErrors() as $e)
				$errors[]=$e;
		}
		for($i=0;$i<count($this->newitems);$i++) 
		{
			foreach($this->newitems[$i]->GetErrors() as $e)
				$errors[]=$e;
		}
		return $errors;
	}	
	
	public function ReportErrors()
	{
		for($i=0;$i<count($this->items);$i++)  
			$this->items[$i]->ReportErrors();
		for($i=0;$i<count($this->newitems);$i++) 
			$this->newitems[$i]->ReportErrors();
	}

	public function JSReportErrors()
	{
		for($i=0;$i<count($this->items);$i++)  
			$this->items[$i]->JSReportErrors();
		for($i=0;$i<count($this->newitems);$i++) 
			$this->newitems[$i]->JSReportErrors();
	}	

	public function SetEach($key,$value='')
	{
		for($i=0;$i<count($this->items);$i++)  
			$this->items[$i]->Set($key,$value);
	}

	public function SetEachNew($key,$value='')
	{
		for($i=0;$i<count($this->newitems);$i++)  
			$this->newitems[$i]->Set($key,$value);
	}
};

class DBTable extends DBRowSet
{
  	public function DBTable($tablename,$primary,$classname,$order='')
	{
		parent::DBRowSet($tablename,$primary,$classname,'1',$order);  
	}
};

?>