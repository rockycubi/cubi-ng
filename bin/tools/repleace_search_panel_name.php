#!/usr/bin/env php
<?php
include_once dirname(dirname(__FILE__)).'/app_init.php';

if($argv[1]){
	$modulename= $argv[1];
	$dir_iterator = new RecursiveDirectoryIterator(MODULE_PATH.'/'.$modulename);
}else{
	$dir_iterator = new RecursiveDirectoryIterator(MODULE_PATH);
}

$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

foreach ($iterator as $file) {
    if(preg_match("/.*?List.*?\.xml$/si",$file)){		    	
    	echo "Testing file: " .str_replace(MODULE_PATH."/","",$file)." ";
    	if(test_xml_file($file)){    		
    		echo " [ Found ]"."\n";
    		echo str_repeat("-",70)."\n";
    		echo "\tModify File: \n";
    		$defaultvalue = get_default_value($file);
    		
    		$formname = strtolower(str_replace("ListForm.xml","",basename($file)));    		
    		$newvalue=substr($defaultvalue,0,4).$formname."_".substr($defaultvalue,4);
    		
    		echo "\t\tDefault Value: [$defaultvalue] \n";    		
    		echo "\t\tNew Value: [$newvalue] \n";
    		while(1) {
    			echo "\tReplace? [y/n/c] (c=custom): ";
    			$exit=false;
				$selection = strtolower(trim(fgets(STDIN)));
				switch($selection){
					case "y":
						enable_default_value($file,$defaultvalue,$newvalue);												
						$exit=true;
						break;
					case "n":
						$exit=true;
					case "c":
						echo "\tPlease Input a New Value? : ";
						$newvalue = trim(fgets(STDIN));
						echo "\t\tNew  Value: [$newvalue] \n";    	
						enable_default_value($file,$defaultvalue,$newvalue);
						$exit=true;
						break;
					default:						
						break;					
				}
				if($exit==true){
					echo "\n";
					break;
				}
    		}
    		echo str_repeat("-",70)."\n";
    	}else{
    		echo " \n";
    	}
    }
}


function test_xml_file($file){
	$data = file_get_contents($file);
	//test if form type is NEW
	$pattern = "/qry_/i";
	if(preg_match($pattern,$data))
	{
		return true;
	}else{
		return false;
	};
}

function get_default_value($file){
	$data = file_get_contents($file);
	$pattern = "/\"(qry_.*?)\"/i";
	if(preg_match($pattern,$data,$match))
	{
		return $match[1];
	}
	else
	{
		return ;	
	}
}

function enable_default_value($file,$value_old,$value_new){
	$data = file_get_contents($file);
	$new_data = str_replace($value_old, $value_new, $data);
	$result = file_put_contents($file,$new_data);
	return $result;
}
?>