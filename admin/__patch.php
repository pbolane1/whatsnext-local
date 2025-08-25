<?php include('../include/common.php') ?>
<?php include('../include/_admin.php') ?>
<?php include('include/wysiwyg_settings.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Site Administration</title>
	<?php include('../modules/head.php');?>
	<?php include('modules/head.php');?>	
</head>
<body class='admin'>
	<?php include ('modules/header.php');?>
	<div class='content_area'>	
		<div class='container'>
			<div class='content_inner'>	
<?php

/*
		$user_contacts=new DBRowSetEX('user_contacts','user_contact_id','user_contact',"1");
		$user_contacts->Retrieve();
		foreach($user_contacts->items as $user_contact)
		{
			echo($user_contact->Get('user_contact_name').'<br>');
			$settings=json_decode($user_contact->Get('user_contact_settings'),true);
			var_dump($settings);
			echo("<br>");
			$settings['notifications']['other']=0;
			$settings['notifications']['user']=1;
			var_dump($settings);
			echo("<br>");
			echo("<br>");
			$user_contact->Set('user_contact_settings',json_encode($settings));		
			$user_contact->Update();
		}
*/

/*
		$agents=new DBRowSetEX('agents','agent_id','agent',"1");
		$agents->Retrieve();
		foreach($agents->items as $agent)
		{
			echo($agent->Get('agent_name').'<br>');
			$settings=json_decode($agent->Get('agent_settings'),true);
			var_dump($settings);
			echo("<br>");
			$settings['notifications']['user']=0;
			$settings['notifications']['other']=1;
//			$settings['notifications']['agent']=1;
			var_dump($settings);
			echo("<br>");
			echo("<br>");
			$agent->Set('agent_settings',json_encode($settings));		
			$agent->Update();
		}

*/


?>	

			</div>
		</div>
	</div>


	<?php include ('modules/footer.php');?>
	<?php include ('../modules/footer_scripts.php');?>
</body>
</html>