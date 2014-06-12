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
 * @version   $Id: LabelList.php 543 2009-10-03 08:50:00Z mr_a_ton$
 */

//include_once("OptionElement.php");

/**
 * LabelList class is element that show description (read only) from Selection.xml
 *
 * @package openbiz.bin.easy.element
 * @author Agus Suhartono, Rocky Swen (original ListBox author)
 * @copyright Copyright (c) 2009
 * @version 1.0
 * @access public
 */
class LabelList extends OptionElement
{
    public $m_BlankOption;

    /**
     * Read metadata info from metadata array and store to class variable
     *
     * @param array $xmlArr metadata array
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_Link = isset($xmlArr["ATTRIBUTES"]["LINK"]) ? $xmlArr["ATTRIBUTES"]["LINK"] : null;
        $this->m_BlankOption = isset($xmlArr["ATTRIBUTES"]["BLANKOPTION"]) ? $xmlArr["ATTRIBUTES"]["BLANKOPTION"] : null;
    }    
    /**
     * Get link that evaluated by Expression::evaluateExpression
     *
     * @return string link
     */
    protected function getLink()
    {
        if ($this->m_Link == null)
            return null;
        $formobj = $this->getFormObj();
        return Expression::evaluateExpression($this->m_Link, $formobj);
    }

    /**
     * Render, draw the element to show description
     *
     * @return string HTML text
     */
    public function render()
    {
    	$value = $this->m_Text ? $this->getText() : $this->m_Value;
        $fromList   = array();
        $this->getFromList($fromList);
        $valueArr = explode(',', $this->m_Value);
        $style = $this->getStyle();
        $func = $this->getFunction();
        $id = $this->m_Name;
        $selectedStr = '';

        $selectedStr = $value;

        foreach ($fromList as $option)
        {
            $test = array_search($option['val'], $valueArr);
            if (!($test === false))
            {
                $selectedStr = $option['txt'] ;
                break;
            }
        }
        
        if($selectedStr=="0" || $selectedStr==null){
        	$selectedStr = $this->m_BlankOption;
        }

        if ($this->getLink())
        {
            $link = $this->getLink();            
            $sHTML = "<a id=\"$id\" href=\"$link\" $func $style>" . $selectedStr . "</a>";
        }
        else
            $sHTML = "<span $func $style>" . $selectedStr . "</span>";

        if($this->m_BackgroundColor)
        {
            	$bgcolor = $this->getBackgroundColor();
       			if($bgcolor){
            		$sHTML = "<div style=\"background-color:#".$bgcolor.";text-indent:10px;\" >$sHTML</div>";
            	}
        }            
        return $sHTML;
    }
}
?>