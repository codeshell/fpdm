<?php
/* forge_fdf, by Sid Steward
   version 1.0
   visit: www.pdfhacks.com/forge_fdf/

  For text fields, combo boxes and list boxes, add
  field values as a name => value pair to $fdf_data_strings.

  For check boxes and radio buttons, add field values
  as a name => value pair to $fdf_data_names.  Typically,
  true and false correspond to the (case sensitive)
  names "Yes" and "Off".

  Any field added to the $fields_hidden or $fields_readonly
  array must also be a key in $fdf_data_strings or
  $fdf_data_names; this might be changed in the future

  Any field listed in $fdf_data_strings or $fdf_data_names
  that you want hidden or read-only must have its field
  name added to $fields_hidden or $fields_readonly; do this
  even if your form has these bits set already

  PDF can be particular about CR and LF characters, so I
  spelled them out in hex: CR == \x0d : LF == \x0a
*/

function escape_pdf_string( $ss )
{
  $ss_esc= '';
  $ss_len= strlen( $ss );
  for( $ii= 0; $ii< $ss_len; ++$ii ) {
    if( ord($ss{$ii})== 0x28 ||  // open paren
	ord($ss{$ii})== 0x29 ||  // close paren
	ord($ss{$ii})== 0x5c )   // backslash
      {
	$ss_esc.= chr(0x5c).$ss{$ii}; // escape the character w/ backslash
      }
    else if( ord($ss{$ii}) < 32 || 126 < ord($ss{$ii}) ) {
      $ss_esc.= sprintf( "\\%03o", ord($ss{$ii}) ); // use an octal code
    }
    else {
      $ss_esc.= $ss{$ii};
    }
  }
  return $ss_esc;
}


/**
  $key = addcslashes($key, "\n\r\t\\()");
  $val = addcslashes($val, "\n\r\t\\()");
**/	   
function escape_pdf_name( $ss )
{
  $ss_esc= '';
  $ss_len= strlen( $ss );
  for( $ii= 0; $ii< $ss_len; ++$ii ) {
    if( ord($ss{$ii}) < 33 || 126 < ord($ss{$ii}) || 
	ord($ss{$ii})== 0x23 ) // hash mark
      {
	$ss_esc.= sprintf( "#%02x", ord($ss{$ii}) ); // use a hex code
      }
    else {
      $ss_esc.= $ss{$ii};
    }
  }
  return $ss_esc;
}



/**
*   Generates the fdf code
*
*@param String      $pdf_form_url:  a string containing a URL path to a PDF file on the
*                   server. This PDF MUST exist and contain fields with
*                   the names referenced by $pdf_data for this function
*                   to work.
*@param Array   	$fdf_data_strings:  an array of any fields in $pdf_form_url that you want to
*                   populate, of the form key=>val; where the field
*                   name is the key, and the field's value is in val.
*@return String 
**/  
function forge_fdf( $pdf_form_url, 
		    $fdf_data_strings,
		    $fdf_data_names,
		    $fields_hidden,
		    $fields_readonly )
{
  $fdf = "%FDF-1.2\x0d%\xe2\xe3\xcf\xd3\x0d\x0a"; // header
  $fdf.= "1 0 obj\x0d<< "; // open the Root dictionary
  $fdf.= "\x0d/FDF << "; // open the FDF dictionary
  $fdf.= "/Fields [ "; // open the form Fields array

  // string data, used for text fields, combo boxes and list boxes
  foreach( $fdf_data_strings as $key => $value ) {
    $fdf.= "<< /V (".escape_pdf_string($value).")".
              "/T (".escape_pdf_string($key).") ";
    if( in_array( $key, $fields_hidden ) )
      $fdf.= "/SetF 2 ";
    else
      $fdf.= "/ClrF 2 ";

    if( in_array( $key, $fields_readonly ) )
      $fdf.= "/SetFf 1 ";
    else
      $fdf.= "/ClrFf 1 ";

    $fdf.= ">> \x0d";
  }

  // name data, used for checkboxes and radio buttons
  // (e.g., /Yes and /Off for true and false)
  foreach( $fdf_data_names as $key => $value ) {
    $fdf.= "<< /V /".escape_pdf_name($value).
             " /T (".escape_pdf_string($key).") ";
    if( in_array( $key, $fields_hidden ) )
      $fdf.= "/SetF 2 ";
    else
      $fdf.= "/ClrF 2 ";

    if( in_array( $key, $fields_readonly ) )
      $fdf.= "/SetFf 1 ";
    else
      $fdf.= "/ClrFf 1 ";
    $fdf.= ">> \x0d";
  }
  
  $fdf.= "] \x0d"; // close the Fields array

  // the PDF form filename or URL, if given
  if( $pdf_form_url ) {
    $fdf.= "/F (".escape_pdf_string($pdf_form_url).") \x0d";
  }
  
  $fdf.= ">> \x0d"; // close the FDF dictionary
  $fdf.= ">> \x0dendobj\x0d"; // close the Root dictionary

  // trailer; note the "1 0 R" reference to "1 0 obj" above
  $fdf.= "trailer\x0d<<\x0d/Root 1 0 R \x0d\x0d>>\x0d";
  $fdf.= "%%EOF\x0d\x0a";

  return $fdf;
}

?>