<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.data
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: MenuRecord.php 3364 2012-05-31 06:06:21Z rockyswen@gmail.com $
 */

/**
 * MenuRecord class, for tree structure
 *
 * @package openbiz.bin.data
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @since 1.2
 * @todo need to move to other package (tool, base, etc?)
 * @access public
 *
 */
class MenuRecord
{
 	public $m_Id;
 	public $m_PId; 
 	public $m_Key;  
    public $m_Name;
    public $m_Module;
    public $m_Description;
    public $m_URL;
    public $m_URL_Match;
	public $m_Target;
	public $m_CssClass;
	public $m_IconImage;
	public $m_IconCSSClass;
    public $m_Access;
	public $m_Current = 0;
	public $m_ChildNodes = null;

    /**
     * Initialize Node
     *
     * @param array $rec
     * @return void
     */
    function __construct($rec)
    {
        $this->m_Id = $rec['Id'];
        $this->m_PId = $rec['PId'];
        $this->m_Name = $rec['title'];
        $this->m_Module = $rec['module'];
        $this->m_Description = $rec['description'];
        $this->m_URL = $rec['link'];
        if (strpos($this->m_URL,'{')===0)
        	$this->m_URL = Expression::evaluateExpression($this->m_URL, $this);
        else if (!empty($this->m_URL)) {
        	if (strpos($this->m_URL,'/')===0)
        		$this->m_URL = APP_INDEX.$this->m_URL;
        	else
        		$this->m_URL = APP_INDEX.'/'.$this->m_URL;
        }
        $this->m_URL_Match = $rec['alias'];
        //$this->m_CssClass = $rec['Id'];
        $this->m_IconImage = $rec['icon'];
        $this->m_IconCSSClass = $rec['icon_css'];
        $this->m_Access = $rec['access'];
        
        $this->translate();	// translate for multi-language support
    }
    
    public function allowAccess()
    {
    	$access = $this->m_Access;
        if (!$access) $access = $this->m_Access;
        if ($access)
        	return BizSystem::allowUserAccess($access);
        return ALLOW;
    }
    
    protected function translate()
    {
    	$module = $this->m_Module;
    	if (!empty($this->m_Name))
    		$this->m_Name = I18n::t($this->m_Name, $this->getTransKey('Title'), $module);
    	if (!empty($this->m_Description))
    		$this->m_Description = I18n::t($this->m_Description, $this->getTransKey('Description'), $module);
    }

    protected function getTransKey($name)
    {
    	//return strtoupper('MENU_'.$this->m_Id.'_'.$name);
        $k = '_MENU_'.$this->m_Id.'_'.$name;
        return strtoupper($k);
    }
}
?>