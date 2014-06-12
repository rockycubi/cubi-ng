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
 * @version   $Id: Button.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

//include_once("InputElement.php");

/**
 * Button class is element for Button
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class Button extends InputElement
{
    /**
     * Image file name
     *
     * @var string
     */
    public $m_Image;
 	public $m_Sortable;
	public $m_Link;
    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_Image = isset($xmlArr["ATTRIBUTES"]["IMAGE"]) ? $xmlArr["ATTRIBUTES"]["IMAGE"] : null;
        $this->m_Sortable = isset($xmlArr["ATTRIBUTES"]["SORTABLE"]) ? $xmlArr["ATTRIBUTES"]["SORTABLE"] : null;        
        $this->m_Link = isset($xmlArr["ATTRIBUTES"]["LINK"]) ? $xmlArr["ATTRIBUTES"]["LINK"] : null;
		
		if ($this->m_Link != null) {
			$this->m_Link = str_replace('{@home:url}',APP_INDEX,$this->m_Link);
			if (strpos($this->m_Link,APP_INDEX) === false) $this->m_Link = APP_INDEX.$this->m_Link;
		}
    }

    /**
     * Render element, according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $style = $this->getStyle();
        $func = $this->getEnabled() == 'N' ? "" : $this->getFunction();
        $id	   = $this->m_Name;
		$link = $this->m_Link;
		
        if ($this->m_Image)
        {
            $imagesPath = Resource::getImageUrl();
            $out = "<img src=\"$imagesPath/" . $this->m_Image . "\" border=0 title=\"" . $this->m_Text . "\" />";
            if ($func != "")
                $out = "<a ng-href=\"$link\" $this->m_HTMLAttr $style $func>".$out."</a>";
        }
        else
        {
            $out = $this->getText();
            //$out = "<input id=\"$id\" type='button' value='$out' $this->m_HTMLAttr $style $func>";
            $out = "<a ng-href=\"$link\" $this->m_HTMLAttr $style $func>".$out."</a>";
        }

        return $out . "\n" . $this->addSCKeyScript();
    }
    
 /**
     * Set the sort flag of the element
     *
     * @param integer $flag 1 or 0
     * @return void
     */
    public function setSortFlag($flag=null)
    {
        $this->m_SortFlag = $flag;
    }

    /**
     * Render label,
     * When render table, it return the table header; when render array, it return the display name
     *
     * @return string HTML text
     */
    public function renderLabel()
    {
        if ($this->m_Sortable == "Y")
        {
            $rule = $this->m_Name;

            $function = $this->m_FormName . ".SortRecord($rule,$this->m_SortFlag)";
            if($this->m_SortFlag == "ASC" || $this->m_SortFlag == "DESC"){
            	$class=" class=\"current\" ";
            }else{
            	$class=" class=\"normal\" ";
            }
            if ($this->m_SortFlag == "ASC")
            	$span_class = " class=\"sort_up\" ";
            else if ($this->m_SortFlag == "DESC")
                $span_class = " class=\"sort_down\" ";
            $sHTML = "<a href=javascript:Openbiz.CallFunction('" . $function . "') $class ><span $span_class >" . $this->m_Label ."</span>";            
            $sHTML .= "</a>";
        }
        else
        {
            $sHTML = $this->m_Label;
        }
        return $sHTML;
    }
    
    public function matchRemoteMethod($method)
    {
        return ($this->m_Sortable == "Y" && $method == "sortrecord");
    }    
}

?>
