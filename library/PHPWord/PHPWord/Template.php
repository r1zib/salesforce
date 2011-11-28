<?php
/**
 * PHPWord
 *
 * Copyright (c) 2011 PHPWord
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 010 PHPWord
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    Beta 0.6.3, 08.07.2011
 */

/* Modif ed le 21/11/2011
*  Suite au http://phpword.codeplex.com/discussions/232636
*  Permet de supprimer les caractères indésérables pour toutes les chaine commencant par ${
*  Ajout de la méthode setValueList
*/
/**
 * PHPWord_DocumentProperties
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 2009 - 2011 PHPWord (http://www.codeplex.com/PHPWord)
 */



class PHPWord_Template {
    
    /**
     * ZipArchive
     * 
     * @var ZipArchive
     */
    private $_objZip;
    
    /**
     * Temporary Filename
     * 
     * @var string
     */
    private $_tempFileName;
    
    /**
     * Document XML
     * 
     * @var string
     */
    private $_documentXML;
    
    
    /**
     * Create a new Template Object
     * 
     * @param string $strFilename
     */
    public function __construct($strFilename) {
        $path = dirname($strFilename);
        
        $this->_tempFileName = $path.DIRECTORY_SEPARATOR.time().'.docx';
        
        $ret = copy($strFilename, $this->_tempFileName); // Copy the source File to the temp File
		if (!$ret) {
			throw new Exception("Copy Filename");
		}
        
        $this->_objZip = new ZipArchive();
        $this->_objZip->open($this->_tempFileName);
        
        $this->_documentXML = $this->_objZip->getFromName('word/document.xml');
        /* ed le 21/11/2011 suppression des caractères indésirable */
		$this->_documentXML = preg_replace_callback('/(\$\{.*\})/U',"self::striptags", $this->_documentXML );
    }
    
    /**
     * Set a Template value
     * 
     * @param mixed $search
     * @param mixed $replace
     */
    public function setValue($search, $replace) {
        if(substr($search, 0, 2) !== '${' && substr($search, -1) !== '}') {
            $search = '${'.$search.'}';
        }
        
        if(!is_array($replace)) {
        	// on est déjà en utf8
            //$replace = utf8_encode($replace);
        }
        
        $this->_documentXML = str_replace($search, $replace, $this->_documentXML);
    }

    /* setValueList : permet de rajouter 'n' lignes 
     * @param String $search le tag dans le document word ex: ${product}
     * @param Array lstReplace un tableau contenant les éléments
     */
    public function setValueList($search, $lstReplace) {
    	/* On va parser le document pour rajouter des lignes */
    	
    	/* Recherche de la chaine */
    	$dom = new DOMDocument();
    	$dom->loadXML($this->_documentXML);
    	$dom->preserveWhiteSpace = true;
    	$dom->formatOutput = true;
    	
    	if(substr($search, 0, 2) !== '${' && substr($search, -1) !== '}') {
    		$search = '${'.$search.'}';
    	}
    	/* On recheche des textes dans <w:t ...>&{toto}</<w:t> */
    	$listBalise = $dom->getElementsByTagName('t');
		foreach ($listBalise as $balise) {
			$text = $balise->nodeValue;
			if (strpos($text,$search) !== FALSE) {
				$this->maj_list($search, $balise, $lstReplace);
				break;
			}
		}
    
    	$this->_documentXML = $dom->saveXML();
    }
    
    /*
     * Permet de créer une ligne 
     * retroune le noeud crée 
     */
    public function create_p ($node) {
    	/* On se trouve sur la balise w:t
    	 * il faut remonter au w:p puis copier cette balise
    	 */
    	$node_p=$node->parentNode->parentNode;
    	$copie = $node_p->cloneNode(TRUE); // True on va copier aussi les sous arbre
    	/* Insersion juste àpres le noeud */
    	$node_p->parentNode->insertBefore($copie,$node_p);
    	return $copie;  
    } 
    /*
    * Permet de rajouter les n ligne à un noeud
    * @param String élément à remplacer
    * @param Node  la position ou l'on doit insérer la liste
    * @param Array la liste à inserer
    */
    public function maj_list ($search, $node, $list) {
    	
    	$nb = count($list);
    	/* Creation des ligne : */
    	
    	for ($i=1; $i < $nb; $i++) {
    		$this->create_p ($node);
    	} 
    	/* Ajout des valeurs dans les lignes 
    	 * On refait la recherche dans l'arbre pour être sur de l'ordre 
    	 */
    	/* On recheche des textes dans <w:t ...>&{toto}</<w:t> */
    	$listBalise = $node->parentNode->parentNode->parentNode->getElementsByTagName('t');
    	$ind = 0;
    	foreach ($listBalise as $balise) {
    		$text = $balise->nodeValue;
    		if (strpos($text,$search) !== FALSE) {
    			$apres = str_replace($search, $list[$ind],$text);
    			$ind++;
    			$balise->nodeValue =  $apres;
    		}
    	}
    	    	
    }
    
    /*
     * Permet de remplacer une image dans le document 
     * Comment faire l'association entre la numérotation des images dans word et l'image à remplacer ?
     *   On peut utiliser une propriété de l'image en mettant le titre avec le tag ${image_replace}
     *   Dans le fichier word/document.xml 
     * 	<wp:docPr id="2" name="Image 2" title="${image_replace}"/>
     * Puis on récupere l'id et on remplace l'image
     */
    public function remplace_image ($search, $urlImage) {
    	/* recherche des images dans le document 
    	 *  /word/media
    	 */
    	if (!file_exists($urlImage)) {
    		return "RI : pb urlImage.";
    	}
    	
		$name = 'word/media/'.$search;
		
		$info = $this->_objZip->statName($name);
	    // l'image n'est pas trouvé 
		if ($info === FALSE) return "RI : L'image n'est pas présente dans le docx. (".$name.")";

		// image est trouvé, il reste à la remplacer
		$ret = $this->_objZip->deleteName($name);
		if (!$ret) return 'RI pb delete image';
		$ret = $this->_objZip->addFile($urlImage,$name);
		if (!$ret) return 'RI addFile';

		return TRUE;
		    	
	}
    /* Permet de récupérer la taille d'une image dans le docx
     *  -> utile pour retailler l'image de remplacement
     *  @param String $search nom de l'image
     *  @return array an array with 7 elements.
     *    0 -> width  
     *    1 -> height of the image 
     */
	
   function getImageSize($search) {
   		$name = 'word/media/'.$search;
     	$image =$this->_objZip->getFromName($name);
     	/* extraction de l'image du zip */
     	$this->_objZip->extractTo(dirname($this->_tempFileName),$name);
     	$file = dirname($this->_tempFileName).'/'.$name;
     	$info = getimagesize($file);
     	/* suppression de l'image */
     	$ret = unlink($file);
     	
      return $info;
   }
   		
    
    
    /**
     * Save Template
     * 
     * @param string $strFilename
     */
    public function save($strFilename) {
        if(file_exists($strFilename)) {
            unlink($strFilename);
        }
        
        $this->_objZip->addFromString('word/document.xml', $this->_documentXML);
        
        // Close zip file
        if($this->_objZip->close() === false) {
            throw new Exception('Could not close zip file.');
        }
        
        rename($this->_tempFileName, $strFilename);
 }
/* Modif ed le 21/11/2011  */ 

    private static function striptags($matches)
    {
	return strip_tags($matches[1]);
    }


}
?>
