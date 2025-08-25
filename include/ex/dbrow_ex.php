<?php


class DBRowEx extends DBRow
{
	public function __construct($id='')
	{
		parent::__construct($id);
	}

	public function Draw()
	{
		if($this->GetFlag('DROPSORT'))
			echo("<tr class='list_item'>");
		else if(!$this->edit_state and $this->id)
		{
//			echo("<tr><td colspan='100' class='edit_topbar'><br></td></tr>");
			echo("<tr class='edit_wrapper list_item'>");
		}
		parent::Draw();
		echo("</tr>");
 	}

	public function GenericLink($linkto='',$text='',$td=true)
    {
     	if($td)	  	echo("<td class='edit_actions'>");
		form::Begin($linkto);
		$this->PreserveInputs();
		form::DrawSubmit('',$text);
		form::end();
     	if($td)		echo("</td>");
	}

	public function CreateLink()
    {
      	echo("<td colspan='15' align='center' class='create_actions'>");
		form::Begin("?".$this->action_parameter."=".$this->GetFormAction('edit').$this->GetFormExtraParams());
		$this->PreserveInputs();
		form::DrawSubmit('','New...');
		form::end();
      	echo("</td>");
	}
	
	public function AjaxHiddenInputs($which='')
	{
		//more via override. 
	  	form::DrawHiddenInput('where',$this->GetFlag('DROPSORT_WHERE'));
	  	form::DrawHiddenInput('limit',$this->GetFlag('DROPSORT_LIMIT'));	
	  	form::DrawHiddenInput('start',$this->GetFlag('DROPSORT_START'));	
	  	form::DrawHiddenInput('order',$this->GetFlag('DROPSORT_ORDER'));
	  	form::DrawHiddenInput('tablename',$this->GetFlag('DROPSORT_TABLENAME'));
	  	form::DrawHiddenInput('primary',$this->GetFlag('DROPSORT_PRIMARY'));
	  	form::DrawHiddenInput('classname',$this->GetFlag('DROPSORT_CLASSNAME'));
	}
	
	public function SortLink($order_field)
    {
		//**SORT**//
      	echo("<td nowrap class='sort_actions'>");
      	
//      	if($this->GetFlag('DROPSORT'))
//			echo("<div class='dropsort_dragbar'></div>");
      	
		if($this->GetFlag('NOSORT'))
			echo("-");
		else if($this->GetFlag('DROPSORT'))
		{
			form::Begin('','','',array('class'=>'dropsort_sort'));			
		  	form::DrawHiddenInput('action',$this->GetFormAction('save'));	
			$this->AjaxHiddenInputs('SORT');
			form::DrawButton('','/\\',array('onclick'=>"DropSortSetReference('".$this->GetFlag('DROPSORT_CONTAINER_ID')."');DropSortSortTo(document.getElementById('".$this->GetFieldName('dropsort')."'),parseInt(document.getElementById('".$this->GetFieldName($order_field)."').value)-2);"));
			form::DrawButton('','\\/',array('onclick'=>"DropSortSetReference('".$this->GetFlag('DROPSORT_CONTAINER_ID')."');DropSortSortTo(document.getElementById('".$this->GetFieldName('dropsort')."'),parseInt(document.getElementById('".$this->GetFieldName($order_field)."').value)+0);"));		  
			form::DrawHiddenInput($this->GetFieldName($order_field),($this->Get($order_field)),array('class'=>'dropsort_sort'));
			form::end();
		}
		else
		{
			if($this->Get($order_field)>1)
			{
				form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
				$this->PreserveInputs();
				form::DrawHiddenInput($this->GetFieldName($order_field),($this->Get($order_field)-1.01));
				form::DrawSubmit('','/\\');
				form::end();
			}
			else
			{
				form::Begin("?".$this->action_parameter."=".$this->GetFormAction('cancel').$this->GetFormExtraParams());
				$this->PreserveInputs();
				form::DrawSubmit('','/\\',array('class'=>'disabled','onclick'=>'return false;'));
				form::end();			  
			}	
			if($this->Get($order_field)<$this->parent_total)
			{
				form::Begin("?".$this->action_parameter."=".$this->GetFormAction('save').$this->GetFormExtraParams());
				$this->PreserveInputs();
				form::DrawHiddenInput($this->GetFieldName($order_field),($this->Get($order_field)+1.01));
				form::DrawSubmit('','\\/');
				form::end();
			}			
			else
			{
				form::Begin("?".$this->action_parameter."=".$this->GetFormAction('cancel').$this->GetFormExtraParams());
				$this->PreserveInputs();
				form::DrawSubmit('','\\/',array('class'=>'disabled','onclick'=>'return false;'));
				form::end();			  
			}				
		}
      	echo("</td>");
	}

	public function EditLink()
    {

      	echo("<td nowrap class='edit_actions'>");
		//**STANDARD**//
		parent::EditLink();

	}
	
	public function DeleteLink()
    {
      
		if($this->GetFlag('DROPSORT'))
		{
			form::Begin('','','',array('class'=>'dropsort_sort'));			
			form::DrawButton('','Delete',array('onclick'=>"if(confirm('Are you sure you want to permanently delete this item?')){DropSortSetReference('".$this->GetFlag('DROPSORT_CONTAINER_ID')."');AjaxSave('".$this->GetFlag('DROPSORT_AJAXURL')."',this.form);AjaxDelete(document.getElementById('".$this->GetFieldName('dropsort')."'));}return false;"));
		  	form::DrawHiddenInput('action',$this->GetFormAction('delete'));	
			$this->AjaxHiddenInputs('DELETE');
			form::End();
    	}
		else  
			parent::DeleteLink();
      	echo("</td>");
	}

	public function SaveLink()
    {
      	if($this->id)
			form::DrawSubmit('','Save');
		else
			form::DrawSubmit('','Create');		
	}
			
	public function CancelLink($params='')
    {
		form::Begin("?".$this->action_parameter."=".$this->GetFormAction('cancel').$this->GetFormExtraParams());
		$this->PreserveInputs();
		form::DrawSubmit('','Cancel');
		form::end();
      	echo("</td>");
	}
	
	public function Display()
	{
		parent::display();
 	}

	public function EditForm()
	{
		echo("<td colspan='2' align='center'></td></tr>");

		parent::EditForm();

		echo("<tr><td colspan='2' class='save_actions'>");
 	}

	public function Save()
	{
		$psv=parent::Save();
		
		global $HTTP_POST_VARS;
		$HTTP_POST_VARS['dbrs_ex_current_id']=$this->id;
		
		return $psv;
	}

	public function GetCrop($field)
	{
	 	$table_prefix='cropped_image';
		$this->CheckCropDB($table_prefix); 
	 	
	 	$crop=new DBRow();
	 	$crop->EstablishTable($table_prefix.'s',$table_prefix.'_id');
	 	if(!$this->id)
	 		return $crop;
	 	$crop->InitByKeys(array('foreign_table','foreign_field','foreign_id'),array($this->tableName,$field,$this->id));
		if(!$crop->id)
		 	$crop->CreateFromKeys(array('foreign_table','foreign_field','foreign_id','x','y','w','h'),array($this->tableName,$field,$this->id,'0','0','0','0'));
	 	

	 	return $crop;
	}
	
	public function CheckCropDB($table_prefix)
	{
		$sql="CREATE TABLE IF NOT EXISTS `".$table_prefix."s` (
				`".$table_prefix."_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`x` FLOAT NOT NULL DEFAULT '0',
				`y` FLOAT NOT NULL DEFAULT '0',
				`h` FLOAT NOT NULL DEFAULT '0',
				`w` FLOAT NOT NULL DEFAULT '0',
				`foreign_table` VARCHAR( 64 ) NOT NULL ,
				`foreign_field` VARCHAR( 64 ) NOT NULL ,
				`foreign_id` INT NOT NULL DEFAULT '0')";	  
		database::Query($sql);		
	}
	
	public function CropImage($display_path,$upload_path,$field,$w,$h,$preview=false)
	{
		$crop=$this->GetCrop($field);
	  	if($preview)
	  	{
//			$current_crop=$this->CropAsSaved($display_path,$upload_path,$field,$w,$h);
			echo("<div class='crop_preview'><div style='height:".$h."px;width:".$w."px;overflow:hidden;'><img id='".$this->GetFieldName('gallery_image_file')."_image' src='".$display_path.$this->Get($field)."'></div></div>");
		}
		list($ow,$oh)=getImageSize($upload_path.$this->Get($field));
		echo("<div class='crop_hint'>Click And Drag To Crop Image.  Highlighted portion will be displyed<br>If not manually cropped a default selection will be chosen</div>");
		echo("<div class='crop_hint'>Original Image Size:".$ow.'px x '.$oh."px</div>");
		echo("<div class='crop_hint'>Target Size:".$w.'px x '.$h."px</div>");
		echo("<div class='crop_form'>");
		echo("<br><img id='".$this->GetFieldName('crop_image')."' src='".$display_path.$this->Get($field)."'>");		
		echo("<link rel='stylesheet' href='"._navigation::GetBaseURL()."jquery.Jcrop.css' type='text/css' />");
		Javascript::IncludeJS('jquery_lib.js');//include jquery so jquery.min.js wins....
		Javascript::IncludeJS('jquery.min.js');
		Javascript::IncludeJS('jquery.Jcrop.min.js');
		Imaging::JCrop($this->GetFieldName('crop_image'),$this->GetFieldName($field)."_image",$this->GetFieldName('cropper'),array('h'=>$crop->Get('h'),'w'=>$crop->Get('w'),'x'=>$crop->Get('x'),'y'=>$crop->Get('y')),$w,$h,$w,$h);
		Imaging::JCropInvoke($this->GetFieldName('cropper'));				
		echo("</div>");
	}
	
	public function SaveCrop($field)
	{
		$res=Imaging::JCropProcess($this->GetFieldName('cropper'));
		$crop=$this->GetCrop($field);
		$crop->Set('h',$res['h']?$res['h']:'0');
		$crop->Set('w',$res['w']?$res['w']:'0');
		$crop->Set('x',$res['x']?$res['x']:'0');
		$crop->Set('y',$res['y']?$res['y']:'0');
	  	$crop->Update();
	}

	public function DeleteCrop($field)
	{
		$crop=$this->GetCrop($field);	  
		$crop->Delete();
	}

	public function CropAsSaved($display_path,$upload_path,$field,$width,$height)
	{
		$crop=$this->GetCrop($field);
		if($crop->Get('h') and $crop->Get('w'))
		{
			$new_name='cr_ex_'.$crop->Get('x').'-'.$crop->Get('y').'_'.$crop->Get('w').'x'.$crop->Get('h').'_'.$this->Get($field);
			$src_image=str_replace($upload_path,'',Imaging::CropExact($this->Get($field),$new_name,$upload_path,$crop->Get('w'),$crop->Get('h'),$crop->Get('x'),$crop->Get('y')));	  
		}
		else
			$src_image=$this->Get($field);
	
		return $src_image;	
		//return $display_path.imaging::ResizeCached($src_image,$upload_path,$width,$height,true);	
	}

	//override to by default allow overwriting existing.
	//also, auto resize file on upload.
	public function SaveImageFile($which,$path,$unique_add='',$allow_types='',$maxsize_ks='',$overwrite=true)
	{
		$ret=parent::SaveImageFile($which,$path,$unique_add,$allow_types,$maxsize_ks,$overwrite);
		if($ret)
		{
			$this->AutoResizeFile($which,$path);		  
		}
	}

	public function AutoResizeFile($which,$path,$image_max_width=1500,$image_max_height=1500)
	{	
		//filename
		$ifilename=$this->Get($which);
		//target = jpg
		//$ifilename2=str_replace(file::GetExtension($this->Get($which)),'jpg',$this->Get($which));
		$ext=file::GetExtension($this->Get($which));
		$ifilename2=str_replace('.'.$ext,'_r.'.$ext,$this->Get($which));
		
		//resize to target dimensions & name
	  	imaging::Resize($ifilename,$ifilename2,$path,$image_max_width,$image_max_height);
		//cropping is invalicdated.
//	  	$this->DeleteCrop($which);
	  	//if changed extension, need to update DB
	  	$this->Set($which,$ifilename2);
	  	$this->Update();
	}


	public static function NormalizePhone($phone)
	{
		$phone=mod_rewrite::ToURL($phone,'0-9');
		$phone=str_replace('-','',$phone);
		$phone=substr($phone,strlen($phone)-10,10);
		return $phone;
	}	

	public static function TwillioFormat($phone)
	{
	 	$phone=str_replace('-','',mod_rewrite::ToURL($phone,'0-9'));
		if(strlen($phone)<=10)
			$phone='1'.$phone;
		$phone='+'.$phone;

		return $phone;
	}	


	public function GetMailParams()
	{
		$agent=new agent($this->Get('agent_id'));
		
		$mail_params=array();
		$mail_params+=$this->attributes;
		
		return $mail_params;
	}

	//ugly but it works for now....
	public static function GetCurrentUser($ignore_proxy=false)
	{
		//who is logged in??
		$admin=new admin(Session::Get('admin_id'));
		if($admin->IsLoggedIn())
			return $admin;

		$coordinator=new coordinator(Session::Get('coordinator_id'));
		if($coordinator->IsLoggedIn())
			return $coordinator;

		$agent=new agent(Session::Get('agent_id'));
		if($agent->IsLoggedIn())
		{
			if($agent->IsProxyLogin())
				return new coordinator(Session::Get('pbt_agent_proxy_login'));

			return $agent;
		}

		$user_contact=new user_contact(Session::Get('user_contact_id'));
		if($user_contact->IsLoggedIn())
			return $user_contact;

		return false;
	}
	
	public function Login($in=true,$silent=false)
	{
		if($in)
		{
			//logour other guys.
			$admin=new admin(Session::Get('admin_id'));
			if($admin->IsLoggedIn() and get_class($this)!=='admin')
				$admin->Login(false,$silent);
	
			$coordinator=new coordinator(Session::Get('coordinator_id'));
			if($coordinator->IsLoggedIn() and get_class($this)!=='coordinator')
				$coordinator->Login(false,$silent);
	
			$agent=new agent(Session::Get('agent_id'));
			if($agent->IsLoggedIn() and get_class($this)!=='agent')
				$agent->Login(false,$silent);
	
			$user_contact=new user_contact(Session::Get('user_contact_id'));
			if($user_contact->IsLoggedIn() and get_class($this)!=='user_contact')
				$user_contact->Login(false,$silent);
		}
	}

	public function IsOwnedBy($object)
	{
		if($this->Get($object->primary)!=$object->id)
			return false;
		return true;
	}

};

?>