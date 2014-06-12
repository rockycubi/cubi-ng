<?php
// install openbiz modules

include_once ("app_init.php");

// scan the /modules 
$mods = array();
$dir = MODULE_PATH;
if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
        $filepath = $dir.'/'.$file;
        if (is_dir($filepath))
        {
            //echo "filename: $file : filetype: " . filetype($dir.'/'.$file) . "\n";
            $modfile = $filepath.'/mod.xml';
            if (file_exists($modfile))
                $mods[$file] = $modfile;
        }
    }
    closedir($dh);
}

// find all modules
foreach ($mods as $mod=>$modfile)
{
    installModule($mod, $modfile);
}

function installModule($mod, $modfile)
{
    $xml = simplexml_load_file($modfile);
    
    global $g_BizSystem;
    $db = $g_BizSystem->getDBConnection();
    
    // write mod info in module table
    $modName = $xml['Name'];
    $modDesc = $xml['Description'];
    echo "****************************\n";
    echo " Install Module $modName \n";
    echo "****************************\n";
    $sql = "SELECT * from module where name='$modName'";
    try {
        $rs = $db->fetchAll($sql);
    }
    catch (Exception $e)
    {
        echo "ERROR: ".$e->getMessage()."\n";
        exit;
    }
    if (count($rs)>0)
        $sql = "UPDATE module SET description='$modDesc' WHERE name='$modName'";
    else
        $sql = "INSERT INTO module (name, description) VALUES ('$modName', '$modDesc');";
    echo $sql;
    try {
        $db->query($sql);
    }
    catch (Exception $e)
    {
        echo "ERROR: ".$e->getMessage()."\n";
        exit;
    }
    echo "\n";

    if (isset($xml->ACL) && isset($xml->ACL->Resource))
    {
        // write mod/acl in acl_action table
        foreach ($xml->ACL->Resource as $res)
        {
            $resName = $res['Name'];
            echo "\n--- Install ACL for resource $resName ---\n";
            foreach ($res->Action as $act)
            {
                $actName = $act['Name'];
                $actDesc = $act['Description'];
                $sql = "SELECT * from acl_action where module='$modName' AND resource='$resName' AND action='$actName'";
                try {
                    $rs = $db->fetchAll($sql);
                }
                catch (Exception $e)
                {
                    echo "ERROR: ".$e->getMessage()."\n";
                    exit;
                }
                if (count($rs)>0)
                    $sql = "UPDATE acl_action SET description='$actDesc' WHERE module='$modName' AND resource='$resName' AND action='$actName'";
                else
                    $sql = "INSERT INTO acl_action (module, resource, action, description) VALUES ('$modName', '$resName','$actName', '$actDesc');";
                echo $sql;
                try {
                    $db->query($sql);
                }
                catch (Exception $e)
                {
                    echo "ERROR: ".$e->getMessage()."\n";
                    exit;
                }
                echo "\n";
            }
        }
    }
}

?>