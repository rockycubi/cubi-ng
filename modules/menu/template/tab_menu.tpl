<div ng-controller="LeftMenuController" ng-init="init('{$form.name}','{$form.dataService}','{$form.queryString}')">
	<ul>
		<li ng-repeat="node in treeNodes">
			<a ng-class="node.m_Current ? 'current':''" href="{literal}{{{/literal}node.m_URL{literal}}}{/literal}">
			{literal}{{{/literal}node.m_Name{literal}}}{/literal}</a>
		</li>
	</ul>
</div>