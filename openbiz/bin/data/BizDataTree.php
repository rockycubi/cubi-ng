<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.data
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: BizDataTree.php 2585 2010-11-23 18:58:17Z jixian2003 $
 */

//include_once(OPENBIZ_BIN.'data/BizDataObj.php');

/**
 * BizDataTree class provide query for tree structured records
 *
 * @package openbiz.bin.data
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class BizDataTree extends BizDataObj
{
    protected $m_RootNodes;
    /**
     * Deep of tree
     * @var int
     */
    protected $m_Depth;

    /**
     * Global search rule
     * @var string
     */
    protected $m_globalSearchRule;

    /**
     * Fetch records in tree structure
     *
     * @return <type>
     */
    public function fetchTree($rootSearchRule, $depth, $globalSearchRule="")
    {
        $this->m_Depth = $depth;
        $this->m_globalSearchRule = $globalSearchRule;

        // query on given search rule
        $searchRule = "(" . $rootSearchRule . ")";
        if ($globalSearchRule!="")
            $searchRule .= " AND (" . $globalSearchRule . ")";
        $recordList = $this->directFetch($searchRule);
        if (!$recordList)
        {
            $this->m_RootNodes = array();
            return;
        }
        foreach ($recordList as $rec)
        {
            $this->m_RootNodes[] = new NodeRecord($rec);
        }
        if ($this->m_Depth <= 1)
            return $this->m_RootNodes;
        if(is_array($this->m_RootNodes)){
	        foreach ($this->m_RootNodes as $node)
	        {
	            $this->_getChildrenNodes($node, 1);
	        }
        }
        return $this->m_RootNodes;
    }

    /**
     * Fetch node path
     *
     * @param string $nodeSearchRule
     * @param array $pathArray
     * @return <type>
     */
    public function fetchNodePath($nodeSearchRule, &$pathArray)
    {
        $recordList = $this->directFetch($nodeSearchRule);
        if(count($recordList)>=1)
        {

            if($recordList[0]['PId']!='0')
            {
                $searchRule = "[Id]='".$recordList[0]['PId']."'";
                $this->fetchNodePath($searchRule, $pathArray);
            }
            $nodes = new NodeRecord($recordList[0]);
            array_push($pathArray,$nodes);
            return $pathArray;
        }
    }

    /**
     * List all children records of a given record
     *
     * @return void
     */
    private function _getChildrenNodes(&$node, $depth)
    {
        $pid = $node->m_Id;

        $searchRule = "[PId]='$pid'";
        if ($this->m_globalSearchRule!="")
                $searchRule .= " AND " . $this->m_globalSearchRule;
        $recordList = $this->directFetch($searchRule);
        
        foreach ($recordList as $rec)
        {
            $node->m_ChildNodes[] = new NodeRecord($rec);
        }
        
        // reach leave node
        if ($node->m_ChildNodes == null)
            return;

        $depth++;
        // reach given depth
        if ($depth >= $this->m_Depth)
            return;
        else
        {
            foreach ($node->m_ChildNodes as $node_c)
            {
                $this->_getChildrenNodes($node_c, $depth);
            }
        }
    }
}

/**
 * NodeRecord class, for tree structure
 *
 * @package openbiz.bin.data
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @since 1.2
 * @todo need to move to other package (tool, base, etc?)
 * @access public
 *
 */
class NodeRecord
{
    public $m_Id = "";
    public $m_PId = "";
    public $m_ChildNodes = null;
    public $m_Record;

    /**
     * Initialize Node
     *
     * @param array $rec
     * @return void
     */
    function __construct($rec)
    {
        $this->m_Id = $rec['Id'];
        $this->m_PId = $rec['PId'];
        $this->m_Record = $rec;
    }
}


?>
