<?php 
define('STR_WIZARD_TITLE',		'Openbiz Cubi 安装部署向导');
define('STR_BTN_START_NOW',		'现在开始!');
define('STR_BTN_INSTALL_CUBI',	'安装 Openbiz Cubi');
define('STR_BACK',				'上一步');
define('STR_NEXT',				'下一步');
define('STR_RETRY',				'重试');

define('STR_SYSTEM_CHECK',		'系统环境检测');
define('STR_SYSTEM_CHECK_DESC',	'请确认所有系统部件的状态全部正常后，点击“下一步”按钮，否则安装程序将无法继续。');
define('STR_ITEM',				'条目');
define('STR_VALUE',				'数值');
define('STR_STATUS',			'状态');
define('STR_OPERATION_SYSTEM',		'操作系统');
define('STR_PHP_VERSION',			'PHP 版本');
define('STR_PHP_VERSION_FAIL',		'FAIL - Zend Framework 需要 PHP5.1.4 或更高');
define('STR_OPENBIZ_PATH',			'Openbiz 路径');
define('STR_OPENBIZ_PATH_FAIL',		'FAIL - OPENBIZ_HOME 没有正确指向到 Openbiz 的安装路径');
define('STR_ZEND_PATH',				'Zend Framework 路径');
define('STR_ZEND_PATH_FAIL',		"FAIL - ZEND_FRWK_HOME 没有正确指向指向到 Zend Framework 的安装路径. 请修改 ZEND_FRWK_HOME 在 ".OPENBIZ_HOME."/bin/sysheader_inc.php 文件中");
define('STR_PDO_EXTENSION',			'PDO 扩展');
define('STR_PDO_EXTENSION_FAIL',	'FAIL - PDO 扩展和 pdo_mysql 驱动是必须的.');

define('STR_DATABASE_CONFIGURATION',		'数据库配置');
define('STR_DATABASE_CONFIGURATION_DESC_1',	'请在如下表单中填写您的数据库连接信息<br />如果您不确定某项信息该如何填写，我们建议您暂且保留默认设置');
define('STR_DATABASE_CONFIGURATION_DESC_2',	'数据库连接信息将被写入文件<strong>application.xml</strong>.');
define('STR_DATABASE_TYPE',			'数据库类型');
define('STR_DATABASE_HOSTNAME',		'数据库主机');
define('STR_DATABASE_PORT',			'数据库端口');
define('STR_DATABASE_NAME',			'数据库名称');
define('STR_DATABASE_USERNAME',		'数据库帐户');
define('STR_DATABASE_PASSWORD',		'数据库密码');
define('STR_DATABASE_CREATE',		'自动创建该数据库');
define('STR_DATABASE_NOT_EMPTY',	'您所选择的数据库中已经存在数据表，请尝试制定另一个空白的数据库.');

define('STR_APPLICATION_CONFIGURATION',		'应用程序配置检测');
define('STR_CHECK_WRITABLE_DIR',			'检查目录是否具有可写入权限');
define('STR_DEFAULT_DATABASE_FILE',			'默认数据库连接 将被写入 '.APP_HOME.DIRECTORY_SEPARATOR.'<strong >application.xml</strong>');
define('STR_NAME',				'名称');
define('STR_DRIVER',			'驱动');
define('STR_SERVER',			'主机');
define('STR_PORT',				'端口');
define('STR_DBNAME',			'数据库名');
define('STR_USER',				'用户名');
define('STR_PASSWORD',			'密码');


define('STR_INSTALLATION_COMPLETED',		'安装已经完成');
define('STR_INSTALLATION_COMPLETED_DESC',	'祝贺您已经完成了 Openbiz Cubi 安装向导。  <br />为了降低安全风险, <strong style="color:#666666;">我们强烈建议您现在动手删除 install 目录，<br/>并赋予 application.xml 只读的权限。</strong><br />并且在您正式使用系统前修改掉默认的系统密码。');
define('STR_DEFAULT_LOGIN_INFO',			'默认登陆信息');
define('STR_USERNAME',						'用户名');
define('STR_READY_GO',						'立即开始');
define('STR_LOGIN_TO_OPENBIZ',				'登陆 Openbiz Cubi');
define('STR_REFERENCE_DOCUMENT',			'相关参考文档');
?>