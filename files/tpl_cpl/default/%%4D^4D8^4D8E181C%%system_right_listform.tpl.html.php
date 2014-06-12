<?php /* Smarty version 2.6.10, created on 2014-06-04 14:45:18
         compiled from C:%5Cxampp%5Chtdocs%5Ccubing%5Ccubi%5Cthemes/default/template/system_right_listform.tpl.html */ ?>
<form id='<?php echo $this->_tpl_vars['form']['name']; ?>
' name='<?php echo $this->_tpl_vars['form']['name']; ?>
' ng-controller="TableFormController" ng-init="init('<?php echo $this->_tpl_vars['form']['name']; ?>
','<?php echo $this->_tpl_vars['form']['dataService']; ?>
','<?php echo $this->_tpl_vars['form']['queryString']; ?>
')">
<div style="padding-left:25px;padding-right:20px;">
	<div>
	<table><tr><td>
		<?php if ($this->_tpl_vars['form']['icon'] != ''): ?>
		<div class="form_icon"><img  src="<?php echo $this->_tpl_vars['form']['icon']; ?>
" border="0" /></div>
		<?php endif; ?>
		<div style="float:left; width:600px;">
		<h2>
		<?php echo $this->_tpl_vars['form']['title']; ?>

		</h2> 
		<p class="form_desc"><?php echo $this->_tpl_vars['form']['description']; ?>
</p>
		</div>
	</td></tr></table>
	</div>
<?php if ($this->_tpl_vars['actionPanel'] || $this->_tpl_vars['searchPanel']): ?>	
	<div class="form_header_panel">	
		<div class="action_panel" >
			<?php $_from = $this->_tpl_vars['searchPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['elem']):
?>
				<?php if ($this->_tpl_vars['elem']['type'] == 'InputDateRangePicker'): ?> <?php echo $this->_tpl_vars['elem']['element'];  endif; ?> 
			<?php endforeach; endif; unset($_from); ?>
		
			<?php $_from = $this->_tpl_vars['actionPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['elem']):
?>
			    	<?php echo $this->_tpl_vars['elem']['element']; ?>

			<?php endforeach; endif; unset($_from); ?>
		</div>
		<div class="search_panel" >		
			<?php $_from = $this->_tpl_vars['searchPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['elem']):
?>
				<?php if ($this->_tpl_vars['elem']['type'] != 'InputDateRangePicker'): ?>
					<?php if ($this->_tpl_vars['elem']['label']): ?><span style="float:left;padding-right:5px;line-height:20px"><?php echo $this->_tpl_vars['elem']['label']; ?>
</span> <?php endif; ?> <?php echo $this->_tpl_vars['elem']['element']; ?>

				<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
		</div>
	</div>	
<?php endif; ?>	

<div class="from_table_container">
<!-- table start -->
<table border="0" cellpadding="0" cellspacing="0" class="form_table" id="<?php echo $this->_tpl_vars['form']['name']; ?>
_data_table">
	<thead>		
     <?php $_from = $this->_tpl_vars['dataPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['elems_name'] => $this->_tpl_vars['cell']):
?>	
     	<?php if ($this->_tpl_vars['cell']['type'] == 'ColumnStyle'): ?>
     		<?php $this->assign('row_style_name', $this->_tpl_vars['elems_name']); ?>     	
		<?php else: ?>
			<?php if ($this->_tpl_vars['cell']['type'] == 'RowCheckbox'): ?>
				<?php $this->assign('th_style', "text-align:left;padding-left:10px;"); ?>
			<?php else: ?>
				<?php $this->assign('th_style', ""); ?>
			<?php endif; ?>
         <th onmouseover="this.className='hover'" onmouseout="this.className=''" nowrap="nowrap" style="<?php echo $this->_tpl_vars['th_style']; ?>
">
		 <?php if ($this->_tpl_vars['cell']['sortable'] == 'Y'): ?><a href='' ng-click="sortRecord('<?php echo $this->_tpl_vars['cell']['field']; ?>
')"><?php echo $this->_tpl_vars['cell']['label']; ?>
</a>
		 <?php else:  echo $this->_tpl_vars['cell']['label']; ?>

		 <?php endif; ?>
		 </th>	 
		<?php endif; ?>
     <?php endforeach; endif; unset($_from); ?>
	</thead>
	<tbody>
		<tr ng-repeat="dataobj in dataset" ng-click="selectRow($index)" ng-class-odd="'odd'" ng-class-even="'even'" ng-class="dataobj.selected==1?'selected':highlightclass" ng-mouseenter="highlightclass='hover'" ng-mouseleave="highlightclass=''">
		<?php $_from = $this->_tpl_vars['dataPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['elems_name'] => $this->_tpl_vars['cell']):
?>
			<td><?php echo $this->_tpl_vars['cell']['element']; ?>
</td>
		<?php endforeach; endif; unset($_from); ?>
		</tr>
	</tbody>
</table>
</div>
<!-- table end -->	

	<div class="form_footer_panel">
		<div class="navi_panel">
<?php if ($this->_tpl_vars['navPanel']): ?>
   <?php $_from = $this->_tpl_vars['navPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['elem']):
?>
   		<?php if ($this->_tpl_vars['elem']['label']): ?> <label style="width:68px;"><?php echo $this->_tpl_vars['elem']['label']; ?>
</label><?php endif; ?>
    	<?php echo $this->_tpl_vars['elem']['element']; ?>

   <?php endforeach; endif; unset($_from);  endif; ?>
		</div>		
	</div>
	
</div>
</form>