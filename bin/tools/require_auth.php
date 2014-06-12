<?php 
include_once (dirname(dirname(__FILE__))."/app_init.php");
if(!defined("CLI")){
	exit;
}

foreach($argv as $arg)
{
	if(preg_match("/-p(.*)/si",$arg,$match)){		
		$password_preset = trim($match[1]);
	}
}

echo "Authentication Required: ".PHP_EOL;
$do = "system.do.UserDO";
$do = BizSystem::getObject($do);
$adminRec = $do->fetchById(1);

$username = $adminRec['username'];
$password = $adminRec['password'];


echo "Your password will not prompted in below".PHP_EOL;
echo "Username: [ $username ]".PHP_EOL;
echo "Password: ";

$auth_counter =1;
while(1) {
	if(isset($password_preset) && $password_preset!=''){
		echo "".PHP_EOL;
		$svcobj 	= BizSystem::getService(AUTH_SERVICE);   
		$result = $svcobj->authenticateUser($username,$password_preset);
		if($result){		
			echo "Authentication Sucessed! ".PHP_EOL;	
			echo "Access Grant! ".PHP_EOL;	
			break;
		}else{
			echo PHP_EOL."Access Denied! ".PHP_EOL;
			exit;
		}
	}
	
	
	
	system('stty -echo');
	$password_input = trim(fgets(STDIN));
	system('stty echo');
	echo PHP_EOL;
	
	$svcobj 	= BizSystem::getService(AUTH_SERVICE);   
	$result = $svcobj->authenticateUser($username,$password_input);
	if($result){		
		echo "Authentication Sucessed! ".PHP_EOL;	
		echo "Access Grant! ".PHP_EOL;	
		break;
	}else{
		echo "Authentication Failed! ";		
				
	}
	$auth_counter++;
	if($auth_counter>3){
		echo PHP_EOL."Access Denied! ".PHP_EOL;
		exit;
	}else{
		echo "Please Try again ($auth_counter/3) ".PHP_EOL;		
		echo "Password: ";
	}
	
}
?>