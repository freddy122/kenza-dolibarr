<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Marc Barilley / Ocebo <marc@ocebo.com>
 * Copyright (C) 2005-2012 Regis Houssin         <regis.houssin@inodbox.com>
 * Copyright (C) 2005      Simon TOSSER          <simon@kornog-computing.com>
 * Copyright (C) 2013      Florian Henry          <florian.henry@open-concept.pro>
 * Copyright (C) 2013      Cédric Salvador       <csalvador@gpcsolutions.fr>
 * Copyright (C) 2017      Ferran Marcet       	 <fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/product/document.php
 *       \ingroup    product
 *       \brief      Page des documents joints sur les produits
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
if (!empty($conf->global->PRODUIT_PDF_MERGE_PROPAL))
	require_once DOL_DOCUMENT_ROOT.'/product/class/propalmergepdfproduct.class.php';

// Load translation files required by the page
$langs->loadLangs(array('other', 'products'));

$id     = GETPOST('id', 'int');
$ref    = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$status_product = GETPOST('status_product');
$sql_user_group = "select fk_user,fk_usergroup from ".MAIN_DB_PREFIX."usergroup_user where fk_user = ".$user->id."";
$resuUser = $db->query($sql_user_group);
$reug = $db->fetch_object($resuUser);
if($reug->fk_usergroup){
    $sql_group = "select code from ".MAIN_DB_PREFIX."usergroup where rowid = ".$reug->fk_usergroup;
    $resuug = $db->query($sql_group);
    $resug = $db->fetch_object($resuug);
    if($resug->code == "fab" && !$status_product) {
        header("Location:".DOL_URL_ROOT."/product/document.php?id=".$id."&action=edit&status_product=produitfab");
        exit;
    }
}
// Security check
$fieldvalue = (!empty($id) ? $id : (!empty($ref) ? $ref : ''));
$fieldtype = (!empty($ref) ? 'ref' : 'rowid');
if ($user->socid) $socid = $user->socid;
$result = restrictedArea($user, 'produit|service', $fieldvalue, 'product&product', '', '', $fieldtype);

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('productdocuments'));

// Get parameters
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page == -1) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortorder) $sortorder = "DESC";
if (!$sortfield) $sortfield = "date";


$object = new Product($db);
if ($id > 0 || !empty($ref))
{
    $result = $object->fetch($id, $ref);

    if (!empty($conf->product->enabled)) $upload_dir = $conf->product->multidir_output[$object->entity].'/'.get_exdir(0, 0, 0, 0, $object, 'product').dol_sanitizeFileName($object->ref);
    elseif (!empty($conf->service->enabled)) $upload_dir = $conf->service->multidir_output[$object->entity].'/'.get_exdir(0, 0, 0, 0, $object, 'product').dol_sanitizeFileName($object->ref);

	if (!empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))    // For backward compatiblity, we scan also old dirs
	{
	    if (!empty($conf->product->enabled)) $upload_dirold = $conf->product->multidir_output[$object->entity].'/'.substr(substr("000".$object->id, -2), 1, 1).'/'.substr(substr("000".$object->id, -2), 0, 1).'/'.$object->id."/photos";
	    else $upload_dirold = $conf->service->multidir_output[$object->entity].'/'.substr(substr("000".$object->id, -2), 1, 1).'/'.substr(substr("000".$object->id, -2), 0, 1).'/'.$object->id."/photos";
	}
}
$modulepart = 'produit';
$permissiontoadd = (($object->type == Product::TYPE_PRODUCT && $user->rights->produit->creer) || ($object->type == Product::TYPE_SERVICE && $user->rights->service->creer));
/* modif fred */
/*if($object->product_type_txt == "fab" && ($action !== "delete" || $action !== "edit" || empty($action) || !$status_product)) {
    header("Location:".DOL_URL_ROOT."/product/document.php?id=".$id."&action=edit&status_product=produitfab");
    exit;
}*/
/*
 * Actions
 */
$parameters = array('id'=>$id);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
if (empty($reshook))
{
    // Delete line if product propal merge is linked to a file
    if (!empty($conf->global->PRODUIT_PDF_MERGE_PROPAL))
    {
        if ($action == 'confirm_deletefile' && $confirm == 'yes')
        {
            //extract file name
            $urlfile = GETPOST('urlfile', 'alpha');
            $filename = basename($urlfile);
            $filetomerge = new Propalmergepdfproduct($db);
            $filetomerge->fk_product = $object->id;
            $filetomerge->file_name = $filename;
            $result = $filetomerge->delete_by_file($user);
            if ($result < 0) {
                    setEventMessages($filetomerge->error, $filetomerge->errors, 'errors');
            }
        }
    }

    // Action submit/delete file/link
    /*modif fred*/
    if($status_product && $status_product == "produitfab") {
        $backtopage = $_SERVER["PHP_SELF"].'?id='.$object->id."&status_product=produitfab&action=edit";
    }
    include_once DOL_DOCUMENT_ROOT.'/core/actions_linkedfiles.inc.php';
}

if ($action == 'filemerge')
{
    $is_refresh = GETPOST('refresh');
    if (empty($is_refresh)) {
        $filetomerge_file_array = GETPOST('filetoadd');
        $filetomerge_file_array = GETPOST('filetoadd');
        if ($conf->global->MAIN_MULTILANGS) {
                $lang_id = GETPOST('lang_id', 'aZ09');
        }
        // Delete all file already associated
        $filetomerge = new Propalmergepdfproduct($db);
        if ($conf->global->MAIN_MULTILANGS) {
                $result = $filetomerge->delete_by_product($user, $object->id, $lang_id);
        } else {
                $result = $filetomerge->delete_by_product($user, $object->id);
        }
        if ($result < 0) {
                setEventMessages($filetomerge->error, $filetomerge->errors, 'errors');
        }

        // for each file checked add it to the product
        if (is_array($filetomerge_file_array)) {
            foreach ($filetomerge_file_array as $filetomerge_file) {
                $filetomerge->fk_product = $object->id;
                $filetomerge->file_name = $filetomerge_file;

                if ($conf->global->MAIN_MULTILANGS) {
                        $filetomerge->lang = $lang_id;
                }

                $result = $filetomerge->create($user);
                if ($result < 0) {
                        setEventMessages($filetomerge->error, $filetomerge->errors, 'errors');
                }
            }
        }
    }
}


/*
 *	View
 */

$form = new Form($db);

$title = $langs->trans('ProductServiceCard');
$helpurl = '';
$shortlabel = dol_trunc($object->label, 16);
if (GETPOST("type") == '0' || ($object->type == Product::TYPE_PRODUCT))
{
	$title = $langs->trans('Product')." ".$shortlabel." - ".$langs->trans('Documents');
	$helpurl = 'EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
}
if (GETPOST("type") == '1' || ($object->type == Product::TYPE_SERVICE))
{
	$title = $langs->trans('Service')." ".$shortlabel." - ".$langs->trans('Documents');
	$helpurl = 'EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
}

llxHeader('', $title, $helpurl);


if ($object->id)
{
    $head = product_prepare_head($object);
    $titre = $langs->trans("CardProduct".$object->type);
    $picto = ($object->type == Product::TYPE_SERVICE ? 'service' : 'product');
    
    if($status_product && $status_product == "produitfab") {
        //dol_fiche_head($head, 'card', $titre, 0, $picto);
        print load_fiche_titre("Modification produit fab", "<a href='".DOL_URL_ROOT."/product/listproduitfab.php?leftmenu=product&type=0&idmenu=37'>Retour</a>", $picto);
        //echo "<pre>";
        $headProductFab = [];
        foreach($head as $kFab => $resFab){
            if($resFab[2] == "card"){
                $resFab[0] = DOL_URL_ROOT."/product/card.php?id=".$object->id."&status_product=produitfab&action=edit";
                array_push($headProductFab,$resFab);
            }
            if($resFab[2] == "documents"){
                $resFab[0] = DOL_URL_ROOT."/product/document.php?id=".$object->id."&status_product=produitfab&action=edit";
                array_push($headProductFab,$resFab);
            }
        }
        dol_fiche_head($headProductFab, 'documents', $titre, -1, $picto);
    }else{
        if($object->product_type_txt == 'fab'){
            $headProductFab = [];
            foreach($head as $kFab => $resFab){
                if($resFab[2] == "suppliers"){
                    $resFab[0] = DOL_URL_ROOT."/product/fournisseurs.php?id=".$object->id;
                    array_push($headProductFab,$resFab);
                }
                if($resFab[2] == "card"){
                    $resFab[0] = DOL_URL_ROOT."/product/card.php?id=".$object->id;
                    array_push($headProductFab,$resFab);
                }
                if($resFab[2] == "documents"){
                    $resFab[0] = DOL_URL_ROOT."/product/document.php?id=".$object->id;
                    array_push($headProductFab,$resFab);
                }
            }
            dol_fiche_head($headProductFab, 'documents', $titre, -1, $picto);
        }else{
            dol_fiche_head($head, 'documents', $titre, -1, $picto);
        }
        //dol_fiche_head($head, 'documents', $titre, -1, $picto);
    }
    $parameters = array();
    $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
    print $hookmanager->resPrint;
	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	// Build file list
	$filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', $sortfield, (strtolower($sortorder) == 'desc' ?SORT_DESC:SORT_ASC), 1);
	if (!empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))    // For backward compatiblity, we scan also old dirs
	{
            $filearrayold = dol_dir_list($upload_dirold, "files", 0, '', '(\.meta|_preview.*\.png)$', $sortfield, (strtolower($sortorder) == 'desc' ?SORT_DESC:SORT_ASC), 1);
            $filearray = array_merge($filearray, $filearrayold);
	}
	$totalsize = 0;
	foreach ($filearray as $key => $file)
	{
            $totalsize += $file['size'];
	}
    $linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
    $object->next_prev_filter = " fk_product_type = ".$object->type;
    $shownav = 1;
    if ($user->socid && !in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) $shownav = 0;

    if(!$status_product) {
        dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');
    }else{
        $width = 80; 
        $entity = (empty($object->entity) ? $conf->entity : $object->entity);
        $showimage = $object->is_photo_available($conf->product->multidir_output[$entity]);
        $maxvisiblephotos = (isset($conf->global->PRODUCT_MAX_VISIBLE_PHOTO) ? $conf->global->PRODUCT_MAX_VISIBLE_PHOTO : 5);
        if ($conf->browser->layout == 'phone') $maxvisiblephotos = 1;
        if ($showimage) print '<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('product', $conf->product->multidir_output[$entity], 'small', $maxvisiblephotos, 0, 0, 0, $width, 0).'</div>';
    }
    print '<div class="fichecenter">';
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border tableforfield centpercent">';
    print '<tr><td class="titlefield">'.$langs->trans("NbOfAttachedFiles").'</td><td colspan="3">'.count($filearray).'</td></tr>';
    print '<tr><td>'.$langs->trans("TotalSizeOfAttachedFiles").'</td><td colspan="3">'.dol_print_size($totalsize, 1, 1).'</td></tr>';
    print '</table>';
    print '</div>';
    print '<div style="clear:both"></div>';
    dol_fiche_end();
    $param = '&id='.$object->id;
    /* modif fred*/
    $isAddFormLien = 1;
    if($status_product && $status_product == "produitfab") {
        $moreparam="&status_product=produitfab&action=edit";
        $param = '&id='.$object->id.'&status_product=produitfab';
        $isAddFormLien = 0;
    }
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
    if($resu_fab !== "fab"){
        
        if(!empty($_FILES['icone_prod_1']['name']) || !empty($_FILES['icone_prod_2']['name'])){
            
            $upload_dir = $conf->product->multidir_output[$conf->entity];
            $sdir = $conf->product->multidir_output[$conf->entity];
            if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) {
                if (version_compare(DOL_VERSION, '3.8.0', '<')){
                    $dir = $sdir .'/'. get_exdir($object->id,2) . $object->id ."/photos";
                }else {
                    $dir = $sdir .'/'. get_exdir($object->id,2,0,0,$object,'product') . $object->id ."/photos";
                }
            } else {
                $principaleProd = "select ref from ".MAIN_DB_PREFIX."product where rowid= ".$object->id;
                $resRefss = $db->getRows($principaleProd);

                $dir = $sdir .'/'.dol_sanitizeFileName($object->ref);
            }
            
            if (! file_exists($dir)) {
                dol_mkdir($dir);
            }
            
            if(!empty($_FILES['icone_prod_1']['name'])){
                $iconePosted1 = $_FILES['icone_prod_1']['name'];
                $ext1 = strtolower(explode(".",$iconePosted1)[1]);
                $icone1 = cleanSpecialChar(cleanString(explode(".",$iconePosted1)[0])).'.'.$ext1;
                $target_file1 = $dir."/".$icone1;
                if(!file_exists($target_file1)){
                    move_uploaded_file($_FILES["icone_prod_1"]["tmp_name"], $target_file1);
                    if (image_format_supported($target_file1) == 1)
                    {
                        $imgThumbSmall = vignette($target_file1, 200, 100, '_small', 80, "thumbs");
                        $imgThumbMini  = vignette($target_file1, 300, 150, '_mini', 80, "thumbs");
                    }
                }
                $sqlUpdateIcon1 = "update ".MAIN_DB_PREFIX."product set "
                . " icone_prod_1 = '".$icone1."' where rowid =  ".$object->id;
                $db->query($sqlUpdateIcon1);
            }
            if(!empty($_FILES['icone_prod_2']['name'])){
                $iconePosted2 = $_FILES['icone_prod_2']['name'];
                $ext2 = strtolower(explode(".",$iconePosted2)[1]);
                $icone2 = cleanSpecialChar(cleanString(explode(".",$iconePosted2)[0])).'.'.$ext2;
                $target_file2 = $dir."/".$icone2;
                if(!file_exists($target_file2)){
                    move_uploaded_file($_FILES["icone_prod_2"]["tmp_name"], $target_file2);
                    if (image_format_supported($target_file2) == 1)
                    {
                        $imgThumbSmall = vignette($target_file2, 200, 100, '_small', 80, "thumbs");
                        $imgThumbMini  = vignette($target_file2, 300, 150, '_mini', 80, "thumbs");
                    }
                }
                $sqlUpdateIcon2 = "update ".MAIN_DB_PREFIX."product set "
                    . " icone_prod_2 = '".$icone2."' where rowid =  ".$object->id;
                $db->query($sqlUpdateIcon2);
            }
            echo "<meta http-equiv='refresh' content='0'>";
        }
        
        print '<form action="'.DOL_URL_ROOT.'/product/document.php?id='.$object->id.'&status_product=produitfab&action=edit" method="POST" enctype="multipart/form-data">';
        print '<table class="border allwidth">';
        
        print '<tr>';
        print '<td style="width:15.5%">ICONE 1 <br><i style="color:red">Taille max autorisé : 2M <br>Largeur max autorisé : 900<br>Hauteur max autorisé : 300 <br>Type de fichier autorisé : jpeg, jpg, png, gif </i></td>';
        $thumbs1Mini    = explode('.',$object->icone_prod_1)[0]."_mini.".explode('.',$object->icone_prod_1)[1];
        $thumbs1Small   = explode('.',$object->icone_prod_1)[0]."_small.".explode('.',$object->icone_prod_1)[1];
        print '<td style="width:15%"><input type="file" name="icone_prod_1" id="icone_prod_1" onchange="readURL(this,\'icss1\',\'icone_prod_1\');">';
        print '</td>';
        print '<td>';
        print ' <a href="javascript:document_preview(\''.DOL_URL_ROOT.'/document.php?modulepart=product&attachment=0&file='.$object->ref.'/'.$object->icone_prod_1.'&entity=1\', \'image/jpeg\', \'Aperçu\')">'
                . '<img src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=product&entity=1&file=/'.$object->ref.'/thumbs/'.$thumbs1Small.'" id="icss1" style="width:15%"/>'
                . '</a>';
        print '</td>';
        print '</tr>';
        print '<tr>'
        . '<td>ICONE 2 <br><i style="color:red">Taille max autorisé : 2M <br>Largeur max autorisé : 900<br>Hauteur max autorisé : 300 <br>Type de fichier autorisé : jpeg, jpg, png, gif</i></td>';
        $thumbs2Mini    = explode('.',$object->icone_prod_2)[0]."_mini.".explode('.',$object->icone_prod_2)[1];
        $thumbs2Small   = explode('.',$object->icone_prod_2)[0]."_small.".explode('.',$object->icone_prod_2)[1];
        print '<td>';
        print '<input type="file" name="icone_prod_2" id="icone_prod_2" onchange="readURL(this,\'icss2\',\'icone_prod_2\');">';
        print '</td>';
        print '<td>';
        print ' <a href="javascript:document_preview(\''.DOL_URL_ROOT.'/document.php?modulepart=product&attachment=0&file='.$object->ref.'/'.$object->icone_prod_2.'&entity=1\', \'image/jpeg\', \'Aperçu\')">'
                . '<img src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=product&entity=1&file=/'.$object->ref.'/thumbs/'.$thumbs2Small.'" id="icss2" style="width:15%"/>'
                . '</a>';
        print '</td>';
        print '</tr>';
        print '</table>';
        print '<input type="submit" class="button" value="Envoyer fichier">';
        print '<img src="'.DOL_URL_ROOT.'/theme/eldy/img/info.png" alt="" title="Changer l\' étiquette dans l\'impression" class="hideonsmartphone">';
        print '</form>';
        print '<hr>';
        ?>
            <script>
                function readURL(input,idImg,idInput) {
                    if (input.files && input.files[0]) {
                        var file = input.files && input.files[0];
                        var img = new Image();
                        img.src = window.URL.createObjectURL(file);
                        if((file.type !== "image/gif" && file.type !== "image/jpeg" && file.type !== "image/png") || file.size > 2000000)  {
                            //$("#updates_products").prop('disabled',true);
                            $("#"+idInput).val('');
                            $( "#dialogerror" ).dialog({
                                modal: true,
                                height: 200,
                                width: 800,
                                resizable: true,
                                title: "Erreur",
                                open: function(){
                                   $("#error_file_to_large").html('- Le type de fichier que vous avez uploadé est : <span style="color:red">'+file.type+' </span> <br>-Taille de l\'image que vous avez uploadé est <span style="color:red">'+bytesToSize(file.size)+'</span> <br>- Les types de fichier autoriser sont : <strong>jpeg, jpg, png, gif</strong><br>- La taille autorisé est inférieur à <strong>2M</strong>');
                                }
                            }).prev(".ui-dialog-titlebar").css({"color":"red","font-weight":"bold"});
                        }else{
                            //$("#updates_products").prop('disabled',false);
                            img.onload = function(e){
                                if(img.width > 900 && (img.width > 900 || img.height > 300)){
                                    //$("#updates_products").prop('disabled',true);
                                    $("#"+idInput).val('');
                                    $( "#dialogerror" ).dialog({
                                        modal: true,
                                        height: 150,
                                        width: 750,
                                        resizable: true,
                                        title: "Erreur",
                                        open: function(){
                                           $("#error_file_to_large").html('- La taille de l\'image que vous avez uploadé est de <span style="color:red;">'+img.width+' x '+img.height+'</span>,  ce qui n\'est pas autoriser <br> - (Longueur x Largeur) maximale autoriser : <strong>900 x 300 </strong>');
                                        }
                                    }).prev(".ui-dialog-titlebar").css({"color":"red","font-weight":"bold"});
                                }else{
                                    $("#updates_products").prop('disabled',false);
                                    var reader = new FileReader();
                                    reader.onload = function (e) {
                                        $('#'+idImg).show();
                                        $('#'+idImg).attr('src', e.target.result);
                                    };
                                    reader.readAsDataURL(input.files[0]);
                                }
                            };
                        }
                    }
                }
                function bytesToSize(bytes) {
                    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                    if (bytes == 0) return '0 Byte';
                    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
                    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
                }
            </script>
            <div id="dialogerror" title="Basic dialog" style="display:none;">
                        <p id="error_file_to_large"></p>
                    </div> 
            <?php
        include_once DOL_DOCUMENT_ROOT.'/core/tpl/document_actions_post_headers.tpl.php';
    }
    // Merge propal PDF document PDF files
    if (!empty($conf->global->PRODUIT_PDF_MERGE_PROPAL))
    {
    	$filetomerge = new Propalmergepdfproduct($db);
    	if ($conf->global->MAIN_MULTILANGS) {
    		$lang_id = GETPOST('lang_id', 'aZ09');
    		$result = $filetomerge->fetch_by_product($object->id, $lang_id);
    	} else {
    		$result = $filetomerge->fetch_by_product($object->id);
    	}
    	$form = new Form($db);
    	$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', 'name', SORT_ASC, 1);
    	if (!empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))    // For backward compatiblity, we scan also old dirs
    	{
            $filearray = array_merge($filearray, dol_dir_list($upload_dirold, "files", 0, '', '\.meta$', 'name', SORT_ASC, 1));
    	}
    	// For each file build select list with PDF extention
    	if (count($filearray) > 0)
    	{
            print '<br>';
            // Actual file to merge is :
            if (count($filetomerge->lines) > 0) {
                    print $langs->trans('PropalMergePdfProductActualFile');
            }

            print '<form name="filemerge" action="'.DOL_URL_ROOT.'/product/document.php?id='.$object->id.'" method="post">';
            print '<input type="hidden" name="token" value="'.newToken().'">';
            print '<input type="hidden" name="action" value="filemerge">';
            if (count($filetomerge->lines) == 0) {
                    print $langs->trans('PropalMergePdfProductChooseFile');
            }

            print  '<table class="noborder">';

            // Get language
            if ($conf->global->MAIN_MULTILANGS) {
                    $langs->load("languages");

                    print  '<tr class="liste_titre"><td>';

                    $default_lang = empty($lang_id) ? $langs->getDefaultLang() : $lang_id;

                    $langs_available = $langs->get_available_languages(DOL_DOCUMENT_ROOT, 12);

                        print Form::selectarray('lang_id', $langs_available, $default_lang, 0, 0, 0, '', 0, 0, 0, 'ASC');

                    if ($conf->global->MAIN_MULTILANGS) {
                            print  '<input type="submit" class="button" name="refresh" value="'.$langs->trans('Refresh').'">';
                    }

                    print  '</td></tr>';
            }

            foreach ($filearray as $filetoadd)
            {
                    if ($ext = pathinfo($filetoadd['name'], PATHINFO_EXTENSION) == 'pdf')
                    {
                            $checked = '';
                            $filename = $filetoadd['name'];

                            if ($conf->global->MAIN_MULTILANGS)
                            {
                                    if (array_key_exists($filetoadd['name'].'_'.$default_lang, $filetomerge->lines))
                                    {
                                            $filename = $filetoadd['name'].' - '.$langs->trans('Language_'.$default_lang);
                                            $checked = ' checked ';
                                    }
                            }
                            else
                            {
                                    if (array_key_exists($filetoadd['name'], $filetomerge->lines))
                                    {
                                            $checked = ' checked ';
                                    }
                            }

                            print  '<tr class="oddeven"><td>';
                            print  '<input type="checkbox" '.$checked.' name="filetoadd[]" id="filetoadd" value="'.$filetoadd['name'].'">'.$filename.'</input>';
                            print  '</td></tr>';
                    }
            }

            print  '<tr><td>';
            print  '<input type="submit" class="button" name="save" value="'.$langs->trans('Save').'">';
            print  '</td></tr>';

            print  '</table>';

            print  '</form>';
    	}
    }
}
else
{
    print $langs->trans("ErrorUnknown");
}
// End of page
llxFooter();
$db->close();
