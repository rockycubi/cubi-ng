<?php
ob_start();
require_once('app_init.php');
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Openbiz Design Center</title>
	<link rel="stylesheet" href="<?php echo THEME_URL.'/'.DEFAULT_THEME_NAME;?>/css/general.css" type="text/css">
	<link rel="stylesheet" href="<?php echo THEME_URL.'/'.DEFAULT_THEME_NAME;?>/css/openbiz.css" type="text/css">
</head>
<body style="padding-left:20px; width:90%;background-color:#ffffff;">

<H2 style="margin-top:20px;">Element Attribute Editing Area</H2>
<hr/>
<h3>Metadata editor hints</h3>
<p style="padding-bottom:10px;padding-left:10px;">
To edit a node attribute, click left tree node to load the attributes edit form in the right area.
</p>
<p style="padding-bottom:10px;padding-left:10px;">
To add an child node, right-click on an node, select "Create" menu item. Then type in a name of the new child node.
</p>
<p style="padding-bottom:10px;padding-left:10px;">
To delete an node, right-click on the node, select "Delete" menu item. Then click "OK" in the delete confirmation popup.
</p>
<p style="padding-bottom:10px;padding-left:10px;">
To move a node, drag the node and drop it to a new position in the same sub tree. 
</p>

</body>
</html>
