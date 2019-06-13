# PDF Form Filling with FPDM

## Package

The FPDM class allows to fill out PDF forms, i.e. populate fields of a PDF file. It is **developed by Olivier Plathey**, author of the [FDPF Library](http://www.fpdf.org/), and has been released as [Skript 93](http://www.fpdf.org/en/script/script93.php).

I created this repository for the following reasons:

- make the current FPDM source available via [composer](https://packagist.org/packages/tmw/fpdm), autoload via classmaps
- bugfixing
    - FIX: compatibility issues with PHP 7.x [e376dc1](https://github.com/codeshell/fpdm/commit/e376dc157655ded24c61e098199586f3325d63c1) v2.9.1
    - FIX: filling forms in multiple files (wrong buffer usage, invalid offsets) [e376dc1](https://github.com/codeshell/fpdm/commit/e376dc157655ded24c61e098199586f3325d63c1) v2.9.1
    - FIX: convert ASCII object names to utf-8 [1eddba7](https://github.com/codeshell/fpdm/commit/1eddba76f610690821e8c0b3753df337a6cf65f7) v2.9.2
- improvements (changes to the original codebase are prefixed with `//FIX: change description` and ended with `//ENDFIX`)
    - ADD: support for checkboxes (disabled by default, activate with `$pdf->useCheckboxParser = true;`) [0375dd9](https://github.com/codeshell/fpdm/commit/0375dd95f05fd2d8d32d9ae1ab882fa0895b07b3) v2.9.2

## Version

Based on version 2.9 (2017-05-11) available from [fpdf.org/en/script/script93.php](http://www.fpdf.org/en/script/script93.php).

_Note: If you find that a new version has been hosted on fpdf.org, please do not hesitate to drop me [a short note](https://github.com/codeshell/fpdm/issues) to make sure I do not miss it out._

This repository only contains the separate php class written for form filling (FPD**M**). If you are looking for a repository containing the main FPD**F** Library, please head over to [github.com/Setasign/FPDF](https://github.com/Setasign/FPDF).

Once again, all credits to Olivier Plathey for providing an easy to use script for form filling in addition to his FPDF library!

## Installation 

### Composer

The preferred way of making FPDM available in your app is to install it via composer with

`composer require tmw/fpdm`

## Usage

### Composer (autoload)

[autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading) FPDM class files by adding this to your code:

`require 'vendor/autoload.php';`

### Standalone Script (legacy)

Load the top level entry point by calling

`require_once '/abolute/path/to/fpdm.php';`

or

`require_once './relative/path/to/fpdm.php';`

## Customization to original code

### classmaps vs. psr-4 (or: legacy code vs modern frameworks á la Laravel)

Autoloading classes with [namespaces](https://www.php.net/manual/en/language.namespaces.basics.php) and following [PSR-4: Autoloader](https://www.php-fig.org/psr/psr-4/) would be desireable. Especially reducing the risk of naming conflicts by using vendor namespaces.

However, FPDM has been around for a long time and as such is used in many projects that use non-namespaced code (I refer to them as legacy projects). Legacy projects instantiate FPDM by calling `$mypdf = new FPDM()` which is unqualified but defaults to the global namespace with non-namespaced code.

Using psr-4 would autoload the class to a subnamespace (e.g. \codeshell\fpdm\FPDM) instead of the global namespace (e.g. \FPDM) thus breaking any legacy code no matter if it used `new FPDM()` or `new \FPDM()`.

__Classmaps are a compromise.__ They allow taking advantage of composers autoloading and dependency management. Yet classes are added to the global namespace. Legacy projects can switch to composer without having to refactor their code. __Newer projects (e.g. utilizing frameworks like laravel, that heavily rely on namespaces) can still use legacy classes__ by using the fully qualified name (in this case the class name prefixed with global prefix operator as in `new \FPDM()`).

That's my reasoning for using classmaps over psr-4 for FPDM. Please let me know if there are use cases where classmaps won't work with modern frameworks.

### Checkboxes

I added support for checkboxes. The feature is not heavily tested but works for me. Can be enabled with `useCheckboxParser = true` like so:

```php
<?php
$fields = array(
    'my_checkbox'    => 'anything that evaluates to true.', // checkbox will be checked;  Careful, that includes ANY non-empty string (even "no" or "unchecked")
    'another_checkbox' => false, // checkbox will be UNchecked; empty string or 0 work as well
);

$pdf = new FPDM('template.pdf');
$pdf->useCheckboxParser = true; // Checkbox parsing is ignored (default FPDM behaviour) unless enabled with this setting
$pdf->Load($fields, true);
$pdf->Merge();
$pdf->Output();
```

You don't have to figure out the technical names of checkbox states. They are retrieved during the parsing process.

## Original Info Page
_Everything below is mirrored from http://www.fpdf.org/en/script/script93.php ._

### Information

Author: Olivier

License: FPDF

### Description

This script allows to merge data into a PDF form. Given a template PDF with text fields, it's
possible to inject values in two different ways:

- from a PHP array
- from an <abbr title="Forms Data Format">FDF</abbr> file

The resulting document is produced by the Output() method, which works the same as for FPDF.

Note: if your template PDF is not compatible with this script, you can process it with
[PDFtk](https://www.pdflabs.com/tools/pdftk-server/) this way:

`pdftk modele.pdf output modele2.pdf`

Then try again with modele2.pdf.

### Example

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
