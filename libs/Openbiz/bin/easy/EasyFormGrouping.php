<?php 

/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: EasyFormGrouping.php 1664 2012-02-02 15:33:22Z hellojixian@gmail.com $
 */

class EasyFormGrouping extends EasyForm
{
	protected $m_GroupBy;
	
	protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
    	$this->m_GroupBy = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["GROUPBY"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["GROUPBY"] : null;    
    }
	
    public function fetchDataGroup()
    {
    	//get group list first
    	$dataObj = $this->getDataObj();    	    	

    	if (!$dataObj) return null;
        if ($this->m_RefreshData)
            $dataObj->resetRules();
        else
            $dataObj->clearSearchRule();
        
        if(strpos($this->m_GroupBy,":")){
        	preg_match("/\[(.*?):(.*?)\]/si",$this->m_GroupBy,$match);        	
        	$GroupFieldName = $match[1];
        	$GroupField = $match[2];
	        
        }else{
	        
	        $GroupField = str_replace("[","",$this->m_GroupBy);
	        $GroupField = str_replace("]","",$GroupField);        	
        }
        $GroupSQLRule="GROUP BY [$GroupField]";
        $dataObj->setOtherSQLRule($GroupSQLRule);
        
    	//within each group, search records like before
        QueryStringParam::setBindValues($this->m_SearchRuleBindValues);       

        if ($this->m_FixSearchRule)
        {
            if ($this->m_SearchRule)
                $searchRule = $this->m_SearchRule . " AND " . $this->m_FixSearchRule;
            else
                $searchRule = $this->m_FixSearchRule;
        }
        else
            $searchRule = $this->m_SearchRule;

        $dataObj->setSearchRule($searchRule);
        
        $resultRecords = $dataObj->fetch();
        $this->m_TotalRecords = $dataObj->count();
        if ($this->m_Range && $this->m_Range > 0)
            $this->m_TotalPages = ceil($this->m_TotalRecords/$this->m_Range);
            
        $this->m_TotalPagesBak = $this->m_TotalPages;
        QueryStringParam::ReSet();
        //looping
        $i=0;
        $results = array();
        foreach($resultRecords as $record){
        	if ($this->m_RefreshData)
	            $dataObj->resetRules();
	        else
	            $dataObj->clearSearchRule();
        	QueryStringParam::setBindValues($this->m_SearchRuleBindValues);
        	$group_val = $record[$GroupField];
	        if ($this->m_FixSearchRule)
	        {
	            if ($this->m_SearchRule)
	                $searchRule = $this->m_SearchRule . " AND " . $this->m_FixSearchRule;
	            else
	                $searchRule = $this->m_FixSearchRule;
	        }
	        else
	            $searchRule = $this->m_SearchRule;
			if($group_val){
		        if($searchRule!=""){
					$searchRule = $searchRule." AND [$GroupField]='$group_val'";	
				}else{
					$searchRule = " [$GroupField]='$group_val'";	
				}
			}else{
				if($searchRule!=""){
					$searchRule = $searchRule." AND [$GroupField]  is NULL";	
				}else{
					$searchRule = " [$GroupField] is NULL";	
				}				
			}
			
			$dataObj->setOtherSQLRule("");
			$dataObj->setLimit(0,0);
	        $dataObj->setSearchRule($searchRule); 
	        $resultRecords_grouped = $dataObj->fetch();
	        //renderTable
	        $resultRecords_grouped_table = $this->m_DataPanel->renderTable($resultRecords_grouped);
	        
	        if($record[$GroupField]){
	        	if($GroupFieldName){
	        		$results[$record[$GroupFieldName]] = $resultRecords_grouped_table;
	        	}else{
	        		$results[$record[$GroupField]] = $resultRecords_grouped_table;
	        	}
	        }else{
	        	$results["Empty"] = $resultRecords_grouped_table;
	        }
	       
	        
	        $i++; 	
	        QueryStringParam::ReSet();
        }
        
        //set active records
        $selectedIndex = 0;
        $this->getDataObj()->setActiveRecord($resultRecords[$selectedIndex]);
        return $results;
    }	
    
    public function fetchDataSet(){
    	$this->fetchDataGroup();
    	$resultset = parent::fetchDataSet();
    	$this->m_TotalPages = $this->m_TotalPagesBak;
    	return $resultset; 
    }
    
    public function outputAttrs()
    {
    	$output = parent::outputAttrs();
    	$output['dataGroup'] = $this->fetchDataGroup();
    	return $output;	
    
    }    	
}
?>
