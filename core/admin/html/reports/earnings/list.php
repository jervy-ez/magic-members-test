<?php 
	mgm_box_top('Earnings Report. ');
	$sformat = mgm_get_date_format('date_format_short');
?>
<form name="frmEarnings" id="frmEarnings" method="POST" action="" class="marginpading0px">
<div class="table form-table widefat" id="earnings-table">
	<div class="row">
		<div class="cell">
			<b><?php _e('Date Range:','mgm') ?></b>
		</div>
		<div class="cell">
			<?php _e('Start', 'mgm')?>: 
			<input type="text" name="bk_date_start" size="10" value="<?php if(isset($data[0]['date_start'])){echo $data[0]['date_start'];} ?>"/> 
			<?php _e('End', 'mgm')?> 
			<input type="text" name="bk_date_end" size="10" value="<?php if(isset($data[0]['date_end'])){echo $data[0]['date_end'];} ?>"/>
		
		</div>
	</div>
	<div class="row">
		<div class="cell width20">
			<b><?php _e('Membership Type:','mgm') ?></b>		
		</div>
		<div class="cell">
			<select name="bk_membership_type");" class="width200px">
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
				onclick="search_earnings()" />
		</div>
	</div>
</div>
</form>

<?php mgm_box_top('Earnings', '', false);?>
<?php
	$count =count($data);
	if($count ==1 && $data[0]['subscription'] ==0 && $data['purchase'] == 0 && $data[0]['recurring'] ==0){
		echo "No reports ...!";
	}else {
?>

<div class="table width99">
	<div class="row">
		<div class="cell mgm_rp_head aleft" > <?php echo __('Date','mgm'); ?>  </div>
		<div class="cell mgm_rp_head aright" > <?php echo __('Subscription','mgm'); ?> </div>
		<div class="cell mgm_rp_head aright" > <?php echo __('Recurring','mgm'); ?> </div>
		<div class="cell mgm_rp_head aright" > <?php echo __('Pay Per Post','mgm') ?> </div>
		<div class="cell mgm_rp_head aright" > <?php echo __('Total Earnings','mgm') ?> </div>
	</div>


<?php

	$system_obj = mgm_get_class('system');	
	$currency_symbol = mgm_get_currency_symbols($system_obj->setting['currency']);

	$total_subscription=0;
	$total_recurring=0;
	$total_post=0;
	$total_amount=0;
	foreach ($data as $row) {

		if(trim($row['subscription']) !=0 || trim($row['purchase']) !=0 || trim($row['recurring']) !=0 ) {
			 $timestamp = strtotime($row['date']);
			 $total_earnings = ($row['purchase'] + $row['subscription'] + $row['recurring']);
			 $flot_datas_subscription[] = '['.($timestamp*1000).','.$row['subscription'].']';
			 $flot_datas_recurring[] = '['.($timestamp*1000).','.$row['recurring'].']';
			 $flot_datas_purchase[] = '['.($timestamp*1000).','.$row['purchase'].']';
			 $flot_datas_total_earnings[] = '['.($timestamp*1000).','.$total_earnings.']';
			  // Calculating the column total
			 $total_subscription = $total_subscription + $row['subscription'];
			 $total_recurring = $total_recurring + $row['recurring'];
			 $total_post = $total_post + $row['purchase'];
			 $total_amount = $total_amount + $total_earnings;
			 $date = date($sformat, strtotime( $row['date']));	
	 
?>
	<div class="row">
		<div class="cell mgm_rp_val aleft" ><?php echo $date; ?>  </div>
		<div class="cell mgm_rp_val " ><?php echo $currency_symbol.' '.number_format($row['subscription'],2); ?> </div>
		<div class="cell mgm_rp_val " ><?php echo $currency_symbol.' '.number_format($row['recurring'],2); ?> </div>
		<div class="cell mgm_rp_val " ><?php echo $currency_symbol.' '.number_format($row['purchase'],2); ?> </div>
		<div class="cell mgm_rp_val " ><?php echo $currency_symbol.' '.number_format($total_earnings,2); ?> </div>
	</div>  
<?php } } ?>
	<div class="row">
		<div class="cell">&nbsp;</div>
	</div>
	<div class="row">
		<div class="cell mgm_rp_head aleft" > Total: </div>
		<div class="cell mgm_rp_head " > <?php echo $currency_symbol.' '.number_format($total_subscription,2); ?> </div>
		<div class="cell mgm_rp_head " > <?php echo $currency_symbol.' '.number_format($total_recurring,2); ?> </div>
		<div class="cell mgm_rp_head " > <?php echo $currency_symbol.' '.number_format($total_post,2); ?> </div>
		<div class="cell mgm_rp_head " > <?php echo $currency_symbol.' '.number_format($total_amount,2); ?> </div>
	</div>
</div>
<?php  
		$flot_data_subscription = '['.implode(',',$flot_datas_subscription).']';
		$flot_data_recurring = '['.implode(',',$flot_datas_recurring).']';
		$flot_data_purchase = '['.implode(',',$flot_datas_purchase).']';
		$flot_data_total_earnings = '['.implode(',',$flot_datas_total_earnings).']';
?>
<?php mgm_box_bottom();?>
<br />
<?php 	
/*		$url = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=download_sales_pdf';
		
		$report_type= "earnings";
		
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
	$title = 'EARNIGS REPORT ';
	if ( !empty($data[0]['date_start']) && !empty($data[0]['date_start']) ) {
		$title .= 'FROM  ' . $data[0]['date_start'] . ' TO ' . $data[0]['date_end'];
	}
	if ( !empty($data[0]['member_type']) ) {
		if ( $data[0]['member_type'] == 'all' ) {
			$title .= ' FOR ALL MEMBERSHIP TYPES';
		} else {
			$title .= ' FOR ' . strtoupper($data[0]['member_type']) . ' MEMBERSHIP TYPE';
		}
	}

	$system_obj = mgm_get_class('system');	
	$currency_symbol = mgm_get_currency_symbols($system_obj->setting['currency']);
	
	$html ='';
	$html ='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	$html .='<br/><center>' . $title . '</center><br/><hr />' ;
	$html .='<table class="width100">
	<tr>
		<td class="mgm_rp_head">'. __('Date','mgm').' </td>
		<td class="mgm_rp_head" align="right">'.__('Subscription','mgm').'</td>
		<td class="mgm_rp_head" align="right">'.__('Recurring','mgm').'</td>
		<td class="mgm_rp_head" align="right">'. __('Pay Per Post','mgm').'</td>
		<td class="mgm_rp_head" align="right">'. __('Total Earnings','mgm').' </td>
	</tr>';

	$s_total  = 0;
	$p_total  = 0;
	$su_total = 0;
	$pu_total = 0;
	$r_total =0;
	
	foreach ($data as $row) {
			
		$total_earnings = ($row['purchase'] + $row['subscription'] + $row['recurring']);
		

		$s_total  += $row['subscription'];
		$r_total  += $row['recurring'];
		$p_total  += $row['purchase'];
		
		
			 
		$html .= '<tr>
			<td class="mgm_rp_val">'. date('d/m/Y',strtotime($row['date'])) .'</td>
			<td class="mgm_rp_val" align="right">'.$currency_symbol.''.number_format($row['subscription'],2).'</td>
			<td class="mgm_rp_val" align="right">'.$currency_symbol.''.number_format($row['recurring'],2).'</td>
			<td class="mgm_rp_val" align="right">'.$currency_symbol.''.number_format($row['purchase'],2).'</td>
			<td class="mgm_rp_val" align="right">'.$currency_symbol.''.number_format($total_earnings,2).'</td>
		</tr>';
	
	} 

	$html .= '<tr><td colspan="9">&nbsp;</td></tr>';
	
	$html .= '<tr>
		<td class="mgm_rp_head">'.__('Total : ','mgm').'</td>
		<td class="mgm_rp_head" align="right">'.$currency_symbol.''.number_format($s_total,2).'</td>
		<td class="mgm_rp_head" align="right">'.$currency_symbol.' '.number_format($r_total,2).'</td>
		<td class="mgm_rp_head" align="right">'.$currency_symbol.' '.number_format($p_total,2).'</td>
		<td class="mgm_rp_head" align="right">'.$currency_symbol.' '.number_format(($s_total+$p_total),2).' </td>
	</tr>';
	
	$html .= '</table>';

	$url =MGM_LIBRARY_URL."third_party/dompdf/earnings_report.php";

?>

<form method="post" action="<?php echo $url; ?>" name="earningsFrm" id="salesFrm">
	<textarea name="earnings" class="visibilityhidden displaynone"><?php echo $html; ?></textarea>
</form>

<script type="text/javascript">
function earnings_report(){
	document.forms["earningsFrm"].submit();
}
</script>

<center>
<a href="javascript: earnings_report()"><span class="pngfix"><?php echo __('[ Download PDF ]','mgm')?></span></a>
</center>

<?php mgm_box_top('Earnings Chart', '', false);?>
<table class="width100" border="0">
<tr>
	<td><div id="placeholder_earnings" class="width700px height300px"></div></td>
</tr>
</table>
<?php mgm_box_bottom();?>
<br/>

<script language="javascript" type="text/javascript">

jQuery(function () {
	
 	var subscription_one = <?php echo $flot_data_subscription; ?>;
 	var recurring_one = <?php echo $flot_data_recurring; ?>;
 	var pay_per_post_one = <?php echo $flot_data_purchase; ?>;
 	var total_earnings = <?php echo $flot_data_total_earnings; ?>;

	var data_one = [{label: "Subscription",data: subscription_one},
				{label: "Recurring",data: recurring_one},
				{label: "Pay Per Post",data: pay_per_post_one},
				{label: "Total Earnings ",data: total_earnings}];


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
			 timeformat: "%d/%m/%y"
			//tickSize:1
		},
		yaxis: {
			//tickSize:10,
			//tickDecimals: 2
			  min: 0
		}
	};

	var placeholder_earnings = jQuery("#placeholder_earnings");

 	jQuery.plot( placeholder_earnings , data_one, options );

});

</script>
<?php } ?>
<script class="code" type="text/javascript">

	mgm_date_picker("#frmEarnings :input[name='bk_date_start']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
	mgm_date_picker("#frmEarnings :input[name='bk_date_end']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
  
	// reload
	search_earnings=function() {
		
		jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=earnings_list', type: 'POST', cache:false, data : jQuery("#earnings-table :input").serialize(),
			beforeSend: function(){	
				// show message
				mgm_show_message('#earnings', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
			 },
			 success:function(data){
				// show message
				mgm_show_message('#earnings', {status:'success', message:'<?php _e('Search Result: ','mgm')?>'});																														
				// append 
				jQuery('#report_earnings').html(data);										 
			 }
		});
	}
</script>