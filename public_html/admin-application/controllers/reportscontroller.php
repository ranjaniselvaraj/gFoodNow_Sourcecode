<?php
class ReportsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,BRANDS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Reports");
		$this->pagesize=Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
    }
	
	
	
	protected function getSalesSearchForm() {
		$frm=new Form('frmSalesSearch','frmSalesSearch');
		$frm->setAction(Utilities::generateUrl("reports","listSales"));
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchSales(this); return false;');
        return $frm;
    }
	
	function sales() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSalesSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listSales($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
			$currencyObj = new Currencies();
			$post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array('status'=>(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"),'pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$sales_data = $this->Reports->getSalesReportData($criteria);
			if($post['export']=="Y") {
				$sheet1=array();
				$arr = array('Serial No.','Date','No. of Orders', 'No. of Qty.','Sub Total','Tax','Shipping','Total','Refunded Qty.','Refunded Amount','Refunded Tax','Sales Earnings');
				array_push($sheet1,$arr);
				foreach ($sales_data as $key=>$row) {
					$sn++;	
					$arr = array($sn,$row['order_date'], $row['totOrders'],$row["totQtys"], $currencyObj->format($row["tot_cart_total"],$row["order_currency_code"],$row["order_currency_value"]), $currencyObj->format($row["tot_tax_charged"],$row["order_currency_code"],$row["order_currency_value"]), $currencyObj->format($row["tot_shipping"],$row["order_currency_code"],$row["order_currency_value"]), $currencyObj->format($row["tot_net_charged"],$row["order_currency_code"],$row["order_currency_value"]),($row["totRefundedQtys"]),$currencyObj->format($row["tot_refunded_amount"],$row["order_currency_code"],$row["order_currency_value"]),$currencyObj->format($row["tot_refunded_tax"],$row["order_currency_code"],$row["order_currency_value"]), $currencyObj->format($row["tot_sales_earnings"],$row["order_currency_code"],$row["order_currency_value"]));
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Sales_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
		
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $sales_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
    function salesdetails($dt,$code) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Sales Report", Utilities::generateUrl("reports","sales"));
		$this->b_crumb->add("Sales Detail Report",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		
		$currencyObj = new Currencies();
		$criteria=array("status"=>(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"),"date"=>$dt,"code"=>$code);
		$sales_data=$this->Reports->getSalesDetailReportData($criteria);
		$get = (array) Utilities::getUrlQuery();
		if(isset($get['export'])) {
			$sheet1=array();
			$arr = array('#','Invoice Number','Customer', 'No. of Qty','Sub Total','Tax','Shipping','Total','Refunded Qty.','Refunded Amount','Refunded Tax','Sales Earnings');
			array_push($sheet1,$arr);
			foreach ($sales_data as $key=>$row) {
				$sn++;	
				$arr = array($sn,$row['opr_order_invoice_number'], $row['order_user_name'],$row["opr_qty"], $currencyObj->format($row["cart_total"],$row["order_currency_code"],$row["order_currency_value"]), $currencyObj->format($row["tax_charged"],$row["order_currency_code"],$row["order_currency_value"]), $currencyObj->format($row["opr_shipping_charges"],$row["order_currency_code"],$row["order_currency_value"]), $currencyObj->format($row["opr_net_charged"],$row["order_currency_code"],$row["order_currency_value"]),$row["opr_refund_qty"],$currencyObj->format($row["opr_refund_amount"],$row["order_currency_code"],$row["order_currency_value"]),$currencyObj->format($row["opr_refund_tax"],$row["order_currency_code"],$row["order_currency_value"]), $currencyObj->format($row["net_sales_earnings"],$row["order_currency_code"],$row["order_currency_value"]));
				array_push($sheet1,$arr);
			}
			Utilities::convert_to_csv($sheet1, 'Sales_Detail_Report_'.date("Y-m-d").'.csv', ',');
			die();
		}
        $this->set('arr_listing',$sales_data);
		$this->set('date', $dt);
        $this->_template->render();
    }
	
	protected function getUsersSearchForm() {
		$frm=new Form('frmUsersSearch','frmUsersSearch');
		$frm->setAction(Utilities::generateUrl("reports","listUsers"));
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchUsers(this); return false;');
        return $frm;
    }
	
	function users() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getUsersSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listUsers($page = 1) {
		global $conf_arr_buyer_seller_advertiser_types;
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
			$post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array('type'=>array("3","4","5"),'pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$users_data = $this->Reports->getUsersReportData($criteria);
			if($post['export']=="Y") {
				$sheet1=array();
				$arr = array('#','Name','Email','Type','Date','Bought Qty','Sold Qty','Orders Placed','Orders Received','Purchases','Sales','Balance');
				array_push($sheet1,$arr);
				foreach ($users_data as $key=>$row) {
					$sn++;	
					$arr = array($sn,$row['user_name'], $row['user_email'], $conf_arr_buyer_seller_advertiser_types[$row['user_type']],Utilities::formatDate($row["user_added_on"]),$row["totUserOrderQtys"], $row["totSoldQty"], $row["totUserOrders"], $row["totVendorOrders"], Utilities::displayMoneyFormat($row["totUserOrderPurchases"]), Utilities::displayMoneyFormat($row["totalVendorSales"]),Utilities::displayMoneyFormat($row["totUserBalance"]));
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Users_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
		
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $users_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	protected function getProductsSearchForm() {
		$sObj=new Shops();
		$cObj=new Categories();
		$bObj=new Brands();
		$frm=new Form('frmProductsSearch','frmProductsSearch');
		$frm->setAction(Utilities::generateUrl("reports","listProducts"));
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Product Shop', 'shop',$sObj->getAssociativeArray(4),'', 'class="small" ');
		$frm->addSelectBox('Product Brand', 'brand',$bObj->getAssociativeArray(),'', 'class="small" ');
		$frm->addSelectBox('Category', 'category', $cObj->getCategoriesAssocArray(), '', 'class="small"', 'Select');
		$frm->addTextBox('Price From', 'minprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('Price To', 'maxprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchProducts(this); return false;');
        return $frm;
    }
	
	function products() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getProductsSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listProducts($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array('pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$products_data = $this->Reports->getProductsReportData($criteria);
			if($post['export']=="Y") {
				$sheet1=array();
				$arr = array('#','Title','Unit Price', 'No. of Orders','Sold Qty','Total (A)','Shipping (B)','Tax (C)','Total (A+B+C)','Commission');
				array_push($sheet1,$arr);
				foreach ($products_data as $key=>$row) {
					$sn++;	
					$arr = array($sn,$row['prod_name'], Utilities::displayMoneyFormat($row["prod_sale_price"]),is_null($row["totOrders"])?0:$row["totOrders"], is_null($row["totSoldQty"])?0:$row["totSoldQty"], Utilities::displayMoneyFormat($row["total"]), Utilities::displayMoneyFormat($row["shipping"]),Utilities::displayMoneyFormat($row["tax"]), Utilities::displayMoneyFormat($row["sub_total"]),Utilities::displayMoneyFormat($row["commission"]));
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Products_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
		
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $products_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	protected function getShopsSearchForm() {
		$frm=new Form('frmShopsSearch','frmShopsSearch');
		$frm->setAction(Utilities::generateUrl("reports","listShops"));
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchShops(this); return false;');
        return $frm;
    }
	
	function shops() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getShopsSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listShops($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array('pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$shops_data = $this->Reports->getShopsReportData($criteria);
			if($post['export']=="Y") {
				$sheet1=array();
				$arr = array('#','Name','Shop Owner', 'Items','Sold Qty','Sales','Site Commissions','Reviews','Rating');
				array_push($sheet1,$arr);
				foreach ($shops_data as $key=>$row) {
					$sn++;	
					$arr = array($sn,$row['shop_name'],$row['shop_owner'],$row["totProducts"], $row["totSoldQty"], Utilities::displayMoneyFormat($row["sub_total"]), Utilities::displayMoneyFormat($row["commission"]),$row["totReviews"], $row["shop_rating"]);
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Shops_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
		
            $pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $this->set('records', $shops_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	protected function getTaxReportSearchForm() {
		$frm=new Form('frmTaxSearch','frmTaxSearch');
		$frm->setAction(Utilities::generateUrl("reports","listTax"));
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchTax(this); return false;');
        return $frm;
    }
	
	function tax() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getTaxReportSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listTax($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array("status"=>(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"),'pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$tax_data = $this->Reports->getTaxReportData($criteria);
			if($post['export']=="Y") {
				$sheet1=array();
				$arr = array('#','Customer Name','Orders', 'Tax');
				array_push($sheet1,$arr);
				foreach ($tax_data as $key=>$row) {
					$sn++;	
					$arr = array($sn,$row['order_user_name'],$row['totbuyerorders'], Utilities::displayMoneyFormat($row["tax"]));
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Tax_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
			$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $this->set('records', $tax_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	protected function getCommissionsReportSearchForm() {
		$frm=new Form('frmCommissionsSearch','frmCommissionsSearch');
		$frm->setAction(Utilities::generateUrl("reports","listCommissions"));
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchCommissions(this); return false;');
		return $frm;
	}
	
	function commissions() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getCommissionsReportSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listCommissions($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array("status"=>(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"),'pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$commissions_data = $this->Reports->getCommissionsReportData($criteria);
			if($post['export']=="Y") {
				$sheet1=array();
				$arr = array('#','Shop Name','Orders', 'Sales','Commission');
				array_push($sheet1,$arr);
				foreach ($commissions_data as $key=>$row) {
					$sn++;	
					$arr = array($sn,$row['opr_product_shop_name'],$row['totvendororders'], Utilities::displayMoneyFormat($row["sales"]), Utilities::displayMoneyFormat($row["commission"]));
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Commissions_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
			$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $this->set('records', $commissions_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	protected function getAffiliatesSearchForm() {
		$frm=new Form('frmAffiliateSearch','frmAffiliateSearch');
		$frm->setAction(Utilities::generateUrl("reports","listAffiliates"));
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchAffiliates(this); return false;');
		return $frm;
		
	}
	
	function affiliates() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getAffiliatesSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listAffiliates($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array('status'=>(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"),'pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$affiliates_data = $this->Reports->getAffiliatesReportData($criteria);
			if($post['export']=="Y") {
				$sheet1=array();
				$arr = array('#','Name','Email','Date','Account Balance','Revenue','Commission Received','Commission Pending','Signups');
				array_push($sheet1,$arr);
				foreach ($affiliates_data as $key=>$row) {
					$sn++;	
					$arr = array($sn,$row['affiliate_name'], $row['affiliate_email'],Utilities::formatDate($row["affiliate_added_on"]),Utilities::displayMoneyFormat($row["balance"]), Utilities::displayMoneyFormat($row["revenue"]),Utilities::displayMoneyFormat($row["received"]),Utilities::displayMoneyFormat($row["pending"]),$row["signups"]);
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Affiliate_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
			$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $this->set('records', $affiliates_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	protected function getAdvertisersSearchForm() {
		$frm=new Form('frmAdvertisersSearch','frmAdvertisersSearch');
		$frm->setAction(Utilities::generateUrl("reports","listAdvertisers"));
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchAdvertisers(this); return false;');
        return $frm;
    }
	
	function advertisers() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getAdvertisersSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listAdvertisers($page = 1) {
		global $conf_arr_buyer_seller_advertiser_types;
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        //if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
			$post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array('type'=>array("1"),'pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$users_data = $this->Reports->getAdvertisersReportData($criteria);
			if($post['export']=="Y") {
				$sheet1=array();
				$arr = array('#','Name','Email','Date','Promotions','Balance');
				array_push($sheet1,$arr);
				foreach ($users_data as $key=>$row) {
					$sn++;	
					$arr = array($sn,$row['user_name'], $row['user_email'],Utilities::formatDate($row["user_added_on"]),$row["totPromotions"],Utilities::displayMoneyFormat($row["totUserBalance"]));
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Advertisers_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
		
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $users_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        //}
    }
	
	protected function getPromotionsSearchForm() {
		$sObj=new Shops();
		$cObj=new Categories();
		$bObj=new Brands();
		$frm=new Form('frmPromotionsSearch','frmPromotionsSearch');
		$frm->setAction(Utilities::generateUrl("reports","listPromotions"));
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$frm->addHiddenField('', 's');
		$frm->addHiddenField('', 'user', "",'','user');
		$frm->addHiddenField('', 'mode', "search");
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$frm->addSelectBox('Approved', 'approved',array(1=>"Yes",0=>"No"),'' , 'class="small"','All');
		$fld_from=$frm->addTextBox('Impressions From (Number)', 'impressions_from')->requirements()->setIntPositive($val=true);
		$fld_to = $frm->addTextBox('Impressions To (Number)', 'impressions_to')->requirements()->setIntPositive($val=true);
		$frm->addSelectBox('Type', 'type',array("1"=>"Product","2"=>"Shop","3"=>"Banners"),'' , 'class="small"','All');
		$fld_from=$frm->addTextBox('Clicks From (Number)', 'clicks_from')->requirements()->setIntPositive($val=true);
		$fld_to = $frm->addTextBox('Clicks To (Number)', 'clicks_to')->requirements()->setIntPositive($val=true);
		$fld=$frm->addTextBox('Promotion By', 'promotion_by');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchPromotions(this); return false;');
        return $frm;
    }
	
	function promotions() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getPromotionsSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listPromotions($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array('pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$promotions_data = $this->Reports->getPromotionsReportData($criteria);
			if($post['export']=="Y") {
				global $duration_freq_arr;
				$sheet1=array();
				$arr = array('#','PROMOTER','TITLE', 'TYPE','CPC','BUDGET','IMPRESSIONS','CLICKS','PAYMENTS','DURATION','APPROVED');
				array_push($sheet1,$arr);
				foreach ($promotions_data as $key=>$row) {
					$sn++;
					if ($row['promotion_type']==1) { 
                       $promotion_title= $row["prod_name"];
					   $promotion_type = "Product";
                    } elseif ($row['promotion_type']==2) {
                       $promotion_title = $row["shop_name"];
					   $promotion_type = "Shop";
                    } elseif ($row['promotion_type']==3) {
                        $promotion_title = $row["promotion_banner_name"];
						$promotion_type = "Banner";
                    }
							
					$arr = array(
								$sn,
								$row['user_name'],
								$promotion_title,
								$promotion_type,
								Utilities::displayMoneyFormat($row["promotion_cost"]), 
								Utilities::displayMoneyFormat($row["promotion_budget"]).'/'.$duration_freq_arr[$row["promotion_budget_period"]],
								$row["totImpressions"], 
								$row["totClicks"],
								Utilities::displayMoneyFormat($row["totPayments"]), 
								Utilities::formatDate($row["promotion_start_date"]).'-'.Utilities::formatDate($row["promotion_end_date"]).' Time Slot '.date(date('H:i',strtotime($row["promotion_start_time"]))).'-'.date(date('H:i',strtotime($row["promotion_end_time"]))),
								$row["promotion_is_approved"]?'Yes':'No'  
								);
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Promotions_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
		
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $promotions_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	protected function getSubscriptionsSearchForm() {
		$sObj=new Shops();
		$cObj=new Categories();
		$bObj=new Brands();
		$frm=new Form('frmSubscriptionsSearch','frmSubscriptionsSearch');
		$frm->setAction(Utilities::generateUrl("reports","listSubscriptions"));
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'export');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'subscriber', "",'','subscriber');
		$fld=$frm->addTextBox('Subscriber', 'subscriber_name');
		$frm->addSelectBox('Subscription Status', 'subscription_status', SubscriptionOrders::subscription_status_arr(), '', 'class="small"', 'Select');
		
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchSubscriptions(this); return false;');
        return $frm;
    }
	
	function subscriptions() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSubscriptionsSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listSubscriptions($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $post = Syspage::getPostedVar();
			if($post['export']=="Y") {
				$post["nolimit"]=1;
			}
			$arr = array('pagesize'=>$this->pagesize);
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
			$criteria = array_merge($post,$arr);
			$promotions_data = $this->Reports->getSubscriptionsReportData($criteria);
			if($post['export']=="Y") {
				$sheet1=array();
				$arr = array('#','Invoice','Subscriber', 'Subscription Date','Plan','Status','No. of Payments','Total');
				array_push($sheet1,$arr);
				foreach ($promotions_data as $key=>$row) {
					$sn++;
					$arr = array(
								$sn,
								$row['mporder_invoice_number'],
								$row['mporder_user_name'],
								Utilities::formatDate($row["mporder_date_added"],true),
								$row['mporder_merchantpack_name'].' - '.$row['mporder_merchantsubpack_name'],
								$row['sorder_status_name'],
								$row["totPaymentRecords"],
								Utilities::displayMoneyFormat($row["totPayments"]) 
								);
					array_push($sheet1,$arr);
				}
				Utilities::convert_to_csv($sheet1, 'Subscriptions_Report_'.date("Y-m-d").'.csv', ',');
				die();
			}
		
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $promotions_data);
            $this->set('pages', $this->Reports->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Reports->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
}