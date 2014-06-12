<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.service
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: lovService.php 4761 2012-11-16 10:12:30Z hellojixian@gmail.com $
 */

class lovService extends MetaObject
{
	
	public function getDict($selectFrom)
	{
		return $this->getDictionary($selectFrom);
	}
	
    public function getDictionary($selectFrom)
    {
    	$this->m_SelectFrom=$selectFrom;
    	$dict = array();
    	$list = $this->getList($selectFrom);
		if ($list) {
			foreach ($list as $item)
			{
				$dict[$item['val']] = $item['txt'];
			}
		}
    	return $dict;
    }
	
    public function getTextByValue($selectFrom,$value)
    {
    	$dict = $this->getDictionary($selectFrom);
    	return $dict[$value];
    }
    
    public function getList($selectFrom)
    {
    	$list = array();
        if (!$selectFrom) {
            $selectFrom = $this->getSelectFrom();
        }
        if (!$selectFrom) {
        	return $this->getSQLFromList();
        }
        $list = $this->getXMLFromList($selectFrom);
        if ($list != null)
            return $list;
        $list = $this->getDOFromList($selectFrom);
        if ($list != null)
            return $list;
        $list = $this->getSimpleFromList($selectFrom);
        if ($list != null)
            return $list;        
        return;
    }
    	
    protected function getSelectFrom($selectFrom)
    {     
        return Expression::evaluateExpression($selectFrom, null);
    }

    protected function getSelectedList($selectFrom)
    {        
        return Expression::evaluateExpression($selectFrom, null);
    }
    
	protected function getSelectFromSQL($selectFrom)
    {
        return Expression::evaluateExpression($selectFrom, null);
    }	

    
    protected function getXMLFromList($selectFrom)
    {
        $pos0 = strpos($selectFrom, "(");
        $pos1 = strpos($selectFrom, ")");
        if ($pos0>0 && $pos1 > $pos0)
        {  // select from xml file
            $xmlFile = substr($selectFrom, 0, $pos0);
            $tag = substr($selectFrom, $pos0 + 1, $pos1 - $pos0-1);
            $tag = strtoupper($tag);
            $xmlFile = BizSystem::GetXmlFileWithPath ($xmlFile);
            if (!$xmlFile) return false;

            $xmlArr = &BizSystem::getXmlArray($xmlFile);
            if ($xmlArr)
            {
                $i = 0;
                if (!key_exists($tag, $xmlArr["SELECTION"]))
                    return false;
                foreach($xmlArr["SELECTION"][$tag] as $node)
                {
                    $list[$i]['val'] = $node["ATTRIBUTES"]["VALUE"];
                    $list[$i]['pic'] = $node["ATTRIBUTES"]["PICTURE"];
                    if ($node["ATTRIBUTES"]["TEXT"])
                    {
                        $list[$i]['txt'] = $node["ATTRIBUTES"]["TEXT"];                        
                    }
                    else
                    {
                        $list[$i]['txt'] = $list[$i]['val'];
                    }
                    $i++;
                    
                }
                $list = $this->translateList($list, $tag);	// supprot multi-language
            }
            return $list;
        }
        return false;
    }
    
    protected function getDOFromList($selectFrom)
    {
        $pos0 = strpos($selectFrom, "[");
        $pos1 = strpos($selectFrom, "]");
        if ($pos0 > 0 && $pos1 > $pos0)
        {  // select from bizObj
            // support BizObjName[BizFieldName] or 
            // BizObjName[BizFieldName4Text:BizFieldName4Value] or 
            // BizObjName[BizFieldName4Text:BizFieldName4Value:BizFieldName4Pic]
            $bizObjName = substr($selectFrom, 0, $pos0);
            $pos3 = strpos($selectFrom, ":");
            if($pos3 > $pos0 && $pos3 < $pos1)
            {
                $fieldName = substr($selectFrom, $pos0 + 1, $pos3 - $pos0 - 1);
                $fieldName_v = substr($selectFrom, $pos3 + 1, $pos1 - $pos3 - 1);
            }
            else
            {
                $fieldName = substr($selectFrom, $pos0 + 1, $pos1 - $pos0 - 1);
                $fieldName_v = $fieldName;
            }
            $pos4 = strpos($fieldName_v, ":");
            if($pos4){
            	$fieldName_v_mixed = $fieldName_v;
            	$fieldName_v = substr($fieldName_v_mixed,0,$pos4);
            	$fieldName_p = substr($fieldName_v_mixed, $pos4+1, strlen($fieldName_v_mixed)-$pos4-1);
            	unset($fieldName_v_mixed);
            }
            $commaPos = strpos($selectFrom, ",", $pos1);
            if ($commaPos > $pos1)
                $searchRule = trim(substr($selectFrom, $commaPos + 1));
            
            /* @var $bizObj BizDataObj */
            $bizObj = BizSystem::getObject($bizObjName);
            if (!$bizObj)
                return false;

            $recList = array();
            $oldAssoc = $bizObj->m_Association;
            $bizObj->m_Association = null;
            QueryStringParam::reset();
            $recList = $bizObj->directFetch($searchRule);
            $bizObj->m_Association = $oldAssoc;

            foreach ($recList as $rec)
            {
                $list[$i]['val'] = $rec[$fieldName_v];
                $list[$i]['txt'] = $rec[$fieldName];
                $list[$i]['pic'] = $rec[$fieldName_p];
                $i++;
            }
           
            return $list;
        }
        return false;
    }
    
    protected function getSimpleFromList($selectFrom)
    {
        // in case of a|b|c
        if (strpos($selectFrom, "[") > 0 || strpos($selectFrom, "(") > 0)
            return;
        $recList = explode('|',$selectFrom);
        foreach ($recList as $rec)
        {
            $list[$i]['val'] = $rec;
            $list[$i]['txt'] = $rec;
            $list[$i]['pic'] = $rec;
            $i++;
        }
        return $list;
    }
    
    public function getSQLFromList()
    {
    	$sql = $this->getSelectFromSQL();
    	if (!$sql) return;
    	$formObj = $this->getFormObj();
    	$do = $formObj->getDataObj();
    	$db = $do->getDBConnection();
    	try {
    		$resultSet = $db->query($sql);
    		$recList = $resultSet->fetchAll();
	    	foreach ($recList as $rec)
	        {
	            $list[$i]['val'] = $rec[0];
	            $list[$i]['txt'] = isset($rec[1]) ? $rec[1] : $rec[0];
	            $i++;
	        }
    	}
    	catch (Exception $e)
        {
            BizSystem::log(LOG_ERR, "DATAOBJ", "Query Error: ".$e->getMessage());
            $this->m_ErrorMessage = "Error in SQL query: ".$sql.". ".$e->getMessage();
            throw new BDOException($this->m_ErrorMessage);
            return null;
        }
        return $list;
    }
    
    protected function translateList($list, $tag)
    {
    	$module = $this->getModuleName($this->m_SelectFrom);
        if (empty($module))
            $module = $this->getModuleName($this->m_FormName);
    	for ($i=0; $i<count($list); $i++)
    	{
    		$key = 'SELECTION_'.strtoupper($tag).'_'.$i.'_TEXT';
    		$list[$i]['txt'] = I18n::t($list[$i]['txt'], $key, $module, $this->getTransLOVPrefix());
    	}
    	return $list;
    }
    

    protected function getTransLOVPrefix()
    {    	
    	$nameArr = explode(".",$this->m_SelectFrom);
    	for($i=1;$i<count($nameArr)-1;$i++)
    	{
    		$prefix .= strtoupper($nameArr[$i])."_";
    	}    	
    	return $prefix;
    }       

}
?>