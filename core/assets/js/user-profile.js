var pwsL10n = {
	empty: "Strength indicator",
	short: "Very weak",
	bad: "Weak",
	good: "Medium",
	strong: "Strong",
	mismatch: "Mismatch"
};
(function(jQuery){

	function check_pass_strength() {
		var pass1 = jQuery('#pass1').val(), user = jQuery('#user_login2').val(), pass2 = jQuery('#pass2').val(), strength;
		if(typeof(user) == 'undefined') user = jQuery('#user_login').val();
		jQuery('#pass-strength-result').removeClass('short bad good strong');
		if ( ! pass1 ) {
			jQuery('#pass-strength-result').html( pwsL10n.empty );
			return;
		}

		strength = passwordStrength(pass1, user, pass2);

		switch ( strength ) {
			case 2:
				jQuery('#pass-strength-result').addClass('bad').html( pwsL10n['bad'] );
				break;
			case 3:
				jQuery('#pass-strength-result').addClass('good').html( pwsL10n['good'] );
				break;
			case 4:
				jQuery('#pass-strength-result').addClass('strong').html( pwsL10n['strong'] );
				break;
			case 5:
				jQuery('#pass-strength-result').addClass('short').html( pwsL10n['mismatch'] );
				break;
			default:
				jQuery('#pass-strength-result').addClass('short').html( pwsL10n['short'] );
		}
	}

	jQuery(document).ready( function() {
		jQuery('#pass1').val('').keyup( check_pass_strength );
		jQuery('#pass2').val('').keyup( check_pass_strength );
		jQuery('.color-palette').click(function(){jQuery(this).siblings('input[name=admin_color]').attr('checked', 'checked')});
		jQuery('#nickname').blur(function(){			
			var str = jQuery(this).val() || user;
			var select = jQuery('#display_name');
			var sel = select.children('option:selected').attr('id');
			select.children('#display_nickname').remove();
			if ( ! select.children('option[value=' + str + ']').length )
				select.append('<option id="display_nickname" value="' + str + '">' + str + '</option>');
			jQuery('#'+sel).attr('selected', 'selected');
		});
		
		jQuery('#first_name, #last_name').blur(function(){
			var select = jQuery('#display_name');
			var first = jQuery('#first_name').val(), last = jQuery('#last_name').val();
			var sel = select.children('option:selected').attr('id');
			jQuery('#display_firstname, #display_lastname, #display_firstlast, #display_lastfirst').remove();
			if ( first && ! select.children('option[value=' + first + ']').length )
				select.append('<option id="display_firstname" value="' + first + '">' + first + '</option>');
			if ( last && ! select.children('option[value=' + last + ']').length )
				select.append('<option id="display_lastname" value="' + last + '">' + last + '</option>');
			if ( first && last ) {
				if ( ! select.children('option[value=' + first + ' ' + last + ']').length )
					select.append('<option id="display_firstlast" value="' + first + ' ' + last + '">' + first + ' ' + last + '</option>');
				if ( ! select.children('option[value=' + last + ' ' + first + ']').length )
					select.append('<option id="display_lastfirst" value="' + last + ' ' + first + '">' + last + ' ' + first + '</option>');
			}
			jQuery('#'+sel).attr('selected', 'selected');
		});
    });

})(jQuery);
