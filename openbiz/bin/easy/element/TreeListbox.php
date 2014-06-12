<?php 
//include_once ("Listbox.php");
class TreeListbox extends Listbox
{
    public $m_SelectFieldName;

    protected function getDOFromList(&$list, $selectFrom)
    {
        // from Database
        $pos0 = strpos($selectFrom, "[");
        $pos1 = strpos($selectFrom, "]");

        if ($pos0 > 0 && $pos1 > $pos0)
        {  // select from bizObj
            // support BizObjName[BizFieldName] or BizObjName[BizFieldName4Text:BizFieldName4Value]
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
            $this->m_SelectFieldName = $fieldName; 
            $commaPos = strpos($selectFrom, ",", $pos1);
            $commaPos2 = strpos($selectFrom, ",", $commaPos+1);
            
            if ($commaPos > $pos1)
            {
				if($commaPos2){
            		$searchRule = trim(substr($selectFrom, $commaPos + 1, ($commaPos2-$commaPos-1)));
				}
				else
				{
					$searchRule = trim(substr($selectFrom, $commaPos + 1));
				}
            }

            if ($commaPos2 > $commaPos)
                $rootSearchRule = trim(substr($selectFrom, $commaPos2 + 1));
                
            $bizObj = BizSystem::getObject($bizObjName);
            if (!$bizObj)
                return;

            $recList = array();

            $oldAssoc = $bizObj->m_Association;
            $bizObj->m_Association = null;

            if ($searchRule)
            {
                $searchRule = Expression::evaluateExpression($searchRule, $this->getFormObj());
            }
			
            if($rootSearchRule)
            {
            	$rootSearchRule = Expression::evaluateExpression($rootSearchRule, $this->getFormObj());            	
            }else{
            	$rootSearchRule = "[PId]=0 OR [PId]='' OR [PId] is NULL";
            }
            
            $recListTree = $bizObj->fetchTree($rootSearchRule,100,$searchRule);
            $bizObj->m_Association = $oldAssoc;

            if (!$recListTree) return; // bugfix : error if data blank

            foreach($recListTree as $recListTreeNode)
            {
                $this->tree2array($recListTreeNode, $recList);
            }

            foreach ($recList as $rec)
            {
                $list[$i]['val'] = $rec[$fieldName_v];
                $list[$i]['txt'] = $rec[$fieldName];
                $i++;
            }
            return;
        }
    }

    private function tree2array($tree,&$array,$level=0)
    {
        if(!is_array($array))
        {
            $array=array();
        }

        $treeNodeArray = array(
                "Level" => $level,
                "Id" => $tree->m_Id,
                "PId" => $tree->m_PId,
        );
        foreach ($tree->m_Record as $key=>$value)
        {
            $treeNodeArray[$key] = $value;
        }
        $treeNodeArray[$this->m_SelectFieldName] = "+".str_repeat("--", $level).$treeNodeArray[$this->m_SelectFieldName];

        array_push($array, $treeNodeArray);
        $level++;
        if(is_array($tree->m_ChildNodes))
        {
            foreach($tree->m_ChildNodes as $treeNode)
            {
                $this->tree2array($treeNode, $array, $level);
            }
        }
        return $array;
    }
}
?>