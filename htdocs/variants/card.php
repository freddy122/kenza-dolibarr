<?php
/* Copyright (C) 2016   Marcos García   <marcosgdf@gmail.com>
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
/* for document */
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';

$id = GETPOST('id', 'int');
$valueid = GETPOST('valueid', 'alpha');
$action = GETPOST('action', 'alpha');
$label = GETPOST('label', 'alpha');
$ref = GETPOST('ref', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'alpha');

$object = new ProductAttribute($db);
$objectval = new ProductAttributeValue($db);

if ($object->fetch($id) < 1) {
	dol_print_error($db, $langs->trans('ErrorRecordNotFound'));
	exit();
}


/*
 * Actions
 */

if ($cancel) $action = '';

if ($_POST) {
	if ($action == 'edit') {
		$object->ref = $ref;
		$object->label = $label;

		if ($object->update($user) < 1) {
			setEventMessages($langs->trans('CoreErrorMessage'), $object->errors, 'errors');
		} else {
			setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
			header('Location: '.dol_buildpath('/variants/card.php?id='.$id, 2));
			exit();
		}
	} elseif ($action == 'update') {
		if ($objectval->fetch($valueid) > 0) {
			$objectval->ref = cleanSpecialChar(cleanString($ref));
			$objectval->value = GETPOST('value', 'alpha');
			$objectval->code_couleur = GETPOST('code_couleur', 'alpha');
			$objectval->valeur_courte = GETPOST('valeur_courte', 'alpha');
                        
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
                        
			$objectval->type_taille = GETPOST('type_taille', 'alpha');

			if (empty($objectval->ref))
			{
				$error++;
				setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Ref")), null, 'errors');
			}
			if (empty($objectval->value))
			{
				$error++;
				setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Label")), null, 'errors');
			}

			if (!$error)
			{
				if ($objectval->update($user) > 0) {
					setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
				} else {
					setEventMessage($langs->trans('CoreErrorMessage'), $objectval->errors, 'errors');
				}
			}
		}

		header('Location: '.dol_buildpath('/variants/card.php?id='.$object->id, 2));
		exit();
	}
}

if ($confirm == 'yes') {
	if ($action == 'confirm_delete') {
		$db->begin();

		$res = $objectval->deleteByFkAttribute($object->id);

		if ($res < 1 || ($object->delete() < 1)) {
			$db->rollback();
			setEventMessages($langs->trans('CoreErrorMessage'), $object->errors, 'errors');
			header('Location: '.dol_buildpath('/variants/card.php?id='.$object->id, 2));
		} else {
			$db->commit();
			setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
			header('Location: '.dol_buildpath('/variants/list.php', 2));
		}
		exit();
	}
	elseif ($action == 'confirm_deletevalue')
	{
            if ($objectval->fetch($valueid) > 0) {
                if ($objectval->delete() < 1) {
                    setEventMessages($langs->trans('CoreErrorMessage'), $objectval->errors, 'errors');
                } else {
                    $sdir = $conf->medias->multidir_output[$conf->entity];
                    $dir = $sdir .'/'.dol_sanitizeFileName(strtoupper(cleanSpecialChar(cleanString($objectval->ref))));
                    if(file_exists($dir)){
                        array_map('unlink', glob("$dir/*.*"));
                        array_map('unlink', glob("$dir/thumbs/*.*"));
                        rmdir($dir."/thumbs");
                        rmdir($dir);
                    }
                    setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
                }

                header('Location: '.dol_buildpath('/variants/card.php?id='.$object->id, 2));
                exit();
            }
	}
}


/*
 * View
 */

$langs->load('products');

$title = $langs->trans('ProductAttributeName', dol_htmlentities($object->label));

llxHeader('', $title);

//print load_fiche_titre($title);

$h = 0;
$head[$h][0] = DOL_URL_ROOT.'/variants/card.php?id='.$object->id;
$head[$h][1] = $langs->trans("ProductAttributeName");
$head[$h][2] = 'variant';
$h++;

dol_fiche_head($head, 'variant', $langs->trans('ProductAttributeName'), -1, 'generic');

if ($action == 'edit') {
    print '<form method="POST">';
}


if ($action != 'edit') {
    print '<div class="fichecenter">';
    print '<div class="underbanner clearboth"></div>';
}
print '<table class="border centpercent tableforfield">';
print '<tr>';
print '<td class="titlefield'.($action == 'edit' ? ' fieldrequired' : '').'">'.$langs->trans('Ref').'</td>';
print '<td>';
if ($action == 'edit') {
	print '<input type="text" name="ref" value="'.$object->ref.'">';
} else {
	print dol_htmlentities($object->ref);
}
print '</td>';
print '</tr>';
print '<tr>';
print '<td'.($action == 'edit' ? ' class="fieldrequired"' : '').'>'.$langs->trans('Label').'</td>';
print '<td>';
if ($action == 'edit') {
	print '<input type="text" name="label" value="'.$object->label.'">';
} else {
	print dol_htmlentities($object->label);
}
print '</td>';
print '</tr>';

print '</table>';


if ($action != 'edit') {
    print '</div>';
}

dol_fiche_end();

if ($action == 'edit') {
	print '<div style="text-align: center;">';
	print '<div class="inline-block divButAction">';
	print '<input type="submit" class="button" value="'.$langs->trans('Save').'">';
	print '&nbsp; &nbsp;';
	print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
	print '</div>';
	print '</div></form>';
} else {
	if ($action == 'delete') {
		$form = new Form($db);

		print $form->formconfirm(
			"card.php?id=".$object->id,
			$langs->trans('Delete'),
			$langs->trans('ProductAttributeDeleteDialog'),
			"confirm_delete",
			'',
			0,
			1
		);
	} elseif ($action == 'delete_value') {
		if ($objectval->fetch($valueid) > 0) {
			$form = new Form($db);

			print $form->formconfirm(
				"card.php?id=".$object->id."&valueid=".$objectval->id,
				$langs->trans('Delete'),
				$langs->trans('ProductAttributeValueDeleteDialog', dol_htmlentities($objectval->value), dol_htmlentities($objectval->ref)),
				"confirm_deletevalue",
				'',
				0,
				1
			);
		}
	}

	?>

	<div class="tabsAction">
		<div class="inline-block divButAction">
			<a href="card.php?id=<?php echo $object->id ?>&action=edit" class="butAction"><?php echo $langs->trans('Modify') ?></a>
			<a href="card.php?id=<?php echo $object->id ?>&action=delete" class="butAction"><?php echo $langs->trans('Delete') ?></a>
		</div>
	</div>


	<?php

	print load_fiche_titre($langs->trans("PossibleValues"));

	if ($action == 'edit_value') {
		print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="'.$id.'">';
		print '<input type="hidden" name="valueid" value="'.$valueid.'">';
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}

	print '<table class="liste">';
	print '<tr class="liste_titre">';
	print '<th class="liste_titre titlefield">'.$langs->trans('Ref').'</th>';
	print '<th class="liste_titre">Libellé</th>';
        if($object->id == 1) {
            print '<th class="liste_titre">Libellé courte (10 caractères max)</th>';
            print '<th class="liste_titre">Code couleur</th>';
            print '<th class="liste_titre">Image couleur</th>';
        }
        if($object->id == 2) {
            print '<th class="liste_titre">Type taille</th>';
        }
	print '<th class="liste_titre"></th>';
	print '</tr>';

	foreach ($objectval->fetchAllByProductAttributeBackendValue($object->id) as $attrval) {
		print '<tr class="oddeven">';
		if ($action == 'edit_value' && ($valueid == $attrval->id)) {
			?>
				<td><input type="text" name="ref" value="<?php echo $attrval->ref ?>"></td>
				<td><input type="text" name="value" value="<?php echo $attrval->value ?>"></td>
                                <?php if($object->id == 2): ?>
                                <td>
                                    <select name="type_taille">
                                        <option value="1" <?php echo intval($attrval->type_taille) == 1 ? "selected='selected'" : ""; ?>>Femme</option>
                                        <option value="2" <?php echo intval($attrval->type_taille) == 2 ? "selected='selected'" : ""; ?> >Fillette</option>
                                        <option value="3" <?php echo intval($attrval->type_taille) == 3 ? "selected='selected'" : ""; ?>>Bébé</option>
                                    </select>
                                </td>
                                <?php endif;?>
                                
                                <?php if($object->id == 1): ?>
                                 <td>
                                     <input id="valeur_courte" type="text" name="valeur_courte" maxlength="10" value="<?php echo $attrval->valeur_courte ?>">
                                </td>
                                <?php if(!empty($attrval->code_couleur)): ?>
                                    <td>
                                        <input id="code_couleur" type="text" name="code_couleur" value="<?php echo $attrval->code_couleur ?>">
                                        <input id="code_couleur_pick" type="color"  value="<?php echo $attrval->code_couleur ?>" >
                                        <script>
                                            $(document).ready(function(){
                                                $("#code_couleur_pick").change(function(){
                                                    $("#code_couleur").val($(this).val());
                                                });
                                            });
                                        </script>
                                    </td>
                                <?php endif ?>
                                
                                    <td>
                                        <input id="image_couleur" type="file" name="image_couleur" id="image_couleur" onchange="readURL(this,'blah<?php echo $attrval->id; ?>','image_couleur');" /><i style="color:red;font-size: 12px;">(Taille max : 2M, Largeur max : 1200, Hauteur max : 1200, Type  : jpeg, jpg, png, gif </i> <br>
                                        <?php 
                                            $thumbsMini    = explode('.',$attrval->image_couleur)[0]."_mini.".explode('.',$attrval->image_couleur)[1];
                                            $thumbsSmall   = explode('.',$attrval->image_couleur)[0]."_small.".explode('.',$attrval->image_couleur)[1];
                                        ?>
                                        <img id="blah<?php echo $attrval->id; ?>" src="<?php echo DOL_URL_ROOT.'/viewimage.php?modulepart=medias&entity='.$entity.'&file=/'. strtoupper($attrval->ref).'/thumbs/'.$thumbsSmall; ?>" style="width:12%;"/>
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
                                                                    $('#'+idImg)
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
                                <?php endif ?>
				<td class="right">
					<input type="submit" value="<?php echo $langs->trans('Save') ?>" class="button">
					&nbsp; &nbsp;
                                        <a href="<?php echo DOL_URL_ROOT."/variants/card.php?id=".$object->id; ?>" class="button" style="background: var(--butactionbg);border-collapse: collapse;border: none;"><?php echo $langs->trans('Cancel') ?></a>
				</td>
			<?php
		} else {
			?>
				<td><?php echo dol_htmlentities($attrval->ref) ?></td>
				<td><?php echo dol_htmlentities($attrval->value) ?></td>
                                <?php if($object->id == 1): ?>
                                        <?php 
                                            $thumbsMini    = explode('.',$attrval->image_couleur)[0]."_mini.".explode('.',$attrval->image_couleur)[1];
                                            $thumbsSmall   = explode('.',$attrval->image_couleur)[0]."_small.".explode('.',$attrval->image_couleur)[1];
                                        ?>
                                    <td><?php echo dol_htmlentities($attrval->valeur_courte) ?></td>
                                    <td><?php echo dol_htmlentities($attrval->code_couleur) ?><p style="width:10px;height:10px; background-color:<?php echo dol_htmlentities($attrval->code_couleur) ?>; "></p></td>
                                    <td><img id="blah" src="<?php echo DOL_URL_ROOT.'/viewimage.php?modulepart=medias&entity='.$entity.'&file=/'. strtoupper($attrval->ref).'/thumbs/'.$thumbsSmall; ?>" style="width:12%;"/></td>
				<?php endif;?>
                                <?php if($object->id == 2): ?>
                                    <td><?php 
                                    $type_taille_text = "";
                                    if(intval($attrval->type_taille) == 1){
                                        $type_taille_text = "Femme";
                                    }elseif(intval($attrval->type_taille) == 2){
                                        $type_taille_text = "Fillette";
                                    }elseif(intval($attrval->type_taille) == 3){
                                        $type_taille_text = "Bébé";
                                    }
                                    echo dol_htmlentities($type_taille_text) 
                                    
                                    ?></td>
				<?php endif;?>
                                <td class="right">
					<a class="editfielda marginrightonly" href="card.php?id=<?php echo $object->id ?>&action=edit_value&valueid=<?php echo $attrval->id ?>"><?php echo img_edit() ?></a>
					<a href="card.php?id=<?php echo $object->id ?>&action=delete_value&valueid=<?php echo $attrval->id ?>"><?php echo img_delete() ?></a>
				</td>
			<?php
		}
		print '</tr>';
	}
	print '</table>';

	if ($action == 'edit_value') {
		print '</form>';
	}

	print '<div class="tabsAction">';
	print '<div class="inline-block divButAction">';
	print '<a href="create_val.php?id='.$object->id.'" class="butAction">'.$langs->trans('Create').'</a>';
	print '</div>';
	print '</div>';
}

// End of page
llxFooter();
$db->close();
