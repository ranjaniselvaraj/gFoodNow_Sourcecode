<?php defined('SYSTEM_INIT') or die('Invalid Usage');  ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <div class="box-head">
					<div class="sucessarea">
                    	<?php if ($supplier_request["usuprequest_status"]==0) {?>
       				 	<div class="custom"><i class="svg-icn">
                <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 67 67" style="enable-background:new 0 0 67 67;" xml:space="preserve">
                  <g>
                    <g>
                      <path d="M15.977,39.119h20c1.104,0,2-1.015,2-2.119V13c0-1.104-0.896-2-2-2s-2,0.896-2,2v22.119h-18c-1.104,0-2,0.896-2,2
			S14.873,39.119,15.977,39.119z"/>
                      <path d="M33.5,67C51.972,67,67,51.973,67,33.5C67,15.028,51.972,0,33.5,0S0,15.028,0,33.5C0,51.973,15.029,67,33.5,67z M33.5,4
			C49.767,4,63,17.234,63,33.5S49.767,63,33.5,63S4,49.766,4,33.5C4,17.234,17.234,4,33.5,4z"/>
                    </g>
                  </g>
                </svg>
                
                </i>
				          <h2><span><?php echo Utilities::getLabel('L_Pending')?></span></h2>
				          <div class="gap"></div>
					          <h6><strong>Hi <?php echo $supplier_request["user_name"]?></strong><br>
					            <?php echo Utilities::getLabel('L_Your_Application_Pending')?><br>
								<?php echo Utilities::getLabel('L_Please_Patient_Review_Application')?></h6>
						      <h5><strong><?php echo Utilities::getLabel('L_Application_Reference')?></strong> <?php echo $supplier_request["usuprequest_reference"]?></h5>
					          <div class="gap"></div>
				         </div>
                     	 <?php } elseif ($supplier_request["usuprequest_status"]==1){ ?>
                         <div class="custom"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/success.png">
				          <h2><span><?php echo Utilities::getLabel('L_Approved')?></span></h2>
				          <div class="gap"></div>
					          <h6><strong>Hi <?php echo $supplier_request["user_name"]?></strong><br>
					            <?php echo Utilities::getLabel('L_Your_Application_Approved')?><br>
								<?php echo Utilities::getLabel('L_Start_Using_Supplier_Please_Contact_Us')?></h6>
						      <h5><strong><?php echo Utilities::getLabel('L_Application_Reference')?></strong> <?php echo $supplier_request["usuprequest_reference"]?></h5>
					          <div class="gap"></div>
				         </div>
						 <?php } elseif ($supplier_request["usuprequest_status"]==2){ ?>
                         <div class="custom">
                          <i class="svg-icn"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 511 511" style="enable-background:new 0 0 511 511;" xml:space="preserve">
<g>
	<path d="M471.5,28h-432C17.72,28,0,45.72,0,67.5v256C0,345.28,17.72,363,39.5,363h160c4.142,0,7.5-3.358,7.5-7.5
		s-3.358-7.5-7.5-7.5h-160C25.991,348,15,337.009,15,323.5v-256C15,53.991,25.991,43,39.5,43h432c13.509,0,24.5,10.991,24.5,24.5
		v256c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5v-256C511,45.72,493.28,28,471.5,28z"/>
	<path d="M207.5,292h-144c-4.142,0-7.5,3.358-7.5,7.5s3.358,7.5,7.5,7.5h144c4.142,0,7.5-3.358,7.5-7.5S211.642,292,207.5,292z"/>
	<path d="M151,155.5v-32c0-12.958-10.542-23.5-23.5-23.5h-48C66.542,100,56,110.542,56,123.5v32c0,12.958,10.542,23.5,23.5,23.5h48
		C140.458,179,151,168.458,151,155.5z M71,155.5V147h8.5c4.142,0,7.5-3.358,7.5-7.5s-3.358-7.5-7.5-7.5H71v-8.5
		c0-4.687,3.813-8.5,8.5-8.5H96v49H79.5C74.813,164,71,160.187,71,155.5z M127.5,164H111v-49h16.5c4.687,0,8.5,3.813,8.5,8.5v8.5
		h-8.5c-4.142,0-7.5,3.358-7.5,7.5s3.358,7.5,7.5,7.5h8.5v8.5C136,160.187,132.187,164,127.5,164z"/>
	<path d="M56,251.5c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5v-16c0-4.142-3.358-7.5-7.5-7.5s-7.5,3.358-7.5,7.5V251.5z"/>
	<path d="M80,235.5v16c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5v-16c0-4.142-3.358-7.5-7.5-7.5S80,231.358,80,235.5z"/>
	<path d="M104,235.5v16c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5v-16c0-4.142-3.358-7.5-7.5-7.5S104,231.358,104,235.5z"/>
	<path d="M128,235.5v16c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5v-16c0-4.142-3.358-7.5-7.5-7.5S128,231.358,128,235.5z"/>
	<path d="M175,251.5v-16c0-4.142-3.358-7.5-7.5-7.5s-7.5,3.358-7.5,7.5v16c0,4.142,3.358,7.5,7.5,7.5S175,255.642,175,251.5z"/>
	<path d="M199,251.5v-16c0-4.142-3.358-7.5-7.5-7.5s-7.5,3.358-7.5,7.5v16c0,4.142,3.358,7.5,7.5,7.5S199,255.642,199,251.5z"/>
	<path d="M215.5,228c-4.142,0-7.5,3.358-7.5,7.5v16c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5v-16
		C223,231.358,219.642,228,215.5,228z"/>
	<path d="M247,251.5v-16c0-4.142-3.358-7.5-7.5-7.5s-7.5,3.358-7.5,7.5v16c0,4.142,3.358,7.5,7.5,7.5S247,255.642,247,251.5z"/>
	<path d="M415.5,179c21.78,0,39.5-17.72,39.5-39.5S437.28,100,415.5,100h-48c-21.78,0-39.5,17.72-39.5,39.5s17.72,39.5,39.5,39.5
		H415.5z M343,139.5c0-13.509,10.991-24.5,24.5-24.5h48c13.509,0,24.5,10.991,24.5,24.5S429.009,164,415.5,164h-48
		C353.991,164,343,153.009,343,139.5z"/>
	<path d="M351.5,228C281.196,228,224,285.196,224,355.5S281.196,483,351.5,483S479,425.804,479,355.5S421.804,228,351.5,228z
		 M351.5,468C289.467,468,239,417.533,239,355.5S289.467,243,351.5,243S464,293.467,464,355.5S413.533,468,351.5,468z"/>
	<path d="M412.803,294.197c-2.929-2.929-7.678-2.929-10.606,0L351.5,344.894l-50.697-50.697c-2.929-2.929-7.678-2.929-10.606,0
		c-2.929,2.929-2.929,7.678,0,10.606l50.697,50.697l-50.697,50.697c-2.929,2.929-2.929,7.678,0,10.606
		c1.464,1.464,3.384,2.197,5.303,2.197s3.839-0.732,5.303-2.197l50.697-50.697l50.697,50.697c1.464,1.464,3.384,2.197,5.303,2.197
		s3.839-0.732,5.303-2.197c2.929-2.929,2.929-7.678,0-10.606L362.106,355.5l50.697-50.697
		C415.732,301.875,415.732,297.125,412.803,294.197z"/>
</g>
</svg>
</i>
				          <h2><span><?php echo Utilities::getLabel('L_Declined_Cancelled')?></span></h2>
                          <div class="gap"></div>
                          <h6><strong><?php echo Utilities::getLabel('L_Reason_for_cancellation')?></strong></h6><br>
                          <p><?php echo nl2br($supplier_request["usuprequest_comments"])?></p>
				          <div class="gap"></div>
					          <h6><strong>Hi <?php echo $supplier_request["user_name"]?></strong><br>
					            <?php echo Utilities::getLabel('L_Your_Application_Declined')?><br>
								<?php echo Utilities::getLabel('L_Think_Error_Please_Contact_Us')?></h6>
                                <a class="btn secondary-btn" href="<?php echo Utilities::generateUrl('account', 'supplier_approval_form',array('reopen')); ?>"><?php echo Utilities::getLabel('L_Submit_Revised_Request')?></a>
                                
						      <h5><strong><?php echo Utilities::getLabel('L_Application_Reference')?></strong> <?php echo $supplier_request["usuprequest_reference"]?></h5>
					          <div class="gap"></div>
				         </div>
                         
                      	 <?php } ?>
                      <a class="btn green" href="<?php echo Utilities::generateUrl('account', 'dashboard_buyer')?>"><?php echo Utilities::getLabel('L_Back_to_dashboard')?></a> 
                      </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  