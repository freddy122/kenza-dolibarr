<?php


require '../../../main.inc.php';

$hosts = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME']."/";
$imgdata = $hosts.DOL_URL_ROOT."/barcodegen/generated/html/imageDisplayed.php?codebare=".GETPOST("codebare");
print '<img src="'.$imgdata.'">';
