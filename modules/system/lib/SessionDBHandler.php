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
 * @version   $Id: SessionDBHandler.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */

define("SESSION_DBNAME","Default");
define("SESSION_TABLE","session");

class SessionDBHandler {
    protected $lifeTime;
    protected $initSessionData;
    protected $sessionDb;

    function __construct()
    {
        $this->lifeTime = TIMEOUT;
        $this->sessionDO = SESSION_DATAOBJ;
    }

    function open($savePath,$sessionName) {
        // echo "session open".nl;
        // connect to session db
        $this->sessionDb = BizSystem::dbConnection(SESSION_DBNAME);
        
        $sessionID = session_id();
        if ($sessionID !== "") {
            $this->initSessionData = $this->read($sessionID);
        }
        return true;
    }

    function close() {
        //echo "session close".nl;
        $this->lifeTime = null;
        $this->initSessionData = null;
        return true;
    }
 
    function read($sessionID) {
        //echo "session read".nl; 
        //debug_print_backtrace();
        $sql = "SELECT `data` FROM `".SESSION_TABLE."` WHERE `id`=?";
        $data = $this->sessionDb->fetchOne($sql, $sessionID);
        $this->initSessionData = $data;
        return $data;
    }

    function write($sessionID,$data) {
        //echo "session write".nl;
        // This is called upon script termination or when session_write_close() is called, which ever is first.
    	$expiration = ($this->lifeTime + time());
        $dataArr = self::unserializesession($data);
        $user_id = (int)$dataArr['_USER_PROFILE']["Id"];
        $ip_addr = $_SERVER["REMOTE_ADDR"];        
        $last_url = $_SERVER["REQUEST_URI"];
        $update_time = date("Y-m-d H:i:s");
        
        
        try {
        	if(SESSION_STRICT==1){
		      	//limited to single session delete prev sessions
		      	$sql = "DELETE FROM `session` WHERE `id`!='$sessionID' AND `user_id`='$user_id' ;";
		       	$this->sessionDb->query($sql);
		    }  
        	
            if ($this->initSessionData == null) {
                //echo "insert session data";
                $create_time = date("Y-m-d H:i:s");
                $this->sessionDb->insert('session', array('id'=>$sessionID, 
                											'data'=>$data, 
                											'expiration'=>$expiration,
                											'user_id'=>$user_id,
                											'ipaddr'=>$ip_addr,
                											'last_url'=>$last_url,
                											'create_time'=>$create_time,
                											'update_time'=>$update_time));
      
            }
            else {
                if ($this->initSessionData == $data) {
                    //echo "update session w/o data change";
                    $this->sessionDb->update('session', array('expiration'=>$expiration,
                    										  'user_id'=>$user_id,
                    										  'ipaddr'=>$ip_addr,
                    										  'last_url'=>$last_url,
                    										  'update_time'=>$update_time
                    										), "id = '$sessionID'");
                }
                else {
                    //echo "update session w/ data change";
                    $this->sessionDb->update('session', array('data'=>$data, 
                    										  'expiration'=>$expiration,
                    										  'user_id'=>$user_id,
                    										  'ipaddr'=>$ip_addr,
                    										  'last_url'=>$last_url,
                    										  'update_time'=>$update_time
                    											), "id = '$sessionID'");
                }
            }
        }
        catch (Exception $e) {
            echo "SQL error: ".$e->getMessage();
        }
        return true;
    }

    function destroy($sessionID) {
        //echo "session destroy".nl;
        // Called when a user logs out...
        $this->sessionDb->delete('session', "id='$sessionID'");
        return true;
    }

    function gc($maxlifetime) {
        //echo "session gc";
        // garbage collection to delete expired session entried
        $expireTime = time(); // time() - $this->lifeTime;
        $this->sessionDb->delete('session', "expiration < $expireTime");
        return true;
    }
    
	public static function unserializesession( $data )
	{
	    if(  strlen( $data) == 0)
	    {
	        return array();
	    }
	   
	    // match all the session keys and offsets
	    preg_match_all('/(^|;|\})([a-zA-Z0-9_]+)\|/i', $data, $matchesarray, PREG_OFFSET_CAPTURE);
	
	    $returnArray = array();
	
	    $lastOffset = null;
	    $currentKey = '';
	    foreach ( $matchesarray[2] as $value )
	    {
	        $offset = $value[1];
	        if(!is_null( $lastOffset))
	        {
	            $valueText = substr($data, $lastOffset, $offset - $lastOffset );
	            $returnArray[$currentKey] = unserialize($valueText);
	        }
	        $currentKey = $value[0];
	
	        $lastOffset = $offset + strlen( $currentKey )+1;
	    }
	
	    $valueText = substr($data, $lastOffset );
	    $returnArray[$currentKey] = unserialize($valueText);
	   
	    return $returnArray;
	}      
}

$sessionHandler = new SessionDBHandler();
session_set_save_handler(
    array (&$sessionHandler,"open"),
    array (&$sessionHandler,"close"),
    array (&$sessionHandler,"read"),
    array (&$sessionHandler,"write"),
    array (&$sessionHandler,"destroy"),
    array (&$sessionHandler,"gc"));

/*

CREATE TABLE IF NOT EXISTS `session` (
  `id` varchar(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `user_id` int(11) NOT NULL,
  `expiration` int(10) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `ipaddr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `create_time` datetime NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `expiration` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
*/
?>
