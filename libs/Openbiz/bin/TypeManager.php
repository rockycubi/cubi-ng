<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: TypeManager.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * A class with help methods to format data to UI and unformat UI input to data.
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class TypeManager
{
    /**
     * the current locale information, see {@link localeconv}
     * @var array
     */
    protected $_localeInfo;

    /**
     * Constructor of TypeManager, set locale with $localCode parameter
     *
     * @param string $localeCode
     * @return void
     **/
    public function __construct ($localeCode = "")
    {
        //try to set correct locale for current language as defined in app.inc section I18n
        $currentLanguage = I18n::getCurrentLangCode();
        $localeCode = $GLOBALS["local"][$currentLanguage];
        setlocale(LC_ALL, $localeCode);
        $this->_localeInfo = localeconv();
        if ($this->_localeInfo['frac_digits'] > 10)
            $this->_localeInfo = null;
    }

    /**
     * Convert Formatted String To Value
     *
     * @param string $type - field type
     * @param string $format - type format
     * @param string $formattedString - formatted string
     * @return mixed
     **/
    public function formattedStringToValue($type, $format, $formattedString)
    {
        if ($formattedString === null || $formattedString === "")
            return null;
        switch ($type)
        {
            case "Number": return $this->numberToValue($format, $formattedString);
            case "Text": return $this->textToValue($format, $formattedString);
            case "Date": return $this->dateToValue($format, $formattedString);
            case "Datetime": return $this->datetimeToValue($format, $formattedString);
            case "Currency": return $this->currencyToValue($format, $formattedString);
            case "Phone": return $this->phoneToValue($format, $formattedString);
            default: return $formattedString;
        }
    }

    /**
     * Convert Value To Formatted String
     *
     * @param string $type - field type
     * @param string $format - type format
     * @param string $value - value
     * @return string - formatted string
     **/
    public function valueToFormattedString($type, $format, $value)
    {
        switch ($type)
        {
            case "Number": return $this->valueToNumber($format, $value);
            case "Text": return $this->valueToText($format, $value);
            case "Date": return $this->valueToDate($format, $value);
            case "Datetime": return $this->valueToDatetime($format, $value);
            case "Currency": return $this->valueToCurrency($format, $value);
            case "Phone": return $this->valueToPhone($format, $value);
            default: return $value;
        }
    }

    /**
     * Format value to number
     *
     * @param string $format - type format
     * @param string $value - value
     * @return string formatted number
     */
    protected function valueToNumber($format, $value)
    {
        if ($format[0] == "%")
            return sprintf($format, $value);
        if (! $this->_localeInfo)
            return $value;
        $formattedNumber = $value;
        if ($format == "Int")
            $formattedNumber = number_format($value, 0, $this->_localeInfo['decimal_point'], $this->_localeInfo['thousands_sep']);
        else
        if ($format == "Float")
            $formattedNumber = number_format($value, $this->_localeInfo['frac_digits'], $this->_localeInfo['decimal_point'], $this->_localeInfo['thousands_sep']);
        return $formattedNumber;
    }

    /**
     * Unformat a number to value
     *
     * @param string $format - type format
     * @param string $formattedValue - formatted string
     * @return mixed
     */
    protected function numberToValue($format, $formattedValue)
    {
        if ($formattedValue === false || $formattedValue === true)
            return null;
        if ($format[0] == "%")
            return sscanf($formattedValue, $format);
        if (! $this->_localeInfo)
            return $formattedValue;
        $tmp = str_replace($this->_localeInfo['thousands_sep'], null, $formattedValue);
        $tmp = str_replace($this->_localeInfo['decimal_point'], ".", $tmp);
        if ($format == "Int")
            return (int) $tmp;
        return $tmp;
    }

    /**
     * Format value to text
     *
     * @param string $format - type format
     * @param string $value - value
     * @return string formatted string
     */
    protected function valueToText($format, $value)
    {
        return $value;
    }

    /**
     * Unformat a text to value
     *
     * @param string $format - type format
     * @param string $formattedValue - formatted string
     * @return mixed
     */
    protected function textToValue($format, $formattedValue)
    {
        if ($formattedValue === false)
            $formattedValue = "";
        return $formattedValue;
    }

    /**
     * Format value to date
     *
     * @param string $format - type format
     * @param string $value - value
     * @return string - Empty string or formatted Time/Date
     */
    protected function valueToDate($format, $value)
    {
        // ISO format YYYY-MM-DD as input
        if ($value == "0000-00-00")
            return "";
        if (! $value)
            return "";
        if (strlen(trim($value)) < 1)
            return "";
        $tt = strtotime($value);
        if ($tt != - 1)
            return strftime($format, strtotime($value));
        return "";
    }

    /**
     * Unformat a date to value
     *
     * @param string $format - type format
     * @param string $formattedValue - formatted string
     * @return mixed $stdFormat - ISO format YYYY-MM-DD
     */
    protected function dateToValue($format, $formattedValue)
    {
        if (! $formattedValue)
            return '';
        $stdFormat = $this->convertDatetimeFormat($formattedValue, $format, '%Y-%m-%d');
        return $stdFormat;
    }

    /**
     * Format value to date time
     *
     * @param string $fmt - type format
     * @param string $value - value
     * @return string - formatted string
     */
    protected function valueToDatetime ($fmt, $value)
    {
        // ISO format YYYY-MM-DD HH:MM:SS as input
        if ($value == "0000-00-00 00:00:00")
            return "";
        if ($fmt == null)
            $fmt = DATETIME_FORMAT;
        return $this->valueToDate($fmt, $value);
    }

    /**
     * Unformat a datetime to value
     *
     * @param string $format - type format
     * @param string $formattedValue - formatted string
     * @return mix $stdFormat - ISO format YYYY-MM-DD HH:MM:SS
     */
    protected function datetimeToValue($format, $formattedValue)
    {
        if (! $formattedValue)
            return '';
        $stdFormat = $this->convertDatetimeFormat($formattedValue, $format, '%Y-%m-%d %H:%M:%S');
        return $stdFormat;
    }

    /**
     * Format value to currency
     *
     * @param string $format - type format
     * @param string $value - value
     * @return string - formatted string
     */
    protected function valueToCurrency($format, $value)
    {
        if (! $value)
            return "";
        if (! $this->_localeInfo)
            return $value;
        $fmtNum = number_format($value, $this->_localeInfo['frac_digits'], $this->_localeInfo['mon_decimal_point'], $this->_localeInfo['mon_thousands_sep']);
        return $this->_localeInfo["currency_symbol"] . $fmtNum;
    }

    /**
     * Unformat a currency to value
     *
     * @param string $format - type format
     * @param string $formattedValue - formatted string value
     * @return mixed - value
     */
    protected function currencyToValue($format, $formattedValue)
    {
        if (! $this->_localeInfo)
            return $formattedValue;
        $tmp = str_replace($this->_localeInfo["currency_symbol"], null, $formattedValue);
        $tmp = str_replace($this->_localeInfo['thousands_sep'], null, $tmp);
        return (float) $tmp;
    }

    /**
     * Format value to phone number
     *
     * @param string $fmt - type format
     * @param string $value - value
     * @return string - formatted string
     */
    protected function valueToPhone($mask, $value)
    {
        if (substr($value, 0, 1) == "*") // if phone starts with "*", it's an international number, don't convert it
            return $value;
        if (trim($value) == "")
            return $value;
        $maskLen = strlen($mask);
        $ph_len = strlen($value);
        $ph_fmt = $mask;
        $j = 0;
        for ($i = 0; $i < $maskLen; $i ++)
        {
            if ($mask[$i] == "#")
            {
                $ph_fmt[$i] = $value[$j];
                $j ++;
                if ($j == $ph_len)
                    break;
            }
        }
        return substr($ph_fmt, 0, $i + 1);
    }

    /**
     * Unformat a phone to value
     *
     * @param string $fmt - type format
     * @param string $formattedValue - formatted string
     * @return mixed
     */
    protected function phoneToValue($mask, $formattedValue)
    {
        if ($formattedValue[0] == "*")
            return $formattedValue;
        return ereg_replace("[^0-9]", null, $formattedValue);
    }

    /**
     * Convert a formatted datetime to another format
     *
     * @param $oldFormattedValue - old formated value
     * @param $oldFormat - old format of value
     * @param $newFormat - new format of value
     * @return string new formatted datetime value
     */
    public function convertDatetimeFormat($oldFormattedValue, $oldFormat, $newFormat)
    {
        if ($oldFormat == $newFormat)
            return $oldFormattedValue;
        $timeStamp = $this->_parseDate($oldFormat, $oldFormattedValue);
        return strftime($newFormat, $timeStamp);
    }

    /**
     * Parse formatted date to time value
     *
     * @param string $fmt type - format
     * @param string $fmtValue - formatted string
     * @return int $timestamp - vallue of time
     */
    private function _parseDate ($fmt, $fmtValue)
    {
        $y = 0;
        $m = 0;
        $d = 0;
        $hr = 0;
        $min = 0;
        $sec = 0;
        $a = preg_split("/\W+/", $fmtValue);
        preg_match_all("/%./", $fmt, $b);
        for ($i = 0; $i < count($a); ++ $i)
        {
            if (! $a[$i])
                continue;
            switch ($b[0][$i])
            {
                case "%d": // the day of the month ( 00 .. 31 )
                case "%e": // the day of the month ( 0 .. 31 )
                    $d = intval($a[$i], 10);
                    break;
                case "%m": // month ( 01 .. 12 )
                    $m = intval($a[$i], 10);
                    break;
                case "%Y": // year including the century ( ex. 1979 )
                case "%y": // year without the century ( 00 .. 99 )
                    $y = intval($a[$i], 10);
                    if ($y < 100)
                        $y += ($y > 29) ? 1900 : 2000;
                    break;
                case "%H": // hour ( 00 .. 23 )
                case "%I": // hour ( 01 .. 12 )
                case "%k": // hour ( 00 .. 23 )
                case "%l": // hour ( 01 .. 12 )
                    $hr = intval($a[$i], 10);
                    break;
                case "%P": // PM or AM
                case "%p": // pm or am
                    if ($a[$i] == 'pm' && $hr < 12)
                        $hr += 12;
                    else
                    if ($a[$i] == 'am' && $hr >= 12)
                        $hr -= 12;
                    break;
                case "%M": // minute ( 00 .. 59 )
                    $min = intval($a[$i], 10);
                    break;
                case "%S": // second ( 00 .. 59 )
                    $sec = intval($a[$i], 10);
                    break;
                //default:
            }
        }
        $timeStamp = mktime($hr, $min, $sec, $m, $d, $y);
        return $timeStamp;
    }
    
    public function setLocaleInfo($localeInfo)
    {
        $this->_localeInfo = $localeInfo;
    }    
}
