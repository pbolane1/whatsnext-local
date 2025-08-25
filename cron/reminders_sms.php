<?php $__CRON__=1; ?>
<?php 
	//SERVER ENVIRONMENT ~ CLI.
	if(!$_SERVER['HTTP_HOST'])
	{
		$HTTP_SERVER_VARS['HTTP_HOST']='https://app.whatsnext.realestate/';
		$HTTP_SERVER_VARS['DOCUMENT_ROOT']='/home/pbolane1/public_html/app.whatsnext.realestate/';
		$HTTP_SERVER_VARS['HTTPS']='on';
		$_SERVER['HTTP_HOST']='https://app.whatsnext.realestate/';
		$_SERVER['DOCUMENT_ROOT']='/home/pbolane1/public_html/app.whatsnext.realestate/';
		$_SERVER['HTTPS']='on';
	}

	//COMMON
	require('../include/common.php');

	//ITEMS DUE WITHIN THE NEXT WEEK OR EARLIER, BUT NOT MORE THAN 7 DAYS OVERDUE

	//which timeline items need remidners?
	$date=new date();
	$date->Add(7);
	$user_ids=array(-1);
	$agent_ids=array(-1);
	$where=array(1);
	$where[]="timeline_item_active=1";
	$where[]="timeline_item_complete=0";
//	$where[]="timeline_item_date>='".$date->GetDBDate()."'";
	$date->Add(7);
	$older=new date();
	$older->Add(-7);
	$where[]="timeline_item_date>='".$older->GetDBDate()."'";
	$where[]="timeline_item_type='TIMELINE'";
	$where[]="(timeline_item_date<='".$date->GetDBDate()."' AND timeline_item_reference_date_type!='NONE')";
	$where[]="user_under_contract>0";
	$where[]="user_active=1";

  	$timeline_item_list=new DBRowSetEX('timeline_items','timeline_item_id','timeline_item',implode(' AND ',$where),'timeline_item_order');
	$timeline_item_list->join_tables='users';
	$timeline_item_list->join_where='users.user_id=timeline_items.user_id';
	$timeline_item_list->Retrieve();
	foreach($timeline_item_list->items as $timeline_item)
	{
		$user_ids[]=$timeline_item->Get('user_id');
		$agent_ids[]=$timeline_item->Get('agent_id');
	}

  	$list=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"user_id IN (".implode(',',$user_ids).")",'user_contact_name');
  	$list->Retrieve();
  	$list->Each('SendSMSReminders',array($where));

  	$list=new DBRowSetEX('agents','agent_id','agent',"agent_id IN (".implode(',',$agent_ids).")",'agent_name');
  	$list->Retrieve();
  	$list->Each('SendSMSReminders',array($where));

  	$list=new DBRowSetEX('coordinators','coordinator_id','coordinator',"1",'coordinator_name');
	$list->join_tables='agents_to_coordinators';
	$list->join_where='agents_to_coordinators.coordinator_id=coordinators.coordinator_id AND agents_to_coordinators.agent_id IN ('.implode(',',$agent_ids).')';
  	$list->Retrieve();
  	$list->Each('SendSMSReminders',array($where));


	echo('DONE');	

	/* Saves the data into a file */
	$fdw = fopen("log.txt", "a");
	fwrite($fdw, "-----".date('m/d/Y h:i:s')."-----");
	fwrite($fdw, "\r\n");
	fwrite($fdw, 'reminders_sms');
	fwrite($fdw, "\r\n");
	fclose($fdw);

?>