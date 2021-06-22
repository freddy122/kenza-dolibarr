<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2021 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    iwsync/admin/setup.php
 * \ingroup iwsync
 * \brief   IWSYNC setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
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
require DOL_DOCUMENT_ROOT.'/variants/class/ProductAttribute.class.php';
require DOL_DOCUMENT_ROOT.'/variants/class/ProductAttributeValue.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once 'class/sincprodfab.class.php';
require_once '../lib/PSWebServiceLibrary.php';



require_once '../lib/iwsync.lib.php';
//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("admin", "iwsync@iwsync"));

// Access control
if (!$user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

$value = GETPOST('value', 'alpha');

$arrayofparameters = array(
	'ACTIVATE_IN_PRD_EDIT'=>array('css'=>'minwidth500', 'enabled'=>1)
);

$error = 0;
$setupnotempty = 0;


/*
 * Actions
 */

if ((float) DOL_VERSION >= 6)
{
	include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';
}

/*
 * View
 */
$form = new Form($db);

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

$page_name = "IWSYNCSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_iwsync@iwsync');

// Configuration header
$head = iwsyncAdminPrepareHead();
dol_fiche_head($head, 'sinciwprodfab', '', -1, "iwsync@iwsync");


/******************************************************************************/
/********************  EXPORT PRODUIT          ********************************/
/******************************************************************************/
echo '<h2 >'.$langs->trans("sinciwprodfabList").'</h2><br>';

$form_html = new Form($db);
$prod_fab_class = new SincProdFab($db);
$prod_object =  new Product($db);
$prod_combination_object =  new ProductCombination($db);

$arrIdProd = [];
if(GETPOST("export_btn")){
    if(!empty(GETPOST("iw_prod_fab_name"))){
        $arrIdProd = GETPOST("iw_prod_fab_name");
    }
}/*elseif(GETPOST("export_all_btn")){
    //SELECT rowid  from llx_product where product_type_txt = 'fab' and position("_" in ref) = 0 order by rowid desc
    $sqlprodfab = "SELECT rowid from ".MAIN_DB_PREFIX."product where product_type_txt = 'fab' and position('_' in ref) = 0 order by rowid desc";
    $idsprods = $db->getRows($sqlprodfab);
    foreach($idsprods as $resId){
        $arrIdProd[] = $resId->rowid;
    }
}*/


$arr_param_to_send = [];
$arr_param_to_send['key_ws'] = $conf->global->CLE_API_PRESTA;
$arr_param_to_send['url_ws'] = $conf->global->URL_PRESTA;
$cmptProd = 0;

/* select all product */
$arrSelectAllProdFab = [];
/* end select all product */

if(GETPOST("export_btn") || GETPOST("export_all_btn")){
    if(!empty($arrIdProd)){
        foreach($arrIdProd as $idprod){
            $prod_object->fetch($idprod);

            $arr_param = [];

            /* nom produit */
            $arr_param["product_name"] = $prod_object->label;

            /* ref produit */
            $arr_param["product_ref"] = $prod_object->ref;
            
            /* weight produit */
            $arr_param["product_weight"] = $prod_object->weight;
            
            /* desc produit */
            $arr_param["product_desc"] = $prod_object->description;

            /* prix de vente ht produit */
            $arr_param["product_price"] = price2num($prod_object->price/1.085,3);

            /* ean13 produit */
            $arr_param['ean13'] = $prod_object->barcode;

            /* stock produit */
            $arr_param['stock'] = $prod_object->total_quantite_commander;


            /* categorie produit */
            $c = new Categorie($db);
            $cats = $c->containing($idprod, Categorie::TYPE_PRODUCT);
            $arrcategmerged = [];
            $arrCateg = [];
            if(is_array($cats)){
                $toprint = [];
                foreach($cats as $ctegories){
                    $ways = $ctegories->print_all_ways(' &gt;&gt; ', '', 0, 1);
                    $allways = $ctegories->get_all_ways(); // Load array of categories
                    $restoPrint = [];
                    foreach ($allways as $way)
                    {
                        $w = array();
                        $icm = 0;
                        foreach ($way as $cat)
                        {
                            if(!strpos($cat->label, 'kenza') && !strpos($cat->label, 'acine')){
                                $w[] = $icm."__". strtoupper(html_entity_decode($cat->label));
                                $icm++;
                            }
                        }
                    }
                    if(!empty($w)){
                        $toprint[] = $w;
                    }
                }
                $arrCategPrincipale = [];
                $arrCategChild = [];

                foreach($toprint as $prints){
                    $principaleCateg = array_shift(array_values($prints));
                    $arrCategPrincipale[] = $principaleCateg;
                    if(count($prints) > 1){
                        array_shift($prints);
                        $arrCategChild[] = $prints;

                    }
                }
                $arrcategmerged = array_merge(array_unique($arrCategPrincipale),$arrCategChild);
            }
            $arr_param['categs'] = $arrcategmerged;
            /* fin categorie produit */

            /* traitement image */
            if(is_dir(DOL_DATA_ROOT."/produit/".$prod_object->ref)){
                $filesprod = array_diff(scandir(DOL_DATA_ROOT."/produit/".$prod_object->ref), array('.', '..'));
                $imageParams = [];
                foreach($filesprod as $allf){
                    if(strpos($allf, ".pdf") === false && strpos($allf, "icon1.") === false && strpos($allf, "icon2.") === false &&  strpos($allf, "thumb") === false){
                        $imageParams[] = $allf;
                    }
                }
                $arr_param['images_prod'] = $imageParams;
            }else{
                $arr_param['images_prod'] = [];
            }
            /* fin traitement image */

            /* variant produit */
            $prodChild = $prod_combination_object->fetchAllByFkProductParent($idprod);
            $arrChild = [];
            $composition = "";
            foreach($prodChild as $child){
                $prod_object->fetch($child->fk_product_child);
                $arrtmpchid = [];
                $arrtmpchid["barcode"] = $prod_object->barcode;

                if(!empty($prod_object->composition)){
                    $composition = $prod_object->composition;
                }
                /* valeur du déclinaison */
                $sqlCombinationss = "SELECT "
                            . " pacv.fk_prod_attr, "
                            . " pacv.fk_prod_attr_val  "
                            . " FROM ".MAIN_DB_PREFIX."product_attribute_combination2val pacv "
                            . " left join ".MAIN_DB_PREFIX."product_attribute_combination pac on pac.rowid = pacv.fk_prod_combination "
                            . " WHERE pac.fk_product_child = ".$child->fk_product_child."  and pac.fk_product_parent = ".$child->fk_product_parent." order by pacv.fk_prod_attr asc";
                $resuCombinationss = $db->getRows($sqlCombinationss);
                $arrCombinationss = [];
                $prodcombi = new ProductCombination($db);
                foreach($resuCombinationss as $rescomb) {
                    $attributes = $prodcombi->getAttributeById($rescomb->fk_prod_attr);
                    $attributesValue = $prodcombi->getAttributeValueById($rescomb->fk_prod_attr_val);
                    $arrCombinationss[$attributes['label']] = $attributesValue['value'];
                    if($attributes['label'] == "Couleur"){
                        $arrCombinationss["code_couleur"] = $attributesValue["code_couleur"];
                    }
                }
                $arrtmpchid["combination_val"] = $arrCombinationss;
                $arrtmpchid["qty_comb"] = $prod_object->quantite_fabriquer;
                $arrtmpchid["weight_comb"] = $prod_object->weight_variant;
                $arrChild[] = $arrtmpchid;
            }
            $arr_param["combination"] = $arrChild;
            /* fin variant produit */

            /* compostion produit */
            $arr_param['composition'] = $composition;

            /* Creation du paramètre à envoyé */
            $arr_param_to_send['product_data'][$cmptProd] = $arr_param;
            $cmptProd++;
        }

        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $conf->global->URL_PRESTA.'iwsyncWebservice/product/iwsaddproduct.api.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arr_param_to_send));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        $ress = curl_exec($ch);
        curl_close($ch);
        print ($ress);
    }else{
        print "<span style='color:red;font-size:16px;'>Aucun donné envoyé, veuillez selectionner un ou plusieurs produit(s)</span><br>";
    }
}elseif(GETPOST("select_all_prodfab")){
    foreach($prod_fab_class->getParentProductFab() as $kresp => $resparent){
        $arrSelectAllProdFab[] = $kresp;
    }
}

print '<form method="POST">';
print '<table class="border tableforfield" width="100%">';

echo "<pre>";
echo "</pre>";
print '<tr>'
    . '<td style="width:20%"> Sélectionner les produits à exporter vers prestashop </td>'
    . '<td class="titlefield"> '.$form_html->multiselectarray("iw_prod_fab_name", $prod_fab_class->getParentProductFab(),(!empty($arrSelectAllProdFab)?$arrSelectAllProdFab:(!empty(GETPOST("iw_prod_fab_name"))?(GETPOST("de_select_all_prodfab")?[]:GETPOST("iw_prod_fab_name")):[])),0,0,'',0,"60%").' '
    . ' <input type="submit" name="select_all_prodfab" class="button" value="Selectionner tout">'
    . ' <input type="submit" name="de_select_all_prodfab" class="button" value="De-selectionner tout"> <br>'
    . '</td>'
    . '</tr>';
print '<tr>'
    . '<td colspan="2"> '
    . '<input type="submit" class="button" name="export_btn" value="Exporter">'
    //. '<input type="submit" class="button" name="export_all_btn" value="Exporter tous"> '
    . '</td>'
    . '</tr>';
print '<table>';
print '</form>';


// Page end
dol_fiche_end();

llxFooter();
$db->close();
