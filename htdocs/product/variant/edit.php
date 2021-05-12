<?php
require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
global $conf;

$idProduct          = GETPOST("productid");
$quantite_commander = GETPOST("quantite_commander");
$quantite_fabriquer = GETPOST("quantite_fabriquer");
$ref_tissus_couleur = GETPOST("ref_tissus_couleur");
$weight_variant     = GETPOST("weight_variant");
$composition        = GETPOST("composition");
$price_yuan         = GETPOST("price_yuan");
$price_euro         = GETPOST("price_euro");
$taux_euro_yuan     = GETPOST("taux_euro_yuan");
$valColor           = GETPOST("valColor");
/* 
SELECT * FROM `llx_product_attribute_combination2val` pacv 
left join llx_product_attribute_combination pac on pac.rowid = pacv.fk_prod_combination
WHERE fk_prod_attr = 1 and fk_prod_attr_val = 61 and pac.fk_product_parent = 7940 
*/
$parentId = GETPOST("parentId");
$prodChild = new Product($db);
$prodChild->fetch($idProduct);

$prodCombinations = new ProductCombination($db);

$user->update($user);
$sql_user_group = "select fk_user,fk_usergroup from ".MAIN_DB_PREFIX."usergroup_user where fk_user = ".$user->id."";
$resuUser = $db->query($sql_user_group);
$reug = $db->fetch_object($resuUser);
$resu_fab = "";

if ($reug->fk_usergroup) {
    $sql_group = "select code from ".MAIN_DB_PREFIX."usergroup where rowid = ".$reug->fk_usergroup;
    $resuug = $db->query($sql_group);
    $resug = $db->fetch_object($resuug);
    $resu_fab = $resug->code;
}

if($_POST['posted_id']){
    $w_variant = !empty($weight_variant) ? $weight_variant : 0;
    $sqlUpdate = "UPDATE ".MAIN_DB_PREFIX."product set " ;
    if($resu_fab !== 'fab' && intval($quantite_commander) >=0 ) {
        $sqlUpdate       .= " quantite_commander = ".intval($quantite_commander).", " ;
        $sqlUpdate       .= " total_quantite_commander = ".intval($quantite_commander).", " ;
    }else{
        if(!empty($prodChild->quantite_commander)){
            $sqlUpdate       .= " quantite_commander = ".$prodChild->quantite_commander.", " ;
            $sqlUpdate       .= " total_quantite_commander = ".$prodChild->quantite_commander.", " ;
        }else{
            $sqlUpdate       .= " quantite_commander = 0, " ;
            $sqlUpdate       .= " total_quantite_commander = 0, " ;
        }
    }
    
    if(intval($quantite_fabriquer) >= 0){
        $sqlUpdate       .= " quantite_fabriquer = ".intval($quantite_fabriquer).", " ; 
        
        /* Mise à jour qty une seul fils */
        $sqlUpdatesStock = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                    . " set tms = '".date('Y-m-d h:i:s')."', "
                    . " fk_entrepot  = 1 ,"
                    . " reel = ".$quantite_fabriquer." where fk_product = ".$idProduct;
        $db->query($sqlUpdatesStock);
        
        /*Mise à jour qty produit parent */
        $arr_child_prod = [];
        foreach($prodCombinations->fetchAllByFkProductParent($parentId) as $resu){
            $sqlgetStockForOneProd = "select reel from ".MAIN_DB_PREFIX."product_stock where fk_product =  ".$resu->fk_product_child;
            $arr_child_prod[] = $db->getRows($sqlgetStockForOneProd)[0]->reel;
        }
        $sqlUpdateQtyParent = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                            . " set tms = '".date('Y-m-d h:i:s')."', "
                            . " fk_entrepot  = 1 ,"
                            . " reel = ".array_sum($arr_child_prod)." where fk_product = ".$parentId;
        $db->query($sqlUpdateQtyParent);
    }else{
        if(!empty($prodChild->quantite_fabriquer)){
            $sqlUpdate       .= " quantite_fabriquer = ".$prodChild->quantite_fabriquer.", " ;
            
            $sqlUpdatesStock = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                    . " set tms = '".date('Y-m-d h:i:s')."', "
                    . " fk_entrepot  = 1 ,"
                    . " reel = ".$prodChild->quantite_fabriquer." where fk_product = ".$idProduct;
            $db->query($sqlUpdatesStock);
            
            /*Mise à jour qty produit parent */
            $arr_child_prod = [];
            foreach($prodCombinations->fetchAllByFkProductParent($parentId) as $resu){
                $sqlgetStockForOneProd = "select reel from ".MAIN_DB_PREFIX."product_stock where fk_product =  ".$resu->fk_product_child;
                $arr_child_prod[] = $db->getRows($sqlgetStockForOneProd)[0]->reel;
            }
            $sqlUpdateQtyParent = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                                . " set tms = '".date('Y-m-d h:i:s')."', "
                                . " fk_entrepot  = 1 ,"
                                . " reel = ".array_sum($arr_child_prod)." where fk_product = ".$parentId;
            $db->query($sqlUpdateQtyParent);
        }else{
            $sqlUpdate       .= " quantite_fabriquer = 0, " ;
            $sqlUpdatesStock = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                    . " set tms = '".date('Y-m-d h:i:s')."', "
                    . " fk_entrepot  = 1 ,"
                    . " reel = 0 where fk_product = ".$idProduct;
            $db->query($sqlUpdatesStock);
            /*Mise à jour qty produit parent */
            $arr_child_prod = [];
            foreach($prodCombinations->fetchAllByFkProductParent($parentId) as $resu){
                $sqlgetStockForOneProd = "select reel from ".MAIN_DB_PREFIX."product_stock where fk_product =  ".$resu->fk_product_child;
                $arr_child_prod[] = $db->getRows($sqlgetStockForOneProd)[0]->reel;
            }
            $sqlUpdateQtyParent = "UPDATE ".MAIN_DB_PREFIX."product_stock "
                                . " set tms = '".date('Y-m-d h:i:s')."', "
                                . " fk_entrepot  = 1 ,"
                                . " reel = ".array_sum($arr_child_prod)." where fk_product = ".$parentId;
            $db->query($sqlUpdateQtyParent);
        }
    }
    
    if(!empty($composition)){
        $sqlUpdate       .= " composition = '".$composition."', " ;
    }else{
        if(!empty($prodChild->composition)){
            $sqlUpdate       .= " composition = '".$prodChild->composition."', " ;
        }else{
            $sqlUpdate       .= " composition = '', " ;
        }
    }
    
    if(!empty($ref_tissus_couleur)){
        $sqlUpdate       .= " ref_tissus_couleur = '".$ref_tissus_couleur."', " ;
    }else{
        if(!empty($prodChild->composition)){
            $sqlUpdate       .= " ref_tissus_couleur = '".$prodChild->ref_tissus_couleur."', " ;
        }else{
            $sqlUpdate       .= " ref_tissus_couleur = '', " ;
        }
    }
    
    if(!empty($taux_euro_yuan)){
        $sqlUpdate       .= " taux_euro_yuan = '".$taux_euro_yuan."', " ;
    }else{
        if(!empty($prodChild->taux_euro_yuan)){
            $sqlUpdate       .= " taux_euro_yuan = '".$prodChild->taux_euro_yuan."', " ;
        }else{
            $sqlUpdate       .= " taux_euro_yuan = 0, " ;
        }
    }
    
    if(!empty($price_yuan)){
        $totalYuan = (!empty($quantite_fabriquer) ? (intval($quantite_fabriquer)*floatval($price_yuan)) : (floatval($price_yuan)*intval($prodChild->quantite_fabriquer)) );
        $sqlUpdate       .= " price_yuan = ".floatval(str_replace(',','.',$price_yuan)).", " ; 
        $sqlUpdate       .= " total_montant_yuan = ".$totalYuan.", " ; 
    }else{
        if(!empty($prodChild->price_yuan)){
            $sqlUpdate       .= " price_yuan = ".$prodChild->price_yuan.", " ;
            $sqlUpdate       .= " total_montant_yuan = ".($prodChild->price_yuan*$prodChild->quantite_fabriquer).", " ;
        }else{
            $sqlUpdate       .= " price_yuan = 0, " ;
            $sqlUpdate       .= " total_montant_yuan = 0, " ;
        }
    }
    
    if(!empty($price_euro)) {
        $totalEuro = (!empty($quantite_fabriquer) ? (intval($quantite_fabriquer)*floatval($price_euro)) : (floatval($price_euro)*intval($prodChild->quantite_fabriquer)) );
        $sqlUpdate       .= " price_euro = ".floatval(str_replace(',','.',$price_euro)).", " ;
        $sqlUpdate       .= " total_montant_euro = ".$totalEuro.", " ;
        //$sqlUpdate       .= " price = ".floatval(str_replace(',','.',$price_euro)).", " ;
        //$sqlUpdate       .= " price_ttc = ".floatval(str_replace(',','.',$price_euro)).", " ;
        
        /* prix fournisseur */
        $qty_fab_calc = (!empty($quantite_fabriquer) ? $quantite_fabriquer : 1);
        $sqlUpdatePriceFournChild = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
            . " set price = ".floatval(floatval(str_replace(',','.',$price_euro))*$qty_fab_calc).", "
            . " quantity = ".floatval($qty_fab_calc).", "
            . " unitprice=".floatval(str_replace(',','.',$price_euro))." "
            . " where fk_product = ".intval($_POST['posted_id']);
        $db->query($sqlUpdatePriceFournChild);
    }else{
        if(!empty($prodChild->price_euro)){
            $sqlUpdate       .= " price_euro = ".$prodChild->price_euro.", " ;
            $sqlUpdate       .= " total_montant_euro = ".($prodChild->price_euro*$prodChild->quantite_fabriquer).", " ;
            //$sqlUpdate     .= " price = ".$prodChild->price_euro.", " ;
            //$sqlUpdate     .= " price_ttc = ".$prodChild->price_euro.", " ;
            
            /* prix fournisseur */
            $qty_fab_calc = (!empty($prodChild->quantite_fabriquer) ? $prodChild->quantite_fabriquer : 1);
            $sqlUpdatePriceFournChild = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
            . " set price = ".(floatval(str_replace(',','.',$prodChild->price_euro))*$qty_fab_calc).", "
            . " quantity = ".$qty_fab_calc.", "
            . " unitprice=".floatval(str_replace(',','.',$prodChild->price_euro))." "
            . " where fk_product = ".intval($_POST['posted_id']);
            $db->query($sqlUpdatePriceFournChild);
        }else{
            $sqlUpdate       .= " price_euro = 0, " ;
            $sqlUpdate       .= " total_montant_euro = 0, " ;
            //$sqlUpdate     .= " price = 0, " ;
            //$sqlUpdate     .= " price_ttc = 0, " ;
        }
    }
    
    if(!empty($w_variant)){
        $sqlUpdate       .= " weight_variant = ".floatval(str_replace(',','.',$w_variant)).", weight = ".floatval(str_replace(',','.',$w_variant))."  " ; 
    }else{
        if(!empty($prodChild->weight_variant)){
            $sqlUpdate       .= " weight_variant = ".$prodChild->weight_variant.", weight = ".$prodChild->weight_variant."  " ;
        }else{
            $sqlUpdate       .= " weight_variant = 0, weight = 0 " ;
        }
    }
    
    $sqlUpdate       .= " where rowid = ".$_POST['posted_id'];
    // print_r($sqlUpdate);die();
    $db->query($sqlUpdate);
    
    $prodCombinates = new ProductCombination($db);
    $resProdChild = $prodCombinates->fetchAllByFkProductParent($parentId);
    $totalQuantiteCom   = 0;
    $totalYuan          = 0;
    $totalEuro          = 0;
    $totalEuroFourn          = 0;
    foreach($resProdChild as $reChil){
        $prodChildUpdate   = new Product($db);
        $prodChildUpdate->fetch($reChil ->fk_product_child);
        $qtyfourn = (!empty($prodChildUpdate->quantite_fabriquer) ? $prodChildUpdate->quantite_fabriquer : 0);
        $totalQuantiteCom += $prodChildUpdate->quantite_fabriquer;
        $totalYuan        += $prodChildUpdate->quantite_fabriquer*$prodChildUpdate->price_yuan;
        $totalEuro        += $prodChildUpdate->quantite_fabriquer*$prodChildUpdate->price_euro;
        $totalEuroFourn        += $qtyfourn*$prodChildUpdate->price_euro;
    }
    //print_r($totalEuroFourn);
    /*Total Qty comm, yuan , euro*/
    $sqlUpdateMontantTotal = "update ".MAIN_DB_PREFIX."product "
    . " set total_quantite_commander = ".$totalQuantiteCom.", "
    . " total_montant_yuan = ".$totalYuan.", "
    . " total_montant_euro = ".$totalEuro." "
    . " where rowid =  ".intval($parentId);
    $db->query($sqlUpdateMontantTotal);
    
    $priceUnits = floatval($totalEuroFourn/(!empty($totalQuantiteCom)?$totalQuantiteCom:1));
    $sqlUpdatePriceFournParent = "UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price "
            . " set price = ".$totalEuroFourn.", "
            . " quantity = ".(!empty($totalQuantiteCom)?$totalQuantiteCom:1).", "
            . " unitprice=".$priceUnits." "
            . " where fk_product = ".intval($parentId);
    //print_r($sqlUpdatePriceFournParent);
    $db->query($sqlUpdatePriceFournParent);
   
    ?>
    <script type="text/javascript">
        window.parent.location.reload()
    </script>
    <?php
    exit;
}

?>
<style>
    .button{
        margin-bottom: 3px;
        margin-top: 3px;
        margin-right: 5px;
        font-family: roboto,arial,tahoma,verdana,helvetica;
        display: inline-block;
        padding: 8px 15px;
        min-width: 90px;
        text-align: center;
        cursor: pointer;
        text-decoration: none !important;
        background-color: #DAEBE1;
        border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
        border: 1px solid #aaa;
        -webkit-border-radius: 2px;
        border-radius: 1px;
        font-weight: bold;
        text-transform: uppercase;
        color: #444;
    }
    input[type=text], input[type=number] {
        width: 100%;
        padding: 12px 20px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        box-sizing: border-box;
    }
    input[type=submit] {
      width: 100%;
      background-color: #DAEBE1;
      color: black;
      padding: 14px 20px;
      margin: 8px 0;
      border: 1px solid #ccc;
      cursor: pointer;
      
    }
    input[type=submit]:hover {
      background-color: #DAEBE1;
    } 
    .custom_label {
        font-weight: bold;
    }
</style>
<form action="" method="POST">
    <strong>Modification produit  : <?php echo $prodChild->label; ?></strong><br>
    <hr>
    <table class="titlefield fieldrequired">
        <input type="hidden" value="<?php echo $prodChild->id; ?>" name="posted_id">
        <?php 
            if($resu_fab !== 'fab') {
        ?>
            <tr>
                <td colspan="3">
                    <label for="ref_tissus_couleur" class="custom_label">Réf tissus</label>
                    <input 
                        type="text"
                        name="ref_tissus_couleur" 
                        class="maxwidth200" 
                        maxlength="128" 
                        value="<?php echo ($ref_tissus_couleur?$ref_tissus_couleur:$prodChild->ref_tissus_couleur); ?>" 
                        id="ref_tissus_couleur" 
                        oninput="changeValueInputRefTissus('ref_tissus_couleur','copie_val_reftissus')">
                    <br>
                    <div id="message_success"></div>
                    <img src = "<?php echo DOL_URL_ROOT.'/cyberoffice/ajax-loading-gif-1.gif'; ?>" style="width: 18%;display:none;" id="load_update_color">
                    <button 
                        class="btn btn-info" 
                        type="button" 
                        id="copie_val_reftissus" 
                        style="display:none;" 
                        onclick="copyValuesOfRowRefTissus('<?php echo $parentId; ?>','<?php echo $idProduct ;?>')">
                        Appliquer la modification pour les déclinaisons de la même couleur
                    </button>
                    
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <label for="qtycomm" class="custom_label">Quantité Commandé</label>
                    <input 
                        type="number" 
                        id="qtycomm" 
                        name="quantite_commander" 
                        class="maxwidth200" 
                        maxlength="128" 
                        value="<?php echo ($quantite_commander?$quantite_commander:$prodChild->quantite_commander); ?>"
                        oninput="changeValueInput('message_success_qtycomm','qtycomm','copie_val_qtycomm','copie_val_qtycomm_color')"
                    >
                    <br>
                    <div id="message_success_qtycomm"></div>
                    <img src = "<?php echo DOL_URL_ROOT.'/cyberoffice/ajax-loading-gif-1.gif'; ?>" style="width: 18%;display:none;" id="load_update_qtycomm">
                    <button 
                        type="button" 
                        id="copie_val_qtycomm_color" 
                        style="display:none;" 
                        onclick="copyValuesOfInput('load_update_qtycomm','<?php echo $parentId; ?>','message_success_qtycomm','copie_val_qtycomm','qtycomm',true,'copie_val_qtycomm_color')">
                        Appliquer la modification pour déclinaison de la même couleur
                    </button>
                    <button 
                        type="button" 
                        id="copie_val_qtycomm" 
                        style="display:none;" 
                        onclick="copyValuesOfInput('load_update_qtycomm','<?php echo $parentId; ?>','message_success_qtycomm','copie_val_qtycomm','qtycomm',false,'copie_val_qtycomm_color')">
                        Appliquer la modification pour toutes les déclinaisons
                    </button>
                </td>
            </tr>
        <?php 
            }
        ?>
        <tr>
            <td colspan="3">
                <label for="qtyfab" class="custom_label">Quantité fabriqué</label>
                <input 
                    type="number" 
                    id="qtyfab" 
                    name="quantite_fabriquer" 
                    class="maxwidth200" 
                    maxlength="128" 
                    value="<?php echo ($quantite_fabriquer?$quantite_fabriquer:$prodChild->quantite_fabriquer); ?>"
                    oninput="changeValueInput('message_success_qtyfab','qtyfab','copie_val_qtyfab','copie_val_qtyfab_color')"
                >
                <br>
                <div id="message_success_qtyfab"></div>
                <img src = "<?php echo DOL_URL_ROOT.'/cyberoffice/ajax-loading-gif-1.gif'; ?>" style="width: 18%;display:none;" id="load_update_qtyfab">
                <button 
                    type="button" 
                    id="copie_val_qtyfab_color" 
                    style="display:none;" 
                    onclick="copyValuesOfInput('load_update_qtyfab','<?php echo $parentId; ?>','message_success_qtyfab','copie_val_qtyfab','qtyfab',true,'copie_val_qtyfab_color')">
                    Appliquer la modification pour déclinaison de la même couleur
                </button>
                <button 
                    type="button" 
                    id="copie_val_qtyfab" 
                    style="display:none;" 
                    onclick="copyValuesOfInput('load_update_qtyfab','<?php echo $parentId; ?>','message_success_qtyfab','copie_val_qtyfab','qtyfab',false,'copie_val_qtyfab_color')">
                    Appliquer la modification pour toutes les déclinaisons
                </button>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <label for="weight" class="custom_label">Poids</label>
                <input type="text" id="weight" name="weight_variant" class="maxwidth200" maxlength="128" value="<?php echo ($weight_variant ? $weight_variant: ($prodChild->weight_variant == 0.000 ? "": str_replace(".",",",$prodChild->weight_variant))); ?>">
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <label for="composition" class="custom_label">Composition</label>
                <input 
                    id="composition" 
                    type="text" 
                    name="composition" 
                    class="maxwidth200"  
                    value="<?php echo ($composition?$composition:$prodChild->composition); ?>"
                    oninput="changeValueInput('message_success_composition','composition','copie_val_composition','copie_val_composition_color')"
                >
                <br>
                <div id="message_success_composition"></div>
                <img src = "<?php echo DOL_URL_ROOT.'/cyberoffice/ajax-loading-gif-1.gif'; ?>" style="width: 18%;display:none;" id="load_update_composition">
                <button 
                    type="button" 
                    id="copie_val_composition_color" 
                    style="display:none;" 
                    onclick="copyValuesOfInput('load_update_qtyfab','<?php echo $parentId; ?>','message_success_composition','copie_val_composition','composition',true,'copie_val_composition_color')">
                    Appliquer la modification pour déclinaison de la même couleur
                </button>
                <button 
                    type="button" 
                    id="copie_val_composition" 
                    style="display:none;" 
                    onclick="copyValuesOfInput('load_update_composition','<?php echo $parentId; ?>','message_success_composition','copie_val_composition','composition',false,'copie_val_composition_color')">
                    Appliquer la modification pour toutes les déclinaisons
                </button>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <label for="price_yuan" class="custom_label">Prix yuan</label>
                <input 
                    type="text" 
                    name="price_yuan" 
                    class="maxwidth200" 
                    id="price_yuan"  
                    value="<?php echo ($price_yuan?$price_yuan:$prodChild->price_yuan); ?>" 
                    oninput="changeEuro('price_yuan','price_euro','taux_change');
                    changeValueInput('message_success_price_yuan','price_yuan','copie_val_price_yuan','copie_val_price_yuan_color')"
                >
                <br>
                <div id="message_success_price_yuan"></div>
                <img src = "<?php echo DOL_URL_ROOT.'/cyberoffice/ajax-loading-gif-1.gif'; ?>" style="width: 18%;display:none;" id="load_update_price_yuan">
                <button 
                    type="button" 
                    id="copie_val_price_yuan_color" 
                    style="display:none;" 
                    onclick="copyValuesOfInput('load_update_price_yuan','<?php echo $parentId; ?>','message_success_price_yuan','copie_val_price_yuan','price_yuan',true,'copie_val_price_yuan_color')">
                    Appliquer la modification pour déclinaison de la même couleur
                </button>
                <button 
                    type="button" 
                    id="copie_val_price_yuan" 
                    style="display:none;" 
                    onclick="copyValuesOfInput('load_update_price_yuan','<?php echo $parentId; ?>','message_success_price_yuan','copie_val_price_yuan','price_yuan',false,'copie_val_price_yuan_color')">
                    Appliquer la modification pour toutes les déclinaisons
                </button>
            </td>
        </tr>
        <tr>
            <td colspan="3" <?php  /*if($resu_fab == 'fab') { echo "style='display:none;'"; }*/ ?>>
                <label for="taux_change" class="custom_label">Taux</label>
                <input 
                    type="text" 
                    name="taux_euro_yuan" <?php  /*if($resu_fab == 'fab') { echo "readonly='readonly'"; }*/ ?>  
                    class="maxwidth200" 
                    id="taux_change" 
                    value="<?php echo $prodChild->taux_euro_yuan; ?>"
                    oninput="changeEuro('price_yuan','price_euro','taux_change');changeValueInput('message_success_taux_change','taux_change','copie_val_taux_change','copie_val_taux_change_color')" 
                >
                <br>
                <div id="message_success_taux_change"></div>
                <img src = "<?php echo DOL_URL_ROOT.'/cyberoffice/ajax-loading-gif-1.gif'; ?>" style="width: 18%;display:none;" id="load_update_taux_change">
                <button
                    type="button"
                    id="copie_val_taux_change_color"
                    style="display:none;"
                    onclick="copyValuesOfInput('load_update_taux_change','<?php echo $parentId; ?>','message_success_taux_change','copie_val_taux_change','taux_change',true,'copie_val_taux_change_color')">
                    Appliquer la modification pour déclinaison de la même couleur
                </button>
                <button
                    type="button"
                    id="copie_val_taux_change"
                    style="display:none;"
                    onclick="copyValuesOfInput('load_update_taux_change','<?php echo $parentId; ?>','message_success_taux_change','copie_val_taux_change','taux_change',false,'copie_val_taux_change_color')">
                    Appliquer la modification pour toutes les déclinaisons
                </button>
            </td>
        </tr>
        <tr>
            <td colspan="3" <?php  /*if($resu_fab == 'fab') { echo "style='display:none;'"; } */ ?>>
                <label for="price_euro" class="custom_label">Prix euros</label>
                <input 
                    type="text" 
                    name="price_euro" <?php  /*if($resu_fab == 'fab') { echo "readonly='readonly'"; }*/ ?>  
                    class="maxwidth200" 
                    id="price_euro" 
                    value="<?php  echo ($price_euro?$price_euro:$prodChild->price_euro); ?>"
                >
            </td>
        </tr>
        <tr>
            
            <td colspan="3">
                <input type="submit" value="Modifier">
            </td>
        </tr>
    </table>
</form> 

<script type="text/javascript" src="<?php  echo DOL_URL_ROOT."/includes/jquery/js/jquery.min.js" ?>" ></script>
<script type="text/javascript">
    
    $(document).ready(function(){
        
    });
    
    function changeEuro(yuan, euro, tauxchange){
        var resy = document.getElementById(yuan).value;
        var tauxchange = document.getElementById(tauxchange).value;
        if(resy){
            document.getElementById(euro).value =  (parseFloat(resy.replace(',','.'))/parseFloat(tauxchange.replace(',','.'))).toFixed(2);
        }else{
            document.getElementById(euro).value = 0;
        }
    }
    
    function changeValueInputRefTissus(inputComp,copyval){
        var resy = $("#"+inputComp).val();
        $("#message_success").hide();
        $("#message_success").css({
            "border":"0px solid",
            "width": "20%",
            "padding": "0%"
        });
        if(resy !== ""){
            $("#"+copyval).show();
        }else{
            $("#"+copyval).hide();
        }
    }
    
    function copyValuesOfRowRefTissus(idparent,idchild){
        $("#load_update_color").show();
        var refs = $("#ref_tissus_couleur").val();
        $.ajax("<?php echo DOL_URL_ROOT.'/product/ajax/updaterefcolor.php'; ?>", {
            type: "POST",
            data : {valCoul:"<?php echo $valColor; ?>", parentId:idparent, refValue:refs},
            success: function (data){
                $("#load_update_color").hide();
                if(data.success){
                    $("#copie_val_reftissus").hide();
                    $("#message_success").show();
                    $("#message_success").css({
                        "border":"1px solid",
                        "width": "95%",
                        "padding": "1%"
                    });
                    $("#message_success").html(data.success_detail);
                }else{
                    alert("Une erreur est survenu");
                }
            }
        });
    }
    
    function changeValueInput(messageSuccess,inputComp,copyval,copyvalcolor = ""){
        var resy = $("#"+inputComp).val();
        $("#"+messageSuccess).hide();
        $("#"+messageSuccess).css({
            "border":"0px solid",
            "width": "20%",
            "padding": "0%"
        });
        if(resy !== ""){
            $("#"+copyval).show();
            if(copyvalcolor !== ""){
                $("#"+copyvalcolor).show();
            }
        }else{
            $("#"+copyval).hide();
            if(copyvalcolor !== ""){
                $("#"+copyvalcolor).hide();
            }
        }
    }
    
    function copyValuesOfInput(loadUpdate,idparent,messageSuccess,buttonId,flag, isColorOnly = false, buttonColor = ""){
        $("#"+loadUpdate).show();
        var qtyComm = $("#qtycomm").val();
        var qtyfab = $("#qtyfab").val();
        var composition = $("#composition").val();
        var prixYuan = $("#price_yuan").val();
        var tauxChange = $("#taux_change").val();
        var prixEuro = $("#price_euro").val();
        var dataTosend = {};
        if(flag === "qtycomm"){
            dataTosend = {
                qtyComm: qtyComm,
                parentId:idparent
            };
            if(isColorOnly === true){
                dataTosend.isColor = 1;
                dataTosend.valCoul = "<?php echo $valColor; ?>";
            }else{
                dataTosend.isColor = 0;
            }
        }
        if(flag === "qtyfab"){
            dataTosend = {
                qtyfab:qtyfab,
                parentId:idparent
            };
            if(isColorOnly  === true){
                dataTosend.isColor = 1;
                dataTosend.valCoul = "<?php echo $valColor; ?>";
            }else{
                dataTosend.isColor = 0;
            }
        }
        if(flag === "composition"){
            dataTosend = {
                composition:composition,
                parentId:idparent
            };
            if(isColorOnly === true){
                dataTosend.isColor = 1;
                dataTosend.valCoul = "<?php echo $valColor; ?>";
            }else{
                dataTosend.isColor = 0;
            }
        }
        if(flag === "price_yuan" || flag === "taux_change"){
            dataTosend = {
                prixYuan:prixYuan,
                tauxChange:tauxChange,
                prixEuro:prixEuro,
                parentId:idparent
            };
            if(isColorOnly === true){
                dataTosend.isColor = 1;
                dataTosend.valCoul = "<?php echo $valColor; ?>";
            }else{
                dataTosend.isColor = 0;
            }
        }
        
        $.ajax("<?php echo DOL_URL_ROOT.'/product/ajax/updateOtherInfo.php'; ?>", {
            type: "POST",
            data : dataTosend,
            success: function (data){
                $("#"+loadUpdate).hide();
                if(data.success){
                    $("#"+buttonId).hide();
                    if(buttonColor){
                        $("#"+buttonColor).hide();
                    }
                    $("#"+messageSuccess).show();
                    $("#"+messageSuccess).css({
                        "border":"1px solid",
                        "width": "95%",
                        "padding": "1%"
                    });
                    $("#"+messageSuccess).html(data.success_detail);
                }else{
                    alert("Une erreur est survenu");
                }
            }
        });
    }
</script>
<?php
