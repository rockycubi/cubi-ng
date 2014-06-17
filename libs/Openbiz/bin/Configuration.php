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
 * @license   http://www.opensource.org/licenses/bsd-license.php     BSD
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: Configuration.php 3334 2012-05-30 08:07:05Z agus.suhartono@gmail.com $
 */

/**
 * Configuration class
 * Configuration management class that has help methods to get data from config.xml
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 * @version	  $Id: Configuration.php 3334 2012-05-30 08:07:05Z agus.suhartono@gmail.com $
 */
class Configuration
{
    /**
     * Array of XML Data
     *
     * @var array
     */
    private $_xmlArr;
    
    /**
     * Associate Array of pre-configure database conections
     *
     * @var array
     */
    private $_databaseInfo;
    private $_themeInfo;

    /**
     * Read application.xml into a internal array.
     */
    public function __construct ()
    {
        $xmlFile = BizSystem::GetXmlFileWithPath("application");
        $this->_xmlArr = &BizSystem::getXmlArray($xmlFile);
    }

    /**
     * Returns the database info from <DataSource> defined in application.xml as an array.
     * Returned array is a 2D map.
     * (DBName1 => ["Name"], ["Driver"], ["Server"], ["DBName"], ["User"], {"Password"])
     * (DBName2 => ["Name"], ["Driver"], ["Server"], ["DBName"], ["User"], {"Password"])
     * (...)
     * If DBName is given, returns the record only related to the given DBName,
     * otherwise returns all records
     *
     * @param string $dbName
     * @return array database information
     */
    public function getDatabaseInfo($dbName = null)
    {
        if ($dbName && $this->_databaseInfo[$dbName])
            return $this->_databaseInfo[$dbName];
        if (! $this->_xmlArr["APPLICATION"]["DATASOURCE"])
        {
            $errMsg = BizSystem::getMessage("SYS_ERROR_NODBINFO");
            trigger_error($errMsg, E_USER_ERROR);
        }
        $breakFlag = false;
        foreach ($this->_xmlArr["APPLICATION"]["DATASOURCE"]["DATABASE"] as $db)
        {
            if (array_key_exists('ATTRIBUTES', $this->_xmlArr["APPLICATION"]["DATASOURCE"]["DATABASE"]))
            {
                $db = $this->_xmlArr["APPLICATION"]["DATASOURCE"]["DATABASE"];
                $breakFlag = true;
            }
            $tmp["Name"]     = $db["ATTRIBUTES"]["NAME"];
            $tmp["Driver"]   = $db["ATTRIBUTES"]["DRIVER"];
            $tmp["Server"]   = $db["ATTRIBUTES"]["SERVER"];
            $tmp["DBName"]   = $db["ATTRIBUTES"]["DBNAME"];
            $tmp["User"]     = $db["ATTRIBUTES"]["USER"];
            $tmp["Password"] = $db["ATTRIBUTES"]["PASSWORD"];
            $tmp["Port"]     = isset($db["ATTRIBUTES"]["PORT"]) ? $db["ATTRIBUTES"]["PORT"] : null;
            $tmp["Charset"]  = isset($db["ATTRIBUTES"]["CHARSET"]) ? $db["ATTRIBUTES"]["CHARSET"] : null;
            $tmp["Options"]  = isset($db["ATTRIBUTES"]["OPTIONS"]) ? $db["ATTRIBUTES"]["OPTIONS"] : null;
            $this->_databaseInfo[$tmp["Name"]] = $tmp;
            if ($breakFlag)
                break;
        }

        if ($dbName && $this->_databaseInfo[$dbName])
            return $this->_databaseInfo[$dbName];
        if ($dbName && ! isset($this->_databaseInfo[$dbName]))
        {
            $errMsg = BizSystem::getMessage("DATA_INVALID_DBNAME", array($dbName,$dbName));
            trigger_error($errMsg, E_USER_ERROR);
        }
        if (! $dbName)
            return $this->_databaseInfo;
    }

}
