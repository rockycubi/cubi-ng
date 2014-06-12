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
 * @version   $Id: utilService.php 4486 2012-10-27 13:31:05Z hellojixian@gmail.com $
 */

/**
 * Service for general utilities 
 */
class utilService
{

    /**
     * Format size number to near unit of B, KB, Mb, GB and TB
     * @param int $size
     * @return string  
     */
    public static function format_bytes($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++)
            $size /= 1024;
        return round($size, 2) . $units[$i];
    }

    /**
     * Get view URL from view name
     * 
     * @param string $viewName
     * @return string 
     */
    public static function getViewURL($viewName)
    {
        $urlArr = explode(".", $viewName);
        $view = str_replace("View", "", $urlArr[2]);
        preg_match_all("/([A-Z]{1}[a-z]+)/s", $view, $match);
        foreach ($match[0] as $viewPart)
        {
            $viewUrl .= strtolower($viewPart) . '_';
        }
        $viewUrl = substr($viewUrl, 0, strlen($viewUrl) - 1);
        $url = $urlArr[0] . '/' . $viewUrl;
        return $url;
    }

	public static function generatePassword ($length = 8)
  	{
	
	    // start with a blank password
	    $password = "";
	
	    // define possible characters - any character in this string can be
	    // picked for use in the password, so if you want to put vowels back in
	    // or add special characters such as exclamation marks, this is where
	    // you should do it
	    //$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
	    $possible = "2346789bcdfghjkmnpqrtvwxyz";
	
	    // we refer to the length of $possible a few times, so let's grab it now
	    $maxlength = strlen($possible);
	  
	    // check for length overflow and truncate if necessary
	    if ($length > $maxlength) {
	      $length = $maxlength;
	    }
		
	    // set up a counter for how many characters are in the password so far
	    $i = 0; 
	    
	    // add random characters to $password until $length is reached
	    while ($i < $length) { 
	
	      // pick a random character from the possible ones
	      $char = substr($possible, mt_rand(0, $maxlength-1), 1);
	        
	      // have we already used this character in $password?
	      if (!strstr($password, $char)) { 
	        // no, so it's OK to add it onto the end of whatever we've already got...
	        $password .= $char;
	        // ... and increase the counter by one
	        $i++;
	      }
	
	    }
	
	    // done!
	    return $password;
	  }
}
?>