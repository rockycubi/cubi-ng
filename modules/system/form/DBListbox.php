<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: DBListbox.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */

include_once (OPENBIZ_BIN."/easy/element/InputElement.php");
class DBListbox extends InputElement{
    public $m_BlankOption;
   
    /**
     * Read metadata info from metadata array and store to class variable
     *
     * @param array $xmlArr metadata array
     * @return void
     */
    protected function readMetaData($xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_BlankOption = isset($xmlArr["ATTRIBUTES"]["BLANKOPTION"]) ? $xmlArr["ATTRIBUTES"]["BLANKOPTION"] : null;        
    }

    /**
     * Render, draw the control according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $fromList = array();
        $this->getFromList($fromList);
        $valueArray = explode(',', $this->m_Value);
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();

        //$sHTML = "<SELECT NAME=\"" . $this->m_Name . "[]\" ID=\"" . $this->m_Name ."\" $disabledStr $this->m_HTMLAttr $style $func>";
        $sHTML = "<SELECT NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" $disabledStr $this->m_HTMLAttr $style $func>";

        if ($this->m_BlankOption) // ADD a blank option
        {
            $entry = explode(",",$this->m_BlankOption);
            $text = $entry[0];
            $value = ($entry[1]!= "") ? $entry[1] : null;
            $entryList = array(array("val" => $value, "txt" => $text ));
            $fromList = array_merge($entryList, $fromList);
        }

        foreach ($fromList as $option)
        {
            $test = array_search($option['val'], $valueArray);
            if ($test === false)
            {
                $selectedStr = '';
            }
            else
            {
                $selectedStr = "SELECTED";
            }
            $sHTML .= "<OPTION VALUE=\"" . $option['val'] . "\" $selectedStr>" . $option['txt'] . "</OPTION>";
        }
        $sHTML .= "</SELECT>";
        /* editable combobox
        <div style="position: relative;">
        <select style="position: absolute; width: 146px; height: 18px; z-index: 1; clip: rect(auto, auto, auto, 127px);">
        <option value="" selected="selected"/>
        <option value="Homer">Homer</option>
        <option value="Marge">Marge</option>
        <option value="Bart">Bart</option>
        <option value="Lisa">Lisa</option>
        <option value="Maggie">Maggie</option>
        </select>
        <div>
        <input type="text" style="width: 128px; height: 20px;"/>
        </div>
        </div>
        */
        return $sHTML;
    }

 	public function getFromList(&$list)
    {
    	//get DB list from setting
    	$formobj = $this->getFormObj();
    	$rec = $formobj->getActiveRecord();    	
    	$server = BizSystem::clientProxy()->getFormInputs('fld_server')?BizSystem::clientProxy()->getFormInputs('fld_server'):$rec['server'];
    	$port = BizSystem::clientProxy()->getFormInputs('fld_port')?BizSystem::clientProxy()->getFormInputs('fld_port'):$rec['port'];
    	$driver = BizSystem::clientProxy()->getFormInputs('fld_driver')?BizSystem::clientProxy()->getFormInputs('fld_driver'):$rec['driver'];
    	$username = BizSystem::clientProxy()->getFormInputs('fld_username')?BizSystem::clientProxy()->getFormInputs('fld_username'):$rec['username'];
    	$password = BizSystem::clientProxy()->getFormInputs('fld_password')?BizSystem::clientProxy()->getFormInputs('fld_password'):$rec['password'];
    	$charset = 'UTF8';
    	
    	if(!$server)
    	{
    		$server = $rec['SERVER'];	
    		$port 	= $rec['PORT'];
    		$driver	= $rec['DRIVER'];
    		$username= $rec['USER'];
    		$password 	= $rec['PASSWORD'];
    		$charset 	= $rec['CHARSET'];
    	}

    	if(!$driver)
        	return;
        

        switch(strtoupper($driver)){
        	case "PDO_MYSQL":
        		$dbconn = @mysql_connect($server.":".$port,$username,$password);
        		$dblist = @mysql_list_dbs($dbconn);  
        		$i = 0 ;
        		while ($row = @mysql_fetch_array($dblist)){
        			if($row['Database']!='information_schema' && 
        				$row['Database']!='performance_schema'){
	        			$list[$i] = array('val'=>$row['Database'],'txt'=>$row['Database']);
	        			$i++;
        			}
        		}      		
        		break;        	
        }

    }    
    
  
}
?>