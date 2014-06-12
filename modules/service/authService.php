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
 * @version   $Id: authService.php 3485 2012-06-18 23:41:37Z agus.suhartono@gmail.com $
 */
/*
 * modified by Jixian 2009-07-12
 * for suport password encryption storage 
 * supported algos:
 * Algos							Speed (number smaller is better)
 * 1.  md4                      	 5307.912
  2.  md5                           6890.058
  3.  crc32b                        7298.946
  4.  crc32                         7561.922
  5.  sha1                          8886.098
  6.  tiger128,3                    11054.992
  7.  haval192,3                    11132.955
  8.  haval224,3                    11160.135
  9.  tiger160,3                    11162.996
  10.  haval160,3                    11242.151
  11.  haval256,3                    11327.981
  12.  tiger192,3                    11630.058
  13.  haval128,3                    11880.874
  14.  tiger192,4                    14776.945
  15.  tiger128,4                    14871.12
  16.  tiger160,4                    14946.937
  17.  haval160,4                    15661.954
  18.  haval192,4                    15717.029
  19.  haval256,4                    15759.944
  20.  adler32                       15796.184
  21.  haval128,4                    15887.022
  22.  haval224,4                    16047.954
  23.  ripemd256                     16245.126
  24.  haval160,5                    17818.927
  25.  haval128,5                    17887.115
  26.  haval224,5                    18085.002
  27.  haval192,5                    18135.07
  28.  haval256,5                    18678.903
  29.  sha256                        19020.08
  30.  ripemd128                     20671.844
  31.  ripemd160                     21853.923
  32.  ripemd320                     22425.889
  33.  sha384                        45102.119
  34.  sha512                        45655.965
  35.  gost                          57237.148
  36.  whirlpool                     64682.96
  37.  snefru                        80352.783
  38.  md2 *
 */

class authService
{

    public $m_AuthticationType;
    public $m_AuthticationDataObj;

    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
    }

    /**
     * Set internal variable from arry (that read from xml file)
     * 
     * @param array $xmlArr 
     */
    protected function readMetadata(&$xmlArr)
    {
        $this->m_AuthticationType = $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["AUTHTYPE"];
        $this->m_AuthticationDataObj = $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"];
    }

    /**
     * Authenticate user
     * 
     * @param string $username
     * @param string $password
     * @return boolean true if user & password true, else return false 
     */
    public function authenticateUser($username, $password)
    {
        if ($this->m_AuthticationType == "database")
            return $this->authDbUser($username, $password);
        return false;
    }

    /**
     * Authenticate user by cookies
     * 
     * @param string $username
     * @param string $password
     * @return boolean true if user & password true, else return false 
     */
    public function authenticateUserByCookies($username, $password)
    {
        /* @var $authDO BizDataObj */
        $authDO = BizSystem::getObject($this->m_AuthticationDataObj);
        if (!$authDO)
            return false;
        
        $searchRule = "[username]='$username' and status='1'";
        $recordList = array();

        $authDO->fetchRecords($searchRule, $recordList, 1);

        $encType = $recordList[0]["enctype"];
        $realPassword = $recordList[0]["password"];

        $realPasswordEnc = md5(md5($realPassword . $username) . md5($recordList[0]["create_time"]));
        if ($realPassword == $realPasswordEnc)
        {
            return true;
        }
        return false;
    }

    /**
     * Authenticate user by SmartCart
     * 
     * @param type $smartcard
     * @return boolean|array 
     */
    public function authenticateUserBySmartCard($smartcard)
    {
        /* @var $authDO BizDataObj */
        $authDO = BizSystem::getObject($this->m_AuthticationDataObj);
        if (!$authDO)
            return false;
        $searchRule = "[smartcard]='$smartcard' and status='1'";
        $recordList = array();

        $authDO->fetchRecords($searchRule, $recordList, 1);

        if (count($recordList) > 0)
        {
            $username = $recordList[0]["username"];
            return $username;
        }
        return false;
    }

    protected function authDbUser($username, $password)
    {
        /* @var $authDO BizDataObj */
        $authDO = BizSystem::getObject($this->m_AuthticationDataObj);
        if (!$authDO)
            return false;
        $searchRule = "[username]='$username' and status='1'";
        $recordList = array();

        $authDO->fetchRecords($searchRule, $recordList, 1);

        if (count($recordList) == 0)
        {
            return false;
        }
        $encType = $recordList[0]["enctype"];
        $realPassword = $recordList[0]["password"];

        if ($this->checkPassword($encType, $password, $realPassword))
        {
            return true;
        }
        return false;
    }

    protected function checkPassword($encType, $password, $realPassword)
    {
        foreach (hash_algos() as $algos)
        {
            if (strtoupper($encType) == strtoupper($algos))
            {
                $password = hash($algos, $password);
                break;
            }
        }
        //echo "$password , $realPassword";
        if ($password == $realPassword)
        {
            return true;
        } else
        {
            return false;
        }
    }

}

?>