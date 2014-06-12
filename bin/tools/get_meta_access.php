<?php
/*
 * get metadata access settings
 */

if ($argc<2) {
	echo "usage: php get_meta_access.php module".PHP_EOL;
	exit;
}
$module = $argv[1];

include_once (dirname(dirname(__FILE__))."/app_init.php");
if(!defined("CLI")){
	exit;
}

include_once dirname(__FILE__)."/require_auth.php";

// check if the /modules/table is already there
$moduleDir = MODULE_PATH."/".str_replace(".","/",$module);

echo "---------------------------------------".PHP_EOL;
echo "Defined resource actions:".PHP_EOL;
$res_acts = list_mod_accesses($moduleDir); 

echo "---------------------------------------".PHP_EOL;
echo "Defined metadata access:".PHP_EOL;
$suggestionList = array();
search_files($moduleDir);

echo "---------------------------------------".PHP_EOL;
echo "Suggested metadata access:".PHP_EOL;
//print_r($suggestionList);
foreach ($suggestionList as $k=>$v) {
    if (stripos($k, "view.xml") !== false) {
        if ($v) {
            echo " - $k, $v.  User suggested access? [y/n] (y) ";
            $answer = trim(fgets(STDIN));
            $answer = $answer?$answer:"y";
            if (strtolower($answer) == 'y')
                setAttribute($moduleDir.$k, 'Access', $v);
        }
        else {
            $opts = "";
            foreach ($res_acts as $i=>$tmp) {
                $r = $tmp[0]; $a = $tmp[1];
                $opts .= "\n   $i. ".$r.".".$a;
            }
            echo " - $k";
            echo $opts;
            echo "\n   Please select one? ";
            $answer = trim(fgets(STDIN));
            if (isset($res_acts[$answer])) {
                setAttribute($moduleDir.$k, 'Access', $r.".".$a);
            }
        }
    }
}

function setAttribute($xmlfile, $attrName, $attrVal)
{
    $doc = new DomDocument();
    $doc->load($xmlfile);

    $elem = $doc->documentElement;
    $elem->setAttribute($attrName, $attrVal);
    
    // save xml file
    $doc->formatOutput = true;
    $doc->save($xmlfile);
}

function list_mod_accesses($moduleDir)
{
    $modfile = $moduleDir."/mod.xml";
    $doc = new DomDocument();
    if (!$doc) return false;
    $ok = $doc->load($modfile);
    if (!$ok)
        return null;
    
    $xpath = new DOMXPath($doc);
    $xpathStr = "//Action";
    $elems = $xpath->query($xpathStr);
    foreach ($elems as $elem) {
        $resourceName = $elem->parentNode->getAttribute('Name');
        $actionName = $elem->getAttribute('Name');
        echo " - $resourceName, ".$actionName.PHP_EOL;
        $res_acts[] = array($resourceName, $actionName);
    }
    return $res_acts;
}

function search_files($path)
{    
    global $moduleDir, $res_acts, $suggestionList;
    $fp = opendir($path);
    while($f = readdir($fp))
    {
    	if ( preg_match("#^\.+$#", $f) ) continue; // ignore symbolic links
        if ($f=='mod.xml') continue;
    	$file_full_path = $path.'/'.$f;
    	if(is_dir($file_full_path)) 
    	{
    		search_files($file_full_path);
    	} 
    	else 
    	{
    		$path_parts = pathinfo($f);
    		if ($path_parts['extension'] == 'xml') {
                $xml = simplexml_load_file($file_full_path);
                $fname = str_replace($moduleDir,'',$file_full_path);
                if (isset($xml['Access']) && $xml['Access']!='') {
                    echo " - $fname, Access=".$xml['Access'].PHP_EOL;
                }
                else {
                    echo " - $fname, Access=".PHP_EOL;
                    $suggestedAccess = "";
                    foreach ($res_acts as $tmp) {
                        $k = $tmp[0]; $v = $tmp[1];
                        $kls = preg_split("/[._-\s]+/", $v);
                        $_k = $kls[count($kls)-1];
                        //print_r($tmp); echo "$_k, ".$xml['Name'].PHP_EOL; exit;
                        if (stripos($xml['Name'], $_k) !== false) {
                            $suggestedAccess = $k.".".$v;
                            break;
                        }
                    }
                    $suggestionList[$fname] = $suggestedAccess;
                }
    		} 
    	}
    }
}
?>