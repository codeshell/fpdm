# Form filling
The FPDM class allows to fill out PDF forms, i.e. populate fields of a PDF file. It is **developed by Olivier**, author of the [FDPF Library](http://www.fpdf.org/), and has been released as [Skript 93](http://www.fpdf.org/en/script/script93.php).

I created this repository for two reasons:
- make the current FPDM source avaiable via composer
- fix compatibility issues with PHP 7.x

Once again, all credits to Olivier for providing an easy to use extension to his FPDF library!

# Version
Based on version 2.9 (2017-05-11) available from http://www.fpdf.org/en/script/script93.php

# Original Info Page
## Information
Author: Olivier

License: FPDF

## Description
This script allows to merge data into a PDF form. Given a template PDF with text fields, it's
possible to inject values in two different ways:
- from a PHP array
- from an <abbr title="Forms Data Format">FDF</abbr> file

The resulting document is produced by the Output() method, which works the same as for FPDF.

Note: if your template PDF is not compatible with this script, you can process it with
[PDFtk](https://www.pdflabs.com/tools/pdftk-server/) this way:

`pdftk modele.pdf output modele2.pdf`
  
Then try again with modele2.pdf.

## Example
This example shows how to merge data from an array:

```php
<?php

/***************************
  Sample using a PHP array
****************************/

require('fpdm.php');

$fields = array(
    'name'    => 'My name',
    'address' => 'My address',
    'city'    => 'My city',
    'phone'   => 'My phone number'
);

$pdf = new FPDM('template.pdf');
$pdf->Load($fields, false); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
$pdf->Merge();
$pdf->Output();
?>
```

View the result [here](http://www.fpdf.org/en/script/ex93.pdf). 
