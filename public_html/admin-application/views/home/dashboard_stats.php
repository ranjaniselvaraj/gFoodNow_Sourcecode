<?php 
switch(strtoupper($stats_type)){
	case 'TOP_COUNTRIES':
		if($stats_info['totalsForAllResults']==0){echo "<li>No record found.</li>"; exit;}
		
		foreach($stats_info['rows'] as $key=>$val){
			echo "<li>".$key." <span class='count'>".$val['%age']."%</span></li>";
		}			
	break;
	case 'TOP_REFERRERS':
		if($stats_info['totalsForAllResults']==0){echo "<li>No record found.</li>"; exit;}
		foreach($stats_info['rows'] as $key=>$val){
			echo "<li>".$key." <span class='count'>".$val['visit']."</span></li>";
		}
	break;
	case 'TRAFFIC_SOURCE':
		if($stats_info['totalsForAllResults']==0){echo "No record found."; exit;}
		$pieChatStats="[['Source', 'Visitors'],";
		if($stats_info['totalsForAllResults']>0){
			foreach($stats_info['rows'] as $key=>$val){
				if($key==''){continue;}
				$pieChatStats.="['".$key."',".intval($val['visit'])."],";
			}
		}
		$pieChatStats=rtrim($pieChatStats,',');
		echo $pieChatStats.="]";			
	break;
	case 'VISITORS_STATS': 
		if(!empty($stats_info['stats'])){
			$chatStats="[['Year', 'Today','Weekly','Last Month','Last 3 Month'],";
			foreach($stats_info['stats'] as $key=>$val){
				if($key==''){continue;}
				$chatStats.="['".Utilities::formatDate($key)."',".intval($val['today']['visit']).",".intval($val['weekly']['visit']).",".intval($val['lastMonth']['visit']).",".intval($val['last3Month']['visit'])."],";
			}
		}	
		$chatStats=rtrim($chatStats,',');
		echo $chatStats.="]";		
	break;
	case 'TOP_PRODUCTS':
		if(count($stats_info)==0){echo "<li>No record found.</li>"; exit;}
		$count=1;
		foreach($stats_info as $row){ 
			if($count>11){break;}
			echo '<li>'.$row['opr_name'].'<span class="count">'.$row['sold'].' sold</span></li>';
		}
	break;
	case 'TOP_SEARCH_KEYWORD': 
		if(count($stats_info)==0){echo "<li>No record found.</li>"; exit;}		
			$count=1;
			foreach($stats_info as $row){ 
				if($count>11){break;}
					$keyword=($row['searchitem_keyword']=='')?'Blank Search':$row['searchitem_keyword'];
					echo '<li>'.$keyword.'<span class="count">'.$row['search_count'].'</span></li>';
			}
		/*if(count($stats_info)==0){echo "<li>No record found.</li>"; exit;}
		/* if($stats_info['totalsForAllResults']==0){echo "<li>No record found.</li>"; exit;}
		foreach($stats_info['rows'] as $key=>$val){
			echo "<li>".$key." <span class='count'>".$val['count']."</span></li>";
		} 
		$count=1;
		foreach($stats_info as $row){ 
			if($count>11){break;}
			$keyword=($row['search_item']=='')?'Blank Search':$row['search_item'];
			echo '<li>'.$keyword.'<span class="count">'.$row['search_count'].'</span></li>';
		}*/
	break;
}	
?>