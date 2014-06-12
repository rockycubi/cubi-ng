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
 * @version   $Id: accessService.php 3485 2012-06-18 23:41:37Z agus.suhartono@gmail.com $
 */

/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   cubi.modules.service
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: accessService.php 3485 2012-06-18 23:41:37Z agus.suhartono@gmail.com $
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
 * @package   baseapp.modules.service
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
        if ($role != null)
            $roles[] = $role;
        else
        {
            //global $g_BizSystem;
            $bizSystem = BizSystem::instance();
            // TODO: get user profile
            $userProfile = $bizSystem->getUserProfile();
            //print_r($profile);
            if ($userProfile && isset($userProfile['roleNames']))
                $roles = $userProfile['roleNames'];
            else
                $roles[] = "";
        }

        $view = $this->getMatchView($viewName);
        if (!$view)
            return true;

        $roleList = $view->getRoleList();
        if (!$roleList)
            return true;
        foreach ($roles as $r)
        {
            if ($roleList->get($r))
                return true;
        }

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
        $viewobj = $this->_restrictedViewList->get($viewName);
        if ($viewobj)
            return $viewobj;
        foreach ($this->_restrictedViewList as $view => $viewobj)
        {
            $preg_view = "/".$view."/";
            if (preg_match($preg_view, $viewName))
            {
                return $viewobj;
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
 * @package   cubi.modules.service
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