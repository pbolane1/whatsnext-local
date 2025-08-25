<?php
//**************************************************************//
//	
//	FILE: /*filename*/
//  CLASS: /*classname*/
//  
//	STUBBED BY: PoCo Technologies LLC CoreLib Autocoder /*version*/
//  PURPOSE: database abstraction for the /*tablename*/ table
//  STUBBED TIMESTAMP: /*timestamp*/
//
//**************************************************************//

class /*classname*/ extends DBRowEx
{
	function /*classname*/($id='')
	{
		parent::DBRow($id);
		$this->AllowFiles();
		$this->EstablishTable('/*tablename*/','/*primarykey*/');
		$this->Retrieve();
	}


	function DisplayEditable()
	{
	 	echo("<td>".$this->Get('/*primarykey*/')."</td>");
	 	echo("<td>".$this->Get('/*primarykey*/')."</td>");
	 	echo("<td>".$this->Get('/*primarykey*/')."</td>");
	 	echo("<td>".$this->Get('/*primarykey*/')."</td>");
	}


	function DisplayFull()
	{

	}

	function DisplayShort()
	{

	}

	function EditForm()
	{
		echo("<td colspan='2' align='center'></td></tr>");

		/*editform*/	

		echo("<tr><td colspan='2' class='save_actions'>");
 	}

	function GatherInputs()
	{
		//parent is default
		parent::GatherInputs();
 		
		/*gather*/
		 
		//**FILE**// 
		//$this->GatherFile($this->GetFieldName('/*classname*/_file_ul'),'/*classname*/_file');
	}

	function ValidateInputs()
	{
		/*validate*/
		return count($this->errors)==0;
 	}

	function Save()
	{
	  	$new=!$this->id;
		$psv=parent::Save();

		//**FILE**//
		//if($psv)
		//	$this->SaveFile('/*classname*/_file',file::GetPath('/*classname*/_upload'),'_'.$this->id,array('gif','jpg','png'));


		return count($this->errors)==0;
	}

	function Delete()
	{
		//**SUBITEMS**//
		//database::query("DELETE FROM /*tablename*/ WHERE /*primarykey*/=".$this->id);

		//**FILE**//
		//$this->DeleteFile('/*classname*/_file',file::GetPath('/*classname*/_upload'));

		parent::Delete();

 	}

 	/**THUMBNAILING**/
	//function GetThumb($width,$height)
 	//{ 	  
	//	if($this->id)
	//		return file::GetPath('/*classname*/_display').imaging::ResizeCached($this->Get('/*classname*/_file'),file::GetPath('/*classname*/_upload'),$width,$height);
	//	return '';
	//}

	//**SUB ITEMS**//
	//function EditSubItems()
	//{
	//	$list=new DBRowSet('/*tablename*/','/*primarykey*/','/*classname*/','/*primarykey*/='.$this->id,'/*classname*/_order');
	//	$list->num_new=1;
	//	$list->Retrieve();
	//	$list->SetEachNew('/*primarykey*/',$this->id);
	//	$list->SetEachNew('/*classname*/_order',count($list->items)+1);
	//	$list->ProcessAction();
	//	$list->CheckSortOrder();
	//	$list->Each('PreserveGlobals','/*primarykey*/');
	//	$list->EachNew('PreserveGlobals','/*primarykey*/');
	//	$list->SetEachNew('/*primarykey*/',$this->id);
	//	$list->SetEachNew('/*classname*/_order',count($list->items)+1);
	//
	//	echo("<table class='spaced' id='listing'>");
	//	$list->SetHTML('BEFORE_EXISTING',"<tr><th colspan='7'></th></tr><tr><td class='header'>a</td><td class='header'>b</td><td class='header'>&nbsp;</td><td class='header'>&nbsp;</td></tr>");
	//	$list->SetHTML('BEFORE_EDIT_EXISTING',"<tr><th colspan='7'>Edit /*classname*/s</th></tr>");
	//	$list->SetHTML('AFTER_NEW',"<tr><td colspan='7'><br></td></tr>");
	//	$list->SetHTML('BEFORE_NEW',"<tr><th colspan='7'>New /*classname*/s</th></tr>");
	//	$list->SetHTML('BEFORE_EDIT_NEW',"<tr><th colspan='7'>New /*classname*/s</th></tr>");
	//	$list->SetHTML('EMPTY_SET',"<tr><td colspan='7'>There are no /*classname*/s to display</td></tr>");	
		
	//	$list->Edit('SINGLE_LINK');

	// 	echo("<tr><td align='center' colspan='7'><br><br><a href='#?action=list'>Back To All /*classname*/s</a></td>");
	//	echo("</table>");
	//}


};
?>
