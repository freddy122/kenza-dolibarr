<?php
require __DIR__ . '../../vendor/autoload.php';

use BarcodeBakery\Common\BCGFontFile;
use BarcodeBakery\Common\BCGColor;
use BarcodeBakery\Common\BCGDrawing;
use BarcodeBakery\Barcode\BCGean8;
use BarcodeBakery\Barcode\BCGean13;
use BarcodeBakery\Barcode\BCGisbn;
use BarcodeBakery\Barcode\BCGupca;

$font = new BCGFontFile(__DIR__ . '/font/Arial.ttf', 16);
$colorFront = new BCGColor(0, 0, 0);
$colorBack = new BCGColor(255, 255, 255);

// Barcode Part

if($_GET["type_codebare"] == 1) { // ean8
    $code = new BCGean8();
    $code->setScale(4);
    $code->setThickness(50);
}elseif($_GET["type_codebare"] == 2) { // ean-13
    $code = new BCGean13();
    $code->setScale(2);
    $code->setThickness(40);
}elseif($_GET["type_codebare"] == 4) { // isbn
    $code = new BCGisbn();
    $code->setScale(2);
    $code->setThickness(40);
}elseif($_GET["type_codebare"] == 3) { // upc
    $code = new BCGupca();
    $code->setScale(2);
    $code->setThickness(40);
}

$code->setForegroundColor($colorFront);
$code->setBackgroundColor($colorBack);
$code->setFont($font);
$code->parse($_GET["codebare"]);

// Drawing Part
$drawing = new BCGDrawing('', $colorBack);
$drawing->setBarcode($code);
$drawing->setDPI(300);
$drawing->draw();
$drawing->getBarcode();
header('Content-Type: image/png');

$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
