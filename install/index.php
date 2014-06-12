<?php ob_start(); 
require_once('../bin/app_init.php');
require_once('include/install_controller.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo STR_WIZARD_TITLE;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="x-ua-compatible" content="ie=7" />
<link rel="stylesheet" href="style/default.css" type="text/css" />
<link rel="stylesheet" href="style/<?php echo $lang;?>.css" type="text/css" /> 
<link rel="stylesheet" href="../themes/default/css/openbiz.css" type="text/css" /> 
<script type="text/javascript" src="js/prototype.js"></script>
</head>
<body>
<div id="body_warp" align="center">
<?php
include('view/step'.$step.'.tpl.php'); 
?>
</div>
</body>
</html>