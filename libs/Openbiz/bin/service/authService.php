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
 * @version   $Id: authService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
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

/**
 * auditService class is the plug-in service of handling user authentication
 *
 * @package openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class authService
{
    public $m_AuthticationType ;
    public $m_AuthticationDataObj;

    /**
     * Initialize auditService with xml array metadata
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
        $this->m_AuthticationType 	= $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["AUTHTYPE"];
        $this->m_AuthticationDataObj 	= $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"];
    }

    /**
     * Authenticate User
     *
     * @param string $userName
     * @param string $password
     * @return boolean
     */
    public function authenticateUser($userName, $password)
    {
        if ($this->m_AuthticationType == "database")
            return $this->authDBUser($userName, $password);
        return false;
    }

    /**
     * Authenticate User that stored in database
     *
     * @param string $userName
     * @param string $password
     * @return boolean
     */
    protected function authDBUser($userName, $password)
    {
        $boAuth = BizSystem::getObject($this->m_AuthticationDataObj);
        if (!$boAuth)
            return false;
        $searchRule = "[login]='$userName'";
        $recordList = array();
        $boAuth->fetchRecords($searchRule, $recordList, 1);

        $encType 	 = $recordList[0]["enctype"];
        $realPassword = $recordList[0]["password"];
        if ($this->checkPassword($encType,$password,$realPassword))
            return true;

        return false;
    }

    /**
     * Check Password
     *
     * @param string $encType encryption type 
     * @param string $password
     * @param string $realPassword
     * @return boolean
     */
    protected function checkPassword($encType,$password,$realPassword)
    {
        foreach(hash_algos() as $algos)
        {
            if(strtoupper($encType)==strtoupper($algos))
            {
                $password=hash($algos, $password);
            }
        }
        if($password==$realPassword)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

?>