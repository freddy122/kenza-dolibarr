<?php

require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
global $conf;
global $db;

$idProd = GETPOST('idProdFrs');
$dataObject = new Product($db);
$dataObject->fetch($idProd);

header("Content-Type:application/json");
echo json_encode(array(
   "success" => true,
   "data" => $dataObject 
));
