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
 * @version   $Id: profileService.php 5526 2014-06-12 06:44:11Z rockyswen@gmail.com $
 */

/**
 * profileService is class that handle user profile information.
 * this service accessed by BizSystem::getService( PROFILE_SERVICE ),
 * example :
 * <code>
 *      $profileService = BizSystem::getService( PROFILE_SERVICE );
 *      $profileName = $profileService->GetProfileName( $accountId, $type );
 * </code> 
 */
class profileService
{

    protected $m_Name = "ProfileService";
    protected $m_Profile;
    protected $m_profileObj = "contact.do.ContactSystemDO";
    protected $m_contactObj = "contact.do.ContactSystemDO";
    protected $m_userDataObj = "system.do.UserSystemDO";
    protected $m_groupDataObj = "system.do.GroupDO";
    protected $m_user_roleDataObj = "system.do.UserRoleDO";
    protected $m_user_groupDataObj = "system.do.UserGroupDO";

    public function __construct(&$xmlArr)
    {
        //$this->readMetadata($xmlArr);
    }

    protected function readMetaData($xmlArr)
    {
        //$this->m_profileObj = $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"];
    }

    /**
     * Initialize user profile
     * 
     * @param type $userName
     * @return type 
     */
    public function initProfile($userName)
    {
        //clear ACL Cache
        BizSystem::getService(ACL_SERVICE)->clearACLCache();

        $this->m_Profile = $this->initDBProfile($userName);
        BizSystem::sessionContext()->setVar("_USER_PROFILE", $this->m_Profile);

        return $this->m_Profile;
    }

    public function getProfile($attribute = null)
    {
        if (!$this->m_Profile)
        {
            $this->m_Profile = BizSystem::sessionContext()->getVar("_USER_PROFILE");
        }
        if (!$this->m_Profile)
        {
            $this->getProfileByCookie();
            if (!$this->m_Profile)
                return null;
        }

        if ($attribute)
        {
            if (isset($this->m_Profile[$attribute]))
            {
                return $this->m_Profile[$attribute];
            } else
            {
                return null;
            }
        }

        return $this->m_Profile;
    }

    /**
     * Set user profile
     * 
     * @param type $profile 
     */
    public function setProfile($profile)
    {
        $this->m_Profile = $profile;
    }

    /**
     * Create user profile
     * 
     * @param type $userId
     * @return type 
     */
    public function createProfile($userId = null)
    {
        if (!$userId)
        {
            $userId = $this->getProfile("Id");
        }

        $profileDo = BizSystem::getObject($this->m_profileObj, 1);
        $userInfo = BizSystem::getObject($this->m_userDataObj, 1)->fetchById($userId);
        $profileArray = array(
            "first_name" => $userInfo['username'],
            "last_name" => $userInfo['username'],
            "display_name" => $userInfo['username'],
            "fast_index" => substr(strtolower($userInfo['username']), 0, 1),
            "email" => $userInfo['email'],
            "company" => "N/A",
            "user_id" => $userId,
        	"owner_id" => $userId,
            "group_perm" => '1',
            "type_id" => '1',
            "other_perm" => '1',
        );
        $profileId = $profileDo->insertRecord($profileArray);
        return $profileId;
    }

    public function checkExist($profileId)
    {
        $profileDo = BizSystem::getObject($this->m_profileObj, 1);
        $profile = $profileDo->fetchById($profileId);

        if ($profile)
        {
            return true;
        } else
        {
            return false;
        }
    }

    protected function getProfileByCookie()
    {
        //print_r($_COOKIE);
        if (isset($_COOKIE["SYSTEM_SESSION_USERNAME"]) && isset($_COOKIE["SYSTEM_SESSION_PASSWORD"]))
        {
            $username = $_COOKIE["SYSTEM_SESSION_USERNAME"];
            $password = $_COOKIE["SYSTEM_SESSION_PASSWORD"];

            $svcobj = BizSystem::getService(AUTH_SERVICE);
            if ($svcobj->authenticateUserByCookies($username, $password))
            {
                $this->InitProfile($username);
            } else
            {
                setcookie("SYSTEM_SESSION_USERNAME", null, time() - 100, "/");
                setcookie("SYSTEM_SESSION_PASSWORD", null, time() - 100, "/");
            }
        }
        return null;
    }

    protected function InitDBProfile($username)
    {
        // fetch user record
        $userDo = BizSystem::getObject($this->m_userDataObj);
        if (!$userDo)
            return false;

        $recordSet = $userDo->directFetch("[username]='$username'", 1);
        if (!$recordSet)
            return null;

        // set the profile array
        $userId = $recordSet[0]['Id'];
        $profile = $recordSet[0];
        $profile['password'] = null;
        $profile['enctype'] = null;
		/*
        $userDo = BizSystem::getObject($this->m_profileObj, 1);
        if (!$userDo)
            return false;

        $recordSet = $userDo->directFetch("[user_id]='$userId'", 1);
        if ($recordSet)
        {
            $recordSet = $recordSet[0];
            if ($recordSet != null)
            {
                foreach ($recordSet as $key => $value)
                {
                    $profile["profile_" . $key] = $value;
                }
            }
        }
		*/
        // fetch roles and set profile roles
        $userDo = BizSystem::getObject($this->m_user_roleDataObj);
        $recordSet = $userDo->directFetch("[user_id]='$userId'");
        if ($recordSet)
        {
            foreach ($recordSet as $record)
            {
                $profile['roles'][] = $record['role_id'];
                $profile['roleNames'][] = $record['role_name'];
                $profile['roleStartpage'][] = $record['role_startpage'];
            }
        }
        // fetch groups and set profile groups
        $userGroupDo = BizSystem::getObject($this->m_user_groupDataObj);
        $recordSet = $userGroupDo->directFetch("[user_id]='$userId'");
        if ($recordSet)
        {
            $profile['default_group'] = null;
            foreach ($recordSet as $record)
            {
                $profile['groups'][] = $record['group_id'];
                $profile['groupNames'][] = $record['group_name'];
                if ($record['default'] == 1 && $profile['default_group'] == null)
                {
                    $profile['default_group'] = $record['group_id'];
                    $profile['default_group_name'] = $record['group_name'];
                }
            }
        }
        if ($profile['default_group'] == null)
        {
            $profile['default_group'] = $recordSet[0]['group_id'];
            $profile['default_group_name'] = $recordSet[0]['group_name'];
        }
        return $profile;
    }

    /**
     *
     * @param type $userId 
     */
    public function switchUserProfile($userId)
    {
        //get previously profile
        if (!BizSystem::sessionContext()->getVar("_PREV_USER_PROFILE"))
        {
            $prevProfile = BizSystem::sessionContext()->getVar("_USER_PROFILE");
            BizSystem::sessionContext()->clearVar("_USER_PROFILE");
            BizSystem::sessionContext()->setVar("_PREV_USER_PROFILE", $prevProfile);
        }
        BizSystem::initUserProfile($userId);
    }

    public function GetGroupName($group_id, $type = 'full')
    {
        $groupName = BizSystem::getObject($this->m_groupDataObj)->fetchById($group_id)->name;
        if($groupName)
        {
        	return $groupName;
        }
        else
        {
        	return "-- Not Available --";
        }
    }
    
    public function GetProfileName($account_id, $type = 'full')
    {
        $do = BizSystem::getObject($this->m_userDataObj);
        if (!$do)
            return "";
        if ($account_id == 0)
        {
            $msg = "-- Not Available --";
            return $msg;
        }

        $rs = $do->fetchById($account_id);
        if (!$rs)
        {
            $msg = "-- Deleted User ( UID:$account_id ) --";
            return $msg;
        }
        $contact_do = BizSystem::getObject($this->m_contactObj);
        $contact_rs = $contact_do->directFetch("[user_id]='$account_id'", 1);
        if (count($contact_rs) == 0)
        {
            //$name = $rs['username']." &lt;".$rs['email']."&gt;";
            $name = $rs['username'];
            $email = $rs['email'];
            if ($email)
            {
                $name.=" <$email>";
            }
        } else
        {
            $contact_rs = $contact_rs[0];
            if ($contact_rs['email'])
            {
                $email = $contact_rs['email'];
            } else
            {
                $email = $rs['email'];
            }
            $name = $contact_rs['display_name'];
            if ($email && $type == 'full')
            {
                $name.=" <$email>";
            }
        }
        return $name;
    }

    public function GetProfileId($account_id)
    {
        $do = BizSystem::getObject($this->m_userDataObj);
        if (!$do)
            return "";
        if ($account_id == 0)
        {
            $profile_id = 0;
            return $profile_id;
        }
        $rs = $do->fetchById($account_id);
        if (!$rs)
        {
            $profile_id = 0;
            return $profile_id;
        }
        $contact_do = BizSystem::getObject($this->m_contactObj);
        $contact_rs = $contact_do->directFetch("[user_id]='$account_id'", 1);
        if (count($contact_rs) > 0)
        {
            $contact_rs = $contact_rs[0];
            $profile_id = $contact_rs['Id'];
        }
        return $profile_id;
    }

    public function GetProfileEmail($account_id)
    {
        $do = BizSystem::getObject($this->m_userDataObj);
        if (!$do)
            return "";


        $rs = $do->fetchById($account_id);
        if (!$rs)
        {
            $msg = "-- Deleted User ( UID:$account_id ) --";
            return $msg;
        }

        return $rs['email'];
    }

}
