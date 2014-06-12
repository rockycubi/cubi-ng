<?PHP
/**
 * PHPOpenBiz
 *
 * @author     Rocky Swen <rocky@phpopenbiz.org>
 * @version    2.3 2009-06-01
 */

define ('DENY', 0);
define ('ALLOW', 1);
define ('ALLOW_OWNER', 2);

class aclService
{
    static protected $role_actionDataObj = "system.do.AclRoleActionDO";
	static protected $_accessMatrix;
	static protected $_defaultAccess = DENY;

    // TODO: conver it to AclService
    // TODO: save the data $userAccesses in session

    // return ALLOW, DENY, ALLOW_OWNER
    public static function allowAccess($res_action)
    {
    	if (!aclService::$_accessMatrix)
        {
            // get the access matrix from session
            aclService::$_accessMatrix = BizSystem::sessionContext()->getVar("_ACCESS_MATRIX");
            if (!aclService::$_accessMatrix || count(aclService::$_accessMatrix) == 0)
            {
                // get user profile
                $profile = BizSystem::getUserProfile();
                if (!$profile)
                    return false; // user not login

                // get the user role id
                $roleIds = $profile['roles'];
                if (!$roleIds)
                    $roleIds[0] = 0; // guest
                $roleId_query = implode (",", $roleIds);

                // generate the access matrix
                
                /* @var $do BizDataObj */
                $do = BizSystem::getObject(aclService::$role_actionDataObj);
                $rs = $do->directFetch("[role_id] in ($roleId_query)");

                if (count($rs)==0)
                    return false;

                aclService::$_accessMatrix = aclService::_generateAccessMatrix($rs);
                BizSystem::sessionContext()->setVar("_ACCESS_MATRIX", aclService::$_accessMatrix);
            }

            $accessLevel = self::$_defaultAccess;	// default is deny
        }

        if (isset(aclService::$_accessMatrix[$res_action]))
            $accessLevel = aclService::$_accessMatrix[$res_action];

        switch ($accessLevel)
        {
            case DENY:  // if access level is DENY, return false
                return false;
            case ALLOW: // if access level is ALLOW or empty, return true
                return true;
            case ALLOW_OWNER:
                // if access level is ALLOW_OWNER, check the OwnerField and OwnerValue.
                // if ownerField's value == ownerValue, return true.
                return true;
        }
    }

    protected static function _generateAccessMatrix($rs)
    {
    	$accessMatrix = array();
        foreach ($rs as $row)
        {
            $resourceAction = $row['resource'].'.'.$row['action'];
            if (!isset($accessMatrix[$resourceAction]))
                $accessMatrix[$resourceAction] = $row['access_level'];
            elseif (isset($accessMatrix[$resourceAction]) && $accessMatrix[$resourceAction] < $row['access_level'])
                $accessMatrix[$resourceAction] = $row['access_level'];
        }
        return $accessMatrix;
    }

    /**
     * Clean ACL cache from session 
     */
    public function clearACLCache() 
    {
		aclService::$_accessMatrix = null;
    	BizSystem::sessionContext()->setVar("_ACCESS_MATRIX", array());
    	BizSystem::sessionContext()->clearVar("_ACCESS_MATRIX");
    }
}