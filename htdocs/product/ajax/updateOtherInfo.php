<?php
require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
global $conf;
global $db;


$refColor = explode("_",GETPOST('valCoul'));
$parentId = GETPOST('parentId');
$qtyComm = (GETPOST('qtyComm') !== null)?GETPOST('qtyComm'):false;
$qtyfab = (GETPOST('qtyfab')  !== null)?GETPOST('qtyfab'):false;
$composition = (GETPOST('composition')  !== null)?GETPOST('composition'):false;
$prixYuan = (GETPOST('prixYuan')  !== null)?GETPOST('prixYuan'):false;
$prixEuro = (GETPOST('prixEuro')  !== null)?GETPOST('prixEuro'):false;
$tauxChange = (GETPOST('tauxChange')  !== null)?GETPOST('tauxChange'):false;

$isColor = GETPOST('isColor');

$sqlGetAllProductSame = " SELECT "
        . " pac.fk_product_parent, "
        . " pac.fk_product_child "
        . " FROM  ".MAIN_DB_PREFIX."product_attribute_combination pac  "
        . " WHERE pac.fk_product_parent = ".intval($parentId);
$resProdSame = $db->getRows($sqlGetAllProductSame);


$totalQtyfab = 0;
foreach($resProdSame as $res){
    if($qtyComm){
        /*if($isColor == 1){
            die('iccc');
        }*/
        $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set quantite_commander = ".$qtyComm." where rowid=".$res->fk_product_child;
        $db->query($sqlUpdateSame);
    }
    
    if($qtyfab){
        $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set quantite_fabriquer = ".$qtyfab." where rowid=".$res->fk_product_child;
        $db->query($sqlUpdateSame);
        /* Mise à jour qty produit fils */
        $sqlUpdatesStock = "UPDATE ".MAIN_DB_PREFIX."product_stock "
            . " set tms = '".date('Y-m-d h:i:s')."', "
            . " fk_entrepot  = 1 ,"
            . " reel = ".$qtyfab." where fk_product = ".$res->fk_product_child;
        $db->query($sqlUpdatesStock);
        $sqlgetStockForOneProd = "select reel from ".MAIN_DB_PREFIX."product_stock where fk_product =  ".$res->fk_product_child;
        $totalQtyfab += $db->getRows($sqlgetStockForOneProd)[0]->reel;
    }
    
    if($composition){
        $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set composition = '".$composition."' where rowid=".$res->fk_product_child;
        $db->query($sqlUpdateSame);
    }
    
    if($prixYuan || $tauxChange){
        $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product "
                . " set price_yuan = ".floatval(str_replace(',','.',$prixYuan)).", "
                . " taux_euro_yuan = ".floatval(str_replace(',','.',$tauxChange)).", "
                . " price_euro = ".floatval(str_replace(',','.',$prixEuro)).", "
                . " price = ".floatval(str_replace(',','.',$prixEuro)).", "
                . " price_ttc = ".floatval(str_replace(',','.',$prixEuro))."  where rowid=".$res->fk_product_child;
        $db->query($sqlUpdateSame);
    }
    
}
/*Mise à jour qty produit parent */
if($qtyfab){
    $sqlUpdateQtyParent = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                        . " set tms = '".date('Y-m-d h:i:s')."', "
                        . " fk_entrepot  = 1 ,"
                        . " reel = ".$totalQtyfab." where fk_product = ".intval($parentId);
    $db->query($sqlUpdateQtyParent);
}


$prodCombinates = new ProductCombination($db);
$resProdChild = $prodCombinates->fetchAllByFkProductParent($parentId);

$totalQuantiteCom   = 0;
$totalYuan          = 0;
$totalEuro          = 0;
foreach($resProdChild as $reChil){
    $prodChildUpdate = new Product($db);
    $prodChildUpdate->fetch($reChil ->fk_product_child);
    $totalQuantiteCom += $prodChildUpdate->quantite_commander;
    $totalYuan        += $prodChildUpdate->quantite_commander*$prodChildUpdate->price_yuan;
    $totalEuro        += $prodChildUpdate->quantite_commander*$prodChildUpdate->price_euro;
}
/*Total Qty comm, yuan , euro*/
$sqlUpdateMontantTotal = "update ".MAIN_DB_PREFIX."product "
. " set total_quantite_commander = ".$totalQuantiteCom.", "
. " total_montant_yuan = ".$totalYuan.", "
. " total_montant_euro = ".$totalEuro." "
. " where rowid =  ".intval($parentId);
$db->query($sqlUpdateMontantTotal);

header("Content-Type:application/json");
echo json_encode(array(
   "success" => true,
   "success_detail" => "Valeur appliqué sur toutes le(s) déclinaison(s), la modification sera appliqué une fois ce page sera actualiser ou après fermeture de ce popup ou après une click sur le bouton modifier"
));
