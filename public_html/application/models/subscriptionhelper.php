<?php
class SubscriptionHelper extends Model {
	static function getFormattedInterval($frequency,$period){
		global $duration_subscription_freq_arr;
		return $frequency.' '.$duration_subscription_freq_arr[$period];
	}
	
	static function displayFormattedSubPackage($price = 0.00 , $frequency = 0, $period="D") {
		return Utilities::displayMoneyFormat($price) .' / '. self::getFormattedInterval($frequency,$period) ;
	}
	
	/*static function displayFormattedPrice($price = 0.00) {
		//return Settings::getSetting("CONF_CURRENCY_SYMBOL") . ' ' . $price ;
		$currencySymbolLeft=html_entity_decode(CONF_CURRENCY_SYMBOL_LEFT, ENT_QUOTES, 'UTF-8');	
		$currencySymbolRight=html_entity_decode(CONF_CURRENCY_SYMBOL_RIGHT, ENT_QUOTES, 'UTF-8');
		return $currencySymbolLeft.$val.$currencySymbolRight;
			
	}*/
	static function getCurrencySymbol() {
		$currencySymbolLeft=html_entity_decode(CONF_CURRENCY_SYMBOL_LEFT, ENT_QUOTES, 'UTF-8');	
		$currencySymbolRight=html_entity_decode(CONF_CURRENCY_SYMBOL_RIGHT, ENT_QUOTES, 'UTF-8');
		return !empty($currencySymbolLeft)?$currencySymbolLeft:$currencySymbolRight ;
	}
}