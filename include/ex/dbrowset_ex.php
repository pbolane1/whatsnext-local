<?php

/////////////////////////////////////////////////////////////
//
//				DBRowSetEX
//
/////////////////////////////////////////////////////////////

class DBRowSetEX extends DBRowSet
{

 	public function __construct($tablename,$primary,$classname,$where='1',$order='',$limit='',$start='')
	{
	  	global $HTTP_GET_VARS;
	  
	 	parent::__construct($tablename,$primary,$classname,$where,$order,$limit,$start);


	  	$sortname=$tablename.'_sort';
	  	$sortordername=$tablename.'_sortorder';
	  	$startname=$tablename.'_start';	  	

	  	if(isset($HTTP_GET_VARS[$sortname]))
		{		  
			Session::Set($sortname,$HTTP_GET_VARS[$sortname]);
			unset($HTTP_GET_VARS[$sortname]);
		 	Session::Set($startname,0);
		}
	  	if(isset($HTTP_GET_VARS[$sortordername]))  
		{
		  	Session::Set($sortordername,$HTTP_GET_VARS[$sortordername]);
		 	unset($HTTP_GET_VARS[$sortordername]);
		 	Session::Set($startname,0);
		}
	  	if(isset($HTTP_GET_VARS[$startname]))  
		{
		  	Session::Set($startname,$HTTP_GET_VARS[$startname]);
		 	unset($HTTP_GET_VARS[$startname]);
		}
		if(Session::Get($sortname))
		  	$order=Session::Get($sortname).' '.Session::Get($sortordername);
		if(Session::Get($startname))
		  	$start=Session::Get($startname);
	  
		$this->column_count=0;
	  
	 	parent::__construct($tablename,$primary,$classname,$where,$order,$limit,$start);
	}	 

	public function ProcessAction($act='')
	{
		parent::ProcessAction();

		//find last editied and move up to it.
		if($this->limit)		
		{
			global $HTTP_POST_VARS;
			$index=$this->GetIndexOf($HTTP_POST_VARS['dbrs_ex_current_id']);	  	  	

			if($index>=0)
			{
			  	$startname=$this->tablename.'_start';	  	
				$start=$this->limit*(floor($index/$this->limit));
				Session::Set($startname,$start);
			  	$this->start=$start;  
			}		
		}
	}


	public function Header($name,$sortfield=false,$colspan='',$class='')
	{
	  	$sortname=$this->tablename.'_sort';
	  	$sortordername=$this->tablename.'_sortorder';
	  	$startname=$this->tablename.'_start';	  	

		$order=explode(' ',$this->order);
		$cursort=$order[0];
		$cursortorder=$order[1];		
		$add='&'.$startname.'=0';


		$hdr='<th '.($colspan?"colspan='".$colspan."'":'').' class="'.$class.'">';
	  	if($sortfield)
	  	{
	  	  	$hdr.="&nbsp;&nbsp;";
	  	  	//asc
	  	  	$order='ASC';
	  	  	if($cursort==$sortfield and $cursortorder!='DESC')
		  	  	$order='DESC';		  	
		  	  	
			$hdr.="<a href='?action=sort&".$sortname."=".$sortfield."&".$sortordername."=".$order.$add."'>".$name."</a>";
		  	$class='';
			if(($cursort==$sortfield) and ($cursortorder=='DESC')) 	
				$class='sort_icon_selected';
			$hdr.="<a class='sort_icon ".$class."' href='?action=sort&".$sortname."=".$sortfield."&".$sortordername."=DESC".$add."'><i class='fas fa-arrow-down'></i></a>";
		  	$class='';
			if(($cursort==$sortfield) and ($cursortorder=='ASC')) 	
				$class='sort_icon_selected';
			$hdr.="<a class='sort_icon ".$class."' href='?action=sort&".$sortname."=".$sortfield."&".$sortordername."=ASC".$add."'><i class='fas fa-arrow-up'></i></a>";
		}
	  	else
			$hdr.=$name;		  	

		$this->column_count+=$colspan?$colspan:1;
	  	
		$hdr.='</th>';  
		return $hdr;

	}

	public function Paginate($fieldname='start',$implicit=true)
	{
	  	if($this->GetActiveItem())
	  		return;
	  
	  	$sortname=$this->tablename.'_sort';
	  	$sortordername=$this->tablename.'_sortorder';
	  	$startname=$this->tablename.'_start';	  	

		$order=explode(' ',$this->order);
		$cursort=$order[0];
		$cursortorder=$order[1];		
		$add='&'.$sortname.'='.$cursort.'&'.$sortordername.'='.$cursortorder;
	  	  	
	  	$next=$this->NextPageLink($startname);
	  	$prev=$this->PrevPageLink($startname);	  		  	
		if($this->GetTotalAvailable()>$this->limit and $this->limit)
		{
		 	if($this->start>0)
			  	$first=$this->PageLink($startname,0);	  		  	
			if((floor($this->GetTotalAvailable()/$this->limit)*$this->limit)>$this->start)
			  	$last=$this->PageLink($startname,floor($this->GetTotalAvailable()/$this->limit));
		}	  	

		$temp=new $this->classname();
		$add=$temp->GetFormExtraParams(array($startname));
		
	 	echo("<div id='listing_pages'><table width='100%'><tr>");
	 	echo ("<td align='left' width='33%'>");
		if($first)	echo("<a href='".$first.$add."'>&lt;&lt;First</a>&nbsp;&nbsp;");
		if($prev)	echo("<a href='".$prev.$add."'>&lt;Previous</a>");
		echo("</td>");
		echo "<td align='center' width='33%'>Showing ".($this->start+1)."-".min(($this->limit?($this->start+$this->limit):$this->GetTotalAvailable()),$this->GetTotalAvailable())." of ".$this->GetTotalAvailable()."</td>";
		echo ("<td align='right' width='33%'>");
		if($next)	echo("<a href='".$next.$add."'>Next&gt;</a>");
		if($last)	echo("&nbsp;&nbsp;<a href='".$last.$add."'>Last&gt;&gt;</a>");
		echo("</td>");
	 	echo("</tr></table></div>");
	}

	
	public function NeedToPaginate($total_available=-1)
	{
	  	$total_available=($total_available>=0)?$total_available:$this->GetTotalAvailable();
	  
	  
	  	if($this->GetActiveItem())
	  		return false;
		if(!$this->limit)		
			return false;
		if($this->limit>=$total_available)
			return false;
		return true;
	}


	public function DrawPages($page,$owner_object='',$total_available=-1,$extra='')
	{
	  	if(is_array($extra))
	  	{
	  		foreach($extra as $k=>$v)
		  	$additionalparams='?'.$k.'='.$v;
	  	}
	  	
	  	$owner_object=$owner_object?$owner_object:$this;
	  
	  	$total_available=($total_available>=0)?$total_available:$this->GetTotalAvailable();
	  
	  	$page_count=ceil($total_available/$this->limit);
	  	
//		if($page_count<=1)	return;
		$page=$page?$page:0;
		
		$max=5;
		$start=0;
		
		if($page_count>$max)		$start=ceil($page-$max/2);
		else						$max=$page_count;

		if($start<0)  				$start=0;
		if($start+$max>$page_count)	$start=$page_count-$max;

		$start=(floor(($page)/$max)*$max);

		if($this->NeedToPaginate($total_available))
		{
			echo('<nav class="pages">');
			echo('<ul class="pagination">');
			if($start>0)
				echo("<li class=''><a href='".$owner_object->PageURL(0).$additionalparams."'>&lt;&lt;</a></li>");
			if($start>=$max)
				echo("<li class=''><a href='".$owner_object->PageURL($start-1).$additionalparams."'>&lt;</a></li>");
			$links=array();
			for($i=$start;($i<$start+$max) and ($i<$page_count);$i++)
				echo("<li class='".($i==$page?'active':'')."'><a href='".$owner_object->PageURL($i).$additionalparams."'>".($i+1)."</a></li>");		  
			if($start<($page_count-$max))
				echo("<li class=''><a href='".$owner_object->PageURL($start+$max).$additionalparams."'>&gt;</a></li>");
			if($i<$page_count)
				echo("<li class=''><a href='".$owner_object->PageURL($page_count-1).$additionalparams."'>&gt;&gt;</a></li>");
			echo("</ul>");
			echo("</nav>");
		}
	}

	public function PageURL($page)
	{
		return '?page='.$page;
	}
	
	public function Retrieve()
	{
		parent::Retrieve();

	  	$total=$this->GetTotalAvailable();

	  	for($i=0;$i<count($this->items);$i++)
	  	{
			$this->items[$i]->rowclass='row'.($i%2);
	  		$this->items[$i]->parent_total=$total;
	  	}
		for($i=0;$i<count($this->newitems);$i++)
		    $this->newitems[$i]->parent_total=$total;
	}
		
	public function Edit($editmode='SINGLE_LINK',$numnew=1,$addtoobjname='',$maxnew=0)
	{
		$this->Retrieve();		
		$this->ProcessAction();		
		$this->JSReportErrors();

		$activeitem=$this->GetActiveItem();
		if($activeitem)		
			$this->SetFlag('DROPSORT',0);
	  	if($this->GetFlag('DROPSORT'))
	  	{
			global $__dbrs_instances;
			$containerid='dbrs'.(++$__dbrs_instances);			
			$this->SetFlag('DROPSORT_CONTAINER_ID',$containerid);
			$this->SetFlag('DROPSORT_AJAXURL',_navigation::GetBaseURL().'ajax/sort_process.php');			
			$this->SetFlag('DROPSORT_WHERE',$this->where);
			$this->SetFlag('DROPSORT_LIMIT',$this->limit);
			$this->SetFlag('DROPSORT_START',$this->start);
			$this->SetFlag('DROPSORT_ORDER',$this->order);
			$this->SetFlag('DROPSORT_TABLENAME',$this->tablename);
			$this->SetFlag('DROPSORT_PRIMARY',$this->primary);
			$this->SetFlag('DROPSORT_CLASSNAME',$this->classname);

//			javascript::IncludeJS('jquery_lib.js');
//			javascript::IncludeJS('AjaxRequest.js');
//			javascript::IncludeJS('drop_sort.js');
		}

	  	if($this->GetFlag('COLUMNS') or $this->GetFlag('DROPSORT'))
	  	{
			if($this->GetFlag('COLUMNS'))
				$this->column_count=$this->GetFlag('COLUMNS');
	  	 	$w=floor(100/$this->column_count);
			echo("<style type='text/css'>");			
			echo(".listing_list TH{width:".$w."% !important;}");
			echo(".listing_list TR.list_item TD{width:".$w."% !important;}");
			echo(".listing_list TH>DIV{overflow:hidden;}");
			echo(".listing_list TH>DIV::-webkit-scrollbar {dispay:none}");
			echo(".listing_list TR.list_item TD>DIV{overflow:auto;}");
			echo(".listing_list TR.list_item TD>DIV::-webkit-scrollbar {dispay:auto}");
			echo(".listing_list TR.list_item TD.edit_actions{white-space:normal}");
			echo(".listing_list TR.list_item TD.edit_actions FORM{margin-bottom:10px;}");
			echo("</style>");			
			
			Javascript::Begin();
			echo("
				jQuery(function(){
				 	var w=jQuery('.listing_list').width()/".$this->column_count."-20;//padding.
					jQuery('.listing_list TH:not(.area)').each(function(){jQuery(this).contents().wrapAll('<div></div>');});
					jQuery('.listing_list TR.list_item TD:not(.create_actions)').each(function(){jQuery(this).contents().wrapAll('<div></div>');});
					jQuery('.listing_list TH:not(.area)>DIV').width(w);
					jQuery('.listing_list TR.list_item TD:not(.create_actions)>DIV').width(w);
				});
			");
			Javascript::End();
		}


		$tableclass=' '.($activeitem?($activeitem->id?'listing_new':'listing_edit'):'listing_list');
		echo("<table class='listing".$tableclass."'>");
		if($activeitem)
		{
			echo($activeitem->id?$this->html['BEFORE_EDIT_EXISTING']:$this->html['BEFORE_EDIT_NEW']);			  	
			echo("<tr><td class='edit_wrapper'>");
			echo("<table class='edit_wrapper'><tr>");				
			$activeitem->Draw();
			echo("</tr></table>");			
			echo("</td></tr>");				
			echo($activeitem->id?$this->html['AFTER_EDIT_EXISTING']:$this->html['AFTER_EDIT_NEW']);			  	
		}
		else
		{
//			echo("<tr><td class='edit_actions' colspan='100'>");
//			$this->Paginate();
//			echo("</td></tr>");


			echo($this->html['BEFORE_NEW']);
			for($i=0;$i<count($this->newitems);$i++) 
				$this->newitems[$i]->Draw();
			echo($this->html['AFTER_NEW']);
			echo($this->html['BEFORE_EXISTING']);
		  	if($this->GetFlag('DROPSORT'))
		  	{
				echo("<tr><td colspan='100' class='dropsort_container_cell'>");		
				echo("<div class='dropsort_container' id='".$containerid."'>");
	
				for($i=0;$i<count($this->items);$i++)  
				{
					echo("<div class='dropsort' id='".$this->items[$i]->GetFieldName('dropsort')."'>");
//					echo("<div class='dropsort_dragbar'></div>");
					echo("<table class='dropsort_wrapper'><tr  class='list_item'>");
				  	$this->items[$i]->Draw();
					echo("</tr></table></div>");			
				}  	
				echo("<div class='placeholder'><br></div>");
				echo("</div>");
				echo("</td></tr>");			
			}
			else
			{
				for($i=0;$i<count($this->items);$i++)  
				{
				  	$this->items[$i]->Draw();
				}
			}
			if(count($this->items)==0)
				echo($this->html['EMPTY_SET']);
			echo($this->html['AFTER_EXISTING']);
		}
		echo("<tr><td class='edit_actions' colspan='100'>");
		$this->Paginate();
		echo("</td></tr>");
		
	  	echo("</table>");
	  	
	  	if($this->GetFlag('DROPSORT'))
	  	{
			javascript::Begin();
			echo("DropSortEnable('".$this->GetFlag('DROPSORT_CONTAINER_ID')."','dropsort_dragbar','dropsort','dropsort_sort','".$this->GetFlag('DROPSORT_AJAXURL')."');");
			javascript::End();
			
		}	  	
		$this->SupportEffects();
	}
	
	public function SupportEffects()
	{

//		javascript::IncludeJS('jquery_lib.js');
//		javascript::IncludeJS('listing_effects.js');			
	  	if($this->GetFlag('ROWHIGHLIGHT'))
	  	{
			javascript::Begin();
			echo("EnableRowHighlight();");
			javascript::End();			
		}
	  	if($this->GetFlag('BUTTONHIGHLIGHT'))
	  	{
			javascript::Begin();
			echo("EnableButtonHighlight();");
			javascript::End();			
		}

		
	}
	
	public function MultiUpload($filefield,$path,$sortfield='',$seteach='',$imagesonly=false,$validationfunction='',$file_types=false)
	{
		//variables to pass in url.
  		if(!$seteach)	$seteach=array();
		$params=array();
	  	foreach($seteach as $k=>$v)
	  	{
	  		$params[]='seteach[]='.$k;
	  		$params[]='seteachas[]='.$v;
		}
		$params[]='imagesonly='.($imagesonly?1:0);
		$params[]='validationfunction='.$validationfunction;
		$params[]='filefield='.$filefield;
		$params[]='path='.$path;
		$params[]='sortfield='.$sortfield;
		$params[]='tablename='.$this->tablename;
		$params[]='primary='.$this->primary;
		$params[]='classname='.$this->classname;
		$params[]='where='.$this->where;
		$params[]='order='.$this->order;

		//where to send it... overridable via set flag before call.
		if(!$this->GetFlag('MULTIUPLOAD_PROCESSURL'))
			$this->SetFlag('MULTIUPLOAD_PROCESSURL',_navigation::GetBaseURL().'fUpload/process_upload.php');			

		fUpload::SetPath(_navigation::GetBaseURL().'fUpload/');		
		fUpload::IncludeAssets();

		//the fupload form.
	  	$upl=new fupload($this->GetFlag('MULTIUPLOAD_PROCESSURL').'?'.implode('&',$params));
		if($imagesonly)
		  	$upl->ImageMode(1000,1000);
	  	else if($file_types)
	  		$upl->params['file_types']=$file_types;
		  echo("<div class=edit_actions' align='center'>");
	  	$upl->Draw(650,650,$this->GetFieldName('fupload'));
	  	echo("</div>");
	}		

	public function SaveMultiUpload($filefield,$path,$sortfield='',$seteach='',$imagesonly=false,$validationfunction='')
	{
		global $HTTP_POST_VARS,$HTTP_GET_VARS;

		$debug=false;
 		if($debug)
		{
			$debug=fopen('debug.txt','a');
			fwrite($debug,"UPLOAD ".date('h:i:s')."\r\n\r\n");			
			foreach($HTTP_GET_VARS as $k=>$v)
				fwrite($debug,"HTTP_GET_VARS[".$k."]=".$v."\r\n\r\n");
			foreach($HTTP_POST_VARS as $k=>$v)
				fwrite($debug,"HTTP_POST_VARS[".$k."]=".$v."\r\n\r\n");
			fwrite($debug,"this - table ".$this->tablname."\r\n");
			fwrite($debug,"this - primary ".$this->primary."\r\n");
			fwrite($debug,"this - class ".$this->classname."\r\n");
			fwrite($debug,"this - where ".$this->where."\r\n");
			fwrite($debug,"this - order ".$this->order."\r\n");
			fwrite($debug,"passed - filefield ".$filefield."\r\n");
			fwrite($debug,"passed - sortfield ".$sortfield."\r\n");
			fwrite($debug,"passed - imagesonly ".$imagesonly."\r\n");
			fwrite($debug,"passed - validationpublic function ".$validationfunction."\r\n");
			fwrite($debug,"passed - path ".$path."....: ".file::GetPath($path)."\r\n");
			foreach($seteach as $k=>$v)
				fwrite($debug,"passed - seteach ".$k." ::: ".$v."\r\n");
		}
		
		$filename=fupload::DefaultProcess(file::GetPath($path),true,true);
		if($filename)
		{
		  	$newitem=new $this->classname();
		  	$newitem->SetFlag('MULTIUPLOAD');
		  	$newitem->Set($filefield,$filename);
			if($sortfield)
		  		$newitem->Set($sortfield,$this->GetTotalAvailable()+1);
			foreach($seteach as $k=>$v)
			  	$newitem->Set($k,$v);
		  	$newitem->Update();
			if(!$delete and $validationfunctions and !$newitem->$validationfunction())
				$delete=true;

			if($debug)
			{
				fwrite($debug,"FILE ".$filename."\r\n");
				foreach($newitem->attributes as $k=>$v)
					fwrite($debug,"ITEM.".$k." ::: ".$v."\r\n");
				if($delete)
					fwrite($debug,"FAILURE.DELETE.\r\n");
				else
					fwrite($debug,"SUCCESS.\r\n");
				foreach($newitem->errors as $k=>$v)
					fwrite($debug,"ERROR.".$k." ::: ".$v."\r\n");
			}

			if(!count($newitem->GetErrors()))
				fupload::ReportSuccess();
			foreach($newitem->GetErrors() as $er)
				fupload::ReportError($er);

			if($delete)
				$newitem->Delete();
		}
	}

	
	public function jMultiUpload($filefield,$path,$sortfield='',$seteach='',$imagesonly=false,$validationfunction='')
	{
		//variables to pass in url.
  		if(!$seteach)	$seteach=array();
		$params=array();
	  	foreach($seteach as $k=>$v)
	  	{
	  		$params[]='seteach[]='.$k;
	  		$params[]='seteachas[]='.$v;
		}
		$params[]='imagesonly='.($imagesonly?1:0);
		$params[]='validationfunction='.$validationfunction;
		$params[]='filefield='.$filefield;
		$params[]='path='.$path;
		$params[]='sortfield='.$sortfield;
		$params[]='tablename='.$this->tablename;
		$params[]='primary='.$this->primary;
		$params[]='classname='.$this->classname;
		$params[]='where='.$this->where;
		$params[]='order='.$this->order;

		//where to send it... overridable via set flag before call.
		if(!$this->GetFlag('MULTIUPLOAD_PROCESSURL'))
			$this->SetFlag('MULTIUPLOAD_PROCESSURL',_navigation::GetBaseURL().'jUpload/process_upload.php');			

		jUpload::SetPath(_navigation::GetBaseURL().'jUpload/');		

		//the jupload form.  Some items hard-coded.  This is ok, as this is the site-wide appearance level abstraction.  Change per implementation.	  		
	  	$upl=new jUpload($this->GetFlag('MULTIUPLOAD_PROCESSURL').'?'.implode('&',$params));
		if($imagesonly)
		  	$upl->ImageMode(1000,1000);
	  	echo("<div class=edit_actions' align='center'>");
	  	$upl->Draw(760,650);
	  	echo("</div>");
	}		

	public function jSaveMultiUpload($filefield,$path,$sortfield='',$seteach='',$imagesonly=false,$validationfunction='')
	{

//		$debug=fopen('debug.txt','a');
//		fwrite($debug,"UPLOAD ".date('h:i:s')."\r\n\r\n");
//		fwrite($debug,"this - table ".$this->tablname."\r\n");
//		fwrite($debug,"this - primary ".$this->primary."\r\n");
//		fwrite($debug,"this - class ".$this->classname."\r\n");
//		fwrite($debug,"this - where ".$this->where."\r\n");
//		fwrite($debug,"this - order ".$this->order."\r\n");
//		fwrite($debug,"passed - filefield ".$filefield."\r\n");
//		fwrite($debug,"passed - sortfield ".$sortfield."\r\n");
//		fwrite($debug,"passed - imagesonly ".$imagesonly."\r\n");
//		fwrite($debug,"passed - validationpublic function ".$validationfunction."\r\n");
//		fwrite($debug,"passed - path ".$path."....: ".file::GetPath($path)."\r\n");
//		foreach($seteach as $k=>$v)
//			fwrite($debug,"passed - seteach ".$k." ::: ".$v."\r\n");
		

		global $_FILES;
		foreach($_FILES as $idx=>$f)  
		{
//			fwrite($debug,"FILE ".$idx."\r\n");

		  	$newitem=new $this->classname();
		  	$newitem->SetFlag('MULTIUPLOAD');
		  	$newitem->GatherFile($idx,$filefield);
			if($sortfield)
		  		$newitem->Set($sortfield,$this->GetTotalAvailable()+1);
			foreach($seteach as $k=>$v)
			  	$newitem->Set($k,$v);
		  	$newitem->Update();

		  	if(!$newitem->SaveFile($filefield,file::GetPath($path),'_'.$newitem->id,$imagesonly?array('gif','jpg','png','jpeg'):array()))
				$delete=true;
			if(!$delete and $validationfunction and !$newitem->$validationfunction())
				$delete=true;

//			foreach($newitem->attributes as $k=>$v)
//				fwrite($debug,"ITEM.".$k." ::: ".$v."\r\n");
//			if($delete)
//				fwrite($debug,"FAILURE.DELETE.\r\n");
//			else
//				fwrite($debug,"SUCCESS.\r\n");
//			foreach($newitem->errors as $k=>$v)
//				fwrite($debug,"ERROR.".$k." ::: ".$v."\r\n");

			if(!count($newitem->GetErrors()))
				jUpload::ReportSuccess();
			foreach($newitem->GetErrors() as $er)
				jUpload::ReportError($er);

			if($delete)
				$newitem->Delete();
		}
		if(!count($_FILES))
			jUpload::ReportError('No File Uploaded');
	}
};

class DBTableEX extends DBRowSetEX
{
  	public function DBTableEX($tablename,$primary,$classname,$order='')
	{
		parent::DBRowSetEX($tablename,$primary,$classname,'1',$order);  
	}

};

?>