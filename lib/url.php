<?php

	/****************************************************************
	*@file url.php
	*@name  Url manipulation toolbox
	*@internal Useful functions to deal with URLs
	*@package URL_TOOLBOX
	*@date    2010-10-27                                                          
	*@author  0livier  
	*@version 1.0
	*@note 
	* 	V1.0 (27.10.2010) First release
	******************************************************************/
	
	if (!defined('URL_TOOLBOX')) {
    
	
		function getScheme(/*$port is optional*/) {
		//---------------------------------------
			$numargs = func_num_args();
			$port=($numargs >0) ? func_get_arg(0) : $_SERVER["SERVER_PORT"];
			$schemes=array(
				'http'=>   80,// default for http
				'https'=> 443, // default for https
				'ftp' =>   21, // default for ftp
				'ftps'=>  990 // default for ftps 
			);
			$ports=array_flip($schemes);
			return (array_key_exists($port,$ports)) ? $ports[$port] : 0;
		}
		
		function getHost() {
		//------------------
			return $_SERVER["HTTP_HOST"];// [SERVER_NAME] 
		}
		
	
		if (!function_exists('fix_path')) {
			// fixes windows paths...
			// (windows accepts forward slashes and backwards slashes, so why does PHP use backwards?
			function fix_path($path) {
			//-------------------------
				return str_replace('\\','/',$path);
			}
		}
		
		function getWebDir($local_dir) {
		//----------------------------
			$local_root=$_SERVER["DOCUMENT_ROOT"];
			$server_dir=str_replace($local_root,'',$local_dir);
			return $server_dir;
		}
		
		//Local dir may be:
		//  the main script dir: dirname($_SERVER['PHP_SELF'])
		//  the current script dir fix_path(dirname(__FILE__))
		//return the full url with ending /
		function getUrlfromDir($local_dir) {
		//-------------------------------
			$server_dir=getWebDir($local_dir);
			$server_scheme=getScheme();
			$server_host=getHost();
			return "{$server_scheme}://{$server_host}/$server_dir";
		}
		
		/**
		 * Compiles url out of array of it's pieces 
		 * 'query' is ignored if 'query_params' is present
		 *
		 * @param Array $aUrl Array of url pieces
		 */
		function build_url($aUrl) {
		//-------------------------
			//[scheme]://[user]:[pass]@[host]/[path]?[query]#[fragment]
		   
		    if (!is_array($aUrl)) {
				return "";
			}
		   
			$sQuery = '';
		   
			// Compile query
			if (isset($aUrl['query_params']) && is_array($aUrl['query_params'])) {
				$aPairs = array();
				foreach ($aUrl['query_params'] as $sKey=>$sValue) {
					$aPairs[] = $sKey.'='.urlencode($sValue);              
				}
				$sQuery = implode('&', $aPairs);   
			} else {
				if(isset($aUrl['query'])) $sQuery = $aUrl['query'];
			}
		   
			// Compile url
			$sUrl =
				$aUrl['scheme'] . '://' . (
					isset($aUrl['user']) && $aUrl['user'] != '' && isset($aUrl['pass'])
					   ? $aUrl['user'] . ':' . $aUrl['pass'] . '@'
					   : ''
				) .
				$aUrl['host'] . (
					isset($aUrl['path']) && $aUrl['path'] != ''
					   ? $aUrl['path']
					   : ''
				) . (
				   $sQuery != ''
					   ? '?' . $sQuery
					   : ''
				) . (
				   isset($aUrl['fragment']) && $aUrl['fragment'] != ''
					   ? '#' . $aUrl['fragment']
					   : ''
				);
			return $sUrl;
		}
		
		function resolve_url($relative_url) {
		//-----------------------------
			$url=parse_url($relative_url);
			$url["path"]=resolve_path($url["path"]); //fix this
			$absolute_url=build_url($url);
			return $absolute_url;
		}
		
		
		//Get realpath without checking existence of file like php function does..
		function resolve_path($path) {
		//----------------------------------
			$out=array();
			foreach(explode('/', $path) as $i=>$fold){
				if ($fold=='' || $fold=='.') continue;
				if ($fold=='..' && $i>0 && end($out)!='..') array_pop($out);
			else $out[]= $fold;
			} return ($path{0}=='/'?'/':'').join('/', $out);
		}
		
		
		//This part is from http://fr2.php.net/manual/en/function.parse-url.php
		function j_parseUrl($url) {
		//--------------------------
			  $r  = "(?:([a-z0-9+-._]+)://)?";
			  $r .= "(?:";
			  $r .=   "(?:((?:[a-z0-9-._~!$&'()*+,;=:]|%[0-9a-f]{2})*)@)?";
			  $r .=   "(?:\[((?:[a-z0-9:])*)\])?";
			  $r .=   "((?:[a-z0-9-._~!$&'()*+,;=]|%[0-9a-f]{2})*)";
			  $r .=   "(?::(\d*))?";
			  $r .=   "(/(?:[a-z0-9-._~!$&'()*+,;=:@/]|%[0-9a-f]{2})*)?";
			  $r .=   "|";
			  $r .=   "(/?";
			  $r .=     "(?:[a-z0-9-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+";
			  $r .=     "(?:[a-z0-9-._~!$&'()*+,;=:@\/]|%[0-9a-f]{2})*";
			  $r .=    ")?";
			  $r .= ")";
			  $r .= "(?:\?((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9a-f]{2})*))?";
			  $r .= "(?:#((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9a-f]{2})*))?";
			  preg_match("`$r`i", $url, $match);
			  $parts = array(
						"scheme"=>'',
						"userinfo"=>'',
						"authority"=>'',
						"host"=> '',
						"port"=>'',
						"path"=>'',
						"query"=>'',
						"fragment"=>'');
			  switch (count ($match)) {
				case 10: $parts['fragment'] = $match[9];
				case 9: $parts['query'] = $match[8];
				case 8: $parts['path'] =  $match[7];
				case 7: $parts['path'] =  $match[6] . $parts['path'];
				case 6: $parts['port'] =  $match[5];
				case 5: $parts['host'] =  $match[3]?"[".$match[3]."]":$match[4];
				case 4: $parts['userinfo'] =  $match[2];
				case 3: $parts['scheme'] =  $match[1];
			  }
			  $parts['authority'] = ($parts['userinfo']?$parts['userinfo']."@":"").
									 $parts['host'].
									($parts['port']?":".$parts['port']:"");
		  return $parts;
		}
	
		define('URL_TOOLBOX',1);
	
	}//End of URL_TOOLBOX
?>