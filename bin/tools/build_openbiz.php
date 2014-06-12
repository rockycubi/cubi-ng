<?php
include_once ("../app_init.php");
if(!defined("CLI")){
	exit;
}

echo "---------------------------------------\n";
execPhing("openbiz.xml");

function execPhing($buildFile, $options=null)
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


?>