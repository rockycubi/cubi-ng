<ul data-role="listview" data-inset="true">	
	{foreach item=item from=$widget.menu}
		{assign var='current' value='0'}
		{foreach item=bc from=$widget.breadcrumb}
			{if $item->m_Id == $bc->m_Id}
	    		{assign var='current' value='1'}
			{/if}
	    {/foreach}
	    {if $current==1}
	    	<li><a class="current"  href="#app_menus_page" >{$item->m_Name}</a></li>
	    {else}
	    	<li><a href="{$item->m_URL}" >{$item->m_Name}</a></li>
	    {/if}
	{/foreach}
</ul>