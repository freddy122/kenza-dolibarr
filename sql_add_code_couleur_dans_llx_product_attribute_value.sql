ALTER TABLE `llx_product_attribute_value` ADD `code_couleur` VARCHAR(255) NULL AFTER `value`; 
ALTER TABLE `llx_product_attribute_value` ADD `type_taille` VARCHAR(255) NULL AFTER `code_couleur`; 
ALTER TABLE `llx_product_attribute_value` ADD `image_couleur` VARCHAR(255) NULL AFTER `type_taille`; 
ALTER TABLE `llx_product_attribute_value` ADD `valeur_courte` VARCHAR(255) NULL;