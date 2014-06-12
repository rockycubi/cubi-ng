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
 * @version   $Id: MenuTreeDO.php 5440 2014-05-07 06:02:48Z rockyswen@gmail.com $
 */

include_once(OPENBIZ_BIN.'data/BizDataObj.php');
include_once(OPENBIZ_BIN.'data/private/BizDataObj_SQLHelper.php');
include_once('MenuRecord.php');

/**
 * BizDataTree class provide query for tree structured records
 *
 * @package openbiz.bin.data
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class MenuTreeDO extends BizDataObj
{
	protected $rootNodes;
	protected $depth;
	static protected $m_BreadCrumb = null; 
	static protected $fullMenuTree = null; 

	public function fetchTree($rootSearchRule, $depth)
	{
		return $this->fetchTreeBySearchRule($rootSearchRule, $depth);
	}
	
	public function fetchTreeByName($menuName, $depth)
	{
		return $this->fetchTreeBySearchRule("[name]='$menuName'", $depth);
	}
	
	public function fetchTreeByQueryParams($rootSearchRule, $depth)
	{
		$queryRules = array();
		foreach ($rootSearchRule as $fieldName=>$value) {
			$queryRules[] = queryParamToRule($fieldName, $value, $this);
		}
		$searchRule = implode(' AND ', $queryRules);
		return $this->fetchTreeBySearchRule($searchRule, $depth);
	}
	
	/**
     * Fetch records in tree structure
     *
     * @return <type>
     */
	public function fetchTreeBySearchRule($rootSearchRule, $depth, $globalSearchRule=null )
	{
		$url = $_SERVER['REQUEST_URI'];
		$this->depth = $depth;
		// query on given search rule
		if($globalSearchRule){
			$searchRule = $rootSearchRule." AND ".$globalSearchRule;
		}else{
			$searchRule = $rootSearchRule;
		}
        $recordList = $this->directFetch($searchRule);
        if (!$recordList)
        {
            $this->rootNodes = array();
            return;
        }
        $i = 0;
        foreach ($recordList as $rec)
        {
            $menuRec = new MenuRecord($rec);
			// hack - not to check access for rest service
            if (1 || $menuRec->allowAccess()) {  // check access with role
                $this->rootNodes[$i] = $menuRec;
                if ($this->rootNodes[$i]->m_URL == $url)
                    $this->rootNodes[$i]->m_Current = 1;
                $i++;
            }
        }
        if ($this->depth <= 1)
            return $this->rootNodes;
        if (!$this->rootNodes) return array();
        foreach ($this->rootNodes as $node)
        {
            $this->_getChildrenNodes($node, 1, $globalSearchRule);
        }
        //print_r($this->rootNodes);
        return $this->rootNodes;
    }

    public function fetchNodePath($nodeSearchRule, &$pathArray)
    {
    	//echo "fetchNodePath($nodeSearchRule)";
		$recordList = $this->directFetch($nodeSearchRule);
		//print_r($recordList); exit;
    	if(count($recordList)>=1){
			$i=0;
    		// find the record whose parent are not empty
			if (count($recordList)>1) {
				for ($i=0; $i<count($recordList); $i++) {
					if ($recordList[$i]['PId']!='') break;
				}
			}
    		if($recordList[$i]['PId']!='' && $recordList[$i]['PId']!='0'){
    			$searchRule = "[Id]='".$recordList[$i]['PId']."'";
    			$this->fetchNodePath($searchRule, $pathArray);
    		}
    		$node = new MenuRecord($recordList[$i]);
    		array_push ($pathArray,$node);
    		return $pathArray;
    	}
    }
    
	public function getBreadCrumb($uri=null)
	{
    	if (self::$m_BreadCrumb != null)
    		return self::$m_BreadCrumb;
    	
    	self::$m_BreadCrumb = array();
		if (!$uri) $uri = $_SERVER['REQUEST_URI'];
		if (empty($uri))
			return array();
    	$matchUri = $this->_getMatchUri($uri);
    	$uri = str_replace("//","/",str_replace(APP_INDEX,'',$uri));
    	
    	$pathArray = array();
    	//global $g_BizSystem;
    	//$currentView = $g_BizSystem->getCurrentViewName();
		//$this->fetchNodePath("[link]='$uri' OR [view]='$currentView'", $pathArray);

    	// first find the exact uri match
    	$this->fetchNodePath("[link]='$uri'", $pathArray);
    	if (count($pathArray)>0) {
    		self::$m_BreadCrumb = $pathArray;
    		return $pathArray;
    	}

    	// then find partial match uri
		$this->fetchNodePath("[url_match] LIKE '%$matchUri%'", $pathArray);	
		if (count($pathArray)>0) {	
			self::$m_BreadCrumb = $pathArray;
			return $pathArray;
		}
    	
    	// then find partial match uri
		$this->fetchNodePath("[link] LIKE '%$matchUri%'", $pathArray);		
		self::$m_BreadCrumb = $pathArray;
		return $pathArray;
    }
    
    private function _getMatchUri($uri)
    {
    	$matchUri = str_replace(APP_INDEX,'',$uri);
    	// only count first 2 parts
    	$_matchUris = explode('/',$matchUri);
    	if (count($_matchUris)>=2) {
    		if ($_matchUris[0]=='')
    			if (count($_matchUris)>=3)
    				$matchUri = '/'.$_matchUris[1].'/'.$_matchUris[2];
    		else
    			$matchUri = $_matchUris[0].'/'.$_matchUris[1];
    	}
    	return $matchUri;
    }
    
    /**
     * List all children records of a given record
     *
     * @return void
     */
    private function _getChildrenNodes(&$node, $depth, $globalSearchRule=null)
    {
        $url = $_SERVER['REQUEST_URI'];
    	$pid = $node->m_Id;
        //echo "<br>in _getChildrenNodes";
        if($globalSearchRule){
        	$searchRule = "[PId]='$pid' AND $globalSearchRule";
        }else{
    		$searchRule = "[PId]='$pid'";
        }
        $recordList = $this->directFetch($searchRule);
        $i = 0;
        foreach ($recordList as $rec)
        {
            // TODO: check access with role
            $menuRec = new MenuRecord($rec);
            if ($menuRec->allowAccess()) {
                $node->m_ChildNodes[$i] = $menuRec;
                $i++;
            }
        }
        //print_r($node->m_ChildNodes);
        // reach leave node
        if ($node->m_ChildNodes == null) {
            return;
        }
        $depth++;
        // reach given depth
        if ($depth >= $this->depth)
            return;
        else
        {
            foreach ($node->m_ChildNodes as $node_c)
            {
                $this->_getChildrenNodes($node_c, $depth, $globalSearchRule);
            }
        }
    }
    
    public function recordCount($sql)
    {
    	$counter = 0;
    	$rs = $this->directFetch($sql);
    	foreach($rs as $record)
    	{
    		$access = $record['access'];
    		if(empty($access) || BizSystem::allowUserAccess($access))
    		{
				$counter++;
    		}
    	}
    	return $counter;
    }
	
	public function directFetch($searchRule="", $count=-1, $offset=0,$sortRule="")
	{
		//return parent::directFetch($searchRule);
		// use menu tree cache
		$this->loadFullMenuTree();
		
		// search menu tree
		$searchRule = str_replace(' = ','=',$searchRule);
		if (!preg_match_all("/\[([a-zA-Z0-9_]+)\]=([^ ]+)/",$searchRule,$m)) {
			return parent::directFetch($searchRule);
		}
		//echo "MenuTreeDO search rule is $searchRule";
		//print_r($m); exit;
		$n = count($m[1]);
		$hasPId = 0;
		$keyvals = array();
		for($i=0; $i<$n; $i++) {
			if ($m[1][$i]=='PId'){ $hasPId=1; $PId = str_replace("'","",$m[2][$i]); }
			else $keyvals[$m[1][$i]] = str_replace("'","",$m[2][$i]);
		}
		if (!$hasPId) {
			return parent::directFetch($searchRule);
		}
		if (!$PId) $PId = "__root__";
		$menuItemIds = self::$fullMenuTree[$PId]['children'];
		$rs = array();
		if (empty($menuItemIds)) return $rs;
		foreach ($menuItemIds as $mId) {
			$rec = self::$fullMenuTree[$mId];
			$matched = true;
			foreach ($keyvals as $k=>$v) {
				if ($rec[$k] != $v) { $matched = false; break; }
			}
			if ($matched) $rs[] = self::$fullMenuTree[$mId];
		}
		//print_r($rs);
		return $rs;
	}
	
	protected function loadFullMenuTree()
	{
		if (self::$fullMenuTree != null) return;
		$cache_id = 'FULL_MENU_LIST';
		$cacheSvc = BizSystem::getService(CACHE_SERVICE,1);
		$cacheSvc->init($this->m_Name, 600);	// cache for 10 mins
		if($cacheSvc->test($cache_id))
		{
			self::$fullMenuTree = $cacheSvc->load($cache_id);
			return;
		}
		$rs = parent::directFetch();
		foreach($rs as $record)
		{
			if (empty($record['PId'])) $record['PId'] = "__root__";
			unset($record['create_by']);
			unset($record['create_time']);
			unset($record['update_by']);
			unset($record['update_time']);
			unset($record['name']); unset($record['parent']);
			self::$fullMenuTree[$record['Id']] = $record;
		}
		foreach(self::$fullMenuTree as $mId=>$record)
		{
			self::$fullMenuTree[$record['PId']]['children'][] = $mId;
		}
		//print_r(self::$fullMenuTree);
		$cacheSvc->save(self::$fullMenuTree,$cache_id);
		// put it in apc or file cache
	}
}

?>