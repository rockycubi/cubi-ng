<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: httpClient.php 5020 2013-01-02 07:52:19Z hellojixian@gmail.com $
 */

class HttpClient
{
	protected $protocol = "GET";
	protected $cookie = "";
	protected $query = "";
	
	public function __construct($protocol)
	{
		$this->protocol = $protocol;
	}
	
	public function setCookie($cookie)
	{
		$this->cookie = $cookie;
	}
	
	public function addQuery($query)
	{
		if ($this->query == "")
            $this->query = $query;
        else
            $this->query .= "&$query";
	}
	
	// DO a http POST request
	function fetchContents($url, $headerList, $removeheaders=true) {
		$url = parse_url($url);
	
		if (!isset($url['port'])) {
			if ($url['scheme'] == 'http') { 
				$url['port']=80; 
			} elseif ($url['scheme'] == 'https') {
				$url['port']=443; 
			}
		}
		$url['query']=isset($url['query'])?$url['query']:'';
	
		$url['protocol']=$url['scheme'].'://';
		$eol="\r\n";
		
		// compose headers
		$headerStr = "";
		foreach ($headerList as $h=>$v)
			$headerStr .= "$h: $v".$eol; 
	
		if ($this->protocol == 'POST')
		{
			$headers =  $this->protocol." ".$url['protocol'].$url['host'].$url['path']." HTTP/1.0".$eol.
				"Host: ".$url['host'].$eol.
				"Referer: ".$url['protocol'].$url['host'].$url['path'].$eol.
				"Cookie: $this->cookie".$eol.
				$headerStr.
				"Content-Type: application/x-www-form-urlencoded".$eol.
				"Content-Length: ".strlen($url['query'].$this->query).$eol.
				$eol.$url['query'].$this->query;
		}
		else
		{
			$headers =  $this->protocol." ".$url['protocol'].$url['host'].$url['path'].'?'.$url['query']." HTTP/1.0".$eol.
				"Host: ".$url['host'].$eol.
				"Referer: ".$url['protocol'].$url['host'].$url['path'].$eol.
				"Cookie: $this->cookie".$eol.
				$headerStr.$eol.$eol;
		}

		$fp = @fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
		if($fp) {
			//print ($headers);
			fputs($fp, $headers);
			$result = '';
			while(!feof($fp)) { $result .= fgets($fp, 128); }
			fclose($fp);
			if ($removeheaders) {
				//removes headers
				$pattern="/^.*\r\n\r\n/s";
				$result=preg_replace($pattern,'',$result);
			}
			//$pattern="/^.*\r\n\r\n/s";
			//$ok = preg_match($pattern,$result,$matchs);
			//print_r($matchs);
			return $result;
		}
	}
	
	// Gets cookie and header values and returns as two arrays.  Kind of crude.
	public function getResponseHeaders($response) {
		$headersArray = array();
		$cookiesArray = array();
		
		list($headers,$body) = explode("\r\n\r\n",$response,2);
		foreach(explode("\r\n",$headers) as $headerline) {
			if(preg_match('/^(\S*)\: (.*)/',$headerline,$matches)) {
				list($name,$value) = array($matches[1],$matches[2]);
				$headersArray[$name] = $value;
				if($name == "Set-Cookie") {
					if(preg_match('/^(\S*)=(\S*)\;/',$value,$matches)) {
						list($cname,$cvalue) = array($matches[1],$matches[2]);
						$cookiesArray[$cname] = $cvalue;
					} 
				}
				
			}
		}
		return array($headersArray,$cookiesArray);
	}

}
?>