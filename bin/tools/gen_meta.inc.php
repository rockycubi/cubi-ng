<?php

/**
 * Cubi Application Platform
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */

/**
 * MetaGenerator class
 *
 * MetaGenerator generate CRUD metadata and template from database table
 *
 * @package   cubi.bin.tools
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2010, Rocky Swen
 * @access    public
 */
class MetaGenerator
{

    /**
     * module name
     * @var string
     */
    public $module;
    /**
     * database name alias in Config.xml
     * @var string
     */
    public $dbname;
    /**
     * table name
     * @var string
     */
    public $table;
    /**
     * option
     * @var array
     */
    public $opts;
    /**
     * Database information that stored in Config.xml
     * @var array
     */
    public $dbConfig;
    /**
     * Object that generate Data object metafile
     * @var DOGenerator
     */
    public $doGen;
    /**
     * Object that generate Form object metafile
     * @var FormGenerator
     */
    public $formGen;
    /**
     * Object that generate View object metafile
     * @var ViewGenerator
     */
    public $viewGen;
    
    public $acl;
    
    public $dashboard_enable=0;

    /**
     * Initialize object
     * @param string $module module name
     * @param string $dbname database name alias in configuration file
     * @param string $table database table name
     * @param array $opts more option
     * @return void
     */
    function __construct($module, $dbname, $table, $opts)
    {
        $this->module = $module;
        $this->dbname = $dbname;
        $this->table = $table;
        $this->opts = $opts;
        $this->dbConfig = BizSystem::configuration()->getDatabaseInfo($dbname);
    }

    /**
     * Generate DataObject metafile
     * @return string file name of data object metafile
     */
    public function genDOMeta()
    {
        $this->doGen = new DOGenerator($this->module, $this->dbname, $this->table, $this->dbConfig, $this->opts);
        $this->doGen->prepareData();
        $doFile = $this->doGen->generateDO();
        return $doFile;
    }

    /**
     * Generate Form metafile
     * @return array array that contain file name of Form object metafile
     */
    public function genFormMeta()
    {
        if (!$this->doGen)
        {
            $this->doGen = new DOGenerator($this->module, $this->dbname, $this->table, $this->dbConfig, $this->opts);
            $this->doGen->prepareData();
        }
        $this->formGen = new FormGenerator($this->module, $this->doGen, $this->opts);
        $formFiles = $this->formGen->generateAllForms();
        return $formFiles;
    }

    /**
     * Generate View object metafile
     * @return string file name of View object metafile
     */
    public function genViewMeta()
    {
        if (!$this->formGen)
        {
            $this->doGen = new DOGenerator($this->module, $this->dbname, $this->table, $this->dbConfig, $this->opts);
            $this->doGen->prepareData();
            $this->formGen = new FormGenerator($this->module, $this->doGen, $this->opts);
        }
        $viewGen = new ViewGenerator($this->module, $this->formGen, $this->opts);
        $viewFile = $viewGen->generateView();
        return $viewFile;
    }

    /**
     * Generate module information metafile
     * @return string file name of module metafile
     */
    public function genModXML()
    {    	
        $modGen = new ModGenerator($this->module, $this->opts, $this->dashboard_enable);
        $modFile = $modGen->generateMod($this->table);
        return $modFile;
    }

	public function modifyModXML()
    {    	
        $modGen = new ModGenerator($this->module, $this->opts, $this->dashboard_enable);
        $modFile = $modGen->modifyMod($this->table);
        return $modFile;
    }    
    
    public function genDashboardXML()
    {
    	$this->dashboard_enable = 1;
        $modGen = new DashboardGenerator($this->module, $this->opts);
        $modFile = $modGen->generateFiles($this->table);
        return $modFile;    	
    }
    
    public function setACL($acl)
    {
        $resource = strtolower($this->opts[3]); // strtolower($this->opts[3]).'.'.strtolower($this->opts[1]);
        switch ($acl) {
            case 1: 
                $this->opts['acl'] = array('access'=>$resource.'.Access', 'manage'=>$resource.'.Manage', 
                                           'create'=>$resource.'.Manage', 'update'=>$resource.'.Manage', 'delete'=>$resource.'.Manage'); 
                break;
            case 2: 
                $this->opts['acl'] = array('access'=>$resource.'.Access', 'manage'=>$resource.'.Manage', 
                                           'create'=>$resource.'.Create', 'update'=>$resource.'.Update', 'delete'=>$resource.'.Delete'); 
                break;
            case 3: 
                $this->opts['acl']['resource'] = '';
                $this->opts['acl'] = array('access'=>'', 'manage'=>'', 'create'=>'', 'update'=>'', 'delete'=>''); 
                break;
        }
        $this->opts['acl']['option'] = $acl;
        $this->opts['acl']['resource'] = $resource;
    }
}

class DashboardGenerator
{
    const DASHBOARD_TEMPLATE = "/dashboard_Template.xml";
    const DASHBOARDVIEW_TEMPLATE = "/dashboard_view_Template.xml";
    const LEFTMENU_TEMPLATE = "/leftmenu_Template.xml";
    
    /**
     * Module name
     * @var string
     */
    public $module;
    
    public $opts;

    /**
     * Initialize
     *
     * @param string $module module name
     * @return void
     */
    function __construct($module, $opts)
    {
        $this->module = $module;
        $this->opts = $opts;
    }
    
	public function generateDashboardWidget($table)
	{
		if(CLI){echo "Start generate DashboardForm.xml ." . PHP_EOL;}
        $targetPath = $moduleDir = MODULE_PATH . "/" . getModuleName($this->module)."/widget";
		$targetFile = $targetPath . "/DashboardForm.xml";
		        
        if (!file_exists($targetPath))
        {
            if(CLI){echo "Create directory $targetPath" . PHP_EOL;}
            mkdir($targetPath, 0777, true);
        }

        if(file_exists($targetFile)){
			if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." exists skipped." . PHP_EOL. PHP_EOL;}
			return ; 
        }            
        
        $smarty = BizSystem::getSmartyTemplate();

        $smarty->assign_by_ref("module_name", getModuleName($this->module));
        $smarty->assign_by_ref("module", $this->module);

        $tpl_file = dirname(__FILE__) . '/' . META_TPL. self::DASHBOARD_TEMPLATE;
        $content = $smarty->fetch($tpl_file);

        // target file        
        file_put_contents($targetFile, $content);
        if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." is generated." . PHP_EOL. PHP_EOL;}
        return $targetFile;		
	}
	
	public function generateDashboardView($table)
	{
		if(CLI){echo "Start generate DashboardView.xml ." . PHP_EOL;}
        $targetPath = $moduleDir = MODULE_PATH . "/" . getModuleName($this->module)."/view";
        $targetFile = $targetPath . "/DashboardView.xml";
        if (!file_exists($targetPath))
        {
            if(CLI){echo "Create directory $targetPath" . PHP_EOL;}
            mkdir($targetPath, 0777, true);
        }

        if(file_exists($targetFile)){
			if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." exists and skipped." . PHP_EOL. PHP_EOL;}
			return ; 
        }    
                
        $smarty = BizSystem::getSmartyTemplate();

        $smarty->assign_by_ref("module_name", getModuleName($this->module));
        $smarty->assign_by_ref("module", $this->module);

        $tpl_file = dirname(__FILE__) . '/' . META_TPL. self::DASHBOARDVIEW_TEMPLATE;
        $content = $smarty->fetch($tpl_file);

        // target file        
        file_put_contents($targetFile, $content);
        if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." is generated." . PHP_EOL. PHP_EOL;}
        return $targetFile;		
	}
		
	
	public function generateLeftmenu($table)
	{		
		if(CLI){echo "Start generate LeftMenu.xml ." . PHP_EOL;}
        $targetPath = $moduleDir = MODULE_PATH . "/" . getModuleName($this->module)."/widget";
        $targetFile = $targetPath . "/LeftMenu.xml";
        if (!file_exists($targetPath))
        {
            if(CLI){echo "Create directory $targetPath" . PHP_EOL;}
            mkdir($targetPath, 0777, true);
        }

        if(file_exists($targetFile)){
			if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." exists and skipped." . PHP_EOL. PHP_EOL;}
			return ; 
        }        
        
        $smarty = BizSystem::getSmartyTemplate();

        $smarty->assign_by_ref("module_name", getModuleName($this->module));
        $smarty->assign_by_ref("module", $this->module);

        $tpl_file = dirname(__FILE__) . '/' . META_TPL. self::LEFTMENU_TEMPLATE;
        $content = $smarty->fetch($tpl_file);

        // target file
        
        file_put_contents($targetFile, $content);
        if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." is generated." . PHP_EOL. PHP_EOL;}
        return $targetFile;			
	}
	
	public function modifyViewTpl()
	{
		if(CLI){echo "Start modify view.tpl to enable module left menu supports ." . PHP_EOL;}
        $targetPath = $moduleDir = MODULE_PATH . "/" . getModuleName($this->module)."/template";
        $targetFile = $targetPath . "/view.tpl";

        $str = '
$left_menu = "'.strtolower(getModuleName($this->module)).'.widget.LeftMenu";
$this->assign("left_menu", $left_menu);
';        
        
        $content = file_get_contents($targetFile);
        if(!preg_match("/widget\.LeftMenu/si",$content)){
        	$content = str_replace("{php}","{php}".$str,$content);
        }else{        
			if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." was modified and skipped." . PHP_EOL. PHP_EOL;}
			return ;
        }
        

        file_put_contents($targetFile, $content);
        if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." is modified." . PHP_EOL. PHP_EOL;}
        return $targetFile;				
	}
	
	public function generateFiles($table)
	{
		$this->generateDashboardWidget($table);
		$this->generateDashboardView($table);
		$this->generateLeftmenu($table);
		$this->modifyViewTpl($table);
	}
}

/**
 * DOGenerator class
 *
 * Generate DataObject (DO) metafile
 *
 * @package   cubi.bin.tools
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2010, Rocky Swen
 * @access    public
 */
class DOGenerator
{
    const DO_TEMPLATE = "/d_Template.xml";

    /**
     * module name
     * @var string
     */
    public $module;
    /**
     * database name that configured in Config.xml
     * @var string
     */
    public $db_name;
    /**
     * table name
     * @var string
     */
    public $table_name;
    /**
     * database configuration that store in Config.xml
     * @var array
     */
    public $db_config;
    /**
     * Option
     * @var array
     */
    public $opts;
    /**
     * DataObject name with "do" namespace/package
     * @var string 
     */
    public $do_name;
    /**
     * DataObject name without "do" namespace/package
     * @var string
     */
    public $do_short_name;
    public $tableIndex, $uniqueness, $fields, $id_identity;

    /**
     * Initialize Object
     * @param string $module module name
     * @param string $db_name database name alias in Config.xml
     * @param string $table_name table name
     * @param array $db_config database config
     * @param array $opts optional
     */
    function __construct($module, $db_name, $table_name, $db_config, $opts)
    {
        $this->module = $module;
        $this->db_name = $db_name;
        $this->table_name = $table_name;
        $this->db_config = $db_config;
        $this->opts = $opts;
        $this->do_name = "do." . $opts[1] . "DO";
        $this->do_short_name = $opts[1] . "DO";
    }

    /**
     * Prepare Data
     * @return void
     */
    public function prepareData()
    {
        $db = BizSystem::dbConnection($this->db_name);
        if (!$db)
        {
            if(CLI){echo "ERROR: Cannot connect to database $this->db_name" . PHP_EOL;}
            exit;
        }
        $this->getTableIndex();
        $this->getDOFields();
    }

    /**
     * Generate DataObject metafile
     * @return string
     */
    public function generateDO()
    {
        if(CLI){echo "Start generate dataobject $this->do_short_name." . PHP_EOL;}
        $targetPath = $moduleDir = MODULE_PATH . "/" . str_replace(".", "/", $this->module) . "/do";
        if (!file_exists($targetPath))
        {
            if(CLI){echo "Create directory $targetPath" . PHP_EOL;}
            mkdir($targetPath, 0777, true);
        }

        $smarty = BizSystem::getSmartyTemplate();

        $smarty->assign_by_ref("do_name", $this->do_name);
        $smarty->assign_by_ref("do_short_name", $this->do_short_name);
        $smarty->assign_by_ref("comp", $this->module);
        $smarty->assign_by_ref("db_name", $this->db_name);
        $smarty->assign_by_ref("table_name", $this->table_name);
        $smarty->assign_by_ref("fields", $this->fields);
        $smarty->assign_by_ref("uniqueness", $this->uniqueness);
        $smarty->assign_by_ref("id_identity", $this->id_identity);
        $smarty->assign_by_ref("sort_column", $this->sort_column);
        $smarty->assign_by_ref("acl", $this->opts['acl']);

        $tpl_file = dirname(__FILE__) . '/' .META_TPL. self::DO_TEMPLATE;
        $content = $smarty->fetch($tpl_file);

        // target file
        $targetFile = $targetPath . "/" . $this->do_short_name . ".xml";

        file_put_contents($targetFile, $content);
        if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." is generated." . PHP_EOL;}
        return $targetFile;
    }

    /**
     * Get DataObject Fields
     * @return void
     */
    protected function getDOFields()
    {
        $db = BizSystem::dbConnection($this->db_name);
        $tblCols = $db->describeTable($this->table_name);
        $db_config = $this->db_config;
        $i = 0;
        if(is_array($tblCols["sort_order"])){
            $this->sort_column= "sort_order";
        }   
        foreach ($tblCols as $colName => $colAttrs)
        {
            // special handling on the primary key column(s)
            // for simple, just consider simple primary key case
            if ($colAttrs['PRIMARY'] == true)
            {
                $fields[$i]['name'] = 'Id';
                $this->do['id_identity'] = $colAttrs['IDENTITY'];
                $this->id_identity= true;                
            }
            else
                $fields[$i]['name'] = $colName;

            $fields[$i]['col'] = $colName;
            $fields[$i]['nullable'] = $colAttrs['NULLABLE'];
            $fields[$i]['length'] = $colAttrs['LENGTH'];
            // different db engine has different type name, but need to convert them to DO types.
            $fields[$i]['type'] = convertDataType($colAttrs['DATA_TYPE'], $db_config['Driver']);
            $fields[$i]['element'] = getDataElement($colAttrs['DATA_TYPE'], $db_config['Driver']);
            $fields[$i]['options'] = getDataOptions($colAttrs['DATA_TYPE'], $db_config['Driver']);
            $fields[$i]['raw_type'] = $colAttrs['DATA_TYPE'];
            if ($colAttrs['DEFAULT'] != "CURRENT_TIMESTAMP")
            {
                $fields[$i]['default'] = $colAttrs['DEFAULT'];
            }
            if(!isset($fields[$i]['default']) && $colName=='name'){
            	$fields[$i]['default']="New ".$this->opts[2];
            }
            $i++;
        }
        $this->fields = $fields;
    }

    /**
     * Load table index and uniqueness information
     * @return void
     */
    protected function getTableIndex()
    {
        $db = BizSystem::dbConnection($this->db_name);
        $db_driver = $this->db_config['Driver'];
        switch (strtoupper($db_driver))
        {
            case 'PDO_MYSQL':
                $sql = "SHOW INDEX FROM ".$this->db_config["DBName"]."."."$this->table_name;";
                $result = $db->query($sql);
                $tblIndexes = $result->fetchAll();
                break;
            default:
                break;
        }
        $tableIndex = array();
        if ($tblIndexes)
        {
            foreach ($tblIndexes as $colIndex)
            {
                $non_unique = $colIndex[1];
                $key_name = $colIndex[2];
                $col_name = $colIndex[4];
                if ($key_name != "PRIMARY" && $tblCols[$col_name]['DATA_TYPE'] != 'int')
                {
                    //$tableIndex[$key_name]=array();
                    $indexInfo = array("NON_UNIQUE" => $non_unique,
                        "KEY_NAME" => $key_name,
                        "COL_NAME" => $col_name);
                    if (!is_array($tableIndex[$key_name]))
                    {
                        $tableIndex[$key_name] = array();
                    }
                    array_push($tableIndex[$key_name], $indexInfo);
                }
            }
        }
        $this->tableIndex = $tableIndex;

        $uniqueness = "";
        foreach ($tableIndex as $key_name => $key_index)
        {
            $key_uniqueness = "";
            foreach ($key_index as $indexInfo)
            {
                if ($indexInfo['NON_UNIQUE'] == "0")
                {
                    if ($key_uniqueness != "")
                    {
                        $key_uniqueness.=",";
                    }
                    $key_uniqueness .= $indexInfo['COL_NAME'];
                }
            }
            if ($key_uniqueness != "")
            {
                $uniqueness .= $key_uniqueness . ";";
            }
        }
        $this->uniqueness = $uniqueness;
    }

}

/**
 * FormGenerator class
 *
 * Generate FormObject (DO) metafile
 *
 * @package   cubi.bin.tools
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2010, Rocky Swen
 * @access    public
 */
class FormGenerator
{
    const LIST_TEMPLATE = "/f_TemplateList.xml";
    const DETAIL_TEMPLATE = "/f_TemplateDetail.xml";
    const EDIT_TEMPLATE = "/f_TemplateEdit.xml";
    const NEW_TEMPLATE = "/f_TemplateNew.xml";
    const COPY_TEMPLATE = "/f_TemplateCopy.xml";
    const DETAIL_TPL = "/f_TemplateDetail.tpl";
    const DETAIL_ES_TPL = "/f_TemplateDetailElementSet.tpl";   
    const GRID_TPL = "/f_TemplateGrid.tpl";
    /**
     * Module name
     * @var string
     */
    public $module;
    /**
     * view name
     * @var string
     */
    public $view_name;
    /**
     * More option parameters
     * @var array
     */
    public $opts;
    /**
     * Form name of data list
     * @var string
     */
    public $list_form;
    /**
     * Form name of new entry form
     * @var string 
     */
    public $new_form;
    /**
     * Form name of edit entry form
     * @var <type>
     */
    public $edit_form;
    /**
     * Form name of copy entry form
     * @var string
     */
    public $copy_form;
    /**
     * Form name of detail form
     * @var string
     */
    public $detail_form;
    /**
     * DOGenerator object
     * @var DOGenerator
     */
    public $doGenerator;
    /**
     * search columns
     * @var array
     */
    public $search_cols = array();
    /**
     * Message file name
     * @var string
     */
    public $message_file = "";
    /**
     * Event name
     * @var string
     */
    public $event_name = "";
    /**
     * Form object class name, default is EasyForm
     * @var string
     */
    public $form_obj_class = "EasyForm";
    /**
     * ACL name
     * @var string
     */
    public $acl_name = "";

    /**
     * Initialize
     *
     * @param string $module module name
     * @param DOGenerator $doGenerator DOGenerator object
     * @param array $opts
     * @return void
     */
    function __construct($module, $doGenerator, $opts)
    {
        $this->module = $module;
        $this->doGenerator = $doGenerator;
        $table_name = $doGenerator->table_name;
        $this->opts = $opts;
        $this->view_name = 'view.' . $opts[1] . 'View';
        $this->list_form = 'form.' . $opts[1] . 'ListForm';
        $this->new_form = 'form.' . $opts[1] . 'NewForm';
        $this->edit_form = 'form.' . $opts[1] . 'EditForm';
        $this->copy_form = 'form.' . $opts[1] . 'CopyForm';
        $this->detail_form = 'form.' . $opts[1] . 'DetailForm';

        foreach ($doGenerator->tableIndex as $index)
        {
            foreach ($index as $search)
            {
                array_push($this->search_cols, $search);
            }
        }
        //$this->form_obj_class = getCompName($table_name)."Form";
        //$this->event_name = getEventName($this->doGenerator->table_name);
    }

    /**
     * Generate all form metafiles
     *
     * @return array list of form file name
     */
    public function generateAllForms()
    {
        $tplDir = dirname(__FILE__) . '/';

        // copy templates file grid.tpl and detail.tpl
        copyTemplateFile("detail.tpl", $tplDir . META_TPL. self::DETAIL_TPL, $this->module);
		copyTemplateFile("detail_elementset.tpl", $tplDir . META_TPL. self::DETAIL_ES_TPL, $this->module);
        copyTemplateFile("grid.tpl", $tplDir . META_TPL. self::GRID_TPL, $this->module);
        
 
        $formFiles[] = $this->generateForm($this->list_form, $tplDir . META_TPL. self::LIST_TEMPLATE);
        $formFiles[] = $this->generateForm($this->new_form, $tplDir . META_TPL. self::NEW_TEMPLATE);
        $formFiles[] = $this->generateForm($this->edit_form, $tplDir . META_TPL. self::EDIT_TEMPLATE);
        $formFiles[] = $this->generateForm($this->detail_form, $tplDir . META_TPL. self::DETAIL_TEMPLATE);
        $formFiles[] = $this->generateForm($this->copy_form, $tplDir . META_TPL. self::COPY_TEMPLATE);
        return $formFiles;
    }

    /**
     * Generate form metafile
     *
     * @param string $form_name form name
     * @param string $tpl_file name of template file
     * @return string
     */
    public function generateForm($form_name, $tpl_file)
    {
        $do_short_name = $this->doGenerator->do_short_name;
        $form_short_name = str_replace("form.", "", $form_name);

        if(CLI){echo "Start generate form object $form_short_name." . PHP_EOL;}
        $targetPath = $moduleDir = MODULE_PATH . "/" . str_replace(".", "/", $this->module) . "/form";
        if (!file_exists($targetPath))
        {
            if(CLI){echo "Create directory $targetPath" . PHP_EOL;}
            mkdir($targetPath, 0777, true);
        }

        $smarty = BizSystem::getSmartyTemplate();
		
        $module_name = $this->opts[2];
        $smarty->assign_by_ref("do_name", $this->doGenerator->do_name);
        $smarty->assign_by_ref("do_short_name", $this->doGenerator->do_short_name);
        $smarty->assign_by_ref("comp", $this->module);
        $smarty->assign_by_ref("table_name", $this->doGenerator->table_name);
        $smarty->assign_by_ref("fields", $this->doGenerator->fields);
        $smarty->assign_by_ref("view_name", $this->view_name);
        $smarty->assign_by_ref("form_name", $form_name);
        $smarty->assign_by_ref("form_short_name", $form_short_name);
        $smarty->assign_by_ref("list_form", $this->list_form);
        $smarty->assign_by_ref("new_form", $this->new_form);
        $smarty->assign_by_ref("copy_form", $this->copy_form);
        $smarty->assign_by_ref("edit_form", $this->edit_form);
        $smarty->assign_by_ref("detail_form", $this->detail_form);
        $smarty->assign_by_ref("searchs", $this->search_cols);
        $smarty->assign_by_ref("acl_name", $this->acl_name);
        $smarty->assign_by_ref("event_name", $this->event_name);
        $smarty->assign_by_ref("message_file", $this->message_file);
        $smarty->assign_by_ref("form_obj_class", $this->form_obj_class);
		$smarty->assign_by_ref("module_name", $module_name);
        $smarty->assign_by_ref("acl", $this->opts['acl']);
        
        $content = $smarty->fetch($tpl_file);

        // target file
        $targetFile = $targetPath . "/" . $form_short_name . ".xml";
        file_put_contents($targetFile, trimEmptyLines($content));
        if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." is generated." . PHP_EOL. PHP_EOL;}
        return $targetFile;
    }

}

/**
 * ViewGenerator class
 *
 * Generate ViewObject metafile
 *
 * @package   cubi.bin.tools
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2010, Rocky Swen
 * @access    public
 */
class ViewGenerator
{
    const VIEW_TEMPLATE = "/v_TemplateEasy.xml";
    const VIEW_TPL = "/v_TemplateView.tpl";
    /**
     * module name
     * @var string
     */
    public $module;
    /**
     * View name
     * @var string
     */
    public $view_name;
    /**
     * More option parameter
     * @var string
     */
    public $opts;
    /**
     * FormGenerator object
     * @var FormGenerator
     */
    public $formGenerator;

    /**
     * Initialize
     *
     * @param string $module module name
     * @param FormGenerator $formGenerator
     * @param array $opts more optional parameter
     */
    function __construct($module, $formGenerator, $opts)
    {
        $this->module = $module;
        $this->formGenerator = $formGenerator;
        $table_name = $formGenerator->doGenerator->table_name;
        $this->opts = $opts;
        $this->view_name = 'view.' . $opts[1] . 'ListView';
    }

    /**
     * Generate View
     *
     * @return string
     */
    function generateView()
    {
        $tplDir = dirname(__FILE__) . '/';
        // copy templates file view.tpl
        copyTemplateFile("view.tpl", $tplDir . META_TPL. self::VIEW_TPL, $this->module);

        $view_short_name = str_replace("view.", "", $this->view_name);
        if(CLI){echo "Start generate form object $view_short_name." . PHP_EOL;}
        $targetPath = $moduleDir = MODULE_PATH . "/" . getModuleName($this->module) . "/view";
        if (!file_exists($targetPath))
        {
            if(CLI){echo "Create directory $targetPath" . PHP_EOL;}
            mkdir($targetPath, 0777, true);
        }

        $smarty = BizSystem::getSmartyTemplate();

        $smarty->assign_by_ref("view_name", $this->view_name);
        $smarty->assign_by_ref("view_short_name", $view_short_name);
        $smarty->assign_by_ref("comp", $this->module);
        $smarty->assign_by_ref("list_form", $this->formGenerator->list_form);
        $smarty->assign_by_ref("new_form", $this->formGenerator->new_form);
        $smarty->assign_by_ref("copy_form", $this->formGenerator->copy_form);
        $smarty->assign_by_ref("edit_form", $this->formGenerator->edit_form);
        $smarty->assign_by_ref("detail_form", $this->formGenerator->detail_form);
        $smarty->assign_by_ref("default_form", $this->formGenerator->list_form);
        $smarty->assign_by_ref("acl", $this->opts['acl']);

        $tpl_file = dirname(__FILE__) . '/' . META_TPL. self::VIEW_TEMPLATE;
        $content = $smarty->fetch($tpl_file);

        // target file
        $targetFile = $targetPath . "/" . $view_short_name . ".xml";
        file_put_contents($targetFile, $content);
        if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." is generated." . PHP_EOL;}
        return $targetFile;
    }

}

/**
 * ModGenerator class
 *
 * Generate module information metafile
 *
 * @package   cubi.bin.tools
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2010, Rocky Swen
 * @access    public
 */
class ModGenerator
{
    const MOD_TEMPLATE = "/mod_Template.xml";
    const MOD_RESOURCE = "/mod_Resource.xml";
    const MOD_ITEMMENU = "/mod_ItemMenu.xml";
    /**
     * Module name
     * @var string
     */
    public $module;
    
    public $opts;

    public $dashboard_enable = 0;
    /**
     * Initialize
     *
     * @param string $module module name
     * @return void
     */
    function __construct($module, $opts, $dashboard_enable=0)
    {
        $this->module = $module;
        $this->opts = $opts;
        $this->dashboard_enable = $dashboard_enable;
    }

    // TODO: modify current mod.xml, acl and menu
    /**
     * Generate module information (mod.xml)
     *
     * @param string $table_name table name
     * @return string
     */
    function generateMod($table_name)
    {
        if(CLI){echo "Start generate mod.xml." . PHP_EOL;}
        $module_name = getModuleName($this->module);
        $targetPath = $moduleDir = MODULE_PATH . "/" . $module_name;
        if (!file_exists($targetPath))
        {
            if(CLI){echo "Create directory $targetPath" . PHP_EOL;}
            mkdir($targetPath, 0777, true);
        }
        
        $listview_uri = strtolower(str_replace(" ","_",$this->opts[2])) . "_list";
        //$listview_uri = strtolower($this->opts[1]) . "_list";
		$module = $this->module;    //.".".$this->opts[1];
        $comp = $this->opts[2];
		
        $smarty = BizSystem::getSmartyTemplate();

        $smarty->assign_by_ref("module_name", $module_name);
        $smarty->assign_by_ref("module", $module );
        $smarty->assign_by_ref("comp", $comp );
        $smarty->assign_by_ref("listview_uri", $listview_uri);
        $smarty->assign_by_ref("acl", $this->opts['acl']);
        $smarty->assign_by_ref("dashboard_enable", $this->dashboard_enable);
        

        $tpl_file = dirname(__FILE__) . '/' . META_TPL. self::MOD_TEMPLATE;
        $content = $smarty->fetch($tpl_file);

        // target file
        $targetFile = $targetPath . "/mod.xml";
        file_put_contents($targetFile, $content);
        if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." is generated." . PHP_EOL;}
        return $targetFile;
    }

	function modifyMod($table_name)
    {
        if(CLI){echo "Start modify mod.xml." . PHP_EOL;}
        $module_name = getModuleName($this->module);
        $targetFile = $moduleDir = MODULE_PATH . "/" . getModuleName($this->module)."/mod.xml";
       
        $content = file_get_contents($targetFile);
        
        $smarty = BizSystem::getSmartyTemplate();

        $listview_uri = strtolower(str_replace(" ","_",$this->opts[2])) . "_list";
        //$listview_uri = strtolower(getSubModuleName($this->module)) . "_list";
        //$listview_uri = strtolower($this->opts[1]) . "_list";
        
		$module = $this->module;    //.".".$this->opts[1];
        $comp = $this->opts[2];
        
        $smarty->assign_by_ref("module_name", $module_name);
        $smarty->assign_by_ref("module", $module );
        $smarty->assign_by_ref("comp", $comp );
        $smarty->assign_by_ref("listview_uri", $listview_uri);
        $smarty->assign_by_ref("acl", $this->opts['acl']);
        $smarty->assign_by_ref("dashboard_enable", $this->dashboard_enable);
                
        //generate ACL sections
        $tpl_file = dirname(__FILE__) . '/' . META_TPL. self::MOD_RESOURCE;
        $str = $smarty->fetch($tpl_file);
        //test if New ACL sections is exists
    	$pattern = "/\<Resource Name=\"".$this->opts['acl'][resource]."\"\>/si";
        
        if(!preg_match($pattern,$content)){
			//do append new sections
        	$content = preg_replace("/(<\/ACL\>)/si",$str."\n</ACL>",$content);
        }
        
        //generate MenuItems sections
		$tpl_file = dirname(__FILE__) . '/' . META_TPL. self::MOD_ITEMMENU;
        $str = $smarty->fetch($tpl_file);
		//test if New MenuItems is exists
        $pattern = "/\<MenuItem Name=\"".ucwords($module)."\"\>/si";
        
        if(!preg_match($pattern,$content)){
			//do append new sections
        	$content = preg_replace("/\<\/MenuItem\>\s*?\<\/Menu\>/si",$str."\n</MenuItem>\n\t</Menu>",$content);
        }    
        
        //save files
        file_put_contents($targetFile, $content);
        if(CLI){echo "\t".str_replace(MODULE_PATH,"",$targetFile)." is modified." . PHP_EOL;}
        return $targetFile;
    }    
    
}

// ---- helper functions ----
/**
 * Trim empty line of text content
 *
 * @param string $content
 * @return string
 */
function trimEmptyLines($content)
{
    $lines = explode("\n", $content);
    $ret = "";
    foreach ($lines as $line)
    {
        if (trim($line) == "")
            continue;
        $ret .= $line . nl;
    }
    return $ret;
}

/**
 * Copy template file to target
 *
 * @param string $tpl_name template name for target
 * @param string $tpl_file template file name
 * @param string $module module name
 * @return bool Returns true on success or false on failure.
 */
function copyTemplateFile($tpl_name, $tpl_file, $module)
{
    $targetPath = $moduleDir = MODULE_PATH . "/" . getModuleName($module) . "/template";
    if (!file_exists($targetPath))
        mkdir($targetPath, 0777, true);
    $targetFile = $targetPath . "/" . $tpl_name;
    if (file_exists($targetFile))
        return;
    return copy($tpl_file, $targetFile);
}

/**
 * Get data option
 *
 * @param string $datatype
 * @param string $db_driver
 * @return string
 */
function getDataOptions($datatype, $db_driver)
{
    switch (strtoupper($db_driver))
    {
        case 'PDO_MYSQL':

            switch ($datatype)
            {
                /*
                 * case "date":
                  $options= "Date";
                  break;
                 */

                default:
                    if (preg_match("/enum\((.*?)\)/si", $datatype, $match))
                    {
                        preg_match_all("/'(.*?)'[,]?/", $match[1], $options);
                        $options = $options[1];
                    }
                    break;
            }

            break;
    }
    if (is_array($options))
    {
        $options = implode("|", $options);
    }
    return $options;
}

/**
 * Get data element from database data type
 * 
 * @param string $datatype data type
 * @param string $db_driver database driver
 * @return string
 */
function getDataElement($datatype, $db_driver)
{
    switch (strtoupper($db_driver))
    {
        case 'PDO_MYSQL':

            switch (strtolower($datatype))
            {
                case "date":
                    $element = "InputDate";
                    break;

                case "datetime":
                    $element = "InputDatetime";
                    break;

                case "int":
                case "float":
                case "bigint":
                    $element = "InputText";
                    break;

                case "tinyint":
                    $element = "Checkbox";
                    break;

                case "text":
                case "shorttext":
                case "longtext":
                    $element = "RichText";
                    break;

                default:
                    if (preg_match("/enum\(.*/si", $datatype, $match))
                    {
                        $element = "Radio";
                    } else
                    {
                        $element = "InputText";
                    }
                    break;
            }

            break;
    }
    return $element;
}

/**
 * Convert data type from database table to OpenBiz
 *
 * @param string $datatype
 * @param string $db_driver database driver
 * @return string Return OpenBiz data type
 */
function convertDataType($datatype, $db_driver)
{
    switch (strtoupper($db_driver))
    {
        case 'PDO_MYSQL':

            switch ($datatype)
            {
                case "date":
                    $type = "Date";
                    break;

                case "timestamp":
                case "datetime":
                    $type = "Datetime";
                    break;

                case "int":
                case "float":
                case "bigint":
                case "tinyint":
                    $type = "Number";
                    break;

                case "text":
                case "shorttext":
                case "longtext":
                default:
                    $type = "Text";
                    break;
            }

            break;
    }
    return $type;
}

/**
 * Get component name from table name
 *  if table name = table_name
 *  component name = TableNama
 *
 * @param string $table_name table name
 * @param int $prefix
 * @return string Return component name
 */
function getCompName($table_name, $prefix=0)
{
    $names = explode("_", $table_name);
    $compName = "";
    for ($i = $prefix; $i < count($names); $i++)
    {
        $compName .= ucwords(strtolower($names[$i]));
    }
    return $compName;
}


function getCompDisplayName($table_name, $prefix=0)
{
    $names = explode("_", $table_name);
    $compName = "";
    for ($i = $prefix; $i < count($names); $i++)
    {
        $compName .= ucwords(strtolower($names[$i]))." ";
    }
    $compName=substr($compName,0,strlen($compName)-1);
    return $compName;
}


function getCompModuleName($table_name, $prefix=0)
{
    $names = explode("_", $table_name);
    $compName = strtolower($names[0]);  
    return $compName;
}

/**
 * Get event name from tabel name
 * event-name = TABLE-NAME
 *
 * @param string $table_name
 * @return string
 */
function getEventName($table_name)
{
    $table_name = strtoupper($table_name);
    return $table_name;
}

/**
 * Get module name from full meta component/object name
 *
 * @param string $comp full component/object name like modname.xxx.yyy.ObjectName
 * @return string Return module name
 */
function getModuleName($comp)
{
    $names = explode(".", $comp);
    return $names[0];
}

function getSubModuleName($comp)
{
    $names = explode(".", $comp);
    return $names[count($names)-1];
}

?>