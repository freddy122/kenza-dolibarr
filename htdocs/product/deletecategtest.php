<?php


require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/product/modules_product.class.php';
require DOL_DOCUMENT_ROOT.'/variants/class/ProductAttribute.class.php';
require DOL_DOCUMENT_ROOT.'/variants/class/ProductAttributeValue.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/autoloader.php';
require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/phpexcel/Classes/PHPExcel.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
global $conf;
global $db;
global $user;

$cats = new Categorie($db);
$rcakts = $cats->get_full_arbo(Categorie::TYPE_PRODUCT, 256, 1);
/*foreach($rcakts as $rcats){
    $sqldel = "delete from llx_categorie where rowid = ".$rcats['rowid'];
    $db->query($sqldel);
}*/
