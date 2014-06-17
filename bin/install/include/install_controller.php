<?php 
require_once('util.php');


$isInstalled = false;
if(is_file(dirname(dirname(dirname(__FILE__))).'/files/install.lock')){
	$isInstalled = true;
}

//detech default language
$lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
if(!is_file(dirname(dirname(__FILE__)).'/language/'.$lang.'.php'))
{
	$lang = 'en';
}
require_once dirname(dirname(__FILE__)).'/language/'.$lang.'.php';

// response ajax call
if($isInstalled==false){
	if (isset($_REQUEST['action']) && !$isInstalled)
	{
	   if ($_REQUEST['action']=='create_db')
	   {
	      createDB();
	      exit;
	   }
	   if ($_REQUEST['action']=='load_modules')
	   {
	      loadModules();
	      exit;
	   }
	   if ($_REQUEST['action']=='replace_db_cfg')
	   {
	      replaceDbConfig();
	      exit;
	   }
	}
}

$stepArr = array("",
				 "System Check",
				 "Database Configuration",
				 "Application Configuration",
				 "Finish"
				 );
$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : '0';

if($isInstalled){
	$step=count($stepArr)-1;
}

if((int)$step>0 && (int)$step<count($stepArr)-1){
	$progress_bar = "<ul class=\"progress_bar\">";
	for($i=0;$i<count($stepArr);$i++){
		if($stepArr[$i]){
			$text = $i.". ".$stepArr[$i];
			if($i>$step){
				$text = "<a>$text</a>";				
				$style="normal";
			}elseif($i==$step){
				$text = "<a href=\"?step=$i\">$text</a>";
				$style= "current";
			}else{
				$text = "<a href=\"?step=$i\">$text</a>";
				$style= "past";
			}
			$progress_bar .= "<li id=\"step_$i\" class=\"$style\">$text</li>";		
		}
	}
	$progress_bar .= "</ul>";	
}
?>