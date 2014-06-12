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
 * @version   $Id: fileUtil.php 4800 2012-11-20 08:12:29Z hellojixian@gmail.com $
 */

function ob_scandir($dir)
{
    $retDirs = null;
    if(is_dir($dir)){
	    $dir0s = scandir($dir);
	    foreach ($dir0s as $dir0) {
	        if (( $dir0 == '.' ) || ( $dir0 == '..' ) || ( $dir0 == '.svn' )) continue;
	        $retDirs[] = $dir0;
	    }
	    //print_r($retDirs);
    }
    return $retDirs;
}

function recurse_copy($src,$dst) {
    if(!is_dir($src)){
		return;
	}
    $dir = opendir($src);
    @mkdir($dst, 0777, true);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' ) && ( $file != '.svn' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
            	if(is_file($src . '/' . $file))
            	{
                	copy($src . '/' . $file,$dst . '/' . $file);
            	}
            }
        }
    }
    closedir($dir);
} 

function recurse_delete($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") recurse_delete($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
}
?>