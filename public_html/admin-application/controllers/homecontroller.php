<?php
class HomeController extends CommonController{
    
    function default_action(){
		
		$db = &Syspage::getDb();
		require_once (CONF_INSTALLATION_PATH . 'public/includes/phpfastcache.php');
		require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/analytics/analyticsapi.php');
		$analyticArr=array(
			'clientId'=>Settings::getSetting("CONF_ANALYTICS_CLIENT_ID"),
			'clientSecretKey'=>Settings::getSetting("CONF_ANALYTICS_SECRET_KEY"),
			'redirectUri'=>Utilities::generateAbsoluteUrl('configurations','redirect'),
			'googleAnalyticsID'=>Settings::getSetting("CONF_ANALYTICS_ID")
		);
		$this->set('configuredAnalytics', false);
		try{
			$analytics=new Ykart_analytics($analyticArr);		
			$token = $analytics->getRefreshToken(Settings::getSetting("CONF_ANALYTICS_ACCESS_TOKEN"));	
			$analytics->setAccessToken(isset($token['accessToken'])?$token['accessToken']:'');
			$accountId=$analytics->setAccountId(Settings::getSetting("CONF_ANALYTICS_ID"));
			if($accountId){
				$this->set('configuredAnalytics', true);
			}else{
				
				//Message::addErrorMessage('Analytic Id not exist with Configured Account.');
			}
				
			}catch(exception $e){
				//Message::addErrorMessage($e->getMessage());
		}
		// simple Caching with:
		phpFastCache::setup("storage","files");
		phpFastCache::setup("path", CONF_USER_UPLOADS_PATH."caching");
		$cache = phpFastCache();
		$dashboard_info = $cache->get("dashboard_info");
		
			if($dashboard_info == null) {
					$statsObj=new Statistics();
					if($accountId){
						$stats_info=$analytics->getVisitsByDate();
						if(!empty($stats_info['stats'])){
							$chatStats="[['Year', 'Today','Weekly','Last Month','Last 3 Month'],";
							foreach($stats_info['stats'] as $key=>$val){
								if($key==''){continue;}
									$chatStats.="['".Utilities::formatDate($key)."',".intval($val['today']['visit']).",".intval($val['weekly']['visit']).",".intval($val['lastMonth']['visit']).",".intval($val['last3Month']['visit'])."],";
								}
						}	
					$chatStats=rtrim($chatStats,',');
					$visits_chart_data= $chatStats.="]";
					$visitCount=$stats_info['result'];
					foreach($stats_info['result'] as $key=>$val){
						$visitCount[$key]=$val['totalsForAllResults'];
					}				
					$socialVisits=$analytics->getSocialVisits();						
				}
				$conversionStats=$statsObj->getConversionStats();
				$conversion_chat_data="['Type','user',{ role: 'style' }],";
				foreach($conversionStats as $key=>$val ){
					$conversion_chat_data.="['".ucwords(str_replace('_',' ',$key))."', ".$val["count"].",'#AEC785'],";
				}
				$conversion_chat_data=rtrim($conversion_chat_data,',');	
		
				$sales_data=$statsObj->getDashboardLast12MonthsSummary('sales');
								
				foreach($sales_data as $key=>$val ){
						$sales_chart_data.="['".$val["duration"]."', ".$val["value"]."],";
				}
				$sales_earnings_data=$statsObj->getDashboardLast12MonthsSummary('earnings');
				
				foreach($sales_earnings_data as $key=>$val ){
						$sales_earnings_chart_data.="['".$val["duration"]."', ".$val["value"]."],";		
				}
				$signups_data=$statsObj->getDashboardLast12MonthsSummary('signups');
				foreach($signups_data as $key=>$val ){
					$signups_chart_data.="['".$val["duration"]."', ".$val["value"]."],";
				}
				$products_data=$statsObj->getDashboardLast12MonthsSummary('products');
				foreach($products_data as $key=>$val ){
					$products_chart_data.="['".$val["duration"]."', ".$val["value"]."],";
				}
				
				
				$affiliate_signups_data=$statsObj->getDashboardLast12MonthsSummary('affiliate_signups');
				foreach($affiliate_signups_data as $key=>$val ){
					$affiliate_signups_chart_data.="['".$val["duration"]."', ".$val["value"]."],";
				}
				
				$oObj=new Orders();
				$dashboard_orders=$oObj->getOrders(array("pagesize"=>5));
				$shopObj=new Shop();
				$dashboard_shops=$shopObj->getShops(array("pagesize"=>10));
				$pObj=new Product(array("pagesize"=>10));
				$pObj->joinWithBrandsTable(array('tpb.brand_id','tpb.brand_name'));
				$pObj->addSpecialPrice();
				$pObj->addSpecialPrice();
				$dashboard_products=$pObj->getProducts(array("sort"=>"rece"));
				
				$uObj=new User();
				$dashboard_users=$uObj->getUsers(array("type"=>array("3","4","5"),"pagesize"=>10));
				$uObj=new User();
				$dashboard_advertisers=$uObj->getUsers(array("type"=>array("1"),"pagesize"=>10));
				$aObj=new Affiliate();
				$dashboard_affiliates=$aObj->getAffiliates(array("pagesize"=>10));
				$wrObj=new WithdrawalRequests();
				$dashboard_withdrawal_requests=$wrObj->getWithdrawRequests(array("pagesize"=>5));
				$afwrObj=new AffiliateWithdrawalRequests();
				
				$affiliate_dashboard_withdrawal_requests=$afwrObj->getWithdrawRequests(array("pagesize"=>5));
				$dashboard_info["summary"]["sales"]=$statsObj->getDashboardSummary('sales');
				$dashboard_info["summary"]["orders"]=$statsObj->getDashboardSummary('orders');
				$dashboard_info["summary"]["users"]=$statsObj->getDashboardSummary('signups');
				
				$dashboard_info["summary"]["shops"]=$statsObj->getDashboardSummary('shops');
				$dashboard_info["summary"]["products"]=$statsObj->getDashboardSummary('products');
				$dashboard_info["orders"]=$dashboard_orders;
				$dashboard_info["shops"]=$dashboard_shops;
				$dashboard_info["products"]=$dashboard_products;
				$dashboard_info["users"]=$dashboard_users;
				$dashboard_info["advertisers"]=$dashboard_advertisers;
				$dashboard_info["affiliates"]=$dashboard_affiliates;
				$dashboard_info["withdrawal_requests"]=$dashboard_withdrawal_requests;
				$dashboard_info["affiliate_withdrawal_requests"]=$affiliate_dashboard_withdrawal_requests;
				$dashboard_info["stats"]["total_users"] = $statsObj->getStats('total_members');
				$dashboard_info["stats"]["total_advertisers"] = $statsObj->getStats('total_advertisers');
				
				$dashboard_info["stats"]["total_affiliates"] = $statsObj->getStats('total_affiliates');
				$dashboard_info["stats"]["total_products"] = $statsObj->getStats('total_products');
				$dashboard_info["stats"]["total_shops"] = $statsObj->getStats('total_shops');
				$dashboard_info["stats"]["total_orders"] = $statsObj->getStats('total_orders');
				$dashboard_info["stats"]["total_sales"] = $statsObj->getStats('total_sales');
				$dashboard_info["stats"]["total_ppc"] = $statsObj->getStats('total_ppc');
				$dashboard_info["stats"]["total_subscription"] = $statsObj->getStats('total_subscription');
				
				
				$dashboard_info["stats"]["total_withdrawal_requests"] = $statsObj->getStats('total_withdrawal_requests');
				$dashboard_info["stats"]["total_affiliate_withdrawal_requests"] = $statsObj->getStats('total_affiliate_withdrawal_requests');
				$dashboard_info["stats"]["product_reviews"] = $statsObj->getStats('product_reviews');
				$dashboard_info['sales_chart_data']=rtrim($sales_chart_data,',');
				$dashboard_info['sales_earnings_chart_data']=rtrim($sales_earnings_chart_data,',');
				$dashboard_info['signups_chart_data']=rtrim($signups_chart_data,',');
				$dashboard_info['products_chart_data']=rtrim($products_chart_data,',');
				$dashboard_info['affiliate_signups_chart_data']=rtrim($affiliate_signups_chart_data,',');
				$dashboard_info['visits_chart_data']=rtrim($visits_chart_data,',');
				$dashboard_info['visitsCount']=$visitCount;
				/*Utilities::printArray($socialVisits);
				die();*/
				$dashboard_info['socialVisits']=$socialVisits;
				$dashboard_info['topProducts']=$statsObj->getTopProducts();
				$dashboard_info['topSearchKeyword']=$statsObj->getTopSearchKeywords();
				$dashboard_info['conversionStats']=$conversionStats;
				$dashboard_info['conversion_chat_data']=$conversion_chat_data;
				$cache->set("dashboard_info",$dashboard_info , 24*60*60);			
			}
			/*$m_time = explode(" ",microtime());
				$m_time = $m_time[0] + $m_time[1];
				$loadend = $m_time;
				$loadtotal = ($loadend - $this->loadstart);
			echo "<small class='no-print'><em>". round($loadtotal,3) ." seconds</em></small>";
				die();
			die('TT');*/
		$this->set('dashboard_info', $dashboard_info);
        $this->_template->render();
    }
    
	function dashboard_stats(){
		$post=getPostedData();
		$type=$post['rtype'];
		$interval=$post['interval'];
		require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/analytics/analyticsapi.php');
		$analyticArr=array(
			'clientId'=>Settings::getSetting("CONF_ANALYTICS_CLIENT_ID"),
			'clientSecretKey'=>Settings::getSetting("CONF_ANALYTICS_SECRET_KEY"),
			'redirectUri'=>Utilities::generateAbsoluteUrl('configurations','redirect'),
			'googleAnalyticsID'=>Settings::getSetting("CONF_ANALYTICS_ID")
		);
		try{
			$analytics=new Ykart_analytics($analyticArr);		
			$token = $analytics->getRefreshToken(Settings::getSetting("CONF_ANALYTICS_ACCESS_TOKEN"));	
			$analytics->setAccessToken($token['accessToken']);
			$accountId=$analytics->setAccountId(Settings::getSetting("CONF_ANALYTICS_ID"));
			switch(strtoupper($type)){
				case 'TOP_COUNTRIES':
					$result=$analytics->getTopCountries($interval,9);				
				break;
				case 'TOP_REFERRERS':
					$result=$analytics->getTopReferrers($interval,9);
				break;
				case 'TOP_SEARCH_KEYWORD':
					//$result=$analytics->getSearchTerm($interval,9);						
					$statsObj=new Statistics();		
					$result=$statsObj->getTopSearchKeywords($interval);								
				break;
				case 'TRAFFIC_SOURCE':
					$result=$analytics->getTrafficSource($interval);							
				break;
				case 'VISITORS_STATS':
					$result=$analytics->getVisitsByDate();											
				break;
				case 'TOP_PRODUCTS':
					$statsObj=new Statistics();		
					$result=$statsObj->getTopProducts($interval);	
				break;				
			}
		}catch(exception $e){
		}				
		$this->set('stats_type', strtoupper($type));
		$this->set('stats_info', $result);
		$this->_template->render(false,false);
	}
	function clear(){
        Utilities::recursiveDelete(CONF_USER_UPLOADS_PATH."caching");
		Message::addMessage('Success: Cache has been cleared');
        Utilities::redirectUser(Utilities::generateUrl("home"));
    }
	
    function testpage(){
        Message::addMessage('From Test page');
        $this->_template->render();
    }
	
	function message_common(){
		$this->_template->render();
	}
}