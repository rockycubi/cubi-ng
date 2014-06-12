<?php

include_once ("../app_init.php");

include_once(OPENBIZ_BIN . "util/xmltoarray.src.php");

$dir = APP_HOME . '/modules';

//echo '<pre>';
//echo $dir . "\n";
echo "\n\n\n";
echo "========================================\n";
echo "========================================\n";
echo "XML To Php Config (Array) Converter\n";
echo "========================================\n";
delArrayConfigFile(APP_HOME, 'application.conf.php');
delFilesInFolder(APP_HOME . DIRECTORY_SEPARATOR . 'modules');
delFilesInFolder(OPENBIZ_META);

echo "========================================\n";


function delFilesInFolder($dir) {

    echo "Location : $dir \n";

    $files = scandir($dir);

    //print_r($files);
    foreach ($files as $file) {

        if ($file != '.' && $file != '..') {

            $fileWithPath = $dir . DIRECTORY_SEPARATOR . $file;

            if (!is_dir($fileWithPath)) {
                if (substr($file, -8) == 'conf.php') { // if XML file
                    delArrayConfigFile($dir, $file);
                }
            } else {
                if (strpos($fileWithPath, 'modules/tool'))                    
                    continue;
                
                delFilesInFolder($fileWithPath);
            }
        }
    }
}

function delArrayConfigFile($dir, $file) {
    $fileWithPath = $dir . DIRECTORY_SEPARATOR . $file;
    unlink($fileWithPath);
}

?>
