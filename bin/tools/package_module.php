<?php
/*
 * package module command line script
 */
if ($argc<3) {
	echo "usage: php package_module.php module_name tag_name".PHP_EOL;
	exit;
}

include_once ("../app_init.php");
if(!defined("CLI")){
	exit;
}

$moduleName = $argv[1];
$tagName = $argv[2];

$buildNumber = generateBuildNumber($moduleName, $tagName);
$ext = "cpk";

// invoke cubi/build/build mod_build.xml -Dbuild.module=$moduleName -Dbuild.number=$buildNumber
echo "---------------------------------------\n";
execPhing("mod_build.xml", "\"-DbuildName=$moduleName\" \"-DbuildNumber=$buildNumber\" \"-Dext=$ext\"");

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
    system($cmd);
}

function generateBuildNumber($moduleName, $tagName)
{
    // read mod.xml, get its version as its release name
    $modfile = MODULE_PATH."/".$moduleName."/mod.xml";
        
    $xml = simplexml_load_file($modfile);
    $modVersion = $xml['Version'];
    $releaseName = $modVersion;
    
    // build_number = releasename_tagname_date_time
    $date = date('Ymd');
    $time = date('Hi');
    $buildNumber = $releaseName.'_T'.$tagName.'_'.$date.'_'.$time;
    return $buildNumber;
}

?>