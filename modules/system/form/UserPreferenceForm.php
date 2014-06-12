<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: UserPreferenceForm.php 5507 2014-06-01 00:11:37Z rockyswen@gmail.com $
 */

/**
 * UserPreferenceForm class - implement the logic of setting user preferences
 *
 * @access public
 */
class UserPreferenceForm extends EasyForm
{
    protected $_userId = null;
    
    function __construct(&$xmlArr)
    {
        parent::__construct($xmlArr);        
        $this->_userId = 0;
    }
    
    public function allowAccess($access=null) {
    	return parent::allowAccess();
    }    
    
    public function fetchData(){
        if ($this->m_ActiveRecord != null)
            return $this->m_ActiveRecord;
        
        $dataObj = $this->getDataObj();
        if ($dataObj == null) return;

		
        if (!$this->m_FixSearchRule && !$this->m_SearchRule)
        	return array();
        
    	QueryStringParam::setBindValues($this->m_SearchRuleBindValues);
        
        	
        if ($this->m_RefreshData)   $dataObj->resetRules();
        else $dataObj->clearSearchRule();

        if ($this->m_FixSearchRule)
        {
            if ($this->m_SearchRule)
                $searchRule = $this->m_SearchRule . " AND " . $this->m_FixSearchRule;
            else
                $searchRule = $this->m_FixSearchRule;
        }

        $dataObj->setSearchRule($searchRule);
        QueryStringParam::setBindValues($this->m_SearchRuleBindValues);        

        $resultRecords = $dataObj->fetch();
        foreach($resultRecords as $record){
        	$prefRecord["_".$record['name']] = $record["value"];
        }
        if($prefRecord["_siteurl"]=="")
        {
        	if($_SERVER["HTTPS"])
        	{
        		$prefRecord["_siteurl"]="https://".$_SERVER["SERVER_NAME"].APP_URL;
        	}
        	else
        	{
        		$prefRecord["_siteurl"]="http://".$_SERVER["SERVER_NAME"].APP_URL;	
        	}        	
        }
        
        $this->m_RecordId = $resultRecords[0]['Id'];
        $this->setActiveRecord($prefRecord);

        QueryStringParam::ReSet();

        return $prefRecord;    
    }
    
    public function updateRecord()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();

        if (count($recArr) == 0)
            return;

        try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }
		
        // new save logic
        $user_id = 0;
        $prefDo = $this->getDataObj();
        
        foreach ($this->m_DataPanel as $element)
        {
            $value = $recArr[$element->m_FieldName];
            if ($value === null){ 
            	continue;
            } 
            if(substr($element->m_FieldName,0,1)=='_'){
	            $name = substr($element->m_FieldName,1);
            	$recArrParam = array(
            		"user_id" => $user_id,
            		"name"	  => $name,
            		"value"   => $value,
	            	"section" => $element->m_ElementSetCode,
	            	"type" 	  => $element->m_Class,	            
	            );
	            //check if its exsit
	            $record = $prefDo->fetchOne("[user_id]='$user_id' and [name]='$name'");
	            if($record){
	            	//update it
	            	$recArrParam["Id"] = $record->Id;
	            	$prefDo->updateRecord($recArrParam,$record->toArray());
	            }else{
	            	//insert it	            	
	            	$prefDo->insertRecord($recArrParam);
	            }
	            
	            //update default app_init setting
	            $config_file = APP_HOME.'/bin/app_init.php';
	            switch($name){
	            	case "theme":
	            		if($value!=DEFAULT_THEME_NAME){
	            			//update default theme DEFAULT_THEME_NAME
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_THEME_NAME[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_THEME_NAME','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;
	            	case "system_name":
	            		if($value!=DEFAULT_SYSTEM_NAME){
	            			//update default theme DEFAULT_THEME_NAME
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_SYSTEM_NAME[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_SYSTEM_NAME','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;
					case "siteurl":
						//update default theme SITE_URL
            			$data = file_get_contents($config_file);	            			
            			$data = preg_replace("/define\([\'\\\"]{1}SITE_URL[\'\\\"]{1}.*?\)\;/i","define('SITE_URL','$value');",$data);	            			
            			@file_put_contents($config_file,$data);
	            		break;	   	            		    
	            	case "sessionstrict":
						//update default theme SESSION_STRICT
	            		if($value!=SESSION_STRICT){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}SESSION_STRICT[\'\\\"]{1}.*?\)\;/i","define('SESSION_STRICT','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;	
	            	case "sessiontimeout":
						//update default theme TIMEOUT
	            		if($value!=TIMEOUT){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}TIMEOUT[\'\\\"]{1}.*?\)\;/i","define('TIMEOUT','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;	
	            	case "data_acl":
						//update default theme DATA_ACL
	            		if($value!=DATA_ACL){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DATA_ACL[\'\\\"]{1}.*?\)\;/i","define('DATA_ACL','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;	     		
	            	case "language":
	            	    if($value!=DEFAULT_LANGUAGE){
	            			//update default theme DEFAULT_LANGUAGE
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_LANGUAGE[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_LANGUAGE','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	   

	            			//make changes now
	            			BizSystem::sessionContext()->setVar("LANG",$value );
	            		}
	            		break;
	            	case "currency":
	            	    if($value!=DEFAULT_CURRENCY){
	            			//update default theme DEFAULT_DEFAULT_CURRENCY
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_CURRENCY[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_CURRENCY','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;	
	            	case "appbuilder":
	            	    if($value!=APPBUILDER){	            			
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}APPBUILDER[\'\\\"]{1}.*?\)\;/i","define('APPBUILDER','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;
	            	case "debug":
	            	    if($value!=DEBUG){	            			
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEBUG[\'\\\"]{1}.*?\)\;/i","define('DEBUG','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;
	            	case "timezone":
	            	    if($value!=DEFAULT_TIMEZONE){
	            			//update default theme DEFAULT_THEME_NAME
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_TIMEZONE[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_TIMEZONE','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;
	            	case "group_data_share":
	            	    if($value!=GROUP_DATA_SHARE){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}GROUP_DATA_SHARE[\'\\\"]{1}.*?\)\;/i","define('GROUP_DATA_SHARE','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;	    
	            	case "owner_perm":
	            	    if($value!=DEFAULT_OWNER_PERM){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_OWNER_PERM[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_OWNER_PERM','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;	
	            	case "group_perm":
	            	    if($value!=DEFAULT_GROUP_PERM){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_GROUP_PERM[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_GROUP_PERM','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;	
	            	case "other_perm":
	            	    if($value!=DEFAULT_OTHER_PERM){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_OTHER_PERM[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_OTHER_PERM','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;		            			            			            		        		
	            }
            }
        }

        // in case of popup form, close it, then rerender the parent form
        if ($this->m_ParentFormName)
        {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();

    }
}  
?>