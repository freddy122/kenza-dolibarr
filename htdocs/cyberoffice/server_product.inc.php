<?php
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
$authentication=array();
$params=array();
$authentication=$_POST['authentication'];
$params= $_POST['params']; 
$now=dol_now();
dol_syslog("cyberoffice::Function: server_product.inc login=".$authentication['login']);

if ($authentication['entity']) 
    $conf->entity=$authentication['entity'];

    // Init and check authentication
$objectresp=array();
$errorcode='';$errorlabel='';
$error=0;
$errortot=0;
$fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);
if ($error)
{
    $objectresp = array('result'=>array('result_code' => 'ko', 'result_label' => 'ko'),'webservice'=>'login');
    $error++;
    return $objectresp;
}

$error=0;
dol_syslog("CyberOffice_server_product::line=".__LINE__);
include_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

$newobject=new Product($db);
//$db->begin();
$count_params = (is_array($params)?count($params):0);
dol_syslog("CyberOffice_server_product::nb produits=".$count_params);
$socid_old=0;
        
$user = new User($db);
$cunits = new CUnits($db);
$user->fetch('', $authentication['login'],'',0);
$user->getrights();

$cyber = new Cyberoffice;
$cyber->entity = 0;
$cyber->myurl = $authentication['myurl'];
$indice = $cyber->numShop();
$indice_name = $cyber->numShop(1);
$objectcat=new Categorie($db);
$catparent0 = array();
if (version_compare(DOL_VERSION, '3.8.0', '<'))
    $catparent0 = $objectcat->rechercher(null,$cyber->myurl,0);
else
    $catparent0 = $objectcat->rechercher(null,$cyber->myurl,'product');

foreach ($catparent0 as $cat_parent0)
{
    $idparent0 = $cat_parent0->id;
}
$arr_combinaison = [];
if (is_array($params) && sizeof($params)>0) {
    $countProducts = 0;
    foreach ($params as $product)
    {
        $db->begin();
        $myref = dol_sanitizeFileName(stripslashes($product['reference']));
        			//echo "<pre>".print_r($product)."</pre>";die();
	dol_syslog("CyberOffice_server_product::traitement produit=".$product['id_product']);
	
	/*****recherche de la correspondance
	************************************/
	if ($product['match'] == '{ref}' && !$product['reference']) {
            $list_ok.= "<br/>ERROR ref ".$product['id_product'];
            dol_syslog("CyberOffice_server_product::ERROR ref ".$product['id_product']);
            continue;
	}
        /***** test produit parent
	**************************/
	$nbr = strpos($product['id_product'], '-');
	if ($nbr === false) 
            $nbr=0;
	$product_id_product = "P".$indice."-".substr($product['id_product'],0,$nbr);
	$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'product WHERE import_key="'.$product_id_product.'"';
	dol_syslog("CyberOffice_server_product::fetch combination sql=".$sql.' nbr='.$nbr);
	if ($nbr>0) {
            $resql = $db->query($sql);
            if ($resql) {
                if ($db->num_rows($resql) > 0) {
                    $res = $db->fetch_array($resql);
                    $produit_id=$res['rowid'];
                } else 
                    $produit_id=0;
            } else 
                $produit_id=0;
            if ($produit_id > 0) {
                $sql = "UPDATE ".MAIN_DB_PREFIX."product SET";
		$sql .= " import_key='P".$indice."-".$product['id_product']."'";
		$sql .= ", ref='".$product['reference']."'";
		$sql.= " WHERE rowid=".$produit_id;
		dol_syslog("server_product::update combination - sql=".$sql);
		$resql = $db->query($sql);
		//$db->commit();
            }
        }

	$sql = "SELECT rowid";
	$sql.= " FROM ".MAIN_DB_PREFIX."product";
	if ($product['match'] == '{ref}')
            $sql.= " WHERE ref = '".$product['reference']."'";
	else
            $sql.= " WHERE import_key = 'P".$indice."-".$product['id_product']."'";
	dol_syslog("CyberOffice_server_product::fetch sql=".$sql);
	$resql = $db->query($sql);
	if ($resql) {
            if ($db->num_rows($resql) > 0) {
                $res = $db->fetch_array($resql);
		$produit_id=$res['rowid'];
            } else {
                $produit_id=0;
            }
        } else {
            $produit_id=0;
        }
        if ($db->num_rows($resql) > 1 && $product['match'] == '{ref}') {
            //$error++;
            $list_ok.= "<br/>ERROR ref ".$product['reference']." x ".$db->num_rows($resql);
            dol_syslog("CyberOffice_server_product::ERROR ref ".$product['reference']." x ".$db->num_rows($resql));
            continue;
	}
	/*****creation
	**************/
	$newobject->price_base_type 	= 'TTC';
        //$newobject->price				= $product['price'];
	$newobject->price				= price2num($product['price'] * (1 + ($product['tax_rate'] / 100)),'MU');
	$newobject->price_ttc 			= price2num($product['price'] * (1 + ($product['tax_rate'] / 100)),'MU');
	$newobject->tva_tx				= $product['tax_rate'];

        if ($conf->global->MAIN_MULTILANGS) {
            $outputlangs = $langs;
            $outputlangs->setDefaultLang($product['LOCALELANG']);
        }
        if (version_compare(DOL_VERSION, '3.8.0', '<'))
            $newobject->libelle				= $product['name'];
	else
            $newobject->label				= $product['name'];
        $newobject->description			= $product['description_short'];
	$newobject->array_options		= array("options_longdescript"=>trim($product['description']));

        $newobject->type				= 0;
	$newobject->status				= $product['active'];
	$newobject->status_buy			= 1;
	if ($conf->global->MAIN_MODULE_BARCODE) {//ean 2 upc 3 isbn 4
            if ($product['ean13'])
            {
                $newobject->barcode				= $product['ean13'];
		$newobject->barcode_type		= 2;
            } 
            elseif ($product['upc'])
            {
                $newobject->barcode				= $product['upc'];
		$newobject->barcode_type		= 3;
            } 
            elseif ($product['isbn'])
            {
                $newobject->barcode				= $product['isbn'];
		$newobject->barcode_type		= 4;
            } 
	}
	$newobject->ref					= '';
	if (!empty($product['reference'])) {
            $newobject->ref = $product['reference'];
            dol_syslog("CyberOffice_server_product::ref1 =".$newobject->ref);
	} else {
            // Load object modCodeProduct
            $module=(! empty($conf->global->PRODUCT_CODEPRODUCT_ADDON)?$conf->global->PRODUCT_CODEPRODUCT_ADDON:'mod_codeproduct_leopard');
            if ($module != 'mod_codeproduct_leopard')	// Do not load module file for leopard
            {
                if (substr($module, 0, 16) == 'mod_codeproduct_' && substr($module, -3) == 'php')
                {
                    $module = substr($module, 0, dol_strlen($module)-4);
                }
		dol_include_once('/core/modules/product/'.$module.'.php');
		$modCodeProduct = new $module;
		if (! empty($modCodeProduct->code_auto))
		{
                    $newobject->ref = $modCodeProduct->getNextValue($newobject,$newobject->type);
                }
		unset($modCodeProduct);
            }
            if (empty($newobject->ref) || !$newobject->ref) 
                $newobject->ref = 'Presta'.$product['id_product'];
	}
	dol_syslog("CyberOffice_server_product::ref2 =".$newobject->ref);
	$user = new User($db);
	$Ruser=$user->fetch('', $authentication['login'],'',0);
				
	/* verification ref existant
	****************************/
	$sql = "SELECT count(*) as nb";
	$sql.= " FROM ".MAIN_DB_PREFIX."product";
	$sql.= " WHERE (ref = '" .$newobject->ref."' OR ref LIKE '".$newobject->ref."(%')";
	$sql.= " AND import_key != 'P".$indice."-".$product['id_product']."'";
	if ($product['match'] == '{ref}') 
            $resultCheck = '';
	else 
            $resultCheck = $db->query($sql);
	if ($resultCheck )
	{
            $obj = $db->fetch_object($resultCheck );
            if ($obj->nb > 0) 
                $newobject->ref = $newobject->ref.'('.($obj->nb + 1).')';
	}

	$result=$produit_id;
	if ($produit_id == 0) {
            $newobject->oldcopy='';
            dol_syslog("CyberOffice_server_product::create Product : ".$product['id_product'].'->'.$produit_id . ' : ' .$product['name']);
            /*
            if ($conf->global->MAIN_MODULE_BARCODE && (!$product['ean13'] && !$product['upc'] && !$product['isbn']))
            {
                dol_syslog("CyberOffice_server_product::cb error =".$product['id_product']);
		continue;
            }
            */
            if ($conf->global->MAIN_MODULE_BARCODE)
            {
                if ($product['ean13'])
		{
                    $newobject->barcode				= $product['ean13'];
                    $newobject->barcode_type		= 2;
                } 
		elseif ($product['upc'])
		{
                    $newobject->barcode				= $product['upc'];
                    $newobject->barcode_type		= 3;
                } 
		elseif ($product['isbn'])
		{
                    $newobject->barcode				= $product['isbn'];
                    $newobject->barcode_type		= 4;
                }
		/*$sql213 = "SELECT barcode FROM ".MAIN_DB_PREFIX."product";
		$sql213.= " WHERE barcode = '".$newobject->barcode."' AND entity=".$conf->entity;
		$resql213=$db->query($sql213);
		$rescode = 0;
		if ($resql213)
		{
                    if ($db->num_rows($resql213) == 0)
                    {
                        $rescode =0;
                    } else {
			$rescode =-1;
                    }
		}
                if ($rescode <> 0)
        	{
                    $errorscb = 'ErrorBarCodeAlreadyUsed';
                    dol_syslog("CyberOffice_server_product::cb error =".$product['id_product'].' '.$errorscb);
                    continue;
        	}*/
            }
            $result = $newobject->create($user);
            if ($result > 0) {
                $produit_id=$result;
		$list_ok.="<br/>Create Product : ".$result. ' : ' .$product['name'];
		$sql = "UPDATE ".MAIN_DB_PREFIX."product SET";
		$sql .= " import_key='P".$indice."-".$product['id_product']."'";
		$sql.= " WHERE rowid=".$result;
		dol_syslog("server_product::update - sql=".$sql);
		$resql = $db->query($sql);
            }
	} 
        
        if ($nbr>0){
            $exploded_id = explode("-", $product['id_product']);
            $arr_combinaison[$exploded_id[0]."-".$product["real_name"]][$countProducts] = $product["reference"]."-".$result;
            $countProducts++;
        }
	/*****modification
	******************/
	$sql = "SELECT rowid";
	$sql.= " FROM ".MAIN_DB_PREFIX."product";
	if ($product['match'] == '{ref}')
            $sql.= " WHERE ref = '".$product['reference']."'";
	else
            $sql.= " WHERE import_key = 'P".$indice."-".$product['id_product']."'";
	dol_syslog("CyberOffice_server_product::fetch2 sql=".$sql);
	$resql = $db->query($sql);
	if ($resql) {
            if ($db->num_rows($resql) > 0) {
                $res = $db->fetch_array($resql);
		$produit_id=$res['rowid'];
		$newobject->fetch($produit_id);
            } else 
                $produit_id=0;
	} else 
            $produit_id=0;
	if ($db->num_rows($resql) > 1 && $product['match'] == '{ref}') {
            //$error++;
            $list_ok.= "<br/>ERROR ref ".$product['reference']." x ".$db->num_rows($resql);
            dol_syslog("CyberOffice_server_product::ERROR ref ".$product['reference']." x ".$db->num_rows($resql));
            continue;
	}
	
	$newobject->url					= $product['product_url'];

        if ($conf->global->MAIN_MULTILANGS) {
            $outputlangs = $langs;
            $outputlangs->setDefaultLang($product['LOCALELANG']);
            //dol_syslog("cyber::setDefaultLang srclang=".$product['LOCALELANG'],LOG_DEBUG);
        }
            if (version_compare(DOL_VERSION, '3.8.0', '<'))
                $newobject->libelle				= $product['name'];
            else
                $newobject->label				= $product['name'];
            $newobject->description			= $product['description_short'];
            $newobject->array_options["options_longdescript"] = trim($product['description']);
	
        $newobject->status				= $product['active'];
        $newobject->weight			 	= $product['weight'];
	$newobject->height              = $product['height'];//hauteur
        $newobject->width               = $product['width'];//largeur
        $newobject->length              = $product['depth'];//longueur profondeur
	$newobject->ref 		= $product['reference'];
        $tabunits = $cunits->fetch(null, '', $product['WEIGHT_UNIT']);
        $newobject->weight_units 	= $cunits->scale;//0;//kg
        $tabunits = $cunits->fetch(null, '', $product['DIMENSION_UNIT']);
	$newobject->length_units 	= $cunits->scale;//-2;//cm
        $tabunits = $cunits->fetch(null, '', $product['VOLUME_UNIT']);
        $newobject->volume_units        = $cunits->scale;
	$newobject->price_base_type 	= 'TTC';
	//$newobject->price				= $product['price'];
	$newobject->price				= price2num($product['price'] * (1 + ($product['tax_rate'] / 100)),'MU');
	$newobject->tva_tx				= $product['tax_rate'];
	$newobject->price_ttc 			= price2num($product['price'] * (1 + ($product['tax_rate'] / 100)),'MU');
	$newobject->id					= $produit_id;
	if ($conf->global->MAIN_MODULE_BARCODE) {
            if ($product['ean13'])
            {
                $newobject->barcode				= $product['ean13'];
		$newobject->barcode_type		= 2;
            } 
            elseif ($product['upc'])
            {
                $newobject->barcode				= $product['upc'];
		$newobject->barcode_type		= 3;
            } 
            elseif ($product['isbn'])
            {
                $newobject->barcode				= $product['isbn'];
		$newobject->barcode_type		= 4;
            }
        }
	if ($produit_id>0) {
            /* verification ref existant
            ****************************/
            $sql = "SELECT count(*) as nb";
            $sql.= " FROM ".MAIN_DB_PREFIX."product";
            $sql.= " WHERE (ref = '" .$newobject->ref."' OR ref LIKE '".$newobject->ref."(%')";
            $sql.= " AND import_key != 'P".$indice."-".$product['id_product']."'";
            if ($product['match'] == '{ref}') 
                $resultCheck = '';
            else 
                $resultCheck = $db->query($sql);
            if ($resultCheck )
            {
                $obj = $db->fetch_object($resultCheck );
		if ($obj->nb > 0) 
                    $newobject->ref = $newobject->ref.'('.($obj->nb + 1).')';
            }
            
            $product_price = new Product($db);
            $product_price->fetch($produit_id);

            if (empty($conf->global->PRODUIT_MULTIPRICES) || $conf->global->PRODUIT_MULTIPRICES == 0)
            {
                if (round($product_price->price,3) != round($product['price'],3) || round($product_price->tva_tx,3) != round($product['tax_rate'],3))
                    $newobject->updatePrice(($product['price'] * (1 + ($product['tax_rate'] / 100))), 'TTC', $user, $product['tax_rate']);
            } else {
                $pricelevel = (int)$conf->global->{"MYCYBEROFFICE_pricelevel".$indice_name} ;
                if ($pricelevel==0) $pricelevel = 1;
                if (round($product_price->multiprices_min[$pricelevel],3) != round($product['price'],3) || round($product_price->tva_tx,3) != round($product['tax_rate'],3))
                    $newobject->updatePrice(($product['price'] * (1 + ($product['tax_rate'] / 100))), 'TTC', $user, $product['tax_rate'], $product_price->multiprices_min[$pricelevel],$pricelevel);
            }
            $newobject->oldcopy='';
            dol_syslog("CyberOffice_server_product::Update Product : ".$product['id_product'].'->'.$produit_id . ' : ' .$product['name']);
            /*
            if ($conf->global->MAIN_MODULE_BARCODE && (!$product['ean13'] && !$product['upc'] && !$product['isbn']))
            {
                dol_syslog("CyberOffice_server_product::cb error =".$product['id_product']);
		continue;
            }
            */
            if ($conf->global->MAIN_MODULE_BARCODE)
            {
                if ($product['ean13'])
                {
                    $newobject->barcode				= $product['ean13'];
                    $newobject->barcode_type		= 2;
                } 
		elseif ($product['upc'])
		{
                    $newobject->barcode				= $product['upc'];
                    $newobject->barcode_type		= 3;
                } 
		elseif ($product['isbn'])
                {
                    $newobject->barcode				= $product['isbn'];
                    $newobject->barcode_type		= 4;
                }
		/*$sql213 = "SELECT barcode FROM ".MAIN_DB_PREFIX."product";
		$sql213.= " WHERE barcode = '".$newobject->barcode."' AND entity=".$conf->entity;
		$sql213.= " AND rowid <> ".$produit_id;
		$resql213=$db->query($sql213);
		$rescode = 0;
		if ($resql213)
		{
                    if ($db->num_rows($resql213) == 0)
                        $rescode =0;
                    else 
                        $rescode =-1;
		}
                if ($rescode <> 0)
        	{
                    $errorscb = 'ErrorBarCodeAlreadyUsed';
                    dol_syslog("CyberOffice_server_product::cb error =".$product['id_product'].' '.$errorscb);
                    continue;
	        }*/
            }
            /**Extrafield
            *************/
            $extraFields = new ExtraFields($db);
            $ProductExtraField = $extraFields->fetch_name_optionals_label('product');
            
            foreach($product['features'] as $feature) {
                $sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_cyberoffice c WHERE c.active=1 AND c.idpresta=".(int)$feature['id_feature'];
                $resql = $db->query($sql);
                if ($resql) {
                    if ($db->num_rows($resql) > 0) {
                        $res = $db->fetch_array($resql);
                        $res_extrafield=$res['extrafield'];
                        if ($extraFields->attribute_type[$res_extrafield]== 'select') {
                            $newobject->array_options['options_'.$res_extrafield] = $feature['id_feature_value'];
                        } else {
                            $newobject->array_options['options_'.$res_extrafield] = $feature['feature_value_lang'];
                        }
                        dol_syslog("CyberOffice_server_product::extrafield  -> ".$res_extrafield.'::'.$newobject->array_options['options_'.$res_extrafield]);
                    }
                }
            }
            /** specificPrice
            ******************/
            if ($conf->global->MAIN_MODULE_PRICELIST==1) {
                dol_include_once('./custom/pricelist/class/pricelist.class.php');
                foreach ($product['specificprice'] as $myi => $myvalue) {
                    $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."societe
                        WHERE import_key LIKE 'P%-".$product['specificprice'][$myi]['id_customer']."'";
                    $resql = $db->query($sql);
                    if ($resql) {
                        if ($db->num_rows($resql) > 0) {
                            $res = $db->fetch_array($resql);
                            $res_rowid=$res['rowid'];
                        } else $res_rowid=0;
                    } else $res_rowid=0;
                    $MyPricelist = new Pricelist($db);
                    $MyPricelist->product_id = $produit_id;
                    $MyPricelist->socid = ($res_rowid>0?$res_rowid:0);
                    $MyPricelist->from_qty = $product['specificprice'][$myi]['from_quantity'];
                    $MyPricelist->price = $product['specificprice'][$myi]['price'];
                    if ($MyPricelist->price==-1) {
                        if ($product['specificprice'][$myi]['reduction_type']=='amount') {
                            $MyPricelist->price = $product['price'] - $product['specificprice'][$myi]['reduction'];
                        } else {
                            $MyPricelist->price = $product['price'] * (1 - $product['specificprice'][$myi]['reduction']);
                        }
                    }

                    if (is_array($product['specificprice'][0])) {
                        $sql = "DELETE FROM ".MAIN_DB_PREFIX."pricelist
                        WHERE import_key='P".$product['specificprice'][$myi]['id_specific_price']."'";
                        $resql = $db->query($sql);
                        $res = $MyPricelist->create($user);
                        $sql = "UPDATE ".MAIN_DB_PREFIX."pricelist SET
                        import_key='P".$product['specificprice'][$myi]['id_specific_price']."'
                        WHERE rowid=".$res;
                        $resql = $db->query($sql);
                    }
                }
            }

            $resultS_U=$newobject->update($produit_id,$user);
            $sql = "UPDATE ".MAIN_DB_PREFIX."product SET";
            $sql .= " import_key='P".$indice."-".$product['id_product']."'";
            $sql.= " WHERE rowid=".$produit_id;
            dol_syslog("server_product::update combination - sql=".$sql);
            $resql = $db->query($sql);
        }
	if ($resultS_U> 0) 
            $list_ok.="<br/>Update Product : ".$product['id_product'].'->'.$produit_id . ' : ' .$product['name'];
					//}
	/*****mise a  jour du stock
	**************************/
	dol_syslog("CyberOffice_server_product::maj_stock  -> ".$product['warehouse'].'::'.$produit_id);
	if ($conf->global->CYBEROFFICE_stock==1) {
            $newobject->id=$produit_id;
            //$stock=$newobject->load_stock();
            $sql = "SELECT ps.reel, ps.rowid as product_stock_id";
            $sql.= " FROM ".MAIN_DB_PREFIX."product_stock as ps";
            $sql.= " WHERE ps.fk_entrepot = ".$product['warehouse'];
            $sql.= " AND ps.fk_product = ".$newobject->id;
            //$sql.= " FOR UPDATE";
            $resql = $db->query($sql);
            if ($resql) {
                if ($db->num_rows($resql) > 0) {
                    $res = $db->fetch_array($resql);
                    $stockW=$res['reel'];
                } else 
                    $stockW=0;
            } else 
                $stockW=0;
            $quantity=$product['quantity'] - $stockW;//$newobject->stock_reel;
            if ($quantity != 0) 
                $newobject->correct_stock($user, $product['warehouse'], $quantity, 0, 'PrestaShop');
	}
	/*****photo
	***********/
	/**********************************************************
	** IMPORTANT !! php directive allow_url_fopen must be on **
	***********************************************************/
	dol_syslog("CyberOffice_server_product::IMAGE -> ".$product['image']);
	
	/*******************
	** supression images
	********************/
	$sdir = $conf->product->multidir_output[$conf->entity];
							
	if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
            if (version_compare(DOL_VERSION, '3.8.0', '<'))
                $dir = $sdir .'/'. get_exdir($produit_id,2) . $produit_id ."/photos";
            else 
                $dir = $sdir .'/'. get_exdir($produit_id,2,0,0,$newobject,'product') . $produit_id ."/photos";
	} else 
            $dir = $sdir .'/'.dol_sanitizeFileName($product['reference']);
	dol_syslog("CyberOffice_server_product::IMAGE -> suppression".__LINE__.$dir);
	if (is_dir($dir))
	{
            if ($repertoire = opendir($dir))
            {
                while(false !== ($fichier = readdir($repertoire)))
		{
                    $chemin = $dir."/".$fichier;
                    $infos = pathinfo($chemin);
                    $extension = $infos['extension'];
                    dol_syslog("CyberOffice_server_product::IMAGE -> suppression".__LINE__.$chemin.'-'.$extension);
                    if($fichier!="." && $fichier!=".." && !is_dir($fichier) && in_array($extension, array('gif','jpg','jpeg','png','bmp')))
                    {
                        dol_syslog("CyberOffice_server_product::IMAGE -> suppression".__LINE__.$chemin.'-'.$product['images']);
                        if ($product['images'] != 'cybernull')
                        {
                            unlink($chemin);
                        }
                    }
		}
                dol_syslog("CyberOffice_server_product::IMAGE -> fermeture".__LINE__.$repertoire);
                closedir($repertoire);
            }
        }
        			
	////////////////
	foreach($product['images'] as $productimages) {
            dol_syslog("CyberOffice_server_product::IMAGE -> ".__LINE__.$productimages['name'].$productimages['url']);
            $picture = $productimages['url'];
            $name = $productimages['name'];
            $ext=preg_match('/(\.gif|\.jpg|\.jpeg|\.png|\.bmp)$/i',$picture,$reg);
            $imgfonction='';
            if (strtolower($reg[1]) == '.gif')  
                $ext= 'gif';
            if (strtolower($reg[1]) == '.png')  
                $ext= 'png';
            if (strtolower($reg[1]) == '.jpg')  
                $ext= 'jpeg';
            if (strtolower($reg[1]) == '.jpeg') 
                $ext= 'jpeg';
            if (strtolower($reg[1]) == '.bmp')  
                $ext= 'wbmp';
            $name=$name.'.'.$ext;
            $file = array("tmp_name"=>"images_temp/temp.$ext","name"=>$name);
            
            switch ($ext) { 
                case 'gif' : 
                    $img = imagecreatefromgif($picture); 
                    break; 
		case 'png' : 
                    $img = imagecreatefrompng($picture); 
                    break; 
		case 'jpeg' : 
                    if ( false !== (@$fd = fopen($picture, 'rb' )) )
                    {
                        if ( fread($fd,2) == chr(255).chr(216) )
                            $img = imagecreatefromjpeg($picture);
                        else
                            $img = imagecreatefrompng($picture);
                    } else
			$img = imagecreatefromjpeg($picture);
                    break;
		case 'wbmp' : 
                    $img = imagecreatefromwbmp($picture); 
                    break; 
            }
							
            $upload_dir = $conf->product->multidir_output[$conf->entity];
            
            $sdir = $conf->product->multidir_output[$conf->entity];
            
            if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
                if (version_compare(DOL_VERSION, '3.8.0', '<'))
                    $dir = $sdir .'/'. get_exdir($produit_id,2) . $produit_id ."/photos";
		else 
                    $dir = $sdir .'/'. get_exdir($produit_id,2,0,0,$newobject,'product') . $produit_id ."/photos";
            } else 
                $dir = $sdir .'/'.dol_sanitizeFileName($product['reference']);
            dol_syslog("CyberOffice_server_product::IMAGE dir ".$dir);
            if (! file_exists($dir)) 
                dol_mkdir($dir);//,'','0705');

            @call_user_func_array("image$ext",array($img,$dir.'/'.$file['name']));
            @imagedestroy($img);
            include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
            if (image_format_supported($dir.'/'.$file['name']) == 1)
            {
                $imgThumbSmall = vignette($dir.'/'.$file['name'], 160, 120, '_small', 50, "thumbs");
		$imgThumbMini = vignette($dir.'/'.$file['name'], 160, 120, '_mini', 50, "thumbs");
            }

            $list_ok.="<br/>Image Product : ".$dir.'/'.$file['name']. ' : ' .$product['name'];
            dol_syslog("CyberOffice_server_product::IMAGE Product : ".$dir.'/'.$file['name']. ' : ' .$product['name']);
        }
	/***** category 
	***************/
	if($newobject->id==0) 
            $newobject->fetch($produit_id);
	$sql  = 'DELETE cp
            FROM '.MAIN_DB_PREFIX.'categorie_product cp
            LEFT JOIN '.MAIN_DB_PREFIX.'categorie c ON (cp.fk_categorie = c.rowid)
             WHERE cp.fk_product='.$newobject->id . ' AND SUBSTRING(c.import_key,2,2)="'.$indice.'"';
	$resql = $db->query($sql);
	$categs = explode('-',$product['category']);
	foreach ($categs  as $categ)
	{
            $sql = "SELECT rowid";
            $sql.= " FROM ".MAIN_DB_PREFIX."categorie";
            $sql.= " WHERE import_key = 'P".$indice."-".$categ."'";
            dol_syslog("CyberOffice_server_product::fetch sql=".$sql);
            $resql = $db->query($sql);
            if ($resql) {
                if ($db->num_rows($resql) > 0) {
                    $res = $db->fetch_array($resql);
                    $res_rowid=$res['rowid'];
                } else 
                    $res_rowid=0;
            } else 
                $res_rowid=0;
            //if ($res_rowid==0) $res_rowid=$idparent0;
            if ($res_rowid != 0) {
		$cat = new Categorie($db);
		$result_Cat=$cat->fetch($res_rowid);
		$result_Cat=$cat->add_type($newobject,'product');
            }
	}
                        
	/****** manufacurer
	*******************/
					/*
					$newobject_m=new Societe($db);
					$sql = "SELECT rowid";
					$sql.= " FROM ".MAIN_DB_PREFIX."societe";
					$sql.= " WHERE import_key = 'P".$indice."m-".$product['id_manufacturer']."'";
					dol_syslog("CyberOffice_server_product::fetch sql=".$sql);
					$resql = $db->query($sql);
					if ($resql) {
						if ($db->num_rows($resql) > 0) {
							$res = $db->fetch_array($resql);
							$res_manufacturer=$res['rowid'];
						} else $res_manufacturer = 0;
					} else $res_manufacturer = 0;
					$newobject_m->status				= 1;
					$newobject_m->name 				= $product['manufacturer'];
					$newobject_m->client				= 0;
					$newobject_m->fournisseur			= 1;
					$newobject_m->import_key			= "P".$indice."m-".$product['id_manufacturer'];
					$newobject_m->code_fournisseur	= -1;
					if ($res_manufacturer == 0) $resultM = $newobject_m->create($user);
					*/
	if ($result <= 0) {
            $db->rollback();
            $error++;
            $list_id.= ' '.$product['id_product'];
            if (version_compare(DOL_VERSION, '3.8.0', '<'))
                $list_ref.= ' '.$newobject->libelle;
            else
                $list_ref.= ' '.$newobject->label;
            dol_syslog("CyberOffice_server_productERROR::product=".$product['id_product'].'::'.$result,LOG_ERR);
        } else 
            $db->commit();
				//}//fin foreach declinaison
			//}//fin if count
    }  //fin foreach
    // combination produit principale
    if(!empty($arr_combinaison)) {
        $idParentAndChild = [];
        foreach($arr_combinaison as $kCombin => $valCombin) {
            $expKCombin = explode('-',$kCombin);
            foreach($valCombin as $kComb => $vComb) {
                $explod_default = explode("-", $vComb);
                $posZero = substr($explod_default[0], 8);
                if($posZero == "0000") {
                    $expvComb = explode('-', $vComb);
                    $idParent[] = intval($expvComb[1]);
                    //Modification nom par defaut
                    //$sqlUpdateDefaultNameCombinaison = 'UPDATE '.MAIN_DB_PREFIX.'product set label = "'.$expKCombin[1].'" where rowid = '.intval($expvComb[1]);
                    //$ressqlUpdateDefaultNameCombinaison = $db->query($sqlUpdateDefaultNameCombinaison);
                    unset($valCombin[$kComb]);
                    $idParentAndChild[intval($expvComb[1])."-".$expKCombin[1]] = $valCombin;
                }
            }
        }
        
        foreach($idParentAndChild as $kp => $valp) {
            $exkp  = explode('-',$kp);
            foreach($valp as $kvalp => $vvalp) {
                $exvalp = explode('-',$vvalp);
                // ajout combination produit 
                $sqlVerif = "SELECT rowid FROM ".MAIN_DB_PREFIX."product_attribute_combination "
                    . " WHERE fk_product_parent = ".intval($exkp[0])." and fk_product_child = ".intval($exvalp[1])."";
                $res = $db->query($sqlVerif);
                $resultats = $db->fetch_object($res);
                if(empty($resultats)){
                    $sqlAssociateProduct = "INSERT INTO ".MAIN_DB_PREFIX."product_attribute_combination "
                        . " (fk_product_parent,fk_product_child,variation_price,variation_price_percentage,variation_weight,entity) "
                        . " values (".intval($exkp[0]).",".intval($exvalp[1]).",0,0,0,1)";
                    $db->query($sqlAssociateProduct);
                }
            }
        }
    }
}		
if (! $error || $error==0)
{
    //$db->commit();
    $objectresp=array('result'=>array('result_code'=>'OK', 'result_label'=>'ok'),'description'=>'ok');//$list_ok
    //$objectresp=array('result'=>'result_code','description'=>$list_ok);
} else {
    //$db->rollback();
    $error++;
    $errorcode='KO';
    $errorlabel=$list_ok.'<br/>'.$newobject->error;
}
	
if ($error && $error > 0)
{
    $objectresp = array('result'=>array('result_code' => $errorcode, 'result_label' => $errorlabel),'description'=>$list_id);
    //$objectresp = array('result'=> 'test','description'=>$list_id);
}
//$objectresp = array('result'=> 'test','description'=>$list_id);
return $objectresp;