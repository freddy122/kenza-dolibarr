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


$prodCombinates = new ProductCombination($db);
$resProdChild = $prodCombinates->fetchAllByFkProductParent($parentId);

$totalQuantiteCom   = 0;
$totalYuan          = 0;
$totalEuro          = 0;
foreach($resProdChild as $reChil){
    $prodChildUpdate = new Product($db);
    $prodChildUpdate->fetch($reChil ->fk_product_child);
    $totalQuantiteCom += $prodChildUpdate->quantite_fabriquer;
    $totalYuan        += $prodChildUpdate->quantite_fabriquer*$prodChildUpdate->price_yuan;
    $totalEuro        += $prodChildUpdate->quantite_fabriquer*$prodChildUpdate->price_euro;
}
/*Total Qty comm, yuan , euro*/
$sqlUpdateMontantTotal = "update ".MAIN_DB_PREFIX."product "
. " set total_quantite_commander = ".$totalQuantiteCom.", "
. " total_montant_yuan = ".$totalYuan.", "
. " total_montant_euro = ".$totalEuro." "
. " where rowid =  ".intval($parentId);
$db->query($sqlUpdateMontantTotal);

$priceUnits = floatval($totalEuro/$totalQuantiteCom);
$sqlUpdatePriceFournParent = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
        . " set price = ".$totalEuro.", "
        . " quantity = ".$totalQuantiteCom.", "
        . " unitprice=".$priceUnits." "
        . " where fk_product = ".intval($parentId);
$db->query($sqlUpdatePriceFournParent);

header("Content-Type:application/json");
echo json_encode(array(
   "success" => true,
   "success_detail" => "Valeur appliqué sur le(s) produit(s) de la même couleur, la modification sera appliqué après fermeture de ce popup ou après une click sur le bouton modifier ou après rafraichissement de ce page"
));
