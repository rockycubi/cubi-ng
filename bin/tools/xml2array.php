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


xml2ArrayFile(APP_HOME, 'application.xml');
convertFilesInFolder(OPENBIZ_META);
convertFilesInFolder(APP_HOME . DIRECTORY_SEPARATOR . 'modules');

echo "========================================\n";


function convertFilesInFolder($dir) {

    echo "Location : $dir \n";

    $files = scandir($dir);

    //print_r($files);
    foreach ($files as $file) {

        if ($file != '.' && $file != '..') {

            $fileWithPath = $dir . DIRECTORY_SEPARATOR . $file;

            if (!is_dir($fileWithPath)) {
                if (substr($file, -3) == 'xml') { // if XML file
                    xml2ArrayFile($dir, $file);
                }
            } else {
                if (strpos($fileWithPath, 'modules/tool'))                    
                    continue;
                
                convertFilesInFolder($fileWithPath);
            }
        }
    }
}

function xml2ArrayFile($dir, $file) {
    $fileWithPath = $dir . DIRECTORY_SEPARATOR . $file;
    echo $fileWithPath . "\n";
    $xmlArr = Resource::getXmlArray($fileWithPath);

    $baseFile = substr($file, 0, strlen($file) - 4);
    $phpFile = $baseFile . '.conf.php';
    $phpFileWithPath = $dir . DIRECTORY_SEPARATOR . $phpFile;

    $text = "<?php \n";
    $text .= 'return ';
    $text .= var_export($xmlArr, true);
    $text .= ';';
    //file_puts_contents('/home/k6/public_html/synfac/myfile.php', $text);

    $fh = fopen($phpFileWithPath, "w") or die("Could not open log file.");
    fwrite($fh, $text) or die("Could not write file!");
    fclose($fh);
}

?>
