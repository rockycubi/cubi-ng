<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>{$description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  <link rel="stylesheet" href="../css/ticket.css" type="text/css">
  {$style_sheets}
  {$scripts}
</head>
<body bgcolor="#EDEDED">

<!-- main -->
<div style="margin: 5">
<table width=100% border=0 cellspacing=10 cellpadding=0>
{foreach item=form from=$forms}
   <tr><td>{$form}</td></tr>
{/foreach}
</table>
</div>

</body>
</html>
