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
 * @version   $Id: cacheService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * accessService class is the plug-in service of handling cache
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class cacheService
{  
    public 	$m_Cache = "Disbaled";
    public	$m_CacheEngine = "File";

    protected $m_CacheOptions = array();
    protected $m_CacheEngineOptions  = array();
    protected $m_CacheObj = null;

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
     * Destroy cache
     *
     * @return void
     */
    public function destroy()
    {
        //$this->m_Cache = null;
        //$this->m_CacheEngine = null;
        //$this->m_CacheObj = null;
        //$this->m_CacheOptions = null;
        //$this->m_CacheEngineOptions = null;
    }

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetadata(&$xmlArr)
    {
        $this->m_Cache 	= isset($xmlArr["PLUGINSERVICE"]["CACHESETTING"]["ATTRIBUTES"]["MODE"]) ? $xmlArr["PLUGINSERVICE"]["CACHESETTING"]["ATTRIBUTES"]["MODE"] : "Enabled";
        $this->m_CacheEngine = isset($xmlArr["PLUGINSERVICE"]["CACHEENGINE"]["ATTRIBUTES"]["TYPE"]) ? $xmlArr["PLUGINSERVICE"]["CACHEENGINE"]["ATTRIBUTES"]["TYPE"] : "FileCache";
        // process Cache settings
        if(strtoupper($this->m_Cache)=="ENABLED")
        {
            $this->_loadConfig($xmlArr["PLUGINSERVICE"]["CACHESETTING"]["CONFIG"],$this->m_CacheOptions);
        }
        switch(strtoupper($this->m_CacheEngine))
        {
            case "FILE":
                $this->_loadConfig($xmlArr["PLUGINSERVICE"]["CACHEENGINE"]["FILE"]["CONFIG"],$this->m_CacheEngineOptions);
                //no break there , because all other engine is inherit from FileCache
                break;

            case "SQLITE":
                $this->_loadConfig($xmlArr["PLUGINSERVICE"]["CACHEENGINE"]["SQLITE"]["CONFIG"],$this->m_CacheEngineOptions);
                break;

            case "MEMCACHED":
                $this->_loadConfig($xmlArr["PLUGINSERVICE"]["CACHEENGINE"]["MEMCACHED"]["CONFIG"],$this->m_CacheEngineOptions);
                break;

            case "XCACHE":
                $this->_loadConfig($xmlArr["PLUGINSERVICE"]["CACHEENGINE"]["XCACHE"]["CONFIG"],$this->m_CacheEngineOptions);
                break;

            case "APC":
                $this->_loadConfig($xmlArr["PLUGINSERVICE"]["CACHEENGINE"]["APC"]["CONFIG"],$this->m_CacheEngineOptions);
                break;

            case "ZENDPLATFORM":
                $this->_loadConfig($xmlArr["PLUGINSERVICE"]["CACHEENGINE"]["ZENDPLATFORM"]["CONFIG"],$this->m_CacheEngineOptions);
                break;

        }
    }

    /**
     * Load cache configuratoon
     *
     * @param array $configs
     * @param array $options
     * @return void
     */
    private function _loadConfig(&$configs, &$options)
    {
        foreach($configs as $config)
        {
			$value_up = strtoupper($config["ATTRIBUTES"]["VALUE"]);
            if($value_up=="Y")
            {
                $config["ATTRIBUTES"]["VALUE"]=true;
            }elseif($value_up=="N")
            {
                $config["ATTRIBUTES"]["VALUE"]=false;
            }
            $options[$config["ATTRIBUTES"]["NAME"]] = $config["ATTRIBUTES"]["VALUE"];
            if($config["ATTRIBUTES"]["NAME"]=='cache_dir')
            {
                $options[$config["ATTRIBUTES"]["NAME"]] = CACHE_PATH."/".$config["ATTRIBUTES"]["VALUE"];
            }
        }
    }

    /**
     * Initialize cache
     *
     * @param string $objName
     * @param number $lifeTime
     * @return boolean|Zend_Cache
     */
    public function init($objName = "", $lifeTime = 0)
    {
        if(strtoupper($this->m_Cache)=="ENABLED")
        {
            if(strtoupper($this->m_CacheEngine)=="FILE" && $objName!="" )
            {
                $objfolder=str_replace(".","/",$objName)."/";
                $objfolder=str_replace(array(':',' '),'_',$objfolder);
                if(!strpos($this->m_CacheEngineOptions['cache_dir'],$objfolder))
                {
                    $this->m_CacheEngineOptions['cache_dir'].=$objfolder;
                }
            }

            if (!file_exists($this->m_CacheEngineOptions['cache_dir']))
            {
                //mkdir($this->m_CacheEngineOptions['cache_dir'], 0777, true);
                $this->_makeDirectory($this->m_CacheEngineOptions['cache_dir'], 0777);
            }

            $this->m_CacheOptions['automatic_serialization']=true;

            if((int)$lifeTime>0)
            {
                $this->m_CacheOptions['lifetime']=(int)$lifeTime;
            }
            require_once 'Zend/Cache.php';
            $this->m_CacheObj = Zend_Cache::factory('Core',
                    $this->m_CacheEngine,
                    $this->m_CacheOptions,
                    $this->m_CacheEngineOptions);
            return $this->m_CacheObj;
        }
        else
        {
            return false;
        }
    }

    /**
     * Save cache
     *
     * @param mixed $data
     * @param string $id
     * @return boolean true if no problem
     */
    public function save($data, $id)
    {
        if($this->m_CacheObj && strtoupper($this->m_Cache)=="ENABLED" )
        {
            return $this->m_CacheObj->save($data, $id);
        }
        else
        {
            return false;
        }
    }

    /**
     * Load cache
     *
     * @param string $id cache id
     * @return mixed cached datas (or false)
     */
    public function load($id)
    {
        if($this->m_CacheObj && strtoupper($this->m_Cache)=="ENABLED")
        {
            return $this->m_CacheObj->load($id);
        }else
        {
            return false;
        }
    }

    /**
     * Test cache
     *
     * @param string $id cache id
     * @return boolean true is a cache is available, false else
     */
    public function test($id)
    {
        if($this->m_CacheObj && strtoupper($this->m_Cache)=="ENABLED")
        {
            return $this->m_CacheObj->test($id);
        }else
        {
            return false;
        }
    }

    /**
     * Remove a cache
     *
     * @param string $id cache id to remove
     * @return boolean true if ok
     */
    public function remove($id)
    {
        if($this->m_CacheObj && strtoupper($this->m_Cache)=="ENABLED")
        {
            return $this->m_CacheObj->remove($id);
        }
        else
        {
            return false;
        }
    }
     
     /**
     * get a list of all caches
     *
     * @return array ids
     */
    public function getIds()
    {
        if($this->m_CacheObj && strtoupper($this->m_Cache)=="ENABLED")
        {
            return $this->m_CacheObj->getIds();
        }
        else
        {
            return false;
        }
    }    

    /**
     * clean all cache
     *
     * @return boolean true if ok
     */
    public function cleanAll()
    {
        if($this->m_CacheObj && strtoupper($this->m_Cache)=="ENABLED")
        {
            return $this->m_CacheObj->clean(Zend_Cache::CLEANING_MODE_ALL);
        }
        else
        {
            return false;
        }
    }

    /**
     * clean Expired
     *
     * @return boolean true if ok
     */
    public function cleanExpired()
    {
        if($this->m_CacheObj && strtoupper($this->m_Cache) == "ENABLED")
        {
            return $this->m_CacheObj->clean(Zend_Cache::CLEANING_MODE_OLD);
        }
        else
        {
            return false;
        }
    }


    /**
     * Make directory recursively
     *
     * @param string $pathName The directory path.
     * @param int $mode<p>
     * The mode is 0777 by default, which means the widest possible
     * access. For more information on modes, read the details
     * on the chmod page.
     * @return bool Returns true on success or false on failure.
     * @todo need move to utility class or helper?
     */
    private function _makeDirectory($pathName, $mode)
    {
        is_dir(dirname($pathName)) || $this->_makeDirectory(dirname($pathName), $mode);
        return is_dir($pathName) || @mkdir($pathName, $mode);
    }
}

?>