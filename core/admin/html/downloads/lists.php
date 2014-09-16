	<!--download list-->
	<form id="mgmdownloadfrm" name="mgmdownloadfrm" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads" method="post">	
		<div class="mgm-search-box">	
			<?php include('search_box.php')?>
		</div>
		<div class="clearfix"></div>
		<div class="table widefatDiv width830px">
			<div class="row headrow">
				<div class="cell theadDivCell width10px maxwidth10px">
					<input type="checkbox" name="check_all" value="downloads[]" title="<?php _e('Select all','mgm'); ?>" />     		
				</div>				
				<div class="cell theadDivCell textalignleft width200px">
					<b><?php _e('Title','mgm') ?></b>
				</div>
				<div class="cell theadDivCell textalignleft width175px">
					<b><?php _e('File','mgm') ?></b>
				</div>
				<div class="cell theadDivCell textalignleft width50px">
					<b><?php _e('Limited Access','mgm') ?></b>
				</div>
				<div class="cell theadDivCell textalignleft width50px">
					<b><?php _e('File Exists?','mgm') ?></b>
				</div>
				<div class="cell theadDivCell textalignleft width130px">
					<b><?php _e('Expires','mgm') ?></b>
				</div>
				<div class="cell theadDivCell textalignleft width140px">
					<b><?php _e('Posted','mgm') ?></b>
				</div>
				<div class="cell theadDivCell textalignleft width100px">
					<b><?php _e('Action','mgm') ?></b>
				</div>
			</div>
			<div class="tbodyDiv" id="download_rows">		
			<?php 
				$wp_date_format = get_option('date_format');
				$date_format = mgm_get_date_format('date_format');
				$date_format_short = mgm_get_date_format('date_format_short');
				//$path = get_option('siteurl') . '/wp-content/uploads/';
				$path = WP_UPLOAD_URL;
				// hook
				$download_hook = mgm_get_class('system')->get_setting('download_hook', 'download');
				// loop
				if (count($data['downloads']) > 0): foreach ($data['downloads'] as $download):	
					//// user	
					$user      = get_userdata($download->user_id);		
					// file name
					$filename  = ($download->real_filename) ? $download->real_filename : str_replace($path, '', $download->filename);
					// links
					$filename  = end(explode('/', $filename));
					// file size
					$filesize  = (is_null($download->filesize) || (int)substr(trim($download->filesize),0,1) == 0) ? mgm_file_get_size($download->filename) : $download->filesize;				
					// expire
					$expire_dt = intval($download->expire_dt)>0? date($date_format_short, strtotime($download->expire_dt)):__('Never','mgm');
					// post
					$post_date = date($wp_date_format, strtotime($download->post_date));
					// format
					$lbl_fmt   = '<span class="%s">%s</span>';?>
					<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="download_row_<?php echo $download->id?>">			
						<div class="cell width10px maxwidth10px paddingleftimp10px">
							<input type="checkbox" name="downloads[]" id="download_<?php echo $download->id ?>" value="<?php echo $download->id ?>" />		
						</div>						
						<div class="cell textalignleft width200px maxwidth200px">
							<a href="javascript:void(0);" title="<?php echo $download->title; ?>"><?php echo mgm_ellipsize($download->title, 20) ?></a>
						</div>
						<div class="cell textalignleft width175px maxwidth175px">
							<a href="javascript:void(0);" title="<?php echo $filename; ?>"><?php printf('%s <br>(%s)', mgm_ellipsize($filename, 20), (!empty($filesize) ? $filesize : '0 bytes'))?></a>
						</div>
						<div class="cell textalignleft width50px maxwidth50px">
							<div align="center">
								<b><?php echo (bool_from_yn($download->members_only) ? sprintf($lbl_fmt,'mgm_color_green',__('Yes', 'mgm')) : sprintf($lbl_fmt,'mgm_color_red',__('No', 'mgm'))) ?></b>
							</div>
						</div>
						<div class="cell textalignleft width50px maxwidth50px">					
							<div align="center">
								<b><?php echo (!empty($filesize) ? sprintf($lbl_fmt,'mgm_color_green',__('Yes', 'mgm')) : sprintf($lbl_fmt,'mgm_color_red',__('No', 'mgm'))) ?></b>
							</div>
						</div>
						<div class="cell textalignleft width130px maxwidth130px">
							<?php echo $expire_dt?>
						</div>
						<div class="cell textalignleft width140px maxwidth140px">
							<?php printf(__('%s <br>(by %s)','mgm'), $post_date, mgm_ellipsize($user->user_login,20))?>
						</div>
						<div class="cell textalignleft width100px maxwidth100px">	
							<?php /*?><a href="javascript://" rel="#download_settings_overlay_<?php echo $download->id ?>" title="<?php _e('Settings','mgm')?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/cog.png" /></a>		
							<?php include('settings_overlay.php');?>	<?php */?>		
							<a href="javascript:mgm_download_edit('<?php echo $download->id ?>')" title="<?php _e('Edit', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/edit.png" /></a>	
							<a href="javascript:mgm_download_delete('<?php echo $download->id ?>')" title="<?php _e('Delete', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png" /></a>										
						</div>
					</div>		
				<?php endforeach; else:?>
				<div class="row">
					<div class="cell mgm-center-txt" ><?php _e('You haven\'t created any downloads yet.','mgm')?></div>
				</div>
				<?php endif;?>	
			</div>
		</div>
		<div class="clearfix"></div>
		<?php if(count($data['downloads']) > 0):?>
		<div class="mgm_bulk_actions_div">
			<select name="bulk_actions" id="bulk_actions" class="width150px">
				<option value=""><?php _e('Bulk Actions','mgm')?></option>
				<?php echo mgm_make_combo_options(array('delete'=>__('Delete','mgm')), $data['bulk_actions'], MGM_KEY_VALUE);?>
			</select>
			<input class="button" type="button" name="apply_btn" value="<?php _e('Apply', 'mgm')?>" onclick="mgm_download_bulk_actions()"/>
		</div>			
		<div class="mgm_page_links_div">
			<?php if($data['page_links']):?><div class="pager-wrap"><?php echo $data['page_links']?></div><?php endif; ?>
		</div>	
		<div class="clearfix"></div>	
		<?php endif;?>			
	</form>
	<script language="javascript">
		<!--
		jQuery(document).ready(function(){			
			// tip
			// jQuery("#download_rows a[rel]").overlay({effect: 'apple'});
			// bind
			jQuery('#downloads').mgm_bind_check_all();
			// set pager anchor 2 post
			mgm_set_pager_anchor2post('#download_list', '#search-table');
			// set pager dropdown 2 post
			mgm_set_pager_select2post('#download_list', '#search-table', '<?php echo $data['page_url']?>');				
		});	
		//-->
	</script>