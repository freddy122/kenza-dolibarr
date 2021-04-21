alter table llx_product 
add quantite_commander INT(11) DEFAULT NULL,  
add quantite_fabriquer INT(11) DEFAULT NULL,
add composition VARCHAR(255) NULL, 
add price_yuan double(24,2) DEFAULT NULL;

alter table llx_product add product_type_txt VARCHAR(255) NULL;
alter table llx_product add price_euro double(24,2) DEFAULT NULL;
alter table llx_product add weight_variant double(24,2) DEFAULT NULL;
alter table llx_product add ref_fab_frs VARCHAR(255) NULL;
alter table llx_product add taux_euro_yuan double(24,3) DEFAULT NULL;

alter table llx_product add ref_tissus_couleur VARCHAR(255) NULL;

alter table llx_product add icone_prod_1 VARCHAR(255) NULL;
alter table llx_product add icone_prod_2 VARCHAR(255) NULL;

alter table llx_product add total_quantite_commander double(24,2) DEFAULT NULL;
ALTER TABLE `llx_product` CHANGE `total_quantite_commander` `total_quantite_commander` FLOAT(20) NULL DEFAULT NULL;
alter table llx_product add total_montant_yuan double(24,2) DEFAULT NULL;
alter table llx_product add total_montant_euro double(24,2) DEFAULT NULL;