<?php
class Subscription_PackagesController extends CommonController{
    function default_action(){
		
		if($this->isUserLogged())
		{
			Utilities::redirectUser(Utilities::generateUrl('account' , 'packages'));
		}
		$packObj=new SubscriptionPackages();
		$criteria['pagesize'] = 30;
		$criteria['status'] = 1;
		$packages=$packObj->getSubscriptionPackages($criteria);
		
		if( $packages ){
			$subPackObj = new SubPackages();
			
			foreach($packages as $key=>&$package){
				
				$criteria['merchantsubpack_merchantpack_id'] = $package['merchantpack_id'];
				$criteria['exclude_free_package'] = true;
				$package['sub_packages'] = $subPackObj->getAssocSubPackages($criteria);
				if( empty($package['sub_packages']) && sizeof($package['sub_packages'])==0 ){
					unset($packages[$key]);
				}
				$package['startsAt'] = $subPackObj->getCheapestPack($criteria);
			}
		}
		$this->set('packages',$packages);
		Syspage::addCss(array('css/accountpackages.css' , 'css/facebox.css'), false);
		
		$this->_template->render();	
    }
	
	function signup_to_subscribe()
	{
		$post = Syspage::getPostedVar();
		$subPackId = intval($post['sub_package_id']);
		if(!empty($subPackId))
		{
			$cartObj->addSubscription( $post['sub_package_id'] );
		}
		$json['status'] = 1;
		$json['msg'] = 'Redirecting...';
		$json['redirectUrl'] = Utilities::generateUrl('user','account');
		die(json_encode($json));
	}
}