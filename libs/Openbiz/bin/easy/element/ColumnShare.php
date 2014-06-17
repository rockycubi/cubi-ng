<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy.element
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: ColumnBool.php 3687 2011-04-12 19:58:36Z jixian2003 $
 */

//include_once("ColumnText.php");

/**
 * ColumnBool class is element for ColumnBool
 * show boolean on data list (table)
 *
 * @package openbiz.bin.easy.element
 * @author wangdong1984 
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class ColumnShare extends ColumnText
{
    public $m_MyPrivateImg		= null;
    public $m_MySharedImg		= null;
    public $m_MyAssignedImg		= null;
    public $m_MyDistributedImg	= null;    
    public $m_GroupSharedImg	= null;
    public $m_OtherSharedImg	= null;
    public $m_DefaultImg		= null;
    
    public $m_RecordOwnerId		= null;
    public $m_RecordGroupId		= null;
    public $m_RecordGroupPerm	= null;
    public $m_RecordOtherPerm	= null;
    public $m_RecordCreatorId 	= null;
    
    protected $m_RecordOwnerId_AutoLoad		= false;
    protected $m_RecordGroupId_AutoLoad		= false;
    protected $m_RecordGroupPerm_AutoLoad	= false;
    protected $m_RecordOtherPerm_AutoLoad	= false;
    
    public $m_hasOwnerField = false;
    
    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_MyPrivateImg = isset($xmlArr["ATTRIBUTES"]["MYPRIVATEIMG"])	? $xmlArr["ATTRIBUTES"]["MYPRIVATEIMG"]	: "{RESOURCE_URL}/common/images/icon_data_private.gif";
        $this->m_MySharedImg  = isset($xmlArr["ATTRIBUTES"]["MYSHAREDIMG"])		? $xmlArr["ATTRIBUTES"]["MYSHAREDIMG"]	: "{RESOURCE_URL}/common/images/icon_data_shared.gif";
        $this->m_MyAssignedImg  = isset($xmlArr["ATTRIBUTES"]["MYASSIGNEDIMG"])		? $xmlArr["ATTRIBUTES"]["MYASSIGNEDIMG"]	: "{RESOURCE_URL}/common/images/icon_data_assigned.gif";
        $this->m_MyDistributedImg  = isset($xmlArr["ATTRIBUTES"]["MYDISTRIBUTEDIMG"])		? $xmlArr["ATTRIBUTES"]["MYDISTRIBUTEDIMG"]	: "{RESOURCE_URL}/common/images/icon_data_distributed.gif";
        $this->m_GroupSharedImg = isset($xmlArr["ATTRIBUTES"]["GROUPSHAREDIMG"])? $xmlArr["ATTRIBUTES"]["GROUPSHAREDIMG"]: "{RESOURCE_URL}/common/images/icon_data_shared_group.gif";
        $this->m_OtherSharedImg = isset($xmlArr["ATTRIBUTES"]["OTHERSHAREDIMG"])? $xmlArr["ATTRIBUTES"]["OTHERSHAREDIMG"]: "{RESOURCE_URL}/common/images/icon_data_shared_other.gif";
        $this->m_DefaultImg = isset($xmlArr["ATTRIBUTES"]["DEFAULTIMG"])? $xmlArr["ATTRIBUTES"]["DEFAULTIMG"]: "{RESOURCE_URL}/common/images/icon_data_shared_other.gif";
        
        $this->m_RecordCreatorId	=	isset($xmlArr["ATTRIBUTES"]["CREATORID"])		? $xmlArr["ATTRIBUTES"]["CREATORID"]	: null;
        $this->m_RecordOwnerId	=	isset($xmlArr["ATTRIBUTES"]["OWNERID"])		? $xmlArr["ATTRIBUTES"]["OWNERID"]	: null;
        $this->m_RecordGroupId	=	isset($xmlArr["ATTRIBUTES"]["GROUPID"])		? $xmlArr["ATTRIBUTES"]["GROUPID"]	: null;
        $this->m_RecordGroupPerm=	isset($xmlArr["ATTRIBUTES"]["GROUPPERM"])	? $xmlArr["ATTRIBUTES"]["GROUPPERM"]: null;
        $this->m_RecordOtherPerm=	isset($xmlArr["ATTRIBUTES"]["OTHERPERM"])	? $xmlArr["ATTRIBUTES"]["OTHERPERM"]: null;
        
        $this->m_RecordOwnerId_AutoLoad		=	isset($xmlArr["ATTRIBUTES"]["OWNERID"])?false:true;	
        $this->m_RecordGroupId_AutoLoad		=	isset($xmlArr["ATTRIBUTES"]["GROUPID"])?false:true;	
        $this->m_RecordGroupPerm_AutoLoad	=	isset($xmlArr["ATTRIBUTES"]["GROUPPERM"])?false:true;	
        $this->m_RecordOtherPerm_AutoLoad	=	isset($xmlArr["ATTRIBUTES"]["OTHERPERM"])?false:true;	
    }
    
    public function setValue($value){
		$formObj = $this->getFormObj();
        $rec = $formObj->getActiveRecord();                
		
        if($this->m_RecordOwnerId_AutoLoad)
        {
        	$this->m_hasOwnerField = $this->hasOwnerField();
        	if($this->m_hasOwnerField){
        		$this->m_RecordOwnerId = $rec['owner_id'];
        		$this->m_RecordCreatorId = $rec['create_by'];
        	}else{
        		$this->m_RecordOwnerId = $rec['create_by'];
        	}        	
        }
        
    	if($this->m_RecordGroupId_AutoLoad)
        {
        	$this->m_RecordGroupId = $rec['group_id'];        	
        }
        
        if($this->m_RecordGroupPerm_AutoLoad)
        {
        	$this->m_RecordGroupPerm = $rec['group_perm'];
        }

        if($this->m_RecordOtherPerm_AutoLoad)
        {
        	$this->m_RecordOtherPerm = $rec['other_perm'];
        }       	     
    }

    public function getValue(){
    	$user_id 	= BizSystem::GetUserProfile("Id");
		$groups 	= BizSystem::GetUserProfile("groups");
        if (!$groups) $groups = array();
    	
		$this->m_hasOwnerField = $this->hasOwnerField();
       if($this->m_hasOwnerField){
       		
			if($this->m_RecordOwnerId != $this->m_RecordCreatorId)
			{
				if($this->m_RecordOwnerId == $user_id)
				{
					$this->m_Value = 4;
					return $this->m_Value ;
				}
				elseif($this->m_RecordCreatorId == $user_id)
				{
					$this->m_Value = 5;
					return $this->m_Value ;
				}
			}
       }
		
	    if($user_id == $this->m_RecordOwnerId)
		{
			if((int)$this->m_RecordGroupPerm>0 || (int)$this->m_RecordOtherPerm>0 )
			{
				
				$this->m_Value = 1;
			}
			else 
			{
				$this->m_Value = 0;
			}
		}
		elseif($this->m_RecordOtherPerm>0)
		{
			$this->m_Value = 3;
			
		}
		else
		{
            foreach($groups as $group_id)
			{
				if($group_id == $this->m_RecordGroupId)
				{
					$this->m_Value = 2;
					break;
				}
			}
			
		} 
		
    
               	
		return $this->m_Value;  		
    }
    
    /**
     * Render element, according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
		$style = $this->getStyle();
        $text = $this->getText();
        $id = $this->m_Name;
        $func = $this->getFunction();        
        
       switch($this->getValue()){
	       	case "0":
	       		$image_url = $this->m_MyPrivateImg;
	       		break;
	       	case "1":
	       		$image_url = $this->m_MySharedImg;
	       		break;
	       	case "2":
	       		$image_url = $this->m_GroupSharedImg;
	       		break;
	       	case "3":
	       		$image_url = $this->m_OtherSharedImg;
	       		break;
	       	case "4":
	       		$image_url = $this->m_MyAssignedImg;
	       		break;
	       	case "5":
	       		$image_url = $this->m_MyDistributedImg;
	       		break;	    
	       	default:
	       		if($this->m_DefaultImg=='{RESOURCE_URL}/common/images/icon_data_shared_other.gif'){
	       			$this->m_DefaultImg = $this->m_OtherSharedImg;
	       		}
	       		$image_url = $this->m_DefaultImg;
	       		break;   			       		
       }
       
        if(preg_match("/\{.*\}/si",$image_url))
        {
        	$formobj = $this->getFormObj();
        	$image_url =  Expression::evaluateExpression($image_url, $formobj);
        }else{
        	$image_url = Resource::getImageUrl()."/".$image_url;
        }
        if($this->m_Width)
        {
        	$width = "width=\"$this->m_Width\"";
        }
    	if ($this->m_Link)
        {
            $link = $this->getLink();
            $target = $this->getTarget();
            $sHTML = "<a   id=\"$id\" href=\"$link\" $target $func $style><img $width src='$image_url' /></a>";
        }else{
        	$sHTML = "<img id=\"$id\"  alt=\"".$text."\" title=\"".$text."\" $width src='$image_url' />";
        }
        return  $sHTML;
    }
    
	
	public function hasOwnerField(){		        
		$field = $this->getFormObj()->getDataObj()->getField('owner_id');
		if($field){
			return true;
		}
		else{
			return false;
		}
		
	}    
}