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
 * @version   $Id: ImageUploader.php 2825 2010-12-08 19:22:02Z jixian2003 $
 */

//include_once("FileUploader.php");

/**
 * File class is the element for Upload Image
 *
 * @package openbiz.bin.easy.element
 * @author jixian2003
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class ImageUploader extends FileUploader
{
    public $m_PicWidth ;
    public $m_PicHeight ;
    public $m_ThumbWidth ;
    public $m_ThumbHeight ;
    public $m_ThumbFolder ;
    public $m_Preview ;

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
        $this->translate();
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
        $this->m_PicWidth 	= isset($xmlArr["ATTRIBUTES"]["PICWIDTH"]) ? $xmlArr["ATTRIBUTES"]["PICWIDTH"] : null;
        $this->m_PicHeight 	= isset($xmlArr["ATTRIBUTES"]["PICHEIGHT"]) ? $xmlArr["ATTRIBUTES"]["PICHEIGHT"] : null;
        $this->m_PicQuality 	= isset($xmlArr["ATTRIBUTES"]["PICQUALITY"]) ? $xmlArr["ATTRIBUTES"]["PICQUALITY"] : 80;
        $this->m_ThumbWidth 	= isset($xmlArr["ATTRIBUTES"]["THUMBWIDTH"]) ? $xmlArr["ATTRIBUTES"]["THUMBWIDTH"] : null;
        $this->m_ThumbHeight 	= isset($xmlArr["ATTRIBUTES"]["THUMBHEIGHT"]) ? $xmlArr["ATTRIBUTES"]["THUMBHEIGHT"] : null;
        $this->m_ThumbQuality	= isset($xmlArr["ATTRIBUTES"]["THUMBQUALITY"]) ? $xmlArr["ATTRIBUTES"]["THUMBQUALITY"] : 50;
        $this->m_ThumbFolder 	= isset($xmlArr["ATTRIBUTES"]["THUMBFOLDER"]) ? $xmlArr["ATTRIBUTES"]["THUMBFOLDER"] : null;
        $this->m_Preview 	= isset($xmlArr["ATTRIBUTES"]["PREVIEW"]) ? $xmlArr["ATTRIBUTES"]["PREVIEW"] : false;
    }

    /**
     * Set value of element
     *
     * @param mixed $value
     * @return mixed
     */
    function setValue($value)
    {
    	if($this->m_Deleteable=='N' )
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
				$picFileName = parent::setValue($value);
				if((int)$this->m_PicWidth>0 || (int)$this->m_PicHeight>0)
				{
					//resize picture size
					$fileName = $this->m_UploadRoot.$picFileName;
					$width = $this->m_PicWidth;
					$height = $this->m_PicHeight;
					$quality = $this->m_PicQuality;

					$this->resizeImage($fileName, $fileName, $width, $height, $quality);
				}
				if(
				((int)$this->m_ThumbWidth>0 || (int)$this->m_ThumbHeight>0) &&
						$this->m_ThumbFolder!=""
				)
				{
					//generate thumbs picture
					if(!is_dir($this->m_UploadRoot.$this->m_ThumbFolder))
					{
						mkdir($this->m_UploadRoot.$this->m_ThumbFolder ,0777,true);
					}
					$file = $_FILES[$this->m_Name];
					$thumbPath = $this->m_ThumbFolder."/thumbs-".date("YmdHis")."-".urlencode($file['name']);
					$thumbFileName = $this->m_UploadRoot.$thumbPath;
					$width = $this->m_ThumbWidth;
					$height = $this->m_ThumbHeight;
					$quality = $this->m_ThumbQuality;

					$this->resizeImage($fileName, $thumbFileName, $width, $height, $quality);

					$result=array('picture'=>$this->m_UploadRootURL.$picFileName,'thumbpic'=>$this->m_UploadRootURL.$thumbPath);	                    
					$this->m_Value=serialize($result);
				}
			}
		}
		else
		{
			$this->m_Value = $value;        	
		}    	 
    }

    /**
     * Resize the image     *
     *
     * @param string $sourceFileName
     * @param string $destFileName
     * @param number $width
     * @param number $height
     * @param int    $quality <p>
     * quality is optional, and ranges from 0 (worst
     * quality, smaller file) to 100 (best quality, biggest file). The
     * default is the default IJG quality value (about 75).
     * </p>
     * @return boolean true is success
     */
    protected function resizeImage($sourceFileName, $destFileName, $width, $height, $quality)
    {
		if(!function_exists("imagejpeg"))
		{
			return ;
		}
        if($width == 0)
        {
            $width = $height;
        }

        if($height == 0)
        {
            $height = $width;
        }

        list($origWidth, $origHeight) = getimagesize($sourceFileName);

        $origRatio = $origWidth / $origHeight;

        if ( ($width / $height) > $origRatio)
        {
            $width = $height * $origRatio;
        }
        else
        {
            $height = $width / $origRatio;
        }

        $image_p = imagecreatetruecolor($width, $height);
        try{
        	$image = @imagecreatefromjpeg($sourceFileName);
        }catch(Exception $e){}
        try{
	        if(!$image){
	        	$image = @imagecreatefrompng($sourceFileName);
	        }
        }catch(Exception $e){}
        try{
	        if(!$image){
	        	$image = @imagecreatefromgif($sourceFileName);
	        }
     	}catch(Exception $e){}
    	try{
	        if(!$image){
	        	$image = @imagecreatefromwbmp($sourceFileName);
	        }
     	}catch(Exception $e){}
    	
    	try{
	        if(!$image){
	        	$image = @imagecreatefromxbm($sourceFileName);
	        }
     	}catch(Exception $e){}
     	
    
    	try{
	        if(!$image){
	        	$image = @imagecreatefromxpm($sourceFileName);
	        }
     	}catch(Exception $e){}
     	
     	if(!$image){
	    	return ;
	    }
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

        return imagejpeg($image_p, $destFileName, $quality);
    }
    
    public function render()
    {
        $disabledStr = ($this->getEnabled() == "N") ? "disabled=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
        $value = $this->getValue();
        if($this->m_Preview){
	        if($value){
	        	$preview = "<img id=\"" . $this->m_Name ."_preview\" src=\"$value\" class=\"image_preview\" />";
	        }
        }
        if($this->m_Deleteable=="Y"){
        	$delete_opt="<input type=\"checkbox\" name=\"" . $this->m_Name . "_DELETE\" id=\"" . $this->m_Name ."_DELETE\" >Delete";
        } else{
        	$delete_opt="";
        }
        $sHTML .= "
        $preview
        <input type=\"file\" onchange=\"Openbiz.ImageUploader.updatePreview('" . $this->m_Name ."')\" name=\"$this->m_Name\" id=\"" . $this->m_Name ."\" value=\"$this->m_Value\" $disabledStr $this->m_HTMLAttr $style $func>
        $delete_opt
        ";
        return $sHTML;
    }    
}
?>