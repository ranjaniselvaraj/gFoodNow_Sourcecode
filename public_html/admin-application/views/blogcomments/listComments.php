<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
 if ($records || is_array($records)) {
 echo createHiddenFormFromPost('paginateForm', '', array(), array());
 }
?>
	
	
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
			<tr>
				<th width="6%">S. No.</th>
				<th>Author Name</th>
				<th>Author Email</th>
				<th>Comment</th>
				<th>Post</th>
				<th>Status</th>
				<?php if($canview === true){ echo '<th>Action</th>'; } ?>
			</tr>
                        <?php if(!$records || !is_array($records)){ echo '<tr><td colspan=6>No Record Found!!</td></tr>'; }else{
		
	?>
<?php
	$i=$start_record;
	foreach($records as $record){
		?>
			<tr>
				<td><?php echo $i; ?></td>
				<td><?php echo ucfirst($record['comment_author_name']); ?></td>
				<td><?php echo $record['comment_author_email']; ?></td>
				<td><?php echo Utilities::myTruncateCharacters($record['comment_content'], 20); ?></td>
				<td>
					<?php if($canview === true){?>
						<a href="<?php echo Utilities::generateUrl('blogposts', 'edit', array($record['post_id'])); ?>"><?php echo $record['post_title']; ?></a>
					<?php
					}
					?>	
					<br />
				<!---	<?php if($canview === true){?>
						<a target="_balnk" href="<?php echo Utilities::generateUrl('blog', 'post', array($record['post_seo_name']), '/'); ?>">View<a/>
					<?php
					}
					?>	 ---->
				</td>
				<td>
					<?php if($record['comment_status'] == 1){
						echo 'Approved';	
					}elseif($record['comment_status'] == 2){
						echo 'Cancelled';	
					}
					else{
						echo 'Pending';
					}
					?>
				</td>
				<td><?php 
                                echo '<ul class = "actions">';
              
          
                
                                
                                if($canview === true){ ?><li><a title = "View" href="<?php echo Utilities::generateUrl('blogcomments', 'view', array($record['comment_id'])); ?>" ><i class ="ion-ios-eye icon"></i></a></li> <?php } ?>
				<?php if($canview === true){ ?><li><a title = "Delete" onclick="return confirmDelete(this);"  data-href="<?php echo Utilities::generateUrl('blogcomments', 'delete', array($record['comment_id'])); ?>" ><i class ="ion-android-delete icon"></i></a></li><?php } ?></ul></td>
			</tr>
		<?php $i++;
	}
                        }
?>
		</table>
		 <?php 
                 $vars = array('page'=>$page,'pages'=>$pages,'start_record'=>$start_record,'end_record'=>$end_record,'total_records'=>$total_records);
                 echo Utilities::renderView(Utilities::getViewsPartialPath().'backend-pagination.php',$vars); ?>