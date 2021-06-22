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

$array_string = "Array
(
    [0] => Array
        (
            [id_product] => 844-0000
            [ean13] => 5603561200000
            [upc] => 5603561200000
            [isbn] => 5603561200000
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => JUPE ORIENTAL OR
            [description_short] => JUPE ORIENTAL OR
            [name] => JUPE ORIENTAL OR
            [real_name] => JUPE ORIENTAL OR
            [tax_rate] => 8.5
            [reference] => 560356120000
            [active] => 1
            [quantity] => 12
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
                            [name] => cover844-5879
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/844-jupe-oriental-or--4854214236514.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [1] => Array
        (
            [id_product] => 844-8230
            [ean13] => 5603561200001
            [upc] => 5603561200001
            [isbn] => 5603561200001
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => Taille : 2 ans, Couleur : Oriental Rouge 
            [description_short] => Taille : 2 ans, Couleur : Oriental Rouge 
            [name] => JUPE ORIENTAL OR 2 ans Oriental Rouge 
            [real_name] => JUPE ORIENTAL OR
            [tax_rate] => 8.5
            [reference] => 560356120001
            [active] => 1
            [quantity] => 1
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
                            [name] => cover844-5879
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/844-jupe-oriental-or--4854214236514.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 10.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [2] => Array
        (
            [id_product] => 844-8231
            [ean13] => 5603561200002
            [upc] => 5603561200002
            [isbn] => 5603561200002
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => Taille : 4 ans, Couleur : Oriental Rouge 
            [description_short] => Taille : 4 ans, Couleur : Oriental Rouge 
            [name] => JUPE ORIENTAL OR 4 ans Oriental Rouge 
            [real_name] => JUPE ORIENTAL OR
            [tax_rate] => 8.5
            [reference] => 560356120002
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
                            [name] => cover844-5879
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/844-jupe-oriental-or--4854214236514.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [3] => Array
        (
            [id_product] => 844-8232
            [ean13] => 5603561200003
            [upc] => 5603561200003
            [isbn] => 5603561200003
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => Taille : 6 ans, Couleur : Oriental Rouge 
            [description_short] => Taille : 6 ans, Couleur : Oriental Rouge 
            [name] => JUPE ORIENTAL OR 6 ans Oriental Rouge 
            [real_name] => JUPE ORIENTAL OR
            [tax_rate] => 8.5
            [reference] => 560356120003
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
                            [name] => cover844-5879
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/844-jupe-oriental-or--4854214236514.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [4] => Array
        (
            [id_product] => 844-8233
            [ean13] => 5603561200004
            [upc] => 5603561200004
            [isbn] => 5603561200004
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => Taille : 8 ans, Couleur : Oriental Rouge 
            [description_short] => Taille : 8 ans, Couleur : Oriental Rouge 
            [name] => JUPE ORIENTAL OR 8 ans Oriental Rouge 
            [real_name] => JUPE ORIENTAL OR
            [tax_rate] => 8.5
            [reference] => 560356120004
            [active] => 1
            [quantity] => 1
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
                            [name] => cover844-5879
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/844-jupe-oriental-or--4854214236514.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [5] => Array
        (
            [id_product] => 844-8234
            [ean13] => 5603561200005
            [upc] => 5603561200005
            [isbn] => 5603561200005
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => Taille : 10 ans, Couleur : Oriental Rouge 
            [description_short] => Taille : 10 ans, Couleur : Oriental Rouge 
            [name] => JUPE ORIENTAL OR 10 ans Oriental Rouge 
            [real_name] => JUPE ORIENTAL OR
            [tax_rate] => 8.5
            [reference] => 560356120005
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
                            [name] => cover844-5879
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/844-jupe-oriental-or--4854214236514.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [6] => Array
        (
            [id_product] => 844-8235
            [ean13] => 5603561200006
            [upc] => 5603561200006
            [isbn] => 5603561200006
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => Taille : 12 ans, Couleur : Oriental Rouge 
            [description_short] => Taille : 12 ans, Couleur : Oriental Rouge 
            [name] => JUPE ORIENTAL OR 12 ans Oriental Rouge 
            [real_name] => JUPE ORIENTAL OR
            [tax_rate] => 8.5
            [reference] => 560356120006
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
                            [name] => cover844-5879
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/844-jupe-oriental-or--4854214236514.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [7] => Array
        (
            [id_product] => 844-8236
            [ean13] => 5603561200007
            [upc] => 5603561200007
            [isbn] => 5603561200007
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => Taille : 14 ans, Couleur : Oriental Rouge 
            [description_short] => Taille : 14 ans, Couleur : Oriental Rouge 
            [name] => JUPE ORIENTAL OR 14 ans Oriental Rouge 
            [real_name] => JUPE ORIENTAL OR
            [tax_rate] => 8.5
            [reference] => 560356120007
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5879/jupe-oriental-or-.jpg
                            [name] => cover844-5879
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/844-jupe-oriental-or--4854214236514.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [8] => Array
        (
            [id_product] => 845-0000
            [ean13] => 5603561300000
            [upc] => 5603561300000
            [isbn] => 5603561300000
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => JUPE ORIENTAL OR GIRL
            [description_short] => JUPE ORIENTAL OR GIRL
            [name] => JUPE ORIENTAL OR GIRL
            [real_name] => JUPE ORIENTAL OR GIRL
            [tax_rate] => 8.5
            [reference] => 560356130000
            [active] => 1
            [quantity] => 13
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
                            [name] => cover845-5882
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/845-jupe-oriental-or-girl--2134521342425.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [9] => Array
        (
            [id_product] => 845-8237
            [ean13] => 5603561300001
            [upc] => 5603561300001
            [isbn] => 5603561300001
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => JUPE ORIENTAL OR GIRL
            [description_short] => Taille : 2 ans, Couleur : Oriental Bleu 
            [name] => JUPE ORIENTAL OR GIRL 2 ans Oriental Bleu 
            [real_name] => JUPE ORIENTAL OR GIRL
            [tax_rate] => 8.5
            [reference] => 560356130001
            [active] => 1
            [quantity] => 1
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
                            [name] => cover845-5882
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/845-jupe-oriental-or-girl--2134521342425.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [10] => Array
        (
            [id_product] => 845-8238
            [ean13] => 5603561300002
            [upc] => 5603561300002
            [isbn] => 5603561300002
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => JUPE ORIENTAL OR GIRL
            [description_short] => Taille : 4 ans, Couleur : Oriental Bleu 
            [name] => JUPE ORIENTAL OR GIRL 4 ans Oriental Bleu 
            [real_name] => JUPE ORIENTAL OR GIRL
            [tax_rate] => 8.5
            [reference] => 560356130002
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
                            [name] => cover845-5882
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/845-jupe-oriental-or-girl--2134521342425.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [11] => Array
        (
            [id_product] => 845-8239
            [ean13] => 5603561300003
            [upc] => 5603561300003
            [isbn] => 5603561300003
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => JUPE ORIENTAL OR GIRL
            [description_short] => Taille : 6 ans, Couleur : Oriental Bleu 
            [name] => JUPE ORIENTAL OR GIRL 6 ans Oriental Bleu 
            [real_name] => JUPE ORIENTAL OR GIRL
            [tax_rate] => 8.5
            [reference] => 560356130003
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
                            [name] => cover845-5882
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/845-jupe-oriental-or-girl--2134521342425.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [12] => Array
        (
            [id_product] => 845-8240
            [ean13] => 5603561300004
            [upc] => 5603561300004
            [isbn] => 5603561300004
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => JUPE ORIENTAL OR GIRL
            [description_short] => Taille : 8 ans, Couleur : Oriental Bleu 
            [name] => JUPE ORIENTAL OR GIRL 8 ans Oriental Bleu 
            [real_name] => JUPE ORIENTAL OR GIRL
            [tax_rate] => 8.5
            [reference] => 560356130004
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
                            [name] => cover845-5882
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/845-jupe-oriental-or-girl--2134521342425.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [13] => Array
        (
            [id_product] => 845-8241
            [ean13] => 5603561300005
            [upc] => 5603561300005
            [isbn] => 5603561300005
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => JUPE ORIENTAL OR GIRL
            [description_short] => Taille : 10 ans, Couleur : Oriental Bleu 
            [name] => JUPE ORIENTAL OR GIRL 10 ans Oriental Bleu 
            [real_name] => JUPE ORIENTAL OR GIRL
            [tax_rate] => 8.5
            [reference] => 560356130005
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
                            [name] => cover845-5882
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/845-jupe-oriental-or-girl--2134521342425.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [14] => Array
        (
            [id_product] => 845-8242
            [ean13] => 5603561300006
            [upc] => 5603561300006
            [isbn] => 5603561300006
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => JUPE ORIENTAL OR GIRL
            [description_short] => Taille : 12 ans, Couleur : Oriental Bleu 
            [name] => JUPE ORIENTAL OR GIRL 12 ans Oriental Bleu 
            [real_name] => JUPE ORIENTAL OR GIRL
            [tax_rate] => 8.5
            [reference] => 560356130006
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
                            [name] => cover845-5882
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/845-jupe-oriental-or-girl--2134521342425.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

    [15] => Array
        (
            [id_product] => 845-8243
            [ean13] => 5603561300007
            [upc] => 5603561300007
            [isbn] => 5603561300007
            [price] => 15.576037
            [height] => 0.000000
            [width] => 0.000000
            [depth] => 0.000000
            [weight] => 0.298000
            [description] => JUPE ORIENTAL OR GIRL
            [description_short] => Taille : 14 ans, Couleur : Oriental Bleu 
            [name] => JUPE ORIENTAL OR GIRL 14 ans Oriental Bleu 
            [real_name] => JUPE ORIENTAL OR GIRL
            [tax_rate] => 8.5
            [reference] => 560356130007
            [active] => 1
            [quantity] => 2
            [warehouse] => 1
            [image] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
            [images] => Array
                (
                    [0] => Array
                        (
                            [url] => http://127.0.0.1/kenzapresta/5882/jupe-oriental-or-girl-.jpg
                            [name] => cover845-5882
                        )

                )

            [category] => 172-64-56-11-50-51-2
            [product_url] => http://127.0.0.1/kenzapresta/accueil/845-jupe-oriental-or-girl--2134521342425.html
            [manufacturer] => Kenza Mode Métisse
            [id_manufacturer] => 3
            [eco_tax] => 0.000000
            [match] => {ref}
            [wholesale_price] => 0.000000
            [unit_price_impact] => 0.000000
            [features] => Array
                (
                )

            [WEIGHT_UNIT] => kg
            [VOLUME_UNIT] => cl
            [DIMENSION_UNIT] => cm
            [LOCALELANG] => fr
            [specificprice] => Array
                (
                )

        )

)";

function do_reverse(&$output)
{
    $expecting = 0; // 0=nothing in particular, 1=array open paren '(', 2=array element or close paren ')'
    $lines = explode("\n", $output);
    $result = null;
    $topArray = null;
    $arrayStack = array();
    $matches = null;
    while (!empty($lines) && $result === null)
    {
        $line = array_shift($lines);
        $trim = trim($line);
        if ($trim == 'Array')
        {
            if ($expecting == 0)
            {
                $topArray = array();
                $expecting = 1;
            }
            else
            {
                trigger_error("Unknown array.");
            }
        }
        else if ($expecting == 1 && $trim == '(')
        {
            $expecting = 2;
        }
        else if ($expecting == 2 && preg_match('/^\[(.+?)\] \=\> (.+)$/', $trim, $matches)) // array element
        {
            list ($fullMatch, $key, $element) = $matches;
            if (trim($element) == 'Array')
            {
                $topArray[$key] = array();
                $newTopArray =& $topArray[$key];
                $arrayStack[] =& $topArray;
                $topArray =& $newTopArray;
                $expecting = 1;
            }
            else
            {
                $topArray[$key] = $element;
            }
        }
        else if ($expecting == 2 && $trim == ')') // end current array
        {
            if (empty($arrayStack))
            {
                $result = $topArray;
            }
            else // pop into parent array
            {
                // safe array pop
                $keys = array_keys($arrayStack);
                $lastKey = array_pop($keys);
                $temp =& $arrayStack[$lastKey];
                unset($arrayStack[$lastKey]);
                $topArray =& $temp;
            }
        }
        // Added this to allow for multi line strings.
    else if (!empty($trim) && $expecting == 2)
    {
        // Expecting close parent or element, but got just a string
        $topArray[$key] .= "\n".$line;
    }
        else if (!empty($trim))
        {
            $result = $line;
        }
    }

    $output = implode("\n", $lines);
    return $result;
}
$params = do_reverse(($array_string));


$authentication=$_POST['authentication'];
//$params= $_POST['params']; 
$now=dol_now();
dol_syslog("cyberoffice::Function: server_product.inc login=".$authentication['login']);

if ($authentication['entity']) 
    $conf->entity=$authentication['entity'];

    // Init and check authentication
$objectresp=array();
$errorcode='';$errorlabel='';
$error=0;
$errortot=0;
/*$fuser=check_authentication($authentication,$error,$errorcode,$errorlabel);
if ($error)
{
    $objectresp = array('result'=>array('result_code' => 'ko', 'result_label' => 'ko'),'webservice'=>'login');
    $error++;
    return $objectresp;
}*/

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
	$newobject->price_base_type 	= 'HT';
	$newobject->price				= $product['price'];
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
	$Ruser=$user->fetch(1);
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
	$newobject->price_base_type 	= 'HT';
	$newobject->price				= $product['price'];
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
                    $newobject->updatePrice($product['price'], 'HT', $user, $product['tax_rate']);
            } else {
                $pricelevel = (int)$conf->global->{"MYCYBEROFFICE_pricelevel".$indice_name} ;
                if ($pricelevel==0) $pricelevel = 1;
                if (round($product_price->multiprices_min[$pricelevel],3) != round($product['price'],3) || round($product_price->tva_tx,3) != round($product['tax_rate'],3))
                    $newobject->updatePrice($product['price'], 'HT', $user, $product['tax_rate'], $product_price->multiprices_min[$pricelevel],$pricelevel);
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
                if(strpos($vComb, "0000") > 0) {
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