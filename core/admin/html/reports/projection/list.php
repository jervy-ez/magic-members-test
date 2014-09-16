<?php 
	mgm_box_top('Projection Report. ');
	$sformat = mgm_get_date_format('date_format_short');
?>
<form name="frmProjection" id="frmProjection" method="POST" action="" class="marginpading0px">
<div class="table form-table widefat" id="projection-table">
	<div class="row">
		<div class="cell">
			<b><?php _e('Date Range:','mgm') ?></b>
		</div>
		<div class="cell">
			<?php _e('Start', 'mgm')?>: 
			<input type="text" name="projection_date_start" size="10" value="<?php if(isset($data[0]['date_start'])){echo $data[0]['date_start'];} ?>"/> 
			<?php _e('End', 'mgm')?> 
			<input type="text" name="projection_date_end" size="10" value="<?php if(isset($data[0]['date_end'])){echo $data[0]['date_end'];} ?>"/>
		</div>
	</div>
	<div class="row">
		<div class="cell width20">
			<b><?php _e('Membership Type:','mgm') ?></b>		
		</div>
		<div class="cell">
			<select name="projection_membership_type");" class="width200px">
				<option value="all"><?php _e('All','mgm') ?></option>
				<?php
				$subscription_packs = mgm_get_class('subscription_packs')->packs;					
				$membership_types_obj = mgm_get_class('membership_types');
				$strTypes = '';
				
				foreach ($membership_types_obj->membership_types as $type_code=>$type_name) {
					if ($type_code == 'guest') {
						continue;
					}
					
					$temp=array();
					foreach($subscription_packs as  $i=>$pack){				
						if ($pack['membership_type'] != $type_code) continue;
						$temp[]=$pack;
					}				
	
					$cost = $temp[0]['cost'] ;
	
					if(trim($cost) > 0){
		
						if(isset($data[0]['member_type'])){
							if($data[0]['member_type'] == $type_code )
							$strTypes .= '<option value="'. $type_code .'" SELECTED>'. __(mgm_stripslashes_deep($type_name), 'mgm') .'</option>';
						}
						$strTypes .= '<option value="'. $type_code .'">'. __(mgm_stripslashes_deep($type_name), 'mgm') .'</option>';
					}
				}
				echo $strTypes;
				?>
			</select>
					&nbsp;
			<input type="button" 
				name="reload" 
				class="button" 
				value="<?php _e('Generate Report','mgm') ?>" 
				onclick="search_projection_list()" />
		</div>
	</div>
</div>
</form>


<?php mgm_box_top('Projection', '', false);?>
<?php
	$count =count($data);
	if($count ==1 && $data[0]['cost'] ==0){
		echo "No Results ...!";
	}else {$total = 0;
?>
<div class="table width100">
	<div class="row">
		<div class="cell mgm_rp_head aleft">
			<?php echo __('Date','mgm'); ?>
		</div>
		<div class="cell mgm_rp_head aleft">
			<?php echo __('Expected Recurring Earning.','mgm'); ?>
		</div>
	</div>
<?php 

	$system_obj = mgm_get_class('system');	
	$currency_symbol = mgm_get_currency_symbols($system_obj->setting['currency']);

foreach ($data as $row) {	
  	$timestamp = strtotime($row['date']);
	$date = date($sformat, $timestamp);
	$cost =$row['cost'];
	$total += $cost;
	if($cost !=0){
    	$flot_datas_excepted_sales[] = '['.($timestamp*1000).','.$cost.']';		
?>	
	<div class="row">
		<div class="cell mgm_rp_val aleft">
			<?php echo $date; ?>
		</div>
		<div class="cell mgm_rp_val aleft">
			<?php echo $currency_symbol.' '.number_format($cost,2); ?>
		</div>
	</div>
<?php } } ?>	
	<div class="row">
		<div class="cell">&nbsp;</div>
	</div>

	<div class="row">
		<div class="cell mgm_rp_head aleft"><?php echo __('Total : ','mgm'); ?></div>
		<div class="cell mgm_rp_head aleft"><?php echo $currency_symbol.' '.number_format($total,2); ?></div>
	</div>
</div>
<?php  
		$flot_data_excepted_sales = '['.implode(',',$flot_datas_excepted_sales).']';

		//date format for graph genaration
		$placeholders = array('y', 'Y', 'm', 'd');
		//replace values array
		$replace_val = array('%y', '%y', '%m', '%d');
		//graph string %d/%m/%y
		$graph_date = str_replace($placeholders, $replace_val, $sformat);
		
		
?>
<?php mgm_box_bottom();?>
<br/>
<?php 	
/*		$url = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=download_sales_pdf';

		$report_type= "projection";		
		
		if(isset($data[0]['date_start'])){
			$url .= '&bk_date_start='.$data[0]['date_start'];
		}
		if(isset($data[0]['date_start'])){
			$url .= '&bk_date_end='.$data[0]['date_end'];
		}
		if(isset($data[0]['member_type'])){
			$url .= '&bk_membership_type='.$data[0]['member_type'];
		}

		if(isset($report_type)){
			$url .= '&bk_report_type='.$report_type;
		}
		
		$random_number =rand(10,100);
		$url .= '&random='.$random_number;
*/
?>

<?php
	//Download PDF -Issue #763
	$title = 'PROJECTION REPORT ';
	
	if ( !empty($data[0]['date_start']) && !empty($data[0]['date_start']) ) {
		$title .= 'FROM  ' . $data[0]['date_start'] . ' TO ' . $data[0]['date_end'];
	}
	if (!empty($data[0]['member_type']) ) {
		if ( $data[0]['member_type'] == 'all' ) {
			$title .= ' FOR ALL MEMBERSHIP TYPES';
		} else {
			$title .= ' FOR ' . strtoupper($data[0]['member_type']) . ' MEMBERSHIP TYPE';
		}
	}
	$html ='';
	$html ='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	$html .='<br/><center>' . $title . '</center><br/><hr />' ;
	$html .='<table class="width100">
	<tr>
		<td class="mgm_rp_head" align="left">'. __('Date','mgm').' </td>
		<td class="mgm_rp_head" align="right">'.__('Expected Recurring Earning','mgm').'</td>
	</tr>';

	$system_obj = mgm_get_class('system');	
	$currency_symbol = mgm_get_currency_symbols($system_obj->setting['currency']);
	$total = 0;
	foreach ($data as $row) {

		$sformat = mgm_get_date_format('date_format_short');
		$date = date($sformat, strtotime( $row['date']));

		$cost =$row['cost'];
		$total += $cost;

		//$total +=	$row['cost'];	
		if($cost !=0){ 
			$html .= '<tr>
				<td class="mgm_rp_val" align="left">'. $date .'</td>
				<td class="mgm_rp_val" align="right">'.$currency_symbol.' '.$row['cost'].'</td>
			</tr>';
		}
	} 

	$html .= '<tr><td colspan="2">&nbsp;</td></tr>';
	
	$html .= '<tr>
		<td class="mgm_rp_head" align="left">'.__('Total : ','mgm').'</td>
		<td class="mgm_rp_head" align="right">'.$currency_symbol.' '.$total.'</td>
	</tr>';
	
	$html .= '</table>';	

	$url =MGM_LIBRARY_URL."third_party/dompdf/projection_report.php";
	
?>	

<form method="post" action="<?php echo $url; ?>" name="projectionFrm" id="salesFrm">
	<textarea name="projection" class="visibilityhidden displaynone"><?php echo $html; ?></textarea>
</form>

<script type="text/javascript">
function projection_report(){
	document.forms["projectionFrm"].submit();
}
</script>

<center>
<a href="javascript: projection_report()"><span class="pngfix"><?php echo __('[ Download PDF ]','mgm')?></span></a>
</center>

<?php mgm_box_top('Projection Chart', '', false);?>
<div class="table">
	<div class="row">
		<div class="cell" >
			<div id="placeholder_projection" class="width700px height300px"></div>
		</div>
	</div>
</div>
<?php mgm_box_bottom();?>
<br/>
<script language="javascript" type="text/javascript">

jQuery(function () {

 	var projection = <?php echo $flot_data_excepted_sales; ?>;
 	var data_one = [{label: "Projection",data: projection}];

 				
	var options = {
		legend: {
			show: true,
			margin: 10,
			backgroundOpacity: 0.5
		},
		points: {
			show: true,
			radius: 3
		},
		lines: {
			show: true
		},
		grid: {
			borderWidth:0
		},

		xaxis: {
			mode: "time",
			 minTickSize: [1, "day"],
			 timeformat: "<?php echo $graph_date; ?>"
			//tickSize:1
		},
		yaxis: {
			//tickSize:10,
			//tickDecimals: 2
			  min: 0
		}
	};

	var placeholder_projection = jQuery("#placeholder_projection");
 	jQuery.plot( placeholder_projection , data_one, options );
 
});

</script>
<?php } ?>
<script class="code" type="text/javascript">
    
	mgm_date_picker("#frmProjection :input[name='projection_date_start']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
	mgm_date_picker("#frmProjection :input[name='projection_date_end']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
    
	// reload
	search_projection_list=function() {
		
		jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=projection_list', type: 'POST', cache:false, data : jQuery("#projection-table :input").serialize(),
			beforeSend: function(){	
				// show message
				mgm_show_message('#projection', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
			 },
			 success:function(data){
				// show message
				mgm_show_message('#projection', {status:'success', message:'<?php _e('Search Result: ','mgm')?>'});																														
				// append 
				jQuery('#report_projection').html(data);										 
			 }
		});
	}

</script>

