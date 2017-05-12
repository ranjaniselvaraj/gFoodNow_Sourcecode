<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $loggedin_user; ?>
<div class="left-data no-print"  id="list_ct_2">
          <div class="dash-nav">
            <ul>
            <li class="dashboard <?php if (($action=="dashboard_buyer") || ($action=="dashboard_supplier")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'dashboard_supplier')?>"> <i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <g>
                  <path fill="#474747" d="M9.5,63.4c0,0-0.1,1.5,1.4,1.5c1.9,0,17.6,0,17.6,0V50.5c0,0-0.2-2.4,2.1-2.4h7.3c2.8,0,2.6,2.4,2.6,2.4
		v14.4c0,0,14.9,0,17.2,0c2,0,1.9-2,1.9-2V33.3L35.2,11.7L9.5,33.4C9.5,33.3,9.5,63.4,9.5,63.4z"/>
                  <path fill="#474747" d="M0,31.3c0,0,2.2,4,7,0L35.5,7.2l26.8,24c5.6,4,7.6,0,7.6,0L35.5,0L0,31.3z"/>
                  <polygon fill="#474747" points="61.6,7.1 54.7,7.1 54.8,15.5 61.6,21.2 	"/>
                </g>
                </svg></i><span><?php echo Utilities::getLabel('M_Dashboard')?></span> </a></li>
            <li class="<?php if (($action=="profile_info") || ($action=="bank_info")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'profile_info')?>"> <i> <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 477.297 477.297" style="enable-background:new 0 0 477.297 477.297;" xml:space="preserve">
                <g>
                  <g>
                    <g>
                      <path  fill="#474747" d="M42.85,358.075c0-24.138,0-306.758,0-330.917c23.9,0,278.867,0,302.767,0c0,8.542,0,49.44,0,99.722
				c5.846-1.079,11.842-1.812,17.99-1.812c3.149,0,6.126,0.647,9.232,0.928V0H15.649v385.233h224.638v-27.158
				C158.534,358.075,57.475,358.075,42.85,358.075z"/>
                      <path d="M81.527,206.842h184.495c1.812-10.16,5.393-19.608,10.095-28.452H81.527V206.842z"/>
                      <rect  fill="#474747" x="81.527" y="89.432" width="225.372" height="28.452"/>
                      <path d="M81.527,295.822h191.268c5.112-3.106,10.57-5.63,16.415-7.183c-5.544-6.45-10.095-13.697-13.978-21.269H81.527V295.822z"
				/>
                      <path d="M363.629,298.669c41.071,0,74.16-33.197,74.16-74.139c0-40.984-33.09-74.16-74.16-74.16
				c-40.898,0-74.009,33.176-74.009,74.16C289.62,265.472,322.731,298.669,363.629,298.669z"/>
                      <path d="M423.143,310.706H304.288c-21.226,0-38.612,19.457-38.612,43.422v119.33c0,1.316,0.604,2.481,0.69,3.84h194.59
				c0.086-1.337,0.69-2.524,0.69-3.84v-119.33C461.733,330.227,444.39,310.706,423.143,310.706z"/>
                    </g>
                  </g>
                </g>
                </svg> </i> <span><?php echo Utilities::getLabel('M_Account_Information')?></span> </a></li>  
                
                <li class="messages <?php if (($action=="messages") || ($action=="view_message")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'messages')?>"> 
                
                <?php if ($user_details["unreadMessages"]) {?>
                	<div class="count"><span><? echo $user_details["unreadMessages"]?></span></div>
				<?php }?>
                
                <i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <g>
                  <path fill="#474747" d="M35,35.2l-8.7-7.6L1.6,48.8c0.9,0.9,2.1,1.4,3.4,1.4H65c1.3,0,2.5-0.5,3.4-1.4L43.7,27.5L35,35.2z M35,35.2
		"/>
                  <path fill="#474747" d="M68.4,1.4C67.5,0.5,66.3,0,65,0H5C3.7,0,2.5,0.5,1.6,1.4L35,30.1L68.4,1.4z M68.4,1.4"/>
                  <path fill="#474747" d="M0,4.4v41.7l24.2-20.6L0,4.4z M0,4.4"/>
                  <path fill="#474747" d="M45.8,25.5L70,46.1V4.4L45.8,25.5z M45.8,25.5"/>
                </g>
                </svg> </i> <span><?php echo Utilities::getLabel('M_Messages')?></span> </a></li>
                
                <li class="credits <?php if (($action=="request_withdrawal") || ($action=="credits")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'credits')?>"> <i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <g>
                  <g>
                    <defs>
                      <rect id="SVGID_1_" width="69.4" height="71.3"/>
                    </defs>
                    <clipPath id="SVGID_2_">
                      <use xlink:href="#SVGID_1_"  overflow="visible"/>
                    </clipPath>
                    <path clip-path="url(#SVGID_2_)" fill="#474747" d="M6.9,20L62.2,5.8l-0.5-2.2c-0.6-2.6-3.4-4.2-6-3.5L10,11.9
			c-2.6,0.6-4.2,3.4-3.5,6L6.9,20z M6.9,20"/>
                    <path clip-path="url(#SVGID_2_)" fill="#474747" d="M65.7,39c2.6-0.6,4.2-3.4,3.6-6l-4.5-17.6l-38.6,9.9h26.1
			c5.2,0,9.6,4.3,9.6,9.6v5.2L65.7,39z M65.7,39"/>
                  </g>
                  <path fill="#474747" d="M57.1,34.8c0-2.8-2.2-4.9-4.9-4.9H4.9c-2.8,0-4.9,2.2-4.9,4.9v30.2C0,67.8,2.2,70,4.9,70h47.2
		c2.8,0,4.9-2.2,4.9-4.9V34.8z M5.4,38.4c0-1.2,1.1-2.3,2.3-2.3h26.2c1.2,0,2.3,1.1,2.3,2.3c0,1.2-1.1,2.3-2.3,2.3H7.7
		C6.5,40.7,5.4,39.6,5.4,38.4L5.4,38.4z M35.2,65.5c-4.3,0-7.9-3.6-7.9-7.9c0-4.3,3.5-7.7,7.9-7.7c4.3,0,7.9,3.5,7.9,7.9
		C43,62.1,39.5,65.5,35.2,65.5L35.2,65.5z M52.1,57.8c0,4.3-3.5,7.9-7.9,7.9c-0.5,0-1.1,0-1.5-0.2c2-2,3.1-4.6,3.1-7.7
		c0-3.1-1.2-5.7-3.1-7.7c0.5-0.2,0.9-0.2,1.5-0.2C48.7,50,52.1,53.5,52.1,57.8L52.1,57.8z M52.1,57.8"/>
                </g>
                </svg></i> <span><?php echo Utilities::getLabel('M_My_Wallet')?></span> </a></li>
                
             <? if (Settings::getSetting("CONF_ENABLE_REFERRER_MODULE") && ($is_buyer_logged)):?>
             <li class="<?php if (($action=="share_earn")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'share_earn')?>"> <i>
                  <svg xml:space="preserve" style="enable-background:new 0 0 47 47;" viewBox="0 0 47 47" height="47px" width="47px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" version="1.1">
                    <g>
                      <g id="Layer_1_1_">
                        <g>
                          <path d="M7.371,19.227H2.137C0.957,19.227,0,20.184,0,21.364v23.5C0,46.043,0.957,47,2.137,47h5.234
				c1.18,0,2.136-0.957,2.136-2.136v-23.5C9.507,20.184,8.551,19.227,7.371,19.227z"/>
                          <path d="M19.869,25.635h-5.234c-1.181,0-2.137,0.957-2.137,2.137v17.09c0,1.181,0.956,2.137,2.137,2.137h5.234
				c1.18,0,2.136-0.957,2.136-2.137v-17.09C22.005,26.592,21.049,25.635,19.869,25.635z"/>
                          <path d="M32.365,25.635h-5.234c-1.18,0-2.136,0.957-2.136,2.137v17.09c0,1.181,0.956,2.137,2.136,2.137h5.234
				c1.181,0,2.137-0.957,2.137-2.137v-17.09C34.502,26.592,33.546,25.635,32.365,25.635z"/>
                          <path d="M44.863,19.227h-5.234c-1.18,0-2.136,0.957-2.136,2.137v23.5c0,1.18,0.956,2.136,2.136,2.136h5.234
				C46.043,47,47,46.042,47,44.864v-23.5C47,20.184,46.043,19.227,44.863,19.227z"/>
                          <path d="M24.123,12.87v3.846c1.164-0.077,2.391-0.623,2.391-1.904C26.514,13.49,25.169,13.103,24.123,12.87z"/>
                          <path d="M20.778,8.267c0,0.972,0.723,1.534,2.18,1.826V6.614C21.634,6.653,20.778,7.431,20.778,8.267z"/>
                          <path d="M23.5,0C17.021,0,11.75,5.272,11.75,11.75c0,6.476,5.271,11.748,11.75,11.748c6.479,0,11.75-5.272,11.75-11.748
				C35.25,5.272,29.979,0,23.5,0z M24.123,18.699v1.203c0,0.331-0.254,0.661-0.586,0.661c-0.328,0-0.579-0.33-0.579-0.661v-1.203
				c-3.283-0.08-4.916-2.042-4.916-3.577c0-0.775,0.469-1.223,1.203-1.223c2.176,0,0.484,2.681,3.713,2.816v-4.06
				c-2.88-0.523-4.624-1.786-4.624-3.942c0-2.641,2.196-4.003,4.624-4.079V3.598c0-0.331,0.251-0.661,0.579-0.661
				c0.332,0,0.586,0.33,0.586,0.661v1.036c1.514,0.04,4.623,0.99,4.623,2.895c0,0.757-0.566,1.203-1.227,1.203
				c-1.264,0-1.246-2.077-3.396-2.117v3.691c2.564,0.545,4.835,1.302,4.835,4.294C28.958,17.202,27.016,18.522,24.123,18.699z"/>
                        </g>
                      </g>
                    </g>
                  </svg>
                  </i><span><?php echo Utilities::getLabel('M_Share_and_Earn')?></span> </a></li>
              <? endif; ?>    
             
             <?php if ($is_buyer_logged) {?>
             <li class="reward_points <?php if (($action=="reward_points")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'reward_points')?>">
                
				 <? if ($user_details["totUserRewardPoints"]>0):?>
                    <div class="count"><span><? echo $user_details["totUserRewardPoints"]?></span></div>
                <? endif; ?>
                
				<i> <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="54.558px" height="54.559px" viewBox="0 0 54.558 54.559" style="enable-background:new 0 0 54.558 54.559;"
	 xml:space="preserve">
  <g>
    <g>
      <path d="M27.28,3.911c-8.024,0-14.553,6.528-14.553,14.552s6.528,14.553,14.553,14.553c8.024,0,14.552-6.529,14.552-14.553
			S35.304,3.911,27.28,3.911z M27.28,31.016c-6.921,0-12.553-5.631-12.553-12.553c0-6.921,5.631-12.552,12.553-12.552
			c6.921,0,12.552,5.631,12.552,12.552C39.832,25.384,34.201,31.016,27.28,31.016z"/>
      <path d="M27.28,7.704c-0.552,0-1,0.448-1,1c0,0.552,0.448,1,1,1c4.83,0,8.758,3.929,8.758,8.759c0,0.552,0.448,1,1,1s1-0.448,1-1
			C38.038,12.53,33.212,7.704,27.28,7.704z"/>
      <path d="M45.743,18.463C45.743,8.282,37.46,0,27.28,0C17.1,0,8.816,8.282,8.816,18.463c0,5.947,2.847,11.471,7.647,14.946
			l-5.877,15.06c-0.124,0.317-0.078,0.676,0.122,0.95c0.2,0.276,0.534,0.437,0.865,0.412l6.676-0.366l4.663,4.791
			c0.19,0.196,0.45,0.303,0.717,0.303c0.066,0,0.132-0.006,0.199-0.02c0.333-0.066,0.609-0.3,0.733-0.615l2.719-6.968L30,53.924
			c0.123,0.315,0.399,0.549,0.732,0.615c0.066,0.014,0.133,0.02,0.199,0.02c0.267,0,0.525-0.106,0.717-0.303l4.663-4.791
			l6.676,0.366c0.022,0.001,0.045,0.003,0.065,0.001c0.549,0.008,1.01-0.443,1.01-1c0-0.197-0.057-0.381-0.156-0.537l-5.811-14.886
			C42.896,29.934,45.743,24.41,45.743,18.463z M23.262,51.747l-3.897-4.004c-0.189-0.194-0.448-0.304-0.717-0.304
			c-0.018,0-0.037,0-0.055,0.002l-5.579,0.306l5.163-13.228c0.019,0.011,0.039,0.02,0.058,0.029
			c0.225,0.127,0.457,0.239,0.686,0.355c0.184,0.095,0.365,0.195,0.552,0.283c0.082,0.039,0.167,0.07,0.249,0.106
			c1.544,0.698,3.171,1.181,4.85,1.429c0.008,0.002,0.016,0.004,0.024,0.004c0.365,0.053,0.734,0.09,1.104,0.121
			c0.096,0.008,0.191,0.021,0.288,0.027c0.294,0.02,0.59,0.025,0.886,0.032c0.136,0.003,0.271,0.015,0.406,0.015
			c0.041,0,0.082-0.006,0.123-0.006c0.513-0.005,1.027-0.027,1.545-0.077c0.039-0.003,0.077-0.003,0.115-0.007
			c0.006,0,0.013,0,0.021-0.001l-2.735,7.004c0,0,0,0.001,0,0.002L23.262,51.747z M35.966,47.441
			c-0.285-0.012-0.57,0.095-0.771,0.302l-3.896,4.004l-2.944-7.543l3.021-7.741c0.34-0.076,0.674-0.171,1.006-0.268
			c0.08-0.021,0.159-0.038,0.237-0.062c0.513-0.154,1.017-0.334,1.513-0.533c0.139-0.056,0.272-0.119,0.409-0.176
			c0.366-0.158,0.728-0.326,1.083-0.507c0.152-0.078,0.305-0.155,0.454-0.237c0.101-0.055,0.206-0.103,0.306-0.16l5.164,13.229
			L35.966,47.441z M36.328,32.208c-1.798,1.187-3.775,1.996-5.881,2.406c-1.632,0.317-3.257,0.389-4.839,0.229
			c-2.636-0.264-5.15-1.166-7.378-2.637c-4.643-3.062-7.415-8.201-7.415-13.746c0-9.078,7.385-16.463,16.463-16.463
			s16.463,7.385,16.463,16.463C43.743,24.007,40.97,29.146,36.328,32.208z"/>
    </g>
  </g>
</svg>
</i> <span><?php echo Utilities::getLabel('M_Reward_Points')?></span> </a></li>
             <?php } ?>   
             
             <li class="sales <?php if (($action=="sales") || ($action=="sales_view_order") || ($action=="cancel_order")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'sales')?>"> <i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <g>
                  <path fill="#474747" d="M17,28.2L5.5,40.1v20.1c0,0.6,0.5,1,1,1h14c0.6,0,1-0.5,1-1V29.4l-3.2-1.2C18.3,28.2,17,28.2,17,28.2z"/>
                  <path fill="#474747" d="M45.4,37.5h-14c-0.6,0-1,0.5-1,1v21.7c0,0.6,0.5,1,1,1h14c0.6,0,1-0.5,1-1V38.6C46.4,38,46,37.5,45.4,37.5
		L45.4,37.5z"/>
                  <g>
                    <path fill="#474747" d="M69.8,15.9c-0.5,0.3-1.1,0.4-1.7,0.4c-0.9,0-1.7-0.3-2.4-0.9l-2.5-2.1l-9.2,10v36.9c0,0.6,0.5,1,1,1h14
			c0.6,0,1-0.5,1-1V15.8C69.9,15.9,69.9,15.9,69.8,15.9L69.8,15.9z"/>
                    <path fill="#474747" d="M39.7,32.8c0.8,0.3,1.7,0.1,2.2-0.5l21.2-23l5,4.1c0.2,0.2,0.6,0.2,0.9,0.1c0.3-0.1,0.4-0.4,0.4-0.8
			l-1.1-12c0-0.4-0.4-0.7-0.8-0.7l-12,1.1c-0.3,0-0.6,0.2-0.7,0.5c0,0.1,0,0.2,0,0.3c0,0.2,0.1,0.4,0.3,0.5l5,4.1L39.8,28.4l-23-8.4
			c-0.8-0.3-1.6-0.1-2.2,0.5l-14,14.6c-0.8,0.8-0.8,2.1,0.1,2.9c0.4,0.4,0.9,0.6,1.4,0.6s1.1-0.2,1.5-0.6l13.1-13.6L39.7,32.8z"/>
                  </g>
                </g>
                <rect y="63.8" fill="#474747" width="70" height="3.9"/>
                <g>
                  <g>
                    <g>
                      <defs>
                        <rect id="SVGID_1_" x="32" y="3" width="10.9" height="20.3"/>
                      </defs>
                      <clipPath id="SVGID_2_">
                        <use xlink:href="#SVGID_1_"  overflow="visible"/>
                      </clipPath>
                      <path clip-path="url(#SVGID_2_)" fill="#474747" d="M38.7,11.5c-2.1-0.8-3-1.3-3-2.1c0-0.7,0.5-1.4,2.1-1.4c1,0,1.9,0.2,2.5,0.4
				c0.3,0.1,0.6,0.1,0.8-0.1c0.3-0.1,0.4-0.4,0.5-0.7l0.2-0.7c0.1-0.5-0.2-1.1-0.7-1.3c-0.7-0.2-1.5-0.4-2.7-0.4V3.6
				C38.6,3.3,38.4,3,38,3h-1.3c-0.3,0-0.6,0.3-0.6,0.6v1.8C33.6,5.9,32,7.6,32,9.8c0,2.4,1.8,3.6,4.5,4.5c1.8,0.6,2.6,1.2,2.6,2.1
				c0,1-1,1.5-2.4,1.5c-1.1,0-2.1-0.2-3-0.6c-0.3-0.1-0.6-0.1-0.9,0c-0.3,0.1-0.5,0.4-0.5,0.7l-0.2,0.8c-0.1,0.5,0.1,1,0.6,1.2
				c0.9,0.4,2.1,0.6,3.3,0.7v1.8c0,0.3,0.3,0.5,0.6,0.5h1.3c0.3,0,0.6-0.2,0.6-0.5v-2c2.9-0.5,4.4-2.4,4.4-4.6
				C42.9,13.9,41.7,12.5,38.7,11.5L38.7,11.5z"/>
                    </g>
                  </g>
                </g>
                </svg></i> <span><?php echo Utilities::getLabel('M_Sales')?></span> </a></li>	                
             
              
               
               <?php if (Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) {?>
               <li class="packages <?php if ($action=="packages"):?>active<?php endif;?>">
				<a href="<?=Utilities::generateUrl('account', 'packages')?>"> <i> <svg   xmlns="http://www.w3.org/2000/svg" viewBox="0 0 490 490" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 490 490">
  <g>
    <g>
      <path d="M264.8,260.6v224.1H490V260.6H264.8z M285.7,464.9V280.4H367v41.7h20.8v-41.7h81.3v184.5H285.7z"/>
      <path d="M0,484.7h225.2V260.6H0V484.7z M20.9,280.4h81.3v41.7H123v-41.7h81.3v184.5H20.9V280.4z"/>
      <path d="M357.6,5.3H132.4v224.1h225.2V5.3z M336.7,209.6H153.3V25.1h81.3v40.7h20.8V25.1h81.3V209.6z"/>
    </g>
  </g>
</svg>
</i> <span><?php echo Utilities::getLabel('M_Packages')?></span> </a></li>
            <li class="<?=( $action=="subscriptions" || $action=="view_subscription" ) ? 'active' : ''; ?>"><a href="<?=Utilities::generateUrl('account', 'subscriptions')?>"><i> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve">
  <g>
    <g>
      <path d="M59,0H30v2h28v52h2V1C60,0.4,59.6,0,59,0z"/>
      <path d="M55,4H23l0,0c-0.1,0-0.2,0-0.3,0.1h-0.1c-0.1,0.1-0.2,0.1-0.3,0.2l0,0l-12,12l0,0c-0.1,0.1-0.1,0.2-0.2,0.3v0.1
			c0,0.1-0.1,0.2-0.1,0.3l0,0v6h2v-5h11c0.6,0,1-0.4,1-1V6h30v52H18v2h37c0.6,0,1-0.4,1-1V5C56,4.4,55.6,4,55,4z M13.4,16l4.3-4.3
			L22,7.4V16H13.4z"/>
      <path d="M2,28h46v20H20v2h29c0.6,0,1-0.4,1-1V27c0-0.6-0.4-1-1-1H1c-0.6,0-1,0.4-1,1v13h2V28z"/>
      <path d="M46,37v-6c0-0.6-0.4-1-1-1H5c-0.6,0-1,0.4-1,1v6c0,0.6,0.4,1,1,1h40C45.6,38,46,37.6,46,37z M44,36H6v-4h38V36z"/>
      <rect x="22" y="40" width="2" height="2"/>
      <rect x="26" y="40" width="20" height="2"/>
      <rect x="36" y="44" width="4" height="2"/>
      <rect x="42" y="44" width="4" height="2"/>
      <rect x="43" y="9" width="8" height="2"/>
      <rect x="39" y="13" width="12" height="2"/>
      <rect x="14" y="21" width="2" height="2"/>
      <rect x="18" y="21" width="2" height="2"/>
      <rect x="22" y="21" width="2" height="2"/>
      <rect x="26" y="21" width="2" height="2"/>
      <rect x="30" y="21" width="2" height="2"/>
      <rect x="34" y="21" width="2" height="2"/>
      <rect x="38" y="21" width="2" height="2"/>
      <rect x="42" y="21" width="2" height="2"/>
      <rect x="46" y="21" width="2" height="2"/>
      <rect x="50" y="21" width="2" height="2"/>
      <rect x="22" y="53" width="2" height="2"/>
      <rect x="26" y="53" width="2" height="2"/>
      <rect x="30" y="53" width="2" height="2"/>
      <rect x="34" y="53" width="2" height="2"/>
      <rect x="38" y="53" width="2" height="2"/>
      <rect x="42" y="53" width="2" height="2"/>
      <rect x="46" y="53" width="2" height="2"/>
      <rect x="50" y="53" width="2" height="2"/>
      <path d="M16,48v-4c0-0.6-0.4-1-1-1H5.4l1.3-1.3l-1.4-1.4l-3,3c-0.4,0.4-0.4,1,0,1.4l3,3l1.4-1.4L5.4,45H14v3H16z"/>
      <path d="M12.7,52.3l-1.4,1.4l1.3,1.3H4v-3H2v4c0,0.6,0.4,1,1,1h9.6l-1.3,1.3l1.4,1.4l3-3c0.4-0.4,0.4-1,0-1.4L12.7,52.3z"/>
    </g>
  </g> 
</svg>
</i><span><?php echo Utilities::getLabel('M_Subscriptions')?></span></a></li>
              <?php } ?>
               
              <li class="publications <?php if (($action=="publications") || ($action=="paused_publications") || ($action=="finalized_publications") || ($action=="product_form")) :?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'publications')?>"> <i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <g>
                  <path fill="#474747" d="M43.7,0H0v70h61.2V17.5L43.7,0z M43.7,6.2l11.3,11.3H43.7V6.2z M56.9,65.6H4.4V4.4h35v17.5h17.5V65.6z
		 M8.7,26.3h43.7v4.4H8.7V26.3z M8.7,39.4h43.7v4.4H8.7V39.4z M8.7,52.5h43.7v4.4H8.7V52.5z M8.7,52.5"/>
                </g>
               </svg></i> <span><?php echo Utilities::getLabel('M_My_Products')?></span> </a></li>
              <li class="shop <?php if ($action=="shop"):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'shop')?>"> <i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <g>
                  <path fill="#474747" d="M0,28.5c0,4.1,2.8,7.5,6.6,8.5v28.8H2.2c-1.2,0-2.2,1-2.2,2.2s1,2.2,2.2,2.2h65.7c1.2,0,2.2-1,2.2-2.2
		s-1-2.2-2.2-2.2h-4.4V36.9c3.8-1,6.6-4.4,6.6-8.5v-2.2H0V28.5z M43.8,37.2c4.8,0,8.8-3.9,8.8-8.8c0,4.1,2.8,7.5,6.6,8.5v28.8H26.3
		V37.2c4.8,0,8.8-3.9,8.8-8.8C35,33.3,39,37.2,43.8,37.2L43.8,37.2z M24.1,36.9v28.8H11V36.9c3.8-1,6.6-4.4,6.6-8.5
		C17.5,32.5,20.3,35.9,24.1,36.9L24.1,36.9z M61.3,6.6H8.8L0,24.1h70.1L61.3,6.6z M18.6,52.9v-5c-0.6-0.4-1.1-1.1-1.1-1.9
		c0-1.2,1-2.2,2.2-2.2c1.2,0,2.2,1,2.2,2.2c0,0.8-0.4,1.5-1.1,1.9v5c0.6,0.4,1.1,1.1,1.1,1.9c0,1.2-1,2.2-2.2,2.2
		c-1.2,0-2.2-1-2.2-2.2C17.5,54,18,53.3,18.6,52.9L18.6,52.9z M8.8,2.2C8.8,1,9.7,0,11,0h48.2c1.2,0,2.2,1,2.2,2.2s-1,2.2-2.2,2.2
		H11C9.7,4.4,8.8,3.4,8.8,2.2L8.8,2.2z M38.4,49l-1.5-1.5l6.2-6.2l1.5,1.5L38.4,49z M38.4,55.2l-1.5-1.5l12.4-12.4l1.5,1.5
		L38.4,55.2z M49.2,47.4l1.5,1.5l-6.2,6.2L43,53.6L49.2,47.4z M49.2,47.4"/>
                </g>
                </svg></i> <span><?php echo Utilities::getLabel('M_Shop')?></span> </a></li>
                
               
               <li class="promote <?php if ($action=="promote"):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'promote')?>"> <i> <svg enable-background="new 0 0 297 297" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 297 297" xmlns="http://www.w3.org/2000/svg" version="1.1">
<g>
 <path d="m240.507,102.858c27.56,0 49.982-22.422 49.982-49.982s-22.422-49.981-49.982-49.981-49.981,22.421-49.981,49.981 22.421,49.982 49.981,49.982z"/>
 <path d="M190.526,165v124.572c0,4.102,3.326,7.428,7.428,7.428h80.092c4.102,0,7.428-3.326,7.428-7.428V165   c0-26.219-21.255-47.474-47.474-47.474h0C211.781,117.526,190.526,138.781,190.526,165z"/>
 <path d="m147.939,37.688l-130.764-37.361c-0.769-0.219-1.549-0.327-2.322-0.327-1.789,0-3.538,0.578-5.014,1.692-2.115,1.596-3.327,4.03-3.327,6.679v97.282c0,2.649 1.212,5.083 3.327,6.679 2.114,1.595 4.787,2.091 7.337,1.365l130.764-37.361c3.572-1.021 6.066-4.328 6.066-8.043v-22.562c0-3.715-2.495-7.022-6.067-8.043z"/>
 <path d="m120.457,109.945l-33.319,10.602v-11.39l-18.039,5.154v18.571c0,2.878 1.373,5.582 3.695,7.281 1.565,1.144 3.434,1.739 5.325,1.739 0.916,0 1.838-0.14 2.734-0.425l51.358-16.342c3.743-1.191 6.285-4.667 6.285-8.595v-22.057l-18.039,5.154v10.308z"/>
</g>
</svg> </i>  <span><?php echo Utilities::getLabel('M_Promotions')?></span> </a></li>
                
                <li class="sales <?php if (($action=="options") || ($action=="option_form")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'options')?>"> <i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <g>
                  <path fill="#474747" d="M17,28.2L5.5,40.1v20.1c0,0.6,0.5,1,1,1h14c0.6,0,1-0.5,1-1V29.4l-3.2-1.2C18.3,28.2,17,28.2,17,28.2z"/>
                  <path fill="#474747" d="M45.4,37.5h-14c-0.6,0-1,0.5-1,1v21.7c0,0.6,0.5,1,1,1h14c0.6,0,1-0.5,1-1V38.6C46.4,38,46,37.5,45.4,37.5
		L45.4,37.5z"/>
                  <g>
                    <path fill="#474747" d="M69.8,15.9c-0.5,0.3-1.1,0.4-1.7,0.4c-0.9,0-1.7-0.3-2.4-0.9l-2.5-2.1l-9.2,10v36.9c0,0.6,0.5,1,1,1h14
			c0.6,0,1-0.5,1-1V15.8C69.9,15.9,69.9,15.9,69.8,15.9L69.8,15.9z"/>
                    <path fill="#474747" d="M39.7,32.8c0.8,0.3,1.7,0.1,2.2-0.5l21.2-23l5,4.1c0.2,0.2,0.6,0.2,0.9,0.1c0.3-0.1,0.4-0.4,0.4-0.8
			l-1.1-12c0-0.4-0.4-0.7-0.8-0.7l-12,1.1c-0.3,0-0.6,0.2-0.7,0.5c0,0.1,0,0.2,0,0.3c0,0.2,0.1,0.4,0.3,0.5l5,4.1L39.8,28.4l-23-8.4
			c-0.8-0.3-1.6-0.1-2.2,0.5l-14,14.6c-0.8,0.8-0.8,2.1,0.1,2.9c0.4,0.4,0.9,0.6,1.4,0.6s1.1-0.2,1.5-0.6l13.1-13.6L39.7,32.8z"/>
                  </g>
                </g>
                <rect y="63.8" fill="#474747" width="70" height="3.9"/>
                <g>
                  <g>
                    <g>
                      <defs>
                        <rect id="SVGID_1_" x="32" y="3" width="10.9" height="20.3"/>
                      </defs>
                      <clipPath id="SVGID_2_">
                        <use xlink:href="#SVGID_1_"  overflow="visible"/>
                      </clipPath>
                      <path clip-path="url(#SVGID_2_)" fill="#474747" d="M38.7,11.5c-2.1-0.8-3-1.3-3-2.1c0-0.7,0.5-1.4,2.1-1.4c1,0,1.9,0.2,2.5,0.4
				c0.3,0.1,0.6,0.1,0.8-0.1c0.3-0.1,0.4-0.4,0.5-0.7l0.2-0.7c0.1-0.5-0.2-1.1-0.7-1.3c-0.7-0.2-1.5-0.4-2.7-0.4V3.6
				C38.6,3.3,38.4,3,38,3h-1.3c-0.3,0-0.6,0.3-0.6,0.6v1.8C33.6,5.9,32,7.6,32,9.8c0,2.4,1.8,3.6,4.5,4.5c1.8,0.6,2.6,1.2,2.6,2.1
				c0,1-1,1.5-2.4,1.5c-1.1,0-2.1-0.2-3-0.6c-0.3-0.1-0.6-0.1-0.9,0c-0.3,0.1-0.5,0.4-0.5,0.7l-0.2,0.8c-0.1,0.5,0.1,1,0.6,1.2
				c0.9,0.4,2.1,0.6,3.3,0.7v1.8c0,0.3,0.3,0.5,0.6,0.5h1.3c0.3,0,0.6-0.2,0.6-0.5v-2c2.9-0.5,4.4-2.4,4.4-4.6
				C42.9,13.9,41.7,12.5,38.7,11.5L38.7,11.5z"/>
                    </g>
                  </g>
                </g>
                </svg></i> <span><?php echo Utilities::getLabel('M_Options_Variants')?></span> </a></li>
                
                
                
                <li class="<?php if (($action=="cancellation_requests") || ($action=="cancellation_request")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'cancellation_requests')?>"> <i> <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="929.6px" height="929.6px" viewBox="0 0 929.6 929.6" style="enable-background:new 0 0 929.6 929.6;" xml:space="preserve"
	>
                <g>
                  <g>
                    <path d="M404.3,894.6c0,19.301,15.7,35,35,35h59c19.3,0,35-15.699,35-35v-157h-129V894.6z"/>
                    <path d="M533.3,35c0-19.3-15.7-35-35-35h-59c-19.3,0-35,15.7-35,35v30.7h129V35z"/>
                    <rect x="404.3" y="365.7" width="129" height="71.9"/>
                    <path d="M779.3,109.4c-6.6-8.6-16.9-13.7-27.8-13.7H533.3h-129H193.8c-19.3,0-35,15.7-35,35v170c0,19.3,15.7,35,35,35h210.5h129
			h218.2c10.899,0,21.2-5.1,27.8-13.7l65.2-85c9.6-12.6,9.6-30,0-42.6L779.3,109.4z"/>
                    <path d="M533.3,707.6h202.5c19.3,0,35-15.699,35-35v-170c0-19.299-15.7-35-35-35H533.3h-129H178.1
			c-10.899,0-21.199,5.1-27.8,13.701l-65.2,85c-9.6,12.6-9.6,30,0,42.6l65.2,85c6.601,8.6,16.9,13.699,27.8,13.699h226.2H533.3z"/>
                  </g>
                </g>
                </svg> </i> <span><?php echo Utilities::getLabel('M_Cancellation_Requests')?></span> </a></li>
                
                <li class="<?php if (($action=="return_requests")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'return_requests')?>"> <i> <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="929.6px" height="929.6px" viewBox="0 0 929.6 929.6" style="enable-background:new 0 0 929.6 929.6;" xml:space="preserve"
	>
                <g>
                  <g>
                    <path d="M404.3,894.6c0,19.301,15.7,35,35,35h59c19.3,0,35-15.699,35-35v-157h-129V894.6z"/>
                    <path d="M533.3,35c0-19.3-15.7-35-35-35h-59c-19.3,0-35,15.7-35,35v30.7h129V35z"/>
                    <rect x="404.3" y="365.7" width="129" height="71.9"/>
                    <path d="M779.3,109.4c-6.6-8.6-16.9-13.7-27.8-13.7H533.3h-129H193.8c-19.3,0-35,15.7-35,35v170c0,19.3,15.7,35,35,35h210.5h129
			h218.2c10.899,0,21.2-5.1,27.8-13.7l65.2-85c9.6-12.6,9.6-30,0-42.6L779.3,109.4z"/>
                    <path d="M533.3,707.6h202.5c19.3,0,35-15.699,35-35v-170c0-19.299-15.7-35-35-35H533.3h-129H178.1
			c-10.899,0-21.199,5.1-27.8,13.701l-65.2,85c-9.6,12.6-9.6,30,0,42.6l65.2,85c6.601,8.6,16.9,13.699,27.8,13.699h226.2H533.3z"/>
                  </g>
                </g>
                </svg> </i> <span><?php echo Utilities::getLabel('M_Return_Requests')?></span> </a></li>
                
                <li class="favorities <?php if (($action=="favorites") || ($action=="favorite_shops") || ($action=="favorite_items") || ($action=="view_list")):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('account', 'favorites')?>"> <i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <g>
                  <path fill="#474747" d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42"/>
                </g>
           </svg></i> <span><?php echo Utilities::getLabel('M_Favorites')?> </span> </a></li>
          
          
                
                <li class="importexport <?php if ($controller=="import_export"):?>active<?php endif;?>"><a href="<?php echo Utilities::generateUrl('import_export', 'default_action')?>"> <i> <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="459px" height="459px" viewBox="0 0 459 459" style="enable-background:new 0 0 459 459;" xml:space="preserve">
<g>
	<g id="import-export">
		<path d="M153,0L51,102h76.5v178.5h51V102H255L153,0z M331.5,357V178.5h-51V357H204l102,102l102-102H331.5z"/>
	</g>
</g>
</svg></i> <span><?php echo Utilities::getLabel('M_Bulk_Import/Export')?> </span> </a></li> 
                      
            </ul>
          </div>
        </div>