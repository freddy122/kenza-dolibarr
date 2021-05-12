<?php
require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
global $conf;

$parentId = GETPOST("parentId");
$prodCombinations = new ProductCombination($db);
$resProdChild = $prodCombinations->fetchAllByFkProductParent($parentId);

//echo "<pre>";
$arrTaille = [];
$ccol = 1;
$arrProdChild = [];
foreach($resProdChild as $chilP){
    $arrProdChild[] = $chilP->fk_product_child;
}

$getTaille = $prodCombinations->getProductTailleWithDetail($parentId,$arrProdChild);
if($_POST['parentId']){
    foreach($_POST['val_prod_taille'] as $kl => $vl){
        if(!empty($_POST['val_poids_taille'][$kl])){
            //print_r($vl."----".$_POST['val_poids_taille'][$kl]."<br>");
            $sqlUpdateProduct = "UPDATE ".MAIN_DB_PREFIX."product set "
                    . " weight_variant = ".floatval(str_replace(",",".",$_POST['val_poids_taille'][$kl])).", "
                    . " weight = ".floatval(str_replace(",",".",$_POST['val_poids_taille'][$kl]))." where rowid in (".$vl.") ";
            $db->query($sqlUpdateProduct);
        }
    }
    echo ' <script type="text/javascript">
        window.parent.location.reload()
    </script>';
}
?>
<h2>Modification poids</h2>
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
<form method="POST">
    <input type="hidden" value="<?php echo $parentId; ?>" name="parentId">
    <table>
        <tr> 
            <td style="text-align: center;">Taille</td>
            <td style="text-align: center;">Poids</td>   
        </tr>
        <?php 
            foreach($getTaille as $kt => $vt){
        ?>
        <input type="hidden" value="<?php echo implode(',',$vt); ?>" name="val_prod_taille[<?php echo $kt; ?>]">
        <tr> 
            <td><input type="text" value = "<?php echo $kt;?>" disabled /> </td>
            <?php 
                $sqlRt = "SELECT distinct weight, weight_variant from ".MAIN_DB_PREFIX."product where rowid in (".implode(',',$vt).")";
                $resus = $db->getRows($sqlRt);
            ?>
            <td><input type="text" value="<?php echo (isset($_POST['val_poids_taille'][$kt])?$_POST['val_poids_taille'][$kt]:((!empty($resus[0]->weight_variant))?$resus[0]->weight_variant:"")); ?>" name="val_poids_taille[<?php echo $kt; ?>]"></td>   
        </tr>
        <?php 
            }
        ?>
    </table>
    <input type="submit" value="Enregistrer">
</form>
<?php 
