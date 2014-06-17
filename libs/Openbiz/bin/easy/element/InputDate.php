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
 * @version   $Id: InputDate.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

//include_once("InputElement.php");

/**
 * InputDate class is element for input date with date picker
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class InputDate extends InputText {
    public $m_DateFormat;

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr) {
        parent::readMetaData($xmlArr);
        $this->m_DateFormat  = isset($xmlArr["ATTRIBUTES"]["DATEFORMAT"]) ? $xmlArr["ATTRIBUTES"]["DATEFORMAT"] : null;
    }

    /**
     * Render / draw the element according to the mode
     *
     * @return string HTML text
     */
    public function render() {
        BizSystem::clientProxy()->includeCalendarScripts();

        $format = $this->m_DateFormat ? $this->m_DateFormat : "%Y-%m-%d";

        $sHTML = parent::render();

        $showTime = 'false';
        //$image = "<img src=\"".Resource::getImageUrl()."/calendar.gif\" border=0 title=\"Select date...\" align='top' hspace='2'>";
        $sHTML .= "<a class=\"date_picker\" href=\"javascript: void(0);\" onclick=\"return showCalendar('$this->m_Name','$format',$showTime,true);\"></a>";
        return $sHTML;
    }

}

?>