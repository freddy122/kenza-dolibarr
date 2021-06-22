<?php

class SincProdFab
{
    /**
    * @var DoliDB Database handler.
    */
    public $db;
    
    /**
    * Constructor
    *
    * @param		DoliDB		$db      Database handler
    */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * 
     * @return array
     */
    public function getParentProductFab()
    {
        // global $entity;
        $sql = "SELECT rowid, label, ref_fab_frs FROM ".MAIN_DB_PREFIX."product "
                . " WHERE product_type_txt = 'fab' AND POSITION('_' IN ref) = 0  AND entity in (".getEntity('product').") "
                . " ORDER BY rowid desc ";
        $query = $this->db->query($sql);
        $return = array();

        while ($result = $this->db->fetch_object($query)) {
                $return[$result->rowid] = $result->label."( ".$result->ref_fab_frs." )";
        }
        return $return;
    }
    
}

