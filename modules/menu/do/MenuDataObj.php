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
 * @version   $Id: MenuDataObj.php 3364 2012-05-31 06:06:21Z rockyswen@gmail.com $
 */

include_once (dirname(__FILE__).'/MenuItemObj.php');

class MenuDataObj extends MetaObject implements iSessionObject{
	public $m_Name;
	public $m_MenuTreeObj;
	public $m_CacheLifeTime;	
	public $m_BreadCrumb=array();

	private $m_RootMenuItem;
	
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
        
    }	
    
    protected function readMetadata(&$xmlArr)
    {
    	parent::readMetaData($xmlArr);
    	$this->m_Name = $this->prefixPackage($this->m_Name);
    	$this->m_CacheLifeTime = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["CACHELIFETIME"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["CACHELIFETIME"] : "0";    	   
    	$this->m_RootMenuItem =  $xmlArr["BIZDATAOBJ"]["MENUITEM"];
    	$this->fetchEntireTree();
    }
    
    public function fetchEntireTree(){
        if($this->m_CacheLifeTime>0)
        {
            $cache_id = md5($this->m_Name);
            //try to process cache service.
            $cacheSvc = BizSystem::getService(CACHE_SERVICE,1);
            $cacheSvc->init($this->m_Name,$this->m_CacheLifeTime);
            if($cacheSvc->test($cache_id))
            {
                BizSystem::log(LOG_DEBUG, "MENU", "Cache Hit. menu dataobj name = ".$this->m_Name);
                $output = $cacheSvc->load($cache_id);
            }
            else
            {
                BizSystem::log(LOG_DEBUG, "MENU", "Set cache. menu dataobj = ".$this->m_Name);
                $xmlArr = $this->m_RootMenuItem;
                $output = new MenuItemObj($xmlArr);                                
                $cacheSvc->save($output, $cache_id);
            }
            $this->m_MenuTreeObj = $output;
        }else{
        	$xmlArr = $this->m_RootMenuItem;
        	$this->m_MenuTreeObj = null;
    		$this->m_MenuTreeObj = new MenuItemObj($xmlArr);
        }
        $this->m_BreadCrumb=array();
        $this->getBreadCrumb();          
    	return $this->m_MenuTreeObj;
    }
    
    public function getBreadCrumb($node=null){
    	if (count($this->m_BreadCrumb)>0)
    		return $this->m_BreadCrumb;
    	$url = $_SERVER['REQUEST_URI'];
    	if($node==null){
    		$node = $this->m_MenuTreeObj;
    	}
    	if($node->m_URL == $_SERVER['REQUEST_URI']){   		    		
    		return "current";    		
    	}
    	elseif($node->m_URL_Match!="")
    	{
    		if(preg_match("@".$node->m_URL_Match."@si", $_SERVER['REQUEST_URI'])){
    			return "current"; 
    		}
    	}
    	else{
    		if(is_array($node->m_ChildNodes)){
    			foreach ($node->m_ChildNodes as $name=>$node){
    				if($this->getBreadCrumb($node) == 'current'){
    					//$node->m_ChildNodes=null;
    					array_unshift($this->m_BreadCrumb,$node);    					
    					return "current";
    				}
    			}
    		}
    	}    	
    }
    

    
    public function fetchTree($start_id, $deep){
        if($this->m_CacheLifeTime>0)
        {
            $cache_id = md5($this->m_Name."-".$start_id."-".$deep);
            //try to process cache service.
            $cacheSvc = BizSystem::getService(CACHE_SERVICE,1);
            $cacheSvc->init($this->m_Name,$this->m_CacheLifeTime);
            if($cacheSvc->test($cache_id))
            {
                BizSystem::log(LOG_DEBUG, "MENU", "Cache Hit. menu fetch tree, name = ".$this->m_Name);
                $output = $cacheSvc->load($cache_id);
            }
            else
            {
                BizSystem::log(LOG_DEBUG, "MENU", "Set cache. menu fetch tree, name = ".$this->m_Name);
                if($start_id!=""){
                	//$this->fetchEntireTree();		
		    		$tree = $this->getTreeByStartID($start_id);
		    	}
		    	$output = $this->cutTree($tree,$deep);                
                $cacheSvc->save($output, $cache_id);
            }
            $tree = $output;
        }else{
    		if($start_id!=""){
    			//$this->fetchEntireTree();    		
	    		$tree = $this->getTreeByStartID($start_id);
	    	}
	    	//$tree = $this->cutTree($tree,$deep);
        }    	

    	return $tree->m_ChildNodes;
    }

    public function fetchTreeByName($start_item, $deep){
		if($this->m_CacheLifeTime>0)
        {
            $cache_id = md5($this->m_Name."-".$start_item."-".$deep);
            //try to process cache service.
            $cacheSvc = BizSystem::getService(CACHE_SERVICE,1);
            $cacheSvc->init($this->m_Name,$this->m_CacheLifeTime);
            if($cacheSvc->test($cache_id))
            {
                BizSystem::log(LOG_DEBUG, "MENU", "Cache Hit. menu fetch tree, name = ".$this->m_Name);
                $output = $cacheSvc->load($cache_id);
            }
            else
            {
                BizSystem::log(LOG_DEBUG, "MENU", "Set cache. menu fetch tree, name = ".$this->m_Name);
                if($start_item!=""){   
                	//$this->fetchEntireTree(); 		
		    		$tree = $this->getTreeByStartItem($start_item);
		    	}		    	
		    	$output = $this->cutTree($tree,$deep);                
                $cacheSvc->save($output, $cache_id);
            }
            $tree = $output;
        }else{
    		if($start_item!=""){   
    			//$this->fetchEntireTree(); 		
	    		$tree = $this->getTreeByStartItem($start_item);
	    	}
	    	$tree = $this->cutTree($tree,$deep);
        }    	

    	return $tree->m_ChildNodes;
    }
        
    protected function getTreeByStartItem($name, $tree = null){
    	if($tree==null)
    	{
    		$tree = $this->m_MenuTreeObj;
    	}
    	if($tree->m_Name==$name){
    		return $tree;
    	}else{
    		if(is_array($tree->m_ChildNodes))
    		{
    			foreach($tree->m_ChildNodes as $tree){
    				$subtree = $this->getTreeByStartItem($name, $tree);
    				if($subtree){
    					return $subtree;
    				}
    			}
    		}
    		
    	}
    }

    protected function getTreeByStartID($id, $tree = null){
    	if($tree==null)
    	{
    		$tree = $this->m_MenuTreeObj;
    	}
    	if($tree->m_Id==$id){
    		return $tree;
    	}else{
    		if(is_array($tree->m_ChildNodes))
    		{
    			foreach($tree->m_ChildNodes as $tree){
    				$subtree = $this->getTreeByStartID($id, $tree);
    				if($subtree){
    					return $subtree;
    				}
    			}
    		}
    		
    	}
    }    
    
    protected function cutTree($tree,$deep=1,$currentDeep=0){
    	
//    		if($currentDeep>=$deep){
//    			$tree->m_ChildNodes = null;
//    			return $tree;
//    		}else{
//	    		
//	    		if(is_array($tree->m_ChildNodes)){
//	    			$currentDeep++;
//		    		foreach($tree->m_ChildNodes as $name=>$subtree){
//		    			$tree->m_ChildNodes[$name] = $this->cutTree($subtree,$deep,$currentDeep);
//		    		}
//	    		}	    		
//    		}
    		return $tree;
    	
    }
    
    protected function prefixPackage($name)
    {
        if ($name && !strpos($name, ".") && ($this->m_Package)) // no package prefix as package.object, add it
            $name = $this->m_Package.".".$name;

        return $name;
    } 
            
	public function setSessionVars($sessCtxt){
		
	}
    public function getSessionVars($sessCtxt){
    	
    }
}

?>