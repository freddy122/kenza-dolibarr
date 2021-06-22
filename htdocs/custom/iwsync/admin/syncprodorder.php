<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2021 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    iwsync/admin/setup.php
 * \ingroup iwsync
 * \brief   IWSYNC setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/iwsync.lib.php';

require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/canvas.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/genericobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/product/modules_product.class.php';
require DOL_DOCUMENT_ROOT.'/variants/class/ProductAttribute.class.php';
require DOL_DOCUMENT_ROOT.'/variants/class/ProductAttributeValue.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';
require_once 'class/sincprodfab.class.php';
require_once '../lib/PSWebServiceLibrary.php';

// Translations
$langs->loadLangs(array("admin", "iwsync@iwsync"));

// Access control
if (!$user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

$value = GETPOST('value', 'alpha');

$arrayofparameters = array(
	'URL_PRESTA'=>array('css'=>'minwidth500', 'enabled'=>1),
	'CLE_API_PRESTA'=>array('css'=>'minwidth500', 'enabled'=>1),
);

$error = 0;
$setupnotempty = 0;


/*
 * Actions
 */

if ((float) DOL_VERSION >= 6)
{
	include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';
}

if ($action == 'updateMask')
{
	$maskconstorder = GETPOST('maskconstorder', 'alpha');
	$maskorder = GETPOST('maskorder', 'alpha');

	if ($maskconstorder) $res = dolibarr_set_const($db, $maskconstorder, $maskorder, 'chaine', 0, '', $conf->entity);

	if (!$res > 0) $error++;

	if (!$error)
	{
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}



/*
 * View
 */

$form = new Form($db);

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

$page_name = "IWSYNCSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_iwsync@iwsync');

// Configuration header
$head = iwsyncAdminPrepareHead();
dol_fiche_head($head, 'synciwprodorder', '', -1, "iwsync@iwsync");

$form_html = new Form($db);
$prod_fab_class = new SincProdFab($db);
$prod_object =  new Product($db);
$prod_combination_object =  new ProductCombination($db);

print '<form method="POST">';
print '<table class="border tableforfield" width="100%">';
print '<tr>'
    . '<td style="width:20%"> Sélectionner identifiant commande </td>'
    . '<td class="titlefield"> '.$form_html->multiselectarray("iw_prod_fab_name", $prod_fab_class->getParentProductFab(),array(),0,0,'',0,"60%").' </td>'
    . '</tr>';
print '<tr>'
    . '<td style="width:20%"> Sélectionner les produits à exporter vers prestashop </td>'
    . '<td class="titlefield"> '.$form_html->multiselectarray("iw_prod_fab_names", $prod_fab_class->getParentProductFab(),array(),0,0,'',0,"60%").' </td>'
    . '</tr>';
print '<tr>'
    . '<td colspan="2"> '
    . '<input type="submit" class="button" name="export_btn" value="Exporter"> '
    //. '<input type="submit" class="button" name="export_all_btn" value="Exporter tous"> '
    . '</td>'
    . '</tr>';
print '<table>';
print '</form>';


// Page end
dol_fiche_end();

llxFooter();
$db->close();
