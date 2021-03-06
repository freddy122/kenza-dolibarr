<?php
/**
 *	CyberOffice
 *
 *  @author    LVSinformatique <contact@lvsinformatique.com>
 *  @copyright 2014 LVSInformatique
 *	@license   NoLicence
 *  @version   1.2.31
 */		

// This is to make Dolibarr working with Plesk
define('NOCSRFCHECK', 1);

set_include_path($_SERVER['DOCUMENT_ROOT'].'/htdocs');
require_once '../master.inc.php';
require_once DOL_DOCUMENT_ROOT.'/cyberoffice/class/nusoap/lib/nusoap.php';         // Include SOAP
require_once DOL_DOCUMENT_ROOT.'/core/lib/ws.lib.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/cyberoffice/class/cyberoffice.class.php';
dol_syslog("cyberoffice::Call Dolibarr webservices interfaces::ServerProduct_ws");
//sleep(15);
//set_time_limit(3600);
@ini_set('default_socket_timeout', 160);
$langs->load("main");
    global $db,$conf,$langs;
    $authentication=array();
    $params=array();
$authentication=$_POST['authentication'];
$params= $_POST['params']; 
    $now=dol_now();
    dol_syslog("cyberoffice::Function: create login=".$authentication['login']);

    if ($authentication['entity']) $conf->entity=$authentication['entity'];

    // Init and check authentication
    $objectresp=array();
    $errorcode='';$errorlabel='';
    $error=0;
    $errortot=0;
    $fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);
    $error=0;
dol_syslog("CyberOffice_server_product::line=".__LINE__);
        include_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

        $newobject=new Product($db);
        $db->begin();
//dol_syslog("CyberOffice_server_product::params=".print_r($params));
        //echo "<pre>".print_r($params)."</pre>";die();
        dol_syslog("CyberOffice_server_product::nb produits=".count($params));
        $socid_old=0;
        
        $user = new User($db);
        $user->fetch('', $authentication['login'],'',0);
		$user->getrights();

        $cyber = new Cyberoffice;
		$cyber->entity = 0;
		$cyber->myurl = $authentication['myurl'];
		$indice = $cyber->numShop();

        $objectcat=new Categorie($db);
        $catparent0 = array();
		if (DOL_VERSION < '3.8.0')
			$catparent0 = $objectcat->rechercher(null,$cyber->myurl,0);
		else
			$catparent0 = $objectcat->rechercher(null,$cyber->myurl,'product');

        foreach ($catparent0 as $cat_parent0)
		{
			$idparent0 = $cat_parent0->id;
		}

	if (sizeof($params)>0)
        foreach ($params as $product)
		{
			//echo "<pre>".print_r($product)."</pre>";die();
			dol_syslog("CyberOffice_server_product::traitement produit=".$product['id_product']);
			//if (count($product['declinaison']) > 0) 
			//{
				//foreach ($product['declinaison']as $declinaison)
				//{
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
					if ($nbr === false) $nbr=0;
					$product_id_product = "P".$indice."-".substr($product['id_product'],0,$nbr);
					$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'product WHERE import_key="'.$product_id_product.'"';
					dol_syslog("CyberOffice_server_product::fetch combination sql=".$sql);
					if ($nbr>0) {
						$resql = $db->query($sql);
						if ($resql) {
							if ($db->num_rows($resql) > 0) {
								$res = $db->fetch_array($resql);
								$produit_id=$res['rowid'];
							} else $produit_id=0;
						} else $produit_id=0;
						if ($produit_id > 0) {
								$sql = "UPDATE ".MAIN_DB_PREFIX."product SET";
								$sql .= " import_key='P".$indice."-".$product['id_product']."'";
								$sql.= " WHERE rowid=".$produit_id;
								dol_syslog("server_product::update combination - sql=".$sql);
								$resql = $db->query($sql);
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
							} else $produit_id=0;
						} else $produit_id=0;
					if ($db->num_rows($resql) > 1 && $product['match'] == '{ref}') {
						//$error++;
						$list_ok.= "<br/>ERROR ref ".$product['reference']." x ".$db->num_rows($resql);
						dol_syslog("CyberOffice_server_product::ERROR ref ".$product['reference']." x ".$db->num_rows($resql));
						continue;
					}
					/*****creation
					**************/
					$newobject->price_base_type 	= 'HT';
					$newobject->price				= $product['price'];
					$newobject->price_ttc 			= price2num($product['price'] * (1 + ($product['tax_rate'] / 100)),'MU');
					$newobject->tva_tx				= $product['tax_rate'];
					if (DOL_VERSION < '3.8.0')
						$newobject->libelle				= $product['name'];
					else
						$newobject->label				= $product['name'];
					$newobject->description			= $product['description_short'];
					$newobject->array_options		= array("options_longdescript"=>trim($product['description']));
					$newobject->type				= 0;
					$newobject->status				= $product['active'];
					$newobject->status_buy			= 1;
					if ($conf->global->MAIN_MODULE_BARCODE) {
						$newobject->barcode				= ($product['ean13']?$product['ean13']:$product['upc']);
						$newobject->barcode_type		= ($product['ean13']?2:3);
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
						if (empty($newobject->ref) || !$newobject->ref) $newobject->ref = 'Presta'.$product['id_product'];
					}
					dol_syslog("CyberOffice_server_product::ref2 =".$newobject->ref);
					$user = new User($db);
					$Ruser=$user->fetch('', $authentication['login'],'',0);
					//echo "<pre>".print_r($user)."</pre>";die();
					
					/* vÃ©rification ref existant
					****************************/
					$sql = "SELECT count(*) as nb";
					$sql.= " FROM ".MAIN_DB_PREFIX."product";
					$sql.= " WHERE (ref = '" .$newobject->ref."' OR ref LIKE '".$newobject->ref."(%')";
					$sql.= " AND import_key != 'P".$indice."-".$product['id_product']."'";
					if ($product['match'] == '{ref}') $resultCheck = '';
						else $resultCheck = $db->query($sql);
					if ($resultCheck )
					{
						$obj = $db->fetch_object($resultCheck );
						if ($obj->nb > 0) $newobject->ref = $newobject->ref.'('.($obj->nb + 1).')';
					}

					$result=$produit_id;
					if ($produit_id == 0) {
						$newobject->oldcopy='';
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
					} //else {
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
							} else $produit_id=0;
						} else $produit_id=0;
						if ($db->num_rows($resql) > 1 && $product['match'] == '{ref}') {
							//$error++;
							$list_ok.= "<br/>ERROR ref ".$product['reference']." x ".$db->num_rows($resql);
							dol_syslog("CyberOffice_server_product::ERROR ref ".$product['reference']." x ".$db->num_rows($resql));
							continue;
						}
						//(dol_textishtml($object->description)?$object->description:dol_nl2br($object->description,1,true))
						$newobject->url					= $product['product_url'];
						if (DOL_VERSION < '3.8.0')
							$newobject->libelle				= $product['name'];
						else
							$newobject->label				= $product['name'];

						$newobject->description			= $product['description_short'];
						$newobject->array_options		= array("options_longdescript"=>trim($product['description']));
						$newobject->weight			 	= $product['weight'];
						$newobject->length 				= $product['width'];
						$newobject->ref 				= $product['reference'];
						$newobject->weight_units 		= 0;
						$newobject->length_units 		= -2;
						$newobject->price_base_type 	= 'HT';
						$newobject->price				= $product['price'];
						$newobject->tva_tx				= $product['tax_rate'];
						$newobject->price_ttc 			= price2num($product['price'] * (1 + ($product['tax_rate'] / 100)),'MU');
						$newobject->id					= $produit_id;
						if ($conf->global->MAIN_MODULE_BARCODE) {
							$newobject->barcode				= ($product['ean13']?$product['ean13']:$product['upc']);
							$newobject->barcode_type		= ($product['ean13']?2:3);
						}
						if ($produit_id>0) {
							/* vÃ©rification ref existant
							****************************/
							$sql = "SELECT count(*) as nb";
							$sql.= " FROM ".MAIN_DB_PREFIX."product";
							$sql.= " WHERE (ref = '" .$newobject->ref."' OR ref LIKE '".$newobject->ref."(%')";
							$sql.= " AND import_key != 'P".$indice."-".$product['id_product']."'";
							if ($product['match'] == '{ref}') $resultCheck = '';
								else $resultCheck = $db->query($sql);
							if ($resultCheck )
							{
								$obj = $db->fetch_object($resultCheck );
								if ($obj->nb > 0) $newobject->ref = $newobject->ref.'('.($obj->nb + 1).')';
							}

							$product_price = new Product($db);
							$product_price->fetch($produit_id);
							if (round($product_price->price,3) != round($product['price'],3) || round($product_price->tva_tx,3) != round($product['tax_rate'],3))
								$newobject->updatePrice($product['price'], 'HT', $user, $product['tax_rate']);
							$newobject->oldcopy='';
							$resultS_U=$newobject->update($produit_id,$user);
						}
						if ($resultS_U> 0) $list_ok.="<br/>Update Product : ".$product['id_product'].'->'.$produit_id . ' : ' .$product['name'];
					//}
					/*****mise Ã  jour du stock
					**************************/
					if ($conf->global->CYBEROFFICE_stock==1) {
						$newobject->id=$produit_id;
						//$stock=$newobject->load_stock();
						$sql = "SELECT ps.reel, ps.pmp, ps.rowid as product_stock_id";
						$sql.= " FROM ".MAIN_DB_PREFIX."product_stock as ps";
						$sql.= " WHERE ps.fk_entrepot = ".$product['warehouse'];
						$sql.= " AND ps.fk_product = ".$newobject->id;
						$resql = $db->query($sql);
						if ($resql) {
							if ($db->num_rows($resql) > 0) {
								$res = $db->fetch_array($resql);
								$stockW=$res['reel'];
							} else $stockW=0;
						} else $stockW=0;
						$quantity=$product['quantity'] - $stockW;//$newobject->stock_reel;
						if ($quantity != 0) $newobject->correct_stock($user, $product['warehouse'], $quantity, 0, 'PrestaShop');
					}
					/*****photo
					***********/
					dol_syslog("CyberOffice_server_product::IMAGE -> ".$product['image']);
					if( $product['image'] ) {
						//if (DOL_VERSION < '3.8.0') {
							$picture = $product['image'];
							$name = explode("/",$picture);
							$name = $name[sizeof($name)-1];
							$ext=preg_match('/(\.gif|\.jpg|\.jpeg|\.png|\.bmp)$/i',$picture,$reg);
							$imgfonction='';
							if (strtolower($reg[1]) == '.gif')  $ext= 'gif';
							if (strtolower($reg[1]) == '.png')  $ext= 'png';
							if (strtolower($reg[1]) == '.jpg')  $ext= 'jpeg';
							if (strtolower($reg[1]) == '.jpeg') $ext= 'jpeg';
							if (strtolower($reg[1]) == '.bmp')  $ext= 'wbmp';
			
							$file = array("tmp_name"=>"images_temp/temp.$ext","name"=>$name);
									
							$img = @call_user_func_array("imagecreatefrom".$ext,array($picture));
							
							$upload_dir = $conf->product->multidir_output[$conf->entity];
									
							$sdir = $conf->product->multidir_output[$conf->entity];
							//$dir = $sdir .'/'. get_exdir($produit_id,2) . $produit_id ."/";
							//$dir .= "photos/";
							
							if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
								if (DOL_VERSION < '3.8.0')
									$dir = $sdir .'/'. get_exdir($produit_id,2) . $produit_id ."/photos";
								else $dir = $sdir .'/'. get_exdir($produit_id,2,0,0,$newobject,'product') . $produit_id ."/photos";
							} else 
								$dir = $sdir .'/'.dol_sanitizeFileName($product['reference']);
							dol_syslog("CyberOffice_server_product::IMAGE dir ".$dir);
							if (! file_exists($dir)) dol_mkdir($dir);//,'','0705');
							
							@call_user_func_array("image$ext",array($img,$dir.'/'.$file['name']));
							@imagedestroy($img);
							include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
									if (image_format_supported($dir.'/'.$file['name']) == 1)
									{
										// Create small thumbs for image (Ratio is near 16/9)
										// Used on logon for example
										$imgThumbSmall = vignette($dir.'/'.$file['name'], 160, 120, '_small', 50, "thumbs");
										// Create mini thumbs for image (Ratio is near 16/9)
										// Used on menu or for setup page for example
										$imgThumbMini = vignette($dir.'/'.$file['name'], 160, 120, '_mini', 50, "thumbs");
									}

							$list_ok.="<br/>Image Product : ".$dir.'/'.$file['name']. ' : ' .$product['name'];
							dol_syslog("CyberOffice_server_product::IMAGE Product : ".$dir.'/'.$file['name']. ' : ' .$product['name']);
						/*
						} else {
							$objectIm = new Product($db);
							$resultIm = $objectIm->fetch($produit_id);
							$picture = $product['image'];
							$name = explode("/",$picture);
						    if (! empty($conf->product->enabled)) $upload_dir = $conf->product->multidir_output[$objectIm->entity].'/'.get_exdir(0, 0, 0, 1, $objectIm, 'product').dol_sanitizeFileName($objectIm->ref);
						    elseif (! empty($conf->service->enabled)) $upload_dir = $conf->service->multidir_output[$objectIm->entity].'/'.get_exdir(0, 0, 0, 1, $objectIm, 'product').dol_sanitizeFileName($objectIm->ref);
							// $upload_dir, $allowoverwrite=0, $donotupdatesession=0, $varfiles='addedfile' -->
							//dol_add_file_process($upload_dir, 0, 1, 'userfile');
							if (dol_mkdir($upload_dir) >= 0) {
								// Define $destpath (path to file including filename) and $destfile (only filename)
								$destpath=$upload_dir . "/" . $name;
								$destfile=$name;
								//$resupload = dol_move_uploaded_file($_FILES[$varfiles]['tmp_name'], $destpath, $allowoverwrite, 0, $_FILES[$varfiles]['error'], 0, $varfiles);
								$src_file_osencoded=dol_osencode($picture);
								$file_name_osencoded=dol_osencode($destpath);
								$resupload =move_uploaded_file($src_file_osencoded, $file_name_osencoded);

								if (is_numeric($resupload) && $resupload > 0)
								{
									include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
									if (image_format_supported($destpath) == 1)
									{
										// Create small thumbs for image (Ratio is near 16/9)
										// Used on logon for example
										$imgThumbSmall = vignette($destpath, 160, 120, '_small', 50, "thumbs");
										// Create mini thumbs for image (Ratio is near 16/9)
										// Used on menu or for setup page for example
										$imgThumbMini = vignette($destpath, 160, 120, '_mini', 50, "thumbs");
									}
								}
							}
						}
						*/
					}
					/***** category 
					***************/
					if($newobject->id==0) $newobject->fetch($produit_id);
					$sql  = "DELETE FROM ".MAIN_DB_PREFIX."categorie_product";
					$sql .= " WHERE fk_product=".$newobject->id;
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
							} else $res_rowid=0;
						} else $res_rowid=0;
						if ($res_rowid==0) $res_rowid=$idparent0;
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
						if (DOL_VERSION < '3.8.0')
							$list_ref.= ' '.$newobject->libelle;
						else
							$list_ref.= ' '.$newobject->label;
						dol_syslog("CyberOffice_server_productERROR::product=".$product['id_product'].'::'.$result,LOG_ERR);
					} else $db->commit();
				//}//fin foreach declinaison
			//}//fin if count
		}  //fin foreach
		
  

        if (! $error || $error==0)
        {
            //$db->commit();
            $objectresp=array('resultc'=>array('result_code'=>'OK', 'result_label'=>''),'description'=>$list_ok);
        }
        else
        {
            //$db->rollback();
            $error++;
            $errorcode='KO';
            $errorlabel=$list_ok.'<br/>'.$newobject->error;
        }
	
    if ($error && $error > 0)
    {
        $objectresp = array('resultd'=>array('result_code' => $errorcode, 'result_label' => $errorlabel),'description'=>$list_id);
    }

    return $objectresp;