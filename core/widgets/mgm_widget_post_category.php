<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * post and category widgets
 *
 * @todo check access
 */
 
// post meta boxes
//add_action('admin_menu'         , 'mgm_post_setup_meta_box');
add_action('save_post'          , 'mgm_post_setup_save');
// categoty access list
add_action('add_category_form'  , 'mgm_category_form');
add_action('edit_category_form' , 'mgm_category_form');
// taxonomy access list
add_action('add_tag_form'       , 'mgm_taxonomy_form');
add_action('edit_tag_form'      , 'mgm_taxonomy_form');
// save/delete 
add_action('create_term'        , 'mgm_term_save', 10, 3);
add_action('edit_term'          , 'mgm_term_save', 10, 3);
add_action('delete_term'        , 'mgm_term_delete', 10, 3);

add_action('admin_menu'         , 'mgm_widget_dashboard_membership_options');

/**
 * add dashboard membership options widget
 */
function mgm_widget_dashboard_membership_options() {
	// check membership options widget is enabled
	if(mgm_is_mgm_menu_enabled('primary', 'mgm_widget_dashboard_membership_options')) {
		mgm_post_setup_meta_box();
	}
}

/**
 * the meta box for post/page purchase
 *
 */
function mgm_post_setup_meta_box(){
	// 2.7+
	if( function_exists( 'add_meta_box' )) {		
		// update for custom post type
		if ( function_exists( 'get_post_types' ) ) {
			// get custom post types
			$custom_post_types = get_post_types( array(), 'objects' );
			// add to array
			foreach ( $custom_post_types as $post_type ) {
				// set
				if ( $post_type->show_ui ) {// check if enabled
					$post_types[] = $post_type->name;
				}
			}
		} else{
			// default post types
			$post_types = array('post','page');
		}				
		// assign
		foreach($post_types as $post_type){			
			add_meta_box('magicmemberdiv', __('Magic Members'), 'mgm_post_setup_meta_box_form', $post_type, 'side', 'high');
		}				
	}else{
		// just for test: deprecated
		add_action('dbx_post_advanced', 'mgm_post_setup_meta_box_form' );
    	add_action('dbx_page_advanced', 'mgm_post_setup_meta_box_form' );
	}	
}

/**
 * the meta box for post/page purchase
 *
 */
function mgm_post_setup_meta_box_form($post){
	// get object
	$post_obj = mgm_get_post($post->ID);	
	$datepickerformat = mgm_get_datepicker_format();
	// set default price
	if($post_obj->purchase_cost == 0){
		if (mgm_get_module('mgm_paypal','payment')->setting['purchase_price']) {
			$post_obj->purchase_cost = mgm_get_module('mgm_paypal','payment')->setting['purchase_price'];
		} else {
			$post_obj->purchase_cost = mgm_get_class('system')->setting['post_purchase_price'];
		}
	}
	// protect
	$protect_content = mgm_protect_content();
	//issue#: 414(changed id submitpost => submitpost_member for the below div )
	?>
	<div class="submitbox" id="submitpost_member">	
		<div class="misc-pub-section">
			<p id="howto">
				<?php _e('Select which membership types will have access to read this post/page.','mgm') ?>
				<?php _e('Note: The private parts of the post should be inside the following tags: <strong>[private]</strong> <em>your text</em> <strong>[/private]</strong>','mgm') ?>
			</p>
			<p>
				<div class="mgm_post_setup_meta_box_div">
					<input type="checkbox" name="check_all" value="mgm_post[access_membership_types][]" /> <span><?php _e('Select all','mgm'); ?></span>
				</div>
			</p>
			<p>
				<?php echo mgm_make_checkbox_group('mgm_post[access_membership_types][]', mgm_get_class('membership_types')->get_membership_types(), $post_obj->access_membership_types, MGM_KEY_VALUE);?>				
			</p>
			<?php if($protect_content == false):?>
			<div class="information mgm_width_230px"><?php echo sprintf(__('<a href="%s">Content Protection</a> is <b>%s</b>. Make sure its enabled to Protect Post/Page.','mgm'), 'admin.php?page=mgm/admin', ($protect_content ? 'enabled' :'disabled'))?></div>			
			<?php endif;?>
		</div>	
		
		<div class="misc-pub-section">
			<b><?php _e( 'Pay Per Post', 'mgm' ); ?>:</b>
			<a href="#payperpost" class="mgm-toggle"><?php _e('Edit') ?></a>
			<div id="payperpostdiv" class="hide-if-js">
				<div class="mgm_padding_5px">				
					<p class="postpurhase-heading"><?php _e('Purchasable Settings','mgm') ?>:</p>
					<ul class="mgm_post_setup_meta_box_ul">
						<li>
							<label><?php _e('If the user doesn\'t have access, is this post/page available to buy?','mgm') ?></label><br/>	
							<input type="radio" class="radio" name="mgm_post[purchasable]" value='N' <?php mgm_check_if_match('N',$post_obj->purchasable);?>/>
							<label><?php _e('No','mgm') ?></label>
							<input type="radio" class="radio" name="mgm_post[purchasable]" value='Y' <?php mgm_check_if_match('Y',$post_obj->purchasable); ?>/> 
							<label><?php _e('Yes','mgm') ?></label>
						</li>
						<li>
							<label><?php _e('Cost of Post?','mgm') ?> </label><br>
							<input type="text" name="mgm_post[purchase_cost]" class="mgm_width_55px" value="<?php echo $post_obj->purchase_cost; ?>"/> <?php echo mgm_get_setting('currency')?>							
						</li>
						<li>
							<label><?php _e('The date that the ability to buy this page/post expires (Leave blank for indefinate).','mgm') ?></label><br />
							<input type="text" name="mgm_post[purchase_expiry]" class="date_input mgm_width_100px" value="<?php echo (intval($post_obj->purchase_expiry)>0) ? date(MGM_DATE_FORMAT_INPUT, strtotime($post_obj->purchase_expiry)) : ''; ?>"/>
							<span class="mgm_font_size_8px">(<?php echo $datepickerformat; ?>)</span>							
						</li>
						<li>
							<label><?php _e('The number of days that the buyer will have access for (0 for indefinate).','mgm') ?></label><br />
							<input type="text" name="mgm_post[access_duration]" class="mgm_width_50px" value="<?php echo $post_obj->get_access_duration(); ?>"/>
						</li>
						<li>
							<label><?php _e('The number of times that the buyer will have access for, "PAY PER VIEW" (0 for unlimited views).','mgm') ?></label><br />
							<input type="text" name="mgm_post[access_view_limit]" class="mgm_width_50px" value="<?php echo $post_obj->get_access_view_limit(); ?>"/>
						</li>	
					</ul>
					
					<?php if($addons = mgm_get_all_addon_combo()):?>
					<p class="postpurhase-heading"><?php _e('Addon Settings','mgm') ?>:</p>
					<ul class="mgm_post_setup_meta_box_ul">		
						<li>
							<label><?php _e('Allow Addons?','mgm') ?></label>	<br />
							<select name="mgm_post[addons][]" class="mgm_width_50px">
								<option value="">-</option>
								<?php echo mgm_make_combo_options($addons, $post_obj->get_addons(), MGM_KEY_VALUE)?>
							</select>							
						</li>					
					</ul>	
					<?php endif;?>

					<p class="postpurhase-heading"><?php _e('Payment Settings','mgm')?>:</p>	
					<p class="fontweightbold"><?php _e('Allow Modules','mgm')?>:</p>
					<?php if( $payment_modules = mgm_get_class('system')->get_active_modules('payment') ): 
					$modue_i = 0; foreach($payment_modules as $payment_module) : if( ! in_array($payment_module, array('mgm_trial'))):?>
					<input type="checkbox" name="mgm_post[allowed_modules][<?php echo $modue_i; ?>]" value="<?php echo $payment_module?>" <?php echo (in_array($payment_module,$post_obj->get_allowed_modules()))?'checked':''?>/> 
					<label><?php echo mgm_get_module($payment_module)->name?></label><br/>
					<?php $modue_i++; endif; endforeach; else:?>				
					<b class="mgm_color_red"><?php _e('No payment module is active.','mgm')?></b>		
					<?php endif;?>
					<?php 
					// init
					$payment_settings = '';
					// product id mapping 
					if( $payment_modules ): 
						foreach($payment_modules as $payment_module) :								
							if( $module = mgm_is_valid_module($payment_module, 'payment', 'object') ):
								if($module->has_product_map()):
									$payment_settings .= $module->settings_post_purchase($post_obj);
								endif;
							endif;
						endforeach; 
					endif;		
					// print
					if(!empty($payment_settings)): echo $payment_settings; endif;?>
					
					<?php do_action('mgm_widget_payperpost_options', $post->ID);?>												
				</div>	
			</div>
		</div>			
		<div class="misc-pub-section misc-pub-section-last">
			<b><?php _e( 'Post Delay (sequential posts)', 'mgm' ); ?>:</b>
			<a href="#postdelay" class="mgm-toggle"><?php _e('Edit') ?></a>
			<div id="postdelaydiv" class="hide-if-js">
				<div class="mgm_padding_5px">				
					<p id="howto"><?php _e('How long should the user have been a member to see this content?','mgm') ?></p>
					<div class="div_table mgm_width_100pr">
					<?php
					foreach (mgm_get_class('membership_types')->membership_types as $type_code=>$type_name) :					
						$val = isset($post_obj->access_delay[$type_code]) ? (int)$post_obj->access_delay[$type_code] : 0;?>
						<div class="row">
							<div class="cell mgm_width_100px mgm_font_size_11px"><?php echo $type_name; ?></div>
							<div class="cell mgm_font_size_11px">
								<input type="text" name="mgm_post[access_delay][<?php echo $type_code; ?>]" value="<?php echo $val ?>" class="mgm_width_50px"/> Day(s)
							</div>
						</div>
					<?php endforeach;?>
					</div>		
				</div>	
			</div>
		</div>				
	</div>	
	
	<script language="javascript">
		jQuery(document).ready(function(){			
			jQuery('.mgm-toggle').bind('click', function(){
				if(jQuery(this).html() == '<?php _e('Edit', 'mgm') ?>'){
					jQuery(jQuery(this).attr('href')+'div').slideDown();
					jQuery(this).html('<?php _e('Close','mgm') ?>')
				}else{
					jQuery(jQuery(this).attr('href')+'div').slideUp();
					jQuery(this).html('<?php _e('Edit','mgm') ?>')
				}
			});
			// check bind
			jQuery("#submitpost_member :checkbox[name='check_all']").bind('click',function(){
				// check
				jQuery("#submitpost_member :checkbox[name='"+jQuery(this).val()+"']").attr('checked', (jQuery(this).attr('checked')=='checked') );
				// label
				if(jQuery(this).attr('checked')){
					jQuery(this).next().html('<?php _e('Deselect all','mgm') ?>');
				}else{
					jQuery(this).next().html('<?php _e('Select all','mgm') ?>');
				}
			});	

			// bind module allow
			jQuery(":checkbox[name^='mgm_post[allowed_modules]']").bind('click',function() {		
				var _m = jQuery(this).val().replace('mgm_', '');
				
				if(jQuery(this).attr('checked')){				
					jQuery('#settings_postpurchase_package_' + _m).slideDown('slow');
				}else{				
					jQuery('#settings_postpurchase_package_' + _m).slideUp('slow');
				}
			});
			// date		
			try{	
				mgm_date_picker('.date_input', false, {yearRange:"<?php echo mgm_get_calendar_year_range() ?>", dateFormat: "<?php echo $datepickerformat; ?>"});
			}catch(ex){}	
		});
	</script>
	<?php
}

/**
 * post/page meta box data save
 *
 */
function mgm_post_setup_save($post_id){
	// donot process ajax
	// if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return true;
	
	// update
	if(isset($_POST['mgm_post']) ){
		// check revision
		if ( $the_post = wp_is_post_revision($post_id) )
			$post_id = $the_post;
			
		// get object
		$post_obj = mgm_get_post($post_id);
		
		// check object
		if(is_object($post_obj)){
			// post data
			$post_objdata = $_POST['mgm_post'];			
			// access membership types
			if(!isset($post_objdata['access_membership_types'])){
				$post_objdata['access_membership_types'] = array();
			}			
			// access delay
			if(!isset($post_objdata['access_delay'])){
				$post_objdata['access_delay'] = array();
			}
			// purchase expiry 
			if(!empty($post_objdata['purchase_expiry'])){
				//issue #1424
				$datepickerformat = mgm_get_datepicker_format();
				$post_objdata['purchase_expiry'] = mgm_format_inputdate_to_mysql($post_objdata['purchase_expiry'],$datepickerformat);				
			}
			// int 
			$post_objdata['access_duration'] = $post_objdata['purchase_duration'] = (int)$post_objdata['access_duration'];
			// int 
			$post_objdata['access_view_limit'] = (int)$post_objdata['access_view_limit'];
			
			// addons
			if(!isset($post_objdata['addons'])){
				$post_objdata['addons'] = array();
			}
			
			// allowed_modules
			if(!isset($post_objdata['allowed_modules'])){
				$post_objdata['allowed_modules'] = array();
			}
			
			// set new fields
			$post_obj->set_fields($post_objdata);			
			
			// apply filter
			$post_obj = apply_filters('mgm_post_update', $post_obj, $post_id);			
			
			// save meta
			$post_obj->save();
			
			// log
			// mgm_log($post_obj, __FUNCTION__);
		}
	}	
	
	// return
	return true;
}

/**
 * edit/add category form to assign access by membership types
 *
 * @param category
 * @return none
 */
function mgm_category_form($category){			
	// member types
	$access_membership_types = mgm_get_class('post_category')->get_access_membership_types();
	// init
	$membership_types = array();
	// check
	if(isset($category->term_id) && $category->term_id>0){
		// check
		if(isset($access_membership_types[$category->term_id])){
			$membership_types = $access_membership_types[$category->term_id];
		}
	}
	// access list
	$mgm_category_access = mgm_make_checkbox_group('mgm_category_access[]', mgm_get_class('membership_types')->membership_types, $membership_types, MGM_KEY_VALUE);?>
	<script language="javascript">
		<!--
		jQuery(document).ready(function(){	
			<?php if(isset($category->term_id) && intval($category->term_id) > 0):?> 							
			var html='<tr class="form-field form-required">' +
					 ' 	<th scope="row" valign="top"><label for="cat_name"><?php _e('Category Protection','mgm')?></label></th>' +
					 '	<td><div>'+"<?php echo $mgm_category_access; ?>"+'</div>'+
					 '  <p><?php _e('Only selected membership types can access the category (Leave all unchecked to allow public access.)','mgm') ?></p></td>' +
					 '</tr>';
			jQuery("#edittag .form-table").append(html);
			<?php else:?>			
			var html ='<div class="form-field">'+
							'<label for="mgm_category_access"><?php _e('Category Protection','mgm')?></label>'+
							"<?php echo $mgm_category_access; ?>"+
							'<p><?php _e('Only selected membership types can access the category (Leave all unchecked to allow public access.)','mgm') ?>.</p>'+
					   '</div>';								
			jQuery("#addtag p.submit").before(html);
			<?php endif;?>
		});
		//-->
	</script>
	<?php		
}

/**
 * edit/add term/taxonomy form to assign access by membership types
 *
 * @param taxonomy
 * @return none
 */
function mgm_taxonomy_form($taxonomy){	
	// except tags
	if(is_object($taxonomy)){
		$term = $taxonomy->taxonomy;
	}else{
		$term = $taxonomy;
	}
	// exit if tags
	if($term == 'post_tag') return;
	
	// member types
	$access_membership_types = mgm_get_class('post_taxonomy')->get_access_membership_types();
	// init
	$membership_types = array();
	// check edit
	if(isset($taxonomy->term_id) && $taxonomy->term_id>0){
		// check
		if(isset($access_membership_types[$taxonomy->term_id])){
			$membership_types = $access_membership_types[$taxonomy->term_id];
		}
	}
	// label
	if($tax = get_taxonomy( $term )){
		$label = isset($tax->singular_label) ? $tax->singular_label : (isset($tax->labels->singular_name) ? $tax->labels->singular_name : mgm_singular($term));
		$name  = isset($tax->name) ? $tax->name : $term;
	}else{
		$label = mgm_singular($term);
		$name  = $term;
	}
	// access
	$mgm_taxonomy_access = mgm_make_checkbox_group('mgm_taxonomy_access[]', mgm_get_class('membership_types')->membership_types, $membership_types, MGM_KEY_VALUE);?>
	<script language="javascript">
		<!--
		jQuery(document).ready(function(){	
			<?php if(isset($taxonomy->term_id) && intval($taxonomy->term_id) > 0):?> 							
			var html='<tr class="form-field form-required">' +
					 ' 	<th scope="row" valign="top"><label for="cat_name"><?php printf(__('%s Protection','mgm'), $label)?></label></th>' +
					 '	<td><div>'+"<?php echo $mgm_taxonomy_access; ?>"+'</div>'+
					 '  <p><?php printf(__('Only selected membership types can access the <b>%s</b> taxonomy (Leave all unchecked to allow public access)','mgm'), $label) ?></p></td>' +
					 '</tr>';
			jQuery("#edittag .form-table:last").append(html);
			<?php else:?>			
			var html='<div class="form-field">'+
					 '<label for="mgm_taxonomy_access"><?php printf(__('%s Protection','mgm'), $label)?></label>'+
					 "<?php echo $mgm_taxonomy_access; ?>"+
					 '<p><?php printf(__('Only selected membership types can access the <b>%s</b> taxonomy (Leave all unchecked to allow public access)','mgm'), $label) ?>.</p>'+
					 '</div>';								
			jQuery("#addtag p.submit").before(html);
			<?php endif;?>
		});
		//-->
	</script>
	<?php
}

/**
 * taxonomy/category save
 *
 * @param int term_id
 * @param int term taxonomy id
 * @param string taxonomy
 * @return none
 */
function mgm_term_save($term_id, $tt_id, $taxonomy){
	// term
	switch($taxonomy){		
		case 'category':
			// class
			$post_category = mgm_get_class('post_category');	
			// set
			$post_category->access_membership_types[$term_id] = $_POST['mgm_category_access'];
			// save
			$post_category->save();
		break;
		case 'post_tag':
		// nothing for tags
		break;
		default:
			// class
			$post_taxonomy = mgm_get_class('post_taxonomy');
			// set	
			$post_taxonomy->access_membership_types[$term_id] = $_POST['mgm_taxonomy_access'];
			// save
			$post_taxonomy->save();
		break;
	}	
}

/**
 * taxonomy/category delete
 *
 * @param int term_id
 * @param int term taxonomy id
 * @param string taxonomy
 * @return none
 */
function mgm_term_delete($term_id, $tt_id, $taxonomy){	
	// term
	switch($taxonomy){		
		case 'category':
			// class
			$post_category = mgm_get_class('post_category');	
			// set
			$post_category->access_membership_types[$term_id] = $_POST['mgm_category_access'];
			// save
			$post_category->save();
		break;
		case 'post_tag':
		// nothing for tags
		break;
		default:
			// class
			$post_taxonomy = mgm_get_class('post_taxonomy');	
			// set
			$post_taxonomy->access_membership_types[$term_id] = $_POST['mgm_taxonomy_access'];
			// save
			$post_taxonomy->save();
		break;
	}
}

// end file core/widgets/mgm_widget_post_category.php