<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: SessionMCHandler.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */

define("SESSION_MEMCACHE","127.0.0.1:11211");

class SessionMCHandler {
    protected $lifeTime;
    protected $memcache;
    protected $initSessionData;
    protected $memcacheHost;
    protected $memcachePort;

    function __construct()
    {
        $this->lifeTime = TIMEOUT;
        $arr = explode(":",SESSION_MEMCACHE);
        $this->memcacheHost = $arr[0];
        $this->memcachePort = $arr[1];
    }

    function open($savePath,$sessionName) {
        $sessionID = session_id();
        $this->memcache = new Memcache;
        $ok = $this->memcache->connect($this->memcacheHost,$this->memcachePort);
        if (!$ok) {
            trigger_error("Cannot connect to memcache $this->memcacheHost at $this->memcachePort",E_USER_ERROR);
            return false;
        }
        /*if ($sessionID !== "") {
            $this->initSessionData = $this->read($sessionID);
        }*/
        return true;
    }

    function close() {
        $this->lifeTime = null;
        $this->memcache = null;
        $this->initSessionData = null;
        return true;
    }
 
    function read($sessionID) {
        $data = $this->memcache->get($sessionID);
        //echo "read session data of $sessionID ".$data;
        // The default miss for MC is (bool) false, so return it
        return $data;
    }

    function write($sessionID,$data) {
        // This is called upon script termination or when session_write_close() is called, which ever is first.
        //echo "set session data of $sessionID $this->lifeTime ".$data;
        $result = $this->memcache->set($sessionID,$data,false,$this->lifeTime);
        return $result;
    }

    function destroy($sessionID) {
        // Called when a user logs out...
        $this->memcache->delete($sessionID);
        return true;
    }

    function gc($maxlifetime) {
        // ?? We need this atomic so it can clear MC keys as well...
        // No action is needed, since lifetime is set, memcache will GC expired cache item anyway
        return true;
    }
}

$sessionHandler = new SessionMCHandler();
session_set_save_handler(
    array (&$sessionHandler,"open"),
    array (&$sessionHandler,"close"),
    array (&$sessionHandler,"read"),
    array (&$sessionHandler,"write"),
    array (&$sessionHandler,"destroy"),
    array (&$sessionHandler,"gc"));

?>
