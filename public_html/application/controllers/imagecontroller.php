<?php
class ImageController extends Controller{
	function default_action(){
		exit('Invalid request!!');
	}
	function site_admin_logo($type){
		
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage(Settings::getSetting("CONF_ADMIN_LOGO"), 172, 55,'','product-no-image.jpg',false);
			break;
			case 'MINI':
			return Utilities::showImage(Settings::getSetting("CONF_ADMIN_LOGO"), 80, 80,'','product-no-image.jpg',false);
			break;
			default:
			return Utilities::showOriginalImage(Settings::getSetting("CONF_ADMIN_LOGO"),'',Settings::getSetting("CONF_ADMIN_LOGO"));
//return Utilities::showImage($img, 200, 300,'products/','product-no-image.jpg',true);
		}
	}
	function site_email_logo($img = ''){
		$img=$img!=""?$img:Settings::getSetting("CONF_FRONT_LOGO");
		return Utilities::showOriginalImage($img);
	}
	function site_logo($img = '',$type){
		$img=$img!=""?$img:Settings::getSetting("CONF_FRONT_LOGO");
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 172, 55,'','product-no-image.jpg',false);
			break;
			case 'MINI':
			return Utilities::showImage($img, 80, 80,'','product-no-image.jpg',false);
			break;
			default:
			return Utilities::showOriginalImage($img);
//return Utilities::showImage($img, 200, 300,'products/','product-no-image.jpg',true);
		}
	}
	function mobile_icon_logo($img = '',$type){
		switch(strtoupper($type)){
			case 'MINI':
			return Utilities::showImage($img, 60, 60,'','product-no-image.jpg',false);
			break;
			default:
			return Utilities::showOriginalImage($img);
		}
	}
	function site_favicon($img = '',$type){
		switch(strtoupper($type)){
			case 'MINI':
			return Utilities::showImage($img, 60, 60,'','product-no-image.jpg',false);
			break;
			default:
			return Utilities::showOriginalImage($img);
		}
	}
	function apple_touch_icon($img = '',$type){
		switch(strtoupper($type)){
			case 'MINI':
			return Utilities::showImage($img, 60, 60,'','product-no-image.jpg',false);
			break;
			default:
			return Utilities::showOriginalImage($img);
		}
	}
	function footer_logo_graphic($img = '',$type){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 268, 82,'','product-no-image.jpg',false);
			break;
			default:
			return Utilities::showOriginalImage($img);
		}
	}
	function social_feed_image($img = '',$type){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 100, '80','','product-no-image.jpg',false);
			break;
			case 'MEDIUM':
			return Utilities::showImage($img, 300, 300,'','product-no-image.jpg',false);
			break;
			default:
			return Utilities::showOriginalImage($img);
		}
	}
	function admin_dashboard_graph($img = ''){
		return Utilities::showOriginalImage($img,'graphs/');
	}
	function last_12_month_sales($width=false, $height=false) {
		Utilities::outputImage('graphs/monthlysales.png', $width, $height, '', false);
	}
	function last_12_month_sales_earnings($width=false, $height=false) {
		Utilities::outputImage('graphs/monthlysalesearnings.png', $width, $height, '', false);
	}
	function last_12_month_signups($width=false, $height=false) {
		Utilities::outputImage('graphs/monthly-signups.png', $width, $height, '', false);
	}
	function last_12_month_products($width=false, $height=false) {
		Utilities::outputImage('graphs/products-listed.png', $width, $height, '', false);
	}
	function watermark_logo($width=false, $height=false) {
		Utilities::outputImage('watermark-image', $width, $height, '', false);
	}
	function watermark($img = ''){
		return Utilities::showImage($img, 60, 60,'','product-no-image.jpg',false);
	}
	function product_image($product_id,$type = 'large'){
		$product=new Products();
		$product=$product->getProductDefaultImage($product_id);
		$img=$product["image_file"];
		switch(strtoupper($type)){
			case 'MINI':
			return Utilities::showImage($img, 47, 47,'products/','product-no-image.jpg',false);
			break;
			case 'THUMB':
			return Utilities::showImage($img, 100, 100,'products/','product-no-image.jpg',false);
			break;
			case 'MEDIUM':
			return Utilities::showImage($img, 280, 280,'products/','product-no-image.jpg',true);
			break;	
			case 'LARGE':
			return Utilities::showImage($img, 400, 400,'products/','product-no-image.jpg',true);
			break;
			case 'ORIGINAL':
			return Utilities::showOriginalImage($img,'products/');
			break;	
			default:
			return Utilities::showOriginalImage($img,'products/');
		}
	}
	function product($type = 'large', $img = ''){
		switch(strtoupper($type)){
			case 'MINI':
			return Utilities::showImage($img, 47, 47,'products/','product-no-image.jpg',false);
			break;
			case 'THUMB':
			return Utilities::showImage($img, 100, 100,'products/','product-no-image.jpg',false);
			break;
			case 'MEDIUM':
			return Utilities::showImage($img, 280, 280,'products/','product-no-image.jpg',true);
			break;
			case 'LARGE':
			return Utilities::showImage($img, 400, 400,'products/','product-no-image.jpg',true);
			break;
			case 'BIG':
			return Utilities::showImage($img, 0, 0,'products/','product-no-image.jpg',false);
			break;
			case 'ORIGINAL':
			return Utilities::showOriginalImage($img,'products/');
			break;	
			default:
			return Utilities::showOriginalImage($img,'products/');
		}
	}
	function coupon($type = 'THUMB', $img = ''){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 100, 100,'coupons/','product-no-image.jpg',false);
			break;
			case 'FRONT':
			return Utilities::showImage($img, 150, 150,'coupons/','product-no-image.jpg',false);
			break;
			default:
			return Utilities::showOriginalImage($img,'coupons/');
		}
	}
	function collection($img = '',$type = 'THUMB'){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 100, 100,'collections/','product-no-image.jpg',false);
			break;
			default:
			return Utilities::showOriginalImage($img,'collections/');
		}
	}
	function shop_logo($img = '', $type = ''){
		$type=$type==""?"THUMB":$type;
		switch(strtoupper($type)){
			case 'MINI':
			return Utilities::showImage($img, 50, 50,'shops/logo/','shop_default_logo.jpg');
			break;
			case 'THUMB':
			return Utilities::showImage($img, 166, 166,'shops/logo/','shop_default_logo.jpg');
			break;
			case 'MEDIUM':
			return Utilities::showImage($img, 252, 218,'shops/logo/','shop_default_logo.jpg');
			break;	
			case 'LARGE':
			return Utilities::showImage($img, 390, 480,'shops/logo/','shop_default_logo.jpg');
			break;
			default:
			return Utilities::showOriginalImage($img,'shops/logo/','product-no-image.jpg');
		}
	}
	function category($type = 'THUMB', $img = ''){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 100, 100,'categories/','product-no-image.jpg');
			break;
			case 'LARGE':
			return Utilities::showImage($img, 500, 500,'categories/','product-no-image.jpg');
			break;
		}
	}
	function shop_banner($img = '',$type = 'large'){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 100, 100,'shops/banner/','shop_default.jpg');
			break;
			case 'LARGE':
			return Utilities::showImage($img, 1200, 361,'shops/banner/','shop_default.jpg');
			break;
			default:
			return Utilities::showOriginalImage($img,'shops/banner/');
		}
	}
	function testimonial_image($img = '',$type){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 100, 100,'testimonials/','default.jpg');
			break;
			default:
			return Utilities::showImage($img, 200, 200, 'testimonials/','default.jpg');
		}
	}
	function slide($img = '',$type){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 100, 50,'slides/','shop_default.jpg');
			break;
			case 'NORMAL':
			return Utilities::showImage($img, 1200, 382,'slides/','shop_default.jpg');
			break;	
			default:
			return Utilities::showOriginalImage($img,'slides/');
		}
	}
	function payment_icon($img = '',$type){
		switch(strtoupper($type)){
			default:
			return Utilities::showImage($img, 22, 22,'paymentmethods/','creditcard.png');
		}
	}
	function ppcpayment_icon($img = '',$type){
		switch(strtoupper($type)){
			default:
			return Utilities::showImage($img, 22, 22,'ppcpaymentmethods/','creditcard.png');
		}
	}
	function subscriptionpayment_icon($img = '',$type){
		switch(strtoupper($type)){
			default:
			return Utilities::showImage($img, 22, 22,'subscriptionpaymentmethods/','creditcard.png');
		}
	}
	function banner($img = '',$type){
		switch(strtoupper($type)){
			case 'THUMB':
				return Utilities::showImage($img, 100, 50,'banners/','shop_default.jpg');
			break;
			default:
			return Utilities::showOriginalImage($img,'banners/');
		}
	}
	function emptycartitems($img = '',$type){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 100, 50,'emptycartitems/','shop_default.jpg');
			break;
			default:
			return Utilities::showOriginalImage($img,'emptycartitems/');
		}
	}
	function user($img = '',$type){
		$type=$type==""?"SMALL":$type;
		switch(strtoupper($type)){
			case 'SMALL':
			case 'THUMB':
			return Utilities::showImage($img, 80, 80,'user-avatar/','default.jpg');
			break;
			case 'MEDIUM':
			return Utilities::showImage($img, 150, 150,'user-avatar/','default.jpg');
			break;
			case 'LARGE':
			return Utilities::showImage($img, 500, 500,'user-avatar/','default.jpg');
			break;
			default:
			return Utilities::showOriginalImage($img,'user-avatar/');
		}
	}	
	function user_photo($img = '',$type){
		return Utilities::showImage($img,0,0,'user-avatar/','default.jpg');
	}
	function affiliate($img = '',$type){
		$type=$type==""?"SMALL":$type;
		switch(strtoupper($type)){
			case 'SMALL':
			case 'THUMB':
			return Utilities::showImage($img, 80, 80,'affiliates/','default.jpg');
			break;
			default:
			return Utilities::showOriginalImage($img,'affiliates/');
		}
	}	
	function affiliate_photo($img = ''){
		return Utilities::showImage($img,0,0,'affiliates/','default.jpg');
	}
	function advertiser_photo($img = ''){
		return Utilities::showImage($img,0,0,'advertisers/','default.jpg');
	}
	function advertisers($img = '',$type){
		$type=$type==""?"SMALL":$type;
		switch(strtoupper($type)){
			case 'SMALL':
			case 'THUMB':
			return Utilities::showImage($img, 80, 80,'advertisers/','default.jpg');
			break;
			default:
			return Utilities::showOriginalImage($img,'advertisers/');
		}
	}	
	function social_platform_icon($img = '',$type){
		switch(strtoupper($type)){
			default:
			return Utilities::showOriginalImage($img,'socialplatforms/');
		}
	}
	function promotion_banner($img = '',$type){
		switch(strtoupper($type)){
			case 'THUMB':
			return Utilities::showImage($img, 60, 60,'promotions/','shop_default.jpg');
			break;
			default:
			return Utilities::showOriginalImage($img,'promotions/');
		}
	}
	function post($type = 'large', $img = '') {
		switch (strtoupper($type)) {
			case 'THUMB':
			return Utilities::showImage($img, 120, 60, 'post-images/', 'no-img.jpg');
			break;
			case 'LARGE':
			return Utilities::showImage($img, 800, 400, 'post-images/', 'no-img.jpg');
			break;
			default:
			return Utilities::showImage($img, 148, 148, 'post-images/', 'no-img.jpg');
		}
	}
}
