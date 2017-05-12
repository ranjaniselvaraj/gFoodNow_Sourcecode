<form method="post" action="https://sandbox.gateway.payulatam.com/ppp-web-gateway/">
<?
	$reference_code='TestPayU'.time();
	$signature = md5('4Vj8eK4rloUd272L48hsrarnUA~508029~'.$reference_code.'~3~USD');
	//die($signature);
?>
  <input name="merchantId"    type="hidden"  value="508029"   >
  <input name="accountId"     type="hidden"  value="512321" >
  <input name="description"   type="hidden"  value="Test PAYU"  >
  <input name="referenceCode" type="hidden"  value="<?=$reference_code?>" >
  <input name="amount"        type="hidden"  value="3"   >
  <input name="tax"           type="hidden"  value="0"  >
  <input name="taxReturnBase" type="hidden"  value="0" >
  <input name="currency"      type="hidden"  value="USD" >
  <input name="signature"     type="hidden"  value="<?=$signature?>"  >
  <input name="test"          type="hidden"  value="1" >
  <input name="buyerEmail"    type="hidden"  value="ravibhalla@dummyid.com" >
  <input name="responseUrl"    type="hidden"  value="http://www.blank.testing.yo-kart.com/dummy/payu_response" >
  <input name="confirmationUrl"   type="hidden"  value="http://www.blank.testing.yo-kart.com/dummy/payu_confirmation" >
  <input name="Submit"        type="submit"  value="Submit" >
</form>