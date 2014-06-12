<?PHP
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.service
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: visService.php 3506 2012-06-25 06:32:24Z agus.suhartono@gmail.com $
 */

/**
 * PHPOpenBiz
 *
 * @author     Rocky Swen <rocky@phpopenbiz.org>
 */
class visService
{

    public static function self($userIdField)
    {
        // get current $user_id
        $userProfile = BizSystem::getUserProfile();
        if (!$userProfile)
            return "";
        $userId = $userProfile['Id'];

        // return "[$userIdField] = '$userId'"
        return "[$userIdField]='$userId'";
    }

    public static function group($groupIdField)
    {
        // get current user's group list
        $userProfile = BizSystem::getUserProfile();
        //print_r($userProfile);
        if (!$userProfile || !$userProfile['groups'])
            return "[" . $groupIdField . "] is null";
        $userId = $userProfile['Id'];
        $groupList = implode(",", $userProfile['groups']);

        return "[" . $groupIdField . "] in (" . $groupList . ")";
    }

    public static function self_group($userIdField, $groupIdField)
    {
        $selfRule = self::self($userIdField);
        $groupRule = self::group($groupIdField);

        return "($selfRule OR $groupRule)";
    }

    public static function custom($dataObj)
    {
        
    }

}