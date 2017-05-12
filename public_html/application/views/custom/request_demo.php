<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="data-side">
    <div class="popup-title">
        <h4>Request a Demo</h4>
    </div>
    
     
     <div class="demo-tabs">
            <div class="pop-us-tabs">
                <ul>
                    <li class="active"><a rel="tabs_1" name="step_1" >Step 1: Your Info</a></li>
                    <li><a rel="tabs_2" name="step_2" class="dnc" >Step 2: Schedule Date/Time</a></li>
                </ul>
            </div>
            <?php echo $frm->getFormTag ();  ?>
            <div>
                                <!--tab 1 start here-->
    	                            <div id="tabs_1" class="tabs_content show_tab_content">
                                       <table width="100%" border="0" class="tableform">
                                        <tr><td><label>Your Name <span class="spn_must_field">*</span></label>
                                        <?php echo $frm->getFieldHTML('name');?></td></tr>
                                    
                                        <tr><td><label>Your Email <span class="spn_must_field">*</span></label>
                                        <?php echo $frm->getFieldHTML('email');?></td></tr>
                                        
                                        <tr><td><label>SKYPE ID <span class="spn_must_field">*</span></label>
                                        <?php echo $frm->getFieldHTML('skype');?></td></tr>
                                        
                                        <tr><td><label>Your Message <span class="spn_must_field">*</span></label>
                                        <?php echo $frm->getFieldHTML('message');?></td></tr>
                                        </table>
            	                    </div>
                                <!--tab 1 end here-->
                                
                                <!--tab 2 start here-->
    	                            <div id="tabs_2" class="tabs_content">
                                       <table width="100%" border="0" class="tableform">
                                        <tr><td><div class="alert alert-info">Information: The demo can only be scheduled for the next business day and the time zone being used is Indian Standard Time (IST).</div></td></tr>
                                        <tr><td><label>Preferred Date & Time 1 <span class="spn_must_field">*</span></label>
                                        		<div class="row">
                            						<div class="col-sm-6"><?php echo $frm->getFieldHTML('preferred_demo_date_first');?></div>
								                    <div class="col-sm-6"><?php echo $frm->getFieldHTML('preferred_demo_time_first');?></div>
								                </div></td></tr>
                                    
                                        <tr><td><label>Preferred Date & Time  2 <span class="spn_must_field">*</span></label>
                                        		<div class="row">
                            						<div class="col-sm-6"><?php echo $frm->getFieldHTML('preferred_demo_date_second');?></div>
								                    <div class="col-sm-6"><?php echo $frm->getFieldHTML('preferred_demo_time_second');?></div>
								                </div></td></tr>
                                        
                                        <tr><td><label>Preferred Date & Time 3 <span class="spn_must_field">*</span></label>
                                        		<div class="row">
                            						<div class="col-sm-6"><?php echo $frm->getFieldHTML('preferred_demo_date_third');?></div>
								                    <div class="col-sm-6"><?php echo $frm->getFieldHTML('preferred_demo_time_third');?></div>
								                </div></td></tr>
                                        <tr><td>
                                        <?php echo $frm->getFieldHTML('g-captcha');?></td></tr>
                                       </table>
            	                    </div>
                                <!--tab 2 end here-->
                               	  <div class="gap"></div>
                                  <div class="product_btn_submit">
	                                  <?php echo $frm->getFieldHTML('btn_submit');?>
                                  </div>
          	</div>
            	
	            <?php echo $frm->getExternalJS();?>
                </form>
              </div>
     
</div>
<script src='https://www.google.com/recaptcha/api.js'></script>