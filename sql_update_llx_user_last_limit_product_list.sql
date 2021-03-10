ALTER TABLE `llx_user` ADD `last_limit_product_list` INT(11) NULL DEFAULT '5000' AFTER `fk_warehouse`;
ALTER TABLE `llx_user` ADD `last_limit_commande_list` INT(11) NULL DEFAULT '5000' AFTER `last_limit_product_list`;
ALTER TABLE `llx_user` ADD `last_limit_commande_fourn_list` INT(11) NULL DEFAULT '5000' AFTER `last_limit_commande_list`;
ALTER TABLE `llx_user` ADD `last_limit_facture_list` INT(11) NULL DEFAULT '5000' AFTER `last_limit_commande_fourn_list`;
ALTER TABLE `llx_user` ADD `last_limit_facture_fourn_list` INT(11) NULL DEFAULT '5000' AFTER `last_limit_facture_list`;