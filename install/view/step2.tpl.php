<?php 
if(!$_REQUEST['dbtype']){
	loadDBConfig();
}
?>
<div class="container">

	<div class="step_2">
	<div  style="padding-left:260px;padding-top:5px;width:600px;">
		<h2><?php echo STR_DATABASE_CONFIGURATION;?></h2>
        <p>
        <?php echo STR_DATABASE_CONFIGURATION_DESC_1;?>
        </p>
        <p>
        <?php echo STR_DATABASE_CONFIGURATION_DESC_2;?>                
        </p>
			<form id="setupform" name="setupform" method="post" action="install.php" style="margin-bottom:15px;">
			<table class="input_table">
			<tr>
				<td width="150"><label><?php echo STR_DATABASE_TYPE;?></label></td>
				<td>
			    <SELECT NAME="dbtype">
			    <OPTION VALUE="Pdo_Mysql"<?php if($_REQUEST['dbtype']=="Pdo_Mysql") echo " selected='selected'";?>>MySQL
			    <OPTION VALUE="Pdo_Pgsql"<?php if($_REQUEST['dbtype']=="Pdo_Pgsql") echo " selected='selected'";?>>PostgreSQL
			    <OPTION VALUE="Pdo_OCi"<?php if($_REQUEST['dbtype']=="Pdo_OCi") echo " selected='selected'";?>>Oracle 
			    <OPTION VALUE="Pdo_Mssql"<?php if($_REQUEST['dbtype']=="Pdo_Mssql") echo " selected='selected'";?>>SQL Server
			    </SELECT>
			    </td>
			</tr>
			
			<tr class="odd">
				<td>
			    	<label><?php echo STR_DATABASE_HOSTNAME;?></label>
			    </td>
				<td>
			 
			    <input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'"
			    	 type="text" name="dbHostName" value="<?php echo  isset($_REQUEST['dbHostName']) ? $_REQUEST['dbHostName'] : '127.0.0.1'?>" tabindex="1" >
			    </td>
			</tr>
			<tr>
				<td><label><?php echo STR_DATABASE_PORT;?></label></td>
				<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'" 
			    		type="text" name="dbHostPort" value="<?php echo  isset($_REQUEST['dbHostPort']) ? $_REQUEST['dbHostPort'] : '3306'?>" tabindex="3"></td>
			</tr>
			<tr class="odd">
				<td><label><?php echo STR_DATABASE_NAME;?></label></td>
				<td><input id="input_dbname" class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'" 
			    		type="text" name="dbName" value="<?php echo  isset($_REQUEST['dbName']) ? $_REQUEST['dbName'] : 'cubi'?>" tabindex="3"></td>
			</tr>
			<tr>
				<td ><label><?php echo STR_DATABASE_USERNAME;?></label></td>
				<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'"
			    		 type="text" name="dbUserName" value="<?php echo  isset($_REQUEST['dbUserName']) ? $_REQUEST['dbUserName'] : 'root'?>" tabindex="4"> <span class="input_desc">&nbsp;</span></td>
			</tr>
			<tr class="odd">
				<td ><label><?php echo STR_DATABASE_PASSWORD;?></label></td>
				<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'"
			    		type="password" name="dbPassword" value="<?php echo  isset($_REQUEST['dbPassword']) ? $_REQUEST['dbPassword'] : ''?>" tabindex="5" > <span class="input_desc">&nbsp;</span></td>
			</tr>
			
			<tr>
			<td>
				<label><?php echo STR_DATABASE_CREATE;?></label>
			</td>
			<td>
				<input  type="checkbox" <?php if($_REQUEST['create_db']!='N'){ echo "checked=\"checked\""; }?>  name="create_db" id="create_db" tabindex="6" />
			
			</td>	
			</tr>
			<tr>
			<td colspan="2">
				<img id="createdb_img" src="images/indicator.white.gif" alt="Create DB indicator." style="display:none"/>
			    <span id="create_db_result" style="color:#ff0000;"></span>
			</td>
			</tr>
			</table>
			
			</form>

    <a href="index.php?step=1" class="btn_back"><?php echo STR_BACK;?></a>
    <a href="javascript:step2_next()" class="btn_next"><?php echo STR_NEXT;?></a>
    </div>
	
	<div id="notice_message" class="popup_dialog" onclick="this.style.display='none';">
	        <img id="createdb_img" src="images/indicator.white.gif" style="display:none"/>
	        <span id="create_db_result"></span>
	        <img id="filldb_img" src="images/indicator.white.gif" style="display:none"/>
	        <span id="fill_db_result"></span>
	    </div>
	    <div id="error_message" class="popup_dialog" onclick="this.style.display='none';"></div>
	</div>

</div>



<script>
function step2_next()
{
    if ($('create_db').checked) {
        create_db();
    }
    else {
		replace_db_cfg();
        //window.location = "index.php?step=3";
    }
    /*
    if ($('load_db').checked && !$('create_db').checked) {
        fill_db();
    }
    if (!$('load_db').checked && !$('create_db').checked)
        alert("Please select the above checkboxes to continue.");
    */
}

function replace_db_cfg()
{
    $('create_db_result').innerHTML='';
    new Ajax.Request('index.php?action=replace_db_cfg', {
      onLoading: function() {
         Element.show('createdb_img'); // or $('createdb_img').show();
      },
      onComplete: function() {
          
         Element.hide('createdb_img');
      },
      onSuccess: function(transport){
         var response = transport.responseText || "no response text";
         $('create_db_result').innerHTML=response;
         if (response.indexOf('SUCCESS')>=0) {
            /*if ($('load_db').checked)
                fill_db();*/
            window.location = "index.php?step=3";
         }
      },
      onFailure: function(){ alert('Something went wrong...') },
      parameters: $('setupform').serialize()
   })	
}

function create_db()
{
    $('create_db_result').innerHTML='';
    new Ajax.Request('index.php?action=create_db', {
      onLoading: function() {
         Element.show('createdb_img'); // or $('createdb_img').show();
      },
      onComplete: function() {
          
         Element.hide('createdb_img');
      },
      onSuccess: function(transport){
         var response = transport.responseText || "no response text";
         $('create_db_result').innerHTML=response;
         if (response.indexOf('SUCCESS')>=0) {
            /*if ($('load_db').checked)
                fill_db();*/
            window.location = "index.php?step=3";
         }
      },
      onFailure: function(){ alert('Something went wrong...') },
      parameters: $('setupform').serialize()
   })
}
/*
function fill_db()
{
    $('fill_db_result').innerHTML='';
    new Ajax.Request('index.php?action=fill_db', {
      onLoading: function() {
         Element.show('filldb_img');
      },
      onComplete: function() {
          //alert('komplete');
         Element.hide('filldb_img');
      },
      onSuccess: function(transport){
         var response = transport.responseText || "no response text";
         //alert('sukses'+response);
         $('fill_db_result').innerHTML = response;
         if (response.indexOf('SUCCESS')>=0) {
            window.location = "index.php?step=3";
         }
      },
      onFailure: function(){ alert('Something went wrong...') },
      parameters: $('setupform').serialize()
   })
}*/
</script>