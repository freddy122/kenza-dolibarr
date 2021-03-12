<?php

/*if(UR_exists("https://kenza.re/8775-larges_default/ensemble-ceremonie-bateau.jpg")){
    echo "okkk";
}else{
    echo "nonokkkk";
}
function UR_exists($url){
   $headers=get_headers($url);
   return stripos($headers[0],"200 OK")?true:false;
}
die('iciiicc');
SELECT * FROM `ps_product_attribute` WHERE id_product in (select id_product where ean13 = "");
*/

/*
*  @author 	LVSinformatique <contact@lvsinformatique.com>
*  @copyright  	2014 LVSInformatique
*  @licence   	All Rights Reserved
*  This source file is subject to a commercial license from LVSInformatique
*  Use, copy, modification or distribution of this source file without written
*  license agreement from LVSInformatique is strictly forbidden.
*/

// This is to make Dolibarr working with Plesk
define('NOCSRFCHECK', 1);
//header("Content-Type:application/json");

// check codebarre empty($conf->barcode->enabled)
//check ref


set_include_path($_SERVER['DOCUMENT_ROOT'].'/htdocs');
require_once '../master.inc.php';

require_once DOL_DOCUMENT_ROOT.'/cyberoffice/class/nusoap/lib/nusoap.php'; 
require_once DOL_DOCUMENT_ROOT.'/core/lib/ws.lib.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/cunits.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/cyberoffice/class/cyberoffice.class.php';
dol_syslog("cyberoffice::Call Dolibarr webservices interfaces::ServerProduct_ws");

//sleep(15);
//set_time_limit(3600);
@ini_set('default_socket_timeout', 320);
//@ini_set('soap.wsdl_cache_enabled', '0'); 
//@ini_set('soap.wsdl_cache_ttl', '0');
$langs->load("main");
global $db,$conf,$langs;
//$authentication=array();

$sqlGetAllProduct = "SELECT rowid,ref,barcode from ".MAIN_DB_PREFIX."product as prod";
$resProd    = $db->getRows($sqlGetAllProduct);

$arrProds = [];
$i=0;
foreach($resProd as $resuP) {
    if(substr($resuP->ref,0 ,8)){
        $arrProds[substr($resuP->ref,0 ,8)][$i] = $resuP;
    }
    $i++;
}

foreach($arrProds as $karrP => $varrP) {
    $firstElement = (array_shift(array_values($varrP)));
    $posZero = substr($firstElement->ref, 8);
    if($posZero == "0000") {
        $frstElem = array_shift($varrP);
        foreach($varrP as $resuVarrP) {
            $sqlCheck = "SELECT rowid,fk_product_parent,fk_product_child from ".MAIN_DB_PREFIX."product_attribute_combination as prodcomb "
                    . "where fk_product_parent = ".$frstElem->rowid." and fk_product_child = ".$resuVarrP->rowid;
            $result = $db->query($sqlCheck);
            $nbtotalofrecords = $db->num_rows($result);
            if($nbtotalofrecords == 0){
                $sqlInsert = "INSERT into ".MAIN_DB_PREFIX."product_attribute_combination (fk_product_parent,fk_product_child,variation_price,variation_price_percentage,variation_weight,entity) "
                        . " values (".$frstElem->rowid.",".$resuVarrP->rowid.",0,0,0,1)";
                $db->query($sqlInsert);
            }
        }
    }
}

header("Content-Type:application/json");
echo json_encode(array(
   "success" => true,
   "success_detail" => "Déclinaison produit régénérer avec succès"
));


