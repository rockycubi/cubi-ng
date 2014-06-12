#!/usr/bin/env php
<?php
include_once dirname(dirname(__FILE__)).'/app_init.php';

$dir_iterator = new RecursiveDirectoryIterator(MODULE_PATH);
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

foreach ($iterator as $file) {
    if(preg_match("/.*?Copy.*?\.xml$/si",$file)){		    	
    	echo "Testing file: " .str_replace(MODULE_PATH."/","",$file)." ";
    	if(test_xml_file($file)){    		
    		echo " [ Found ]"."\n";
    		echo str_repeat("-",70)."\n";

    		while(1) {
    			echo "\tFix Form Type? [y/n] : ";
    			$exit=false;
				$selection = strtolower(trim(fgets(STDIN)));
				switch($selection){
					case "y":
						fix_formtype($file,"Copy");
						echo "\tCopy Form Type Fixed\n";
						$exit=true;
						break;
					case "n":
						$exit=true;					
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
	$pattern = "/formtype\s?=\s?\"edit\"/i";
	if(preg_match($pattern,$data))
	{		
		return true;		
	}else{
		return false;
	};
}



function fix_formType($file,$value){
	$data = file_get_contents($file);
	$pattern = "/\<easyform\s.*?formtype\s?=\s?\"edit\".*?/i";
	if(preg_match($pattern,$data))
	{	
		$new_data = preg_replace("/(\<easyform\s.*?)formtype\s?=\s?\"edit\".*?/i","$1 FormType=\"$value\"",$data);
	};
	
	$result = file_put_contents($file,$new_data);
	return $result;
}
?>