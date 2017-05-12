<?php

function isValidEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidUrl($url) {
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

function isValidCardNo($cc_number) {
   /* Validate; return value is card type if valid. */
   $false = false;
   $card_type = "";
   $card_regexes = array(
      "/^4\d{12}(\d\d\d){0,1}$/" => "visa",
      "/^5[12345]\d{14}$/"       => "mastercard",
      "/^3[47]\d{13}$/"          => "amex",
      "/^6011\d{12}$/"           => "discover",
      "/^30[012345]\d{11}$/"     => "diners",
      "/^3[68]\d{12}$/"          => "diners",
   );
 
   foreach ($card_regexes as $regex => $type) {
       if (preg_match($regex, $cc_number)) {
           $card_type = $type;
           break;
       }
   }
 
   if (!$card_type) {
       return $false;
   }
 
   /*  mod 10 checksum algorithm  */
   $revcode = strrev($cc_number);
   $checksum = 0; 
 
   for ($i = 0; $i < strlen($revcode); $i++) {
       $current_num = intval($revcode[$i]);  
       if($i & 1) {  /* Odd  position */
          $current_num *= 2;
       }
       /* Split digits and add. */
           $checksum += $current_num % 10; if
       ($current_num >  9) {
           $checksum += 1;
       }
   }
 
   if ($checksum % 10 == 0) {
       return $card_type;
   } else {
       return $false;
   }
}

function displayHtmlForm($frm,$seprator='</br>'){
	$str='';
	$str .= $frm->getFormTag();
	$total_fields=$frm->getFieldCount();
	for($i=0; $i<$total_fields; $i++){
	$fld=$frm->getFieldByNumber($i);
	if($fld->getAttached()) continue;
	if($fld->fldType=='hidden'){
		$str .= $fld->getHTML();
		continue;
	}
	if($fld->merge_cells<2 && !$fld->merge_caption){
		$str .= '<fieldset><label>' . $fld->field_caption . '</label>';
		$str .= $fld->getHTML();
		$str .= '</fieldset>';
	}else{
		$str .= '<fieldset>'.$fld->field_caption.'</fieldset>';;
	}
	
	}
	$str .= '</form>';
	$str .= $frm->getExternalJS();
	if (($frm->getRequiredStarWith()=="not-required"))
		$str = preg_replace('#<span class="spn_must_field">(.*?)</span>#is', '', $str);
	return $str;
}

function displayHtmlFormTableBased($frm){
	$str='';
	$str .= $frm->getFormTag();
	$total_fields=$frm->getFieldCount();
	$str .= '<table>';
	for($i=0; $i<$total_fields; $i++){
		$fld=$frm->getFieldByNumber($i);
		if($fld->getAttached()) continue;
		if($fld->fldType=='hidden'){
			$str .= $fld->getHTML();
			continue;
		}
		$str .= '<tr><td><label>' . $fld->field_caption . '</label></td><td>'.$fld->getHTML().'</td></tr>';
	}
	$str .= '</table>';
	$str .= '</form>';
	$str .= $frm->getExternalJS();
	if (($frm->getRequiredStarWith()=="not-required"))
		$str = preg_replace('#<span class="spn_must_field">(.*?)</span>#is', '', $str);
	return $str;
}


function stripRequiredStar($str){
	return preg_replace('#<span class="spn_must_field">(.*?)</span>#is', '', $str);
}
