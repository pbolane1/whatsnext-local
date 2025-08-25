<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/

	class DBForm
	{
	 	 
		//actions, etc...
		var $action_parameter='action';

		var $form_method='post';
		var $file_upload=false;   //can upload files for this class
		var $preserve_globals='';
		var $flags;

		public function __construct()
		{
			$this->preserve_globals=array();		  
			$this->flags=array();
		}

		public function Begin($act)
		{
			form::Begin("?".$this->action_parameter."=".$this->GetFormAction($act).$this->GetFormExtraParams(),$this->form_method,$this->file_upload);		
		}
		
		public function End()
		{
			form::End();
		}
		
		public function GetFieldName($field)
		{
			return $this->GetUniqueName($field);
	 	}
		 
		public function GetFormAction($act)
		{
			return $this->GetUniqueName($act);
	 	}
		 
		public function GetUniqueName($for)
		{
			//override this.
		}

		//////////////////////////////////////////////////////
		//
		//      Flagging Routines
		//

		public function SetFlag($which,$value=true)
		{
			$this->flags[$which]=$value;
		}

		public function ClearFlag($which)
		{
			$this->flags[$which]=false;
		}

		public function GetFlag($which)
		{
			return $this->flags[$which];
		}

		public function GetFlags()
		{
			return $this->flags;
		}

		//////////////////////////////////////////////////////
		//
		//      Class/instance configuration
		//
		
		//allow file uploads?  EncTepe for edit form
		public function AllowFiles($allow=true)
		{
	        $this->file_upload=$allow;
	 	}
	
		//globals to absolutely preserve in the form
		public function PreserveGlobals($pres='')
		{
			if(!is_array($pres))	$pres=explode(',',$pres);
			if(!is_array($pres))	$pres=array();
	
		  	foreach($pres as $g)
		        $this->preserve_globals[]=$g;
	 	}

		public function PreserveInputs()
		{
			foreach($this->preserve_globals	as $k)
			{
				global $$k;
				form::DrawHiddenInput($k,$$k);
			}	
		}
	
		 
		public function GetFormExtraParams($ignore='')
		{
			global $HTTP_GET_VARS;
			$strs="";
			if(!$ignore or !is_array($ignore))	$ignore=explode(',',$ignore);
			$ignore[]=$this->action_parameter;
			foreach($HTTP_GET_VARS as $k=>$v)
			{
				if(!in_array($k,$ignore))
				{
					if(!is_array($v))   $strs.="&".$k."=".$v;
					else
					{
						foreach($v as $val)
							$strs.="&".$k."[]=".$val;
					} 
				}
			}
			return $strs;
		}
		
		
  
	}
?>