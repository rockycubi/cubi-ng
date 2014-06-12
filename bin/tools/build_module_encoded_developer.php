#!/usr/bin/php
<?php
/*
 * build module command line script
 */
if ($argc<2) {
	echo "usage: php build_module_encoded.php module_name".PHP_EOL;
	exit;
}

include_once ("../app_init.php");
if(!defined("CLI")){
	exit;
}

$moduleName = $argv[1];
$passphrase = $argv[2];

$buildNumber = getModuleVersion($moduleName);
$ext = "tar.gz";

// invoke cubi/build/build mod_build.xml -Dbuild.module=$moduleName -Dbuild.number=$buildNumber
echo "---------------------------------------\n";
execPhing("mod_build_encoded_developer.xml", "\"-DbuildName=$moduleName\" \"-DbuildNumber=$buildNumber\" \"-Dext=$ext\"  \"-Dpassphrase=$passphrase\"");

function execPhing($buildFile, $options)
{
    $phingHome = APP_HOME.DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR."phing";
    putenv("PHING_HOME=$phingHome");
    $phpClasses = $phingHome.DIRECTORY_SEPARATOR."classes";
    putenv("PHP_CLASSPATH=$phpClasses");
    $phingBin = $phingHome.DIRECTORY_SEPARATOR."bin";
    //putenv("PATH=$phingBin");
    $cmd = $phingBin.DIRECTORY_SEPARATOR."phing"." -buildfile $buildFile $options";
    echo "Executing $cmd\n";
    chdir(APP_HOME.DIRECTORY_SEPARATOR."build");
    system($cmd,$output);
    if($output){
    	echo $output;
    }
}

function getModuleVersion($moduleName)
{
    // read mod.xml, get its version as its release name
    $modfile = MODULE_PATH."/".$moduleName."/mod.xml";
        
    $xml = simplexml_load_file($modfile);
    $modVersion = $xml['Version'];
    $releaseName = $modVersion;

    $buildNumber = $releaseName;
    return $buildNumber;
}

?>
