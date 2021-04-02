<?php
require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
global $conf;

$idProduct          = GETPOST("productid");
$quantite_commander = GETPOST("quantite_commander");
$quantite_fabriquer = GETPOST("quantite_fabriquer");
$weight_variant     = GETPOST("weight_variant");
$composition        = GETPOST("composition");
$price_yuan         = GETPOST("price_yuan");
$price_euro         = GETPOST("price_euro");
$taux_euro_yuan     = GETPOST("taux_euro_yuan");
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
    if($resu_fab !== 'fab' && !empty($quantite_commander)) {
        $sqlUpdate       .= " quantite_commander = ".$quantite_commander.", " ;
    }else{
        if(!empty($prodChild->quantite_commander)){
            $sqlUpdate       .= " quantite_commander = ".$prodChild->quantite_commander.", " ;
        }else{
            $sqlUpdate       .= " quantite_commander = 0, " ;
        }
    }
    
    if(!empty($quantite_fabriquer)){
        $sqlUpdate       .= " quantite_fabriquer = ".$quantite_fabriquer.", " ; 
        
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
        $sqlUpdate       .= " price_yuan = ".floatval(str_replace(',','.',$price_yuan)).", " ; 
    }else{
        if(!empty($prodChild->price_yuan)){
            $sqlUpdate       .= " price_yuan = ".$prodChild->price_yuan.", " ;
        }else{
            $sqlUpdate       .= " price_yuan = 0, " ;
        }
    }
    
    if($resu_fab !== 'fab' && !empty($price_euro)) {
        $sqlUpdate       .= " price_euro = ".floatval(str_replace(',','.',$price_euro)).", " ;
        $sqlUpdate       .= " price = ".floatval(str_replace(',','.',$price_euro)).", " ;
        $sqlUpdate       .= " price_ttc = ".floatval(str_replace(',','.',$price_euro)).", " ;
    }else{
        if(!empty($prodChild->price_euro)){
            $sqlUpdate       .= " price_euro = ".$prodChild->price_euro.", " ;
            $sqlUpdate       .= " price = ".$prodChild->price_euro.", " ;
            $sqlUpdate       .= " price_ttc = ".$prodChild->price_euro.", " ;
        }else{
            $sqlUpdate       .= " price_euro = 0, " ;
            $sqlUpdate       .= " price = 0, " ;
            $sqlUpdate       .= " price_ttc = 0, " ;
        }
    }
    
    if(!empty($w_variant)){
        $sqlUpdate       .= " weight_variant = ".floatval(str_replace(',','.',$w_variant))." " ; 
    }else{
        if(!empty($prodChild->weight_variant)){
            $sqlUpdate       .= " weight_variant = ".$prodChild->weight_variant." " ;
        }else{
            $sqlUpdate       .= " weight_variant = 0 " ;
        }
    }
    
    $sqlUpdate       .= " where rowid = ".$_POST['posted_id'];
    
    $db->query($sqlUpdate);
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
</style>
<form action="" method="POST">
    <strong>Modification produit  : <?php echo $prodChild->label; ?></strong><br><br>
    <table class="titlefield fieldrequired">
        <input type="hidden" value="<?php echo $prodChild->id; ?>" name="posted_id">
        <?php 
            if($resu_fab !== 'fab') {
        ?>
        <tr>
            <td class="titlefield fieldrequired">Quantité Commandé</td>
            <td colspan="3">
                <input name="quantite_commander" class="maxwidth200" maxlength="128" value="<?php echo ($quantite_commander?$quantite_commander:$prodChild->quantite_commander); ?>">
            </td>
        </tr>
        <?php 
            }
        ?>
        
        <tr>
            <td class="titlefield fieldrequired">Quantité fabriqué</td>
            <td colspan="3"><input name="quantite_fabriquer" class="maxwidth200" maxlength="128" value="<?php echo ($quantite_fabriquer?$quantite_fabriquer:$prodChild->quantite_fabriquer); ?>">
            </td>
        </tr>
        <tr>
            <td class="titlefield fieldrequired">Poids</td>
            <td colspan="3"><input name="weight_variant" class="maxwidth200" maxlength="128" value="<?php echo ($weight_variant?$weight_variant:$prodChild->weight_variant); ?>">
            </td>
        </tr>
        <tr>
            <td class="titlefield fieldrequired">Composition</td>
            <td colspan="3"><input name="composition" class="maxwidth200"  value="<?php echo ($composition?$composition:$prodChild->composition); ?>">
            </td>
        </tr>
        <tr>
            <td class="titlefield fieldrequired">Prix yuan</td>
            <td colspan="3"><input name="price_yuan" class="maxwidth200" id="price_yuan"  maxlength="128" value="<?php echo ($price_yuan?$price_yuan:$prodChild->price_yuan); ?>" oninput="changeEuro('price_yuan','price_euro','taux_change');">
            </td>
        </tr>
        
        <tr>
            <td class="titlefield fieldrequired" <?php  if($resu_fab == 'fab') { echo "style='display:none;'"; } ?>>Taux</td>
            <td colspan="3" <?php  if($resu_fab == 'fab') { echo "style='display:none;'"; } ?>><input name="taux_euro_yuan" <?php  if($resu_fab == 'fab') { echo "readonly='readonly'"; } ?>  class="maxwidth200" id="taux_change" maxlength="128" oninput="changeEuro('price_yuan','price_euro','taux_change');" value="<?php echo $prodChild->taux_euro_yuan; ?>">
            </td>
        </tr>
        <tr>
            <td class="titlefield fieldrequired" <?php  if($resu_fab == 'fab') { echo "style='display:none;'"; } ?>>Prix euros</td>
            <td colspan="3" <?php  if($resu_fab == 'fab') { echo "style='display:none;'"; } ?>><input name="price_euro" <?php  if($resu_fab == 'fab') { echo "readonly='readonly'"; } ?>  class="maxwidth200" id="price_euro" maxlength="128" value="<?php  echo ($price_euro?$price_euro:$prodChild->price_euro); ?>">
            </td>
        </tr>
         
        <tr>
            <td class="titlefield fieldrequired"></td>
            <td colspan="3"><input type="submit" value="Modifier" class="button">
            </td>
        </tr>
    </table>
</form> 

<script>
    function changeEuro(yuan, euro, tauxchange){
        var resy = document.getElementById(yuan).value;
        var tauxchange = document.getElementById(tauxchange).value;
        if(resy){
            document.getElementById(euro).value =  (parseFloat(resy.replace(',','.'))/parseFloat(tauxchange.replace(',','.'))).toFixed(2);
        }else{
            document.getElementById(euro).value = 0;
        }
    }
</script>
<?php
