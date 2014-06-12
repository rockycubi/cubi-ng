<?php /* Smarty version 2.6.10, created on 2014-06-10 13:55:12
         compiled from C:%5Cxampp%5Chtdocs%5Ccubing%5Ccubi%5Cmodules/user/template/login.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 't', 'C:\\xampp\\htdocs\\cubing\\cubi\\modules/user/template/login.tpl.html', 99, false),)), $this); ?>
<form id="<?php echo $this->_tpl_vars['form']['name']; ?>
" name="<?php echo $this->_tpl_vars['form']['name']; ?>
" ng-controller="LoginFormController" ng-init="init('<?php echo $this->_tpl_vars['form']['name']; ?>
','<?php echo $this->_tpl_vars['form']['dataService']; ?>
')">
<!-- 	
<div class="cubi_logo_large" style="background-image:url(<?php echo $this->_tpl_vars['app_url']; ?>
/images/cubi_logo_large.png?rnd=<?php echo rand(); ?>)"></div>
 -->
 <table>
 	<tr>
 	<td>
 	
		<div class="cubi_logo_large" ></div>	
	</td>
	<td class="v_line">
	<div class="login_form " >
		<table><tr><td class="login-top-padding" valign="top">
		<div class="cubi_login_banner">
		<h1 id="welcome-message">
			<?php echo @DEFAULT_SYSTEM_NAME;  echo $this->_tpl_vars['use_login_welcome_message']; ?>

		</h1>
		</div>

		
		<?php if ($this->_tpl_vars['dataPanel']['username']['label']): ?>
		<p class="input_row" ng-class="<?php echo '{'; ?>
 'has-error' : errors.username <?php echo '}'; ?>
">
			<label style="width:60px;"><?php echo $this->_tpl_vars['dataPanel']['username']['label']; ?>
</label>
			<span style="width:200px; display:block;float:left;"><?php echo $this->_tpl_vars['dataPanel']['username']['element']; ?>
</span>
			<span class="input_error_msg" ng-show="errors.username"><?php echo '{{'; ?>
 errors.username <?php echo '}}'; ?>
</span>		
		</p>
		
		<p class="input_row" ng-class="<?php echo '{'; ?>
 'has-error' : errors.password <?php echo '}'; ?>
">
			<label style="width:60px;"><?php echo $this->_tpl_vars['dataPanel']['password']['label']; ?>
</label>
			<span style="width:200px; display:block;float:left;"><?php echo $this->_tpl_vars['dataPanel']['password']['element']; ?>
</span>
			<span class="input_error_msg" ng-show="errors.password"><?php echo '{{'; ?>
 errors.password <?php echo '}}'; ?>
</span>		
		</p>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['dataPanel']['antispam']['label']): ?>
		<p class="input_row">
			<label style="width:60px;"><?php echo $this->_tpl_vars['dataPanel']['antispam']['label']; ?>
</label>
			<span style="width:200px; display:block;float:left;"><?php echo $this->_tpl_vars['dataPanel']['antispam']['element']; ?>
</span>
			<?php if ($this->_tpl_vars['errors']['antispam']): ?>
			<span class="input_error_msg"><?php echo $this->_tpl_vars['errors']['antispam']; ?>
</span>
			<?php endif; ?>		
		</p>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['dataPanel']['smartcard']['label']): ?>
		<div class="input_row" style="height:140px;">
			<div style="height:30px;">
			<label style="width:120px;"><?php echo $this->_tpl_vars['dataPanel']['smartcard']['label']; ?>
</label>
			</div>
			<div style="height:120px;">
			<span style="width:400px; display:block;float:left;"><?php echo $this->_tpl_vars['dataPanel']['smartcard']['element']; ?>
</span>
			</div>			
		</div>
		<?php endif; ?>		
		
		<?php if ($this->_tpl_vars['dataPanel']['session_timeout']['label']): ?>
		<div class="input_row"  style="width:400px;display:block;height:35px;overflow:hidden;">
			<label style="width:60px;"><?php echo $this->_tpl_vars['dataPanel']['session_timeout']['label']; ?>
</label>
			<span style="width:200px; display:block;height:35px;float:left;"><?php echo $this->_tpl_vars['dataPanel']['session_timeout']['element']; ?>
</span>
			<?php if ($this->_tpl_vars['errors']['session_timeout']): ?>
			<span class="input_error_msg"><?php echo $this->_tpl_vars['errors']['session_timeout']; ?>
</span>
			<?php endif; ?>		
		</div>	
		<?php endif; ?>	
		<?php if ($this->_tpl_vars['dataPanel']['current_language']['label']): ?>
		<div class="input_row" style="width:400px;display:block;height:35px;overflow:hidden;" >
			<label style="width:60px;"><?php echo $this->_tpl_vars['dataPanel']['current_language']['label']; ?>
</label>
			<span style="width:200px; display:block;height:35px;float:left;"><?php echo $this->_tpl_vars['dataPanel']['current_language']['element']; ?>
</span>
			<?php if ($this->_tpl_vars['errors']['current_language']): ?>
			<span class="input_error_msg"><?php echo $this->_tpl_vars['errors']['current_language']; ?>
</span>
			<?php endif; ?>		
		</div>	
		<?php endif; ?>
		<?php if ($this->_tpl_vars['dataPanel']['current_theme']['label']): ?>
		<div class="input_row" style="width:400px;display:block;height:35px;overflow:hidden;">
			<label style="width:60px;"><?php echo $this->_tpl_vars['dataPanel']['current_theme']['label']; ?>
</label>
			<span style="width:200px; display:block;height:35px;float:left;"><?php echo $this->_tpl_vars['dataPanel']['current_theme']['element']; ?>
</span>
			<?php if ($this->_tpl_vars['errors']['current_theme']): ?>
			<span class="input_error_msg"><?php echo $this->_tpl_vars['errors']['current_theme']; ?>
</span>
			<?php endif; ?>		
		</div>
		<?php endif; ?>
						
		<p class="input_row" style="height:38px;padding-top:5px;padding-left:0px;clear:both" >
			<!-- onclick="document.getElementById('login_indicator').className='show'"  -->
			<span style="height:30px;display:block;">
			<?php $_from = $this->_tpl_vars['actionPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['elem']):
?>
			<?php echo $this->_tpl_vars['elem']['element']; ?>

			<?php endforeach; endif; unset($_from); ?>
			</span>
		</p>
			<!-- 
			<?php if ($this->_tpl_vars['errors']['login_status']): ?>
				<span id="login_indicator" class="show" style="float:none;text-indent:0px;">
				<?php echo $this->_tpl_vars['errors']['login_status']; ?>

				</span>
			<?php else: ?>
				<span id="login_indicator" class="hidden" style="float:none;text-indent:0px;">
				<?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Processing login...<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?>
				</span>
			<?php endif; ?>
			 -->	

		<!--<div id='errorsDiv' class='innerError errorBox' ng-show="errors.login_status" style="width:235px;margin-bottom:10px">
			<?php echo '{{'; ?>
 errors.login_status <?php echo '}}'; ?>

		</div>-->
		
		<p class="input_row">
	    <span style="margin-right:10px;"> <?php echo $this->_tpl_vars['dataPanel']['register_new']['element']; ?>
  </span> <?php echo $this->_tpl_vars['dataPanel']['forget_pass']['element']; ?>

		</p>

		</td></tr></table>
		</div>
		
	</td></tr></table>


</form>

<script>
<?php echo '
function LoginFormController($scope, $http, $window, $timeout) {
	$scope.dataobj = {};
	
	$scope.init = function(name, dataService) {
		$scope.name = name;
		$scope.dataService = dataService;
	}
	
	$scope.login = function() {
		console.log($scope.dataobj);
		var url = $scope.dataService+\'/?method=login&format=json\';
		var postQueryString = "argsJson="+angular.toJson($scope.dataobj);
		$http.defaults.headers.post[\'Content-Type\'] = \'application/x-www-form-urlencoded; charset=UTF-8\';
		$http.post(url, postQueryString).success(function(responseObj) {
			if (responseObj.data.redirect) {
				// redirect to new page
				$scope.redirect = responseObj.data.redirect;
				$window.location.href = $scope.redirect;
			}
			else if (responseObj.data.errors) {
				// display error
				$scope.errors = responseObj.data.errors;
				//$timeout(function(){$scope.errors = null;}, 3000);
				//alert("error: "+responseObj.data.error);
			}
		});
	}
}
'; ?>

</script>