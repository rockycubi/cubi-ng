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
 * @version   $Id: preferenceService.php 5071 2013-01-07 08:15:03Z hellojixian@gmail.com $
 */

/**
 * User preference service 
 */
class preferenceService
{
    protected $m_Name = "ProfileService";    
    protected $m_PreferenceObj ;    
    protected $m_Preference;

    public function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
    }

    protected function readMetadata(&$xmlArr)
    {
        $this->m_PreferenceObj = $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"];
    }

    public function initPreference($userId)
    {
        $this->m_Preference = $this->InitDBPreference($userId);
        BizSystem::sessionContext()->setVar("_USER_PREFERENCE", $this->m_Preference);        
        BizSystem::sessionContext()->setVar("LANG",$this->m_Preference['language']);
        BizSystem::sessionContext()->setVar("THEME",$this->m_Preference['theme']);
        BizSystem::sessionContext()->setVar("TIMEZONE",$this->m_Preference['timezone']);
        date_default_timezone_set($this->m_Preference['timezone']);
        return $this->m_Preference;
    }

    /**
     * Get user preference
     * 
     * @param type $attribute
     * @return null 
     */
    public function getPreference($attribute=null)
    {    	
        if (!$this->m_Preference)
        {
            $this->m_Preference = BizSystem::sessionContext()->getVar("_USER_PREFERENCE");
        }
        if (!$this->m_Preference)
        {
        		return null;
        }
        if ($attribute){
        	if(isset($this->m_Preference[$attribute])){
        		return $this->m_Preference[$attribute];
        	}else{
        		return null;
        	}
        }
            
        return $this->m_Preference;
    }

    /**
     * Set user preference
     * 
     * @param <type> $preference 
     */
    public function setPreference($attribute,$value=null)
    {    	    	    
        $this->m_Preference[$attribute] = $value;
        BizSystem::sessionContext()->setVar("_USER_PREFERENCE", $this->m_Preference);  
        //update user preference to DB 
        $do = BizSystem::getObject($this->m_PreferenceObj);
        if (!$do)
            return false;
        $user_id = BizSystem::getUserProfile("Id");
        $prefRec = $do->fetchOne("[user_id]='$user_id' AND [name]='$attribute'");
        $prefRec['value'] = (string) $value;
        return $prefRec->save();
    }
    
 
    /**
     * Initialize user preference from database
     * 
     * @param type $user_id
     * @return boolean 
     */
    protected function initDbPreference($user_id)
    {
        $do = BizSystem::getObject($this->m_PreferenceObj);
        if (!$do)
            return false;

        $rs = $do->directFetch("[user_id]='$user_id'");
      
        if ($rs)
        {
	        	foreach ($rs as $item)
	        	{        		
	        		$preference[$item["name"]] = $item["value"];        	
	        	}	
        }
        return $preference;
    }

}
