<?php
	trait public_user
	{
		abstract protected function HasNotifications();
		abstract protected function GetName();
		abstract protected function GetPhone();
		abstract protected function GetEmail();
		abstract protected function GetSettings();

		public function DrawFlares()
		{
			$where=array(1);
			$list=new DBRowSetEX('animations','animation_id','animation',implode(' AND ',$where),'animation_name');
			$list->Retrieve();
			$list->ListShort();
	
			$where=array(1);
			$list=new DBRowSetEX('sounds','sound_id','sound',implode(' AND ',$where),'sound_name');
			$list->Retrieve();
			$list->ListShort();
	
			echo("<div id='delete-flare' style='display:none;width:300px;height:300px;top:0px;left:0px;;margin:-150px 0px 0px -150px;position:absolute;z-index:1000'><lottie-player src='/animations/delete.json' background='transparent' speed='1' loop autoplay></lottie-player></div>");
			echo("<audio id='delete-sound' src='/animations/delete.mp3' style='display:none'></audio>");
	
			echo("<div id='congratulations-flare' style='display:none;width:100%;height:100%;top:0px;left:0px;;margin:0px 0px 0px 0px;position:fixed;z-index:1000'><lottie-player src='/animations/congratulations.json' background='transparent' speed='1' loop autoplay></lottie-player></div>");
			echo("<audio id='congratulations-sound' src='/animations/congratulations.mp3' style='display:none'></audio>");
	
	
	
	/*
			foreach(file::GetFilesInDirectory(file::GetPath('flares')) as $file)
			{
				$file=str_replace(_navigation::GetBasePath(),_navigation::GetBaseURL(),$file);
				//if(file::GetExtension($file)=='gif')
				//	echo("<div class='flare flare_gif'><img src='' data-src='".$file."'></div>");			
				if(file::GetExtension($file)=='json')
					echo("<div class='flare flare_json'><lottie-player src='".$file."' background='transparent' speed='1' loop autoplay></lottie-player></div>");
			}
			
			echo('<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>');
	*/
	/*
			foreach(file::GetFilesInDirectory(file::GetPath('sound_flares')) as $file)
			{
				$file=str_replace(_navigation::GetBasePath(),_navigation::GetBaseURL(),$file);
				echo("<audio class='flare_sound' src='".$file."' style='display:none'></audio>");
			}
	*/		
		}	 
	}	  
?>