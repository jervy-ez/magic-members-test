/**
 * jQuery Autosave 1.0.0
 *
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Written by Stan Lemon <stosh1985@gmail.com>
 * Last updated: 2009.12.19
 *
 * jQuery Autosave monitors the state of a form and detects when changes occur.
 * When changes take place to the form it then triggers an event which allows for
 * a develop to integrate hooks for autosaving content.
 *
 * This plugin requires the fieldSerialize() method in the official jQuery
 * form plugin, which can be downloaded at:
 * http://jquery.malsup.com/form/
 *
 * To Do:
 * - Improve performance and memory management, specifically in Firefox.
 */
(function($) {

	$.fn.autosave = function(o) {
		var o = $.extend({}, $.fn.autosave.defaults, o);
		var saver, monitor;
		var self = this;

		return this.addClass('autosave').bind('autosave.setup', function(){
			o.setup.apply( self , [self,o] );

			// Start by recording the current state of the form for comparison later
			$(self).trigger('autosave.record');

			// Fire off the autosave at an interval
			saver = setInterval( function() {
				$(self).trigger('autosave.save');
			}, o.interval);
			
			// Monitor the state of the form
			monitor = setInterval( function() {
				var values = $(self).data('form');

				// @todo Can this be replaced using a version of serializeArray() ?
				for ( var y=0; y<(f = $(self)[0].elements).length; y++ ) {
					if ( values[ $(f[y]).attr('name') ] != undefined && values[ $(f[y]).attr('name') ] != $(f[y]).val() && !$(f[y]).is('button') && !$(f[y]).is('.autosave\-ignore') ) {
						// Store this value into our stack.
						values[ $(f[y]).attr('name') ] = $(f[y]).val();

						// Trigger the change handler which will mark the form as dirty until it's saved.
						$(self).data('dirty', true);
						o.dirty.apply( self , [self,o] );
					}
				}
			}, o.monitor);
		}).bind('autosave.shutdown', function() {
			o.shutdown.apply( self , [self,o] );

			clearInterval(saver);
			clearInterval(monitor);

		    // We'll call a synchronous ajax request to autosave the form before we move on.
		    // It's synchronous so that the browser will not move on without first completing
		    // the autosave request.
			if ( $(this).data('dirty') == true ) {
			    $(this).trigger('autosave.save', [false]);
			}
			
			$(self).removeClass('autosave').unbind('autosave');
			$(self).data('form', null);
			$(self).data('dirty', null);

		}).bind('autosave.record', function() {
			o.record.apply( self , [self,o] );

			$(this).data('dirty', false);
			// Store all of the current values of the form.
			var values = {};

			for ( var i=0; i<(e = $(self)[0].elements).length; i++ ) {
				if ( $(e[i]).attr('name') != undefined && $(e[i]).attr('name') != NaN && $(e[i]).attr('name') != '' && !$(e[i]).is('button') && !$(e[i]).is('.autosave\-ignore') ) {
					values[ $(e[i]).attr('name') ] = $(e[i]).val();
				}
			}
			$(this).data('form', values);

		}).bind('autosave.save', function(e, async) {
			o.save.apply( self , [self,o] );

			// If the form is using the validator plugin, silently check and do not save if invalid
			if ( $.fn.validate != undefined && $(this).data('validator') != null && !$(this).data('validator').checkForm() )
				return;

			// If the form is dirty and there is not already an active execution of the autosaver.
			if ( $(this).data('dirty') == true && $(self).data('active') != true ) {
				var callback = function(){
					$(self).trigger('autosave.record');
				};
				var data = $(self).find('input,select,textarea').not('.autosave\-ignore').fieldSerialize();

				if (o.url != undefined && $.isFunction(o.url)) {
					o.url.apply( self , [self,o,data,callback] );
				} else {
					$.ajax({
					    async: 	(async == undefined) ? true : async,
						url: 	(o == undefined || o.url == undefined) ? $(self).attr('action') : o.url,
						type: 	'post',
						data: 	data,
						beforeSend: function() {
							$(self).data('active', true);

							if ( $.ajaxSettings.beforeSend != undefined ) 
								$.ajaxSettings.beforeSend.apply(this, arguments);
						},
						success: callback,
						complete: function() {
							$(self).data('active', false);

							if ( $.ajaxSettings.complete != undefined ) 
								$.ajaxSettings.complete.apply(this, arguments);
						}
					});
				}
			}
		}).trigger('autosave.setup');
	};
	
	$.fn.autosave.defaults = {
		/** Saving **/
		//url : function(e,o,callback) {} <-- If not defined, uses standard AJAX call on the form.
		/** Timer durations **/
		interval: 	60000,
		monitor: 	3000,
		/** Callbacks **/
		setup: 		function(e,o) {},
		record: 	function(e,o) {},
		save: 		function(e,o) {},
		shutdown: 	function(e,o) {},
		dirty: 		function(e,o) {}
	};

	window.onbeforeunload = function() {
		$('form.autosave').trigger('autosave.shutdown');
	};

})(jQuery);