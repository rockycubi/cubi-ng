<?PHP
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
 * @version   $Id: EasyFormTree.php 2606 2010-11-25 07:51:59Z mr_a_ton $
 */

/**
 * EasyFormTree class - contains formtree object metadata functions
 *
 * @package openbiz.bin.easy
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @since 1.2
 * @access public
 */
class EasyFormTree extends EasyForm
{
	public $m_TitleField;
	public $m_RootSearchRule;
    public $m_TreeDepth;
    
	protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_TitleField = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["TITLEFIELD"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["TITLEFIELD"] : "title";
        $this->m_RootSearchRule = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["ROOTSEARCHRULE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["ROOTSEARCHRULE"] : null;
        $this->m_TreeDepth = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["TREEDEPTH"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["TREEDEPTH"] : 10;
    }
    
   public function fetchDataSet()
    {
        
        $dataObj = $this->getDataObj();
        if (!$dataObj) return null;
        
        QueryStringParam::setBindValues($this->m_SearchRuleBindValues);

        if ($this->m_RefreshData)
            $dataObj->resetRules();
        else
            $dataObj->clearSearchRule();

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
        if($this->m_StartItem>1)
        {
            $dataObj->setLimit($this->m_Range, $this->m_StartItem);
        }
        else
        {
            $dataObj->setLimit($this->m_Range, ($this->m_CurrentPage-1)*$this->m_Range);
        }
        //$resultRecords = $dataObj->fetch();
        
        $resultRecordTree = $dataObj->fetchTree($this->m_RootSearchRule,$this->m_TreeDepth);
        if(is_array($resultRecordTree)){
	        foreach ($resultRecordTree as $resultRecordTreeNode){
	        	$this->tree2array($resultRecordTreeNode, $resultRecords);
	        }
        }
        $this->m_TotalRecords = $dataObj->count();
        if ($this->m_Range && $this->m_Range > 0)
            $this->m_TotalPages = ceil($this->m_TotalRecords/$this->m_Range);
        $selectedIndex = 0;
        $this->getDataObj()->setActiveRecord($resultRecords[$selectedIndex]);

        QueryStringParam::ReSet();

        return $resultRecords;
    }    
    
    private function tree2array($tree,&$array,$level=0){
    	if(!is_array($array)){
    		$array=array();
    	}
    	
    	$treeNodeArray = array(
    		"Level" => $level,
    		"Id" => $tree->m_Id,
    		"PId" => $tree->m_PId,
    	);
    	foreach ($tree->m_Record as $key=>$value){
    		$treeNodeArray[$key] = $value;    		
    	}
    	$treeNodeArray[$this->m_TitleField] = "+ ".str_repeat("- - - -", $level)." ".$treeNodeArray[$this->m_TitleField];
    	
    	array_push($array, $treeNodeArray);
    	$level++;   
    	if(is_array($tree->m_ChildNodes)){    		
    		foreach($tree->m_ChildNodes as $treeNode){    			
    			$this->tree2array($treeNode, $array, $level);    			    			
    		}    		
    	}
    	return $array;
    }
}
?>