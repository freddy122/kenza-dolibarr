<?php
require __DIR__ . '../../vendor/autoload.php';

use BarcodeBakery\Common\BCGFontFile;
use BarcodeBakery\Common\BCGColor;
use BarcodeBakery\Common\BCGDrawing;
use BarcodeBakery\Barcode\BCGean8;

$font = new BCGFontFile(__DIR__ . '/font/Arial.ttf', 12);
$colorFront = new BCGColor(0, 0, 0);
$colorBack = new BCGColor(255, 255, 255);

// Barcode Part
$code = new BCGean8();
$code->setScale(5);
$code->setThickness(45);
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
