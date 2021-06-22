<?php
/**
 * Edited by Fréderic id?web
 */

if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1'); // Disables token renewal
if (!defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (!defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (!defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.product.class.php';

$idCommande = GETPOST('idCommandeFournisseur');
$sqlGetCommandeFournisseur = "SELECT  cf.rowid, cf.ref from llx_commande_fournisseur as cf where cf.rowid in (". implode(',', $idCommande).")";
$resfournsql    = $db->getRows($sqlGetCommandeFournisseur);
echo (json_encode($resfournsql));