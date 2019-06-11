# Form filling
The FPDM class allows to fill out PDF forms, i.e. populate fields of a PDF file. It is **developed by Olivier Plathey**, author of the [FDPF Library](http://www.fpdf.org/), and has been released as [Skript 93](http://www.fpdf.org/en/script/script93.php).

I created this repository for the following reasons:
- make the current FPDM source available via [composer](https://packagist.org/packages/tmw/fpdm)
- fix compatibility issues with PHP 7.x
- bugfixing

This repository only contains the separate php class written for form filling. If you are looking for a repository containing the main FPDF Library, please head over to [github.com/Setasign/FPDF](https://github.com/Setasign/FPDF).

Once again, all credits to Olivier Plathey for providing an easy to use extension to his FPDF library!

# Version
Based on version 2.9 (2017-05-11) available from [fpdf.org/en/script/script93.php](http://www.fpdf.org/en/script/script93.php).

Note: If you find that a new version has been hosted on fpdf.org, please do not hesitate to drop me [a short note](https://github.com/codeshell/fpdm/issues) to make sure I do not miss it out.

# Usage
## Composer (autoload)
As this package specifies autoloading information, you can [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading) all the dependencies by adding this to your code:

`require 'vendor/autoload.php';`

## Standalone Script
Load the class file by calling

`require_once '/abolute/path/to/fpdm.php';`

or

`require_once './relative/path/to/fpdm.php';`


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
