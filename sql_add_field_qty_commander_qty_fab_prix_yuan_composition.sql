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

alter TABLE llx_product MODIFY weight_variant double(24,3) DEFAULT NULL;