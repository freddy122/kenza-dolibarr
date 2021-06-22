<?php

$arrposted = 
Array
(
    "token" => "$2y$10x28lqiVzcTkY1tVm7Q2/vOW61nAUNamehzc/Awh55jk5yHM.sXPrS",
    "action" => "add",
    "type" => 0,
    "code_auto" => 1,
    "barcode_auto" => 1,
    "backtopage" => "",
    "dataPopupNewProduct" => "",
    "commandeIdDraft" => "",
    "pageCommande" => "",
    "ref" => "560356130009",
    "label" => "sef",
    "fk_barcode_type" => 2,
    "barcode" => "5603561300098",
    "desc" => "",
    "finished" => 1,
    "weight" => "",
    "weight_units" => 0,
    "note_private" => "",
    "price" => 5,
    "price_base_type" => "TTC",
    "choix_categ_variant" => 1,
    "choix_couleur" => 4,
    "choix_taille" => 8,
    "valCouleurs" => Array
        (
            "0" => 2,
            "1" => 2,
            "2" => 3,
            "3" => 3
        ),

    "valTailles" => Array
        (
            "0" => 5,
            "1" => 6,
            "2" => 7,
            "3" => 8
        ),

    "qtycomm" => Array
        (
            "0" => 10,
            "1" => 8,
            "2" => 2,
            "3" => 6
        ),

    "qtyfabriq" => Array
        (
            "0" => 23,
            "1" => 12,
            "2" => 42,
            "3" => 45
        ),

    "poidsfabriq" => Array
        (
            "0" => "",
            "1" => "",
            "2" => "",
            "3" => ""
        ),

    "compfabriq" => Array
        (
            "0" => "",
            "1" => "",
            "2" => "",
            "3" => ""
        ),

    "priceYuan" => Array
        (
            "0" => 41,
            "1" => 23,
            "2" => 85,
            "3" => 63
        ),

    "tauxChange" => Array
        (
            "0" => 0.13,
            "1" => 0.13,
            "2" => 0.13,
            "3" => 0.13
        ),

    "priceEuro" => Array
        (
            "0" => "",
            "1" => "",
            "2" => "",
            "3" => ""
        ),

    "totalYuan" => Array
        (
            "0" => "",
            "1" => "",
            "2" => "",
            "3" => ""
        ),

    "totalEuro" => Array
        (
            "0" => "",
            "1" => "",
            "2" => "",
            "3" => ""
        )
);

echo "<pre>";

print_r($arrposted);
print_r($arrposted['valCouleurs']);
print_r($arrposted['valTailles']);
$arrCouleur = [];
