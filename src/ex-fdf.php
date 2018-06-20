<?php

/***************************
  Sample using an FDF file
****************************/

use Shihjay2\Fpdm\FPDM;

$pdf = new FPDM('template.pdf', 'fields.fdf');
$pdf->Merge();
$pdf->Output();
?>
