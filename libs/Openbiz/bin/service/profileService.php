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
 * @version   $Id: profileService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * profileService class is the plug-in service for getting user profile
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2003-2009, Rocky Swen
 * @access    public
 */
class profileService
{
    public $role;
    public $roleId;
    public $group;
    public $groupId;
    public $position;
    public $positionId;
    public $division;
    public $divisionId;
    public $org;
    public $orgId;

    /**
     * Initialize profileService
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Get user profile
     *
     * @param string $userId
     * @return array array of user profile
     */
    public function getProfile($userId=null)
    {
        // with the userid, query for role, group, position, division, org
        if (!$userId) return null;

        $profile['USERID'] = $userId;
        
        // just make up something for test. must be replace in real application
        if ($userId == "admin")
            $profile['ROLE'] = 'admin';
        else if ($userId == "member")
            $profile['ROLE'] = 'member';

        $profile['ROLEID'] = 'RLE_2';
        $profile['GROUP'] = 'Member';
        $profile['GROUPID'] = 'GRP_3';
        $profile['POSITION'] = 'Marketing - France';
        $profile['POSITIONID'] = 'PSN_10';
        $profile['DIVISION'] = 'Marketing - Europe';
        $profile['DIVISIONID'] = 'DVN_5';
        $profile['ORG'] = 'IBM';
        $profile['ORGID'] = 'ORG_101';

        return $profile;
    }

    /**
     * Get Database profile
     *
     * @todo not finish
     * @param <type> $userId
     * @param <type> $password
     */
    protected function getDBProfile($userId, $password)
    {
        // CASE 1: simple one table query
        // SELECT role, group, pstn, divn, org FROm user_table AS t1
        // WHERE t1.userid='$userid'

        // CASE 2: intersection table user_pstn (user_role, user_divn, user_org ...), need to query multiple times
        // SELECT t1.pstnid, t2.name FROM user_pstn_table AS t1
        // JOIN pstn_table AS t2 ON t1.pstnid=t2.id
        // WHERE t1.userid='$userid'

        // CASE 3: all hierarchy info contained in one big party table, do query once, then filter on type column
        // SELECT t1.partyid, t2.name, t2.type FROM user_party_table AS t1
        // JOIN party_table AS t2 ON t1.partyid=t2.id
        // WHERE t1.userid='$userid'

        $db = BizSystem::dbConnection();
        $resultSet = $db->execute($sql);
        $sqlArr = $resultSet->fetchRow();
        // process the result
    }

    /**
     * Get attribut
     *
     * @todo NYI (not yet implemented)
     * @param <type> $userId
     * @param <type> $attribut
     * @return void
     */
    public function getAttribute($userId, $attribut)
    {
    }
}

?>