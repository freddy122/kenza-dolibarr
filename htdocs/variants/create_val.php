<?php
/* Copyright (C) 2016	Marcos García	<marcosgdf@gmail.com>
 * Copyright (C) 2018   Frédéric France <frederic.france@netlogic.fr>
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

require '../main.inc.php';
require 'class/ProductAttribute.class.php';
require 'class/ProductAttributeValue.class.php';

/*modif fred*/
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$value = GETPOST('value', 'alpha');
$code_couleur = GETPOST('code_couleur', 'alpha');
$image_couleur = GETPOST('image_couleur', 'alpha');
$type_taille = GETPOST('type_taille', 'alpha');
$dataPopup = GETPOST('data_popup');

if($dataPopup && $dataPopup == 1){
    print '<style>
            #tmenu_tooltip {
                display:none!important;
            }
            .side-nav{
                display:none!important;
            }
            #topmenu-login-dropdown {
                display:none!important
            }
            .login_block_other {
                display:none!important
            }
            .table-fiche-title{
                display:none!important
            }
            .tabs{
                display:none!important
            }
        </style>';
}
$action = GETPOST('action', 'alpha');
$cancel = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

$object = new ProductAttribute($db);
$objectval = new ProductAttributeValue($db);

if ($object->fetch($id) < 1) {
	dol_print_error($db, $langs->trans('ErrorRecordNotFound'));
	exit();
}


/*
 * Actions
 */

if ($cancel)
{
	$action = '';
	header('Location: '.DOL_URL_ROOT.'/variants/card.php?id='.$object->id);
	exit();
}

// None



/*
 * View
 */

if ($action == 'add')
{
	if (empty($ref) || empty($value)) {
		setEventMessages($langs->trans('ErrorFieldsRequired'), null, 'errors');
	} else {
		$objectval->fk_product_attribute = $object->id;
		$objectval->ref = cleanSpecialChar(cleanString($ref));
		$objectval->value = $value;
		$objectval->code_couleur = $code_couleur;
                
                if(!empty($_FILES['image_couleur']['name'])){
                    $sdir = $conf->medias->multidir_output[$conf->entity];
                    $dir = $sdir .'/'.dol_sanitizeFileName(strtoupper(cleanSpecialChar(cleanString($ref))));
                    if(!file_exists($dir)){
                        dol_mkdir($dir);
                    }
                    $img_coul_posted = $_FILES['image_couleur']['name'];
                    $extimg          = strtolower(explode(".",$img_coul_posted)[1]);
                    $imgscoul        = cleanSpecialChar(cleanString(explode(".",$img_coul_posted)[0])).'.'.$extimg;
                    $target_file     = $dir."/".$imgscoul;
                    move_uploaded_file($_FILES["image_couleur"]["tmp_name"], $target_file);

                    $objectval->image_couleur = $imgscoul;
                    if (image_format_supported($target_file) == 1)
                    {
                        $imgThumbSmall = vignette($target_file, 150, 150, '_small', 50, "thumbs");
                        $imgThumbMini  = vignette($target_file, 200, 200, '_mini', 80, "thumbs");
                    }
                }
		$objectval->type_taille = $type_taille;

		if ($objectval->create($user) > 0) {
                    if($dataPopup && $dataPopup == 1){
			print '<script type="text/javascript">
                                    window.parent.location.reload()
                                </script> ';
                    }else{
                         
			setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
			header('Location: '.DOL_URL_ROOT.'/variants/card.php?id='.$object->id);
			exit();
                    }
		} else {
			setEventMessages($langs->trans('ErrorCreatingProductAttributeValue'), $objectval->errors, 'errors');
                        header('Location: '.DOL_URL_ROOT.'/variants/create_val.php?id='.$object->id."&data_popup=1");
			exit();
		}
	}
}

$langs->load('products');

$title = $langs->trans('ProductAttributeName', dol_htmlentities($object->label));

llxHeader('', $title);

$h = 0;
$head[$h][0] = DOL_URL_ROOT.'/variants/card.php?id='.$object->id;
$head[$h][1] = $langs->trans("ProductAttributeName");
$head[$h][2] = 'variant';
$h++;

dol_fiche_head($head, 'variant', $langs->trans('ProductAttributeName'), -1, 'generic');

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';
?>
<table class="border" style="width: 100%">
	<tr>
		<td class="titlefield fieldrequired"><?php echo $langs->trans('Ref') ?></td>
		<td><?php echo dol_htmlentities($object->ref) ?>
	</tr>
	<tr>
		<td class="fieldrequired"><?php echo $langs->trans('Label') ?></td>
		<td><?php echo dol_htmlentities($object->label) ?></td>
	</tr>
</table>

<?php
print '</div>';

dol_fiche_end();

print '<br>';


print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST" enctype="multipart/form-data">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="add">';
print '<input type="hidden" name="id" value="'.$object->id.'">';
print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

print load_fiche_titre($langs->trans('NewProductAttributeValue'));

dol_fiche_head();

?>
	<table class="border" style="width: 100%">
            <input type ="hidden" value="<?php echo $dataPopup ; ?>" name="data_popup">
            <tr>
                    <td class="titlefield fieldrequired"><label for="ref"><?php echo $langs->trans('Ref') ?></label></td>
                    <td><input id="ref" type="text" name="ref" value="<?php echo cleanSpecialChar(cleanString($ref)); ?>" required></td>
            </tr>
            <tr>
                    <td class="fieldrequired"><label for="value"><?php echo $langs->trans('Label') ?></label></td>
                    <td><input id="value" type="text" name="value" value="<?php echo $value ?>" required></td>
            </tr>
            <?php if($object->id == 2): ?>
                <tr>
                    <td class="fieldrequired"><label for="value">Catégorie taille</label></td>
                    <td>
                        <select name="type_taille">
                            <option value="1" selected="selected" >Femme</option>
                            <option value="2" >Fillette</option>
                            <option value="3" >Bébé</option>
                        </select>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if($object->id == 1): ?>
                <tr>
                    <td class="fieldrequired"><label for="value">Couleur</label></td>
                    <td>
                        <input id="code_couleur" type="text" name="code_couleur" value="#fff152">
                        <input id="code_couleur_pick" type="color"  value="#fff152" >
                        <script>
                            $(document).ready(function(){
                                $("#code_couleur_pick").change(function(){
                                    $("#code_couleur").val($(this).val());
                                });
                            });
                        </script>
                    </td>
                </tr>
                <tr>
                    <td class="fieldrequired"><label for="value">Image couleur</label><br><i style="color:red;font-size: 12px;">Taille max autorisé : 2M <br>Largeur max autorisé : 1200<br>Hauteur max autorisé : 1200 <br>Type de fichier autorisé : jpeg, jpg, png, gif </i></td>
                    <td>
                        <input id="image_couleur" type="file" name="image_couleur" id="image_couleur" onchange="readURL(this,'blah','image_couleur');"/> 
                        <img id="blah" src="#" style="display: none;" style="width:20%;">
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
                                               $("#error_file_to_large").html('- Type de fichier non autorisé (<span style="color:red">'+file.type+' </span>) ou taille très grand (<span style="color:red">'+bytesToSize(file.size)+'</span>) <br>- Les types de fichier autoriser sont : <strong>jpeg, jpg, png, gif</strong><br>- La taille autorisé est inférieur à <strong>2M<strong>');
                                            }
                                        }).prev(".ui-dialog-titlebar").css({"color":"red","font-weight":"bold"});
                                    }else{
                                        //$("#updates_products").prop('disabled',false);
                                        img.onload = function(e){
                                            if(img.width > 1200 && (img.width > 1200 || img.height > 1200)){
                                                //$("#updates_products").prop('disabled',true);
                                                $("#"+idInput).val('');
                                                $( "#dialogerror" ).dialog({
                                                    modal: true,
                                                    height: 150,
                                                    width: 750,
                                                    resizable: true,
                                                    title: "Erreur",
                                                    open: function(){
                                                       $("#error_file_to_large").html('- La taille de l\'image que vous avez uploadé est de <span style="color:red;">'+img.width+' x '+img.height+'</span>,  ce qui n\'est pas autoriser');
                                                    }
                                                }).prev(".ui-dialog-titlebar").css({"color":"red","font-weight":"bold"});
                                            }else{
                                                $("#updates_products").prop('disabled',false);
                                                var reader = new FileReader();
                                                reader.onload = function (e) {
                                                    $('#'+idImg).show();
                                                    $('#'+idImg).width(90)
                                                        .attr('src', e.target.result);
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
                    </td>
                </tr>
            <?php endif; ?>
	</table>
<?php

dol_fiche_end();

print '<div class="center">';
print '<input type="submit" class="button" name="create" value="'.$langs->trans("Create").'">';
print ' &nbsp; ';
print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
print '</div>';

print '</form>';

// End of page
llxFooter();
$db->close();
