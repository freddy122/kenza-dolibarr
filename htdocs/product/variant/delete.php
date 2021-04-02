<?php

require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
global $conf;

$idProduct          = GETPOST("productid");
$prodChild = new Product($db);
$prodChild->fetch($idProduct);

if($_POST['yes_delete']){
    $sqlDeleteProdPrice = "DELETE from ".MAIN_DB_PREFIX."product_price where fk_product = ".intval($idProduct);
    $db->query($sqlDeleteProdPrice);
    
    $sqlDeleteProdActionComm = "DELETE from ".MAIN_DB_PREFIX."actioncomm where fk_element = ".intval($idProduct);
    $db->query($sqlDeleteProdActionComm);
    
    $sqlDeleteProdExtraFields = "DELETE from ".MAIN_DB_PREFIX."product_extrafields where fk_objecgt = ".intval($idProduct);
    $db->query($sqlDeleteProdExtraFields);
    
    $sqlCombinations = "SELECT rowid from ".MAIN_DB_PREFIX."product_attribute_combination where fk_product_child = ".intval($idProduct);
    $resCombi = $db->getRows($sqlCombinations);
    
    if(!empty($resCombi)){
        $sqlDeleteCombinationsValue = "DELETE from ".MAIN_DB_PREFIX."product_attribute_combination2val where fk_prod_combination = ".intval($resCombi[0]->rowid);
        $db->query($sqlDeleteCombinationsValue);
    }
    
    $sqlDeleteCombinations = "DELETE from ".MAIN_DB_PREFIX."product_attribute_combination where fk_product_child = ".intval($idProduct);
    $db->query($sqlDeleteCombinations);
    
    $sqlDeleteProduct = "DELETE from ".MAIN_DB_PREFIX."product where rowid = ".intval($idProduct);
    $db->query($sqlDeleteProduct);
    
    print '<script type="text/javascript">
            window.parent.location.reload()
        </script>';
}

if($_POST['no_delete']){
    print '<script type="text/javascript">
            window.parent.location.reload()
        </script>';
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
    .button_delete{
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
        background-color: #ec4646;
        border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
        border: 1px solid #aaa;
        -webkit-border-radius: 2px;
        border-radius: 1px;
        font-weight: bold;
        text-transform: uppercase;
        color: #fff;
    }
</style>
<form action="" method="POST">
    <strong>Voulez vous vraiment supprimer la déclinaison : <?php echo $prodChild->label; ?></strong><br><br>
    <table class="titlefield fieldrequired">
        <tr>
            <td><input type="submit" name="yes_delete" value="OUI" class="button_delete"/></td>
            <td><input type="submit" name="no_delete" value="NON" class="button"/></td>
        </tr>
    </table>
</form>
<?php

