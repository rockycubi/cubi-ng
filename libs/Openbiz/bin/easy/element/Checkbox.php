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
 * @version   $Id: Checkbox.php 3780 2011-04-18 18:26:11Z jixian2003 $
 */

//include_once("OptionElement.php");

/**
 * Checkbox class is element for Checkbox
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class Checkbox extends InputElement
{
	protected $trueValue="1";
	protected $falseValue="0";
	
	protected function readMetaData($xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->trueValue = isset($xmlArr["ATTRIBUTES"]["TRUEVALUE"]) ? $xmlArr["ATTRIBUTES"]["TRUEVALUE"] : "1";
		$this->falseValue = isset($xmlArr["ATTRIBUTES"]["FALSEVALUE"]) ? $xmlArr["ATTRIBUTES"]["FALSEVALUE"] : "0";
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
        $func = $this->getFunction();

		$sHTML = "<INPUT TYPE=\"CHECKBOX\" NAME=\"$this->m_Name\" ID=\"$this->m_Name\" $this->m_ModelText $this->m_HTMLAttr $style $func ng-true-value='$this->trueValue' ng-false-value='$this->falseValue'/> ".$text."";

        return $sHTML;
    }
}

?>
