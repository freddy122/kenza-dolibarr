<?php
$refprod = $_GET["refprod"];
$image_name = $_GET["image_name"];
$location  = dirname(dirname((__FILE__)));
$imagesdir = $location."/documents/produit/".$refprod;
$myfiles = array_diff(scandir($imagesdir), array('.', '..')); 
ob_clean();
header('Content-Type:image/jpeg');
header('Content-Length: '.filesize($location."/documents/produit/".$refprod."/".$image_name));
echo file_get_contents($location."/documents/produit/".$refprod."/".$image_name);