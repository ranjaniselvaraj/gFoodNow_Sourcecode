<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/LiveEditor/scripts/innovaeditor.js"></script>
<script src="<?php echo CONF_WEBROOT_URL; ?>js/LiveEditor/scripts/common/webfont.js" type="text/javascript"></script>
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
					<section class="section">
						<div class="sectionhead"><h4>Add - Blog Post</h4></div>
						<div class="sectionbody space">
							<?php echo $frmAdd->getFormTag(); ?>
							<div class="formhorizontal">
								<div class="field_control horizontal">
									<label class="field_label">Post Title <span class="spn_must_field">*</span></label>
									<div class="field_cover">
										<?php echo $frmAdd->getFieldHtml('post_title'); ?>
									</div>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Post Contributor Name<span class="spn_must_field"></span></label>
									<div class="field_cover">
										<?php echo $frmAdd->getFieldHtml('post_contributor_name'); ?>
									</div>
								</div>   
								<div class="field_control horizontal">
									<label class="field_label">Post SEO Name<span class="spn_must_field">*</span></label>
									<div class="field_cover">
										<?php echo $frmAdd->getFieldHtml('post_seo_name'); ?>
									</div>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Post Short Description</label>
									<div class="field_cover">
										<?php echo $frmAdd->getFieldHtml('post_short_description'); ?>
									</div>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Post Content<span class="spn_must_field">*</span></label>
									<div class="field_cover">
										<?php echo $frmAdd->getFieldHtml('post_content'); ?>
									</div>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Post Image</label>
									<?php echo Utilities::renderView(Utilities::getViewsPartialPath().'upload_images.php'); ?>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Post Comment Status</label>
									<div class="field_cover">
										<div class="selectfield">
											<?php echo $frmAdd->getFieldHtml('post_comment_status'); ?>
										</div>
									</div>
								</div>
							
								<div class="field_control horizontal">
									<label class="field_label">Parent Category<span class="spn_must_field">*</span></label>
									<div class="field_cover">
										<div class="boxwraplist">
											<span class="boxlabel">Select Category</span>
											<?php //echo $frmAddUpdate->getFieldHtml('relation_id[]'); ?>
											<div class="scrollerwrap">
												<ul class="verticalcheck_list">
												<?php echo html_entity_decode($categoriesStructure, ENT_QUOTES, 'UTF-8'); ?>
												
												</ul>
											</div>
										</div>
										<?php 
										/* Just to show error message properly*/
											echo  '<input style="display:none;" type="checkbox" title="parent category" name="relation_category_id[]">';
										?>
									</div>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Meta Title</label>
									<div class="field_cover">
										<?php echo $frmAdd->getFieldHtml('meta_title'); ?>
									</div>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Meta Keyword</label>
									<div class="field_cover">
										<?php echo $frmAdd->getFieldHtml('meta_keywords'); ?>
									</div>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Meta Description</label>
									<div class="field_cover">
										<?php echo $frmAdd->getFieldHtml('meta_description'); ?>
									</div>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Meta Other</label>
									<div class="field_cover">
										<?php echo $frmAdd->getFieldHtml('meta_others'); ?>
									</div>
								</div>
								<div class="field_control horizontal">
									<label class="field_label">Post Status<span class="spn_must_field">*</span></label>
									<div class="field_cover">
										<div class="selectfield">
											<?php echo $frmAdd->getFieldHtml('post_status'); ?>
										</div>
									</div>
								</div>
								<div class="field_control horizontal">
									<div class="field_cover offset">
										<?php echo $frmAdd->getFieldHtml('btn_submit'); ?>
										<?php echo $frmAdd->getFieldHtml('post_id'); ?>
										<?php echo $frmAdd->getFieldHtml('meta_id'); ?>
										</form><?php echo $frmAdd->getExternalJs(); ?>
									</div>
								</div>
 
							</div>
 
							</form>
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
<script>
     function validateCheckbox() {
        var chbox_length = $('input[name$="relation_category_id[]"]:checked').length;
        //   alert(chbox_length);
        if (chbox_length == 0) {
             $.extend(frmPost_validator_requirements,{'relation_category_id': {"required": true}});
        } else {
         $.extend(frmPost_validator_requirements,{'relation_category_id': {"required": false}});
        }
        $("#frmPost").unbind("submit");
        frmPost_validator = $("#frmPost").validation(frmPost_validator_requirements, frmPost_validator_formatting);
    }
    $(document).ready(function () {
        validateCheckbox();
    });
</script>