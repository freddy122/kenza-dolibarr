<?php
/* Copyright (C) 2001-2007	Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2016	Laurent Destailleur	 <eldy@users.sourceforge.net>
 * Copyright (C) 2005		Eric Seigne		     <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2015	Regis Houssin		 <regis.houssin@capnetworks.com>
 * Copyright (C) 2006		Andre Cianfarani	 <acianfa@free.fr>
 * Copyright (C) 2006		Auguria SARL		 <info@auguria.org>
 * Copyright (C) 2010-2015	Juanjo Menent		 <jmenent@2byte.es>
 * Copyright (C) 2013-2016	Marcos García		 <marcosgdf@gmail.com>
 * Copyright (C) 2012-2013	Cédric Salvador		 <csalvador@gpcsolutions.fr>
 * Copyright (C) 2011-2020	Alexandre Spangaro	 <aspangaro@open-dsi.fr>
 * Copyright (C) 2014		Cédric Gross		 <c.gross@kreiz-it.fr>
 * Copyright (C) 2014-2015	Ferran Marcet		 <fmarcet@2byte.es>
 * Copyright (C) 2015		Jean-François Ferry	 <jfefe@aternatik.fr>
 * Copyright (C) 2015		Raphaël Doursenaud	 <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2016		Charlie Benke		 <charlie@patas-monkey.com>
 * Copyright (C) 2016		Meziane Sof		     <virtualsof@yahoo.fr>
 * Copyright (C) 2017		Josep Lluís Amador	 <joseplluis@lliuretic.cat>
 * Copyright (C) 2019       Frédéric France      <frederic.france@netlogic.fr>
 * Copyright (C) 2019-2020  Thibault FOUCART     <support@ptibogxiv.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/product/card.php
 *  \ingroup    product
 *  \brief      Page to show product
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/canvas.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/genericobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/product/modules_product.class.php';

if (!empty($conf->propal->enabled))     require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
if (!empty($conf->facture->enabled))    require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
if (!empty($conf->commande->enabled))   require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
if (!empty($conf->accounting->enabled)) require_once DOL_DOCUMENT_ROOT.'/core/lib/accounting.lib.php';
if (!empty($conf->accounting->enabled)) require_once DOL_DOCUMENT_ROOT.'/core/class/html.formaccounting.class.php';
if (!empty($conf->accounting->enabled)) require_once DOL_DOCUMENT_ROOT.'/accountancy/class/accountingaccount.class.php';

// Load translation files required by the page
$langs->loadLangs(array('products', 'other'));
if (!empty($conf->stock->enabled)) $langs->load("stocks");
if (!empty($conf->facture->enabled)) $langs->load("bills");
if (!empty($conf->productbatch->enabled)) $langs->load("productbatch");

$mesg = ''; $error = 0; $errors = array();

$refalreadyexists = 0;

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$type = (GETPOST('type', 'int') !== '') ? GETPOST('type', 'int') : Product::TYPE_PRODUCT;
$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'view');
$cancel = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$socid = GETPOST('socid', 'int');
$duration_value = GETPOST('duration_value', 'int');
$duration_unit = GETPOST('duration_unit', 'alpha');

$accountancy_code_sell = GETPOST('accountancy_code_sell', 'alpha');
$accountancy_code_sell_intra = GETPOST('accountancy_code_sell_intra', 'alpha');
$accountancy_code_sell_export = GETPOST('accountancy_code_sell_export', 'alpha');
$accountancy_code_buy = GETPOST('accountancy_code_buy', 'alpha');
$accountancy_code_buy_intra = GETPOST('accountancy_code_buy_intra', 'alpha');
$accountancy_code_buy_export = GETPOST('accountancy_code_buy_export', 'alpha');

// by default 'alphanohtml' (better security); hidden conf MAIN_SECURITY_ALLOW_UNSECURED_LABELS_WITH_HTML allows basic html
$label_security_check = empty($conf->global->MAIN_SECURITY_ALLOW_UNSECURED_LABELS_WITH_HTML) ? 'alphanohtml' : 'restricthtml';

if (!empty($user->socid)) $socid = $user->socid;

$object = new Product($db);
$object->type = $type; // so test later to fill $usercancxxx is correct
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

if ($id > 0 || !empty($ref))
{
    $result = $object->fetch($id, $ref);

    if (!empty($conf->product->enabled)) $upload_dir = $conf->product->multidir_output[$object->entity].'/'.get_exdir(0, 0, 0, 0, $object, 'product').dol_sanitizeFileName($object->ref);
    elseif (!empty($conf->service->enabled)) $upload_dir = $conf->service->multidir_output[$object->entity].'/'.get_exdir(0, 0, 0, 0, $object, 'product').dol_sanitizeFileName($object->ref);

    if (!empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))    // For backward compatiblity, we scan also old dirs
    {
        if (!empty($conf->product->enabled)) $upload_dirold = $conf->product->multidir_output[$object->entity].'/'.substr(substr("000".$object->id, -2), 1, 1).'/'.substr(substr("000".$object->id, -2), 0, 1).'/'.$object->id."/photos";
        else $upload_dirold = $conf->service->multidir_output[$object->entity].'/'.substr(substr("000".$object->id, -2), 1, 1).'/'.substr(substr("000".$object->id, -2), 0, 1).'/'.$object->id."/photos";
    }
}

$modulepart = 'product';

// Get object canvas (By default, this is not defined, so standard usage of dolibarr)
$canvas = !empty($object->canvas) ? $object->canvas : GETPOST("canvas");
$objcanvas = null;
if (!empty($canvas))
{
    require_once DOL_DOCUMENT_ROOT.'/core/class/canvas.class.php';
    $objcanvas = new Canvas($db, $action);
    $objcanvas->getCanvas('product', 'card', $canvas);
}

// Security check
$fieldvalue = (!empty($id) ? $id : (!empty($ref) ? $ref : ''));
$fieldtype = (!empty($id) ? 'rowid' : 'ref');
$result = restrictedArea($user, 'produit|service', $fieldvalue, 'product&product', '', '', $fieldtype);

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('productcard', 'globalcard'));



/*
 * Actions
 */

if ($cancel) $action = '';

$usercanread = (($object->type == Product::TYPE_PRODUCT && $user->rights->produit->lire) || ($object->type == Product::TYPE_SERVICE && $user->rights->service->lire));
$usercancreate = (($object->type == Product::TYPE_PRODUCT && $user->rights->produit->creer) || ($object->type == Product::TYPE_SERVICE && $user->rights->service->creer));
$usercandelete = (($object->type == Product::TYPE_PRODUCT && $user->rights->produit->supprimer) || ($object->type == Product::TYPE_SERVICE && $user->rights->service->supprimer));
$createbarcode = empty($conf->barcode->enabled) ? 0 : 1;
if (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->barcode->creer_advance)) $createbarcode = 0;

$parameters = array('id'=>$id, 'ref'=>$ref, 'objcanvas'=>$objcanvas);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
    // Type
    if ($action == 'setfk_product_type' && $usercancreate)
    {
    	$result = $object->setValueFrom('fk_product_type', GETPOST('fk_product_type'), '', null, 'text', '', $user, 'PRODUCT_MODIFY');
    	header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
    	exit;
    }

    // Actions to build doc
    $upload_dir = $conf->product->dir_output;
    $permissiontoadd = $usercancreate;
    include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

    include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

    // Barcode type
    if ($action == 'setfk_barcode_type' && $createbarcode)
    {
        $result = $object->setValueFrom('fk_barcode_type', GETPOST('fk_barcode_type'), '', null, 'text', '', $user, 'PRODUCT_MODIFY');
    	header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
    	exit;
    }

    // Barcode value
    if ($action == 'setbarcode' && $createbarcode)
    {
        require DOL_DOCUMENT_ROOT . '/barcodegen1d/generated/vendor/autoload.php';
        
        // ISBN, EAN13, EAN8, UPC
        $object->fetch_barcode();
        //print_r($object->barcode_type_label);die();
        if(($object->barcode_type_label == "ISBN" || $object->barcode_type_label == "EAN13")  && intval(strlen(GETPOST('barcode')) != 13)) {
            $langs->load("errors");
            $errors[] = 'La taille de valeur du codebare doit égale à 13 POUR EAN13 et ISBN';
            $error++;
            setEventMessages($errors, null, 'errors');
            header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
            exit;
        }
        if(intval(strlen(GETPOST('barcode')) != 12) && $object->barcode_type_label == "UPC") {
            $langs->load("errors");
            $errors[] = 'La taille de valeur du codebare pour UPC doit  être égale à 12';
            $error++;
            setEventMessages($errors, null, 'errors');
            header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
            exit;
        }
        
        if(intval(strlen(GETPOST('barcode')) != 8) && $object->barcode_type_label == "EAN8") {
            $langs->load("errors");
            $errors[] = 'La taille de valeur du codebare pour EAN8 doit égale à 8';
            $error++;
            setEventMessages($errors, null, 'errors');
            header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
            exit;
        }
            
        if(!empty(GETPOST('barcode'))) {
            if(!empty($object->barcode_type_label)) { // ean8
                if($object->barcode_type_label == "EAN8") { // ean8
                    $code = new BarcodeBakery\Barcode\BCGean8();
                    $code->parse(GETPOST('barcode'));
                    $generatedBareCode = $code->getLabel().$code->getChecksum();
                    $result = $object->check_barcode($generatedBareCode, GETPOST('barcode_type_code'));
                }else if($object->barcode_type_label == "EAN13") { // ean-13
                    $code = new BarcodeBakery\Barcode\BCGean13();
                    $code->parse(GETPOST('barcode'));
                    $generatedBareCode = $code->getLabel().$code->getChecksum();
                    $result = $object->check_barcode($generatedBareCode, GETPOST('barcode_type_code'));
                }else if($object->barcode_type_label == "ISBN") { // isbn
                    $code = new BarcodeBakery\Barcode\BCGisbn();
                    $code->parse(GETPOST('barcode'));
                    $generatedBareCode = $code->getLabel().$code->getChecksum();
                    $result = $object->check_barcode($generatedBareCode, GETPOST('barcode_type_code'));
                }else if($object->barcode_type_label == "UPC") { // upc
                    $code = new BarcodeBakery\Barcode\BCGupca();
                    $code->parse(GETPOST('barcode'));
                    $generatedBareCode = $code->getLabel().$code->getChecksum();
                    $result = $object->check_barcode($generatedBareCode, GETPOST('barcode_type_code'));
                }
            }
        }
       // print_r($generatedBareCode);die();
        if ($result >= 0)
        {
            if(!empty(GETPOST('barcode'))) {
                $result = $object->setValueFrom('barcode', $generatedBareCode, '', null, 'text', '', $user, 'PRODUCT_MODIFY');
            }else{
                $result = $object->setValueFrom('barcode', "", '', null, 'text', '', $user, 'PRODUCT_MODIFY');
            }
            header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
            exit;
        }
        else
        {
            $langs->load("errors");
            if ($result == -1) $errors[] = 'ErrorBadBarCodeSyntax';
            elseif ($result == -2) $errors[] = 'ErrorBarCodeRequired';
            elseif ($result == -3) $errors[] = 'ErrorBarCodeAlreadyUsed';
            else $errors[] = 'FailedToValidateBarCode';

            $error++;
            setEventMessages($errors, null, 'errors');
        }
    }

    // Add a product or service
    if ($action == 'add' && $usercancreate)
    {
        $error = 0;

        if (!GETPOST('label', $label_security_check))
        {
            setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('Label')), null, 'errors');
            $action = "create";
            $error++;
        }
        if (empty($ref))
        {
            setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('Ref')), null, 'errors');
            $action = "create";
            $error++;
        }
        /*if (empty(GETPOST('nombre_produit_en_stock')))
        {
            setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('NombreProduitEnStock')), null, 'errors');
            $action = "create";
            $error++;
        }*/
        if (empty(GETPOST('price')))
        {
            setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('Prix de vente')), null, 'errors');
            $action = "create";
            $error++;
        }
        if (!empty($duration_value) && empty($duration_unit))
        {
            setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('Unit')), null, 'errors');
            $action = "create";
            $error++;
        }
        
        if ( (!empty(GETPOST("ref_prod_fourn")) || !empty(GETPOST("best_purchase_price")) || !empty(GETPOST("coefficient_of_return"))) &&  GETPOST("id_fourn") == -1 )
        {
            setEventMessages("Le champ fournisseur est réquis si l\'un de champs Référence prod fournisseur,Meilleur prix d'achat,Coefficient de révient sont renseigné", null, 'errors');
            $action = "create";
            $error++;
        }
        
        if(GETPOST("id_fourn") != -1 && (empty(GETPOST("ref_prod_fourn")) || empty(GETPOST("best_purchase_price")) || empty(GETPOST("coefficient_of_return")))) {
            setEventMessages("Le champ Référence prod fournisseur,Meilleur prix d'achat,Coefficient de révient  sont réquises si le champ fournisseur est renseigné", null, 'errors');
            $action = "create";
            $error++;
        }
        
        if(!empty(GETPOST('barcode'))) {
            if(!empty(GETPOST('fk_barcode_type'))) {
                if(intval(GETPOST('fk_barcode_type')) == 1 && intval(strlen(GETPOST('barcode'))) != 8 ) {
                   setEventMessages('La taille de valeur du codebare doit égale à 8  pour EAN8',null,"errors");
                   $action = "create";
                    $error++;
                }

                if( (intval(GETPOST('fk_barcode_type')) == 2 || intval(GETPOST('fk_barcode_type')) == 4) && intval(strlen(GETPOST('barcode'))) != 13 ) {
                    setEventMessages('La taille de valeur du codebare doit égale à 13 POUR EAN13 et ISBN',null,"errors");
                    $action = "create";
                    $error++;
                }
            }
        }

        if (!$error)
        {
	        $units = GETPOST('units', 'int');

            $object->ref                   = $ref;
            $object->label                 = GETPOST('label', $label_security_check);
            $object->price_base_type       = GETPOST('price_base_type', 'aZ09');

            if ($object->price_base_type == 'TTC')
            	$object->price_ttc = GETPOST('price');
            else
            	$object->price = GETPOST('price');
            if ($object->price_base_type == 'TTC')
            	$object->price_min_ttc = GETPOST('price_min');
            else
            	$object->price_min = GETPOST('price_min');

	        $tva_tx_txt = GETPOST('tva_tx', 'alpha'); // tva_tx can be '8.5'  or  '8.5*'  or  '8.5 (XXX)' or '8.5* (XXX)'

	        // We must define tva_tx, npr and local taxes
	        $vatratecode = '';
	        $tva_tx = preg_replace('/[^0-9\.].*$/', '', $tva_tx_txt); // keep remove all after the numbers and dot
	        $npr = preg_match('/\*/', $tva_tx_txt) ? 1 : 0;
	        $localtax1 = 0; $localtax2 = 0; $localtax1_type = '0'; $localtax2_type = '0';
	        // If value contains the unique code of vat line (new recommanded method), we use it to find npr and local taxes
	        if (preg_match('/\((.*)\)/', $tva_tx_txt, $reg))
	        {
	            // We look into database using code (we can't use get_localtax() because it depends on buyer that is not known). Same in update price.
	            $vatratecode = $reg[1];
	            // Get record from code
	            $sql = "SELECT t.rowid, t.code, t.recuperableonly, t.localtax1, t.localtax2, t.localtax1_type, t.localtax2_type";
	            $sql .= " FROM ".MAIN_DB_PREFIX."c_tva as t, ".MAIN_DB_PREFIX."c_country as c";
	            $sql .= " WHERE t.fk_pays = c.rowid AND c.code = '".$mysoc->country_code."'";
	            $sql .= " AND t.taux = ".((float) $tva_tx)." AND t.active = 1";
	            $sql .= " AND t.code ='".$vatratecode."'";
	            $resql = $db->query($sql);
	            if ($resql)
	            {
	                $obj = $db->fetch_object($resql);
	                $npr = $obj->recuperableonly;
	                $localtax1 = $obj->localtax1;
	                $localtax2 = $obj->localtax2;
	                $localtax1_type = $obj->localtax1_type;
	                $localtax2_type = $obj->localtax2_type;
	            }
	        }

	        $object->default_vat_code = $vatratecode;
	        $object->tva_tx = $tva_tx;
	        $object->tva_npr = $npr;
	        $object->localtax1_tx = $localtax1;
	        $object->localtax2_tx = $localtax2;
	        $object->localtax1_type = $localtax1_type;
	        $object->localtax2_type = $localtax2_type;

            $object->type               	 = $type;
            $object->status             	 = GETPOST('statut');
            $object->status_buy            = GETPOST('statut_buy');
			$object->status_batch = GETPOST('status_batch');

            $object->barcode_type          = GETPOST('fk_barcode_type');
            //$object->barcode = GETPOST('barcode');
            if(!empty(GETPOST('barcode'))) {
                require DOL_DOCUMENT_ROOT . '/barcodegen1d/generated/vendor/autoload.php';
                if(!empty(GETPOST('fk_barcode_type'))) {
                    if(GETPOST('fk_barcode_type') == 1) { // ean8
                        $code = new BarcodeBakery\Barcode\BCGean8();
                        $code->parse(GETPOST('barcode'));
                        $object->barcode = $code->getLabel().$code->getChecksum();
                    }else if(GETPOST('fk_barcode_type') == 2) { // ean-13
                        $code = new BarcodeBakery\Barcode\BCGean13();
                        $code->parse(GETPOST('barcode'));
                        $object->barcode = $code->getLabel().$code->getChecksum();
                    }else if(GETPOST('fk_barcode_type') == 4) { // isbn
                        $code = new BarcodeBakery\Barcode\BCGisbn();
                        $code->parse(GETPOST('barcode'));
                        $object->barcode = $code->getLabel().$code->getChecksum();
                    }else if(GETPOST('fk_barcode_type') == 3) { // upc
                        $code = new BarcodeBakery\Barcode\BCGupca();
                        $code->parse(GETPOST('barcode'));
                        $object->barcode = $code->getLabel().$code->getChecksum();
                    }
                }
            }
            // Set barcode_type_xxx from barcode_type id
            $stdobject = new GenericObject($db);
    	    $stdobject->element = 'product';
            $stdobject->barcode_type = GETPOST('fk_barcode_type');
            $result = $stdobject->fetch_barcode();
            if ($result < 0)
            {
            	$error++;
            	$mesg = 'Failed to get bar code type information ';
            	setEventMessages($mesg.$stdobject->error, $mesg.$stdobject->errors, 'errors');
            }
            $object->barcode_type_code      = $stdobject->barcode_type_code;
            $object->barcode_type_coder     = $stdobject->barcode_type_coder;
            $object->barcode_type_label     = $stdobject->barcode_type_label;

            $object->description        	 = dol_htmlcleanlastbr(GETPOST('desc', 'none'));
            $object->url = GETPOST('url');
            $object->note_private          	 = dol_htmlcleanlastbr(GETPOST('note_private', 'none'));
            $object->note               	 = $object->note_private; // deprecated
            $object->customcode              = GETPOST('customcode', 'alphanohtml');
            $object->country_id              = GETPOST('country_id', 'int');
            $object->duration_value     	 = $duration_value;
            $object->duration_unit      	 = $duration_unit;
            $object->fk_default_warehouse	 = GETPOST('fk_default_warehouse');
            $object->seuil_stock_alerte 	 = GETPOST('seuil_stock_alerte') ?GETPOST('seuil_stock_alerte') : 0;
            $object->desiredstock          = GETPOST('desiredstock') ?GETPOST('desiredstock') : 0;
            $object->canvas             	 = GETPOST('canvas');
            $object->net_measure           = GETPOST('net_measure');
            $object->net_measure_units     = GETPOST('net_measure_units'); // This is not the fk_unit but the power of unit
            $object->weight             	 = GETPOST('weight');
            $object->weight_units       	 = GETPOST('weight_units'); // This is not the fk_unit but the power of unit
            $object->length             	 = GETPOST('size');
            $object->length_units       	 = GETPOST('size_units'); // This is not the fk_unit but the power of unit
            $object->width = GETPOST('sizewidth');
            $object->height             	 = GETPOST('sizeheight');
            $object->surface            	 = GETPOST('surface');
            $object->surface_units      	 = GETPOST('surface_units'); // This is not the fk_unit but the power of unit
            $object->volume             	 = GETPOST('volume');
            $object->volume_units       	 = GETPOST('volume_units'); // This is not the fk_unit but the power of unit
            $object->finished           	 = GETPOST('finished', 'alpha');
            $object->fk_unit = GETPOST('units', 'alpha'); // This is the fk_unit of sale

	        $accountancy_code_sell = GETPOST('accountancy_code_sell', 'alpha');
	        $accountancy_code_sell_intra = GETPOST('accountancy_code_sell_intra', 'alpha');
	        $accountancy_code_sell_export = GETPOST('accountancy_code_sell_export', 'alpha');
	        $accountancy_code_buy = GETPOST('accountancy_code_buy', 'alpha');
			$accountancy_code_buy_intra = GETPOST('accountancy_code_buy_intra', 'alpha');
			$accountancy_code_buy_export = GETPOST('accountancy_code_buy_export', 'alpha');

			if ($accountancy_code_sell <= 0) { $object->accountancy_code_sell = ''; } else { $object->accountancy_code_sell = $accountancy_code_sell; }
			if ($accountancy_code_sell_intra <= 0) { $object->accountancy_code_sell_intra = ''; } else { $object->accountancy_code_sell_intra = $accountancy_code_sell_intra; }
			if ($accountancy_code_sell_export <= 0) { $object->accountancy_code_sell_export = ''; } else { $object->accountancy_code_sell_export = $accountancy_code_sell_export; }
			if ($accountancy_code_buy <= 0) { $object->accountancy_code_buy = ''; } else { $object->accountancy_code_buy = $accountancy_code_buy; }
			if ($accountancy_code_buy_intra <= 0) { $object->accountancy_code_buy_intra = ''; } else { $object->accountancy_code_buy_intra = $accountancy_code_buy_intra; }
			if ($accountancy_code_buy_export <= 0) { $object->accountancy_code_buy_export = ''; } else { $object->accountancy_code_buy_export = $accountancy_code_buy_export; }

            // MultiPrix
            if (!empty($conf->global->PRODUIT_MULTIPRICES))
            {
                for ($i = 2; $i <= $conf->global->PRODUIT_MULTIPRICES_LIMIT; $i++)
                {
                    if (GETPOSTISSET("price_".$i))
                    {
                        $object->multiprices["$i"] = price2num($_POST["price_".$i], 'MU');
                        $object->multiprices_base_type["$i"] = $_POST["multiprices_base_type_".$i];
                    }
                    else
                    {
                        $object->multiprices["$i"] = "";
                    }
                }
            }

            // Fill array 'array_options' with data from add form
        	$ret = $extrafields->setOptionalsFromPost(null, $object);
			if ($ret < 0) $error++;

			if (!$error)
			{
            	$id = $object->create($user);
			}

            if ($id > 0)
            {
                        $supplier = new Fournisseur($db);
                        $result = $supplier->fetch(intval(GETPOST("id_fourn")));
                        
                        $productFournisseur = new ProductFournisseur($db);
                        $productFournisseur->fetch($id);
                        
                        if(!empty(GETPOST("id_fourn")) && !empty(GETPOST("best_purchase_price", 'alpha')) && !empty(GETPOST("ref_prod_fourn"))) {
                            $ret = $productFournisseur->update_buyprice(1, GETPOST("best_purchase_price"), $user, $_POST["price_base_type_prd_frs"], $supplier, $_POST["oselDispo"], GETPOST("ref_prod_fourn", 'alpha'), $tva_tx, $_POST["charges"], 0, 0, 0, 0, "", array(), '', 0, 'HT', 1, '', "", GETPOST('barcode'), "");
                        }
                        
                        if(!empty(GETPOST("coefficient_of_return"))) {
                            $object->coef_revient = floatval(str_replace(",",".",GETPOST("coefficient_of_return")));
                        }
                        
                        if(!empty(GETPOST("cost_of_return"))) {
                            $object->cout_revient = floatval(str_replace(",",".",GETPOST("cost_of_return")));
                        }
                        
                        if(!empty(GETPOST("price_of_return"))) {
                            $object->cost_price = floatval(str_replace(",",".",GETPOST("price_of_return")));
                        }
                        
			if(!empty(GETPOST("carte_metisse"))) {
                            $object->carte_metisse = floatval(str_replace(",",".",GETPOST("carte_metisse")));
                        }
                        /*if(!empty(GETPOST("average_price_weighted"))) {
                            $sqlUpdatePmp = 'update '.MAIN_DB_PREFIX.'product set pmp = '.GETPOST("average_price_weighted").' where rowid='.$id;
                            $db->query($sqlUpdatePmp);
                        }*/
                        
                        if(!empty(GETPOST("margin_product"))) {
                            $object->margin_product = floatval(str_replace(",",".",GETPOST("margin_product")));
                        }
                        
                        if(!empty(GETPOST("suggest_price"))) {
                            $object->suggest_price = floatval(str_replace(",",".",GETPOST("suggest_price")));
                        }
                        
                        if(!empty(GETPOST("coeff_vente_ttc"))) {
                            $object->coeff_vente_ttc = floatval(str_replace(",",".",GETPOST("coeff_vente_ttc")));
                        }
                        
                        if(!empty(GETPOST("margin_rate_as_percentage"))) {
                            $object->margin_rate_as_percentage = floatval(str_replace(",",".",GETPOST("margin_rate_as_percentage")));
                        }
                        
                        if(!empty(GETPOST("margin_ttc"))) {
                            $object->margin_ttc = floatval(str_replace(",",".",GETPOST("margin_ttc")));
                        }
                        
                        if(!empty(GETPOST("brand_rate_in_percent"))) {
                            $object->brand_rate_in_percent = floatval(str_replace(",",".",GETPOST("brand_rate_in_percent")));
                        }
                        
                        if(!empty(GETPOST("selling_price_excl_tax"))) {
                            $object->selling_price_excl_tax = floatval(str_replace(",",".",GETPOST("selling_price_excl_tax")));
                        }
                        
                        if(!empty(GETPOST("vat_price"))) {
                            $object->vat_price = floatval(str_replace(",",".",GETPOST("vat_price")));
                        }
                        
                        
			$object->update($id, $user);
                        
                        // Category association
                        $categories = GETPOST('categories', 'array');
                        $object->setCategories($categories);

                        if (!empty($backtopage))
                        {
                            $backtopage = preg_replace('/--IDFORBACKTOPAGE--/', $object->id, $backtopage); // New method to autoselect project after a New on another form object creation
                            if (preg_match('/\?/', $backtopage)) $backtopage .= '&socid='.$object->id; // Old method
                            header("Location: ".$backtopage);
                            exit;
                        }
                        else
                        {
                            if(!empty(GETPOST("nombre_produit_en_stock"))) {
                                $httpCmdUrl = explode('?',$_SERVER['HTTP_REFERER']);
                                $pageCommande = GETPOST('pageCommande');
                                $cmdUrlstr = str_replace("product", $pageCommande, $httpCmdUrl[0]);
                                $cmdUrl = $cmdUrlstr."?id=".GETPOST('commandeIdDraft');
                                $date = new DateTime();
                                $inventoryCode = $date->format('ymdHis');

                                $sqlInsertInProductStock = "INSERT INTO ".MAIN_DB_PREFIX."product_stock (`rowid`, `tms`, `fk_product`, `fk_entrepot`, `reel`, `import_key`)  VALUES (NULL, CURRENT_TIMESTAMP, ".$id.", '1', ".intval(GETPOST("nombre_produit_en_stock")).", NULL); ";
                                $sqlInsertInStockMouvement = "INSERT INTO ".MAIN_DB_PREFIX."stock_mouvement (`rowid`, `tms`, `datem`, `fk_product`, `batch`, `eatby`, `sellby`, `fk_entrepot`, `value`, `price`, `type_mouvement`, `fk_user_author`, `label`, `inventorycode`, `fk_project`, `fk_origin`, `origintype`, `model_pdf`, `fk_projet`)  "
                                        . "VALUES (NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ".$id.", NULL, NULL, NULL, ".$user->id.", 1, '0.00000000', '0', ".intval(GETPOST("nombre_produit_en_stock")).", 'Correction du stock', ".$inventoryCode.", NULL, '0', NULL, NULL, '0'); ";
                                $sqlUpdateProduct = "UPDATE ".MAIN_DB_PREFIX."product set stock = ".intval(GETPOST("nombre_produit_en_stock"))." where rowid = ".$id;
                                $db->query($sqlInsertInProductStock);
                                $db->query($sqlInsertInStockMouvement);
                                $db->query($sqlUpdateProduct);
                            }
                            if(GETPOST('dataPopupNewProduct') == 1) {
                                ?>
                                <script type="text/javascript">
                                    window.parent.location.reload()
                                </script>    
                                <?php
                            } else {
                                header("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
                            }
                            exit;
                        }
            }
            else
			{
            	if (count($object->errors)) setEventMessages($object->error, $object->errors, 'errors');
				else setEventMessages($langs->trans($object->error), null, 'errors');
                $action = "create";
            }
        }
    }

    // Update a product or service
    if ($action == 'update' && $usercancreate)
    {
    	if (GETPOST('cancel', 'alpha'))
        {
            $action = '';
        }
        else
        {
            if ($object->id > 0)
            {
				$object->oldcopy = clone $object;

                $object->ref                    = $ref;
                $object->label                  = GETPOST('label', $label_security_check);
                $object->description            = dol_htmlcleanlastbr(GETPOST('desc', 'none'));
            	$object->url = GETPOST('url');
    			if (!empty($conf->global->MAIN_DISABLE_NOTES_TAB))
    			{
                	$object->note_private = dol_htmlcleanlastbr(GETPOST('note_private', 'none'));
                    $object->note = $object->note_private;
    			}
                $object->customcode             = GETPOST('customcode', 'alpha');
                $object->country_id             = GETPOST('country_id', 'int');
                $object->status                 = GETPOST('statut', 'int');
                $object->status_buy             = GETPOST('statut_buy', 'int');
                $object->status_batch = GETPOST('status_batch', 'aZ09');
                // removed from update view so GETPOST always empty
                $object->fk_default_warehouse   = GETPOST('fk_default_warehouse');
                /*
                $object->seuil_stock_alerte     = GETPOST('seuil_stock_alerte');
                $object->desiredstock           = GETPOST('desiredstock');
                */
                $object->duration_value         = GETPOST('duration_value', 'int');
                $object->duration_unit          = GETPOST('duration_unit', 'alpha');

                $object->canvas                 = GETPOST('canvas');
                $object->net_measure            = GETPOST('net_measure');
                $object->net_measure_units      = GETPOST('net_measure_units'); // This is not the fk_unit but the power of unit
                $object->weight                 = GETPOST('weight');
                $object->weight_units           = GETPOST('weight_units'); // This is not the fk_unit but the power of unit
                $object->length                 = GETPOST('size');
                $object->length_units           = GETPOST('size_units'); // This is not the fk_unit but the power of unit
                $object->width = GETPOST('sizewidth');
                $object->height = GETPOST('sizeheight');

                $object->surface                = GETPOST('surface');
                $object->surface_units          = GETPOST('surface_units'); // This is not the fk_unit but the power of unit
                $object->volume                 = GETPOST('volume');
                $object->volume_units           = GETPOST('volume_units'); // This is not the fk_unit but the power of unit
                $object->finished               = GETPOST('finished', 'alpha');

	            $units = GETPOST('units', 'int');

	            if ($units > 0) {
		            $object->fk_unit = $units;
	            } else {
		            $object->fk_unit = null;
	            }

	            $object->barcode_type = GETPOST('fk_barcode_type');
    	        //$object->barcode = GETPOST('barcode');
                if(!empty(GETPOST('barcode'))) {
                    require DOL_DOCUMENT_ROOT . '/barcodegen1d/generated/vendor/autoload.php';
                    if(!empty(GETPOST('fk_barcode_type'))) {
                        if(GETPOST('fk_barcode_type') == 1) { // ean8
                            $code = new BarcodeBakery\Barcode\BCGean8();
                            $code->parse(GETPOST('barcode'));
                            $object->barcode = $code->getLabel().$code->getChecksum();
                        }else if(GETPOST('fk_barcode_type') == 2) { // ean-13
                            $code = new BarcodeBakery\Barcode\BCGean13();
                            $code->parse(GETPOST('barcode'));
                            $object->barcode = $code->getLabel().$code->getChecksum();
                        }else if(GETPOST('fk_barcode_type') == 4) { // isbn
                            $code = new BarcodeBakery\Barcode\BCGisbn();
                            $code->parse(GETPOST('barcode'));
                            $object->barcode = $code->getLabel().$code->getChecksum();
                        }else if(GETPOST('fk_barcode_type') == 3) { // upc
                            $code = new BarcodeBakery\Barcode\BCGupca();
                            $code->parse(GETPOST('barcode'));
                            $object->barcode = $code->getLabel().$code->getChecksum();
                        }
                    }
                }
    	        // Set barcode_type_xxx from barcode_type id
    	        $stdobject = new GenericObject($db);
    	        $stdobject->element = 'product';
    	        $stdobject->barcode_type = GETPOST('fk_barcode_type');
    	        $result = $stdobject->fetch_barcode();
    	        if ($result < 0)
    	        {
    	        	$error++;
    	        	$mesg = 'Failed to get bar code type information ';
            		setEventMessages($mesg.$stdobject->error, $mesg.$stdobject->errors, 'errors');
    	        }
    	        $object->barcode_type_code      = $stdobject->barcode_type_code;
    	        $object->barcode_type_coder     = $stdobject->barcode_type_coder;
    	        $object->barcode_type_label     = $stdobject->barcode_type_label;

    	        $accountancy_code_sell = GETPOST('accountancy_code_sell', 'alpha');
    	        $accountancy_code_sell_intra = GETPOST('accountancy_code_sell_intra', 'alpha');
    	        $accountancy_code_sell_export = GETPOST('accountancy_code_sell_export', 'alpha');
    	        $accountancy_code_buy = GETPOST('accountancy_code_buy', 'alpha');
    	        $accountancy_code_buy_intra = GETPOST('accountancy_code_buy_intra', 'alpha');
    	        $accountancy_code_buy_export = GETPOST('accountancy_code_buy_export', 'alpha');

				if ($accountancy_code_sell <= 0) { $object->accountancy_code_sell = ''; } else { $object->accountancy_code_sell = $accountancy_code_sell; }
				if ($accountancy_code_sell_intra <= 0) { $object->accountancy_code_sell_intra = ''; } else { $object->accountancy_code_sell_intra = $accountancy_code_sell_intra; }
				if ($accountancy_code_sell_export <= 0) { $object->accountancy_code_sell_export = ''; } else { $object->accountancy_code_sell_export = $accountancy_code_sell_export; }
				if ($accountancy_code_buy <= 0) { $object->accountancy_code_buy = ''; } else { $object->accountancy_code_buy = $accountancy_code_buy; }
				if ($accountancy_code_buy_intra <= 0) { $object->accountancy_code_buy_intra = ''; } else { $object->accountancy_code_buy_intra = $accountancy_code_buy_intra; }
				if ($accountancy_code_buy_export <= 0) { $object->accountancy_code_buy_export = ''; } else { $object->accountancy_code_buy_export = $accountancy_code_buy_export; }

                // Fill array 'array_options' with data from add form
        		$ret = $extrafields->setOptionalsFromPost(null, $object);
				if ($ret < 0) $error++;

                if (!$error && $object->check())
                {
                    if ($object->update($object->id, $user) > 0)
                    {
						// Category association
						$categories = GETPOST('categories', 'array');
						$object->setCategories($categories);

                        $action = 'view';
                    }
                    else
					{
						if (count($object->errors)) setEventMessages($object->error, $object->errors, 'errors');
                    	else setEventMessages($langs->trans($object->error), null, 'errors');
                        $action = 'edit';
                    }
                }
                else
				{
					if (count($object->errors)) setEventMessages($object->error, $object->errors, 'errors');
                	else setEventMessages($langs->trans("ErrorProductBadRefOrLabel"), null, 'errors');
                    $action = 'edit';
                }
            }
        }
    }

    // Action clone object
    if ($action == 'confirm_clone' && $confirm != 'yes') { $action = ''; }
    if ($action == 'confirm_clone' && $confirm == 'yes' && $usercancreate)
    {
        if (!GETPOST('clone_content') && !GETPOST('clone_prices'))
        {
        	setEventMessages($langs->trans("NoCloneOptionsSpecified"), null, 'errors');
        }
        else
        {
            $db->begin();

            $originalId = $id;
            if ($object->id > 0)
            {
                $object->ref = GETPOST('clone_ref', 'alphanohtml');
                $object->status = 0;
                $object->status_buy = 0;
                $object->id = null;
                $object->barcode = -1;

                if ($object->check())
                {
                	$object->context['createfromclone'] = 'createfromclone';
                	$id = $object->create($user);
                    if ($id > 0)
                    {
                        if (GETPOST('clone_composition'))
                        {
                            $result = $object->clone_associations($originalId, $id);

                            if ($result < 1)
                            {
                                $db->rollback();
                                setEventMessages($langs->trans('ErrorProductClone'), null, 'errors');
                                header("Location: ".$_SERVER["PHP_SELF"]."?id=".$originalId);
                                exit;
                            }
                        }

                        if (GETPOST('clone_categories'))
                        {
                            $result = $object->cloneCategories($originalId, $id);

                            if ($result < 1)
                            {
                                $db->rollback();
                                setEventMessages($langs->trans('ErrorProductClone'), null, 'errors');
                                header("Location: ".$_SERVER["PHP_SELF"]."?id=".$originalId);
                                exit;
                            }
                        }

                        if (GETPOST('clone_prices')) {
                            $result = $object->clone_price($originalId, $id);

                            if ($result < 1) {
                                $db->rollback();
                                setEventMessages($langs->trans('ErrorProductClone'), null, 'errors');
                                header('Location: '.$_SERVER['PHP_SELF'].'?id='.$originalId);
                                exit();
                            }
                        }

                        // $object->clone_fournisseurs($originalId, $id);

                        $db->commit();
                        $db->close();

                        header("Location: ".$_SERVER["PHP_SELF"]."?id=".$id);
                        exit;
                    }
                    else
                    {
                        $id = $originalId;

                        if ($object->error == 'ErrorProductAlreadyExists')
                        {
                            $db->rollback();

                            $refalreadyexists++;
                            $action = "";

                            $mesg = $langs->trans("ErrorProductAlreadyExists", $object->ref);
                            $mesg .= ' <a href="'.$_SERVER["PHP_SELF"].'?ref='.$object->ref.'">'.$langs->trans("ShowCardHere").'</a>.';
                            setEventMessages($mesg, null, 'errors');
                            $object->fetch($id);
                        }
                        else
                     	{
                            $db->rollback();
                            if (count($object->errors))
                            {
                            	setEventMessages($object->error, $object->errors, 'errors');
                            	dol_print_error($db, $object->errors);
                            }
                            else
                            {
                            	setEventMessages($langs->trans($object->error), null, 'errors');
                            	dol_print_error($db, $object->error);
                            }
                        }
                    }

                    unset($object->context['createfromclone']);
                }
            }
            else
            {
                $db->rollback();
                dol_print_error($db, $object->error);
            }
        }
    }

    // Delete a product
    if ($action == 'confirm_delete' && $confirm != 'yes') { $action = ''; }
    if ($action == 'confirm_delete' && $confirm == 'yes' && $usercandelete)
	{
		$result = $object->delete($user);

        if ($result > 0)
        {
            header('Location: '.DOL_URL_ROOT.'/product/list.php?type='.$object->type.'&delprod='.urlencode($object->ref));
            exit;
        }
        else
        {
        	setEventMessages($langs->trans($object->error), null, 'errors');
            $reload = 0;
            $action = '';
        }
    }


    // Add product into object
    if ($object->id > 0 && $action == 'addin')
    {
        $thirpdartyid = 0;
        if (GETPOST('propalid') > 0)
        {
        	$propal = new Propal($db);
	        $result = $propal->fetch(GETPOST('propalid'));
	        if ($result <= 0)
	        {
	            dol_print_error($db, $propal->error);
	            exit;
	        }
	        $thirpdartyid = $propal->socid;
        }
        elseif (GETPOST('commandeid') > 0)
        {
            $commande = new Commande($db);
	        $result = $commande->fetch(GETPOST('commandeid'));
	        if ($result <= 0)
	        {
	            dol_print_error($db, $commande->error);
	            exit;
	        }
	        $thirpdartyid = $commande->socid;
        }
        elseif (GETPOST('factureid') > 0)
        {
    	    $facture = new Facture($db);
	        $result = $facture->fetch(GETPOST('factureid'));
	        if ($result <= 0)
	        {
	            dol_print_error($db, $facture->error);
	            exit;
	        }
	        $thirpdartyid = $facture->socid;
        }

        if ($thirpdartyid > 0) {
            $soc = new Societe($db);
            $result = $soc->fetch($thirpdartyid);
            if ($result <= 0) {
                dol_print_error($db, $soc->error);
                exit;
            }

            $desc = $object->description;

            $tva_tx = get_default_tva($mysoc, $soc, $object->id);
            $tva_npr = get_default_npr($mysoc, $soc, $object->id);
            if (empty($tva_tx)) $tva_npr = 0;
            $localtax1_tx = get_localtax($tva_tx, 1, $soc, $mysoc, $tva_npr);
            $localtax2_tx = get_localtax($tva_tx, 2, $soc, $mysoc, $tva_npr);

            $pu_ht = $object->price;
            $pu_ttc = $object->price_ttc;
            $price_base_type = $object->price_base_type;

            // If multiprice
            if ($conf->global->PRODUIT_MULTIPRICES && $soc->price_level) {
                $pu_ht = $object->multiprices[$soc->price_level];
                $pu_ttc = $object->multiprices_ttc[$soc->price_level];
                $price_base_type = $object->multiprices_base_type[$soc->price_level];
            } elseif (!empty($conf->global->PRODUIT_CUSTOMER_PRICES)) {
                require_once DOL_DOCUMENT_ROOT.'/product/class/productcustomerprice.class.php';

                $prodcustprice = new Productcustomerprice($db);

                $filter = array('t.fk_product' => $object->id, 't.fk_soc' => $soc->id);

                $result = $prodcustprice->fetch_all('', '', 0, 0, $filter);
                if ($result) {
                    if (count($prodcustprice->lines) > 0) {
                        $pu_ht = price($prodcustprice->lines [0]->price);
                        $pu_ttc = price($prodcustprice->lines [0]->price_ttc);
                        $price_base_type = $prodcustprice->lines [0]->price_base_type;
                        $tva_tx = $prodcustprice->lines [0]->tva_tx;
                    }
                }
            }

			$tmpvat = price2num(preg_replace('/\s*\(.*\)/', '', $tva_tx));
			$tmpprodvat = price2num(preg_replace('/\s*\(.*\)/', '', $prod->tva_tx));

            // On reevalue prix selon taux tva car taux tva transaction peut etre different
            // de ceux du produit par defaut (par exemple si pays different entre vendeur et acheteur).
            if ($tmpvat != $tmpprodvat) {
                if ($price_base_type != 'HT') {
                    $pu_ht = price2num($pu_ttc / (1 + ($tmpvat / 100)), 'MU');
                } else {
                    $pu_ttc = price2num($pu_ht * (1 + ($tmpvat / 100)), 'MU');
                }
            }

            if (GETPOST('propalid') > 0) {
                // Define cost price for margin calculation
                $buyprice = 0;
                if (($result = $propal->defineBuyPrice($pu_ht, GETPOST('remise_percent'), $object->id)) < 0)
                {
                    dol_syslog($langs->trans('FailedToGetCostPrice'));
                    setEventMessages($langs->trans('FailedToGetCostPrice'), null, 'errors');
                }
                else
                {
                    $buyprice = $result;
                }

                $result = $propal->addline(
                    $desc,
                    $pu_ht,
                    GETPOST('qty'),
                    $tva_tx,
                    $localtax1_tx, // localtax1
                    $localtax2_tx, // localtax2
                    $object->id,
                    GETPOST('remise_percent'),
                    $price_base_type,
                    $pu_ttc,
                    0,
                    0,
                    -1,
                    0,
                    0,
                    0,
                    $buyprice,
                    '',
                    '',
                    '',
                    0,
                    $object->fk_unit
                );
                if ($result > 0) {
                    header("Location: ".DOL_URL_ROOT."/comm/propal/card.php?id=".$propal->id);
                    return;
                }

                setEventMessages($langs->trans("ErrorUnknown").": $result", null, 'errors');
            } elseif (GETPOST('commandeid') > 0) {
                // Define cost price for margin calculation
                $buyprice = 0;
                if (($result = $commande->defineBuyPrice($pu_ht, GETPOST('remise_percent'), $object->id)) < 0)
                {
                    dol_syslog($langs->trans('FailedToGetCostPrice'));
                    setEventMessages($langs->trans('FailedToGetCostPrice'), null, 'errors');
                }
                else
                {
                    $buyprice = $result;
                }

                $result = $commande->addline(
                    $desc,
                    $pu_ht,
                    GETPOST('qty'),
                    $tva_tx,
                    $localtax1_tx, // localtax1
                    $localtax2_tx, // localtax2
                    $object->id,
                    GETPOST('remise_percent'),
                    '',
                    '',
                    $price_base_type,
                    $pu_ttc,
                    '',
                    '',
                    0,
                    -1,
                    0,
                    0,
                    null,
                    $buyprice,
                    '',
                    0,
                    $object->fk_unit
                );

                if ($result > 0) {
                    header("Location: ".DOL_URL_ROOT."/commande/card.php?id=".$commande->id);
                    exit;
                }
            } elseif (GETPOST('factureid') > 0) {
                // Define cost price for margin calculation
                $buyprice = 0;
                if (($result = $facture->defineBuyPrice($pu_ht, GETPOST('remise_percent'), $object->id)) < 0)
                {
                    dol_syslog($langs->trans('FailedToGetCostPrice'));
                    setEventMessages($langs->trans('FailedToGetCostPrice'), null, 'errors');
                }
                else
                {
                    $buyprice = $result;
                }

                $result = $facture->addline(
                    $desc,
                    $pu_ht,
                    GETPOST('qty'),
                    $tva_tx,
                    $localtax1_tx,
                    $localtax2_tx,
                    $object->id,
                    GETPOST('remise_percent'),
                    '',
                    '',
                    '',
                    '',
                    '',
                    $price_base_type,
                    $pu_ttc,
                    Facture::TYPE_STANDARD,
                    -1,
                    0,
                    '',
                    0,
                    0,
                    null,
                    $buyprice,
                    '',
                    0,
                    100,
                    '',
                    $object->fk_unit
                );

                if ($result > 0) {
                    header("Location: ".DOL_URL_ROOT."/compta/facture/card.php?facid=".$facture->id);
                    exit;
                }
            }
        }
        else {
            $action = "";
            setEventMessages($langs->trans("WarningSelectOneDocument"), null, 'warnings');
        }
    }
}



/*
 * View
 */

$title = $langs->trans('ProductServiceCard');
$helpurl = '';
$shortlabel = dol_trunc($object->label, 16);
if (GETPOST("type") == '0' || ($object->type == Product::TYPE_PRODUCT))
{
	$title = $langs->trans('Product')." ".$shortlabel." - ".$langs->trans('Card');
	$helpurl = 'EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
}
if (GETPOST("type") == '1' || ($object->type == Product::TYPE_SERVICE))
{
	$title = $langs->trans('Service')." ".$shortlabel." - ".$langs->trans('Card');
	$helpurl = 'EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
}

llxHeader('', $title, $helpurl);

$form = new Form($db);
$formfile = new FormFile($db);
$formproduct = new FormProduct($db);
if (!empty($conf->accounting->enabled)) $formaccounting = new FormAccounting($db);

// Load object modBarCodeProduct
$res = 0;
if (!empty($conf->barcode->enabled) && !empty($conf->global->BARCODE_PRODUCT_ADDON_NUM))
{
	$module = strtolower($conf->global->BARCODE_PRODUCT_ADDON_NUM);
	$dirbarcode = array_merge(array('/core/modules/barcode/'), $conf->modules_parts['barcode']);
	foreach ($dirbarcode as $dirroot)
	{
		$res = dol_include_once($dirroot.$module.'.php');
		if ($res) break;
	}
	if ($res > 0)
	{
			$modBarCodeProduct = new $module();
	}
}


if (is_object($objcanvas) && $objcanvas->displayCanvasExists($action))
{
	// -----------------------------------------
	// When used with CANVAS
	// -----------------------------------------
	if (empty($object->error) && $id)
	{
		$object = new Product($db);
		$result = $object->fetch($id);
		if ($result <= 0) dol_print_error('', $object->error);
	}
	$objcanvas->assign_values($action, $object->id, $object->ref); // Set value for templates
	$objcanvas->display_canvas($action); // Show template
}
else
{
    // -----------------------------------------
    // When used in standard mode
    // -----------------------------------------
	if ($action == 'create' && $usercancreate)
    {
        //WYSIWYG Editor
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

		// Load object modCodeProduct
        $module = (!empty($conf->global->PRODUCT_CODEPRODUCT_ADDON) ? $conf->global->PRODUCT_CODEPRODUCT_ADDON : 'mod_codeproduct_leopard');
        if (substr($module, 0, 16) == 'mod_codeproduct_' && substr($module, -3) == 'php')
        {
            $module = substr($module, 0, dol_strlen($module) - 4);
        }
        $result = dol_include_once('/core/modules/product/'.$module.'.php');
        if ($result > 0)
        {
        	$modCodeProduct = new $module();
        }

        dol_set_focus('input[name="ref"]');
        // target="_parent"
        // $ref = GETPOST('ref', 'alpha');
        // GETPOST('label', $label_security_check)
        // GETPOST('nombre_produit_en_stock')
        print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="add">';
        print '<input type="hidden" name="type" value="'.$type.'">'."\n";
		if (!empty($modCodeProduct->code_auto))
			print '<input type="hidden" name="code_auto" value="1">';
		if (!empty($modBarCodeProduct->code_auto))
			print '<input type="hidden" name="barcode_auto" value="1">';
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

		if ($type == 1) {
			$picto = 'service';
			$title = $langs->trans("NewService");
		}
		else {
			$picto = 'product';
			$title = $langs->trans("NewProduct");
                        $datapopup = GETPOST('dataPopupNewProduct');
                        $commandeIdDraft = GETPOST('commandeIdDraft');
                        $pageCommande = GETPOST('pageCommande');
                        print '<input type="hidden" name="dataPopupNewProduct" value="'.$datapopup.'">';
                        print '<input type="hidden" name="commandeIdDraft" value="'.$commandeIdDraft.'">';
                        print '<input type="hidden" name="pageCommande" value="'.$pageCommande.'">';
                        if($datapopup == 1) {
                            print '<input type="hidden" name="datapopup" value="'.$datapopup.'">';
                            print '<input type="hidden" name="commandeIdDraft" value="'.$commandeIdDraft.'">';
                            print '<input type="hidden" name="pageCommande" value="'.$pageCommande.'">';
                        ?>
                        <style>
                            #tmenu_tooltip {
                                display:none!important;
                            }
                            .side-nav{
                                display:none!important;
                            }
                            #topmenu-login-dropdown {
                                display:none!important
                            }
                            .login_block_other {
                                display:none!important
                            }
                            .table-fiche-title{
                                display:none!important
                            }
                        </style>
                        <?php
                        }
		}
        $linkback = "";
        print load_fiche_titre($title, $linkback, $picto);

        dol_fiche_head('');

        print '<table class="border centpercent">';

        print '<tr>';
        $tmpcode = '';
        $isDisabled = "";
	if (!empty($modCodeProduct->code_auto)) {
            $tmpcode = $modCodeProduct->getNextValue($object, $type);
            $isDisabled = "readonly";
        }
	print '<td class="titlefieldcreate fieldrequired">'.$langs->trans("Ref").'</td><td colspan="3"><input id="ref" name="ref" class="maxwidth200" maxlength="128" value="'.dol_escape_htmltag(GETPOSTISSET('ref') ? GETPOST('ref', 'alphanohtml') : $tmpcode).'" '.$isDisabled.'>';
        if ($refalreadyexists)
        {
            print $langs->trans("RefAlreadyExists");
        }
        print '</td></tr>';

        // Label
        print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td><td colspan="3"><input name="label" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag(GETPOST('label', $label_security_check)).'"></td></tr>';
        
        /*if($datapopup == 1)
        {*/
        print '<tr><td class="">Nombre produit en stock</td><td colspan="3"><input name="nombre_produit_en_stock" type="number" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.GETPOST('nombre_produit_en_stock').'"></td></tr>';
        //}
        // On sell
        print '<tr><td class="fieldrequired">'.$langs->trans("Status").' ('.$langs->trans("Sell").')</td><td colspan="3">';
        $statutarray = array('1' => $langs->trans("OnSell"), '0' => $langs->trans("NotOnSell"));
        print $form->selectarray('statut', $statutarray, GETPOST('statut'));
        print '</td></tr>';

        // To buy
        print '<tr><td class="fieldrequired">'.$langs->trans("Status").' ('.$langs->trans("Buy").')</td><td colspan="3">';
        $statutarray = array('1' => $langs->trans("ProductStatusOnBuy"), '0' => $langs->trans("ProductStatusNotOnBuy"));
        print $form->selectarray('statut_buy', $statutarray, GETPOST('statut_buy'));
        print '</td></tr>';

	    // Batch number management
		if (!empty($conf->productbatch->enabled))
		{
			print '<tr><td>'.$langs->trans("ManageLotSerial").'</td><td colspan="3">';
			$statutarray = array('0' => $langs->trans("ProductStatusNotOnBatch"), '1' => $langs->trans("ProductStatusOnBatch"));
			print $form->selectarray('status_batch', $statutarray, GETPOST('status_batch'));
			print '</td></tr>';
		}

        $showbarcode = empty($conf->barcode->enabled) ? 0 : 1;
        if (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->barcode->lire_advance)) $showbarcode = 0;

        if ($showbarcode)
        {
 	        print '<tr><td>'.$langs->trans('BarcodeType').'sefse</td><td>';
 	        if (isset($_POST['fk_barcode_type']))
	        {
	         	$fk_barcode_type = GETPOST('fk_barcode_type');
	        }
	        else
	        {
	        	if (empty($fk_barcode_type) && !empty($conf->global->PRODUIT_DEFAULT_BARCODE_TYPE)) $fk_barcode_type = $conf->global->PRODUIT_DEFAULT_BARCODE_TYPE;
	        }
	        require_once DOL_DOCUMENT_ROOT.'/core/class/html.formbarcode.class.php';
            $formbarcode = new FormBarCode($db);
            print $formbarcode->selectBarcodeType($fk_barcode_type, 'fk_barcode_type', 1);
	        print '</td>';
	        if ($conf->browser->layout == 'phone') print '</tr><tr>';
	        print '<td>'.$langs->trans("BarcodeValue").'</td><td>';
	        $tmpcode = isset($_POST['barcode']) ?GETPOST('barcode') : $object->barcode;
	        if (empty($tmpcode) && !empty($modBarCodeProduct->code_auto)) $tmpcode = $modBarCodeProduct->getNextValue($object, $type);
	        print '<input class="maxwidth100" type="text" name="barcode" value="'.dol_escape_htmltag($tmpcode).'">';
	        print '</td></tr>';
        }

        // Description (used in invoice, propal...)
        print '<tr><td class="tdtop">'.$langs->trans("Description").'</td><td colspan="3">';

        $doleditor = new DolEditor('desc', GETPOST('desc', 'none'), '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
        $doleditor->Create();

        print "</td></tr>";

        // Public URL
        print '<tr><td>'.$langs->trans("PublicUrl").'</td><td colspan="3">';
		print '<input type="text" name="url" class="quatrevingtpercent" value="'.GETPOST('url').'">';
        print '</td></tr>';

        if ($type != 1 && !empty($conf->stock->enabled))
        {
            // Default warehouse
            print '<tr><td>'.$langs->trans("DefaultWarehouse").'</td><td>';
            print $formproduct->selectWarehouses(GETPOST('fk_default_warehouse'), 'fk_default_warehouse', 'warehouseopen', 1);
            print ' <a href="'.DOL_URL_ROOT.'/product/stock/card.php?action=create&amp;backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$object->id.'&action=edit').'">';
            print '<span class="fa fa-plus-circle valignmiddle paddingleft" title="'.$langs->trans("AddWarehouse").'"></span>';
            print '</a>';
            print '</td>';
            print '</tr>';

            // Stock min level
            print '<tr><td>'.$form->textwithpicto($langs->trans("StockLimit"), $langs->trans("StockLimitDesc"), 1).'</td><td>';
            print '<input name="seuil_stock_alerte" class="maxwidth50" value="'.GETPOST('seuil_stock_alerte').'">';
            print '</td>';
            if ($conf->browser->layout == 'phone') print '</tr><tr>';
            // Stock desired level
            print '<td>'.$form->textwithpicto($langs->trans("DesiredStock"), $langs->trans("DesiredStockDesc"), 1).'</td><td>';
            print '<input name="desiredstock" class="maxwidth50" value="'.GETPOST('desiredstock').'">';
            print '</td></tr>';
        }
        else
        {
            print '<input name="seuil_stock_alerte" type="hidden" value="0">';
            print '<input name="desiredstock" type="hidden" value="0">';
        }

        // Duration
        if ($type == 1)
        {
            print '<tr><td>'.$langs->trans("Duration").'</td><td colspan="3">';
            print '<input name="duration_value" size="4" value="'.GETPOST('duration_value', 'int').'">';
            print $formproduct->selectMeasuringUnits("duration_unit", "time", GETPOST('duration_value', 'alpha'), 0, 1);
            print '</td></tr>';
        }

        if ($type != 1)	// Nature, Weight and volume only applies to products and not to services
        {
            // Nature
            print '<tr><td>'.$langs->trans("Nature").'</td><td colspan="3">';
            $statutarray = array('1' => $langs->trans("Finished"), '0' => $langs->trans("RowMaterial"));
			$natureSelectedDefault = GETPOST('finished', 'alpha');
			if(empty(GETPOST('finished', 'alpha'))) {
				$natureSelectedDefault = 1;
			}
            print $form->selectarray('finished', $statutarray, $natureSelectedDefault, 1);
            print '</td></tr>';

            // Brut Weight
            print '<tr><td>'.$langs->trans("Weight").'</td><td colspan="3">';
            print '<input name="weight" size="4" value="'.GETPOST('weight').'">';
            print $formproduct->selectMeasuringUnits("weight_units", "weight", GETPOSTISSET('weight_units') ?GETPOST('weight_units', 'alpha') : (empty($conf->global->MAIN_WEIGHT_DEFAULT_UNIT) ? 0 : $conf->global->MAIN_WEIGHT_DEFAULT_UNIT), 0, 2);
            print '</td></tr>';

            // Brut Length
            if (empty($conf->global->PRODUCT_DISABLE_SIZE))
            {
                print '<tr><td>'.$langs->trans("Length").' x '.$langs->trans("Width").' x '.$langs->trans("Height").'</td><td colspan="3">';
                print '<input name="size" class="width50" value="'.GETPOST('size').'"> x ';
                print '<input name="sizewidth" class="width50" value="'.GETPOST('sizewidth').'"> x ';
                print '<input name="sizeheight" class="width50" value="'.GETPOST('sizeheight').'">';
                print $formproduct->selectMeasuringUnits("size_units", "size", GETPOSTISSET('size_units') ?GETPOST('size_units', 'alpha') : '0', 0, 2);
                print '</td></tr>';
            }
            if (empty($conf->global->PRODUCT_DISABLE_SURFACE))
            {
                // Brut Surface
                print '<tr><td>'.$langs->trans("Surface").'</td><td colspan="3">';
                print '<input name="surface" size="4" value="'.GETPOST('surface').'">';
                print $formproduct->selectMeasuringUnits("surface_units", "surface", GETPOSTISSET('surface_units') ?GETPOST('surface_units', 'alpha') : '0', 0, 2);
                print '</td></tr>';
            }
            if (empty($conf->global->PRODUCT_DISABLE_VOLUME))
            {
                // Brut Volume
                print '<tr><td>'.$langs->trans("Volume").'</td><td colspan="3">';
                print '<input name="volume" size="4" value="'.GETPOST('volume').'">';
                print $formproduct->selectMeasuringUnits("volume_units", "volume", GETPOSTISSET('volume_units') ?GETPOST('volume_units', 'alpha') : '0', 0, 2);
                print '</td></tr>';
            }

            if (!empty($conf->global->PRODUCT_ADD_NET_MEASURE))
            {
	            // Net Measure
	            print '<tr><td>'.$langs->trans("NetMeasure").'</td><td colspan="3">';
	            print '<input name="net_measure" size="4" value="'.GETPOST('net_measure').'">';
	            print $formproduct->selectMeasuringUnits("net_measure_units", '', GETPOSTISSET('net_measure_units') ?GETPOST('net_measure_units', 'alpha') : (empty($conf->global->MAIN_WEIGHT_DEFAULT_UNIT) ? 0 : $conf->global->MAIN_WEIGHT_DEFAULT_UNIT), 0, 0);
	            print '</td></tr>';
            }
        }

        // Units
	    if ($conf->global->PRODUCT_USE_UNITS)
	    {
		    print '<tr><td>'.$langs->trans('DefaultUnitToShow').'</td>';
		    print '<td colspan="3">';
		    print $form->selectUnits('', 'units');
		    print '</td></tr>';
	    }

        // Custom code
        if (empty($conf->global->PRODUCT_DISABLE_CUSTOM_INFO) && empty($type))
        {
	        print '<tr><td>'.$langs->trans("CustomCode").'</td><td><input name="customcode" class="maxwidth100onsmartphone" value="'.GETPOST('customcode').'"></td>';
	        if ($conf->browser->layout == 'phone') print '</tr><tr>';
	        // Origin country
	        print '<td>'.$langs->trans("CountryOrigin").'</td>';
	        print '<td>';
	        print $form->select_country(GETPOST('country_id', 'int'), 'country_id');
	        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
	        print '</td></tr>';
        }

        // Other attributes
        $parameters = array('colspan' => 3, 'cols' => '3');
        $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
        print $hookmanager->resPrint;
        if (empty($reshook))
        {
        	print $object->showOptionals($extrafields, 'edit', $parameters);
        }

        // Note (private, no output on invoices, propales...)
        //if (! empty($conf->global->MAIN_DISABLE_NOTES_TAB))       available in create mode
        //{
            print '<tr><td class="tdtop">'.$langs->trans("NoteNotVisibleOnBill").'</td><td colspan="3">';

            // We use dolibarr_details as type of DolEditor here, because we must not accept images as description is included into PDF and not accepted by TCPDF.
            $doleditor = new DolEditor('note_private', GETPOST('note_private', 'none'), '', 140, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_8, '90%');
    	    $doleditor->Create();

            print "</td></tr>";
        //}

		if ($conf->categorie->enabled) {
			// Categories
			print '<tr><td>'.$langs->trans("Categories").'</td><td colspan="3">';
			$cate_arbo = $form->select_all_categories(Categorie::TYPE_PRODUCT, '', 'parent', 64, 0, 1);
			print $form->multiselectarray('categories', $cate_arbo, GETPOST('categories', 'array'), '', 0, '', 0, '100%');
			print "</td></tr>";
		}

        print '</table>';

        print '<hr>';

        if (!empty($conf->global->PRODUIT_MULTIPRICES))
        {
            // We do no show price array on create when multiprices enabled.
            // We must set them on prices tab.
            print '<table class="border centpercent">';
            // VAT
            print '<tr><td class="titlefieldcreate">'.$langs->trans("VATRate").'</td><td>';
            $defaultva = get_default_tva($mysoc, $mysoc);
            print $form->load_tva("tva_tx", $defaultva, $mysoc, $mysoc, 0, 0, '', false, 1);
            print '</td></tr>';
            print '</table>';

            print '<br>';
        }
        else
		{
            print '<table class="border centpercent">';

            // Fournisseur
            print '<tr><td class="fieldrequired">'.$langs->trans("SupplierOfProduct").'</td><td>';
            print $form->select_company(GETPOST("id_fourn", 'alpha'), 'id_fourn', 'fournisseur=1', 'SelectThirdParty', 0, 0);
            print '</td></tr>';
            
            if (!empty($conf->global->FOURN_PRODUCT_AVAILABILITY))
            {
                    $langs->load("propal");
                    print '<tr><td>'.$langs->trans("Availability").'</td><td>';
                    $form->selectAvailabilityDelay($productFournisseur->fk_availability, "oselDispo", 1);
                    print '</td></tr>'."\n";
            }
            
            // Option to define a transport cost on supplier price
            if ($conf->global->PRODUCT_CHARGES)
            {
                if (!empty($conf->margin->enabled))
                {
                    print '<tr>';
                    print '<td>'.$langs->trans("Charges").'</td>';
                    print '<td><input class="flat" name="charges" size="8" value="'.(GETPOST('charges') ?price(GETPOST('charges')) : (isset($productFournisseur->fourn_charges) ?price($productFournisseur->fourn_charges) : '')).'">';
                    print '</td>';
                    print '</tr>';
                }
            }
            
            // Ref produit fournisseur
            $refProdParDefaut = "";
            if (!empty($modCodeProduct->code_auto)) {
                $refProdParDefaut = $modCodeProduct->getNextValue($object, $type);
            }
            $refProdFourn = !empty(GETPOST("ref_prod_fourn", 'alpha'))?GETPOST("ref_prod_fourn", 'alpha'):$refProdParDefaut;
            print '<tr><td  class="fieldrequired">'.$langs->trans("RefProduitFournisseur").'</td>';
            print '<td><input name="ref_prod_fourn" class="maxwidth60" value="'.$refProdFourn.'">';
            print '</td></tr>';
            
            // best purchase price
            print '<tr><td  class="fieldrequired">'.$langs->trans("BestPurchasePrice").'</td>';
            print '<td> <input id="best_purchase_price_hidden" type="hidden"/>'
            . ' <input id="best_purchase_price" onInput="doCalcul()" name="best_purchase_price" class="maxwidth50" value="'.GETPOST("best_purchase_price", 'alpha').'">&nbsp';
            print $form->selectPriceBaseType("HT", "price_base_type_prd_frs");
            print '</td></tr>';

            // coefficient of return
            print '<tr><td  class="fieldrequired">'.$langs->trans("CoefficientOfReturn").'</td>';
            print '<td> <input type="hidden" id="coefficient_of_return_hidden">'
            . '<input id="coefficient_of_return" onInput="doCalcul()" name="coefficient_of_return" class="maxwidth50" value="'.GETPOST("coefficient_of_return", 'alpha').'">';
            print '</td></tr>';
            
            print '<tr><td><hr></td></tr>';
            
            
            // Cout de revient
            print '<tr><td>'.$langs->trans("CostOfReturn").'</td>';
            print '<td><input id="cost_of_return" name="cost_of_return" class="maxwidth50" value="'.GETPOST("cost_of_return", 'alpha').'" readonly>';
            print '</td></tr>';
            
            // Prix de revient
            print '<tr><td>'.$langs->trans("PriceOfReturn").'</td>';
            print '<td><input id="price_of_return" name="price_of_return" class="maxwidth50" value="'.GETPOST("price_of_return", 'alpha').'" readonly>';
            print '</td></tr>';
            
            // Prix moyen pondéré (pmp)
            /*print '<tr><td>'.$langs->trans("AveragePriceWeighted").'</td>';
            print '<td><input id="average_price_weighted" name="average_price_weighted" class="maxwidth50" value="'.GETPOST("average_price_weighted", 'alpha').'" readonly>';
            print '</td></tr>';*/
            
            print '<tr><td><hr></td></tr>';
            // Coef vente
            print '<tr><td>'.$langs->trans("CoeffVente").'</td>';
            print '<td><input id="coef_vente" name="coef_vente" class="maxwidth50" value="'.$conf->global->COEFFICIENT_VENTE.'" readonly>';
            print '</td></tr>';
            
            // Marge
            print '<tr><td>'.$langs->trans("Margin").'</td>';
            print '<td><input id="margin_product" name="margin_product" class="maxwidth50" value="'.GETPOST("margin_product", 'alpha').'" readonly>';
            print '</td></tr>';
            
            // Prix suggeré
            print '<tr><td>'.$langs->trans("SuggestPrice").'</td>';
            print '<td><input id="suggest_price" name="suggest_price" class="maxwidth50" value="'.GETPOST("suggest_price", 'alpha').'" readonly>';
            print '</td></tr>';
            
            print '<tr><td><hr></td></tr>';
             
            // Price
            print '<tr><td class="titlefieldcreate fieldrequired">'.$langs->trans("SellingPrice").'(TTC)</td>';
            print '<td><input name="price" class="maxwidth50" value="'.GETPOST("price", 'alpha').'" onInput="doCalcul()" id="price_ttc">';
            print $form->selectPriceBaseType('TTC', "price_base_type");
            print '</td></tr>';

            // Min price
            print '<tr style="display:none;"><td>'.$langs->trans("MinPrice").'</td>';
            print '<td><input name="price_min" class="maxwidth50" value="'.$object->price_min.'"  style="display:none;">';
            print '</td></tr>';

            // VAT
            print '<tr><td>'.$langs->trans("VATRate").'</td><td>';
            $defaultva = get_default_tva($mysoc, $mysoc);
            
            $sqlTauxTva = "SELECT t.taux as vat_rate, t.code as default_vat_code "
                    . " FROM llx_c_tva as t, llx_c_country as c "
                    . " WHERE t.active=1 AND t.fk_pays = c.rowid AND c.code='".$mysoc->country_code."' "
                    . " ORDER BY t.taux DESC, t.code ASC, t.recuperableonly ASC "
                    . " LIMIT 1";
            $resqlTauxTva = $db->query($sqlTauxTva);
            if ($resqlTauxTva)
            {
                $objTauxTva = $db->fetch_object($resqlTauxTva);
                print '<input type="hidden" value="'.$objTauxTva->vat_rate.'" id="default_taux_tva">';
            }
            print $form->load_tva("tva_tx", $defaultva, $mysoc, $mysoc, 0, 0, '', false, 1);
            print '</td></tr>';
            
            print '<tr><td><hr></td></tr>';
            // Coefficient de vente TTC
            print '<tr><td>'.$langs->trans("CoeffVenteTTC").'</td>';
            print '<td><input id="coeff_vente_ttc" name="coeff_vente_ttc" class="maxwidth50" value="'.GETPOST("coeff_vente_ttc", 'alpha').'" readonly>';
            print '</td></tr>';
            
            
            // Taux de marge en %
            print '<tr><td>'.$langs->trans("MarginRateAsPercentage").'</td>';
            print '<td><input id="margin_rate_as_percentage" name="margin_rate_as_percentage" class="maxwidth50" value="'.GETPOST("margin_rate_as_percentage", 'alpha').'" readonly>';
            print '</td></tr>';
            
            // Marge TTC
            print '<tr><td>'.$langs->trans("MarginTTC").'</td>';
            print '<td><input id="margin_ttc" name="margin_ttc" class="maxwidth50" value="'.GETPOST("margin_ttc", 'alpha').'" readonly>';
            print '</td></tr>';
            
            // Taux de marque %
            print '<tr><td>'.$langs->trans("BrandRateInPercent").'</td>';
            print '<td><input id="brand_rate_in_percent" name="brand_rate_in_percent" class="maxwidth50" value="'.GETPOST("brand_rate_in_percent", 'alpha').'" readonly>';
            print '</td></tr>';
            
            // Prix de vente HT
            print '<tr><td>'.$langs->trans("SellingPriceExclTax").'</td>';
            print '<td><input id="selling_price_excl_tax" name="selling_price_excl_tax" class="maxwidth50" value="'.GETPOST("selling_price_excl_tax", 'alpha').'" readonly>';
            print '</td></tr>';
            
            // TVA
            print '<tr><td>'.$langs->trans("Vat").'(8,5%)</td>';
            print '<td><input id="vat_price" name="vat_price" class="maxwidth50" value="'.GETPOST("vat_price", 'alpha').'" readonly>';
            print '</td></tr>';
            
            // Carte metisse 5%
            print '<tr><td>Carte metisse 5%</td>';
            print '<td><input id="carte_metisse" name="carte_metisse" class="maxwidth50" value="'.GETPOST("carte_metisse", 'alpha').'" readonly>';
            print '</td></tr>';
            

            print '</table>';
            ?> 
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        $("#select_price_base_type option:contains('HT')").attr("disabled","disabled").hide();
                    });
                    function doCalcul() {
                        /*best price edit*/
                        var best_purchase_price = parseFloat((document.getElementById("best_purchase_price").value).replace(',','.'));
                        var coefficient_of_return = parseFloat((document.getElementById("coefficient_of_return").value).replace(',','.'));
                        var coef_vente = parseFloat(document.getElementById("coef_vente").value);
                        var cost_of_return = (best_purchase_price*20)/100;
                        var price_of_return = coefficient_of_return*best_purchase_price;
			var cost_of_return = price_of_return-best_purchase_price;
                        
                        //var average_price_weighted = coefficient_of_return*best_purchase_price;
                        var suggest_price = price_of_return*coef_vente;
			var margin_product = suggest_price-price_of_return;
                        
                        if(isNaN(cost_of_return)) {
                            document.getElementById("cost_of_return").value = "";
                        }else {
                            document.getElementById("cost_of_return").value = parseFloat(cost_of_return).toFixed(2);
                            document.getElementById("cost_of_return").style.color = "grey";
                        }
                        
                        if(isNaN(price_of_return)) {
                            document.getElementById("price_of_return").value = "";
                        } else {
                            document.getElementById("price_of_return").value = parseFloat(price_of_return).toFixed(2);
                            document.getElementById("price_of_return").style.color = "grey";
                        }
                        
                        /*if(isNaN(average_price_weighted)) {
                            document.getElementById("average_price_weighted").value = "";
                        }else{
                            document.getElementById("average_price_weighted").value = parseFloat(average_price_weighted).toFixed(2);
                            document.getElementById("average_price_weighted").style.color = "grey";
                        }*/
                        
                        if(isNaN(margin_product)) {
                            document.getElementById("margin_product").value = "";
                        }else{
                            document.getElementById("margin_product").value = parseFloat(margin_product).toFixed(2);
                            document.getElementById("margin_product").style.color = "grey";
                        }
                        
                        if(isNaN(suggest_price)) {
                            document.getElementById("suggest_price").value = "";
                        }else{
                            document.getElementById("suggest_price").value = parseFloat(suggest_price).toFixed(2);
                            document.getElementById("suggest_price").style.color = "grey";
                        }
                        
                        if(isNaN(best_purchase_price)) {
                            document.getElementById("best_purchase_price_hidden").value = "";
                        }else{
                            document.getElementById("best_purchase_price_hidden").value = parseFloat(best_purchase_price).toFixed(2);
                            document.getElementById("best_purchase_price_hidden").style.color = "grey";
                        }
                        
                        if(isNaN(coefficient_of_return)) {
                            document.getElementById("coefficient_of_return_hidden").value = "";
                        }else{
                            document.getElementById("coefficient_of_return_hidden").value = parseFloat(coefficient_of_return).toFixed(2);
                            document.getElementById("coefficient_of_return_hidden").style.color = "grey";
                        }
                        
                        
                        /* price ttc edit */
                        var default_taux_tva =  parseFloat((document.getElementById("default_taux_tva").value).replace(',','.'));
                        var price_ttc =  parseFloat((document.getElementById("price_ttc").value).replace(',','.'));
                        var price_of_return =  parseFloat((document.getElementById("price_of_return").value).replace(',','.'));
                        var coefficient_of_return =  parseFloat((document.getElementById("coefficient_of_return_hidden").value).replace(',','.'));
                        var best_purchase_price =  parseFloat((document.getElementById("best_purchase_price_hidden").value).replace(',','.'));
                        var tva_calculated = (price_ttc/((default_taux_tva+100)/100))*(default_taux_tva/100);
                        var price_ht_calculated = price_ttc-tva_calculated;
                        
                        var margin_ttc = price_ttc-price_of_return;
                        var coeff_vente_ttc = price_ttc/price_of_return;
                        var margin_rate_as_percentage = (margin_ttc*100)/price_of_return;
                        var brand_rate_in_percent = (margin_ttc*100)/price_ttc;
                        var carte_metisse = price_ttc*0.95;
                        
                        if(isNaN(tva_calculated)) {
                            document.getElementById("vat_price").value ="";
                        }else{
                            document.getElementById("vat_price").value = parseFloat(tva_calculated).toFixed(2);
                            document.getElementById("vat_price").style.color = "grey";
                        }
                        
                        if(isNaN(price_ht_calculated) /*|| price_ht_calculated<0*/) {
                            document.getElementById("selling_price_excl_tax").value = "";
                        }else{
                            document.getElementById("selling_price_excl_tax").value = parseFloat(price_ht_calculated).toFixed(2);
                            document.getElementById("selling_price_excl_tax").style.color = "grey";
                        }
                        
                        if(isNaN(brand_rate_in_percent) /*|| brand_rate_in_percent<0*/) {
                            document.getElementById("brand_rate_in_percent").value = "";
                        }else{
                            document.getElementById("brand_rate_in_percent").value = parseFloat(brand_rate_in_percent).toFixed(2);
                            document.getElementById("brand_rate_in_percent").style.color = "grey";
                        }
                        
                        if(isNaN(margin_ttc) /*|| margin_ttc<0*/) {
                            document.getElementById("margin_ttc").value = "";
                        }else{
                            document.getElementById("margin_ttc").value = parseFloat(margin_ttc).toFixed(2);
                            document.getElementById("margin_ttc").style.color = "grey";
                        }
                        
                        if(isNaN(coeff_vente_ttc)) {
                            document.getElementById("coeff_vente_ttc").value = "";
                        }else{
                            document.getElementById("coeff_vente_ttc").value = parseFloat(coeff_vente_ttc).toFixed(2);
                            document.getElementById("coeff_vente_ttc").style.color = "grey";
                        }
                        
                        if(isNaN(margin_rate_as_percentage)) {
                            document.getElementById("margin_rate_as_percentage").value = "";
                        }else{
                            document.getElementById("margin_rate_as_percentage").value = parseFloat(margin_rate_as_percentage).toFixed(2);
                            document.getElementById("margin_rate_as_percentage").style.color = "grey";
                        }
						
			if(isNaN(carte_metisse)) {
                            document.getElementById("carte_metisse").value = "";
                        }else{
                            document.getElementById("carte_metisse").value = parseFloat(Math.floor(carte_metisse*10)/10).toFixed(2);
                            document.getElementById("carte_metisse").style.color = "grey";
                        }
                    }
                </script>  
        <?php
            print '<br>';
        }

        // Accountancy codes
        print '<table class="border centpercent">';

		if (!empty($conf->accounting->enabled))
		{
			// Accountancy_code_sell
			print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancySellCode").'</td>';
			print '<td>';
            if ($type == 0) {
                $accountancy_code_sell = (GETPOSTISSET('accountancy_code_sell') ? GETPOST('accountancy_code_sell', 'alpha') : $conf->global->ACCOUNTING_PRODUCT_SOLD_ACCOUNT);
            } else {
                $accountancy_code_sell = (GETPOSTISSET('accountancy_code_sell') ? GETPOST('accountancy_code_sell', 'alpha') : $conf->global->ACCOUNTING_SERVICE_SOLD_ACCOUNT);
            }
            print $formaccounting->select_account($accountancy_code_sell, 'accountancy_code_sell', 1, null, 1, 1, '');
			print '</td></tr>';

			// Accountancy_code_sell_intra
			if ($mysoc->isInEEC())
			{
				print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancySellIntraCode").'</td>';
				print '<td>';
                if ($type == 0) {
                    $accountancy_code_sell_intra = (GETPOSTISSET('accountancy_code_sell_intra') ? GETPOST('accountancy_code_sell_intra', 'alpha') : $conf->global->ACCOUNTING_PRODUCT_SOLD_INTRA_ACCOUNT);
                } else {
                	$accountancy_code_sell_intra = (GETPOSTISSET('accountancy_code_sell_intra') ? GETPOST('accountancy_code_sell_intra', 'alpha') : $conf->global->ACCOUNTING_SERVICE_SOLD_INTRA_ACCOUNT);
                }
                print $formaccounting->select_account($accountancy_code_sell_intra, 'accountancy_code_sell_intra', 1, null, 1, 1, '');
                print '</td></tr>';
			}

			// Accountancy_code_sell_export
			print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancySellExportCode").'</td>';
			print '<td>';
            if ($type == 0)
            {
                $accountancy_code_sell_export = (GETPOST('accountancy_code_sell_export') ? GETPOST('accountancy_code_sell_export', 'alpha') : $conf->global->ACCOUNTING_PRODUCT_SOLD_EXPORT_ACCOUNT);
            } else {
            	$accountancy_code_sell_export = (GETPOST('accountancy_code_sell_export') ? GETPOST('accountancy_code_sell_export', 'alpha') : $conf->global->ACCOUNTING_SERVICE_SOLD_EXPORT_ACCOUNT);
            }
            print $formaccounting->select_account($accountancy_code_sell_export, 'accountancy_code_sell_export', 1, null, 1, 1, '');
            print '</td></tr>';

			// Accountancy_code_buy
			print '<tr><td>'.$langs->trans("ProductAccountancyBuyCode").'</td>';
			print '<td>';
            if ($type == 0)
            {
                $accountancy_code_buy = (GETPOST('accountancy_code_buy', 'alpha') ? (GETPOST('accountancy_code_buy', 'alpha')) : $conf->global->ACCOUNTING_PRODUCT_BUY_ACCOUNT);
            } else {
                $accountancy_code_buy = GETPOST('accountancy_code_buy', 'alpha');
            }
			print $formaccounting->select_account($accountancy_code_buy, 'accountancy_code_buy', 1, null, 1, 1, '');
			print '</td></tr>';

			// Accountancy_code_buy_intra
			if ($mysoc->isInEEC())
			{
				print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancyBuyIntraCode").'</td>';
				print '<td>';
				if ($type == 0) {
					$accountancy_code_buy_intra = (GETPOSTISSET('accountancy_code_buy_intra') ? GETPOST('accountancy_code_buy_intra', 'alpha') : $conf->global->ACCOUNTING_PRODUCT_BUY_INTRA_ACCOUNT);
				} else {
					$accountancy_code_buy_intra = (GETPOSTISSET('accountancy_code_buy_intra') ? GETPOST('accountancy_code_buy_intra', 'alpha') : $conf->global->ACCOUNTING_SERVICE_BUY_INTRA_ACCOUNT);
				}
				print $formaccounting->select_account($accountancy_code_buy_intra, 'accountancy_code_buy_intra', 1, null, 1, 1, '');
				print '</td></tr>';
			}

			// Accountancy_code_buy_export
			print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancyBuyExportCode").'</td>';
			print '<td>';
			if ($type == 0)
			{
				$accountancy_code_buy_export = (GETPOST('accountancy_code_buy_export') ? GETPOST('accountancy_code_buy_export', 'alpha') : $conf->global->ACCOUNTING_PRODUCT_BUY_EXPORT_ACCOUNT);
			} else {
				$accountancy_code_buy_export = (GETPOST('accountancy_code_buy_export') ? GETPOST('accountancy_code_buy_export', 'alpha') : $conf->global->ACCOUNTING_SERVICE_BUY_EXPORT_ACCOUNT);
			}
			print $formaccounting->select_account($accountancy_code_buy_export, 'accountancy_code_buy_export', 1, null, 1, 1, '');
			print '</td></tr>';
		}
		else // For external software
		{
			if (!empty($accountancy_code_sell)) { $object->accountancy_code_sell = $accountancy_code_sell; }
			if (!empty($accountancy_code_sell_intra)) { $object->accountancy_code_sell_intra = $accountancy_code_sell_intra; }
			if (!empty($accountancy_code_sell_export)) { $object->accountancy_code_sell_export = $accountancy_code_sell_export; }
			if (!empty($accountancy_code_buy)) { $object->accountancy_code_buy = $accountancy_code_buy; }
			if (!empty($accountancy_code_buy_intra)) { $object->accountancy_code_buy_intra = $accountancy_code_buy_intra; }
			if (!empty($accountancy_code_buy_export)) { $object->accountancy_code_buy_export = $accountancy_code_buy_export; }

			// Accountancy_code_sell
			print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancySellCode").'</td>';
			print '<td class="maxwidthonsmartphone"><input class="minwidth100" name="accountancy_code_sell" value="'.$object->accountancy_code_sell.'">';
			print '</td></tr>';

			// Accountancy_code_sell_intra
			if ($mysoc->isInEEC())
			{
				print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancySellIntraCode").'</td>';
				print '<td class="maxwidthonsmartphone"><input class="minwidth100" name="accountancy_code_sell_intra" value="'.$object->accountancy_code_sell_intra.'">';
				print '</td></tr>';
			}

			// Accountancy_code_sell_export
			print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancySellExportCode").'</td>';
			print '<td class="maxwidthonsmartphone"><input class="minwidth100" name="accountancy_code_sell_export" value="'.$object->accountancy_code_sell_export.'">';
			print '</td></tr>';

			// Accountancy_code_buy
			print '<tr><td>'.$langs->trans("ProductAccountancyBuyCode").'</td>';
			print '<td class="maxwidthonsmartphone"><input class="minwidth100" name="accountancy_code_buy" value="'.$object->accountancy_code_buy.'">';
			print '</td></tr>';

			// Accountancy_code_buy_intra
			if ($mysoc->isInEEC())
			{
				print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancyBuyIntraCode").'</td>';
				print '<td class="maxwidthonsmartphone"><input class="minwidth100" name="accountancy_code_buy_intra" value="'.$object->accountancy_code_buy_intra.'">';
				print '</td></tr>';
			}

			// Accountancy_code_buy_export
			print '<tr><td class="titlefieldcreate">'.$langs->trans("ProductAccountancyBuyExportCode").'</td>';
			print '<td class="maxwidthonsmartphone"><input class="minwidth100" name="accountancy_code_buy_export" value="'.$object->accountancy_code_buy_export.'">';
			print '</td></tr>';
		}
		print '</table>';

		dol_fiche_end();

		print '<div class="center">';
		print '<input type="submit" class="button" value="'.$langs->trans("Create").'">';
		print ' &nbsp; &nbsp; ';
                if($datapopup != 1) {
                    print '<input type="button" class="button" value="'.$langs->trans("Cancel").'" onClick="javascript:history.go(-1)">';
                }
		print '</div>';

		print '</form>';
	}

    /*
     * Product card
     */

    elseif ($object->id > 0)
    {
        // Fiche en mode edition
		if ($action == 'edit' && $usercancreate)
		{
            //WYSIWYG Editor
            require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

            $type = $langs->trans('Product');
            if ($object->isService()) $type = $langs->trans('Service');
            //print load_fiche_titre($langs->trans('Modify').' '.$type.' : '.(is_object($object->oldcopy)?$object->oldcopy->ref:$object->ref), "");

            // Main official, simple, and not duplicated code
            print '<form action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'" method="POST">'."\n";
            print '<input type="hidden" name="token" value="'.newToken().'">';
            print '<input type="hidden" name="action" value="update">';
            print '<input type="hidden" name="id" value="'.$object->id.'">';
            print '<input type="hidden" name="canvas" value="'.$object->canvas.'">';

            $head = product_prepare_head($object);
            $titre = $langs->trans("CardProduct".$object->type);
            $picto = ($object->type == Product::TYPE_SERVICE ? 'service' : 'product');
            dol_fiche_head($head, 'card', $titre, 0, $picto);

            print '<table class="border allwidth">';

            // Ref
            print '<tr><td class="titlefield fieldrequired">'.$langs->trans("Ref").'</td><td colspan="3"><input name="ref" class="maxwidth200" maxlength="128" value="'.dol_escape_htmltag($object->ref).'"></td></tr>';

            // Label
            print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td><td colspan="3"><input name="label" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag($object->label).'"></td></tr>';

            // Status To sell
            print '<tr><td class="fieldrequired">'.$langs->trans("Status").' ('.$langs->trans("Sell").')</td><td colspan="3">';
            print '<select class="flat" name="statut">';
            if ($object->status)
            {
                print '<option value="1" selected>'.$langs->trans("OnSell").'</option>';
                print '<option value="0">'.$langs->trans("NotOnSell").'</option>';
            }
            else
            {
                print '<option value="1">'.$langs->trans("OnSell").'</option>';
                print '<option value="0" selected>'.$langs->trans("NotOnSell").'</option>';
            }
            print '</select>';
            print '</td></tr>';

            // Status To Buy
            print '<tr><td class="fieldrequired">'.$langs->trans("Status").' ('.$langs->trans("Buy").')</td><td colspan="3">';
            print '<select class="flat" name="statut_buy">';
            if ($object->status_buy)
            {
                print '<option value="1" selected>'.$langs->trans("ProductStatusOnBuy").'</option>';
                print '<option value="0">'.$langs->trans("ProductStatusNotOnBuy").'</option>';
            }
            else
            {
                print '<option value="1">'.$langs->trans("ProductStatusOnBuy").'</option>';
                print '<option value="0" selected>'.$langs->trans("ProductStatusNotOnBuy").'</option>';
            }
            print '</select>';
            print '</td></tr>';

			// Batch number managment
			if ($conf->productbatch->enabled)
			{
				if ($object->isProduct() || !empty($conf->global->STOCK_SUPPORTS_SERVICES))
				{
					print '<tr><td>'.$langs->trans("ManageLotSerial").'</td><td colspan="3">';
					$statutarray = array('0' => $langs->trans("ProductStatusNotOnBatch"), '1' => $langs->trans("ProductStatusOnBatch"));
					print $form->selectarray('status_batch', $statutarray, $object->status_batch);
					print '</td></tr>';
				}
			}

            // Barcode
            $showbarcode = empty($conf->barcode->enabled) ? 0 : 1;
            if (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->barcode->lire_advance)) $showbarcode = 0;

	        if ($showbarcode)
	        {
		        print '<tr><td>'.$langs->trans('BarcodeType').'sefsef</td><td>';
		        if (isset($_POST['fk_barcode_type']))
		        {
		         	$fk_barcode_type = GETPOST('fk_barcode_type');
		        }
		        else
		        {
	        		$fk_barcode_type = $object->barcode_type;
		        	if (empty($fk_barcode_type) && !empty($conf->global->PRODUIT_DEFAULT_BARCODE_TYPE)) $fk_barcode_type = $conf->global->PRODUIT_DEFAULT_BARCODE_TYPE;
		        }
		        require_once DOL_DOCUMENT_ROOT.'/core/class/html.formbarcode.class.php';
	            $formbarcode = new FormBarCode($db);
                print $formbarcode->selectBarcodeType($fk_barcode_type, 'fk_barcode_type', 1);
		        print '</td><td>'.$langs->trans("BarcodeValue").'</td><td>';
		        $tmpcode = isset($_POST['barcode']) ?GETPOST('barcode') : $object->barcode;
		        if (empty($tmpcode) && !empty($modBarCodeProduct->code_auto)) $tmpcode = $modBarCodeProduct->getNextValue($object, $type);
		        print '<input size="40" class="maxwidthonsmartphone" type="text" name="barcode" value="'.dol_escape_htmltag($tmpcode).'">';
		        print '</td></tr>';
	        }

            // Description (used in invoice, propal...)
            print '<tr><td class="tdtop">'.$langs->trans("Description").'</td><td colspan="3">';

            // We use dolibarr_details as type of DolEditor here, because we must not accept images as description is included into PDF and not accepted by TCPDF.
            $doleditor = new DolEditor('desc', $object->description, '', 160, 'dolibarr_details', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
            $doleditor->Create();

            print "</td></tr>";
            print "\n";

            // Public Url
            print '<tr><td>'.$langs->trans("PublicUrl").'</td><td colspan="3">';
            print '<input type="text" name="url" class="quatrevingtpercent" value="'.$object->url.'">';
            print '</td></tr>';

            // Stock
            if ($object->isProduct() && !empty($conf->stock->enabled))
            {
                // Default warehouse
                print '<tr><td>'.$langs->trans("DefaultWarehouse").'</td><td>';
                print $formproduct->selectWarehouses($object->fk_default_warehouse, 'fk_default_warehouse', 'warehouseopen', 1);
                print ' <a href="'.DOL_URL_ROOT.'/product/stock/card.php?action=create&amp;backtopage='.urlencode($_SERVER['PHP_SELF'].'?action=create&type='.GETPOST('type', 'int')).'"><span class="fa fa-plus-circle valignmiddle paddingleft" title="'.$langs->trans("AddWarehouse").'"></span></a>';
                print '</td>';
                /*
                print "<tr>".'<td>'.$langs->trans("StockLimit").'</td><td>';
                print '<input name="seuil_stock_alerte" size="4" value="'.$object->seuil_stock_alerte.'">';
                print '</td>';

                print '<td>'.$langs->trans("DesiredStock").'</td><td>';
                print '<input name="desiredstock" size="4" value="'.$object->desiredstock.'">';
                print '</td></tr>';
                */
            }
            /*
            else
            {
                print '<input name="seuil_stock_alerte" type="hidden" value="'.$object->seuil_stock_alerte.'">';
                print '<input name="desiredstock" type="hidden" value="'.$object->desiredstock.'">';
            }*/

            if ($object->isService())
            {
                // Duration
                print '<tr><td>'.$langs->trans("Duration").'</td><td colspan="3">';
                print '<input name="duration_value" size="5" value="'.$object->duration_value.'"> ';
                print $formproduct->selectMeasuringUnits("duration_unit", "time", $object->duration_unit, 0, 1);
                print '</td></tr>';
            }
            else
            {
                // Nature
                print '<tr><td>'.$langs->trans("Nature").'</td><td colspan="3">';
                $statutarray = array('-1'=>'&nbsp;', '1' => $langs->trans("Finished"), '0' => $langs->trans("RowMaterial"));
                print $form->selectarray('finished', $statutarray, $object->finished);
                print '</td></tr>';

                // Brut Weight
                print '<tr><td>'.$langs->trans("Weight").'</td><td colspan="3">';
                print '<input name="weight" size="5" value="'.$object->weight.'"> ';
                print $formproduct->selectMeasuringUnits("weight_units", "weight", $object->weight_units, 0, 2);
                print '</td></tr>';

                if (empty($conf->global->PRODUCT_DISABLE_SIZE))
                {
					// Brut Length
					print '<tr><td>'.$langs->trans("Length").' x '.$langs->trans("Width").' x '.$langs->trans("Height").'</td><td colspan="3">';
					print '<input name="size" size="5" value="'.$object->length.'">x';
					print '<input name="sizewidth" size="5" value="'.$object->width.'">x';
					print '<input name="sizeheight" size="5" value="'.$object->height.'"> ';
					print $formproduct->selectMeasuringUnits("size_units", "size", $object->length_units, 0, 2);
					print '</td></tr>';
                }
                if (empty($conf->global->PRODUCT_DISABLE_SURFACE))
                {
                    // Brut Surface
                    print '<tr><td>'.$langs->trans("Surface").'</td><td colspan="3">';
                    print '<input name="surface" size="5" value="'.$object->surface.'"> ';
                    print $formproduct->selectMeasuringUnits("surface_units", "surface", $object->surface_units, 0, 2);
                    print '</td></tr>';
                }
                if (empty($conf->global->PRODUCT_DISABLE_VOLUME))
                {
                    // Brut Volume
                    print '<tr><td>'.$langs->trans("Volume").'</td><td colspan="3">';
                    print '<input name="volume" size="5" value="'.$object->volume.'"> ';
                    print $formproduct->selectMeasuringUnits("volume_units", "volume", $object->volume_units, 0, 2);
                    print '</td></tr>';
                }

                if (!empty($conf->global->PRODUCT_ADD_NET_MEASURE))
                {
                	// Net Measure
	                print '<tr><td>'.$langs->trans("NetMeasure").'</td><td colspan="3">';
	                print '<input name="net_measure" size="5" value="'.$object->net_measure.'"> ';
	                print $formproduct->selectMeasuringUnits("net_measure_units", "", $object->net_measure_units, 0, 0);
	                print '</td></tr>';
                }
            }
        	// Units
	        if ($conf->global->PRODUCT_USE_UNITS)
	        {
		        print '<tr><td>'.$langs->trans('DefaultUnitToShow').'</td>';
		        print '<td colspan="3">';
		        print $form->selectUnits($object->fk_unit, 'units');
		        print '</td></tr>';
	        }

	        // Custom code
    	    if (!$object->isService() && empty($conf->global->PRODUCT_DISABLE_CUSTOM_INFO))
        	{
	            print '<tr><td>'.$langs->trans("CustomCode").'</td><td><input name="customcode" class="maxwidth100onsmartphone" value="'.$object->customcode.'"></td>';
	            // Origin country
	            print '<td>'.$langs->trans("CountryOrigin").'</td><td>';
	            print $form->select_country($object->country_id, 'country_id', '', 0, 'minwidth100 maxwidthonsmartphone');
	            if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
	            print '</td></tr>';
        	}

            // Other attributes
            $parameters = array('colspan' => ' colspan="3"', 'cols' => 3);
            $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
            print $hookmanager->resPrint;
            if (empty($reshook))
            {
            	print $object->showOptionals($extrafields, 'edit', $parameters);
            }

			// Tags-Categories
            if ($conf->categorie->enabled)
			{
				print '<tr><td>'.$langs->trans("Categories").'</td><td colspan="3">';
				$cate_arbo = $form->select_all_categories(Categorie::TYPE_PRODUCT, '', 'parent', 64, 0, 1);
				$c = new Categorie($db);
				$cats = $c->containing($object->id, Categorie::TYPE_PRODUCT);
				$arrayselected = array();
				if (is_array($cats)) {
					foreach ($cats as $cat) {
						$arrayselected[] = $cat->id;
					}
				}
				print $form->multiselectarray('categories', $cate_arbo, $arrayselected, '', 0, '', 0, '100%');
				print "</td></tr>";
			}

            // Note private
			if (!empty($conf->global->MAIN_DISABLE_NOTES_TAB))
			{
                print '<tr><td class="tdtop">'.$langs->trans("NoteNotVisibleOnBill").'</td><td colspan="3">';

                $doleditor = new DolEditor('note_private', $object->note_private, '', 140, 'dolibarr_notes', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_4, '90%');
                $doleditor->Create();

                print "</td></tr>";
			}

            print '</table>';

            print '<br>';

            print '<table class="border centpercent">';

			if (!empty($conf->accounting->enabled))
			{
				// Accountancy_code_sell
				print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancySellCode").'</td>';
				print '<td>';
				print $formaccounting->select_account($object->accountancy_code_sell, 'accountancy_code_sell', 1, '', 1, 1);
				print '</td></tr>';

				// Accountancy_code_sell_intra
				if ($mysoc->isInEEC())
				{
					print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancySellIntraCode").'</td>';
					print '<td>';
					print $formaccounting->select_account($object->accountancy_code_sell_intra, 'accountancy_code_sell_intra', 1, '', 1, 1);
					print '</td></tr>';
				}

				// Accountancy_code_sell_export
				print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancySellExportCode").'</td>';
				print '<td>';
				print $formaccounting->select_account($object->accountancy_code_sell_export, 'accountancy_code_sell_export', 1, '', 1, 1);
				print '</td></tr>';

				// Accountancy_code_buy
				print '<tr><td>'.$langs->trans("ProductAccountancyBuyCode").'</td>';
				print '<td>';
				print $formaccounting->select_account($object->accountancy_code_buy, 'accountancy_code_buy', 1, '', 1, 1);
				print '</td></tr>';

				// Accountancy_code_buy_intra
				if ($mysoc->isInEEC())
				{
					print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancyBuyIntraCode").'</td>';
					print '<td>';
					print $formaccounting->select_account($object->accountancy_code_buy_intra, 'accountancy_code_buy_intra', 1, '', 1, 1);
					print '</td></tr>';
				}

				// Accountancy_code_buy_export
				print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancyBuyExportCode").'</td>';
				print '<td>';
				print $formaccounting->select_account($object->accountancy_code_buy_export, 'accountancy_code_buy_export', 1, '', 1, 1);
				print '</td></tr>';
			}
			else // For external software
			{
				// Accountancy_code_sell
				print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancySellCode").'</td>';
				print '<td><input name="accountancy_code_sell" class="maxwidth200" value="'.$object->accountancy_code_sell.'">';
				print '</td></tr>';

				// Accountancy_code_sell_intra
				if ($mysoc->isInEEC())
				{
					print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancySellIntraCode").'</td>';
					print '<td><input name="accountancy_code_sell_intra" class="maxwidth200" value="'.$object->accountancy_code_sell_intra.'">';
					print '</td></tr>';
				}

				// Accountancy_code_sell_export
				print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancySellExportCode").'</td>';
				print '<td><input name="accountancy_code_sell_export" class="maxwidth200" value="'.$object->accountancy_code_sell_export.'">';
				print '</td></tr>';

				// Accountancy_code_buy
				print '<tr><td>'.$langs->trans("ProductAccountancyBuyCode").'</td>';
				print '<td><input name="accountancy_code_buy" class="maxwidth200" value="'.$object->accountancy_code_buy.'">';
				print '</td></tr>';

				// Accountancy_code_buy_intra
				if ($mysoc->isInEEC())
				{
					print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancyBuyIntraCode").'</td>';
					print '<td><input name="accountancy_code_buy_intra" class="maxwidth200" value="'.$object->accountancy_code_buy_intra.'">';
					print '</td></tr>';
				}

				// Accountancy_code_buy_export
				print '<tr><td class="titlefield">'.$langs->trans("ProductAccountancyBuyExportCode").'</td>';
				print '<td><input name="accountancy_code_buy_export" class="maxwidth200" value="'.$object->accountancy_code_buy_export.'">';
				print '</td></tr>';
			}
			print '</table>';

			dol_fiche_end();

			print '<div class="center">';
			print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
			print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
			print '</div>';

			print '</form>';
		}
        // Fiche en mode visu
        else
		{
            $showbarcode = empty($conf->barcode->enabled) ? 0 : 1;
            if (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->barcode->lire_advance)) $showbarcode = 0;

		    $head = product_prepare_head($object);
            $titre = $langs->trans("CardProduct".$object->type);
            $picto = ($object->type == Product::TYPE_SERVICE ? 'service' : 'product');

            dol_fiche_head($head, 'card', $titre, -1, $picto);

            $linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1&type='.$object->type.'">'.$langs->trans("BackToList").'</a>';
            $object->next_prev_filter = " fk_product_type = ".$object->type;

            $shownav = 1;
            if ($user->socid && !in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) $shownav = 0;

            dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');


            print '<div class="fichecenter">';
            print '<div class="fichehalfleft">';

            print '<div class="underbanner clearboth"></div>';
            print '<table class="border tableforfield" width="100%">';

			// Type
			if (!empty($conf->product->enabled) && !empty($conf->service->enabled))
			{
				// TODO change for compatibility with edit in place
				$typeformat = 'select;0:'.$langs->trans("Product").',1:'.$langs->trans("Service");
				print '<tr><td class="titlefield">';
				print (empty($conf->global->PRODUCT_DENY_CHANGE_PRODUCT_TYPE)) ? $form->editfieldkey("Type", 'fk_product_type', $object->type, $object, $usercancreate, $typeformat) : $langs->trans('Type');
				print '</td><td colspan="2">';
				print $form->editfieldval("Type", 'fk_product_type', $object->type, $object, $usercancreate, $typeformat);
				print '</td></tr>';
			}

            if ($showbarcode)
            {
                // Barcode type
                print '<tr><td class="nowrap">';
                print '<table width="100%" class="nobordernopadding"><tr><td class="nowrap">';
                print $langs->trans("BarcodeType");
                print '</td>';
                if (($action != 'editbarcodetype') && $usercancreate && $createbarcode) print '<td class="right"><a class="editfielda" href="'.$_SERVER["PHP_SELF"].'?action=editbarcodetype&amp;id='.$object->id.'">'.img_edit($langs->trans('Edit'), 1).'</a></td>';
                print '</tr></table>';
                print '</td><td colspan="2">';
                if ($action == 'editbarcodetype' || $action == 'editbarcode')
                {
                    require_once DOL_DOCUMENT_ROOT.'/core/class/html.formbarcode.class.php';
                    $formbarcode = new FormBarCode($db);
		}
                if ($action == 'editbarcodetype')
                {
                    print $formbarcode->formBarcodeType($_SERVER['PHP_SELF'].'?id='.$object->id, $object->barcode_type, 'fk_barcode_type');
                }
                else
                {
                    $object->fetch_barcode();
                    print $object->barcode_type_label ? $object->barcode_type_label : ($object->barcode ? '<div class="warning">'.$langs->trans("SetDefaultBarcodeType").'<div>' : '');
                }
                print '</td></tr>'."\n";

                // Barcode value
                print '<tr><td class="nowrap">';
                print '<table width="100%" class="nobordernopadding"><tr><td class="nowrap">';
                print $langs->trans("BarcodeValue");
                print '</td>';
                if (($action != 'editbarcode') && $usercancreate && $createbarcode) print '<td class="right"><a class="editfielda" href="'.$_SERVER["PHP_SELF"].'?action=editbarcode&amp;id='.$object->id.'">'.img_edit($langs->trans('Edit'), 1).'</a></td>';
                print '</tr></table>';
                print '</td><td colspan="2">';
                if ($action == 'editbarcode')
                {
					$tmpcode = isset($_POST['barcode']) ?GETPOST('barcode') : $object->barcode;
					if (empty($tmpcode) && !empty($modBarCodeProduct->code_auto)) $tmpcode = $modBarCodeProduct->getNextValue($object, $type);

					print '<form method="post" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
					print '<input type="hidden" name="token" value="'.newToken().'">';
					print '<input type="hidden" name="action" value="setbarcode">';
					print '<input type="hidden" name="barcode_type_code" value="'.$object->barcode_type_code.'">';
					print '<input size="40" class="maxwidthonsmartphone" type="text" name="barcode" value="'.$tmpcode.'">';
					print '&nbsp;<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
					print '</form>';
                }
                else
                {
					print $object->barcode;
                }
                print '</td></tr>'."\n";
            }

			// Accountancy sell code
			print '<tr><td class="nowrap">';
			print $langs->trans("ProductAccountancySellCode");
			print '</td><td colspan="2">';
			if (!empty($conf->accounting->enabled))
			{
				if (!empty($object->accountancy_code_sell))
				{
					$accountingaccount = new AccountingAccount($db);
					$accountingaccount->fetch('', $object->accountancy_code_sell, 1);

					print $accountingaccount->getNomUrl(0, 1, 1, '', 1);
				}
			} else {
				print $object->accountancy_code_sell;
			}
			print '</td></tr>';

			// Accountancy sell code intra-community
			if ($mysoc->isInEEC())
			{
				print '<tr><td class="nowrap">';
				print $langs->trans("ProductAccountancySellIntraCode");
				print '</td><td colspan="2">';
				if (!empty($conf->accounting->enabled))
				{
					if (!empty($object->accountancy_code_sell_intra))
					{
						$accountingaccount2 = new AccountingAccount($db);
						$accountingaccount2->fetch('', $object->accountancy_code_sell_intra, 1);

						print $accountingaccount2->getNomUrl(0, 1, 1, '', 1);
					}
				} else {
					print $object->accountancy_code_sell_intra;
				}
				print '</td></tr>';
			}

			// Accountancy sell code export
			print '<tr><td class="nowrap">';
			print $langs->trans("ProductAccountancySellExportCode");
			print '</td><td colspan="2">';
			if (!empty($conf->accounting->enabled))
			{
				if (!empty($object->accountancy_code_sell_export))
				{
					$accountingaccount3 = new AccountingAccount($db);
					$accountingaccount3->fetch('', $object->accountancy_code_sell_export, 1);

					print $accountingaccount3->getNomUrl(0, 1, 1, '', 1);
				}
			} else {
				print $object->accountancy_code_sell_export;
			}
			print '</td></tr>';

			// Accountancy buy code
			print '<tr><td class="nowrap">';
			print $langs->trans("ProductAccountancyBuyCode");
			print '</td><td colspan="2">';
			if (!empty($conf->accounting->enabled))
			{
				if (!empty($object->accountancy_code_buy))
				{
					$accountingaccount4 = new AccountingAccount($db);
					$accountingaccount4->fetch('', $object->accountancy_code_buy, 1);

					print $accountingaccount4->getNomUrl(0, 1, 1, '', 1);
				}
			} else {
				print $object->accountancy_code_buy;
			}
			print '</td></tr>';

			// Accountancy buy code intra-community
			if ($mysoc->isInEEC())
			{
				print '<tr><td class="nowrap">';
				print $langs->trans("ProductAccountancyBuyIntraCode");
				print '</td><td colspan="2">';
				if (!empty($conf->accounting->enabled))
				{
					if (!empty($object->accountancy_code_buy_intra))
					{
						$accountingaccount5 = new AccountingAccount($db);
						$accountingaccount5->fetch('', $object->accountancy_code_buy_intra, 1);

						print $accountingaccount5->getNomUrl(0, 1, 1, '', 1);
					}
				} else {
					print $object->accountancy_code_buy_intra;
				}
				print '</td></tr>';
			}

			// Accountancy buy code export
			print '<tr><td class="nowrap">';
			print $langs->trans("ProductAccountancyBuyExportCode");
			print '</td><td colspan="2">';
			if (!empty($conf->accounting->enabled))
			{
				if (!empty($object->accountancy_code_buy_export))
				{
					$accountingaccount6 = new AccountingAccount($db);
					$accountingaccount6->fetch('', $object->accountancy_code_buy_export, 1);

					print $accountingaccount6->getNomUrl(0, 1, 1, '', 1);
				}
			} else {
				print $object->accountancy_code_buy_export;
			}
			print '</td></tr>';

            // Batch number management (to batch)
            if (!empty($conf->productbatch->enabled))
            {
				if ($object->isProduct() || !empty($conf->global->STOCK_SUPPORTS_SERVICES))
				{
            		print '<tr><td>'.$langs->trans("ManageLotSerial").'</td><td colspan="2">';
            	    if (!empty($conf->use_javascript_ajax) && $usercancreate && !empty($conf->global->MAIN_DIRECT_STATUS_UPDATE)) {
            	        print ajax_object_onoff($object, 'status_batch', 'tobatch', 'ProductStatusOnBatch', 'ProductStatusNotOnBatch');
            	    } else {
            	        print $object->getLibStatut(0, 2);
            	    }
            	    print '</td></tr>';
				}
            }

            // Description
            print '<tr><td class="tdtop">'.$langs->trans("Description").'</td><td colspan="2">'.(dol_textishtml($object->description) ? $object->description : dol_nl2br($object->description, 1, true)).'</td></tr>';

            // Public URL
            print '<tr><td>'.$langs->trans("PublicUrl").'</td><td colspan="2">';
			print dol_print_url($object->url);
            print '</td></tr>';

            // Default warehouse
            if ($object->isProduct() && !empty($conf->stock->enabled))
            {
                $warehouse = new Entrepot($db);
                $warehouse->fetch($object->fk_default_warehouse);

                print '<tr><td>'.$langs->trans("DefaultWarehouse").'</td><td>';
                print (!empty($warehouse->id) ? $warehouse->getNomUrl(1) : '');
                print '</td>';
            }

            // Parent product.
            if (!empty($conf->variants->enabled) && ($object->isProduct() || $object->isService())) {
                $combination = new ProductCombination($db);

                if ($combination->fetchByFkProductChild($object->id) > 0) {
                    $prodstatic = new Product($db);
                    $prodstatic->fetch($combination->fk_product_parent);

                    // Parent product
                    print '<tr><td>'.$langs->trans("ParentProduct").'</td><td colspan="2">';
                    print $prodstatic->getNomUrl(1);
                    print '</td></tr>';
                }
            }

            print '</table>';
            print '</div>';
            print '<div class="fichehalfright"><div class="ficheaddleft">';

            print '<div class="underbanner clearboth"></div>';
            print '<table class="border tableforfield" width="100%">';

            if ($object->isService())
            {
                // Duration
                print '<tr><td class="titlefield">'.$langs->trans("Duration").'</td><td colspan="2">'.$object->duration_value.'&nbsp;';
                if ($object->duration_value > 1)
                {
                    $dur = array("i"=>$langs->trans("Minute"), "h"=>$langs->trans("Hours"), "d"=>$langs->trans("Days"), "w"=>$langs->trans("Weeks"), "m"=>$langs->trans("Months"), "y"=>$langs->trans("Years"));
                }
                elseif ($object->duration_value > 0)
                {
                    $dur = array("i"=>$langs->trans("Minute"), "h"=>$langs->trans("Hour"), "d"=>$langs->trans("Day"), "w"=>$langs->trans("Week"), "m"=>$langs->trans("Month"), "y"=>$langs->trans("Year"));
                }
                print (!empty($object->duration_unit) && isset($dur[$object->duration_unit]) ? $langs->trans($dur[$object->duration_unit]) : '')."&nbsp;";

                print '</td></tr>';
            }
            else
            {
                // Nature
                print '<tr><td class="titlefield">'.$langs->trans("Nature").'</td><td colspan="2">';
                print $object->getLibFinished();
                print '</td></tr>';

                // Brut Weight
                print '<tr><td class="titlefield">'.$langs->trans("Weight").'</td><td colspan="2">';
                if ($object->weight != '')
                {
                	print $object->weight." ".measuringUnitString(0, "weight", $object->weight_units);
                }
                else
                {
                    print '&nbsp;';
                }
                print "</td></tr>\n";

                if (empty($conf->global->PRODUCT_DISABLE_SIZE))
                {
                    // Brut Length
                    print '<tr><td>'.$langs->trans("Length").' x '.$langs->trans("Width").' x '.$langs->trans("Height").'</td><td colspan="2">';
                    if ($object->length != '' || $object->width != '' || $object->height != '')
                    {
                        print $object->length;
                        if ($object->width) print " x ".$object->width;
                        if ($object->height) print " x ".$object->height;
                        print ' '.measuringUnitString(0, "size", $object->length_units);
                    }
                    else
                    {
                        print '&nbsp;';
                    }
                    print "</td></tr>\n";
                }
                if (empty($conf->global->PRODUCT_DISABLE_SURFACE))
                {
                    // Brut Surface
                    print '<tr><td>'.$langs->trans("Surface").'</td><td colspan="2">';
                    if ($object->surface != '')
                    {
                    	print $object->surface." ".measuringUnitString(0, "surface", $object->surface_units);
                    }
                    else
                    {
                        print '&nbsp;';
                    }
                    print "</td></tr>\n";
                }
                if (empty($conf->global->PRODUCT_DISABLE_VOLUME))
                {
                    // Brut Volume
                    print '<tr><td>'.$langs->trans("Volume").'</td><td colspan="2">';
                    if ($object->volume != '')
                    {
                    	print $object->volume." ".measuringUnitString(0, "volume", $object->volume_units);
                    }
                    else
                    {
                        print '&nbsp;';
                    }
                    print "</td></tr>\n";
                }

                if (!empty($conf->global->PRODUCT_ADD_NET_MEASURE))
                {
                	// Net Measure
                	print '<tr><td class="titlefield">'.$langs->trans("NetMeasure").'</td><td colspan="2">';
                	if ($object->net_measure != '')
                	{
                		print $object->net_measure." ".measuringUnitString($object->net_measure_units);
                	}
                	else
                	{
                		print '&nbsp;';
                	}
                }
            }

			// Unit
			if (!empty($conf->global->PRODUCT_USE_UNITS))
			{
				$unit = $object->getLabelOfUnit();

				print '<tr><td>'.$langs->trans('DefaultUnitToShow').'</td><td>';
				if ($unit !== '') {
					print $langs->trans($unit);
				}
				print '</td></tr>';
			}

        	// Custom code
    	    if (!$object->isService() && empty($conf->global->PRODUCT_DISABLE_CUSTOM_INFO))
        	{
	            print '<tr><td>'.$langs->trans("CustomCode").'</td><td colspan="2">'.$object->customcode.'</td>';

            	// Origin country code
            	print '<tr><td>'.$langs->trans("CountryOrigin").'</td><td colspan="2">'.getCountry($object->country_id, 0, $db).'</td>';
        	}

            // Other attributes
        	$parameters = array('colspan' => ' colspan="'.(2 + (($showphoto || $showbarcode) ? 1 : 0)).'"', 'cols' => (2 + (($showphoto || $showbarcode) ? 1 : 0)));
            include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

			// Categories
			if ($conf->categorie->enabled) {
				print '<tr><td class="valignmiddle">'.$langs->trans("Categories").'</td><td colspan="3">';
				print $form->showCategories($object->id, Categorie::TYPE_PRODUCT, 1);
				print "</td></tr>";
			}

            // Note private
			if (!empty($conf->global->MAIN_DISABLE_NOTES_TAB))
			{
    			print '<!-- show Note --> '."\n";
                print '<tr><td class="tdtop">'.$langs->trans("NotePrivate").'</td><td colspan="'.(2 + (($showphoto || $showbarcode) ? 1 : 0)).'">'.(dol_textishtml($object->note_private) ? $object->note_private : dol_nl2br($object->note_private, 1, true)).'</td></tr>'."\n";
                print '<!-- End show Note --> '."\n";
			}

            print "</table>\n";
    		print '</div>';

            print '</div></div>';
            print '<div style="clear:both"></div>';

            dol_fiche_end();
        }
    }
    elseif ($action != 'create')
    {
        exit;
    }
}

// Load object modCodeProduct
$module = (!empty($conf->global->PRODUCT_CODEPRODUCT_ADDON) ? $conf->global->PRODUCT_CODEPRODUCT_ADDON : 'mod_codeproduct_leopard');
if (substr($module, 0, 16) == 'mod_codeproduct_' && substr($module, -3) == 'php')
{
    $module = substr($module, 0, dol_strlen($module) - 4);
}
$result = dol_include_once('/core/modules/product/'.$module.'.php');
if ($result > 0)
{
	$modCodeProduct = new $module();
}

$tmpcode = '';
if (!empty($modCodeProduct->code_auto)) $tmpcode = $modCodeProduct->getNextValue($object, $object->type);

// Define confirmation messages
$formquestionclone = array(
	'text' => $langs->trans("ConfirmClone"),
    array('type' => 'text', 'name' => 'clone_ref', 'label' => $langs->trans("NewRefForClone"), 'value' => empty($tmpcode) ? $langs->trans("CopyOf").' '.$object->ref : $tmpcode, 'size'=>24),
    array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneContentProduct"), 'value' => 1),
    array('type' => 'checkbox', 'name' => 'clone_categories', 'label' => $langs->trans("CloneCategoriesProduct"), 'value' => 1),
);
if (!empty($conf->global->PRODUIT_MULTIPRICES)) {
    $formquestionclone[] = array('type' => 'checkbox', 'name' => 'clone_prices', 'label' => $langs->trans("ClonePricesProduct").' ('.$langs->trans("CustomerPrices").')', 'value' => 0);
}
if (!empty($conf->global->PRODUIT_SOUSPRODUITS))
{
    $formquestionclone[] = array('type' => 'checkbox', 'name' => 'clone_composition', 'label' => $langs->trans('CloneCompositionProduct'), 'value' => 1);
}

// Confirm delete product
if (($action == 'delete' && (empty($conf->use_javascript_ajax) || !empty($conf->dol_use_jmobile)))	// Output when action = clone if jmobile or no js
	|| (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile)))							// Always output when not jmobile nor js
{
    print $form->formconfirm("card.php?id=".$object->id, $langs->trans("DeleteProduct"), $langs->trans("ConfirmDeleteProduct"), "confirm_delete", '', 0, "action-delete");
}

// Clone confirmation
if (($action == 'clone' && (empty($conf->use_javascript_ajax) || !empty($conf->dol_use_jmobile)))		// Output when action = clone if jmobile or no js
	|| (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile)))							// Always output when not jmobile nor js
{
    print $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneProduct', $object->ref), 'confirm_clone', $formquestionclone, 'yes', 'action-clone', 350, 600);
}


/* ************************************************************************** */
/*                                                                            */
/* Barre d'action                                                             */
/*                                                                            */
/* ************************************************************************** */
if ($action != 'create' && $action != 'edit')
{
    print "\n".'<div class="tabsAction">'."\n";

    $parameters = array();
    $reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
    if (empty($reshook))
	{
		if ($usercancreate)
        {
            if (!isset($object->no_button_edit) || $object->no_button_edit <> 1) print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&amp;id='.$object->id.'">'.$langs->trans("Modify").'</a>';

            if (!isset($object->no_button_copy) || $object->no_button_copy <> 1)
            {
                if (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile))
                {
                    print '<span id="action-clone" class="butAction">'.$langs->trans('ToClone').'</span>'."\n";
                }
                else
    			{
                    print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=clone&amp;id='.$object->id.'">'.$langs->trans("ToClone").'</a>';
                }
            }
        }
        $object_is_used = $object->isObjectUsed($object->id);

        if ($usercandelete)
        {
            if (empty($object_is_used) && (!isset($object->no_button_delete) || $object->no_button_delete <> 1))
            {
                if (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile))
                {
                    print '<span id="action-delete" class="butActionDelete">'.$langs->trans('Delete').'</span>'."\n";
                }
                else
    			{
                    print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?action=delete&amp;id='.$object->id.'">'.$langs->trans("Delete").'</a>';
                }
            }
            else
    		{
                print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("ProductIsUsed").'">'.$langs->trans("Delete").'</a>';
            }
        }
        else
    	{
            print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans("Delete").'</a>';
        }
    }

    print "\n</div>\n";
}

/*
 * All the "Add to" areas
 */

if (!empty($conf->global->PRODUCT_ADD_FORM_ADD_TO) && $object->id && ($action == '' || $action == 'view') && $object->status)
{
    //Variable used to check if any text is going to be printed
    $html = '';
	//print '<div class="fichecenter"><div class="fichehalfleft">';

    // Propals
    if (!empty($conf->propal->enabled) && $user->rights->propale->creer)
    {
        $propal = new Propal($db);

        $langs->load("propal");

        $otherprop = $propal->liste_array(2, 1, 0);

        if (is_array($otherprop) && count($otherprop))
        {
        	$html .= '<tr><td style="width: 200px;">';
        	$html .= $langs->trans("AddToDraftProposals").'</td><td>';
        	$html .= $form->selectarray("propalid", $otherprop, 0, 1);
        	$html .= '</td></tr>';
        }
        else
		{
        	$html .= '<tr><td style="width: 200px;">';
        	$html .= $langs->trans("AddToDraftProposals").'</td><td>';
        	$html .= $langs->trans("NoDraftProposals");
        	$html .= '</td></tr>';
        }
    }

    // Commande
    if (!empty($conf->commande->enabled) && $user->rights->commande->creer)
    {
        $commande = new Commande($db);

        $langs->load("orders");

        $othercom = $commande->liste_array(2, 1, null);
        if (is_array($othercom) && count($othercom))
        {
        	$html .= '<tr><td style="width: 200px;">';
        	$html .= $langs->trans("AddToDraftOrders").'</td><td>';
        	$html .= $form->selectarray("commandeid", $othercom, 0, 1);
        	$html .= '</td></tr>';
        }
        else
		{
        	$html .= '<tr><td style="width: 200px;">';
        	$html .= $langs->trans("AddToDraftOrders").'</td><td>';
        	$html .= $langs->trans("NoDraftOrders");
        	$html .= '</td></tr>';
        }
    }

    // Factures
    if (!empty($conf->facture->enabled) && $user->rights->facture->creer)
    {
    	$invoice = new Facture($db);

    	$langs->load("bills");

    	$otherinvoice = $invoice->liste_array(2, 1, null);
    	if (is_array($otherinvoice) && count($otherinvoice))
    	{
    		$html .= '<tr><td style="width: 200px;">';
    		$html .= $langs->trans("AddToDraftInvoices").'</td><td>';
    		$html .= $form->selectarray("factureid", $otherinvoice, 0, 1);
    		$html .= '</td></tr>';
    	}
    	else
    	{
    		$html .= '<tr><td style="width: 200px;">';
    		$html .= $langs->trans("AddToDraftInvoices").'</td><td>';
    		$html .= $langs->trans("NoDraftInvoices");
    		$html .= '</td></tr>';
    	}
    }

    //If any text is going to be printed, then we show the table
    if (!empty($html))
    {
	    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
    	print '<input type="hidden" name="token" value="'.newToken().'">';
    	print '<input type="hidden" name="action" value="addin">';

	    print load_fiche_titre($langs->trans("AddToDraft"), '', '');

		dol_fiche_head('');

    	$html .= '<tr><td class="nowrap">'.$langs->trans("Quantity").' ';
    	$html .= '<input type="text" class="flat" name="qty" size="1" value="1"></td>';
        $html .= '<td class="nowrap">'.$langs->trans("ReductionShort").'(%) ';
    	$html .= '<input type="text" class="flat" name="remise_percent" size="1" value="0">';
    	$html .= '</td></tr>';

    	print '<table width="100%" class="border">';
        print $html;
        print '</table>';

        print '<div class="center">';
        print '<input type="submit" class="button" value="'.$langs->trans("Add").'">';
        print '</div>';

        dol_fiche_end();

        print '</form>';
    }
}


/*
 * Documents generes
 */

if ($action != 'create' && $action != 'edit' && $action != 'delete')
{
    print '<div class="fichecenter"><div class="fichehalfleft">';
    print '<a name="builddoc"></a>'; // ancre

    // Documents
    $objectref = dol_sanitizeFileName($object->ref);
    $relativepath = $comref.'/'.$objectref.'.pdf';
    $filedir = $conf->product->dir_output.'/'.$objectref;
    $urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
    $genallowed = $usercanread;
    $delallowed = $usercancreate;

    print $formfile->showdocuments($modulepart, $object->ref, $filedir, $urlsource, $genallowed, $delallowed, '', 0, 0, 0, 28, 0, '', 0, '', $object->default_lang, '', $object);
    $somethingshown = $formfile->numoffiles;

    print '</div><div class="fichehalfright"><div class="ficheaddleft">';

    $MAXEVENT = 10;

    $morehtmlright = '<a href="'.DOL_URL_ROOT.'/product/agenda.php?id='.$object->id.'">';
    $morehtmlright .= $langs->trans("SeeAll");
    $morehtmlright .= '</a>';

    // List of actions on element
    include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
    $formactions = new FormActions($db);
    $somethingshown = $formactions->showactions($object, 'product', 0, 1, '', $MAXEVENT, '', $morehtmlright); // Show all action for product

    print '</div></div></div>';
}

// End of page
llxFooter();
$db->close();
