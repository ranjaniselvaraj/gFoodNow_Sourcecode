<?php
class Applicationconstants{
	public static $admin_dashboard_layout=array(0=>'default', 1=>'switch_layout');
	public static $arr_status = array(
		 '1' => 'Active',
		 '0' => 'Inactive'
    );
	public static $is_approved = array('0' => 'No', '1' => 'Yes');
    public static $post_status = array('0' => 'Draft', '1' => 'Published');
	public static $discount_valid_for=array(1=>'One Time', 2=>'Recurring');
}