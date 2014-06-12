#!/usr/bin/env php
<?php
include_once dirname(dirname(__FILE__)).'/app_init.php';

$dir_iterator = new RecursiveDirectoryIterator(MODULE_PATH);
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

foreach ($iterator as $file) {
    if(preg_match("/.*?New.*?\.xml$/si",$file)){		    	
    	echo "Testing file: " .str_replace(MODULE_PATH."/","",$file)." ";
    	if(test_xml_file($file)){    		
    		echo " [ Found ]"."\n";
    		echo str_repeat("-",70)."\n";
    		echo "\tModify File: \n";
    		$defaultvalue = get_default_value($file);
    		echo "\t\tDefault Value: [$defaultvalue] \n";    		
    		while(1) {
    			echo "\tEnable Default Value? [y/n/c] (c=custom): ";
    			$exit=false;
				$selection = strtolower(trim(fgets(STDIN)));
				switch($selection){
					case "y":
						enable_default_value($file,$defaultvalue);
						echo "\tDefault Value Enabled\n";
						$exit=true;
						break;
					case "n":
						$exit=true;
					case "c":
						echo "\tPlease Input a New Value? : ";
						$defaultvalue = trim(fgets(STDIN));
						echo "\t\tNew Default Value: [$defaultvalue] \n";    	
						enable_default_value($file,$defaultvalue);
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
	$pattern = "/formtype\s?=\s?\"new\"/i";
	if(preg_match($pattern,$data))
	{
		//test if its contains fld_name field
		$pattern = "/\<element\s.*?name\s?=\s?\"fld_name\"/i";
		if(preg_match($pattern,$data))
		{
			//test if its already have a default value, have = true , dont have = false
			$pattern = "/\<element\s.*?name\s?=\s?\"fld_name\".*?defaultvalue\s?=\s?\".+\"/i";
			if(preg_match($pattern,$data))
			{					
				return false;
			}else{
				return true;
			};
		}else{
			return false;
		};
	}else{
		return false;
	};
}

function get_default_value($file){
	$data = file_get_contents($file);
	$pattern = "/title\s?=\s?\"(.*?)\"/i";
	if(preg_match($pattern,$data,$match))
	{
		return $match[1];
	}
	else
	{
		return ;	
	}
}

function enable_default_value($file,$value){
	$data = file_get_contents($file);
	$pattern = "/\<element\s.*?name\s?=\s?\"fld_name\".*?defaultvalue\s?=\s?\"\"/i";
	if(preg_match($pattern,$data))
	{					
		//have a empty defaultvalue attribute
		$new_data = preg_replace("/\<element\s.*?name\s?=\s?\"fld_name\".*?defaultvalue\s?=\s?\"(.*?)\"/i",$value,$data);		
	}else{
		$new_data = preg_replace("/(\<element\s.*?name\s?=\s?\"fld_name\")/i","$1 DefaultValue=\"$value\"",$data);
	};
	
	$result = file_put_contents($file,$new_data);
	return $result;
}
?>