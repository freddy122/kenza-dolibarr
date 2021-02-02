<?php
/*
 * @author 	LVSinformatique <contact@lvsinformatique.com>
 * @copyright  	2014 LVSInformatique
 * This source file is subject to a commercial license from LVSInformatique
 * Use, copy or distribution of this source file without written
 * license agreement from LVSInformatique is strictly forbidden.
 */

include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");

class modcyberoffice extends DolibarrModules
{

   /**
    *   \brief      Constructor. Define names, constants, directories, boxes, permissions
    *   \param      DB      Database handler
    */
    function modcyberoffice ($DB)
    {
        global $langs;
	$this->db = $DB;
	$langs->load("cyberoffice@cyberoffice");
	$this->numero = 171100;
	$this->rights_class = 'cyberoffice';	// Permission key
        $this->family = "technic";
	$this->name = preg_replace('/^mod/i','',get_class($this));
        $this->description = $langs->trans("Synchronization from Prestashop to Dolibarr ERP/CRM");
	$this->version = '1.5.5';
	$this->editor_name = 'LVSInformatique';
	$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
	$this->special = 2;
	$this->picto='cyberoffice@cyberoffice';
	$this->langfiles = array("cyberoffice@cyberoffice");
	$this->module_parts = array();
	$this->triggers = 0;
	$this->dirs = array();
	$r=0;
	$this->config_page_url = array("cyberoffice_setupapage.php@cyberoffice");

	// Dependencies
	$this->depends = array();		// List of modules id that must be enabled if this module is enabled
	$this->depends= array('modCategorie', 'modService');
        $this->requiredby = array();	// List of modules id to disable if this one is disabled
	$this->phpmin = array(5,0);
	$this->need_dolibarr_version = array(3,5);
	$this->tabs = '';
	// Dictionaries
	//$this->dictionaries=array();
        $this->dictionaries=array(
            'langs'=>'cyberoffice@cyberoffice',
            'tabname'=>array(   MAIN_DB_PREFIX."c_cyberoffice",
                                MAIN_DB_PREFIX."c_cyberoffice2"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array(    "cyberoffice_extrafield",
                                "cyberoffice_carrier_warehouse"),													// Label of tables
            'tabsql'=>array(    'SELECT f.rowid as rowid, f.extrafield, f.idpresta, f.active FROM '.MAIN_DB_PREFIX.'c_cyberoffice as f',
                                'SELECT g.rowid as rowid, g.warehouse, g.carrier, g.active FROM '.MAIN_DB_PREFIX.'c_cyberoffice2 as g'),	// Request to select fields
            'tabsqlsort'=>array(    "extrafield ASC",
                                    "warehouse ASC"),																					// Sort order
            'tabfield'=>array("extrafield,idpresta",
                                "warehouse,carrier"),																					// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("extrafield,idpresta",
                                    "warehouse,carrier"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("extrafield,idpresta",
                                    "warehouse,carrier"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array(1,1)												// Condition to show each dictionary
        );
        
	// Boxes
	$this->boxes = array();			// List of boxes
	$r=0;

	// Permissions
	$this->rights = array();		// Permission array used by this module
	
        // Menus
	$this->menu = array();
	$r=0;
    }

    /**
     *	\brief      Function called when module is enabled.
     *	The init function add previous constants, boxes and permissions into Dolibarr database.
     *	It also creates data directories.
     */
      
    function init($options='')
    {	
        global $db,$conf,$langs,$user,$mysoc;
        
        require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
  	require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
  	require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
  	require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
        require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
  	
  		//longdescript, longdescript, text, , 2000, product, 0, 0,, Array
  		/* ====================== extrafiels=======================*/
  		$myextra = new ExtraFields($db);
  		$myextra->addExtraField('longdescript', 'longdescript', 'text',0, 2000, 'product');
  		//$myextra2 = new ExtraFields($db);
  		//$myextra2->addExtraField('cyberprice', 'cyberprice', 'double',0, '24,8', 'product');
  		$form = new Form($db);
  		$num = $form->load_cache_vatrates("'".$mysoc->country_code."'");
  		//print $num;die();
        if ($num > 0)
        {
        	// Definition du taux a pre-selectionner (si defaulttx non force et donc vaut -1 ou '')
        	if ($defaulttx < 0 || dol_strlen($defaulttx) == 0)
        	{
        		if (version_compare(DOL_VERSION, '4.0.0', '>=')) {
					$tmpthirdparty=new Societe($db);
	        		$defaulttx=get_default_tva($mysoc,(is_object($societe_acheteuse)?$societe_acheteuse:$tmpthirdparty),$idprod);
	        		$defaultnpr=get_default_npr($mysoc,(is_object($societe_acheteuse)?$societe_acheteuse:$tmpthirdparty),$idprod);
	        		if (empty($defaulttx)) $defaultnpr=0;
				} else {
					$defaulttx=get_default_tva($mysoc,'',$idprod);
        			$defaultnpr=get_default_npr($mysoc,'',$idprod);
				}
        	}

        	// Si taux par defaut n'a pu etre determine, on prend dernier de la liste.
        	// Comme ils sont tries par ordre croissant, dernier = plus eleve = taux courant
        	if ($defaulttx < 0 || dol_strlen($defaulttx) == 0 || $defaulttx == 0)
        	{
        		if (empty($conf->global->MAIN_VAT_DEFAULT_IF_AUTODETECT_FAILS)) $defaulttx = $form->cache_vatrates[$num-1]['txtva'];
        		else $defaulttx=$conf->global->MAIN_VAT_DEFAULT_IF_AUTODETECT_FAILS;
        	}
		}
		//@chmod('../images_temp', octdec(0777));
		//print $defaulttx;die();
  		$produit = new Product($db);
  		$produit->price_base_type 	= 'HT';
		$produit->price				= 0;
		$produit->price_ttc 		= 0;
		$produit->tva_tx			= $defaulttx;
		if (version_compare(DOL_VERSION, '3.8.0', '<'))
				$produit->libelle				= 'PrestaShipping';
		else
				$produit->label				= 'PrestaShipping';
		$produit->description		= 'PrestaShipping';
		$produit->type				= 1;
		$produit->status			= 1;
		$produit->status_buy		= 1;
		$produit->ref				= 'PrestaShipping';
		//barcode
		//empty($code) && $this->code_null
		dol_syslog("Cyberoffice::produit->createPrestaShipping");
  		$results = $produit->create($user);
  		if ($results > 0) 
  			dolibarr_set_const($db, 'CYBEROFFICE_SHIPPING', $results, 'chaine', 0, '', $conf->entity);
                else 
  		{
  			if ($produit->fetch('','PrestaShipping') > 0) {
                                $results = $produit->id;
  				dolibarr_set_const($db, 'CYBEROFFICE_SHIPPING', $produit->id, 'chaine', 0, '', $conf->entity);
                        }
  		}
  		dol_syslog("Cyberoffice::produit->createPrestaShipping ".$results );
		
		$produitD = new Product($db);
  		$produitD->price_base_type 	= 'HT';
		$produitD->price				= 0;
		$produitD->price_ttc 		= 0;
		$produitD->tva_tx			= $defaulttx;
		if (version_compare(DOL_VERSION, '3.8.0', '<'))
				$produitD->libelle				= 'PrestaDiscount';
		else
				$produitD->label				= 'PrestaDiscount';
		$produitD->description		= 'PrestaDiscount';
		$produitD->type				= 1;
		$produitD->status			= 1;
		$produitD->status_buy		= 1;
		$produitD->ref				= 'PrestaDiscount';
		dol_syslog("Cyberoffice::produit->createPrestaDiscount ");
  		$resultd = $produitD->create($user);
  		if ($resultd > 0) 
  			dolibarr_set_const($db, 'CYBEROFFICE_DISCOUNT', $resultd, 'chaine', 0, '', $conf->entity);
  		else 
  		{
  			if ($produitD->fetch('','PrestaDiscount') > 0) {
                            $resultd = $produitD->id;
  				dolibarr_set_const($db, 'CYBEROFFICE_DISCOUNT', $produitD->id, 'chaine', 0, '', $conf->entity);
                        }
  		}
  		dol_syslog("Cyberoffice::produit->createPrestaDiscount ".$resultd );
                
                $produitW = new Product($db);
  		$produitW->price_base_type 	= 'HT';
		$produitW->price				= 0;
		$produitW->price_ttc 		= 0;
		$produitW->tva_tx			= $defaulttx;
		if (version_compare(DOL_VERSION, '3.8.0', '<'))
				$produitW->libelle				= 'Prestawrapping';
		else
				$produitW->label				= 'Prestawrapping';
		$produitW->description		= 'Prestawrapping';
		$produitW->type				= 1;
		$produitW->status			= 1;
		$produitW->status_buy		= 1;
		$produitW->ref				= 'Prestawrapping';
		dol_syslog("Cyberoffice::produit->createPrestawrapping ");
  		$resultw = $produitW->create($user);
  		if ($resultw > 0) 
  			dolibarr_set_const($db, 'CYBEROFFICE_wrapping', $resultw, 'chaine', 0, '', $conf->entity);
  		else 
  		{
  			if ($produitW->fetch('','Prestawrapping') > 0) {
                            $resultw = $produitW->id;
  				dolibarr_set_const($db, 'CYBEROFFICE_wrapping', $produitW->id, 'chaine', 0, '', $conf->entity);
                        }
  		}
  		dol_syslog("Cyberoffice::produit->createPrestawrapping ".$resultw );
                
  		//print $resultd;die();
		$this->const = array(
			0=>array('CYBEROFFICE_SHIPPING','chaine',$results,'service\'s rowid',1, 'current'),
		    1=>array('CYBEROFFICE_DISCOUNT','chaine',$resultd,'service\'s rowid',1, 'current'),
		    2=>array('CYBEROFFICE_invoice','chaine',1,'Invoice Synchronization',1, 'current'),
		    3=>array('CYBEROFFICE_stock','chaine',1,'Stock Synchronization',1, 'current'),
		    4=>array('CYBEROFFICE_chanel','chaine',5,'number of chanels',1, 'current'),
                    5=>array('CYBEROFFICE_wrapping','chaine',$resultw,'service\'s rowid',1, 'current'),
		    );
                    
    	$sql = array();
	$sql=array(array('sql' => "ALTER TABLE ".MAIN_DB_PREFIX."c_paiement ADD COLUMN cyberbank INTEGER DEFAULT NULL;", 'ignoreerror' => 1));
	$result=$this->load_tables();
        ////extrafield
        foreach($myextra->fetch_name_optionals_label('product') as $key => $value)
        {
            $mysql = "INSERT INTO ".MAIN_DB_PREFIX."c_cyberoffice (extrafield) VALUES ('".$key."')";
            if ($key != 'longdescript') 
                $resql = $db->query($mysql);
        }
        
        $entrepot = new Entrepot($db);
        foreach($entrepot->list_array() as $key => $value)
        {
            $mysql = "INSERT INTO ".MAIN_DB_PREFIX."c_cyberoffice2 (warehouse, carrier) VALUES (".$key.",0)";
            $resql = $db->query($mysql);
        }
	return $this->_init($sql, $options);
    }

	/**
	 *		\brief		Function called when module is disabled.
 	 *              	Remove from database constants, boxes and permissions from Dolibarr database.
 	 *					Data directories are not deleted.
 	 */
	function remove($options='')
	{
		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
  		global $db,$conf,$langs,$user,$mysoc;
  		
  		dol_syslog("Cyberoffice::myextra->delete");
  		$myextra = new ExtraFields($db);
  		$resultExtra=$myextra->delete('longdescript','product');
		$resultExtra=$myextra->delete('cyberprice','product');
		
  		/*$produitS = new Product($db);
		if($produitS->fetch('','PrestaShipping') > 0) {
			if (DOL_VERSION < '6.0.0') $res = $produitS->delete($produitS->id);
				else $res = $produitS->delete($user);
			dol_syslog("Cyberoffice::produit->delete ".$produitS->id.' ' . $res);
			if ($res > 0) dolibarr_del_const($db, 'CYBEROFFICE_SHIPPING', -1);
				else dolibarr_set_const($db, 'CYBEROFFICE_SHIPPING', $produitS->id);
		}
		$produitD = new Product($db);
		if($produitD->fetch('','PrestaDiscount') > 0) {
			if (DOL_VERSION < '6.0.0') $res = $produitD->delete($produitD->id);
			else $res = $produitD->delete($user);
			dol_syslog("Cyberoffice::produit->delete ".$produitD->id.' ' . $res);
			if ($res > 0) dolibarr_del_const($db, 'CYBEROFFICE_DISCOUNT', -1);
				else dolibarr_set_const($db, 'CYBEROFFICE_DISCOUNT', $produitD->id);
		}*/
		$sql = array();

		return $this->_remove($sql, $options);
	}
	function load_tables()
	{
		// ALTER TABLE llx_c_ziptown ADD COLUMN fk_pays integer NOT NULL DEFAULT 0 after fk_county; -->
		return $this->_load_tables('/cyberoffice/sql/');
	}
}
?>