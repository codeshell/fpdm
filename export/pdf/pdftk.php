<?php

	/****************************************************************
	*@file pdftk.php
	*@name  Generates pdf files using the pdf toolkit
	*@requires the pdftk binary os dependant placed in this same directory, see line 63 of this script.
	*@internal fdf bridge to pdftk 
	*@package fpdftk
	*@date    2010-12-06                                                         
	*@author  0livier    
	*@version 2.0
	*@note 
	*	V2.0 '06.12.2010) Add security support , first public release.
	* 	V1.0 (05.12.2010) First working release
	******************************************************************/

	if (!defined('URL_TOOLBOX')) die("Requires the URL_TOOLBOX package!");
		
	define("PHP5_ENGINE",version_compare(phpversion(), "5"));
 
	//!NOTE try to detect your OS
	
	function is_windows(){
		$PHP_OS=php_uname('s');
		return (strtoupper(substr($PHP_OS, 0, 3)) === 'WIN');
	}
	
	function is_mac() {
	//--------------
		$PHP_OS=php_uname('s');
		return (strtoupper(substr($PHP_OS, 0, 6)) === 'DARWIN'); //not tested
	}
	 
 
	/**
	*Generate randomly an unique id
	*@note this is used to fight acrobat cache
	**/
	function rnunid() {
		return md5( uniqid() );  // 32 characters long
		//$unique = sha1( uniqid() );  // 40 characters long
	}

	/**
	*@name  pdftk
	*@brief Validate with xmlint (external tool) an xml file using the schema (XML|DTD|XSD|RNG|SCH)
	*@access public 
	*@note 	This function will call pdftk/pdftk.exe like this:
	*	pdftk form.pdf fill_form data.fdf output out.pdf flatten
	*	(pdftk form.filled.pdf output out.pdf flatten is not supported)
	*
	*	 If  the  input  FDF file includes Rich Text formatted data in
	*	 addition to plain text, then the Rich	Text  data  is	packed
	*	 into  the  form fields as well as the plain text.  Pdftk also
	*	 sets a flag that cues Acrobat/Reader to  generate  new  field
	*	 appearances  based on the Rich Text data.  That way, when the
	*	 user opens the PDF, the viewer  will  create  the  Rich  Text
	*	 fields  on  the spot.	If the user's PDF viewer does not sup-
	*	 port Rich Text, then the user will see the  plain  text  data
	*	 instead.   If	you  flatten  this  form  before Acrobat has a
	*	 chance to create (and save) new field appearances,  then  the
	*	 plain text field data is what you'll see.
	*	 
	*@internal Wrapper to call pdftk, a shell command, in background.
	*@param String pdf_file absolute pathname to a pdf form file
	*@param String fdf_file absolute pathname to a pdf data file
	*@param String settings 
	*
	*	Output modes 'compress', 'uncompress', 'flatten' ..(see pdftk --help)
	*@return Array an associative array with two keys: 
	*	Boolean success a flag , if positive meaning the process is a success
	*	String return the path to the pdf generated or the error message 
	**/
	function pdftk($pdf_file,$fdf_file,$settings) {
	//------------------------------------------
	
		$descriptorspec = array(
			0 => array("pipe", "r"),  // // stdin 
			1 => array("pipe", "w"),  // stdout 
			2 => array("pipe", "w") // stderr 
		);

		$output_modes=$settings['output_modes'];
		$security=$settings['security'];
		
		$cwd = '/tmp';
		$env = array('misc_options' => 'aeiou');
		$err='';
		$success=0;

		if(is_windows()) {
			$cmd="pdftk.exe"; //For windows
		}else{
			$cmd="pdftk"; //For linux and mac
		}
		
		$dircmd=fix_path(dirname(__file__));
		
		if(file_exists("$dircmd/$cmd")) {
		
			$pdf_out=FPDM_CACHE."pdf_flatten.pdf";
			
			$cmdline="$dircmd/$cmd \"$pdf_file\" fill_form \"$fdf_file\" output \"$pdf_out\" $output_modes $security"; //direct to ouptut	

			//echo htmlentities("$cmdline , $descriptorspec, $cwd, $env");

			if(PHP5_ENGINE) { // Php5
				$process = proc_open($cmdline, $descriptorspec, $pipes, $cwd, $env);
			}else { //Php4
				$process = proc_open($cmdline, $descriptorspec, $pipes);
			}

			if (is_resource($process)) {

				if(PHP5_ENGINE) { 
					$err=stream_get_contents($pipes[2]);
				}else { //Php4
					$err= "";
					while (($str = fgets($pipes[2], 4096))) {
						$err.= "$str\n";
					}
				}

				fclose($pipes[2]);
				
				//Its important to close the pipes before proc_close call to avoid  dead locks 
				$return_value = proc_close($process);
				
			}else {
				$err="No more resource to execute the command";
			}
			
		}else {
			$err="Sorry but pdftk binary is not provided / Cette fonctionnalite requiere pdftk non fourni ici<ol>";
			$err.="<li>download it from / telecharger ce dernier a partir de <br><blockquote><a href=\"http://www.pdflabs.com/docs/install-pdftk/\">pdflabs</a></blockquote>";
			$err.="<li>copy the executable in this directory / Copier l'executable dans<br><blockquote><b>$dircmd</b></blockquote>" ;
			$err.="<li>set \$cmd to match binary name in / configurer \$cmd pour  qu'il corresponde dans le fichier<br><blockquote><b>".__file__."</b></blockquote></ol>";
		}
		
		if($err) {
			$ret=array("success"=> false,"return"=>$err);
		}else 
			$ret=array("success"=> true,"return"=>$pdf_out);

		return $ret;
	}

?>