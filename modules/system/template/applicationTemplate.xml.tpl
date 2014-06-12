<?xml version="1.0" standalone="no"?>
<Application>   
{foreach item=item key=itemName from=$data.APPLICATION }{if $itemName != 'ATTRIBUTES'}
	<DataSource>
{if $item.ATTRIBUTES.NAME != ""}
		<Database Name="{$item.ATTRIBUTES.NAME}" Driver="{$item.ATTRIBUTES.DRIVER}" Server="{$item.ATTRIBUTES.SERVER}" Port="{$item.ATTRIBUTES.PORT}" DBName="{$item.ATTRIBUTES.DBNAME}" User="{$item.ATTRIBUTES.USER}" Password="{$item.ATTRIBUTES.PASSWORD}" Charset="{$item.ATTRIBUTES.CHARSET}" Status="{$item.ATTRIBUTES.STATUS}" />
{else}
{foreach item=db key=ruleName from=$item.DATABASE }
		<Database Name="{$db.ATTRIBUTES.NAME}" Driver="{$db.ATTRIBUTES.DRIVER}" Server="{$db.ATTRIBUTES.SERVER}" Port="{$db.ATTRIBUTES.PORT}" DBName="{$db.ATTRIBUTES.DBNAME}" User="{$db.ATTRIBUTES.USER}" Password="{$db.ATTRIBUTES.PASSWORD}" Charset="{$db.ATTRIBUTES.CHARSET}" Status="{$db.ATTRIBUTES.STATUS}" />
{/foreach}
{/if}
	</DataSource>
{/if}{/foreach}
</Application>