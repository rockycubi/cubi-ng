<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.service
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: accessService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/* configuration
<PluginService ...>
   <access-constraint>
     <view-collection>
       <view name="view1">
         <role name="admin"/>
         <role name="member"/>
       </view>
       <view name="reg_expr"/>
     </view-collection>
   </access-constraint>
</PluginService ...>
*/

/**
 * accessService class is the plug-in service of handling role based view access control
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class accessService
{
    private $_configFile = "accessService.xml";
    private $_restrictedViewList;

    /**
     * Initialize accessService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
    }

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetadata(&$xmlArr)
    {
        $viewCollection = $xmlArr["PLUGINSERVICE"]["ACCESS-CONSTRAINT"]["VIEW-COLLECTION"];
        $this->_restrictedViewList = new MetaIterator($xmlArr["PLUGINSERVICE"]["ACCESS-CONSTRAINT"]["VIEW-COLLECTION"]["VIEW"],"RestrictedView");
    }

    /**
     * Allow view access
     *
     * @param string $viewName
     * @param <type> $role
     * @return boolean
     */
    public function allowViewAccess($viewName, $role=null)
    {
        if (!$role)
            $role = "";

        $view = $this->getMatchView($viewName);
        if (!$view)
            return true;

        $roleList = $view->getRoleList();
        if (!$roleList)
            return true;
        if ($roleList->get($role))
            return true;

        return false;
    }

    /**
     * Get match view
     *
     * @param string $viewName
     * @return EasyView|null
     */
    protected function getMatchView($viewName)
    {
        /* @var $viewObj EasyView */
        $viewObj = $this->_restrictedViewList->get($viewName);
        if ($viewObj)
            return $viewObj;
        foreach ($this->_restrictedViewList as $view => $viewObj)
        {
            $preg_view = "/".$view."/";
            if (preg_match($preg_view, $viewName))
            {
                return $viewObj;
            }
        }
        return null;
    }
}

/**
 * RestrictedView class
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class RestrictedView
{
    /**
     * Name of view
     *
     * @var string
     */
    public $m_Name;

    /**
     * List of role
     *
     * @var MetaIterator
     */
    private $_roleList;


    /**
     * Initialize RestrictedView with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    public function __construct($xmlArr)
    {
        $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
        $this->_roleList = new MetaIterator($xmlArr["ROLE"],"RestrictedRole");
    }

    /**
     * Get view name
     *
     * @return string the view name
     */
    public function getViewName()
    {
        return $this->m_Name;
    }

    /**
     * Get list of role
     *
     * @return MetaIterator
     */
    public function getRoleList()
    {
        return $this->_roleList;
    }
}


/**
 * RestrictedRole class
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class RestrictedRole
{
    /**
     * Role name
     *
     * @var string
     */
    public $m_Name;

    /**
     * Initialize RestrictedView with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    public function __construct($xmlArr)
    {
        $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
    }

    /**
     * Get role name
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->m_Name;
    }
}
?>