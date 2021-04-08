<?php

require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
global $conf;
global $db;

$refColor = explode("_",GETPOST('valCoul'));
$parentId = GETPOST('parentId');
$refValue = GETPOST('refValue');
$sqlGetAllProductSameColor = " SELECT "
        . " pacv.fk_prod_attr, "
        . " pacv.fk_prod_attr_val, "
        . " pac.fk_product_parent, "
        . " pac.fk_product_child "
        . " FROM ".MAIN_DB_PREFIX."product_attribute_combination2val pacv "
        . " left join ".MAIN_DB_PREFIX."product_attribute_combination pac on pac.rowid = pacv.fk_prod_combination "
        . " WHERE fk_prod_attr = ".intval($refColor[0])." and fk_prod_attr_val = ".intval($refColor[1])." and pac.fk_product_parent = ".intval($parentId);
$resProdSameColors = $db->getRows($sqlGetAllProductSameColor);

foreach($resProdSameColors as $resProdsameColor){
    $sqlUpdateSameColor = "UPDATE ".MAIN_DB_PREFIX."product set ref_tissus_couleur = '".$refValue."' where rowid=".$resProdsameColor->fk_product_child;
    $db->query($sqlUpdateSameColor);
}

header("Content-Type:application/json");
echo json_encode(array(
   "success" => true,
   "success_detail" => "Valeur appliqué sur le(s) produit(s) de la même couleur, la modification sera appliqué après fermeture de ce popup ou après une click sur le bouton modifier ou après rafraichissement de ce page"
));
