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
 * @copyright Copyright &copy; 2005-2010, Jixian
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: LabelBool.php 3640 2011-04-11 03:18:17Z jixian2003 $
 */

//include_once("LabelText.php");

class LabelBool extends LabelText{
    public $m_TrueImg;
    public $m_FlaseImg;
    public $m_TrueValue;
    public $m_FlaseValue;

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData($xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_TrueImg=isset($xmlArr["ATTRIBUTES"]["TRUEIMG"])?$xmlArr["ATTRIBUTES"]["TRUEIMG"]:"flag_y.gif";
        $this->m_FalseImg=isset($xmlArr["ATTRIBUTES"]["FALSEIMG"])?$xmlArr["ATTRIBUTES"]["FALSEIMG"]:"flag_n.gif";
        $this->m_TrueValue=isset($xmlArr["ATTRIBUTES"]["TRUEVALUE"])?$xmlArr["ATTRIBUTES"]["TRUEVALUE"]:true;
        $this->m_FalseValue=isset($xmlArr["ATTRIBUTES"]["FLASEVALUE"])?$xmlArr["ATTRIBUTES"]["FLASEVALUE"]:false;
    }
    /**
     * Render element, according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
    	// create ng-src="$image_path/{{dataobj.fieldname==1 && $this->m_TrueImg | $this->m_FalseImg}}"
		$imagePath = Resource::getImageUrl();
		$imgsrcText = "ng-src=\"$imagePath/{{dataobj.".$this->m_FieldName."==1 && '$this->m_TrueImg' || '$this->m_FalseImg'}}\"";
		//$imgsrcText = "ng-src='".$imagePath."/$this->m_TrueImg'";
		
		$id = $this->m_Name;
		$sHTML = "<img id=\"$id\" $imgsrcText/>";
		return $sHTML;
    }    	
}

?>