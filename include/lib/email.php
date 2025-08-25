<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


$_VALID_TLDS=array('com','edu','gov','int','mil','net','org','aero','biz','coop','info','museum','name','pro');
$_VALID_INTRNATIONAL_TLDS=array('ac','ad','ae','af','ag','ai','al','am','an','ao','aq','ar','as','at','au','aw','az','ba','bb','bd','be','bf','bg','bh',
								'bi','bj','bm','bn','bo','br','bs','bt','bv','bw','by','bz','ca','cc','cd','cf','cg','ch','ci','ck','cl','cm','cn','co',
								'cr','cu','cv','cx','cy','cz','de','dj','dk','dm','do','dz','ec','ee','eg','eh','er','es','et','fi','fj','fk','fm','fo',
								'fr','fx','ga','gb','gd','ge','gf','gh','gi','gl','gm','gn','gp','gq','gr','gs','gt','gu','gw','gy','hk','hm','hn','hr',
								'ht','hu','id','ie','il','in','io','iq','ir','is','it','jm','jo','jp','ke','kg','kh','ki','km','kn','kp','kr','kw','ky',
								'kz','la','lb','lc','li','lk','lr','ls','lt','lu','lv','ly','ma','mc','md','mg','mh','mk','ml','mm','mn','mo','mp','mq',
								'mr','ms','mt','mu','mv','mw','mx','my','mz','na','nc','ne','nf','ng','ni','nl','no','np','nr','nu','nz','om','pa','pe',
								'pf','pg','ph','pk','pl','pm','pn','pr','pt','pw','py','qa','re','ro','ru','rw','sa','sb','sc','sd','se','sg','sh','si',
								'sj','sk','sl','sm','sn','so','sr','st','sv','sy','sz','tc','td','tf','tg','th','tj','tk','tm','tn','to','tp','tr','tt',
								'tv','tw','tz','ua','ug','uk','um','us','uy','uz','va','vc','ve','vg','vi','vn','vu','wf','ws','ye','yt','yu','za','zm',
								'zw');

$_email_email_addresses=array();

class email
{
	//output a list of contacts to csv for import into ms outlook
	//mailing list should be $passin=array(array('Title'=>'data','First Name'=>'data',etc...),array('Title'=>'data',
	//'First Name'=>'data',etc...),array('Title'=>'data','First Name'=>'data',etc...));
	//in other words, an array containing arrays w/keys=csv datafields.
	static function ExportToCSV($mailinglist,$filename='email_database.csv')
	{
        header("Content-Type:  application/csv");
		header("Content-Disposition: attachment; filename=".$filename);
		header("Pragma: no-cache");
		header("Expires: 0");

		//list of datafields
		$datafields=array("Title","First Name","Middle Name","Last Name","Suffix","Company","Department","Job Title","Business Street","Business Street 2","Business Street 3","Business City","Business State","Business Postal Code","Business Country","Home Street","Home Street 2","Home Street 3","Home City","Home State","Home Postal Code","Home Country","Other Street","Other Street 2","Other Street 3","Other City","Other State","Other Postal Code","Other Country","Assistant\'s Phone","Business Fax","Business Phone","Business Phone 2","Callback","Car Phone","Company Main Phone","Home Fax","Home Phone","Home Phone 2","ISDN","Mobile Phone","Other Fax","Other Phone","Pager","Primary Phone","Radio Phone","TTY/TDD Phone","Telex","Account","Anniversary","Assistant\'s Name","Billing Information","Birthday","Categories","Children","Directory Server","E-mail Address","E-mail Display Name","E-mail 2 Address","E-mail 2 Display Name","E-mail 3 Address","E-mail 3 Display Name","Gender","Government ID Number","Hobby","Initials","Internet Free Busy","Keywords","Language","Location","Manager\'s Name","Mileage","Notes","Office Location","Organizational ID Number","PO Box","Priority","Private","Profession","Referred By","Sensitivity","Spouse","User 1","User 2","User 3","User 4","Web Page");


		//standard first line for MSOutlook CSV
		echo(implode(",",$datafields)."\n");

		//go throught all the people
		foreach($mailinglist as $person)
		{
			//go through datafields for ea. person
			$info=array();
			foreach($datafields as $field)
				$info[$field]=$person[$field];
			//ea. line
			echo(implode(",",$info)."\n");

		}
		
		//end output.  Thus this can be called up w/in a file as result of action for example (before html output)
		die();
	}

	static function ImportFromCSV($filename='email_database.csv')
	{
	    $contacts=array();

		$f=fopen($filename,'r');
					
		if ($f)
		{
		    set_time_limit(0);		   
		    //the top line is the field names
		    $fields = fgetcsv($f, 4096, ',');		   
		    //loop through one row at a time
		    while (($data = fgetcsv($f, 4096, ',')) !== FALSE)
		    {
		      	//map to fields
		      	$datum=array();
		      	foreach($fields as $i=>$k)
		      		$datum[$k]=$data[$i];
		        $contacts[]=$datum;
		    }		
		    //cleanup file (assumes temp file)
		    fclose($f);		    
			unlink($filename);    
		}
		return $contacts;
	}


	static function ParseFullNameForCSV($fullname)
	{
		$name_parts=array();
		$prefixes=array('mr','mrs','ms','miss','dr','sir','sr');
		$suffixes=array('jr','sr','ii','iii');

		$fullname=str_replace(".","",$fullname);
		$name_split=explode(' ',$fullname);
		$actual_parts=array();

		//capture and remove prefixes and suffixes
		foreach($name_split as $part)
		{
			if(in_array(strtolower($part),$prefixes))
				$name_parts['prefix']=$part;
			else if(in_array(strtolower($part),$suffixes))
				$name_parts['suffix']=$part;
			else
				$actual_parts[]=$part;
  		}
		if(count($actual_parts)>0)
			$name_parts['first_name']=$actual_parts[0];
		if(count($actual_parts)>1)
			$name_parts['last_name']=$actual_parts[count($actual_parts)-1];
		if(count($actual_parts)>2)
			$name_parts['middle_name']=$actual_parts[1];

		return $name_parts;
 	}


	static function ValidateEmail($email,$checkdns=true)
	{

		//search strings
	    $mail_pat = '^(.+)@(.+)$';
	    $valid_chars = "[^] \(\)<>@,;:\.\\\"\[]";
	    $atom = "$valid_chars+";
	    $quoted_user='(\"[^\"]*\")';
	    $word = "($atom|$quoted_user)";
	    $user_pat = "^$word(\.$word)*$";
	    $ip_domain_pat='^\[([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\]$';
	    $domain_pat = "^$atom(\.$atom)*$";


	    if (preg_match('/'.$mail_pat.'/i', $email, $components))
		{
			$user = $components[1];
			$domain = $components[2];
			// validate user
			if (preg_match('/'.$user_pat.'/i', $user))
			{
		        // validate domain
		        if (preg_match('/'.$ip_domain_pat.'/i', $domain, $ip_components))
				{
		          	// this is an IP address
		      	 	for ($i=1;$i<=4;$i++)
					{
		      	    	if ($ip_components[$i] > 255)
			  			    return false;
		          	}
		        }
		        else
				{
					// Domain is a name, not an IP
					if (preg_match('/'.$domain_pat.'/i', $domain))
					{
			            /* domain name seems valid, but now make sure that it ends in a valid TLD or ccTLD
			               and that there's a hostname preceding the domain or country. */
			            $domain_components = explode(".", $domain);			            
			            // Make sure there's a host name preceding the domain.
			            if (sizeof($domain_components) < 2)
			  			    return false;
						else if(false)
						{
							$top_level_domain = strtolower($domain_components[sizeof($domain_components)-1]);

			              	// Allow all 2-letter TLDs (ccTLDs)
							if (preg_match('/'.'^[a-z][a-z]$'.'/i', $top_level_domain) != 1)
							{
								$tld_pattern = '';
								// Get authorized TLDs from text file
								$tlds = email::GetTLDS();
								foreach($tlds as $tld)
								{
									// TLDs should be 3 letters or more
									if (preg_match('/'.'^[a-z]{3,}$'.'/i', $tld) == 1)
					                    $tld_pattern .= '^' . $tld . '$|';
									else if (preg_match('/'.'^[a-z]{2,}$'.'/i', $tld) == 1)
					                    $tld_pattern .= '^' . $tld . '$|';					                    
	                  			}
	     					}

			                if(!in_array($top_level_domain,email::GetTLDS()))
			                {
				                // Remove last '|'			                
								$tld_pattern = substr($tld_pattern, 0, -1);			                
				                if (preg_match('/'."$tld_pattern".'/i', $top_level_domain) == 0)
					  			    return false;
							}
						}
					}
					else
		  			    return false;
				}
			}
			else
			    return false;
		}
		else
		    return false;

		//domain/ dns check
	    if ($checkdns)
			return email::CheckDNS($domain);
		//we survived
	    return true;
	}

	static function GetTLDS()
	{
		global $_VALID_TLDS,$_VALID_INTRNATIONAL_TLDS;
		return $_VALID_TLDS+$_VALID_INTRNATIONAL_TLDS;
 	}

	static function CheckDNS($domain)
	{
		if(!function_exists('checkdnsrr'))
		    return true;
		if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A"))
			return false;
		return true;
    }

	//STATIC
	static function SetEmail($e='',$which='',$options=array())
	{
		global $_email_email_addresses,$_email_email_addresses_options;
		$_email_email_addresses[$which]=$e;
		$_email_email_addresses_options[$which]=$options;
	}
	
	static function GetEmail($which='')
	{
		global $_email_email_addresses;
		return $_email_email_addresses[$which];
	}

	static function GetEmailOptions($which='')
	{
		global $_email_email_addresses_options;
		return $_email_email_addresses_options[$which];
	}
	
	static function SetAdminEmail($e='')
	{
		Email::SetEmail($e);
	}
	
	static function GetAdminEmail()
	{
		return Email::GetEmail();
	}		

	static function SetMailer($mailer)
	{
		global $_email_mailer;
		$_email_mailer=$mailer;
	}
	
	static function GetMailer()
	{
		global $_email_mailer;
		return $_email_mailer;
	}

	static function ConvertTemplateReplacements($values,$replace_array=array())
	{
		foreach($values as $k=>$v)
		{
			if(is_array($v))
			{
				foreach($v as $k1=>$v1)
					$replace_array['<'.$k.'/>'][$k1]=email::ConvertTemplateReplacements($v1);
			}
			else
				$replace_array['<'.$k.'/>']=$v;
		}
		return $replace_array;
	}

	static function TemplateMail($to,$from,$subject,$template_path,$values,$headers='',$attach='',$returnpath=false,$options=array())
	{	  	  	
	 	//returnpath
	 	if(!$returnpath)	$returnpath=$from;

	  	//is it html?
	  	$is_html=strpos($template_path,'.htm')!==false;
	  	
	  	//attachments?
	  	if(!$attach)	$attach=array();
	  
	  	//convert values to XML replace array
	  	$replace_array=email::ConvertTemplateReplacements($values);

		//if we had a sibject in the values array use that
		if($replace_array['<subject/>'])	$subject=$replace_array['<subject/>'];
	  		
	  	//proces the template	
	  	$msg=html::ProcessTemplateFile($template_path,$replace_array);
			
		//make a plain text part...
		$ext=file::GetExtension($template_path);
		$template_path_txt=str_replace('.'.$ext,'.txt',$template_path);
		if(file_exists($template_path_txt))
		  	$txt_msg=html::ProcessTemplateFile($template_path_txt,$replace_array);
		else	
			$txt_msg=email::HTMLtoText($msg);

		//procces the headers
		if(is_array($headers))	$headers=implode("\r\n", $headers);

		if(is_array($to))
			$to=implode(',',$to);

		//mail it - html formal if html template
		if(!$is_html and !count($attach))
		{
			return mail($to,$subject,$msg,'FROM: '.$from."\r\n".$headers,'-f'.$returnpath);	  	  
		}
		else if(email::GetMailer()=='PHPMAILER')
	 	{
			//Create a new PHPMailer instance
			//$mail = new PHPMailer;
			$mail = new PHPMailer\PHPMailer\PHPMailer();
			$mail->setFrom($from);
			$mail->addReplyTo($returnpath?$returnpath:$from);
			if($options['SMTP_AUTH'])
			{
				$mail->isSMTP();
				$mail->Host = $options['SMTP_AUTH_HOST'];
				$mail->SMTPAuth = true;
				$mail->Username = $options['SMTP_AUTH_USER'];
				$mail->Password = $options['SMTP_AUTH_PASSWORD'];
				$mail->SMTPSecure = $options['SMTP_AUTH_SECURITY'];
				$mail->Port =$options['SMTP_AUTH_PORT'];
			}
			foreach(explode(',',$to) as $toa)
				$mail->addAddress($toa);
			$mail->Subject = $subject;
			$mail->Sender = $returnpath;
			$mail->msgHTML($msg);
			$mail->AltBody=$txt_msg;		
			foreach($attach as $name=>$file)
				$mail->addAttachment($file);			
			return $mail->send();
	 	}		
		else if(is_callable(email::GetMailer()))
		{
			$fn=email::GetMailer();	
			$fn($to,$from,$subject,$template_path,$values,$headers,$attach,$returnpath);
		}		
		else if(!count($attach))
		{
		 	if($headers)
		 		$headers.="\r\n";
		    $headers.="MIME-Version: 1.0\r\n";
		    $headers.="Content-type: text/html; charset=iso-8859-1\r\n";		 
			return mail($to,$subject,$msg,'FROM: '.$from."\r\n".$headers,'-f'.$returnpath);	  	  
		}
		else
		{
			$email=new mime_email();

 			//add html and/or text
			if($is_html)	$email->add_html($msg,$txt_msg);
			else			$email->add_text($msg);

			//add attachment
			foreach($attach as $name=>$file)
				$email->add_attachment($email->get_file($file),$name);

			//buildnsend.
			$email->build_message();
			$email->return_path=$returnpath;
			return $email->send('', $to,'',$from,$subject,$headers);
  		}
	}	
	
	static function HTMLtoText($content)
	{
	  	//remove newlines...
	 	$content=str_replace("\r","",$content);
	 	$content=str_replace("\n","",$content);
	  
		//remove style, script, head tags in full
		$patterns=array();
		$patterns[]='/<head.*head>/i';
		$patterns[]='/<style.*style>/i';
		$patterns[]='/<script.*script>/i';
//		$patterns[]='/<!--.*-->/i';
		foreach($patterns as $pattern)
			$content = preg_replace($pattern,'',$content); 

		//turn end-EOL type tags into textual EOL
		$patterns=array();
		$patterns[]='/<\/tr>/i';
		$patterns[]='/<\/table>/i';
		$patterns[]='/<\/div>/i';
		$patterns[]='/<\/p>/i';
		$patterns[]='/<br>/i';
		$patterns[]='/<br\/>/i';
		$patterns[]='/<hr>/i';
		$patterns[]='/<hr\/>/i';
		$patterns[]='/<\/h1>/i';
		$patterns[]='/<\/h2>/i';
		$patterns[]='/<\/h3>/i';
		$patterns[]='/<\/h4>/i';
		$patterns[]='/<\/h5>/i';
		$patterns[]='/<\/h6>/i';
		foreach($patterns as $pattern)
			$content = preg_replace($pattern,"\r\n",$content); 


		//turn space type tags into textual space
		$patterns=array();
		$patterns[]='/<\/td>/i';
		$patterns[]='/<\/th>/i';
		$patterns[]='/<\/span>/i';
		$patterns[]='/<\/td>/i';
		$patterns[]='/<\/b>/i';
		$patterns[]='/<\/i>/i';
		$patterns[]='/<\/u>/i';
		$patterns[]='/<\/em>/i';
		foreach($patterns as $pattern)
			$content = preg_replace($pattern," ",$content); 
	  
	  	//remove other tags
		$content=strip_tags($content);
		return $content;
	}

};

?>