// JavaScript Document
// bind checkall
jQuery.fn.mgm_bind_check_all = function(){
	// selector
	_selector = jQuery(this);
	// bind
	_selector.find(":checkbox[name='check_all']").bind('click',function(){
		// checked
		var checked = (jQuery(this).attr('checked') == 'checked') ? true : false;
		// switch checked state
		_selector.find(":checkbox[name='" + jQuery(this).val() + "']").attr('checked', checked);			
	});		
}
// serialize form
jQuery.fn.mgm_serialize_form = function (data) {  
  	// fields
	var fields = jQuery(this).find(':input').serializeArray();		
	// merge form data
	jQuery.each(fields, function(i, field){
		data[field.name] = field.value;
	});		
	// return
	return data;
}
// reset form
jQuery.fn.mgm_reset_form = function () {
  jQuery(this).each (function() { this.reset(); });
}