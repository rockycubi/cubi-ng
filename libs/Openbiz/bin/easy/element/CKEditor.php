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
 * @version   $Id: CKEditor.php 3486 2011-03-11 17:30:28Z jixian2003 $
 */

//include_once("InputElement.php");

/**
 * CKEditor class is element for CKEditor
 *
 * @package openbiz.bin.easy.element
 * @author jixian2003, Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class CKEditor extends InputElement
{
	public $m_Config;
	public $m_Mode;
    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_Mode = isset($xmlArr["ATTRIBUTES"]["MODE"]) ? $xmlArr["ATTRIBUTES"]["MODE"] : null;
        $this->m_Config = isset($xmlArr["ATTRIBUTES"]["CONFIG"]) ? $xmlArr["ATTRIBUTES"]["CONFIG"] : null;
    }

    /**
     * Render element, according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        BizSystem::clientProxy()->includeCKEditorScripts();

        $elementName = $this->m_Name;

        $value = $this->getValue();
        $value = htmlentities($value, ENT_QUOTES, "UTF-8");
        $style = $this->getStyle();
        $width = $this->m_Width ? $this->m_Width : 600;
        $height = $this->m_Height ? $this->m_Height : 300;
        //$func = "onclick=\"editRichText('$elementName', $width, $height);\"";
        if(!strlen($value)>0) // fix suggested by smarques
            $value="&nbsp;";

        $type = strtolower($this->m_Mode);
        $fileBrowserPage = APP_URL."/bin/filebrowser/browser.html";

        $languageCode = I18n::getCurrentLangCode();
        $languageCode = str_replace("_","-",$languageCode);
        $config = $this->m_Config;        
        $sHTML .= "<textarea id=\"$elementName\" name=\"$elementName\" >$value</textarea>\n";
        $sHTML .= "<script type=\"text/javascript\">\n";
        if($config){
        	//remove the last commas
        	$config=trim($config);
        	if(substr($config,strlen($config)-1,1)==','){
        		$config = substr($config,strlen($config)-1);
        	}
        	$sHTML .= "Openbiz.CKEditor.init('$elementName',{'type':'$type','filebrowserBrowseUrl':'$fileBrowserPage','language':'$languageCode','height':'$height','width':'$width',$config});\n";
        }else{
        	$sHTML .= "Openbiz.CKEditor.init('$elementName',{'type':'$type','filebrowserBrowseUrl':'$fileBrowserPage','language':'$languageCode','height':'$height','width':'$width'});\n";
        }
        $sHTML .= "</script>\n";
        
        return $sHTML;
    }

}

?>
