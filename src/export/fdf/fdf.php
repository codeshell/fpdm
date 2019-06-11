<?php
	
	
	/****************************************************************
	*@file fdf.php
	*@name  Generates fdf files
	*@internal fdf bridge to forge_fdf 
	*@package fpdftk
	*@date    2010-12-05                                                          
	*@author  0livier    
	*@version 2.1
	*@note 
	*	V2.1 (05.12.2010) Adaptation for FPDM
	*	V2.0 (27.10.2010) USe of URL TOOLBOX package
	* 	V1.0 (22.10.2010) First working release
	******************************************************************/
	
	require ("forge_fdf.php"); 
	
	if (!defined('URL_TOOLBOX')) die("Requires the URL_TOOLBOX package!");
	
	
	/**
	*Resolves relative pdf urls to absolute
	*
	*@note pdf paths MUST BE ABSOLUTE in the fdf file or http scheme because when path contains .. then fdf fails
	*@param String $pdf_url any url
	*@return String $url the absolute url
	**/
	function resolve_pdf_url($pdf_url) {
	//----------------------------------
		$url=resolve_url($pdf_url);
		return $url;
	} 
    
    /**
	*Generates a form definition file (fdf)
	*
	*@note error message is dumped into syslog if supported
	*@todo Expand support not only to fdf_data_strings (I don't need this feature)
	*@param String $pdf_url
	*@param Array $pdf_data the array that holds fields datas (field_name => field_value
	*@param String $output_mode 
	*	'D' : WARNING!! By default, THIS FUNCTION SENDS HTTP HEADERS! It MUST be called before 
	*   any content is spooled to the browser, or the function will fail!
	*	'S' : Return the fdf file generated as a string
	*	<fdf_file> fullpathname to where the fdf file content has to be saved.
	*@return mixed ret the return value which can be:
	*	-a boolean true when output_mode is set to 'D'
	*	-a text the fdf content when output_mode is set to 'S'
	*	-an array holding success flag with either the fdf size or the error message
	**/
	function output_fdf($pdf_url,$pdf_data,$output_mode='D') {
	//---------------------------------------------------------    
	
	   // Ensures pdf path is absolute
	   $pdf_form_url=resolve_pdf_url($pdf_url);
	   
	    // string data, used for text fields, combo boxes and list boxes
	   $fdf_data_strings=$pdf_data;
	   
	    // name data, used for checkboxes and radio buttons
		// (e.g., /Yes and /Off for true and false)
	   $fdf_data_names=array();
	   
	   //fields security and accessibility attributes
	   $fields_hidden=array();
	   $fields_readonly=array();
	   
	   
	   $fdf=forge_fdf( $pdf_form_url, 
				$fdf_data_strings,
				$fdf_data_names,
				$fields_hidden,
				$fields_readonly );
		
			
	   switch($output_mode) {
			case "D"://Send the fdf header so acrobat recognize it.
				header ("Content-Type: application/vnd.fdf");
				print $fdf;
				$ret=true;
				break;
			case "S"://String
				$ret=$fdf;
				break;
			default:// write the file out
				
				$error_fdf_access='';
				$fdf_file=$output_mode;
				$fdf_dir=dirname($fdf_file);
				
				//Paranoïd access mode with syslog in background as watchdog for errors
				if(file_exists($fdf_dir)) {
					if(is_writable($fdf_dir)) {
						if(!is_writable($fdf_file)&&false) { //Create
							$error_fdf_access="can not write fdf file ($fdf_file), disk full or missing rights?";
						}
					}else {
						$error_fdf_access="can not write into fdf's directory ($fdf_dir)";
					}
				}else {
					$error_fdf_access="can not access to fdf's directory ($fdf_dir)";
				}
				$success=false;
				if($error_fdf_access !="") {
					$err="output_fdf : Unable to create fdf file '".$fdf_file."'<br> because $error_fdf_access.";
				} else {
					if($fp=fopen($fdf_file,'w')){
						$err=fwrite($fp,$fdf,strlen($fdf));
						if(function_exists('syslog')) syslog(LOG_WARNING,"FDF file '".$output_mode."' written successfully ($err bytes)");
						$success=true;
					}else{
						$err="output_fdf : Unable to generate file '".$output_mode."', disk full or corrupted?.";
					}
					fclose($fp);					
				}
				$ret=array("success"=>$success,"return"=>$err);
				
		}
		return $ret;
	}
	

?>