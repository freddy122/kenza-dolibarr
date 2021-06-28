<?php

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/product/modules_product.class.php';
require DOL_DOCUMENT_ROOT.'/variants/class/ProductAttribute.class.php';
require DOL_DOCUMENT_ROOT.'/variants/class/ProductAttributeValue.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/autoloader.php';
require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/phpexcel/Classes/PHPExcel.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
global $conf;
global $db;
global $user;
//$objPHPExcel = $objReader->load(DOL_DOCUMENT_ROOT."/importData/fic_lingerie.xls");
$inputFileName = DOL_DOCUMENT_ROOT."/importData/fic_lingerie.xls";
try {
    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
} catch(Exception $e) {
    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
}
$sheet = $objPHPExcel->getSheet(0);
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

$arrDataFinale = [];
for ($row = 2; $row <= $highestRow; $row++){ 
    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,NULL,TRUE,FALSE);
    //$object = new Product($db);
    $arrDataFinale[] = $rowData;
}
$arrProd = [];
$compt = 0;
foreach($arrDataFinale as $res){
    $arrProd[$res[0][1]][$compt] = $res[0];
    $compt++;
}



 echo "<pre>";
// print_r($arrProd);
$cats = new Categorie($db);
$rcakts = $cats->get_full_arbo(Categorie::TYPE_PRODUCT, 256, 1);
/*foreach($rcakts as $rcats){
    $sqldel = "delete from llx_categorie where rowid = ".$rcats['rowid'];
    $db->query($sqldel);
}*/

/*foreach($arrProd as $kprd => $vprd){
    
    foreach($vprd as $valcombi){
        $arrCombi = [];
        $sqlColor = "select rowid from ".MAIN_DB_PREFIX."product_attribute_value  where fk_product_attribute = 1 and ref  = '".str_replace(' ','-',trim(strtoupper($valcombi[3])))."' ";
        $resColor = $db->getRows($sqlColor);
        print_r($sqlColor."<br>");
        $sqlTaille = "select rowid from ".MAIN_DB_PREFIX."product_attribute_value  where fk_product_attribute = 2 and ref  = '".str_replace('/','',str_replace(' ','-',trim(strtoupper($valcombi[4]))))."' ";
        $resTaille = $db->getRows($sqlTaille);
        print_r($sqlTaille."<br><br><br>");
        $arrCombi['1'] = $resColor[0]->rowid;
        $arrCombi['2'] = $resTaille[0]->rowid;
    }
}*/



//exit;
    
/* classement produit par référence */
$compprod = 0;
foreach($arrProd as $kprd => $vprd){
    
    $firstElem = array_shift(array_values($vprd));
    $sqlCheckprod = "select rowid from ".MAIN_DB_PREFIX."product where ref = '".substr($firstElem[5],0,-1)."'";
    
    $rowcheck = $db->getRows($sqlCheckprod);
    if(empty($rowcheck)){
        /* Création produit parent */
        
        $object = new Product($db);
        $object->label = $firstElem[2];
        $object->ref_fab_frs = $firstElem[1];
        $object->product_type_txt = "fab";
        $object->price_base_type = "TTC";
        $object->barcode_type = 2;
        $object->lib_court = substr($firstElem[2],0,20);
        $object->ref = substr($firstElem[5],0,-1);

        $id = $object->create($user);

        /* catégorie */
        $arr_categ = [810];
        $sqlCategorie = "select rowid, label from ".MAIN_DB_PREFIX."categorie where label like '%".$firstElem[8]."%' or label like '%".$firstElem[9]."%'  ";
        $resSqlcat = $db->getRows($sqlCategorie);
        foreach($resSqlcat as $cats){
            $arr_categ[] = $cats->rowid;
        }
        $object->setCategories(array_unique($arr_categ));

        /* stock */
        $sqlCheckStock =  "SELECT fk_product from ".MAIN_DB_PREFIX."product_stock where fk_product = ".$id;
        $rescheckstock  = $db->query($sqlCheckStock);
        $resustock = $db->fetch_object($rescheckstock);
        $curdt = date('Y-m-d H:i:s');
        if(empty($resustock->fk_product)){

            $sqlUpdateStock = "INSERT INTO ".MAIN_DB_PREFIX."product_stock (tms,fk_product,fk_entrepot,reel) values ('".$curdt."',".$id.",1,0)";
            $db->query($sqlUpdateStock);
        }

        /* image par defaut */
        $upload_dir = $conf->product->multidir_output[$conf->entity];
        $sdir = $conf->product->multidir_output[$conf->entity];
        if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
            if (version_compare(DOL_VERSION, '3.8.0', '<')){
                $dir = $sdir .'/'. get_exdir($id,2) . $id ."/photos";
            }else {
                $dir = $sdir .'/'. get_exdir($id,2,0,0,$object,'product') . $id ."/photos";
            }
        } else {
            $principaleProd = "select ref from ".MAIN_DB_PREFIX."product where rowid= ".$id;
            $resRefss = $db->getRows($principaleProd);

            $dir = $sdir .'/'.dol_sanitizeFileName(substr($firstElem[5],0,-1));
        }
        if (! file_exists($dir)) {
            dol_mkdir($dir);
        }
        copy(DOL_DOCUMENT_ROOT."/product/defaulticon/icon1.png", $dir."/icon1.png");
        $sqlUpdateIcon1 = "update ".MAIN_DB_PREFIX."product set "
        . " icone_prod_1 = 'icon1.png' where rowid =  ".$id;
        $db->query($sqlUpdateIcon1);
        if (image_format_supported($dir."/icon1.png") == 1)
        {
            $imgThumbSmall = vignette($dir."/icon1.png", 200, 100, '_small', 80, "thumbs");
            $imgThumbMini  = vignette($dir."/icon1.png", 300, 150, '_mini', 80, "thumbs");
        }

        copy(DOL_DOCUMENT_ROOT."/product/defaulticon/icon2.png", $dir."/icon2.png");
        $sqlUpdateIcon2 = "update ".MAIN_DB_PREFIX."product set "
        . " icone_prod_2 = 'icon2.png' where rowid =  ".$id;
        $db->query($sqlUpdateIcon2);
        if (image_format_supported($dir."/icon2.png") == 1)
        {
            $imgThumbSmall = vignette($dir."/icon2.png", 200, 100, '_small', 80, "thumbs");
            $imgThumbMini  = vignette($dir."/icon2.png", 300, 150, '_mini', 80, "thumbs");
        }
        /* fin image par defaut */

        /* extra field */
        $sqlUpdateExtra = "INSERT INTO ".MAIN_DB_PREFIX."product_extrafields (tms,fk_object,theme,marque) values ('".$curdt."',".$id.",'".$firstElem[0]."','".$firstElem[7]."')";
        $db->query($sqlUpdateExtra);

        /* prix fournisseur */
        $productFournisseur = new ProductFournisseur($db);
        $supplierDefaultTest = new Fournisseur($db);
        $result = $supplierDefaultTest->fetch(3287);
        $productFournisseur->fetch($id);
        $productFournisseur->update_buyprice(1, 0.000001, $user, "HT", $supplierDefaultTest, 0, "ref_".$id, 8.5, 0, 0, 0, 0, 0, "", array(), '', 0, 'HT', 1, '', "", $object->barcode, "");

        /* combinaison */
        foreach($vprd as $valcombi){
            $arrCombi = [];
            $sqlColor = "select rowid from ".MAIN_DB_PREFIX."product_attribute_value  where fk_product_attribute = 1 and ref  = '".str_replace(' ','-',trim(strtoupper($valcombi[3])))."' ";
            $resColor = $db->getRows($sqlColor);
            $sqlTaille = "select rowid from ".MAIN_DB_PREFIX."product_attribute_value  where fk_product_attribute = 2 and ref  = '".str_replace('/','',str_replace(' ','-',trim(strtoupper($valcombi[4]))))."' ";
            $resTaille = $db->getRows($sqlTaille);
            $arrCombi['1'] = $resColor[0]->rowid;
            $arrCombi['2'] = $resTaille[0]->rowid;
            $arrOtherInfo = [];
            $arrOtherInfo["ref_tissus_couleur"] = "";
            $arrOtherInfo["quantite_commander"] = 0;
            $arrOtherInfo["quantite_fabriquer"] = 0;
            $arrOtherInfo["composition"]        = "";
            $arrOtherInfo["price_yuan"]         = floatval(0);
            $arrOtherInfo["price_euro"]         = floatval(0);
            $arrOtherInfo["poidsfabriq"]        = floatval(0);
            $arrOtherInfo["tauxChange"]         = floatval(7.5);
            $arrOtherInfo["ref_fab_frs"]        = $valcombi[1];
            $arrOtherInfo["lib_court"]          = substr($valcombi[2],0,20);
            $arrOtherInfo["codebares"]          = $valcombi[5];
            $arrOtherInfo["product_type_txt"]   = "fab";
            $arrOtherInfo["sell_price"]         = 0.00000001;
            $arrOtherInfo["tva_tx_fourn"]       = floatval(8.5);
            $arrOtherInfo["id_fourn_prod_fab"] = $supplierDefaultTest->id;
            $prodcomb = new ProductCombination($db);
            $prodcomb->createProductCombination($user, $object, $arrCombi, array(), false, false, false, false,$arrOtherInfo);
        }

        $compprod++;
    }
}

print_r($compprod . ' produits importées <br>');
 
//print_r($user->id);
//print_r($arrProd);
exit;
