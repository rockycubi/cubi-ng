<?php

class DeviceUtil 
{
	public static $DEVICE = null;
	public static $DEVICE_STYLE = '';
	public static $DEVICE_TYPE = '';
	public static $PHONE_TOUCH = 0;
	
	public static function init()
	{
		self::get_device_info();
	}

	public static function get_device_info()
	{
		$device = '';
		$style = '';
		if(!isset($_SERVER['HTTP_USER_AGENT']))
		{
			return ;
		}
		if( stristr($_SERVER['HTTP_USER_AGENT'],'ipad') ) {
			$device = "ipad";
			$style = "touch";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'iphone') || strstr($_SERVER['HTTP_USER_AGENT'],'ipod') ) {
			$device = "iphone";
			$style = "touch";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'blackberry') ) {
			$device = "blackberry";
			$style = "touch";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'android') ) {
			$device = "android";
			$style = "touch";
		}

		self::$DEVICE_TYPE = $device; 
		self::$DEVICE_STYLE = $style; 
		if ($device != '' && $style=='touch') {
			self::$PHONE_TOUCH = 1;
			self::$DEVICE = 'mobile';
		}
	}
}

DeviceUtil::init();
?>