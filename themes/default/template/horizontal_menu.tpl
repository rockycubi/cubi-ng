<ul class="toplevel {$widget.css} top_menu" id="nav">
	{foreach item=item from=$widget.menu}
	<li>
	    {assign var='current' value='0'}
	    {foreach item=bc from=$widget.breadcrumb}
			{if $item->m_Id == $bc->m_Id}
	    		{assign var='current' value='1'}
			{/if}
	    {/foreach}
		{if $current == 1 }
			{assign var=menu_class value="current" }	
		{else}
			{assign var=menu_class value="" }
	    {/if}	
		<a class="{$menu_class}" href="{if $item->m_URL and false}{$item->m_URL}{else}#{/if}">
			<img class="{$item->m_IconCSSClass}" src="{$image_url}/{if $item->m_IconImage!=''}{$item->m_IconImage}{else}spacer.gif{/if}" />{$item->m_Name}
		</a>	
		{if $item->m_ChildNodes|@count > 0}
		<ul class="secondlevel module" {if $menu_class eq 'current'}style="display:block;"{/if}>
		{foreach item=subitem from=$item->m_ChildNodes}
    		{assign var='current' value='0'}
    	    {foreach item=bc from=$widget.breadcrumb}
    			{if $subitem->m_Id == $bc->m_Id}
    	    		{assign var='current' value='1'}
    			{/if}
    	    {/foreach}
			{if $current == 1 }
				{assign var=submenu_class value="current" }	
			{else}
				{assign var=submenu_class value="" }
		    {/if}					
				<li><a class="{$submenu_class}" href="{$subitem->m_URL}">{$subitem->m_Name}</a></li>						
		{/foreach}	
		</ul>
		{/if}
	</li>
	{/foreach}	
</ul>
<div class="v_spacer"></div>