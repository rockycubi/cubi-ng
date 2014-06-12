#!/usr/bin/env php
<?php
/*
 * Link DataObjects with their foreign keys
 * usage: php link_do.php do1name do2name
 * Example:
 * # php php link_do.php help.do.HelpDO help.do.HelpCategoryDO
        Select the 'help' foreign key column pointing to 'help_category':
            1. category_id (category_id)
        Please select a number: 1

        Please confirm the following before start:
            Table relationship: help.category_id ---> help_category.id
            The 'name' column of help_category is 'name'
        Start linking two data objects? (y/n) y
        --------------------------------------------------------
        Create a Join in help2.do.HelpDO ...
        Create a ObjReference in help2.do.HelpCategoryDO ...
        Apple join to C:\xampp\htdocs\ob24\cubi\modules/help2/form/HelpCopyForm.xml
        Apple join to C:\xampp\htdocs\ob24\cubi\modules/help2/form/HelpDetailForm.xml
        Apple join to C:\xampp\htdocs\ob24\cubi\modules/help2/form/HelpEditForm.xml
        Apple join to C:\xampp\htdocs\ob24\cubi\modules/help2/form/HelpListForm.xml
        Apple join to C:\xampp\htdocs\ob24\cubi\modules/help2/form/HelpNewForm.xml
        Done.
 */

if ($argc<3) {
	echo "usage: php link_do.php child_do parent_do".PHP_EOL;
	exit;
}

$do1name = $argv[1];
$do2name = $argv[2];

include_once (dirname(dirname(__FILE__))."/app_init.php");
include_once (MODULE_PATH."/tool/lib/DomMeta.php");
if(!defined("CLI")){
	exit;
}

$moduleDir = MODULE_PATH.DIRECTORY_SEPARATOR.str_replace(".",DIRECTORY_SEPARATOR,$module);

// read do1 
$do1 = BizSystem::getObject($do1name);
$do1Table = $do1->m_MainTable;
// read do2 
$do2 = BizSystem::getObject($do2name);
$do2Table = $do2->m_MainTable;

$fkCol = "";
foreach ($do1->m_BizRecord as $field) {
    if ($field->m_Column == "$do2name"."_id") {
        $fkCol = $field->m_Column;
    }
    
}
if ($fkCol != null) {
    echo "$do1name has foreign key column pointing to $do2name: $fkCol\n";
}
else {
    echo "Select the '$do1Table' foreign key column pointing to '$do2Table':\n";
    $i = 1;
    foreach ($do1->m_BizRecord as $field) {
        if (stripos($field->m_Column, "id")!==false && $field->m_Name != "Id") {
            echo "\t$i. $field->m_Name ($field->m_Column)\n";
            $i++;
        }
    }
    echo "Please select a number: ";
    $selection = trim(fgets(STDIN));
    $i = 1;
    foreach ($do1->m_BizRecord as $field) {
        if (stripos($field->m_Column, "id")!==false && $field->m_Name != "Id") {
            if ($i == $selection) {
                $fkCol = $field->m_Column;
                $fkName = $field->m_Name;
                break;
            }
            $i++;
        }
    }
}
if ($fkCol == "") { echo "Cannot find valid foreign key column in $do1name. Please try it again.\n"; exit; }

//ask for label name
$defaultLabel = ucwords(str_replace('_',' ',$do2Table));  	
echo "Default Label for Join Element is : [ ".$defaultLabel." ] \n";
echo "Would you like to specify a new label ?: ";
while(1) {
	$input = trim(fgets(STDIN));				
	if($input!=''){
		echo "New Label is : [ ".$input." ]\n";
		$defaultLabel=$input;
		break;
	}
	else
	{ 
		$defaultLabel=null;
		break;
	}
}    


//else echo "You selected '$fkCol' as the foreign key column.\n";
echo "\nPlease confirm the following before start:\n";
echo "    Table relationship: $do1Table.$fkCol ---> $do2Table.".$do2->getField("Id")->m_Column . "\n";
echo "    The 'name' column of $do2Table is 'name'\n";
echo "Start linking two data objects? (y/n) ";
if (trim(fgets(STDIN)) != 'y') exit;

$pkCol = $do2->getField('Id')->m_Column;

// generate a join in do1
echo "--------------------------------------------------------\n";
echo "Create a Join in $do1name ...\n";
$do1Dom = new DomMeta($do1name);
$joinName = "join_".$do2Table;
$elemAttrs = array( "Name"=>$joinName, "Table"=>$do2Table,
                    "Column"=>$pkCol, "ColumnRef"=>$fkCol, "JoinType"=>"INNER JOIN");
$do1Dom->RemoveElement("BizDataObj/TableJoins/Join", $joinName);
$do1Dom->AddElement("BizDataObj/TableJoins/Join", $elemAttrs, null);

$joinCol = "name"; $fieldName = $do2Table.'_'.$joinCol;
$elemAttrs = array( "Name"=>$fieldName, "Column"=>$joinCol, "Join"=>$joinName);
$do1Dom->RemoveElement("BizDataObj/BizFieldList/BizField", $fieldName);
$do1Dom->AddElement("BizDataObj/BizFieldList/BizField", $elemAttrs, null);

// create objref in do2
echo "Create a ObjReference in $do2name ...\n";

$do2Dom = new DomMeta($do2name);
$elemAttrs = array( "Name"=>$do1name, "Relationship"=>"1-M",
                    "Table"=>$do1Table, "Column"=>$fkCol, "FieldRef"=>"Id", "CascadeDelete"=>"Y");
$do2Dom->RemoveElement("BizDataObj/ObjReferences/Object", $do1name);
$do2Dom->AddElement("BizDataObj/ObjReferences/Object", $elemAttrs, null);

// apply join to forms
$forms = getDoForms($do1name);
foreach ($forms as $form) {
    echo "Apple join to $form\n";
    applyDoJoin2Form($form, $fieldName, $fkCol, $fkName, $defaultLabel);
}

echo "Done.\n";
exit;

function applyDoJoin2Form($formXml, $joinFieldName, $fkCol, $fkName=null,$defaultLabel=null) {
    global $do1name, $do2name;
    $formDom = new DomMeta($formXml);
    $doc = $formDom->GetDocDocument();
    $root = $doc->documentElement;
    $formType = $root->getAttribute("FormType");

    if($defaultLabel){
    	$label = $defaultLabel;
    }else{
      $label = ucwords(str_replace('_',' ',$joinFieldName));  	    	
    }

    
    
    // for List form, add a new element
    if (strtolower($formType) == "list") {
        $elemAttrs = array("Name"=>"fld_$joinFieldName", "Class"=>"ColumnText", "FieldName"=>$joinFieldName, "Label"=>$label);
        
        if($fkName!=null){        	        	
        	$formDom->RemoveElement("EasyForm/DataPanel/Element", $elemAttrs["Name"]);
        	$formDom->ReplaceElement("EasyForm/DataPanel/Element", $elemAttrs, 'fld_'.$fkName);
        }else{
			$formDom->RemoveElement("EasyForm/DataPanel/Element", $elemAttrs["Name"]);
        	$formDom->AddElement("EasyForm/DataPanel/Element", $elemAttrs, null);        	
        }
    }
    // for other form, change fkcol field to listbox
    else if (strtolower($formType) == "edit" || strtolower($formType) == "new") {
        $elemAttrs = array("Name"=>"fld_$fkCol", "Class"=>"Listbox", "FieldName"=>$fkCol, "Label"=>$label, "SelectFrom"=>$do2name."["."name:Id"."]");
        $formDom->SaveElement("EasyForm/DataPanel/Element", $elemAttrs);
    }
    else {
        $elemAttrs = array("Name"=>"fld_$joinFieldName", "Class"=>"LabelText", "FieldName"=>$joinFieldName, "Label"=>$label);
        $formDom->RemoveElement("EasyForm/DataPanel/Element", $elemAttrs["Name"]);
        $formDom->AddElement("EasyForm/DataPanel/Element", $elemAttrs, null);
    }
}

function getDoForms($doName) {
    global $g_MetaFiles;
    $name_list = explode(".",$doName);
    $moduleName = $name_list[0];
    $modulePath = MODULE_PATH."/".$moduleName;
    php_grep(array("<EasyForm","DataObj=\"$doName\""), $modulePath);
    
    $forms = $g_MetaFiles;
    $g_MetaFiles = array();
    return $forms;
}

$g_MetaFiles = array();

function php_grep($q, $path)
{    
    global $g_MetaFiles;
    $fp = opendir($path);
    while($f = readdir($fp))
    {
    	if ( preg_match("#^\.+$#", $f) ) continue; // ignore symbolic links
    	$file_full_path = $path.'/'.$f;
    	if(is_dir($file_full_path)) 
    	{
    		php_grep($q, $file_full_path);
    	} 
    	else 
    	{
    		$path_parts = pathinfo($f);
    		if ($path_parts['extension'] != 'xml') continue; // consider only xml files
    		
    		//echo file_get_contents($file_full_path); exit;
            $content = file_get_contents($file_full_path);
            $match = 1;
            foreach ($q as $_q) {
                if( !stristr($content, $_q)) {
                    $match = 0; break;
                }
            }
            if ($match) $g_MetaFiles[] = $file_full_path;
    	}
    }
}

?>