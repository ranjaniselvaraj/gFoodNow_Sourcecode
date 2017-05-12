<?php
global $product_types;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="25%">Name</th>
        <th width="10%">Type</th>
        <th width="15%">Shop</th>
        <th width="2%">Sold</th>
        <th width="2%">Available</th>
        <th width="10%">Price</th>
        <th width="8%">Date</th>
        <th width="6%">Commission</th>
        <?php if ($canview === true) {
            echo '<th>Action</th>';
        } ?>
    </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
        $i = $start_record;
        foreach ($records as $record) {
            ?>
            <tr style="color:<?php if (($record['prod_status']==0)){ ?>#AAAAAA<?php }?>">
                
                <td><?php echo trim($record["prod_name"]) ?></td>
                <td><?php echo $product_types[$record["prod_type"]]; ?></td>
                <td><?php echo trim($record["shop_name"]) ?></td>
                <td><?php echo $record["prod_sold_count"] ?></td>
                <td><?php echo $record["prod_stock"] ?></td>
                <td nowrap="nowrap">
                <?php if ($record['special']) { ?>
                    <span class="cutTxt"><?php echo Utilities::displayMoneyFormat($record['prod_sale_price']); ?></span><br/>
                    <?php echo Utilities::displayMoneyFormat($record['special']); ?>
                    <?php } else { ?>
                        <?php echo Utilities::displayMoneyFormat($record['prod_sale_price']); ?>
                <?php } ?>
                </td>
                <td><?php echo displayDate($record["prod_added_on"])?></td>
                <td nowrap="nowrap">
                    <?php echo Utilities::displayMoneyFormat($record['commission']); ?>
                </td>
                
                <?php if ($canview === true) { 
							$hover_text = 'Click to Disable';
							$css_anchor_text = 'enabled';
							if ($record['prod_status']==0){
								$hover_text = 'Click to Enable';
								$css_anchor_text = 'disabled';
							}
					?>
                    
                    <td nowrap="nowrap">
                        <?php
                        echo '<ul class = "actions">
               <li><a href="javascript:void(0);" title="'.$hover_text.'" class="toggleswitch '.$css_anchor_text.'" ><i onclick="UpdateProductStatus(' . $record['prod_id'] . ', $(this));" class="ion-checkmark-circled icon"></i></a></li>
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('products', 'form', array($record['prod_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Delete" href="javascript:void(0);" onclick="ConfirmProductDelete(' . $record['prod_id'] . ', $(this));" ><i class ="ion-android-delete icon"></i></a></li>
				</ul>';
                    }
                    ?></td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
    <div class="gap"></div>
    <?php 
    if ($pages > 1) {
        $vars = array('page' => $page, 'pages' => $pages, 'start_record' => $start_record, 'end_record' => $end_record, 'total_records' => $total_records);
        echo Utilities::renderView(Utilities::getViewsPartialPath().'backend-pagination.php', $vars);
    }
}
?>