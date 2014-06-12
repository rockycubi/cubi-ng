// JavaScript Document
function show_submenu(obj){
	var menu_links = $$('.toplevel li a');
	for(i=0;i<menu_links.length;i++) {	
		if(menu_links[i].className=='current'){
			menu_links[i].className='';			
		}
	}
	var submenus = $$('.secondlevel');
	for(i=0;i<submenus.length;i++) {	
	      if(submenus[i].style.display=='block'){
			  submenus[i].hide(); 
		  }
	}
	obj.className='current';
	var current_submenu ;
	current_submenu = obj.nextSiblings();	
	current_submenu[0].style.display="block";
	current_submenu[0].fade({duration: 0.5,from: 0.5, to: 1}); 
}

function switch_help_content(){
	var c = new Cookies();
	if($('switch_help_content_btn').className=='btn_min'){
		control_help_content("hide");
		c.set('help_form','hidden');
	}else{
		control_help_content("show");
		c.set('help_form','shown');
	}
}

function init_help_content(){
	var c = new Cookies();	
	status = c.get('help_form');
	switch(status){		
		case 'shown':
			control_help_content("show");
			break;
		case 'hidden':
		default:
			control_help_content("hide");
			break;
	
	}
}

function control_help_content(action){
	switch(action){
		case "hide":
			$('help_content').style.display="none";
			$('switch_help_content_btn').className='btn_max';			
			break;
		case "show":
			default:
			$('help_content').style.display="block";
			$('help_content').fade({duration: 0.5,from: 0.5, to: 1}); 
			$('switch_help_content_btn').className='btn_min';			
			setTimeout("$('help_content').show();",600);
			break;
	}
}

function top_menu_move(){
	var c = new Cookies();
	top_menu_first=parseInt(top_menu_first);
	switch(top_menu_move_direction){
		case 'left':
			if(top_menu_first>0){
				top_menu_first=top_menu_first-1;
			}			
			break;
		case 'right':
			if(top_menu_first<(top_menu_count-5)){
				top_menu_first=top_menu_first+1;
			}			
			break;			
	}
	pos=0-top_menu_first*top_menu_item_width ;
	c.set('top_menu_first',top_menu_first);
	new Effect.Move($('top_menu_list'), { x: pos, y: 0, mode: 'absolute' });
}

function init_top_menu_pos(){
	var c = new Cookies();
	if(c.get('top_menu_first')){
		top_menu_first=c.get('top_menu_first');		
	}
	$('menu').style.position='relative';
	$('top_menu_list').style.position='relative';
	pos = (0-top_menu_first*top_menu_item_width);
	$('top_menu_list').style.left=pos+'px';
	try{
		$('top_menu_list').style.paddingLeft=pos+'px';
	}catch(e){}
	new Effect.Move($('top_menu_list'), { x: pos, y: 0, mode: 'absolute' });
	top_menu_play_daemon();
}

function top_menu_play_daemon(){
	if(top_menu_play==true){
		top_menu_move();
	}
	setTimeout("top_menu_play_daemon()",top_menu_play_speed);
}

window.onload=fade_loader;

function fade_loader(){
	window.setTimeout("$('main_loader').fade( {from: 0.7, to: 0});",800);
	window.setTimeout("$('main_loader_bg').fade( {from: 0.7, to: 0});",800);	
	window.setTimeout("$('main_loader').hide();",1000);
	window.setTimeout("$('main_loader_bg').hide();",1000);	
	window.setTimeout("$('main_loader_bg').style.height='0px';",1500);	
}

function show_loader(){	
	$('main_loader').show();
	$('main_loader_bg').show();
	$('main_loader_bg').style.height='1000px';
	$('main_loader_bg').style.display='block';	
}

function toggleDisplay(id){
	if($(id).style.display=='none'){
		$(id).show();
		$(id+"_toggler").className='shrink';
	}else{
		$(id).hide();
		$(id+"_toggler").className='expand';		
	}	
}


function init_elementset(formName,id)
{
	var c = new Cookies();	
	status = c.get(formName+"_"+id);
	switch(status){		
		default:
		case 'shown':
			control_elementset(formName,id,'show');
			break;
		case 'hidden':		
			control_elementset(formName,id,'hide');
			break;
	
	}	
}

function switch_elementset(formName,id)
{
	var c = new Cookies();
	if($('element_set_btn_'+id).className=='shrink'){
		control_elementset(formName,id,"hide");
		c.set(formName+"_"+id,'hidden');
	}else{
		control_elementset(formName,id,"show");
		c.set(formName+"_"+id,'shown');
	}	
}

function control_elementset(formName,id,action)
{
	switch(action){
	case "hide":		
		$('element_set_panel_'+id).style.position="absolute";
		$('element_set_panel_'+id).style.left="-10000px";		
		$('element_set_btn_'+id).className='expand';			
		break;
	case "show":
		default:		
		if($('element_set_panel_'+id).style.position=="absolute"){
			$('element_set_panel_'+id).style.position="static";
			$('element_set_panel_'+id).style.left="";
			$('element_set_panel_'+id).fade({duration: 0.5,from: 0.5, to: 1}); 
			$('element_set_btn_'+id).className='shrink';
		}
		break;
	}	
}
