<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
?>
<?php echo html_entity_decode($breadcrumb); ?>
<div class="fixed_container">
    <div class="row">
        <div class="col-sm-12">  
            <h1>Blog Comment - Delete </h1>
            <section class="section">
                <div class="sectionhead"><p>You can't recover "Contribution" once deleted, are you sure to delete? <a class="edit" href="<?php echo $delete_link; ?>">Yes</a> / <a class="edit" href="<?php echo Utilities::generateUrl('blogcontributions'); ?>">Cancel</a></p></div>
            </section>
        </div>
</div>
