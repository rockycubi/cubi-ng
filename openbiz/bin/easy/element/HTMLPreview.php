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

//include_once("Element.php");

/**
 * LebelText - class LabelText is element that view value who binds
 * with a BizField
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2009
 * @version 1.0
 * @access public
 */
class HTMLPreview extends Element
{
    public $m_FieldName;
    public $m_Label;
    public $m_DisplayFormat;
    public $m_Text;
    public $m_Link;    
    public $m_Target;
    public $m_MaxLength;
    public $m_Percent;
    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_FieldName = isset($xmlArr["ATTRIBUTES"]["FIELDNAME"]) ? $xmlArr["ATTRIBUTES"]["FIELDNAME"] : null;
        $this->m_Label = isset($xmlArr["ATTRIBUTES"]["LABEL"]) ? $xmlArr["ATTRIBUTES"]["LABEL"] : null;
        $this->m_Text = isset($xmlArr["ATTRIBUTES"]["TEXT"]) ? $xmlArr["ATTRIBUTES"]["TEXT"] : null;
        $this->m_Link = isset($xmlArr["ATTRIBUTES"]["LINK"]) ? $xmlArr["ATTRIBUTES"]["LINK"] : null;
        $this->m_Target = isset($xmlArr["ATTRIBUTES"]["TARGET"]) ? $xmlArr["ATTRIBUTES"]["TARGET"] : null;
        $this->m_MaxLength = isset($xmlArr["ATTRIBUTES"]["MAXLENGHT"]) ? $xmlArr["ATTRIBUTES"]["MAXLENGHT"] : null;
        $this->m_MaxLength = isset($xmlArr["ATTRIBUTES"]["MAXLENGTH"]) ? $xmlArr["ATTRIBUTES"]["MAXLENGTH"] : null;
        $this->m_Percent = isset($xmlArr["ATTRIBUTES"]["PERCENT"]) ? $xmlArr["ATTRIBUTES"]["PERCENT"] : "N";
        $this->m_DisplayFormat = isset($xmlArr["ATTRIBUTES"]["DISPLAYFORMAT"]) ? $xmlArr["ATTRIBUTES"]["DISPLAYFORMAT"] : null;
    }

    /**
     * Get target of link
     * <a target='...'>...</a>
     *
     * @return string
     */
    protected function getTarget()
    {
        if ($this->m_Target == null)
            return null;

        return "target='" . $this->m_Target ."'";
        ;
    }

    /**
     * Get link of LabelText
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
     * Get text of label
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
     * Render label
     *
     * @return string HTML text
     */
    public function renderLabel()
    {
        return $this->m_Label;
    }

    /**
     * Render, draw the element according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $value = $this->m_Text ? $this->getText() : $this->m_Value;
        
        if ($value == null || $value =="")
            return "";

        $style = $this->getStyle();
        $id = $this->m_Name;
        $func = $this->getFunction();

        if ($this->m_Translatable == 'Y')
            $value = $this->translateString($value);
        $value_org = strip_tags($value);
        if((int)$this->m_MaxLength>0){
	        if(function_exists('mb_strlen') && function_exists('mb_substr')){
	        	if(mb_strlen($value,'UTF8') > (int)$this->m_MaxLength){
	        		$value = mb_substr($value,0,(int)$this->m_MaxLength,'UTF8').'...';
	        	}        	
	        }else{
	        	if(strlen($value) > (int)$this->m_MaxLength){
	        		$value = substr($value,0,(int)$this->m_MaxLength).'...';
	        	}         	
	        }
        }
        if($this->m_Height)
        {
        	$height = $this->m_Height.'px';
        }
        if ($value!=null)
        {
        	$header = "
        	<head><link href=\"".JS_URL."/ckeditor/contents.css\" rel=\"stylesheet\" type=\"text/css\"></head>
        	";
           $sHTML = "
           			<script>".$id."_data=".json_encode($header.$value)."</script>
           			<iframe border=\"0\" frameborder=\"0\" allowtransparency=\"true\"
           				tabIndex=\"-1\" style=\"width:100%;height:$height;background: none repeat scroll 0 0 transparent;border: 0 none;border-collapse: collapse;\"
           				src=\"javascript:setTimeout(%20function()%7Bdocument.open()%3Bdocument.write(%20window.parent%5B%20%22".$id."_data%22%20%5D%20)%3Bdocument.close()%3Bwindow.parent%5B%20%22".$id."_data%22%20%5D%20%3D%20null%3B%7D%2C%20200%20)\">
           			</iframe>
           			";
            
        }

        return $sHTML;
    }

}

?>