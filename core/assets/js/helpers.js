// JavaScript Document for custom functions

// create main inline tabs	
var mgm_primary_tab_urls = [];
var mgm_secondary_tabs;
var mgm_primary_tabs;

// hash to query
mgm_hash_to_queryvar=function(qVar,hash){
	var hash    = hash || window.location.hash.replace("#","");	
	var qVarVal = ""; 
	if(hash){
		hash_vars=hash.toString().split("/");	
		for(var i=0;i<hash_vars.length;i++){
			if(hash_vars[i].toString().is_empty()==false){
				if(hash_vars[i]==qVar){
					qVarVal=hash_vars[i+1];
				}
				i++;
			}
		}
	}
	return qVarVal;
}

// tab index
mgm_tab_index=function(type){
	var _index = 0;	
	var qtabs  = jQuery.query.get('tabs');	
	var tabs   = qtabs.split(',');	
	switch(type){	
		case 'main':			
			jQuery('#mgm-panel-content ul li a[href]').each(function(i){
				if(jQuery(this).attr('href').toString().replace('#','')==tabs[0]){
					_index=i;
					return false;
				}
			});	
		break;
		case 'sub':			
			if(tabs[1]){					
				jQuery('#'+tabs[0]+' .content-div ul li a[href]').each(function(i){		
					if(jQuery(this).attr('href').toString().replace('#','')==tabs[1]){
						_index=i;
						return false;
					}
				});				
			}
		break;	
	}
	
	return _index;
}

// date oicker
mgm_date_picker=function(filter,image,_options){
	// image not 
	var image = image || false;
	var _defaults = {changeYear: true, changeMonth: true, yearRange: '-50:+10', showOn:'focus'};
	// date
	if(image){
		_options = jQuery.extend(_options, {showOn:'button',buttonImage:image+'/images/icons/calendar.png',buttonImageOnly:true});
	}		
	// extend
	_options = jQuery.extend(_defaults,_options);	
	// trigger
	jQuery(filter).each(function(i){jQuery(this).attr('size','11').attr('maxlength','10').datepicker(_options);});
	// wrap scope, not so elegant solution, but works at least
	jQuery("#ui-datepicker-div").wrap("<div class='mgm'></div>");		
}

// attach tips	
mgm_attach_tips=function(){
	jQuery(".box-description, .box-video").click(function(){	
		var contentclass= jQuery(this).attr('class')+'-content';	
				
		var descheading = jQuery(this).parent().prev("h3").html();	            
		
		var desctext = jQuery(this).parent().parent().children("."+contentclass).html();					
		
		switch(jQuery(this).attr('class')){
			case "box-description":
				var id='mgm-custom-lbox';
				jQuery('body').append("<div id='mgm-custom-lbox'><div class='shadow'></div><div class='box-desc'><div class='box-desc-top'></div><div class='box-desc-content'><h3>"+descheading+"</h3>"+desctext+"<div class='lightboxclose'></div> </div> <div class='box-desc-bottom'></div></div></div>");
			break;
			case "box-video":
				var id='mgm-custom-lbox2';
				jQuery('body').append("<div id='mgm-custom-lbox2'><div class='shadow'></div><div class='box-desc'><div class='box-desc-top'></div><div class='box-desc-content'><h3>"+descheading+"</h3>"+desctext+"<div class='lightboxclose'></div> </div> <div class='box-desc-bottom'></div></div></div>");
			break;
		}

		jQuery(".shadow").animate({ opacity: "show" }, "fast").fadeTo("fast", 0.75);

		jQuery('.lightboxclose').click(function(){

			jQuery(".shadow").animate({ opacity: "hide" }, "fast", function(){jQuery("#"+id).remove();});	

		});

	});
}	

// load pager page
mgm_load_pager_page=function(selector, url, method, data){
	// method
	var _method = method || 'get';	
	// switch
	switch(_method){
		case 'get':
			var _index = data || 0;
			// set new url							   	
			jQuery(selector + ' .content-div').tabs( 'url', index, url );	   
			// reload
			jQuery(selector + ' .content-div').tabs( 'load', index);
		break;
		case 'post':
			var _data = data || {};
			// load
			jQuery(selector).load(url, _data); 
		break;
	}	
}

// convert pager links to ajax call, get request
mgm_set_pager_anchor2get=function(selector, index){		
	// index
	var _index = index || 0;	
	// convert each link
	jQuery(selector + ' .pager-wrap a[href]').each(function(){
		// get url
		var url = jQuery(this).attr('href');		
		// disable href
		jQuery(this).attr('href','javascript://');
		// bind click
		jQuery(this).bind('click',function(){										   
			// load					
			mgm_load_pager_page(selector, url, 'get', _index);				
			// exit
			return;
		});					
	});
}

// convert pager links to ajax call with post to capture form inputs
mgm_set_pager_anchor2post=function(selector, selector_post){		
	// data
	if(selector_post)
		var _data = jQuery(selector_post + ' :input').serializeArray();
	else
		var _data = {};
		
	// convert each link
	jQuery(selector + ' .pager-wrap a[href]').each(function(){
		// get url
		var url = jQuery(this).attr('href');			
		// disable href
		jQuery(this).attr('href','javascript://');
		// bind click
		jQuery(this).bind('click',function(){	
			// load
			mgm_load_pager_page(selector, url, 'post', _data);	
			// exit
			return;
		});					
	});
}

// convert pager select dropdown to ajax call with post to capture form inputs 
mgm_set_pager_select2post=function(selector, selector_post, url){
	// data
	if(selector_post)
		var _data = jQuery(selector_post + ' :input').serializeArray();
	else
		var _data = {};
		
	jQuery(selector + ' #page_no_s').bind('change', function(){
		mgm_load_pager_page(selector, (url + '&page_no=' + jQuery(this).val()), 'post', _data);
	});
}

// toggle
mgm_toggle= function(id){	
	jQuery('#'+id).toggle();	
}

// toggle image
mgm_toggle_img= function(id){	
	// toggle
	jQuery('#'+id).toggle('slow');	
	// class
	if(jQuery('#'+id+'_trig').hasClass('open')){
		jQuery('#'+id+'_trig').removeClass('open').addClass('close');	
	}else{
		jQuery('#'+id+'_trig').removeClass('close').addClass('open');	
	}
}

// module logo upload
mgm_upload_logo=function(obj, messages){	
	// set default	
	var messages = jQuery.extend(( messages || {} ), {e_unsafe_files: 'Please upload only gif,jpg and png files', e_processing: 'Processing', e_update: 'Update'});		
	// langs	
	if(jQuery(obj).val().toString().is_empty()==false){	
		// check ext	
		if(!(/\.(png|jpe?g|gif)$/i).test(jQuery(obj).val().toString())){
			alert(messages.e_unsafe_files);
			return;
		}						
		// vars				
		var module  = jQuery(obj).attr('name').replace('logo_','');	
		var form_id = jQuery(jQuery(obj).get(0).form).attr('id');		
		// before send, remove old message
		jQuery('#'+form_id+' #message').remove();		
		// create new message
		jQuery('#'+form_id).prepend('<div id="message" class="running"><span>' + messages.e_processing + '...</span></div>');
		// remove old hidden
		jQuery("#"+form_id+" :input[name='logo_new_"+module+"']").remove();						
		// upload 
		jQuery.ajaxFileUpload({
			url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_file_upload&module='+module, 
			secureuri:false,
			fileElementId:jQuery(obj).attr('id'),
			dataType: 'json',						
			success: function (data, status){		
				// show message
				mgm_show_message('#'+form_id, data);					
				// uploaded	
				if(data.status=='success'){		
					// set hidden
					jQuery('#'+form_id).append('<input type="hidden" name="logo_new_'+module+'" value="'+data.logo.image_url+'">');	
					// change logo sample
					jQuery('#'+form_id+' #logo_image_'+module).attr({'src': data.logo.image_url, title: data.logo.image_url, width: '100px', height: '100px'});						
					// box setting will update update button
					if(/^frmmodbox_/.test(form_id)){	
						jQuery("#box_logo_elements_" + module).html('<div style="margin-top:3px"><input type="button" class="button" value="' + messages.e_update + '" onclick="mgm_update_module(\'#' + form_id + '\', \'logo_update\')"></div>');	
					}else{
					// just remove upload file element	
						jQuery("#"+form_id+" :file[name='"+jQuery(obj).attr('name')+"']").remove();
					}
				}											
			}
		});		
		// end
	}				
}		

// uploader
mgm_file_uploader=function(selector, callback){	
	// bind
	jQuery(selector+" :file").bind('change', function(f){												   
		if(callback) callback(this);
	});
}

// message
mgm_show_message=function(selector, data, is_focus){
	// def
	var is_focus = is_focus || false;
	// remove message										   														
	jQuery(selector+' #message').remove();
	// create message
	jQuery(selector).prepend('<div id="message"></div>');	
	// show message
	if(data) jQuery(selector+' #message').addClass(data.status).html(data.message);			
	// scroll 
	if(is_focus) jQuery.scrollTo(selector, 400);
}

// hide
mgm_hide_message=function(selector){	
	// remove message										   														
	jQuery(selector + ' #message').remove();		
}
// form toggle
mgm_paymentform_toggle=function(code){
	jQuery('form').hide();
	jQuery('form#'+code+'_form :input[type=image]').hide(); 
	jQuery('form#'+code+'_form').show();
	jQuery('#'+code+'_form_cc').fadeIn();
	return false;	
}

// submit cc payment
mgm_submit_cc_payment=function(code){	
	// check whether validate function exists
	if(jQuery.isFunction( jQuery.fn.validate)) {
		jQuery('form#'+code+'_form').validate({			  	
			errorClass: "invalid",
			validClass: "valid",						
			errorPlacement: function(error, element) {		
				if(element.is(":input[name='mgm_card_expiry_month']"))
					error.insertAfter(element.next());
				else error.insertAfter(element);
			}			  
		});
		// hide 
		if(jQuery('form#'+code+'_form').valid()){
			// hide submit	
			jQuery('form#'+code+'_form .btnwrap :input').hide();
			// show processing
			jQuery('form#'+code+'_form #mgm_cc_processing').show();
			// return
			return true;
		}
		// return
		return false;
	}			
	// return 
	return true;
}

// cancel
mgm_cancel_cc_payment=function(url){	
	window.location.href = url;	
}

// tab url
mgm_set_tab_url=function(p, s){	
	// set
	var p_idx = p;
	var s_idx = s;			
	// primary onload
	mgm_primary_tabs.bind('tabsload', function(event, ui) {	
	//console.log('primary tab loaded'+p_idx);
		event.stopPropagation();
		// load if
		if(s_idx!=null) {	
			// secondary onload
			mgm_secondary_tabs.bind('tabsload', function(event, ui) {	
			//console.log('secondary tab loaded'+s_idx);
				event.stopPropagation();
				// set null
				if(s_idx!=null) {
					p_idx = s_idx = null;	
				}	
			});
			// load secondary	
			mgm_secondary_tabs.tabs( 'option', 'active', s_idx); 									
		}		
	});
	// select		
	mgm_primary_tabs.tabs( 'option', 'active', p_idx);
}

// mgm_toggle_trial
mgm_toggle_trial=function(elem){
	var pack = jQuery(elem).attr('name').toString().replace('packs[','').replace('][trial_on]','');
	if(jQuery(elem).val() == 1){
		jQuery('.pack_trial_'+pack).fadeIn();	
	}else{
		jQuery('.pack_trial_'+pack).fadeOut();
	}	
}

// html textbox editor
mgm_wysiwyg_editor=function(scope){
	var scope = scope || '.wysiwyg';
	jQuery(scope).wysiwyg({
		controls: {
			strikeThrough : { visible : true },
			underline     : { visible : true },
	
			justifyLeft   : { visible : true },
			justifyCenter : { visible : true },
			justifyRight  : { visible : true },
			justifyFull   : { visible : true },
	
			indent  : { visible : true },
			outdent : { visible : true },
	
			subscript   : { visible : true },
			superscript : { visible : true },
	
			undo : { visible : true },
			redo : { visible : true },
	
			insertOrderedList    : { visible : true },
			insertUnorderedList  : { visible : true },
			insertHorizontalRule : { visible : true },
	
			h4: {
				  visible: true,
				  className: 'h4',
				  command: jQuery.browser.msie ? 'formatBlock' : 'heading',
				  arguments: [jQuery.browser.msie ? '<h4>' : 'h4'],
				  tags: ['h4'],
				  tooltip: 'Header 4'
			},
			h5: {
				  visible: true,
				  className: 'h5',
				  command: jQuery.browser.msie ? 'formatBlock' : 'heading',
				  arguments: [jQuery.browser.msie ? '<h5>' : 'h5'],
				  tags: ['h5'],
				  tooltip: 'Header 5'
			},
			h6: {
				  visible: true,
				  className: 'h6',
				  command: jQuery.browser.msie ? 'formatBlock' : 'heading',
				  arguments: [jQuery.browser.msie ? '<h6>' : 'h6'],
				  tags: ['h6'],
				  tooltip: 'Header 6'
			},
	
			cut   : { visible : true },
			copy  : { visible : true },
			paste : { visible : true },
			html  : { visible: true }
		}
	}); 
}

// ajax loading mask
mgm_ajax_loader=function(){			
	jQuery(document).ajaxStart(function() {
        // wait cursor							
		jQuery('body').css({opacity:'.70',cursor:'wait'});	
    });
	
    jQuery(document).ajaxStop(function() {
		// default cursor							   
        jQuery('body').css({opacity:'',cursor:'default'});	
		// attach tips
		try{ mgm_attach_tips(); }catch(x){}
    });
}

// toggle module endpoint region
mgm_module_endpoints_toggle=function(code){
// custom endpoints
	jQuery('#module_settings_' + code + ' #setting_end_points').bind('click', function(){
		// selected
		if( jQuery(this).attr('checked') ){
			// show
			jQuery('#module_settings_' + code + ' #custom_end_points_region').slideDown('slow');
			// enable
			jQuery('#module_settings_' + code + ' #custom_end_points_region :input').attr('disabled', false);
		}else{
		// de-selected
			// hide
			jQuery('#module_settings_' + code + ' #custom_end_points_region').slideUp('slow');
			// disable
			jQuery('#module_settings_' + code + ' #custom_end_points_region :input').attr('disabled', true);
		}
	});		
}

mgm_tabs_remove=function(selector, index){

	if(mgm_compare(jQuery.ui.version,'1.9.1')) {
		jQuery(selector).tabs('remove',index);
	}else{
		// Remove the tab
		var tab = jQuery( selector ).find( ".ui-tabs-nav li:eq( " + index + " )" ).remove();
		// Find the id of the associated panel
		var panelId = tab.attr( "aria-controls" );
		// Remove the panel
		jQuery( "#" + panelId ).remove();
		// Refresh the tabs widget
		jQuery( selector ).tabs( "refresh" );
	}
}

mgm_tabs_add=function(selector, url, label){

	if(mgm_compare(jQuery.ui.version,'1.9.1')) {
		jQuery(selector).tabs('add',url,label);
	}else{	
		jQuery( "<li><a href='"+ url +"'>"+ label +"</a></li>" ).appendTo( selector + " .ui-tabs-nav" );
		jQuery( selector ).tabs( "refresh" );
	}
}

mgm_compare=function(number1, number2) {
	
	var precision1, precision2, decimal1, decimal2, flag = false;
	
	precision1 = parseInt(number1.substr(0, String(number1).indexOf('.')));
	decimal1 = parseInt(number1.substr(String(number1).indexOf('.') +1));
	
	precision2 = parseInt(number2.substr(0, String(number2).indexOf('.')));
	decimal2 = parseInt(number2.substr(String(number2).indexOf('.') +1));
	
	if(precision1 < precision2) flag = true;
	else if(precision1 == precision2 && decimal1 < decimal2) flag = true;
	
	return flag;
}

/**
 * selecet payment modules on pack selection
 * when payment modules list created as custom field
 * tested on registration,upgrade and extend, other cases may require fixing
 *
 * @param string element
 * @return void 
 */
mgm_select_pack_modules=function(e_type){
	// try
	try{
		// console.dir(mgm_pack_modules);
		// with radio
		if( e_type == 'select' ){
			selected = jQuery("select[rel='mgm_subscription_options']").val();
			// console.log('Select: ' + selected);
		}else{
			selected = jQuery(":input[rel='mgm_subscription_options']:checked").val();
			// console.log('Radio: ' + selected);
		}
		
		// hide all
		jQuery('#mgm_payment_gateways_container').find("div.mgm_payment_opt_wrapper").hide();
		// show selected
		jQuery.each(mgm_pack_modules, function(key, modules){
			// match
			if( key == selected ){
				// size
				_size = jQuery(modules).size();
				_checked = 0;
				// console.dir(modules);
				jQuery.each(modules, function(i, module){
					// console.log( module );
					jQuery('#mgm_payment_gateways_container').find('#' + module +'_container').slideDown();
					// check
					if( jQuery('#mgm_payment_gateways_container').find('#' + module +'_container').find(":radio[name='mgm_payment_gateways']").attr('checked') ){
						_checked++;
					}
				});
				// select first 
				if( _size == 1 || _checked == 0){
					jQuery('#mgm_payment_gateways_container').find(":radio[name='mgm_payment_gateways']:first").attr('checked', true);
				}
				// stop
				return;
			}
		});
	}catch( ex ){
		// we have not declared pack modules json data
	}
}
