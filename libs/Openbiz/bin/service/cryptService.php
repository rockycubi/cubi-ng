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
 * @copyright Copyright (c) 2005-2011, Rocky Swen, Jixian
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: cryptService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */


class cryptService
{
    protected $m_DefaultKey ;
    public $m_Algorithm;
    public $m_OperationMode;

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
        $this->m_DefaultKey 	= strtolower($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["DEFAULTKEY"]);
    	$this->m_Algorithm 		= strtolower($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["ALGORITHM"]);
        $this->m_OperationMode 	= strtolower($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["OPERATIONMODE"]);
    }

    public function encrypt($data, $key=null)
    {
    	if(!function_exists("mcrypt_module_open"))
    	{
    		return $data;
    	}
    	if($key==null){
    		$key = $this->m_DefaultKey;
    	}
    	if($data==null)
    		return;
        $td = mcrypt_module_open($this->m_Algorithm, '', $this->m_OperationMode, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
       // $iv ="jixian";
        $ks = mcrypt_enc_get_key_size($td);
        $keystr = substr(md5($key), 0, $ks);
        mcrypt_generic_init($td, $keystr, $iv);
        $encrypted = mcrypt_generic($td, $data);
        mcrypt_module_close($td);
        $hexdata = bin2hex($encrypted);       
        return $hexdata;
    }

    public function decrypt($data, $key=null)
    {
    	if(!function_exists("mcrypt_module_open"))
    	{
    		return $data;
    	}
   	 	if($key==null){
    		$key = $this->m_DefaultKey;
    	}    	
    	if($data==null)
    		return;
        $td = mcrypt_module_open($this->m_Algorithm, '', $this->m_OperationMode, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);        
        $ks = mcrypt_enc_get_key_size($td);
        $keystr = substr(md5($key), 0, $ks);
        mcrypt_generic_init($td, $keystr, $iv);
        $encrypted = pack( "H*", $data);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);        
        return trim($decrypted);
    }    

}

?>