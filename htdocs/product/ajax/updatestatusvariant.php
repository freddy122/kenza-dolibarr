<?php
require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
global $conf;
global $db;

$valstatus = GETPOST("selectedVal");
$sql_update_stat = "UPDATE ".MAIN_DB_PREFIX."const SET value = '".intval($valstatus)."' WHERE  name = 'SHOW_OR_NOT_DECLINAISON'";
$db->query($sql_update_stat);