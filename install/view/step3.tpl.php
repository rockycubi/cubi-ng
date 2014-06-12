<div class="container">

   
	<div class="step_3">
		<div style="padding-left:260px;padding-top:35px;width:600px;">
		
		<h2 style="padding-bottom: 10px;"><?php echo STR_APPLICATION_CONFIGURATION;?></h2>
		<h4><?php echo STR_CHECK_WRITABLE_DIR;?></h4>
		<table class="form_table"  cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;width:600px;">
		<tr>
			<th><?php echo STR_ITEM;?></th>
			<th><?php echo STR_VALUE;?></th>
			<th><?php echo STR_STATUS;?></th>
		</tr>
		<?php
		$status = getApplicationStatus();
		$i=0;
		$hasError = false;
		foreach ($status as $s) {
		    if(fmod($i,2)){
		        $default_style="even";
		    }else{
		        $default_style="odd";
		    }
		    
		    if (strpos($s['status'],'OK') === 0) {
		        $flag_icon="flag_y.gif";        
		    }else{
		        $flag_icon="flag_n.gif";
				$hasError = true;
		    }
		     $i++;
		?>
		        <tr
		            class="<?php echo $default_style;?>"
		            onmouseover="if(this.className!='selected')this.className='hover'" 
		            onmouseout="if(this.className!='selected')this.className='<?php echo $default_style;?>'" 
		        >
		            <td><?php echo $s['item'];?></td>
		            <td><?php echo $s['value'];?></td>
		            <td><img src="../themes/default/images/<?php echo $flag_icon;?>" /></td>
		        </tr>
		<?php
		}
		?>
		</table>
		
		<h4 ><?php echo STR_DEFAULT_DATABASE_FILE;?></h4>
		<?php $db = getDefaultDB(); ?>
		<table class="form_table"  cellpadding="0" cellspacing="0" border="0" style="width:600px;">
		<tr>
			<th><?php echo STR_NAME;?></th>
			<th><?php echo STR_DRIVER;?></th>
			<th><?php echo STR_SERVER;?></th>
			<th><?php echo STR_PORT;?></th>
			<th><?php echo STR_DBNAME;?></th>
			<th><?php echo STR_USER;?></th>
			<th><?php echo STR_PASSWORD;?></th>
		</tr>
		<tr
			class="even"
		    onmouseover="if(this.className!='selected')this.className='hover'" 
		    onmouseout="if(this.className!='selected')this.className='even'" 
		>
		    <td><?php echo $db['Name'];?></td>
		    <td><?php echo $db['Driver'];?></td>
		    <td><?php echo $db['Server'];?></td>
		    <td><?php echo $db['Port'];?></td>
		    <td><?php echo $db['DBName'];?></td>
		    <td><?php echo $db['User'];?></td>
		    <td><?php echo $db['Password'];?></td>
		</tr>
		</table>
		<div style="display:none;">
		<a href="javascript:load_modules();" class="button_m_highlight">Load Modules</a>
		<a href="javascript:showContent('loadmodules_results','load modules');" class="button_m">Show results</a>
		</div>
		<div>
		<div id="loadmodules_img"  style="display:none;color:#00344d;padding-top:10px;height:30px;" >
			<img src="images/ajax-loader.gif" style="display:block;float:left;"/> <span style="display:block;float:left;line-height:22px;padding-left:16px;font-style:italic;">Loading modules, it will takes 1-2 mintues, please wait...</span>
		</div>
		<span id="loadmodules_status"></span>
		</div>
		<div id="loadmodules_results" style="display:none;" onclick="showContent('loadmodules_results','load modules');">
		</div>
		
		<div style="padding-top:10px">
			<a href="index.php?step=2" class="btn_back"><?php echo STR_BACK;?></a>
			<?php 
			if (!$hasError){
			?>
			    <a href="javascript:load_modules();" class="btn_next"><?php echo STR_NEXT;?></a>
			<?php
			}else{
			?>
			    <a href="index.php?step=3" class="btn_next"><?php echo STR_RETRY;?></a>
			<?php
			}
			?>
		</div>
		</div>
		<div id="error_message" class="popup_dialog" onclick="this.style.display='none';"></div>
	</div>
</div>
		
<script>
function load_modules()
{
    $('loadmodules_results').innerHTML='';
    $('loadmodules_status').innerHTML='';
    new Ajax.Request('index.php?action=load_modules', {
      onLoading: function() {
         Element.show('loadmodules_img');
      },
      onComplete: function() {
          //alert('komplete');
         Element.hide('loadmodules_img');
      },
      onSuccess: function(transport){
         var response = transport.responseText || "no response text";
         //alert('sukses'+response);
         $('loadmodules_results').innerHTML = response;
         if (response.indexOf('###')>=0) {
            $('loadmodules_status').innerHTML = response.substr(0,response.indexOf('###'));
            window.location = "index.php?step=4";
         }else{
        	 showContent('loadmodules_results','load modules');
         }
      },
      onFailure: function(){ alert('Something went wrong...') }
      //parameters: $('setupform').serialize()
   })
}

function showContent(div, title, w, h)
{
    var top;
    w = w ? w : 600; h = h ? h : 500;
    left = (screen.width) ? (screen.width-w)/2 : 0; top = (screen.height) ? (screen.height-h)/2 : 0;
    popup = window.open("","",'height='+h+',width='+w+',left='+left+',top='+top+',scrollbars=1,resizable=1,statu=0');
    text = $(div).innerHTML;
    body = "<body bgcolor=#D9D9D9><pre>"+text+"</pre></body>";
    popup.document.writeln("<head><title>"+title+"</title>"+body+"</head>");
}
</script>

