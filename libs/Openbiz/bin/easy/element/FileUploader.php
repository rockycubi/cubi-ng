<?PHP
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
 * @version   $Id: FileUploader.php 3086 2011-01-22 20:06:43Z jixian2003 $
 */

//include_once("FileInput.php");

/**
 * File class is the element for Upload File
 *
 * @package openbiz.bin.easy.element
 * @author jixian2003
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class FileUploader extends FileInput
{
    public $m_UploadRoot ;
    public $m_UploadRootURL ;
    public $m_UploadFolder ;
    public $m_UploadFileType ;
    public $m_Uploaded =false;   	
    public $m_Deleteable;
    public $m_UseRawName=false;        

    /**
     * Initialize Element with xml array
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr, $formObj)
    {
        parent::__construct($xmlArr, $formObj);
        $this->readMetaData($xmlArr);
        if(defined("PUBLIC_UPLOAD_PATH")){
        	$this->m_UploadRoot= constant("PUBLIC_UPLOAD_PATH");
        }else{
        	$this->m_UploadRoot= APP_HOME."/files/upload";
        }
        if(defined("PUBLIC_UPLOAD_URL")){
        	$this->m_UploadRootURL = str_replace(APP_URL,"",constant("PUBLIC_UPLOAD_URL"));
        }else{
        	$this->m_UploadRootURL = "/files/upload";
        }
    }

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_UploadFolder = isset($xmlArr["ATTRIBUTES"]["UPLOADFOLDER"]) ? $xmlArr["ATTRIBUTES"]["UPLOADFOLDER"] : null;
        $this->m_UploadFileType = isset($xmlArr["ATTRIBUTES"]["FILETYPE"]) ? $xmlArr["ATTRIBUTES"]["FILETYPE"] : null;
        $this->m_Deleteable = isset($xmlArr["ATTRIBUTES"]["DELETEABLE"]) ? $xmlArr["ATTRIBUTES"]["DELETEABLE"] : "N";
        $this->m_UseRawName = isset($xmlArr["ATTRIBUTES"]["USERAWNAME"]) ? $xmlArr["ATTRIBUTES"]["USERAWNAME"] : false;
    }

    /**
     * Set value of element
     *
     * @param mixed $value
     * @return string
     */
    function setValue($value)
    {
        if($this->m_Deleteable=='N')
    	{

    	}
    	else
    	{
    		$delete_user_opt=BizSystem::clientProxy()->getFormInputs($this->m_Name."_DELETE");
    		if($delete_user_opt)
    		{
    			$this->m_Value="";
    			return;
    		}
    		else
    		{
    			if(count($_FILES)>0){
    				
    			}else{
    				$this->m_Value = $value;
    			}   
    		} 
    	}
    	if(count($_FILES)>0)
		{
			if(!$this->m_Uploaded && $_FILES[$this->m_Name]["size"] > 0)
			{
				$file = $_FILES[$this->m_Name];

				if(!is_dir($this->m_UploadRoot.$this->m_UploadFolder))
				{
					mkdir($this->m_UploadRoot.$this->m_UploadFolder ,0777,true);
				}
				if($this->m_UseRawName){
					$uploadFile = $this->m_UploadFolder."/".$file['name'];
				}else{
					$uploadFile = $this->m_UploadFolder."/".date("YmdHis")."-".md5($file['name']);
				}
				if($this->m_UploadFileType){
					$pattern = "/".$this->m_UploadFileType."$/si";
					if(!preg_match($pattern,$file['name'])){
						return;
					}	                		                	
				}
				if(move_uploaded_file($file['tmp_name'], $this->m_UploadRoot.$uploadFile))
				{
					$this->m_Value = $this->m_UploadRootURL.$uploadFile;
					$this->m_Uploaded=true;
				}	                	                
				return $uploadFile;		
			}
		}    	
    }

    public function render()
    {
    	if($this->m_Deleteable=="Y"){
        	$delete_opt="<input type=\"checkbox\" name=\"" . $this->m_Name . "_DELETE\" id=\"" . $this->m_Name ."_DELETE\" >Delete";
        } else{
        	$delete_opt="";
        }
        $disabledStr = ($this->getEnabled() == "N") ? "disabled=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
        $sHTML .= "<input type=\"file\" name='$this->m_Name' id=\"" . $this->m_Name ."\" value='$this->m_Value' $disabledStr $this->m_HTMLAttr $style $func />        
        			$delete_opt";
        return $sHTML;
    }    

}
?>