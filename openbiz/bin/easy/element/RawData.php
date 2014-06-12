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
 * @version   $Id: RawData.php 3437 2011-03-08 16:40:19Z jixian2003 $
 */

//include_once("Element.php");

/**
 * RawData class is element for render raw data
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class RawData extends Element
{		

    public $m_UnSerialize;

    /**
     * Read metadata info from metadata array and store to class variable
     *
     * @param array $xmlArr metadata array
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_FieldName = isset($xmlArr["ATTRIBUTES"]["FIELDNAME"]) ? $xmlArr["ATTRIBUTES"]["FIELDNAME"] : null;
        $this->m_Label = isset($xmlArr["ATTRIBUTES"]["LABEL"]) ? $xmlArr["ATTRIBUTES"]["LABEL"] : null;
        $this->m_Text = isset($xmlArr["ATTRIBUTES"]["TEXT"]) ? $xmlArr["ATTRIBUTES"]["TEXT"] : null;
        $this->m_Link = isset($xmlArr["ATTRIBUTES"]["LINK"]) ? $xmlArr["ATTRIBUTES"]["LINK"] : null;
        $this->m_UnSerialize = isset($xmlArr["ATTRIBUTES"]["UNSERIALIZE"]) ? $xmlArr["ATTRIBUTES"]["UNSERIALIZE"] : null;
    }

    /**
     * Get link of element
     *
     * @return string
     */
    protected function getLink()
    {
        if ($this->m_Link == null)
            return null;
        $formobj = $this->getFormObj();
        return Expression::evaluateExpression($this->m_Link, $formobj);
    }

    /**
     * Render label, just return elemen label
     *
     * @return string HTML text
     */
    public function renderLabel()
    {
        return $this->m_Label;
    }

    /**
     * Get text of element
     *
     * @return string
     */
    protected function getText()
    {
        if ($this->m_Text == null)
            return null;
        $formObj = $this->getFormObj();
        return Expression::evaluateExpression($this->m_Text, $formObj);
    }
    
    /**
     * Render, draw the element according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {       
    	$value = $this->m_Text ? $this->getText() : $this->m_Value;	
        if ($value === null || $value == "")
            return $value;

        if($this->m_UnSerialize=="Y")
        {
            $value = unserialize($value);
        }

        if($this->m_Translatable=='Y')
        {
            if(is_array($value))
            {
                foreach($value as $key => $value)
                {
                    $value[$key] = $value = $this->translateString($value);
                }
            }
            else
            {
                $value = $this->translateString($value);
            }
        }
        return $value;
    }
}

?>