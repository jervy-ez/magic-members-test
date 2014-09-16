     <div class="container grid tbpd40 refer-mob">
        <div class="grid__item two-thirds">
 <ul class="form-fields">

 	<?php echo $data['billing_info']?>

	<li class="grid__item one-half tmg20 rpd8">  
		<p class="epsilon color2 bmg12">Card Holder Name</p>
		<span class="form-border">
			<label class="form-label infield" for="mgm_card_holder_name"><?php _e('Please enter the cardholder name','mgm')?></label>
			<input autocomplete="off" type="text" value="<?php echo $data['name']?>" name="mgm_card_holder_name" id="mgm_card_holder_name" size="40" maxlength="150" class="form-text-input" />
		</span>
	</li>

	<li class="grid__item one-half tmg20 rpd8">  
		<p class="epsilon color2 bmg12">Card Number</p>
	 	<span class="form-border">
			<label class="form-label infield" for="mgm_card_number">Enter Your Card Number</label>
			<input autocomplete="off" type="text" value="" name="mgm_card_number" id="mgm_card_number" size="40" maxlength="16"  class="form-text-input" />
		</span>
	</li>	

<li class="grid__item one-third tmg20 rpd8"> 


  <p class="epsilon color2 bmg12"><?php _e('Card Expiry Date','mgm')?></p> 

						

		  <div class="grid__item one-half">
		<div class="form--select">
		<select name="mgm_card_expiry_month" id="mgm_card_expiry_month" class="select width70px">

			<?php echo mgm_make_combo_options(array('01','02','03','04','05','06','07','08','09','10','11','12'), date('m'), MGM_VALUE_ONLY)?>

		</select>
	</div></div>
	  <div class="grid__item one-half">
<div class="form--select">
		<select name="mgm_card_expiry_year" id="mgm_card_expiry_year" class="select width100px">

			<?php echo mgm_make_combo_options(range(date('Y')-1, date('Y')+10), date('Y'), MGM_VALUE_ONLY)?>

		</select>
	</div>
	</div>

	</li>		

	<li class="grid__item one-third tmg20 rpd8"> 

	 <p class="epsilon color2 bmg12">Card Security Code</p> 
	 <span class="form-border">

		<label class="form-label infield"  for="mgm_card_code"><?php _e('Card Security Code','mgm')?></label>

		<input class="form-text-input" autocomplete="off" type="text" size="4" value="" name="mgm_card_code" id="mgm_card_code" maxlength="4" />
	</span>
	</li>

	<li class="grid__item one-third tmg20 rpd8">  

	<p class="epsilon color2 bmg12"><?php _e('Card Type','mgm')?></p>

	
		<div class="form--select">
		<select name="mgm_card_type" id="mgm_card_type" class="select width250px">

		<?php echo mgm_make_combo_options($data['card_types'], 'Visa', MGM_KEY_VALUE)?>

		</select>
	</div>

	</li>




	</ul>
 <div class="grid__item one-half tmg20 rpd8">
                </div>
                <div class="grid__item one-half tmg20 lpd8">


		<button type="submit" id="payment-button" onClick="return mgm_submit_cc_payment('<?php echo $data['code']?>')" class="btn btn--full">Complete <span class="icon-uniF108 delta bankcard"></span></button>

		

</div>

