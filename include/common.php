<?php
// ...existing code...

	error_reporting(E_ERROR | E_PARSE);
	ini_set('display_errors', 'on');
	//	error_reporting(E_ALL);
	
	set_include_path(".");
	//config....	
	//globalize
	$HTTP_POST_VARS = array();
	$HTTP_GET_VARS = array();
	$HTTP_SERVER_VARS = array();
	foreach ($_POST as $k => $v)
		$HTTP_POST_VARS[$k] = $v;
	foreach ($_GET as $k => $v)
		$HTTP_GET_VARS[$k] = $v;
	foreach ($_SERVER as $k => $v)
		$HTTP_SERVER_VARS[$k] = $v;
	foreach ($HTTP_POST_VARS as $k => $v)
		$$k = $v;
	foreach ($HTTP_GET_VARS as $k => $v)
		$$k = $v;
	
	//library
	require_once("lib/lib.php.inc");
	require_once("lib/base_calendar.php");
	require_once("lib/captcha.php");
	require_once("lib/fupload.php");
	
	//VERSION
	$__VERSION__="v0.2.2";
	
	//DEV?
	$__DEV__ = strpos(_navigation::GetBaseURL(), 'dev.');

	
	//stripe
	define('STRIPE_PUBLIC_KEY', 'YOUR_STRIPE_PUBLIC_KEY_HERE');
	define('STRIPE_PRIVATE_KEY', 'YOUR_STRIPE_PRIVATE_KEY_HERE');
	require(_navigation::GetBasePath() . '/include/stripe/init.php');
	\Stripe\Stripe::setApiKey(STRIPE_PRIVATE_KEY);
	
	//Twillio
	require_once('Twilio/autoload.php');
	use Twilio\Rest\Client;
	define('TWILLIO_SID', 'YOUR_TWILLIO_SID_HERE');
	define('TWILLIO_KEY', 'YOUR_TWILLIO_KEY_HERE');
	define('TWILLIO_NUMBER', 'YOUR_TWILLIO_NUMBER_HERE');
	
	//connect to the database
	if ($__DEV__)
		database::connect('localhost', 'YOUR_DB_USER', 'YOUR_DB_PASSWORD', 'YOUR_DB_NAME_DEV');
	else
		database::connect('localhost', 'YOUR_DB_USER', 'YOUR_DB_PASSWORD', 'YOUR_DB_NAME');
	
	//database::query("set session sql_mode=''");
		
	//library extensions
	php::RequireAllOnce(_navigation::GetBasePath() . "include/ex/");
	
	//traits
	php::RequireAllOnce(_navigation::GetBasePath() . "include/traits/");
	
	//local classes
	php::RequireAllOnce(_navigation::GetBasePath() . "include/classes/");

	//php mailer.	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	require _navigation::GetBasePath()."include/lib/phpMailer/src/Exception.php";
	require _navigation::GetBasePath()."include/lib/phpMailer/src/PHPMailer.php";
	require _navigation::GetBasePath()."include/lib/phpMailer/src/SMTP.php";

	//----------CHARACTER SET & CONFIG----------------------------------------------------------///
	ini_set('default_charset', 'utf-8');
		//mysql_set_charset('utf8', $link);
	date_default_timezone_set('America/Los_Angeles');
	
	//----------PERFORMANCE LOGGING---------------------------------------------------------------------///
	$performance_log = new performance_log();
	register_shutdown_function(function () {
		global $performance_log;
		$performance_log->Commit();
	});
	
	
	//php mode.
	php::setMode('LARGEFILES');
	php::Set('max_execution_time', 60); //60 secs max, for dev / endless looops
	php::Set("session.cookie_lifetime", 3600 * 24 * 2); //one day.
	php::Set('session.gc_maxlifetime', 3600 * 24 * 2); //one day.
	date_default_timezone_set('America/Los_Angeles');
	session_set_cookie_params(3600 * 24 * 2);
	
	//use image magick
	imaging::UseImageMagick();
	imagemagick::SetPath('/usr/bin/'); 	
	
	//paths
	file::SetPath(_navigation::GetBasePath() . 'dynamic/images/users/', 'user_upload');
	file::SetPath(_navigation::GetBaseURL() . 'dynamic/images/users/', 'user_display');
	file::SetPath(_navigation::GetBasePath() . 'dynamic/images/agents/', 'agent_upload');
	file::SetPath(_navigation::GetBaseURL() . 'dynamic/images/agents/', 'agent_display');
	file::SetPath(_navigation::GetBasePath() . 'dynamic/images/coordinators/', 'coordinator_upload');
	file::SetPath(_navigation::GetBaseURL() . 'dynamic/images/coordinators/', 'coordinator_display');
	file::SetPath(_navigation::GetBasePath() . 'dynamic/files/features/', 'feature_upload');
	file::SetPath(_navigation::GetBaseURL() . 'dynamic/files/features/', 'feature_display');
	file::SetPath(_navigation::GetBasePath() . 'dynamic/files/timeline_items/', 'timeline_item_upload');
	file::SetPath(_navigation::GetBaseURL() . 'dynamic/files/timeline_items/', 'timeline_item_display');
	file::SetPath(_navigation::GetBasePath() . 'dynamic/files/animations/', 'animation_upload');
	file::SetPath(_navigation::GetBaseURL() . 'dynamic/files/animations/', 'animation_display');
	file::SetPath(_navigation::GetBasePath() . 'dynamic/files/sounds/', 'sound_upload');
	file::SetPath(_navigation::GetBaseURL() . 'dynamic/files/sounds/', 'sound_display');
	
	file::SetPath(_navigation::GetBasePath() . 'images/flares/', 'flares');
	file::SetPath(_navigation::GetBasePath() . 'sounds/flares/', 'sound_flares');
	
	file::SetPath(_navigation::GetBasePath() . 'dynamic/temp/', 'temp');	
	file::SetPath(_navigation::GetBasePath() . 'admin/temp/', 'admin_temp');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_template.html', 'email_template');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_admin_password.txt', 'email_admin_password');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_agent_password.txt', 'email_agent_password');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_user_password.txt', 'email_user_password');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_user_contact_password.txt', 'email_user_contact_password');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_coordinator_password.txt', 'email_coordinator_password');	
	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_user_contact_notifications.html', 'email_user_contact_notifications');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_agent_notifications.html', 'email_agent_notifications');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_user_contact_reminders.html', 'email_user_contact_reminders');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_agent_reminders.html', 'email_agent_reminders');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_user_contact_notices.html', 'email_user_contact_notices');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_user_contact_login_reminder.html', 'email_user_contact_login_reminder');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_agent_general.html', 'email_agent_general');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_agent_login_reminder.html', 'email_agent_login_reminder');	
	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_key_dates.html', 'email_key_dates');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_agent_welcome.html', 'email_agent_welcome');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_user_welcome.html', 'email_user_contact_welcome');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_coordinator_welcome.html', 'email_coordinator_welcome');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_activity_log.html', 'email_activity_log');	
	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_agent_tc_invite.html', 'email_agent_tc_invite');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_new_agent_tc_invite.html', 'email_new_agent_tc_invite');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_cordinator_invite_accepted.html', 'email_cordinator_invite_accepted');	
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_cordinator_invite_declined.html', 'email_cordinator_invite_declined');	
	
	
	file::SetPath(_navigation::GetBasePath() . 'content_templates/agent_terms_of_service.html', 'agent_terms_of_service');	
	file::SetPath(_navigation::GetBasePath() . 'content_templates/user_terms_of_service.html', 'user_terms_of_service');	
	file::SetPath(_navigation::GetBasePath() . 'content_templates/welcome_email_notice.html', 'welcome_email_notice');	
	file::SetPath(_navigation::GetBasePath() . 'content_templates/key_dates_notice.html', 'key_dates_notice');	
	file::SetPath(_navigation::GetBasePath() . 'content_templates/add_to_calendar_info.html', 'add_to_calendar_info');	
	
	
	//email.
	email::SetEmail('noreply@whatsnext.realestate');
	file::SetPath(_navigation::GetBasePath() . 'email_templates/email_contact.txt', 'email_contact');

	session_id($HTTP_GET_VARS[Session::GetIDName()]);
	Session::Start();	

	//login cues
	if($HTTP_GET_VARS['user_contact_hash'])
	{
		$rec=database::fetch_array(database::query("SELECT user_id,user_contact_id FROM user_contacts WHERE MD5(CONCAT('user_contact',user_contact_id))='".$HTTP_GET_VARS['user_contact_hash']."'"));
		$HTTP_GET_VARS['user_contact_id']=$rec['user_contact_id'];
	}
	if($HTTP_GET_VARS['user_hash'])
	{
		$rec=database::fetch_array(database::query("SELECT user_id FROM users WHERE MD5(CONCAT('user',user_id))='".$HTTP_GET_VARS['user_hash']."'"));
		$HTTP_GET_VARS['user_id']=$rec['user_id'];
	}
	if($HTTP_GET_VARS['agent_hash'])
	{
		$rec=database::fetch_array(database::query("SELECT agent_id FROM agents WHERE MD5(CONCAT('agent',agent_id))='".$HTTP_GET_VARS['agent_hash']."'"));
		$HTTP_GET_VARS['agent_id']=$rec['agent_id'];
	}
	if($HTTP_GET_VARS['coordinator_hash'])
	{
		$rec=database::fetch_array(database::query("SELECT coordinator_id FROM coordinators WHERE MD5(CONCAT('coordinator',coordinator_id))='".$HTTP_GET_VARS['coordinator_hash']."'"));
		$HTTP_GET_VARS['coordinator_id']=$rec['coordinator_id'];
	}

	//defines
	define('REQUIRED',"<span class='required'> * </span>");	



?>