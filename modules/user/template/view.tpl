<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>{$description}</title>
  {$style_sheets}
  {$scripts}
</head>
<body >

{php} 
$this->assign('header', 'header.tpl'); 
$this->assign('footer', 'footer.tpl'); 
{/php}

<!-- header -->
{include file=$header}

<!-- main -->


{foreach item=form from=$forms}
   <div>{$form}</div>
{/foreach}


<!-- footer -->
{include file=$footer}

</body>
</html>
