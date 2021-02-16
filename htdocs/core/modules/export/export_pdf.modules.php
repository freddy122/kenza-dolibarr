<?php
/* Copyright (C) 2006-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *		\file       htdocs/core/modules/export/export_csv.modules.php
 *		\ingroup    export
 *		\brief      File of class to build exports with CSV format
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/export/modules_export.php';
require_once DOL_DOCUMENT_ROOT.'/includes/dompdf/autoload.inc.php';


// avoid timeout for big export
set_time_limit(0);

/**
 *	Class to build export files with format CSV
 */
class ExportPdf extends ModeleExports
{
	/**
	 * @var string ID ex: csv, tsv, excel...
	 */
	public $id;

	/**
        * @var string export files label
        */
       public $label;

	public $extension;

	/**
        * Dolibarr version of the loaded document
        * @var string
        */
	public $version = 'dolibarr';

	public $label_lib;

	public $version_lib;

	public $separator;

	public $handle; // Handle fichier
        
	public $pdflib; // Handle pdf
        
        public $file; // To save filename
        
        public $htmlData;
        
        public $arrayTitle = [];
       

	/**
	 *	Constructor
	 *
	 *	@param	    DoliDB	$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs;
		$this->db = $db;

		$this->separator = ',';
		if (!empty($conf->global->EXPORT_CSV_SEPARATOR_TO_USE)) $this->separator = $conf->global->EXPORT_CSV_SEPARATOR_TO_USE;
		$this->escape = '"';
		$this->enclosure = '"';

		$this->id = 'pdf'; // Same value then xxx in file name export_xxx.modules.php
		$this->label = 'PDF'; // Label of driver
		$this->desc = "Generation fichier pdf au format .pdf";
		$this->extension = 'pdf'; // Extension for generated file by this driver
		$this->picto = 'mime/other'; // Picto
		$this->version = '1.32'; // Driver version

		// If driver use an external library, put its name here
		$this->label_lib = 'DomPdf';
		$this->version_lib = "1.0.2";
	}

	/**
	 * getDriverId
	 *
	 * @return string
	 */
	public function getDriverId()
	{
		return $this->id;
	}

	/**
	 * getDriverLabel
	 *
	 * @return 	string			Return driver label
	 */
	public function getDriverLabel()
	{
		return $this->label;
	}

	/**
	 * getDriverDesc
	 *
	 * @return string
	 */
	public function getDriverDesc()
	{
		return $this->desc;
	}

	/**
	 * getDriverExtension
	 *
	 * @return string
	 */
	public function getDriverExtension()
	{
		return $this->extension;
	}

	/**
	 * getDriverVersion
	 *
	 * @return string
	 */
	public function getDriverVersion()
	{
		return $this->version;
	}

	/**
	 * getLabelLabel
	 *
	 * @return string
	 */
	public function getLibLabel()
	{
		return $this->label_lib;
	}

	/**
	 * getLibVersion
	 *
	 * @return string
	 */
	public function getLibVersion()
	{
		return $this->version_lib;
	}


    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Open output file
	 *
	 *	@param		string		$file			Path of filename to generate
	 * 	@param		Translate	$outputlangs	Output language object
	 *	@return		int							<0 if KO, >=0 if OK
	 */
	public function open_file($file, $outputlangs)
	{
            // phpcs:enable
            global $langs;

            dol_syslog("ExportPdf::open_file file=".$file);
            //print_r($file);die('aaaa');
            $ret = 1;

            $outputlangs->load("exports");
            $this->file = $file;
            // create new PDF document
            //$this->pdflib = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $this->pdflib = new Dompdf\Dompdf();

            return $ret;
	}

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     * 	Output header into file
     *
     * 	@param		Translate	$outputlangs	Output language object
     * 	@return		int							<0 if KO, >0 if OK
     */
    public function write_header($outputlangs)
    {
    // phpcs:enable
            return 0;
    }


    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * 	Output title line into file
	 *
     *  @param      array		$array_export_fields_label   	Array with list of label of fields
     *  @param      array		$array_selected_sorted       	Array with list of field to export
     *  @param      Translate	$outputlangs    				Object lang to translate values
     *  @param		array		$array_types					Array with types of fields
        * 	@return		int											<0 if KO, >0 if OK
        */
       public function write_title($array_export_fields_label, $array_selected_sorted, $outputlangs, $array_types)
       {
           global $conf;
           $this->htmlData = '
            <html>
                <head>
                    <style>
                        @page {
                           margin: 100px 25px;
                        }
                        header {
                           position: fixed;
                           top: -50px;
                           left: 0px;
                           right: 0px;
                           height: 31px;
                           border-bottom: 2px solid;
                           text-align:center;
                        }
                        footer {
                           position: fixed;
                           bottom: -50px;
                           left: 0px; 
                           right: 0px;
                           height: 31px;
                           border-top: 2px solid;
                           text-align:center;
                        }
                        .page:after { content: counter(page, upper); }
                        #customers {
                           font-family: Arial, Helvetica, sans-serif;
                           font-size: 10px;
                           border-collapse: collapse;
                           width: 100%;
                         }
                         #customers td, #customers th {
                           border: 1px solid #ddd;
                           padding: 8px;
                         }
                         #customers tr:nth-child(even){background-color: #f2f2f2;}
                         #customers tr:hover {background-color: #ddd;}
                         #customers th {
                           padding-top: 12px;
                           padding-bottom: 12px;
                           text-align: left;
                           background-color: #263C5C;
                           color: white;
                         }
                    </style>
                </head>
            <body>
               <header>
                   Export
               </header>
               <footer>
                   <div style="display:inline">
                       <div style="float:left">&copy; Kenza </div>
                       <div class="page"  style="float:left;margin-left:50%">Page '.$PAGE_NUM.'</div>
                   </div>        
               </footer>
               <main>
               <table id="customers">';
           $this->htmlData .= "<tr>";
           foreach ($array_selected_sorted as $code => $value)
           {
               $alias = $array_export_fields_label[$code];
               if($alias !== null || !empty($alias)) {
                   $this->htmlData .= "<th>".$outputlangs->transnoentities($alias)."</th>";
               }
           }
           $this->htmlData .= "</tr>";
           return 0;
       }


    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     *	Output record line into file
     *
     *  @param     	array		$array_selected_sorted      Array with list of field to export
     *  @param     	resource	$objp                       A record from a fetch with all fields from select
     *  @param     	Translate	$outputlangs    			Object lang to translate values
     *  @param		array		$array_types				Array with types of fields
     * 	@return		int										<0 if KO, >0 if OK
    */
    public function write_record($array_selected_sorted, $objp, $outputlangs, $array_types)
    {
        global $conf;
        $reg = array();
        $this->htmlData .= "<tr>";
        foreach ($array_selected_sorted as $code => $value)
        {
            if (strpos($code, ' as ') == 0) $alias = str_replace(array('.', '-', '(', ')'), '_', $code);
            else $alias = substr($code, strpos($code, ' as ') + 4);
            if (empty($alias)) dol_print_error('', 'Bad value for field with code='.$code.'. Try to redefine export.');
            $newvalue = $objp->$alias;
            $this->htmlData .= "<td>".$newvalue."</td>";
        }
        $this->htmlData .= "</tr>";
        return 0;
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     * 	Output footer into file
     *
     * 	@param		Translate	$outputlangs	Output language object
     * 	@return		int							<0 if KO, >0 if OK
     */
    public function write_footer($outputlangs)
    {
    // phpcs:enable
            return 0;
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     * 	Close file handle
     *
     * 	@return		int							<0 if KO, >0 if OK
     */
    public function close_file()
    {
        $this->htmlData .= '</table>
                </main>
                <script type="text/php">
                    if ( isset($pdf) ) { 
                      $font = Font_Metrics::get_font("helvetica", "normal");
                      $size = 9;
                      $y = $pdf->get_height() - 24;
                      $x = $pdf->get_width() - 15 - Font_Metrics::get_text_width("1/1", $font, $size);
                      $pdf->page_text($x, $y, "{PAGE_NUM}/{PAGE_COUNT}", $font, $size);
                    } 
                  </script>
            </body>
        </html>';
        $this->pdflib->loadHtml($this->htmlData);
        $this->pdflib->setPaper('A4', 'portrait');
        $this->pdflib->render();
        $output = $this->pdflib->output();
        file_put_contents($this->file, $output);
        return 0;
    }


    /**
     * Clean a cell to respect rules of CSV file cells
     * Note: It uses $this->separator
     * Note: We keep this function public to be able to test
     *
     * @param 	string	$newvalue	String to clean
     * @param	string	$charset	Input AND Output character set
     * @return 	string				Value cleaned
     */
    public function csvClean($newvalue, $charset)
    {
        global $conf;
        $addquote = 0;


        // Rule Dolibarr: No HTML
        //print $charset.' '.$newvalue."\n";
        //$newvalue=dol_string_nohtmltag($newvalue,0,$charset);
        $newvalue = dol_htmlcleanlastbr($newvalue);
        //print $charset.' '.$newvalue."\n";

        // Rule 1 CSV: No CR, LF in cells (except if USE_STRICT_CSV_RULES is on, we can keep record as it is but we must add quotes)
        $oldvalue = $newvalue;
        $newvalue = str_replace("\r", '', $newvalue);
        $newvalue = str_replace("\n", '\n', $newvalue);
        if (!empty($conf->global->USE_STRICT_CSV_RULES) && $oldvalue != $newvalue)
        {
                // If strict use of CSV rules, we just add quote
                $newvalue = $oldvalue;
                $addquote = 1;
        }

        // Rule 2 CSV: If value contains ", we must escape with ", and add "
        if (preg_match('/"/', $newvalue))
        {
                $addquote = 1;
                $newvalue = str_replace('"', '""', $newvalue);
        }

        // Rule 3 CSV: If value contains separator, we must add "
        if (preg_match('/'.$this->separator.'/', $newvalue))
        {
                $addquote = 1;
        }

        return ($addquote ? '"' : '').$newvalue.($addquote ? '"' : '');
    }
}
