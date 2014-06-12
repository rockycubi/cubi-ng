<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.websvc.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: WebsvcError.php 3376 2012-05-31 06:23:51Z rockyswen@gmail.com $
 */


class WebsvcError
{   
    const OK = 0;
    const INVALID_APIKEY = 601;
    const NOT_AUTH = 602;
    const INVALID_METHOD = 603;
    const INVALID_ARGS = 604;
    const SERVICE_ERROR = 605;
    
    protected static $_errorMessage = 
        array(
            '0'=>'Success',
            '601'=>'Incorrect api key or secret.',
            '602'=>'No permission to access the service.',
            '603'=>'Invalid service method',
            '604'=>'Invalid input service method arguments',
            '605'=>'Service internal error.'
        );
    
    public static function getErrorMessage($errorCode)
    {
        if (isset(self::$_errorMessage[$errorCode]))
            return self::$_errorMessage[$errorCode];
        return '';
    }
}

?>