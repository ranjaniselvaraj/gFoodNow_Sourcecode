<?php defined('SYSTEM_INIT') or die('Invalid Usage');  global $review_status;?> 
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">			
					<div id="form-div"></div>
					<section class="section"> 
                        <div class="sectionhead"><h4>404 Error Page</h4><br />
						</div>
						
                        <div class="sectionbody">   
                        	<div class="toptitle"><h4>Sorry the page you requested is unavailable at the moment.</h4>
                            
                            <p><strong>It may have occured due to:</strong>
		    			    	  <br />
						        	  1. Invalid request,<br />
    						      2. Incorrect page url.,<br />
			    	    		  3. Page or File link is timed out at this moment.</p>
                                  
                            </div>
			 				 
                                                 
                        </div>								
                        </section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				