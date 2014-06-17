<?php
define('SYS_ERROR_RPCARGS', "You pass less than 2 arguments which are object name and method name.");
define('SYS_ERROR_CLASSNOTFOUND', "Cannot find the class with name as %s.");
define('SYS_ERROR_METHODNOTFOUND', "Object %s doesn't have such a method %s.");
define('SYS_ERROR_NODBINFO', "No Database indefine('Formation found in the config file.");
define('SYS_ERROR_INVALID_THEME', "Unable to find a valide entry with Name as '%s' in metadata/Config.xml. Please add a Theme entry with Name as '%s' in metadata/Config.xml file");
define('SYS_ERROR_REQUEST_REJECT', "The client request '%s->%s' is not allowed.");
define('SYS_ERROR_INVALID_MSGFILE', "Unable to open the message file %s.");

define('DATA_UNABLE_CONN_DB', "Unable to connection to database %s.");
define('DATA_INVALID_DBNAME', "Unable to find a valide entry with Name as '%s' in metadata/Config.xml. Please add a Database entry with Name as '%s' in metadata/Config.xml file");
define('DATA_NOT_UNIQUE', "Unable to save the data because uniqueness check on %s fails.");
define('DATA_FIELD_REQUIRED', "Field %s is required.");
define('DATA_FIELD_INVALID_INPUT', "Unable to validate field [%s] = %s as input value against rule %s.");
define('DATA_NO_PERMISSION_UPDATE', "You don't have permission to update this record.");
define('DATA_NO_PERMISSION_DELETE', "You don't have permission to delete this record.");
define('DATA_UNABLE_GET_ID', "Unable to get ID for new inserted record: Id column is not Identity type.");
define('DATA_FIELD_INCORRECT_TYPE', "The value you input on %s is not a vaild %s.");
define('DATA_UNABLE_DEL_REC_CASCADE', "Unable to delete the record due to cascade restriction on %s.");
define('DATA_UNABLE_UPD_REC_CASCADE', "Unable to update the record due to cascade restriction on %s.");
define('DATA_ERROR_QUERY', "Error in query:");


define('FORM_ELEMENT_REQUIRED', "%s is required.");
define('FORM_ELEMENT_INVALID_INPUT', "Unable to validate element [%s] = %s as input value against rule %s.");
define('BFM_ERROR_INVALID_DISPMODE', "Unable to find a valid display mode for BizForm %s.");
define('PLEASE_EDIT_A_RECORD', "Please select a record to edit.");
define('UNABLE_TO_LOCATE_TEMPLATE_FILE', "Unable to location template file %s.");
define('FORM_OPERATION_NOT_PERMITTED',"You don't have enough permission to do this operation.");

?>