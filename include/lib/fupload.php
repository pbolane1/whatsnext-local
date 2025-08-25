<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


class fUpload
{
	var $params;
	static function fUpload($process_url,$params=array())
	{
		$this->params=array();		
        $this->params['process_url']=$process_url;
        $this->params['process_url_params']=$params;
        $this->params['file_types']=array('*.*');
        $this->params['file_type_names']='All Files';
        $this->params['max_file_count']=1000;
        $this->params['max_filesize']='32'; //in MB
        $this->params['max_image_width']=5000;
        $this->params['max_image_height']=5000;
		$this->params['chunksize']='16';//mb
	}

	static function SetPath($path)
	{
		file::SetPath($path,'fupload_codebase');
	}

	static function GetPath()
	{
		$path=file::getPath('fupload_codebase');	  
		return $path?$path:'/fUpload/';
	}
  
	static function IncludeAssets()
	{
		echo('<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" />');
		echo('<link rel="stylesheet" href="'.fupload::GetPath().'js/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />');
		echo('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>');
		echo('<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>');
		echo('<script type="text/javascript" src="'.fupload::GetPath().'js/plupload.full.min.js"></script>');
		echo('<script type="text/javascript" src="'.fupload::GetPath().'js/jquery.ui.plupload/jquery.ui.plupload.js"></script>');		
	}

  	static function ImageMode($rw='',$rh='',$mode='')
  	{
        $this->params['file_types']=array('jpg','gif','png');
        $this->params['file_type_names']="Image Files";
        
      	$this->params['max_image_width']=$rw;
        $this->params['max_image_height']=$rh; 
	}
  
  	static function Set($key,$value='')
  	{
		$this->params[$key]=$value;
	}
  
  	static function Draw($w='',$h='',$name='uploader')
  	{
  	 	$url_params=array();
 		foreach($this->params['process_url_params'] as $k=>$v)
			$url_params[]='"'.$k.'" : "'.$v.'"';
		$url_params=implode(',',$url_params);

		echo('<div id="'.$name.'"><p>Uploader Unavailable - please use a browser that supports Flash, Silverlight and HTML5.</p></div>');
 	 
		echo('
			<script type="text/javascript">
			// Initialize the widget when the DOM is ready
			$(function() {
				$("#'.$name.'").plupload({
					// General settings
					runtimes : "html5,flash,silverlight,html4",
					url : "'.$this->params['process_url'].'",
			
					// User can upload no more then 20 files in one go (sets multiple_queues to false)
					max_file_count: '.$this->params['max_file_count'].',		
					chunk_size: "'.$this->params['chunksize'].'mb",
			
					// Resize images on clientside if we can
					resize : {
						width : '.$this->params['max_image_width'].', 
						height : '.$this->params['max_image_height'].', 
						quality : 100,
						crop: false // crop to exact dimensions
					},
					
					filters : {
						// Maximum file size
						max_file_size : "'.$this->params['max_filesize'].'mb",
						// Specify what files to browse for
						mime_types: [{title : "'.$this->params['file_type_names'].'", extensions : "'.implode(',',$this->params['file_types']).'"}]
					},
			
					// Rename files by clicking on their titles
					rename: true,
					
					// Sort files
					sortable: true,
			
					// Enable ability to dragndrop files onto the widget (currently only HTML5 supports that)
					dragdrop: true,
			
					// Views to activate
					views: {
						list: true,
						thumbs: true, // Show thumbs
						active: "thumbs"
					},

					multipart_params : {'.$url_params.'},
					// Flash settings
					flash_swf_url : "'.fupload::GetPath().'js/Moxie.swf",
			
					// Silverlight settings
					silverlight_xap_url : "'.fupload::GetPath().'js/Moxie.xap"
				});
			});
		</script>');
		
	}
  
  	static function DefaultProcess($path='',$unique_name=false,$report=true)
  	{
		// Settings
		$targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
		if($path)
			$targetDir = $path;
		$cleanupTargetDir = false; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds

		// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;		
		
		// Get a file name
		if (isset($_REQUEST["name"]))
			$fileName = $_REQUEST["name"];
		elseif (!empty($_FILES))
			$fileName = $_FILES["file"]["name"];
		else
			$fileName = uniqid("file_");
	  	if($unique_name and (!$chunks || $chunk == $chunks - 1)) 	
		  	$fileName=mktime().'_'.$fileName;
		$fileName=file::RemoveIllegalCharacters($fileName);
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
		
		
		// Remove old temp files	
		if ($cleanupTargetDir) 
		{
			if (!is_dir($targetDir) || !$dir = opendir($targetDir))
				fupload::ReportError(100,"Failed to open temp directory.");
			while (($file = readdir($dir)) !== false) 
			{
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;		
				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}.part")
					continue;
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge))
					@unlink($tmpfilePath);
			}
			closedir($dir);
		}	
		// Open temp file
		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb"))
			fupload::ReportError(102,"Failed to open output stream.");
		if (!empty($_FILES)) 
		{
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) 
				fupload::ReportError(103,"Failed to move uploaded file.");
			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb"))
				fupload::ReportError(101,"Failed to open input stream.");
		} 
		else if (!$in = @fopen("php://input", "rb")) 
			die();		
		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}
		
		@fclose($out);
		@fclose($in);
		
		// Check if file has been uploaded
		// Strip the temp .part suffix off 
		$done=false;
		if (!$chunks || $chunk == $chunks - 1)
		{
			rename($filePath.".part", $filePath);
			$done=true;
		}

		// Return Success JSON-RPC response
		fupload::ReportSuccess();

		if($done)
			return($fileName);
	}

	static function ReportError($code,$err)
	{
		echo '{"jsonrpc" : "2.0", "error" : {"code": '.$code.', "message": "'.$err.'"}, "id" : "id"}';
	}

	static function ReportSuccess()
	{
		echo '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';		
	}
};