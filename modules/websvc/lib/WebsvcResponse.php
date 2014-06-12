<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.websvc.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: WebsvcResponse.php 4420 2012-10-08 09:09:31Z hellojixian@gmail.com $
 */

include_once 'WebsvcError.php';
include_once 'Array2Xml.php';

class WebsvcResponse
{   
	protected $checksumKey;
    protected $response = array('error'=>null, 'data'=>null, 'checksum'=>null);
    
    public function setChecksumKey($key)
    {
    	$this->checksumKey = $key;
    }
    
    public function genCheckSum()
    {
    	$this->response['checksum'] = md5(serialize($this->response['data']).$this->checksumKey);
    }
    
    public function setError($errorCode, $errorMsg)
    {
        $this->response['error']['code'] = $errorCode;
        $this->response['error']['message'] = $errorMsg;
    }
    
    public function setData($data)
    {
        $this->response['data'] = $data;
    }
    
    public function output($format)
    {
    	$this->genchecksum();
    	switch (strtolower($format))
    	{
    		case "xml":
    			return $this->printXml();
    		case "json":
    			 return $this->printJson();
    		case "jsonp":
    			return $this->printJsonp();
    		default:
    			print_r($this->response);
    	}
        
    }

    protected function printXml()
    {
        header ("Content-Type:text/xml; charset=utf-8"); 
        $xml = new array2xml('response');
        $xml->createNode($this->response);
        echo $xml;
    }
    
    protected function printJson()
    {
        header("Content-type: application/json; charset=utf-8");
        //print json_encode($this->response);
        $x = json_encode($this->response);
        $y = json_decode($x);
        print_r($x);
    }
    
    protected function printJsonp()
    {
    	$callback = $_GET['callback'];
        header("Content-type: application/json; charset=utf-8");
        //print json_encode($this->response);
        $x = json_encode($this->response);
        $y = json_decode($x);
        print_r($callback.'('.$x.')');
    }
    
}

?>