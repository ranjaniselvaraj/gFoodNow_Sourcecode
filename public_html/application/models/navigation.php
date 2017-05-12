<?php
class Navigation extends Model{
    function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getNavigation($nav_id,$pagesize=0){
		 $log_protection=User::isUserLogged()?1:2;
		 //die($log_protection."#");
		 $nav=new SearchBase('tbl_navigations','nav');
		 $nav->addCondition('nav.nav_id', '=', $nav_id);
		 $nav->addCondition('nav.nav_status', '=',1);
		 $nav->joinTable('tbl_nav_links', 'INNER JOIN', 'nav.nav_id=nl.nl_nav_id AND nl.nl_is_deleted=0', 'nl');
		 $nav->addCondition('nl.nl_is_deleted','=',0);
		 $nav->addCondition('nl.nl_login_protected','in',array("0",$log_protection));
		 $nav->addOrder('nav.nav_id, nl.nl_display_order');
	     return $this->db->fetch_all($nav->getResultSet());
	}
	
	function getNavigationByType($nav_type){
		 $log_protection=User::isUserLogged()?1:2;
		 $nav=new SearchBase('tbl_navigations','nav');
		 $nav->addCondition('nav.nav_type', '=', $nav_type);
		 $nav->addCondition('nav.nav_status', '=',1);
		 $nav->joinTable('tbl_nav_links', 'INNER JOIN', 'nav.nav_id=nl.nl_nav_id AND nl.nl_is_deleted=0', 'nl');
		 $nav->addCondition('nl.nl_is_deleted','=',0);
		 $nav->addCondition('nl.nl_login_protected','in',array("0",$log_protection));
		 $nav->addOrder('nav.nav_id, nl.nl_display_order');
	     return $this->db->fetch_all($nav->getResultSet());
		 
	}
	
	function getNavigationPages($nav_type){
		 $log_protection=User::isUserLogged()?1:2;
		 $nav=new SearchBase('tbl_navigations','nav');
		 if ($nav_type>0)
			 $nav->addCondition('nav.nav_type', '=', $nav_type);
		 $nav->addCondition('nav.nav_status', '=',1);
		 $nav->joinTable('tbl_nav_links', 'INNER JOIN', 'nav.nav_id=nl.nl_nav_id AND nl.nl_is_deleted=0', 'nl');
		 $nav->addCondition('nl.nl_is_deleted','=',0);
		 $nav->addCondition('nl.nl_login_protected','in',array("0",$log_protection));
		 $nav->addOrder('nav.nav_id, nl.nl_display_order');
		 $nav->addMultipleFields(array('DISTINCT nl.*'));
		 //die($nav->getquery());
	     return $this->db->fetch_all($nav->getResultSet());
	}
}
