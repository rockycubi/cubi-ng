<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.menu.do
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: MenuItemObj.php 3364 2012-05-31 06:06:21Z rockyswen@gmail.com $
 */

class MenuItemObj
{
 	public $m_Id;
 	public $m_PId;   
    public $m_Name;   
    public $m_Description;
    public $m_URL;
    public $m_URL_Match;
	public $m_Target;
	public $m_CssClass;
	public $m_IconImage;
	public $m_IconCSSClass;
	public $m_ChildNodes = null;
	 
    function __construct($xmlArr, $pid="0")
    {
    	$this->m_PId		 = $pid;
    	$this->readMetadata($xmlArr);
    }
    
    function readMetadata($xmlArr){
        $this->m_Id		 	 = $xmlArr["ATTRIBUTES"]["ID"];        
    	$this->m_Name		 = $xmlArr["ATTRIBUTES"]["NAME"];
        $this->m_Description = $xmlArr["ATTRIBUTES"]["DESCRIPTION"];
        $this->m_URL		 = $xmlArr["ATTRIBUTES"]["URL"];
        $this->m_URL		 = Expression::evaluateExpression($this->m_URL, $this);
        $this->m_URL_Match	 = $xmlArr["ATTRIBUTES"]["URLMATCH"];
        $this->m_Target		 = $xmlArr["ATTRIBUTES"]["TARGET"];        
        $this->m_CssClass		 = $xmlArr["ATTRIBUTES"]["CSSCLASS"];
        $this->m_IconImage		 = $xmlArr["ATTRIBUTES"]["ICONIMAGE"];
        $this->m_IconCSSClass		 = $xmlArr["ATTRIBUTES"]["ICONCSSCLASS"];
        if(is_array($xmlArr["MENUITEM"])){
        	$this->m_ChildNodes = array();
        	if(isset($xmlArr["MENUITEM"]["ATTRIBUTES"])){
        		$this->m_ChildNodes[$xmlArr["MENUITEM"]["ATTRIBUTES"]["ID"]] = new MenuItemObj($xmlArr["MENUITEM"],$this->m_Id);
        	}else{        	
	        	foreach($xmlArr["MENUITEM"] as $menuItem){
	        		$this->m_ChildNodes[$menuItem["ATTRIBUTES"]["ID"]] = new MenuItemObj($menuItem,$this->m_Id);
	        	}
        	}
        }
    	
    }
    
    
    
}
?>