<?php

/* Copyright (C) 2016	Marcos García	<marcosgdf@gmail.com>
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
 * Class ProductAttributeValue
 * Used to represent a product attribute value
 */
class ProductAttributeValue
{
	/**
	 * Database handler
	 * @var DoliDB
	 */
	private $db;

	/**
	 * Attribute value id
	 * @var int
	 */
	public $id;

	/**
	 * Product attribute id
	 * @var int
	 */
	public $fk_product_attribute;

	/**
	 * Attribute value ref
	 * @var string
	 */
	public $ref;

	/**
	 * Attribute value value
	 * @var string
	 */
	public $value;
        
        
	/**
	 * Attribute value code_couleur
	 * @var string
	 */
	public $code_couleur;
        
	/**
	 * Attribute value valeur_courte
	 * @var string
	 */
	public $valeur_courte;
        
	/**
	 * Attribute value image_couleur
	 * @var string
	 */
	public $image_couleur;
        
	/**
	 * Attribute value type_taille
	 * @var string
	 */
	public $type_taille;
        
        

    /**
     * Constructor
     *
     * @param   DoliDB $db     Database handler
     */
    public function __construct(DoliDB $db)
    {
		global $conf;

		$this->db = $db;
		$this->entity = $conf->entity;
    }

	/**
	 * Gets a product attribute value
	 *
	 * @param int $valueid Product attribute value id
	 * @return int <0 KO, >0 OK
	 */
	public function fetch($valueid)
	{
		$sql = "SELECT rowid, fk_product_attribute, ref, value, code_couleur,valeur_courte, image_couleur, type_taille FROM ".MAIN_DB_PREFIX."product_attribute_value WHERE rowid = ".(int) $valueid." AND entity IN (".getEntity('product').")";

		$query = $this->db->query($sql);

		if (!$query) {
			return -1;
		}

		if (!$this->db->num_rows($query)) {
			return -1;
		}

		$obj = $this->db->fetch_object($query);

		$this->id = $obj->rowid;
		$this->fk_product_attribute = $obj->fk_product_attribute;
		$this->ref = $obj->ref;
		$this->code_couleur = $obj->code_couleur;
		$this->valeur_courte = $obj->valeur_courte;
		$this->image_couleur = $obj->image_couleur;
		$this->type_taille = $obj->type_taille;
		$this->value = $obj->value;

		return 1;
	}
        
        
        /**
	 * Returns all product attribute values of a product attribute
	 *
	 * @param int $prodattr_id Product attribute id
	 * @param bool $only_used Fetch only used attribute values
	 * @return ProductAttributeValue[]
	 */
	public function fetchAllByProductAttributeBackendValue($prodattr_id, $only_used = false)
	{
		$return = array();

		$sql = 'SELECT ';

		if ($only_used) {
			$sql .= 'DISTINCT ';
		}

		$sql .= 'v.fk_product_attribute, v.rowid, v.ref, v.value, v.code_couleur,v.valeur_courte, v.image_couleur, v.type_taille FROM '.MAIN_DB_PREFIX.'product_attribute_value v ';

		if ($only_used) {
			$sql .= 'LEFT JOIN '.MAIN_DB_PREFIX.'product_attribute_combination2val c2v ON c2v.fk_prod_attr_val = v.rowid ';
			$sql .= 'LEFT JOIN '.MAIN_DB_PREFIX.'product_attribute_combination c ON c.rowid = c2v.fk_prod_combination ';
			$sql .= 'LEFT JOIN '.MAIN_DB_PREFIX.'product p ON p.rowid = c.fk_product_child ';
		}

		$sql .= 'WHERE v.fk_product_attribute = '.(int) $prodattr_id;

		if ($only_used) {
			$sql .= ' AND c2v.rowid IS NOT NULL AND p.tosell = 1';
		}
                
               $sql .= ' ORDER BY v.rowid  desc';
                
		$query = $this->db->query($sql);

		while ($result = $this->db->fetch_object($query)) {
			$tmp = new ProductAttributeValue($this->db);
			$tmp->fk_product_attribute = $result->fk_product_attribute;
			$tmp->id = $result->rowid;
			$tmp->ref = $result->ref;
			$tmp->value = $result->value;
                        if($result->code_couleur){
                            $tmp->code_couleur = $result->code_couleur;
                        }
                        if($result->valeur_courte){
                            $tmp->valeur_courte = $result->valeur_courte;
                        }
                        if($result->image_couleur){
                            $tmp->image_couleur = $result->image_couleur;
                        }
                        if($result->type_taille){
                            $tmp->type_taille = $result->type_taille;
                        }
			$return[] = $tmp;
		}

		return $return;
	}
        
        
        
	/**
	 * Returns all product attribute values of a product attribute
	 *
	 * @param int $prodattr_id Product attribute id
	 * @param bool $only_used Fetch only used attribute values
	 * @return ProductAttributeValue[]
	 */
	public function fetchAllByProductAttribute($prodattr_id, $only_used = false)
	{
		$return = array();

		$sql = 'SELECT ';

		if ($only_used) {
			$sql .= 'DISTINCT ';
		}

		$sql .= 'v.fk_product_attribute, v.rowid, v.ref, v.value, v.code_couleur,v.valeur_courte, v.image_couleur, v.type_taille FROM '.MAIN_DB_PREFIX.'product_attribute_value v ';

		if ($only_used) {
			$sql .= 'LEFT JOIN '.MAIN_DB_PREFIX.'product_attribute_combination2val c2v ON c2v.fk_prod_attr_val = v.rowid ';
			$sql .= 'LEFT JOIN '.MAIN_DB_PREFIX.'product_attribute_combination c ON c.rowid = c2v.fk_prod_combination ';
			$sql .= 'LEFT JOIN '.MAIN_DB_PREFIX.'product p ON p.rowid = c.fk_product_child ';
		}

		$sql .= 'WHERE v.fk_product_attribute = '.(int) $prodattr_id;

		if ($only_used) {
			$sql .= ' AND c2v.rowid IS NOT NULL AND p.tosell = 1';
		}
                
                /* traitement taille */
                if((int)$prodattr_id == 2){
                    $sql .= ' ORDER BY  v.type_taille, SUBSTRING(v.value,-3,LENGTH(v.value)) asc ';
                }elseif((int)$prodattr_id == 1){
                    $sql .= ' ORDER BY v.value  asc ';
                }
                
                
		$query = $this->db->query($sql);

		while ($result = $this->db->fetch_object($query)) {
			$tmp = new ProductAttributeValue($this->db);
			$tmp->fk_product_attribute = $result->fk_product_attribute;
			$tmp->id = $result->rowid;
			$tmp->ref = $result->ref;
			$tmp->value = $result->value;
                        if($result->code_couleur){
                            $tmp->code_couleur = $result->code_couleur;
                        }
                        if($result->valeur_courte){
                            $tmp->valeur_courte = $result->valeur_courte;
                        }
                        if($result->image_couleur){
                            $tmp->image_couleur = $result->image_couleur;
                        }
                        if($result->type_taille){
                            $tmp->type_taille = $result->type_taille;
                        }
			$return[] = $tmp;
		}

		return $return;
	}

	/**
	 * Creates a value for a product attribute
	 *
	 * @param	User	$user		Object user
	 * @return 	int 				<0 KO >0 OK
	 */
	public function create(User $user)
	{
		if (!$this->fk_product_attribute) {
			return -1;
		}

		// Ref must be uppercase
		$this->ref = strtoupper($this->ref);

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."product_attribute_value (fk_product_attribute, ref, value, entity, code_couleur,valeur_courte, image_couleur, type_taille)
		VALUES ('".(int) $this->fk_product_attribute."', '".$this->db->escape($this->ref)."',
		'".$this->db->escape($this->value)."', ".(int) $this->entity.", '".$this->db->escape($this->code_couleur)."', '".$this->db->escape($this->valeur_courte)."' , '".$this->db->escape($this->image_couleur)."', '".$this->db->escape($this->type_taille)."')";

		$query = $this->db->query($sql);

		if ($query) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.'product_attribute_value');
			return 1;
		}

		return -1;
	}

	/**
	 * Updates a product attribute value
	 *
	 * @param	User	$user	Object user
	 * @return 	int				<0 if KO, >0 if OK
	 */
	public function update(User $user)
	{
		//Ref must be uppercase
		$this->ref = trim(strtoupper($this->ref));
		$this->value = trim($this->value);

		$sql = "UPDATE ".MAIN_DB_PREFIX."product_attribute_value
		SET fk_product_attribute = '".(int) $this->fk_product_attribute."', ref = '".$this->db->escape($this->ref)."',
		value = '".$this->db->escape($this->value)."', code_couleur = '".$this->db->escape($this->code_couleur)."',valeur_courte='".$this->db->escape($this->valeur_courte)."', image_couleur = '".$this->db->escape($this->image_couleur)."', type_taille ='".$this->db->escape($this->type_taille)."' WHERE rowid = ".(int) $this->id;

		if ($this->db->query($sql)) {
			return 1;
		}

		return -1;
	}

	/**
	 * Deletes a product attribute value
	 *
	 * @return int <0 KO, >0 OK
	 */
	public function delete()
	{
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."product_attribute_value WHERE rowid = ".(int) $this->id;

		if ($this->db->query($sql)) {
			return 1;
		}

		return -1;
	}

	/**
	 * Deletes all product attribute values by a product attribute id
	 *
	 * @param int $fk_attribute Product attribute id
	 * @return int <0 KO, >0 OK
	 */
	public function deleteByFkAttribute($fk_attribute)
	{
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."product_attribute_value WHERE fk_product_attribute = ".(int) $fk_attribute;

		if ($this->db->query($sql)) {
			return 1;
		}

		return -1;
	}
}
