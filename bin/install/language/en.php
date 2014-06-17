<?php 
define('STR_WIZARD_TITLE',		'Openbiz Cubi Installation');
define('STR_BTN_START_NOW',		'Start Now!');
define('STR_BTN_INSTALL_CUBI',	'Install Openbiz Cubi');
define('STR_BACK',				'Back');
define('STR_NEXT',				'Next');
define('STR_RETRY',				'Retry');

define('STR_SYSTEM_CHECK',		'System Environment Check');
define('STR_SYSTEM_CHECK_DESC',	'Please make sure the status of all necessary system component have installed before you click "Next" button,otherwise the application might would not work properly.');
define('STR_ITEM',				'Item');
define('STR_VALUE',				'Value');
define('STR_STATUS',			'Status');
define('STR_OPERATION_SYSTEM',		'Operation System');
define('STR_PHP_VERSION',			'PHP Version');
define('STR_PHP_VERSION_FAIL',		'FAIL - Zend Framework required PHP5.1.4 or later');
define('STR_OPENBIZ_PATH',			'Openbiz Path');
define('STR_OPENBIZ_PATH_FAIL',		'FAIL - OPENBIZ_HOME does not point to Openbiz installed path');
define('STR_ZEND_PATH',				'Zend Framework Path');
define('STR_ZEND_PATH_FAIL',		"FAIL - ZEND_FRWK_HOME doesn't point to Zend Framework installed path. Please modify ZEND_FRWK_HOME in ".OPENBIZ_HOME."/bin/sysheader_inc.php");
define('STR_PDO_EXTENSION',			'PDO Extensions');
define('STR_PDO_EXTENSION_FAIL',	'FAIL - PDO and pdo_mysql extensions are required.');

define('STR_DATABASE_CONFIGURATION',		'Database Configuration');
define('STR_DATABASE_CONFIGURATION_DESC_1',	'Please enter your database configuration information below.<br />If you are unsure of what to fill in, we suggest that you use the default values.');
define('STR_DATABASE_CONFIGURATION_DESC_2',	'The database information will be write to <strong>application.xml</strong>.');
define('STR_DATABASE_TYPE',			'Database Type');
define('STR_DATABASE_HOSTNAME',		'Database Host Name');
define('STR_DATABASE_PORT',			'Database Port');
define('STR_DATABASE_NAME',			'Database Name');
define('STR_DATABASE_USERNAME',		'Database Username');
define('STR_DATABASE_PASSWORD',		'Database Password');
define('STR_DATABASE_CREATE',		'Create Database');
define('STR_DATABASE_NOT_EMPTY',	'Specified database already has tables. Please try another empty DB.');


define('STR_APPLICATION_CONFIGURATION',		'Application Configuration');
define('STR_CHECK_WRITABLE_DIR',			'Check Writable Directories:');
define('STR_DEFAULT_DATABASE_FILE',			'Default Database in '.APP_HOME.DIRECTORY_SEPARATOR.'<strong >application.xml</strong>');
define('STR_NAME',				'Name');
define('STR_DRIVER',			'Driver');
define('STR_SERVER',			'Server');
define('STR_PORT',				'Port');
define('STR_DBNAME',			'DBName');
define('STR_USER',				'User');
define('STR_PASSWORD',			'Password');


define('STR_INSTALLATION_COMPLETED',		'Installation Completed');
define('STR_INSTALLATION_COMPLETED_DESC',	'Congratulations for completing Openbiz Cubi Setup Wizard. <br />For security reason, <strong style="color:#666666;">we strongly recommend you to delete install folder and remove write permission on applicaiton.xml now.</strong><br />And also please change default login info before use.');
define('STR_DEFAULT_LOGIN_INFO',			'Default Login Info');
define('STR_USERNAME',						'Username');
define('STR_READY_GO',						'Ready Go');
define('STR_LOGIN_TO_OPENBIZ',				'Login to Openbiz Cubi');
define('STR_REFERENCE_DOCUMENT',			'Reference Documents');
?>