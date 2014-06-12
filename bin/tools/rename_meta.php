#!/usr/bin/env php
<?php
/*
 * rename metadata from given database table
 */

if ($argc<3) {
	echo "usage: php rename_meta.php module formname toname".PHP_EOL;
	exit;
}
$module = $argv[1];
$formName = $argv[2];
$toName = $argv[3];

include_once (dirname(dirname(__FILE__))."/app_init.php");
if(!defined("CLI")){
	exit;
}
// check if the /modules/table is already there
$moduleDir = MODULE_PATH."/".str_replace(".","/",$module);

echo "Warning: Rename tool will rename all related metadata files, do you want to continue? [y/n] ";
// Read the input
$answer = trim(fgets(STDIN));
if ($answer != 'y')
	exit;

// create do xml
echo "---------------------------------------".PHP_EOL;
echo "Rename metadata files ...".PHP_EOL;
// rename the file name and replace do name in dos, forms
feil_str_replace($formName, $toName, $moduleDir);


function feil_str_replace($from, $to, $path)
{    
    $fp = opendir($path);
    while($f = readdir($fp))
    {
    	if ( preg_match("#^\.+$#", $f) ) continue; // ignore symbolic links
    	$file_full_path = $path.'/'.$f;
    	if(is_dir($file_full_path)) 
    	{
    		feil_str_replace($from, $to, $file_full_path);
    	} 
    	else 
    	{
    		$path_parts = pathinfo($f);
    		if ($path_parts['extension'] == 'xml') {
    			if ($path_parts['filename'] == $from) {
    				$newFile = $path.'/'.$to.'.xml';
    				rename($file_full_path, $newFile);
    				echo "Rename file $file_full_path to $newFile".nl;
    				$file_full_path = $newFile;
    			}
    		} 
    		
    		$count = 0;
    		$fileContent = str_replace($from, $to, file_get_contents($file_full_path), $count);
    		if ($count>0) { 
    			file_put_contents($file_full_path, $fileContent);
    			echo "Made $count replacement in file $file_full_path".nl;
    		}
    	}
    }
}
?>