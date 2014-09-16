<?php 
	mgm_box_top('Sales Report. ');
	$sformat = mgm_get_date_format('date_format_short');
?>
<form name="frmSales" id="frmSales" method="POST" action="" class="marginpading0px">
<div class="table form-table widefat" id="sales-table">
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
    <div class="cell">
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
						$strTypes .= '<option value="'. $type_code .'" SELECTED>'. __(mgm_stripslashes_deep($type_name), 'mgm') .$d[0]['cost'].'</option>';
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
			onclick="search_sales_list()" />    </div>
  </div>
</div>
</form>
<?php mgm_box_top('Sales', '', false);?>
<?php
	$count =count($data);
	if($count ==1 && $data[0]['subscription'] ==0 && $data['purchase'] == 0){
		echo "No Results ...!";
	}else {
		
?>

<div class="table">
	<div class="row">
		<div class="cell mgm_rp_head aleft" > <?php echo __('Date','mgm'); ?>  </div>
		<div class="cell mgm_rp_head " > <?php echo __('Subscription','mgm'); ?>	</div>
		<div class="cell mgm_rp_head " > <?php echo __('Pay Per Post','mgm') ?> </div>
		<div class="cell mgm_rp_head " > <?php echo __('Total Sales','mgm') ?>	</div>
		<div class="cell mgm_rp_head " style="width:5%;"> &nbsp; </div>
		<div class="cell mgm_rp_head aleft" > <?php echo __('Date','mgm'); ?> </div>
		<div class="cell mgm_rp_head " > <?php echo __('Subscription','mgm'); ?> </div>
		<div class="cell mgm_rp_head " > <?php echo __('Pay Per Post','mgm') ?> </div>
		<div class="cell mgm_rp_head " style="width:15%;"> <?php echo __('Total Order Count','mgm') ?> </div>
	</div>  

<?php 
	//totals
	$s_total  = 0;
	$p_total  = 0;
	$su_total = 0;
	$pu_total = 0;

	$system_obj = mgm_get_class('system');	
	$currency_symbol = mgm_get_currency_symbols($system_obj->setting['currency']);
	
	foreach ($data as $row) {

		if(trim($row['subscription']) !=0 || trim($row['purchase']) !=0 ) {

			$timestamp = strtotime($row['date']);
			 
			 $total_sales = ($row['purchase'] + $row['subscription'] );
			 $flot_datas_subscription[] = '['.($timestamp*1000).','.$row['subscription'].']';
			 $flot_datas_purchase[] = '['.($timestamp*1000).','.$row['purchase'].']';
			 $flot_datas_total_sales[] = '['.($timestamp*1000).','.$total_sales.']';
	
			 $order_total = ($row['pcount'] + $row['scount'] );
			 $flot_datas_scount[] = '['.($timestamp*1000).','.$row['scount'].']';
			 $flot_datas_pcount[] = '['.($timestamp*1000).','.$row['pcount'].']';
			 $flot_datas_order_total[] = '['.($timestamp*1000).','.$order_total.']';
			 
			 $s_total  += $row['subscription'];
			 $p_total  += $row['purchase'];
			 $su_total += $row['scount'];
			 $pu_total += $row['pcount'];
	
			 $date = date($sformat, strtotime( $row['date']));	
?>	
	<div class="row">
		<div class="cell mgm_rp_val aleft" > <?php echo $date; ?>  </div>
		<div class="cell mgm_rp_val " > <?php echo $currency_symbol.' '.number_format($row['subscription'],2); ?> </div>
		<div class="cell mgm_rp_val " > <?php echo $currency_symbol.' '.number_format($row['purchase'],2); ?> </div>
		<div class="cell mgm_rp_val " > <?php echo $currency_symbol.' '.number_format($total_sales,2); ?> </div>
		<div class="cell mgm_rp_val " style="width:5%;"> &nbsp; </div>
		<div class="cell mgm_rp_val aleft" > <?php echo $date; ?> </div>
		<div class="cell mgm_rp_val " > <?php echo $row['scount']; ?> </div>
		<div class="cell mgm_rp_val " > <?php echo $row['pcount']; ?> </div>
		<div class="cell mgm_rp_val " style="width:15%;"> <?php echo $order_total; ?> </div>
	</div>  

<?php } } ?>
	<div class="row">
		<div class="cell mgm_rp_head aleft" > <?php echo __('Total : ','mgm'); ?>  </div>
		<div class="cell mgm_rp_head " > <?php echo $currency_symbol.' '.number_format($s_total,2); ?> </div>
		<div class="cell mgm_rp_head " > <?php echo $currency_symbol.' '.number_format($p_total,2); ?> </div>
		<div class="cell mgm_rp_head " > <?php echo $currency_symbol.' '.number_format(($s_total+$p_total),2); ?> </div>
		<div class="cell mgm_rp_head " style="width:5%;"> &nbsp; </div>
		<div class="cell mgm_rp_head aleft" > &nbsp; </div>
		<div class="cell mgm_rp_head " > <?php echo $su_total; ?> </div>
		<div class="cell mgm_rp_head " > <?php echo $pu_total; ?> </div>
		<div class="cell mgm_rp_head " style="width:15%;"> <?php echo ($su_total+$pu_total); ?> </div>
	</div>  
	
</div>
<?php  
		$flot_data_subscription = '['.implode(',',$flot_datas_subscription).']';
		$flot_data_purchase = '['.implode(',',$flot_datas_purchase).']';
		$flot_data_total_sales = '['.implode(',',$flot_datas_total_sales).']';

		$flot_data_scount = '['.implode(',',$flot_datas_scount).']';
		$flot_data_pcount = '['.implode(',',$flot_datas_pcount).']';
		$flot_data_order_total = '['.implode(',',$flot_datas_order_total).']';

		//date format for graph genaration
		$placeholders = array('y', 'Y', 'm', 'd');
		//replace values array
		$replace_val = array('%y', '%y', '%m', '%d');
		//graph string %d/%m/%y
		$graph_date = str_replace($placeholders, $replace_val, $sformat);
?>
<?php mgm_box_bottom();?>
<br/>


<br />
<?php 	
/*		$url = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=download_sales_pdf';


		if(isset($data[0]['date_start'])){
			$url .= '&bk_date_start='.$data[0]['date_start'];
		}
		if(isset($data[0]['date_start'])){
			$url .= '&bk_date_end='.$data[0]['date_end'];
		}
		if(isset($data[0]['member_type'])){
			$url .= '&bk_membership_type='.$data[0]['member_type'];
		}
		$random_number =rand(10,100);
		$url .= '&random='.$random_number;
*/		
		

?>

<?php

	//Download PDF -Issue #763
	$title = 'SALES REPORT ';
	
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
	$html .='<table width="100%">
	<tr>
		<td width="10%" align="left"><b>'. __('Date','mgm').'</b></td>
		<td width="10%" align="right"><b>'.__('Subscription','mgm').'</b></td>
		<td width="10%" align="right"><b>'. __('Pay Per Post','mgm').'</b></td>
		<td width="10%" align="right"><b>'. __('Total Sales','mgm').'</b></td>
		<td width="5%">&nbsp;</td>
		<td width="10%" align="left"><b>'. __('Date','mgm').'</b></td>
		<td width="10%" align="right"><b>'. __('Subscription','mgm').'</b></td>
		<td width="10%" align="right"><b>'. __('Pay Per Post','mgm').'</b> </td>
		<td width="15%" align="right"><b>'. __('Total Order Count','mgm').'</b></td>
	</tr>';

	$s_total  = 0;
	$p_total  = 0;
	$su_total = 0;
	$pu_total = 0;

	$system_obj = mgm_get_class('system');	
	$currency_symbol = mgm_get_currency_symbols($system_obj->setting['currency']);

	foreach ($data as $row) {
			
		$total_sales = ($row['purchase'] + $row['subscription'] );
		$order_total = ($row['pcount'] + $row['scount'] );

		$s_total  += $row['subscription'];
		$p_total  += $row['purchase'];
		$su_total += $row['scount'];
		$pu_total += $row['pcount'];
		$sformat = mgm_get_date_format('date_format_short');
		$date = date($sformat, strtotime( $row['date']));		 
		$html .= '<tr>
			<td width="10%" align="left">'. $date .'</td>
			<td width="10%" align="right"> '.$currency_symbol.' '.number_format($row['subscription'],2).'</td>
			<td width="10%" align="right">'.$currency_symbol.' '.number_format($row['purchase'],2).'</td>
			<td width="10%" align="right">'.$currency_symbol.' '.number_format($total_sales,2).'</td>
			<td width="5%" align="right">&nbsp;&nbsp; &nbsp;</td>
			<td width="10%" align="left">'. $date .'</td>
			<td width="10%" align="right">'. $row['scount'].'</td>
			<td width="10%" align="right">'. $row['pcount'].'</td>
			<td width="15%" align="right">'. $order_total.'</td>
		</tr>';
	
	} 

	$html .= '<tr><td colspan="9">&nbsp;</td></tr>';
	
	$html .= '<tr>
		<td width="10%" align="left"><b>'.__('Total : ','mgm').'</b></td>
		<td width="10%" align="right"><b>'.$currency_symbol.' '.number_format($s_total,2).'</b></td>
		<td width="10%" align="right"><b>'.$currency_symbol.' '.number_format($p_total,2).'</b></td>
		<td width="10%" align="right"><b>'.$currency_symbol.' '.number_format(($s_total+$p_total),2).'</b> </td>
		<td width="5%" align="right">&nbsp;</td>
		<td width="10%" align="right"></td>
		<td width="10%" align="right"><b>'.$su_total.'</b></td>
		<td width="10%" align="right"><b>'.$pu_total.'</b> </td>
		<td width="15%" align="right"><b>'.($su_total+$pu_total).'</b></td>
	</tr>';
	
	$html .= '</table>';
	
	//echo $html;
	$url =MGM_LIBRARY_URL."third_party/dompdf/sales_report.php";
?>

<form method="post" action="<?php echo $url; ?>" name="salesFrm" id="salesFrm">
	<textarea name="sales" class="visibilityhidden displaynone"><?php echo $html; ?></textarea>
</form>

<script type="text/javascript">
function sales_report(){
	document.forms["salesFrm"].submit();
}
</script>
<center>
	<a href="javascript: sales_report()"><span class="pngfix"><?php echo __('[ Download PDF ]','mgm')?></span></a>
</center>

<?php mgm_box_top('Sales Chart', '', false);?>
<div class="table">
<div class="row">
	<div class="cell" ><div id="placeholder_one" class="width700px height300px"></div></div>
</div>
<div class="row">
	<div class="cell" >&nbsp;</div>
</div>
<div class="row">
	<div class="cell" ><div id="placeholder_two" class="width700px height300px"></div></div>
</div>
</div>
<?php mgm_box_bottom();?>
<br/>

<script language="javascript" type="text/javascript">

jQuery(function () {

 	var subscription_one = <?php echo $flot_data_subscription; ?>;
 	var pay_per_post_one = <?php echo $flot_data_purchase; ?>;
 	var total_sales = <?php echo $flot_data_total_sales; ?>;

	var data_one = [{label: "Subscription",data: subscription_one},
				{label: "Pay Per Post",data: pay_per_post_one},
				{label: "Total sales ",data: total_sales}];

  	var subscription_two = <?php echo $flot_data_scount; ?>;
 	var pay_per_post_two = <?php echo $flot_data_pcount; ?>;
 	var total_order = <?php echo $flot_data_order_total; ?>;

	var data_two = [{label: "Subscription",data: subscription_two},
				{label: "Pay Per Post",data: pay_per_post_two},
				{label: "Total Order Count",data: total_order}];
				
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

	var placeholder_one = jQuery("#placeholder_one");
	var placeholder_two = jQuery("#placeholder_two");

 	jQuery.plot( placeholder_one , data_one, options );
 	jQuery.plot( placeholder_two , data_two, options );

});

</script>
<?php  } ?>
<script class="code" type="text/javascript">

	mgm_date_picker("#frmSales :input[name='bk_date_start']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
	mgm_date_picker("#frmSales :input[name='bk_date_end']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
  
	// reload
	search_sales_list=function() {
		
		jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=sales_list', type: 'POST', cache:false, data : jQuery("#sales-table :input").serialize(),
			beforeSend: function(){	
				// show message
				mgm_show_message('#sales', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
			 },
			 success:function(data){
				// show message
				mgm_show_message('#sales', {status:'success', message:'<?php _e('Search Result: ','mgm')?>'});																														
				// append 
				jQuery('#report_sales').html(data);										 
			 }
		});
	}
</script>