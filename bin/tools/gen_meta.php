#!/usr/bin/env php
<?php
/*
 * Generate metadata from given database table
 * usage: php gen_meta.php dbname table [modulename]
 * Example:
 * # php gen_meta.php Default trac_ticket trac.ticket
    ---------------------------------------
	Please select metadata naming:
	1. module path: D:\Apache2\htdocs\ob\cubi\modules\trac\ticket, object name: TracTicket
	2. module path: D:\Apache2\htdocs\ob\cubi\modules\trac\ticket, object name: Ticket
	Please select: [1/2] 2
	---------------------------------------
	Target dir: D:\Zend\ServerCE\Apache2\htdocs\ob\cubi\modules\trac\ticket
	Medata file to create:
	  do/TicketDO.xml
	  form/Ticket...Form.xml
	  view/TicketView.xml
	Do you want to continue? [y/n] y
	...
 */

/* How to create a sub module by "specify a custom module path, object name and module name"
    #php gen_meta.php Default package_category
    ---------------------------------------
    Please select metadata naming:
    1. module path: \package_category, object name: PackageCategory, module name: package
    2. module path: \package_category, object name: Category, module name: package
    S. specify a custom module path, object name and module name
    Please select: [1/2/s] (1) : S
    Please set a module path: package/category
    Please set the component name: PackageCategory
    Please set the component display name: Package Category
    Please set the module name: package.category
    ...
 */
 
if ($argc<3) {
	echo "usage: php gen_meta.php dbname table [modulename] [metadata template set]".PHP_EOL;
	exit;
}

$dbname = $argv[1];
//if(!check_db)
$table = $argv[2];
$module = isset($argv[3]) ? $argv[3] : $table;
$metatpl = isset($argv[4]) ? $argv[4] : "metatpl";

define("META_TPL",$metatpl);

include_once dirname(dirname(__FILE__))."/app_init.php";

//include_once dirname(__FILE__)."/require_auth.php";

include_once dirname(__FILE__)."/gen_meta.inc.php";
if(!defined("CLI")){
	exit;
}
$moduleDir = MODULE_PATH.DIRECTORY_SEPARATOR.str_replace(".",DIRECTORY_SEPARATOR,$module);

// help user to set the metadata namings
$temp = explode("_",$table);
for ($i=0;$i<count($temp);$i++){
	$namings[$i] = array($moduleDir, 
						 getCompName($table,$i),	
						 getCompDisplayName($table,$i),	
						 $module);
}
echo "---------------------------------------".PHP_EOL;
echo "Please select metadata naming:".PHP_EOL;
for ($i=0; $i<count($namings); $i++) {
	echo ($i+1).". module path: ".str_replace(MODULE_PATH,"",$namings[$i][0]).
				", object name: ".$namings[$i][1].
				", module name: ".$namings[$i][3].
				PHP_EOL;
	$ques[]= $i+1;
}
echo "S. specify a custom module path, object name and module name".PHP_EOL;
echo "Please select: [".implode("/",$ques)."/s] (1) : ";
$n=0;
while(1) {
	$selection = trim(fgets(STDIN));
	$answer = intval($selection)-1;	
	if(strtolower($selection)=='s'){
		echo "Please set a module path: ";
        $module = trim(fgets(STDIN));
		$custom_opts[0] = MODULE_PATH.DIRECTORY_SEPARATOR.trim($module);
		
		echo "Please set the component name: ";
		$custom_opts[1] = trim(fgets(STDIN));
		
		echo "Please set the component display name: ";
		$custom_opts[2] = trim(fgets(STDIN));	

		echo "Please set the module name: ";
		$custom_opts[3] = trim(fgets(STDIN));	
        $module = $custom_opts[3];
		
		if($custom_opts[0] && $custom_opts[1]){
			break;
		}
	}else{
		$answer = $answer>-1?$answer:0;
		if (!isset($namings[$answer]) && $n++ < 3)
			echo "Please select again: [".implode("/",$ques)."/s] : ";
		else 
			break;
	}
}
if ($n > 3) exit;
if(is_array($custom_opts)){
	$opts = $custom_opts;
}else{
	$opts = $namings[$answer];
}

//print_r($opts);
echo PHP_EOL."Access control options: ".PHP_EOL;
echo "1. Access and Manage (default)".PHP_EOL;
echo "2. Access, Create, Update and Delete".PHP_EOL;
echo "3. No access control".PHP_EOL;
echo "Please select access control type [1/2/3] (1) : ";
$acl = trim(fgets(STDIN));
$acl = $acl?$acl:"1";

// check if the /modules/table is already there
echo PHP_EOL."---------------------------------------".PHP_EOL;
echo "Target dir: $opts[0]".PHP_EOL;
echo "Medata file to create: ".PHP_EOL;
echo "  do/$opts[1]DO.xml".PHP_EOL;
echo "  form/$opts[1]...Form.xml".PHP_EOL;
echo "  view/$opts[1]View.xml".PHP_EOL;
echo "Do you want to continue? [y/n] (y) : ";
// Read the input
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) != 'y')
	exit;
	
$metaGen = new MetaGenerator($module, $dbname, $table, $opts);
$metaGen->setACL($acl);

// create do xml
echo "---------------------------------------".PHP_EOL;
echo "Do you want to generate data Object? [y/n] (y) : ";
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) == 'y'){
	echo "Generate Data Object metadata file ...".PHP_EOL;
	$metaGen->genDOMeta();
}

// create forms xml
echo "---------------------------------------".PHP_EOL;
echo "Do you want to generate form Object? [y/n] (y) : ";
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) == 'y'){
	echo "Generate Form Object metadata files ...".PHP_EOL;
	$metaGen->genFormMeta();
}

// create view xml
echo "---------------------------------------".PHP_EOL;
echo "Do you want to generate view Object? [y/n] (y) : ";
// Read the input
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) == 'y'){
	echo "Generate view Object metadata files ...".PHP_EOL;	
	$metaGen->genViewMeta();
}

echo "---------------------------------------".PHP_EOL;
echo "Do you want to generate module dashboard files? [y/n] (y) : ";
// Read the input
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) == 'y'){
	// create mod.xml
	echo "Generate Module Dashboard ...".PHP_EOL;
	$metaGen->genDashboardXML();
}


//detects if mod.xml exists
$modFolder = getModuleName(strtolower($opts[3]));
$modFile = $moduleDir = MODULE_PATH . "/" . $modFolder."/mod.xml";
if(file_exists($modFile)){
echo "---------------------------------------".PHP_EOL;
	echo "Do you want to modify mod.xml? [y/n] (y) : ";
	// Read the input
	$answer = trim(fgets(STDIN));
	$answer = $answer?$answer:"y";
	if (strtolower($answer) == 'y'){
		// create mod.xml
		echo "Modify mod.xml ...".PHP_EOL;
		$metaGen->modifyModXML();
	}		
}else{
	echo "---------------------------------------".PHP_EOL;
	echo "Do you want to create mod.xml? [y/n] (y) : ";
	// Read the input
	$answer = trim(fgets(STDIN));
	$answer = $answer?$answer:"y";
	if (strtolower($answer) == 'y'){
		// create mod.xml
		echo "Generate mod.xml ...".PHP_EOL;
		$metaGen->genModXML();
	}	
}

/*
$tmplist = explode('.',$opts[3]);
$compName = $tmplist[0];
echo "---------------------------------------".PHP_EOL;
echo "Do you want to load this module $compName now? [y/n] (y) : ";
// Read the input
$answer = trim(fgets(STDIN));
$answer = $answer?$answer:"y";
if (strtolower($answer) == 'y'){
	// create mod.xml
	echo "Load module : ".$compName." ...".PHP_EOL;
	$script = dirname(__FILE__)."/load_module.php";
	$cmd = "$script ".$compName;
	exec($cmd,$output);
	$result = implode("\n", $output );
	echo $result."\n";
}
*/
?>
