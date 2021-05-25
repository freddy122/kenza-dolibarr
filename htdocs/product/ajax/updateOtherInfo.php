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
    $prodToUpdate = new Product($db);
    $prodToUpdate->fetch($res->fk_product_child);
    
    if(intval($qtyComm) >= 0 ){
        if($isColor == 1){
            $resProdSameColors = getAllProductSameColor($refColor,$parentId);
            foreach($resProdSameColors as $resProdSameCol){
                if($resProdSameCol->fk_product_child == $res->fk_product_child){
                    $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set quantite_commander = ".$qtyComm.", total_quantite_commander = ".$qtyComm." where rowid=".$res->fk_product_child;
                    $db->query($sqlUpdateSame);
                }
            }
        }else{
            $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set quantite_commander = ".$qtyComm.", total_quantite_commander = ".$qtyComm." where rowid=".$res->fk_product_child;
            $db->query($sqlUpdateSame);
        }
    }
    
    if(intval($qtyfab) >= 0){
        if($isColor == 1){
            $resProdSameColors = getAllProductSameColor($refColor,$parentId);
            foreach($resProdSameColors as $resProdSameCol){
                if($resProdSameCol->fk_product_child == $res->fk_product_child){
                    $qtyComCalc = empty($qtyfab)? $prodToUpdate->quantite_fabriquer : $qtyfab;
                    $price_yuan = !empty($prixYuan) ? floatval(str_replace(',','.',$prixYuan)) : $prodToUpdate->price_yuan ;
                    $price_euro = !empty($prixEuro) ? floatval(str_replace(',','.',$prixEuro)) : $prodToUpdate->price_euro ;
                    $totalYuan = $qtyComCalc * floatval(str_replace(',','.',$price_yuan)) ;
                    $totalEuro = $qtyComCalc * floatval(str_replace(',','.',$price_euro)) ;
                    
                    $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set "
                            . " quantite_fabriquer = ".$qtyfab.", "
                            . " total_montant_yuan = ".floatval($totalYuan).", "
                            . " total_montant_euro = ".floatval($totalEuro)." "
                            . " where rowid=".$res->fk_product_child;
                    $db->query($sqlUpdateSame);
                    /* Mise à jour qty produit fils */
                    $sqlUpdatesStock = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                        . " set tms = '".date('Y-m-d h:i:s')."', "
                        . " fk_entrepot  = 1 ,"
                        . " reel = ".$qtyfab." where fk_product = ".$res->fk_product_child;
                    $db->query($sqlUpdatesStock);
                    $sqlgetStockForOneProd = "select reel from ".MAIN_DB_PREFIX."product_stock where fk_product =  ".$res->fk_product_child;
                    $totalQtyfab += $db->getRows($sqlgetStockForOneProd)[0]->reel;
                    $sqlUpdatePriceFournChild = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
                        . " set price = ".floatval($prodToUpdate->price_euro*floatval((!empty($qtyfab)?$qtyfab:1))).", "
                        . " quantity = ".(!empty($qtyfab)?$qtyfab:1).", "
                        . " unitprice=".floatval(floatval($prodToUpdate->price_euro*floatval((!empty($qtyfab)?$qtyfab:1)))/(!empty($qtyfab)?$qtyfab:1))." "
                        . " where fk_product = ".$res->fk_product_child;

                    $db->query($sqlUpdatePriceFournChild);
                }
            }
        }else{
            
            $qtyComCalc = empty($qtyfab)? $prodToUpdate->quantite_fabriquer : $qtyfab;
            $price_yuan = !empty($prixYuan) ? floatval(str_replace(',','.',$prixYuan)) : $prodToUpdate->price_yuan ;
            $price_euro = !empty($prixEuro) ? floatval(str_replace(',','.',$prixEuro)) : $prodToUpdate->price_euro ;
            $totalYuan = $qtyComCalc * floatval(str_replace(',','.',$price_yuan)) ;
            $totalEuro = $qtyComCalc * floatval(str_replace(',','.',$price_euro)) ;
            
            $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set "
                    . " quantite_fabriquer = ".$qtyfab.", "
                    . " total_montant_yuan = ".floatval($totalYuan).", "
                    . " total_montant_euro = ".floatval($totalEuro)." "
                    . " where rowid=".$res->fk_product_child;
            $db->query($sqlUpdateSame);
            /* Mise à jour qty produit fils */
            $sqlUpdatesStock = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                . " set tms = '".date('Y-m-d h:i:s')."', "
                . " fk_entrepot  = 1 ,"
                . " reel = ".$qtyfab." where fk_product = ".$res->fk_product_child;
            $db->query($sqlUpdatesStock);
            $sqlgetStockForOneProd = "select reel from ".MAIN_DB_PREFIX."product_stock where fk_product =  ".$res->fk_product_child;
            $totalQtyfab += $db->getRows($sqlgetStockForOneProd)[0]->reel;
            
            $sqlUpdatePriceFournChild = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
                . " set price = ".floatval($prodToUpdate->price_euro*floatval((!empty($qtyfab)?$qtyfab:1))).", "
                . " quantity = ".(!empty($qtyfab)?$qtyfab:1).", "
                . " unitprice=".floatval(floatval($prodToUpdate->price_euro*floatval((!empty($qtyfab)?$qtyfab:1)))/(!empty($qtyfab)?$qtyfab:1))." "
                . " where fk_product = ".$res->fk_product_child;
            
            $db->query($sqlUpdatePriceFournChild);
            
        }
    }
    
    if($composition){
        if($isColor == 1){
            $resProdSameColors = getAllProductSameColor($refColor,$parentId);
            foreach($resProdSameColors as $resProdSameCol){
                if($resProdSameCol->fk_product_child == $res->fk_product_child){
                    $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set composition = '".$composition."' where rowid=".$res->fk_product_child;
                    $db->query($sqlUpdateSame);
                }
            }
        }else{
            $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product set composition = '".$composition."' where rowid=".$res->fk_product_child;
            $db->query($sqlUpdateSame);
        }
    }
    
    if($prixYuan  || $tauxChange){
        if($isColor == 1){
            $resProdSameColors = getAllProductSameColor($refColor,$parentId);
            foreach($resProdSameColors as $resProdSameCol){
                if($resProdSameCol->fk_product_child == $res->fk_product_child){
                    $qtyComCalc = empty($qtyfab)? $prodToUpdate->quantite_fabriquer : $qtyfab;
                    $totalYuan = $qtyComCalc * floatval(str_replace(',','.',$prixYuan)) ;
                    $totalEuro = $qtyComCalc * floatval(str_replace(',','.',$prixEuro)) ;
                    $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product "
                    . " set price_yuan = ".floatval(str_replace(',','.',$prixYuan)).", "
                    . " taux_euro_yuan = ".floatval(str_replace(',','.',$tauxChange)).", "
                    . " total_montant_yuan = ".floatval($totalYuan).", "
                    . " total_montant_euro = ".floatval($totalEuro).", "
                    . " price_euro = ".floatval(str_replace(',','.',$prixEuro))." "
                    /*. " price = ".floatval(str_replace(',','.',$prixEuro)).", "
                    . " price_ttc = ".floatval(str_replace(',','.',$prixEuro)).""*/
                    . " where rowid=".$res->fk_product_child;
                    $db->query($sqlUpdateSame);
                    
                    /* prix fournisseur */
                    $sqlUpdatePriceFournChild = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
                        . " set price = ".floatval($totalEuro).", "
                        . " quantity = ".$qtyComCalc.", "
                        . " unitprice=".floatval(str_replace(',','.',$prixEuro))." "
                        . " where fk_product = ".$res->fk_product_child;
                    $db->query($sqlUpdatePriceFournChild);
                }
            }
        }else{
           
            $qtyComCalc = empty($qtyfab)? $prodToUpdate->quantite_fabriquer : $qtyfab;
            $totalYuan = $qtyComCalc * floatval(str_replace(',','.',$prixYuan)) ;
            $totalEuro = $qtyComCalc * floatval(str_replace(',','.',$prixEuro)) ;
            
            $sqlUpdateSame = "UPDATE ".MAIN_DB_PREFIX."product "
                    . " set price_yuan = ".floatval(str_replace(',','.',$prixYuan)).", "
                    . " taux_euro_yuan = ".floatval(str_replace(',','.',$tauxChange)).", "
                    . " total_montant_yuan = ".floatval($totalYuan).", "
                    . " total_montant_euro = ".floatval($totalEuro).", "
                    . " price_euro = ".floatval(str_replace(',','.',$prixEuro))." "
                    /*. " price = ".floatval(str_replace(',','.',$prixEuro)).", "
                    . " price_ttc = ".floatval(str_replace(',','.',$prixEuro))."  "*/
                    . " where rowid=".$res->fk_product_child;
            $db->query($sqlUpdateSame);
            
            /* prix fournisseur */
            $sqlUpdatePriceFournChild = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
                . " set price = ".floatval($totalEuro).", "
                . " quantity = ".$qtyComCalc.", "
                . " unitprice=".floatval(str_replace(',','.',$prixEuro))." "
                . " where fk_product = ".$res->fk_product_child;
            
            $db->query($sqlUpdatePriceFournChild);
            
        }
    }
    
}
/*Mise à jour qty produit parent */
if(intval($qtyfab)>=0){
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

if(!empty($totalQuantiteCom)){
$priceUnits = floatval($totalEuro/$totalQuantiteCom);
$sqlUpdatePriceFournParent = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
        . " set price = ".$totalEuro.", "
        . " quantity = ".$totalQuantiteCom.", "
        . " unitprice=".$priceUnits." "
        . " where fk_product = ".intval($parentId);
$db->query($sqlUpdatePriceFournParent);
}
function getAllProductSameColor($refColor,$parentId){
    global $db;
    $sqlGetAllProductSameColor = " SELECT "
                . " pacv.fk_prod_attr, "
                . " pacv.fk_prod_attr_val, "
                . " pac.fk_product_parent, "
                . " pac.fk_product_child "
                . " FROM ".MAIN_DB_PREFIX."product_attribute_combination2val pacv "
                . " left join ".MAIN_DB_PREFIX."product_attribute_combination pac on pac.rowid = pacv.fk_prod_combination "
                . " WHERE fk_prod_attr = ".intval($refColor[0])." and fk_prod_attr_val = ".intval($refColor[1])." and pac.fk_product_parent = ".intval($parentId);
    $resProdSameColors = $db->getRows($sqlGetAllProductSameColor);
    return $resProdSameColors;
}

header("Content-Type:application/json");
echo json_encode(array(
   "success" => true,
   "success_detail" => "Valeur appliqué sur toutes le(s) déclinaison(s), la modification sera appliqué une fois ce page sera actualiser ou après fermeture de ce popup ou après une click sur le bouton modifier"
));
