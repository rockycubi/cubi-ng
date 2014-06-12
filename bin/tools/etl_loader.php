#!/usr/bin/env php
<?php
if(isset($argc)){ 
	if ($argc<3) {
		echo "usage: php etl_loader.php [config_file] [queue_name]".PHP_EOL;
		exit;
	}else{
		echo PHP_EOL;
		echo str_repeat("=",52).PHP_EOL;
		echo "\tOpenbiz Reporting ETL Module ".PHP_EOL;
		echo str_repeat("=",52).PHP_EOL;
		echo PHP_EOL;
	}
}
include_once (dirname(dirname(__FILE__))."/app_init.php");
include_once (dirname(__FILE__)."/gen_meta.inc.php");
if(!defined("CLI")){
	exit;
}

//assign paramenters
$config_file = $argv[1]?$argv[1]:"etl_sample.xml";
if($argv[2]){
	$queue_name = $argv[2];
}else{
	$queue_name = "all";
}



//load class files
if(defined("CLI")){
	echo "Loading ETL core classes: \n";
} 
$lib_path = MODULE_PATH.DIRECTORY_SEPARATOR."report".DIRECTORY_SEPARATOR."etl".DIRECTORY_SEPARATOR."lib";
foreach(glob($lib_path.DIRECTORY_SEPARATOR."*.php") as $filename){
	include_once($filename);
	if(defined("CLI")){
		echo "\t".basename($filename)." \n";
	} 
}



//load user defined function libs
if(defined("CLI")){
	echo "\nLoading user defined functions: \n";
} 
$lib_path = MODULE_PATH.DIRECTORY_SEPARATOR."report".DIRECTORY_SEPARATOR."etl".DIRECTORY_SEPARATOR."func";
foreach(glob($lib_path.DIRECTORY_SEPARATOR."*.php") as $filename){
	include_once($filename);
	if(defined("CLI")){
		echo "\t".basename($filename)." \n";
	} 	
}



//load config data
if(defined("CLI")){
	echo "\nLoading ETL config file $config_file : \n";
} 
$conf_path = MODULE_PATH.DIRECTORY_SEPARATOR."report".DIRECTORY_SEPARATOR."etl".DIRECTORY_SEPARATOR."conf";
$conf_file = $conf_path.DIRECTORY_SEPARATOR.$config_file;

if(!is_file($conf_file)){
	echo "Config file not found! ";
	exit;
}
$xmlArr = BizSystem::getXmlArray($conf_file);
if(is_array($xmlArr["ETL"]["QUEUE"][0]["ATTRIBUTES"])){
	$etlQueuesArr = $xmlArr["ETL"]["QUEUE"];	
}else{
	$etlQueuesArr = array($xmlArr["ETL"]["QUEUE"]);
}
if(is_array($xmlArr["ETL"]["DATASOURCE"]["DATABASE"][0]["ATTRIBUTES"])){
	$dbConnections = $xmlArr["ETL"]["DATASOURCE"]["DATABASE"];	
}else{
	$dbConnections = array($xmlArr["ETL"]["DATASOURCE"]["DATABASE"]);
}
if(defined("CLI")){
	echo "\t".basename($config_file)." \n";
} 

//init for each queue
foreach ($etlQueuesArr as $queueXMLArr){
	$etlQueue = new QueueLoader($queueXMLArr,$dbConnections);
	$etlQueue->process(); 
}


echo PHP_EOL;
echo str_repeat("=",52).PHP_EOL;
echo "\tETL Process Finished ".PHP_EOL;
echo str_repeat("=",52).PHP_EOL;
echo PHP_EOL;
?>