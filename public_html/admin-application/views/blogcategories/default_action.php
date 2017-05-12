<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		<?php echo html_entity_decode($breadcrumb); ?>		
		<div class="fixed_container">
			<div class="row">
			<div class="col-sm-12">				
				<section class="section"><div id="form-div"></div>
                        <div class="sectionhead"><h4>Blog Category Management</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <?php if($canview === true){ ?>
											<li><a href="<?php echo Utilities::generateUrl('blogcategories', 'add', array(0, $catId)); ?>">Add New Category</a></li><?php } ?>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						
                        <div style="display:none;" class="sectionbody togglewrap">                            
						<?php echo $frmCategory->getFormHtml(); ?>                         
						</div>
						<div class="sectionbody" id="listing-div"> </div>
					</section>	
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>
<script>    
   var catId=" <?php echo $catId ?>";
   $(document).ready(function () {
        //Table DND call
        $('.table').tableDnD({
            onDrop: function (table, row) {
                var order = $.tableDnD.serialize('id');
                order+='&catId='+catId;                
                // $.mbsmessage('Updating display order....');
                callAjax(generateUrl('blogcategories', 'setCatDisplayOrder'), order, function (t) {
                });
            }
        });
    });
</script>				