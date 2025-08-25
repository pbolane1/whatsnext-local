<?php
/***************************************************\
*
*	POCO TECHNOLOGIES LLC CORE LIBRARY
*	
*	AUTHOR: Paul Siebels PoCo Technolgies LLC
*	EMAIL: paul@pocotechnology.com
*
\***************************************************/


class jUpload
{
	var $params;
	static function jUpload($process_url)
	{
		$this->params=array();
		
        $this->params['uc_uploadUrl']=$process_url;


		//http://www.jumploader.com/api/jmaster/jumploader/model/api/config/UploaderConfig.html
		
		$this->params['uc_addImagesOnly']='false';//          shows whether images only allowed
		$this->params['uc_COMPRESSION_MODE_ZIP_ON_ADD'];//          zip file will be created for each file added to the queue
		$this->params['uc_compressionMode'];//          compression mode, see constants
		$this->params['uc_cookie'];//          cookie
		$this->params['uc_directoriesEnabled'];//          shows whether folder addition enabled (will expand and add all files)
		$this->params['uc_duplicateFileEnabled'];//          duplicate files enabled
		$this->params['uc_ERROR_RESPONSE_PREFIX']='ERROR';// error response token
		$this->params['uc_fileNamePattern'];//          allowed file name (not path) regex pattern, null for all
		$this->params['uc_fileParameterName'];//          file parameter name (for POST request)
		$this->params['uc_httpUploaderClassName'];//          http uploader class name
		$this->params['uc_imageEditorEnabled'];//          shows whether image editor is enabled
		$this->params['uc_maxFileLength']=-1;//          max file length allowed (per file), -1 if unlimited
		$this->params['uc_maxFiles']=-1;//          max files in a list, -1 if unlimited
		$this->params['uc_maximumImageDimension'];//          maximum image size allowed ({width}x{height})
		$this->params['uc_maxLength']=-1;//          max files length allowed (total), -1 if unlimited
		$this->params['uc_maxTransferRate'];//          maximum transfer rate (bytes/sec)
		$this->params['uc_minFileLength'];//          min file length allowed (per file), -1 if unlimited
		$this->params['uc_minFiles'];//          min files in a list, -1 if unlimited
		$this->params['uc_minimumImageDimension'];//          minimum image size allowed ({width}x{height})
		$this->params['uc_PARAM_FILE_ID'];//          file id parameter name
		$this->params['uc_PARAM_FILE_LENGTH'];//          file length parameter name
		$this->params['uc_PARAM_FILE_NAME'];//          file name parameter name
		$this->params['uc_PARAM_FILE_PATH'];//          file path parameter name
		$this->params['uc_PARAM_MD5'];//          MD5
		$this->params['uc_PARAM_PARTITION_COUNT'];//          partition count
		$this->params['uc_PARAM_PARTITION_INDEX'];//          partition index parameter name
		$this->params['uc_PARAM_PARTITION_MD5'];//          partition MD5
		$this->params['uc_partitionLength'];//          partitionLength
		$this->params['uc_PROPERTY'];//          property file
		$this->params['uc_requestEncoding'];//          request encoding to use (UTF-8 by default)
		$this->params['uc_resumeCheckUrl'];//          resume check url
		$this->params['uc_scaledInstanceDimensions'];//          scaled instance dimensions (comma separated, for example "100x100,200x200,400x400")
		$this->params['uc_scaledInstanceNames'];//          scaled instance names (comma separated, e.g.
		$this->params['uc_scaledInstanceQualityFactors'];//          scaled instance quality factors (0-worse quality, 1000-best), (comma spearated, fo example, "900,800,700");
		$this->params['uc_sendFileLastModified'];//          shows whether lastModified attribute should be send for a file
		$this->params['uc_sendFilePath'];//          send file path
		$this->params['uc_stretchImages'];//          shows whether can resize smaller images to bigger dimension
		$this->params['uc_uploadOriginalImage'];//          shows whether original image should be uploaded along with scaled images, valid only if uploadScaledImages=true
		$this->params['uc_uploadQueueReorderingAllowed'];//          shows whether upload queue reardering allowed (false by default)
		$this->params['uc_uploadScaledImages'];//          shows whether scaled images should be uploaded.
		$this->params['uc_uploadThreadCount'];//          upload thread count
		$this->params['uc_uploadUrl'];//          upload url
		$this->params['uc_urlEncodeParameters'];//          shows whether filename parameters sent to server should be urlencoded
		$this->params['uc_useMainFile'];//          shows whether main file should be used
		$this->params['uc_useMd5'];//          use MD5 hash, if true, MD5 value will be send with last partition upload request
		$this->params['uc_usePartitio'];//          use MD5 hash for each partition, if true, MD5 value for current partition will be send with each partition upload request
		$this->params['uc_userAgent'];//          user agent
		$this->params['uc_zipDirectoriesOnAdd '];//		  add directory as zip flag
		
		$this->params['gc_loggingLevel']="DEBUG";//          user agent

		$this->params['ac_fireUploaderFileStatusChanged']='true';
		$this->params['ac_fireUploaderStatusChanged']='true';
		$this->params['ac_fireUploaderFileRemoved']='true';
		$this->params['ac_fireUploaderFileAdded']='true';
		$this->params['ac_fireUploaderFilesReset']='true';
		/*
						
		<param name="gc_loggingLevel" value="DEBUG"/>
		
		<param name="uc_uploadThreadCount" value="1"/>
		<param name="uc_maxFiles" value="10"/>
		<param name="uc_maxFileLength" value="5000000"/>
		<param name="uc_maxLength" value="100000000"/>
		<param name="uc_fileNamePattern" value=""/>
		<param name="uc_directoriesEnabled" value="true"/>
		<param name="uc_duplicateFileEnabled" value="false"/>
		<param name="uc_fileParameterName" value="file"/>
		<param name="uc_fileNamePolicy" value="random"/>
		<param name="uc_logServerResponse" value="true"/>
		<param name="uc_partitionLength" value="10000000"/>
		
		<param name="vc_lookAndFeel" value="crossPlatform"/>
		<param name="vc_localFilesViewEnabled" value="false"/>
		<param name="vc_addFilesActionEnabled" value="false"/>
		<param name="vc_logoEnabled" value="true"/>
		<param name="vc_logoUrl" value="http://youserver/yourfile.png"/>
		<param name="vc_fileTreeShowFileLength" value="true"/>
		<param name="vc_fileTreeUploadSplitLocationPercent" value="50"/>
		
		<param name="ac_fireUploaderFileAdded" value="false"/>
		<param name="ac_fireUploaderFileRemoved" value="false"/>
		<param name="ac_fireUploaderFileStatusChanged" value="false"/>
		<param name="ac_fireUploaderFilesReset" value="false"/>
		<param name="ac_fireUploaderStatusChanged" value="false"/>
		*/

	}

	static function SetPath($path)
	{
		file::SetPath($path,'jupload_codebase');
	}

	static function GetPath()
	{
		$path=file::getPath('jupload_codebase');	  
		return $path?$path:'/jUpload/';
	}
  
  	static function ImageMode($rw='',$rh='',$mode='')
  	{
	 	if($rw and $rh)   
	 	{
			if($mode=='MAX')
				$this->params['uc_maximumImageDimension']=$rw.'x'.$rh; //to restirct image size...
			else if($mode=='MIN')
				$this->params['uc_minimumImageDimension']=$rw.'x,'.$rh;
			else
			{
				$this->params['uc_uploadScaledImages']='true';
				$this->params['uc_scaledInstanceNames']='original';
				$this->params['uc_scaledInstanceDimensions']=$rw.'x'.$rh;
				$this->params['uc_scaledInstanceQualityFactors']='1000';
			}
		}		
        $this->params['uc_addImagesOnly ']='true';
	}
  
  	static function Set($key,$value='')
  	{
		$this->params[$key]=$value;
	}
  
  	static function Draw($w=800,$h=600,$nojava_text='This feature requires Java 1.5 or higher')
  	{
  	  	//error trap
  	  	if(!$this->params['uc_uploadUrl'])
  	  		echo("ERROR: uc_uploadUrl required - pass in jUpload Constructor.");
  	  	else  	  	
  	  	{
  	  	  	if(!$this->params['debugfile'])
		        $this->params['debugfile']=$this->GetPath().'debug.txt';


	  		$chmod=str_replace(_navigation::GetBaseURL(),_navigation::GetBasePath(),$this->params['uc_uploadUrl']);
  	  	  	$qpos=strpos($chmod,'?');
			if($qpos)	$chmod=substr($chmod,0,$qpos);
			if(file_exists($chmod))
				chmod($chmod,0755);
						
		    //applet - assumes standard naming of applet
	        echo("<applet name='jumpLoaderApplet' code='jmaster.jumploader.app.JumpLoaderApplet.class' archive='jumploader_z.jar' codebase='".$this->GetPath()."' width='".$w."' height='".$h."' alt='' mayscript>");
			foreach($this->params as $k=>$v)
				echo("<param name='".$k."' value='".$v."' />");
			echo("</applet>");
			Javascript::Begin()	    ;
			echo("
				var jumploader_reported=new Array();
				static function uploaderFileStatusChanged( uploader, file ) {			  	
					if(file.getStatus()==2 && !jumploader_reported[file.getId()])
					{
					  	var response=file.getResponseContent();
						var er=response.indexOf('ERROR');
						if(er>-1)
						{
							var theerr='File '+file.getName()+' - '+response.substring(er,response.indexOf('...'));					
						  	jumploader_reported[file.getId()]=theerr;
						  	alert(theerr);
						}
					}
					//alert('index='+file.getIndex()+', status=' + file.getStatus() + ',content=' + file.getResponseContent()+'');
				}
			");
			
			echo("						
				static function uploaderFilesReset( uploader ) 
				{
				}
			");
			
			echo("			 
				static function uploaderFileAdded( uploader, file ) 
				{
				}
			");

			echo("
				static function uploaderFileRemoved( uploader, file ) 
				{
				}
			");
												
			echo("
				static function uploaderStatusChanged( uploader ) 
				{			   
			");
			
			if(file::GetPath('jumpload_complete_redirect'))
			{
				echo("			  
					if(uploader.getStatus()==0)
					{
						if(confirm('Upload Complete.  Click OK to proceed, Cancel to upload additional items'))
							document.location='".file::GetPath('jumpload_complete_redirect')."';
					}
				");					
			}
			echo("					
				}			
			");
			Javascript::End();

		}
		
	}
  
  	static function DefaultProcess($path='',$unique_name=false,$report=true)
  	{
  	  	global $_FILES;
	
		//defualt path
		if(!$path)	$path=_navigation::GetBasePath().'/images/uploded/';
		
		foreach($_FILES as $item=>$file)
		{
		  	//naming...
		  	$newname=file::RemoveIllegalCharacters($file['name']);
		  	if($unique_name) 	$newname=mktime().'_'.$newname;
		  	
		  	//upload/process
			if(file::Upload($file,$path,'','',false,$newname))
			{
				if($report)
					$this->ReportSuccess();
			}
			else
				$this->ReportError(file::GetError());
		}
		if(!count($_FILES))		
			$this->ReportError('No files uploaded.');
	}

	static function ReportError($err)
	{
		echo "ERROR: ".str_replace('...','---',$err)."...";		
	}

	static function ReportSuccess()
	{
		echo "SUCCESS";		
	}
};
/*

//sampe code for chunking..../
//TODO: adapt and deply.
//----------------------------------------------
//    partitioned upload file handler script
//----------------------------------------------

//
//    specify upload directory - storage
//    for reconstructed uploaded files
$upload_dir = "uploaded/".$_REQUEST["category"]."/";

//
//    specify stage directory - temporary storage
//    for uploaded partitions
$stage_dir = "uploaded/stage/";

//
//    retrieve request parameters
$file_param_name = 'file';
$file_name = $_FILES[ $file_param_name ][ 'name' ];
$file_id = $_POST[ 'fileId' ];
$partition_index = $_POST[ 'partitionIndex' ];
$partition_count = $_POST[ 'partitionCount' ];
$file_length = $_POST[ 'fileLength' ];

//
//    the $client_id is an essential variable,
//    this is used to generate uploaded partitions file prefix,
//    because we can not rely on 'fileId' uniqueness in a
//    concurrent environment - 2 different clients (applets)
//    may submit duplicate fileId. thus, this is responsibility
//    of a server to distribute unique clientId values
//    (or other variable, for example this could be session id)
//    for instantiated applets.
$client_id = $_GET[ 'clientId' ];

//
//    move uploaded partition to the staging folder
//    using following name pattern:
//    ${clientId}.${fileId}.${partitionIndex}
$source_file_path = $_FILES[ $file_param_name ][ 'tmp_name' ];
$target_file_path = $stage_dir . $client_id . "." . $file_id .
    "." . $partition_index;
if( !move_uploaded_file( $source_file_path, $target_file_path ) ) {
    echo "Error:Can't move uploaded file";
    return;
}

//
//    check if we have collected all partitions properly
$all_in_place = true;
$partitions_length = 0;
for( $i = 0; $all_in_place && $i < $partition_count; $i++ ) {
    $partition_file = $stage_dir . $client_id . "." . $file_id . "." . $i;
    if( file_exists( $partition_file ) ) {
        $partitions_length += filesize( $partition_file );
    } else {
        $all_in_place = false;
    }
}

//
//    issue error if last partition uploaded, but partitions validation failed
if( $partition_index == $partition_count - 1 &&
        ( !$all_in_place || $partitions_length != intval( $file_length ) ) ) {
    echo "Error:Upload validation error";
    return;
}

//
//    reconstruct original file if all ok
if( $all_in_place ) {
    $file = $upload_dir . $client_id . "." . $file_id;
    $file_handle = fopen( $file, 'a' );
    for( $i = 0; $all_in_place && $i < $partition_count; $i++ ) {
        //
        //    read partition file
        $partition_file = $stage_dir . $client_id . "." . $file_id . "." . $i;
        $partition_file_handle = fopen( $partition_file, "rb" );
        $contents = fread( $partition_file_handle, filesize( $partition_file ) );
        fclose( $partition_file_handle );
        //
        //    write to reconstruct file
        fwrite( $file_handle, $contents );
        //
        //    remove partition file
        unlink( $partition_file );
    }
    fclose( $file_handle );
    
    // Rename File
    rename($file,$upload_dir.strtolower($file_name));
}

*/



?>