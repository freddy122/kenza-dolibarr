<?php

require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
global $conf;
global $db;


$parentId = GETPOST('parentId');
$childId = GETPOST('childId');
$qtyComm = (GETPOST('qtyComm') !== null)?GETPOST('qtyComm'):false;
$qtyfab = (GETPOST('qtyfab')  !== null)?GETPOST('qtyfab'):false;
$prixYuan = (GETPOST('prixYuan')  !== null)?GETPOST('prixYuan'):false;
$prixEuro = (GETPOST('prixEuro')  !== null)?GETPOST('prixEuro'):false;
$tauxChange = (GETPOST('tauxChange')  !== null)?GETPOST('tauxChange'):false;
$composition = (GETPOST('composition')  !== null)?GETPOST('composition'):false;
//print_r($childId." ---  ".$parentId." ---- ".$qtyComm);

$prodToUpdate = new Product($db);
$prodToUpdate->fetch(intval($childId));

if($qtyComm){
    $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set quantite_commander = ".intval($qtyComm).", total_quantite_commander = ".intval($qtyComm)." where rowid=".$childId;
    $db->query($sqlUpdateSame);
}

if($qtyfab){
    $qtyFabCalc = empty($qtyfab)? $prodToUpdate->quantite_fabriquer : $qtyfab;
    $price_yuan = ($prixYuan) ? floatval(str_replace(',','.',$prixYuan)) : $prodToUpdate->price_yuan ;
    $price_euro = ($prixEuro) ? floatval(str_replace(',','.',$prixEuro)) : $prodToUpdate->price_euro ;
    $totalYuan = $qtyFabCalc * floatval(str_replace(',','.',$price_yuan)) ;
    $totalEuro = $qtyFabCalc * floatval(str_replace(',','.',$price_euro)) ;

    $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set "
            . " quantite_fabriquer = ".$qtyfab.", "
            . " total_montant_yuan = ".floatval($totalYuan).", "
            . " total_montant_euro = ".floatval($totalEuro)." "
            . " where rowid=".$childId;
    $db->query($sqlUpdateSame);
    
    /* Mise à jour qty produit fils */
    $sqlUpdatesStock = "UPDATE ".MAIN_DB_PREFIX."product_stock "
        . " set tms = '".date('Y-m-d h:i:s')."', "
        . " fk_entrepot  = 1 ,"
        . " reel = ".$qtyfab." where fk_product = ".$childId;
    $db->query($sqlUpdatesStock);
    
    $sqlUpdatePriceFournChild = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
        . " set price = ".floatval($prodToUpdate->price_euro*floatval((!empty($qtyfab)?$qtyfab:1))).", "
        . " quantity = ".(!empty($qtyfab)?$qtyfab:1).", "
        . " unitprice=".floatval(floatval($prodToUpdate->price_euro*floatval((!empty($qtyfab)?$qtyfab:1)))/(!empty($qtyfab)?$qtyfab:1))." "
        . " where fk_product = ".$childId;

    $db->query($sqlUpdatePriceFournChild);
}

if($composition){
    $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set composition = '".$composition."' where rowid=".$childId;
    $db->query($sqlUpdateSame);
}

if($prixYuan  || $tauxChange){
    $qtyComCalc = empty($qtyfab)? $prodToUpdate->quantite_fabriquer : $qtyfab;
    $totalYuan = $qtyComCalc * floatval(str_replace(',','.',$prixYuan)) ;
    $totalEuro = $qtyComCalc * floatval(str_replace(',','.',$prixEuro)) ;
    $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product "
    . " set price_yuan = ".floatval(str_replace(',','.',$prixYuan)).", "
    . " taux_euro_yuan = ".floatval(str_replace(',','.',$tauxChange)).", "
    . " total_montant_yuan = ".floatval($totalYuan).", "
    . " total_montant_euro = ".floatval($totalEuro).", "
    . " price_euro = ".floatval(str_replace(',','.',$prixEuro)).", "
    . " price = ".floatval(str_replace(',','.',$prixEuro)).", "
    . " price_ttc = ".floatval(str_replace(',','.',$prixEuro))."  where rowid=".$childId;
    $db->query($sqlUpdateSame);

    /* prix fournisseur */
    $sqlUpdatePriceFournChild = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
        . " set price = ".floatval($totalEuro).", "
        . " quantity = ".$qtyComCalc.", "
        . " unitprice=".floatval(str_replace(',','.',$prixEuro))." "
        . " where fk_product = ".$childId;
    $db->query($sqlUpdatePriceFournChild);
}


/*Mise à jour qty produit parent */

$prodCombinates = new ProductCombination($db);
$resProdChild = $prodCombinates->fetchAllByFkProductParent($parentId);
$totalQuantiteFab   = 0;
$totalQuantitecomm   = 0;
$totalYuan          = 0;
$totalEuro          = 0;
foreach($resProdChild as $reChil){
    $prodChildUpdate = new Product($db);
    $prodChildUpdate->fetch($reChil ->fk_product_child);
    $totalQuantiteFab += $prodChildUpdate->quantite_fabriquer;
    $totalQuantitecomm += $prodChildUpdate->quantite_fabriquer;
    $totalYuan        += $prodChildUpdate->quantite_fabriquer*$prodChildUpdate->price_yuan;
    $totalEuro        += $prodChildUpdate->quantite_fabriquer*$prodChildUpdate->price_euro;
}

if(intval($qtyfab)>=0){
    $sqlUpdateQtyParent = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                        . " set tms = '".date('Y-m-d h:i:s')."', "
                        . " fk_entrepot  = 1 ,"
                        . " reel = ".$totalQuantiteFab." where fk_product = ".intval($parentId);
    $db->query($sqlUpdateQtyParent);
}


/*Total Qty fab, yuan , euro*/
$sqlUpdateMontantTotal = "update ".MAIN_DB_PREFIX."product "
. " set total_quantite_commander = ".$totalQuantitecomm.", " 
. " total_montant_yuan = ".$totalYuan.", "
. " total_montant_euro = ".$totalEuro." "
. " where rowid =  ".intval($parentId);
$db->query($sqlUpdateMontantTotal);

if(!empty($totalQuantiteFab)){ 
    $priceUnits = floatval($totalEuro/$totalQuantiteFab);
    $sqlUpdatePriceFournParent = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
            . " set price = ".$totalEuro.", "
            . " quantity = ".$totalQuantiteFab.", "
            . " unitprice=".$priceUnits." "
            . " where fk_product = ".intval($parentId);
    $db->query($sqlUpdatePriceFournParent);
}

header("Content-Type:application/json");
echo json_encode(array(
   "success" => true,
));
