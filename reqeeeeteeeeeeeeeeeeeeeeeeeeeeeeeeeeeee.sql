SELECT DISTINCT p.rowid, p.ref, p.label, p.fk_product_type, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.entity, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.datec as date_creation, p.tms as date_update, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.product_type_txt, p.volume_units, MIN(pfp.unitprice) as minsellprice, pac.rowid prod_comb_id, ef.longdescript as options_longdescript 
FROM llx_product as p 
LEFT JOIN llx_product_extrafields as ef on (p.rowid = ef.fk_object) 
LEFT JOIN llx_product_fournisseur_price as pfp ON p.rowid = pfp.fk_product 
LEFT JOIN llx_product_attribute_combination pac ON pac.fk_product_child = p.rowid 
WHERE p.entity IN (1) AND p.fk_product_type <> 1 
AND pac.rowid IS NULL 
 AND p.rowid not in  (
 select rowid from llx_product where p.product_type_txt = 'fab' 
 ) 
 GROUP BY p.rowid, p.ref, p.label, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.datec, p.tms, p.entity, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.volume_units, pac.rowid, ef.longdescript ORDER BY p.rowid DESC

-----------------------------------------------------
SELECT 
DISTINCT p.rowid, p.ref, p.label, p.fk_product_type, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.entity, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.datec as date_creation, p.tms as date_update, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.product_type_txt, p.volume_units, MIN(pfp.unitprice) as minsellprice, ef.longdescript as options_longdescript 
FROM llx_product as p 
LEFT JOIN llx_product_extrafields as ef on (p.rowid = ef.fk_object) 
JOIN llx_product_fournisseur_price as pfp ON p.rowid = pfp.fk_product 
WHERE p.entity IN (1) AND p.fk_product_type <> 1
 AND p.rowid not in  (
 select rowid from llx_product where p.product_type_txt = 'fab' 
 )  
 GROUP BY p.rowid, p.ref, p.label, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.datec, p.tms, p.entity, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.volume_units, ef.longdescript ORDER BY p.rowid DESC

-------------------------------------------------
SELECT DISTINCT p.rowid, p.ref, p.label, p.fk_product_type, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.entity, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.datec as date_creation, p.tms as date_update, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.product_type_txt, p.volume_units,  p.quantite_commander , p.quantite_fabriquer , p.composition , p.price_yuan , p.product_type_txt , p.price_euro , p.weight_variant , p.ref_fab_frs , p.taux_euro_yuan , p.ref_tissus_couleur ,p.total_quantite_commander, p.total_montant_yuan, p.total_montant_euro,p.price_yuan, p.price_euro,   MIN(pfp.unitprice) as minsellprice, pac.rowid prod_comb_id, ef.longdescript as options_longdescript 
FROM llx_product as p 
LEFT JOIN llx_product_extrafields as ef on (p.rowid = ef.fk_object) 
LEFT JOIN llx_product_fournisseur_price as pfp ON p.rowid = pfp.fk_product 
LEFT JOIN llx_product_attribute_combination pac ON pac.fk_product_child = p.rowid WHERE p.entity IN (1) AND p.fk_product_type <> 1 AND pac.rowid IS NULL AND p.product_type_txt = 'fab'  GROUP BY p.rowid, p.ref, p.label, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.datec, p.tms, p.entity, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.volume_units, pac.rowid, ef.longdescript ORDER BY p.rowid DESC LIMIT 1001 

-------------------------------------------

SELECT DISTINCT p.rowid, p.ref, p.label, p.fk_product_type, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.entity, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.datec as date_creation, p.tms as date_update, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.product_type_txt, p.volume_units,  p.quantite_commander , p.quantite_fabriquer , p.composition , p.price_yuan , p.product_type_txt , p.price_euro , p.weight_variant , p.ref_fab_frs , p.taux_euro_yuan , p.ref_tissus_couleur ,p.total_quantite_commander, p.total_montant_yuan, p.total_montant_euro,p.price_yuan, p.price_euro,   MIN(pfp.unitprice) as minsellprice, ef.longdescript as options_longdescript 
FROM llx_product as p 
LEFT JOIN llx_product_extrafields as ef on (p.rowid = ef.fk_object) 
LEFT JOIN llx_product_fournisseur_price as pfp ON p.rowid = pfp.fk_product 
WHERE p.entity IN (1) AND p.product_type_txt = 'fab'  
GROUP BY p.rowid, p.ref, p.label, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.datec, p.tms, p.entity, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.volume_units, ef.longdescript 
ORDER BY p.rowid DESC LIMIT 1001 

--------------------------------------------------
SELECT DISTINCT p.rowid,SUBSTRING( p.ref,1,13) as refssss, p.ref, p.label, p.fk_product_type, p.barcode
FROM llx_product as p 
LEFT JOIN llx_product_extrafields as ef on (p.rowid = ef.fk_object) 
LEFT JOIN llx_product_fournisseur_price as pfp ON p.rowid = pfp.fk_product 
WHERE p.entity IN (1) AND p.product_type_txt = 'fab'  
GROUP BY p.rowid, p.ref, p.label, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.datec, p.tms, p.entity, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.volume_units, ef.longdescript 
ORDER BY SUBSTRING(p.ref,1,13) DESC LIMIT 1001

------------------------------------------------------------


SELECT DISTINCT p.rowid, p.ref, p.label, p.fk_product_type, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.entity,
 p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.tobatch, p.accountancy_code_sell,
 p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export,
 p.datec as date_creation, p.tms as date_update, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, 
 p.height_units, p.surface, p.surface_units, p.volume, p.product_type_txt, p.volume_units, 
 MIN(pfp.unitprice) as minsellprice, ef.longdescript as options_longdescript 
 FROM llx_product as p 
 LEFT JOIN llx_product_extrafields as ef on (p.rowid = ef.fk_object) 
 LEFT JOIN llx_product_fournisseur_price as pfp ON p.rowid = pfp.fk_product
 WHERE p.entity IN (1) AND p.fk_product_type <> 1 
  AND p.rowid not in (select rowid from llx_product where p.product_type_txt = 'fab' ) 
 GROUP BY p.rowid, p.ref, p.label, p.barcode, p.price, p.tva_tx, p.price_ttc, p.price_base_type, p.fk_product_type, p.duration, p.finished, p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock, p.datec, p.tms, p.entity, p.tobatch, p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export, p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export, p.pmp, p.stock, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.volume_units,  ef.longdescript ORDER BY p.rowid DESC LIMIT 1001
 
 
 ----------------------------------------------------------------
 <pre>
 UPDATE llx_product set quantite_commander = 20, total_quantite_commander = 20 where rowid=8197
 UPDATE llx_product  set price_yuan = 0,  taux_euro_yuan = 0,  total_montant_yuan = 0,  total_montant_euro = 0,  price_euro = 0,  price = 0,  price_ttc = 0  where rowid=8197
 
 UPDATE llx_product set quantite_commander = 20, total_quantite_commander = 20 where rowid=8198UPDATE llx_product  set price_yuan = 0,  taux_euro_yuan = 0,  total_montant_yuan = 0,  total_montant_euro = 0,  price_euro = 0,  price = 0,  price_ttc = 0  where rowid=8198UPDATE llx_product set quantite_commander = 20, total_quantite_commander = 20 where rowid=8199UPDATE llx_product  set price_yuan = 0,  taux_euro_yuan = 0,  total_montant_yuan = 0,  total_montant_euro = 0,  price_euro = 0,  price = 0,  price_ttc = 0  where rowid=8199okkk
 
 
 
 INSERT INTO llx_product_fournisseur_price 
 (
 rowid, 
 entity, 
 datec, 
 tms, 
 fk_product, 
 fk_soc, 
 ref_fourn, 
 fk_availability, 
 price, 
 quantity, 
 unitprice, 
 barcode, 
 fk_barcode_type, 
 tva_tx, 
 fk_user
 ) 
 VALUES 
 (
 NULL,
 1,
 '2021-04-29 09:58:01',
 CURRENT_TIMESTAMP,
 8226,
 19,
 'ref-fredddd',
 0,
 20,
 10,
 20,
 'barcdssss', 
 2, 
 8.5,
 1
 );
 
 