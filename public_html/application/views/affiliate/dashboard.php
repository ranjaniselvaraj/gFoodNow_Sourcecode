<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . 'affiliate_subheader.php'; ?>
    
    
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . 'affiliate_leftpanel.php'; ?>
        <div class="data-side">
          <div class="tabz-dashboard">
            <div class="tabz-content">
              <h3><?php echo Utilities::getLabel('M_Your_Dashboard')?></h3>
              <div class="orders-list">
                <ul>
                  <li> <a href="<?php echo Utilities::generateUrl('affiliate', 'credits')?>"><span class="txt-big"><?php echo Utilities::displayMoneyFormat($affiliate_details["balance"])?> </span>
                    <p><?php echo Utilities::getLabel('M_Account_Balance')?></p>
                    </a> </li>
                  <li> <a href="#"><span class="txt-big"><?php echo Utilities::displayMoneyFormat($affiliate_details["affTotalRevenue"])?> </span>
                    <p><?php echo Utilities::getLabel('M_Total_Revenue')?></p>
                    </a> </li>
                    <li> <a href="<?php echo Utilities::generateUrl('affiliate','orders')?>?sts=pending"><span class="txt-big"><?php echo Utilities::displayMoneyFormat($affiliate_details["pending"])?> </span>
                    <p><?php echo Utilities::getLabel('M_Pending_Order_Commission')?></p>
                    </a> </li>
                    <li> <a href="<?php echo Utilities::generateUrl('affiliate','orders')?>?sts=received"><span class="txt-big"><?php echo Utilities::displayMoneyFormat($affiliate_details["received"])?> </span>
                    <p><?php echo Utilities::getLabel('M_Received_Order_Commission')?></p>
                    </a> </li>
                </ul>
              </div>
              
              <div class="banks-tbs">
                <div id="personal-info-tab">
                  <ul class="resp-tabs-list">
                    <li id="tab_1"><?php echo Utilities::getLabel('L_Personal_Information')?></li>
                    <li id="tab_2"><?php echo Utilities::getLabel('L_Address_Information')?></li>
                  </ul>
                  <a class="view-more" id="editinfotab" href="<?php echo Utilities::generateUrl('affiliate', 'profile_info')?>"><?php echo Utilities::getLabel('L_Edit_Information')?></a>
                  <div class="resp-tabs-container">
                    <div class="">
                      <table class="tbl-normal">
                        <tbody>
                          <tr>
                            <td><strong><?php echo Utilities::getLabel('L_Username')?>:</strong> <?php echo $affiliate_details["affiliate_username"]?> <span class="smallItalicText">(<?php echo Utilities::getLabel('L_Cannot_be_changed')?>)</span></td>
                            <td><strong><?php echo Utilities::getLabel('L_Email')?>:</strong> <?php echo $affiliate_details["affiliate_email"]?></td>
                          </tr>
                          <tr>
                            <td><strong><?php echo Utilities::getLabel('L_Name')?>:</strong> <?php echo $affiliate_details["affiliate_name"]?></td>
                            <td><strong><?php echo Utilities::getLabel('L_Phone')?>:</strong> <?php echo Utilities::displayNotApplicable($affiliate_details["affiliate_phone"])?></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div class="">
                      <table class=" tbl-normal">
                        <tbody>
                        <tr>
                        	<td><strong><?php echo Utilities::getLabel('L_COMPANY')?>:</strong> <?php echo Utilities::displayNotApplicable($affiliate_details["affiliate_company"])?></td>
	                        <td><strong><?php echo Utilities::getLabel('L_WEBSITE')?>:</strong> <?php echo Utilities::displayNotApplicable($affiliate_details["affiliate_website"])?></td>
                        </tr>
                        <tr>
    	                    <td><strong><?php echo Utilities::getLabel('L_ADDRESS_LINE_1')?>:</strong> <?php echo Utilities::displayNotApplicable($affiliate_details["affiliate_address_1"])?></td>
        	                <td><strong><?php echo Utilities::getLabel('L_ADDRESS_LINE_2')?>:</strong> <?php echo Utilities::displayNotApplicable($affiliate_details["affiliate_address_2"])?></td>
                        </tr>
                        <tr>
	                        <td><strong><?php echo Utilities::getLabel('L_CITY')?>:</strong> <?php echo Utilities::displayNotApplicable($affiliate_details["affiliate_city"])?></td>
    	                    <td><strong><?php echo Utilities::getLabel('M_POSTCODE_ZIP')?>:</strong> <?php echo Utilities::displayNotApplicable($affiliate_details["affiliate_postcode"])?></td>
                        </tr>
                        <tr>
	                        <td><strong><?php echo Utilities::getLabel('M_STATE_COUNTY_PROVINCE')?>:</strong> <?php echo Utilities::displayNotApplicable($affiliate_details["state_name"])?></td>
    	                    <td><strong><?php echo Utilities::getLabel('M_COUNTRY')?>:</strong> <?php echo Utilities::displayNotApplicable($affiliate_details["country_name"])?></td>
                        </tr>
                        
                        </tbody>
                      </table>
                    </div>
                    
                  </div>
                </div>
              </div>
              
              
            </div>
          </div>
          
          
        </div>
        
        <div class="gap"></div>
        
        
        
      </div>
    </div>
  </div>
